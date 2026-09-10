@php
$authReturn = $authWebinar ? route('webinars.show', $authWebinar, false) : route('webinars.index', [], false);
$webinarHasPassword = $authWebinar && $authWebinar->registrationForm && $authWebinar->registrationForm->fields->where('is_enabled', true)->where('field_type', 'password')->isNotEmpty();
$showLoginPassword = $authWebinar ? $webinarHasPassword : (($authSettings['registration_password_enabled'] ?? '1') === '1');
@endphp
<span hidden data-auth-open="{{ old('_auth_modal', request('auth')) }}"></span>
<div class="modal fade microsite-auth-modal frontend-auth-modal" id="micrositeLoginModal" tabindex="-1" aria-labelledby="micrositeLoginTitle">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><span class="auth-modal-kicker">WELCOME BACK</span><h2 class="modal-title" id="micrositeLoginTitle">Login to continue</h2><p>{{ $authWebinar?->title ?? 'Your next learning experience awaits.' }}</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close login"></button></div>
        <div class="modal-body">
            @if(old('_auth_modal')==='login' && $errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}" class="row g-3">
                @csrf
                <input type="hidden" name="_auth_modal" value="login">
                <input type="hidden" name="return_to" value="{{ $authReturn }}">
                @if($authWebinar)<input type="hidden" name="webinar_id" value="{{ $authWebinar->id }}">@endif
                <div class="col-12"><label class="form-label" for="frontendLogin">{{ $loginField ? $loginField->label : 'Email address' }}</label><input id="frontendLogin" class="form-control" name="login" type="{{ ($loginField && $loginField->field_type === 'email') ? 'email' : 'text' }}" value="{{ old('login') }}" placeholder="{{ $loginField ? ($loginField->placeholder ?: 'Enter your '.$loginField->label) : 'you@example.com' }}" autocomplete="username" required></div>
                @if($showLoginPassword)<div class="col-12"><label class="form-label" for="frontendPassword">Password</label><div class="input-group"><input id="frontendPassword" class="form-control" name="password" type="password" autocomplete="current-password" required><button class="btn btn-outline-secondary" type="button" onclick="const p=document.getElementById('frontendPassword');const icon=this.querySelector('i');if(p.type==='password'){p.type='text';icon.classList.remove('bi-eye');icon.classList.add('bi-eye-slash');}else{p.type='password';icon.classList.remove('bi-eye-slash');icon.classList.add('bi-eye');}}"><i class="bi bi-eye"></i></button></div></div>
                <div class="col-12 text-end"><button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#frontendForgotModal">Forgot password?</button></div>@endif
                <div class="col-12"><button class="btn btn-gradient w-100">Login <i class="bi bi-arrow-right"></i></button></div>
            </form>
        </div>
    </div></div>
</div>
<div class="modal fade microsite-auth-modal frontend-auth-modal" id="frontendForgotModal" tabindex="-1" aria-labelledby="frontendForgotTitle">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><span class="auth-modal-kicker">ACCOUNT HELP</span><h2 class="modal-title" id="frontendForgotTitle">Forgot password?</h2><p>Enter the email address associated with your account.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close password help"></button></div>
        <div class="modal-body">
            @if(old('_auth_modal')==='forgot' && $errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('password.request') }}" class="row g-3">@csrf
                <input type="hidden" name="_auth_modal" value="forgot"><input type="hidden" name="return_to" value="{{ $authReturn }}">
                <div class="col-12"><label for="frontendRecoveryEmail" class="form-label">Email address</label><input id="frontendRecoveryEmail" class="form-control" name="email" type="email" autocomplete="email" required></div>
                <div class="col-12"><button class="btn btn-gradient w-100">Send reset instructions</button></div>
                <div class="col-12 auth-modal-switch"><button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Back to login</button></div>
            </form>
        </div>
    </div></div>
</div>
<div class="modal fade microsite-auth-modal frontend-auth-modal" id="micrositeRegisterModal" tabindex="-1" aria-labelledby="micrositeRegisterTitle">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<div>
<h2 class="modal-title" id="micrositeRegisterTitle">{{ $authWebinar ? 'Register' : 'Create your account' }}</h2>
<p class="text-muted mb-0">Create your attendee account to continue.</p>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close registration">
</button>
</div>
<div class="modal-body">
@if(auth()->check() && $authWebinar)
    @if(old('_auth_modal')==='register' && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('webinars.register', $authWebinar) }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <div class="p-3 rounded-3 bg-light border d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-weight:700;flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <strong class="text-truncate d-block">{{ auth()->user()->name }}</strong>
                        <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                    </div>
                </div>
            </div>
            @foreach($registrationFields as $field)
                @php
                    $lowerLabel = strtolower(trim($field->label));
                    $isName = in_array($lowerLabel, ['name', 'full name', 'your name']) || str_starts_with($field->field_key, 'name') || str_starts_with($field->field_key, 'full_name');
                    $isEmail = in_array($lowerLabel, ['email', 'email address']) || str_starts_with($field->field_key, 'email');
                    $isMobile = in_array($lowerLabel, ['mobile', 'mobile number', 'phone', 'phone number']) || str_starts_with($field->field_key, 'mobile');
                @endphp
                @if($isName)
                    <input type="hidden" name="fields[{{ $field->id }}]" value="{{ auth()->user()->name }}">
                @elseif($isEmail)
                    <input type="hidden" name="fields[{{ $field->id }}]" value="{{ auth()->user()->email }}">
                @elseif($isMobile && auth()->user()->mobile)
                    <input type="hidden" name="fields[{{ $field->id }}]" value="{{ auth()->user()->mobile }}">
                @else
                    @include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'fields'])
                @endif
            @endforeach
            <div class="col-12">
                <button class="btn btn-gradient w-100 btn-lg">Complete Registration</button>
            </div>
        </div>
    </form>
