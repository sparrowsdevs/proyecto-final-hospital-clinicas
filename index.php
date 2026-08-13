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

    <div class="bg-wrapper">
        <img class="bg-image" alt="Hospital hallway blur" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCbAsT5R3OSCP9oyjy1_ujCis5LY0KmCsYBLqs9HOFkK8noivD8ZyCgo42fYS6DNRMpfVY6AkENFMdPgMkt_CHpZ2dVi3R-VUhrLq1USRPnFHpGIML76kd-22FnxczBAlW7SMlev1D5PxdyEZTAkWH94DTbYC4Fw9GTTbJ_PTtcgyvaElPr6rGMaq5GQz5WWR2JZfM1GbXI1AS9tMHCpmWA-cf8HMO4YG2vsUHs7cl3Gkv23Tv7fznqzycWDJtm3ZjW3flC1n7XffXi">
    </div>

    <main class="main-content">
        <div class="login-card">
            <div class="accent-bar"></div>
            
            <div class="card-body">
                <div class="card-header">
                    <img alt="Hospital de Clínicas Logo" class="brand-logo" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
                    <h1 class="title">Inicio de Sesión</h1>
                    <p class="subtitle">Módulo de Gestión Documental</p>
                </div>

                <form class="login-form" id="loginForm">

                    <div class="mensaje-error" id="mensajeError" role="alert" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label" for="cedula">Cédula de Identidad</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">person</span>
                            <input class="form-control" id="cedula" name="cedula" placeholder="1.234.567-8" required type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">lock</span>
                            <input class="form-control pr-12" id="password" name="contrasena" placeholder="••••••••" required type="password">
                            <button class="toggle-password-btn" type="button" aria-label="Mostrar contraseña">
                                <span class="material-symbols-outlined" id="togglePassword">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <label class="checkbox-wrapper">
                            <input class="custom-checkbox" type="checkbox">
                            <span class="checkbox-text">Recordarme</span>
                        </label>
                        <a class="forgot-link" href="#">¿Olvidó su contraseña?</a>
                    </div>

                    <button class="btn-submit" id="submitBtn" type="submit">
                        <span class="btn-text">Ingresar</span>
                        <span class="material-symbols-outlined btn-icon">login</span>
                    </button>
                </form>

                <div class="card-footer">
                    <p>¿No tiene credenciales?</p>
                    <a class="btn-outline" href="#">Solicitar acceso</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="global-footer">
        <div class="footer-content">
            <span class="footer-copy">
                © 2026 Hospital de Clínicas - Sistema de Gestión Documental. Uso Institucional.
            </span>
            <div class="footer-links">
                <a href="#">Protocolo de Privacidad</a>
                <a href="#">Seguridad</a>
                <a href="#">Soporte IT</a>
            </div>
        </div>
    </footer>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        // Toggle Password Visibility
        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'visibility_off' : 'visibility';
        });

        // Envío real del formulario vía AJAX
        const mensajeError = document.getElementById('mensajeError');

        loginForm.addEventListener('submit', async (evento) => {
            evento.preventDefault();

            const originalContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            mensajeError.style.display = 'none';

            submitBtn.innerHTML = `
                <svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="spinner-head" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Verificando...</span>
            `;

            try {
                const datosFormulario = new FormData(loginForm);

                const respuesta = await fetch('Servicios Comunes/Autenticacion/procesar-login.php', {
                    method: 'POST',
                    body: datosFormulario,
                });

                const resultado = await respuesta.json();

                if (resultado.exito) {
                    window.location.href = resultado.redireccion;
                    return; // No restauramos el botón: la página está navegando
                }

                mensajeError.textContent = resultado.mensaje;
                mensajeError.style.display = 'block';
            } catch (error) {
                mensajeError.textContent = 'No fue posible conectar con el servidor. Intente nuevamente.';
                mensajeError.style.display = 'block';
            } finally {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });

        // Parallax Effect
        document.addEventListener('mousemove', (e) => {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
            const bg = document.querySelector('.bg-image');
            if (bg) {
                bg.style.transform = `scale(1.1) translate(${moveX}px, ${moveY}px)`;
            }
        });
    </script>
</body>
</html>