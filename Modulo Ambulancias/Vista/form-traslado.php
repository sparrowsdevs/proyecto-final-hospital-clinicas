<?php

require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../Modelo/TrasladoModelo.php';

$auth = new AuthController();
$auth->protegerRuta();

$paginaActual = 'traslado-formulario';

date_default_timezone_set('America/Montevideo');

// Catálogos que alimentan los selects del formulario
$trasladoModelo = new TrasladoModelo();
$tiposElemento = $trasladoModelo->obtenerTiposElemento();
$ubicaciones = $trasladoModelo->obtenerUbicaciones();
$vehiculos = $trasladoModelo->obtenerVehiculos();
$usuariosActivos = $trasladoModelo->obtenerUsuariosActivos();

$registradoPor = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$fechaMinima = date('Y-m-d\TH:i');

/** Atajo para escapar texto antes de imprimirlo en HTML. */
function esc(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Formulario de Alta de Traslado - S.I.G.S.M.</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/form-traslado.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">

        <div class="page-container">
            
            <div class="page-header">
                <div>
                    <div class="breadcrumb">
                        <a href="panel-de-gestion.php">Traslados</a>
                        <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
                        <span class="active">Nuevo Traslado</span>
                    </div>
                    <h1 class="page-title">Formulario de Alta de Traslado</h1>
                    <p class="page-subtitle">Complete los detalles a continuación para registrar una nueva solicitud de traslado en el sistema logístico.</p>
                </div>
            </div>

            <div class="alerta alerta-error" id="mensajeError" role="alert" hidden></div>
            <div class="alerta alerta-exito" id="mensajeExito" role="status" hidden>
                <span id="textoExito"></span>
                <a href="panel-de-gestion.php">Ir al Panel de Gestión</a>
            </div>

            <form id="transfer-form" class="form-grid" novalidate>
                <div class="col-main">
                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">person_add</span></div>
                            <h2 class="card-title">1. Datos del Traslado</h2>
                        </div>
                        <div class="input-grid">
                            <div class="form-group">
                                <label class="form-label" for="ciPaciente">Cédula del paciente <span class="text-error">*</span></label>
                                <input class="form-control" id="ciPaciente" name="ci_paciente" type="text"
                                       inputmode="numeric" maxlength="11" autocomplete="off"
                                       placeholder="Ej: 12345678" required>
                                <p class="ayuda-campo" id="ayudaPaciente">Sin puntos ni guión (7 u 8 dígitos).</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="idTipoElemento">Elemento a trasladar <span class="text-error">*</span></label>
                                <select class="form-control" id="idTipoElemento" name="id_tipo_elemento" required>
                                    <option disabled selected value="">Seleccione el elemento...</option>
                                    <?php foreach ($tiposElemento as $elemento): ?>
                                        <option value="<?= (int) $elemento['id_tipo_elemento'] ?>"><?= esc($elemento['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="nombrePaciente">Nombre <span class="text-error">*</span></label>
                                <input class="form-control" id="nombrePaciente" name="nombre_paciente" type="text"
                                       maxlength="100" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="apellidoPaciente">Apellido <span class="text-error">*</span></label>
                                <input class="form-control" id="apellidoPaciente" name="apellido_paciente" type="text"
                                       maxlength="100" autocomplete="off" required>
                            </div>
                            <div class="form-group span-2">
                                <label class="form-label" for="horaPrevista">Fecha y hora prevista <span class="text-error">*</span></label>
                                <input class="form-control" id="horaPrevista" name="hora_prevista" type="datetime-local"
                                       min="<?= esc($fechaMinima) ?>" required>
                                <p class="ayuda-campo">Solo traslados programados (no urgentes).</p>
                            </div>
                        </div>
                    </section>
                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">route</span></div>
                            <h2 class="card-title">2. Ruta del Traslado</h2>
                        </div>
                        <div class="route-container">
                            <div class="route-line"></div>
                            <div class="route-step">
                                <div class="step-icon origin"><span class="material-symbols-outlined">trip_origin</span></div>
                                <div class="route-content form-group">
                                    <label class="form-label" for="idOrigen">Origen <span class="text-error">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control selector-ubicacion" id="idOrigen" name="id_origen" required>
                                            <option disabled selected value="">Seleccione ubicación de origen...</option>
                                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                                <option value="<?= (int) $ubicacion['id_ubicacion'] ?>"><?= esc($ubicacion['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-icon-outline btn-nueva-ubicacion" type="button" data-destino="idOrigen" title="Agregar ubicación" aria-label="Agregar ubicación de origen">
                                            <span class="material-symbols-outlined">add_location</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="route-step">
                                <div class="step-icon dest"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">location_on</span></div>
                                <div class="route-content form-group">
                                    <label class="form-label" for="idDestino">Destino <span class="text-error">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control selector-ubicacion" id="idDestino" name="id_destino" required>
                                            <option disabled selected value="">Seleccione ubicación de destino...</option>
                                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                                <option value="<?= (int) $ubicacion['id_ubicacion'] ?>"><?= esc($ubicacion['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-icon-outline btn-nueva-ubicacion" type="button" data-destino="idDestino" title="Agregar ubicación" aria-label="Agregar ubicación de destino">
                                            <span class="material-symbols-outlined">add_location</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-side">
                    <section class="card">
                        <div class="card-header">
                            <div class="card-icon"><span class="material-symbols-outlined">assignment_ind</span></div>
                            <h2 class="card-title">3. Asignación</h2>
                        </div>
                        <p class="ayuda-campo ayuda-asignacion">Opcional: también se puede asignar después desde el Panel de Gestión.</p>
                        <div class="form-group">
                            <label class="form-label" for="idVehiculo">Vehículo</label>
                            <select class="form-control" id="idVehiculo" name="id_vehiculo">
                                <option value="">Sin asignar</option>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                    <option value="<?= (int) $vehiculo['id_vehiculo'] ?>"
                                            data-compatibles="<?= esc(implode(',', $vehiculo['elementos_compatibles'])) ?>">
                                        <?= esc($vehiculo['matricula']) ?> (<?= esc($vehiculo['tipo']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="ayuda-campo" id="ayudaVehiculo"></p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="idChofer">Chofer</label>
                            <select class="form-control" id="idChofer" name="id_chofer">
                                <option value="">Sin asignar</option>
                                <?php foreach ($usuariosActivos as $usuario): ?>
                                    <option value="<?= (int) $usuario['id_usuario'] ?>"><?= esc($usuario['apellido'] . ', ' . $usuario['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="idEnfermero">Personal acompañante</label>
                            <select class="form-control" id="idEnfermero" name="id_enfermero">
                                <option value="">Sin acompañante</option>
                                <?php foreach ($usuariosActivos as $usuario): ?>
                                    <option value="<?= (int) $usuario['id_usuario'] ?>"><?= esc($usuario['apellido'] . ', ' . $usuario['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </section>
                    <section class="summary-card">
                        <h3>Resumen</h3>
                        <ul class="summary-list">
                            <li><span>Estado Inicial:</span> <span class="val" id="estadoInicial">Solicitado</span></li>
                            <li><span>Fecha de Creación:</span> <span class="val">Hoy, <?= esc(date('d/m/Y')) ?></span></li>
                            <li><span>Registrado por:</span> <span class="val"><?= esc($registradoPor) ?></span></li>
                        </ul>
                        <button class="btn btn-action" id="btnRegistrar" type="submit">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">send</span>
                            Registrar Traslado
                        </button>
                    </section>
                </div>
            </form>
        </div>
    </main>


    <!-- Modal: agregar una ubicación nueva (origen o destino) -->
    <div class="modal-fondo" id="modalUbicacion" hidden>
        <div class="modal-ventana" role="dialog" aria-modal="true" aria-labelledby="tituloModalUbicacion">
            <h2 class="modal-titulo" id="tituloModalUbicacion">Agregar ubicación</h2>
            <form id="formUbicacion" novalidate>
                <div class="form-group">
                    <label class="form-label" for="nombreUbicacion">Nombre de la ubicación</label>
                    <input class="form-control" id="nombreUbicacion" type="text" maxlength="150"
                           placeholder="Ej: Hospital Pasteur" autocomplete="off">
                </div>
                <p class="mensaje-modal" id="errorUbicacion" role="alert"></p>
                <div class="modal-acciones">
                    <button class="btn btn-outline" id="btnCancelarUbicacion" type="button">Cancelar</button>
                    <button class="btn btn-action" id="btnGuardarUbicacion" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/form-traslado.js"></script>
</body>
</html>