<?php

require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/UsuarioModelo.php';

$auth = new AuthController();
$auth->protegerRuta('Administrador');

$paginaActual = 'usuarios';

$usuarioModelo = new UsuarioModelo();
$usuarios = $usuarioModelo->listarTodos();
$idUsuarioSesion = (int) ($_SESSION['id_usuario'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Gestión de Usuarios - S.I.G.S.M.</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/gestion-usuarios.css">
    <link rel="stylesheet" href="../../Servicios Comunes/Vista General/assets/css/navbar.css">
</head>
<body>

    <?php require __DIR__ . '/../../Servicios Comunes/Vista General/navbar.php'; ?>

    <main class="main-wrapper">
        <div class="page-body">
            <div class="container">

                <div class="page-header">
                    <h1>Gestión de Usuarios</h1>
                    <p>Administre los usuarios registrados en el sistema: edite sus datos o cambie su estado.</p>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <?php
                                    $esCuentaPropia = (int) $usuario['id_usuario'] === $idUsuarioSesion;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($usuario['cedula'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($usuario['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge badge-rol"><?= htmlspecialchars($usuario['roles'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <?php if ($usuario['activo']): ?>
                                            <span class="badge badge-activo">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactivo">Suspendido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acciones-cell">
                                            <button
                                                class="btn-icon-action"
                                                title="Editar usuario"
                                                onclick="abrirModalEdicion(
                                                    <?= (int) $usuario['id_usuario'] ?>,
                                                    '<?= htmlspecialchars(addslashes($usuario['nombre']), ENT_QUOTES, 'UTF-8') ?>',
                                                    '<?= htmlspecialchars(addslashes($usuario['apellido']), ENT_QUOTES, 'UTF-8') ?>',
                                                    '<?= htmlspecialchars(addslashes($usuario['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>',
                                                    <?= $usuario['activo'] ? 'true' : 'false' ?>,
                                                    <?= $esCuentaPropia ? 'true' : 'false' ?>
                                                )">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </button>

                                            <?php if ($usuario['activo']): ?>
                                                <button
                                                    class="btn-icon-action danger"
                                                    title="<?= $esCuentaPropia ? 'No puede suspender su propia cuenta' : 'Suspender usuario' ?>"
                                                    onclick="confirmarCambioEstado(<?= (int) $usuario['id_usuario'] ?>, 'suspender')"
                                                    <?= $esCuentaPropia ? 'disabled' : '' ?>>
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                                                </button>
                                            <?php else: ?>
                                                <button
                                                    class="btn-icon-action success"
                                                    title="Reactivar usuario"
                                                    onclick="confirmarCambioEstado(<?= (int) $usuario['id_usuario'] ?>, 'reactivar')">
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--outline);">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal de edición de usuario -->
    <div class="modal-overlay hidden" id="modalEdicion">
        <div class="modal-window">
            <div class="modal-header">
                <h3>Editar Usuario</h3>
                <button class="btn-close-circle" type="button" onclick="cerrarModalEdicion()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="formEdicionUsuario">
                <div class="modal-body">
                    <div class="mensaje-error" id="mensajeErrorEdicion"></div>

                    <input type="hidden" id="editIdUsuario" name="id_usuario">

                    <div class="form-group">
                        <label class="form-label" for="editNombre">Nombre</label>
                        <input class="form-input" id="editNombre" name="nombre" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editApellido">Apellido</label>
                        <input class="form-input" id="editApellido" name="apellido" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editEmail">Email</label>
                        <input class="form-input" id="editEmail" name="email" type="email">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editContrasena">Nueva contraseña</label>
                        <input class="form-input" id="editContrasena" name="contrasena" type="password" autocomplete="new-password">
                        <span class="form-hint">Dejar en blanco para mantener la contraseña actual.</span>
                    </div>

                    <div class="checkbox-row">
                        <input type="checkbox" id="editActivo" name="activo">
                        <label for="editActivo">Cuenta activa</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" type="button" onclick="cerrarModalEdicion()">Cancelar</button>
                    <button class="btn-primary-action" type="submit" id="btnGuardarEdicion">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/gestion-usuarios.js"></script>
</body>
</html>
