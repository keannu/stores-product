<?php

namespace Tests\Unit\Services\Login;

use App\Models\User;
use App\Services\Login\LoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoginService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LoginService();

        // Ensure a clean rate-limiter state before each test
        RateLimiter::clear('login:user@example.com');
        RateLimiter::clear('login:locked@example.com');
    }

    private function makeRequest(string $email, string $password): Request
    {
        $request = Request::create('/login', 'POST', [
            'email'    => $email,
            'password' => $password,
        ]);

        $request->setLaravelSession($this->app['session']->driver());

        return $request;
    }

    public function test_returns_404_when_email_does_not_exist(): void
    {
        $request = $this->makeRequest('nonexistent@example.com', 'password');

        $response = $this->service->login($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(
            'No account found with that email address.',
            $response->getData()->message,
        );
    }

    public function test_returns_401_with_remaining_attempts_on_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->service->login(
            $this->makeRequest('user@example.com', 'wrong-password'),
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString(
            '4 attempt(s) remaining',
            $response->getData()->message,
        );
    }

    public function test_remaining_attempts_decrease_on_each_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->service->login($this->makeRequest('user@example.com', 'wrong'));
        $this->service->login($this->makeRequest('user@example.com', 'wrong'));

        $response = $this->service->login(
            $this->makeRequest('user@example.com', 'wrong'),
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString(
            '2 attempt(s) remaining',
            $response->getData()->message,
        );
    }

    public function test_returns_429_when_rate_limit_is_already_exceeded(): void
    {
        $key = 'login:locked@example.com';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 3600);
        }

        $response = $this->service->login(
            $this->makeRequest('locked@example.com', 'password'),
        );

        $this->assertSame(429, $response->getStatusCode());
        $this->assertStringContainsString(
            'Account locked',
            $response->getData()->message,
        );
    }

    public function test_returns_429_after_exhausting_all_attempts(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        // Pre-fill 4 failures so the next wrong attempt is the 5th (final lockout)
        $key = 'login:user@example.com';
        for ($i = 0; $i < 4; $i++) {
            RateLimiter::hit($key, 3600);
        }

        $response = $this->service->login(
            $this->makeRequest('user@example.com', 'wrong-password'),
        );

        $this->assertSame(429, $response->getStatusCode());
        $this->assertStringContainsString(
            'Account locked',
            $response->getData()->message,
        );
    }

    public function test_returns_200_with_redirect_on_successful_login(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->service->login(
            $this->makeRequest('user@example.com', 'secret'),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Login successful', $response->getData()->message);
        $this->assertObjectHasProperty('redirect', $response->getData());
    }

    public function test_clears_rate_limiter_on_successful_login(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('secret'),
        ]);

        // Simulate prior failures
        $key = 'login:user@example.com';
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);

        $this->service->login($this->makeRequest('user@example.com', 'secret'));

        $this->assertSame(0, RateLimiter::attempts($key));
    }
}
