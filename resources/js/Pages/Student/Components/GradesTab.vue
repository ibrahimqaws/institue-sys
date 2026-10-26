<template>
  <div class="space-y-6">
    
    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-4">
      <div class="border-b border-ink-line pb-3">
        <h2 class="text-md font-bold text-ink">📊 العلامات ونتائج الاختبارات</h2>
        <p class="text-xs text-ink-muted mt-0.5">كل اختبار أو تقييم رصده مدرسوك مع نسبته من الدرجة العظمى.</p>
      </div>

      <div v-if="groups.length === 0" class="p-6 text-center text-xs text-ink-muted">لا توجد علامات مرصودة حتى الآن.</div>

      <div v-for="g in groups" :key="g.key" class="rounded-xl border border-ink-line overflow-x-auto">
        <div class="flex flex-wrap justify-between items-center gap-2 bg-edu-100 px-4 py-3 border-b border-ink-line">
          <span class="text-sm font-bold text-ink">{{ g.course_title || 'كورس' }} <span class="text-xs text-ink-muted">({{ g.batch_name }})</span></span>
          <span v-if="g.average !== null" class="text-xs text-ink-soft">المعدل: <b :class="pctClass(g.average)">{{ g.average }}%</b></span>
        </div>
        <table class="w-full text-start text-xs">
          <thead class="bg-edu-100 text-edu-800 font-bold">
            <tr><th class="px-4 py-3 font-bold whitespace-nowrap">الاختبار / التقييم</th><th class="px-4 py-3 font-bold whitespace-nowrap">الدرجة</th><th class="px-4 py-3 font-bold whitespace-nowrap">النسبة</th><th class="px-4 py-3 font-bold whitespace-nowrap">التاريخ</th></tr>
          </thead>
          <tbody class="divide-y divide-ink-line">
            <tr v-for="gr in g.items" :key="gr.id" class="hover:bg-edu-50 transition-colors">
              <td class="px-4 py-3 text-ink font-bold">{{ gr.title }}</td>
              <td class="px-4 py-3 text-ink-soft">{{ num(gr.score) }} / {{ num(gr.max_score) }}</td>
              <td class="px-4 py-3"><span :class="['px-2.5 py-1 border text-[11px] rounded-lg font-black inline-block', badgeClass(gr.percentage)]">{{ gr.percentage === null ? '—' : gr.percentage + '%' }}</span></td>
              <td class="px-4 py-3 text-ink-muted font-mono" dir="ltr">{{ gr.date }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    
    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-4">
      <div class="border-b border-ink-line pb-3">
        <h2 class="text-md font-bold text-ink">🗒️ ملاحظات المدرسين</h2>
      </div>

      <div v-if="notes.length === 0" class="p-6 text-center text-xs text-ink-muted">لا توجد ملاحظات حتى الآن.</div>

      <div v-for="n in notes" :key="n.id" class="p-4 bg-edu-50 border border-ink-line rounded-xl">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span :class="['px-2 py-0.5 border text-[10px] rounded font-bold', n.type === 'academic' ? 'bg-sky-50 text-sky-700 border-sky-100' : 'bg-sun-soft text-amber-700 border-amber-200']">{{ n.type_label }}</span>
          <span class="text-xs text-ink-soft">{{ n.course_title || '' }} <span v-if="n.batch_name" class="text-ink-muted">({{ n.batch_name }})</span></span>
          <span class="text-[11px] text-ink-muted ms-auto font-mono" dir="ltr">{{ n.date }}</span>
        </div>
        <p class="text-sm text-ink leading-relaxed whitespace-pre-line">{{ n.content }}</p>
        <p class="text-[11px] text-ink-muted mt-2">من: {{ n.teacher || 'المدرس' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  grades: { type: Array, default: () => [] },
  notes: { type: Array, default: () => [] },
});

const num = (v) => (v === null || v === undefined ? '—' : Number(v));

const groups = computed(() => {
  const map = new Map();
  props.grades.forEach((g) => {
    if (!map.has(g.batch_id)) {
      map.set(g.batch_id, { key: g.batch_id, course_title: g.course_title, batch_name: g.batch_name, items: [] });
    }
    map.get(g.batch_id).items.push(g);
  });

  return [...map.values()].map((g) => {
    const pcts = g.items.map((i) => i.percentage).filter((p) => p !== null && p !== undefined);
    return { ...g, average: pcts.length ? Math.round((pcts.reduce((a, b) => a + b, 0) / pcts.length) * 10) / 10 : null };
  });
});

const pctClass = (p) => (p >= 60 ? 'text-edu-700' : 'text-red-600');
const badgeClass = (p) =>
  p === null || p === undefined
    ? 'text-ink-muted bg-slate-100 border-ink-line'
    : p >= 60
      ? 'text-edu-700 bg-edu-100 border-edu-200'
      : 'text-red-700 bg-red-50 border-red-100';
</script>
