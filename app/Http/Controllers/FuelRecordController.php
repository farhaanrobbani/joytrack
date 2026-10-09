<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuelRecordRequest;
use App\Http\Requests\UpdateFuelRecordRequest;
use App\Models\FuelRecord;
use App\Services\FuelRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelRecordController extends Controller
{
    public function __construct(protected FuelRecordService $service) {}

    public function index(): View
    {
        return view('fuel-records.index');
    }

    public function create(Request $request): View
    {
        $selectedVehicle = $request->query('vehicle_id');

        return view('fuel-records.create', compact('selectedVehicle'));
    }

    public function store(StoreFuelRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['total_cost'] = $request->input('total_cost') ?? ($data['liters'] * $data['price_per_liter']);

        $this->service->create($data);

        return redirect()->route('fuel-records.index')->with('status', __('Catatan BBM berhasil dibuat.'));
    }

    public function show(FuelRecord $fuelRecord): View
    {
        $this->authorize('view', $fuelRecord);
        $fuelRecord->load(['vehicle', 'account', 'transaction', 'attachments']);

        // previous record for this vehicle
        $prev = FuelRecord::where('vehicle_id', $fuelRecord->vehicle_id)
            ->where('id', '!=', $fuelRecord->id)
            ->where('odometer', '<', $fuelRecord->odometer)
            ->orderByDesc('odometer')
            ->first();
        $distance = $prev ? $fuelRecord->odometer - $prev->odometer : null;
        $efficiency = FuelRecord::efficiency($distance, (float) $fuelRecord->liters);
        $costPerKm = FuelRecord::costPerKm($distance, (float) $fuelRecord->total_cost);

        return view('fuel-records.show', compact('fuelRecord', 'distance', 'efficiency', 'costPerKm'));
    }

    public function edit(FuelRecord $fuelRecord): View
    {
        $this->authorize('update', $fuelRecord);

        return view('fuel-records.edit', compact('fuelRecord'));
    }

    public function update(UpdateFuelRecordRequest $request, FuelRecord $fuelRecord): RedirectResponse
    {
        $this->authorize('update', $fuelRecord);
        $data = $request->validated();

        $this->service->update($fuelRecord, $data);

        return redirect()->route('fuel-records.index')->with('status', __('Catatan BBM berhasil diperbarui.'));
    }

    public function destroy(FuelRecord $fuelRecord): RedirectResponse
    {
        $this->authorize('delete', $fuelRecord);
        $this->service->delete($fuelRecord);

        return redirect()->route('fuel-records.index')->with('status', __('Catatan BBM berhasil dihapus.'));
    }
}
