<template>
  <div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="card in cards" :key="card.label" class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs font-semibold text-ink-muted">{{ card.label }}</p>
        <h3 :class="['text-2xl font-black mt-2', card.color]">{{ card.value }}</h3>
        <p v-if="card.hint" class="text-[11px] text-ink-muted mt-1">{{ card.hint }}</p>
      </div>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
      <h2 class="text-md font-bold text-ink mb-4">📅 محاضرات اليوم ({{ dayNames[today] }})</h2>
      <div v-if="todayLessons.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div v-for="b in todayLessons" :key="b.id" class="p-4 bg-edu-50 border border-ink-line rounded-xl">
          <div class="text-sm font-bold text-ink">{{ b.course_title || b.name }}</div>
          <div class="text-xs text-ink-muted mt-1">{{ b.name }}<span v-if="b.room"> • 🏛️ {{ b.room }}</span></div>
          <div class="text-xs text-edu-700 font-mono mt-1" dir="ltr">{{ b.start_time || '--:--' }}<span v-if="b.end_time"> - {{ b.end_time }}</span></div>
        </div>
      </div>
      <p v-else class="text-xs text-ink-muted">لا توجد محاضرات لك اليوم.</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { money } from '@/Components/Admin/ui';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  schedule: { type: Array, default: () => [] },
  dayNames: { type: Object, default: () => ({}) },
});

const jsDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
const today = jsDays[new Date().getDay()];

const todayLessons = computed(() =>
  props.schedule
    .filter((b) => (b.days || []).includes(today))
    .sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''))
);

const cards = computed(() => [
  {
    label: 'نسبة الحضور',
    value: props.stats.attendance_rate === null || props.stats.attendance_rate === undefined ? '—' : `${props.stats.attendance_rate}%`,
    color: 'text-edu-700',
    hint: props.stats.attendance_rate === null || props.stats.attendance_rate === undefined ? 'لا توجد سجلات بعد' : '',
  },
  { label: 'كورسات نشطة', value: props.stats.active_courses ?? 0, color: 'text-edu-700' },
  { label: 'طلبات قيد المراجعة', value: props.stats.pending_requests ?? 0, color: 'text-amber-700' },
  { label: 'المتبقي من الأقساط', value: money(props.stats.remaining), color: 'text-red-600' },
]);
</script>
