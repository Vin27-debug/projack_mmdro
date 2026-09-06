<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MuniResQ | MDRRMO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
use Illuminate\Support\Facades\Route;
@endphp

<body>
    <style>
        :root {
            --navy: #06152e;
            --navy-deep: #031022;
            --blue: #014cfd;
            --green: #00994d;
            --red: #e31b23;
            --ink: #eef4ff;
            --ink-soft: #a8b8cb;
            --paper: #08172f;
            --line: rgba(255, 255, 255, .13);
            --focus: #66b7ff;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 15% 0%, rgba(1, 76, 253, .16), transparent 32%), linear-gradient(180deg, var(--navy) 0%, var(--paper) 100%);
            display: flex;
            flex-direction: column;
        }

        .gov-strip {
            background: rgba(3, 16, 34, .86);
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            padding: 0.4rem 1rem;
            text-align: center;
        }

        .gov-strip strong {
            color: #fff;
        }

        .gov-header {
            background: rgba(6, 21, 46, .92);
            border-bottom: 1px solid rgba(102, 183, 255, .22);
            padding: 0.9rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .gov-seal {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(145deg, var(--blue), #003399);
            border: 1px solid rgba(255, 255, 255, .28);
            flex: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: .65rem;
            letter-spacing: .08em;
            color: #fff;
            box-shadow: 0 8px 22px rgba(1, 76, 253, .28);
        }

        .gov-header .titles {
            line-height: 1.25;
        }

        .gov-header .agency {
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .gov-header .office {
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.76rem;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
        }

        .login-panel {
            width: 100%;
            max-width: 430px;
            background: rgba(10, 31, 61, .94);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
        }

        .panel-head {
            padding: 1.7rem 1.85rem 1.25rem;
            border-bottom: 1px solid var(--line);
            text-align: center;
        }

        .panel-head h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 0.3rem;
        }

        .panel-head .sub {
            font-size: 0.82rem;
            color: var(--ink-soft);
            margin: 0;
        }

        .login-brand-name {
            margin-bottom: .35rem;
            color: #fff;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .login-brand-subtitle {
            margin: .2rem 0 1.1rem;
            color: #9db1c7;
            font-size: .72rem;
            letter-spacing: .03em;
        }

        .panel-body {
            padding: 1.5rem 1.85rem 1.8rem;
        }

        .field {
            margin-bottom: 1.1rem;
        }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.32rem;
        }

        .field input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.6rem 0.7rem;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 9px;
            background: rgba(3, 16, 34, .42);
            color: var(--ink);
        }

        .field input::placeholder {
            color: #9aa1a8;
        }

        .field input:focus {
            outline: 2px solid var(--focus);
            outline-offset: 1px;
            border-color: var(--focus);
        }

        .field-error {
            font-size: 0.76rem;
            color: var(--red);
            margin-top: 0.3rem;
        }

        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.35rem;
            font-size: 0.8rem;
        }

        .row-between label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--ink-soft);
        }

        .row-between a {
            color: #8ec5ff;
            text-decoration: none;
        }

        .row-between a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 0.68rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            background: var(--blue);
            border: 1px solid #003399;
            border-radius: 9px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #003399;
        }

        .btn-submit:focus-visible {
            outline: 2px solid #66b7ff;
            outline-offset: 2px;
        }

        .access-note {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
            font-size: 0.74rem;
            color: var(--ink-soft);
        }

        .alert-status {
            background: #eaf3ec;
            border: 1px solid #bfe0c9;
            color: #205b38;
            font-size: 0.84rem;
            padding: 0.6rem 0.75rem;
            border-radius: 3px;
            margin-bottom: 1.2rem;
        }

        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.72rem;
            color: var(--ink-soft);
            border-top: 1px solid var(--line);
        }

        footer .rep {
            font-weight: 600;
            color: var(--ink);
        }
    </style>

    <div class="gov-strip">
        <strong>Republic of the Philippines</strong> — Official system for authorized personnel only
    </div>

    <div class="gov-header">
        <div class="titles">
            <div class="agency">Municipal Disaster Risk Reduction and Management Office</div>
            <div class="office">MuniResQ — Emergency Response System</div>
        </div>
    </div>

    <main>
        <div class="login-panel">
            <div class="panel-head">
                <div class="login-brand-name">MuniResQ</div>
                <div class="login-brand-subtitle">Emergency Response &amp; Ambulance Tracking System</div>
                <h1>Personnel sign in</h1>
                <p class="sub">Access is restricted to MDRRMO admin, superadmin, and driver accounts.</p>
            </div>

            <div class="panel-body">
                @if(session('status'))
                <div class="alert-status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            required
                            autofocus
                            placeholder="you@mdrrmo.gov.ph">
                        @error('email')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••">
                        @error('password')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row-between">
                        <label for="remember">
                            <input type="checkbox" name="remember" id="remember">
                            Remember me
                        </label>

                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">Sign in</button>
                </form>

                <p class="access-note">
                    Unauthorized access to this system is prohibited and may be subject to disciplinary or legal action under RA 10173 (Data Privacy Act).
                </p>
            </div>
        </div>
    </main>

    <footer>
        <div class="rep">MDRRMO · MuniResQ Emergency Operations</div>
        <div>This is an official government system. For access issues, contact your system administrator.</div>
    </footer>
</body>

</html>