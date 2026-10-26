<template>
  <AdminLayout>
    <Head title="المصاريف" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">المصاريف العامة <span class="edu-underline">للمعهد</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">الإيجار والفواتير والتسويق وغيرها. الرواتب تُصرف من صفحة «الرواتب».</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">
        + تسجيل مصروف
      </button>
    </header>

    <div class="edu-bar flex flex-wrap items-end gap-3 mb-5">
      <div>
        <label :class="labelClass">التصنيف</label>
        <select v-model="category" :class="[inputClass, 'w-44']" @change="apply">
          <option value="">الكل</option>
          <option v-for="c in [...categories, 'salary']" :key="c" :value="c">{{ catLabel[c] }}</option>
        </select>
      </div>
      <div><label :class="labelClass">من</label><input v-model="from" type="date" :class="inputClass" @change="apply" /></div>
      <div><label :class="labelClass">إلى</label><input v-model="to" type="date" :class="inputClass" @change="apply" /></div>
      <div class="mr-auto text-sm text-ink-soft">إجمالي النتائج: <b class="text-red-600 text-lg">{{ money(total) }}</b></div>
    </div>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">البيان</th><th class="p-4">التصنيف</th><th class="p-4">المبلغ</th><th class="p-4">التاريخ</th><th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="e in expenses.data" :key="e.id" class="hover:bg-edu-50">
            <td class="p-4 text-ink font-bold">{{ e.title }}<div v-if="e.notes" class="text-xs text-ink-muted font-normal">{{ e.notes }}</div></td>
            <td class="p-4"><span :class="chip(e.category === 'salary' ? 'blue' : 'green')">{{ catLabel[e.category] || e.category }}</span></td>
            <td class="p-4 text-red-600 font-black">{{ money(e.amount) }}</td>
            <td class="p-4 text-xs text-ink-muted font-mono">{{ e.expense_date }}</td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <a :href="route('admin.expenses.voucher', e.id)" target="_blank" class="px-3 py-1.5 bg-white border border-ink-line hover:bg-edu-50 text-ink-soft rounded-md text-xs font-bold">🖨️ سند</a>
              <template v-if="!e.locked">
                <button @click="openEdit(e)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">تعديل</button>
                <button @click="remove(e)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-md text-xs font-bold">حذف</button>
              </template>
              <span v-else class="text-xs text-ink-muted">راتب مصروف</span>
            </td>
          </tr>
          <tr v-if="expenses.data.length === 0"><td colspan="5" class="p-8 text-center text-ink-muted">لا توجد مصاريف مطابقة.</td></tr>
        </tbody>
      </table>
    </div>
    <Pagination :links="expenses.links" />

    <ExpenseModal :show="showModal" :expense="editing" :categories="categories" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import ExpenseModal from './ExpenseModal.vue';
import { inputClass, labelClass, cardClass, money, primaryBtn, pageTitle, pageSubtitle, chip } from '@/Components/Admin/ui';

const props = defineProps({ expenses: Object, total: Number, categories: Array, filters: Object });

const catLabel = { rent: '🏢 إيجار', utilities: '⚡ فواتير وخدمات', marketing: '📣 تسويق', supplies: '🧰 مستلزمات', other: '📦 أخرى', salary: '💸 رواتب' };

const category = ref(props.filters?.category || '');
const from = ref(props.filters?.from || '');
const to = ref(props.filters?.to || '');
const showModal = ref(false);
const editing = ref(null);

const apply = () => router.get(route('admin.expenses.index'), {
  category: category.value || undefined, from: from.value || undefined, to: to.value || undefined,
}, { preserveState: true });

const openCreate = () => { editing.value = null; showModal.value = true; };
const openEdit = (e) => { editing.value = e; showModal.value = true; };
const remove = (e) => confirm(`حذف المصروف «${e.title}»؟`) && router.delete(route('admin.expenses.destroy', e.id), { preserveScroll: true });
</script>
