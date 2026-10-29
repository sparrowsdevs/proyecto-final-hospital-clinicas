<?php
if (!isset($auth) || !($auth instanceof AuthController)) {
    throw new RuntimeException('navbar.php requiere que $auth (AuthController) ya esté inicializado.');
}


$paginaActual = $paginaActual ?? '';

$esAdministrador = $auth->tieneRol('Administrador');
$nombreUsuario = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
$rolUsuario = $esAdministrador ? 'Administrador' : 'Usuario Básico';

function claseActivaNav(string $pagina, string $paginaActual): string
{
    return $pagina === $paginaActual ? 'enlace-nav activo' : 'enlace-nav';
}
?>
<aside class="barra-lateral">
    <div class="encabezado-lateral">
        <img alt="Hospital de Clínicas Dr. Manuel Quintela" class="logo-marca" src="../../img/logosigsm.png">
        <h2>S.I.G.S.M.</h2>
        <p><?= $esAdministrador ? 'Administración Central' : 'Hospital de Clínicas' ?></p>
    </div>

    <nav class="nav-lateral">
        <p class="titulo-seccion-nav">Documentación</p>

        <a class="<?= claseActivaNav('documentacion', $paginaActual) ?>" href="../../Modulo Documentacion/Vista/panel-documentacion.php">
            <span class="material-symbols-outlined">folder_shared</span>
            <span>Documentación</span>
        </a>

        <?php if ($esAdministrador): ?>
        <a class="<?= claseActivaNav('cargar-documento', $paginaActual) ?>" href="../../Modulo Documentacion/Vista/cargar-documento.php">
            <span class="material-symbols-outlined">upload_file</span>
            <span>Carga de Archivos</span>
        </a>

        <a class="<?= claseActivaNav('usuarios', $paginaActual) ?>" href="../../Modulo Documentacion/Vista/gestion-usuarios.php">
            <span class="material-symbols-outlined">group</span>
            <span>Gestión de Usuarios</span>
        </a>
        <?php endif; ?>

        <p class="titulo-seccion-nav">Ambulancias</p>

        <a class="<?= claseActivaNav('traslado-formulario', $paginaActual) ?>" href="../../Modulo Ambulancias/Vista/form-traslado.php">
            <span class="material-symbols-outlined">edit_note</span>
            <span>Formulario de Traslado</span>
        </a>

        <a class="<?= claseActivaNav('traslado-panel', $paginaActual) ?>" href="../../Modulo Ambulancias/Vista/panel-de-gestion.php">
            <span class="material-symbols-outlined">local_shipping</span>
            <span>Panel de Gestión</span>
        </a>

        <a class="<?= claseActivaNav('traslado-seguimiento', $paginaActual) ?>" href="../../Modulo Ambulancias/Vista/seguimiento.php">
            <span class="material-symbols-outlined">pin_drop</span>
            <span>Seguimiento</span>
        </a>

        <p class="titulo-seccion-nav">Encuestas</p>

        <a class="<?= claseActivaNav('encuestas-resultados', $paginaActual) ?>" href="../../Modulo Encuestas/Vista/resultados-encuestas.php">
            <span class="material-symbols-outlined">analytics</span>
            <span>Resultados de Encuestas</span>
        </a>
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
