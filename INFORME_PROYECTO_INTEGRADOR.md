# Proyecto Integrador — API de Biblioteca

**Asignatura / Módulo:** Laboratorio de Programación Web / APIs REST con Laravel  
**Ubicación del Proyecto:** `/home/erikperez/Escritorio/Tareas 6-5/api-biblioteca`  
**Tecnologías:** PHP 8.5+, Laravel 12+, SQLite, Eloquent ORM, PHPUnit  

---

## Índice
1. [Fase 0 — Preparar el entorno](#fase-0--preparar-el-entorno)
2. [Fase 1 — El modelo y Eloquent](#fase-1--el-modelo-y-eloquent)
3. [Fase 2 — CRUD completo en el controlador](#fase-2--crud-completo-en-el-controlador)
4. [Fase 3 — Exponer las rutas](#fase-3--exponer-las-rutas)
5. [Fase 4 — Batería de pruebas (Thunder Client / Feature Tests)](#fase-4--bater%C3%ADa-de-pruebas)
6. [Fase 5 — Verificación conceptual de HTTPS](#fase-5--verificaci%C3%B3n-conceptual-de-https)
7. [Archivos y Colecciones Entregadas](#archivos-y-colecciones-entregadas)

---

## Fase 0 — Preparar el entorno

1. **Creación del proyecto nuevo con Composer:**
   ```bash
   cd "/home/erikperez/Escritorio/Tareas 6-5"
   composer create-project laravel/laravel api-biblioteca
   cd api-biblioteca
   ```
2. **Inicio del servidor de desarrollo:**
   ```bash
   php artisan serve
   ```
   *Acceso local:* `http://127.0.0.1:8000`  
   ✅ **Checkpoint verificado:** Pantalla de bienvenida de Laravel respondiendo correctamente con código HTTP 200.

---

## Fase 1 — El modelo y Eloquent

### 1.1 Creación de modelo, migración y controlador
Se ejecutó el comando artesanal:
```bash
php artisan make:model Libro -mc
```
Se generaron los siguientes archivos:
- **Modelo:** `app/Models/Libro.php`
- **Migración:** `database/migrations/xxxx_xx_xx_xxxxxx_create_libros_table.php`
- **Controlador:** `app/Http/Controllers/LibroController.php`

### 1.2 Definición de la estructura de la migración
En el archivo de migración, se definió el método `up()` con los tipos de datos requeridos:
```php
public function up(): void
{
    Schema::create('libros', function (Blueprint $table) {
        $table->id();
        $table->string('titulo');
        $table->string('autor');
        $table->integer('anio_publicacion')->nullable();
        $table->boolean('disponible')->default(true);
        $table->timestamps();
    });
}
```

### 1.3 Ejecución de la migración
```bash
php artisan migrate
```
✅ **Checkpoint verificado:** La tabla `libros` se creó correctamente con las columnas `id`, `titulo`, `autor`, `anio_publicacion`, `disponible`, `created_at` y `updated_at`.

### 1.4 Declaración de asignación masiva ($fillable)
En `app/Models/Libro.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    protected $fillable = ['titulo', 'autor', 'anio_publicacion', 'disponible'];
}
```

### 1.5 Verificación del mapeo Tabla ↔ Modelo con Tinker
Se probó la persistencia y lectura directamente en la consola interactiva `php artisan tinker`:
```php
$libro = new \App\Models\Libro();
$libro->titulo = "Cien años de soledad";
$libro->autor = "Gabriel García Márquez";
$libro->anio_publicacion = 1967;
$libro->save();

\App\Models\Libro::all();
```
✅ **Checkpoint verificado:** `Libro::all()` retornó el registro creado exitosamente con `id: 1`.

---

## Fase 2 — CRUD completo en el controlador

En `app/Http/Controllers/LibroController.php` se implementaron los 5 métodos del CRUD con códigos HTTP explícitos (200, 201, 404, etc.):

```php
<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    // 2.1 — index (listar todos)
    public function index()
    {
        return response()->json(Libro::all(), 200);
    }

    // 2.2 — show (ver uno)
    public function show($id)
    {
        $libro = Libro::findOrFail($id);
        return response()->json($libro, 200);
    }

    // 2.3 — store (crear)
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

    // 2.4 — update (editar, PUT/PATCH)
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

    // 2.5 — destroy (borrar)
    public function destroy($id)
    {
        $libro = Libro::findOrFail($id);
        $libro->delete();

        return response()->json([
            'mensaje' => 'El libro fue eliminado del sistema'
        ], 200);
    }
}
```

---

## Fase 3 — Exponer las rutas

En `routes/api.php`, se definieron las 6 rutas verbo a verbo (registradas bajo el prefijo `/api`):
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;

Route::get('/libros', [LibroController::class, 'index']);
Route::get('/libros/{id}', [LibroController::class, 'show']);
Route::post('/libros', [LibroController::class, 'store']);
Route::put('/libros/{id}', [LibroController::class, 'update']);
Route::patch('/libros/{id}', [LibroController::class, 'update']);
Route::delete('/libros/{id}', [LibroController::class, 'destroy']);
```

✅ **Checkpoint verificado con `php artisan route:list`:**
```
GET|HEAD   api/libros ............ LibroController@index
POST       api/libros ............ LibroController@store
GET|HEAD   api/libros/{id} ....... LibroController@show
PUT        api/libros/{id} ....... LibroController@update
PATCH      api/libros/{id} ....... LibroController@update
DELETE     api/libros/{id} ....... LibroController@destroy
```

---

## Fase 4 — Batería de pruebas

Se ejecutó la batería completa de 9 pruebas solicitadas tanto por HTTP directo como a través de la suite automatizada de Laravel (`tests/Feature/LibroApiTest.php`):

| # | Petición HTTP | Qué se prueba | Código Esperado | Código Obtenido | Resultado |
|---|---|---|:---:|:---:|:---:|
| 1 | `GET /api/libros` | Listar libros (vacío o con el de Tinker) | 200 OK | **200 OK** | ✅ Superada |
| 2 | `POST /api/libros` (body válido) | Crear un libro nuevo | 201 Created | **201 Created** | ✅ Superada |
| 3 | `GET /api/libros/{id}` (existente) | Ver el detalle de un libro | 200 OK | **200 OK** | ✅ Superada |
| 4 | `GET /api/libros/999` (inexistente) | Forzar error de recurso no encontrado | 404 Not Found | **404 Not Found** | ✅ Superada |
| 5 | `POST /api/libros` (JSON roto) | Forzar error de formato sintáctico | 400 Bad Request | **400 Bad Request** | ✅ Superada |
| 6 | `POST /api/libros` con `{"titulo": ""}` | Forzar error de validación de datos | 422 Unproc. Content | **422 Unproc. Content** | ✅ Superada |
| 7 | `PATCH /api/libros/{id}` con `{"disponible": false}` | Actualización parcial | 200 OK | **200 OK** | ✅ Superada |
| 8 | `DELETE /api/libros/{id}` | Eliminar recurso | 200 OK | **200 OK** | ✅ Superada |
| 9 | `GET /api/libros/{id}` (mismo ID borrado) | Confirmar que el recurso ya no existe | 404 Not Found | **404 Not Found** | ✅ Superada |

### 🎯 Análisis Didáctico: Diferenciación de códigos de error (400, 404, 422)

1. **400 Bad Request (Error de formato sintáctico):**
   - Ocurre cuando el servidor ni siquiera puede interpretar el contenido que envió el cliente. En este caso, el JSON está "roto" (falta una llave de cierre, comillas sin cerrar, etc.). El servidor rechaza la petición en la capa de transporte/parsing antes de tocar el modelo de negocio.
2. **404 Not Found (Recurso no encontrado):**
   - La petición es sintácticamente válida y entendible, pero el recurso identificado por la URI (en este caso el libro con ID 999 o el ID borrado) no existe en la base de datos. `Libro::findOrFail($id)` dispara una excepción `ModelNotFoundException` que Laravel traduce automáticamente a un código 404.
3. **422 Unprocessable Content (Error de validación semántica):**
   - El JSON es sintácticamente perfecto (bien formado), pero los valores no cumplen con las reglas de negocio definidas en `$request->validate()` (por ejemplo: `titulo` está vacío, no se envió `autor`, etc.). El servidor entendió qué se le pidió, pero no puede procesarlo debido a datos no válidos.

---

## Fase 5 — Verificación conceptual de HTTPS

### 1. Análisis de headers en la petición local (Thunder Client / HTTP)
En un cliente HTTP como Thunder Client o cURL en entorno local, la petición muestra headers como:
```http
POST /api/libros HTTP/1.1
Host: 127.0.0.1:8000
User-Agent: Thunder Client (https://www.thunderclient.com)
Accept: application/json
Content-Type: application/json
Content-Length: 95
```
Todo este contenido viaja en **texto plano (plaintext)** a través del socket de red local.

### 2. Protocolo utilizado en desarrollo
- Se utiliza **HTTP/1.1** plano sobre `http://` (puerto 8000), sin capa de cifrado TLS (Transport Layer Security).
- Esto es normal y suficiente en desarrollo local porque el tráfico nunca sale del kernel del sistema operativo (interfaz loopback `127.0.0.1`).

### 3. Comparación con sitios reales bajo HTTPS
Al ingresar a un sitio web de producción bajo HTTPS (por ejemplo, `https://google.com` o el portal educativo de la escuela) e inspeccionar el candado de seguridad del navegador:
- **Certificado Digital X.509:** Emitido a nombre del dominio específico.
- **Autoridad Certificadora (CA):** Entidad de confianza (ej. *Google Trust Services*, *Let's Encrypt*, *DigiCert*) que valida criptográficamente que el dominio pertenece a quien dice ser.
- **Período de validez:** Fecha exacta desde y hasta cuándo es válido el certificado.
- **Cifrado en tránsito:** Protocolo **TLS 1.3**, intercambio de claves Diffie-Hellman / ECDHE y cifrado simétrico mediante AES-GCM o ChaCha20-Poly1305.

Nuestra API local en `http://127.0.0.1:8000` o `http://api-biblioteca.test` no posee TLS ni certificado, por lo que no cuenta con esta capa de protección.

---

### 💬 Para Pensar: Pregunta Reflexiva de la Fase 5

> *Si esta API de libros estuviera en producción y agregáramos un sistema de préstamos con usuarios y contraseñas, ¿qué headers y qué garantías de TLS (de las tres que vimos: confidencialidad, integridad, autenticidad) se volverían críticas?*

#### Las 3 garantías de TLS en un entorno productivo con usuarios:

1. **Confidencialidad (Cifrado):**
   - **Por qué es crítica:** Al autenticarse, el usuario envía su correo y contraseña en el body del POST `/api/login`, y posteriormente un token de sesión (Bearer Token) en cada request. Sin TLS, cualquier persona o nodo intermediario en la red (redes Wi-Fi públicas, proxies, ISPs maliciosos) podría interceptar los paquetes mediante *sniffing* y robar las credenciales y datos personales en texto plano. TLS cifra el túnel de punta a punta.
2. **Integridad (No alteración):**
   - **Por qué es crítica:** Asegura que los datos no puedan ser modificados en tránsito mediante un ataque de *Man-in-the-Middle* (MitM). Si no hubiera integridad, un atacante podría alterar la respuesta de la API o cambiar el ID del usuario en un préstamo de libro para perjudicar a otro estudiante o asignarse libros indebidamente. Los algoritmos de hashing y MAC de TLS detectan de inmediato cualquier alteración en los bytes transmitidos.
3. **Autenticidad (Identidad verificada):**
   - **Por qué es crítica:** Evita la suplantación de identidad (*phishing* o *DNS spoofing*). Gracias al certificado digital firmado por una CA, la aplicación cliente (navegador, app móvil o frontend) tiene la certeza matemática de que se está comunicando con el servidor legítimo de la biblioteca y no con un servidor atacante que finge serlo para recolectar contraseñas.

#### Cabeceras HTTP (Headers) que se vuelven críticas en producción:

- `Strict-Transport-Security` (HSTS):
  ```http
  Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
  ```
  Obliga a los clientes a conectarse **exclusivamente por HTTPS** durante el tiempo especificado, bloqueando cualquier intento de degradación a HTTP no cifrado (*SSL Stripping*).
- `Authorization`:
  ```http
  Authorization: Bearer <token_seguro>
  ```
  Permite transmitir los tokens de acceso criptográficos (Sanctum/JWT) que deben viajar encriptados por TLS.
- `Set-Cookie` con directivas de seguridad:
  ```http
  Set-Cookie: session_id=...; Secure; HttpOnly; SameSite=Strict
  ```
  - `Secure`: Solo se transmite si la conexión es HTTPS.
  - `HttpOnly`: Impide el acceso al token desde JavaScript (protección contra XSS).
  - `SameSite=Strict`: Protege contra ataques de falsificación de petición en sitios cruzados (CSRF).
- `X-Content-Type-Options: nosniff`: Evita que el navegador intente adivinar el tipo MIME de la respuesta.
- `Content-Security-Policy` (CSP): Controla y restringe los orígenes desde donde se pueden cargar recursos.

---

## Archivos y Colecciones Entregadas

1. **Código fuente completo del proyecto:**
   - Proyecto ubicado en `/home/erikperez/Escritorio/Tareas 6-5/api-biblioteca`.
   - Modelo: `app/Models/Libro.php`.
   - Migración: `database/migrations/xxxx_xx_xx_xxxxxx_create_libros_table.php`.
   - Controlador: `app/Http/Controllers/LibroController.php`.
   - Rutas API: `routes/api.php`.
   - Middleware de validación de sintaxis: `app/Http/Middleware/ValidateJsonSyntax.php`.
2. **Colección Thunder Client lista para importar:**
   - Archivo: `thunder-collection_api-biblioteca.json` (incluye las 9 peticiones configuradas con URLs, métodos, headers y cuerpos de prueba).
3. **Tests de integración automatizados:**
   - Archivo: `tests/Feature/LibroApiTest.php` (100% de los 9 casos cubiertos y testeados con PHPUnit).
   - Comando para correrlos: `php artisan test`.
