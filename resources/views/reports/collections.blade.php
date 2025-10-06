@extends('layouts.app')

@section('title', 'Collections Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-credit-card me-2"></i>Collections Report</h4>
                <div class="btn-group">
                    <a href="{{ route('reports.export-excel', 'collections') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('reports.export-pdf', 'collections') }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Collections Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">${{ number_format($collectionStats['total_collections'] ?? 0, 0) }}</div>
                    <div class="stat-label">Total Collections</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value">{{ number_format($collectionStats['collection_count'] ?? 0) }}</div>
                    <div class="stat-label">Collection Count</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-value">${{ number_format($collectionStats['average_collection'] ?? 0, 0) }}</div>
                    <div class="stat-label">Average Collection</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-value">${{ number_format($collectionStats['daily_collections'] ?? 0, 0) }}</div>
                    <div class="stat-label">Today's Collections</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collections Chart -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Collections Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="collectionsTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Collections by Method
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="collectionsMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Collection Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Loan</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collections ?? [] as $collection)
                                    <tr>
                                        <td>{{ $collection->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $collection->client->full_name ?? 'N/A' }}</td>
                                        <td>#{{ $collection->loan->id ?? 'N/A' }}</td>
                                        <td>${{ number_format($collection->amount, 2) }}</td>
                                        <td>{{ ucfirst($collection->payment_method ?? 'N/A') }}</td>
                                        <td>{{ $collection->reference ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No collections found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($collections) && $collections->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $collections->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Collections Trend Chart
const trendCtx = document.getElementById('collectionsTrendChart').getContext('2d');
const collectionsTrendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Collections ($)',
            data: [12000, 15000, 18000, 16000, 20000, 22000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Collections Method Chart
const methodCtx = document.getElementById('collectionsMethodChart').getContext('2d');
const collectionsMethodChart = new Chart(methodCtx, {
    type: 'doughnut',
    data: {
        labels: ['Cash', 'Bank Transfer', 'Mobile Money', 'Cheque'],
        datasets: [{
            data: [40, 30, 20, 10],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endsection
