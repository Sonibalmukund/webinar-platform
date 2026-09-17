@php
$fieldId = 'auth-'.$prefix.'-'.$field->id;
$fieldName = $prefix.'['.$field->id.']';
$fieldValue = old($prefix.'.'.$field->id);
$lowerLabel = strtolower(trim($field->label));
$isName = in_array($lowerLabel, ['name', 'full name', 'your name']) || str_starts_with($field->field_key, 'name') || str_starts_with($field->field_key, 'full_name');
$isEmail = $field->field_type === 'email' || in_array($lowerLabel, ['email', 'email address']) || str_starts_with($field->field_key, 'email');
$isMobile = in_array($field->field_type, ['mobile', 'phone', 'tel']) || in_array($lowerLabel, ['mobile', 'mobile number', 'phone', 'phone number']) || str_starts_with($field->field_key, 'mobile') || str_starts_with($field->field_key, 'phone');
$isNumber = $field->field_type === 'number';

if ($fieldValue === null && auth()->check()) {
    if ($isName) {
        $fieldValue = auth()->user()->name;
    } elseif ($isEmail) {
        $fieldValue = auth()->user()->email;
    } elseif ($isMobile) {
        $fieldValue = auth()->user()->mobile;
    }
}
$inputType = $isEmail ? 'email' : ($isNumber ? 'number' : ($isMobile ? 'tel' : 'text'));
@endphp
<div class="col-12">
    <label class="form-label" for="{{ $fieldId }}">{{ $field->label }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
    @if(in_array($field->field_type,['dropdown','country','state','city'],true))
        @php
        $choices = match($field->field_type) {
            'country' => $countries,
            'state' => $states,
            'city' => $cities,
            default => $field->options,
        };
        @endphp
        <select class="form-select" id="{{ $fieldId }}" name="{{ $fieldName }}" @required($field->is_required)>
            <option value="">Select {{ strtolower($field->label) }}</option>
            @foreach($choices as $option)
                @php
                $value = in_array($field->field_type, ['country', 'state', 'city']) ? $option->id : $option->value;
                @endphp
                <option value="{{ $value }}" @selected((string)$fieldValue === (string)$value)>{{ $option->label ?? $option->name }}</option>
            @endforeach
        </select>
    @elseif(in_array($field->field_type,['radio','checkbox'],true))
        <div class="choice-group" role="group" aria-label="{{ $field->label }}">
            @foreach($field->options as $option)<label><input type="{{ $field->field_type }}" name="{{ $fieldName }}{{ $field->field_type==='checkbox'?'[]':'' }}" value="{{ $option->value }}" @checked(in_array($option->value,(array)$fieldValue)) @required($field->is_required && $field->field_type==='radio')> {{ $option->label }}</label>@endforeach
        </div>
    @elseif($field->field_type==='password')
        <div class="input-group">
            <input id="{{ $fieldId }}" type="password" class="form-control" name="{{ $fieldName }}" placeholder="{{ $field->placeholder ?: 'Enter '.$field->label }}" @required($field->is_required)>
            <button class="btn btn-outline-secondary" type="button" onclick="const input = document.getElementById('{{ $fieldId }}'); const icon = this.querySelector('i'); if (input.type === 'password') { input.type = 'text'; icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash'); } else { input.type = 'password'; icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye'); }">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    @else
        <input id="{{ $fieldId }}" type="{{ $inputType }}" class="form-control" name="{{ $fieldName }}" value="{{ $fieldValue }}" placeholder="{{ $field->placeholder ?: 'Enter '.$field->label }}" @required($field->is_required) @if($isEmail) data-rule-email="true" @endif>
    @endif
    @if($field->help_text)<small class="text-muted">{{ $field->help_text }}</small>@endif
</div>
