<?php

require_once __DIR__ . '/../../Servicios Comunes/Conexion BD/conexion.php';

class TrasladoModelo
{
    // Nombres de los registros de catálogo que usa el formulario
    public const CANAL_FORMULARIO = 'App Interna';
    public const ESTADO_SOLICITADO = 'Solicitado';
    public const ESTADO_VEHICULO_ASIGNADO = 'Vehículo Asignado';

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
    // Auxiliares privados
    // ------------------------------------------------------------------

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
