<template>
  <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
    <div class="mb-5">
      <h2 class="text-md font-black text-ink">📅 جدولي الدراسي <span class="edu-underline">الأسبوعي</span></h2>
      <p class="text-xs text-ink-muted mt-1">محاضرات مجموعاتك المسجّل فيها حسب أيام الأسبوع.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
      <div
        v-for="day in order"
        :key="day"
        :class="['rounded-xl border p-3 min-h-[110px]', day === today ? 'border-edu-600 bg-edu-100' : 'border-ink-line bg-edu-50']"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-black" :class="day === today ? 'text-edu-700' : 'text-ink-soft'">{{ dayNames[day] }}</span>
          <span v-if="day === today" class="text-[10px] font-bold bg-edu-600 text-white rounded-md px-1.5 py-0.5">اليوم</span>
        </div>
        <div v-for="b in byDay[day]" :key="b.id" class="mb-2 p-2 rounded-lg bg-white border border-ink-line shadow-card">
          <div class="text-xs font-bold text-ink truncate">{{ b.course_title || b.name }}</div>
          <div class="text-[11px] text-ink-muted font-mono" dir="ltr">{{ b.start_time || '--:--' }}<span v-if="b.end_time"> - {{ b.end_time }}</span></div>
          <div v-if="b.room" class="text-[11px] text-ink-muted">🏛️ {{ b.room }}</div>
        </div>
        <div v-if="!byDay[day].length" class="text-[11px] text-ink-muted">—</div>
      </div>
    </div>

    <p v-if="unscheduled.length" class="mt-4 text-xs font-bold text-amber-700 bg-sun-soft rounded-md px-3 py-2">
      مجموعات بلا أيام محددة (سيُعلن موعدها لاحقاً): {{ unscheduled.map((b) => b.course_title || b.name).join('، ') }}
    </p>
    <p v-if="!schedule.length" class="mt-4 text-xs text-ink-muted">لا توجد مجموعات نشطة في جدولك حالياً.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  schedule: { type: Array, default: () => [] },
  dayNames: { type: Object, default: () => ({}) },
});

const order = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];
const jsDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
const today = jsDays[new Date().getDay()];

const byDay = computed(() => {
  const map = Object.fromEntries(order.map((d) => [d, []]));
  props.schedule.forEach((b) => (b.days || []).forEach((d) => map[d]?.push(b)));
  order.forEach((d) => map[d].sort((a, b) => (a.start_time || '').localeCompare(b.start_time || '')));
  return map;
});

const unscheduled = computed(() => props.schedule.filter((b) => !(b.days || []).length));
</script>
