<?php

require_once __DIR__ . '/../../Servicios Comunes/Conexion BD/conexion.php';

/** Error de regla de negocio: se le informa al usuario, no es una falla del sistema. */
class ReglaDeNegocioException extends Exception
{
}

class TrasladoModelo
{
    // Nombres de los registros de catálogo que usa el formulario
    public const CANAL_FORMULARIO = 'App Interna';
    public const ESTADO_SOLICITADO = 'Solicitado';
    public const ESTADO_VEHICULO_ASIGNADO = 'Vehículo Asignado';
    public const ESTADO_EN_TRANSITO = 'En Tránsito';
    public const ESTADO_COMPLETADO = 'Completado';
    public const ESTADO_CANCELADO = 'Cancelado'; // borrado lógico

    /** Estados que ya no admiten cambios (Cancelado solo puede reactivarse). */
    private const ESTADOS_FINALES = [self::ESTADO_COMPLETADO, self::ESTADO_CANCELADO];

    /** Flujo permitido de estados desde el formulario de edición (cancelar es una acción aparte). */
    private const TRANSICIONES = [
        self::ESTADO_SOLICITADO => [self::ESTADO_VEHICULO_ASIGNADO],
        self::ESTADO_VEHICULO_ASIGNADO => [self::ESTADO_SOLICITADO, self::ESTADO_EN_TRANSITO],
        self::ESTADO_EN_TRANSITO => [self::ESTADO_COMPLETADO],
    ];

