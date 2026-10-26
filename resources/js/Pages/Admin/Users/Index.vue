<template>
  <AdminLayout>
    <Head title="إدارة المستخدمين" />
    <header class="flex flex-wrap justify-between items-center gap-4 mb-8">
      <div>
        <h1 :class="pageTitle">إدارة الحسابات <span class="edu-underline">والمستخدمين</span></h1>
        <p :class="[pageSubtitle, 'mt-3']">إضافة وتعديل وتفعيل حسابات الطلاب والمدرسين والموظفين.</p>
      </div>
      <button @click="openCreate" :class="primaryBtn">+ إضافة حساب جديد</button>
    </header>

    <div class="edu-bar flex flex-wrap gap-2 mb-6">
      <button v-for="tab in tabs" :key="tab.role" @click="go({ role: tab.role })"
        :class="[current_role === tab.role ? 'bg-edu-600 text-white font-bold border-edu-600' : 'bg-white text-ink-soft hover:bg-edu-50 border-ink-line']"
        class="px-5 py-2 rounded-lg text-sm transition border">
        {{ tab.label }} <span class="opacity-70">({{ counts[tab.role] || 0 }})</span>
      </button>
    </div>

    <div class="flex flex-wrap gap-3 mb-5">
      <input v-model="search" @keyup.enter="applyFilters" type="search" placeholder="بحث بالاسم أو البريد أو الهاتف..." :class="[inputClass, 'max-w-xs']" />
      <select v-model="status" @change="applyFilters" :class="[inputClass, 'max-w-[10rem]']">
        <option value="">كل الحالات</option>
        <option value="active">فعّال</option>
        <option value="inactive">معطّل</option>
      </select>
      <button @click="applyFilters" class="px-4 py-2 bg-edu-100 text-edu-700 text-sm font-bold rounded-lg hover:bg-edu-200">بحث</button>
    </div>

    <div :class="[cardClass, 'overflow-x-auto']">
      <table class="w-full text-start">
        <thead>
          <tr class="bg-edu-100 text-edu-800 text-xs font-bold">
            <th class="p-4">الاسم</th>
            <th class="p-4">البريد / الهاتف</th>
            <th class="p-4">الصلاحية</th>
            <th v-if="current_role !== 'student'" class="p-4">الراتب</th>
            <th class="p-4">الحالة</th>
            <th class="p-4 text-center">العمليات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-line text-sm">
          <tr v-for="user in users.data" :key="user.id" class="hover:bg-edu-50 transition">
            <td class="p-4 font-bold text-ink">{{ user.name }}</td>
            <td class="p-4 text-ink-soft">
              <div>{{ user.email }}</div>
              <div class="text-xs text-ink-muted font-mono" dir="ltr">{{ user.phone }}</div>
            </td>
            <td class="p-4">
              <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-edu-100 text-edu-700">{{ roleLabel[user.role] }}</span>
            </td>
            <td v-if="current_role !== 'student'" class="p-4 text-amber-700 font-bold">{{ user.salary ? money(user.salary) : '—' }}</td>
            <td class="p-4">
              <span :class="user.is_active ? 'bg-edu-100 text-edu-700' : 'bg-red-50 text-red-700'" class="px-2.5 py-1 rounded-md text-xs font-bold">
                {{ user.is_active ? 'فعّال' : 'معطّل' }}
              </span>
            </td>
            <td class="p-4 text-center whitespace-nowrap space-x-2 space-x-reverse">
              <button @click="openEdit(user)" class="px-3 py-1.5 bg-edu-100 hover:bg-edu-200 text-edu-700 rounded-md text-xs font-bold">تعديل</button>
              <button @click="toggle(user)" class="px-3 py-1.5 bg-sun-soft hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold">
                {{ user.is_active ? 'تعطيل' : 'تفعيل' }}
              </button>
              <button @click="remove(user)" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-semibold">حذف</button>
            </td>
          </tr>
          <tr v-if="users.data.length === 0">
            <td colspan="6" class="p-8 text-center text-ink-muted">لا يوجد مستخدمون مطابقون.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="users.links" />

    <UserModal :show="showModal" :user="editing" :currentRole="current_role" @close="showModal = false" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import UserModal from './UserModal.vue';
import { inputClass, primaryBtn, cardClass, money, pageTitle, pageSubtitle } from '@/Components/Admin/ui';

const props = defineProps({ users: Object, current_role: String, filters: Object, counts: Object });

const tabs = [
  { role: 'student', label: '👨‍🎓 الطلاب' },
  { role: 'teacher', label: '👨‍🏫 المدرسون' },
  { role: 'admin', label: '👨‍💼 الإدارة' },
];
const roleLabel = { student: 'طالب', teacher: 'مدرس', admin: 'مدير' };

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const showModal = ref(false);
const editing = ref(null);

const go = (extra = {}) => {
  const params = { role: props.current_role, search: search.value || undefined, status: status.value || undefined, ...extra };
  router.get(route('admin.users.index'), params, { preserveState: true });
};
const applyFilters = () => go();

const openCreate = () => { editing.value = null; showModal.value = true; };
const openEdit = (u) => { editing.value = u; showModal.value = true; };
const toggle = (u) => router.patch(route('admin.users.toggle', u.id), {}, { preserveScroll: true });
const remove = (u) => {
  if (confirm(`هل أنت متأكد من حذف حساب «${u.name}» نهائياً؟`)) {
    router.delete(route('admin.users.destroy', u.id), { preserveScroll: true });
  }
};
</script>
