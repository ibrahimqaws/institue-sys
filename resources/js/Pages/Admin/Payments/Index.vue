<template>
  <AdminLayout>
    <Head title="الأقساط والمدفوعات" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">سجل المقبوضات وأقساط <span class="edu-underline">الطلاب</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">تسجيل الدفعات ومتابعة المتبقي على كل طالب وطباعة سندات القبض.</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">
        + تسجيل دفعة جديدة
      </button>
    </header>

    
    <section :class="[cardClass, 'p-5 sm:p-6 mb-8']">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-amber-700">⏳ الأقساط المتبقية على الطلاب ({{ owing.length }})</h2>
        <span class="text-sm text-ink-soft">الإجمالي: <b class="text-amber-700">{{ money(owingTotal) }}</b></span>
      </div>
      <div class="overflow-x-auto max-h-64 overflow-y-auto">
        <table class="w-full text-start text-xs">
          <thead>
            <tr class="bg-edu-100 text-edu-800 font-bold">
              <th class="p-2 rounded-s-lg">الطالب</th><th class="p-2">المجموعة</th><th class="p-2">الرسوم</th><th class="p-2">المدفوع</th><th class="p-2">المتبقي</th><th class="p-2 rounded-e-lg"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-ink-line">
            <tr v-for="b in owing" :key="b.student_id + '-' + b.batch_id">
              <td class="p-2 text-ink font-bold">{{ b.student_name }}</td>
              <td class="p-2 text-ink-soft">{{ b.batch_name }} <span class="text-edu-700">({{ b.course_title }})</span></td>
              <td class="p-2 text-ink-soft">{{ money(b.fee) }}</td>
              <td class="p-2 text-edu-700">{{ money(b.paid) }}</td>
              <td class="p-2 text-amber-700 font-black">{{ money(b.remaining) }}</td>
              <td class="p-2 text-end"><button @click="openCreate(b)" class="px-2.5 py-1 bg-edu-100 text-edu-700 rounded-lg font-semibold">تسديد</button></td>
            </tr>
            <tr v-if="owing.length === 0"><td colspan="6" class="p-4 text-center text-ink-muted">لا توجد أقساط متبقية 🎉</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <div class="flex flex-wrap gap-3 mb-5">
      <input v-model="search" @keyup.enter="doSearch" type="search" placeholder="بحث باسم الطالب أو رقم السند..." :class="[inputClass, 'max-w-xs']" />
      <button @click="doSearch" class="px-4 py-2 bg-edu-100 text-edu-700 text-sm font-bold rounded-lg hover:bg-edu-200">بحث</button>
    </div>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">رقم السند</th><th class="p-4">الطالب</th><th class="p-4">الكورس / المجموعة</th>
            <th class="p-4">المبلغ</th><th class="p-4">الوسيلة</th><th class="p-4">التاريخ</th><th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="p in payments.data" :key="p.id" class="hover:bg-edu-50">
            <td class="p-4 font-mono font-bold text-amber-700">#{{ p.receipt_number }}</td>
            <td class="p-4 font-bold text-ink">{{ p.student?.name || '—' }}</td>
            <td class="p-4 text-edu-700">{{ p.batch?.course?.title }} ({{ p.batch?.name }})</td>
            <td class="p-4 font-black text-edu-700">{{ money(p.amount_paid) }}</td>
            <td class="p-4 text-xs text-ink-soft">{{ methods[p.payment_method] || p.payment_method }}</td>
            <td class="p-4 text-ink-muted text-xs font-mono">{{ p.payment_date }}</td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <a :href="route('admin.payments.receipt', p.id)" target="_blank" class="px-3 py-1.5 bg-white border border-ink-line hover:bg-edu-50 text-ink-soft rounded-md text-xs font-bold">🖨️ سند</a>
              <button @click="openEdit(p)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">تعديل</button>
              <button @click="remove(p)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-md text-xs font-bold">حذف</button>
            </td>
          </tr>
          <tr v-if="payments.data.length === 0"><td colspan="7" class="p-8 text-center text-ink-muted">لا توجد دفعات مسجلة.</td></tr>
        </tbody>
      </table>
    </div>
    <Pagination :links="payments.links" />

    <PaymentModal :show="showModal" :payment="editing" :preset="preset" :balances="balances" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import PaymentModal from './PaymentModal.vue';
import { inputClass, cardClass, money, primaryBtn, pageTitle, pageSubtitle } from '@/Components/Admin/ui';

const props = defineProps({ payments: Object, balances: Array, filters: Object });

const methods = { cash: '💵 نقداً', transfer: '🏦 تحويل', card: '💳 بطاقة' };
const search = ref(props.filters?.search || '');
const showModal = ref(false);
const editing = ref(null);
const preset = ref(null);

const owing = computed(() => props.balances.filter((b) => b.remaining > 0));
const owingTotal = computed(() => owing.value.reduce((s, b) => s + b.remaining, 0));

const doSearch = () => router.get(route('admin.payments.index'), { search: search.value || undefined }, { preserveState: true });
const openCreate = (b = null) => { editing.value = null; preset.value = b && b.student_id ? b : null; showModal.value = true; };
const openEdit = (p) => { editing.value = p; preset.value = null; showModal.value = true; };
const remove = (p) => confirm(`حذف الدفعة #${p.receipt_number}؟ سيزيد المتبقي على الطالب.`) && router.delete(route('admin.payments.destroy', p.id), { preserveScroll: true });
</script>
