@extends('layouts.app')

@section('title', 'Client Risk Profiles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Client Risk Profiles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Risk Assessment Management</strong> - This module manages client risk profiles, credit scoring, and risk-based lending decisions.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                    <h5>Low Risk</h5>
                                    <h3>0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <h5>Medium Risk</h5>
                                    <h3>0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                    <h5>High Risk</h5>
                                    <h3>0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-ban fa-2x mb-2"></i>
                                    <h5>Very High Risk</h5>
                                    <h3>0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
