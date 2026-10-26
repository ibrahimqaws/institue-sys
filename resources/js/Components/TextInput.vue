<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-lg border-ink-line bg-white px-3.5 py-2.5 text-sm text-ink placeholder-ink-muted/70 shadow-sm focus:border-edu-600 focus:ring-2 focus:ring-edu-600/20"
        v-model="model"
        ref="input"
    />
</template>
