@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="markAllAsRead()">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testNotificationModal">
                <i class="fas fa-paper-plane"></i> Send Test
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Notifications</h6>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}" 
                             onclick="markAsRead('{{ $notification->id }}')">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    @if($notification->read_at)
                                        <i class="fas fa-envelope-open text-muted"></i>
                                    @else
                                        <i class="fas fa-envelope text-primary"></i>
                                    @endif
                                    {{ ucfirst(str_replace('_', ' ', $notification->data['type'] ?? 'notification')) }}
                                </h6>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $notification->data['message'] ?? 'No message' }}</p>
                            @if(isset($notification->data['amount']))
                                <small class="text-success">Amount: ${{ number_format($notification->data['amount'], 2) }}</small>
                            @endif
                            @if(isset($notification->data['due_date']))
                                <small class="text-warning">Due: {{ \Carbon\Carbon::parse($notification->data['due_date'])->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications yet</h5>
                    <p class="text-muted">You'll receive notifications about your loans, payments, and account activities here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Test Notification Modal -->
<div class="modal fade" id="testNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="testNotificationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required 
                                  placeholder="Enter your test message here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Test notification form
document.getElementById('testNotificationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/notifications/send-test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test notification sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('testNotificationModal')).hide();
            this.reset();
        } else {
            alert('Error sending test notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending test notification');
    });
});
</script>
@endsection
