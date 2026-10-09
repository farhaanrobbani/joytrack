<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        return view('documents.index');
    }

    public function create(): View
    {
        return view('documents.create');
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['vehicle_id'] = $request->filled('vehicle_id') ? $request->vehicle_id : null;

        Document::create($data);

        return redirect()->route('documents.index')
            ->with('status', __('Dokumen berhasil dibuat.'));
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);

        return view('documents.edit', compact('document'));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $data = $request->validated();
        $data['vehicle_id'] = $request->filled('vehicle_id') ? $request->vehicle_id : null;

        $document->update($data);

        return redirect()->route('documents.index')
            ->with('status', __('Dokumen berhasil diperbarui.'));
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return redirect()->route('documents.index')
            ->with('status', __('Dokumen berhasil dihapus.'));
    }
}
