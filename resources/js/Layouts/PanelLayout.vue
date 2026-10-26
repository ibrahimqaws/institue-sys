<template>
  <div class="min-h-screen bg-edu-50 text-ink font-sans flex flex-col" dir="rtl">
    <Toast />

    
    <div class="hidden md:flex items-center justify-between gap-4 bg-topbar px-6 py-2 text-xs text-white/80">
      <span>مرحباً <span class="font-bold text-white">{{ user.name }}</span></span>
      <span v-if="roleLabel" class="text-edu-300 font-bold">{{ roleLabel }}</span>
    </div>

    
    <header class="md:hidden sticky top-0 z-40 flex items-center justify-between gap-3 bg-white border-b border-ink-line px-4 py-3">
      <div class="flex items-center gap-2.5 min-w-0">
        <span class="w-9 h-9 rounded-lg bg-edu-100 text-edu-700 flex items-center justify-center shrink-0">
          <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.5C10.5 5 8 4.5 4 4.5v13c4 0 6.5.5 8 2 1.5-1.5 4-2 8-2v-13c-4 0-6.5.5-8 2z"/><path d="M12 6.5v13"/></svg>
        </span>
        <span class="font-extrabold text-ink truncate">{{ brand.title }}</span>
      </div>
      <button type="button" class="p-2 rounded-lg border border-ink-line text-ink-soft hover:bg-edu-50" aria-label="القائمة" @click="open = !open">
        <span v-if="!open">☰</span><span v-else>✕</span>
      </button>
    </header>

    
    <div v-if="open" class="md:hidden fixed inset-0 bg-ink/50 backdrop-blur-sm z-40" @click="open = false"></div>

    <div class="flex-1 flex flex-col md:flex-row md:items-start md:gap-6 md:p-6">
      <aside
        :class="[
          'bg-white border-ink-line flex flex-col justify-between p-4 shrink-0 z-50',
          'fixed inset-y-0 right-0 w-72 max-w-[85vw] border-l transition-transform duration-200 overflow-y-auto',
          'md:sticky md:top-6 md:w-64 md:max-w-none md:translate-x-0 md:border md:rounded-card md:shadow-card md:max-h-[calc(100vh-4.5rem)]',
          open ? 'translate-x-0' : 'translate-x-full',
        ]"
      >
        <div>
          <div class="hidden md:flex items-center gap-3 mb-6 px-2 pt-1">
            <span class="w-10 h-10 rounded-lg bg-edu-100 text-edu-700 flex items-center justify-center shrink-0">
              <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.5C10.5 5 8 4.5 4 4.5v13c4 0 6.5.5 8 2 1.5-1.5 4-2 8-2v-13c-4 0-6.5.5-8 2z"/><path d="M12 6.5v13"/></svg>
            </span>
            <span class="text-lg font-black text-ink">{{ brand.title }}</span>
          </div>

          <div class="md:hidden mb-4 px-2">
            <p class="text-sm font-bold text-ink truncate">{{ user.name }}</p>
            <p class="text-xs text-edu-700 font-bold">{{ roleLabel }}</p>
          </div>

          <nav class="space-y-1">
            <template v-for="item in nav" :key="item.key">
              
              <button
                v-if="item.tab && activeTab !== undefined"
                type="button"
                :class="[(activeTab === item.tab) ? activeClass : inactiveClass, itemBase]"
                @click="selectTab(item.tab)"
              >
                <span class="w-6 text-center">{{ item.icon }}</span><span>{{ item.label }}</span>
              </button>

              
              <Link
                v-else
                :href="item.href"
                :class="[item.active ? activeClass : inactiveClass, itemBase]"
                @click="open = false"
              >
                <span class="w-6 text-center">{{ item.icon }}</span><span>{{ item.label }}</span>
                <span v-if="item.badge" class="ms-auto bg-sun text-ink text-xs font-black rounded-full px-2 py-0.5">{{ item.badge }}</span>
              </Link>
            </template>
          </nav>
        </div>

        <div class="pt-4 mt-6 border-t border-ink-line space-y-1">
          <Link :href="route('profile.edit')" :class="[route().current('profile.*') ? activeClass : inactiveClass, itemBase]" @click="open = false">
            <span class="w-6 text-center">⚙️</span><span>حسابي</span>
          </Link>
          <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg border-s-4 border-transparent text-red-600 hover:bg-red-50 transition text-sm font-semibold" @click="logout">
            <span class="w-6 text-center">🚪</span><span>تسجيل الخروج</span>
          </button>
        </div>
      </aside>

      <main class="flex-1 min-w-0 p-4 sm:p-6 md:p-0">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
  activeTab: { type: String, default: undefined },
});
const emit = defineEmits(['update:activeTab']);

