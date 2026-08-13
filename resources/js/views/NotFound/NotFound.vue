<template>
    <div class="min-h-screen bg-neutral-100 flex items-center justify-center px-4">
        <div class="text-center">
            <p class="text-8xl font-bold text-blue-600">404</p>
            <h1 class="mt-4 text-2xl font-semibold text-neutral-900 tracking-tight">Page not found</h1>
            <p class="mt-2 text-sm text-neutral-500">The page you're looking for doesn't exist.</p>
            <button
                @click="router.push('/')"
                class="mt-8 inline-block bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition"
            >
                Go home
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
</script>
