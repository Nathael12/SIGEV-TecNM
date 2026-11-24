<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    {{-- DomPDF no entiende CSS moderno (flex, grid, Tailwind): solo tablas y estilos simples --}}
    <style>
        @page {
            margin: 100px 30px 55px 30px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #111827;
        }

        header {
            position: fixed;
            top: -82px;
            left: 0;
            right: 0;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
        }

        header .institucion {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a8a;
        }

        header .titulo {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        header .generado {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }

        footer {
            position: fixed;
            bottom: -38px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #6b7280;
            text-align: right;
        }

        .pagenum:before {
            content: counter(page);
        }

        .bloque {
            margin-bottom: 10px;
            padding: 6px 8px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
        }

        .bloque b {
            color: #1e3a8a;
        }

        table.datos {
            width: 100%;
            border-collapse: collapse;
        }

        table.datos thead {
            display: table-header-group;
        }

        table.datos th {
            background-color: #1e3a8a;
            color: #ffffff;
            border: 1px solid #1e3a8a;
            padding: 5px 4px;
            font-size: 9px;
            text-transform: uppercase;
        }

        table.datos td {
            border: 1px solid #d1d5db;
            padding: 4px;
        }

        table.datos tr {
            page-break-inside: avoid;
        }

        table.datos tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .centro {
            text-align: center;
        }

        .vacio {
            text-align: center;
            padding: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <header>
        <div class="institucion">{{ $institucion }}</div>
        <div class="titulo">{{ $titulo }}</div>
        <div class="generado">Generado el {{ $generado->format('d/m/Y h:i A') }} por {{ $usuario }}</div>
    </header>

    <footer>
        Página <span class="pagenum"></span>
    </footer>

    <main>
        @yield('contenido')
    </main>
</body>
</html>