@else
    @if(old('_auth_modal')==='register' && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('register') }}">
    <div class="row g-3">@csrf<input type="hidden" name="_auth_modal" value="register">@if($authWebinar)<input type="hidden" name="webinar_id" value="{{ $authWebinar->id }}">@endif<input type="hidden" name="return_to" value="{{ $authReturn }}">
    @if($authWebinar && $registrationFields->isNotEmpty())
        @foreach($registrationFields as $field)
            @include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'fields'])
        @endforeach
    @else
        <div class="col-12">
        <label class="form-label">Full name</label>
        <input class="form-control" name="name" value="{{ old('name') }}" required>
        </div>@if(($authSettings['registration_email_enabled']??'1')==='1')<div class="col-md-6">
        <label class="form-label">Email address</label>
        <input class="form-control" name="email" type="email" value="{{ old('email') }}" @required(($authSettings['registration_email_required']??'1')==='1')>
        </div>@endif @if(($authSettings['registration_mobile_enabled']??'1')==='1')<div class="col-md-6">
        <label class="form-label">Mobile number</label>
        <input class="form-control" name="mobile" value="{{ old('mobile') }}" @required(($authSettings['registration_mobile_required']??'0')==='1')>
        </div>@endif @if(($authSettings['registration_country_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">Country</label>
        <select class="form-select" name="country_id" id="micrositeCountry" required>
        <option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->id }}" @selected((int)old('country_id',(int)($authSettings['registration_default_country_id']??0))===$country->id)>{{ $country->name }}</option>@endforeach</select>
        </div>@endif @if(($authSettings['registration_state_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">State</label>
        <select class="form-select" name="state_id" id="micrositeState" required>
        <option value="">Select state</option>@foreach($states as $state)<option value="{{ $state->id }}" @selected((int)old('state_id',(int)($authSettings['registration_default_state_id']??0))===$state->id)>{{ $state->name }}</option>@endforeach</select>
        </div>@endif @if(($authSettings['registration_city_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">City</label>
        <select class="form-select" name="city_id" id="micrositeCity" required>
        <option value="">Select city</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected((int)old('city_id')===$city->id)>{{ $city->name }}</option>@endforeach</select>
        </div>@endif @if(($authSettings['registration_password_enabled']??'1')==='1')<div class="col-md-6">
        <label class="form-label">Password</label>
        <div class="input-group"><input class="form-control" id="regPassword" name="password" type="password" minlength="6" @required(($authSettings['registration_password_required']??'1')==='1')><button class="btn btn-outline-secondary" type="button" onclick="const p=document.getElementById('regPassword');const icon=this.querySelector('i');if(p.type==='password'){p.type='text';icon.classList.remove('bi-eye');icon.classList.add('bi-eye-slash');}else{p.type='password';icon.classList.remove('bi-eye-slash');icon.classList.add('bi-eye');}}"><i class="bi bi-eye"></i></button></div>
        </div>
        <div class="col-md-6">
        <label class="form-label">Confirm password</label>
        <div class="input-group"><input class="form-control" id="regPasswordConfirm" name="password_confirmation" type="password" @required(($authSettings['registration_password_required']??'1')==='1')><button class="btn btn-outline-secondary" type="button" onclick="const p=document.getElementById('regPasswordConfirm');const icon=this.querySelector('i');if(p.type==='password'){p.type='text';icon.classList.remove('bi-eye');icon.classList.add('bi-eye-slash');}else{p.type='password';icon.classList.remove('bi-eye-slash');icon.classList.add('bi-eye');}}"><i class="bi bi-eye"></i></button></div>
        </div>@endif
        @foreach($signupFields as $field)@include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'custom'])@endforeach
    @endif
    <div class="col-12">
    <label class="form-check">
    <input class="form-check-input" type="checkbox" required> <span class="form-check-label">I agree to the Terms and Privacy Policy.</span>
    </label>
    </div>
    <div class="col-12">
    <button class="btn btn-gradient w-100 btn-lg">Complete Registration</button>
    </div>
    <div class="col-12 auth-modal-switch">Already registered? <button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Login here</button></div>
    </div>
    </form>
@endif
</div>
</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const updateTokens = (token) => {
        if (!token) return;
        document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = token; });
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', token);
    };
    const refreshCsrf = async () => {
        try {
            const res = await fetch('{{ route('csrf.token') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data && data.token) updateTokens(data.token);
            }
        } catch(e) {}
    };
    document.querySelectorAll('.frontend-auth-modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', refreshCsrf);
    });
    window.addEventListener('focus', refreshCsrf);
});
</script>
