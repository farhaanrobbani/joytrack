<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenewSubscriptionRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Services\SubscriptionRenewalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionRenewalService $renewalService) {}

    public function index(): View
    {
        return view('subscriptions.index');
    }

    public function create(): View
    {
        return view('subscriptions.create');
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        Subscription::create($data);

        return redirect()->route('subscriptions.index')
            ->with('status', __('Berlangganan berhasil dibuat.'));
    }

    public function edit(Subscription $subscription): View
    {
        $this->authorize('update', $subscription);

        return view('subscriptions.edit', compact('subscription'));
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);

        $subscription->update($request->validated());

        return redirect()->route('subscriptions.index')
            ->with('status', __('Berlangganan berhasil diperbarui.'));
    }

    public function renew(RenewSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);

        $payload = $request->validated();
        $payload['create_transaction'] = $request->boolean('create_transaction');

        $renewal = $this->renewalService->renew($subscription, $payload);

        $target = $request->input('back') === 'reminders'
            ? route('reminders.index')
            : route('subscriptions.index');

        return redirect($target)
            ->with('status', __('Berlangganan berhasil diperpanjang hingga :date.', [
                'date' => $renewal->new_date->format('d M Y'),
            ]));
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $this->authorize('delete', $subscription);

        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('status', __('Berlangganan berhasil dihapus.'));
    }
}
