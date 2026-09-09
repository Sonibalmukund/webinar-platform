@php($authReturn = $authWebinar ? route('webinars.show', $authWebinar, false) : route('webinars.index', [], false))
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
                <div class="col-12"><label class="form-label" for="frontendLogin">Email address</label><input id="frontendLogin" class="form-control" name="login" type="email" value="{{ old('login') }}" placeholder="you@example.com" autocomplete="email" required></div>
                @if(($authSettings['registration_password_enabled']??'1')==='1')<div class="col-12"><label class="form-label" for="frontendPassword">Password</label><input id="frontendPassword" class="form-control" name="password" type="password" autocomplete="current-password" required></div>
                <div class="col-12 text-end"><button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#frontendForgotModal">Forgot password?</button></div>@endif
                <div class="col-12"><button class="btn btn-gradient w-100">Login <i class="bi bi-arrow-right"></i></button></div>
                <div class="col-12 auth-modal-switch">New attendee? <button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#micrositeRegisterModal">Register here</button></div>
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
<h2 class="modal-title" id="micrositeRegisterTitle">{{ $authWebinar ? 'Register for this webinar' : 'Create your account' }}</h2>
<p class="text-muted mb-0">Create your attendee account to continue.</p>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close registration">
</button>
</div>
<div class="modal-body">
@if(old('_auth_modal')==='register' && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif<form method="POST" action="{{ route('register') }}">
<div class="row g-3">@csrf<input type="hidden" name="_auth_modal" value="register">@if($authWebinar)<input type="hidden" name="webinar_id" value="{{ $authWebinar->id }}">@endif<input type="hidden" name="return_to" value="{{ $authReturn }}">
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
<input class="form-control" name="password" type="password" minlength="6" @required(($authSettings['registration_password_required']??'1')==='1')>
</div>
<div class="col-md-6">
<label class="form-label">Confirm password</label>
<input class="form-control" name="password_confirmation" type="password" @required(($authSettings['registration_password_required']??'1')==='1')>
</div>@endif
@foreach($signupFields as $field)@include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'custom'])@endforeach @foreach($registrationFields as $field)@unless(in_array($field->field_key,['full_name','email','mobile','city'],true))@include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'fields'])@endunless @endforeach<div class="col-12">
<label class="form-check">
<input class="form-check-input" type="checkbox" required> <span class="form-check-label">I agree to the Terms and Privacy Policy.</span>
</label>
</div>
<div class="col-12">
<button class="btn btn-gradient w-100">Continue Registration</button>
</div>
<div class="col-12 auth-modal-switch">Already registered? <button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Login here</button></div>
</div>
</form>
</div>
</div>
</div>
</div>
