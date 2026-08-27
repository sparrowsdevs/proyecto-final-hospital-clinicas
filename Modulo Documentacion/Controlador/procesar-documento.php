<?php
/*
 * Endpoint AJAX exclusivo del rol Administrador: procesa las acciones
 * del CRUD de documentos (crear, actualizar, suspender, reactivar),
 * incluyendo la subida real del archivo PDF al servidor.
 * Consumido por cargar-documento.js vía fetch(). Devuelve JSON.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

// Carpeta física donde se guardan los PDFs (dentro de htdocs, accesible por URL)
const CARPETA_UPLOADS = __DIR__ . '/../uploads/documentos/';
const TAMANIO_MAXIMO_BYTES = 10 * 1024 * 1024; // 10 MB

$auth = new AuthController();

if (!$auth->sesionActiva() || !$auth->tieneRol('Administrador')) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

/**
 * Valida y mueve un archivo subido a la carpeta de uploads.
 * Verifica el tipo MIME real del archivo (no solo su extensión declarada),
 * para evitar que se suba un ejecutable disfrazado de PDF.
 */
function procesarArchivoSubido(array $archivo): array
{
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['exito' => false, 'mensaje' => 'Debe seleccionar un archivo PDF.', 'rutaRelativa' => null];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['exito' => false, 'mensaje' => 'Ocurrió un error al subir el archivo.', 'rutaRelativa' => null];
    }
    if ($archivo['size'] > TAMANIO_MAXIMO_BYTES) {
        return ['exito' => false, 'mensaje' => 'El archivo supera el tamaño máximo permitido (10 MB).', 'rutaRelativa' => null];
    }

    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($archivo['tmp_name']);

    if ($mimeReal !== 'application/pdf') {
        return ['exito' => false, 'mensaje' => 'El archivo debe ser un PDF válido.', 'rutaRelativa' => null];
    }

    if (!is_dir(CARPETA_UPLOADS)) {
        mkdir(CARPETA_UPLOADS, 0755, true);
    }

    // Nombre de archivo generado por el servidor 
    $nombreArchivo = 'doc_' . bin2hex(random_bytes(8)) . '.pdf';
    $destino = CARPETA_UPLOADS . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        return ['exito' => false, 'mensaje' => 'No se pudo guardar el archivo en el servidor.', 'rutaRelativa' => null];
    }

    // Ruta relativa a "Modulo Documentacion/", para armar el link desde cualquier vista
    return ['exito' => true, 'mensaje' => '', 'rutaRelativa' => 'uploads/documentos/' . $nombreArchivo];
}

$accion = $_POST['accion'] ?? '';
$idDocumento = isset($_POST['id_documento']) ? (int) $_POST['id_documento'] : 0;

if ($accion !== 'crear' && $idDocumento <= 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'Documento inválido.']);
    exit;
}

$documentoModelo = new DocumentoModelo();

switch ($accion) {
    case 'crear':
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idCategoria = isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0;

        if ($titulo === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'El título es obligatorio.']);
            exit;
        }
        if ($idCategoria <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar una categoría.']);
            exit;
        }

        $resultadoArchivo = procesarArchivoSubido($_FILES['archivo'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        if (!$resultadoArchivo['exito']) {
            echo json_encode(['exito' => false, 'mensaje' => $resultadoArchivo['mensaje']]);
            exit;
        }

        $documentoModelo->crear([
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'archivo_url' => $resultadoArchivo['rutaRelativa'],
            'id_categoria' => $idCategoria,
            'id_usuario_carga' => (int) $_SESSION['id_usuario'],
        ]);

        echo json_encode(['exito' => true, 'mensaje' => 'Documento cargado correctamente.']);
        break;

    case 'actualizar':
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idCategoria = isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0;

        if ($titulo === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'El título es obligatorio.']);
            exit;
        }
        if ($idCategoria <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar una categoría.']);
            exit;
        }

        $documentoActual = $documentoModelo->buscarPorId($idDocumento);
        if ($documentoActual === false) {
            echo json_encode(['exito' => false, 'mensaje' => 'El documento no existe.']);
            exit;
        }

        // Reemplazar el archivo es opcional al editar: si no suben uno nuevo,
        // se conserva el que ya estaba guardado.
        $rutaArchivo = $documentoActual['archivo_url'];
        $archivoSubido = $_FILES['archivo'] ?? ['error' => UPLOAD_ERR_NO_FILE];

        if ($archivoSubido['error'] !== UPLOAD_ERR_NO_FILE) {
            $resultadoArchivo = procesarArchivoSubido($archivoSubido);
            if (!$resultadoArchivo['exito']) {
                echo json_encode(['exito' => false, 'mensaje' => $resultadoArchivo['mensaje']]);
                exit;
            }

            // Borra el PDF anterior del disco, ya reemplazado por el nuevo
            $rutaFisicaAnterior = __DIR__ . '/../' . $documentoActual['archivo_url'];
            if (is_file($rutaFisicaAnterior)) {
                unlink($rutaFisicaAnterior);
            }

            $rutaArchivo = $resultadoArchivo['rutaRelativa'];
        }

        $ok = $documentoModelo->actualizar($idDocumento, [
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'archivo_url' => $rutaArchivo,
            'id_categoria' => $idCategoria,
        ]);

        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Documento actualizado correctamente.' : 'No se pudo actualizar el documento.',
        ]);
        break;

    case 'suspender':
        $ok = $documentoModelo->suspender($idDocumento);
        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Documento suspendido correctamente.' : 'No se pudo suspender el documento.',
        ]);
        break;

    case 'reactivar':
        $ok = $documentoModelo->reactivar($idDocumento);
        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Documento reactivado correctamente.' : 'No se pudo reactivar el documento.',
        ]);
        break;

    default:
        echo json_encode(['exito' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
        
}
