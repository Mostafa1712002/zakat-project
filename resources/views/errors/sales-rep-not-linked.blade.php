<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حساب غير مفعل - Rogence System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 16px;
        }
        p {
            color: #64748b;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 24px;
        }
        .info-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-box p {
            color: #92400e;
            margin-bottom: 0;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: #0891b2;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
        }
        .btn:hover {
            background: #0e7490;
        }
        .user-info {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>حسابك غير مفعل بعد</h1>
        <p>
            تم إنشاء حسابك كمندوب مبيعات، لكن لم يتم ربطه بسجل مندوب في النظام بعد.
        </p>

        <div class="info-box">
            <p>
                ⚠️ يرجى التواصل مع إدارة النظام لربط حسابك بسجل مندوب حتى تتمكن من استخدام النظام.
            </p>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn">🔄 إعادة تسجيل الدخول</button>
        </form>

        <div class="user-info">
            <strong>{{ auth()->user()->name }}</strong><br>
            {{ auth()->user()->email }}
        </div>
    </div>
</body>
</html>
