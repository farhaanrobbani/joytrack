<?php

namespace App\Http\Controllers;

use App\Http\Traits\ResolvesReportPreset;
use App\Models\FuelRecord;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Services\ReportService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use ResolvesReportPreset;

    public function __construct(protected ReportService $reportService) {}

    public function transactions(Request $request): StreamedResponse
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderBy('transaction_date');

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $filename = 'transactions-'.now()->format('Ymd-His').'.csv';

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
        $filename = 'finance-'.$start.'_to_'.$end.'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ringkasan Keuangan', 'Periode: '.$data['start'].' - '.$data['end']]);
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
        $filename = 'vehicle-'.$start.'_to_'.$end.'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Laporan Kendaraan', 'Periode: '.$data['start'].' - '.$data['end']]);
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
        $query = FuelRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('fuel_date');
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('start_date')) {
            $query->where('fuel_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('fuel_date', '<=', $request->end_date);
        }

        $filename = 'fuel-'.now()->format('Ymd-His').'.csv';

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
        $query = ServiceRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('service_date');
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('start_date')) {
            $query->where('service_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('service_date', '<=', $request->end_date);
        }

        $filename = 'service-'.now()->format('Ymd-His').'.csv';

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

    public function transactionsExcel(Request $request): StreamedResponse
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderBy('transaction_date')->orderBy('id');

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return $this->xlsxDownload('transactions-'.now()->format('Ymd-His').'.xlsx', function ($writer) use ($query) {
            $writer->addRow(Row::fromValues(['Tanggal', 'Jenis', 'Akun', 'Tujuan', 'Kategori', 'Nominal', 'Deskripsi', 'Catatan']));
            $this->eachChunk($query, function ($rows) use ($writer) {
                foreach ($rows as $r) {
                    $writer->addRow(Row::fromValues([
                        $r->transaction_date->format('Y-m-d'),
                        $r->type,
                        $r->account->name ?? '',
                        $r->destinationAccount->name ?? '',
                        $r->category->name ?? '',
                        (float) $r->amount,
                        $r->description ?? '',
                        $r->notes ?? '',
                    ]));
                }
            });
        });
    }

    public function financeExcel(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->finance(auth()->id(), $start, $end);

        return $this->xlsxDownload('finance-'.$start.'_to_'.$end.'.xlsx', function ($writer) use ($data) {
            $writer->addRow(Row::fromValues(['Ringkasan Keuangan', 'Periode: '.$data['start'].' - '.$data['end']]));
            $writer->addRow(Row::fromValues(['Total Pemasukan', (float) $data['totalIncome']]));
            $writer->addRow(Row::fromValues(['Total Pengeluaran', (float) $data['totalExpense']]));
            $writer->addRow(Row::fromValues(['Net Cashflow', (float) $data['netCashflow']]));
            $writer->addRow(Row::fromValues(['Total Saldo', (float) $data['totalBalance']]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues(['Pengeluaran per Kategori']));
            $writer->addRow(Row::fromValues(['Kategori', 'Total']));
            foreach ($data['expenseByCategory'] as $cat) {
                $writer->addRow(Row::fromValues([$cat['name'], (float) $cat['total']]));
            }
        });
    }

    public function vehicleExcel(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);

        return $this->xlsxDownload('vehicle-'.$start.'_to_'.$end.'.xlsx', function ($writer) use ($data) {
            $writer->addRow(Row::fromValues(['Laporan Kendaraan', 'Periode: '.$data['start'].' - '.$data['end']]));
            $writer->addRow(Row::fromValues(['Total BBM', (float) $data['fuelStats']['total']]));
            $writer->addRow(Row::fromValues(['Total Servis', (float) $data['serviceStats']['total']]));
            $writer->addRow(Row::fromValues(['Total Biaya', (float) $data['totalVehicleCost']]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues(['Per Kendaraan', 'BBM', 'Servis', 'Total']));
            foreach ($data['perVehicle'] as $row) {
                $writer->addRow(Row::fromValues([$row['vehicle']->name, (float) $row['fuel_total'], (float) $row['service_total'], (float) $row['total']]));
            }
        });
    }

    public function fuelExcel(Request $request): StreamedResponse
    {
        $query = FuelRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('fuel_date')->orderBy('id');
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('start_date')) {
            $query->where('fuel_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('fuel_date', '<=', $request->end_date);
        }

        return $this->xlsxDownload('fuel-'.now()->format('Ymd-His').'.xlsx', function ($writer) use ($query) {
            $writer->addRow(Row::fromValues(['Tanggal', 'Kendaraan', 'Odometer', 'Jenis', 'Liter', 'Harga/L', 'Total', 'SPBU']));
            $this->eachChunk($query, function ($rows) use ($writer) {
                foreach ($rows as $r) {
                    $writer->addRow(Row::fromValues([$r->fuel_date->format('Y-m-d'), $r->vehicle->name ?? '', $r->odometer, $r->fuel_type ?? '', (float) $r->liters, (float) $r->price_per_liter, (float) $r->total_cost, $r->station ?? '']));
                }
            });
        });
    }

    public function serviceExcel(Request $request): StreamedResponse
    {
        $query = ServiceRecord::where('user_id', auth()->id())->with('vehicle')->orderBy('service_date')->orderBy('id');
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('start_date')) {
            $query->where('service_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('service_date', '<=', $request->end_date);
        }

        return $this->xlsxDownload('service-'.now()->format('Ymd-His').'.xlsx', function ($writer) use ($query) {
            $writer->addRow(Row::fromValues(['Tanggal', 'Kendaraan', 'Jenis', 'Bengkel', 'Jasa', 'Sparepart', 'Total', 'Odometer']));
            $this->eachChunk($query, function ($rows) use ($writer) {
                foreach ($rows as $r) {
                    $writer->addRow(Row::fromValues([$r->service_date->format('Y-m-d'), $r->vehicle->name ?? '', $r->service_type, $r->workshop ?? '', (float) $r->labor_cost, (float) $r->parts_cost, (float) $r->total_cost, $r->odometer]));
                }
            });
        });
    }

    public function financePdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->finance(auth()->id(), $start, $end);
        $html = view('reports.pdf.finance', $data)->render();

        return $this->pdfResponse($html, 'finance-'.$start.'_to_'.$end.'.pdf');
    }

    public function vehiclePdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->vehicle(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $html = view('reports.pdf.vehicle', array_merge($data, ['vehicles' => Vehicle::where('user_id', auth()->id())->get()]))->render();

        return $this->pdfResponse($html, 'vehicle-'.$start.'_to_'.$end.'.pdf');
    }

    public function transactionsPdf(Request $request)
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'destinationAccount', 'category'])
            ->orderBy('transaction_date')->orderBy('id');

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $html = view('reports.pdf.transactions', [
            'transactions' => $query->get(),
            'start' => $request->start_date,
            'end' => $request->end_date,
        ])->render();

        return $this->pdfResponse($html, 'transactions-'.now()->format('Ymd-His').'.pdf');
    }

    public function fuelPdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->fuel(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $html = view('reports.pdf.fuel', $data)->render();

        return $this->pdfResponse($html, 'fuel-'.$start.'_to_'.$end.'.pdf');
    }

    public function servicePdf(Request $request)
    {
        [$start, $end] = $this->resolvePreset($request);
        $data = $this->reportService->service(auth()->id(), $start, $end, $request->vehicle_id ? (int) $request->vehicle_id : null);
        $html = view('reports.pdf.service', $data)->render();

        return $this->pdfResponse($html, 'service-'.$start.'_to_'.$end.'.pdf');
    }

    private function xlsxDownload(string $filename, callable $fill): StreamedResponse
    {
        return response()->streamDownload(function () use ($fill) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');
            $fill($writer);
            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function eachChunk($query, callable $callback, int $perPage = 500): void
    {
        $page = 1;
        do {
            $rows = (clone $query)->forPage($page, $perPage)->get();
            if ($rows->isNotEmpty()) {
                $callback($rows);
            }
            $page++;
        } while ($rows->count() === $perPage);
    }

    private function pdfResponse(string $html, string $filename)
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
