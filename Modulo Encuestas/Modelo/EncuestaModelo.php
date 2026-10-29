<?php
/*
 * Encuesta de satisfacción por documento.
 * Las respuestas son anónimas: no se guarda ningún dato personal del paciente.
 */

require_once __DIR__ . '/../../Servicios Comunes/Conexion BD/conexion.php';

class EncuestaModelo
{
    /** Valores permitidos para la pregunta de utilidad (coinciden con el ENUM de la tabla). */
    public const OPCIONES_UTILIDAD = ['muy_util', 'si', 'regular', 'no', 'para_nada'];

    public const LARGO_MAXIMO_COMENTARIO = 500;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtenerConexion();
    }

    /**
     * Guarda una respuesta de encuesta.
     *
     * @return int id de la encuesta registrada
     */
    public function guardar(int $idDocumento, int $calificacionAcceso, string $utilidad, ?string $comentario): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO encuesta_documento (id_documento, calificacion_acceso, utilidad, comentario)
             VALUES (:id_documento, :calificacion, :utilidad, :comentario)'
        );
        $consulta->bindValue(':id_documento', $idDocumento, PDO::PARAM_INT);
        $consulta->bindValue(':calificacion', $calificacionAcceso, PDO::PARAM_INT);
        $consulta->bindValue(':utilidad', $utilidad, PDO::PARAM_STR);
        $consulta->bindValue(':comentario', $comentario, $comentario === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }

    // ------------------------------------------------------------------
    // Resultados (dashboard). Todas las consultas aceptan un id de documento
    // opcional: null = todos los documentos.
    // ------------------------------------------------------------------

    /** Condición WHERE opcional por documento. */
    private function filtroDocumento(?int $idDocumento): string
    {
        return $idDocumento === null ? '' : ' WHERE id_documento = :id_documento';
    }

    private function vincularDocumento(PDOStatement $consulta, ?int $idDocumento): void
    {
        if ($idDocumento !== null) {
            $consulta->bindValue(':id_documento', $idDocumento, PDO::PARAM_INT);
        }
    }

    /**
     * Totales: cantidad de respuestas, promedio de la calificación (null si no hay)
     * y cantidad de respuestas que consideran la información útil (muy útil + sí).
     */
    public function obtenerResumen(?int $idDocumento = null): array
    {
        $consulta = $this->conexion->prepare(
            "SELECT COUNT(*) AS total,
                    AVG(calificacion_acceso) AS promedio,
                    COALESCE(SUM(CASE WHEN utilidad IN ('muy_util', 'si') THEN 1 ELSE 0 END), 0) AS utiles
             FROM encuesta_documento" . $this->filtroDocumento($idDocumento)
        );
        $this->vincularDocumento($consulta, $idDocumento);
        $consulta->execute();
        $fila = $consulta->fetch();

        return [
            'total' => (int) $fila['total'],
            'promedio' => $fila['promedio'] === null ? null : (float) $fila['promedio'],
            'utiles' => (int) $fila['utiles'],
        ];
    }

    /** Cantidad de respuestas por calificación: [1 => n, 2 => n, ... 5 => n]. */
    public function obtenerDistribucionCalificacion(?int $idDocumento = null): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT calificacion_acceso, COUNT(*) AS cantidad FROM encuesta_documento'
            . $this->filtroDocumento($idDocumento) . ' GROUP BY calificacion_acceso'
        );
        $this->vincularDocumento($consulta, $idDocumento);
        $consulta->execute();

        $distribucion = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($consulta->fetchAll() as $fila) {
            $distribucion[(int) $fila['calificacion_acceso']] = (int) $fila['cantidad'];
        }
        return $distribucion;
    }

    /** Cantidad de respuestas por opción de utilidad (todas las opciones, aunque sean 0). */
    public function obtenerDistribucionUtilidad(?int $idDocumento = null): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT utilidad, COUNT(*) AS cantidad FROM encuesta_documento'
            . $this->filtroDocumento($idDocumento) . ' GROUP BY utilidad'
        );
        $this->vincularDocumento($consulta, $idDocumento);
        $consulta->execute();

        $distribucion = array_fill_keys(self::OPCIONES_UTILIDAD, 0);
        foreach ($consulta->fetchAll() as $fila) {
            if (array_key_exists($fila['utilidad'], $distribucion)) {
                $distribucion[$fila['utilidad']] = (int) $fila['cantidad'];
            }
        }
        return $distribucion;
    }

    /** Resultados agrupados por documento (solo los que tienen respuestas), con más respuestas primero. */
    public function obtenerResumenPorDocumento(): array
    {
        $consulta = $this->conexion->prepare(
            "SELECT d.id_documento, d.titulo, d.activo, c.nombre_categoria,
                    COUNT(*) AS total,
                    AVG(e.calificacion_acceso) AS promedio,
                    SUM(CASE WHEN e.utilidad IN ('muy_util', 'si') THEN 1 ELSE 0 END) AS utiles
             FROM encuesta_documento e
             INNER JOIN documento d ON d.id_documento = e.id_documento
             INNER JOIN categoria c ON c.id_categoria = d.id_categoria
             GROUP BY d.id_documento, d.titulo, d.activo, c.nombre_categoria
             ORDER BY total DESC, d.titulo"
        );
        $consulta->execute();
        return $consulta->fetchAll();
    }

    /** Últimos comentarios escritos por los pacientes (solo respuestas con comentario). */
    public function obtenerComentariosRecientes(?int $idDocumento = null, int $limite = 10): array
    {
        $condicion = ' WHERE e.comentario IS NOT NULL';
        if ($idDocumento !== null) {
            $condicion .= ' AND e.id_documento = :id_documento';
        }
        $consulta = $this->conexion->prepare(
            'SELECT e.comentario, e.calificacion_acceso, e.utilidad, e.fecha_hora, d.titulo
             FROM encuesta_documento e
             INNER JOIN documento d ON d.id_documento = e.id_documento'
            . $condicion . ' ORDER BY e.id_encuesta DESC LIMIT :limite'
        );
        $this->vincularDocumento($consulta, $idDocumento);
        $consulta->bindValue(':limite', $limite, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll();
    }
}
