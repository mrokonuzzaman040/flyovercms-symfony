<script setup>
import { ref, computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Eye, Download, Forward } from 'lucide-vue-next';

const props = defineProps({
    message: { type: Object, required: true },
    isOwn: { type: Boolean, default: false },
});

const emit = defineEmits(['forward']);

const senderName = computed(() => props.message?.user?.name ?? props.message?.sender?.name ?? 'Unknown');
const attachments = computed(() => props.message?.attachments ?? []);
const previewAttachment = ref(null);
const showPreview = ref(false);

function attachmentUrl(att, download = false) {
    const url = `/messaging/attachments/${att.id}`;
    return download ? `${url}?download=1` : url;
}

function formatTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function openPreview(att) {
    previewAttachment.value = att;
    showPreview.value = true;
}

function closePreview() {
    showPreview.value = false;
    previewAttachment.value = null;
}

function isImage(att) {
    return att?.mime_type?.startsWith('image/');
}

function isPdf(att) {
    return att?.mime_type === 'application/pdf';
}

function isAudio(att) {
    return att?.type === 'voice' || att?.mime_type?.startsWith('audio/');
}

function canPreview(att) {
    return isImage(att) || isPdf(att) || isAudio(att);
}

function formatSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function onForward() {
    emit('forward', props.message);
}
</script>

<template>
    <div class="flex gap-2" :class="isOwn ? 'flex-row-reverse' : ''">
        <Avatar class="h-8 w-8 shrink-0">
            <AvatarImage
                v-if="(message?.user?.avatar ?? message?.sender?.avatar)"
                :src="message?.user?.avatar ?? message?.sender?.avatar"
            />
            <AvatarFallback class="text-xs">{{ senderName.slice(0, 2).toUpperCase() }}</AvatarFallback>
        </Avatar>
        <div class="flex flex-col max-w-[75%] group" :class="isOwn ? 'items-end' : 'items-start'">
            <div
                class="rounded-lg px-3 py-2 text-sm"
                :class="isOwn ? 'bg-primary text-primary-foreground' : 'bg-muted'"
            >
                <p v-if="!isOwn" class="text-xs font-medium text-muted-foreground mb-0.5">{{ senderName }}</p>
                <p v-if="message.body" class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                <div v-if="attachments.length" class="mt-2 space-y-2">
                    <template v-for="att in attachments" :key="att.id">
                        <div
                            v-if="att.type !== 'voice'"
                            class="flex items-center gap-2 flex-wrap"
                            :class="isOwn ? 'flex-row-reverse' : ''"
                        >
                            <div class="flex items-center gap-1 shrink-0">
                                <Button
                                    v-if="canPreview(att)"
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7"
                                    :class="isOwn ? 'text-primary-foreground/80 hover:bg-primary-foreground/20' : ''"
                                    title="Preview"
                                    @click="openPreview(att)"
                                >
                                    <Eye class="h-3.5 w-3.5" />
                                </Button>
                                <a
                                    :href="attachmentUrl(att, true)"
                                    download
                                    :class="isOwn ? 'text-primary-foreground/90 hover:underline' : 'text-muted-foreground hover:underline'"
                                    class="inline-flex items-center gap-1 text-xs"
                                    title="Download"
                                >
                                    <Download class="h-3.5 w-3.5 shrink-0" />
                                    {{ att.filename }}
                                </a>
                            </div>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <audio
                                controls
                                class="max-w-full h-8"
                                :src="attachmentUrl(att)"
                            />
                            <a
                                :href="attachmentUrl(att, true)"
                                download
                                class="shrink-0 inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-black/10"
                                :class="isOwn ? 'text-primary-foreground/80' : 'text-muted-foreground'"
                                title="Download"
                            >
                                <Download class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </template>
                </div>
                <div class="flex items-center gap-1 mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="h-6 px-1.5 text-xs"
                        :class="isOwn ? 'text-primary-foreground/80 hover:bg-primary-foreground/20' : ''"
                        title="Forward"
                        @click="onForward"
                    >
                        <Forward class="h-3 w-3 mr-0.5" />
                        Forward
                    </Button>
                </div>
            </div>
            <span class="text-[10px] text-muted-foreground mt-0.5">{{ formatTime(message.created_at) }}</span>
        </div>
    </div>

    <!-- Preview modal -->
    <Dialog :open="showPreview" @update:open="showPreview = $event">
        <DialogContent
            class="max-w-3xl max-h-[85vh] overflow-hidden flex flex-col"
            @pointer-down-outside="closePreview"
        >
            <DialogHeader>
                <DialogTitle class="text-sm font-medium truncate">
                    {{ previewAttachment?.filename }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    Preview of {{ previewAttachment?.filename }}
                </DialogDescription>
            </DialogHeader>
            <div class="flex-1 min-h-0 overflow-auto flex items-center justify-center p-4">
                <img
                    v-if="previewAttachment && isImage(previewAttachment)"
                    :src="attachmentUrl(previewAttachment)"
                    :alt="previewAttachment.filename"
                    class="max-w-full max-h-[70vh] object-contain rounded"
                />
                <iframe
                    v-else-if="previewAttachment && isPdf(previewAttachment)"
                    :src="attachmentUrl(previewAttachment)"
                    class="w-full h-[70vh] rounded border-0"
                    title="PDF preview"
                />
                <audio
                    v-else-if="previewAttachment && isAudio(previewAttachment)"
                    controls
                    class="max-w-full"
                    :src="attachmentUrl(previewAttachment)"
                />
                <div v-else-if="previewAttachment" class="text-center text-muted-foreground">
                    <p class="text-sm">{{ previewAttachment.filename }}</p>
                    <p class="text-xs mt-1">{{ formatSize(previewAttachment.size) }}</p>
                    <Button as-child class="mt-3">
                        <a :href="attachmentUrl(previewAttachment, true)" download>Download</a>
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
