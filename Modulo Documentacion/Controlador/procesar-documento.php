<?php
/*
 * Endpoint AJAX exclusivo del rol Administrador: procesa las acciones
 * del CRUD de documentos (crear, actualizar, suspender, reactivar).
 * Consumido por cargar-documento.js vía fetch(). Devuelve JSON.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/DocumentoModelo.php';

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
        $archivoUrl = trim($_POST['archivo_url'] ?? '');
        $idCategoria = isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0;

        if ($titulo === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'El título es obligatorio.']);
            exit;
        }
        if ($archivoUrl === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe indicar la URL o ruta del documento.']);
            exit;
        }
        if ($idCategoria <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar una categoría.']);
            exit;
        }

        $documentoModelo->crear([
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'archivo_url' => $archivoUrl,
            'id_categoria' => $idCategoria,
            'id_usuario_carga' => (int) $_SESSION['id_usuario'],
        ]);

        echo json_encode(['exito' => true, 'mensaje' => 'Documento cargado correctamente.']);
        break;

    case 'actualizar':
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $archivoUrl = trim($_POST['archivo_url'] ?? '');
        $idCategoria = isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0;

        if ($titulo === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'El título es obligatorio.']);
            exit;
        }
        if ($archivoUrl === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe indicar la URL o ruta del documento.']);
            exit;
        }
        if ($idCategoria <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar una categoría.']);
            exit;
        }

        $ok = $documentoModelo->actualizar($idDocumento, [
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'archivo_url' => $archivoUrl,
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
