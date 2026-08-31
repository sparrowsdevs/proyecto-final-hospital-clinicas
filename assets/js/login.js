/*
 * Lógica de la vista de login (index.php): mostrar/ocultar contraseña,
 * validación del formulario, envío vía AJAX (fetch) y efecto de
 * movimiento (parallax) en la imagen de fondo.
 */

// Referencias a los elementos del formulario (por su "id" en el HTML)
const formularioLogin = document.getElementById('formularioLogin');
const botonEnviar = document.getElementById('botonEnviar');
const mostrarContrasena = document.getElementById('mostrarContrasena');
const inputCedula = document.getElementById('cedula');
const inputContrasena = document.getElementById('contrasena');
const mensajeError = document.getElementById('mensajeError');
const errorCedula = document.getElementById('errorCedula');
const errorContrasena = document.getElementById('errorContrasena');

// ============================================
// Validaciones del formulario (solo para mejorar la experiencia del
// usuario). La validación real y definitiva siempre ocurre en el backend.
// ============================================

/**
 * Revisa que la cédula tenga el formato correcto: solo números, 7 u 8 dígitos.
 * @returns {boolean} true si es válida.
 */
function validarCedula() {
    const valor = inputCedula.value.trim();
    const esValida = /^\d{7,8}$/.test(valor);

    if (valor === '') {
        mostrarError(inputCedula, errorCedula, 'La cédula es obligatoria.');
    } else if (!esValida) {
        mostrarError(inputCedula, errorCedula, 'Ingrese solo números (7 u 8 dígitos, sin puntos ni guión).');
    } else {
        limpiarError(inputCedula, errorCedula);
    }

    return esValida;
}

/**
 * Revisa que el campo de contraseña no esté vacío.
 * @returns {boolean} true si es válida.
 */
function validarContrasena() {
    const esValida = inputContrasena.value.length > 0;

    if (!esValida) {
        mostrarError(inputContrasena, errorContrasena, 'La contraseña es obligatoria.');
    } else {
        limpiarError(inputContrasena, errorContrasena);
    }

    return esValida;
}

/**
 * Marca un campo como inválido (borde rojo) y muestra el mensaje de error.
 */
function mostrarError(campoInput, contenedorError, mensaje) {
    campoInput.classList.add('input-invalido');
    contenedorError.textContent = mensaje;
}

/**
 * Quita la marca de inválido de un campo y borra su mensaje de error.
 */
function limpiarError(campoInput, contenedorError) {
    campoInput.classList.remove('input-invalido');
    contenedorError.textContent = '';
}

/**
 * Habilita el botón "Ingresar" solo cuando ambos campos son válidos.
 * Se llama después de cada validación individual, sin mostrar mensajes nuevos.
 */
function actualizarEstadoBoton() {
    const cedulaValida = /^\d{7,8}$/.test(inputCedula.value.trim());
    const contrasenaValida = inputContrasena.value.length > 0;
    botonEnviar.disabled = !(cedulaValida && contrasenaValida);
}

// Mientras el usuario escribe la cédula: solo permite números y valida al vuelo
inputCedula.addEventListener('input', () => {
    inputCedula.value = inputCedula.value.replace(/\D/g, ''); // Elimina cualquier caracter que no sea número
    validarCedula();
    actualizarEstadoBoton();
});

// Valida la contraseña recién cuando el usuario sale del campo (no mientras escribe)
inputContrasena.addEventListener('blur', validarContrasena);
inputContrasena.addEventListener('input', actualizarEstadoBoton);

// Al cargar la página, el botón arranca deshabilitado hasta completar ambos campos
botonEnviar.disabled = true;

// ============================================
// Mostrar u ocultar la contraseña al hacer clic en el ícono del ojo
// ============================================
mostrarContrasena.addEventListener('click', () => {
    const estaOculta = inputContrasena.type === 'password';
    inputContrasena.type = estaOculta ? 'text' : 'password';
    mostrarContrasena.textContent = estaOculta ? 'visibility_off' : 'visibility';
});

// ============================================
// Envío del formulario de login vía AJAX (sin recargar la página)
// ============================================
formularioLogin.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    // Doble chequeo: por si el envío se dispara sin pasar por el botón (ej. tecla Enter)
    const cedulaValida = validarCedula();
    const contrasenaValida = validarContrasena();
    if (!cedulaValida || !contrasenaValida) {
        actualizarEstadoBoton();
        return;
    }

    const contenidoOriginalBoton = botonEnviar.innerHTML;
    botonEnviar.disabled = true;
    botonEnviar.classList.add('cargando-estado');
    mensajeError.style.display = 'none';

    // Muestra un ícono girando mientras se espera la respuesta del servidor
    botonEnviar.innerHTML = `
        <svg class="cargando" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="cargando-pista" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="cargando-cabeza" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Verificando...</span>
    `;

    try {
        const datosFormulario = new FormData(formularioLogin);

        const respuesta = await fetch('Servicios Comunes/Autenticacion/procesar-login.php', {
            method: 'POST',
            body: datosFormulario,
        });

        const resultado = await respuesta.json();

        if (resultado.exito) {
            window.location.href = resultado.redireccion;
            return; // No restauramos el botón: la página está navegando a otra pantalla
        }

        mensajeError.textContent = resultado.mensaje;
        mensajeError.style.display = 'block';
    } catch (error) {
        mensajeError.textContent = 'No fue posible conectar con el servidor. Intente nuevamente.';
        mensajeError.style.display = 'block';
    } finally {
        botonEnviar.innerHTML = contenidoOriginalBoton;
        botonEnviar.disabled = false;
        botonEnviar.classList.remove('cargando-estado');
    }
});

// ============================================
// Efecto de movimiento sutil (parallax) en la imagen de fondo,
// siguiendo el mouse por la pantalla
// ============================================
document.addEventListener('mousemove', (evento) => {
    const moverX = (evento.clientX - window.innerWidth / 2) * 0.01;
    const moverY = (evento.clientY - window.innerHeight / 2) * 0.01;
    const imagenFondo = document.querySelector('.imagen-fondo');
    if (imagenFondo) {
        imagenFondo.style.transform = `scale(1.1) translate(${moverX}px, ${moverY}px)`;
    }
});
