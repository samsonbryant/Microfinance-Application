@extends('layouts.app')

@section('title', 'Create Journal Entry')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice text-primary me-2"></i>Create Journal Entry
            </h1>
            <p class="text-muted mb-0">Manual double-entry accounting transaction</p>
        </div>
        <a href="{{ route('accounting.journal-entries') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Journal Entries
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Journal Entry Form</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('accounting.journal-entries.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date" id="transaction_date" 
                               class="form-control @error('transaction_date') is-invalid @enderror" 
                               value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" 
                               class="form-control @error('reference_number') is-invalid @enderror" 
                               value="{{ old('reference_number') }}" placeholder="JE-001">
                        @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="2" required placeholder="Describe this journal entry...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3">Journal Entry Lines</h6>
                
                <div id="journal-lines">
                    <!-- Debit Line -->
                    <div class="row mb-2">
                        <div class="col-md-5">
                            <select name="lines[0][account_id]" class="form-select" required>
                                <option value="">Select Debit Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="lines[0][debit]" class="form-control" placeholder="Debit Amount" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="lines[0][credit]" class="form-control" placeholder="Credit Amount (0)" value="0" readonly>
                        </div>
                    </div>

                    <!-- Credit Line -->
                    <div class="row mb-2">
                        <div class="col-md-5">
                            <select name="lines[1][account_id]" class="form-select" required>
                                <option value="">Select Credit Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="lines[1][debit]" class="form-control" placeholder="Debit Amount (0)" value="0" readonly>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="lines[1][credit]" class="form-control" placeholder="Credit Amount" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Journal Entry
                    </button>
                    <a href="{{ route('accounting.journal-entries') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

