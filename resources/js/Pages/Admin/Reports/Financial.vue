<template>
    <AdminLayout>
      <Head title="التقارير المالية" />
      <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div>
          <h1 :class="pageTitle">الخزينة والتقارير <span class="edu-underline">المالية</span></h1>
          <p :class="[pageSubtitle, 'mt-3']">مراقبة الأرباح الصافية، وتتبع رواتب المدرسين والمصاريف التشغيلية.</p>
        </div>
        <div class="flex gap-3">
          <Link :href="route('admin.expenses.index')" :class="primaryBtn">🧾 المصاريف</Link>
          <Link :href="route('admin.payrolls.index')" :class="secondaryBtn">💸 الرواتب</Link>
        </div>
      </header>

      <!-- 1️⃣ Cards Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div :class="[cardClass, 'p-6']">
          <div class="text-xs font-semibold text-ink-muted">إجمالي التدفقات الواردة (أقساط الطلاب)</div>
          <div class="text-3xl font-black text-edu-700 mt-2">{{ money(total_revenue) }}</div>
        </div>
        <div :class="[cardClass, 'p-6']">
          <div class="text-xs font-semibold text-ink-muted">إجمالي النفقات (المصاريف + الرواتب)</div>
          <div class="text-3xl font-black text-red-600 mt-2">{{ money(total_expenses) }}</div>
        </div>
        <div class="rounded-card border border-edu-200 bg-edu-100 p-6">
          <div class="text-xs font-semibold text-edu-800">صافي الأرباح التشغيلية للمعهد</div>
          <div class="text-3xl font-black text-edu-700 mt-2">{{ money(net_profit) }}</div>
        </div>
      </div>

      
      <FinancialCharts
  :total-revenue="total_revenue"
  :total-expenses="total_expenses"
  :expenses-by-category="expenses_by_category"
  :monthly-revenue="monthly_revenue"
  :monthly-expenses="monthly_expenses"
/>
      <!-- 3️⃣ Detailed Tables Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div :class="[cardClass, 'p-5 sm:p-6']">
          <h3 class="text-base font-black text-ink mb-4 flex items-center gap-2">
            <span>📥</span> <span>سجل مقبوضات الطلاب (أحدث الدفعات)</span>
          </h3>
          <div :class="tableWrap">
            <table :class="tableClass">
              <thead :class="theadClass">
                <tr>
                  <th :class="[thClass, 'rounded-s-lg']">الطالب</th>
                  <th :class="thClass">المبلغ</th>
                  <th :class="[thClass, 'rounded-e-lg']">التاريخ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pay in recent_payments" :key="pay.id" :class="trClass">
                  <td :class="[tdClass, 'text-ink font-bold']">{{ pay.student?.name || 'طالب' }}</td>
                  <td :class="[tdClass, 'text-edu-700 font-black']">{{ money(pay.amount_paid) }}</td>
                  <td :class="[tdClass, 'text-ink-muted']">{{ pay.payment_date }}</td>
                </tr>
                <tr v-if="recent_payments.length === 0">
                  <td colspan="3" class="p-4 text-center text-ink-muted">لا توجد دفعات مسجلة مؤخراً.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        
        <div :class="[cardClass, 'p-5 sm:p-6']">
          <h3 class="text-base font-black text-ink mb-4 flex items-center gap-2">
            <span>📤</span> <span>سجل الصادرات والرواتب (أحدث النفقات)</span>
          </h3>
          <div :class="tableWrap">
            <table :class="tableClass">
              <thead :class="theadClass">
                <tr>
                  <th :class="[thClass, 'rounded-s-lg']">البيان / المستفيد</th>
                  <th :class="thClass">المبلغ</th>
                  <th :class="thClass">التصنيف</th>
                  <th :class="[thClass, 'rounded-e-lg']">التاريخ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="exp in recent_expenses" :key="exp.id" :class="trClass">
                  <td :class="[tdClass, 'text-ink font-bold']">{{ exp.title }}</td>
                  <td :class="[tdClass, 'text-red-600 font-black']">{{ money(exp.amount) }}</td>
                  <td :class="tdClass">
                    <span :class="chip('green')">
                      {{ catLabel[exp.category] || exp.category }}
                    </span>
                  </td>
                  <td :class="[tdClass, 'text-ink-muted']">{{ exp.expense_date }}</td>
                </tr>
                <tr v-if="recent_expenses.length === 0">
                  <td colspan="4" class="p-4 text-center text-ink-muted">لا توجد مصاريف مسجلة مؤخراً.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </AdminLayout>
  </template>

<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
  import FinancialCharts from './FinancialCharts.vue';
  import { Head, Link } from '@inertiajs/vue3';

  import { money, cardClass, primaryBtn, secondaryBtn, pageTitle, pageSubtitle, chip, tableWrap, tableClass, theadClass, thClass, trClass, tdClass } from '@/Components/Admin/ui';
  const catLabel = { salary: 'راتب', rent: 'إيجار', utilities: 'فواتير', marketing: 'تسويق', supplies: 'مستلزمات', other: 'أخرى' };

  defineProps({
  total_revenue: Number,
  total_expenses: Number,
  net_profit: Number,
  expenses_by_category: Array,
  monthly_revenue: Array,
  monthly_expenses: Array,
  recent_payments: Array,
  recent_expenses: Array
});

  </script>