<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Support\AuditTrail;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Speaker;
use App\Models\Poll;
use App\Models\RegistrationField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        $siteSettings=Schema::hasTable('settings') ? DB::table('settings')->where('group','site')->pluck('value','key') : collect();
        View::share('siteSettings',$siteSettings);
        View::composer('components.frontend-auth', function ($view) {
            $webinar = $view->getData()['authWebinar'] ?? null;
            $settings = DB::table('settings')->where('group', 'registration')->pluck('value', 'key');
            $countryId = (int) old('country_id', $settings['registration_default_country_id'] ?? 0);
            $stateId = (int) old('state_id', $settings['registration_default_state_id'] ?? 0);
            $fields = $webinar?->registrationForm?->fields?->where('is_enabled', true) ?? collect();
            $view->with([
                'authWebinar' => $webinar,
                'authSettings' => $settings,
                'loginField' => $fields->first(fn ($field) => $field->login_enabled),
                'registrationFields' => $fields,
                'countries' => \App\Models\Country::where('is_active', true)->orderBy('name')->get(),
                'states' => \App\Models\State::where('country_id', $countryId)->where('is_active', true)->orderBy('name')->get(),
                'cities' => \App\Models\City::where('state_id', $stateId)->where('is_active', true)->orderBy('name')->get(),
                'signupFields' => \App\Models\SignupField::with(['options' => fn ($q) => $q->where('is_enabled', true)])->where('is_enabled', true)->orderBy('display_order')->get(),
            ]);
        });
        foreach([Banner::class,Brand::class,Speaker::class,Poll::class,RegistrationField::class] as $modelClass){$label=class_basename($modelClass);$modelClass::created(fn($model)=>AuditTrail::record(strtolower($label).'.created',$model,$label.' created.'));$modelClass::updated(fn($model)=>AuditTrail::record(strtolower($label).'.updated',$model,$label.' updated.',['changes'=>$model->getChanges()]));$modelClass::deleted(fn($model)=>AuditTrail::record(strtolower($label).'.deleted',$model,$label.' deleted.'));}
    }
}
