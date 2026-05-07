<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    branch: Object,
    stats: Object,
    recentLeads: Array,
    branchUsers: Array,
});
</script>

<template>

    <Head :title="`Branch Dashboard - ${branch.name}`" />

    <DashboardLayout>
        <div class="fade-in">
            <!-- Branch Header -->
            <div class="mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 m-0">{{ branch.name }} Dashboard</h1>
                        <p class="text-gray-500 text-lg my-2">{{ branch.code }} - {{ branch.description }}</p>
                        <div class="flex flex-wrap items-center gap-4 text-gray-500 text-sm">
                            <span v-if="branch.address"><i class="fas fa-map-marker-alt mr-1"></i> {{ branch.address
                                }}</span>
                            <span v-if="branch.phone"><i class="fas fa-phone mr-1"></i> {{ branch.phone }}</span>
                            <span v-if="branch.email"><i class="fas fa-envelope mr-1"></i> {{ branch.email }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2"
                            :class="branch.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            <i :class="branch.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                            {{ branch.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 border-l-4 border-l-blue-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold text-blue-500 uppercase tracking-wider mb-2">Total Leads
                            </div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total_leads }}</div>
                        </div>
                        <div class="text-gray-300 text-3xl">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 border-l-4 border-l-emerald-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mb-2">New Leads
                                Today</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.new_leads }}</div>
                        </div>
                        <div class="text-gray-300 text-3xl">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 border-l-4 border-l-sky-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold text-sky-500 uppercase tracking-wider mb-2">Branch Users
                            </div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total_users }}</div>
                        </div>
                        <div class="text-gray-300 text-3xl">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 border-l-4 border-l-amber-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold text-amber-500 uppercase tracking-wider mb-2">Branch
                                Managers</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.branch_managers }}</div>
                        </div>
                        <div class="text-gray-300 text-3xl">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Leads -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-900 m-0">Recent Leads</h3>
                        <Link :href="route('branch.leads', branch.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            View All</Link>
                    </div>
                    <div class="p-6">
                        <div v-if="recentLeads.length > 0" class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="p-3 text-left font-semibold text-gray-700">Name</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Phone</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Email</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Created</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="lead in recentLeads" :key="lead.id"
                                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="p-3 text-gray-700">{{ lead.name }}</td>
                                        <td class="p-3 text-gray-500">{{ lead.phone }}</td>
                                        <td class="p-3 text-gray-500">{{ lead.email }}</td>
                                        <td class="p-3 text-gray-500">{{ new Date(lead.created_at).toLocaleDateString()
                                            }}</td>
                                        <td class="p-3">
                                            <Link :href="route('leads.show', lead.id)"
                                                class="bg-sky-500 hover:bg-sky-600 text-white px-3 py-1 rounded-md text-xs transition-colors">
                                                View</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center p-8 text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <div class="font-medium mb-2">No leads found</div>
                            <div class="text-sm">Leads will appear here once they are created for this branch.</div>
                        </div>
                    </div>
                </div>

                <!-- Branch Users -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-900 m-0">Branch Users</h3>
                        <Link :href="route('branch.users', branch.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Manage</Link>
                    </div>
                    <div class="p-6">
                        <div v-if="branchUsers.length > 0">
                            <div v-for="user in branchUsers" :key="user.id"
                                class="flex items-center mb-4 p-3 bg-gray-50 rounded-lg">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center text-white font-semibold mr-3 shrink-0">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-700 truncate">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500 truncate mb-1">{{ user.email }}</div>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="role in user.roles" :key="role.id"
                                            class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center p-8 text-gray-500">
                            <i class="fas fa-user-tie text-4xl mb-4 text-gray-300"></i>
                            <div class="font-medium mb-2">No users assigned</div>
                            <div class="text-sm">Assign users to this branch to manage leads and operations.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
