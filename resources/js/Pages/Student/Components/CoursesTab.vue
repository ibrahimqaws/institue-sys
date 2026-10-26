<template>
  <div class="space-y-6">
    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card flex flex-wrap justify-between items-center gap-3">
      <div>
        <h2 class="text-md font-bold text-ink">✨ التسجيل في كورس جديد</h2>
        <p class="text-xs text-ink-muted mt-1">استعرض الكورسات والمجموعات المتاحة وقدّم طلب تسجيل أولي.</p>
      </div>
      <Link :href="route('student.courses.available')" class="px-5 py-2.5 bg-edu-600 hover:bg-edu-700 text-white text-sm font-bold rounded-lg transition">
        استعراض الكورسات المتاحة
      </Link>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-4">
      <h2 class="text-md font-bold text-ink">📚 كورساتي وطلبات التسجيل</h2>

      <div v-if="enrollments.length === 0" class="p-6 bg-edu-50 border border-ink-line rounded-xl text-xs text-ink-muted text-center">
        لم تسجّل في أي كورس حتى الآن.
      </div>

      <div v-else class="grid grid-cols-1 gap-3">
        <div v-for="e in enrollments" :key="e.id" class="bg-edu-50 p-4 rounded-xl border border-ink-line flex flex-wrap justify-between items-center gap-3">
          <div>
            <span :class="['px-2 py-0.5 border text-[10px] rounded-md font-bold me-2', badge(e.status).cls]">{{ badge(e.status).label }}</span>
            <strong class="text-sm text-ink">{{ e.batch?.course_title || 'كورس غير مسمى' }}</strong>
            <p class="text-xs text-ink-muted mt-1.5">
              المجموعة: <span class="text-ink">{{ e.batch?.name || '—' }}</span> •
              المدرس: <span class="text-ink">{{ e.batch?.teacher || 'غير محدد' }}</span> •
              القاعة: <span class="text-ink">{{ e.batch?.room || 'غير محددة' }}</span>
            </p>
          </div>
          <div class="text-start bg-edu-100 px-3 py-2 rounded-lg">
            <span class="text-[10px] text-edu-700 block font-bold">⏰ الموعد الأسبوعي</span>
            <span class="text-xs text-ink-soft">{{ e.batch?.schedule_label || 'لم يُحدَّد بعد' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  enrollments: { type: Array, default: () => [] },
});

const badges = {
  pending: { label: 'قيد المراجعة', cls: 'bg-sun-soft text-amber-700 border-amber-200' },
  active: { label: 'مسجّل', cls: 'bg-edu-100 text-edu-700 border-edu-200' },
  completed: { label: 'منتهٍ', cls: 'bg-sky-50 text-sky-700 border-sky-100' },
  dropped: { label: 'منسحب / مرفوض', cls: 'bg-red-50 text-red-700 border-red-100' },
};
const badge = (s) => badges[s] || { label: s, cls: 'bg-slate-100 text-ink-soft border-ink-line' };
</script>
