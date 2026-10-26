<template>
  <Head title="لوحة المدرس" />
  <PanelLayout>
    <div class="max-w-7xl mx-auto w-full">
      <header class="mb-6">
        <h1 :class="pageTitle">مرحباً <span class="edu-underline">{{ $page.props.auth.user.name }}</span></h1>
        <p :class="pageSubtitle">جدول محاضراتك ومجموعاتك، الحضور والغياب، الدرجات، المواد والواجبات، والملاحظات.</p>
      </header>

      
      <ScheduleWidget :batches="batches" :day-names="dayNames" />

      
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
        <BatchCard
          v-for="batch in batches"
          :key="batch.id"
          :batch="batch"
          @open-attendance="selected = { type: 'attendance', batch: $event }"
          @open-grades="selected = { type: 'grades', batch: $event }"
          @open-material="selected = { type: 'material', batch: $event }"
          @open-notes="selected = { type: 'notes', batch: $event }"
        />
        <div v-if="!batches.length" :class="[cardClass, 'col-span-full p-12 text-center text-ink-muted text-sm']">
          لا توجد مجموعات دراسية مرتبطة بحسابك حالياً.
        </div>
      </div>

      <AttendanceTable v-if="selected?.type === 'attendance'" :batch="selected.batch" @close="selected = null" />
      <GradeModal v-if="selected?.type === 'grades'" :batch="selected.batch" @close="selected = null" />
      <MaterialUploadForm v-if="selected?.type === 'material'" :batch="selected.batch" @close="selected = null" />
      <StudentNoteModal v-if="selected?.type === 'notes'" :batch="selected.batch" @close="selected = null" />
    </div>
  </PanelLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import PanelLayout from '@/Layouts/PanelLayout.vue';
import { cardClass, pageTitle, pageSubtitle } from '@/Components/Admin/ui.js';

import ScheduleWidget from '@/Pages/Teacher/ScheduleWidget.vue';
import BatchCard from '@/Pages/Teacher/BatchCard.vue';
import AttendanceTable from '@/Pages/Teacher/AttendanceTable.vue';
import GradeModal from '@/Pages/Teacher/GradeModal.vue';
import MaterialUploadForm from '@/Pages/Teacher/MaterialUploadForm.vue';
import StudentNoteModal from '@/Pages/Teacher/StudentNoteModal.vue';

defineProps({
  batches: { type: Array, default: () => [] },
  dayNames: { type: Object, default: () => ({}) },
});

const selected = ref(null); // { type, batch }
</script>
