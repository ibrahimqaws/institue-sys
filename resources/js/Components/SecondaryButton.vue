<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-edu-200 bg-white px-5 py-2.5 text-sm font-bold text-edu-700 transition hover:bg-edu-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-edu-600 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
