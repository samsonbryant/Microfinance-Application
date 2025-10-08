<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('user')->latest()->paginate(20);
        return view('payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $staff = User::whereHas('roles')->get();
        return view('payrolls.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'month' => 'required|string',
            'year' => 'required|integer',
        ]);

        $validated['net_salary'] = $validated['basic_salary'] + ($validated['allowances'] ?? 0) - ($validated['deductions'] ?? 0);

        Payroll::create($validated);

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll created successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('user');
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $staff = User::whereHas('roles')->get();
        return view('payrolls.edit', compact('payroll', 'staff'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $validated['net_salary'] = $validated['basic_salary'] + ($validated['allowances'] ?? 0) - ($validated['deductions'] ?? 0);

        $payroll->update($validated);

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll updated successfully.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll deleted successfully.');
    }
}

