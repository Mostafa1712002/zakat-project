<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إنشاء حساب - نظام إدارة العملاء</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0891b2;
            --primary-dark: #0e7490;
            --primary-light: #06b6d4;
            --success: #059669;
            --warning: #f59e0b;
            --danger: #dc2626;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 420px;
        }

        .register-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 32px 24px;
            text-align: center;
            color: white;
        }

        .register-logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
        }

        .register-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .register-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .register-body {
            padding: 32px 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            transition: all 0.2s ease;
        }

        .form-input[type="email"] {
            direction: ltr;
            text-align: right;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--card);
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-error {
            color: var(--danger);
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .register-btn {
            width: 100%;
            padding: 14px 24px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(8, 145, 178, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            padding: 20px 24px;
            background: var(--bg);
            border-top: 1px solid var(--border);
        }

        .register-footer p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .password-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .register-header {
                padding: 24px 20px;
            }

            .register-body {
                padding: 24px 20px;
            }

            .register-title {
                font-size: 20px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">✨</div>
                <h1 class="register-title">إنشاء حساب جديد</h1>
                <p class="register-subtitle">أنشئ حسابك للوصول إلى نظام إدارة العملاء</p>
            </div>

            <div class="register-body">
                @if ($errors->any())
                    <div class="alert alert-error">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            @foreach ($errors->all() as $error)
                                <li>⚠ {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">الاسم الكامل</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            value="{{ old('name') }}"
                            placeholder="أدخل اسمك الكامل"
                            required
                            autofocus
                            autocomplete="name"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            value="{{ old('email') }}"
                            placeholder="example@email.com"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        <p class="password-hint">يجب أن تكون كلمة المرور 8 أحرف على الأقل</p>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="register-btn">
                        <span>🚀</span>
                        <span>إنشاء الحساب</span>
                    </button>
                </form>
            </div>

            <div class="register-footer">
                <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</body>
</html>
