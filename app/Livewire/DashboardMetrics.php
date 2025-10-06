<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardMetrics extends Component
{
    public $metrics = [];
    public $role;
    public $branchId;
    public $refreshInterval = 30; // seconds

    protected $listeners = ['refreshMetrics'];

    public function mount()
    {
        $this->role = Auth::user()->getRoleNames()->first() ?? 'admin';
        $this->branchId = Auth::user()->branch_id;
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        $dashboardService = app(DashboardService::class);
        $this->metrics = $dashboardService->getMetrics($this->role, $this->branchId);
    }

    public function refreshMetrics()
    {
        $this->loadMetrics();
        $this->dispatch('metricsUpdated');
    }

    public function render()
    {
        return view('livewire.dashboard-metrics');
    }
}