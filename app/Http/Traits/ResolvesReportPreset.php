<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;

trait ResolvesReportPreset
{
    protected function validateReportParams(Request $request, array $extra = []): void
    {
        $request->validate(array_merge([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'preset' => ['nullable', 'in:today,week,month,year,custom'],
        ], $extra));
    }

    protected function resolvePreset(Request $request): array
    {
        $preset = $request->input('preset');
        $tz = 'Asia/Jakarta';
        $now = Carbon::now($tz);

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
