<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /**
     * Reglas y mensajes compartidos por store y update.
     * En update el archivo es opcional (si no suben uno, se conserva el actual).
     */
    private function reglas(bool $archivoObligatorio): array
    {
        return [
            'descripcion_documento' => 'required|string|max:255',
            'vigencia_inicio'       => 'required|date',
            'vigencia_fin'          => 'required|date|after_or_equal:vigencia_inicio',
            'importe'               => 'required|numeric|min:0|max:99999999.99',
            // RF-13: solo PDF o imágenes, máximo 5 MB (5120 KB)
            'archivo'               => ($archivoObligatorio ? 'required' : 'nullable')
                . '|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    private function mensajes(): array
    {
        return [
            'required'                    => 'Este campo es obligatorio.',
            'integer'                     => 'Escribe un número entero.',
            'numeric'                     => 'Escribe un número válido.',
            'date'                        => 'Escribe una fecha válida.',
            'min'                         => 'El valor mínimo es :min.',
            'max'                         => 'El valor máximo es :max.',
            'vehiculo_id.required'        => 'Selecciona un vehículo.',
            'vehiculo_id.exists'          => 'El vehículo seleccionado no existe.',
            'vigencia_fin.after_or_equal' => 'La vigencia final no puede ser anterior a la inicial.',
            'archivo.required'            => 'Debes adjuntar un archivo.',
            'archivo.mimes'               => 'Solo se permiten archivos PDF, JPG o PNG.',
            'archivo.max'                 => 'El archivo no puede pesar más de 5 MB.',
            'archivo.uploaded'            => 'No se pudo subir el archivo. Puede superar el tamaño permitido.',
        ];
    }

    /**
     * Regresa al listado de documentos del vehículo indicado.
     */
    private function volverAlListado($vehiculoId, string $mensaje)
    {
        return redirect()->route('admin.documentos.index', ['vehiculo' => $vehiculoId])
            ->with('mensaje', $mensaje)
            ->with('icono', 'success');
    }

    /**
     * Listado de documentos. Con ?vehiculo=ID solo muestra los de ese vehículo.
     * Incluye alertas de vigencia (RF-14).
     */
    public function index(Request $request)
    {
        $consulta = Documento::with('vehiculo')->orderBy('vigencia_fin');
        $vehiculo = null;

        if ($request->filled('vehiculo')) {
            $vehiculo = Vehiculo::findOrFail($request->vehiculo);
            $consulta->where('vehiculo_id', $vehiculo->getKey());
        }

        $documentos = $consulta->get();

        $vencidos  = $documentos->filter(fn ($d) => $d->estado_vigencia === 'Vencido')->count();
        $porVencer = $documentos->filter(fn ($d) => $d->estado_vigencia === 'Por vencer')->count();

        return view('admin.documentos.index', compact('documentos', 'vencidos', 'porVencer', 'vehiculo'));
    }

    /**
     * Formulario para registrar un documento.
     * Un documento siempre se registra desde el vehículo al que pertenece
     * (?vehiculo=ID), así no se puede mezclar la documentación entre vehículos.
     */
    public function create(Request $request)
    {
        if (! $request->filled('vehiculo')) {
            return redirect()->route('admin.vehiculos.index')
                ->with('mensaje', 'Para registrar un documento, entra a la Documentación del vehículo correspondiente.');
        }

        $vehiculo = Vehiculo::findOrFail($request->vehiculo);

        return view('admin.documentos.create', compact('vehiculo'));
    }

    /**
     * Guarda un documento y su archivo adjunto (RF-10, RF-11, RF-13).
     */
    public function store(Request $request)
    {
        $request->validate(
            $this->reglas(true) + ['vehiculo_id' => 'required|integer|exists:vehiculos,id_vehiculo'],
            $this->mensajes()
        );

        $archivo = $request->file('archivo');

        // Metadatos: se leen antes de guardar el archivo
        $nombreOriginal = $archivo->getClientOriginalName();
        $tipo           = $archivo->getMimeType();
        $tamano         = $archivo->getSize();

        // Se guarda en storage/app/private/documentos (no es accesible por URL)
        $ruta = $archivo->store('documentos');

        Documento::create([
            'vehiculo_id'           => $request->vehiculo_id,
            'descripcion_documento' => $request->descripcion_documento,
            'vigencia_inicio'       => $request->vigencia_inicio,
            'vigencia_fin'          => $request->vigencia_fin,
            'importe'               => $request->importe,
            'ruta_archivo'          => $ruta,
            'nombre_original'       => $nombreOriginal,
            'tipo_archivo'          => $tipo,
            'tamano_archivo'        => $tamano,
        ]);

        return $this->volverAlListado($request->vehiculo_id, 'Documento registrado correctamente');
    }

    /**
     * Muestra los datos de un documento.
     */
    public function show(string $id)
    {
        $documento = Documento::with('vehiculo')->findOrFail($id);
        return view('admin.documentos.show', compact('documento'));
    }

    /**
     * Formulario para editar un documento.
     */
    public function edit(string $id)
    {
        $documento = Documento::with('vehiculo')->findOrFail($id);

        return view('admin.documentos.edit', compact('documento'));
    }

    /**
     * Actualiza un documento (el archivo solo se reemplaza si suben uno nuevo).
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->reglas(false), $this->mensajes());

        $documento = Documento::findOrFail($id);

        $documento->descripcion_documento = $request->descripcion_documento;
        $documento->vigencia_inicio       = $request->vigencia_inicio;
        $documento->vigencia_fin          = $request->vigencia_fin;
        $documento->importe               = $request->importe;

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');

            // Metadatos del archivo nuevo
            $nombreOriginal = $archivo->getClientOriginalName();
            $tipo           = $archivo->getMimeType();
            $tamano         = $archivo->getSize();

            // Se elimina el archivo anterior y se guarda el nuevo
            if ($documento->ruta_archivo) {
                Storage::delete($documento->ruta_archivo);
            }

            $documento->ruta_archivo    = $archivo->store('documentos');
            $documento->nombre_original = $nombreOriginal;
            $documento->tipo_archivo    = $tipo;
            $documento->tamano_archivo  = $tamano;
        }

        $documento->save();

        return $this->volverAlListado($documento->vehiculo_id, 'Documento actualizado correctamente');
    }

    /**
     * Elimina un documento (soft delete). El archivo se conserva en disco.
     */
    public function destroy(string $id)
    {
        $documento = Documento::findOrFail($id);
        $vehiculoId = $documento->vehiculo_id;

        $documento->delete();

        return $this->volverAlListado($vehiculoId, 'Documento eliminado correctamente');
    }

    /**
     * Descarga el archivo adjunto (RF-12). La ruta exige sesión iniciada.
     */
    public function descargar(string $id)
    {
        $documento = Documento::findOrFail($id);

        abort_unless(
            $documento->ruta_archivo && Storage::exists($documento->ruta_archivo),
            404,
            'El archivo no existe.'
        );

        return Storage::download($documento->ruta_archivo, $documento->nombre_original);
    }
}