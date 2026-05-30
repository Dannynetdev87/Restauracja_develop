<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZoneController extends Controller
{
    public function store(Request $request)
    {
        Zone::create($this->validatedZone($request));

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Strefa została dodana.');
    }

    public function update(Request $request, Zone $zone)
    {
        $zone->update($this->validatedZone($request, $zone));

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Strefa została zaktualizowana.');
    }

    public function toggle(Zone $zone)
    {
        $zone->update([
            'is_active' => ! $zone->is_active,
        ]);

        return redirect()
            ->route('manager.tables.index')
            ->with('success', $zone->is_active ? 'Strefa została aktywowana.' : 'Strefa została wyłączona.');
    }

    public function destroy(Zone $zone)
    {
        RestaurantTable::query()
            ->where('zone_id', $zone->id)
            ->update(['zone_id' => null]);

        $zone->delete();

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Strefa została usunięta, a stoliki przeniesiono poza strefę.');
    }

    private function validatedZone(Request $request, ?Zone $zone = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('zones', 'name')->ignore($zone),
            ],
            'assigned_waiter_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', User::ROLE_WAITER)
                    ->where('is_active', true),
            ],
        ]);
    }
}
