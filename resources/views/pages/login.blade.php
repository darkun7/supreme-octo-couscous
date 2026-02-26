<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Game Manager</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-card: #151c2e;
            --bg-input: #0d1220;
            --bg-input-focus: #111827;
            --border-default: #1e293b;
            --border-focus: #6366f1;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-tertiary: #64748b;
            --accent-primary: #6366f1;
            --accent-primary-hover: #818cf8;
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
            --gradient-accent: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa);
            --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.15);
            --font-sans: 'Inter', sans-serif;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(99, 102, 241, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .login-box {
            position: relative;
            z-index: 1;
            background: var(--bg-card);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            justify-content: center;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-accent);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: var(--shadow-glow);
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .logo-sub {
            font-size: 12px;
            color: var(--text-tertiary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 16px; background: var(--bg-input); border: 1px solid var(--border-default);
            border-radius: 6px; font-size: 14px; color: white; transition: all 150ms; outline: none;
        }
        .form-input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
        .btn-primary {
            width: 100%; padding: 12px; background: var(--gradient-accent); color: white;
            border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3); transition: all 150ms;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4); }
        .error-msg { background: var(--danger-bg); color: var(--danger); font-size: 13px; padding: 10px; border-radius: 6px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-box">
            <div class="logo-icon">🎮</div>
            <div>
                <div class="logo-text">Game Manager</div>
                <div class="logo-sub">Data Editor</div>
            </div>
        </div>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" value="{{ old('username') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            
            <button type="submit" class="btn-primary">Sign In</button>
        </form>
    </div>
</body>
</html>
