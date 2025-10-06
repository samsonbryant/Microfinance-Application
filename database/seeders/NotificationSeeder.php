<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Client;
use App\Models\Loan;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $clients = Client::limit(3)->get();
        $loans = Loan::limit(2)->get();

        $notifications = [
            [
                'notification_type' => 'in_app',
                'category' => 'loan_approved',
                'title' => 'Loan Application Approved',
                'message' => 'Loan application LOAN-000001 has been approved for $15,000',
                'user_id' => $admin->id,
                'client_id' => $clients->first()->id ?? null,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subHours(2),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'payment_received',
                'title' => 'Payment Received',
                'message' => 'Payment of $500 received from John Doe for loan LOAN-000001',
                'user_id' => $admin->id,
                'client_id' => $clients->first()->id ?? null,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subHours(4),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'overdue_alert',
                'title' => 'Overdue Loan Alert',
                'message' => 'Loan LOAN-000002 is 5 days overdue. Amount: $2,500',
                'user_id' => $admin->id,
                'client_id' => $clients->skip(1)->first()->id ?? null,
                'status' => 'delivered',
                'priority' => 'high',
                'created_at' => now()->subHours(6),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'kyc_verified',
                'title' => 'KYC Verification Complete',
                'message' => 'KYC documents for Jane Smith have been verified and approved',
                'user_id' => $admin->id,
                'client_id' => $clients->skip(2)->first()->id ?? null,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subHours(8),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'system',
                'title' => 'System Maintenance',
                'message' => 'Scheduled system maintenance will occur tonight from 2:00 AM to 4:00 AM',
                'user_id' => $admin->id,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subHours(12),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'loan_approved',
                'title' => 'New Loan Application',
                'message' => 'New loan application received from Michael Johnson for $8,500',
                'user_id' => $admin->id,
                'client_id' => $clients->first()->id ?? null,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subDays(1),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'payment_received',
                'title' => 'Bulk Payment Received',
                'message' => 'Bulk payment of $2,500 received from 5 clients',
                'user_id' => $admin->id,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subDays(2),
            ],
            [
                'notification_type' => 'in_app',
                'category' => 'system',
                'title' => 'Monthly Report Ready',
                'message' => 'Monthly financial report for October 2025 is ready for review',
                'user_id' => $admin->id,
                'status' => 'delivered',
                'priority' => 'normal',
                'created_at' => now()->subDays(3),
            ]
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
