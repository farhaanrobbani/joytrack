@props(['model', 'type'])

@php
    $attachments = $model->attachments ?? collect();
@endphp

<div class="bg-white shadow sm:rounded-lg p-6 mt-6">
    <h3 class="font-semibold text-gray-800 mb-3">{{ __('Lampiran') }} <span class="text-sm font-normal text-gray-500">({{ $attachments->count() }})</span></h3>

    @if($attachments->isNotEmpty())
        <ul class="divide-y divide-gray-100">
            @foreach($attachments as $att)
                <li class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        @if($att->isImage())
                            <img src="{{ $att->url() }}" alt="{{ $att->file_name }}" class="w-12 h-12 object-cover rounded border">
                        @else
                            <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded border text-xs text-gray-500">{{ pathinfo($att->file_name, PATHINFO_EXTENSION) }}</div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900 truncate max-w-[200px]">{{ $att->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($att->file_size/1024,1) }} KB • {{ $att->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('attachments.show', $att) }}" class="text-sm text-emerald-600 hover:underline">{{ __('Lihat') }}</a>
                        <form action="{{ route('attachments.destroy', $att) }}" method="POST" onsubmit="return confirm('{{ __('Yakin hapus file?') }}')">@csrf @method('DELETE')<button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Hapus') }}</button></form>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-gray-400">{{ __('Belum ada lampiran') }}</p>
    @endif

    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex gap-2 items-end">
        @csrf
        <input type="hidden" name="attachable_type" value="{{ $type }}">
        <input type="hidden" name="attachable_id" value="{{ $model->id }}">
        <div class="flex-1">
            <x-input-label for="file-{{ $type }}-{{ $model->id }}" :value="__('Upload File (jpg, png, pdf, max 5MB)')" />
            <input id="file-{{ $type }}-{{ $model->id }}" name="file" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
        </div>
        <x-primary-button type="submit" class="h-10">{{ __('Upload') }}</x-primary-button>
    </form>
    @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
