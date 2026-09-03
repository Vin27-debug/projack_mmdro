@extends('layouts.superadmin')

@section('content')

<div class="admin-create-page">

    {{-- HEADER --}}
    <div class="page-header">

        <div>
            <div class="page-eyebrow">SUPER ADMIN</div>

            <h1>Create Administrator</h1>

            <p>
                Register an authorized government administrator for the MuniResQ system.
            </p>
        </div>

        <a href="{{ route('superadmin.dashboard') }}" class="back-btn">
            ← Back to Dashboard
        </a>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))

    <div class="alert success">
        ✓ {{ session('success') }}
    </div>

    @endif


    {{-- ERRORS --}}
    @if($errors->any())

    <div class="alert error">

        <strong>Please check the following:</strong>

        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

    @endif


    <div class="admin-layout">


        {{-- =========================
             MAIN FORM
        ========================== --}}

        <div class="form-card">

            <div class="form-header">

                <div class="header-icon">
                    👤
                </div>

                <div>

                    <h2>Administrator Information</h2>

                    <p>
                        Provide the official information of the administrator.
                    </p>

                </div>

            </div>


            {{-- IMPORTANT:
                 This is the NEW route
            --}}

            <form
                method="POST"
                action="{{ route('admins.store') }}">

                @csrf


                {{-- =========================
                     PERSONAL INFORMATION
                ========================== --}}

                <div class="form-section">

                    <div class="section-title">

                        <h3>Personal Information</h3>

                        <p>
                            Basic identification information of the administrator.
                        </p>

                    </div>


                    <div class="form-grid">


                        {{-- FULL NAME --}}

                        <div class="form-group">

                            <label for="name">
                                Full Name
                                <span>*</span>
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Juan Dela Cruz"
                                required>

                        </div>


                        {{-- EMPLOYEE ID --}}

                        <div class="form-group">

                            <label for="employee_id">
                                Employee ID
                                <span>*</span>
                            </label>

                            <input
                                type="text"
                                id="employee_id"
                                name="employee_id"
                                value="{{ old('employee_id') }}"
                                placeholder="EMP-2026-001"
                                required>

                        </div>


                    </div>

                </div>



                {{-- =========================
                     GOVERNMENT INFORMATION
                ========================== --}}

                <div class="form-section">

                    <div class="section-title">

                        <h3>Government / Office Information</h3>

                        <p>
                            Identify the administrator's official position and assigned office.
                        </p>

                    </div>


                    <div class="form-grid">


                        {{-- POSITION --}}

                        <div class="form-group">

                            <label for="position">
                                Official Position
                                <span>*</span>
                            </label>

                            <select
                                id="position"
                                name="position"
                                required>

                                <option value="">
                                    Select Position
                                </option>

                                <option value="Municipal Administrator"
                                    {{ old('position') == 'Municipal Administrator' ? 'selected' : '' }}>
                                    Municipal Administrator
                                </option>

                                <option value="Emergency Response Administrator"
                                    {{ old('position') == 'Emergency Response Administrator' ? 'selected' : '' }}>
                                    Emergency Response Administrator
                                </option>

                                <option value="Operations Officer"
                                    {{ old('position') == 'Operations Officer' ? 'selected' : '' }}>
                                    Operations Officer
                                </option>

                                <option value="Emergency Management Officer"
                                    {{ old('position') == 'Emergency Management Officer' ? 'selected' : '' }}>
                                    Emergency Management Officer
                                </option>

                                <option value="Administrative Officer"
                                    {{ old('position') == 'Administrative Officer' ? 'selected' : '' }}>
                                    Administrative Officer
                                </option>

                            </select>

                        </div>



                        {{-- DEPARTMENT --}}

                        <div class="form-group">

                            <label for="department">
                                Department
                                <span>*</span>
                            </label>

                            <select
                                id="department"
                                name="department"
                                required>

                                <option value="">
                                    Select Department
                                </option>

                                <option value="Municipal Disaster Risk Reduction and Management Office"
                                    {{ old('department') == 'Municipal Disaster Risk Reduction and Management Office' ? 'selected' : '' }}>
                                    MDRRMO
                                </option>

                                <option value="Municipal Health Office"
                                    {{ old('department') == 'Municipal Health Office' ? 'selected' : '' }}>
                                    Municipal Health Office
                                </option>

                                <option value="Municipal Police Station"
                                    {{ old('department') == 'Municipal Police Station' ? 'selected' : '' }}>
                                    Municipal Police Station
                                </option>

                                <option value="Municipal Fire Station"
                                    {{ old('department') == 'Municipal Fire Station' ? 'selected' : '' }}>
                                    Municipal Fire Station
                                </option>

                                <option value="Municipal Government Office"
                                    {{ old('department') == 'Municipal Government Office' ? 'selected' : '' }}>
                                    Municipal Government Office
                                </option>

                            </select>

                        </div>



                        {{-- OFFICE --}}

                        <div class="form-group">

                            <label for="office">
                                Office Assignment
                                <span>*</span>
                            </label>

                            <select
                                id="office"
                                name="office"
                                required>

                                <option value="">
                                    Select Office
                                </option>

                                <option value="Municipal Hall"
                                    {{ old('office') == 'Municipal Hall' ? 'selected' : '' }}>
                                    Municipal Hall
                                </option>

                                <option value="MDRRMO Office"
                                    {{ old('office') == 'MDRRMO Office' ? 'selected' : '' }}>
                                    MDRRMO Office
                                </option>

                                <option value="Emergency Operations Center"
                                    {{ old('office') == 'Emergency Operations Center' ? 'selected' : '' }}>
                                    Emergency Operations Center
                                </option>

                                <option value="Municipal Health Office"
                                    {{ old('office') == 'Municipal Health Office' ? 'selected' : '' }}>
                                    Municipal Health Office
                                </option>

                                <option value="Police Station"
                                    {{ old('office') == 'Police Station' ? 'selected' : '' }}>
                                    Police Station
                                </option>

                                <option value="Fire Station"
                                    {{ old('office') == 'Fire Station' ? 'selected' : '' }}>
                                    Fire Station
                                </option>

                            </select>

                        </div>



                        {{-- CONTACT NUMBER --}}

                        <div class="form-group">

                            <label for="contact_number">
                                Contact Number
                                <span>*</span>
                            </label>

                            <input
                                type="tel"
                                id="contact_number"
                                name="contact_number"
                                value="{{ old('contact_number') }}"
                                placeholder="09XXXXXXXXX"
                                maxlength="11"
                                required>

                        </div>


                    </div>

                </div>



                {{-- =========================
                     ACCOUNT INFORMATION
                ========================== --}}

                <div class="form-section">

                    <div class="section-title">

                        <h3>Account Information</h3>

                        <p>
                            Login credentials for the administrator's MuniResQ account.
                        </p>

                    </div>


                    <div class="form-grid">


                        {{-- EMAIL --}}

                        <div class="form-group full">

                            <label for="email">
                                Official Government Email
                                <span>*</span>
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="administrator@government.gov.ph"
                                required>

                            <small>
                                Use the administrator's official government email address.
                            </small>

                        </div>



                        {{-- PASSWORD --}}

                        <div class="form-group">

                            <label for="password">
                                Password
                                <span>*</span>
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimum 8 characters"
                                required>

                        </div>



                        {{-- CONFIRM PASSWORD --}}

                        <div class="form-group">

                            <label for="password_confirmation">
                                Confirm Password
                                <span>*</span>
                            </label>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Repeat password"
                                required>

                        </div>


                    </div>

                </div>



                {{-- =========================
                     FOOTER
                ========================== --}}

                <div class="form-footer">

                    <div class="security">

                        <span>🔒</span>

                        <div>

                            <strong>Government Account</strong>

                            <p>
                                This account is intended only for authorized personnel.
                            </p>

                        </div>

                    </div>


                    <div class="buttons">

                        <a
                            href="{{ route('superadmin.dashboard') }}"
                            class="cancel-btn">
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="create-btn">
                            ＋ Create Administrator
                        </button>

                    </div>

                </div>


            </form>

        </div>



        {{-- =========================
             RIGHT PANEL
        ========================== --}}

        <aside class="info-card">

            <div class="info-icon">
                🛡
            </div>

            <h2>Administrator Access</h2>

            <p>
                Administrator accounts are intended for authorized
                government personnel responsible for managing
                MuniResQ emergency response operations.
            </p>


            <hr>


            <h4>Required Information</h4>

            <div class="requirement">
                <span>✓</span>
                Employee ID
            </div>

            <div class="requirement">
                <span>✓</span>
                Official Position
            </div>

            <div class="requirement">
                <span>✓</span>
                Department
            </div>

            <div class="requirement">
                <span>✓</span>
                Office Assignment
            </div>

            <div class="requirement">
                <span>✓</span>
                Contact Number
            </div>

            <div class="requirement">
                <span>✓</span>
                Official Email
            </div>


            <div class="warning">

                <strong>Important</strong>

                <p>
                    Only create accounts for verified government
                    personnel authorized to access the MuniResQ system.
                </p>

            </div>

        </aside>

    </div>

