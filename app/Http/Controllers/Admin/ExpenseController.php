<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public const CATEGORIES = ['rent', 'utilities', 'marketing', 'supplies', 'other'];

    public function index(Request $request)
    {
        $category = $request->query('category');
        $from = $request->query('from');
        $to = $request->query('to');

        $base = Expense::query()
            ->when($category, fn ($q, $c) => $q->where('category', $c))
            ->when($from, fn ($q, $d) => $q->whereDate('expense_date', '>=', $d))
            ->when($to, fn ($q, $d) => $q->whereDate('expense_date', '<=', $d));

        $payrollExpenseIds = Payroll::whereNotNull('expense_id')->pluck('expense_id')->all();

        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => (clone $base)->with('user:id,name')->orderByDesc('expense_date')->orderByDesc('id')
                ->paginate(15)->withQueryString()
                ->through(fn ($e) => array_merge($e->toArray(), ['locked' => in_array($e->id, $payrollExpenseIds, true)])),
            'total' => (float) (clone $base)->sum('amount'),
            'categories' => self::CATEGORIES,
            'filters' => ['category' => $category, 'from' => $from, 'to' => $to],
        ]);
    }

    public function store(Request $request)
    {
        Expense::create($this->validated($request, self::CATEGORIES));

        return back()->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function update(Request $request, Expense $expense)
    {
        if ($this->isLocked($expense)) {
            return back()->with('error', 'هذا المصروف ناتج عن صرف راتب، عدّله من كشف الرواتب.');
        }

        $allowed = $expense->category === 'salary' ? [...self::CATEGORIES, 'salary'] : self::CATEGORIES;
        $expense->update($this->validated($request, $allowed));

        return back()->with('success', 'تم تعديل المصروف');
    }

    public function destroy(Expense $expense)
    {
        if ($this->isLocked($expense)) {
            return back()->with('error', 'هذا المصروف ناتج عن صرف راتب ولا يُحذف من هنا.');
        }

        $expense->delete();

        return back()->with('success', 'تم حذف المصروف');
    }

    public function voucher(Expense $expense)
    {
        return Inertia::render('Admin/Expenses/Voucher', [
            'expense' => $expense->load('user:id,name,email'),
        ]);
    }

    private function validated(Request $request, array $categories): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', Rule::in($categories)],
            'amount' => 'required|numeric|min:0.01|max:9999999999',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
    }

    private function isLocked(Expense $expense): bool
    {
        return Payroll::where('expense_id', $expense->id)->exists();
    }
}
