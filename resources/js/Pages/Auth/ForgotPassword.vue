<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: { type: String } });

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="استعادة كلمة المرور" />

        <h1 class="mb-1 text-2xl font-black text-ink">استعادة <span class="edu-underline">كلمة المرور</span></h1>
        <p class="mb-6 mt-3 text-sm leading-relaxed text-ink-muted">
            أدخل بريدك الإلكتروني وسنرسل لك رابطاً لتعيين كلمة مرور جديدة.
        </p>

        <div v-if="status" class="mb-4 rounded-lg border border-edu-200 bg-edu-100 p-3 text-sm font-medium text-edu-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="البريد الإلكتروني" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <PrimaryButton :disabled="form.processing">إرسال رابط الاستعادة</PrimaryButton>
                <Link :href="route('login')" class="text-sm font-semibold text-edu-700 hover:text-edu-800 hover:underline">العودة لتسجيل الدخول</Link>
            </div>
        </form>
    </GuestLayout>
</template>
