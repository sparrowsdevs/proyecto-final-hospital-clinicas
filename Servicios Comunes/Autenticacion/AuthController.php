<?php
/*
 * Controlador transversal de autenticación: maneja el inicio/cierre de sesión
 * y la verificación de roles. Punto de entrada único al sistema para todos
 * los módulos (Documentación, Ambulancias, Encuestas).
 */

require_once __DIR__ . '/UsuarioModelo.php';

class AuthController
{
    private UsuarioModelo $usuarioModelo;

    public function __construct()
    {
        // Toda pantalla que use este controlador debe iniciar sesión PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->usuarioModelo = new UsuarioModelo();
    }

    /**
     * Procesa el intento de login (cédula + contraseña).
     * Si es exitoso, guarda los datos del usuario y sus roles en $_SESSION.
     * Nunca guarda la contraseña en sesión.
     */
    public function iniciarSesion(string $cedula, string $contrasena): array
    {
        $usuario = $this->usuarioModelo->autenticar($cedula, $contrasena);

        if ($usuario === false) {
            return [
                'exito'   => false,
                'mensaje' => 'Cédula o contraseña incorrectas, o usuario inactivo.',
            ];
        }

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['cedula']     = $usuario['cedula'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['roles']      = $usuario['roles']; // Array completo de roles del usuario

        return [
            'exito'   => true,
            'mensaje' => 'Inicio de sesión exitoso.',
        ];
    }

    /**
     * Cierra la sesión activa, eliminando todos los datos guardados.
     */
    public function cerrarSesion(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametrosCookie = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametrosCookie['path'],
                $parametrosCookie['domain'],
                $parametrosCookie['secure'],
                $parametrosCookie['httponly']
            );
        }

        session_destroy();
    }

   
    public function protegerRuta(?string $rolRequerido = null, string $rutaLogin = '../../index.php'): void
    {
        // Cabeceras anti-caché: fuerzan al navegador a no guardar esta página
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

        $autorizado = $rolRequerido === null
            ? $this->sesionActiva()
            : $this->sesionActiva() && $this->tieneRol($rolRequerido);

        if (!$autorizado) {
            header('Location: ' . $rutaLogin);
            exit;
        }
    }

    /**
     * Verifica si existe una sesión activa (usuario logueado).
     * @return bool
     */
    public function sesionActiva(): bool
    {
        return isset($_SESSION['id_usuario']);
    }

    
    public function tieneRol(string $nombreRol): bool
    {
        if (!$this->sesionActiva() || !isset($_SESSION['roles'])) {
            return false;
        }

        foreach ($_SESSION['roles'] as $rol) {
            if (strcasecmp($rol['nombre_rol'], $nombreRol) === 0) {
                return true;

            }
        }

        return false;
    }
}