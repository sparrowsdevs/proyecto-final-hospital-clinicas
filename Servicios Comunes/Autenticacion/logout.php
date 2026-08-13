<?php
/**
 * Destruye la sesión activa del usuario (vía AuthController::cerrarSesion())
 * y lo redirige al login. Pensado para usarse como link/botón "Cerrar sesión"
 * desde cualquier panel del sistema.
 */

declare(strict_types=1);

require_once __DIR__ . '/AuthController.php';

$auth = new AuthController();
$auth->cerrarSesion();

header('Location: ../../index.php');
exit;