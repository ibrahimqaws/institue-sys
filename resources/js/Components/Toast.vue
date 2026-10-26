<template>
  <div class="fixed top-4 left-4 right-4 sm:right-auto sm:w-96 z-[60] space-y-2 pointer-events-none" dir="rtl" aria-live="polite">
    <TransitionGroup name="toast">
      <div
        v-for="t in toasts"
        :key="t.id"
        role="status"
        :class="[
          'pointer-events-auto flex items-start justify-between gap-3 p-4 rounded-xl border border-s-4 text-sm font-bold bg-white shadow-pop',
          t.type === 'success'
            ? 'border-edu-200 border-s-edu-600 text-edu-800'
            : 'border-red-100 border-s-red-500 text-red-700',
        ]"
      >
        <span class="leading-relaxed">{{ t.type === 'success' ? '✅' : '⚠️' }} {{ t.text }}</span>
        <button type="button" class="text-xs text-ink-muted hover:text-ink shrink-0" aria-label="إغلاق" @click="dismiss(t.id)">✕</button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);
let seq = 0;

const push = (type, text) => {
  if (!text) return;
  const id = ++seq;
  toasts.value.push({ id, type, text });
  setTimeout(() => dismiss(id), type === 'error' ? 8000 : 5000);
};

const dismiss = (id) => {
  toasts.value = toasts.value.filter((t) => t.id !== id);
};

watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) return;
    push('success', flash.success);
    push('error', flash.error);
  },
  { immediate: true },
);
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}
</style>
