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

new #[Layout('components.layouts.auth')] class extends Component {
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
<div class="flex w-full flex-col gap-8 text-slate-700 [&_[data-flux-error]]:text-red-700">
    <div class="space-y-2">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-900">Acceso al sistema</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Bienvenido a SIGEV</h1>
        <p class="text-sm text-slate-700">Ingresa tus credenciales para continuar</p>
    </div>

    <x-auth-session-status class="text-center text-green-800" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5" data-sigev-login>
        <flux:input wire:model="email" label:class="text-slate-800" label="Usuario o correo electrónico" type="text" name="email" icon="user" required autofocus autocomplete="username" placeholder="Ingresa tu usuario o correo" class:input="rounded-xl !bg-white text-slate-800 placeholder:text-slate-500 border-slate-300 focus:border-blue-800 focus:ring-blue-800 dark:!bg-white" class="[&_[data-flux-input]>div:first-child]:text-slate-600" />

        <div class="relative" x-data="{ visible: false }">
            <flux:input wire:model="password" label:class="text-slate-800" label="Contraseña" type="password" x-bind:type="visible ? 'text' : 'password'" name="password" icon="lock-closed" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class:input="rounded-xl !bg-white text-slate-800 placeholder:text-slate-500 border-slate-300 focus:border-blue-800 focus:ring-blue-800 dark:!bg-white" class="[&_[data-flux-input]>div:first-child]:text-slate-600" />
            <button type="button" class="absolute right-3 top-[2.35rem] rounded p-1 text-slate-600 transition hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-700" x-on:click="visible = !visible" x-bind:aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                <flux:icon name="eye" class="size-5" x-show="!visible" />
                <flux:icon name="eye-slash" class="size-5" x-show="visible" />
            </button>
            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <x-text-link class="text-sm font-semibold text-blue-900 hover:text-blue-950" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</x-text-link>
                </div>
            @endif
        </div>

        <div class="text-slate-700 [&_[data-flux-label]]:text-slate-700">
        <flux:checkbox wire:model="remember" label="Recordarme" />
        </div>

        <flux:button variant="primary" type="submit" class="mt-2 w-full justify-center bg-slate-900 text-white hover:bg-blue-950">
            <span>Iniciar sesión</span>
            <flux:icon name="arrow-right" class="ml-2 size-4" />
        </flux:button>
    </form>
</div>
