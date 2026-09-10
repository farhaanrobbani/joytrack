<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRecordRequest;
use App\Http\Requests\UpdateServiceRecordRequest;
use App\Models\Account;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use App\Services\ServiceRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRecordController extends Controller
{
    public function __construct(protected ServiceRecordService $service) {}

    public function index(Request $request): View
    {
        $query = ServiceRecord::where('user_id', auth()->id())
            ->with(['vehicle', 'account', 'transaction'])
            ->orderByDesc('service_date')
            ->orderByDesc('id');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('service_type')) {
            $query->where('service_type', 'like', '%' . $request->service_type . '%');
        }
        if ($request->filled('workshop')) {
            $query->where('workshop', 'like', '%' . $request->workshop . '%');
        }
        if ($request->filled('date_from')) {
            $query->where('service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('service_date', '<=', $request->date_to);
        }

        $records = $query->paginate(15)->withQueryString();
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();

        // reminder stats: count upcoming via date/km
        $upcoming = app(\App\Services\ServiceReminderService::class)->getReminders(auth()->id())->count();

        return view('service-records.index', compact('records', 'vehicles', 'upcoming'));
    }

    public function create(Request $request): View
    {
        $vehicles = Vehicle::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $accounts = Account::where('user_id', auth()->id())->active()->orderBy('name')->get();
        $selectedVehicle = $request->query('vehicle_id');
        $serviceTypes = ['Servis rutin','Ganti oli','Ganti filter oli','Ganti filter udara','Ganti busi','Ganti kampas rem','Ganti ban','Servis CVT','Servis mesin','Servis AC','Kelistrikan','Perbaikan','Sparepart','Lainnya'];

        return view('service-records.create', compact('vehicles', 'accounts', 'selectedVehicle', 'serviceTypes'));
    }

    public function store(StoreServiceRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $this->service->create($data);

        return redirect()->route('service-records.index')->with('status', __('Catatan servis berhasil dibuat.'));
    }

    public function show(ServiceRecord $serviceRecord): View
    {
        $this->authorize('view', $serviceRecord);
        $serviceRecord->load(['vehicle', 'account', 'transaction', 'attachments']);

        return view('service-records.show', compact('serviceRecord'));
    }

    public function edit(ServiceRecord $serviceRecord): View
    {
        $this->authorize('update', $serviceRecord);
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        $serviceTypes = ['Servis rutin','Ganti oli','Ganti filter oli','Ganti filter udara','Ganti busi','Ganti kampas rem','Ganti ban','Servis CVT','Servis mesin','Servis AC','Kelistrikan','Perbaikan','Sparepart','Lainnya'];

        return view('service-records.edit', compact('serviceRecord', 'vehicles', 'accounts', 'serviceTypes'));
    }

    public function update(UpdateServiceRecordRequest $request, ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->authorize('update', $serviceRecord);
        $this->service->update($serviceRecord, $request->validated());

        return redirect()->route('service-records.index')->with('status', __('Catatan servis berhasil diperbarui.'));
    }

    public function destroy(ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->authorize('delete', $serviceRecord);
        $this->service->delete($serviceRecord);

        return redirect()->route('service-records.index')->with('status', __('Catatan servis berhasil dihapus.'));
    }
}
