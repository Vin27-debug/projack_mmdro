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
            --navy: #0a2f5c;
            --navy-deep: #071f3d;
            --gold: #ffb81c;
            --red: #c8102e;
            --ink: #1c2229;
            --ink-soft: #545b63;
            --paper: #f4f5f6;
            --line: #d7dbe0;
            --focus: #0a2f5c;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--paper);
            display: flex;
            flex-direction: column;
        }

        .gov-strip {
            background: var(--navy-deep);
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
            background: var(--navy);
            border-bottom: 3px solid var(--gold);
            padding: 0.9rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .gov-seal {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--gold);
            flex: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.85rem;
            color: var(--navy);
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
            max-width: 400px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 4px;
        }

        .panel-head {
            padding: 1.5rem 1.75rem 1.1rem;
            border-bottom: 1px solid var(--line);
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

        .panel-body {
            padding: 1.5rem 1.75rem 1.75rem;
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
            border: 1px solid #c2c7cc;
            border-radius: 3px;
            background: #fff;
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
            color: var(--navy);
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
            background: var(--navy);
            border: 1px solid var(--navy-deep);
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: var(--navy-deep);
        }

        .btn-submit:focus-visible {
            outline: 2px solid var(--gold);
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
        <div class="gov-seal" aria-hidden="true">DRRM</div>
        <div class="titles">
            <div class="agency">Municipal Disaster Risk Reduction and Management Office</div>
            <div class="office">MuniResQ — Emergency Response System</div>
        </div>
    </div>

    <main>
        <div class="login-panel">
            <div class="panel-head">
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