<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class GeographyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.settings.manage');

        $countries = Country::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->withCount('states')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('backend.admin.system.geography.index', [
            'countries' => $countries,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function toggleCountry(Country $country)
    {
        $this->authorize('platform.settings.manage');

        $country->update(['is_active' => ! $country->is_active]);

        return back()->with('success', 'Country updated.');
    }

    public function states(Country $country)
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.geography.states', [
            'country' => $country,
            'states' => $country->states()->withCount('cities')->orderBy('name')->paginate(30),
        ]);
    }

    public function toggleState(State $state)
    {
        $this->authorize('platform.settings.manage');

        $state->update(['is_active' => ! $state->is_active]);

        return back()->with('success', 'State updated.');
    }

    public function cities(State $state)
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.geography.cities', [
            'state' => $state,
            'cities' => $state->cities()->orderBy('name')->paginate(30),
        ]);
    }

    public function toggleCity(City $city)
    {
        $this->authorize('platform.settings.manage');

        $city->update(['is_active' => ! $city->is_active]);

        return back()->with('success', 'City updated.');
    }
}
