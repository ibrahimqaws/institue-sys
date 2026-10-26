<template>
  <Modal :show="show" :title="classroom ? '✏️ تعديل القاعة' : '🏛️ إضافة قاعة / مختبر'" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label :class="labelClass">اسم القاعة / المختبر</label>
        <input v-model="form.name" type="text" :class="inputClass" placeholder="مثال: مختبر الحاسوب 1" required />
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">السعة (عدد الطلاب)</label>
          <input v-model="form.capacity" type="number" min="1" :class="inputClass" required />
          <p v-if="form.errors.capacity" class="text-red-600 text-xs mt-1">{{ form.errors.capacity }}</p>
        </div>
        <div>
          <label :class="labelClass">نوع القاعة</label>
          <select v-model="form.type" :class="inputClass">
            <option value="lab">🖥️ مختبر حاسوب</option>
            <option value="hall">🏫 قاعة محاضرات</option>
            <option value="lecture">📖 قاعة دراسية</option>
            <option value="workshop">🛠️ ورشة عمل</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end space-x-3 space-x-reverse pt-4">
        <button type="button" @click="$emit('close')" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
        <button type="submit" :disabled="form.processing" :class="primaryBtn">تأكيد الحفظ</button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn } from '@/Components/Admin/ui';

const props = defineProps({ show: Boolean, classroom: Object });
const emit = defineEmits(['close']);

const form = useForm({ name: '', capacity: 20, type: 'lab' });

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  form.name = props.classroom?.name ?? '';
  form.capacity = props.classroom?.capacity ?? 20;
  form.type = props.classroom?.type ?? 'lab';
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.classroom
    ? form.put(route('admin.classrooms.update', props.classroom.id), options)
    : form.post(route('admin.classrooms.store'), options);
};
</script>
