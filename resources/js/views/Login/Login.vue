<template>
    <div class="min-h-screen bg-white sm:bg-neutral-100 flex flex-col sm:items-center sm:justify-center sm:px-4 sm:py-12">

        <div class="flex-1 flex flex-col w-full sm:flex-none sm:max-w-md">

            <!-- Card (full-bleed on mobile, boxed from sm up) -->
            <div class="flex-1 flex flex-col bg-white px-6 py-8 sm:flex-none sm:px-8 sm:border sm:border-neutral-200 sm:rounded-2xl sm:shadow-sm">

                <!-- Brand -->
                <div class="flex items-center gap-3 mb-8 sm:mb-7">
                    <div class="w-11 h-11 sm:w-10 sm:h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-neutral-900 tracking-tight">Stores</span>
                </div>

                <!-- Heading -->
                <h1 class="text-3xl sm:text-2xl font-bold text-neutral-900 tracking-tight">Sign in to your account</h1>

                <!-- Form -->
                <form @submit.prevent="handleLogin" class="mt-8 sm:mt-7 space-y-5">

                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-800 mb-1.5">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Email"
                            autocomplete="email"
                            class="w-full bg-white border border-neutral-300 rounded-lg px-4 sm:px-3.5 py-3.5 sm:py-2.5 text-base sm:text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-sm font-medium text-neutral-800">Password</label>
                            <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password</a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                class="w-full bg-white border border-neutral-300 rounded-lg pl-4 sm:pl-3.5 pr-12 sm:pr-11 py-3.5 sm:py-2.5 text-base sm:text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                class="absolute inset-y-0 right-0 px-4 sm:px-3.5 flex items-center text-neutral-400 hover:text-neutral-600 transition"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 sm:gap-2.5 cursor-pointer select-none py-1 sm:py-0">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-5 h-5 sm:w-4 sm:h-4 rounded border-neutral-300 text-blue-600 focus:ring-2 focus:ring-blue-500 accent-blue-600"
                        />
                        <span class="text-base sm:text-sm text-neutral-700">Keep me signed in on this device</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-base sm:text-sm font-semibold py-4 sm:py-3 rounded-lg shadow-sm transition"
                    >
                        Sign in
                    </button>
                </form>

                <!-- Trust note (always directly below the Sign in button) -->
                <div class="mt-7 pt-6">
                    <div class="flex items-start gap-2.5 border-t border-neutral-200 pt-6">
                        <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Encrypted connection. We never ask for your password by email or phone.
                        </p>
                    </div>

                    <!-- Register -->
                    <div class="mt-5 px-4 py-3 text-center">
                        <p class="text-sm text-neutral-600">
                            New here?
                            <button @click="handleRegister" class="text-blue-600 hover:underline font-medium">Create an account</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const showPassword = ref(false);

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

async function handleLogin() {
    const response = await fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: JSON.stringify(form),
    });

    if (response.ok) {
        router.push('/');
    }
}

function handleRegister() {
    router.push('/register');
}
</script>
