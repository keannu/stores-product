<template>
    <div class="min-h-screen bg-neutral-100 font-sans">

        <TopNavigationBar @logout="handleLogout" />

        <LeftNavigationBar />

        <div class="flex pt-16 min-h-screen">
            <main class="flex-1 ml-0 md:ml-56 px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import TopNavigationBar from './TopNavigationBar.vue';
import LeftNavigationBar from './LeftNavigationBar.vue';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

async function handleLogout() {
    await axios.post('/logout');
    window.location.href = '/login';
}
</script>
