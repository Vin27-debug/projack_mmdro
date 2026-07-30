@php
$routeName = Route::currentRouteName();

$isActive = fn(string $name) =>
str_contains($routeName, $name)
? 'active text-white'
: 'text-white-50';
@endphp

<nav class="nav flex-column p-3 gap-2">

    <a
        class="nav-link rounded {{ $isActive('driver.dashboard') }}"
        href="{{ route('driver.dashboard') }}">
        📊 Dashboard
    </a>

    <a
        class="nav-link rounded {{ $isActive('driver.assignment') }}"
        href="{{ route('driver.assignment') }}">
        🚑 My Assignment
    </a>

    <span class="nav-link text-secondary">
        🗺 Navigation
    </span>

    <span class="nav-link text-secondary">
        📝 Reports
    </span>

    <a
        class="nav-link rounded {{ $isActive('profile') }}"
        href="{{ route('profile.edit') }}">
        👤 Profile
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button
            type="submit"
            class="btn btn-danger w-100 mt-3">
            Logout
        </button>
    </form>

</nav>