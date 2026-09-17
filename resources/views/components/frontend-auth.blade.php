@php
$authReturn = $authWebinar ? route('webinars.show', $authWebinar, false) : route('webinars.index', [], false);
@endphp
<span hidden data-auth-open="{{ old('_auth_modal', request('auth')) }}"></span>
<div class="modal fade microsite-auth-modal frontend-auth-modal" id="micrositeLoginModal" tabindex="-1" aria-labelledby="micrositeLoginTitle">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><span class="auth-modal-kicker">WELCOME BACK</span><h2 class="modal-title" id="micrositeLoginTitle">Login to continue</h2><p>{{ $authWebinar?->title ?? 'Your next learning experience awaits.' }}</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close login"></button></div>
        <div class="modal-body">
            @if(old('_auth_modal')==='login' && $errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}" class="row g-3 frontend-auth-form" id="micrositeLoginForm" novalidate>
                @csrf
                <input type="hidden" name="_auth_modal" value="login">
                <input type="hidden" name="return_to" value="{{ $authReturn }}">
                @if($authWebinar)<input type="hidden" name="webinar_id" value="{{ $authWebinar->id }}">@endif
                <div class="col-12"><label class="form-label" for="frontendLogin">{{ $loginField ? $loginField->label : 'Email address' }} <span class="text-danger">*</span></label><input id="frontendLogin" class="form-control" name="login" type="{{ ($loginField && $loginField->field_type === 'email') ? 'email' : 'text' }}" value="{{ old('login') }}" placeholder="{{ $loginField ? ($loginField->placeholder ?: 'Enter your '.$loginField->label) : 'you@example.com' }}" autocomplete="username" required @if(!$loginField || $loginField->field_type === 'email') data-rule-email="true" @endif></div>
                <div class="col-12"><button class="btn btn-gradient w-100" type="submit">Login <i class="bi bi-arrow-right"></i></button></div>
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
    <form method="POST" action="{{ route('webinars.register', $authWebinar) }}" class="frontend-auth-form" id="micrositeAuthWebinarRegisterForm" novalidate>
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
                <button class="btn btn-gradient w-100 btn-lg" type="submit">Complete Registration</button>
            </div>
        </div>
    </form>
