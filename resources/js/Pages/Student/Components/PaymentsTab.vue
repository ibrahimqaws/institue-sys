<template>
  <div class="space-y-6">
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">إجمالي الرسوم</p>
        <h3 class="text-2xl font-black text-ink mt-2">{{ money(finance.totals?.fee) }}</h3>
      </div>
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">المدفوع</p>
        <h3 class="text-2xl font-black text-edu-700 mt-2">{{ money(finance.totals?.paid) }}</h3>
      </div>
      <div class="bg-white p-5 rounded-card border border-ink-line shadow-card">
        <p class="text-xs text-ink-muted">المتبقي</p>
        <h3 class="text-2xl font-black text-amber-700 mt-2">{{ money(finance.totals?.remaining) }}</h3>
      </div>
    </div>

    
    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-3">
      <h2 class="text-md font-bold text-ink">💰 الرسوم والمتبقي لكل كورس</h2>
      <div class="overflow-x-auto rounded-xl border border-ink-line">
        <table class="w-full text-start text-xs">
          <thead class="bg-edu-100 text-edu-800 font-bold">
            <tr><th class="px-4 py-3 font-bold whitespace-nowrap">الكورس / المجموعة</th><th class="px-4 py-3 font-bold whitespace-nowrap">الرسوم</th><th class="px-4 py-3 font-bold whitespace-nowrap">المدفوع</th><th class="px-4 py-3 font-bold whitespace-nowrap">المتبقي</th></tr>
          </thead>
          <tbody class="divide-y divide-ink-line">
            <tr v-for="c in finance.courses" :key="c.enrollment_id" class="hover:bg-edu-50 transition-colors">
              <td class="px-4 py-3 text-ink font-bold">{{ c.course_title || 'كورس' }} <span class="text-ink-muted font-normal">({{ c.batch_name }})</span></td>
              <td class="px-4 py-3 text-ink-soft">{{ money(c.fee) }}</td>
              <td class="px-4 py-3 text-edu-700">{{ money(c.paid) }}</td>
              <td class="px-4 py-3">
                <span v-if="c.remaining > 0" class="text-amber-700 font-black">{{ money(c.remaining) }}</span>
                <span v-else class="text-edu-700 font-bold">مسدّد ✓</span>
              </td>
            </tr>
            <tr v-if="!finance.courses?.length"><td colspan="4" class="p-6 text-center text-ink-muted">لا توجد رسوم مستحقة؛ تظهر هنا بعد تأكيد تسجيلك في مجموعة.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    
    <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card space-y-3">
      <h2 class="text-md font-bold text-ink">📜 سجل المدفوعات</h2>
      <div class="overflow-x-auto rounded-xl border border-ink-line">
        <table class="w-full text-start text-xs">
          <thead class="bg-edu-100 text-edu-800 font-bold">
            <tr><th class="px-4 py-3 font-bold whitespace-nowrap">رقم السند</th><th class="px-4 py-3 font-bold whitespace-nowrap">الكورس</th><th class="px-4 py-3 font-bold whitespace-nowrap">المبلغ</th><th class="px-4 py-3 font-bold whitespace-nowrap">الطريقة</th><th class="px-4 py-3 font-bold whitespace-nowrap">التاريخ</th></tr>
          </thead>
          <tbody class="divide-y divide-ink-line">
            <tr v-for="p in finance.payments" :key="p.id" class="hover:bg-edu-50 transition-colors">
              <td class="px-4 py-3 text-ink-muted font-mono" dir="ltr">{{ p.receipt_number }}</td>
              <td class="px-4 py-3 text-ink">{{ p.course_title || 'دفعة' }} <span class="text-ink-muted">({{ p.batch_name }})</span></td>
              <td class="px-4 py-3 text-amber-700 font-black">{{ money(p.amount) }}</td>
              <td class="px-4 py-3 text-ink-muted">{{ p.method_label }}</td>
              <td class="px-4 py-3 text-ink-muted font-mono" dir="ltr">{{ p.payment_date }}</td>
            </tr>
            <tr v-if="!finance.payments?.length"><td colspan="5" class="p-6 text-center text-ink-muted">لا توجد مدفوعات مسجلة حتى الآن.</td></tr>
          </tbody>
        </table>
      </div>
      <p class="text-[11px] text-ink-muted">تُسجَّل الدفعات من قِبل إدارة المعهد. للاستفسار عن أي دفعة راجع الإدارة.</p>
    </div>
  </div>
</template>

<script setup>
import { money } from '@/Components/Admin/ui';

defineProps({
  finance: { type: Object, default: () => ({ courses: [], payments: [], totals: {} }) },
});
</script>
