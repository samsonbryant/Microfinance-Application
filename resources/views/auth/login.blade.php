<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Microfinance MMS') }} - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
            margin: 20px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating input {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .demo-accounts {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .demo-accounts h6 {
            color: #495057;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .demo-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .demo-account:last-child {
            border-bottom: none;
        }
        
        .demo-account .role {
            font-weight: 500;
            color: #495057;
        }
        
        .demo-account .credentials {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .copy-btn {
            background: #667eea;
            border: none;
            border-radius: 5px;
            color: white;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: #764ba2;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        @media (max-width: 576px) {
            .login-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-university fa-3x mb-3"></i>
            <h1>Microfinance MMS</h1>
            <p>Management System</p>
        </div>
        
        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

                <div class="form-floating">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" placeholder="Email" 
                           value="{{ old('email') }}" required autofocus>
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

                <div class="form-floating">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

                <div class="remember-forgot">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
            </label>
        </div>
            @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Forgot password?
                </a>
            @endif
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <!-- Demo Accounts -->
            <div class="demo-accounts">
                <h6><i class="fas fa-info-circle me-2"></i>Demo Accounts</h6>
                
                <div class="demo-account">
                    <div>
                        <div class="role">Admin</div>
                        <div class="credentials">admin@microfinance.com / admin123</div>
                    </div>
                    <button class="copy-btn" onclick="copyCredentials('admin@microfinance.com', 'admin123')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="demo-account">
                    <div>
                        <div class="role">Branch Manager</div>
                        <div class="credentials">bm@microfinance.com / bm123</div>
                    </div>
                    <button class="copy-btn" onclick="copyCredentials('bm@microfinance.com', 'bm123')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="demo-account">
                    <div>
                        <div class="role">Loan Officer</div>
                        <div class="credentials">lo@microfinance.com / lo123</div>
                    </div>
                    <button class="copy-btn" onclick="copyCredentials('lo@microfinance.com', 'lo123')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="demo-account">
                    <div>
                        <div class="role">Borrower</div>
                        <div class="credentials">borrower@microfinance.com / borrower123</div>
                    </div>
                    <button class="copy-btn" onclick="copyCredentials('borrower@microfinance.com', 'borrower123')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyCredentials(email, password) {
            // Copy email to email field
            document.getElementById('email').value = email;
            document.getElementById('email').focus();
            
            // Copy password to password field
            document.getElementById('password').value = password;
            
            // Show success message
            const btn = event.target.closest('.copy-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.style.background = '#28a745';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '#667eea';
            }, 2000);
        }
        
        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>