<?php

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component {
    #[Locked]
    public int $roleId;

    public string $roleName;

    public array $modules = [];

    public int $totalPermissions = 0;

    public int $assignedPermissions = 0;

    public function mount(Role $role): void
    {
        Gate::authorize('actualizar rol');

        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->refreshPermissions();
    }

    public function togglePermission(int $permissionId): void
    {
        Gate::authorize('actualizar rol');

        $role = Role::query()->findOrFail($this->roleId);
        $permission = Permission::query()
            ->where('guard_name', $role->guard_name)
            ->findOrFail($permissionId);

        $isAssigned = $role->hasPermissionTo($permission);

        if ($role->name === 'Administrador'
            && in_array($permission->name, ['ver roles', 'actualizar rol'], true)
            && $isAssigned) {
            $this->dispatch('permission-updated', message: 'El Administrador debe conservar los permisos para administrar roles.');

            return;
        }

        if ($isAssigned) {
            $role->revokePermissionTo($permission);
            $message = "Permiso '{$permission->name}' desactivado.";
        } else {
            $role->givePermissionTo($permission);
            $message = "Permiso '{$permission->name}' activado.";
        }

        $this->refreshPermissions();
        $this->dispatch('permission-updated', message: $message);
    }

    private function refreshPermissions(): void
    {
        $role = Role::query()->findOrFail($this->roleId);
        $permissions = Permission::query()
            ->where('guard_name', $role->guard_name)
            ->orderBy('name')
            ->get();
        $assignedIds = $role->permissions()->pluck('permissions.id')->all();
        $modules = config('permisos.modulos', []);
        $listedNames = collect($modules)->flatten()->all();

        $this->modules = collect($modules)
            ->map(function (array $permissionNames, string $moduleName) use ($permissions, $assignedIds) {
                $items = $permissions
                    ->whereIn('name', $permissionNames)
                    ->map(fn (Permission $permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'assigned' => in_array($permission->id, $assignedIds),
                    ])
                    ->values()
                    ->all();

                return [
                    'name' => $moduleName,
                    'permissions' => $items,
                    'assigned' => count(array_filter($items, fn (array $item) => $item['assigned'])),
                    'total' => count($items),
                ];
            })
            ->filter(fn (array $module) => $module['total'] > 0)
            ->values()
            ->all();

        $unmappedPermissions = $permissions
            ->reject(fn (Permission $permission) => in_array($permission->name, $listedNames))
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'assigned' => in_array($permission->id, $assignedIds),
            ])
            ->values()
            ->all();

        if ($unmappedPermissions !== []) {
            $this->modules[] = [
                'name' => 'Otros permisos',
                'permissions' => $unmappedPermissions,
                'assigned' => count(array_filter($unmappedPermissions, fn (array $item) => $item['assigned'])),
                'total' => count($unmappedPermissions),
            ];
        }

        $this->totalPermissions = $permissions->count();
        $this->assignedPermissions = count($assignedIds);
    }
}; ?>

<div class="mx-auto w-full max-w-7xl space-y-6" x-data="{ notice: '', noticeTimer: null }" @permission-updated.window="notice = $event.detail.message; clearTimeout(noticeTimer); noticeTimer = setTimeout(() => notice = '', 3000)">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                <flux:icon name="shield-check" class="size-6" />
            </span>
            <div>
                <flux:heading size="xl" level="1">Rol: {{ $roleName }}</flux:heading>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Activa o desactiva permisos con los switches.</p>
            </div>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800" wire:navigate>
            <flux:icon name="arrow-left" class="size-4" /> Regresar a roles
        </a>
    </div>

    <div x-cloak x-show="notice" x-transition role="status" class="fixed right-5 top-5 z-50 max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" x-text="notice"></div>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Resumen de permisos">
        @foreach ([['label' => 'Total de permisos', 'value' => $totalPermissions, 'icon' => 'key'], ['label' => 'Módulos', 'value' => count($modules), 'icon' => 'squares-2x2'], ['label' => 'Asignados', 'value' => $assignedPermissions, 'icon' => 'check-circle'], ['label' => 'No asignados', 'value' => $totalPermissions - $assignedPermissions, 'icon' => 'minus-circle']] as $stat)
            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 sm:text-sm">{{ $stat['label'] }}</p>
                    <flux:icon :name="$stat['icon']" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="Permisos por módulo">
        @foreach ($modules as $module)
            @php($complete = $module['assigned'] === $module['total'])
            <article wire:key="module-{{ $loop->index }}" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <header class="flex items-center justify-between gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <div class="flex min-w-0 items-center gap-3">
                        <span @class(['flex size-9 shrink-0 items-center justify-center rounded-xl', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $complete, 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' => ! $complete])>
                            <flux:icon :name="$complete ? 'check' : 'squares-2x2'" class="size-5" />
                        </span>
                        <h2 class="truncate font-semibold text-zinc-900 dark:text-white">{{ $module['name'] }}</h2>
                    </div>
                    <span @class(['shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $complete, 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' => ! $complete])>{{ $module['assigned'] }}/{{ $module['total'] }}</span>
                </header>

                <ul class="divide-y divide-zinc-100 px-5 dark:divide-zinc-800">
                    @foreach ($module['permissions'] as $permission)
                        <li wire:key="permission-{{ $permission['id'] }}" class="flex items-center justify-between gap-4 py-3.5">
                            <span class="min-w-0 break-words text-sm text-zinc-700 dark:text-zinc-300">{{ $permission['name'] }}</span>
                            <button type="button" role="switch" aria-label="{{ $permission['name'] }}" aria-checked="{{ $permission['assigned'] ? 'true' : 'false' }}" wire:click="togglePermission({{ $permission['id'] }})" wire:loading.attr="disabled" wire:target="togglePermission({{ $permission['id'] }})" @class(['relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition disabled:cursor-wait disabled:opacity-60', 'bg-emerald-600' => $permission['assigned'], 'bg-zinc-300 dark:bg-zinc-700' => ! $permission['assigned']])>
                                <span @class(['inline-block size-4 rounded-full bg-white shadow transition-transform', 'translate-x-6' => $permission['assigned'], 'translate-x-1' => ! $permission['assigned']])></span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </section>
</div>
