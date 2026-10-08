<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenewSubscriptionRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Account;
use App\Models\Subscription;
use App\Services\ExpiryReminderService;
use App\Services\SubscriptionRenewalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected ExpiryReminderService $reminders,
        protected SubscriptionRenewalService $renewalService,
    ) {}

    public function index(): View
    {
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->orderBy('next_renewal_date')
            ->paginate(15)
            ->withQueryString();

        $subscriptions->through(fn (Subscription $subscription) => [
            'model' => $subscription,
            'days' => $this->reminders->daysUntilDate($subscription->next_renewal_date),
            'status' => $this->reminders->statusForSubscription($subscription),
            'renew_preview' => $this->renewalService->previewNextDate($subscription),
        ]);

        $accounts = Account::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $renewPayload = $subscriptions->getCollection()->mapWithKeys(fn (array $row) => [
            $row['model']->id => [
                'id' => $row['model']->id,
                'url' => route('subscriptions.renew', $row['model']),
                'name' => $row['model']->name,
                'cycle' => $row['model']->cycle_label,
                'next' => $row['model']->next_renewal_date->format('d M Y'),
                'preview' => $row['renew_preview']->format('d M Y'),
                'amount' => $row['model']->amount,
            ],
        ]);

        return view('subscriptions.index', compact('subscriptions', 'accounts', 'renewPayload'));
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

        $subscription->load('renewals');

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

        return redirect()->route('subscriptions.index')
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
