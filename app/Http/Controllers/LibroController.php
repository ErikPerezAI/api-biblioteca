<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    public function index()
    {
        return response()->json(Libro::all(), 200);
    }

    public function show($id)
    {
        $libro = Libro::findOrFail($id);
        return response()->json($libro, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'anio_publicacion' => 'nullable|integer',
            'disponible' => 'sometimes|boolean',
        ]);

        $libro = Libro::create($request->all());

        return response()->json([
            'mensaje' => 'Libro creado exitosamente',
            'datos' => $libro
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $libro = Libro::findOrFail($id);

        $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'autor' => 'sometimes|required|string|max:255',
            'anio_publicacion' => 'sometimes|nullable|integer',
            'disponible' => 'sometimes|boolean',
        ]);

        $libro->update($request->all());

        return response()->json([
            'mensaje' => 'Libro actualizado exitosamente',
            'datos' => $libro
        ], 200);
    }

    public function destroy($id)
    {
        $libro = Libro::findOrFail($id);
        $libro->delete();

        return response()->json([
            'mensaje' => 'El libro fue eliminado del sistema'
        ], 200);
    }
}
