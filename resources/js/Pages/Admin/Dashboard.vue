<template>
  <AdminLayout>
    <Head title="لوحة الإحصائيات" />
    <header class="mb-8">
      <h1 :class="pageTitle">لوحة <span class="edu-underline">الإحصائيات</span></h1>
      <p :class="[pageSubtitle, 'mt-3']">نظرة عامة على المعهد: الأعداد، مالية الشهر الحالي، ونسب الغياب.</p>
    </header>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <Link v-for="s in cards" :key="s.label" :href="route(s.route)" :class="[cardClass, 'p-5 hover:border-edu-300 hover:shadow-pop transition']">
        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-edu-100 text-lg">{{ s.icon }}</span>
        <div class="text-3xl font-black text-ink mt-3">{{ s.value }}</div>
        <div class="text-xs font-semibold text-ink-muted mt-1">{{ s.label }}</div>
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div :class="[cardClass, 'p-5']"><div class="text-xs font-semibold text-ink-muted">إيرادات هذا الشهر</div><div class="text-2xl font-black text-edu-700 mt-1">{{ money(finance.revenue_month) }}</div></div>
      <div :class="[cardClass, 'p-5']"><div class="text-xs font-semibold text-ink-muted">مصاريف هذا الشهر</div><div class="text-2xl font-black text-red-600 mt-1">{{ money(finance.expenses_month) }}</div></div>
      <div class="rounded-card border border-edu-200 bg-edu-100 p-5"><div class="text-xs font-semibold text-edu-800">صافي الشهر</div><div class="text-2xl font-black text-edu-700 mt-1">{{ money(finance.profit_month) }}</div></div>
      <Link :href="route('admin.payments.index')" :class="[cardClass, 'p-5 border-sun/50 hover:shadow-pop transition']"><div class="text-xs font-semibold text-ink-muted">أقساط متبقية على الطلاب</div><div class="text-2xl font-black text-amber-700 mt-1">{{ money(finance.outstanding) }}</div></Link>
    </div>

    <section :class="[cardClass, 'p-5 sm:p-6']">
      <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <h2 class="font-black text-ink">📉 نسب الحضور والغياب</h2>
        <div class="text-sm text-ink-soft flex flex-wrap gap-x-4 gap-y-1">
          <span>سجلات: <b class="text-ink">{{ attendance.records }}</b></span>
          <span class="text-edu-700">حاضر: <b>{{ attendance.present }}</b></span>
          <span class="text-red-600">غائب: <b>{{ attendance.absent }}</b></span>
          <span class="text-amber-700">متأخر: <b>{{ attendance.late }}</b></span>
          <span>نسبة الغياب العامة: <b class="text-red-600">{{ attendance.absence_rate }}%</b></span>
        </div>
      </div>

      <div :class="tableWrap">
        <table :class="tableClass">
          <thead :class="theadClass">
            <tr><th :class="[thClass, 'rounded-s-lg']">المجموعة</th><th :class="thClass">الكورس</th><th :class="thClass">السجلات</th><th :class="thClass">غياب</th><th :class="thClass">تأخر</th><th :class="[thClass, 'w-56 rounded-e-lg']">نسبة الغياب</th></tr>
          </thead>
          <tbody>
            <tr v-for="b in attendance.by_batch" :key="b.batch" :class="trClass">
              <td :class="[tdClass, 'text-ink font-bold']">{{ b.batch }}</td>
              <td :class="[tdClass, 'text-edu-700 font-semibold']">{{ b.course }}</td>
              <td :class="tdClass">{{ b.records }}</td>
              <td :class="[tdClass, 'text-red-600']">{{ b.absent }}</td>
              <td :class="[tdClass, 'text-amber-700']">{{ b.late }}</td>
              <td :class="tdClass">
                <div class="flex items-center gap-2">
                  <div class="flex-1 h-2 rounded-full bg-edu-100 overflow-hidden">
                    <div :class="b.absence_rate >= 25 ? 'bg-red-500' : b.absence_rate >= 10 ? 'bg-sun' : 'bg-edu-600'" class="h-full rounded-full" :style="{ width: Math.min(b.absence_rate, 100) + '%' }"></div>
                  </div>
                  <span class="text-xs font-bold text-ink w-12">{{ b.absence_rate }}%</span>
                </div>
              </td>
            </tr>
            <tr v-if="attendance.by_batch.length === 0"><td colspan="6" class="p-6 text-center text-ink-muted">لم يُسجَّل حضور بعد.</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { cardClass, money, pageTitle, pageSubtitle, tableWrap, tableClass, theadClass, thClass, trClass, tdClass } from '@/Components/Admin/ui';

const props = defineProps({ stats: Object, finance: Object, attendance: Object });

const cards = computed(() => [
  { icon: '👨‍🎓', label: 'الطلاب الفعّالون', value: props.stats.students, route: 'admin.users.index' },
  { icon: '👨‍🏫', label: 'المدرسون', value: props.stats.teachers, route: 'admin.users.index' },
  { icon: '📚', label: 'الكورسات', value: props.stats.courses, route: 'admin.courses.index' },
  { icon: '👥', label: 'مجموعات نشطة', value: props.stats.active_batches, route: 'admin.batches.index' },
  { icon: '⏳', label: 'طلبات تسجيل معلّقة', value: props.stats.pending_enrollments, route: 'admin.enrollments.index' },
]);
</script>
