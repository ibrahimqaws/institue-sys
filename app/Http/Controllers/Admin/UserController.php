<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = in_array($request->query('role'), User::ROLES, true) ? $request->query('role') : User::ROLE_STUDENT;
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->where('role', $role)
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'current_role' => $role,
            'filters' => ['search' => $search, 'status' => $status],
            'counts' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['password'] = $request->validate(['password' => 'required|string|min:8|max:255'])['password'];

        User::create($validated);

        return redirect()->route('admin.users.index', ['role' => $validated['role']])
            ->with('success', 'تم إنشاء الحساب بنجاح');
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validated($request, $user);

        if ($request->filled('password')) {
            $validated['password'] = $request->validate(['password' => 'string|min:8|max:255'])['password'];
        }

        $isSelf = $user->id === $request->user()->id;

        if ($isSelf && ($validated['role'] !== $user->role || ! $validated['is_active'])) {
            return back()->with('error', 'لا يمكنك تغيير دورك أو تعطيل حسابك بنفسك.');
        }

        if ($user->isAdmin() && ($validated['role'] !== User::ROLE_ADMIN || ! $validated['is_active']) && $this->isLastActiveAdmin($user)) {
            return back()->with('error', 'لا يمكن تعديل آخر مدير فعّال في النظام.');
        }

        $user->update($validated);

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    public function toggle(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك بنفسك.');
        }

        if ($user->is_active && $user->isAdmin() && $this->isLastActiveAdmin($user)) {
            return back()->with('error', 'لا يمكن تعطيل آخر مدير فعّال.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'تم تفعيل الحساب.' : 'تم تعطيل الحساب.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك بنفسك.');
        }

        if ($user->isAdmin() && $this->isLastActiveAdmin($user)) {
            return back()->with('error', 'لا يمكن حذف آخر مدير فعّال.');
        }

        if ($user->isTeacher() && $user->taughtBatches()->exists()) {
            return back()->with('error', 'لهذا المدرس مجموعات دراسية. انقل المجموعات لمدرس آخر أو عطّل الحساب بدل حذفه.');
        }

        if ($user->isStudent() && ($user->payments()->exists() || $user->enrollments()->exists())) {
            return back()->with('error', 'لهذا الطالب تسجيلات أو دفعات مالية. عطّل الحساب بدل حذفه للحفاظ على السجل المالي.');
        }

        if ($user->payrolls()->exists()) {
            return back()->with('error', 'لهذا الحساب سجلات رواتب. عطّل الحساب بدل حذفه.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف الحساب من النظام');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(User::ROLES)],
            'is_active' => 'required|boolean',
            'salary' => 'nullable|numeric|min:0|max:9999999999',
        ]);

        if ($data['role'] === User::ROLE_STUDENT) {
            $data['salary'] = null;
        }

        return $data;
    }

    private function isLastActiveAdmin(User $user): bool
    {
        return ! User::where('role', User::ROLE_ADMIN)->where('is_active', true)->where('id', '!=', $user->id)->exists();
    }
}
