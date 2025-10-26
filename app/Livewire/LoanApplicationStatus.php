<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class LoanApplicationStatus extends Component
{
    public $applications;
    public $pendingCount = 0;
    public $approvedCount = 0;
    public $rejectedCount = 0;
    public $disbursedCount = 0;

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
        $client = $user->client;

        if ($client) {
            $this->applications = $client->loans()
                ->orderBy('created_at', 'desc')
                ->get();

            $this->pendingCount = $this->applications->where('status', 'pending')->count();
            $this->approvedCount = $this->applications->where('status', 'approved')->count();
            $this->rejectedCount = $this->applications->where('status', 'rejected')->count();
            $this->disbursedCount = $this->applications->whereIn('status', ['disbursed', 'active'])->count();
        } else {
            $this->applications = collect([]);
        }
    }

    public function refreshApplications()
    {
        $this->loadApplications();
        $this->dispatch('applicationsUpdated');
    }

    public function render()
    {
        return view('livewire.loan-application-status');
    }
}