const page = usePage();
const open = ref(false);
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.role);

const roleLabel = computed(() => ({ admin: 'مدير', teacher: 'مدرس', student: 'طالب' }[role.value] || ''));

const brands = {
  admin: { title: 'إدارة المعهد' },
  teacher: { title: 'لوحة المدرس' },
  student: { title: 'بوابة الطالب' },
};
const brand = computed(() => brands[role.value] || brands.student);

const pending = computed(() => page.props.pending_enrollments || 0);

const adminItems = [
  ['admin.dashboard', 'admin.dashboard', '📊', 'لوحة الإحصائيات'],
  ['admin.users.index', 'admin.users.*', '👨‍🎓', 'إدارة المستخدمين'],
  ['admin.enrollments.index', 'admin.enrollments.*', '📝', 'طلبات التسجيل', true],
  ['admin.courses.index', 'admin.courses.*', '📚', 'الكورسات'],
  ['admin.classrooms.index', 'admin.classrooms.*', '🏛️', 'القاعات والمختبرات'],
  ['admin.batches.index', 'admin.batches.*', '👥', 'المجموعات الدراسية'],
  ['admin.payments.index', 'admin.payments.*', '💳', 'الأقساط والمدفوعات'],
  ['admin.payrolls.index', 'admin.payrolls.*', '💸', 'الرواتب'],
  ['admin.expenses.index', 'admin.expenses.*', '🧾', 'المصاريف'],
  ['admin.financial.report', 'admin.financial.*', '💰', 'التقارير المالية'],
];

const studentTabs = [
  ['overview', '🎓', 'الرئيسية'],
  ['courses', '📚', 'كورساتي'],
  ['schedule', '📅', 'الجدول الأسبوعي'],
  ['attendance', '📊', 'سجل الحضور'],
  ['grades', '📝', 'العلامات والملاحظات'],
  ['materials', '📎', 'المواد والواجبات'],
  ['payments', '💰', 'السجل المالي'],
];

const nav = computed(() => {
  if (role.value === 'admin') {
    return adminItems.map(([r, match, icon, label, hasBadge]) => ({
      key: r,
      href: route(r),
      active: route().current(match),
      icon,
      label,
      badge: hasBadge && pending.value ? pending.value : null,
    }));
  }
  if (role.value === 'teacher') {
    return [{ key: 'teacher.dashboard', href: route('teacher.dashboard'), active: route().current('teacher.*'), icon: '🗓️', label: 'جدولي ومجموعاتي' }];
  }
  if (role.value === 'student') {
    return [
      ...studentTabs.map(([tab, icon, label]) => ({
        key: tab,
        tab,
        href: route('student.dashboard', { tab }),
        active: false,
        icon,
        label,
      })),
      { key: 'register', href: route('student.courses.available'), active: route().current('student.courses.*'), icon: '✨', label: 'التسجيل في كورس' },
    ];
  }
  return [];
});

const selectTab = (tab) => {
  emit('update:activeTab', tab);
  open.value = false;
};

const itemBase = 'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-sm border-s-4';
const activeClass = 'bg-edu-100 text-edu-700 border-edu-600 font-bold';
const inactiveClass = 'text-ink-soft hover:bg-edu-50 hover:text-edu-700 border-transparent font-semibold';

const logout = () => router.post(route('logout'));
</script>
