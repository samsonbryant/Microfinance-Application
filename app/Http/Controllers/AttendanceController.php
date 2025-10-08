<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->paginate(20);
        return view('attendance.index', compact('attendances'));
    }

    public function create()
    {
        $staff = User::whereHas('roles')->get();
        return view('attendance.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'required',
            'check_out' => 'nullable',
            'status' => 'required|in:present,absent,half_day,late',
            'notes' => 'nullable|string',
        ]);

        Attendance::create($validated);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load('user');
        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in' => 'required',
            'check_out' => 'nullable',
            'status' => 'required|in:present,absent,half_day,late',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($validated);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance deleted successfully.');
    }
}

