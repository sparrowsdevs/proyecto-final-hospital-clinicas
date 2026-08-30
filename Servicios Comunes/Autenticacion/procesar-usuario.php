<?php


declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/UsuarioModelo.php';

$auth = new AuthController();

// Solo Administrador puede llegar hasta acá, y solo vía POST
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
$idUsuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;


if ($accion !== 'crear' && $idUsuario <= 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario inválido.']);
    exit;
}

// Protección: un Administrador no puede suspenderse ni desactivarse a sí mismo
$esSuMismaCuenta = $idUsuario === (int) ($_SESSION['id_usuario'] ?? 0);

$usuarioModelo = new UsuarioModelo();

switch ($accion) {
    case 'suspender':
        if ($esSuMismaCuenta) {
            echo json_encode(['exito' => false, 'mensaje' => 'No puede suspender su propia cuenta.']);
            exit;
        }
        $ok = $usuarioModelo->suspender($idUsuario);
        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Usuario suspendido correctamente.' : 'No se pudo suspender al usuario.',
        ]);
        break;

    case 'reactivar':
        $ok = $usuarioModelo->reactivar($idUsuario);
        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Usuario reactivado correctamente.' : 'No se pudo reactivar al usuario.',
        ]);
        break;

    case 'actualizar':
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $activo = isset($_POST['activo']) && $_POST['activo'] === '1';
        $idRol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;

        if ($nombre === '' || $apellido === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'Nombre y apellido son obligatorios.']);
            exit;
        }
        if ($idRol <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar un rol para el usuario.']);
            exit;
        }

        // Protección: no permitir que se autodesactive editando el estado desde el formulario
        if ($esSuMismaCuenta && !$activo) {
            echo json_encode(['exito' => false, 'mensaje' => 'No puede desactivar su propia cuenta.']);
            exit;
        }

        // Protección: no permitir que un Administrador se cambie su propio rol
        // (evita que se quite sus propios privilegios por error)
        if ($esSuMismaCuenta) {
            $rolesActuales = $usuarioModelo->obtenerRoles($idUsuario);
            $idsRolesActuales = array_column($rolesActuales, 'id_rol');
            if (!in_array($idRol, $idsRolesActuales, true)) {
                echo json_encode(['exito' => false, 'mensaje' => 'No puede cambiar su propio rol.']);
                exit;
            }
        }

        $ok = $usuarioModelo->actualizar($idUsuario, [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'activo' => $activo,
            'contrasena' => $contrasena,
        ]);

        if ($ok) {
            $ok = $usuarioModelo->cambiarRol($idUsuario, $idRol);
        }

        echo json_encode([
            'exito' => $ok,
            'mensaje' => $ok ? 'Usuario actualizado correctamente.' : 'No se pudo actualizar al usuario.',
        ]);
        break;

    case 'crear':
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $idRol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;

        if (!preg_match('/^\d{7,8}$/', $cedula)) {
            echo json_encode(['exito' => false, 'mensaje' => 'La cédula debe tener 7 u 8 dígitos numéricos.']);
            exit;
        }
        if ($nombre === '' || $apellido === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'Nombre y apellido son obligatorios.']);
            exit;
        }
        if (strlen($contrasena) < 4) {
            echo json_encode(['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 4 caracteres.']);
            exit;
        }
        if ($idRol <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar un rol para el usuario.']);
            exit;
        }

        try {
            $nuevoIdUsuario = $usuarioModelo->crear([
                'cedula' => $cedula,
                'contrasena' => $contrasena,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email !== '' ? $email : null,
            ]);

            $usuarioModelo->asignarRol($nuevoIdUsuario, $idRol);

            echo json_encode(['exito' => true, 'mensaje' => 'Usuario creado correctamente.']);
        } catch (PDOException $e) {
            // Código 23000 = violación de restricción única (cédula o email duplicados)
            if ($e->getCode() === '23000') {
                echo json_encode(['exito' => false, 'mensaje' => 'Ya existe un usuario registrado con esa cédula o email.']);
            } else {
                error_log('Error al crear usuario: ' . $e->getMessage());
                echo json_encode(['exito' => false, 'mensaje' => 'Ocurrió un error al crear el usuario.']);
            }
        }
        break;

    default:
        echo json_encode(['exito' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}
