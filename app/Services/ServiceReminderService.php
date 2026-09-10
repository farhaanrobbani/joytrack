<?php

namespace App\Services;

use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ServiceReminderService
{
    public const DATE_WARNING_DAYS = 7;
    public const ODOMETER_WARNING_KM = 500;

    /**
     * @return Collection<int, array{vehicle: Vehicle, record: ServiceRecord, status: string, messages: array<string>}>
     */
    public function getReminders(int $userId): Collection
    {
        $vehicles = Vehicle::where('user_id', $userId)->where('is_active', true)->get();
        $reminders = collect();

        foreach ($vehicles as $vehicle) {
            $record = ServiceRecord::where('vehicle_id', $vehicle->id)
                ->where(function ($q) {
                    $q->whereNotNull('next_service_date')->orWhereNotNull('next_service_odometer');
                })
                ->orderByDesc('service_date')
                ->orderByDesc('id')
                ->first();

            if (! $record) {
                continue;
            }

            $status = $this->getStatus($vehicle, $record);
            if ($status !== 'ok') {
                $reminders->push([
                    'vehicle' => $vehicle,
                    'record' => $record,
                    'status' => $status, // overdue | due_soon | ok
                    'messages' => $this->getMessages($vehicle, $record, $status),
                ]);
            }
        }

        // overdue first, then due_soon
        return $reminders->sortBy(fn ($r) => $r['status'] === 'overdue' ? 0 : 1)->values();
    }

    public function getStatus(Vehicle $vehicle, ServiceRecord $record): string
    {
        $now = Carbon::now('Asia/Jakarta')->startOfDay();
        $odometer = $vehicle->current_odometer;

        $dateOverdue = $record->next_service_date && $record->next_service_date->lt($now);
        $kmOverdue = $record->next_service_odometer && $odometer >= $record->next_service_odometer;

        if ($dateOverdue || $kmOverdue) {
            return 'overdue';
        }

        $dateSoon = $record->next_service_date && $record->next_service_date->diffInDays($now, false) >= -self::DATE_WARNING_DAYS && $record->next_service_date->gte($now);
        // diff negative when future; we want within 7 days ahead
        if ($record->next_service_date) {
            $daysUntil = $now->diffInDays($record->next_service_date, false);
            if ($daysUntil >= 0 && $daysUntil <= self::DATE_WARNING_DAYS) {
                return 'due_soon';
            }
        }
        $kmSoon = $record->next_service_odometer && ($record->next_service_odometer - $odometer) <= self::ODOMETER_WARNING_KM && ($record->next_service_odometer - $odometer) > 0;

        if ($dateSoon || $kmSoon) {
            return 'due_soon';
        }

        return 'ok';
    }

    private function getMessages(Vehicle $vehicle, ServiceRecord $record, string $status): array
    {
        $messages = [];
        $now = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($record->next_service_date) {
            if ($record->next_service_date->lt($now)) {
                $messages[] = __('Servis jatuh tempo pada :date (terlambat :days hari)', [
                    'date' => $record->next_service_date->format('d M Y'),
                    'days' => $now->diffInDays($record->next_service_date),
                ]);
            } elseif ($now->diffInDays($record->next_service_date, false) <= self::DATE_WARNING_DAYS) {
                $messages[] = __('Servis akan jatuh tempo pada :date', ['date' => $record->next_service_date->format('d M Y')]);
            }
        }

        if ($record->next_service_odometer) {
            $diff = $record->next_service_odometer - $vehicle->current_odometer;
            if ($diff <= 0) {
                $messages[] = __('Odometer telah melewati :km km (saat ini :current km)', [
                    'km' => number_format($record->next_service_odometer, 0, ',', '.'),
                    'current' => number_format($vehicle->current_odometer, 0, ',', '.'),
                ]);
            } elseif ($diff <= self::ODOMETER_WARNING_KM) {
                $messages[] = __('Servis pada :km km (sisa :diff km)', [
                    'km' => number_format($record->next_service_odometer, 0, ',', '.'),
                    'diff' => number_format($diff, 0, ',', '.'),
                ]);
            }
        }

        if ($status === 'overdue' && empty($messages)) {
            $messages[] = __('Servis kendaraan akan segera jatuh tempo.');
        } elseif ($status === 'due_soon' && empty($messages)) {
            $messages[] = __('Servis kendaraan akan segera jatuh tempo.');
        }

        return $messages;
    }
}
