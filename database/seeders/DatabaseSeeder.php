<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            AccountingPermissionsSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);

        // Create test branch
        $branch = \App\Models\Branch::firstOrCreate(
            ['code' => 'MB001'],
            [
                'name' => 'Main Branch',
                'address' => '123 Main Street',
                'city' => 'City',
                'state' => 'State',
                'country' => 'Country',
                'phone' => '+1234567890',
                'email' => 'main@microfinance.com',
                'manager_name' => 'Branch Manager',
                'is_active' => true,
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@microfinance.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create general manager
        $gm = User::firstOrCreate(
            ['email' => 'gm@microfinance.com'],
            [
                'name' => 'General Manager',
                'username' => 'gm',
                'password' => Hash::make('gm123'),
                'role' => 'general_manager',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$gm->hasRole('general_manager')) {
            $gm->assignRole('general_manager');
        }

        // Create branch manager
        $bm = User::firstOrCreate(
            ['email' => 'bm@microfinance.com'],
            [
                'name' => 'Branch Manager',
                'username' => 'bm',
                'password' => Hash::make('bm123'),
                'role' => 'branch_manager',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$bm->hasRole('branch_manager')) {
            $bm->assignRole('branch_manager');
        }

        // Create loan officer
        $lo = User::firstOrCreate(
            ['email' => 'lo@microfinance.com'],
            [
                'name' => 'Loan Officer',
                'username' => 'lo',
                'password' => Hash::make('lo123'),
                'role' => 'loan_officer',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$lo->hasRole('loan_officer')) {
            $lo->assignRole('loan_officer');
        }

        // Create HR user
        $hr = User::firstOrCreate(
            ['email' => 'hr@microfinance.com'],
            [
                'name' => 'HR Manager',
                'username' => 'hr',
                'password' => Hash::make('hr123'),
                'role' => 'hr',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$hr->hasRole('hr')) {
            $hr->assignRole('hr');
        }

        // Create accountant user
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@microfinance.com'],
            [
                'name' => 'Accountant',
                'username' => 'accountant',
                'password' => Hash::make('accountant123'),
                'role' => 'accountant',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$accountant->hasRole('accountant')) {
            $accountant->assignRole('accountant');
        }

        // Create sample borrower
        $borrower = User::firstOrCreate(
            ['email' => 'borrower@microfinance.com'],
            [
                'name' => 'John Doe',
                'username' => 'borrower',
                'password' => Hash::make('borrower123'),
                'role' => 'borrower',
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (!$borrower->hasRole('borrower')) {
            $borrower->assignRole('borrower');
        }
    }
}
