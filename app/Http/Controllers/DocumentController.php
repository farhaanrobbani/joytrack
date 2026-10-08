<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\Vehicle;
use App\Services\ExpiryReminderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(protected ExpiryReminderService $reminders) {}

    public function index(): View
    {
        $documents = Document::with('vehicle')
            ->where('user_id', auth()->id())
            ->orderBy('expiry_date')
            ->paginate(15)
            ->withQueryString();

        $documents->through(fn (Document $document) => [
            'model' => $document,
            'days' => $this->reminders->daysUntilDate($document->expiry_date),
            'status' => $this->reminders->statusForDocument($document),
        ]);

        return view('documents.index', compact('documents'));
    }

    public function create(): View
    {
        return view('documents.create', $this->formData());
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

        return view('documents.edit', ['document' => $document] + $this->formData());
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

    /**
     * @return array{vehicles: Collection, types: array<string, string>}
     */
    protected function formData(): array
    {
        return [
            'vehicles' => Vehicle::where('user_id', auth()->id())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'types' => Document::TYPE_LABELS,
        ];
    }
}
