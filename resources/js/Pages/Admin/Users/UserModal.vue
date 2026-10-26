<template>
  <Modal :show="show" :title="user ? '✏️ تعديل الحساب' : '👤 إضافة مستخدم جديد'" @close="$emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label :class="labelClass">الاسم الكامل</label>
        <input v-model="form.name" type="text" :class="inputClass" required />
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">البريد الإلكتروني</label>
          <input v-model="form.email" type="email" :class="inputClass" dir="ltr" required />
          <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
        </div>
        <div>
          <label :class="labelClass">الهاتف</label>
          <input v-model="form.phone" type="text" :class="inputClass" dir="ltr" />
          <p v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</p>
        </div>
      </div>

      <div>
        <label :class="labelClass">{{ user ? 'كلمة مرور جديدة (اتركها فارغة لعدم التغيير)' : 'كلمة المرور' }}</label>
        <input v-model="form.password" type="password" :class="inputClass" autocomplete="new-password" :required="!user" minlength="8" />
        <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">الدور / الصلاحية</label>
          <select v-model="form.role" :class="inputClass">
            <option value="student">👨‍🎓 طالب</option>
            <option value="teacher">👨‍🏫 مدرس / مدرب</option>
            <option value="admin">👨‍💼 مدير نظام</option>
          </select>
          <p v-if="form.errors.role" class="text-red-600 text-xs mt-1">{{ form.errors.role }}</p>
        </div>
        <div v-if="form.role !== 'student'">
          <label :class="labelClass">الراتب الشهري الأساسي ($)</label>
          <input v-model="form.salary" type="number" min="0" step="0.01" :class="inputClass" />
          <p v-if="form.errors.salary" class="text-red-600 text-xs mt-1">{{ form.errors.salary }}</p>
        </div>
      </div>

      <label class="flex items-center gap-2 text-sm text-ink-soft">
        <input v-model="form.is_active" type="checkbox" class="rounded border-ink-line bg-white text-edu-700 focus:ring-edu-600" />
        الحساب فعّال (يستطيع تسجيل الدخول)
      </label>

      <div class="flex justify-end space-x-3 space-x-reverse pt-4">
        <button type="button" @click="$emit('close')" class="px-4 py-2 bg-white border border-edu-200 text-edu-700 text-xs font-bold rounded-lg hover:bg-edu-50">إلغاء</button>
        <button type="submit" :disabled="form.processing" :class="primaryBtn">{{ user ? 'حفظ التعديلات' : 'إنشاء الحساب' }}</button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Admin/Modal.vue';
import { inputClass, labelClass, primaryBtn } from '@/Components/Admin/ui';

const props = defineProps({ show: Boolean, user: Object, currentRole: String });
const emit = defineEmits(['close']);

const form = useForm({ name: '', email: '', phone: '', password: '', role: 'student', salary: '', is_active: true });

watch(() => props.show, (open) => {
  if (!open) return;
  form.clearErrors();
  const u = props.user;
  form.name = u?.name ?? '';
  form.email = u?.email ?? '';
  form.phone = u?.phone ?? '';
  form.password = '';
  form.role = u?.role ?? props.currentRole ?? 'student';
  form.salary = u?.salary ?? '';
  form.is_active = u?.is_active ?? true;
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  props.user
    ? form.put(route('admin.users.update', props.user.id), options)
    : form.post(route('admin.users.store'), options);
};
</script>
