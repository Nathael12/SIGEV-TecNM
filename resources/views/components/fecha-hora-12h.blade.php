{{--
    Campo de fecha y hora en formato de 12 horas (AM/PM).
    Guardar en: resources/views/components/fecha-hora-12h.blade.php

    Uso:  <x-fecha-hora-12h name="fecha_salida" :value="old('fecha_salida')" :required="true" />

    Envía UN solo campo oculto con el nombre indicado, en formato AAAA-MM-DDTHH:MM (24 h),
    que es lo que ya espera el controlador. Los demás controles solo sirven para capturar.
--}}
@props(['name', 'value' => null, 'required' => false])

@php
    $valor = $value ? rescue(fn () => \Illuminate\Support\Carbon::parse($value), null, false) : null;
    $hora12 = $valor ? (int) $valor->format('g') : null;
    $minuto = $valor ? $valor->format('i') : null;
    $ampm = $valor ? $valor->format('A') : null;
@endphp

<div class="fecha-hora-12h" data-nombre="{{ $name }}">
    <input type="hidden" name="{{ $name }}" value="{{ $valor ? $valor->format('Y-m-d\TH:i') : '' }}">

    {{-- Fecha --}}
    <flux:input type="date" name="{{ $name }}_fecha" value="{{ $valor ? $valor->format('Y-m-d') : '' }}"
        icon="calendar-days" :required="$required" />

    {{-- Hora, minuto y AM/PM --}}
    <div style="display:flex;gap:8px;margin-top:8px;">
        <div style="flex:1;min-width:0;">
            <flux:select name="{{ $name }}_hora" placeholder="Hora" :required="$required">
                @for ($h = 1; $h <= 12; $h++)
                    <flux:select.option value="{{ $h }}" :selected="$hora12 === $h">
                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</flux:select.option>
                @endfor
            </flux:select>
        </div>

        <div style="flex:1;min-width:0;">
            <flux:select name="{{ $name }}_minuto" placeholder="Min" :required="$required">
                @for ($m = 0; $m < 60; $m++)
                    <flux:select.option value="{{ sprintf('%02d', $m) }}"
                        :selected="$minuto === sprintf('%02d', $m)">{{ sprintf('%02d', $m) }}</flux:select.option>
                @endfor
            </flux:select>
        </div>

        <div style="flex:1;min-width:0;">
            <flux:select name="{{ $name }}_ampm" placeholder="AM/PM" :required="$required">
                <flux:select.option value="AM" :selected="$ampm === 'AM'">AM</flux:select.option>
                <flux:select.option value="PM" :selected="$ampm === 'PM'">PM</flux:select.option>
            </flux:select>
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            function iniciar() {
                document.querySelectorAll('.fecha-hora-12h').forEach(function (contenedor) {
                    if (contenedor.dataset.listo === '1') return;
                    contenedor.dataset.listo = '1';

                    const nombre = contenedor.dataset.nombre;
                    const oculto = contenedor.querySelector('input[type="hidden"]');
                    const fecha = contenedor.querySelector('[name="' + nombre + '_fecha"]');
                    const hora = contenedor.querySelector('[name="' + nombre + '_hora"]');
                    const minuto = contenedor.querySelector('[name="' + nombre + '_minuto"]');
                    const ampm = contenedor.querySelector('[name="' + nombre + '_ampm"]');

                    if (!oculto || !fecha || !hora || !minuto || !ampm) return;

                    const dos = (n) => String(n).padStart(2, '0');

                    // Convierte los cuatro controles en un solo valor de 24 horas
                    function sincronizar() {
                        if (!fecha.value || !hora.value || minuto.value === '' || !ampm.value) {
                            oculto.value = '';
                            return;
                        }

                        let h = parseInt(hora.value, 10) % 12;
                        if (ampm.value === 'PM') h += 12;

                        oculto.value = fecha.value + 'T' + dos(h) + ':' + minuto.value;
                    }

                    [fecha, hora, minuto, ampm].forEach(function (control) {
                        control.addEventListener('change', sincronizar);
                        control.addEventListener('input', sincronizar);
                    });

                    // Si está vacío, se propone la fecha y hora actuales del equipo
                    if (!oculto.value) {
                        const ahora = new Date();
                        fecha.value = ahora.getFullYear() + '-' + dos(ahora.getMonth() + 1) + '-' + dos(ahora.getDate());
                        hora.value = String(ahora.getHours() % 12 || 12);
                        minuto.value = dos(ahora.getMinutes());
                        ampm.value = ahora.getHours() >= 12 ? 'PM' : 'AM';
                    }

                    sincronizar();
                });
            }

            if (document.readyState === 'complete') {
                iniciar();
            } else {
                window.addEventListener('load', iniciar);
            }
        })();
    </script>
@endonce