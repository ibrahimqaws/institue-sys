<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-xs font-bold text-ink-soft">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
