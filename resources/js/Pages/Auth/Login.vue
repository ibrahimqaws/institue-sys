<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تسجيل الدخول" />

        <h1 class="mb-1 text-2xl font-black text-ink">تسجيل <span class="edu-underline">الدخول</span></h1>
        <p class="mb-6 mt-3 text-sm text-ink-muted">أدخل بريدك الإلكتروني وكلمة المرور للوصول إلى لوحتك.</p>

        <div v-if="status" class="mb-4 rounded-lg border border-edu-200 bg-edu-100 p-3 text-sm font-medium text-edu-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="البريد الإلكتروني" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="كلمة المرور" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-ink-soft">تذكّرني</span>
            </label>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">دخول</PrimaryButton>
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-semibold text-edu-700 hover:text-edu-800 hover:underline">
                    نسيت كلمة المرور؟
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
