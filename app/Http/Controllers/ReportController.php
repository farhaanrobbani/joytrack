<?php

namespace App\Http\Controllers;

use App\Http\Traits\ResolvesReportPreset;
use App\Models\Vehicle;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    use ResolvesReportPreset;

    public function __construct(protected ReportService $service) {}

    public function finance(Request $request): View
    {
        $this->validateReportParams($request);

        [$start, $end] = $this->resolvePreset($request);

        $data = $this->service->finance(auth()->id(), $start, $end);

        return view('reports.finance', array_merge($data, ['preset' => $request->input('preset', 'month')]));
    }

    public function vehicle(Request $request): View
    {
        $this->validateReportParams($request, [
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
        ]);

        [$start, $end] = $this->resolvePreset($request);

        $data = $this->service->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();

        return view('reports.vehicle', array_merge($data, ['vehicles' => $vehicles, 'preset' => $request->input('preset', 'month'), 'selectedVehicle' => $request->vehicle_id]));
    }
}
