<template>
  <AdminLayout>
    <Head title="طلبات التسجيل" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">طلبات التسجيل في <span class="edu-underline">المجموعات</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">الموافقة على طلبات الطلاب أو تسجيلهم يدوياً في المجموعات.</p>
      </div>
      <button @click="showAdd = true" :class="primaryBtn">+ تسجيل طالب في مجموعة</button>
    </header>

    <div class="edu-bar flex flex-wrap gap-2 mb-6">
      <button v-for="t in tabs" :key="t.key" @click="router.get(route('admin.enrollments.index'), { status: t.key })"
        :class="[status === t.key ? 'bg-edu-600 text-white font-bold border-edu-600' : 'bg-white text-ink-soft hover:bg-edu-50 border-ink-line']"
        class="px-5 py-2 rounded-lg text-sm transition border">
        {{ t.label }} <span class="opacity-70">({{ counts[t.key] || 0 }})</span>
      </button>
    </div>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">الطالب</th>
            <th class="p-4">المجموعة / الكورس</th>
            <th class="p-4">الرسوم</th>
            <th class="p-4">تاريخ الطلب</th>
            <th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="e in enrollments.data" :key="e.id" class="hover:bg-edu-50">
            <td class="p-4">
              <div class="font-bold text-ink">{{ e.student?.name }}</div>
              <div class="text-xs text-ink-muted">{{ e.student?.email }}</div>
            </td>
            <td class="p-4 text-ink-soft">
              {{ e.batch?.name }}
              <div class="text-xs text-edu-700">{{ e.batch?.course?.title }}</div>
            </td>
            <td class="p-4 text-amber-700 font-bold">{{ money(e.batch?.course?.price) }}</td>
            <td class="p-4 text-xs text-ink-muted font-mono">{{ e.enrollment_date }}</td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <template v-if="e.status === 'pending'">
                <button @click="setStatus(e, 'active')" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-lg text-xs font-semibold">موافقة</button>
                <button @click="setStatus(e, 'dropped')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-semibold">رفض</button>
              </template>
              <template v-else-if="e.status === 'active'">
                <button @click="setStatus(e, 'completed')" class="px-3 py-1.5 bg-sky-50 text-sky-700 rounded-lg text-xs font-semibold">إنهاء</button>
                <button @click="setStatus(e, 'dropped')" class="px-3 py-1.5 bg-sun-soft text-amber-700 rounded-lg text-xs font-semibold">سحب</button>
              </template>
              <template v-else>
                <button @click="setStatus(e, 'active')" class="px-3 py-1.5 bg-edu-100 text-edu-700 rounded-lg text-xs font-semibold">إعادة تفعيل</button>
              </template>
              <button @click="remove(e)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-semibold">حذف</button>
            </td>
          </tr>
          <tr v-if="enrollments.data.length === 0">
            <td colspan="5" class="p-8 text-center text-ink-muted">لا توجد سجلات بهذه الحالة.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination :links="enrollments.links" />

    <Modal :show="showAdd" title="📝 تسجيل طالب في مجموعة" @close="showAdd = false">
      <form @submit.prevent="add" class="space-y-4">
        <div>
          <label :class="labelClass">الطالب</label>
          <select v-model="form.student_id" :class="inputClass" required>
            <option value="" disabled>اختر الطالب</option>
            <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
          </select>
          <p v-if="form.errors.student_id" class="text-red-600 text-xs mt-1">{{ form.errors.student_id }}</p>
        </div>
        <div>
          <label :class="labelClass">المجموعة</label>
          <select v-model="form.batch_id" :class="inputClass" required>
            <option value="" disabled>اختر المجموعة</option>
            <option v-for="b in batches" :key="b.id" :value="b.id">{{ b.name }} — {{ b.course?.title }}</option>
          </select>
          <p v-if="form.errors.batch_id" class="text-red-600 text-xs mt-1">{{ form.errors.batch_id }}</p>
        </div>
        <div class="flex justify-end space-x-3 space-x-reverse pt-2">
          <button type="button" @click="showAdd = false" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
          <button type="submit" :disabled="form.processing" :class="primaryBtn">تسجيل</button>
        </div>
      </form>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Admin/Modal.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import { inputClass, labelClass, primaryBtn, cardClass, money, pageTitle, pageSubtitle } from '@/Components/Admin/ui';

defineProps({ enrollments: Object, status: String, counts: Object, students: Array, batches: Array });

const tabs = [
  { key: 'pending', label: '⏳ معلّقة' },
  { key: 'active', label: '✅ فعّالة' },
  { key: 'completed', label: '🎓 منتهية' },
  { key: 'dropped', label: '🚫 منسحبة/مرفوضة' },
];

const showAdd = ref(false);
const form = useForm({ student_id: '', batch_id: '' });

const add = () => form.post(route('admin.enrollments.store'), {
  preserveScroll: true,
  onSuccess: () => { form.reset(); showAdd.value = false; },
});
const setStatus = (e, status) => router.put(route('admin.enrollments.update', e.id), { status }, { preserveScroll: true });
const remove = (e) => confirm('حذف هذا السجل؟') && router.delete(route('admin.enrollments.destroy', e.id), { preserveScroll: true });
</script>
