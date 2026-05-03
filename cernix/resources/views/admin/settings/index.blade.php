@extends('layouts.admin')

@section('title', 'Settings')
@section('page_title', 'Settings')
@section('breadcrumb', 'Admin / Settings')

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>General Settings</h2>
            <p class="section-copy">Configure display, session defaults, QR expiry, and security preferences.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="app_name">App Name</label>
                <input id="app_name" name="app_name" value="{{ $settings['app_name'] }}" required>
            </div>
            <div class="field">
                <label for="app_timezone">App Timezone</label>
                <select id="app_timezone" name="app_timezone">
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected($settings['app_timezone']===$timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="default_session_duration">Default Duration</label>
                <input id="default_session_duration" type="number" name="default_session_duration" value="{{ $settings['default_session_duration'] }}" min="15">
                <span class="hint">Minutes per exam session.</span>
            </div>
            <div class="field">
                <label for="qr_token_expiry">QR Expiry</label>
                <input id="qr_token_expiry" type="number" name="qr_token_expiry" value="{{ $settings['qr_token_expiry'] }}" min="5">
                <span class="hint">Minutes before an issued token expires.</span>
            </div>
            <div class="field">
                <label for="session_lifetime">Session Lifetime</label>
                <input id="session_lifetime" type="number" name="session_lifetime" value="{{ $settings['session_lifetime'] }}" min="10">
            </div>
            <div class="field">
                <label>Allow Re-registration</label>
                <label class="check-control"><input type="checkbox" name="allow_re_registration" value="1" @checked($settings['allow_re_registration']==='1')> Yes</label>
            </div>
            <div class="field">
                <label>Require HTTPS</label>
                <label class="check-control"><input type="checkbox" name="require_https" value="1" @checked($settings['require_https']==='1')> Enforce in production</label>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Save Settings</button></div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Danger Zone</h2>
            <p class="section-copy">Destructive maintenance actions. Use only when resetting test or operational data is intentional.</p>
        </div>
    </div>
    <div class="card-body stack">
        <form method="POST" action="{{ route('admin.settings.clear-scan-logs') }}" data-confirm-inline class="confirm-inline">
            @csrf
            <button type="button" class="btn danger ask-confirm">Clear all scan logs</button>
            <span class="confirm-question">Are you sure?</span>
            <button class="btn danger confirm-btn" type="submit">Confirm</button>
            <button type="button" class="btn cancel-btn">Cancel</button>
        </form>

        <form method="POST" action="{{ route('admin.settings.reset-system') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="reset_confirmation">Type RESET to confirm</label>
                <input id="reset_confirmation" name="reset_confirmation" placeholder="RESET">
            </div>
            <div class="form-action"><button class="btn danger" type="submit">Reset system</button></div>
        </form>
    </div>
</section>
@endsection
