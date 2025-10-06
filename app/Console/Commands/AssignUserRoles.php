<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignUserRoles extends Command
{
    protected $signature = 'users:assign-roles';
    protected $description = 'Assign default roles to users who don\'t have any roles';

    public function handle()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            if (!$user->hasAnyRole(['admin', 'general_manager', 'loan_officer', 'hr', 'borrower'])) {
                $user->assignRole('borrower');
                $this->info("Assigned borrower role to {$user->email}");
            } else {
                $this->line("User {$user->email} already has roles: " . $user->roles->pluck('name')->join(', '));
            }
        }
        
        $this->info('Role assignment completed!');
    }
}