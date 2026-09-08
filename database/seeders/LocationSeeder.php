<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $worldFile = database_path('data/world-locations.json.gz');
        if (is_file($worldFile)) {
            $this->importWorldDataset($worldFile);
            return;
        }
        $data = [
            'IN' => ['India', '+91', [
                'GJ' => ['Gujarat', ['Ahmedabad','Surat','Vadodara','Rajkot','Gandhinagar','Bhavnagar','Jamnagar','Junagadh','Anand','Mehsana']],
                'MH' => ['Maharashtra', ['Mumbai','Pune','Nagpur','Nashik','Thane']],
                'DL' => ['Delhi', ['New Delhi','Delhi']], 'KA' => ['Karnataka', ['Bengaluru','Mysuru','Mangaluru']],
                'TN' => ['Tamil Nadu', ['Chennai','Coimbatore','Madurai']], 'RJ' => ['Rajasthan', ['Jaipur','Udaipur','Jodhpur']],
                'UP' => ['Uttar Pradesh', ['Lucknow','Noida','Varanasi','Agra']], 'WB' => ['West Bengal', ['Kolkata','Howrah','Darjeeling']],
                'TG' => ['Telangana', ['Hyderabad','Warangal']], 'KL' => ['Kerala', ['Thiruvananthapuram','Kochi','Kozhikode']],
                'MP' => ['Madhya Pradesh', ['Bhopal','Indore','Gwalior']], 'PB' => ['Punjab', ['Chandigarh','Ludhiana','Amritsar']],
                'HR' => ['Haryana', ['Gurugram','Faridabad','Panipat']], 'BR' => ['Bihar', ['Patna','Gaya']],
                'OR' => ['Odisha', ['Bhubaneswar','Cuttack']], 'AS' => ['Assam', ['Guwahati','Dibrugarh']],
                'GA' => ['Goa', ['Panaji','Margao']], 'JH' => ['Jharkhand', ['Ranchi','Jamshedpur']],
                'UK' => ['Uttarakhand', ['Dehradun','Haridwar']], 'HP' => ['Himachal Pradesh', ['Shimla','Dharamshala']],
            ]],
            'US' => ['United States', '+1', ['CA'=>['California',['Los Angeles','San Francisco','San Diego']], 'NY'=>['New York',['New York City','Buffalo']], 'TX'=>['Texas',['Houston','Austin','Dallas']], 'FL'=>['Florida',['Miami','Orlando']]]],
            'GB' => ['United Kingdom', '+44', ['ENG'=>['England',['London','Manchester','Birmingham']], 'SCT'=>['Scotland',['Edinburgh','Glasgow']], 'WLS'=>['Wales',['Cardiff','Swansea']]]],
            'CA' => ['Canada', '+1', ['ON'=>['Ontario',['Toronto','Ottawa']], 'BC'=>['British Columbia',['Vancouver','Victoria']], 'QC'=>['Quebec',['Montreal','Quebec City']]]],
            'AU' => ['Australia', '+61', ['NSW'=>['New South Wales',['Sydney','Newcastle']], 'VIC'=>['Victoria',['Melbourne','Geelong']], 'QLD'=>['Queensland',['Brisbane','Gold Coast']]]],
            'AE' => ['United Arab Emirates', '+971', ['DU'=>['Dubai',['Dubai']], 'AZ'=>['Abu Dhabi',['Abu Dhabi','Al Ain']], 'SH'=>['Sharjah',['Sharjah']]]],
        ];
        foreach ($data as $iso => [$name, $phone, $states]) {
            $country = Country::updateOrCreate(['iso2'=>$iso], ['name'=>$name,'phone_code'=>$phone,'is_active'=>true]);
            foreach ($states as $code => [$stateName, $cities]) {
                $state = State::updateOrCreate(['country_id'=>$country->id,'name'=>$stateName], ['code'=>$code,'is_active'=>true]);
                foreach ($cities as $city) City::updateOrCreate(['state_id'=>$state->id,'name'=>$city], ['is_active'=>true]);
            }
        }
    }

    private function importWorldDataset(string $path): void
    {
        ini_set('memory_limit', '-1');
        $countries = json_decode(gzdecode(file_get_contents($path)), true, 512, JSON_THROW_ON_ERROR);
        $now = now();
        DB::table('countries')->upsert(array_map(fn ($country) => [
            'name'=>$country['name'], 'iso2'=>$country['iso2'], 'phone_code'=>$country['phonecode'] ?: null,
            'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
        ], $countries), ['iso2'], ['name','phone_code','is_active','updated_at']);
        $countryIds = DB::table('countries')->pluck('id','iso2');

        foreach ($countries as $country) {
            $countryId = $countryIds[$country['iso2']] ?? null;
            if (!$countryId || empty($country['states'])) continue;
            $stateRows = array_map(fn ($state) => [
                'country_id'=>$countryId, 'name'=>$state['name'], 'code'=>($state['iso2'] ?? null) ?: null,
                'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ], $country['states']);
            DB::table('states')->upsert($stateRows, ['country_id','name'], ['code','is_active','updated_at']);
            $stateIds = DB::table('states')->where('country_id',$countryId)->pluck('id','name');
            $cityRows = [];
            foreach ($country['states'] as $state) {
                $stateId = $stateIds[$state['name']] ?? null;
                if (!$stateId) continue;
                foreach ($state['cities'] ?? [] as $city) {
                    $name = is_array($city) ? ($city['name'] ?? null) : $city;
                    if (!$name) continue;
                    $cityRows[] = ['state_id'=>$stateId,'name'=>$name,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now];
                    if (count($cityRows) >= 1000) {
                        DB::table('cities')->upsert($cityRows, ['state_id','name'], ['is_active','updated_at']);
                        $cityRows = [];
                    }
                }
            }
            if ($cityRows) DB::table('cities')->upsert($cityRows, ['state_id','name'], ['is_active','updated_at']);
        }
    }
}
