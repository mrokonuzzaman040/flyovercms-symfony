<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Welcome to FlyoverCMS — Multi-tenant edition</p>
        <div class="mt-4 rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Tenant: <strong>{{ tenant }}</strong></p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    tenant: String,
})
</script>
