<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const page = usePage();
const appName = computed(() => page.props.appName || 'FlyoverCMS');
</script>

<template>
    <div class="flex min-h-screen w-full">
        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:flex-1 relative bg-gradient-to-br from-primary to-primary/80 items-center justify-center overflow-hidden">
            <!-- Animated background shapes -->
            <div class="absolute inset-0">
                <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-white/5 rounded-full blur-3xl animate-pulse delay-1000"></div>
            </div>
            
            <!-- Floating shapes -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="shape-1"></div>
                <div class="shape-2"></div>
                <div class="shape-3"></div>
            </div>
            
            <!-- Brand content -->
            <div class="relative z-10 text-center text-white px-8">
                <div class="w-20 h-20 mx-auto mb-6 bg-white/20 rounded-2xl backdrop-blur-sm flex items-center justify-center border border-white/30 shadow-lg">
                    <span class="text-3xl font-bold">F</span>
                </div>
                <h1 class="text-4xl font-bold tracking-tight mb-3">{{ appName }}</h1>
                <p class="text-lg text-white/80 max-w-sm mx-auto">
                    Manage your leads, track conversions, and grow your business
                </p>
            </div>
        </div>
        
        <!-- Right Panel - Form -->
        <div class="flex flex-1 flex-col items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8">
            <!-- Mobile logo -->
            <div class="lg:hidden mb-8 text-center">
                <div class="w-12 h-12 mx-auto mb-4 bg-primary rounded-xl flex items-center justify-center">
                    <span class="text-xl font-bold text-primary-foreground">F</span>
                </div>
                <h2 class="text-xl font-semibold">{{ appName }}</h2>
            </div>
            
            <slot />
        </div>
    </div>
</template>

<style scoped>
.shape-1, .shape-2, .shape-3 {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.shape-1 {
    width: 60px;
    height: 60px;
    top: 20%;
    left: 10%;
    animation: float 8s ease-in-out infinite;
}

.shape-2 {
    width: 40px;
    height: 40px;
    top: 60%;
    right: 10%;
    animation: float 10s ease-in-out infinite reverse;
    animation-delay: 2s;
}

.shape-3 {
    width: 80px;
    height: 80px;
    bottom: 20%;
    left: 20%;
    animation: float 12s ease-in-out infinite;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
        opacity: 0.6;
    }
    50% {
        transform: translateY(-30px) rotate(180deg);
        opacity: 0.3;
    }
}

.delay-1000 {
    animation-delay: 1000ms;
}
</style>
