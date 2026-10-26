<template>
  <Modal :show="show" :title="batch ? '✏️ تعديل المجموعة' : '👥 إضافة مجموعة دراسية'" max-width="max-w-2xl" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div v-if="form.errors.schedule" class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ form.errors.schedule }}</div>

      <div>
        <label :class="labelClass">اسم المجموعة</label>
        <input v-model="form.name" type="text" :class="inputClass" placeholder="مثال: الدفعة الأولى - شعبة A" required />
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label :class="labelClass">الكورس</label>
          <select v-model="form.course_id" :class="inputClass" required>
            <option value="" disabled>اختر الكورس</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
          </select>
          <p v-if="form.errors.course_id" class="text-red-600 text-xs mt-1">{{ form.errors.course_id }}</p>
        </div>
        <div>
          <label :class="labelClass">المدرس</label>
          <select v-model="form.teacher_id" :class="inputClass" required>
            <option value="" disabled>اختر المدرس</option>
            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
          <p v-if="form.errors.teacher_id" class="text-red-600 text-xs mt-1">{{ form.errors.teacher_id }}</p>
        </div>
        <div>
          <label :class="labelClass">القاعة (اختياري)</label>
          <select v-model="form.classroom_id" :class="inputClass">
            <option value="">بدون قاعة</option>
            <option v-for="r in classrooms" :key="r.id" :value="r.id">{{ r.name }} ({{ r.capacity }})</option>
          </select>
        </div>
      </div>

      <div>
        <label :class="labelClass">أيام المحاضرات</label>
        <div class="flex flex-wrap gap-2">
          <label v-for="(label, key) in days" :key="key"
            :class="['px-3 py-1.5 rounded-lg text-xs border cursor-pointer select-none transition', form.days.includes(key) ? 'bg-edu-600 border-edu-600 text-white font-bold' : 'bg-white border-ink-line text-ink-soft hover:bg-edu-50']">
            <input type="checkbox" class="hidden" :value="key" v-model="form.days" /> {{ label }}
          </label>
        </div>
        <p v-if="form.errors.days" class="text-red-600 text-xs mt-1">{{ form.errors.days }}</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
          <label :class="labelClass">من الساعة</label>
          <input v-model="form.start_time" type="time" :class="inputClass" required />
          <p v-if="form.errors.start_time" class="text-red-600 text-xs mt-1">{{ form.errors.start_time }}</p>
        </div>
        <div>
          <label :class="labelClass">إلى الساعة</label>
          <input v-model="form.end_time" type="time" :class="inputClass" required />
          <p v-if="form.errors.end_time" class="text-red-600 text-xs mt-1">{{ form.errors.end_time }}</p>
        </div>
        <div>
          <label :class="labelClass">تاريخ البداية</label>
          <input v-model="form.start_date" type="date" :class="inputClass" required />
          <p v-if="form.errors.start_date" class="text-red-600 text-xs mt-1">{{ form.errors.start_date }}</p>
        </div>
        <div>
          <label :class="labelClass">تاريخ النهاية</label>
          <input v-model="form.end_date" type="date" :class="inputClass" />
          <p v-if="form.errors.end_date" class="text-red-600 text-xs mt-1">{{ form.errors.end_date }}</p>
        </div>
      </div>

      <div v-if="batch">
        <label :class="labelClass">الحالة</label>
        <select v-model="form.status" :class="inputClass">
          <option value="active">نشطة</option>
          <option value="completed">منتهية</option>
          <option value="cancelled">ملغاة</option>
        </select>
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

const props = defineProps({ show: Boolean, batch: Object, courses: Array, teachers: Array, classrooms: Array, days: Object });
const emit = defineEmits(['close']);

const form = useForm({
  name: '', course_id: '', teacher_id: '', classroom_id: '',
  days: [], start_time: '', end_time: '', start_date: '', end_date: '', status: 'active',
});

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  const b = props.batch;
  form.name = b?.name ?? '';
  form.course_id = b?.course_id ?? '';
  form.teacher_id = b?.teacher_id ?? '';
  form.classroom_id = b?.classroom_id ?? '';
  form.days = b?.days ? b.days.split(',').filter(Boolean) : [];
  form.start_time = b?.start_time ? b.start_time.slice(0, 5) : '';
  form.end_time = b?.end_time ? b.end_time.slice(0, 5) : '';
  form.start_date = b?.start_date ?? '';
  form.end_date = b?.end_date ?? '';
  form.status = b?.status ?? 'active';
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.batch
    ? form.put(route('admin.batches.update', props.batch.id), options)
    : form.post(route('admin.batches.store'), options);
};
</script>
