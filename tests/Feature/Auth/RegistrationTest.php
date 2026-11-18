<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            // Antes se probaba name con el nombre real completo.
            // ->set('name', 'Test User')
            ->set('name', 'testuser24')
            ->set('email', 'test@example.com')
            ->set('nombre_usuario', 'Test')
            ->set('apellido_paterno', 'User')
            ->set('apellido_materno', 'Example')
            ->set('telefono_usuario', '1234567890')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
