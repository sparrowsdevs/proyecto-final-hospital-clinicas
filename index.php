<?php
/**
 * Si el usuario ya tiene una sesión activa, se lo redirige directo
 * al panel correspondiente sin mostrar el formulario nuevamente.
 */
require_once __DIR__ . '/Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();

if ($auth->sesionActiva()) {
    $redireccion = $auth->tieneRol('Administrador')
        ? 'Modulo Documentacion/Vista/panel-administrador.php'
        : 'Modulo Documentacion/Vista/panel-documentacion.php';

    header('Location: ' . $redireccion);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Inicio de Sesión - Hospital de Clínicas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="styles/inicio-sesion.css">
</head>
<body>

    <div class="contenedor-fondo">
        <img class="imagen-fondo" alt="Hospital hallway blur" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCbAsT5R3OSCP9oyjy1_ujCis5LY0KmCsYBLqs9HOFkK8noivD8ZyCgo42fYS6DNRMpfVY6AkENFMdPgMkt_CHpZ2dVi3R-VUhrLq1USRPnFHpGIML76kd-22FnxczBAlW7SMlev1D5PxdyEZTAkWH94DTbYC4Fw9GTTbJ_PTtcgyvaElPr6rGMaq5GQz5WWR2JZfM1GbXI1AS9tMHCpmWA-cf8HMO4YG2vsUHs7cl3Gkv23Tv7fznqzycWDJtm3ZjW3flC1n7XffXi">
    </div>

    <main class="contenido-principal">
        <div class="tarjeta-login">
            <div class="barra-acento"></div>
            
            <div class="cuerpo-tarjeta">
                <div class="encabezado-tarjeta">
                    <img alt="Hospital de Clínicas Logo" class="logo-marca" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
                    <h1 class="titulo-login">Inicio de Sesión</h1>
                    <p class="subtitulo">Módulo de Gestión Documental</p>
                </div>

                <form class="formulario-login" id="formularioLogin">

                    <div class="mensaje-error" id="mensajeError" role="alert" style="display: none;"></div>

                    <div class="grupo-formulario">
                        <label class="etiqueta-formulario" for="cedula">Cédula de Identidad</label>
                        <div class="contenedor-input">
                            <span class="material-symbols-outlined icono-input">person</span>
                            <input class="control-formulario" id="cedula" name="cedula" placeholder="1.234.567-8" required type="text" maxlength="8" inputmode="numeric">
                        </div>
                        <span class="mensaje-validacion" id="errorCedula"></span>
                    </div>

                    <div class="grupo-formulario">
                        <label class="etiqueta-formulario" for="contrasena">Contraseña</label>
                        <div class="contenedor-input">
                            <span class="material-symbols-outlined icono-input">lock</span>
                            <input class="control-formulario pr-12" id="contrasena" name="contrasena" placeholder="••••••••" required type="password">
                            <button class="btn-mostrar-contrasena" type="button" aria-label="Mostrar contraseña">
                                <span class="material-symbols-outlined" id="mostrarContrasena">visibility</span>
                            </button>
                        </div>
                        <span class="mensaje-validacion" id="errorContrasena"></span>
                    </div>

                    <div class="acciones-formulario">
                        <label class="contenedor-checkbox">
                            <input class="checkbox-personalizado" type="checkbox">
                            <span class="texto-checkbox">Recordarme</span>
                        </label>
                        <a class="enlace-olvido" href="#">¿Olvidó su contraseña?</a>
                    </div>

                    <button class="btn-enviar" id="botonEnviar" type="submit">
                        <span class="texto-boton">Ingresar</span>
                        <span class="material-symbols-outlined icono-boton">login</span>
                    </button>
                </form>

                <div class="pie-tarjeta">
                    <p>¿No tiene credenciales?</p>
                    <a class="btn-contorno" href="#">Solicitar acceso</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="pie-global">
        <div class="contenido-pie">
            <span class="copyright-pie">
                © 2026 Hospital de Clínicas - Sistema de Gestión Documental. Uso Institucional.
            </span>
            <div class="enlaces-pie">
                <a href="#">Protocolo de Privacidad</a>
                <a href="#">Seguridad</a>
                <a href="#">Soporte IT</a>
            </div>
        </div>
    </footer>

    <script src="assets/js/login.js"></script>
</body>
</html>