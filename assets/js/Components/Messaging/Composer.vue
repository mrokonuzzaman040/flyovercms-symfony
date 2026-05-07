<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Send, Paperclip, Mic } from 'lucide-vue-next';

const props = defineProps({
    conversationId: { type: Number, required: true },
});

const body = ref('');
const fileInput = ref(null);
const selectedFiles = ref([]);
const isRecording = ref(false);
const mediaRecorder = ref(null);
const voiceBlob = ref(null);

function onFileChange(e) {
    const files = e.target.files ? Array.from(e.target.files) : [];
    selectedFiles.value = files;
}

function removeFile(index) {
    selectedFiles.value = selectedFiles.value.filter((_, i) => i !== index);
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    const hasBody = body.value?.trim();
    const hasFiles = selectedFiles.value.length > 0;
    const hasVoice = !!voiceBlob.value;
    if (!hasBody && !hasFiles && !hasVoice) return;

    const formData = new FormData();
    if (hasBody) formData.append('body', body.value.trim());
    selectedFiles.value.forEach((file) => formData.append('attachments[]', file));
    if (voiceBlob.value) {
        const ext = voiceBlob.value.type?.includes('mp4') ? 'm4a' : 'webm';
        formData.append('attachments[]', voiceBlob.value, `voice.${ext}`);
    }

    router.post(route('messaging.messages.store', props.conversationId), formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            body.value = '';
            selectedFiles.value = [];
            voiceBlob.value = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function getSupportedAudioMimeType() {
    const types = ['audio/webm', 'audio/webm;codecs=opus', 'audio/mp4', 'audio/ogg'];
    for (const type of types) {
        if (MediaRecorder.isTypeSupported?.(type)) return type;
    }
    return 'audio/webm';
}

function toggleRecord() {
    if (!navigator.mediaDevices?.getUserMedia) return;
    if (isRecording.value) {
        stopRecord();
        return;
    }
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then((stream) => {
            const mimeType = getSupportedAudioMimeType();
            const opts = MediaRecorder.isTypeSupported(mimeType) ? { mimeType } : {};
            const mr = new MediaRecorder(stream, opts);
            const chunks = [];
            mr.ondataavailable = (e) => e.data?.size > 0 && chunks.push(e.data);
            mr.onstop = () => {
                const type = mr.mimeType || 'audio/webm';
                voiceBlob.value = chunks.length ? new Blob(chunks, { type }) : null;
                stream.getTracks().forEach((t) => t.stop());
                mediaRecorder.value = null;
                isRecording.value = false;
            };
            mr.onerror = () => {
                stream.getTracks().forEach((t) => t.stop());
                mediaRecorder.value = null;
                isRecording.value = false;
            };
            mr.start(100);
            mediaRecorder.value = mr;
            isRecording.value = true;
        })
        .catch(() => {});
}

function stopRecord() {
    if (mediaRecorder.value?.state === 'recording') {
        mediaRecorder.value.requestData();
        mediaRecorder.value.stop();
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-2">
        <div class="flex gap-2 items-end">
            <textarea
                v-model="body"
                rows="2"
                class="min-h-[40px] flex-1 rounded-md border bg-background px-3 py-2 text-sm resize-none"
                placeholder="Type a message... (Enter to send, Shift+Enter for new line)"
                @keydown.enter.exact.prevent="submit"
            />
            <div class="flex shrink-0 gap-1">
                <input
                    ref="fileInput"
                    type="file"
                    multiple
                    class="hidden"
                    accept="image/*,.pdf,.doc,.docx,text/plain,audio/*"
                    @change="onFileChange"
                />
                <Button type="button" variant="ghost" size="icon" class="h-9 w-9" @click="fileInput?.click()" title="Attach file">
                    <Paperclip class="h-4 w-4" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9"
                    :class="{ 'text-red-500': isRecording }"
                    :title="isRecording ? 'Stop recording' : 'Record voice'"
                    @click="isRecording ? stopRecord() : toggleRecord()"
                >
                    <Mic class="h-4 w-4" />
                </Button>
                <Button type="submit" size="icon" class="h-9 w-9" title="Send">
                    <Send class="h-4 w-4" />
                </Button>
            </div>
        </div>
        <div v-if="selectedFiles.length > 0 || voiceBlob" class="flex flex-wrap gap-2 text-xs">
            <span
                v-for="(f, i) in selectedFiles"
                :key="`file-${i}`"
                class="inline-flex items-center gap-1 rounded bg-muted px-2 py-1"
            >
                {{ f.name }}
                <button type="button" class="text-muted-foreground hover:text-foreground" @click="removeFile(i)">×</button>
            </span>
            <span v-if="voiceBlob" class="inline-flex items-center gap-1 rounded bg-muted px-2 py-1">
                Voice recording
                <button type="button" class="text-muted-foreground hover:text-foreground" @click="voiceBlob = null">×</button>
            </span>
        </div>
    </form>
</template>
