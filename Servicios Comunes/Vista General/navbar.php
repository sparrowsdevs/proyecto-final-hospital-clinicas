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
    return $pagina === $paginaActual ? 'nav-link active' : 'nav-link';
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img alt="Logo Institucional" class="brand-logo" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
        <h2>Gestión Documental</h2>
        <p><?= $esAdministrador ? 'Administración Central' : 'Documentación Clínica' ?></p>
    </div>

    <nav class="sidebar-nav">
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

    <?php if ($esAdministrador): ?>
    <div class="sidebar-footer">
        <a href="cargar-documento.php" class="btn btn-action">
            <span class="material-symbols-outlined">add_circle</span>
            Nuevo Documento
        </a>
    </div>
    <?php endif; ?>
</aside>

<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle"><span class="material-symbols-outlined">menu</span></button>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" placeholder="Buscar paciente...">
        </div>

        <div class="user-actions">
            <button class="icon-btn">
                <span class="material-symbols-outlined">notifications</span>
                <span class="badge-dot"></span>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <p class="user-name"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="user-role"><?= htmlspecialchars(mb_strtoupper($rolUsuario, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <img alt="Avatar del usuario" class="user-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDfsEIou6s2sMAq4m24L_nLyJfh2FtUJGkUBwv9EYRRDsyNxnRz9sPofD3A54kY6d6OSy7SI77mXa_LxtgZRen0VE8zM1KlqNcGE-sEadHhKeYv9_5fbTVtJopEfkdTCe3v_JgxfARWaziGM4U4Lo3MP8-qfOhlIwVpFFGv2iM-eALZ_YBT2ZujuZ-FpPL4w7K6c287gkV9A9RecFUoL_kZBi1XydK38hLm0BKDr-Mz3lW1LR8UlWOsEujLJFsOm-n2MRrKvuLN105U">
            </div>
        </div>
    </div>
    <div class="topbar-right">
        <a href="../../Servicios Comunes/Autenticacion/logout.php" class="btn-logout">
            <span class="material-symbols-outlined">logout</span>
            <span class="logout-text">Cerrar Sesión</span>
        </a>
    </div>
</header>
