<template>
  <Modal :show="true" :title="`📋 رصد الحضور — ${batch.name}`" max-width="max-w-3xl" @close="$emit('close')">
    <div class="flex flex-wrap items-end gap-3 mb-4">
      <div>
        <label :class="labelClass">التاريخ</label>
        <input v-model="date" type="date" :max="todayStr" :class="inputClass" @change="load" />
      </div>
      <div class="flex gap-2 mb-0.5">
        <button type="button" @click="markAll('present')" class="px-3 py-2 bg-edu-100 hover:bg-edu-200 text-edu-700 text-xs font-bold rounded-lg border border-edu-200 transition">الكل حاضر</button>
        <button type="button" @click="markAll('absent')" class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-lg border border-red-100 transition">الكل غائب</button>
      </div>
      <p v-if="savedBefore" class="text-xs font-bold text-amber-700 bg-sun-soft rounded-md px-2.5 py-1.5 mb-0.5">يوجد سجل محفوظ لهذا التاريخ — الحفظ سيعدّله.</p>
    </div>

    <p v-if="loading" class="text-sm text-ink-muted py-6 text-center">جاري التحميل...</p>
    <p v-else-if="!students.length" class="text-sm text-ink-muted py-6 text-center">لا يوجد طلاب مسجلون في هذه المجموعة.</p>

    <div v-else :class="tableWrap">
      <table :class="tableClass">
        <thead :class="theadClass">
          <tr>
            <th :class="[thClass, 'rounded-s-lg']">الطالب</th>
            <th :class="[thClass, 'text-center']">الحالة</th>
            <th :class="[thClass, 'rounded-e-lg']">ملاحظة</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in students" :key="s.id" :class="trClass">
            <td :class="[tdClass, 'text-ink font-bold']">{{ s.name }}</td>
            <td :class="[tdClass, 'text-center']">
              <div class="inline-flex gap-1 bg-edu-50 p-1 rounded-lg border border-ink-line">
                <button v-for="o in options" :key="o.value" type="button" @click="records[s.id].status = o.value"
                  :class="['px-3 py-1 rounded-lg text-xs font-bold transition', records[s.id].status === o.value ? o.active : 'text-ink-muted hover:text-ink hover:bg-white']">
                  {{ o.label }}
                </button>
              </div>
            </td>
            <td :class="tdClass"><input v-model="records[s.id].remarks" maxlength="255" placeholder="اختياري" :class="[inputClass, '!p-2 !text-xs']" /></td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="error" class="text-xs text-red-600 mt-3">{{ error }}</p>

    <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-ink-line">
      <button type="button" @click="$emit('close')" :class="secondaryBtn">إغلاق</button>
      <button type="button" :disabled="saving || !students.length" @click="save" :class="primaryBtn">{{ saving ? 'جاري الحفظ...' : 'حفظ الحضور' }}</button>
    </div>
  </Modal>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn, secondaryBtn, tableWrap, tableClass, theadClass, thClass, trClass, tdClass } from '@/Components/Admin/ui.js';

const props = defineProps({ batch: Object });
const emit = defineEmits(['close']);

const todayStr = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
const date = ref(todayStr);
const students = ref([]);
const records = reactive({});
const savedBefore = ref(false);
const loading = ref(false);
const saving = ref(false);
const error = ref('');

const options = [
  { value: 'present', label: 'حاضر', active: 'bg-edu-600 text-white' },
  { value: 'absent', label: 'غائب', active: 'bg-red-600 text-white' },
  { value: 'late', label: 'متأخر', active: 'bg-amber-700 text-white' },
];

const load = async () => {
  if (!date.value) return;
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.get(route('teacher.attendance.batch', props.batch.id), { params: { date: date.value } });
    students.value = data.students;
    Object.keys(records).forEach((k) => delete records[k]);
    const saved = data.attendance || {};
    savedBefore.value = Object.keys(saved).length > 0;
    data.students.forEach((s) => {
      records[s.id] = { status: saved[s.id]?.status || 'present', remarks: saved[s.id]?.remarks || '' };
    });
  } catch (e) {
    error.value = e.response?.data?.message || 'تعذر تحميل بيانات الحضور.';
  } finally {
    loading.value = false;
  }
};

const markAll = (status) => students.value.forEach((s) => (records[s.id].status = status));

const save = () => {
  saving.value = true;
  router.post(route('teacher.attendance.store'), { batch_id: props.batch.id, date: date.value, records }, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onError: (e) => (error.value = Object.values(e)[0]),
    onFinish: () => (saving.value = false),
  });
};

onMounted(load);
</script>
