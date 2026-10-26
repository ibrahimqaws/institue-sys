<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends Controller
{
    private const METHODS = ['cash', 'transfer', 'card'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $payments = Payment::with(['student:id,name,email', 'batch:id,name,course_id', 'batch.course:id,title'])
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $q->where(fn ($w) => $w->where('receipt_number', 'like', $like)
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $like)));
            })
            ->when($request->query('batch_id'), fn ($q, $id) => $q->where('batch_id', $id))
            ->latest('payment_date')->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'balances' => $this->balances(),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'batch_id' => 'required|exists:batches,id',
            'amount_paid' => 'required|numeric|min:0.01|max:9999999999',
            'payment_date' => 'required|date',
            'payment_method' => ['required', Rule::in(self::METHODS)],
            'receipt_number' => 'nullable|string|max:50|unique:payments,receipt_number',
            'notes' => 'nullable|string|max:1000',
        ]);

        $enrollment = Enrollment::with('batch.course')
            ->where('student_id', $data['student_id'])
            ->where('batch_id', $data['batch_id'])
            ->where('status', 'active')
            ->first();

        if (! $enrollment) {
            return back()->withErrors(['batch_id' => 'الطالب غير مسجّل (بحالة نشطة) في هذه المجموعة.']);
        }

        if ((float) $data['amount_paid'] > $enrollment->remaining() + 0.001) {
            return back()->withErrors(['amount_paid' => 'المبلغ أكبر من المتبقي على الطالب (' . number_format($enrollment->remaining(), 2) . ').']);
        }

        $data['receipt_number'] = $data['receipt_number'] ?: $this->newReceiptNumber();

        $payment = DB::transaction(fn () => Payment::create($data));

        return redirect()->route('admin.payments.index')
            ->with('success', 'تم تسجيل الدفعة برقم سند ' . $payment->receipt_number);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'amount_paid' => 'required|numeric|min:0.01|max:9999999999',
            'payment_date' => 'required|date',
            'payment_method' => ['required', Rule::in(self::METHODS)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $enrollment = Enrollment::with('batch.course')
            ->where('student_id', $payment->student_id)->where('batch_id', $payment->batch_id)->first();

        if ($enrollment) {
            $remainingWithoutThis = $enrollment->remaining() + (float) $payment->amount_paid;
            if ((float) $data['amount_paid'] > $remainingWithoutThis + 0.001) {
                return back()->withErrors(['amount_paid' => 'المبلغ أكبر من المتبقي على الطالب.']);
            }
        }

        $payment->update($data);

        return back()->with('success', 'تم تعديل الدفعة.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return back()->with('success', 'تم حذف الدفعة.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['student:id,name,email,phone', 'batch.course:id,title,price']);

        $enrollment = Enrollment::with('batch.course')
            ->where('student_id', $payment->student_id)->where('batch_id', $payment->batch_id)->first();

        return Inertia::render('Admin/Payments/Receipt', [
            'payment' => $payment,
            'fee' => $enrollment?->totalFee(),
            'total_paid' => $enrollment?->totalPaid(),
            'remaining' => $enrollment?->remaining(),
        ]);
    }

    
    private function balances(): array
    {
        $paid = Payment::select('student_id', 'batch_id', DB::raw('SUM(amount_paid) as paid'))
            ->groupBy('student_id', 'batch_id')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->student_id . '-' . $p->batch_id => (float) $p->paid]);

        return Enrollment::with(['student:id,name,email', 'batch:id,name,course_id', 'batch.course:id,title,price'])
            ->where('status', 'active')
            ->get()
            ->map(function ($e) use ($paid) {
                $fee = (float) ($e->batch?->course?->price ?? 0);
                $p = $paid[$e->student_id . '-' . $e->batch_id] ?? 0.0;

                return [
                    'student_id' => $e->student_id,
                    'batch_id' => $e->batch_id,
                    'student_name' => $e->student?->name,
                    'batch_name' => $e->batch?->name,
                    'course_title' => $e->batch?->course?->title,
                    'fee' => $fee,
                    'paid' => $p,
                    'remaining' => max(0, $fee - $p),
                ];
            })
            ->sortBy('student_name')
            ->values()
            ->all();
    }

    private function newReceiptNumber(): string
    {
        do {
            $number = 'REC-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Payment::where('receipt_number', $number)->exists());

        return $number;
    }
}
