<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user=$request->user()->load(['country','state','city','signupAnswers.field']);
        return view('pages.user.profile', ['user'=>$user,'countries'=>Country::where('is_active',true)->orderBy('name')->get(),'states'=>State::where('country_id',$user->country_id)->where('is_active',true)->orderBy('name')->get(),'cities'=>City::where('state_id',$user->state_id)->where('is_active',true)->orderBy('name')->get()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'], 'email'=>['required','email','unique:users,email,'.$request->user()->id],
            'mobile'=>['nullable','string','max:30'], 'job_title'=>['nullable','string','max:255'],
            'company'=>['nullable','string','max:255'], 'bio'=>['nullable','string','max:2000'],
            'country_id'=>['nullable','exists:countries,id'],'state_id'=>['nullable','exists:states,id'],'city_id'=>['nullable','exists:cities,id'],
        ]);
        if(!empty($data['country_id']) && !empty($data['state_id'])) {
            $state=State::whereKey($data['state_id'])->where('country_id',$data['country_id'])->firstOrFail();
            if(!empty($data['city_id'])) City::whereKey($data['city_id'])->where('state_id',$state->id)->firstOrFail();
        }
        $request->user()->update($data);
        return back()->with('status','Profile updated successfully.');
    }

    public function password(): View { return view('pages.admin.password'); }
    public function adminProfile(Request $request): View { return view('pages.admin.profile',['user'=>$request->user()]); }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data=$request->validate(['current_password'=>['required','current_password'],'password'=>['required','string','min:8','confirmed']]);
        $request->user()->update(['password'=>Hash::make($data['password'])]);
        return back()->with('status','Password changed successfully.');
    }
}
