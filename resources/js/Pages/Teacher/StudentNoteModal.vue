<template>
  <Modal :show="true" :title="`✏️ ملاحظات الطلاب — ${batch.name}`" max-width="max-w-2xl" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div v-if="!editingId">
          <label :class="labelClass">الطالب</label>
          <select v-model="form.student_id" :class="inputClass" required>
            <option value="" disabled>-- اختر الطالب --</option>
            <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label :class="labelClass">نوع الملاحظة</label>
          <select v-model="form.type" :class="inputClass" required>
            <option value="academic">أكاديمية (تحصيل)</option>
            <option value="behavioral">سلوكية (انضباط / مشاركة)</option>
          </select>
        </div>
      </div>
      <div>
        <label :class="labelClass">نص الملاحظة</label>
        <textarea v-model="form.content" rows="3" maxlength="1000" :class="inputClass" required></textarea>
      </div>
      <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-red-600">{{ msg }}</p>
      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" :class="primaryBtn">{{ editingId ? 'حفظ التعديل' : 'إضافة الملاحظة' }}</button>
        <button v-if="editingId" type="button" @click="resetForm" :class="secondaryBtn">إلغاء</button>
      </div>
    </form>

    <div class="mt-5 border-t border-ink-line pt-4">
      <p v-if="!notes.length" class="text-sm text-ink-muted text-center py-4">لا توجد ملاحظات بعد.</p>
      <div v-for="n in notes" :key="n.id" class="py-2.5 border-b border-ink-line/40 text-xs">
        <div class="flex items-center justify-between gap-2 mb-1">
          <span class="font-bold text-ink">{{ n.student_name }}</span>
          <span :class="chip(n.type === 'academic' ? 'blue' : 'yellow')">{{ n.type === 'academic' ? 'أكاديمية' : 'سلوكية' }}</span>
          <span class="text-ink-muted flex-1">{{ n.created_at }}</span>
          <button @click="edit(n)" :class="ghostBtn">تعديل</button>
          <button @click="remove(n)" :class="dangerGhostBtn">حذف</button>
        </div>
        <p class="text-ink-soft leading-relaxed">{{ n.content }}</p>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, secondaryBtn, ghostBtn, dangerGhostBtn, chip } from '@/Components/Admin/ui.js';

const props = defineProps({ batch: Object });
defineEmits(['close']);

const students = ref([]);
const notes = ref([]);
const editingId = ref(null);

const form = useForm({ batch_id: props.batch.id, student_id: '', type: 'academic', content: '' });

const load = async () => {
  const [st, nt] = await Promise.all([
    axios.get(route('teacher.attendance.batch', props.batch.id)),
    axios.get(route('teacher.notes.batch', props.batch.id)),
  ]);
  students.value = st.data.students;
  notes.value = nt.data.notes;
};

const resetForm = () => {
  editingId.value = null;
  form.reset('student_id', 'type', 'content');
  form.clearErrors();
};

const edit = (n) => {
  editingId.value = n.id;
  form.type = n.type;
  form.content = n.content;
};

const submit = () => {
  const opts = { preserveScroll: true, onSuccess: () => { resetForm(); load(); } };
  if (editingId.value) {
    form.put(route('teacher.notes.update', editingId.value), opts);
  } else {
    form.post(route('teacher.notes.store'), opts);
  }
};

const remove = (n) => {
  if (!confirm('حذف هذه الملاحظة؟')) return;
  router.delete(route('teacher.notes.destroy', n.id), { preserveScroll: true, onSuccess: load });
};

onMounted(load);
</script>