@else
    @if(old('_auth_modal')==='register' && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('register') }}" class="frontend-auth-form" id="micrositeRegisterForm" novalidate>
    <div class="row g-3">@csrf<input type="hidden" name="_auth_modal" value="register">@if($authWebinar)<input type="hidden" name="webinar_id" value="{{ $authWebinar->id }}">@endif<input type="hidden" name="return_to" value="{{ $authReturn }}">
    @if($authWebinar && $registrationFields->isNotEmpty())
        @foreach($registrationFields as $field)
            @include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'fields'])
        @endforeach
    @else
        <div class="col-12">
        <label class="form-label">Full name <span class="text-danger">*</span></label>
        <input class="form-control" name="name" value="{{ old('name') }}" required placeholder="Enter your full name">
        </div>@if(($authSettings['registration_email_enabled']??'1')==='1')<div class="col-md-6">
        <label class="form-label">Email address @if(($authSettings['registration_email_required']??'1')==='1')<span class="text-danger">*</span>@endif</label>
        <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" @required(($authSettings['registration_email_required']??'1')==='1') data-rule-email="true">
        </div>@endif @if(($authSettings['registration_mobile_enabled']??'1')==='1')<div class="col-md-6">
        <label class="form-label">Mobile number @if(($authSettings['registration_mobile_required']??'0')==='1')<span class="text-danger">*</span>@endif</label>
        <input class="form-control" name="mobile" type="tel" value="{{ old('mobile') }}" placeholder="Enter mobile number" @required(($authSettings['registration_mobile_required']??'0')==='1')>
        </div>@endif @if(($authSettings['registration_country_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">Country <span class="text-danger">*</span></label>
        <select class="form-select" name="country_id" id="micrositeCountry" required>
        <option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->id }}" @selected((int)old('country_id',(int)($authSettings['registration_default_country_id']??0))===$country->id)>{{ $country->name }}</option>@endforeach</select>
        </div>@endif @if(($authSettings['registration_state_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">State <span class="text-danger">*</span></label>
        <select class="form-select" name="state_id" id="micrositeState" required>
        <option value="">Select state</option>@foreach($states as $state)<option value="{{ $state->id }}" @selected((int)old('state_id',(int)($authSettings['registration_default_state_id']??0))===$state->id)>{{ $state->name }}</option>@endforeach</select>
        </div>@endif @if(($authSettings['registration_city_enabled']??'1')==='1')<div class="col-md-4">
        <label class="form-label">City <span class="text-danger">*</span></label>
        <select class="form-select" name="city_id" id="micrositeCity" required>
        <option value="">Select city</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected((int)old('city_id')===$city->id)>{{ $city->name }}</option>@endforeach</select>
        </div>@endif
        @foreach($signupFields as $field)@include('components.frontend-auth-field', ['field'=>$field,'prefix'=>'custom'])@endforeach
    @endif
    <div class="col-12">
    <label class="form-check">
    <input class="form-check-input" type="checkbox" name="terms" id="authTermsAgree" required> <span class="form-check-label">I agree to the Terms and Privacy Policy. <span class="text-danger">*</span></span>
    </label>
    </div>
    <div class="col-12">
    <button class="btn btn-gradient w-100 btn-lg" type="submit">Complete Registration</button>
    </div>
    <div class="col-12 auth-modal-switch">Already registered? <button type="button" class="auth-modal-link" data-bs-toggle="modal" data-bs-target="#micrositeLoginModal">Login here</button></div>
    </div>
    </form>
@endif
</div>
</div>
</div>
</div>

<style>
.frontend-auth-modal .invalid-feedback {
    display: block !important;
    font-size: 0.78rem;
    font-weight: 600;
    margin-top: 5px;
    color: #dc2626 !important;
}
.frontend-auth-modal .form-control.is-invalid,
.frontend-auth-modal .form-select.is-invalid {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important;
}
.frontend-auth-modal .form-control.is-valid,
.frontend-auth-modal .form-select.is-valid {
    border-color: #16a34a !important;
}
.frontend-auth-modal .form-check-input.is-invalid {
    border-color: #dc2626 !important;
}
</style>

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

    // Client-side jQuery validation for auth modals
    const initAuthValidation = () => {
        const forms = document.querySelectorAll('.frontend-auth-form');
        forms.forEach(form => {
            form.setAttribute('novalidate', 'novalidate');

            // Hook jQuery Validate if loaded
            const $ = window.jQuery || window.$;
            let jqueryValidateActive = false;
            if ($ && $.fn && $.fn.validate) {
                jqueryValidateActive = true;
                $(form).validate({
                    errorElement: 'div',
                    errorClass: 'invalid-feedback d-block',
                    ignore: ':hidden:not(select)',
                    focusInvalid: true,
                    highlight: function(element) {
                        $(element).addClass('is-invalid').removeClass('is-valid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                        if ($(element).val() && String($(element).val()).trim()) {
                            $(element).addClass('is-valid');
                        }
                    },
                    errorPlacement: function(error, element) {
                        const container = element.closest('.col-12, .col-md-6, .col-md-4, .form-check, .mb-3, .input-group') || element.parent();
                        container.find('.invalid-feedback').remove();
                        if (element.closest('.input-group').length) {
                            error.insertAfter(element.closest('.input-group'));
                        } else if (element.closest('.form-check').length) {
                            error.insertAfter(element.closest('.form-check'));
                        } else if (element.closest('.choice-group').length) {
                            error.insertAfter(element.closest('.choice-group'));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    messages: {
                        terms: {
                            required: 'Please agree to the Terms and Privacy Policy to continue.'
                        }
                    }
                });
            }

            // Interactive client-side validation fallback (ONLY if jQuery Validate is NOT loaded)
            if (!jqueryValidateActive) {
                form.addEventListener('submit', (e) => {
                    let isValid = true;
                    let firstInvalid = null;

                    const validateField = (input) => {
                        if (input.disabled || input.type === 'hidden') return true;
                        let valid = true;
                        let msg = 'This field is required.';

                        if (input.type === 'checkbox') {
                            if (input.required && !input.checked) {
                                valid = false;
                                msg = 'Please agree to the Terms and Privacy Policy to continue.';
                            }
                        } else if (input.tagName === 'SELECT') {
                            if (input.required && (!input.value || input.value === '')) {
                                valid = false;
                                msg = 'Please make a selection.';
                            }
                        } else {
                            const val = input.value.trim();
                            if (input.required && !val) {
                                valid = false;
                                msg = 'This field is required.';
                            } else if (val && (input.type === 'email' || input.dataset.ruleEmail === 'true')) {
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(val)) {
                                    valid = false;
                                    msg = 'Please enter a valid email address.';
                                }
                            }
                        }

                        // Remove existing feedback
                        const parent = input.closest('.col-12, .col-md-6, .col-md-4, .form-check, .mb-3') || input.parentElement;
                        parent.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                        if (!valid) {
                            input.classList.add('is-invalid');
                            input.classList.remove('is-valid');
                            const errDiv = document.createElement('div');
                            errDiv.className = 'invalid-feedback d-block';
                            errDiv.textContent = msg;

                            if (input.closest('.input-group')) {
                                input.closest('.input-group').after(errDiv);
                            } else if (input.closest('.form-check')) {
                                input.closest('.form-check').after(errDiv);
                            } else {
                                input.after(errDiv);
                            }
                            return false;
                        } else {
                            input.classList.remove('is-invalid');
                            if (input.value && input.value.trim()) input.classList.add('is-valid');
                            return true;
                        }
                    };

                    const fields = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
                    fields.forEach(field => {
                        if (!validateField(field)) {
                            isValid = false;
                            if (!firstInvalid) firstInvalid = field;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            }

            // Live clear of error state as attendee types or changes selection
            form.querySelectorAll('input, select, textarea').forEach(input => {
                const clearFn = () => {
                    if (input.classList.contains('is-invalid')) {
                        const parent = input.closest('.col-12, .col-md-6, .col-md-4, .form-check, .mb-3') || input.parentElement;
                        if (input.type === 'checkbox' && input.checked) {
                            input.classList.remove('is-invalid');
                            parent.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                        } else if (input.tagName === 'SELECT' && input.value) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                            parent.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                        } else if (input.value && input.value.trim()) {
                            if (input.type === 'email' || input.dataset.ruleEmail === 'true') {
                                if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
                                    input.classList.remove('is-invalid');
                                    input.classList.add('is-valid');
                                    parent.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                                }
                            } else {
                                input.classList.remove('is-invalid');
                                input.classList.add('is-valid');
                                parent.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                            }
                        }
                    }
                };
                input.addEventListener('input', clearFn);
                input.addEventListener('change', clearFn);
            });
        });
    };

    initAuthValidation();
});
</script>
