<?php


require_once __DIR__ . '/../Conexion BD/conexion.php';

class UsuarioModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = conexion::obtenerConexion();
    }
    
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

    
    public function obtenerRolesDisponibles(): array
    {
        $sql = 'SELECT id_rol, nombre_rol FROM rol ORDER BY nombre_rol';

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll();
    }

   
    public function asignarRol(int $idUsuario, int $idRol): bool
    {
        $sql = 'INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':id_rol', $idRol, PDO::PARAM_INT);

        return $consulta->execute();
    }

  
    public function listarTodos(): array
    {
        $sql = "SELECT
                    u.id_usuario, u.cedula, u.nombre, u.apellido, u.email, u.activo,
                    MIN(ur.id_rol) AS id_rol,
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
     * Cambia el rol de un usuario: elimina su(s) asignación(es) actual(es)
     * en usuario_rol y asigna el nuevo rol. Uso exclusivo del rol Administrador.
     * Envuelto en transacción para que no quede el usuario sin rol si algo falla.
     *
     * @param int $idUsuario
     * @param int $idRol
     * @return bool
     */
    public function cambiarRol(int $idUsuario, int $idRol): bool
    {
        try {
            $this->conexion->beginTransaction();

            $eliminar = $this->conexion->prepare('DELETE FROM usuario_rol WHERE id_usuario = :id_usuario');
            $eliminar->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $eliminar->execute();

            $insertar = $this->conexion->prepare('INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)');
            $insertar->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $insertar->bindValue(':id_rol', $idRol, PDO::PARAM_INT);
            $insertar->execute();

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log('Error al cambiar rol de usuario: ' . $e->getMessage());
            return false;
        }
    }

    
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