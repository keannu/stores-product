<template>
    <div class="min-h-screen bg-neutral-100 font-sans">

        <!-- Top Navigation -->
        <header class="fixed top-0 left-0 right-0 z-30 bg-white border-b border-neutral-200 shadow-sm">
            <div class="h-16 flex items-center justify-between px-4 sm:px-6">

                <div class="flex items-center gap-3">
                    <!-- Hamburger — mobile only -->
                    <button
                        @click="toggleSidebar"
                        class="md:hidden flex items-center justify-center w-9 h-9 rounded-lg text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition"
                        :aria-label="isOpen ? 'Close navigation' : 'Open navigation'"
                    >
                        <svg v-if="!isOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Brand -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-base font-semibold text-neutral-900 tracking-tight">Stores</span>
                    </div>
                </div>

                <!-- Logout -->
                <button
                    @click="handleLogout"
                    class="flex items-center gap-2 text-sm text-neutral-600 hover:text-neutral-900 transition px-3 py-2 rounded-lg hover:bg-neutral-100"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </div>
        </header>

        <!-- Mobile overlay backdrop -->
        <div
            v-if="isOpen"
            @click="toggleSidebar"
            class="fixed inset-0 z-20 bg-neutral-900/40 md:hidden"
        ></div>

        <div class="flex pt-16 min-h-screen">

            <!-- Side Navigation -->
            <aside
                class="fixed top-16 left-0 bottom-0 w-56 bg-white border-r border-neutral-200 flex flex-col z-20 transition-transform duration-200 ease-in-out"
                :class="isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            >
                <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition"
                        :class="isActive(item.href)
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'"
                    >
                        <svg
                            class="w-[18px] h-[18px] shrink-0"
                            :class="isActive(item.href) ? 'text-blue-600' : 'text-neutral-400'"
                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                        </svg>
                        {{ item.label }}
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 ml-0 md:ml-56 px-6 py-8">

                <!-- Welcome -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Dashboard</h1>
                    <p class="mt-1 text-sm text-neutral-500">Welcome back. Here's an overview of your account.</p>
                </div>

                <!-- Cards grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    <!-- Account card -->
                    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-neutral-700">Account</h2>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Name</dt>
                                <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                            </div>
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Email</dt>
                                <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                            </div>
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Role</dt>
                                <dd class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700">—</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Store card -->
                    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-neutral-700">Store</h2>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Store Name</dt>
                                <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                            </div>
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Address</dt>
                                <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                            </div>
                            <div>
                                <dt class="text-neutral-400 text-xs uppercase tracking-wide">Email</dt>
                                <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Security card -->
                    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 bg-neutral-50 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-neutral-700">Security</h2>
                        </div>
                        <p class="text-sm text-neutral-500 leading-relaxed">
                            Your session is protected. Sign out when you're done using a shared device.
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Session active
                        </div>
                    </div>

                </div>
            </main>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

const route = useRoute();
const isOpen = ref(false);

const navItems = [
    { label: 'Stores',   href: '/stores',   icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z' },
    { label: 'Products', href: '/products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10' },
    { label: 'Orders',   href: '/orders',   icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Users',    href: '/users',    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

function isActive(href) {
    return route.path === href;
}

function toggleSidebar() {
    isOpen.value = !isOpen.value;
}

async function handleLogout() {
    await axios.post('/logout');
    window.location.href = '/';
}
</script>
