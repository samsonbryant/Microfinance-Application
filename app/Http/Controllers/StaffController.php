<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'general_manager', 'branch_manager', 'loan_officer', 'hr']);
        })->with('roles', 'branch')->paginate(20);

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    public function show(User $staff)
    {
        $staff->load('roles', 'branch');
        return view('staff.show', compact('staff'));
    }

    public function edit(User $staff)
    {
        $staff->load('roles');
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'branch_id' => $validated['branch_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $staff->syncRoles([$validated['role']]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }
    
    /**
     * Activate staff member
     */
    public function activate(User $staff)
    {
        $staff->update(['is_active' => true]);
        
        activity()
            ->performedOn($staff)
            ->causedBy(auth()->user())
            ->log("Staff member activated: {$staff->name}");
        
        return back()->with('success', 'Staff member activated successfully.');
    }
    
    /**
     * Deactivate staff member
     */
    public function deactivate(User $staff)
    {
        $staff->update(['is_active' => false]);
        
        activity()
            ->performedOn($staff)
            ->causedBy(auth()->user())
            ->log("Staff member deactivated: {$staff->name}");
        
        return back()->with('success', 'Staff member deactivated successfully.');
    }
}