</div>



<style>
    /* =========================
   PAGE
========================= */

    .admin-create-page {
        max-width: 1400px;
        margin: auto;
        padding: 30px;
        color: #fff;
    }



    /* =========================
   HEADER
========================= */

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: end;
        margin-bottom: 25px;
    }

    .page-eyebrow {
        color: #5ca9ff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .page-header h1 {
        margin: 6px 0;
        font-size: 32px;
        font-weight: 800;
    }

    .page-header p {
        color: #91a6c4;
        margin: 0;
        font-size: 14px;
    }

    .back-btn {
        color: #cbd8eb;
        text-decoration: none;
        border: 1px solid #294a78;
        padding: 10px 16px;
        border-radius: 8px;
    }

    .back-btn:hover {
        background: #102a4e;
        color: white;
    }



    /* =========================
   LAYOUT
========================= */

    .admin-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 290px;
        gap: 20px;
    }



    /* =========================
   FORM CARD
========================= */

    .form-card {
        background: linear-gradient(145deg, #101d32, #0b1729);
        border: 1px solid #243b5d;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0, 0, 0, .25);
    }



    /* =========================
   FORM HEADER
========================= */

    .form-header {
        background: linear-gradient(135deg,
                #173e78,
                #174b91);

        padding: 22px 28px;

        display: flex;
        align-items: center;

        gap: 15px;
    }

    .header-icon {
        width: 46px;
        height: 46px;

        border-radius: 10px;

        background: rgba(255, 255, 255, .12);

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 21px;
    }

    .form-header h2 {
        margin: 0;
        color: white;
        font-size: 19px;
    }

    .form-header p {
        margin: 4px 0 0;
        color: #b9d2f5;
        font-size: 12px;
    }



    /* =========================
   SECTIONS
========================= */

    .form-section {
        padding: 28px;
        border-bottom: 1px solid #243b5d;
    }

    .section-title {
        margin-bottom: 20px;
    }

    .section-title h3 {
        color: #d9e5f5;
        font-size: 15px;
        font-weight: 800;
        margin: 0;
    }

    .section-title p {
        color: #91a5c1;
        font-size: 12px;
        margin: 5px 0 0;
    }



    /* =========================
   GRID
========================= */

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }



    /* =========================
   FORM GROUP
========================= */

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        color: #cbd8eb;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .form-group label span {
        color: #e74c3c;
    }



    /* =========================
   INPUT + SELECT
========================= */

    .form-group input,
    .form-group select {
        width: 100%;
        height: 45px;

        box-sizing: border-box;

        border: 1px solid #28538a;
        border-radius: 8px;

        padding: 0 13px;

        background: #122d51;

        color: #cbd8eb;

        font-size: 13px;

        outline: none;

        transition: .2s;
    }

    .form-group input::placeholder {
        color: #6b7c93;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #5ca9ff;

        box-shadow:
            0 0 0 3px rgba(92, 169, 255, .20);
        background: #1a3a65;
    }

    .form-group select {
        cursor: pointer;
    }

    .form-group small {
        color: #91a5c1;
        font-size: 11px;
        margin-top: 6px;
    }



    /* =========================
   FOOTER
========================= */

    .form-footer {
        background: rgba(0, 0, 0, .15);

        padding: 20px 28px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;
    }

    .security {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .security>span {
        font-size: 18px;
    }

    .security strong {
        color: #d9e5f5;
        font-size: 12px;
    }

    .security p {
        margin: 2px 0 0;
        color: #91a5c1;
        font-size: 11px;
    }

    .buttons {
        display: flex;
        gap: 10px;
    }

    .cancel-btn,
    .create-btn {
        height: 40px;

        padding: 0 18px;

        border-radius: 8px;

        display: inline-flex;

        align-items: center;
        justify-content: center;

        text-decoration: none;

        font-size: 12px;
        font-weight: 700;

        cursor: pointer;
    }

    .cancel-btn {
        color: #cbd8eb;
        background: transparent;
        border: 1px solid #28538a;
    }

    .cancel-btn:hover {
        background: rgba(92, 169, 255, .10);
        border-color: #5ca9ff;
    }

    .create-btn {
        color: white;
        background: #2168c4;
        border: none;
    }

    .create-btn:hover {
        background: #1858a9;
    }



    /* =========================
   RIGHT INFO CARD
========================= */

    .info-card {
        background: linear-gradient(145deg,
                #101d32,
                #0b1729);

        border: 1px solid #243b5d;

        border-radius: 14px;

        padding: 22px;

        color: white;

        height: fit-content;
    }

    .info-icon {
        width: 42px;
        height: 42px;

        border-radius: 9px;

        background: #122d51;

        border: 1px solid #28538a;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-bottom: 18px;
    }

    .info-card h2 {
        font-size: 17px;
        margin: 0 0 8px;
    }

    .info-card>p {
        color: #91a5c1;
        font-size: 12px;
        line-height: 1.6;
    }

    .info-card hr {
        border: 0;
        border-top: 1px solid #263b58;
        margin: 20px 0;
    }

    .info-card h4 {
        font-size: 11px;
        text-transform: uppercase;
        color: #d9e5f5;
        margin-bottom: 13px;
    }

    .requirement {
        display: flex;
        align-items: center;
        gap: 9px;

        color: #9eb1cc;

        font-size: 12px;

        margin-bottom: 11px;
    }

    .requirement span {
        width: 16px;
        height: 16px;

        border-radius: 50%;

        background: #27d889;

        color: #062d1c;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 10px;
        font-weight: 900;
    }



    /* =========================
   WARNING
========================= */

    .warning {
        margin-top: 22px;

        padding: 13px;

        border-radius: 8px;

        background: rgba(30, 67, 112, .35);

        border: 1px solid #274b78;
    }

    .warning strong {
        color: #d9e8ff;
        font-size: 11px;
    }

    .warning p {
        color: #8ea6c5;
        font-size: 11px;
        line-height: 1.5;
        margin: 5px 0 0;
    }



    /* =========================
   ALERT
========================= */

    .alert {
        margin-bottom: 18px;
        padding: 13px 16px;
        border-radius: 8px;
    }

    .alert.success {
        background: #0d3025;
        border: 1px solid #1d7655;
        color: #a9f5d4;
    }

    .alert.error {
        background: #351b20;
        border: 1px solid #74333d;
        color: #ffc5cb;
    }

    .alert ul {
        margin: 7px 0 0;
    }



    /* =========================
   RESPONSIVE
========================= */

    @media (max-width: 1000px) {

        .admin-layout {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 700px) {

        .admin-create-page {
            padding: 20px 15px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .buttons {
            width: 100%;
        }

        .cancel-btn,
        .create-btn {
            flex: 1;
        }

    }
</style>

@endsection