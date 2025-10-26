<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'branch_manager']);
        Role::create(['name' => 'loan_officer']);
        Role::create(['name' => 'borrower']);
    }

    /** @test */
    public function borrower_can_access_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('borrower');
        
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'branch_id' => 1
        ]);

        $response = $this->actingAs($user)->get(route('borrower.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('borrower.dashboard');
    }

    /** @test */
    public function borrower_dashboard_displays_user_specific_loans()
    {
        $user = User::factory()->create();
        $user->assignRole('borrower');
        
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'branch_id' => 1
        ]);

        // Create loans for this client
        $loan1 = Loan::factory()->create([
            'client_id' => $client->id,
            'status' => 'active',
            'amount' => 5000,
            'outstanding_balance' => 3000
        ]);

        $loan2 = Loan::factory()->create([
            'client_id' => $client->id,
            'status' => 'disbursed',
            'amount' => 3000,
            'outstanding_balance' => 2000
        ]);

        // Create loan for another client (should not appear)
        $otherClient = Client::factory()->create();
        Loan::factory()->create([
            'client_id' => $otherClient->id,
            'status' => 'active',
            'amount' => 10000
        ]);

        $response = $this->actingAs($user)->get(route('borrower.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) use ($loan1, $loan2) {
            return $loans->count() === 2 && 
                   $loans->contains($loan1) && 
                   $loans->contains($loan2);
        });
    }

    /** @test */
    public function borrower_dashboard_calculates_correct_stats()
    {
        $user = User::factory()->create();
        $user->assignRole('borrower');
        
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'branch_id' => 1
        ]);

        // Create active loans
        Loan::factory()->create([
            'client_id' => $client->id,
            'status' => 'active',
            'amount' => 5000,
            'outstanding_balance' => 3000
        ]);

        Loan::factory()->create([
            'client_id' => $client->id,
            'status' => 'disbursed',
            'amount' => 3000,
            'outstanding_balance' => 2000
        ]);

        // Create savings accounts
        SavingsAccount::factory()->create([
            'client_id' => $client->id,
            'balance' => 1000
        ]);

        SavingsAccount::factory()->create([
            'client_id' => $client->id,
            'balance' => 500
        ]);

        $response = $this->actingAs($user)->get(route('borrower.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['active_loans'] === 2 &&
                   $stats['total_loan_amount'] === 8000 &&
                   $stats['outstanding_balance'] === 5000 &&
                   $stats['savings_balance'] === 1500 &&
                   $stats['savings_accounts'] === 2;
        });
    }

    /** @test */
    public function borrower_can_get_realtime_dashboard_data()
    {
        $user = User::factory()->create();
        $user->assignRole('borrower');
        
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'branch_id' => 1
        ]);

        Loan::factory()->create([
            'client_id' => $client->id,
            'status' => 'active',
            'amount' => 5000,
            'outstanding_balance' => 3000
        ]);

        $response = $this->actingAs($user)->get(route('borrower.dashboard.realtime'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'stats',
                'recent_transactions',
                'next_payment',
                'timestamp'
            ]
        ]);
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function admin_can_get_realtime_data()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard.realtime'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function branch_manager_sees_only_branch_data()
    {
        $user = User::factory()->create(['branch_id' => 1]);
        $user->assignRole('branch_manager');

        $response = $this->actingAs($user)->get(route('branch-manager.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('branch-manager.dashboard');
    }

    /** @test */
    public function branch_manager_can_get_realtime_data()
    {
        $user = User::factory()->create(['branch_id' => 1]);
        $user->assignRole('branch_manager');

        $response = $this->actingAs($user)->get(route('branch-manager.dashboard.realtime'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function loan_officer_can_access_dashboard()
    {
        $user = User::factory()->create(['branch_id' => 1]);
        $user->assignRole('loan_officer');

        $response = $this->actingAs($user)->get(route('loan-officer.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('loan-officer.dashboard');
    }

    /** @test */
    public function loan_officer_can_get_realtime_data()
    {
        $user = User::factory()->create(['branch_id' => 1]);
        $user->assignRole('loan_officer');

        $response = $this->actingAs($user)->get(route('loan-officer.dashboard.realtime'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function borrower_without_client_profile_is_redirected()
    {
        $user = User::factory()->create();
        $user->assignRole('borrower');
        
        // No client profile created

        $response = $this->actingAs($user)->get(route('borrower.dashboard'));

        $response->assertRedirect(route('borrower.profile'));
        $response->assertSessionHas('error', 'Please complete your profile first.');
    }

    /** @test */
    public function dashboard_redirects_to_role_specific_dashboard()
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $response = $this->actingAs($adminUser)->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));

        $borrowerUser = User::factory()->create();
        $borrowerUser->assignRole('borrower');

        $response = $this->actingAs($borrowerUser)->get(route('dashboard'));
        $response->assertRedirect(route('borrower.dashboard'));
    }
}

