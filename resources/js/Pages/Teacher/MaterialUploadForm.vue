<template>
  <Modal :show="true" :title="`📁 المواد والواجبات — ${batch.name}`" max-width="max-w-3xl" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div v-if="!editingId">
          <label :class="labelClass">النوع</label>
          <select v-model="form.type" :class="inputClass" required>
            <option value="file">مادة علمية</option>
            <option value="assignment">واجب</option>
          </select>
        </div>
        <div :class="editingId ? 'sm:col-span-3' : 'sm:col-span-2'">
          <label :class="labelClass">العنوان</label>
          <input v-model="form.title" :class="inputClass" required />
        </div>
      </div>
      <div>
        <label :class="labelClass">الوصف والتعليمات</label>
        <textarea v-model="form.description" rows="2" :class="inputClass"></textarea>
      </div>
      <div v-if="currentType === 'assignment'">
        <label :class="labelClass">آخر موعد للتسليم</label>
        <input v-model="form.due_date" type="datetime-local" :class="inputClass" />
      </div>
      <div>
        <label :class="labelClass">{{ editingId ? 'استبدال الملف (اختياري)' : 'الملف (PDF, Doc, Zip, صور — حتى 10MB)' }}</label>
        <input :key="fileKey" type="file" @change="(e) => (form.file = e.target.files[0])" class="w-full text-xs text-ink-muted file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-edu-600 hover:file:bg-edu-700 file:text-white" />
      </div>
      <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-red-600">{{ msg }}</p>
      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" :class="primaryBtn">{{ editingId ? 'حفظ التعديل' : 'رفع الآن' }}</button>
        <button v-if="editingId" type="button" @click="resetForm" :class="secondaryBtn">إلغاء</button>
      </div>
    </form>

    <div class="mt-5 border-t border-ink-line pt-4">
      <p v-if="loading" class="text-sm text-ink-muted text-center py-4">جاري التحميل...</p>
      <p v-else-if="!materials.length" class="text-sm text-ink-muted text-center py-4">لم تُرفع أي مواد لهذه المجموعة بعد.</p>
      <div v-for="m in materials" :key="m.id" class="flex items-center justify-between gap-2 py-2 border-b border-ink-line/40 text-xs">
        <div class="flex-1 min-w-0">
          <div class="text-ink font-bold truncate">{{ m.type === 'assignment' ? '📝' : '📄' }} {{ m.title }}</div>
          <div class="text-ink-muted">
            {{ m.created_at }}<span v-if="m.has_file"> • 📎 {{ m.file_name }}</span><span v-if="m.due_date"> • التسليم: {{ m.due_date.replace('T', ' ') }}</span>
          </div>
        </div>
        <button @click="edit(m)" :class="ghostBtn">تعديل</button>
        <button @click="remove(m)" :class="dangerGhostBtn">حذف</button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, secondaryBtn, ghostBtn, dangerGhostBtn } from '@/Components/Admin/ui.js';

const props = defineProps({ batch: Object });
defineEmits(['close']);

const materials = ref([]);
const loading = ref(false);
const editingId = ref(null);
const editingType = ref(null);
const fileKey = ref(0);

const form = useForm({ batch_id: props.batch.id, type: 'file', title: '', description: '', due_date: '', file: null });
const currentType = computed(() => (editingId.value ? editingType.value : form.type));

const load = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(route('teacher.materials.batch', props.batch.id));
    materials.value = data.materials;
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  editingId.value = null;
  editingType.value = null;
  form.reset('type', 'title', 'description', 'due_date', 'file');
  form.clearErrors();
  fileKey.value++;
};

const edit = (m) => {
  editingId.value = m.id;
  editingType.value = m.type;
  form.title = m.title;
  form.description = m.description || '';
  form.due_date = m.due_date || '';
  form.file = null;
  fileKey.value++;
};

const submit = () => {
  const opts = { preserveScroll: true, forceFormData: true, onSuccess: () => { resetForm(); load(); } };
  if (editingId.value) {
    form.post(route('teacher.materials.update', editingId.value), opts);
  } else {
    form.post(route('teacher.materials.store'), opts);
  }
};

const remove = (m) => {
  if (!confirm('حذف هذه المادة (والملف المرفق) نهائياً؟')) return;
  router.delete(route('teacher.materials.destroy', m.id), { preserveScroll: true, onSuccess: load });
};

onMounted(load);
</script>
