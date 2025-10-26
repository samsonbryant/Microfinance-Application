<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KycDocument;
use App\Models\Client;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class KycDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display KYC documents for a client
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = KycDocument::with(['client', 'uploadedBy']);
        
        // Filter by branch if not admin
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->whereHas('client', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        // Filter by client if specified
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by document type
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }
        
        // Filter by verification status
        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        
        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get clients for filter dropdown
        $clients = Client::when(!$user->hasRole('admin') && $user->branch_id, function($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        })->orderBy('first_name')->get();
        
        return view('kyc-documents.index', compact('documents', 'clients'));
    }

    /**
     * Show the form for creating a new KYC document
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $clients = Client::when(!$user->hasRole('admin') && $user->branch_id, function($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        })->where('status', 'active')->orderBy('first_name')->get();
        
        $selectedClientId = $request->get('client_id');
        
        return view('kyc-documents.create', compact('clients', 'selectedClientId'));
    }

    /**
     * Store a newly created KYC document
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document_type' => 'required|in:national_id,passport,driving_license,birth_certificate,utility_bill,bank_statement,salary_slip,business_license,tax_certificate,other',
            'document_file' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'issuing_authority' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();
            
            // Store the uploaded file
            $file = $request->file('document_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('kyc-documents', $filename, 'public');
            
            // Create KYC document record
            $kycDocument = KycDocument::create([
                'client_id' => $request->client_id,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'file_path' => $filepath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'issuing_authority' => $request->issuing_authority,
                'notes' => $request->notes,
                'verification_status' => 'pending',
                'uploaded_by' => auth()->id(),
            ]);
            
            // Update client KYC status if this is a required document
            $client = Client::find($request->client_id);
            $this->updateClientKycStatus($client);
            
            // Log activity
            activity()
                ->performedOn($kycDocument)
                ->causedBy(auth()->user())
                ->log("KYC document uploaded: {$request->document_type} for client {$client->first_name} {$client->last_name}");
            
            DB::commit();
            
            return redirect()->route('kyc-documents.index', ['client_id' => $request->client_id])
                ->with('success', 'KYC document uploaded successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('KYC document upload error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified KYC document
     */
    public function show(KycDocument $kycDocument)
    {
        // Check if user has permission to view this document
        $user = auth()->user();
        if (!$user->hasRole('admin') && $kycDocument->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to document.');
        }
        
        return view('kyc-documents.show', compact('kycDocument'));
    }

    /**
     * Show the form for editing the specified KYC document
     */
    public function edit(KycDocument $kycDocument)
    {
        // Check if user has permission to edit this document
        $user = auth()->user();
        if (!$user->hasRole('admin') && $kycDocument->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to document.');
        }
        
        return view('kyc-documents.edit', compact('kycDocument'));
    }

    /**
     * Update the specified KYC document
     */
    public function update(Request $request, KycDocument $kycDocument)
    {
        $request->validate([
            'document_type' => 'required|in:national_id,passport,driving_license,birth_certificate,utility_bill,bank_statement,salary_slip,business_license,tax_certificate,other',
            'document_file' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'issuing_authority' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();
            
            $updateData = [
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'issuing_authority' => $request->issuing_authority,
                'notes' => $request->notes,
            ];
            
            // Handle file replacement if new file uploaded
            if ($request->hasFile('document_file')) {
                // Delete old file
                if ($kycDocument->file_path && Storage::disk('public')->exists($kycDocument->file_path)) {
                    Storage::disk('public')->delete($kycDocument->file_path);
                }
                
                // Store new file
                $file = $request->file('document_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filepath = $file->storeAs('kyc-documents', $filename, 'public');
                
                $updateData['file_path'] = $filepath;
                $updateData['original_filename'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
                $updateData['verification_status'] = 'pending'; // Reset verification status
            }
            
            $kycDocument->update($updateData);
            
            // Update client KYC status
            $this->updateClientKycStatus($kycDocument->client);
            
            // Log activity
            activity()
                ->performedOn($kycDocument)
                ->causedBy(auth()->user())
                ->log("KYC document updated: {$kycDocument->document_type} for client {$kycDocument->client->first_name} {$kycDocument->client->last_name}");
            
            DB::commit();
            
            return redirect()->route('kyc-documents.show', $kycDocument)
                ->with('success', 'KYC document updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('KYC document update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating document: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified KYC document
     */
    public function destroy(KycDocument $kycDocument)
    {
        try {
            // Delete the file from storage
            if ($kycDocument->file_path && Storage::disk('public')->exists($kycDocument->file_path)) {
                Storage::disk('public')->delete($kycDocument->file_path);
            }
            
            $clientName = $kycDocument->client->first_name . ' ' . $kycDocument->client->last_name;
            $documentType = $kycDocument->document_type;
            $client = $kycDocument->client;
            
            $kycDocument->delete();
            
            // Update client KYC status
            $this->updateClientKycStatus($client);
            
            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->log("KYC document deleted: {$documentType} for client {$clientName}");
            
            return redirect()->route('kyc-documents.index')
                ->with('success', 'KYC document deleted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('KYC document deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }

    /**
     * Verify a KYC document
     */
    public function verify(Request $request, KycDocument $kycDocument)
    {
        $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $kycDocument->update([
                'verification_status' => $request->verification_status,
                'verification_notes' => $request->verification_notes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
            
            // Update client KYC status
            $this->updateClientKycStatus($kycDocument->client);
            
            // Log activity
            activity()
                ->performedOn($kycDocument)
                ->causedBy(auth()->user())
                ->log("KYC document {$request->verification_status}: {$kycDocument->document_type} for client {$kycDocument->client->first_name} {$kycDocument->client->last_name}");
            
            return response()->json([
                'success' => true,
                'message' => "Document {$request->verification_status} successfully!"
            ]);
            
        } catch (\Exception $e) {
            \Log::error('KYC document verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error verifying document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a KYC document
     */
    public function download(KycDocument $kycDocument)
    {
        // Check if user has permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $kycDocument->client->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to document.');
        }
        
        if (!Storage::disk('public')->exists($kycDocument->file_path)) {
            return back()->with('error', 'File not found.');
        }
        
        return Storage::disk('public')->download($kycDocument->file_path, $kycDocument->original_filename);
    }

    /**
     * Update client KYC status based on uploaded documents
     */
    private function updateClientKycStatus(Client $client)
    {
        $documents = $client->kycDocuments;
        
        $requiredTypes = ['national_id', 'utility_bill']; // Basic requirements
        $verifiedDocuments = $documents->where('verification_status', 'verified');
        
        $hasRequiredDocs = collect($requiredTypes)->every(function($type) use ($verifiedDocuments) {
            return $verifiedDocuments->where('document_type', $type)->isNotEmpty();
        });
        
        if ($hasRequiredDocs) {
            $client->update(['kyc_status' => 'verified']);
        } elseif ($documents->where('verification_status', 'pending')->isNotEmpty()) {
            $client->update(['kyc_status' => 'pending']);
        } else {
            $client->update(['kyc_status' => 'incomplete']);
        }
    }
}