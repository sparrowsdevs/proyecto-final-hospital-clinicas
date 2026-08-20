/*
 * Lógica de la vista de login (index.php): toggle de visibilidad de
 * contraseña, envío del formulario vía AJAX (fetch) y efecto parallax
 * del fondo.
 */

const loginForm = document.getElementById('loginForm');
const submitBtn = document.getElementById('submitBtn');
const togglePassword = document.getElementById('togglePassword');
const cedulaInput = document.getElementById('cedula');
const passwordInput = document.getElementById('password');
const mensajeError = document.getElementById('mensajeError');
const errorCedula = document.getElementById('errorCedula');
const errorPassword = document.getElementById('errorPassword');

// Validaciones de formulario (experiencia de usuario)
// La validación real y definitiva sigue ocurriendo en el backend.


/**
 * Valida el formato de la cédula: solo dígitos, 7 u 8 caracteres.
 * @returns {boolean} true si es válida.
 */
function validarCedula() {
    const valor = cedulaInput.value.trim();
    const esValida = /^\d{7,8}$/.test(valor);

    if (valor === '') {
        mostrarError(cedulaInput, errorCedula, 'La cédula es obligatoria.');
    } else if (!esValida) {
        mostrarError(cedulaInput, errorCedula, 'Ingrese solo números (7 u 8 dígitos, sin puntos ni guión).');
    } else {
        limpiarError(cedulaInput, errorCedula);
    }

    return esValida;
}

/**
 * Valida que la contraseña no esté vacía.
 * @returns {boolean} true si es válida.
 */
function validarPassword() {
    const esValida = passwordInput.value.length > 0;

    if (!esValida) {
        mostrarError(passwordInput, errorPassword, 'La contraseña es obligatoria.');
    } else {
        limpiarError(passwordInput, errorPassword);
    }

    return esValida;
}

function mostrarError(input, contenedorError, mensaje) {
    input.classList.add('input-invalido');
    contenedorError.textContent = mensaje;
}

function limpiarError(input, contenedorError) {
    input.classList.remove('input-invalido');
    contenedorError.textContent = '';
}

/**
 * Revisa el estado global del formulario y habilita/deshabilita el botón.
 * Se llama tras cada validación individual, no dispara mensajes nuevos.
 */
function actualizarEstadoBoton() {
    const cedulaOk = /^\d{7,8}$/.test(cedulaInput.value.trim());
    const passwordOk = passwordInput.value.length > 0;
    submitBtn.disabled = !(cedulaOk && passwordOk);
}

// Validación mientras el usuario escribe la cédula (solo permite dígitos)
cedulaInput.addEventListener('input', () => {
    cedulaInput.value = cedulaInput.value.replace(/\D/g, ''); // Elimina cualquier no-dígito al tipear
    validarCedula();
    actualizarEstadoBoton();
});

// Validación de contraseña al perder el foco (no interrumpe mientras escribe)
passwordInput.addEventListener('blur', validarPassword);
passwordInput.addEventListener('input', actualizarEstadoBoton);

// Estado inicial: botón deshabilitado hasta que completen ambos campos
submitBtn.disabled = true;

// Toggle Password Visibility
togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? 'visibility_off' : 'visibility';
});

// Envío real del formulario vía AJAX
loginForm.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    // Doble chequeo: por si el envío se dispara sin pasar por el botón (ej. tecla Enter)
    const cedulaValida = validarCedula();
    const passwordValida = validarPassword();
    if (!cedulaValida || !passwordValida) {
        actualizarEstadoBoton();
        return;
    }

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