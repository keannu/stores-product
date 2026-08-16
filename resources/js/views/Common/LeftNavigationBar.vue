<template>
    <!-- Hamburger button teleported into header -->
    <Teleport to="#sidebar-toggle-anchor">
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
    </Teleport>

    <!-- Mobile overlay backdrop -->
    <div
        v-if="isOpen"
        @click="toggleSidebar"
        class="fixed inset-0 z-20 bg-neutral-900/40 md:hidden"
    ></div>

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
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const isOpen = ref(false);

function toggleSidebar() {
    isOpen.value = !isOpen.value;
}

const navItems = [
    { label: 'Stores',   href: '/dashboard/stores',   icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z' },
    { label: 'Products', href: '/dashboard/products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10' },
    { label: 'Orders',   href: '/dashboard/orders',   icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Users',    href: '/dashboard/users',    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

function isActive(href) {
    return route.path === href;
}
</script>
