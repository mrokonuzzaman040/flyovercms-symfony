<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/ui/pagination/Pagination.vue';

defineProps({
    branch: Object,
    leads: Object,
});
</script>

<template>

    <Head :title="`Branch Leads - ${branch.name}`" />

    <DashboardLayout>
        <div class="fade-in">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">{{ branch.name }} - Leads</h1>
                    <p class="text-gray-500 text-lg my-2">Manage leads for this branch</p>
                </div>
                <Link :href="route('branch.dashboard', branch.id)"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </Link>
            </div>

            <!-- Leads Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900 m-0">Branch Leads ({{ leads.total }})</h3>
                </div>
                <div class="p-6">
                    <div v-if="leads.data.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="p-3 text-left font-semibold text-gray-700">Name</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Phone</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Email</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Education</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Created</th>
                                        <th class="p-3 text-left font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="lead in leads.data" :key="lead.id"
                                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="p-3 text-gray-700 font-medium">{{ lead.full_name }}</td>
                                        <td class="p-3 text-gray-500">{{ lead.phone }}</td>
                                        <td class="p-3 text-gray-500">{{ lead.email }}</td>
                                        <td class="p-3 text-gray-500">
                                            <span v-if="lead.education_history && lead.education_history.length > 0">
                                                {{ lead.education_history[0].degree }}
                                            </span>
                                            <span v-else class="text-gray-400">Not specified</span>
                                        </td>
                                        <td class="p-3 text-gray-500">{{ new Date(lead.created_at).toLocaleDateString()
                                            }}</td>
                                        <td class="p-3">
                                            <div class="flex gap-2">
                                                <Link :href="route('leads.show', lead.id)"
                                                    class="bg-sky-500 hover:bg-sky-600 text-white px-3 py-1 rounded-md text-xs flex items-center gap-1 transition-colors">
                                                    <i class="fas fa-eye"></i> View
                                                </Link>
                                                <Link :href="route('leads.edit', lead.id)"
                                                    class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded-md text-xs flex items-center gap-1 transition-colors">
                                                    <i class="fas fa-edit"></i> Edit
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex justify-center mt-8">
                            <Pagination :links="leads.links" />
                        </div>
                    </div>
                    <div v-else class="text-center p-12 text-gray-500">
                        <i class="fas fa-users text-5xl mb-4 text-gray-300"></i>
                        <h5 class="text-lg font-medium mb-2 text-gray-700">No leads found for this branch</h5>
                        <p class="text-sm">Leads will appear here once they are created for this branch.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
