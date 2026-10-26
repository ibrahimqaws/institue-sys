<script setup>
import { Link } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

defineProps({ title: { type: String, default: 'نظام إدارة المعهد' } });
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-edu-100 px-4 py-10 font-sans text-ink" dir="rtl">
        <Toast />

        
        <svg class="pointer-events-none absolute start-6 top-8 hidden h-16 w-16 text-sun sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true">
            <path d="M8 40 L22 14" /><path d="M22 48 L38 18" /><path d="M38 54 L54 24" />
        </svg>
        <svg class="pointer-events-none absolute bottom-8 end-6 hidden h-14 w-14 text-sun sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true">
            <path d="M8 40 L22 14" /><path d="M22 48 L38 18" /><path d="M38 54 L54 24" />
        </svg>

        <Link href="/" class="mb-6 flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-edu-700 shadow-card">
                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.5C10.5 5 8 4.5 4 4.5v13c4 0 6.5.5 8 2 1.5-1.5 4-2 8-2v-13c-4 0-6.5.5-8 2z"/><path d="M12 6.5v13"/></svg>
            </span>
            <span class="text-2xl font-black text-ink">{{ title }}</span>
        </Link>

        <div class="relative w-full max-w-md rounded-card border border-ink-line bg-white p-6 shadow-pop sm:p-8">
            <slot />
        </div>
    </div>
</template>
