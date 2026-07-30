<h1>Driver Registration</h1>

@if(session('success'))
<p>{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('driver.register.store') }}">
    @csrf

    <input type="text" name="name" placeholder="Full Name"><br><br>

    <input type="email" name="email" placeholder="Email"><br><br>

    <input type="text" name="contact_number" placeholder="Contact Number"><br><br>

    <input type="text" name="license_number" placeholder="License Number"><br><br>

    <input type="date" name="license_expiry"><br><br>

    <input type="password" name="password" placeholder="Password"><br><br>

    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br><br>

    <button type="submit">
        Register Driver
    </button>
</form>