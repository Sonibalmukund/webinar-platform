<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\City;
use App\Models\Country;
use App\Models\Poll;
use App\Models\RegistrationField;
use App\Models\SignupField;
use App\Models\Speaker;
use App\Models\State;
use App\Support\AuditTrail;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        try {
            $siteSettings = Schema::hasTable('settings') ? DB::table('settings')->where('group', 'site')->pluck('value', 'key') : collect();
        } catch (\Throwable $e) {
            $siteSettings = collect();
        }
        View::share('siteSettings', $siteSettings);
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
                'countries' => Country::where('is_active', true)->orderBy('name')->get(),
                'states' => State::where('country_id', $countryId)->where('is_active', true)->orderBy('name')->get(),
                'cities' => City::where('state_id', $stateId)->where('is_active', true)->orderBy('name')->get(),
                'signupFields' => SignupField::with(['options' => fn ($q) => $q->where('is_enabled', true)])->where('is_enabled', true)->orderBy('display_order')->get(),
            ]);
        });
    }
}
