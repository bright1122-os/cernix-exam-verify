@extends('layouts.portal')

@section('title', 'Examiner Login')

@section('content')
<style>
    .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); }
    .login-card { width: 100%; max-width: 440px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); padding: 28px; }
    .login-card h1 { margin: 0 0 8px; font-size: 24px; font-weight: 800; color: var(--ink); }
    .login-card p { margin: 0 0 22px; color: var(--ink-3); font-size: 14px; line-height: 1.5; }
</style>
<main class="login-shell">
    <section class="login-card">
        <h1>Examiner Login</h1>
        <p>Sign in with an examiner account to scan and verify student QR passes.</p>

        @if ($errors->any())
            <div class="error-box" style="margin-bottom:16px">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('examiner.login') }}">
            @csrf
            <div class="field mono">
                <label for="username">Examiner username</label>
                <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign in to Examiner</button>
        </form>
    </section>
</main>
@endsection
