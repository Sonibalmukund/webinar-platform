<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RegistrationField;
use App\Models\SignupField;
use App\Models\State;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $settings = DB::table('settings')->where('group', 'registration')->pluck('value', 'key');
        $webinarQuery = Webinar::query()->when(
            $request->user()->hasRole('sub-admin'),
            fn ($query) => $query->whereIn('id', $request->user()->assignedWebinars()->pluck('webinars.id'))
        );
        $selectedWebinar = $request->filled('webinar_id')
            ? (clone $webinarQuery)->with('registrationForm.fields.options')->findOrFail($request->integer('webinar_id'))
            : null;

        return view('pages.admin.registration-settings', [
            'settings' => $settings, 'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'states' => State::where('country_id', $settings['registration_default_country_id'] ?? 0)->orderBy('name')->get(),
            'fields' => SignupField::with('options')->orderBy('display_order')->get(),
            'webinars' => $webinarQuery->with('registrationForm')->latest()->get(),
            'selectedWebinar' => $selectedWebinar,
        ]);
    }

    public function preview(Webinar $webinar): View
    {
        return view('pages.admin.registration-preview', ['webinar' => $webinar->load('registrationForm.fields.options')]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! $request->boolean('email_enabled') && ! $request->boolean('mobile_enabled')) {
            return back()->withErrors(['email_enabled' => 'Enable at least Email or Mobile.'])->withInput();
        }
        $data = $request->validate(['default_country_id' => ['required', 'exists:countries,id'], 'default_state_id' => ['required', 'exists:states,id']]);
        $values = [
            'registration_country_enabled' => $request->boolean('country_enabled') ? '1' : '0',
            'registration_state_enabled' => $request->boolean('state_enabled') ? '1' : '0',
            'registration_city_enabled' => $request->boolean('city_enabled') ? '1' : '0',
            'registration_mobile_enabled' => $request->boolean('mobile_enabled') ? '1' : '0',
            'registration_email_enabled' => $request->boolean('email_enabled') ? '1' : '0',
            'registration_email_required' => $request->boolean('email_enabled') && $request->boolean('email_required') ? '1' : '0',
            'registration_mobile_required' => $request->boolean('mobile_enabled') && $request->boolean('mobile_required') ? '1' : '0',
            'registration_password_enabled' => $request->boolean('password_enabled') ? '1' : '0',
            'registration_password_required' => $request->boolean('password_enabled') && $request->boolean('password_required') ? '1' : '0',
            'registration_default_country_id' => (string) $data['default_country_id'],
            'registration_default_state_id' => (string) $data['default_state_id'],
        ];
        foreach ($values as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['group' => 'registration', 'value' => $value, 'is_public' => false, 'created_at' => now(), 'updated_at' => now()]);
        }

        return back()->with('status', 'Location settings saved.');
    }

    public function storeField(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'], 'icon' => ['nullable', 'string', 'max:60'], 'field_type' => ['required', 'in:text,password,dropdown,radio,checkbox'],
            'placeholder' => ['nullable', 'string', 'max:255'], 'options' => ['nullable', 'string'],
        ]);
        $field = SignupField::create([
            'label' => $data['label'], 'icon' => $data['icon'] ?? 'input-cursor-text', 'field_key' => Str::slug($data['label'], '_').'_'.Str::lower(Str::random(4)),
            'field_type' => $data['field_type'], 'placeholder' => $data['placeholder'] ?? null,
            'is_required' => $request->boolean('is_required'), 'is_enabled' => true,
            'display_order' => (SignupField::max('display_order') ?? 0) + 1,
        ]);
        if (in_array($field->field_type, ['dropdown', 'radio', 'checkbox'])) {
            foreach (array_values(array_filter(array_map('trim', explode("\n", $data['options'] ?? '')))) as $order => $label) {
                $field->options()->create(['label' => $label, 'value' => Str::slug($label, '_'), 'display_order' => $order]);
            }
        }

        return back()->with('status', 'Custom registration field added.');
    }

    public function toggleField(Request $request, SignupField $field): RedirectResponse
    {
        $field->update(['is_enabled' => ! $field->is_enabled]);

        return back()->with('status', 'Field visibility updated.');
    }

    public function destroyField(SignupField $field): RedirectResponse
    {
        $field->delete();

        return back()->with('status', 'Custom field removed.');
    }

    public function updateWebinarForm(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string']]);
        $webinar->registrationForm()->updateOrCreate([], ['title' => $data['title'], 'description' => $data['description'] ?? null, 'is_active' => $request->boolean('is_active'), 'require_login' => true]);

        return back()->with('status', 'Selected webinar form updated.');
    }

    public function storeWebinarField(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $this->validateWebinarField($request);
        $form = $webinar->registrationForm()->firstOrCreate([], ['title' => 'Registration Form', 'is_active' => true, 'require_login' => true]);
        if ($request->boolean('login_enabled')) {
            $form->fields()->update(['login_enabled' => false]);
        }
        $field = $form->fields()->create(['label' => $data['label'], 'field_key' => Str::slug($data['label'], '_').'_'.Str::lower(Str::random(4)), 'field_type' => $data['field_type'], 'placeholder' => $data['placeholder'] ?? null, 'icon' => $request->input('icon', 'input-cursor-text'), 'width' => $request->input('width', 'full'), 'is_required' => $request->boolean('is_required'), 'is_enabled' => true, 'login_enabled' => $request->boolean('login_enabled'), 'condition_field_id' => $data['condition_field_id'] ?? null, 'condition_operator' => $data['condition_operator'] ?? null, 'condition_value' => $data['condition_value'] ?? null, 'display_order' => ($form->fields()->max('display_order') ?? 0) + 1]);
        if (in_array($field->field_type, ['dropdown', 'radio', 'checkbox'])) {
            foreach (array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['options'] ?? '')))) as $order => $label) {
                $field->options()->create(['label' => $label, 'value' => Str::slug($label, '_'), 'display_order' => $order, 'is_enabled' => true]);
            }
        }

        return redirect()->route('admin.dynamic-fields.index', ['webinar_id' => $webinar->id])->with('status', 'Dynamic field added to '.$webinar->title.'.');
    }

    public function createWebinarField(Webinar $webinar): View
    {
        return view('pages.admin.registration-field-form', ['webinar' => $webinar->load('registrationForm.fields'), 'field' => new RegistrationField]);
    }

    public function editWebinarField(RegistrationField $field): View
    {
        return view('pages.admin.registration-field-form', ['webinar' => $field->form->webinar->load('registrationForm.fields'), 'field' => $field->load('options')]);
    }

    public function updateWebinarField(Request $request, RegistrationField $field): RedirectResponse
    {
        $data = $this->validateWebinarField($request, $field);
        if ($request->boolean('login_enabled')) {
            $field->form->fields()->whereKeyNot($field->id)->update(['login_enabled' => false]);
        }
        $field->update(['label' => $data['label'], 'field_type' => $data['field_type'], 'placeholder' => $data['placeholder'] ?? null, 'icon' => $request->input('icon', 'input-cursor-text'), 'width' => $request->input('width', 'full'), 'is_required' => $request->boolean('is_required'), 'is_enabled' => $request->boolean('is_enabled'), 'login_enabled' => $request->boolean('login_enabled'), 'condition_field_id' => $data['condition_field_id'] ?? null, 'condition_operator' => $data['condition_operator'] ?? null, 'condition_value' => $data['condition_value'] ?? null]);
        $field->options()->delete();
        if (in_array($field->field_type, ['dropdown', 'radio', 'checkbox'])) {
            foreach (array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['options'] ?? '')))) as $order => $label) {
                $field->options()->create(['label' => $label, 'value' => Str::slug($label, '_'), 'display_order' => $order, 'is_enabled' => true]);
            }
        }

        return redirect()->route('admin.dynamic-fields.index', ['webinar_id' => $field->form->webinar_id])->with('status', 'Dynamic field updated.');
    }

    public function bulkUpdateWebinarFields(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $request->validate([
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.icon' => ['required', 'string', 'max:60'],
            'fields.*.width' => ['required', 'in:half,full'],
            'login_field_id' => ['nullable', 'integer'],
        ]);
        $rows = $data['fields'] ?? [];
        $form = $webinar->registrationForm;
        $loginFieldId = (int) ($data['login_field_id'] ?? 0);
        foreach ($rows as $id => $row) {
            $field = $form?->fields()->find($id);
            if ($field) {
                $field->update([
                    'label' => $row['label'], 'placeholder' => $row['placeholder'] ?? null,
                    'icon' => $row['icon'], 'width' => $row['width'],
                    'is_required' => isset($row['is_required']), 'is_enabled' => isset($row['is_enabled']),
                    'login_enabled' => $loginFieldId === (int) $field->id,
                    'display_order' => (int) ($row['display_order'] ?? 0),
                ]);
            }
        }

        return back()->with('status', 'Dynamic field settings saved.');
    }

    public function toggleWebinarField(RegistrationField $field): RedirectResponse
    {
        $field->update(['is_enabled' => ! $field->is_enabled]);

        return back()->with('status', 'Webinar field visibility updated.');
    }

    public function destroyWebinarField(RegistrationField $field): RedirectResponse
    {
        $field->delete();

        return back()->with('status', 'Webinar field deleted.');
    }

    public function applyPreset(Request $request, Webinar $webinar): RedirectResponse
    {
        $preset = $request->validate(['preset' => ['required', 'in:business,education,healthcare,marketing']])['preset'];
        $sets = ['business' => [['Company', 'building'], ['Job title', 'briefcase'], ['Industry', 'graph-up']], 'education' => [['Institution', 'building'], ['Course or program', 'mortarboard'], ['Experience level', 'bar-chart']], 'healthcare' => [['Organization', 'building'], ['Professional role', 'person-badge'], ['Specialty', 'heart-pulse']], 'marketing' => [['Company', 'building'], ['Team size', 'people'], ['Primary goal', 'bullseye']]];
        $form = $webinar->registrationForm()->firstOrCreate([], ['title' => 'Registration Form', 'is_active' => true, 'require_login' => true]);
        foreach ($sets[$preset] as $item) {
            $key = Str::slug($item[0], '_');
            $form->fields()->firstOrCreate(['field_key' => $key], ['label' => $item[0], 'field_type' => 'text', 'placeholder' => 'Enter '.Str::lower($item[0]), 'icon' => $item[1], 'width' => 'half', 'is_required' => false, 'is_enabled' => true, 'display_order' => ($form->fields()->max('display_order') ?? 0) + 1]);
        }

        return back()->with('status', ucfirst($preset).' smart field template applied. Existing fields were preserved.');
    }

    private function validateWebinarField(Request $request, ?RegistrationField $field = null): array
    {
        return $request->validate(['label' => ['required', 'string', 'max:255'], 'field_type' => ['required', 'in:text,password,dropdown,radio,checkbox,country,state,city'], 'placeholder' => ['nullable', 'string', 'max:255'], 'options' => ['nullable', 'string'], 'condition_field_id' => ['nullable', 'integer', 'exists:registration_fields,id'], 'condition_operator' => ['nullable', 'in:equals,not_equals'], 'condition_value' => ['nullable', 'string', 'max:255']]);
    }
}
