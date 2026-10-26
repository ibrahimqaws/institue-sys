<template>
  <Head title="التسجيل في كورس" />
  <PanelLayout>
    <div class="max-w-6xl mx-auto space-y-6">
      <header class="flex flex-wrap justify-between items-center gap-3 bg-white p-4 rounded-card border border-ink-line shadow-card">
        <div>
          <h1 class="text-2xl font-black text-ink">✨ الكورسات <span class="edu-underline">المتاحة</span> للتسجيل</h1>
          <p class="text-sm text-ink-muted mt-1">اختر المجموعة المناسبة وأرسل طلب تسجيل أولي؛ تؤكده إدارة المعهد.</p>
        </div>
      </header>

      <div v-if="batches.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="b in batches" :key="b.id" class="bg-white p-5 rounded-card border border-ink-line shadow-card hover:shadow-pop transition-shadow flex flex-col gap-3">
          <div>
            <h3 class="text-base font-black text-ink">{{ b.course.title }}</h3>
            <p v-if="b.course.description" class="text-xs text-ink-muted mt-1 leading-relaxed">{{ b.course.description }}</p>
          </div>

          <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
            <div><dt class="text-ink-muted inline">المجموعة: </dt><dd class="text-ink inline">{{ b.name }}</dd></div>
            <div><dt class="text-ink-muted inline">المدرس: </dt><dd class="text-ink inline">{{ b.teacher || 'سيُعيَّن لاحقاً' }}</dd></div>
            <div><dt class="text-ink-muted inline">الموعد: </dt><dd class="text-ink inline">{{ b.schedule_label || 'يُحدَّد لاحقاً' }}</dd></div>
            <div><dt class="text-ink-muted inline">القاعة: </dt><dd class="text-ink inline">{{ b.room || 'غير محددة' }}</dd></div>
            <div><dt class="text-ink-muted inline">البداية: </dt><dd class="text-ink inline font-mono" dir="ltr">{{ b.start_date }}</dd></div>
            <div><dt class="text-ink-muted inline">المدة: </dt><dd class="text-ink inline">{{ b.course.duration_hours ? b.course.duration_hours + ' ساعة' : '—' }}</dd></div>
          </dl>

          <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-ink-line">
            <div class="bg-edu-100 rounded-lg px-3 py-2">
              <span class="text-lg font-black text-edu-700">{{ money(b.course.price) }}</span>
              <span v-if="b.seats_left !== null" :class="['text-[11px] ms-3', b.seats_left > 0 ? 'text-ink-muted' : 'text-red-600']">
                {{ b.seats_left > 0 ? `المقاعد المتبقية: ${b.seats_left}` : 'مكتملة العدد' }}
              </span>
            </div>

            <span v-if="b.my_status === 'pending'" class="px-3 py-2 text-xs font-bold rounded-lg bg-sun-soft text-amber-700 border border-amber-200">قيد المراجعة</span>
            <span v-else-if="b.my_status === 'active'" class="px-3 py-2 text-xs font-bold rounded-lg bg-edu-100 text-edu-700 border border-edu-200">أنت مسجّل ✓</span>
            <span v-else-if="b.my_status === 'completed'" class="px-3 py-2 text-xs font-bold rounded-lg bg-sky-50 text-sky-700 border border-sky-100">أنهيتها سابقاً</span>
            <button
              v-else
              @click="register(b)"
              :disabled="busyId === b.id || b.seats_left === 0"
              class="px-5 py-2.5 bg-edu-600 hover:bg-edu-700 text-white text-xs font-bold rounded-lg transition disabled:opacity-50"
            >
              {{ busyId === b.id ? 'جاري الإرسال...' : (b.my_status === 'dropped' ? 'إعادة تقديم الطلب' : 'تسجيل أولي') }}
            </button>
          </div>
        </div>
      </div>

      <div v-else class="p-10 bg-white border border-ink-line rounded-card shadow-card text-center text-sm text-ink-muted">
        لا توجد مجموعات متاحة للتسجيل حالياً.
      </div>
    </div>
  </PanelLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { money } from '@/Components/Admin/ui';

defineProps({ batches: { type: Array, default: () => [] } });

const busyId = ref(null);

const register = (batch) => {
  busyId.value = batch.id;
  router.post(route('student.courses.register'), { batch_id: batch.id }, {
    preserveScroll: true,
    onFinish: () => (busyId.value = null),
  });
};
</script>
