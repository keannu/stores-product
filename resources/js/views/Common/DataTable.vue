<template>
    <!-- Table card -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- Count bar (upper left) -->
        <div v-if="!loading && meta.total > 0" class="px-5 py-3 border-b border-neutral-100 bg-neutral-50/40">
            <p class="text-sm font-medium text-neutral-600 tabular-nums">
                {{ meta.total }} {{ itemLabel }}{{ meta.total !== 1 ? 's' : '' }} found
            </p>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="divide-y divide-neutral-100">
            <div v-for="i in 5" :key="i" class="flex items-center gap-4 px-5 py-4 animate-pulse">
                <div class="w-9 h-9 rounded-full bg-neutral-200 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3.5 bg-neutral-200 rounded w-36"></div>
                    <div class="h-3 bg-neutral-100 rounded w-52"></div>
                </div>
                <div class="h-5 bg-neutral-100 rounded-full w-16"></div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!items.length" class="flex flex-col items-center justify-center py-16 px-4">
            <div class="w-14 h-14 bg-neutral-100 rounded-2xl flex items-center justify-center mb-4">
                <slot name="empty-icon">
                    <svg class="w-7 h-7 text-neutral-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                    </svg>
                </slot>
            </div>
            <p class="text-sm font-medium text-neutral-700 mb-1">{{ emptyTitle }}</p>
            <p class="text-xs text-neutral-400">{{ emptySubtitle }}</p>
        </div>

        <!-- Data table -->
        <table v-else class="w-full text-sm">
            <thead>
                <slot name="header"></slot>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                <slot :items="items"></slot>
            </tbody>
        </table>

        <!-- Pagination (lower right, inside table card) -->
        <div v-if="meta.last_page > 1" class="flex items-center justify-end gap-3 px-5 py-3 border-t border-neutral-100 bg-neutral-50/40">
            <span class="text-sm font-medium text-neutral-600 tabular-nums">
                Page {{ meta.current_page }} of {{ meta.last_page }}
            </span>
            <div class="flex items-center gap-1.5">
                <button
                    @click="$emit('page-change', meta.current_page - 1)"
                    :disabled="meta.current_page === 1"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border border-blue-200 text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>
                    Prev
                </button>
                <button
                    @click="$emit('page-change', meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border border-blue-200 text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                >
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    items: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({ current_page: 1, last_page: 1, total: 0 }),
    },
    emptyTitle: {
        type: String,
        default: 'No results found',
    },
    emptySubtitle: {
        type: String,
        default: 'Try adjusting your filters.',
    },
    itemLabel: {
        type: String,
        default: 'item',
    },
})

defineEmits(['page-change'])
</script>
