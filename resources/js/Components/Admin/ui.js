
export const inputClass =
  'w-full bg-white border border-ink-line rounded-lg px-3.5 py-2.5 text-sm text-ink placeholder-ink-muted/70 shadow-sm transition focus:outline-none focus:border-edu-600 focus:ring-2 focus:ring-edu-600/20 disabled:bg-edu-50 disabled:text-ink-muted';
export const labelClass = 'block text-xs font-bold text-ink-soft mb-1.5';

export const primaryBtn =
  'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-edu-600 hover:bg-edu-700 text-white text-sm font-bold rounded-lg transition focus-visible:outline-edu-600 disabled:opacity-50 disabled:cursor-not-allowed';
export const secondaryBtn =
  'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white hover:bg-edu-50 text-edu-700 text-sm font-bold rounded-lg border border-edu-200 transition disabled:opacity-50 disabled:cursor-not-allowed';
export const ghostBtn =
  'inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold text-edu-700 bg-edu-100 hover:bg-edu-200 rounded-md transition disabled:opacity-50';
export const dangerBtn =
  'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed';
export const dangerGhostBtn =
  'inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 rounded-md transition disabled:opacity-50';

export const cardClass = 'bg-white rounded-card border border-ink-line shadow-card';
export const barClass = 'bg-edu-100 rounded-xl p-3 sm:p-4';
export const pageTitle = 'text-2xl sm:text-3xl font-black text-ink leading-tight';
export const pageSubtitle = 'text-sm text-ink-muted mt-1';

const chipBase = 'inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold';
export const chipTones = {
  green: `${chipBase} bg-edu-100 text-edu-700`,
  gray: `${chipBase} bg-slate-100 text-ink-soft`,
  yellow: `${chipBase} bg-sun-soft text-amber-700`,
  red: `${chipBase} bg-red-50 text-red-700`,
  blue: `${chipBase} bg-sky-50 text-sky-700`,
};
export const chip = (tone = 'green') => chipTones[tone] || chipTones.green;

export const tableWrap = 'overflow-x-auto';
export const tableClass = 'w-full text-sm text-start';
export const theadClass = 'bg-edu-100 text-edu-800 text-xs font-bold';
export const thClass = 'px-4 py-3 font-bold whitespace-nowrap';
export const trClass = 'border-b border-ink-line last:border-0 hover:bg-edu-50 transition-colors';
export const tdClass = 'px-4 py-3 text-ink-soft';

export const money = (v) =>
  '$' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