    /** Subconsulta escalar: nombre del último estado registrado del traslado "t". */
    private const SQL_ESTADO_ACTUAL =
        '(SELECT e.nombre FROM historial_estado h
          INNER JOIN estado_traslado e ON e.id_estado = h.id_estado
          WHERE h.id_traslado = t.id_traslado
          ORDER BY h.id_historial DESC LIMIT 1)';

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtenerConexion();
    }

    // ------------------------------------------------------------------
    // Catálogos para poblar el formulario
    // ------------------------------------------------------------------

    /** Elementos que acompañan al paciente (camilla, silla de ruedas, ninguno). */
    public function obtenerTiposElemento(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id_tipo_elemento, nombre FROM tipo_elemento ORDER BY id_tipo_elemento'
        );
        $consulta->execute();
        return $consulta->fetchAll();
    }

    /** Ubicaciones disponibles como origen o destino. */
    public function obtenerUbicaciones(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id_ubicacion, nombre FROM ubicacion ORDER BY nombre'
        );
        $consulta->execute();
        return $consulta->fetchAll();
    }

    /**
     * Vehículos con su tipo y la lista de elementos que pueden transportar
     * (tabla compatibilidad). Cada vehículo trae 'elementos_compatibles':
     * arreglo de id_tipo_elemento.
     */
    public function obtenerVehiculos(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT v.id_vehiculo, v.matricula, t.nombre AS tipo, v.id_tipo_vehiculo
             FROM vehiculo v
             INNER JOIN tipo_vehiculo t ON t.id_tipo_vehiculo = v.id_tipo_vehiculo
             ORDER BY v.matricula'
        );
        $consulta->execute();
        $vehiculos = $consulta->fetchAll();

        $consultaCompat = $this->conexion->prepare(
            'SELECT id_tipo_vehiculo, id_tipo_elemento FROM compatibilidad'
        );
        $consultaCompat->execute();

        $compatibles = [];
        foreach ($consultaCompat->fetchAll() as $fila) {
            $compatibles[(int) $fila['id_tipo_vehiculo']][] = (int) $fila['id_tipo_elemento'];
        }

        foreach ($vehiculos as &$vehiculo) {
            $vehiculo['elementos_compatibles'] = $compatibles[(int) $vehiculo['id_tipo_vehiculo']] ?? [];
        }
        unset($vehiculo);

        return $vehiculos;
    }

    /** Usuarios activos, para elegir chofer y personal acompañante. */
    public function obtenerUsuariosActivos(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id_usuario, nombre, apellido, cedula
             FROM usuario
             WHERE activo = TRUE
             ORDER BY apellido, nombre'
        );
        $consulta->execute();
        return $consulta->fetchAll();
    }

    // ------------------------------------------------------------------
    // Ubicaciones
    // ------------------------------------------------------------------

    /**
     * Crea una ubicación nueva. Si ya existe una con el mismo nombre
     * (sin distinguir mayúsculas), devuelve la existente.
     *
     * @return array ['id_ubicacion' => int, 'nombre' => string, 'existia' => bool]
     */
    public function crearUbicacion(string $nombre): array
    {
        $buscar = $this->conexion->prepare(
            'SELECT id_ubicacion, nombre FROM ubicacion WHERE LOWER(nombre) = LOWER(:nombre)'
        );
        $buscar->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $buscar->execute();
        $existente = $buscar->fetch();

        if ($existente !== false) {
            return [
                'id_ubicacion' => (int) $existente['id_ubicacion'],
                'nombre' => $existente['nombre'],
                'existia' => true,
            ];
        }

        $insertar = $this->conexion->prepare('INSERT INTO ubicacion (nombre) VALUES (:nombre)');
        $insertar->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $insertar->execute();

        return [
            'id_ubicacion' => (int) $this->conexion->lastInsertId(),
            'nombre' => $nombre,
            'existia' => false,
        ];
    }

    // ------------------------------------------------------------------
    // Pacientes
    // ------------------------------------------------------------------

    /** Busca un paciente por cédula (solo dígitos). */
    public function buscarPaciente(string $ci): array|false
    {
        $consulta = $this->conexion->prepare(
            'SELECT ci, nombre, apellido FROM paciente WHERE ci = :ci'
        );
        $consulta->bindValue(':ci', $ci, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch();
    }

    // ------------------------------------------------------------------
    // Validaciones de negocio
    // ------------------------------------------------------------------

    /** Indica si el vehículo puede transportar el elemento (tabla compatibilidad). */
    public function esVehiculoCompatible(int $idVehiculo, int $idTipoElemento): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT COUNT(*) FROM vehiculo v
             INNER JOIN compatibilidad c ON c.id_tipo_vehiculo = v.id_tipo_vehiculo
             WHERE v.id_vehiculo = :id_vehiculo AND c.id_tipo_elemento = :id_tipo_elemento'
        );
        $consulta->bindValue(':id_vehiculo', $idVehiculo, PDO::PARAM_INT);
        $consulta->bindValue(':id_tipo_elemento', $idTipoElemento, PDO::PARAM_INT);
        $consulta->execute();
        return (int) $consulta->fetchColumn() > 0;
    }

    /** Verifica que exista una fila con ese id en la tabla indicada (lista blanca). */
    public function existeRegistro(string $tabla, string $columnaId, int $id): bool
    {
        $permitidos = [
            'tipo_elemento' => 'id_tipo_elemento',
            'ubicacion' => 'id_ubicacion',
            'vehiculo' => 'id_vehiculo',
            'usuario' => 'id_usuario',
        ];
        if (($permitidos[$tabla] ?? null) !== $columnaId) {
            throw new InvalidArgumentException('Consulta de existencia no permitida.');
        }

        $extra = $tabla === 'usuario' ? ' AND activo = TRUE' : '';
        $consulta = $this->conexion->prepare(
            "SELECT COUNT(*) FROM {$tabla} WHERE {$columnaId} = :id{$extra}"
        );
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return (int) $consulta->fetchColumn() > 0;
    }

    // ------------------------------------------------------------------
    // Alta de traslado
    // ------------------------------------------------------------------

    /**
     * Registra un traslado completo en una sola transacción:
     *  1. paciente (se crea si la cédula es nueva; si existe, se conserva),
     *  2. ruta (se reutiliza la del par origen/destino o se crea),
     *  3. solicitud_traslado,
     *  4. traslado,
     *  5. historial_estado con el estado inicial.
     *
     * El estado inicial es "Vehículo Asignado" si se eligió vehículo y
     * "Solicitado" en caso contrario.
     *
     * @param array $datos ci, nombre, apellido, id_tipo_elemento, id_origen,
     *                     id_destino, hora_prevista (Y-m-d H:i:s),
     *                     id_vehiculo|null, id_chofer|null, id_enfermero|null,
     *                     id_usuario (quien registra)
     * @return array ['id_traslado' => int, 'estado' => string]
     * @throws RuntimeException si falta algún registro de catálogo.
     */
    public function crearTraslado(array $datos): array
    {
        $estadoInicial = $datos['id_vehiculo'] !== null
            ? self::ESTADO_VEHICULO_ASIGNADO
            : self::ESTADO_SOLICITADO;

        $this->conexion->beginTransaction();

        try {
            $idCanal = $this->obtenerIdPorNombre('canal_solicitud', 'id_canal', self::CANAL_FORMULARIO);
            $idEstado = $this->obtenerIdPorNombre('estado_traslado', 'id_estado', $estadoInicial);

            // 1. Paciente: si la cédula ya existe se conservan sus datos
            if ($this->buscarPaciente($datos['ci']) === false) {
                $insertarPaciente = $this->conexion->prepare(
                    'INSERT INTO paciente (ci, nombre, apellido) VALUES (:ci, :nombre, :apellido)'
                );
                $insertarPaciente->bindValue(':ci', $datos['ci'], PDO::PARAM_STR);
                $insertarPaciente->bindValue(':nombre', $datos['nombre'], PDO::PARAM_STR);
                $insertarPaciente->bindValue(':apellido', $datos['apellido'], PDO::PARAM_STR);
                $insertarPaciente->execute();
            }

            // 2. Ruta: una por cada par origen/destino
            $idRuta = $this->obtenerOCrearRuta((int) $datos['id_origen'], (int) $datos['id_destino']);

            // 3. Solicitud
            $insertarSolicitud = $this->conexion->prepare(
                'INSERT INTO solicitud_traslado
                    (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal,
                     id_usuario_solicita, estado_solicitud)
                 VALUES (:ci, :id_tipo_elemento, :id_origen, :id_destino, :id_canal,
                         :id_usuario, :estado)'
            );
            $insertarSolicitud->bindValue(':ci', $datos['ci'], PDO::PARAM_STR);
            $insertarSolicitud->bindValue(':id_tipo_elemento', (int) $datos['id_tipo_elemento'], PDO::PARAM_INT);
            $insertarSolicitud->bindValue(':id_origen', (int) $datos['id_origen'], PDO::PARAM_INT);
            $insertarSolicitud->bindValue(':id_destino', (int) $datos['id_destino'], PDO::PARAM_INT);
            $insertarSolicitud->bindValue(':id_canal', $idCanal, PDO::PARAM_INT);
            $insertarSolicitud->bindValue(':id_usuario', (int) $datos['id_usuario'], PDO::PARAM_INT);
            $insertarSolicitud->bindValue(':estado', $estadoInicial, PDO::PARAM_STR);
            $insertarSolicitud->execute();
            $idSolicitud = (int) $this->conexion->lastInsertId();

            // 4. Traslado
            $insertarTraslado = $this->conexion->prepare(
                'INSERT INTO traslado
                    (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_ruta, hora_prevista)
                 VALUES (:id_solicitud, :id_vehiculo, :id_chofer, :id_enfermero, :id_ruta, :hora_prevista)'
            );
            $insertarTraslado->bindValue(':id_solicitud', $idSolicitud, PDO::PARAM_INT);
            $insertarTraslado->bindValue(':id_vehiculo', $datos['id_vehiculo'], $datos['id_vehiculo'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insertarTraslado->bindValue(':id_chofer', $datos['id_chofer'], $datos['id_chofer'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insertarTraslado->bindValue(':id_enfermero', $datos['id_enfermero'], $datos['id_enfermero'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insertarTraslado->bindValue(':id_ruta', $idRuta, PDO::PARAM_INT);
            $insertarTraslado->bindValue(':hora_prevista', $datos['hora_prevista'], PDO::PARAM_STR);
            $insertarTraslado->execute();
            $idTraslado = (int) $this->conexion->lastInsertId();

            // 5. Historial: primer estado y quién lo registró
            $insertarHistorial = $this->conexion->prepare(
                'INSERT INTO historial_estado (id_traslado, id_estado, id_usuario)
                 VALUES (:id_traslado, :id_estado, :id_usuario)'
            );
            $insertarHistorial->bindValue(':id_traslado', $idTraslado, PDO::PARAM_INT);
            $insertarHistorial->bindValue(':id_estado', $idEstado, PDO::PARAM_INT);
            $insertarHistorial->bindValue(':id_usuario', (int) $datos['id_usuario'], PDO::PARAM_INT);
            $insertarHistorial->execute();

            $this->conexion->commit();

            return ['id_traslado' => $idTraslado, 'estado' => $estadoInicial];
        } catch (Throwable $error) {
            $this->conexion->rollBack();
            throw $error;
        }
    }

    // ------------------------------------------------------------------
    // Lectura de traslados (Panel de Gestión)
    // ------------------------------------------------------------------

    /** Consulta base: un traslado con paciente, ruta, vehículo, personal y estado actual. */
    private function consultaTraslados(): string
    {
        return "SELECT t.id_traslado, t.id_solicitud, t.id_vehiculo, t.id_chofer, t.id_enfermero,
                       t.hora_prevista, t.hora_real, t.hora_retorno,
                       s.ci_paciente, s.id_tipo_elemento, s.id_origen, s.id_destino,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       te.nombre AS elemento,
                       uo.nombre AS origen, ud.nombre AS destino,
                       v.matricula, tv.nombre AS tipo_vehiculo,
                       uc.nombre AS chofer_nombre, uc.apellido AS chofer_apellido,
                       ue.nombre AS enfermero_nombre, ue.apellido AS enfermero_apellido,
                       " . self::SQL_ESTADO_ACTUAL . " AS estado
                FROM traslado t
                INNER JOIN solicitud_traslado s ON s.id_solicitud = t.id_solicitud
                INNER JOIN paciente p ON p.ci = s.ci_paciente
                INNER JOIN tipo_elemento te ON te.id_tipo_elemento = s.id_tipo_elemento
                INNER JOIN ubicacion uo ON uo.id_ubicacion = s.id_origen
                INNER JOIN ubicacion ud ON ud.id_ubicacion = s.id_destino
                LEFT JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
                LEFT JOIN tipo_vehiculo tv ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
                LEFT JOIN usuario uc ON uc.id_usuario = t.id_chofer
                LEFT JOIN usuario ue ON ue.id_usuario = t.id_enfermero";
    }

    /** Todos los traslados (más recientes primero), con su estado actual. */
    public function listarTraslados(): array
    {
        $consulta = $this->conexion->prepare($this->consultaTraslados() . ' ORDER BY t.id_traslado DESC');
        $consulta->execute();
        return $consulta->fetchAll();
    }

    /** Un traslado por id. Con $bloquear = true lo bloquea hasta terminar la transacción (MySQL). */
    public function buscarTraslado(int $idTraslado, bool $bloquear = false): array|false
    {
        $sql = $this->consultaTraslados() . ' WHERE t.id_traslado = :id';
        if ($bloquear && $this->conexion->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql .= ' FOR UPDATE';
        }
        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':id', $idTraslado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch();
    }

    /** Nombres de los estados a los que se puede pasar desde $estadoActual (sin Cancelado). */
    public function obtenerTransiciones(string $estadoActual): array
    {
        return self::TRANSICIONES[$estadoActual] ?? [];
    }

    /** Nombres de todos los estados, en orden de flujo. */
    public function obtenerEstados(): array
    {
        $consulta = $this->conexion->prepare('SELECT nombre FROM estado_traslado ORDER BY id_estado');
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Cantidad de vehículos de la flota. */
    public function contarVehiculos(): int
    {
        $consulta = $this->conexion->prepare('SELECT COUNT(*) FROM vehiculo');
        $consulta->execute();
        return (int) $consulta->fetchColumn();
    }

    /**
     * Números de las tarjetas del panel, calculados a partir de la lista de traslados:
     *  - total (sin cancelados) y cancelados,
     *  - vehículos ocupados/disponibles (asignados a traslados con vehículo asignado o en tránsito),
     *  - choferes con traslados en curso,
     *  - solicitudes pendientes de asignación (estado Solicitado).
     */
    public function obtenerResumen(array $traslados, int $totalVehiculos): array
    {
        $total = $cancelados = $pendientes = 0;
        $vehiculosOcupados = [];
        $choferesActivos = [];

        foreach ($traslados as $traslado) {
            $estado = $traslado['estado'];
            if ($estado === self::ESTADO_CANCELADO) {
                $cancelados++;
                continue;
            }
            $total++;
            if ($estado === self::ESTADO_SOLICITADO) {
                $pendientes++;
            }
            if ($estado === self::ESTADO_VEHICULO_ASIGNADO || $estado === self::ESTADO_EN_TRANSITO) {
                if ($traslado['id_vehiculo'] !== null) {
                    $vehiculosOcupados[(int) $traslado['id_vehiculo']] = true;
                }
                if ($traslado['id_chofer'] !== null) {
                    $choferesActivos[(int) $traslado['id_chofer']] = true;
                }
            }
        }

        return [
            'total' => $total,
            'cancelados' => $cancelados,
            'pendientes' => $pendientes,
            'vehiculos_total' => $totalVehiculos,
            'vehiculos_disponibles' => max(0, $totalVehiculos - count($vehiculosOcupados)),
            'choferes_activos' => count($choferesActivos),
        ];
    }

    // ------------------------------------------------------------------
    // Actualizar, cancelar y reactivar
    // ------------------------------------------------------------------

    /**
     * Edita un traslado: fecha prevista, vehículo, chofer, acompañante y estado.
     * Todo en una transacción; si cambia el estado se registra en historial_estado.
     *
     * @param array $datos hora_prevista (Y-m-d H:i:s), id_vehiculo|null, id_chofer|null,
     *                     id_enfermero|null, estado|null (estado al que se quiere pasar)
     * @return array ['estado' => string, 'cambio_estado' => bool]
     * @throws ReglaDeNegocioException si el cambio no cumple las reglas.
     */
    public function actualizarTraslado(int $idTraslado, array $datos, int $idUsuario): array
    {
        return $this->enTransaccion(function () use ($idTraslado, $datos, $idUsuario): array {
            $actual = $this->buscarTrasladoOFalla($idTraslado);

            if (in_array($actual['estado'], self::ESTADOS_FINALES, true)) {
                throw new ReglaDeNegocioException(
                    "No se puede modificar un traslado en estado {$actual['estado']}."
                );
            }

            $idVehiculo = $datos['id_vehiculo'];
            $idChofer = $datos['id_chofer'];

            if ($idVehiculo !== null
                && !$this->esVehiculoCompatible($idVehiculo, (int) $actual['id_tipo_elemento'])) {
                throw new ReglaDeNegocioException('El vehículo seleccionado no es compatible con el elemento a trasladar.');
            }

            $nuevoEstado = $this->resolverEstado($actual['estado'], $datos['estado'] ?? null, $idVehiculo, $idChofer);

            if ($nuevoEstado === self::ESTADO_EN_TRANSITO) {
                $this->verificarSinConflictoEnTransito($idTraslado, $idVehiculo, $idChofer);
            }

            $ahora = date('Y-m-d H:i:s');
            $sql = 'UPDATE traslado
                    SET hora_prevista = :hora_prevista, id_vehiculo = :id_vehiculo,
                        id_chofer = :id_chofer, id_enfermero = :id_enfermero';
            if ($nuevoEstado === self::ESTADO_EN_TRANSITO) {
                $sql .= ', hora_real = COALESCE(hora_real, :ahora)';
            } elseif ($nuevoEstado === self::ESTADO_COMPLETADO) {
                $sql .= ', hora_retorno = :ahora';
            }
            $sql .= ' WHERE id_traslado = :id_traslado';

            $consulta = $this->conexion->prepare($sql);
            $consulta->bindValue(':hora_prevista', $datos['hora_prevista'], PDO::PARAM_STR);
            $consulta->bindValue(':id_vehiculo', $idVehiculo, $idVehiculo === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $consulta->bindValue(':id_chofer', $idChofer, $idChofer === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $consulta->bindValue(':id_enfermero', $datos['id_enfermero'], $datos['id_enfermero'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $consulta->bindValue(':id_traslado', $idTraslado, PDO::PARAM_INT);
            if ($nuevoEstado === self::ESTADO_EN_TRANSITO || $nuevoEstado === self::ESTADO_COMPLETADO) {
                $consulta->bindValue(':ahora', $ahora, PDO::PARAM_STR);
            }
            $consulta->execute();

            $cambio = $nuevoEstado !== $actual['estado'];
            if ($cambio) {
                $this->registrarEstado($idTraslado, (int) $actual['id_solicitud'], $nuevoEstado, $idUsuario);
            }

            return ['estado' => $nuevoEstado, 'cambio_estado' => $cambio];
        });
    }

    /** Cancelación (borrado lógico): el traslado pasa al estado "Cancelado". */
    public function cancelarTraslado(int $idTraslado, int $idUsuario): void
    {
        $this->enTransaccion(function () use ($idTraslado, $idUsuario): void {
            $actual = $this->buscarTrasladoOFalla($idTraslado);

            if (in_array($actual['estado'], self::ESTADOS_FINALES, true)) {
                throw new ReglaDeNegocioException(
                    "El traslado ya está en estado {$actual['estado']} y no puede cancelarse."
                );
            }
            $this->registrarEstado($idTraslado, (int) $actual['id_solicitud'], self::ESTADO_CANCELADO, $idUsuario);
        });
    }

    /**
     * Reactiva un traslado cancelado: vuelve a "Vehículo Asignado" si conserva
     * vehículo, o a "Solicitado" si no. Se limpian las horas real y de retorno.
     */
    public function reactivarTraslado(int $idTraslado, int $idUsuario): string
    {
        return $this->enTransaccion(function () use ($idTraslado, $idUsuario): string {
            $actual = $this->buscarTrasladoOFalla($idTraslado);

            if ($actual['estado'] !== self::ESTADO_CANCELADO) {
                throw new ReglaDeNegocioException('Solo se pueden reactivar traslados cancelados.');
            }

            $nuevoEstado = $actual['id_vehiculo'] !== null
                ? self::ESTADO_VEHICULO_ASIGNADO
                : self::ESTADO_SOLICITADO;

            $limpiar = $this->conexion->prepare(
                'UPDATE traslado SET hora_real = NULL, hora_retorno = NULL WHERE id_traslado = :id'
            );
            $limpiar->bindValue(':id', $idTraslado, PDO::PARAM_INT);
            $limpiar->execute();

            $this->registrarEstado($idTraslado, (int) $actual['id_solicitud'], $nuevoEstado, $idUsuario);
            return $nuevoEstado;
        });
    }

    // ------------------------------------------------------------------
    // Auxiliares privados
    // ------------------------------------------------------------------

    /** Ejecuta $operacion dentro de una transacción: confirma si termina bien, revierte si falla. */
    private function enTransaccion(callable $operacion): mixed
    {
        $this->conexion->beginTransaction();
        try {
            $resultado = $operacion();
            $this->conexion->commit();
            return $resultado;
        } catch (Throwable $error) {
            $this->conexion->rollBack();
            throw $error;
        }
    }

    private function buscarTrasladoOFalla(int $idTraslado): array
    {
        $traslado = $this->buscarTraslado($idTraslado, true);
        if ($traslado === false) {
            throw new ReglaDeNegocioException('El traslado no existe.');
        }
        return $traslado;
    }

    /**
     * Decide el estado resultante de una edición.
     * Sin estado solicitado: se ajusta solo (asignar vehículo => Vehículo Asignado,
     * quitarlo => Solicitado). Con estado solicitado: debe ser una transición válida.
     */
    private function resolverEstado(string $actual, ?string $solicitado, ?int $idVehiculo, ?int $idChofer): string
    {
        if ($solicitado === null || $solicitado === '' || $solicitado === $actual) {
            if ($actual === self::ESTADO_SOLICITADO && $idVehiculo !== null) {
                return self::ESTADO_VEHICULO_ASIGNADO;
            }
            if ($actual === self::ESTADO_VEHICULO_ASIGNADO && $idVehiculo === null) {
                return self::ESTADO_SOLICITADO;
            }
            if ($actual === self::ESTADO_EN_TRANSITO && ($idVehiculo === null || $idChofer === null)) {
                throw new ReglaDeNegocioException('Un traslado En Tránsito necesita vehículo y chofer.');
            }
            return $actual;
        }

        if (!in_array($solicitado, self::TRANSICIONES[$actual] ?? [], true)) {
            throw new ReglaDeNegocioException("No se puede pasar de «{$actual}» a «{$solicitado}».");
        }

        switch ($solicitado) {
            case self::ESTADO_VEHICULO_ASIGNADO:
                if ($idVehiculo === null) {
                    throw new ReglaDeNegocioException('Para asignar el vehículo debe elegir uno.');
                }
                break;
            case self::ESTADO_SOLICITADO:
                if ($idVehiculo !== null) {
                    throw new ReglaDeNegocioException('Para volver a «Solicitado» quite el vehículo asignado.');
                }
                break;
            case self::ESTADO_EN_TRANSITO:
            case self::ESTADO_COMPLETADO:
                if ($idVehiculo === null || $idChofer === null) {
                    throw new ReglaDeNegocioException("Para pasar a «{$solicitado}» el traslado necesita vehículo y chofer.");
                }
                break;
        }
        return $solicitado;
    }

    /** Un vehículo o chofer no puede estar en dos traslados "En Tránsito" a la vez. */
    private function verificarSinConflictoEnTransito(int $idTraslado, ?int $idVehiculo, ?int $idChofer): void
    {
        $verificaciones = [
            ['t.id_vehiculo', $idVehiculo, 'El vehículo ya está en otro traslado En Tránsito.'],
            ['t.id_chofer', $idChofer, 'El chofer ya está en otro traslado En Tránsito.'],
        ];

        foreach ($verificaciones as [$columna, $valor, $mensaje]) {
            if ($valor === null) {
                continue;
            }
            $consulta = $this->conexion->prepare(
                "SELECT COUNT(*) FROM traslado t
                 WHERE t.id_traslado <> :id_traslado AND {$columna} = :valor
                   AND " . self::SQL_ESTADO_ACTUAL . " = :estado"
            );
            $consulta->bindValue(':id_traslado', $idTraslado, PDO::PARAM_INT);
            $consulta->bindValue(':valor', $valor, PDO::PARAM_INT);
            $consulta->bindValue(':estado', self::ESTADO_EN_TRANSITO, PDO::PARAM_STR);
            $consulta->execute();
            if ((int) $consulta->fetchColumn() > 0) {
                throw new ReglaDeNegocioException($mensaje);
            }
        }
    }

    /** Registra un nuevo estado: historial_estado + estado_solicitud de la solicitud. */
    private function registrarEstado(int $idTraslado, int $idSolicitud, string $nombreEstado, int $idUsuario): void
    {
        $idEstado = $this->obtenerIdPorNombre('estado_traslado', 'id_estado', $nombreEstado);

        $historial = $this->conexion->prepare(
            'INSERT INTO historial_estado (id_traslado, id_estado, id_usuario)
             VALUES (:id_traslado, :id_estado, :id_usuario)'
        );
        $historial->bindValue(':id_traslado', $idTraslado, PDO::PARAM_INT);
        $historial->bindValue(':id_estado', $idEstado, PDO::PARAM_INT);
        $historial->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $historial->execute();

        $solicitud = $this->conexion->prepare(
            'UPDATE solicitud_traslado SET estado_solicitud = :estado WHERE id_solicitud = :id_solicitud'
        );
        $solicitud->bindValue(':estado', $nombreEstado, PDO::PARAM_STR);
        $solicitud->bindValue(':id_solicitud', $idSolicitud, PDO::PARAM_INT);
        $solicitud->execute();
    }

    /**
     * Devuelve el id de un registro de catálogo buscándolo por nombre.
     * $tabla y $columnaId son siempre constantes internas, nunca datos del usuario.
     */
    private function obtenerIdPorNombre(string $tabla, string $columnaId, string $nombre): int
    {
        $consulta = $this->conexion->prepare("SELECT {$columnaId} FROM {$tabla} WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();
        $id = $consulta->fetchColumn();

        if ($id === false) {
            throw new RuntimeException(
                "Falta el registro '{$nombre}' en {$tabla}. Ejecute el script database/modulo-ambulancias/01-preparar-traslados.sql."
            );
        }
        return (int) $id;
    }

    /** Reutiliza la ruta del par origen/destino o la crea si no existe. */
    private function obtenerOCrearRuta(int $idOrigen, int $idDestino): int
    {
        $buscar = $this->conexion->prepare(
            'SELECT id_ruta FROM ruta WHERE id_origen = :origen AND id_destino = :destino'
        );
        $buscar->bindValue(':origen', $idOrigen, PDO::PARAM_INT);
        $buscar->bindValue(':destino', $idDestino, PDO::PARAM_INT);
        $buscar->execute();
        $idRuta = $buscar->fetchColumn();

        if ($idRuta !== false) {
            return (int) $idRuta;
        }

        $insertar = $this->conexion->prepare(
            'INSERT INTO ruta (id_origen, id_destino) VALUES (:origen, :destino)'
        );
        $insertar->bindValue(':origen', $idOrigen, PDO::PARAM_INT);
        $insertar->bindValue(':destino', $idDestino, PDO::PARAM_INT);
        $insertar->execute();
        return (int) $this->conexion->lastInsertId();
    }
}
