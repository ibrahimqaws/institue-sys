<template>
  <div class="space-y-6">
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">نسبة الحضور</p>
        <h3 class="text-2xl font-black text-edu-700 mt-2">{{ summary.rate === null || summary.rate === undefined ? '—' : summary.rate + '%' }}</h3>
        <div class="mt-3 w-full bg-edu-100 rounded-full h-1.5 overflow-hidden">
          <div class="bg-edu-600 h-1.5 rounded-full" :style="{ width: (summary.rate || 0) + '%' }"></div>
        </div>
      </div>
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">حاضر</p>
        <h3 class="text-2xl font-black text-edu-700 mt-2">{{ summary.present || 0 }}</h3>
      </div>
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">متأخر</p>
        <h3 class="text-2xl font-black text-amber-700 mt-2">{{ summary.late || 0 }}</h3>
      </div>
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">غائب</p>
        <h3 class="text-2xl font-black text-red-600 mt-2">{{ summary.absent || 0 }}</h3>
      </div>
    </div>

    
    <div v-if="byBatch.length > 1" class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
      <h2 class="text-md font-bold text-ink mb-3">حسب المجموعة</h2>
      <div class="space-y-2">
        <div v-for="b in byBatch" :key="b.batch_id" class="flex flex-wrap justify-between items-center gap-2 text-xs bg-edu-50 p-3 rounded-lg border border-ink-line">
          <span class="text-ink font-bold">{{ b.course_title || 'كورس' }} <span class="text-ink-muted">({{ b.batch_name }})</span></span>
          <span class="text-ink-muted">
            حضور {{ b.present }} • تأخر {{ b.late }} • غياب {{ b.absent }} •
            <b class="text-edu-700">{{ b.rate }}%</b>
          </span>
        </div>
      </div>
    </div>

    
    <div class="bg-white border border-ink-line rounded-card shadow-card overflow-x-auto">
      <table class="w-full text-start text-xs">
        <thead class="bg-edu-100 text-edu-800 font-bold">
          <tr>
            <th class="px-4 py-3 font-bold whitespace-nowrap">الكورس / المجموعة</th>
            <th class="px-4 py-3 font-bold whitespace-nowrap">التاريخ</th>
            <th class="px-4 py-3 font-bold whitespace-nowrap">الحالة</th>
            <th class="px-4 py-3 font-bold whitespace-nowrap">ملاحظة</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line">
          <tr v-for="att in attendance" :key="att.id" class="hover:bg-edu-50 transition-colors">
            <td class="px-4 py-3 text-ink">{{ att.course_title || 'عام' }} <span class="text-ink-muted">({{ att.batch_name }})</span></td>
            <td class="px-4 py-3 text-ink-muted font-mono" dir="ltr">{{ att.date }}</td>
            <td class="px-4 py-3">
              <span :class="['px-2 py-0.5 rounded border text-[11px] font-bold', statuses[att.status]?.cls]">{{ statuses[att.status]?.label || att.status }}</span>
            </td>
            <td class="px-4 py-3 text-ink-muted">{{ att.remarks || '—' }}</td>
          </tr>
          <tr v-if="attendance.length === 0">
            <td colspan="4" class="p-4 text-center text-ink-muted">لا توجد سجلات حضور مسجلة حالياً.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  attendance: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({}) },
  byBatch: { type: Array, default: () => [] },
});

const statuses = {
  present: { label: 'حاضر', cls: 'text-edu-700 bg-edu-100 border-edu-200' },
  late: { label: 'متأخر', cls: 'text-amber-700 bg-sun-soft border-amber-200' },
  absent: { label: 'غائب', cls: 'text-red-700 bg-red-50 border-red-100' },
};
</script>
