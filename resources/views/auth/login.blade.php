<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated background orbs */
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 8s ease-in-out infinite;
        }
        .bg-orb-1 { width: 400px; height: 400px; background: #3b82f6; top: -100px; left: -100px; animation-delay: 0s; }
        .bg-orb-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -50px; right: -50px; animation-delay: -3s; }
        .bg-orb-3 { width: 250px; height: 250px; background: #06b6d4; top: 50%; left: 60%; animation-delay: -5s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo .icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }
        .brand-logo .icon i { font-size: 1.5rem; color: #fff; }
        .brand-logo h1 { font-size: 1.4rem; font-weight: 700; color: #f1f5f9; letter-spacing: -0.5px; }
        .brand-logo p { font-size: 0.85rem; color: #94a3b8; margin-top: 0.4rem; }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i {
            position: absolute;
            left: 1rem;
            color: #64748b;
            font-size: 1rem;
            transition: color 0.3s;
        }
        .input-wrapper input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        .input-wrapper input::placeholder { color: #475569; }
        .input-wrapper input:focus {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .input-wrapper input:focus + i,
        .input-wrapper:focus-within i { color: #3b82f6; }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .form-options label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            color: #94a3b8;
            cursor: pointer;
        }
        .form-options input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #3b82f6;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 0.95rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover::after { left: 100%; }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.78rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-logo">
                <div class="icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <h1>Aira Link Indonesia</h1>
                <p>Masuk ke dashboard Anda</p>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="form-group">
                    <label>Username / Email</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username atau email" required autofocus>
                        <i class="bi bi-person"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="Masukkan password" required>
                        <i class="bi bi-lock"></i>
                    </div>
                </div>
                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                </div>
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>

            <div class="footer-text">
                &copy; {{ date('Y') }} Aira Link Indonesia. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
