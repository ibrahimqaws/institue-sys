<template>
  <Modal :show="show" :title="expense ? '✏️ تعديل المصروف' : '🧾 تسجيل مصروف'" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label :class="labelClass">البيان</label>
        <input v-model="form.title" type="text" :class="inputClass" placeholder="مثال: فاتورة كهرباء - أكتوبر" required />
        <p v-if="form.errors.title" class="text-red-600 text-xs mt-1">{{ form.errors.title }}</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">المبلغ ($)</label>
          <input v-model="form.amount" type="number" min="0.01" step="0.01" :class="inputClass" required />
          <p v-if="form.errors.amount" class="text-red-600 text-xs mt-1">{{ form.errors.amount }}</p>
        </div>
        <div>
          <label :class="labelClass">التاريخ</label>
          <input v-model="form.expense_date" type="date" :class="inputClass" required />
        </div>
      </div>
      <div>
        <label :class="labelClass">التصنيف</label>
        <select v-model="form.category" :class="inputClass">
          <option value="rent">🏢 إيجار المقر</option>
          <option value="utilities">⚡ فواتير خدمات (كهرباء/إنترنت)</option>
          <option value="marketing">📣 حملات تسويقية</option>
          <option value="supplies">🧰 مستلزمات وأدوات</option>
          <option value="other">📦 مصاريف أخرى</option>
          <option v-if="expense?.category === 'salary'" value="salary">💸 رواتب (قديم)</option>
        </select>
        <p v-if="form.errors.category" class="text-red-600 text-xs mt-1">{{ form.errors.category }}</p>
      </div>
      <div>
        <label :class="labelClass">ملاحظات</label>
        <textarea v-model="form.notes" :class="[inputClass, 'h-20']" placeholder="رقم الفاتورة أو أي تفاصيل..."></textarea>
      </div>
      <div class="flex justify-end space-x-3 space-x-reverse pt-4">
        <button type="button" @click="$emit('close')" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
        <button type="submit" :disabled="form.processing" :class="primaryBtn">حفظ</button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn } from '@/Components/Admin/ui';

const props = defineProps({ show: Boolean, expense: Object, categories: Array });
const emit = defineEmits(['close']);

const today = () => new Date().toISOString().slice(0, 10);
const form = useForm({ title: '', amount: '', expense_date: today(), category: 'other', notes: '' });

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  const e = props.expense;
  form.title = e?.title ?? '';
  form.amount = e?.amount ?? '';
  form.expense_date = e?.expense_date ?? today();
  form.category = e?.category ?? 'other';
  form.notes = e?.notes ?? '';
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.expense
    ? form.put(route('admin.expenses.update', props.expense.id), options)
    : form.post(route('admin.expenses.store'), options);
};
</script>
