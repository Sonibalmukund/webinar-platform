<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email,'.$request->user()->id],
            'mobile' => ['nullable', 'string', 'max:30'], 'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'], 'bio' => ['nullable', 'string', 'max:2000'],
            'country_id' => ['nullable', 'exists:countries,id'], 'state_id' => ['nullable', 'exists:states,id'], 'city_id' => ['nullable', 'exists:cities,id'],
        ]);
        if (! empty($data['country_id']) && ! empty($data['state_id'])) {
            $state = State::whereKey($data['state_id'])->where('country_id', $data['country_id'])->firstOrFail();
            if (! empty($data['city_id'])) {
                City::whereKey($data['city_id'])->where('state_id', $state->id)->firstOrFail();
            }
        }
        $request->user()->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function password(): View
    {
        return view('pages.admin.password');
    }

    public function adminProfile(Request $request): View
    {
        return view('pages.admin.profile', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password changed successfully.');
    }
}
