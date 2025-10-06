<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function getStats()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = $this->getBranchQuery($branchId, $user->role);

        $stats = [
            'total_clients' => $query(Client::class)->count(),
            'total_loans' => $query(Loan::class)->count(),
            'active_loans' => $query(Loan::class)->where('status', 'active')->count(),
            'overdue_loans' => $query(Loan::class)->where('status', 'overdue')->count(),
            'pending_applications' => $query(LoanApplication::class)->pending()->count(),
            'total_savings' => $query(SavingsAccount::class)->where('status', 'active')->sum('balance'),
            'total_portfolio' => $query(Loan::class)->whereIn('status', ['active', 'overdue', 'disbursed'])->sum('outstanding_balance'),
        ];

        return response()->json($stats);
    }

    public function getNotifications()
    {
        $user = auth()->user();
        
        // Get recent notifications
        $notifications = Notification::where('user_id', $user->id)
            ->orWhere('client_id', function($query) use ($user) {
                $query->select('id')
                    ->from('clients')
                    ->where('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->category,
                    'created_at' => $notification->created_at,
                    'is_read' => $notification->read_at !== null,
                ];
            });

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    private function getBranchQuery($branchId, $userRole = null)
    {
        return function ($model) use ($branchId, $userRole) {
            $query = $model::query();
            
            if ($branchId && $userRole !== 'admin') {
                $query->where('branch_id', $branchId);
            }
            
            return $query;
        };
    }
}
