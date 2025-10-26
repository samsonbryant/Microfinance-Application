<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;

class EnsureBorrowerHasClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Only apply to borrower role
        if ($user && $user->hasRole('borrower')) {
            // If user doesn't have a client record, create one automatically
            if (!$user->client) {
                $client = Client::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'first_name' => $user->name,
                        'last_name' => '',
                        'email' => $user->email,
                        'phone' => $user->phone ?? '',
                        'address' => '',
                        'date_of_birth' => null,
                        'national_id' => null,
                        'status' => 'active',
                        'kyc_status' => 'pending',
                        'credit_score' => 0,
                        'branch_id' => $user->branch_id,
                    ]
                );
                
                // Attach client to user
                $user->client_id = $client->id;
                $user->save();
            }
        }
        
        return $next($request);
    }
}

