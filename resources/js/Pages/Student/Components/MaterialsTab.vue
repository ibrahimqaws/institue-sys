<template>
  <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-4">
    <div class="border-b border-ink-line pb-3">
      <h2 class="text-md font-bold text-ink">📎 المواد التعليمية والواجبات</h2>
      <p class="text-xs text-ink-muted mt-0.5">ما رفعه مدرسوك لمجموعاتك. التنزيل متاح لك فقط كطالب مسجّل في المجموعة.</p>
    </div>

    <div v-if="materials.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="m in materials" :key="m.id" class="p-4 bg-edu-50 border border-ink-line rounded-xl flex justify-between items-start gap-3">
        <div class="space-y-1 min-w-0">
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-edu-100 text-edu-700 border border-edu-200 text-[10px] rounded font-bold">{{ m.course_title || 'كورس' }}</span>
            <span :class="['px-2 py-0.5 border text-[10px] rounded font-bold', m.type === 'assignment' ? 'bg-sun-soft text-amber-700 border-amber-200' : 'bg-sky-50 text-sky-700 border-sky-100']">
              {{ m.type === 'assignment' ? 'واجب' : 'مادة علمية' }}
            </span>
          </div>
          <h4 class="font-bold text-sm text-ink">{{ m.title }}</h4>
          <p v-if="m.description" class="text-xs text-ink-muted whitespace-pre-line">{{ m.description }}</p>
          <p class="text-[11px] text-ink-muted">
            {{ m.teacher || 'المدرس' }} • <span class="font-mono" dir="ltr">{{ m.created_at }}</span>
          </p>
          <p v-if="m.due_date" :class="['text-[11px] font-bold', m.is_overdue ? 'text-red-600' : 'text-amber-700']">
            ⏳ موعد التسليم: <span class="font-mono" dir="ltr">{{ m.due_date }}</span><span v-if="m.is_overdue"> (انتهى)</span>
          </p>
        </div>

        <a
          v-if="m.download_url"
          :href="m.download_url"
          class="px-4 py-2 bg-edu-600 hover:bg-edu-700 text-white text-xs font-bold rounded-lg transition shrink-0"
        >⬇️ تنزيل</a>
        <span v-else class="text-[11px] text-ink-muted shrink-0">بلا ملف</span>
      </div>
    </div>

    <div v-else class="p-6 bg-edu-50 border border-ink-line rounded-xl text-xs text-ink-muted text-center">
      لا توجد مواد أو واجبات مرفوعة حالياً.
    </div>
  </div>
</template>

<script setup>
defineProps({ materials: { type: Array, default: () => [] } });
</script>
