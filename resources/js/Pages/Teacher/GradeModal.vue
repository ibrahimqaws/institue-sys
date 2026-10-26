<template>
  <Modal :show="true" :title="`🎯 الدرجات — ${batch.name}`" max-width="max-w-3xl" @close="$emit('close')">
    <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end mb-2">
      <div v-if="!editingId" class="sm:col-span-2">
        <label :class="labelClass">الطالب</label>
        <select v-model="form.student_id" :class="inputClass" required>
          <option value="" disabled>-- اختر الطالب --</option>
          <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div :class="editingId ? 'sm:col-span-4' : 'sm:col-span-2'">
        <label :class="labelClass">اسم التقييم / الاختبار</label>
        <input v-model="form.title" :class="inputClass" placeholder="مثال: اختبار منتصف الفصل" required />
      </div>
      <div>
        <label :class="labelClass">الدرجة</label>
        <input v-model="form.score" type="number" step="0.5" min="0" :class="inputClass" required />
      </div>
      <div>
        <label :class="labelClass">العظمى</label>
        <input v-model="form.max_score" type="number" step="0.5" min="1" :class="inputClass" required />
      </div>
      <div class="sm:col-span-2 flex gap-2">
        <button type="submit" :disabled="form.processing" :class="primaryBtn">{{ editingId ? 'حفظ التعديل' : 'إضافة الدرجة' }}</button>
        <button v-if="editingId" type="button" @click="resetForm" :class="secondaryBtn">إلغاء</button>
      </div>
    </form>
    <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-red-600">{{ msg }}</p>

    <div class="mt-5 border-t border-ink-line pt-4">
      <p v-if="loading" class="text-sm text-ink-muted text-center py-4">جاري التحميل...</p>
      <p v-else-if="!students.length" class="text-sm text-ink-muted text-center py-4">لا يوجد طلاب مسجلون في هذه المجموعة.</p>
      <div v-else class="space-y-4">
        <div v-for="s in students" :key="s.id">
          <h4 class="text-sm font-bold text-edu-700 mb-1">{{ s.name }}</h4>
          <p v-if="!s.grades.length" class="text-xs text-ink-muted">لا توجد درجات بعد.</p>
          <div v-for="g in s.grades" :key="g.id" class="flex items-center justify-between gap-2 py-1.5 border-b border-ink-line/40 text-xs">
            <span class="text-ink flex-1 truncate">{{ g.title || g.exam_name }}</span>
            <span class="font-mono text-ink-soft">{{ Number(g.score ?? g.student_score) }} / {{ Number(g.max_score) }}</span>
            <span class="text-ink-muted w-12 text-end">{{ g.percentage }}%</span>
            <button @click="edit(s, g)" :class="ghostBtn">تعديل</button>
            <button @click="remove(g)" :class="dangerGhostBtn">حذف</button>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, secondaryBtn, ghostBtn, dangerGhostBtn } from '@/Components/Admin/ui.js';

const props = defineProps({ batch: Object });
defineEmits(['close']);

const students = ref([]);
const loading = ref(false);
const editingId = ref(null);

const form = useForm({ batch_id: props.batch.id, student_id: '', title: '', score: '', max_score: 100 });

const load = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(route('teacher.grades.batch', props.batch.id));
    students.value = data.students;
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  editingId.value = null;
  form.reset('student_id', 'title', 'score');
  form.clearErrors();
};

const edit = (student, g) => {
  editingId.value = g.id;
  form.student_id = student.id;
  form.title = g.title || g.exam_name;
  form.score = Number(g.score ?? g.student_score);
  form.max_score = Number(g.max_score);
};

const submit = () => {
  const opts = { preserveScroll: true, onSuccess: () => { resetForm(); load(); } };
  if (editingId.value) {
    form.put(route('teacher.grades.update', editingId.value), opts);
  } else {
    form.post(route('teacher.grades.store'), opts);
  }
};

const remove = (g) => {
  if (!confirm('حذف هذه الدرجة نهائياً؟')) return;
  router.delete(route('teacher.grades.destroy', g.id), { preserveScroll: true, onSuccess: load });
};

onMounted(load);
</script>
