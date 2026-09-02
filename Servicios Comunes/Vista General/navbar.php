<?php
if (!isset($auth) || !($auth instanceof AuthController)) {
    throw new RuntimeException('navbar.php requiere que $auth (AuthController) ya esté inicializado.');
}


$paginaActual = $paginaActual ?? '';

// Datos del usuario que inició sesión, para mostrarlos en la barra superior
$esAdministrador = $auth->tieneRol('Administrador');
$nombreUsuario = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$rolUsuario = $esAdministrador ? 'Administrador' : 'Usuario Básico';

/*
 * Devuelve la clase CSS del enlace del menú, marcándolo como "activo"
 * si corresponde a la página en la que está parado el usuario.
 */
function claseActivaNav(string $pagina, string $paginaActual): string
{
    return $pagina === $paginaActual ? 'enlace-nav activo' : 'enlace-nav';
}
?>
<aside class="barra-lateral">
    <div class="encabezado-lateral">
        <img alt="Logo Institucional" class="logo-marca" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
        <h2>Gestión Documental</h2>
        <p><?= $esAdministrador ? 'Administración Central' : 'Documentación Clínica' ?></p>
    </div>

    <nav class="nav-lateral">
        <?php if ($esAdministrador): ?>
        <a class="<?= claseActivaNav('dashboard', $paginaActual) ?>" href="panel-administrador.php">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>

        <a class="<?= claseActivaNav('documentacion', $paginaActual) ?>" href="panel-documentacion.php">
            <span class="material-symbols-outlined">folder_shared</span>
            <span>Documentación</span>
        </a>

        <?php if ($esAdministrador): ?>
        <a class="<?= claseActivaNav('cargar-documento', $paginaActual) ?>" href="cargar-documento.php">
            <span class="material-symbols-outlined">upload_file</span>
            <span>Carga de Archivos</span>
        </a>

        <a class="<?= claseActivaNav('usuarios', $paginaActual) ?>" href="gestion-usuarios.php">
            <span class="material-symbols-outlined">group</span>
            <span>Gestión de Usuarios</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>

<!-- Barra superior: menú hamburguesa (mobile) + datos del usuario + logout -->
<header class="barra-superior">
    <div class="superior-izquierda">
        <button class="boton-menu"><span class="material-symbols-outlined">menu</span></button>
    </div>

    <div class="superior-derecha">
        <div class="perfil-usuario">
            <div class="info-usuario">
                <p class="nombre-usuario"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="rol-usuario"><?= htmlspecialchars(mb_strtoupper($rolUsuario, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <a href="../../Servicios Comunes/Autenticacion/logout.php" class="btn-cerrar-sesion">
            <span class="material-symbols-outlined">logout</span>
            <span class="texto-cerrar-sesion">Cerrar Sesión</span>
        </a>
    </div>
</header>
