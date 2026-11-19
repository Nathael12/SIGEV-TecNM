<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Implementación anterior: new class extends Component {
new #[Layout('components.layouts.app')] class extends Component {
    public string $name = '';
    public string $nombre_usuario = '';
    public string $apellido_paterno = '';
    public string $apellido_materno = '';
    public string $email = '';
    public string $telefono_usuario = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->nombre_usuario = Auth::user()->nombre_usuario;
        $this->apellido_paterno = Auth::user()->apellido_paterno;
        $this->apellido_materno = Auth::user()->apellido_materno;
        $this->email = Auth::user()->email;
        $this->telefono_usuario = Auth::user()->telefono_usuario;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'nombre_usuario' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'telefono_usuario' => ['required', 'digits:10'],
        ]);

        $user->fill($validated);
        $user->correo_usuario = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Profile" subheading="Update your personal information">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <flux:input wire:model="name" label="Nombre de usuario" type="text" name="name" required autofocus autocomplete="username" />
                    <flux:error name="name" />
                </div>

                <div>
                    <flux:input wire:model="nombre_usuario" label="Nombre" type="text" name="nombre_usuario" required autocomplete="given-name" />
                    <flux:error name="nombre_usuario" />
                </div>

                <div>
                    <flux:input wire:model="apellido_paterno" label="Apellido paterno" type="text" name="apellido_paterno" required autocomplete="family-name" />
                    <flux:error name="apellido_paterno" />
                </div>

                <div>
                    <flux:input wire:model="apellido_materno" label="Apellido materno" type="text" name="apellido_materno" required autocomplete="additional-name" />
                    <flux:error name="apellido_materno" />
                </div>

                <div>
                    <flux:input wire:model="email" label="Correo electrónico" type="email" name="email" required autocomplete="email" />
                    <flux:error name="email" />
                </div>

                <div>
                    <flux:input wire:model="telefono_usuario" label="Número de teléfono" type="tel" name="telefono_usuario" required autocomplete="tel" />
                    <flux:error name="telefono_usuario" />
                </div>
            </div>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button
                            wire:click.prevent="resendVerificationNotification"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
