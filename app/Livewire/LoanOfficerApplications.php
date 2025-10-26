<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class LoanOfficerApplications extends Component
{
    public $applications;
    public $pendingCount = 0;
    public $underReviewCount = 0;
    public $approvedCount = 0;

    protected $listeners = [
        'loan.application.submitted' => 'refreshApplications',
        'loan.application.reviewed' => 'refreshApplications',
        'loan.approved' => 'refreshApplications',
        'loan.updated' => 'refreshApplications',
    ];

    public function mount()
    {
        $this->loadApplications();
    }

    public function loadApplications()
    {
        $user = Auth::user();
        
        // Loan officers see applications from their branch
        if ($user->hasRole('loan_officer')) {
            $this->applications = Loan::with(['client', 'branch', 'createdBy'])
                ->where('branch_id', $user->branch_id)
                ->whereIn('status', ['pending', 'under_review', 'approved'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->hasRole('branch_manager')) {
            // Branch managers see all from their branch
            $this->applications = Loan::with(['client', 'branch', 'createdBy'])
                ->where('branch_id', $user->branch_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->applications = collect([]);
        }

        $this->pendingCount = $this->applications->where('status', 'pending')->count();
        $this->underReviewCount = $this->applications->where('status', 'under_review')->count();
        $this->approvedCount = $this->applications->where('status', 'approved')->count();
    }

    public function refreshApplications()
    {
        $this->loadApplications();
        $this->dispatch('applicationsRefreshed');
    }

    public function moveToReview($loanId)
    {
        $loan = Loan::find($loanId);
        
        if ($loan && $loan->status === 'pending') {
            $loan->update([
                'status' => 'under_review',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Loan Officer moved loan {$loan->loan_number} to under review");
                
            $this->dispatch('success', 'Loan moved to under review');
            $this->refreshApplications();
        }
    }

    public function render()
    {
        return view('livewire.loan-officer-applications');
    }
}

