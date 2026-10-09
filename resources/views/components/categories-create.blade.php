<?php

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $type = 'expense';
    public string $icon = '';
    public bool $is_active = true;

    public function save(): void
    {
        $data = $this->formData();

        $validator = Validator::make($data, StoreCategoryRequest::categoryRules(), StoreCategoryRequest::categoryMessages());
        $validator->validate();

        Category::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        session()->flash('status', __('Kategori berhasil dibuat.'));
        $this->redirect(route('categories.index'));
    }

    private function formData(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'icon' => $this->icon !== '' ? $this->icon : null,
            'is_active' => $this->is_active,
        ];
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<div>
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nama Kategori')" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="type" :value="__('Jenis')" />
                    <select id="type" wire:model="type" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                        <option value="income">{{ __('Pemasukan') }}</option>
                        <option value="expense">{{ __('Pengeluaran') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="icon" :value="__('Icon (opsional)')" />
                    <x-text-input id="icon" wire:model="icon" type="text" class="mt-1 block w-full" placeholder="mis. tag, wallet" />
                    <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                </div>
                <div class="flex items-center">
                    <input id="is_active" wire:model="is_active" type="checkbox" class="rounded-sm border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200">{{ __('Batal') }}</a>
                    <x-primary-button type="submit" class="ms-auto">{{ __('Simpan') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
