<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Dashboard permissions
            'view-dashboard',
            'view-analytics',
            
            // Client management permissions
            'view-clients',
            'create-clients',
            'edit-clients',
            'delete-clients',
            'verify-kyc',
            'suspend-clients',
            'activate-clients',
            
            // Loan management permissions
            'view-loans',
            'create-loans',
            'edit-loans',
            'delete-loans',
            'approve-loans',
            'disburse-loans',
            'collect-payments',
            'manage-overdue',
            
            // Savings management permissions
            'view-savings',
            'create-savings',
            'edit-savings',
            'delete-savings',
            'process-deposits',
            'process-withdrawals',
            
            // Transaction permissions
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'delete-transactions',
            'approve-transactions',
            'reverse-transactions',
            
            // Branch management permissions
            'view-branches',
            'create-branches',
            'edit-branches',
            'delete-branches',
            'manage-branch-staff',
            
            // User management permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'manage-permissions',
            
            // Staff management permissions
            'view-staff',
            'create-staff',
            'edit-staff',
            'delete-staff',
            'manage-payroll',
            'process-payroll',
            
            // Collections and recovery permissions
            'view-collections',
            'manage-collections',
            'view-recovery',
            'manage-recovery',
            'escalate-collections',
            
            // Reporting permissions
            'view-reports',
            'generate-reports',
            'export-reports',
            'view-financial-reports',
            'view-performance-reports',
            
            // Settings permissions
            'view-settings',
            'edit-settings',
            'manage-system',
            'backup-data',
            'clear-cache',
            
            // Borrower portal permissions
            'view-own-loans',
            'view-own-savings',
            'make-payments',
            'view-own-transactions',
            'update-profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createAdminRole();
        $this->createGeneralManagerRole();
        $this->createBranchManagerRole();
        $this->createLoanOfficerRole();
        $this->createHRRole();
        $this->createBorrowerRole();
    }

    private function createAdminRole()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        
        // Admin has all permissions
        $role->givePermissionTo(Permission::all());
    }

    private function createGeneralManagerRole()
    {
        $role = Role::firstOrCreate(['name' => 'general_manager']);
        
        $permissions = [
            'view-dashboard',
            'view-analytics',
            'view-clients',
            'create-clients',
            'edit-clients',
            'verify-kyc',
            'suspend-clients',
            'activate-clients',
            'view-loans',
            'create-loans',
            'edit-loans',
            'approve-loans',
            'disburse-loans',
            'collect-payments',
            'manage-overdue',
            'view-savings',
            'create-savings',
            'edit-savings',
            'process-deposits',
            'process-withdrawals',
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'approve-transactions',
            'view-branches',
            'create-branches',
            'edit-branches',
            'manage-branch-staff',
            'view-users',
            'create-users',
            'edit-users',
            'assign-roles',
            'view-staff',
            'create-staff',
            'edit-staff',
            'manage-payroll',
            'view-collections',
            'manage-collections',
            'view-recovery',
            'manage-recovery',
            'escalate-collections',
            'view-reports',
            'generate-reports',
            'export-reports',
            'view-financial-reports',
            'view-performance-reports',
            'view-settings',
            'edit-settings',
        ];
        
        $role->givePermissionTo($permissions);
    }

    private function createBranchManagerRole()
    {
        $role = Role::firstOrCreate(['name' => 'branch_manager']);
        
        $permissions = [
            'view-dashboard',
            'view-analytics',
            'view-clients',
            'create-clients',
            'edit-clients',
            'verify-kyc',
            'suspend-clients',
            'activate-clients',
            'view-loans',
            'create-loans',
            'edit-loans',
            'approve-loans',
            'disburse-loans',
            'collect-payments',
            'manage-overdue',
            'view-savings',
            'create-savings',
            'edit-savings',
            'process-deposits',
            'process-withdrawals',
            'view-transactions',
            'create-transactions',
            'edit-transactions',
            'approve-transactions',
            'view-users',
            'create-users',
            'edit-users',
            'view-staff',
            'create-staff',
            'edit-staff',
            'view-collections',
            'manage-collections',
            'view-recovery',
            'manage-recovery',
            'view-reports',
            'generate-reports',
            'export-reports',
        ];
        
        $role->givePermissionTo($permissions);
    }

    private function createLoanOfficerRole()
    {
        $role = Role::firstOrCreate(['name' => 'loan_officer']);
        
        $permissions = [
            'view-dashboard',
            'view-clients',
            'create-clients',
            'edit-clients',
            'verify-kyc',
            'view-loans',
            'create-loans',
            'edit-loans',
            'collect-payments',
            'view-savings',
            'create-savings',
            'edit-savings',
            'process-deposits',
            'process-withdrawals',
            'view-transactions',
            'create-transactions',
            'view-collections',
            'manage-collections',
            'view-reports',
            'generate-reports',
        ];
        
        $role->givePermissionTo($permissions);
    }

    private function createHRRole()
    {
        $role = Role::firstOrCreate(['name' => 'hr']);
        
        $permissions = [
            'view-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'assign-roles',
            'view-staff',
            'create-staff',
            'edit-staff',
            'delete-staff',
            'manage-payroll',
            'process-payroll',
            'view-reports',
            'generate-reports',
        ];
        
        $role->givePermissionTo($permissions);
    }

    private function createBorrowerRole()
    {
        $role = Role::firstOrCreate(['name' => 'borrower']);
        
        $permissions = [
            'view-dashboard',
            'view-own-loans',
            'view-own-savings',
            'make-payments',
            'view-own-transactions',
            'update-profile',
        ];
        
        $role->givePermissionTo($permissions);
    }
}