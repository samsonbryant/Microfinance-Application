<?php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycDocumentController extends Controller
{
    public function index()
    {
        $documents = KycDocument::with('client')->latest()->paginate(20);
        return view('kyc-documents.index', compact('documents'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('kyc-documents.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('kyc-documents', 'public');
            $validated['document_path'] = $path;
        }

        $validated['status'] = 'pending';
        
        KycDocument::create($validated);

        return redirect()->route('kyc-documents.index')
            ->with('success', 'KYC document uploaded successfully.');
    }

    public function show(KycDocument $kycDocument)
    {
        $kycDocument->load('client');
        return view('kyc-documents.show', compact('kycDocument'));
    }

    public function edit(KycDocument $kycDocument)
    {
        $clients = Client::all();
        return view('kyc-documents.edit', compact('kycDocument', 'clients'));
    }

    public function update(Request $request, KycDocument $kycDocument)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($request->hasFile('document_file')) {
            // Delete old file
            if ($kycDocument->document_path) {
                Storage::disk('public')->delete($kycDocument->document_path);
            }
            $path = $request->file('document_file')->store('kyc-documents', 'public');
            $validated['document_path'] = $path;
        }

        $kycDocument->update($validated);

        return redirect()->route('kyc-documents.index')
            ->with('success', 'KYC document updated successfully.');
    }

    public function destroy(KycDocument $kycDocument)
    {
        if ($kycDocument->document_path) {
            Storage::disk('public')->delete($kycDocument->document_path);
        }
        
        $kycDocument->delete();

        return redirect()->route('kyc-documents.index')
            ->with('success', 'KYC document deleted successfully.');
    }
}

