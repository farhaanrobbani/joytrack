<?php

use App\Models\Document;
use App\Services\ExpiryReminderService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function render()
    {
        $documents = Document::with('vehicle')
            ->where('user_id', auth()->id())
            ->orderBy('expiry_date')
            ->paginate(15);

        $reminders = app(ExpiryReminderService::class);
        $documents->through(fn (Document $document) => [
            'model' => $document,
            'days' => $reminders->daysUntilDate($document->expiry_date),
            'status' => $reminders->statusForDocument($document),
        ]);

        return $this->view(['documents' => $documents]);
    }
};
?>

<div>
    <div class="bg-white shadow sm:rounded-lg overflow-hidden" wire:loading.class="opacity-50">
        @if($documents->isEmpty())
            <div class="p-8 text-center text-gray-500">{{ __('Belum ada dokumen') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jenis') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kendaraan') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Kadaluarsa') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($documents as $row)
                            @php($d = $row['model'])
                            <tr wire:key="doc-{{ $d->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $d->name }}
                                    @if(!$d->is_active)
                                        <span class="ms-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ __('Nonaktif') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $d->type_label }}</td>
                                <td class="px-4 py-3 text-sm">{{ $d->vehicle?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $d->expiry_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($row['status'] === 'overdue')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ __('Terlambat :days hari', ['days' => abs($row['days'])]) }}</span>
                                    @elseif($row['days'] === 0)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Kadaluarsa hari ini') }}</span>
                                    @elseif($row['status'] === 'due_soon')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('documents.edit', $d) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('documents.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus dokumen ini?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $documents->links() }}</div>
        @endif
    </div>
</div>
