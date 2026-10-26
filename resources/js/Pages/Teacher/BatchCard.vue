<template>
  <div :class="[cardClass, 'p-5 flex flex-col justify-between hover:shadow-pop transition-shadow duration-300']">
    <div>
      <div class="flex justify-between items-center mb-3">
        <span :class="chip(batch.status === 'active' ? 'green' : batch.status === 'cancelled' ? 'red' : 'gray')">
          {{ statusLabel }}
        </span>
        <span class="text-xs text-ink-muted">👥 {{ batch.students_count || 0 }} طالب</span>
      </div>

      <h3 class="text-lg font-black text-ink">{{ batch.name }}</h3>
      <p class="text-xs text-edu-700 font-bold mt-1">📚 {{ batch.subject_name || 'المادة غير محددة' }}</p>

      <div class="mt-4 p-3 bg-edu-100 rounded-lg space-y-1.5 text-xs text-ink-soft">
        <div class="font-bold text-ink">🕒 {{ batch.schedule_label || 'الموعد غير محدد' }}</div>
        <div v-if="batch.room" class="text-ink-muted">🏛️ القاعة: {{ batch.room }}</div>
        <div class="text-ink-muted">📁 {{ batch.materials_count || 0 }} مادة/واجب</div>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 mt-5">
      <button @click="$emit('open-attendance', batch)" :class="[primaryBtn, '!py-2 !text-xs']">📝 الحضور</button>
      <button @click="$emit('open-grades', batch)" :class="[secondaryBtn, '!py-2 !text-xs']">🎯 الدرجات</button>
      <button @click="$emit('open-material', batch)" :class="[secondaryBtn, '!py-2 !text-xs']">📁 المواد</button>
      <button @click="$emit('open-notes', batch)" :class="[secondaryBtn, '!py-2 !text-xs']">✏️ الملاحظات</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { cardClass, chip, primaryBtn, secondaryBtn } from '@/Components/Admin/ui.js';

const props = defineProps({ batch: Object });
defineEmits(['open-attendance', 'open-grades', 'open-material', 'open-notes']);

const statusLabel = computed(() => ({ active: 'مجموعة نشطة', completed: 'منتهية', cancelled: 'ملغاة' }[props.batch.status] || props.batch.status));
</script>
