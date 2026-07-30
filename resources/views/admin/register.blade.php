<h1>Admin Registration</h1>

@if(session('success'))
<p>{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('admin.register.store') }}">
    @csrf

    <input type="text" name="name" placeholder="Full Name"><br><br>

    <input type="email" name="email" placeholder="Email"><br><br>

    <input type="password" name="password" placeholder="Password"><br><br>

    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br><br>

    <button type="submit">
        Register Admin
    </button>
</form>