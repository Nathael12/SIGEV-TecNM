<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen overflow-x-hidden bg-white antialiased">
        {{-- Diseño anterior conservado:
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('login') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">{{ $slot }}</div>
            </div>
        </div>
        --}}

        <main class="grid min-h-screen grid-cols-1 lg:grid-cols-[56%_44%]">
            <section class="relative hidden min-h-screen overflow-hidden bg-slate-950 lg:block" aria-label="Identidad institucional">
                @if (file_exists(public_path('images/login/its-hopelchen-vehiculos.jpg')))
                    <img src="{{ asset('images/login/its-hopelchen-vehiculos.jpg') }}" alt="Vehículos institucionales del Instituto Tecnológico Superior de Hopelchén" class="absolute inset-0 h-full w-full object-cover">
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-950"></div>
                    <div class="absolute inset-0 flex items-center justify-center p-10">
                        <div class="flex h-full max-h-[680px] w-full items-center justify-center rounded-3xl border border-white/15 bg-white/[0.03] text-center">
                            <p class="rounded-full border border-white/20 bg-slate-950/40 px-5 py-3 text-sm text-white/70">Fotografía institucional pendiente</p>
                        </div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-slate-950/10"></div>

                <div class="pointer-events-none absolute left-8 top-8 h-28 w-28 rotate-45 border border-sky-300/20 sm:left-12 sm:top-12 sm:h-36 sm:w-36"></div>
                <div class="pointer-events-none absolute bottom-8 right-8 h-28 w-28 rotate-45 border border-blue-200/20 sm:bottom-12 sm:right-12 sm:h-36 sm:w-36"></div>

                <div class="absolute inset-x-0 bottom-0 p-10 text-white xl:p-14">
                    <div class="mb-5 h-1 w-16 rounded-full bg-sky-400"></div>
                    <h1 class="max-w-2xl text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">Movilidad institucional,<br> bajo control</h1>
                    <p class="mt-5 text-base font-medium tracking-wide text-slate-200">Sistema de Control Vehicular <span class="mx-2 text-sky-300">·</span> TecNM Hopelchén</p>
                </div>
            </section>

            <section class="flex min-h-screen flex-col bg-white px-6 py-8 sm:px-10 lg:px-12 xl:px-16">
                <div class="mx-auto flex w-full max-w-md items-center justify-center gap-3 text-center">
                    @if (file_exists(public_path('images/logo-tecnm-hopelchen.png')))
                        <img src="{{ asset('images/logo-tecnm-hopelchen.png') }}" alt="Logo oficial del TecNM Hopelchén" class="h-14 max-w-[190px] object-contain">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-[10px] font-medium uppercase leading-tight text-slate-400">Logo<br>oficial</div>
                    @endif
                    <div class="border-l border-slate-200 pl-3 text-center">
                        <p class="text-sm font-semibold tracking-wide text-slate-950">SIGEV-TecNM</p>
                        <p class="mt-1 text-xs text-slate-700">Instituto Tecnológico Superior de Hopelchén</p>
                    </div>
                </div>

                <div class="mx-auto flex w-full max-w-md flex-1 flex-col items-center justify-center py-10">
                    {{ $slot }}
                </div>

                <footer class="mx-auto w-full max-w-md border-t border-slate-200 pt-5 text-center text-xs leading-5 text-slate-600">
                    <p>© 2026 SIGEV-TecNM · Instituto Tecnológico de Hopelchén</p>
                    <p class="mt-1 text-slate-600">Acceso exclusivo para personal autorizado</p>
                </footer>
            </section>
        </main>
        @fluxScripts
    </body>
</html>
