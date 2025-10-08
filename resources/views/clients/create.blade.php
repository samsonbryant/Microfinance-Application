@extends('layouts.app')

@section('title', 'Add a Borrower')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-user-plus me-2"></i>Add a Borrower</h1>
    <p class="page-subtitle">Create a new client/borrower profile</p>
</div>

<form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" id="clientForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                </div>
                <div class="card-body">
                    <!-- Avatar Upload -->
                    <div class="mb-4">
                        <label class="form-label">Avatar</label>
                        <div class="avatar-upload">
                            <div class="avatar-preview" id="avatarPreview">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                   name="avatar" id="avatar" accept=".jpeg,.jpg,.png">
                            <small class="text-muted">Allowed *.jpeg, *.jpg, *.png, max size 10MB</small>
                            @error('avatar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" 
                                       placeholder="First name" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" 
                                       placeholder="Last name" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" name="gender" required>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="marital_status" class="form-label">Marital Status</label>
                                <select class="form-select @error('marital_status') is-invalid @enderror" 
                                        id="marital_status" name="marital_status">
                                    <option value="single" {{ old('marital_status') == 'single' ? 'selected' : 'selected' }}>Single</option>
                                    <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                                @error('marital_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identification Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Identification Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="identification_type" class="form-label">Identification Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('identification_type') is-invalid @enderror" 
                                        id="identification_type" name="identification_type" required>
                                    <option value="national_id" {{ old('identification_type') == 'national_id' ? 'selected' : 'selected' }}>National ID</option>
                                    <option value="passport" {{ old('identification_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="drivers_license" {{ old('identification_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                                    <option value="voter_id" {{ old('identification_type') == 'voter_id' ? 'selected' : '' }}>Voter ID</option>
                                </select>
                                @error('identification_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="identification_number" class="form-label">Identification Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('identification_number') is-invalid @enderror" 
                                       id="identification_number" name="identification_number" value="{{ old('identification_number') }}" 
                                       placeholder="National Registration #, License #, etc" required>
                                @error('identification_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Employment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="occupation" class="form-label">Occupation <span class="text-danger">*</span></label>
                                <select class="form-select @error('occupation') is-invalid @enderror" 
                                        id="occupation" name="occupation" required>
                                    <option value="employed" {{ old('occupation') == 'employed' ? 'selected' : 'selected' }}>Employed</option>
                                    <option value="self_employed" {{ old('occupation') == 'self_employed' ? 'selected' : '' }}>Self Employed</option>
                                    <option value="business_owner" {{ old('occupation') == 'business_owner' ? 'selected' : '' }}>Business Owner</option>
                                    <option value="farmer" {{ old('occupation') == 'farmer' ? 'selected' : '' }}>Farmer</option>
                                    <option value="unemployed" {{ old('occupation') == 'unemployed' ? 'selected' : '' }}>Unemployed</option>
                                    <option value="retired" {{ old('occupation') == 'retired' ? 'selected' : '' }}>Retired</option>
                                </select>
                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employer" class="form-label">Employer</label>
                                <input type="text" class="form-control @error('employer') is-invalid @enderror" 
                                       id="employer" name="employer" value="{{ old('employer') }}" 
                                       placeholder="Employer name">
                                @error('employer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_number" class="form-label">Employee Number</label>
                                <input type="text" class="form-control @error('employee_number') is-invalid @enderror" 
                                       id="employee_number" name="employee_number" value="{{ old('employee_number') }}" 
                                       placeholder="Employee number">
                                @error('employee_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax_number" class="form-label">Tax Identification Number</label>
                                <input type="text" class="form-control @error('tax_number') is-invalid @enderror" 
                                       id="tax_number" name="tax_number" value="{{ old('tax_number') }}" 
                                       placeholder="Tax Identification Number / Reference">
                                @error('tax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="monthly_income" class="form-label">Monthly Income <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('monthly_income') is-invalid @enderror" 
                               id="monthly_income" name="monthly_income" value="{{ old('monthly_income') }}" 
                               step="0.01" min="0" required>
                        @error('monthly_income')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Primary Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 120px;" name="primary_phone_country">
                                        <option value="US" selected>LBR +231</option>
                                        <option value="GB">🇬🇧 +44</option>
                                        <option value="NG">🇳🇬 +234</option>
                                        <option value="KE">🇰🇪 +254</option>
                                        <option value="GH">🇬🇭 +233</option>
                                    </select>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="secondary_phone" class="form-label">Secondary Phone Number</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 120px;" name="secondary_phone_country">
                                        <option value="US" selected>LBR +231</option>
                                        <option value="GB">🇬🇧 +44</option>
                                        <option value="NG">🇳🇬 +234</option>
                                        <option value="KE">🇰🇪 +254</option>
                                        <option value="GH">🇬🇭 +233</option>
                                    </select>
                                    <input type="tel" class="form-control @error('secondary_phone') is-invalid @enderror" 
                                           id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone') }}">
                                </div>
                                @error('secondary_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="email address" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" placeholder="address" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city') }}" placeholder="city" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/Province <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" value="{{ old('state') }}" placeholder="State/Province" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="zip_code" class="form-label">Zipcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                       id="zip_code" name="zip_code" value="{{ old('zip_code') }}" placeholder="zipcode" required>
                                @error('zip_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                id="branch_id" name="branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Borrower Files -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Borrower Files</h6>
                </div>
                <div class="card-body">
                    <div class="upload-area" id="fileUploadArea">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-2"><strong>Upload files</strong> or drag and drop</p>
                        <p class="text-muted small">PNG, JPG, GIF up to 10MB</p>
                        <input type="file" class="form-control d-none" id="borrowerFiles" 
                               name="borrower_files[]" multiple accept=".png,.jpg,.jpeg,.gif,.pdf">
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="document.getElementById('borrowerFiles').click()">
                            <i class="fas fa-upload me-1"></i>Choose Files
                        </button>
                    </div>
                    <div id="fileList" class="mt-3"></div>
                </div>
            </div>

            <!-- Next of Kin Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Next of Kin Information</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addNextOfKin">
                        <i class="fas fa-plus me-1"></i>Add Next of Kin
                    </button>
                </div>
                <div class="card-body">
                    <div id="nextOfKinContainer">
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-users fa-2x mb-2"></i><br>
                            No next of kin added yet...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i>Save Borrower
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.avatar-upload {
    text-align: center;
}

.avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 3px dashed #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    background: #f8f9fa;
    overflow: hidden;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.upload-area.drag-over {
    border-color: #007bff;
    background: #e7f1ff;
}

.next-of-kin-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background: #f8f9fa;
}
</style>

@section('scripts')
<script>
// Avatar Preview
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').innerHTML = 
                `<img src="${e.target.result}" alt="Avatar">`;
        }
        reader.readAsDataURL(file);
    }
});

// File Upload
document.getElementById('borrowerFiles')?.addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'alert alert-info d-flex justify-content-between align-items-center';
        fileItem.innerHTML = `
            <span><i class="fas fa-file me-2"></i>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
});

// Next of Kin Management
let nextOfKinCount = 0;
document.getElementById('addNextOfKin')?.addEventListener('click', function() {
    nextOfKinCount++;
    const container = document.getElementById('nextOfKinContainer');
    
    // Remove "no next of kin" message
    if (nextOfKinCount === 1) {
        container.innerHTML = '';
    }
    
    const kinItem = document.createElement('div');
    kinItem.className = 'next-of-kin-item';
    kinItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Next of Kin #${nextOfKinCount}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.next-of-kin-item').remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="kin_first_name[]" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="kin_last_name[]" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Relationship</label>
                    <select class="form-select" name="kin_relationship[]" required>
                        <option value="">Select Relationship</option>
                        <option value="spouse">Spouse</option>
                        <option value="parent">Parent</option>
                        <option value="sibling">Sibling</option>
                        <option value="child">Child</option>
                        <option value="friend">Friend</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="kin_phone[]" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="kin_email[]">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="kin_address[]">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(kinItem);
});

// Drag and Drop for Files
const uploadArea = document.getElementById('fileUploadArea');
if (uploadArea) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('drag-over');
        });
    });

    uploadArea.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        document.getElementById('borrowerFiles').files = files;
        document.getElementById('borrowerFiles').dispatchEvent(new Event('change'));
    });
}
</script>
@endsection
@endsection
