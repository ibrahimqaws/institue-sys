<template>
  <Modal :show="show" :title="course ? '✏️ تعديل الكورس' : '✨ إضافة كورس جديد'" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label :class="labelClass">عنوان الكورس</label>
        <input v-model="form.title" type="text" :class="inputClass" placeholder="مثال: دبلومة تطوير الويب الكاملة" required />
        <p v-if="form.errors.title" class="text-red-600 text-xs mt-1">{{ form.errors.title }}</p>
      </div>
      <div>
        <label :class="labelClass">الوصف</label>
        <textarea v-model="form.description" :class="[inputClass, 'h-24']" placeholder="تفاصيل المنهج..."></textarea>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">السعر ($)</label>
          <input v-model="form.price" type="number" min="0" step="0.01" :class="inputClass" required />
          <p v-if="form.errors.price" class="text-red-600 text-xs mt-1">{{ form.errors.price }}</p>
        </div>
        <div>
          <label :class="labelClass">عدد الساعات</label>
          <input v-model="form.duration_hours" type="number" min="1" :class="inputClass" required />
          <p v-if="form.errors.duration_hours" class="text-red-600 text-xs mt-1">{{ form.errors.duration_hours }}</p>
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

const props = defineProps({ show: Boolean, course: Object });
const emit = defineEmits(['close']);

const form = useForm({ title: '', description: '', price: 0, duration_hours: 1 });

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  form.title = props.course?.title ?? '';
  form.description = props.course?.description ?? '';
  form.price = props.course?.price ?? 0;
  form.duration_hours = props.course?.duration_hours ?? 1;
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.course
    ? form.put(route('admin.courses.update', props.course.id), options)
    : form.post(route('admin.courses.store'), options);
};
</script>
