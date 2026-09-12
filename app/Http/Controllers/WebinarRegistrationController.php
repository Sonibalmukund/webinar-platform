<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\Webinar;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebinarRegistrationController extends Controller
{
    public function store(Request $request, Webinar $webinar): RedirectResponse
    {
        abort_unless($webinar->registrationForm?->is_active, 403, 'Registration is disabled.');
        $fields = $webinar->registrationForm->fields()->with('options')->where('is_enabled', true)->get();
        $input = $request->all();
        foreach ($fields as $field) {
            $lowerLabel = strtolower(trim($field->label));
            $isName = in_array($lowerLabel, ['name', 'full name', 'your name']) || str_starts_with($field->field_key, 'name') || str_starts_with($field->field_key, 'full_name');
            $isEmail = in_array($lowerLabel, ['email', 'email address']) || str_starts_with($field->field_key, 'email');
            $isMobile = in_array($lowerLabel, ['mobile', 'mobile number', 'phone', 'phone number']) || str_starts_with($field->field_key, 'mobile');

            if ($request->user()) {
                if ($isName && empty(data_get($input, 'fields.'.$field->id))) {
                    data_set($input, 'fields.'.$field->id, $request->user()->name);
                } elseif ($isEmail && empty(data_get($input, 'fields.'.$field->id))) {
                    data_set($input, 'fields.'.$field->id, $request->user()->email);
                } elseif ($isMobile && empty(data_get($input, 'fields.'.$field->id)) && $request->user()->mobile) {
                    data_set($input, 'fields.'.$field->id, $request->user()->mobile);
                }
            }
        }
        $request->merge($input);

        $rules = [];
        foreach ($fields as $field) {
            $conditionMet = true;
            if ($field->condition_field_id) {
                $actual = data_get($request->input('fields', []), (string) $field->condition_field_id);
                $conditionMet = $field->condition_operator === 'not_equals' ? (string) $actual !== (string) $field->condition_value : (string) $actual === (string) $field->condition_value;
            }
            $typeRule = match ($field->field_type) {
                'checkbox' => 'array','country' => 'exists:countries,id','state' => 'exists:states,id','city' => 'exists:cities,id',default => 'string'
            };
            $rules['fields.'.$field->id] = [$field->is_required && $conditionMet ? 'required' : 'nullable', $typeRule];
        }
        $request->validate($rules);
        $existingRegistration = Registration::where(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id])->first();
        $admittedCount = $webinar->registrations()->admitted()->count();
        $status = $existingRegistration && ! in_array($existingRegistration->status, ['waitlisted', 'cancelled', 'rejected'], true)
            ? 'approved'
            : ($webinar->max_attendees && $admittedCount >= $webinar->max_attendees ? 'waitlisted' : 'approved');
        $registration = Registration::updateOrCreate(
            ['webinar_id' => $webinar->id, 'email' => $request->user()->email],
            ['user_id' => $request->user()->id, 'status' => $status, 'source' => 'web', 'registered_at' => now(), 'approved_at' => $status === 'approved' ? now() : null]
        );
        foreach ($fields as $field) {
            $lowerLabel = strtolower(trim($field->label));
            $isName = in_array($lowerLabel, ['name', 'full name', 'your name']) || str_starts_with($field->field_key, 'name') || str_starts_with($field->field_key, 'full_name');
            $isEmail = in_array($lowerLabel, ['email', 'email address']) || str_starts_with($field->field_key, 'email');
            $isMobile = in_array($lowerLabel, ['mobile', 'mobile number', 'phone', 'phone number']) || str_starts_with($field->field_key, 'mobile');

            $value = data_get($request->input('fields', []), (string) $field->id);
            if (($value === null || $value === '') && $request->user()) {
                if ($isName) {
                    $value = $request->user()->name;
                } elseif ($isEmail) {
                    $value = $request->user()->email;
                } elseif ($isMobile) {
                    $value = $request->user()->mobile;
                }
            }
            if ($value !== null) {
                RegistrationAnswer::updateOrCreate(['registration_id' => $registration->id, 'registration_field_id' => $field->id], ['value' => is_array($value) ? json_encode($value) : $value]);
            }
        }
        AuditTrail::record('registration.created', $registration, 'Webinar registration submitted.', ['status' => $status, 'webinar_id' => $webinar->id]);

        return redirect()->route($status === 'waitlisted' ? 'webinars.show' : 'webinars.dashboard', $webinar)->with('registration_status', $status === 'waitlisted' ? 'The webinar is full. You have been added to the waitlist.' : 'Your registration is confirmed. You can enter the webinar now.');
    }
}
