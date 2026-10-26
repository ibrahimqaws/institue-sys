<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->month($request->query('month'));

        $payrolls = Payroll::with(['user:id,name,role', 'expense:id'])
            ->where('period_month', $month->toDateString())
            ->get()
            ->sortBy(fn ($p) => $p->user?->name)
            ->values();

        $missing = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN])
            ->where('is_active', true)
            ->where('salary', '>', 0)
            ->whereNotIn('id', $payrolls->pluck('user_id'))
            ->count();

        return Inertia::render('Admin/Payroll/Index', [
            'payrolls' => $payrolls,
            'month' => $month->format('Y-m'),
            'missing' => $missing,
            'totals' => [
                'pending' => (float) $payrolls->where('status', Payroll::STATUS_PENDING)->sum('net_amount'),
                'paid' => (float) $payrolls->where('status', Payroll::STATUS_PAID)->sum('net_amount'),
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $month = $this->month($request->input('month'));

        $created = 0;
        $staff = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN])
            ->where('is_active', true)->where('salary', '>', 0)->get();

        foreach ($staff as $user) {
            $payroll = Payroll::firstOrCreate(
                ['user_id' => $user->id, 'period_month' => $month->toDateString()],
                ['base_amount' => $user->salary, 'bonus' => 0, 'deductions' => 0, 'net_amount' => $user->salary, 'status' => Payroll::STATUS_PENDING]
            );
            $created += $payroll->wasRecentlyCreated ? 1 : 0;
        }

        return redirect()->route('admin.payrolls.index', ['month' => $month->format('Y-m')])
            ->with('success', $created > 0 ? "تم توليد {$created} قيد راتب." : 'لا توجد قيود جديدة (الكشف مكتمل أو لا توجد رواتب محددة للموظفين).');
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== Payroll::STATUS_PENDING) {
            return back()->with('error', 'لا يمكن تعديل راتب مصروف.');
        }

        $data = $request->validate([
            'base_amount' => 'required|numeric|min:0|max:9999999999',
            'bonus' => 'required|numeric|min:0|max:9999999999',
            'deductions' => 'required|numeric|min:0|max:9999999999',
            'notes' => 'nullable|string|max:1000',
        ]);

        $net = round($data['base_amount'] + $data['bonus'] - $data['deductions'], 2);

        if ($net <= 0) {
            return back()->withErrors(['deductions' => 'صافي الراتب يجب أن يكون أكبر من صفر.']);
        }

        $payroll->update($data + ['net_amount' => $net]);

        return back()->with('success', 'تم تحديث قيد الراتب.');
    }

    public function pay(Payroll $payroll)
    {
        $result = DB::transaction(function () use ($payroll) {
            $locked = Payroll::with('user')->lockForUpdate()->find($payroll->id);

            if ($locked->status !== Payroll::STATUS_PENDING) {
                return 'already';
            }

            $expense = Expense::create([
                'title' => 'راتب ' . $locked->user->name . ' - ' . $locked->period_month->format('Y-m'),
                'category' => 'salary',
                'amount' => $locked->net_amount,
                'expense_date' => now()->toDateString(),
                'user_id' => $locked->user_id,
                'notes' => $locked->notes,
            ]);

            $locked->update([
                'status' => Payroll::STATUS_PAID,
                'paid_at' => now()->toDateString(),
                'expense_id' => $expense->id,
            ]);

            return 'ok';
        });

        return $result === 'ok'
            ? back()->with('success', 'تم صرف الراتب وتسجيله ضمن المصاريف.')
            : back()->with('error', 'هذا الراتب مصروف مسبقاً.');
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status !== Payroll::STATUS_PENDING) {
            return back()->with('error', 'لا يمكن حذف راتب مصروف.');
        }

        $payroll->delete();

        return back()->with('success', 'تم حذف قيد الراتب.');
    }

    private function month(?string $value): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', ($value && preg_match('/^\d{4}-\d{2}$/', $value) ? $value : now()->format('Y-m')) . '-01')->startOfDay();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
