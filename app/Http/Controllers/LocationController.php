<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function states(Request $request): JsonResponse
    {
        return response()->json(State::where('country_id', $request->integer('country_id'))->where('is_active', true)->orderBy('name')->get(['id','name']));
    }
    public function cities(Request $request): JsonResponse
    {
        return response()->json(City::where('state_id', $request->integer('state_id'))->where('is_active', true)->orderBy('name')->get(['id','name']));
    }
}
