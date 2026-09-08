@extends('layouts.portal')
@section('title','Registration Fields')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow">REGISTRATION</span><h1>Registration fields</h1><p>Select a webinar, then manage all of its registration fields in one simple list.</p></div>
    @if($selectedWebinar)
        <a class="btn btn-gradient" href="{{ route('admin.registration.webinar.fields.create',$selectedWebinar) }}"><i class="bi bi-plus"></i> Add field</a>
    @endif
</div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<section class="panel-card mb-4">
    <div class="d-flex gap-3 flex-wrap">
        <select class="form-select" id="registrationWebinarSelect" style="max-width:340px">
            <option value="">Select webinar</option>
            @foreach($webinars as $webinar)
                <option value="{{ $webinar->id }}" @selected($selectedWebinar?->id===$webinar->id)>{{ $webinar->title }}</option>
            @endforeach
        </select>
        @if($selectedWebinar)
            <input class="form-control" id="dynamicFieldSearch" style="max-width:300px" placeholder="Search fields">
        @endif
    </div>
</section>

@if($selectedWebinar)
    @php($form=$selectedWebinar->registrationForm)
    <section class="panel-card mb-4"><div class="panel-title"><div><h3>Smart field generator</h3><p>Start with a proven field set, then reorder or edit it below. Existing fields stay untouched.</p></div><a class="btn btn-light" target="_blank" href="{{ route('admin.registration.preview',$selectedWebinar) }}"><i class="bi bi-eye"></i> Live preview</a></div><form class="d-flex gap-2 flex-wrap" method="POST" action="{{ route('admin.registration.webinar.preset',$selectedWebinar) }}">@csrf<select class="form-select" name="preset" style="max-width:280px" required><option value="">Select a template</option><option value="business">Business webinar</option><option value="education">Education webinar</option><option value="healthcare">Healthcare webinar</option><option value="marketing">Marketing webinar</option></select><button class="btn btn-gradient"><i class="bi bi-stars"></i> Generate fields</button></form></section>
    <section class="panel-card table-responsive">
        <form method="POST" action="{{ route('admin.registration.webinar.fields.bulk',$selectedWebinar) }}">@csrf @method('PUT')
            <table class="premium-table dynamic-field-table">
                <thead><tr><th>Move</th><th>Index</th><th>Field name</th><th>Label</th><th>Placeholder</th><th>Required</th><th>Status</th><th>Login with</th><th>Action</th></tr></thead>
                <tbody id="dynamicFieldRows">
                @forelse($form?->fields ?? [] as $field)
                    <tr draggable="true" data-dynamic-field-row>
                        <td class="drag-handle"><i class="bi bi-grip-vertical"></i></td>
                        <td><span class="status-badge draft" data-field-index>{{ $loop->iteration }}</span></td>
                        <td><code>{{ $field->field_key }}</code><input type="hidden" name="fields[{{ $field->id }}][display_order]" value="{{ $loop->index }}" data-display-order><input type="hidden" name="fields[{{ $field->id }}][icon]" value="{{ $field->icon?:'input-cursor-text' }}"><input type="hidden" name="fields[{{ $field->id }}][width]" value="{{ $field->width?:'full' }}"></td>
                        <td><input class="form-control form-control-sm" name="fields[{{ $field->id }}][label]" value="{{ $field->label }}" placeholder="Field label" required></td>
                        <td><input class="form-control form-control-sm" name="fields[{{ $field->id }}][placeholder]" value="{{ $field->placeholder }}" placeholder="Input placeholder"></td>
                        <td><label class="form-switch"><input class="form-check-input" type="checkbox" name="fields[{{ $field->id }}][is_required]" value="1" @checked($field->is_required)><span class="visually-hidden">Required</span></label></td>
                        <td><label class="form-switch"><input class="form-check-input" type="checkbox" name="fields[{{ $field->id }}][is_enabled]" value="1" @checked($field->is_enabled)><span class="visually-hidden">Enabled</span></label></td>
                        <td><input class="form-check-input" type="radio" name="login_field_id" value="{{ $field->id }}" @checked($field->login_enabled) title="Use as login field"></td>
                        <td><div class="d-flex gap-1"><a class="btn btn-sm btn-light" href="{{ route('admin.registration.webinar.fields.edit',$field) }}"><i class="bi bi-pencil"></i></a><button class="icon-btn text-danger" type="submit" form="delete-field-{{ $field->id }}"><i class="bi bi-trash"></i></button></div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">No registration fields have been added to this webinar.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="d-flex align-items-center justify-content-between mt-4"><label><input type="radio" name="login_field_id" value="" @checked(!($form?->fields?->contains('login_enabled',true)))> No custom login field</label><button class="btn btn-gradient">Save field list</button></div>
        </form>
    </section>
    @foreach($form?->fields ?? [] as $field)
        <form id="delete-field-{{ $field->id }}" method="POST" action="{{ route('admin.registration.webinar.fields.destroy',$field) }}">@csrf @method('DELETE')</form>
    @endforeach
@endif
@endsection
