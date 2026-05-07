<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const visible = ref(false);
let showTimeout = null;

const show = () => {
    if (showTimeout) return;
    showTimeout = setTimeout(() => {
        visible.value = true;
    }, 300);
};

const hide = () => {
    if (showTimeout) {
        clearTimeout(showTimeout);
        showTimeout = null;
    }
    visible.value = false;
};

const unsubscribes = [];

onMounted(() => {
    unsubscribes.push(router.on('start', show));
    unsubscribes.push(router.on('finish', hide));
    unsubscribes.push(router.on('cancel', hide));
});

onUnmounted(() => {
    unsubscribes.forEach((unsub) => typeof unsub === 'function' && unsub());
    if (showTimeout) clearTimeout(showTimeout);
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="visible"
            class="fixed inset-0 z-[10000] flex items-center justify-center bg-background/80 backdrop-blur-sm"
            aria-live="polite"
            aria-busy="true"
            role="status"
        >
            <div class="flex flex-col items-center gap-6 rounded-xl border border-border/50 bg-card/95 px-10 py-8 shadow-2xl shadow-primary/5 dark:shadow-primary/10">
                <div class="page-loader-nexus" aria-hidden="true">
                    <div class="nexus-ring nexus-ring-outer" />
                    <div class="nexus-ring nexus-ring-inner" />
                    <div class="nexus-core" />
                </div>
                <p class="text-sm font-medium tracking-wider text-muted-foreground uppercase">Loading</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.page-loader-nexus {
    position: relative;
    width: 64px;
    height: 64px;
}

.nexus-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid transparent;
    border-top-color: var(--primary);
}

.nexus-ring-outer {
    animation: nexus-spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-right-color: var(--primary);
    opacity: 0.7;
}

.nexus-ring-inner {
    inset: 12px;
    animation: nexus-spin-reverse 0.9s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-bottom-color: var(--primary);
    border-right-color: var(--primary);
    opacity: 0.5;
}

.nexus-core {
    position: absolute;
    inset: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--primary);
    box-shadow:
        0 0 12px 2px var(--primary),
        0 0 24px 4px color-mix(in oklch, var(--primary) 30%, transparent);
    animation: nexus-pulse 1.5s ease-in-out infinite;
}

@keyframes nexus-spin {
    to { transform: rotate(360deg); }
}

@keyframes nexus-spin-reverse {
    to { transform: rotate(-360deg); }
}

@keyframes nexus-pulse {
    0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.6; transform: translate(-50%, -50%) scale(1.2); }
}
</style>
