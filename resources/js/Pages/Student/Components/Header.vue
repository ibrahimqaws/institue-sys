<template>
  <header class="bg-white p-4 rounded-card border border-ink-line shadow-card">
    <h1 class="text-2xl font-black text-ink">مرحباً <span class="edu-underline">{{ name }}</span></h1>
    <p class="text-sm text-ink-muted mt-1">تابع جدولك الدراسي، سجل حضورك، علاماتك، موادك، ووضعك المالي.</p>
  </header>
</template>

<script setup>
defineProps({ name: { type: String, default: '' } });
</script>
