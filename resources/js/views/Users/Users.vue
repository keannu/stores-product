<template>
    <DashboardLayout>

        <!-- Page header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Users</h1>
                <p class="mt-1 text-sm text-neutral-500">Manage all users in the system.</p>
            </div>
            <button
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </button>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Search by name or email…"
                class="w-full sm:w-72 px-4 py-2 text-sm border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Table -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 bg-neutral-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Store</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="5" class="px-4 py-10 text-center text-neutral-400">Loading…</td>
                    </tr>
                    <tr v-else-if="!users.length">
                        <td colspan="5" class="px-4 py-10 text-center text-neutral-400">No users found.</td>
                    </tr>
                    <template v-else>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50 transition"
                        >
                            <td class="px-4 py-3 font-medium text-neutral-800">{{ user.name }}</td>
                            <td class="px-4 py-3 text-neutral-600">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"
                                    :class="user.role === 'admin' ? 'bg-blue-50 text-blue-700' : 'bg-neutral-100 text-neutral-600'"
                                >{{ user.role }}</span>
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ storeName(user.store_id) }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button @click="openEdit(user)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                <button @click="confirmDelete(user)" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="flex items-center justify-between">
            <p class="text-sm text-neutral-500">{{ meta.total }} user{{ meta.total !== 1 ? 's' : '' }} total</p>
            <div class="flex items-center gap-2">
                <button
                    @click="goToPage(meta.current_page - 1)"
                    :disabled="meta.current_page === 1"
                    class="px-3 py-1.5 text-sm border border-neutral-200 rounded-lg text-neutral-600 hover:bg-neutral-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >Prev</button>
                <span class="text-sm text-neutral-600">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button
                    @click="goToPage(meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    class="px-3 py-1.5 text-sm border border-neutral-200 rounded-lg text-neutral-600 hover:bg-neutral-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >Next</button>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div
                v-if="modal.show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">
                        {{ modal.mode === 'create' ? 'Add User' : 'Edit User' }}
                    </h2>
                    <form @submit.prevent="submitModal" class="space-y-4">

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Name</label>
                            <input
                                v-model="modal.form.name"
                                type="text"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.name ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.name" class="mt-1 text-xs text-red-500">{{ modal.errors.name[0] }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
                            <input
                                v-model="modal.form.email"
                                type="email"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.email ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.email" class="mt-1 text-xs text-red-500">{{ modal.errors.email[0] }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Password</label>
                            <input
                                v-model="modal.form.password"
                                type="password"
                                :placeholder="modal.mode === 'edit' ? 'Leave blank to keep current' : ''"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.password ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.password" class="mt-1 text-xs text-red-500">{{ modal.errors.password[0] }}</p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Role</label>
                            <select
                                v-model="modal.form.role"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.role ? 'border-red-400' : 'border-neutral-300'"
                            >
                                <option value="">Select role…</option>
                                <option value="admin">admin</option>
                                <option value="customer">customer</option>
                            </select>
                            <p v-if="modal.errors.role" class="mt-1 text-xs text-red-500">{{ modal.errors.role[0] }}</p>
                        </div>

                        <!-- Store -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Store</label>
                            <select
                                v-model="modal.form.store_id"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.store_id ? 'border-red-400' : 'border-neutral-300'"
                            >
                                <option :value="null">None</option>
                                <option v-for="store in stores" :key="store.id" :value="store.id">
                                    {{ store.id }} — {{ store.store_name }}
                                </option>
                            </select>
                            <p v-if="modal.errors.store_id" class="mt-1 text-xs text-red-500">{{ modal.errors.store_id[0] }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-neutral-600 border border-neutral-300 rounded-xl hover:bg-neutral-50 transition"
                            >Cancel</button>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 transition"
                            >{{ submitting ? 'Saving…' : 'Save' }}</button>
                        </div>

                    </form>
                </div>
            </div>
        </Teleport>

    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import DashboardLayout from '../Common/DashboardLayout.vue';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// --- State ---
const users       = ref([]);
const stores      = ref([]);
const loading     = ref(false);
const submitting  = ref(false);
const search      = ref('');
const currentPage = ref(1);
const meta        = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const modal = reactive({
    show:   false,
    mode:   'create',
    editId: null,
    form:   emptyForm(),
    errors: {},
});

function emptyForm() {
    return { name: '', email: '', password: '', role: '', store_id: null };
}

// --- Fetch ---
async function fetchUsers() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/dashboard/users', {
            params: { search: search.value, page: currentPage.value },
        });
        users.value = data.data;
        meta.value  = data.meta;
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load users.' });
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

// Debounced search — resets to page 1 on new term
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchUsers();
    }, 400);
});

// --- Helpers ---
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
    modal.form   = { name: user.name, email: user.email, password: '', role: user.role, store_id: user.store_id };
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
            Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message ?? 'Something went wrong.' });
        }
    } finally {
        submitting.value = false;
    }
}

// --- Delete ---
async function confirmDelete(user) {
    const result = await Swal.fire({
        title:              'Delete user?',
        text:               `"${user.name}" will be removed.`,
        icon:               'warning',
        showCancelButton:   true,
        confirmButtonText:  'Delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText:   'Cancel',
    });

    if (!result.isConfirmed) return;

    try {
        await axios.delete(`/api/dashboard/users/${user.id}`);
        fetchUsers();
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message ?? 'Failed to delete user.' });
    }
}
</script>
