<?php

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'search')]
    public string $search = '';

    public function render()
    {
        $query = User::query()->orderByDesc('created_at');
        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(15)->withQueryString();

        return $this->view(compact('users'));
    }
};
?>

<div>
<p class="mb-4 text-sm text-gray-500">({{ __('Jumlah user aktif: :count', ['count' => $users->total()]) }})</p>

<x-card class="mb-6">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Cari nama/email') }}" class="flex-1 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl text-sm">
        <x-primary-button type="submit">{{ __('Cari') }}</x-primary-button>
    </form>
</x-card>

<x-card>
    <div class="overflow-x-auto -mx-6">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Nama') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Role') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($users as $u)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $u->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $u->email }}</td>
                        <td class="px-6 py-4 text-sm"><span class="px-2 py-1 rounded-full text-xs {{ $u->role==='admin' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">{{ $u->role }}</span></td>
                        <td class="px-6 py-4 text-sm text-right">
                            <form action="{{ route('admin.users.toggle', $u) }}" method="POST" onsubmit="return confirm('{{ __('Ubah role user ini?') }}')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $u->role==='admin' ? 'bg-gray-100 hover:bg-gray-200' : 'bg-amber-100 hover:bg-amber-200 text-amber-800' }}">{{ $u->role==='admin' ? __('Jadikan User') : __('Jadikan Admin') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-card>
</div>
