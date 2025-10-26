<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Microfinance MMS">
    <meta name="description" content="Comprehensive Microfinance Management System">
    <meta name="theme-color" content="#007bff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Microfinance MMS">
    <meta name="msapplication-TileColor" content="#007bff">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
        <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
        <!-- Custom CSS -->
        <style>
            /* App Layout */
            .app-container {
                display: flex;
                min-height: 100vh;
                background: #f8f9fa;
            }
            
            .main-content {
                flex: 1;
                margin-left: 280px;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            
            .top-navbar {
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                padding: 1rem 2rem;
                position: sticky;
                top: 0;
                z-index: 100;
            }
            
            .navbar-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .navbar-left {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            
            .navbar-right {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            
            .main-body {
                flex: 1;
                padding: 2rem;
                background: #f8f9fa;
            }
            
            .sidebar-toggle {
                background: none;
                border: none;
                font-size: 1.25rem;
                color: #6c757d;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 5px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle:hover {
                background: #e9ecef;
                color: #495057;
            }
            
            /* Mobile Responsive */
            @media (max-width: 991.98px) {
                .main-content {
                    margin-left: 0;
                }
                
                .main-body {
                    padding: 1rem;
                }
                
                .top-navbar {
                    padding: 1rem;
                }
            }
            
            /* Dashboard Cards */
            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 2rem;
                color: white;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
                margin-bottom: 1.5rem;
            }
            .stat-card:hover {
                transform: translateY(-5px);
            }
            .stat-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
                opacity: 0.8;
            }
            .stat-value {
                font-size: 2.5rem;
                font-weight: bold;
                margin-bottom: 0.5rem;
            }
            .stat-label {
                font-size: 1rem;
                opacity: 0.9;
            }
            
            /* Page Headers */
            .page-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 2rem 0;
                margin-bottom: 2rem;
                border-radius: 0 0 20px 20px;
            }
            .page-title {
                font-size: 2.5rem;
                font-weight: bold;
                margin-bottom: 0.5rem;
            }
            .page-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                margin: 0;
            }
            
            /* Cards */
            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                margin-bottom: 1.5rem;
            }
            
            .card-header {
                background: white;
                border-bottom: 1px solid #e9ecef;
                border-radius: 15px 15px 0 0 !important;
                padding: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            /* Tables */
            .table {
                margin-bottom: 0;
            }
            
            .table th {
                border-top: none;
                font-weight: 600;
                color: #495057;
                background: #f8f9fa;
            }
            
            /* Buttons */
            .btn {
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .btn:hover {
                transform: translateY(-1px);
            }
            
            /* Responsive Grid */
            @media (max-width: 768px) {
                .stat-card {
                    padding: 1.5rem;
                    margin-bottom: 1rem;
                }
                
                .stat-value {
                    font-size: 2rem;
                }
                
                .stat-icon {
                    font-size: 2rem;
                }
                
                .page-header {
                    padding: 1.5rem 0;
                    margin-bottom: 1rem;
                }
                
                .page-title {
                    font-size: 2rem;
                }
                
                .main-body {
                    padding: 1rem;
                }
            }
            
            @media (max-width: 576px) {
                .stat-card {
                    padding: 1rem;
                }
                
                .stat-value {
                    font-size: 1.5rem;
                }
                
                .stat-icon {
                    font-size: 1.5rem;
                }
                
                .page-title {
                    font-size: 1.5rem;
                }
                
                .navbar-content {
                    flex-direction: column;
                    gap: 1rem;
                    align-items: flex-start;
                }
                
                .navbar-right {
                    width: 100%;
                    justify-content: space-between;
                }
            }

            /* Sidebar Financial Metrics Styles */
            .sidebar-financial-metrics {
                padding: 1rem;
                border-bottom: 1px solid #e9ecef;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            }
            
            .metrics-header {
                margin-bottom: 1rem;
                text-align: center;
            }
            
            .metrics-header h6 {
                color: #2c3e50;
                font-weight: 600;
                margin-bottom: 0.25rem;
            }
            
            .metrics-section {
                margin-bottom: 1.5rem;
            }
            
            .metrics-section:last-child {
                margin-bottom: 0;
            }
            
            .metrics-title {
                font-size: 0.875rem;
                font-weight: 600;
                color: #495057;
                margin-bottom: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .metric-item {
                display: flex;
                align-items: center;
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            
            .metric-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }
            
            .metric-item:last-child {
                margin-bottom: 0;
            }
            
            .metric-icon {
                width: 45px;
                height: 45px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 0.75rem;
                color: white;
                font-size: 1.1rem;
            }
            
            .metric-content {
                flex: 1;
                min-width: 0;
            }
            
            .metric-value {
                font-size: 1.1rem;
                font-weight: 700;
                color: #2c3e50;
                line-height: 1.2;
            }
            
            .metric-label {
                font-size: 0.8rem;
                color: #6c757d;
                font-weight: 500;
                margin-top: 0.25rem;
            }
            
            .metric-amount {
                font-size: 0.75rem;
                color: #28a745;
                font-weight: 600;
                margin-top: 0.25rem;
            }
            
            .metric-sub {
                font-size: 0.7rem;
                color: #6c757d;
                margin-top: 0.25rem;
            }
            
            /* Real-time update indicator */
            .realtime-indicator {
                position: relative;
            }
            
            .realtime-indicator::after {
                content: '';
                position: absolute;
                top: -2px;
                right: -2px;
                width: 8px;
                height: 8px;
                background: #28a745;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.2); }
                100% { opacity: 1; transform: scale(1); }
            }
            
            /* Responsive adjustments for sidebar metrics */
            @media (max-width: 768px) {
                .sidebar-financial-metrics {
                    padding: 0.75rem;
                }
                
                .metric-item {
                    padding: 0.5rem;
                }
                
                .metric-icon {
                    width: 35px;
                    height: 35px;
                    font-size: 0.9rem;
                }
                
                .metric-value {
                    font-size: 1rem;
                }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
    <body class="font-sans antialiased">
        <div class="app-container">
    <!-- Sidebar -->
            @auth
                @php
                    // Ensure role is one of the supported roles; default to 'borrower' if unknown
                    $userRole = auth()->user()->getRoleNames()->first();
                    $allowedRoles = ['admin', 'general_manager', 'branch_manager', 'loan_officer', 'hr', 'borrower'];
                    if (!in_array($userRole, $allowedRoles, true)) {
                        $userRole = 'borrower';
                    }
                @endphp
                <x-sidebar :role="$userRole" />
            @endauth

    <!-- Main Content -->
            <div class="main-content">
                <!-- Top Navigation -->
                <nav class="top-navbar">
                    <div class="navbar-content">
                        <div class="navbar-left">
                            <button class="sidebar-toggle d-lg-none" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                            <h4 class="page-title mb-0">@yield('title', 'Dashboard')</h4>
                </div>
                        <div class="navbar-right">
                            <!-- Notifications -->
                            <div class="dropdown">
                                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell"></i>
                                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                        <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                                    @endif
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Notifications</h6></li>
                                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                        @foreach(auth()->user()->unreadNotifications->take(5) as $notification)
                                            <li><a class="dropdown-item" href="#">{{ $notification->data['message'] ?? 'New notification' }}</a></li>
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('notifications.index') }}">View all notifications</a></li>
                                    @else
                                        <li><a class="dropdown-item" href="#">No new notifications</a></li>
                                    @endif
                                </ul>
            </div>
            
                            <!-- User Menu -->
                            <div class="dropdown">
                                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i>
                                    {{ auth()->user()->name ?? 'User' }}
                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="fas fa-bell me-2"></i>Notifications</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
                                        </form>
                                    </li>
                                </ul>
                                </div>
                </div>
            </div>
                </nav>

                <!-- Page Content -->
                <main class="main-body">
            @yield('content')
        </main>
    </div>
        </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <!-- Custom Scripts -->
        <script>
            // Initialize DataTables
            $(document).ready(function() {
                // Only initialize DataTables on tables that have a thead and are not already initialized
                $('table.table').each(function() {
                    var $table = $(this);
                    
                    // Skip if already initialized
                    if ($.fn.DataTable.isDataTable($table)) {
                        return;
                    }
                    
                    // Only initialize on proper tables
                    if ($table.find('thead').length > 0 && $table.find('tbody').length > 0) {
                        var theadColumnCount = $table.find('thead tr:first th').length;
                        var tbodyColumnCount = $table.find('tbody tr:first td').length;
                        
                        // Only initialize if column counts match
                        if (theadColumnCount === tbodyColumnCount && theadColumnCount > 0) {
                            try {
                                $table.DataTable({
                                    "pageLength": 25,
                                    "responsive": true,
                                    "autoWidth": false,
                                    "language": {
                                        "search": "Search:",
                                        "lengthMenu": "Show _MENU_ entries",
                                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                                        "emptyTable": "No data available",
                                        "paginate": {
                                            "first": "First",
                                            "last": "Last",
                                            "next": "Next",
                                            "previous": "Previous"
                                        }
                                    }
                                });
                            } catch (e) {
                                console.warn('DataTables initialization skipped:', e.message);
                            }
                        }
                    }
                });
                
                // Mobile sidebar toggle
                $('#mobileSidebarToggle').on('click', function() {
                    $('#sidebar').toggleClass('show');
                });
                
                // Close sidebar when clicking outside on mobile
                $(document).on('click', function(e) {
                    if (window.innerWidth < 992) {
                        if (!$(e.target).closest('#sidebar, #mobileSidebarToggle').length) {
                            $('#sidebar').removeClass('show');
                        }
                    }
                });

                // Real-time sidebar updates
                startSidebarUpdates();
            });

            // Real-time sidebar updates function
            function startSidebarUpdates() {
                let lastUpdate = new Date();
                
                // Update sidebar metrics every 30 seconds
                setInterval(function() {
                    updateSidebarMetrics();
                }, 30000);
                
                // Initial update
                updateSidebarMetrics();
            }
            
            function updateSidebarMetrics() {
                // Determine role from the sidebar user-role text content to avoid leaking admin endpoints cross-role
                const roleText = document.querySelector('.user-role')?.textContent?.toLowerCase() || '';
                // Map displayed text back to machine role names
                let role = 'borrower';
                if (roleText.includes('admin')) role = 'admin';
                else if (roleText.includes('general')) role = 'general_manager';
                else if (roleText.includes('branch')) role = 'branch_manager';
                else if (roleText.includes('loan')) role = 'loan_officer';
                else if (roleText.includes('hr')) role = 'hr';
                
                // Determine the correct endpoint based on role
                let endpoint = '/dashboard/data';
                if (role === 'branch_manager') {
                    endpoint = '/branch-manager/dashboard/realtime';
                } else if (role === 'loan_officer') {
                    endpoint = '/loan-officer/dashboard/realtime';
                } else if (role === 'admin') {
                    endpoint = '/admin/dashboard/realtime';
                }
                
                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateSidebarValues(data.data);
                            updateLastUpdateTime();
                        }
                    })
                    .catch(error => {
                        console.error('Error updating sidebar metrics:', error);
                    });
            }
            
            function updateSidebarValues(data) {
                // Update portfolio overview metrics
                if (data.loans_due_today) {
                    updateMetric('loans-due-today', data.loans_due_today.count);
                    updateMetricAmount('loans-due-today', data.loans_due_today.amount);
                }
                
                if (data.overdue_loans) {
                    updateMetric('overdue-loans', data.overdue_loans.count);
                    updateMetricAmount('overdue-loans', data.overdue_loans.amount);
                }
                
                if (data.active_loans) {
                    updateMetric('active-loans', data.active_loans.count);
                    updateMetricAmount('active-loans', data.active_loans.outstanding);
                }
                
                if (data.loan_requests) {
                    updateMetric('loan-requests', data.loan_requests.count);
                    updateMetricAmount('loan-requests', data.loan_requests.amount);
                }
                
                // Update financial performance metrics
                if (data.released_principal) {
                    updateMetricValue('released-principal', data.released_principal.total);
                    updateMetricSub('released-principal', data.released_principal.this_month);
                }
                
                if (data.outstanding_principal) {
                    updateMetricValue('outstanding-principal', data.outstanding_principal.total);
                }
                
                if (data.interest_collected) {
                    updateMetricValue('interest-collected', data.interest_collected.total);
                    updateMetricSub('interest-collected', data.interest_collected.this_month);
                }
                
                if (data.repayments_collected) {
                    updateMetricValue('repayments-collected', data.repayments_collected.total);
                    updateMetricSub('repayments-collected', data.repayments_collected.this_month);
                }
                
                // Update portfolio at risk metrics
                if (data.portfolio_at_risk) {
                    updateMetricValue('par-14', data.portfolio_at_risk['14_day_par'].percentage + '%');
                    updateMetricValue('par-30', data.portfolio_at_risk['30_day_par'].percentage + '%');
                    updateMetricValue('par-total', data.portfolio_at_risk.total_par.percentage + '%');
                }
                
                // Update active borrowers
                if (data.active_borrowers) {
                    updateMetric('active-borrowers', data.active_borrowers.count);
                    updateMetricSub('active-borrowers', data.active_borrowers.percentage + '% of total');
                }
            }
            
            function updateMetric(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                    element.classList.add('realtime-indicator');
                    setTimeout(() => element.classList.remove('realtime-indicator'), 2000);
                }
            }
            
            function updateMetricValue(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    const formattedValue = value >= 1000 ? '$' + Math.round(value / 1000) + 'K' : '$' + Math.round(value);
                    element.textContent = formattedValue;
                    element.classList.add('realtime-indicator');
                    setTimeout(() => element.classList.remove('realtime-indicator'), 2000);
                }
            }
            
            function updateMetricAmount(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    const amountElement = element.parentElement.querySelector('.metric-amount');
                    if (amountElement) {
                        amountElement.textContent = '$' + Math.round(value).toLocaleString();
                    }
                }
            }
            
            function updateMetricSub(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    const subElement = element.parentElement.querySelector('.metric-sub');
                    if (subElement) {
                        const formattedValue = value >= 1000 ? '$' + Math.round(value / 1000) + 'K' : '$' + Math.round(value);
                        subElement.textContent = 'This Month: ' + formattedValue;
                    }
                }
            }
            
            function updateLastUpdateTime() {
                const lastUpdateElement = document.getElementById('last-update');
                if (lastUpdateElement) {
                    const now = new Date();
                    lastUpdateElement.textContent = 'Updated: ' + now.toLocaleTimeString();
                }
            }

            // PWA Installation
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showInstallButton();
            });

            function showInstallButton() {
                if (deferredPrompt) {
                    const installButton = document.createElement('button');
                    installButton.innerHTML = '📱 Install App';
                    installButton.className = 'btn btn-primary position-fixed';
                    installButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,123,255,0.3);';
                    installButton.onclick = installApp;
                    document.body.appendChild(installButton);
                }
            }

            function installApp() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        }
                        deferredPrompt = null;
                    });
                }
            }

            // Service Worker Registration
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('SW registered: ', registration);
                        })
                        .catch((registrationError) => {
                            console.log('SW registration failed: ', registrationError);
                        });
                });
            }
        </script>

        @yield('scripts')
</body>
</html>
