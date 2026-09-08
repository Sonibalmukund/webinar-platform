@extends('layouts.portal')
@section('title','Update Password')
@section('content')
<div class="page-heading"><div><span class="eyebrow">MY ACCOUNT</span><h1>Update password</h1><p>Choose a strong password for your administrator account.</p></div><a class="btn btn-light" href="{{ route('admin.profile') }}"><i class="bi bi-arrow-left"></i> Back to profile</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<section class="panel-card profile-edit-full"><div class="form-header"><span class="modal-icon"><i class="bi bi-shield-lock"></i></span><div><h2>Password security</h2><p>Updating password for {{ auth()->user()->email }}</p></div></div><form method="POST" action="{{ route('admin.password.update') }}" class="form-grid">@csrf @method('PUT')
<label class="full">Current password<div class="input-icon password-field"><i class="bi bi-lock"></i><input class="form-control" type="password" name="current_password" placeholder="Enter current password" required><button type="button" class="password-toggle"><i class="bi bi-eye"></i></button></div></label>
<label>New password<div class="input-icon password-field"><i class="bi bi-key"></i><input class="form-control" type="password" name="password" placeholder="Minimum 8 characters" required><button type="button" class="password-toggle"><i class="bi bi-eye"></i></button></div></label>
<label>Confirm new password<div class="input-icon password-field"><i class="bi bi-key-fill"></i><input class="form-control" type="password" name="password_confirmation" placeholder="Enter new password again" required><button type="button" class="password-toggle"><i class="bi bi-eye"></i></button></div></label>
<div class="full password-guidance"><i class="bi bi-info-circle"></i><span>Use at least 8 characters. A mix of uppercase, lowercase, numbers and symbols is recommended.</span></div>
<div class="full d-flex justify-content-end gap-2"><a class="btn btn-light btn-lg" href="{{ route('admin.profile') }}">Cancel</a><button class="btn btn-gradient btn-lg">Update password</button></div></form></section>
@endsection
