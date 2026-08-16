<template>
    <header class="fixed top-0 left-0 right-0 z-30 bg-white border-b border-neutral-200 shadow-sm">
        <div class="h-16 flex items-center justify-between px-4 sm:px-6">

            <div class="flex items-center gap-3">
                <div id="sidebar-toggle-anchor"></div>

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
                @click="$emit('logout')"
                class="flex items-center gap-2 text-sm text-neutral-600 hover:text-neutral-900 transition px-3 py-2 rounded-lg hover:bg-neutral-100"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </button>
        </div>
    </header>
</template>

<script setup>
defineEmits(['logout']);
</script>
