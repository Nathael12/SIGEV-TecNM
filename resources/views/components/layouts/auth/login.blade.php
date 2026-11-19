<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#061a3b] antialiased">
        @php
            $ajuste = \App\Models\Ajuste::query()->first();
            $nombreSistema = filled($ajuste?->nombre) ? trim($ajuste->nombre) : 'SIGEV-TecNM';
            $descripcionAjustes = trim((string) ($ajuste?->descripcion ?? ''));
            $descripcionSistema = $descripcionAjustes !== ''
                ? \Illuminate\Support\Str::limit($descripcionAjustes, 220)
                : 'Plataforma institucional para la gestión y el control de la movilidad vehicular.';
            $logoUrl = $ajuste?->logo_url;

            $sitioWeb = trim((string) ($ajuste?->web ?? ''));
            $sitioWebCandidato = $sitioWeb === ''
                ? null
                : (preg_match('/^https?:\/\//i', $sitioWeb) ? $sitioWeb : 'https://' . $sitioWeb);
            $sitioWebUrl = $sitioWebCandidato && filter_var($sitioWebCandidato, FILTER_VALIDATE_URL)
                ? $sitioWebCandidato
                : null;
        @endphp

        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-[#061a3b] p-3 sm:p-5 lg:h-[100dvh] lg:min-h-0 lg:p-8">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-[18rem] -top-[22rem] h-[56rem] w-[56rem] rotate-45 border-[3rem] border-[#174A85]/20"></div>
                <div class="absolute -bottom-[24rem] -right-[18rem] h-[56rem] w-[56rem] rotate-45 border-[3rem] border-[#174A85]/20"></div>
            </div>

            <div class="relative grid w-full max-w-[84rem] overflow-hidden rounded-2xl border border-white/20 bg-white shadow-[0_24px_70px_-32px_rgba(0,0,0,0.55)] sm:rounded-3xl lg:w-[94%] lg:h-[76dvh] lg:max-h-[calc(100dvh-4rem)] lg:min-h-[37rem] lg:grid-cols-[1.12fr_0.88fr]">
                <section class="relative isolate flex min-h-[31rem] flex-col overflow-hidden bg-[#102B50] text-white lg:min-h-0" aria-label="Identidad institucional">
                    <div aria-hidden="true" class="relative min-h-[15rem] flex-1 overflow-hidden bg-[#102B50] sm:min-h-[18rem] lg:min-h-0 lg:flex-[0.9]">
                        <img src="{{ asset('images/login/Instituto2.jpeg') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-bottom">
                        <div class="absolute inset-0 bg-gradient-to-b from-[#102B50]/0 via-[#102B50]/5 to-[#102B50]/70"></div>
                    </div>

                    <div class="relative z-10 shrink-0 px-6 pb-7 pt-4 sm:px-8 sm:pb-9 lg:px-10 lg:pb-7 xl:px-12">
                        <h1 class="break-words text-3xl font-bold tracking-tight sm:text-[2.75rem]">{{ $nombreSistema }}</h1>
                        <div class="my-4 h-1 w-24 rounded-full bg-[#35A9E8]"></div>
                        <p class="max-w-2xl text-sm leading-6 text-slate-100/95 sm:text-base sm:leading-7">{{ $descripcionSistema }}</p>
                        @if ($sitioWebUrl)
                            <a href="{{ $sitioWebUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#35A9E8] transition hover:text-white">
                                <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                Sitio institucional
                            </a>
                        @endif
                    </div>
                </section>

                <section class="flex min-h-[44rem] flex-col bg-white px-6 py-7 sm:px-10 sm:py-8 lg:min-h-0 lg:px-10 lg:py-7 xl:px-14" aria-label="Acceso al sistema">
                    <div class="mx-auto flex w-full max-w-[32rem] items-center justify-center gap-4">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logotipo de {{ $nombreSistema }}" class="h-16 w-20 shrink-0 object-contain sm:h-20 sm:w-24">
                        @else
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-[#E5EAF0] bg-white px-2 text-center text-xs font-bold leading-tight text-[#102B50] sm:size-20">{{ $nombreSistema }}</div>
                        @endif
                        <div class="min-w-0 flex-1 border-l border-[#E5EAF0] pl-4 text-left">
                            <p class="text-[0.65rem] font-bold uppercase leading-4 tracking-[0.1em] text-[#174A85] sm:text-xs">Tecnol&oacute;gico Nacional de M&eacute;xico</p>
                            <p class="mt-1 whitespace-nowrap text-[0.625rem] font-semibold leading-5 text-[#102B50] sm:text-xs xl:text-sm">Instituto Tecnol&oacute;gico Superior de Hopelch&eacute;n</p>
                        </div>
                    </div>

                    <div class="mx-auto flex w-full max-w-[32rem] flex-1 flex-col justify-center py-6 sm:py-8 lg:py-4">
                        {{ $slot }}
                    </div>

                    <footer class="mx-auto w-full max-w-[32rem] border-t border-[#E5EAF0] pt-4 text-center">
                        <p class="text-xs leading-5 text-slate-500">&copy; {{ date('Y') }} {{ $nombreSistema }} &middot; Instituto Tecnol&oacute;gico de Hopelch&eacute;n</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Acceso exclusivo para personal autorizado</p>
                    </footer>
                </section>
            </div>
        </main>
        @fluxScripts
    </body>
</html>
