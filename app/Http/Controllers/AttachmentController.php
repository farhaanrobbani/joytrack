<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'attachable_type' => ['required', 'in:transaction,fuel_record,service_record'],
            'attachable_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $map = [
            'transaction' => \App\Models\Transaction::class,
            'fuel_record' => \App\Models\FuelRecord::class,
            'service_record' => \App\Models\ServiceRecord::class,
        ];

        $type = $request->input('attachable_type');
        $modelClass = $map[$type];
        $model = $modelClass::findOrFail($request->input('attachable_id'));
        $this->authorize('view', $model);

        $file = $request->file('file');
        $path = $file->store('attachments/' . $type . '/' . $model->id, 'public');

        Attachment::create([
            'user_id' => auth()->id(),
            'attachable_type' => $modelClass,
            'attachable_id' => $model->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', __('File berhasil diunggah.'));
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', __('File berhasil dihapus.'));
    }

    public function show(Attachment $attachment)
    {
        $this->authorize('view', $attachment);
        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
