<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected ReportService $service) {}

    public function finance(Request $request): View
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'preset' => ['nullable', 'in:today,week,month,year,custom'],
        ]);

        [$start, $end] = $this->resolvePreset($request);

        $data = $this->service->finance(auth()->id(), $start, $end);
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get(); // for sidebar context if needed

        return view('reports.finance', array_merge($data, ['preset' => $request->input('preset', 'month')]));
    }

    public function vehicle(Request $request): View
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'preset' => ['nullable', 'in:today,week,month,year,custom'],
        ]);

        [$start, $end] = $this->resolvePreset($request);

        $data = $this->service->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $vehicles = Vehicle::where('user_id', auth()->id())->orderBy('name')->get();

        return view('reports.vehicle', array_merge($data, ['vehicles' => $vehicles, 'preset' => $request->input('preset', 'month'), 'selectedVehicle' => $request->vehicle_id]));
    }

    private function resolvePreset(Request $request): array
    {
        $preset = $request->input('preset');
        $tz = 'Asia/Jakarta';
        $now = \Carbon\Carbon::now($tz);

        return match ($preset) {
            'today' => [$now->toDateString(), $now->toDateString()],
            'week' => [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'month' => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'year' => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default => [
                $request->input('start_date') ?? $now->copy()->startOfMonth()->toDateString(),
                $request->input('end_date') ?? $now->copy()->endOfMonth()->toDateString(),
            ],
        };
    }
}
