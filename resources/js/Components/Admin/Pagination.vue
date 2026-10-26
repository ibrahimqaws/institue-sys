<template>
  <nav v-if="links && links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6" dir="rtl">
    <template v-for="(link, i) in links" :key="i">
      <Link
        v-if="link.url"
        :href="link.url"
        preserve-scroll
        :class="['px-3 py-1.5 rounded-lg text-xs border transition', link.active ? 'bg-edu-600 border-edu-600 text-white font-bold' : 'bg-white border-ink-line text-ink-soft hover:bg-edu-100 hover:border-edu-200']"
        v-html="label(link.label)"
      />
      <span v-else class="px-3 py-1.5 rounded-lg text-xs border border-ink-line bg-white text-ink-muted/50" v-html="label(link.label)" />
    </template>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ links: Array });

const label = (l) => l.replace('&laquo; Previous', 'السابق').replace('Next &raquo;', 'التالي');
</script>
