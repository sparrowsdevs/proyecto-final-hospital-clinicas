# Módulo Documentación

Carga, clasificación y entrega de documentos informativos para pacientes (ayuno previo a un estudio, cuidados posteriores a una cirugía, etc.), con acceso del paciente mediante un código QR.

**Límite del módulo:** solo maneja material informativo en PDF. No almacena historias clínicas, diagnósticos ni datos médicos de ninguna persona.

## Estructura

| Carpeta o archivo                    | Función                                                                               |
| ------------------------------------ | ------------------------------------------------------------------------------------- |
| `Modelo/DocumentoModelo.php`         | Consultas sobre documentos y categorías.                                              |
| `Controlador/procesar-documento.php` | Endpoint AJAX del Administrador: crear, actualizar, suspender y reactivar documentos. |
| `Controlador/servir-documento.php`   | Única vía para ver o descargar un PDF.                                                |
| `Vista/panel-documentacion.php`      | Consulta de documentos y ventana del QR. Todos los usuarios con sesión.               |
| `Vista/cargar-documento.php`         | Gestión de documentos. Solo Administrador.                                            |
| `Vista/gestion-usuarios.php`         | Gestión de usuarios. Solo Administrador.                                              |
| `Vista/repositorio-publico.php`      | Lo que ve el paciente al escanear el QR. Sin sesión.                                  |
| `uploads/documentos/`                | PDF subidos. Ver su [README](uploads/README.md).                                      |

## Cómo funciona el QR

1. El panel obtiene de `AccesoPublico::urlRepositorio()` una URL del tipo `https://dominio/Modulo%20Documentacion/Vista/repositorio-publico.php?t=TOKEN`.
2. `qr-pacientes.js` y `qrcode.js` convierten esa URL en un código QR dentro del navegador. No se genera ni se guarda ninguna imagen en el servidor.
3. El paciente lo escanea sin iniciar sesión. `AccesoPublico::tokenValido()` compara el token con `hash_equals`.
4. Con token válido ve únicamente los documentos activos y puede abrirlos o descargarlos. El mismo token le permite responder la encuesta, y no habilita nada más.

La URL pública depende de `SIGSM_URL_BASE`. Si vale `localhost`, el celular no puede abrirla (ver `Servicios Comunes/README.md`).

## Alta de un documento

1. El Administrador completa el formulario de `cargar-documento.php` (título, descripción, categoría y archivo).
2. `procesar-documento.php` valida que el archivo sea realmente un PDF y que no pese más de 10 MB.
3. Guarda el archivo con un nombre generado en `uploads/documentos/` y registra en la base la ruta, la fecha y el usuario que lo cargó.

## Entrega de un PDF

`servir-documento.php` recibe `id`, `accion` (`ver` o `descargar`) y, en el caso del paciente, `t` (token). Entrega el archivo solo si:

- hay una sesión activa **o** un token válido;
- el documento existe y está activo (los suspendidos solo los ve el Administrador);
- el nombre de archivo guardado es el esperado, lo que evita recorrer directorios.

## Reglas

- **Borrado lógico.** Suspender un documento lo retira del repositorio público sin borrar el archivo ni la fila. Se puede reactivar.
- Las categorías son las 44 que carga el script de la base. Corresponden a servicios del hospital.
- Los permisos se controlan en cada vista con `protegerRuta()`.
