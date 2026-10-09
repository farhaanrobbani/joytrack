<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public bool $confirmingUserDeletion = false;
    public string $password = '';

    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag();
        $this->password = '';
        $this->confirmingUserDeletion = true;
    }

    public function cancelUserDeletion(): void
    {
        $this->confirmingUserDeletion = false;
        $this->password = '';
        $this->resetErrorBag();
    }

    public function deleteAccount(): void
    {
        $validator = Validator::make(
            ['password' => $this->password],
            ['password' => ['required', 'current_password']]
        );
        $validator->validate();

        $user = auth()->user();

        Auth::logout();

        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button wire:click="confirmUserDeletion">{{ __('Delete Account') }}</x-danger-button>

    @if($confirmingUserDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" x-data x-on:keydown.escape.window="$wire.cancelUserDeletion()">
            <div class="fixed inset-0 transform transition-all" x-on:click="$wire.cancelUserDeletion()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="relative mx-auto mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-2xl">
                <form wire:submit="deleteAccount" class="p-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div class="mt-6">
                        <x-input-label for="delete_password" value="{{ __('Password') }}" class="sr-only" />

                        <x-text-input
                            id="delete_password"
                            wire:model="password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="{{ __('Password') }}"
                        />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button wire:click="cancelUserDeletion">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button type="submit" class="ms-3">
                            {{ __('Delete Account') }}
                        </x-danger-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
