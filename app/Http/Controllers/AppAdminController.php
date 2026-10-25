<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrera;
use App\Models\EspacioLia;
use App\Models\PersonalLia;
use App\Models\ActividadLia;
use App\Models\ReporteConfigurado;
use App\Models\Tipo;
use App\Models\Ubicacion;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AppAdminController extends Controller
{
    /**
     * Panel Principal de Configuración y Gestión de la App (app_admin)
     */
    public function index(): View
    {
        $totalCarreras = Carrera::count();
        $carrerasActivas = Carrera::activas()->count();
        $totalEspacios = EspacioLia::count();
        $totalPersonal = PersonalLia::count();
        $totalActividades = ActividadLia::count();

        $ultimasActividades = ActividadLia::with('autor')->latest()->take(3)->get();
        $ultimosEspacios = EspacioLia::orderBy('orden')->take(4)->get();
        $personalDestacado = PersonalLia::orderBy('orden')->take(4)->get();

        return view('admin.app.index', compact(
            'totalCarreras',
            'carrerasActivas',
            'totalEspacios',
            'totalPersonal',
            'totalActividades',
            'ultimasActividades',
            'ultimosEspacios',
            'personalDestacado'
        ));
    }

    // ==========================================
    // 1. GESTIÓN DE CARRERAS (HISTORIAL INMUTABLE)
    // ==========================================
    public function carrerasIndex(): View
    {
        $carreras = Carrera::withCount('usuarios')->orderBy('activa', 'desc')->orderBy('nombre')->get();
        return view('admin.app.carreras', compact('carreras'));
    }

    public function carreraStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:carreras,nombre',
            'codigo' => 'nullable|string|max:50',
            'activa' => 'nullable|boolean',
        ]);

        $validated['activa'] = $request->has('activa');

        Carrera::create($validated);

        return redirect()->route('app_admin.carreras')
            ->with('success', 'Carrera agregada exitosamente a la lista institucional.');
    }

    public function carreraUpdate(Request $request, Carrera $carrera): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:carreras,nombre,' . $carrera->id,
            'codigo' => 'nullable|string|max:50',
            'activa' => 'nullable|boolean',
        ]);

        $validated['activa'] = $request->has('activa');

        $carrera->update($validated);

        return redirect()->route('app_admin.carreras')
            ->with('success', 'Datos de la carrera actualizados correctamente.');
    }

    public function carreraToggle(Carrera $carrera): RedirectResponse
    {
        $carrera->update(['activa' => !$carrera->activa]);
        $estado = $carrera->activa ? 'habilitada' : 'deshabilitada / archivada';

        return redirect()->route('app_admin.carreras')
            ->with('success', "La carrera {$carrera->nombre} fue {$estado}. Los usuarios históricos mantienen su registro inmutable.");
    }

    public function carreraDestroy(Carrera $carrera): RedirectResponse
    {
        if ($carrera->usuarios()->exists()) {
            // Preservación histórica garantizada: no borrar físicamente si hay usuarios
            $carrera->update(['activa' => false]);
            return redirect()->route('app_admin.carreras')
                ->with('success', "La carrera {$carrera->nombre} cuenta con {$carrera->usuarios()->count()} usuarios asociados. Se desactivó de la oferta de registro para proteger el historial inmutable.");
        }

        $carrera->delete();
        return redirect()->route('app_admin.carreras')
            ->with('success', 'Carrera eliminada exitosamente del catálogo.');
    }

    // ==========================================
    // 2. GESTIÓN DE ESPACIOS DEL LIA (FOTOS)
    // ==========================================
    public function espaciosIndex(): View
    {
        $espacios = EspacioLia::orderBy('orden')->orderBy('id')->get();
        return view('admin.app.espacios', compact('espacios'));
    }

    public function espacioStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|max:4096',
            'imagen_url' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
        ]);

        $imagenPath = $validated['imagen_url'] ?? null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('espacios', 'public');
            $imagenPath = '/storage/' . $path;
        }

        EspacioLia::create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'imagen_path' => $imagenPath ?: '/assets/notebook-wide.png',
            'orden' => $validated['orden'] ?? (EspacioLia::max('orden') + 1),
            'activo' => true,
        ]);

        return redirect()->route('app_admin.espacios')
            ->with('success', 'Espacio/Laboratorio del LIA publicado con éxito.');
    }

    public function espacioUpdate(Request $request, EspacioLia $espacio): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|max:4096',
            'imagen_url' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data = [
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'orden' => $validated['orden'] ?? $espacio->orden,
            'activo' => $request->has('activo'),
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('espacios', 'public');
            $data['imagen_path'] = '/storage/' . $path;
        } elseif (!empty($validated['imagen_url'])) {
            $data['imagen_path'] = $validated['imagen_url'];
        }

        $espacio->update($data);

        return redirect()->route('app_admin.espacios')
            ->with('success', 'Espacio actualizado correctamente.');
    }

    public function espacioDestroy(EspacioLia $espacio): RedirectResponse
    {
        $espacio->delete();
        return redirect()->route('app_admin.espacios')
            ->with('success', 'Espacio eliminado del catálogo.');
    }

    // ==========================================
    // 3. PERSONAL DEL LIA (FIGURAS CLAVE)
    // ==========================================
    public function personalIndex(): View
    {
        $personal = PersonalLia::orderBy('orden')->orderBy('id')->get();
        return view('admin.app.personal', compact('personal'));
    }

    public function personalStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'required|string|max:150',
            'comentario' => 'nullable|string|max:1000',
            'email' => 'nullable|email|max:150',
            'foto' => 'nullable|image|max:4096',
            'foto_url' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
        ]);

        $fotoPath = $validated['foto_url'] ?? null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('personal', 'public');
            $fotoPath = '/storage/' . $path;
        }

        PersonalLia::create([
            'nombre' => $validated['nombre'],
            'cargo' => $validated['cargo'],
            'comentario' => $validated['comentario'] ?? null,
            'email' => $validated['email'] ?? null,
            'foto_path' => $fotoPath,
            'orden' => $validated['orden'] ?? (PersonalLia::max('orden') + 1),
            'activo' => true,
        ]);

        return redirect()->route('app_admin.personal')
            ->with('success', 'Miembro institucional añadido con éxito.');
    }

    public function personalUpdate(Request $request, PersonalLia $personal): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'required|string|max:150',
            'comentario' => 'nullable|string|max:1000',
            'email' => 'nullable|email|max:150',
            'foto' => 'nullable|image|max:4096',
            'foto_url' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'cargo' => $validated['cargo'],
            'comentario' => $validated['comentario'] ?? null,
            'email' => $validated['email'] ?? null,
            'orden' => $validated['orden'] ?? $personal->orden,
            'activo' => $request->has('activo'),
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('personal', 'public');
            $data['foto_path'] = '/storage/' . $path;
        } elseif (!empty($validated['foto_url'])) {
            $data['foto_path'] = $validated['foto_url'];
        }

        $personal->update($data);

        return redirect()->route('app_admin.personal')
            ->with('success', 'Registro de personal actualizado.');
    }

    public function personalDestroy(PersonalLia $personal): RedirectResponse
    {
        $personal->delete();
        return redirect()->route('app_admin.personal')
            ->with('success', 'Miembro eliminado del listado institucional.');
    }

    // ==========================================
    // 4. ACTIVIDADES DEL LIA (POSTS TIPO INSTAGRAM)
    // ==========================================
    public function actividadesIndex(): View
    {
        $actividades = ActividadLia::with('autor')->orderBy('fecha_publicacion', 'desc')->get();
        return view('admin.app.actividades', compact('actividades'));
    }

    public function actividadStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string|max:3000',
            'foto' => 'nullable|image|max:5120',
            'imagen_url' => 'nullable|string|max:255',
            'fecha_publicacion' => 'nullable|date',
        ]);

        $imagenPath = $validated['imagen_url'] ?? null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('actividades', 'public');
            $imagenPath = '/storage/' . $path;
        }

        ActividadLia::create([
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'imagen_path' => $imagenPath,
            'fecha_publicacion' => !empty($validated['fecha_publicacion']) ? $validated['fecha_publicacion'] : now(),
            'activo' => true,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('app_admin.actividades')
            ->with('success', 'Actividad publicada en el feed del LIA con éxito.');
    }

    public function actividadUpdate(Request $request, ActividadLia $actividad): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string|max:3000',
            'foto' => 'nullable|image|max:5120',
            'imagen_url' => 'nullable|string|max:255',
            'fecha_publicacion' => 'nullable|date',
            'activo' => 'nullable|boolean',
        ]);

        $data = [
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'fecha_publicacion' => !empty($validated['fecha_publicacion']) ? $validated['fecha_publicacion'] : $actividad->fecha_publicacion,
            'activo' => $request->has('activo'),
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('actividades', 'public');
            $data['imagen_path'] = '/storage/' . $path;
        } elseif (!empty($validated['imagen_url'])) {
            $data['imagen_path'] = $validated['imagen_url'];
        }

        $actividad->update($data);

        return redirect()->route('app_admin.actividades')
            ->with('success', 'Posteo de actividad actualizado.');
    }

    public function actividadDestroy(ActividadLia $actividad): RedirectResponse
    {
        $actividad->delete();
        return redirect()->route('app_admin.actividades')
            ->with('success', 'Publicación eliminada del feed.');
    }

    // ==========================================
    // 5. GESTIÓN DE REPORTES CONFIGURADOS
    // ==========================================
    public function reportesIndex(): View
    {
        $reportes = ReporteConfigurado::with('creador')
            ->orderBy('es_sistema', 'desc')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalReportes = $reportes->count();
        $reportesSistema = $reportes->where('es_sistema', true)->count();
        $reportesPersonalizados = $reportes->where('es_sistema', false)->count();
        $reportesActivos = $reportes->where('activo', true)->count();

        $tipos = Tipo::orderBy('nombre')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        return view('admin.app.reportes', compact(
            'reportes',
            'totalReportes',
            'reportesSistema',
            'reportesPersonalizados',
            'reportesActivos',
            'tipos',
            'ubicaciones'
        ));
    }

    public function guardarDesdeInventario(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'subtitulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:50',
            'categoria' => 'nullable|string|max:50',
            'filtros_fijos' => 'nullable|array',
            'variables_requeridas' => 'nullable|array',
        ]);

        $slug = ReporteConfigurado::generarSlug($validated['titulo']);

        $reporte = ReporteConfigurado::create([
            'slug' => $slug,
            'titulo' => $validated['titulo'],
            'subtitulo' => $validated['subtitulo'] ?? null,
            'descripcion' => $validated['descripcion'] ?? 'Reporte configurado desde vista de Inventario',
            'icono' => $validated['icono'] ?: 'cubes',
            'categoria' => $validated['categoria'] ?: 'personalizado',
            'filtros_fijos' => $validated['filtros_fijos'] ?? [],
            'variables_requeridas' => $validated['variables_requeridas'] ?? [],
            'es_sistema' => false,
            'activo' => true,
            'creado_por' => auth()->id(),
            'orden' => (ReporteConfigurado::max('orden') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', "Reporte \"{$reporte->titulo}\" creado y registrado exitosamente en el catálogo de Reportes.");
    }

    public function reporteStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'subtitulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:50',
            'categoria' => 'nullable|string|max:50',
            'filtros_fijos' => 'nullable|array',
            'variables_requeridas' => 'nullable|array',
            'activo' => 'nullable|boolean',
            'orden' => 'nullable|integer',
        ]);

        $slug = ReporteConfigurado::generarSlug($validated['titulo']);

        ReporteConfigurado::create([
            'slug' => $slug,
            'titulo' => $validated['titulo'],
            'subtitulo' => $validated['subtitulo'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'icono' => $validated['icono'] ?: 'cubes',
            'categoria' => $validated['categoria'] ?: 'general',
            'filtros_fijos' => $validated['filtros_fijos'] ?? [],
            'variables_requeridas' => $validated['variables_requeridas'] ?? [],
            'es_sistema' => false,
            'activo' => $request->has('activo'),
            'orden' => (int)($validated['orden'] ?? ((ReporteConfigurado::max('orden') ?? 0) + 1)),
            'creado_por' => auth()->id(),
        ]);

        return redirect()->route('app_admin.reportes')->with('success', 'Reporte añadido correctamente.');
    }

    public function reporteUpdate(ReporteConfigurado $reporte, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'subtitulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:50',
            'categoria' => 'nullable|string|max:50',
            'filtros_fijos' => 'nullable|array',
            'variables_requeridas' => 'nullable|array',
            'activo' => 'nullable|boolean',
            'orden' => 'nullable|integer',
        ]);

        $data = [
            'titulo' => $validated['titulo'],
            'subtitulo' => $validated['subtitulo'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'icono' => $validated['icono'] ?: $reporte->icono,
            'categoria' => $validated['categoria'] ?: $reporte->categoria,
            'filtros_fijos' => $validated['filtros_fijos'] ?? [],
            'variables_requeridas' => $validated['variables_requeridas'] ?? [],
            'activo' => $request->has('activo'),
            'orden' => (int)($validated['orden'] ?? $reporte->orden),
        ];

        $reporte->update($data);

        return redirect()->route('app_admin.reportes')->with('success', "Reporte \"{$reporte->titulo}\" actualizado correctamente.");
    }

    public function reporteToggle(ReporteConfigurado $reporte): RedirectResponse
    {
        $reporte->activo = !$reporte->activo;
        $reporte->save();

        $estado = $reporte->activo ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "El reporte \"{$reporte->titulo}\" ha sido {$estado}.");
    }

    public function reporteDestroy(ReporteConfigurado $reporte): RedirectResponse
    {
        if ($reporte->es_sistema) {
            $reporte->activo = false;
            $reporte->save();
            return redirect()->route('app_admin.reportes')->with('success', "El reporte del sistema \"{$reporte->titulo}\" ha sido desactivado.");
        }

        $nombre = $reporte->titulo;
        $reporte->delete();

        return redirect()->route('app_admin.reportes')->with('success', "Reporte \"{$nombre}\" eliminado exitosamente.");
    }
}
