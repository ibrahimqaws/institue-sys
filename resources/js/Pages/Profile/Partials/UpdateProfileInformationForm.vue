<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone ?? '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-black text-ink">البيانات الشخصية</h2>
            <p class="mt-1 text-sm text-ink-muted">حدّث اسمك وبريدك الإلكتروني ورقم هاتفك.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'), { preserveScroll: true })" class="mt-6 space-y-5">
            <div>
                <InputLabel for="name" value="الاسم" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="البريد الإلكتروني" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone" value="رقم الهاتف" />
                <TextInput id="phone" type="tel" class="mt-1 block w-full" v-model="form.phone" autocomplete="tel" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">حفظ</PrimaryButton>
            </div>
        </form>
    </section>
</template>
