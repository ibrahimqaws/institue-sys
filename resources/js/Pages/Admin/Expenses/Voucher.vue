<template>
  <Head :title="`سند صرف ${expense.id}`" />
  <div class="min-h-screen bg-edu-50 print:bg-white p-6 flex flex-col items-center" dir="rtl">
    <div class="no-print w-full max-w-2xl flex justify-between mb-4">
      <button @click="print" class="px-5 py-2 bg-edu-600 hover:bg-edu-700 text-white rounded-lg text-sm font-bold transition">🖨️ طباعة</button>
      <button @click="close" class="px-5 py-2 bg-white text-edu-700 hover:bg-edu-50 rounded-lg text-sm font-bold border border-edu-200 transition">إغلاق</button>
    </div>

    <article class="w-full max-w-2xl bg-white text-ink p-10 rounded-card shadow-card print:shadow-none print:rounded-none border border-ink-line">
      <header class="flex justify-between items-start border-b-2 border-edu-600 pb-4 mb-6">
        <div>
          <h1 class="text-2xl font-black text-edu-700">سند صرف</h1>
          <p class="text-sm text-ink-muted">معهد التدريب</p>
        </div>
        <div class="text-end text-sm">
          <div class="font-mono font-bold">EXP-{{ String(expense.id).padStart(5, '0') }}</div>
          <div class="text-ink-muted">{{ expense.expense_date }}</div>
        </div>
      </header>

      <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-ink-muted">البيان</dt><dd class="font-bold">{{ expense.title }}</dd>
        <dt class="text-ink-muted">التصنيف</dt><dd class="font-bold">{{ labels[expense.category] || expense.category }}</dd>
        <template v-if="expense.user"><dt class="text-ink-muted">المستفيد</dt><dd class="font-bold">{{ expense.user.name }}</dd></template>
        <dt class="text-ink-muted">المبلغ المصروف</dt><dd class="font-black text-xl">{{ money(expense.amount) }}</dd>
        <template v-if="expense.notes"><dt class="text-ink-muted">ملاحظات</dt><dd>{{ expense.notes }}</dd></template>
      </dl>

      <footer class="grid grid-cols-2 gap-8 mt-16 text-center text-sm text-ink-muted">
        <div class="border-t border-ink-muted pt-2">توقيع المحاسب</div>
        <div class="border-t border-ink-muted pt-2">توقيع المستلم</div>
      </footer>
    </article>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { money } from '@/Components/Admin/ui';

defineProps({ expense: Object });

const labels = { rent: 'إيجار', utilities: 'فواتير وخدمات', marketing: 'تسويق', supplies: 'مستلزمات', other: 'أخرى', salary: 'راتب' };
const print = () => window.print();
const close = () => (window.history.length > 1 ? window.history.back() : window.close());
</script>

<style>
@media print {
  .no-print { display: none !important; }
}
</style>
