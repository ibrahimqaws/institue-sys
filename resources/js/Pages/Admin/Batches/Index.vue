<template>
  <AdminLayout>
    <Head title="المجموعات الدراسية" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">إدارة المجموعات والشُعب <span class="edu-underline">الدراسية</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">ربط المجموعة بالكورس والمدرس والقاعة والتوقيت، مع منع التعارض تلقائياً.</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">+ إضافة مجموعة</button>
    </header>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">المجموعة</th>
            <th class="p-4">الكورس</th>
            <th class="p-4">المدرس</th>
            <th class="p-4">القاعة</th>
            <th class="p-4">الجدول</th>
            <th class="p-4">الطلاب</th>
            <th class="p-4">الحالة</th>
            <th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="b in batches" :key="b.id" class="hover:bg-edu-50">
            <td class="p-4 font-bold text-ink">{{ b.name }}</td>
            <td class="p-4 text-edu-700">{{ b.course?.title || '—' }}</td>
            <td class="p-4 text-ink-soft">{{ b.teacher?.name || '—' }}</td>
            <td class="p-4 text-ink-soft">{{ b.classroom?.name || 'بدون قاعة' }}</td>
            <td class="p-4 text-xs text-ink-soft">
              {{ b.schedule_label || '—' }}
              <div class="text-ink-muted font-mono mt-1">{{ b.start_date }} ← {{ b.end_date || '...' }}</div>
            </td>
            <td class="p-4 text-ink-soft">
              {{ b.students_count }}<span v-if="b.classroom"> / {{ b.classroom.capacity }}</span>
            </td>
            <td class="p-4">
              <span :class="chip(statusTone[b.status] || 'gray')">{{ statusLabel[b.status] || b.status }}</span>
            </td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <button @click="openEdit(b)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">تعديل</button>
              <button @click="remove(b)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-md text-xs font-bold">حذف</button>
            </td>
          </tr>
          <tr v-if="batches.length === 0">
            <td colspan="8" class="p-8 text-center text-ink-muted">لا توجد مجموعات دراسية حالياً.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <BatchModal :show="showModal" :batch="editing" :courses="courses" :teachers="teachers" :classrooms="classrooms" :days="days" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BatchModal from './BatchModal.vue';
import { primaryBtn, cardClass, pageTitle, pageSubtitle, chip } from '@/Components/Admin/ui';

defineProps({ batches: Array, courses: Array, teachers: Array, classrooms: Array, days: Object });

const statusLabel = { active: 'نشطة', completed: 'منتهية', cancelled: 'ملغاة' };
const statusTone = { active: 'green', completed: 'gray', cancelled: 'red' };
const showModal = ref(false);
const editing = ref(null);

const openCreate = () => { editing.value = null; showModal.value = true; };
const openEdit = (b) => { editing.value = b; showModal.value = true; };
const remove = (b) => confirm(`حذف المجموعة «${b.name}»؟`) && router.delete(route('admin.batches.destroy', b.id), { preserveScroll: true });
</script>
