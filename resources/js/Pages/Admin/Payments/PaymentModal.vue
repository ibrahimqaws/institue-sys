<template>
  <Modal :show="show" :title="payment ? '✏️ تعديل الدفعة' : '💳 تسجيل دفعة قسط'" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div v-if="!payment">
        <label :class="labelClass">الطالب / المجموعة (تسجيل نشط)</label>
        <select v-model="selected" :class="inputClass" required>
          <option value="" disabled>اختر الطالب والمجموعة</option>
          <option v-for="b in balances" :key="key(b)" :value="key(b)">
            {{ b.student_name }} — {{ b.batch_name }} (المتبقي {{ money(b.remaining) }})
          </option>
        </select>
        <p v-if="form.errors.batch_id || form.errors.student_id" class="text-red-600 text-xs mt-1">{{ form.errors.batch_id || form.errors.student_id }}</p>
        <p v-if="current" class="text-xs text-ink-muted mt-2">
          الرسوم {{ money(current.fee) }} · المدفوع {{ money(current.paid) }} · <b class="text-amber-700">المتبقي {{ money(current.remaining) }}</b>
        </p>
      </div>
      <p v-else class="text-sm text-ink-soft">سند <b class="font-mono text-amber-700">#{{ payment.receipt_number }}</b> — {{ payment.student?.name }}</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">المبلغ ($)</label>
          <input v-model="form.amount_paid" type="number" min="0.01" step="0.01" :class="inputClass" required />
          <p v-if="form.errors.amount_paid" class="text-red-600 text-xs mt-1">{{ form.errors.amount_paid }}</p>
        </div>
        <div v-if="!payment">
          <label :class="labelClass">رقم السند (يُولَّد تلقائياً إن تُرك فارغاً)</label>
          <input v-model="form.receipt_number" type="text" :class="inputClass" placeholder="REC-1002" dir="ltr" />
          <p v-if="form.errors.receipt_number" class="text-red-600 text-xs mt-1">{{ form.errors.receipt_number }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">تاريخ الدفع</label>
          <input v-model="form.payment_date" type="date" :class="inputClass" required />
        </div>
        <div>
          <label :class="labelClass">وسيلة الدفع</label>
          <select v-model="form.payment_method" :class="inputClass">
            <option value="cash">💵 نقداً</option>
            <option value="transfer">🏦 تحويل بنكي</option>
            <option value="card">💳 بطاقة</option>
          </select>
        </div>
      </div>

      <div>
        <label :class="labelClass">ملاحظات</label>
        <textarea v-model="form.notes" :class="[inputClass, 'h-20']" placeholder="دفعة أولى / القسط الثاني..."></textarea>
      </div>

      <div class="flex justify-end space-x-3 space-x-reverse pt-4">
        <button type="button" @click="$emit('close')" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
        <button type="submit" :disabled="form.processing" :class="primaryBtn">حفظ الدفعة</button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, money } from '@/Components/Admin/ui';

const props = defineProps({ show: Boolean, payment: Object, preset: Object, balances: Array });
const emit = defineEmits(['close']);

const today = () => new Date().toISOString().slice(0, 10);
const key = (b) => `${b.student_id}-${b.batch_id}`;

const selected = ref('');
const form = useForm({
  student_id: '', batch_id: '', amount_paid: '', payment_date: today(),
  payment_method: 'cash', receipt_number: '', notes: '',
});

const current = computed(() => props.balances.find((b) => key(b) === selected.value));

watch(selected, (k) => {
  const b = props.balances.find((x) => key(x) === k);
  if (b && !props.payment) {
    form.student_id = b.student_id;
    form.batch_id = b.batch_id;
    form.amount_paid = b.remaining;
  }
});

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  const p = props.payment;
  selected.value = p ? '' : (props.preset ? key(props.preset) : '');
  form.student_id = p?.student_id ?? props.preset?.student_id ?? '';
  form.batch_id = p?.batch_id ?? props.preset?.batch_id ?? '';
  form.amount_paid = p?.amount_paid ?? props.preset?.remaining ?? '';
  form.payment_date = p?.payment_date ?? today();
  form.payment_method = p?.payment_method ?? 'cash';
  form.receipt_number = '';
  form.notes = p?.notes ?? '';
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.payment
    ? form.put(route('admin.payments.update', props.payment.id), options)
    : form.post(route('admin.payments.store'), options);
};
</script>
