<?php
/*
 * Modelo transversal de Usuario: autenticación por Cédula de Identidad,
 * gestión de roles (N:M) y administración de estado (activo/inactivo).
 * Reutilizable por los módulos Documentación, Ambulancias y Encuestas.
 */

require_once __DIR__ . '/../Conexion BD/conexion.php';

class UsuarioModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = conexion::obtenerConexion();
    }

    /**
     * Busca un usuario por su Cédula de Identidad.
     * No filtra por estado activo/inactivo: esa validación la hace autenticar().
     *
     * @param string $cedula
     * @return array|false Datos del usuario, o false si no existe.
     */
    public function buscarPorCedula(string $cedula): array|false
    {
        $sql = 'SELECT id_usuario, cedula, contrasena, nombre, apellido, email, activo
                FROM usuario
                WHERE cedula = :cedula';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':cedula', $cedula, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch();
    }

    /**
     * Autentica a un usuario mediante cédula + contraseña.
     * Verifica: existencia, estado activo y coincidencia del hash de contraseña.
     * Si la autenticación es exitosa, incluye los roles del usuario en el resultado.
     */
    public function autenticar(string $cedula, string $contrasena): array|false
    {
        $usuario = $this->buscarPorCedula($cedula);

        if ($usuario === false) {
            return false; // Cédula no registrada
        }

        if (!$usuario['activo']) {
            return false; // Usuario suspendido/inactivo
        }

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            return false; // Contraseña incorrecta
        }

        // No devolver el hash de contraseña hacia afuera del modelo
        unset($usuario['contrasena']);

        $usuario['roles'] = $this->obtenerRoles((int) $usuario['id_usuario']);

        return $usuario;
    }

    /**
     * Obtiene los roles asignados a un usuario (join usuario_rol + rol).
     *
     */
    public function obtenerRoles(int $idUsuario): array
    {
        $sql = 'SELECT r.id_rol, r.nombre_rol, r.descripcion, ur.fecha_asignacion
                FROM usuario_rol ur
                INNER JOIN rol r ON r.id_rol = ur.id_rol
                WHERE ur.id_usuario = :id_usuario';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Crea un nuevo usuario en el sistema (acción exclusiva del rol Administrador).
     * La contraseña se hashea internamente antes de guardarla.
     */
    public function crear(array $datosUsuario): int
    {
        $sql = 'INSERT INTO usuario (cedula, contrasena, nombre, apellido, email, activo)
                VALUES (:cedula, :contrasena, :nombre, :apellido, :email, :activo)';

        $contrasenaHasheada = password_hash($datosUsuario['contrasena'], PASSWORD_DEFAULT);
        $activo = true;

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':cedula', $datosUsuario['cedula'], PDO::PARAM_STR);
        $consulta->bindValue(':contrasena', $contrasenaHasheada, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $datosUsuario['nombre'], PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $datosUsuario['apellido'], PDO::PARAM_STR);
        $consulta->bindValue(':email', $datosUsuario['email'] ?? null, PDO::PARAM_STR);
        $consulta->bindValue(':activo', $activo, PDO::PARAM_BOOL);
        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Lista todos los usuarios del sistema con su rol principal, para la
     * pantalla de Gestión de Usuarios (uso exclusivo Administrador).
     * Si un usuario tuviera más de un rol asignado, se muestran todos
     * separados por coma (hoy el sistema asigna un único rol por usuario).
     *
     * @return array Lista de usuarios con: id_usuario, cedula, nombre,
     *                apellido, email, activo, roles (string).
     */
    public function listarTodos(): array
    {
        $sql = "SELECT
                    u.id_usuario, u.cedula, u.nombre, u.apellido, u.email, u.activo,
                    GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') AS roles
                FROM usuario u
                LEFT JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN rol r ON r.id_rol = ur.id_rol
                GROUP BY u.id_usuario, u.cedula, u.nombre, u.apellido, u.email, u.activo
                ORDER BY u.nombre, u.apellido";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Busca un usuario por su ID (para precargar el formulario de edición).
     *
     * @param int $idUsuario
     * @return array|false
     */
    public function buscarPorId(int $idUsuario): array|false
    {
        $sql = 'SELECT id_usuario, cedula, nombre, apellido, email, activo
                FROM usuario
                WHERE id_usuario = :id_usuario';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch();
    }

    /**
     * Actualiza los datos personales de un usuario (nombre, apellido, email,
     * estado activo/inactivo, y opcionalmente la contraseña).
     * Uso exclusivo del rol Administrador. No modifica el rol asignado.
     *
     * @param int $idUsuario
     * @param array $datos Debe incluir: nombre, apellido, email, activo (bool).
     *                      'contrasena' es opcional: si viene no vacía, se
     *                      hashea y se actualiza; si no, se deja la actual.
     * @return bool
     */
    public function actualizar(int $idUsuario, array $datos): bool
    {
        $campos = [
            'nombre = :nombre',
            'apellido = :apellido',
            'email = :email',
            'activo = :activo',
        ];

        $parametros = [
            ':id_usuario' => $idUsuario,
            ':nombre' => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':email' => $datos['email'] ?? null,
            ':activo' => $datos['activo'],
        ];

        // La contraseña solo se actualiza si el administrador cargó una nueva
        if (!empty($datos['contrasena'])) {
            $campos[] = 'contrasena = :contrasena';
            $parametros[':contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        }

        $sql = 'UPDATE usuario SET ' . implode(', ', $campos) . ' WHERE id_usuario = :id_usuario';

        $consulta = $this->conexion->prepare($sql);

        foreach ($parametros as $marcador => $valor) {
            $tipo = is_bool($valor) ? PDO::PARAM_BOOL : PDO::PARAM_STR;
            $consulta->bindValue($marcador, $valor, $tipo);
        }

        return $consulta->execute();
    }

    /**
     * Suspende (borrado lógico) a un usuario: activo = false.
     * Uso exclusivo del rol Administrador.
     */
    public function suspender(int $idUsuario): bool
    {
        return $this->actualizarEstado($idUsuario, false);
    }

    /**
     * Reactiva a un usuario previamente suspendido: activo = true.
     * Uso exclusivo del rol Administrador.

     */
    public function reactivar(int $idUsuario): bool
    {
        return $this->actualizarEstado($idUsuario, true);
    }

    /**
     * Método privado auxiliar: actualiza el campo activo de un usuario.
     * Evita duplicar la consulta SQL entre suspender() y reactivar().
     */
    private function actualizarEstado(int $idUsuario, bool $estado): bool
    {
        $sql = 'UPDATE usuario SET activo = :activo WHERE id_usuario = :id_usuario';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':activo', $estado, PDO::PARAM_BOOL);
        $consulta->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);

        return $consulta->execute();
    }
}