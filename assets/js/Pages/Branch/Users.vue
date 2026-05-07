<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/ui/pagination/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    branch: Object,
    users: Object, // Paginator
    roles: Array,
    potentialUsers: Array, // Users that can be added - Need to pass this from controller
});

const showAssignModal = ref(false);

const form = useForm({
    user_id: '',
    role_id: '',
});

const submitAssign = () => {
    form.post(route('branch.assign-user', props.branch.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>

    <Head :title="`Branch Users - ${branch.name}`" />

    <DashboardLayout>
        <div class="fade-in">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">{{ branch.name }} - Users</h1>
                    <p class="text-gray-500 text-lg my-2">Manage users assigned to this branch</p>
                </div>
                <div class="flex gap-4">
                    <button @click="showAssignModal = true"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2 transition-colors">
                        <i class="fas fa-user-plus"></i> Assign User
                    </button>
                    <Link :href="route('branch.dashboard', branch.id)"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2 transition-colors">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </Link>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900 m-0">Branch Users ({{ users.total }})</h3>
                </div>
                <div class="p-6">
                    <div v-if="users.data.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="p-3 text-left font-semibold text-gray-700">Name</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Email</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Roles</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Assigned</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data" :key="user.id"
                                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="p-3 text-gray-700 font-medium">{{ user.name }}</td>
                                        <td class="p-3 text-gray-500">{{ user.email }}</td>
                                        <td class="p-3">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="role in user.roles" :key="role.id"
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                    :class="role.slug === 'branch-manager' ? 'bg-amber-100 text-amber-900' : 'bg-gray-200 text-gray-700'">
                                                    {{ role.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-gray-500">{{ new Date(user.created_at).toLocaleDateString()
                                            }}</td>
                                        <td class="p-3">
                                            <Link :href="route('branch.remove-user', [branch.id, user.id])"
                                                method="delete" as="button"
                                                :onBefore="() => confirm('Are you sure you want to remove this user from the branch?')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-xs flex items-center gap-1 transition-colors">
                                                <i class="fas fa-user-minus"></i> Remove
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex justify-center mt-8">
                            <Pagination :links="users.links" />
                        </div>
                    </div>
                    <div v-else class="text-center p-12 text-gray-500">
                        <i class="fas fa-user-tie text-5xl mb-4 text-gray-300"></i>
                        <h5 class="text-lg font-medium mb-2 text-gray-700">No users assigned to this branch</h5>
                        <p class="text-sm">Assign users to this branch to manage leads and operations.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign User Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    @click="showAssignModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitAssign">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Assign User to Branch
                                </h3>
                                <button type="button" @click="showAssignModal = false"
                                    class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mb-4">
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Select
                                    User</label>
                                <select id="user_id" v-model="form.user_id" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="" disabled>Choose a user...</option>
                                    <option v-for="user in potentialUsers" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                                <div v-if="form.errors.user_id" class="text-red-600 text-sm mt-1">{{ form.errors.user_id
                                    }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Assign
                                    Role</label>
                                <select id="role_id" v-model="form.role_id" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="" disabled>Choose a role...</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.role_id" class="text-red-600 text-sm mt-1">{{ form.errors.role_id
                                    }}</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                {{ form.processing ? 'Assigning...' : 'Assign User' }}
                            </button>
                            <button type="button" @click="showAssignModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
