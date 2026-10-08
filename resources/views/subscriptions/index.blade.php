<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Berlangganan') }}</h2>
            <a href="{{ route('subscriptions.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">{{ __('Tambah Berlangganan') }}</a>
        </div>
    </x-slot>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        @if($subscriptions->isEmpty())
            <div class="p-8 text-center text-gray-500">{{ __('Belum ada berlangganan') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Nominal') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Perpanjangan Berikutnya') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($subscriptions as $row)
                            @php($s = $row['model'])
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $s->name }}
                                    @if(!$s->is_active)
                                        <span class="ms-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ __('Nonaktif') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium">{{ $s->amount !== null ? 'Rp '.number_format((float) $s->amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $s->next_renewal_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($row['status'] === 'overdue')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ __('Terlambat :days hari', ['days' => abs($row['days'])]) }}</span>
                                    @elseif($row['days'] === 0)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Jatuh tempo hari ini') }}</span>
                                    @elseif($row['status'] === 'due_soon')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">{{ __('Sisa :days hari', ['days' => $row['days']]) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('subscriptions.edit', $s) }}" class="text-blue-600 hover:text-blue-700 mr-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('subscriptions.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus berlangganan ini?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</x-app-layout>
