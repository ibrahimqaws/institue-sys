<template>
  <Head title="بوابة الطالب" />
  <PanelLayout v-model:activeTab="activeTab">
    <div class="max-w-7xl mx-auto w-full space-y-6">
      <Header :name="$page.props.auth.user.name" />

      <OverviewTab v-if="activeTab === 'overview'" :stats="stats" :schedule="schedule" :day-names="dayNames" />
      <CoursesTab v-if="activeTab === 'courses'" :enrollments="enrollments" />
      <ScheduleTab v-if="activeTab === 'schedule'" :schedule="schedule" :day-names="dayNames" />
      <AttendanceTab v-if="activeTab === 'attendance'" :attendance="attendance" :summary="attendanceSummary" :by-batch="attendanceByBatch" />
      <GradesTab v-if="activeTab === 'grades'" :grades="grades" :notes="notes" />
      <MaterialsTab v-if="activeTab === 'materials'" :materials="materials" />
      <PaymentsTab v-if="activeTab === 'payments'" :finance="finance" />
    </div>
  </PanelLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import PanelLayout from '@/Layouts/PanelLayout.vue';
import Header from './Components/Header.vue';
import OverviewTab from './Components/OverviewTab.vue';
import CoursesTab from './Components/CoursesTab.vue';
import ScheduleTab from './Components/ScheduleTab.vue';
import AttendanceTab from './Components/AttendanceTab.vue';
import GradesTab from './Components/GradesTab.vue';
import MaterialsTab from './Components/MaterialsTab.vue';
import PaymentsTab from './Components/PaymentsTab.vue';

defineProps({
  stats: { type: Object, default: () => ({}) },
  enrollments: { type: Array, default: () => [] },
  schedule: { type: Array, default: () => [] },
  dayNames: { type: Object, default: () => ({}) },
  attendance: { type: Array, default: () => [] },
  attendanceSummary: { type: Object, default: () => ({}) },
  attendanceByBatch: { type: Array, default: () => [] },
  grades: { type: Array, default: () => [] },
  notes: { type: Array, default: () => [] },
  materials: { type: Array, default: () => [] },
  finance: { type: Object, default: () => ({ courses: [], payments: [], totals: {} }) },
});

const validTabs = ['overview', 'courses', 'schedule', 'attendance', 'grades', 'materials', 'payments'];
const initial = new URLSearchParams(window.location.search).get('tab');
const activeTab = ref(validTabs.includes(initial) ? initial : 'overview');
</script>
