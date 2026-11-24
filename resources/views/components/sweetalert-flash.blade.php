@if (session('mensaje'))
    <div class="hidden" data-swal-flash data-icon="{{ session('tipo_mensaje', 'success') }}" data-title="{{ session('mensaje') }}"></div>
@endif

@if (session('advertencia'))
    <div class="hidden" data-swal-flash data-icon="warning" data-title="{{ session('advertencia') }}"></div>
@endif

@if (session('error'))
    <div class="hidden" data-swal-flash data-icon="error" data-title="{{ session('error') }}"></div>
@endif

@if ($errors->any())
    <div class="hidden" data-swal-flash data-icon="error" data-title="No se pudo guardar" data-text="{{ implode('. ', $errors->all()) }}"></div>
@endif
