<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRecordRequest;
use App\Http\Requests\UpdateServiceRecordRequest;
use App\Models\ServiceRecord;
use App\Services\ServiceRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRecordController extends Controller
{
    public function __construct(protected ServiceRecordService $service) {}

    public function index(): View
    {
        return view('service-records.index');
    }

    public function create(Request $request): View
    {
        $selectedVehicle = $request->query('vehicle_id');

        return view('service-records.create', compact('selectedVehicle'));
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

        return view('service-records.edit', compact('serviceRecord'));
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
