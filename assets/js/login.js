/**
 * login.js
 * assets/js/
 *
 * Proyecto S.I.G.S.M. - Hospital de Clínicas
 * Sparrows Devs
 *
 * Lógica de la vista de login (index.php): toggle de visibilidad de
 * contraseña, envío del formulario vía AJAX (fetch) y efecto parallax
 * del fondo.
 */

const loginForm = document.getElementById('loginForm');
const submitBtn = document.getElementById('submitBtn');
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const mensajeError = document.getElementById('mensajeError');

// Toggle Password Visibility
togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? 'visibility_off' : 'visibility';
});

// Envío real del formulario vía AJAX
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