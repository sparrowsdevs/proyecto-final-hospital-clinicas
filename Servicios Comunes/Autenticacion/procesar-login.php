<?php
/**
 * Endpoint AJAX: recibe cédula + contraseña vía POST (FormData),
 * procesa el login mediante AuthController y devuelve el resultado en JSON.
 * Consumido por el fetch() del formulario en index.php.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/AuthController.php';

// Solo se acepta método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Método no permitido.',
    ]);
    exit;
}

$cedula     = trim($_POST['cedula'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

// Validación básica de campos vacíos antes de tocar la base de datos
if ($cedula === '' || $contrasena === '') {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Debe completar cédula y contraseña.',
    ]);
    exit;
}

$auth = new AuthController();
$resultado = $auth->iniciarSesion($cedula, $contrasena);

if (!$resultado['exito']) {
    echo json_encode([
        'exito'   => false,
        'mensaje' => $resultado['mensaje'],
    ]);
    exit;
}

// Determina a qué panel redirigir según el rol del usuario.
// Prioridad: Administrador > resto de roles (Médico, Enfermería, etc.).
// Ruta relativa a la raíz del proyecto, donde vive index.php.
$redireccion = 'Modulo Documentacion/Vista/panel-documentacion.html';

if ($auth->tieneRol('Administrador')) {
    $redireccion = 'Modulo Documentacion/Vista/panel-administrador.html';
}

echo json_encode([
    'exito'       => true,
    'mensaje'     => $resultado['mensaje'],
    'redireccion' => $redireccion,
]);