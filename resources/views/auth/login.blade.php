<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>تسجيل الدخول - CRM Laravel</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap');

        :root {
            --font-ui: 'IBM Plex Sans Arabic', 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif;
            --font-display: 'Cairo', 'IBM Plex Sans Arabic', 'Tajawal', Tahoma, Arial, sans-serif;
            --ink: #18202f;
            --muted: #667085;
            --line: #dde3eb;
            --page: #f5f7fb;
            --panel: #ffffff;
            --brand: #0f766e;
            --brand-strong: #115e59;
            --accent: #b42318;
            --shadow-soft: 0 10px 28px rgba(16, 24, 40, .06);
        }

        * { box-sizing: border-box; }
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid rgba(15, 118, 110, .28);
            outline-offset: 2px;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--page);
            color: var(--ink);
            font-family: var(--font-ui);
            font-size: 14px;
            font-weight: 400;
        }

        .login-shell {
            width: min(100%, 440px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            font-family: var(--font-display);
            font-size: 21px;
            font-weight: 600;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: var(--brand);
            color: white;
        }

        .panel {
            padding: 22px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: var(--shadow-soft);
        }

        h1 {
            margin: 0 0 6px;
            font-family: var(--font-display);
            font-size: 27px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .subtitle {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 13px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 500;
        }

        input {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 9px 11px;
            font: inherit;
            font-size: 13px;
        }

        .field {
            margin-bottom: 14px;
        }

        .check-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 18px;
        }

        .check-row input {
            width: auto;
            min-height: auto;
        }

        .button {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--brand);
            border-radius: 8px;
            background: var(--brand);
            color: white;
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 500;
            transition: background-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .button:hover {
            background: var(--brand-strong);
            box-shadow: var(--shadow-soft);
            transform: translateY(-1px);
        }
        .error { margin-top: 6px; color: var(--accent); font-size: 13px; }
        .flash {
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            background: #ecfdf5;
            color: #065f46;
            font-weight: 500;
        }
        @media (max-width: 420px) {
            body { padding: 12px; }
            .panel { padding: 18px; }
            h1 { font-size: 24px; }
            .brand { font-size: 18px; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <div class="brand">
            <span class="brand-mark">CRM</span>
            <span>إدارة العملاء</span>
        </div>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        <section class="panel">
            <h1>تسجيل الدخول</h1>
            <p class="subtitle">ادخل إلى لوحة إدارة العملاء والمتابعات.</p>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <input id="password" type="password" name="password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <label class="check-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>تذكرني</span>
                </label>

                <button class="button" type="submit">دخول</button>
            </form>
        </section>
    </main>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
