<?php

if (!isset($auth) || !($auth instanceof AuthController)) {
    throw new RuntimeException('navbar.php requiere que $auth (AuthController) ya esté inicializado.');
}

$paginaActual = $paginaActual ?? '';
$esAdministrador = $auth->tieneRol('Administrador');
$nombreCompleto = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$rolMostrado = $esAdministrador ? 'Administrador' : 'Usuario Básico';

/**
 * Helper local para no repetir la lógica de "clase activa" en cada link.
 */
function claseActiva(string $pagina, string $paginaActual): string
{
    return $pagina === $paginaActual ? 'nav-link active' : 'nav-link';
}
?>
<aside class="app-sidebar">
    <div class="app-sidebar-header">
        <img alt="Logo Institucional" class="app-brand-logo" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
        <h2>S.I.G.S.M.</h2>
        <p>Hospital de Clínicas</p>
    </div>

    <nav class="app-sidebar-nav">
        <a class="<?= claseActiva('documentacion', $paginaActual) ?>"
           href="../../Modulo Documentacion/Vista/panel-documentacion.php">
            <span class="material-symbols-outlined">folder_shared</span>
            <span>Documentación</span>
        </a>

        <?php if ($esAdministrador): ?>
        <a class="<?= claseActiva('admin', $paginaActual) ?>"
           href="../../Modulo Documentacion/Vista/panel-administrador.php">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Panel Administrador</span>
        </a>

        <a class="<?= claseActiva('cargar-documento', $paginaActual) ?>"
           href="../../Modulo Documentacion/Vista/cargar-documento.php">
            <span class="material-symbols-outlined">upload_file</span>
            <span>Gestión de Documentos</span>
        </a>

        <a class="<?= claseActiva('usuarios', $paginaActual) ?>"
           href="../../Modulo Documentacion/Vista/gestion-usuarios.php">
            <span class="material-symbols-outlined">group</span>
            <span>Gestión de Usuarios</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="app-sidebar-footer">
        <div class="app-user-info">
            <span class="material-symbols-outlined app-user-icon">account_circle</span>
            <div>
                <p class="app-user-name"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="app-user-role"><?= htmlspecialchars($rolMostrado, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <a class="app-logout-link" href="../../Servicios Comunes/Autenticacion/logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Cerrar sesión</span>
        </a>
    </div>
</aside>