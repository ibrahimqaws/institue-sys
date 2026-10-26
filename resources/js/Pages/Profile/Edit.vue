<script setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="حسابي" />

    <PanelLayout>
        <div class="mx-auto w-full max-w-3xl space-y-6">
            <header class="rounded-card border border-ink-line bg-white p-4 shadow-card sm:p-5">
                <h1 class="text-2xl font-black text-ink">⚙️ حسابي</h1>
                <p class="mt-1 text-sm text-ink-muted">عدّل بياناتك الشخصية وكلمة المرور.</p>
            </header>

            <div class="rounded-card border border-ink-line bg-white p-5 shadow-card sm:p-8">
                <UpdateProfileInformationForm class="max-w-xl" />
            </div>

            <div class="rounded-card border border-ink-line bg-white p-5 shadow-card sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

        </div>
    </PanelLayout>
</template>
