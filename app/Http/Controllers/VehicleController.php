<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::where('user_id', auth()->id())
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        Vehicle::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('vehicles.index')->with('status', __('Kendaraan berhasil dibuat.'));
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorize('view', $vehicle);

        $fuelStats = \App\Models\FuelRecord::where('vehicle_id', $vehicle->id)->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_cost),0) as cost')->first();
        $serviceStats = \Illuminate\Support\Facades\Schema::hasTable('service_records')
            ? \App\Models\ServiceRecord::where('vehicle_id', $vehicle->id)->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_cost),0) as cost')->first()
            : (object) ['cnt' => 0, 'cost' => 0];

        $stats = [
            'total_fuel_cost' => (float) ($fuelStats->cost ?? 0),
            'total_service_cost' => (float) ($serviceStats->cost ?? 0),
            'fuel_count' => (int) ($fuelStats->cnt ?? 0),
            'service_count' => (int) ($serviceStats->cnt ?? 0),
        ];

        $recentFuels = \App\Models\FuelRecord::where('vehicle_id', $vehicle->id)->orderByDesc('fuel_date')->limit(5)->get();
        $recentServices = \App\Models\ServiceRecord::where('vehicle_id', $vehicle->id)->orderByDesc('service_date')->limit(5)->get();

        return view('vehicles.show', compact('vehicle', 'stats', 'recentFuels', 'recentServices'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('update', $vehicle);
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')->with('status', __('Kendaraan berhasil diperbarui.'));
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('status', __('Kendaraan berhasil dihapus.'));
    }
}
