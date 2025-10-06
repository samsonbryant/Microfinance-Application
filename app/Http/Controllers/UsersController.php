<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = User::with(['branch'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by role if provided
        if (request()->has('role')) {
            $query->where('role', request('role'));
        }

        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('is_active', request('status') === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,officer,clerk',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['branch']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,manager,officer,clerk',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->update($request->all());

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }
}
