<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function transactions(Request $request): StreamedResponse
    {
        $query = \App\Models\Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderBy('transaction_date');

        if ($request->filled('start_date')) $query->where('transaction_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('transaction_date', '<=', $request->end_date);
        if ($request->filled('type')) $query->where('type', $request->type);

        $filename = 'transactions-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jenis', 'Akun', 'Tujuan', 'Kategori', 'Nominal', 'Deskripsi', 'Catatan']);
            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->transaction_date->format('Y-m-d'),
                        $r->type,
                        $r->account->name ?? '',
                        $r->destinationAccount->name ?? '',
                        $r->category->name ?? '',
                        $r->amount,
                        $r->description ?? '',
                        $r->notes ?? '',
                    ]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function finance(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->finance(auth()->id(), $start, $end);
        $filename = 'finance-' . $start . '_to_' . $end . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ringkasan Keuangan', 'Periode: ' . $data['start'] . ' - ' . $data['end']]);
            fputcsv($handle, ['Total Pemasukan', $data['totalIncome']]);
            fputcsv($handle, ['Total Pengeluaran', $data['totalExpense']]);
            fputcsv($handle, ['Net Cashflow', $data['netCashflow']]);
            fputcsv($handle, ['Total Saldo', $data['totalBalance']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Pengeluaran per Kategori']);
            fputcsv($handle, ['Kategori', 'Total']);
            foreach ($data['expenseByCategory'] as $cat) {
                fputcsv($handle, [$cat['name'], $cat['total']]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function vehicle(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $filename = 'vehicle-' . $start . '_to_' . $end . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Laporan Kendaraan', 'Periode: ' . $data['start'] . ' - ' . $data['end']]);
            fputcsv($handle, ['Total BBM', $data['fuelStats']['total']]);
            fputcsv($handle, ['Total Servis', $data['serviceStats']['total']]);
            fputcsv($handle, ['Total Biaya', $data['totalVehicleCost']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Per Kendaraan', 'BBM', 'Servis', 'Total']);
            foreach ($data['perVehicle'] as $row) {
                fputcsv($handle, [$row['vehicle']->name, $row['fuel_total'], $row['service_total'], $row['total']]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function fuel(Request $request): StreamedResponse
    {
        $query = \App\Models\FuelRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('fuel_date');
        if ($request->filled('vehicle_id')) $query->where('vehicle_id', $request->vehicle_id);
        if ($request->filled('start_date')) $query->where('fuel_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('fuel_date', '<=', $request->end_date);

        $filename = 'fuel-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Kendaraan', 'Odometer', 'Jenis', 'Liter', 'Harga/L', 'Total', 'SPBU']);
            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->fuel_date->format('Y-m-d'), $r->vehicle->name ?? '', $r->odometer, $r->fuel_type ?? '', $r->liters, $r->price_per_liter, $r->total_cost, $r->station ?? '']);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function service(Request $request): StreamedResponse
    {
        $query = \App\Models\ServiceRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('service_date');
        if ($request->filled('vehicle_id')) $query->where('vehicle_id', $request->vehicle_id);
        if ($request->filled('start_date')) $query->where('service_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('service_date', '<=', $request->end_date);

        $filename = 'service-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Kendaraan', 'Jenis', 'Bengkel', 'Jasa', 'Sparepart', 'Total', 'Odometer']);
            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->service_date->format('Y-m-d'), $r->vehicle->name ?? '', $r->service_type, $r->workshop ?? '', $r->labor_cost, $r->parts_cost, $r->total_cost, $r->odometer]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function financePdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->finance(auth()->id(), $start, $end);
        $html = view('reports.pdf.finance', $data)->render();
        return $this->pdfResponse($html, 'finance-' . $start . '_to_' . $end . '.pdf');
    }

    public function vehiclePdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $html = view('reports.pdf.vehicle', array_merge($data, ['vehicles' => \App\Models\Vehicle::where('user_id', auth()->id())->get()]))->render();
        return $this->pdfResponse($html, 'vehicle-' . $start . '_to_' . $end . '.pdf');
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

    private function pdfResponse(string $html, string $filename)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
