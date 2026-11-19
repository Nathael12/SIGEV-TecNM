<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth.login')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex w-full flex-col gap-7 text-slate-700 [&_[data-flux-error]]:text-red-700">
    <div class="space-y-2.5 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-[#102B50] sm:text-4xl">¿Olvidaste tu contraseña?</h1>
        <p class="text-sm leading-6 text-slate-500">Ingresa tu correo electrónico para recibir un enlace de restablecimiento de contraseña.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-5">
        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" label:class="font-semibold text-[#174A85]" label="Ingresa tu correo" type="email" name="email" icon="envelope" required autofocus placeholder="email@example.com" class:input="rounded-xl !border-[#E5EAF0] !bg-white text-slate-800 placeholder:text-slate-400 !shadow-none transition focus:!border-[#35A9E8] focus:!ring-2 focus:!ring-[#35A9E8]/20 dark:!bg-white" class="[&_[data-flux-input]>div:first-child]:text-[#174A85] [&_[data-flux-icon]]:text-[#174A85]" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full rounded-xl !bg-[#102B50] py-3 font-semibold text-white shadow-sm transition hover:!bg-[#174A85] hover:shadow-md focus:!ring-4 focus:!ring-[#35A9E8]/25">Enviar enlace de restablecimiento</flux:button>
    </form>

    <div class="space-x-1 text-center text-sm text-slate-500">
        ¿Recordaste tu contraseña? Vuelve a
        <x-text-link class="font-semibold !text-[#174A85] underline decoration-[#35A9E8]/60 underline-offset-4 transition hover:!text-[#35A9E8]" href="{{ route('login') }}">iniciar sesión</x-text-link>
    </div>
</div>
