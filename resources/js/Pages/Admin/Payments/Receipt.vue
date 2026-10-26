<template>
  <Head :title="`سند قبض ${payment.receipt_number}`" />
  <div class="min-h-screen bg-edu-50 print:bg-white p-6 flex flex-col items-center" dir="rtl">
    <div class="no-print w-full max-w-2xl flex justify-between mb-4">
      <button @click="print" class="px-5 py-2 bg-edu-600 hover:bg-edu-700 text-white rounded-lg text-sm font-bold transition">🖨️ طباعة</button>
      <button @click="close" class="px-5 py-2 bg-white text-edu-700 hover:bg-edu-50 rounded-lg text-sm font-bold border border-edu-200 transition">إغلاق</button>
    </div>

    <article class="w-full max-w-2xl bg-white text-ink p-10 rounded-card shadow-card print:shadow-none print:rounded-none border border-ink-line">
      <header class="flex justify-between items-start border-b-2 border-edu-600 pb-4 mb-6">
        <div>
          <h1 class="text-2xl font-black text-edu-700">سند قبض</h1>
          <p class="text-sm text-ink-muted">معهد التدريب</p>
        </div>
        <div class="text-end text-sm">
          <div class="font-mono font-bold">#{{ payment.receipt_number }}</div>
          <div class="text-ink-muted">{{ payment.payment_date }}</div>
        </div>
      </header>

      <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-ink-muted">استلمنا من الطالب</dt><dd class="font-bold">{{ payment.student?.name }}</dd>
        <dt class="text-ink-muted">الكورس</dt><dd class="font-bold">{{ payment.batch?.course?.title }}</dd>
        <dt class="text-ink-muted">المجموعة</dt><dd class="font-bold">{{ payment.batch?.name }}</dd>
        <dt class="text-ink-muted">وسيلة الدفع</dt><dd class="font-bold">{{ methods[payment.payment_method] || payment.payment_method }}</dd>
        <dt class="text-ink-muted">المبلغ المستلم</dt><dd class="font-black text-xl">{{ money(payment.amount_paid) }}</dd>
        <template v-if="fee !== null">
          <dt class="text-ink-muted">رسوم الكورس</dt><dd>{{ money(fee) }}</dd>
          <dt class="text-ink-muted">إجمالي المدفوع حتى الآن</dt><dd>{{ money(total_paid) }}</dd>
          <dt class="text-ink-muted">المتبقي</dt><dd class="font-bold">{{ money(remaining) }}</dd>
        </template>
        <template v-if="payment.notes"><dt class="text-ink-muted">ملاحظات</dt><dd>{{ payment.notes }}</dd></template>
      </dl>

      <footer class="grid grid-cols-2 gap-8 mt-16 text-center text-sm text-ink-muted">
        <div class="border-t border-ink-muted pt-2">توقيع المستلم</div>
        <div class="border-t border-ink-muted pt-2">توقيع الطالب</div>
      </footer>
    </article>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { money } from '@/Components/Admin/ui';

defineProps({ payment: Object, fee: Number, total_paid: Number, remaining: Number });

const methods = { cash: 'نقداً', transfer: 'تحويل بنكي', card: 'بطاقة' };
const print = () => window.print();
const close = () => (window.history.length > 1 ? window.history.back() : window.close());
</script>

<style>
@media print {
  .no-print { display: none !important; }
}
</style>
