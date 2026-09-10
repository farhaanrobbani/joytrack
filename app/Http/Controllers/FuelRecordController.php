<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuelRecordRequest;
use App\Http\Requests\UpdateFuelRecordRequest;
use App\Models\Account;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use App\Services\FuelRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelRecordController extends Controller
{
    public function __construct(protected FuelRecordService $service) {}

    public function index(Request $request): View
    {
        $query = FuelRecord::where('user_id', auth()->id())
            ->with(['vehicle', 'account', 'transaction'])
            ->orderByDesc('fuel_date')
            ->orderByDesc('id');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }
        if ($request->filled('date_from')) {
            $query->where('fuel_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('fuel_date', '<=', $request->date_to);
        }

        $records = $query->paginate(15)->withQueryString();
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();
        $stats = $this->service->stats(auth()->id(), $request->vehicle_id ? (int) $request->vehicle_id : null);

        // per-record km/l calculation
        $records->getCollection()->transform(function ($r) {
            static $prev = null;
            // need ordered by odometer asc for distance calc, but we display desc. So compute separately for stats above.
            return $r;
        });

        return view('fuel-records.index', compact('records', 'vehicles', 'stats'));
    }

    public function create(Request $request): View
    {
        $vehicles = Vehicle::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $accounts = Account::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $selectedVehicle = $request->query('vehicle_id');

        return view('fuel-records.create', compact('vehicles', 'accounts', 'selectedVehicle'));
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
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();

        return view('fuel-records.edit', compact('fuelRecord', 'vehicles', 'accounts'));
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
