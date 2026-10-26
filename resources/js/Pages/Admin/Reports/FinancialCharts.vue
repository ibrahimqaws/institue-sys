<template>
    <div class="space-y-6 mb-8" dir="rtl">

      
      <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
        <h3 class="text-base font-black text-ink mb-4 flex items-center gap-2">
          <span>📈</span>
          <span>مسار التدفق المالي الشهري (نمو الإيرادات vs النفقات)</span>
        </h3>
        <div class="h-72">
          <Line :data="lineData" :options="lineOptions" />
        </div>
      </div>

      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        
        <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
          <h3 class="text-base font-black text-ink mb-4 flex items-center gap-2">
            <span>📊</span>
            <span>إجمالي المقبوضات مقابل النفقات</span>
          </h3>
          <div class="h-60">
            <Bar :data="barData" :options="barOptions" />
          </div>
        </div>

        
        <div class="bg-white p-5 sm:p-6 rounded-card border border-ink-line shadow-card">
          <h3 class="text-base font-black text-ink mb-4 flex items-center gap-2">
            <span>🍕</span>
            <span>توزيع النفقات والمصاريف</span>
          </h3>
          <div class="h-60 flex items-center justify-center">
            <Doughnut v-if="expensesByCategory && expensesByCategory.length > 0" :data="doughnutData" :options="doughnutOptions" />
            <div v-else class="text-ink-muted text-xs">لا توجد مصاريف مسجلة لرسم المخطط</div>
          </div>
        </div>

      </div>

    </div>
  </template>

  <script setup>
  import { computed } from 'vue';
  import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    PointElement,
    LineElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Filler
  } from 'chart.js';
  import { Bar, Doughnut, Line } from 'vue-chartjs';

  ChartJS.register(
    Title, Tooltip, Legend, BarElement, PointElement,
    LineElement, CategoryScale, LinearScale, ArcElement, Filler
  );

  const props = defineProps({
    totalRevenue: { type: Number, default: 0 },
    totalExpenses: { type: Number, default: 0 },
    expensesByCategory: { type: Array, default: () => [] },
    monthlyRevenue: { type: Array, default: () => [] },
    monthlyExpenses: { type: Array, default: () => [] }
  });

  const lineData = computed(() => {
    const months = Array.from(new Set([
      ...props.monthlyRevenue.map(item => item.month),
      ...props.monthlyExpenses.map(item => item.month)
    ])).sort();

    const revData = months.map(m => {
      const found = props.monthlyRevenue.find(item => item.month === m);
      return found ? found.total : 0;
    });

    const expData = months.map(m => {
      const found = props.monthlyExpenses.find(item => item.month === m);
      return found ? found.total : 0;
    });

    return {
      labels: months.length > 0 ? months : ['لا توجد بيانات'],
      datasets: [
        {
          label: 'الإيرادات ($)',
          data: revData,
          borderColor: '#2F8A4F',
          backgroundColor: 'rgba(47, 138, 79, 0.12)',
          fill: true,
          tension: 0.4
        },
        {
          label: 'النفقات ($)',
          data: expData,
          borderColor: '#E5484D',
          backgroundColor: 'rgba(229, 72, 77, 0.08)',
          fill: true,
          tension: 0.4
        }
      ]
    };
  });

  const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { color: '#3C4B5C' } }
    },
    scales: {
      y: { grid: { color: 'rgba(27, 42, 58, 0.06)' }, ticks: { color: '#5E6A78' } },
      x: { grid: { display: false }, ticks: { color: '#3C4B5C' } }
    }
  };

  // 2️⃣ Bar Chart
  const barData = computed(() => ({
    labels: ['المقبوضات (الواردات)', 'النفقات (الصادرات)'],
    datasets: [{
      label: 'المبلغ ($)',
      data: [props.totalRevenue || 0, props.totalExpenses || 0],
      backgroundColor: ['#2F8A4F', '#E5484D'],
      borderRadius: 12,
      barThickness: 45
    }]
  }));

  const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: 'rgba(27, 42, 58, 0.06)' }, ticks: { color: '#5E6A78' } },
      x: { grid: { display: false }, ticks: { color: '#3C4B5C' } }
    }
  };

  // 3️⃣ Doughnut Chart
  const doughnutData = computed(() => {
    const labelsMap = {
      salary: '💸 الرواتب والمستحقات',
      rent: '🏢 إيجار المقر',
      utilities: '⚡️ خدمات وفواتير',
      marketing: '📣 تسويق وإعلانات',
      supplies: '🧰 مستلزمات',
      other: '📦 مصاريف أخرى'
    };

    const categories = props.expensesByCategory || [];
    return {
      labels: categories.map(item => labelsMap[item.category] || item.category),
      datasets: [{
        data: categories.map(item => item.total),
        backgroundColor: ['#2F8A4F', '#F5B301', '#62B987', '#1F5A36', '#A3D9B8', '#6B7785'],
        borderColor: '#FFFFFF',
        borderWidth: 2
      }]
    };
  });

  const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { color: '#3C4B5C', font: { size: 11 } } }
    }
  };
  </script>