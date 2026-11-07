<?php

namespace App\Http\Controllers;

use App\Models\Documento;
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
            'vehiculo_id'           => 'required|integer|min:1',
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
            'required'                   => 'Este campo es obligatorio.',
            'integer'                    => 'Escribe un número entero.',
            'numeric'                    => 'Escribe un número válido.',
            'date'                       => 'Escribe una fecha válida.',
            'min'                        => 'El valor mínimo es :min.',
            'max'                        => 'El valor máximo es :max.',
            'vigencia_fin.after_or_equal' => 'La vigencia final no puede ser anterior a la inicial.',
            'archivo.required'           => 'Debes adjuntar un archivo.',
            'archivo.mimes'              => 'Solo se permiten archivos PDF, JPG o PNG.',
            'archivo.max'                => 'El archivo no puede pesar más de 5 MB.',
            'archivo.uploaded'           => 'No se pudo subir el archivo. Puede superar el tamaño permitido.',
        ];
    }

    /**
     * Listado de documentos (con alertas de vigencia, RF-14).
     */
    public function index()
    {
        $documentos = Documento::orderBy('vigencia_fin')->get();

        $vencidos  = $documentos->filter(fn ($d) => $d->estado_vigencia === 'Vencido')->count();
        $porVencer = $documentos->filter(fn ($d) => $d->estado_vigencia === 'Por vencer')->count();

        return view('admin.documentos.index', compact('documentos', 'vencidos', 'porVencer'));
    }

    /**
     * Formulario para registrar un documento.
     */
    public function create()
    {
        return view('admin.documentos.create');
    }

    /**
     * Guarda un documento y su archivo adjunto (RF-10, RF-11, RF-13).
     */
    public function store(Request $request)
    {
        $request->validate($this->reglas(true), $this->mensajes());

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

        return redirect()->route('admin.documentos.index')
            ->with('mensaje', 'Documento registrado correctamente')
            ->with('icono', 'success');
    }

    /**
     * Muestra los datos de un documento.
     */
    public function show(string $id)
    {
        $documento = Documento::findOrFail($id);
        return view('admin.documentos.show', compact('documento'));
    }

    /**
     * Formulario para editar un documento.
     */
    public function edit(string $id)
    {
        $documento = Documento::findOrFail($id);
        return view('admin.documentos.edit', compact('documento'));
    }

    /**
     * Actualiza un documento (el archivo solo se reemplaza si suben uno nuevo).
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->reglas(false), $this->mensajes());

        $documento = Documento::findOrFail($id);

        $documento->vehiculo_id           = $request->vehiculo_id;
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

        return redirect()->route('admin.documentos.index')
            ->with('mensaje', 'Documento actualizado correctamente')
            ->with('icono', 'success');
    }

    /**
     * Elimina un documento (soft delete). El archivo se conserva en disco.
     */
    public function destroy(string $id)
    {
        $documento = Documento::findOrFail($id);
        $documento->delete();

        return redirect()->route('admin.documentos.index')
            ->with('mensaje', 'Documento eliminado correctamente')
            ->with('icono', 'success');
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