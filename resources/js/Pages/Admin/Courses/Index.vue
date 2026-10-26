<template>
  <AdminLayout>
    <Head title="الكورسات" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">إدارة الكورسات <span class="edu-underline">والدورات</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">تحديد المواد والأسعار وعدد الساعات التدريبية.</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">+ إضافة كورس جديد</button>
    </header>

    <div v-if="courses.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <article v-for="course in courses" :key="course.id" :class="[cardClass, 'p-3 flex flex-col hover:shadow-pop transition']">
        
        <div class="flex h-36 items-center justify-center rounded-xl bg-edu-100 text-edu-700">
          <svg viewBox="0 0 24 24" class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.5C10.5 5 8 4.5 4 4.5v13c4 0 6.5.5 8 2 1.5-1.5 4-2 8-2v-13c-4 0-6.5.5-8 2z"/><path d="M12 6.5v13"/></svg>
        </div>

        <div class="flex flex-1 flex-col px-2 pt-4">
          <h3 class="font-black text-ink leading-snug">{{ course.title }}</h3>
          <p class="mt-1.5 text-xs leading-relaxed text-ink-muted line-clamp-2 min-h-[2.5rem]">{{ course.description || 'لا يوجد وصف لهذا الكورس.' }}</p>

          <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-ink-soft">
            <span class="text-edu-700">⏱</span><span>{{ course.duration_hours }} ساعة تدريبية</span>
          </div>

          
          <div class="mt-4 rounded-lg bg-edu-100 px-4 py-3 text-lg font-black text-edu-700">{{ money(course.price) }}</div>

          <div class="mt-4 flex justify-end gap-2 pb-2">
            <button @click="openEdit(course)" :class="ghostBtn">تعديل</button>
            <button @click="remove(course)" :class="dangerGhostBtn">حذف</button>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="p-12 text-center text-ink-muted bg-white rounded-card border border-ink-line">
      لا توجد كورسات مسجلة حالياً. أضف كورسك الأول!
    </div>

    <CourseModal :show="showModal" :course="editing" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourseModal from './CourseModal.vue';
import { primaryBtn, cardClass, money, pageTitle, pageSubtitle, ghostBtn, dangerGhostBtn } from '@/Components/Admin/ui';

defineProps({ courses: Array });

const showModal = ref(false);
const editing = ref(null);

const openCreate = () => { editing.value = null; showModal.value = true; };
const openEdit = (c) => { editing.value = c; showModal.value = true; };
const remove = (c) => confirm(`حذف الكورس «${c.title}»؟`) && router.delete(route('admin.courses.destroy', c.id), { preserveScroll: true });
</script>
