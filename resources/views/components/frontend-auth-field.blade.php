@php($fieldId = 'auth-'.$prefix.'-'.$field->id)
@php($fieldName = $prefix.'['.$field->id.']')
@php($fieldValue = old($prefix.'.'.$field->id))
<div class="col-12">
    <label class="form-label" for="{{ $fieldId }}">{{ $field->label }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
    @if(in_array($field->field_type,['dropdown','country','state','city'],true))
        <select class="form-select" id="{{ $fieldId }}" name="{{ $fieldName }}" @required($field->is_required)>
            <option value="">Select {{ strtolower($field->label) }}</option>
            @php($choices = match($field->field_type) {'country'=>$countries,'state'=>$states,'city'=>$cities,default=>$field->options})
            @foreach($choices as $option)@php($value = in_array($field->field_type,['country','state','city']) ? $option->id : $option->value)<option value="{{ $value }}" @selected((string)$fieldValue===(string)$value)>{{ $option->label ?? $option->name }}</option>@endforeach
        </select>
    @elseif(in_array($field->field_type,['radio','checkbox'],true))
        <div class="choice-group" role="group" aria-label="{{ $field->label }}">
            @foreach($field->options as $option)<label><input type="{{ $field->field_type }}" name="{{ $fieldName }}{{ $field->field_type==='checkbox'?'[]':'' }}" value="{{ $option->value }}" @checked(in_array($option->value,(array)$fieldValue)) @required($field->is_required && $field->field_type==='radio')> {{ $option->label }}</label>@endforeach
        </div>
    @else
        <input id="{{ $fieldId }}" class="form-control" name="{{ $fieldName }}" value="{{ $fieldValue }}" placeholder="{{ $field->placeholder }}" @required($field->is_required)>
    @endif
    @if($field->help_text)<small class="text-muted">{{ $field->help_text }}</small>@endif
</div>
