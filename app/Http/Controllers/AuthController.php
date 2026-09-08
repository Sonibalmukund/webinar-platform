<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\SignupField;
use App\Models\SignupFieldAnswer;
use App\Models\RegistrationAnswer;
use App\Models\RegistrationField;
use App\Models\Webinar;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\FrontendAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request): RedirectResponse { return redirect(FrontendAuth::landing($request, 'login')); }
    public function showRegister(Request $request): RedirectResponse { return redirect(FrontendAuth::landing($request, 'register')); }
    public function showForgot(Request $request): RedirectResponse { return redirect(FrontendAuth::landing($request, 'forgot')); }
    public function showAdminLogin(): View { return $this->show('admin'); }
    public function showSubAdminLogin(): View { return $this->show('subadmin'); }
    public function loginUser(Request $request): RedirectResponse { return $this->login($request, 'user'); }
    public function loginAdmin(Request $request): RedirectResponse { return $this->login($request, 'admin'); }
    public function loginSubAdmin(Request $request): RedirectResponse { return $this->login($request, 'subadmin'); }

    public function show(string $mode = 'login'): View
    {
        $data = compact('mode');
        $data['locationSettings'] = DB::table('settings')->where('group','registration')->pluck('value','key');
        if ($mode === 'register') {
            $settings = $data['locationSettings'];
            $countryId = (int)($settings['registration_default_country_id'] ?? Country::where('iso2','IN')->value('id'));
            $stateId = (int)($settings['registration_default_state_id'] ?? State::where('name','Gujarat')->value('id'));
            $data += ['locationSettings'=>$settings, 'countries'=>Country::where('is_active',true)->orderBy('name')->get(),
                'states'=>State::where('country_id',$countryId)->where('is_active',true)->orderBy('name')->get(),
                'cities'=>City::where('state_id',$stateId)->where('is_active',true)->orderBy('name')->get(),
                'signupFields'=>SignupField::with(['options'=>fn($q)=>$q->where('is_enabled',true)])->where('is_enabled',true)->orderBy('display_order')->get()];
        }
        return view('pages.auth.auth', $data);
    }

    public function login(Request $request, string $portal = 'user'): RedirectResponse
    {
        if ($portal === 'user') $request->merge(['_auth_modal' => 'login']);
        $rules = ['login' => ['required','string'], 'password' => ['required', 'string']];
        $credentials = $portal === 'user' ? $this->validateFrontend($request, $rules, 'login') : $request->validate($rules);
        $login=$credentials['login'];
        $loginSettings = DB::table('settings')->where('group','registration')->pluck('value','key');
        $user=null;
        if ($portal === 'user' && $request->filled(['webinar_id','login_field_id'])) {
            $field=RegistrationField::whereKey($request->integer('login_field_id'))
                ->where('login_enabled',true)->where('is_enabled',true)
                ->whereHas('form',fn($query)=>$query->where('webinar_id',$request->integer('webinar_id')))->first();
            if ($field) {
                $answer=RegistrationAnswer::where('registration_field_id',$field->id)->where('value',$login)
                    ->whereHas('registration',fn($query)=>$query->where('webinar_id',$request->integer('webinar_id')))
                    ->with('registration.user')->first();
                $user=$answer?->registration?->user;
            }
        } else {
            $userQuery = User::query();
            if ($portal !== 'user' || ($loginSettings['registration_email_enabled'] ?? '1') === '1') $userQuery->orWhere('email',$login);
            if ($portal !== 'user' || ($loginSettings['registration_mobile_enabled'] ?? '1') === '1') $userQuery->orWhere('mobile',$login);
            $user=$userQuery->first();
        }
        if (! $user || ! Hash::check($credentials['password'],$user->password)) {
            return ($portal === 'user' ? redirect(FrontendAuth::landing($request, 'login')) : back())->withErrors(['login' => 'The login details or password are incorrect.'])->onlyInput('login', '_auth_modal', 'return_to', 'webinar_id', 'login_field_id');
        }
        Auth::login($user,$request->boolean('remember'));
        $request->session()->regenerate();
        $user = $request->user();
        $requiredRole = ['admin' => 'super-admin', 'subadmin' => 'sub-admin'][$portal] ?? 'learner';
        if (! $user->hasRole($requiredRole)) {
            Auth::logout();
            return ($portal === 'user' ? redirect(FrontendAuth::landing($request, 'login')) : back())->withErrors(['login' => 'This account does not have access to this portal.'])->onlyInput('login', '_auth_modal', 'return_to');
        }
        $dashboardWebinar=$portal === 'user' ? FrontendAuth::webinar($request) : null;
        $destination = null;
        if ($dashboardWebinar) {
            $request->session()->put('frontend_event_slug', $dashboardWebinar->slug);
            $destination = route('webinars.show', $dashboardWebinar);
            if (! $dashboardWebinar->registrations()->where('user_id', $user->id)->exists()) $destination .= '#registration';
            $request->session()->forget('url.intended');
        }
        if($portal==='user' && !$dashboardWebinar && $destination && preg_match('#^/(?:webinars/)?([A-Za-z0-9-]+)$#',$destination,$matches)) {
            $registeredWebinar=Webinar::where('slug',$matches[1])->whereHas('registrations',fn($query)=>$query->where('user_id',$user->id))->first();
            if($registeredWebinar) $destination=route('webinars.dashboard',$registeredWebinar);
        }
        if ($destination) {
            return redirect($destination)->with('auth_status','Login successful. Welcome, '.$user->name.'.');
        }

        return redirect()->intended(match ($portal) { 'admin' => '/admin/dashboard', 'subadmin' => '/sub-admin/dashboard', default => '/dashboard' })->with('auth_status','Welcome back, '.$user->name.'. You are signed in.');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['_auth_modal' => 'register']);
        $settings = DB::table('settings')->where('group','registration')->pluck('value','key');
        $fields = SignupField::with('options')->where('is_enabled',true)->get();
        $emailEnabled = ($settings['registration_email_enabled'] ?? '1') === '1';
        $mobileEnabled = ($settings['registration_mobile_enabled'] ?? '1') === '1';
        $passwordEnabled = ($settings['registration_password_enabled'] ?? '1') === '1';
        $returnSlug=trim($request->string('return_to')->toString(),'/');
        $webinar=$request->filled('webinar_id')
            ? Webinar::with('registrationForm.fields.options')->find($request->integer('webinar_id'))
            : (preg_match('/^[A-Za-z0-9-]+$/',$returnSlug) ? Webinar::with('registrationForm.fields.options')->where('slug',$returnSlug)->first() : null);
        $registrationFields=$webinar?->registrationForm?->fields?->where('is_enabled',true) ?? collect();
        $rules = ['name'=>['required','string','max:255']];
        if ($emailEnabled) $rules['email'] = [($settings['registration_email_required'] ?? '1')==='1'?'required':'nullable','email','unique:users'];
        if ($mobileEnabled) $rules['mobile'] = [($settings['registration_mobile_required'] ?? '0')==='1'?'required':'nullable','string','max:30','unique:users,mobile'];
        if ($passwordEnabled) $rules['password'] = [($settings['registration_password_required'] ?? '1')==='1'?'required':'nullable','string','min:6','confirmed'];
        if (($settings['registration_country_enabled'] ?? '1') === '1') $rules['country_id'] = ['required','exists:countries,id'];
        if (($settings['registration_state_enabled'] ?? '1') === '1') $rules['state_id'] = ['required','exists:states,id'];
        if (($settings['registration_city_enabled'] ?? '1') === '1') $rules['city_id'] = ['required','exists:cities,id'];
        foreach ($fields as $field) $rules['custom.'.$field->id] = [$field->is_required?'required':'nullable', $field->field_type==='checkbox'?'array':'string'];
        foreach($registrationFields as $field){
            if(in_array($field->field_key,['full_name','email','mobile','city'],true)) continue;
            $typeRule=match($field->field_type){'checkbox'=>'array','country'=>'exists:countries,id','state'=>'exists:states,id','city'=>'exists:cities,id',default=>'string'};
            $rules['fields.'.$field->id]=[$field->is_required?'required':'nullable',$typeRule];
        }
        $data = $this->validateFrontend($request, $rules, 'register');
        $countryId = ($settings['registration_country_enabled'] ?? '1') === '1' ? $request->integer('country_id') : (int)$settings['registration_default_country_id'];
        $stateId = ($settings['registration_state_enabled'] ?? '1') === '1' ? $request->integer('state_id') : (int)$settings['registration_default_state_id'];
        $cityId = ($settings['registration_city_enabled'] ?? '1') === '1' ? $request->integer('city_id') : null;
        if ($cityId) {
            $city = City::with('state.country')->findOrFail($cityId);
            $stateId = $city->state_id;
            $countryId = $city->state->country_id;
        }
        $email = $data['email'] ?? ('mobile-'.preg_replace('/\D/','',$data['mobile'] ?? '').'-'.Str::lower(Str::random(6)).'@internal.local');
        $user = User::create(['name'=>$data['name'],'email'=>$email,'mobile'=>$data['mobile']??null,'password'=>$data['password']??Str::random(40),'country_id'=>$countryId,'state_id'=>$stateId,'city_id'=>$cityId]);
        $role = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner', 'description' => 'Webinar learner']);
        $user->roles()->sync([$role->id]);
        foreach ($fields as $field) {
            $value = data_get($request->input('custom', []), (string)$field->id);
            if ($value !== null) SignupFieldAnswer::create(['user_id'=>$user->id,'signup_field_id'=>$field->id,'value'=>is_array($value)?json_encode($value):$value]);
        }
        if($webinar && $webinar->registrationForm?->is_active){
            $status=$webinar->auto_approve?'approved':'pending';
            $registration=Registration::create(['webinar_id'=>$webinar->id,'user_id'=>$user->id,'email'=>$user->email,'status'=>$status,'source'=>'public-microsite','registered_at'=>now(),'approved_at'=>$status==='approved'?now():null]);
            foreach($registrationFields as $field){
                $value=match($field->field_key){'full_name'=>$user->name,'email'=>$user->email,'mobile'=>$user->mobile,'city'=>$user->city_id,default=>data_get($request->input('fields',[]),(string)$field->id)};
                if($value!==null && $value!=='') RegistrationAnswer::create(['registration_id'=>$registration->id,'registration_field_id'=>$field->id,'value'=>is_array($value)?json_encode($value):$value]);
            }
        }
        Auth::login($user);
        $request->session()->regenerate();
        if ($webinar) {
            $request->session()->put('frontend_event_slug', $webinar->slug);
            $request->session()->forget('url.intended');
        }
        if ($webinar) {
            return redirect()->route('webinars.show', $webinar)->with('registration_status','Registration successfully completed. Your webinar seat is ready.');
        }
        return redirect($this->micrositeReturnPath($request) ?: '/dashboard')->with('registration_status','Account created successfully.');
    }

    private function micrositeReturnPath(Request $request): ?string
    {
        $path = $request->string('return_to')->toString();

        return preg_match('#^/(?:webinars/)?[A-Za-z0-9-]+(?:/dashboard)?$#', $path) ? $path : null;
    }

    public function forgot(Request $request): RedirectResponse
    {
        $request->merge(['_auth_modal' => 'forgot']);
        $this->validateFrontend($request, ['email' => ['required','email']], 'forgot');
        return redirect(FrontendAuth::landing($request, 'forgot'))->with('status', 'If that account exists, password reset instructions have been prepared.');
    }

    private function validateFrontend(Request $request, array $rules, string $modal): array
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) throw (new ValidationException($validator))->redirectTo(FrontendAuth::landing($request, $modal));
        return $validator->validated();
    }

    public function logout(Request $request): RedirectResponse
    {
        $staffDestination = $request->user()?->hasRole('super-admin') ? '/admin/login'
            : ($request->user()?->hasRole('sub-admin') ? '/sub-admin/login' : null);
        $destination = null;
        if ($request->filled('return_to') || $request->filled('webinar_id')) $destination = FrontendAuth::landing($request);
        if(!$destination && ($referer=$request->headers->get('referer'))) {
            $path=parse_url($referer,PHP_URL_PATH);
            if(is_string($path) && preg_match('#^/([A-Za-z0-9-]+)/dashboard$#',$path,$matches) && Webinar::where('slug',$matches[1])->whereNot('status','draft')->exists()) $destination='/'.$matches[1];
        }
        $destination=$staffDestination ?: ($destination ?: FrontendAuth::landing($request));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect($destination);
    }
}
