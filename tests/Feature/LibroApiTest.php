<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Libro;

class LibroApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba 1: GET /api/libros -> Listar (200)
     */
    public function test_01_listar_libros(): void
    {
        Libro::create([
            'titulo' => 'Cien años de soledad',
            'autor' => 'Gabriel García Márquez',
            'anio_publicacion' => 1967,
            'disponible' => true,
        ]);

        $response = $this->getJson('/api/libros');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    /**
     * Prueba 2: POST /api/libros con body válido -> Crear (201)
     */
    public function test_02_crear_libro_con_body_valido(): void
    {
        $response = $this->postJson('/api/libros', [
            'titulo' => 'Rayuela',
            'autor' => 'Julio Cortázar',
            'anio_publicacion' => 1963,
            'disponible' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'mensaje' => 'Libro creado exitosamente',
                     'datos' => [
                         'titulo' => 'Rayuela',
                         'autor' => 'Julio Cortázar',
                         'anio_publicacion' => 1963,
                         'disponible' => true,
                     ]
                 ]);
    }

    /**
     * Prueba 3: GET /api/libros/{id} con un ID que existe -> Ver detalle (200)
     */
    public function test_03_ver_detalle_libro_existente(): void
    {
        $libro = Libro::create([
            'titulo' => 'Ficciones',
            'autor' => 'Jorge Luis Borges',
            'anio_publicacion' => 1944,
            'disponible' => true,
        ]);

        $response = $this->getJson("/api/libros/{$libro->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $libro->id,
                     'titulo' => 'Ficciones',
                 ]);
    }

    /**
     * Prueba 4: GET /api/libros/999 (ID inexistente) -> Error recurso no encontrado (404)
     */
    public function test_04_error_recurso_no_encontrado_404(): void
    {
        $response = $this->getJson('/api/libros/999');

        $response->assertStatus(404);
    }

    /**
     * Prueba 5: POST /api/libros con body mal formado -> Error de formato (400)
     */
    public function test_05_error_formato_json_roto_400(): void
    {
        $response = $this->call(
            'POST',
            '/api/libros',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            '{"titulo": "mal formado",'
        );

        $response->assertStatus(400)
                 ->assertJson([
                     'mensaje' => 'Error de formato: JSON mal formado',
                 ]);
    }

    /**
     * Prueba 6: POST /api/libros con {"titulo": ""} -> Error de validación (422)
     */
    public function test_06_error_validacion_422(): void
    {
        $response = $this->postJson('/api/libros', [
            'titulo' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['titulo', 'autor']);
    }

    /**
     * Prueba 7: PATCH /api/libros/{id} con {"disponible": false} -> Actualizar parcialmente (200)
     */
    public function test_07_actualizar_parcialmente_200(): void
    {
        $libro = Libro::create([
            'titulo' => 'El Aleph',
            'autor' => 'Jorge Luis Borges',
            'anio_publicacion' => 1949,
            'disponible' => true,
        ]);

        $response = $this->patchJson("/api/libros/{$libro->id}", [
            'disponible' => false,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Libro actualizado exitosamente',
                     'datos' => [
                         'disponible' => false,
                     ]
                 ]);

        $this->assertDatabaseHas('libros', [
            'id' => $libro->id,
            'disponible' => false,
        ]);
    }

    /**
     * Prueba 8: DELETE /api/libros/{id} -> Borrar (200)
     */
    public function test_08_borrar_libro_200(): void
    {
        $libro = Libro::create([
            'titulo' => 'Sobre héroes y tumbas',
            'autor' => 'Ernesto Sabato',
            'anio_publicacion' => 1961,
            'disponible' => true,
        ]);

        $response = $this->deleteJson("/api/libros/{$libro->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'El libro fue eliminado del sistema',
                 ]);

        $this->assertDatabaseMissing('libros', [
            'id' => $libro->id,
        ]);
    }

    /**
     * Prueba 9: GET /api/libros/{id} del mismo ID que se borró -> Confirmar que ya no existe (404)
     */
    public function test_09_confirmar_inexistencia_post_delete_404(): void
    {
        $libro = Libro::create([
            'titulo' => 'La tregua',
            'autor' => 'Mario Benedetti',
            'anio_publicacion' => 1960,
            'disponible' => true,
        ]);

        $id = $libro->id;

        $this->deleteJson("/api/libros/{$id}")->assertStatus(200);

        $response = $this->getJson("/api/libros/{$id}");
        $response->assertStatus(404);
    }
}
