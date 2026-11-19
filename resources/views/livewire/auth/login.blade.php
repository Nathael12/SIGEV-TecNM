<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth.login')] class extends Component {
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $campo = filter_var($this->email, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'name';

        // Usuario eliminado (SoftDeletes): solo se avisa si la contraseña es correcta,
        // así no se revela a terceros que la cuenta existe.
        $usuarioEliminado = User::onlyTrashed()
            ->where($campo, $this->email)
            ->get()
            ->first(fn ($u) => Hash::check($this->password, $u->password));

        if ($usuarioEliminado) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        if (! Auth::attempt([
            $campo => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

{{-- Implementacion visual anterior conservada:
<div class="flex flex-col gap-6">
    <x-auth-header title="Iniciar sesion" description="Ingresa tu correo electronico o nombre y contrasena." />
    <x-auth-session-status class="text-center text-green-800" :status="session('status')" />
    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input wire:model="email" label="Correo electronico o nombre" type="text" name="email" required autofocus autocomplete="username" placeholder="Correo o nombre" />
        <flux:input wire:model="password" label="Password" type="password" name="password" required autocomplete="current-password" placeholder="Password" />
        @if (Route::has('password.request')) <x-text-link href="{{ route('password.request') }}">Forgot your password?</x-text-link> @endif
        <flux:checkbox wire:model="remember" label="Remember me" />
        <flux:button variant="primary" type="submit" class="w-full">Iniciar sesion</flux:button>
    </form>

    <!-- Acceso anterior al registro, conservado y oculto visualmente:
    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Don't have an account?
        <x-text-link href="{{ route('register') }}">Sign up</x-text-link>
    </div>
    -->
</div>
--}}
<div class="flex w-full flex-col gap-7 text-slate-700 [&_[data-flux-error]]:text-red-700">
    <div class="space-y-2.5 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-[#102B50] sm:text-4xl">Inicia sesión</h1>
        <p class="text-sm leading-6 text-slate-500">Ingresa tus credenciales para continuar</p>
    </div>

    <x-auth-session-status class="text-center text-green-800" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5" data-sigev-login>
        <flux:input wire:model="email" label:class="font-semibold text-[#174A85]" label="Usuario o correo electrónico" type="text" name="email" icon="user" required autofocus autocomplete="username" placeholder="usuario@tecnm.mx" class:input="rounded-xl !border-[#E5EAF0] !bg-white text-slate-800 placeholder:text-slate-400 shadow-sm transition focus:!border-[#35A9E8] focus:!ring-2 focus:!ring-[#35A9E8]/20 dark:!bg-white" class="[&_[data-flux-input]>div:first-child]:text-[#174A85] [&_[data-flux-icon]]:text-[#174A85]" />

        <div class="relative" x-data="{ visible: false }">
            <flux:input wire:model="password" label:class="font-semibold text-[#174A85]" label="Contraseña" type="password" x-bind:type="visible ? 'text' : 'password'" name="password" icon="lock-closed" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class:input="rounded-xl !border-[#E5EAF0] !bg-white text-slate-800 placeholder:text-slate-400 shadow-sm transition focus:!border-[#35A9E8] focus:!ring-2 focus:!ring-[#35A9E8]/20 dark:!bg-white" class="[&_[data-flux-input]>div:first-child]:text-[#174A85] [&_[data-flux-icon]]:text-[#174A85]" />
            <button type="button" class="absolute right-3 top-[1.95rem] flex size-8 items-center justify-center rounded-lg text-[#174A85] transition hover:bg-[#EAF3FF] hover:text-[#35A9E8] focus:outline-none focus:ring-2 focus:ring-[#35A9E8]/50" x-on:click="visible = !visible" x-bind:aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                <flux:icon name="eye" class="size-5" x-show="!visible" />
                <flux:icon name="eye-slash" class="size-5" x-show="visible" />
            </button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 text-slate-600 [&_[data-flux-label]]:text-slate-600 [&_[data-flux-checkbox]]:border-[#E5EAF0] [&_[data-flux-checkbox]:checked]:bg-[#174A85]">
            <flux:checkbox wire:model="remember" label="Recordarme" class="!size-5 !border-2 !border-[#35A9E8] !bg-transparent shadow-sm data-checked:!border-[#35A9E8] data-checked:!bg-transparent [&_[data-flux-checkbox-indicator]]:!bg-transparent [&_[data-flux-checkbox-indicator]>svg:first-child]:!text-[#35A9E8]" />
            @if (Route::has('password.request'))
                <x-text-link class="text-sm font-semibold !text-[#174A85] underline decoration-[#35A9E8]/60 underline-offset-4 transition hover:!text-[#35A9E8]" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</x-text-link>
            @endif
        </div>

        <flux:button variant="primary" type="submit" class="mt-1 w-full !flex-row justify-center whitespace-nowrap rounded-xl !bg-[#102B50] py-3 font-semibold text-white shadow-sm transition duration-200 hover:!bg-[#102B50] hover:shadow-md focus:!ring-4 focus:!ring-[#35A9E8]/25">
            Iniciar sesión
        </flux:button>
    </form>
</div>
