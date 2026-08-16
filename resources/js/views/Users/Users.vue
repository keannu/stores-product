<template>
    <DashboardLayout>

        <!-- Page header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Users</h1>
                <p class="mt-1 text-sm text-neutral-500">Manage all users in the system.</p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-500 rounded-xl hover:bg-blue-600 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-4 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="relative w-full sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name or email…"
                        class="w-full pl-10 pr-9 py-2.5 text-sm bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white placeholder-neutral-400 transition"
                    />
                    <button
                        v-if="search"
                        @click="search = ''; applyFilters()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="relative w-full sm:w-48">
                    <select
                        v-model="filterStoreId"
                        class="w-full pl-3.5 pr-9 py-2.5 text-sm bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition appearance-none"
                    >
                        <option :value="null">All Stores</option>
                        <option v-for="store in stores" :key="store.id" :value="store.id">
                            {{ store.store_name }}
                        </option>
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
                <div class="relative w-full sm:w-40">
                    <select
                        v-model="filterRole"
                        class="w-full pl-3.5 pr-9 py-2.5 text-sm bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition appearance-none"
                    >
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
                <button
                    @click="applyFilters"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
                    </svg>
                    Filter
                </button>
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-xl hover:bg-neutral-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- Table card -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">

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
            <div v-else-if="!users.length" class="flex flex-col items-center justify-center py-16 px-4">
                <div class="w-14 h-14 bg-neutral-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-neutral-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-neutral-700 mb-1">No users found</p>
                <p class="text-xs text-neutral-400">{{ search ? 'Try a different search term.' : 'Add your first user to get started.' }}</p>
            </div>

            <!-- Data table -->
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 bg-neutral-50/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider hidden md:table-cell">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider hidden lg:table-cell">Store</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="group hover:bg-blue-50/30 transition-colors"
                    >
                        <!-- User: avatar + name/email stacked -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold uppercase shrink-0"
                                    :class="user.role === 'admin'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-neutral-100 text-neutral-500'"
                                >{{ userInitials(user.name) }}</div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-neutral-900 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-neutral-400 truncate">{{ user.email }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Role badge -->
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                                :class="user.role === 'admin'
                                    ? 'bg-blue-50 text-blue-700'
                                    : 'bg-neutral-100 text-neutral-600'"
                            >{{ user.role }}</span>
                        </td>

                        <!-- Store -->
                        <td class="px-5 py-3.5 hidden lg:table-cell">
                            <span class="text-sm text-neutral-500">{{ storeName(user.store_id) }}</span>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button
                                    @click="openEdit(user)"
                                    class="p-2 rounded-lg text-neutral-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Edit"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125"/>
                                    </svg>
                                </button>
                                <button
                                    @click="openChangePassword(user)"
                                    class="p-2 rounded-lg text-neutral-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Change Password"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(user)"
                                    class="p-2 rounded-lg text-neutral-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination + count -->
        <div class="flex items-center mt-5" :class="meta.last_page > 1 ? 'justify-between' : 'justify-end'">
            <template v-if="meta.last_page > 1">
                <p class="text-xs text-neutral-400 tabular-nums">
                    Page {{ meta.current_page }} of {{ meta.last_page }}
                </p>
                <div class="flex items-center gap-1.5">
                    <button
                        @click="goToPage(meta.current_page - 1)"
                        :disabled="meta.current_page === 1"
                        class="p-2 rounded-lg border border-neutral-200 text-neutral-500 hover:bg-neutral-50 disabled:opacity-30 disabled:cursor-not-allowed transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <button
                        @click="goToPage(meta.current_page + 1)"
                        :disabled="meta.current_page === meta.last_page"
                        class="p-2 rounded-lg border border-neutral-200 text-neutral-500 hover:bg-neutral-50 disabled:opacity-30 disabled:cursor-not-allowed transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </template>
            <p v-if="meta.total > 0" class="text-xs text-neutral-400 tabular-nums">
                {{ meta.total }} user{{ meta.total !== 1 ? 's' : '' }} found
            </p>
        </div>

        <!-- Create / Edit Modal -->
        <Teleport to="body">
            <!-- Backdrop -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="modal.show"
                    class="fixed inset-0 z-50 bg-neutral-900/50 backdrop-blur-sm"
                    @click="closeModal"
                ></div>
            </Transition>

            <!-- Panel -->
            <Transition
                enter-active-class="transition duration-250 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-2"
            >
                <div
                    v-if="modal.show"
                    class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none"
                >
                    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-neutral-900/5 w-full max-w-md mx-4 overflow-hidden pointer-events-auto">

                        <!-- Header -->
                        <div class="px-6 pt-6 pb-5 border-b border-neutral-100">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3.5">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                        :class="modal.mode === 'create' ? 'bg-blue-50' : 'bg-amber-50'"
                                    >
                                        <svg
                                            v-if="modal.mode === 'create'"
                                            class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-neutral-900">
                                            {{ modal.mode === 'create' ? 'Add New User' : 'Edit User' }}
                                        </h2>
                                        <p class="text-xs text-neutral-400 mt-0.5">
                                            {{ modal.mode === 'create' ? 'Fill in the details to create a user.' : 'Update the user information below.' }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="closeModal"
                                    class="p-1.5 -mr-1.5 -mt-0.5 rounded-lg text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <form @submit.prevent="submitModal" class="p-6 space-y-4">

                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Name</label>
                                <input
                                    v-model="modal.form.name"
                                    type="text"
                                    placeholder="Full name"
                                    class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition"
                                    :class="modal.errors.name ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                />
                                <p v-if="modal.errors.name" class="mt-1.5 text-xs text-red-500">{{ modal.errors.name[0] }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Email</label>
                                <input
                                    v-model="modal.form.email"
                                    type="email"
                                    placeholder="user@example.com"
                                    class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition"
                                    :class="modal.errors.email ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                />
                                <p v-if="modal.errors.email" class="mt-1.5 text-xs text-red-500">{{ modal.errors.email[0] }}</p>
                            </div>

                            <!-- Role & Store — side by side -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Role</label>
                                    <select
                                        v-model="modal.form.role"
                                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition appearance-none"
                                        :class="modal.errors.role ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                    >
                                        <option value="">Select…</option>
                                        <option value="admin">Admin</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                    <p v-if="modal.errors.role" class="mt-1.5 text-xs text-red-500">{{ modal.errors.role[0] }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Store</label>
                                    <select
                                        v-model="modal.form.store_id"
                                        class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition appearance-none"
                                        :class="modal.errors.store_id ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                    >
                                        <option :value="null">None</option>
                                        <option v-for="store in stores" :key="store.id" :value="store.id">
                                            {{ store.store_name }}
                                        </option>
                                    </select>
                                    <p v-if="modal.errors.store_id" class="mt-1.5 text-xs text-red-500">{{ modal.errors.store_id[0] }}</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-2.5 pt-3 border-t border-neutral-100 mt-5">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-4 py-2.5 text-sm font-medium text-neutral-600 rounded-xl border border-neutral-200 hover:bg-neutral-50 active:scale-[0.97] transition-all"
                                >Cancel</button>
                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-blue-500 to-blue-600 rounded-xl hover:from-blue-600 hover:to-blue-700 active:scale-[0.97] disabled:opacity-50 shadow-md shadow-blue-500/25 transition-all"
                                >
                                    <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    {{ submitting ? 'Saving…' : (modal.mode === 'create' ? 'Create User' : 'Save Changes') }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="deleteModal.show"
                    class="fixed inset-0 z-50 bg-neutral-900/50 backdrop-blur-sm"
                    @click="closeDeleteModal"
                ></div>
            </Transition>

            <Transition
                enter-active-class="transition duration-250 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-2"
            >
                <div
                    v-if="deleteModal.show"
                    class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none"
                >
                    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-neutral-900/5 w-full max-w-sm mx-4 overflow-hidden pointer-events-auto">
                        <div class="p-6 text-center">
                            <!-- Icon -->
                            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </div>

                            <h3 class="text-base font-bold text-neutral-900 mb-1">Delete User</h3>
                            <p class="text-sm text-neutral-500">
                                Are you sure you want to delete <span class="font-semibold text-neutral-700">"{{ deleteModal.user?.name }}"</span>? This action cannot be undone.
                            </p>
                        </div>

                        <div class="flex gap-3 px-6 pb-6">
                            <button
                                @click="closeDeleteModal"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-neutral-600 rounded-xl border border-neutral-200 hover:bg-neutral-50 active:scale-[0.97] transition-all"
                            >Cancel</button>
                            <button
                                @click="executeDelete"
                                :disabled="deleting"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-red-500 to-red-600 rounded-xl hover:from-red-600 hover:to-red-700 active:scale-[0.97] disabled:opacity-50 shadow-md shadow-red-500/25 transition-all"
                            >
                                <svg v-if="deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                {{ deleting ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Change Password Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="passwordModal.show"
                    class="fixed inset-0 z-50 bg-neutral-900/50 backdrop-blur-sm"
                    @click="closePasswordModal"
                ></div>
            </Transition>

            <Transition
                enter-active-class="transition duration-250 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-2"
            >
                <div
                    v-if="passwordModal.show"
                    class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none"
                >
                    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-neutral-900/5 w-full max-w-sm mx-4 overflow-hidden pointer-events-auto">

                        <!-- Header -->
                        <div class="px-6 pt-6 pb-5 border-b border-neutral-100">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-neutral-900">Change Password</h2>
                                        <p class="text-xs text-neutral-400 mt-0.5">{{ passwordModal.userName }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="closePasswordModal"
                                    class="p-1.5 -mr-1.5 -mt-0.5 rounded-lg text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <form @submit.prevent="submitPassword" class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">New Password</label>
                                <input
                                    v-model="passwordModal.form.password"
                                    type="password"
                                    placeholder="Enter new password"
                                    class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition"
                                    :class="passwordModal.errors.password ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                />
                                <p v-if="passwordModal.errors.password" class="mt-1.5 text-xs text-red-500">{{ passwordModal.errors.password[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Confirm Password</label>
                                <input
                                    v-model="passwordModal.form.password_confirmation"
                                    type="password"
                                    placeholder="Confirm new password"
                                    class="w-full px-3.5 py-2.5 text-sm bg-neutral-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 focus:bg-white transition"
                                    :class="passwordModal.errors.password_confirmation ? 'border-red-300 bg-red-50/50 focus:ring-red-500/40 focus:border-red-500' : 'border-neutral-200'"
                                />
                                <p v-if="passwordModal.errors.password_confirmation" class="mt-1.5 text-xs text-red-500">{{ passwordModal.errors.password_confirmation[0] }}</p>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-2.5 pt-3 border-t border-neutral-100 mt-5">
                                <button
                                    type="button"
                                    @click="closePasswordModal"
                                    class="px-4 py-2.5 text-sm font-medium text-neutral-600 rounded-xl border border-neutral-200 hover:bg-neutral-50 active:scale-[0.97] transition-all"
                                >Cancel</button>
                                <button
                                    type="submit"
                                    :disabled="changingPassword"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-amber-500 to-amber-600 rounded-xl hover:from-amber-600 hover:to-amber-700 active:scale-[0.97] disabled:opacity-50 shadow-md shadow-amber-500/25 transition-all"
                                >
                                    <svg v-if="changingPassword" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    {{ changingPassword ? 'Updating…' : 'Update Password' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import DashboardLayout from '../Common/DashboardLayout.vue';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// --- State ---
const users       = ref([]);
const stores      = ref([]);
const loading     = ref(false);
const submitting  = ref(false);
const search        = ref('');
const filterStoreId = ref(null);
const filterRole    = ref('');
const currentPage   = ref(1);
const meta        = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const deleteModal = reactive({
    show: false,
    user: null,
});
const deleting = ref(false);

const modal = reactive({
    show:   false,
    mode:   'create',
    editId: null,
    form:   emptyForm(),
    errors: {},
});

function emptyForm() {
    return { name: '', email: '', role: '', store_id: null };
}

// --- Fetch ---
async function fetchUsers() {
    loading.value = true;
    try {
        const params = { search: search.value, page: currentPage.value };
        if (filterStoreId.value !== null) {
            params.store_id = filterStoreId.value;
        }
        if (filterRole.value !== '') {
            params.role = filterRole.value;
        }
        const { data } = await axios.get('/api/dashboard/users', { params });
        users.value = data.data;
        meta.value  = data.meta;
    } catch {
        alert('Failed to load users.');
    } finally {
        loading.value = false;
    }
}

async function fetchStores() {
    try {
        const { data } = await axios.get('/api/dashboard/stores');
        stores.value = data;
    } catch {
        // non-critical — store select will simply be empty
    }
}

onMounted(() => {
    fetchUsers();
    fetchStores();
});

const hasActiveFilters = computed(() => search.value !== '' || filterStoreId.value !== null || filterRole.value !== '');

function applyFilters() {
    currentPage.value = 1;
    fetchUsers();
}

function resetFilters() {
    search.value        = '';
    filterStoreId.value = null;
    filterRole.value    = '';
    currentPage.value   = 1;
    fetchUsers();
}

// --- Helpers ---
function userInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    return parts.length >= 2
        ? parts[0][0] + parts[parts.length - 1][0]
        : parts[0][0];
}

function storeName(storeId) {
    if (!storeId) return '—';
    const store = stores.value.find(s => s.id === storeId);
    return store ? store.store_name : storeId;
}

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page) return;
    currentPage.value = page;
    fetchUsers();
}

// --- Modal ---
function openCreate() {
    modal.mode   = 'create';
    modal.editId = null;
    modal.form   = emptyForm();
    modal.errors = {};
    modal.show   = true;
}

function openEdit(user) {
    modal.mode   = 'edit';
    modal.editId = user.id;
    modal.form   = { name: user.name, email: user.email, role: user.role, store_id: user.store_id };
    modal.errors = {};
    modal.show   = true;
}

function closeModal() {
    modal.show = false;
}

async function submitModal() {
    submitting.value = true;
    modal.errors     = {};
    try {
        if (modal.mode === 'create') {
            await axios.post('/api/dashboard/users', modal.form);
        } else {
            await axios.put(`/api/dashboard/users/${modal.editId}`, modal.form);
        }
        closeModal();
        fetchUsers();
    } catch (error) {
        if (error.response?.status === 422) {
            modal.errors = error.response.data.errors ?? {};
        } else {
            alert(error.response?.data?.message ?? 'Something went wrong.');
        }
    } finally {
        submitting.value = false;
    }
}

// --- Change Password ---
const passwordModal = reactive({
    show: false,
    userId: null,
    userName: '',
    form: { password: '', password_confirmation: '' },
    errors: {},
});
const changingPassword = ref(false);

function openChangePassword(user) {
    passwordModal.userId   = user.id;
    passwordModal.userName = user.name;
    passwordModal.form     = { password: '', password_confirmation: '' };
    passwordModal.errors   = {};
    passwordModal.show     = true;
}

function closePasswordModal() {
    passwordModal.show = false;
}

async function submitPassword() {
    changingPassword.value = true;
    passwordModal.errors   = {};
    try {
        await axios.put(`/api/dashboard/users/${passwordModal.userId}/password`, passwordModal.form);
        closePasswordModal();
    } catch (error) {
        if (error.response?.status === 422) {
            passwordModal.errors = error.response.data.errors ?? {};
        } else {
            alert(error.response?.data?.message ?? 'Failed to change password.');
        }
    } finally {
        changingPassword.value = false;
    }
}

// --- Delete ---
function confirmDelete(user) {
    deleteModal.user = user;
    deleteModal.show = true;
}

function closeDeleteModal() {
    deleteModal.show = false;
}

async function executeDelete() {
    if (!deleteModal.user) return;
    deleting.value = true;
    try {
        await axios.delete(`/api/dashboard/users/${deleteModal.user.id}`);
        closeDeleteModal();
        fetchUsers();
    } catch (error) {
        closeDeleteModal();
        alert(error.response?.data?.message ?? 'Failed to delete user.');
    } finally {
        deleting.value = false;
    }
}
</script>
