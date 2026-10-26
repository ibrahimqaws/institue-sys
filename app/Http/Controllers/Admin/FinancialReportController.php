<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    public function getReport()
    {
        $totalRevenue = (float) Payment::sum('amount_paid');
        $totalExpenses = (float) Expense::sum('amount');

        $since = now()->subMonths(11)->startOfMonth()->toDateString();

        $monthlyRevenue = Payment::whereDate('payment_date', '>=', $since)->get(['payment_date', 'amount_paid'])
            ->groupBy(fn ($p) => $p->payment_date->format('Y-m'))
            ->map(fn ($g, $m) => ['month' => $m, 'total' => round((float) $g->sum('amount_paid'), 2)])
            ->values();

        $monthlyExpenses = Expense::whereDate('expense_date', '>=', $since)->get(['expense_date', 'amount'])
            ->groupBy(fn ($e) => $e->expense_date->format('Y-m'))
            ->map(fn ($g, $m) => ['month' => $m, 'total' => round((float) $g->sum('amount'), 2)])
            ->values();

        return Inertia::render('Admin/Reports/Financial', [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
            'expenses_by_category' => Expense::select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')->get()
                ->map(fn ($r) => ['category' => $r->category, 'total' => (float) $r->total])->values(),
            'monthly_revenue' => $monthlyRevenue,
            'monthly_expenses' => $monthlyExpenses,
            'recent_payments' => Payment::with(['student:id,name', 'batch:id,name,course_id', 'batch.course:id,title'])
                ->latest('payment_date')->latest('id')->take(10)->get(),
            'recent_expenses' => Expense::latest('expense_date')->latest('id')->take(10)->get(),
        ]);
    }
}
