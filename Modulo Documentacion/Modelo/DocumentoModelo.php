<?php
/**
 * DocumentoModelo.php
 * Modulo Documentacion / Modelo
 *
 * Proyecto S.I.G.S.M. - Hospital de Clínicas
 * Sparrows Devs
 *
 * CRUD de documentos: alta, listado, edición y borrado lógico
 * (suspender/reactivar). Uso exclusivo del rol Administrador.
 */

require_once __DIR__ . '/../../Servicios Comunes/Conexion BD/conexion.php';

class DocumentoModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtenerConexion();
    }

    /**
     * Lista todos los documentos activos, con el nombre de su categoría
     * y el nombre de quien lo cargó, para la tabla de administración.
     *
     * @return array
     */
    public function listarActivos(): array
    {
        $sql = "SELECT
                    d.id_documento, d.titulo, d.descripcion, d.archivo_url,
                    d.fecha_carga, d.fecha_modificacion, d.activo,
                    d.id_categoria, c.nombre_categoria,
                    CONCAT(u.nombre, ' ', u.apellido) AS cargado_por
                FROM documento d
                INNER JOIN categoria c ON c.id_categoria = d.id_categoria
                INNER JOIN usuario u ON u.id_usuario = d.id_usuario_carga
                WHERE d.activo = TRUE
                ORDER BY d.fecha_carga DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Lista TODOS los documentos (activos e inactivos), para el panel de
     * administración donde también se ven los suspendidos.
     *
     * @return array
     */
    public function listarTodos(): array
    {
        $sql = "SELECT
                    d.id_documento, d.titulo, d.descripcion, d.archivo_url,
                    d.fecha_carga, d.fecha_modificacion, d.activo,
                    d.id_categoria, c.nombre_categoria,
                    CONCAT(u.nombre, ' ', u.apellido) AS cargado_por
                FROM documento d
                INNER JOIN categoria c ON c.id_categoria = d.id_categoria
                INNER JOIN usuario u ON u.id_usuario = d.id_usuario_carga
                ORDER BY d.fecha_carga DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Busca un documento por su ID (para precargar el formulario de edición).
     *
     * @param int $idDocumento
     * @return array|false
     */
    public function buscarPorId(int $idDocumento): array|false
    {
        $sql = 'SELECT id_documento, titulo, descripcion, archivo_url,
                       id_categoria, activo
                FROM documento
                WHERE id_documento = :id_documento';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':id_documento', $idDocumento, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch();
    }

    /**
     * Crea un nuevo documento.
     *
     * @param array $datos Debe incluir: titulo, descripcion, archivo_url,
     *                      id_categoria, id_usuario_carga.
     * @return int ID del documento recién creado.
     */
    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO documento (titulo, descripcion, archivo_url, id_categoria, id_usuario_carga, activo)
                VALUES (:titulo, :descripcion, :archivo_url, :id_categoria, :id_usuario_carga, TRUE)';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $datos['descripcion'] ?? null, PDO::PARAM_STR);
        $consulta->bindValue(':archivo_url', $datos['archivo_url'], PDO::PARAM_STR);
        $consulta->bindValue(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario_carga', $datos['id_usuario_carga'], PDO::PARAM_INT);
        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Actualiza los datos de un documento existente.
     * Registra automáticamente la fecha de modificación.
     *
     * @param int $idDocumento
     * @param array $datos Debe incluir: titulo, descripcion, archivo_url, id_categoria.
     * @return bool
     */
    public function actualizar(int $idDocumento, array $datos): bool
    {
        $sql = 'UPDATE documento
                SET titulo = :titulo,
                    descripcion = :descripcion,
                    archivo_url = :archivo_url,
                    id_categoria = :id_categoria,
                    fecha_modificacion = CURRENT_DATE()
                WHERE id_documento = :id_documento';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $datos['descripcion'] ?? null, PDO::PARAM_STR);
        $consulta->bindValue(':archivo_url', $datos['archivo_url'], PDO::PARAM_STR);
        $consulta->bindValue(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
        $consulta->bindValue(':id_documento', $idDocumento, PDO::PARAM_INT);

        return $consulta->execute();
    }

    /**
     * Suspende (borrado lógico) un documento: activo = false.
     */
    public function suspender(int $idDocumento): bool
    {
        return $this->actualizarEstado($idDocumento, false);
    }

    /**
     * Reactiva un documento previamente suspendido: activo = true.
     */
    public function reactivar(int $idDocumento): bool
    {
        return $this->actualizarEstado($idDocumento, true);
    }

    /**
     * Elimina físicamente un documento. Uso restringido: preferir siempre
     * suspender() (borrado lógico); esto solo se ofrece para el caso de
     * un documento cargado por error y que nunca debió existir.
     */
    public function eliminarFisico(int $idDocumento): bool
    {
        $sql = 'DELETE FROM documento WHERE id_documento = :id_documento';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindParam(':id_documento', $idDocumento, PDO::PARAM_INT);

        return $consulta->execute();
    }

    /**
     * Obtiene el catálogo completo de categorías, para poblar el selector
     * del formulario de carga/edición de documentos.
     *
     * @return array Lista de categorías (id_categoria, nombre_categoria).
     */
    public function obtenerCategorias(): array
    {
        $sql = 'SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria';

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Método privado auxiliar: actualiza el campo activo de un documento.
     */
    private function actualizarEstado(int $idDocumento, bool $estado): bool
    {
        $sql = 'UPDATE documento SET activo = :activo WHERE id_documento = :id_documento';

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':activo', $estado, PDO::PARAM_BOOL);
        $consulta->bindParam(':id_documento', $idDocumento, PDO::PARAM_INT);

        return $consulta->execute();
    }
}
