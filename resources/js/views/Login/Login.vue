<template>
    <div class="min-h-screen flex bg-neutral-50">

        <!-- Left panel — brand / hero -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-600 flex-col justify-between p-12 relative overflow-hidden">

            <!-- Background circles for depth -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500 rounded-full opacity-40"></div>
            <div class="absolute -bottom-32 -right-20 w-[28rem] h-[28rem] bg-blue-700 rounded-full opacity-40"></div>

            <!-- Logo -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                        <path d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                    </svg>
                </div>
                <span class="text-white text-lg font-semibold tracking-tight">StoresPro</span>
            </div>

            <!-- Center copy -->
            <div class="relative z-10">
                <h2 class="text-4xl font-bold text-white leading-snug mb-4">
                    Your trusted<br />marketplace
                </h2>
                <p class="text-blue-100 text-base leading-relaxed max-w-xs">
                    Thousands of products. Verified sellers. Secure checkout — every single time.
                </p>

                <!-- Trust badges -->
                <div class="mt-10 space-y-3">
                    <div v-for="badge in badges" :key="badge.label" class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="badge.icon"/>
                            </svg>
                        </div>
                        <span class="text-blue-100 text-sm">{{ badge.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="relative z-10 text-blue-200 text-xs">© 2026 StoresPro. All rights reserved.</p>
        </div>

        <!-- Right panel — form -->
        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">

                <!-- Mobile logo -->
                <div class="flex lg:hidden items-center gap-2 mb-10">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                            <path d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                    </div>
                    <span class="text-neutral-800 text-base font-semibold">StoresPro</span>
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Sign in</h1>
                    <p class="text-sm text-neutral-500 mt-1">New here?
                        <button @click="handleRegister" class="text-blue-600 hover:underline font-medium">Create an account</button>
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleLogin" class="space-y-5">

                    <div>
                        <label for="username" class="block text-sm font-medium text-neutral-700 mb-1.5">Username</label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            placeholder="your_username"
                            autocomplete="username"
                            class="w-full bg-white border border-neutral-300 rounded-xl px-4 py-3 text-sm text-neutral-900 placeholder-neutral-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-sm font-medium text-neutral-700">Password</label>
                            <a href="#" class="text-xs text-blue-600 hover:underline">Forgot password?</a>
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full bg-white border border-neutral-300 rounded-xl px-4 py-3 text-sm text-neutral-900 placeholder-neutral-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition"
                    >
                        Sign in
                    </button>

                    <div class="relative flex items-center gap-3">
                        <div class="flex-1 h-px bg-neutral-200"></div>
                        <span class="text-xs text-neutral-400 shrink-0">or</span>
                        <div class="flex-1 h-px bg-neutral-200"></div>
                    </div>

                    <button
                        type="button"
                        @click="handleRegister"
                        class="w-full bg-white hover:bg-neutral-50 active:bg-neutral-100 text-neutral-700 text-sm font-medium py-3 rounded-xl border border-neutral-300 shadow-sm transition"
                    >
                        Create an account
                    </button>
                </form>

                <!-- Trust note -->
                <p class="mt-8 text-center text-xs text-neutral-400 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.657-1.343-3-3-3S6 9.343 6 11v1H5a1 1 0 00-1 1v6a1 1 0 001 1h14a1 1 0 001-1v-6a1 1 0 00-1-1h-1v-1c0-1.657-1.343-3-3-3s-3 1.343-3 3v1H9v-1z"/>
                    </svg>
                    Secured with 256-bit SSL encryption
                </p>
            </div>
        </div>

    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = reactive({
    username: '',
    password: '',
});

const badges = [
    { label: 'SSL-encrypted & secure checkout', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { label: 'Verified sellers & buyer protection', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { label: 'Easy returns within 30 days', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
];

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
