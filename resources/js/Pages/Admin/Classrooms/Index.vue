<template>
  <AdminLayout>
    <Head title="القاعات والمختبرات" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">إدارة القاعات <span class="edu-underline">والمختبرات</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">تحديد السعة الاستيعابية للقاعات والمختبرات وتصنيفها.</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">+ إضافة قاعة جديدة</button>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div v-for="classroom in classrooms" :key="classroom.id"
        :class="[cardClass, 'p-5 flex flex-col justify-between hover:shadow-pop transition']">
        <div>
          <div class="flex justify-between items-start mb-4">
            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-edu-100 text-xl">{{ icons[classroom.type] || '🏫' }}</span>
            <span :class="chip('green')">{{ labels[classroom.type] || classroom.type }}</span>
          </div>
          <h3 class="text-lg font-black text-ink mb-3">{{ classroom.name }}</h3>
          <div class="flex items-center justify-between rounded-lg bg-edu-100 px-4 py-2.5 text-sm">
            <span class="text-ink-soft font-semibold">👥 السعة</span>
            <span class="font-black text-edu-700">{{ classroom.capacity }} طالب</span>
          </div>
        </div>
        <div class="mt-5 pt-4 border-t border-ink-line flex justify-end gap-2">
          <button @click="openEdit(classroom)" :class="ghostBtn">تعديل</button>
          <button @click="remove(classroom)" :class="dangerGhostBtn">حذف</button>
        </div>
      </div>
    </div>

    <div v-if="classrooms.length === 0" class="p-12 text-center text-ink-muted bg-white rounded-card border border-ink-line">
      لا توجد قاعات مضافة حالياً. أنشئ القاعة الأولى لمعهدك!
    </div>

    <ClassroomModal :show="showModal" :classroom="editing" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClassroomModal from './ClassroomModal.vue';
import { primaryBtn, cardClass, pageTitle, pageSubtitle, chip, ghostBtn, dangerGhostBtn } from '@/Components/Admin/ui';

defineProps({ classrooms: Array });

const icons = { lab: '🖥️', hall: '🏫', lecture: '📖', workshop: '🛠️' };
const labels = { lab: 'مختبر', hall: 'قاعة محاضرات', lecture: 'قاعة دراسية', workshop: 'ورشة' };

const showModal = ref(false);
const editing = ref(null);

const openCreate = () => { editing.value = null; showModal.value = true; };
const openEdit = (c) => { editing.value = c; showModal.value = true; };
const remove = (c) => confirm(`حذف القاعة «${c.name}»؟`) && router.delete(route('admin.classrooms.destroy', c.id), { preserveScroll: true });
</script>
