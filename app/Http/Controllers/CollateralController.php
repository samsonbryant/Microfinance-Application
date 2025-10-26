<?php

namespace App\Http\Controllers;

use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollateralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Collateral::with(['client', 'loans']);
        
        // Filter by branch if not admin
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->whereHas('client', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'LIKE', "%{$search}%")
                                  ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('value_range')) {
            switch ($request->value_range) {
                case '0-1000':
                    $query->whereBetween('value', [0, 1000]);
                    break;
                case '1000-5000':
                    $query->whereBetween('value', [1000, 5000]);
                    break;
                case '5000-10000':
                    $query->whereBetween('value', [5000, 10000]);
                    break;
                case '10000-50000':
                    $query->whereBetween('value', [10000, 50000]);
                    break;
                case '50000+':
                    $query->where('value', '>=', 50000);
                    break;
            }
        }
        
        $collaterals = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('collaterals.index', compact('collaterals'));
    }

    public function create()
    {
        $user = auth()->user();
        $clients = Client::when(!$user->hasRole('admin') && $user->branch_id, function($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        })->where('status', 'active')->orderBy('first_name')->get();
        
        return view('collaterals.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'condition' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'documents.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();
            
            // Handle file uploads
            $documents = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filepath = $file->storeAs('collateral-documents', $filename, 'public');
                    $documents[] = [
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $filepath,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
            }
            
            $validated['documents'] = $documents;
            $validated['status'] = 'pending';
            $validated['valuation_date'] = now();

            $collateral = Collateral::create($validated);
            
            // Log activity
            activity()
                ->performedOn($collateral)
                ->causedBy(auth()->user())
                ->log("Collateral created: {$collateral->type} - {$collateral->description}");
            
            DB::commit();

            return redirect()->route('collaterals.index')
                ->with('success', 'Collateral created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Collateral creation error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating collateral: ' . $e->getMessage());
        }
    }

    public function show(Collateral $collateral)
    {
        // Check if user has permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $collateral->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to collateral.');
        }
        
        $collateral->load(['client', 'loans.client', 'valuedBy']);
        return view('collaterals.show', compact('collateral'));
    }

    public function edit(Collateral $collateral)
    {
        // Check if user has permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $collateral->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to collateral.');
        }
        
        $clients = Client::when(!$user->hasRole('admin') && $user->branch_id, function($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        })->where('status', 'active')->orderBy('first_name')->get();
        
        return view('collaterals.edit', compact('collateral', 'clients'));
    }

    public function update(Request $request, Collateral $collateral)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'condition' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'documents.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();
            
            // Handle file uploads if new files provided
            if ($request->hasFile('documents')) {
                $documents = $collateral->documents ?? [];
                
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filepath = $file->storeAs('collateral-documents', $filename, 'public');
                    $documents[] = [
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $filepath,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
                
                $validated['documents'] = $documents;
            }

            $collateral->update($validated);
            
            // Log activity
            activity()
                ->performedOn($collateral)
                ->causedBy(auth()->user())
                ->log("Collateral updated: {$collateral->type} - {$collateral->description}");
            
            DB::commit();

            return redirect()->route('collaterals.show', $collateral)
                ->with('success', 'Collateral updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Collateral update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating collateral: ' . $e->getMessage());
        }
    }

    public function destroy(Collateral $collateral)
    {
        try {
            // Check if collateral is still active on any loans
            if ($collateral->loans()->whereIn('status', ['active', 'pending', 'approved'])->exists()) {
                return back()->with('error', 'Cannot delete collateral that is still active on loans.');
            }
            
            $description = $collateral->description;
            
            // Delete associated documents
            if ($collateral->documents) {
                foreach ($collateral->documents as $document) {
                    if (isset($document['path']) && \Storage::disk('public')->exists($document['path'])) {
                        \Storage::disk('public')->delete($document['path']);
                    }
                }
            }
            
            $collateral->delete();
            
            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->log("Collateral deleted: {$description}");
            
            return redirect()->route('collaterals.index')
                ->with('success', 'Collateral deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Collateral deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting collateral: ' . $e->getMessage());
        }
    }

    /**
     * Verify a collateral
     */
    public function verify(Request $request, Collateral $collateral)
    {
        try {
            $collateral->update([
                'status' => 'active',
                'valuation_date' => now(),
                'valued_by' => auth()->id(),
            ]);
            
            // Log activity
            activity()
                ->performedOn($collateral)
                ->causedBy(auth()->user())
                ->log("Collateral verified: {$collateral->type} - {$collateral->description}");
            
            return response()->json([
                'success' => true,
                'message' => 'Collateral verified successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Collateral verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error verifying collateral: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download collateral document
     */
    public function downloadDocument(Collateral $collateral, $documentIndex)
    {
        // Check if user has permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $collateral->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to document.');
        }
        
        if (!$collateral->documents || !isset($collateral->documents[$documentIndex])) {
            return back()->with('error', 'Document not found.');
        }
        
        $document = $collateral->documents[$documentIndex];
        
        if (!\Storage::disk('public')->exists($document['path'])) {
            return back()->with('error', 'File not found.');
        }
        
        return \Storage::disk('public')->download($document['path'], $document['original_name']);
    }
}