<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $nombre_usuario = '';
    public string $apellido_paterno = '';
    public string $apellido_materno = '';
    public string $telefono_usuario = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            // Regla anterior: name guardaba el nombre completo de la persona.
            // 'name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'min:4', 'max:20', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/', Rule::unique('users', 'name')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'nombre_usuario' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'telefono_usuario' => ['required', 'digits:10'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.min' => 'El nombre de usuario debe tener al menos 4 caracteres.',
            'name.max' => 'El nombre de usuario no puede superar 20 caracteres.',
            'name.regex' => 'Usa de 4 a 20 caracteres: letras, números y guion bajo; debe comenzar con una letra.',
            'name.unique' => 'Ese nombre de usuario ya está registrado.',
        ]);

        $validated['correo_usuario'] = $validated['email'];
        $validated['estado'] = 'Activo';
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function updatedName(): void
    {
        $this->validateOnly('name', [
            'name' => ['required', 'string', 'min:4', 'max:20', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/', Rule::unique('users', 'name')],
        ], [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.min' => 'El nombre de usuario debe tener al menos 4 caracteres.',
            'name.max' => 'El nombre de usuario no puede superar 20 caracteres.',
            'name.regex' => 'Usa de 4 a 20 caracteres: letras, números y guion bajo; debe comenzar con una letra.',
            'name.unique' => 'Ese nombre de usuario ya está registrado.',
        ]);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Create an account" description="Enter your details below to create your account" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        {{-- Campo original de name antes de convertirlo en identificador de acceso, conservado como referencia:
        <flux:input wire:model="name" id="name" label="{{ __('Name') }}" type="text" name="name" required autofocus autocomplete="name" placeholder="Full name" />
        --}}
        <!-- Nombre de usuario guardado en users.name -->
        <div class="grid gap-2">
            <flux:input wire:model.live.debounce.300ms="name" id="name" label="Nombre de usuario" type="text" name="name" required autofocus autocomplete="username" placeholder="jorge_11" maxlength="20" pattern="[a-zA-Z][a-zA-Z0-9_]{3,19}" x-on:keydown.enter.prevent x-on:input.capture="$el.value = $el.value.replace(/[^a-zA-Z0-9_]/g, '').replace(/^[0-9_]+/, '').slice(0, 20)" />
            <p class="text-xs text-zinc-500 dark:text-zinc-400">4 a 20 caracteres: letras, números y guion bajo; comienza con una letra. <span>{{ strlen($name) }}/20</span></p>
            <flux:error name="name" />
        </div>

        <!-- Real name -->
        <div class="grid gap-2">
            <flux:input wire:model="nombre_usuario" id="nombre_usuario" label="Nombre" type="text" name="nombre_usuario" required autocomplete="given-name" placeholder="Nombre" />
            <flux:error name="nombre_usuario" />
        </div>

        <div class="grid gap-2">
            <flux:input wire:model="apellido_paterno" id="apellido_paterno" label="Apellido paterno" type="text" name="apellido_paterno" required autocomplete="family-name" />
            <flux:error name="apellido_paterno" />
        </div>

        <div class="grid gap-2">
            <flux:input wire:model="apellido_materno" id="apellido_materno" label="Apellido materno" type="text" name="apellido_materno" required />
            <flux:error name="apellido_materno" />
        </div>

        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" id="email" label="Correo electrónico" type="email" name="email" required autocomplete="email" placeholder="email@example.com" />
            <flux:error name="email" />
        </div>

        <div class="grid gap-2">
            <flux:input wire:model="telefono_usuario" id="telefono_usuario" label="Teléfono" type="tel" name="telefono_usuario" required maxlength="10" inputmode="numeric" autocomplete="tel" />
            <flux:error name="telefono_usuario" />
        </div>

        <!-- Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password"
                id="password"
                label="{{ __('Password') }}"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Password"
            />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password_confirmation"
                id="password_confirmation"
                label="{{ __('Confirm password') }}"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
            />
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Already have an account?
        <x-text-link href="{{ route('login') }}">Log in</x-text-link>
    </div>
</div>
