/*
 * Encuesta pública: valida en el navegador, envía por fetch y muestra el
 * agradecimiento en la misma página (sin alert()).
 */
(function () {
    'use strict';

    const formulario = document.getElementById('surveyForm');
    const tarjetaEncuesta = document.getElementById('tarjetaEncuesta');
    const tarjetaGracias = document.getElementById('tarjetaGracias');
    const tituloGracias = document.getElementById('tituloGracias');
    const mensajeError = document.getElementById('mensajeError');
    const botonEnviar = document.getElementById('botonEnviar');
    const comentario = document.getElementById('comments');
    const contador = document.getElementById('contadorComentario');

    function mostrarError(texto) {
        mensajeError.textContent = texto;
        mensajeError.hidden = texto === '';
    }

    function actualizarContador() {
        contador.textContent = String(comentario.value.length);
    }
    comentario.addEventListener('input', actualizarContador);
    actualizarContador();

    // Al elegir una respuesta se oculta el aviso de error anterior
    formulario.addEventListener('change', function () { mostrarError(''); });

    function mostrarGracias(titulo) {
        if (titulo) tituloGracias.textContent = titulo;
        tarjetaEncuesta.hidden = true;
        tarjetaGracias.hidden = false;
        tarjetaGracias.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    formulario.addEventListener('submit', async function (evento) {
        evento.preventDefault();
        mostrarError('');

        if (!formulario.querySelector('input[name="calificacion_acceso"]:checked')) {
            mostrarError('Califique del 1 al 5 qué tan fácil fue acceder a la información.');
            return;
        }
        if (!formulario.querySelector('input[name="utilidad"]:checked')) {
            mostrarError('Indique qué tan útil le resultó la información.');
            return;
        }

        botonEnviar.disabled = true;
        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                credentials: 'same-origin'
            });
            const datos = await respuesta.json();
            if (datos.exito) {
                formulario.reset();
                actualizarContador();
                mostrarGracias('¡Gracias por su opinión!');
                return;
            }
            mostrarError(datos.mensaje || 'No se pudo enviar la encuesta.');
        } catch (error) {
            mostrarError('No se pudo conectar con el servidor. Intente nuevamente.');
        }
        botonEnviar.disabled = false;
    });
})();
