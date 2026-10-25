<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';

date_default_timezone_set('America/Montevideo');

/** Responde con JSON y corta la ejecución. */
function responder(bool $exito, string $mensaje, array $extra = [], int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode(array_merge(['exito' => $exito, 'mensaje' => $mensaje], $extra));
    exit;
}

$auth = new AuthController();

if (!$auth->sesionActiva()) {
    responder(false, 'Su sesión expiró. Vuelva a iniciar sesión.', [], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.', [], 405);
}

/** Convierte un campo en entero positivo; devuelve 0 si no es un número entero válido. */
function enteroEstricto(mixed $valor): int
{
    $valor = trim((string) $valor);
    return preg_match('/^[1-9]\d{0,9}$/', $valor) ? (int) $valor : 0;
}

/**
 * Campo opcional: cadena vacía = null (sin asignar). Si trae algo que no es
 * un entero positivo, devuelve 0 para que la validación posterior lo rechace.
 */
function enteroOpcional(mixed $valor): ?int
{
    return trim((string) $valor) === '' ? null : enteroEstricto($valor);
}

$accion = $_POST['accion'] ?? '';
$modelo = new TrasladoModelo();

try {
    switch ($accion) {

        // ------------------------------------------------------------------
        case 'buscar-paciente':
            $ci = preg_replace('/\D/', '', (string) ($_POST['ci_paciente'] ?? ''));
            if (!preg_match('/^\d{7,8}$/', $ci)) {
                responder(true, '', ['paciente' => null]);
            }
            $paciente = $modelo->buscarPaciente($ci);
            responder(true, '', ['paciente' => $paciente === false ? null : $paciente]);
            break;

        // ------------------------------------------------------------------
        case 'crear-ubicacion':
            $nombre = trim(preg_replace('/\s+/', ' ', (string) ($_POST['nombre'] ?? '')));
            $largo = mb_strlen($nombre, 'UTF-8');
            if ($largo < 3 || $largo > 150) {
                responder(false, 'El nombre de la ubicación debe tener entre 3 y 150 caracteres.');
            }
            $ubicacion = $modelo->crearUbicacion($nombre);
            responder(true, $ubicacion['existia']
                ? 'Esa ubicación ya existía y quedó seleccionada.'
                : 'Ubicación agregada correctamente.', ['ubicacion' => $ubicacion]);
            break;

        // ------------------------------------------------------------------
        case 'crear':
            // --- Paciente
            $ci = preg_replace('/\D/', '', (string) ($_POST['ci_paciente'] ?? ''));
            $patronNombre = '/^[\p{L}][\p{L}\' .-]{1,99}$/u';
            $nombre = trim(preg_replace('/\s+/', ' ', (string) ($_POST['nombre_paciente'] ?? '')));
            $apellido = trim(preg_replace('/\s+/', ' ', (string) ($_POST['apellido_paciente'] ?? '')));

            if (!preg_match('/^\d{7,8}$/', $ci)) {
                responder(false, 'La cédula debe tener 7 u 8 dígitos (sin puntos ni guión).');
            }
            if (!preg_match($patronNombre, $nombre)) {
                responder(false, 'Ingrese un nombre válido para el paciente.');
            }
            if (!preg_match($patronNombre, $apellido)) {
                responder(false, 'Ingrese un apellido válido para el paciente.');
            }

            // --- Traslado
            $idTipoElemento = enteroEstricto($_POST['id_tipo_elemento'] ?? '');
            $idOrigen = enteroEstricto($_POST['id_origen'] ?? '');
            $idDestino = enteroEstricto($_POST['id_destino'] ?? '');

            if (!$modelo->existeRegistro('tipo_elemento', 'id_tipo_elemento', $idTipoElemento)) {
                responder(false, 'Seleccione el elemento a trasladar.');
            }
            if (!$modelo->existeRegistro('ubicacion', 'id_ubicacion', $idOrigen)) {
                responder(false, 'Seleccione una ubicación de origen válida.');
            }
            if (!$modelo->existeRegistro('ubicacion', 'id_ubicacion', $idDestino)) {
                responder(false, 'Seleccione una ubicación de destino válida.');
            }
            if ($idOrigen === $idDestino) {
                responder(false, 'El origen y el destino no pueden ser la misma ubicación.');
            }

            // Fecha y hora prevista (datetime-local: 2026-10-25T14:30)
            $fecha = DateTime::createFromFormat('Y-m-d\TH:i', (string) ($_POST['hora_prevista'] ?? ''));
            $erroresFecha = DateTime::getLastErrors();
            if ($fecha === false || ($erroresFecha && ($erroresFecha['warning_count'] || $erroresFecha['error_count']))) {
                responder(false, 'Indique la fecha y hora prevista del traslado.');
            }
            if ($fecha->getTimestamp() < time() - 5 * 60) {
                responder(false, 'La fecha y hora prevista no puede estar en el pasado.');
            }

            // --- Asignación (opcional)
            $idVehiculo = enteroOpcional($_POST['id_vehiculo'] ?? '');
            $idChofer = enteroOpcional($_POST['id_chofer'] ?? '');
            $idEnfermero = enteroOpcional($_POST['id_enfermero'] ?? '');

            if ($idVehiculo !== null) {
                if (!$modelo->existeRegistro('vehiculo', 'id_vehiculo', $idVehiculo)) {
                    responder(false, 'El vehículo seleccionado no existe.');
                }
                if (!$modelo->esVehiculoCompatible($idVehiculo, $idTipoElemento)) {
                    responder(false, 'El vehículo seleccionado no es compatible con el elemento a trasladar.');
                }
            }
            foreach ([[$idChofer, 'chofer'], [$idEnfermero, 'personal acompañante']] as [$idUsuario, $rol]) {
                if ($idUsuario !== null && !$modelo->existeRegistro('usuario', 'id_usuario', $idUsuario)) {
                    responder(false, "El {$rol} seleccionado no es un usuario activo.");
                }
            }
            if ($idChofer !== null && $idChofer === $idEnfermero) {
                responder(false, 'El chofer y el personal acompañante deben ser personas distintas.');
            }

            $resultado = $modelo->crearTraslado([
                'ci' => $ci,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'id_tipo_elemento' => $idTipoElemento,
                'id_origen' => $idOrigen,
                'id_destino' => $idDestino,
                'hora_prevista' => $fecha->format('Y-m-d H:i:s'),
                'id_vehiculo' => $idVehiculo,
                'id_chofer' => $idChofer,
                'id_enfermero' => $idEnfermero,
                'id_usuario' => (int) $_SESSION['id_usuario'],
            ]);

            responder(true, 'Traslado registrado correctamente.', [
                'id_traslado' => $resultado['id_traslado'],
                'codigo' => sprintf('TR-%05d', $resultado['id_traslado']),
                'estado' => $resultado['estado'],
            ]);
            break;

        // ------------------------------------------------------------------
        default:
            responder(false, 'Acción no reconocida.');
    }
} catch (RuntimeException $error) {
    // Errores de configuración propios del sistema (p. ej. falta un catálogo)
    error_log('[S.I.G.S.M. traslados] ' . $error->getMessage());
    responder(false, $error->getMessage(), [], 500);
} catch (Throwable $error) {
    error_log('[S.I.G.S.M. traslados] ' . $error->getMessage());
    responder(false, 'No se pudo completar la operación. Intente nuevamente.', [], 500);
}
