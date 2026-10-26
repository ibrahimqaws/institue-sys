<template>
  <AdminLayout>
    <Head title="الرواتب" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">رواتب ومستحقات المدرسين <span class="edu-underline">والموظفين</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">توليد كشف الشهر، تعديل المكافآت والخصومات، ثم الصرف (يُسجَّل تلقائياً ضمن المصاريف).</p>
      </div>
      <div class="flex items-center gap-3">
        <input v-model="monthValue" type="month" :class="[inputClass, 'w-44']" @change="goMonth" />
        <button v-if="missing > 0" @click="generate" :class="primaryBtn">توليد كشف الشهر ({{ missing }})</button>
      </div>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
      <div :class="[cardClass, 'p-5']"><div class="text-xs text-ink-muted">رواتب بانتظار الصرف</div><div class="text-2xl font-black text-amber-700 mt-1">{{ money(totals.pending) }}</div></div>
      <div :class="[cardClass, 'p-5']"><div class="text-xs text-ink-muted">رواتب مصروفة</div><div class="text-2xl font-black text-edu-700 mt-1">{{ money(totals.paid) }}</div></div>
    </div>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">الموظف</th><th class="p-4">الأساسي</th><th class="p-4">مكافأة</th><th class="p-4">خصم</th><th class="p-4">الصافي</th><th class="p-4">الحالة</th><th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="p in payrolls" :key="p.id" class="hover:bg-edu-50">
            <td class="p-4 text-ink font-bold">{{ p.user?.name }}<div class="text-xs text-ink-muted font-normal">{{ p.user?.role === 'teacher' ? 'مدرس' : 'إدارة' }}</div></td>
            <td class="p-4 text-ink-soft">{{ money(p.base_amount) }}</td>
            <td class="p-4 text-edu-700">{{ money(p.bonus) }}</td>
            <td class="p-4 text-red-600">{{ money(p.deductions) }}</td>
            <td class="p-4 text-amber-700 font-black">{{ money(p.net_amount) }}</td>
            <td class="p-4">
              <span :class="chip(p.status === 'paid' ? 'green' : 'yellow')">
                {{ p.status === 'paid' ? `مصروف ${p.paid_at || ''}` : 'بانتظار الصرف' }}
              </span>
            </td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <template v-if="p.status === 'pending'">
                <button @click="openEdit(p)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">تعديل</button>
                <button @click="pay(p)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">صرف</button>
                <button @click="remove(p)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-md text-xs font-bold">حذف</button>
              </template>
              <a v-else-if="p.expense_id" :href="route('admin.expenses.voucher', p.expense_id)" target="_blank" class="px-3 py-1.5 bg-white border border-ink-line hover:bg-edu-50 text-ink-soft rounded-md text-xs font-bold">🖨️ سند صرف</a>
            </td>
          </tr>
          <tr v-if="payrolls.length === 0">
            <td colspan="7" class="p-8 text-center text-ink-muted">لا توجد قيود لهذا الشهر. حدّد راتباً أساسياً للموظفين من «إدارة المستخدمين» ثم ولّد الكشف.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showEdit" :title="`✏️ تعديل راتب ${editing?.user?.name || ''}`" @close="showEdit = false">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div><label :class="labelClass">الأساسي</label><input v-model="form.base_amount" type="number" min="0" step="0.01" :class="inputClass" required /></div>
          <div><label :class="labelClass">مكافأة</label><input v-model="form.bonus" type="number" min="0" step="0.01" :class="inputClass" required /></div>
          <div><label :class="labelClass">خصم</label><input v-model="form.deductions" type="number" min="0" step="0.01" :class="inputClass" required /></div>
        </div>
        <p class="text-sm text-ink-soft">الصافي: <b class="text-amber-700">{{ money(net) }}</b></p>
        <p v-if="form.errors.deductions" class="text-red-600 text-xs">{{ form.errors.deductions }}</p>
        <div><label :class="labelClass">ملاحظات</label><textarea v-model="form.notes" :class="[inputClass, 'h-20']"></textarea></div>
        <div class="flex justify-end space-x-3 space-x-reverse pt-2">
          <button type="button" @click="showEdit = false" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
          <button type="submit" :disabled="form.processing" :class="primaryBtn">حفظ</button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, cardClass, money, pageTitle, pageSubtitle, chip } from '@/Components/Admin/ui';

const props = defineProps({ payrolls: Array, month: String, missing: Number, totals: Object });

const monthValue = ref(props.month);
const showEdit = ref(false);
const editing = ref(null);
const form = useForm({ base_amount: 0, bonus: 0, deductions: 0, notes: '' });
const net = computed(() => Number(form.base_amount || 0) + Number(form.bonus || 0) - Number(form.deductions || 0));

const goMonth = () => router.get(route('admin.payrolls.index'), { month: monthValue.value });
const generate = () => router.post(route('admin.payrolls.generate'), { month: props.month }, { preserveScroll: true });
const openEdit = (p) => {
  editing.value = p;
  form.clearErrors();
  form.base_amount = p.base_amount; form.bonus = p.bonus; form.deductions = p.deductions; form.notes = p.notes ?? '';
  showEdit.value = true;
};
const save = () => form.put(route('admin.payrolls.update', editing.value.id), { preserveScroll: true, onSuccess: () => (showEdit.value = false) });
const pay = (p) => confirm(`صرف راتب ${p.user?.name} (${money(p.net_amount)})؟ لا يمكن التراجع.`) && router.post(route('admin.payrolls.pay', p.id), {}, { preserveScroll: true });
const remove = (p) => confirm('حذف قيد الراتب؟') && router.delete(route('admin.payrolls.destroy', p.id), { preserveScroll: true });
</script>
