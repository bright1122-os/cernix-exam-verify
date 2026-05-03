@extends('layouts.portal')

@section('title', 'Student Login')

@section('content')
<style>
    .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); }
    .login-card { width: 100%; max-width: 440px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); padding: 28px; }
    .login-card h1 { margin: 0 0 8px; font-size: 24px; font-weight: 800; color: var(--ink); }
    .login-card p { margin: 0 0 22px; color: var(--ink-3); font-size: 14px; line-height: 1.5; }
    .register-link { display: inline-block; margin-top: 16px; color: var(--navy); font-size: 13px; font-weight: 700; text-decoration: none; }
</style>
<main class="login-shell">
    <section class="login-card">
        <h1>Student Login</h1>
        <p>Use your matric number as your student ID. For newly generated passes, your Remita RRR is your initial password.</p>

        @if ($errors->any())
            <div class="error-box" style="margin-bottom:16px">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('student.login') }}">
            @csrf
            <div class="field mono">
                <label for="student_id">Student ID</label>
                <input id="student_id" name="student_id" type="text" class="input" value="{{ old('student_id') }}" placeholder="CSC/2021/001" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign in to Student</button>
        </form>

        <a href="{{ route('student.register') }}" class="register-link">Generate exam QR pass</a>
    </section>
</main>
@endsection
