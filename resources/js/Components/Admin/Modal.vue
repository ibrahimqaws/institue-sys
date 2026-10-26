<template>
  <div v-if="show" class="fixed inset-0 bg-ink/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" dir="rtl" @click.self="$emit('close')">
    <div :class="['bg-white border border-ink-line w-full rounded-card p-6 shadow-pop max-h-[90vh] overflow-y-auto', maxWidth]">
      <h3 class="text-xl font-black text-ink mb-5">{{ title }}</h3>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  title: String,
  maxWidth: { type: String, default: 'max-w-lg' },
});
defineEmits(['close']);
</script>
