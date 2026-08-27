/*
 * Lógica del CRUD de documentos: apertura/cierre de los modales de
 * creación y edición, envío vía AJAX, y confirmación de suspender/
 * reactivar. Todas las acciones se procesan en
 * Modulo Documentacion/Controlador/procesar-documento.php.
 */

const ENDPOINT_DOCUMENTOS = '../Controlador/procesar-documento.php';


// Modal de creación


const uploadModal = document.getElementById('uploadModal');
const formCreacionDocumento = document.getElementById('formCreacionDocumento');
const mensajeErrorCreacion = document.getElementById('mensajeErrorCreacion');
const btnGuardarCreacion = document.getElementById('btnGuardarCreacion');

function abrirModalCreacion() {
    formCreacionDocumento.reset();
    mensajeErrorCreacion.textContent = '';

    uploadModal.classList.remove('hidden');
    setTimeout(() => {
        uploadModal.querySelector('.modal-window').classList.add('show');
    }, 10);
}

function cerrarModalCreacion() {
    uploadModal.querySelector('.modal-window').classList.remove('show');
    setTimeout(() => {
        uploadModal.classList.add('hidden');
    }, 300);
}

uploadModal.addEventListener('click', (evento) => {
    if (evento.target === uploadModal) cerrarModalCreacion();
});

formCreacionDocumento.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    mensajeErrorCreacion.textContent = '';
    btnGuardarCreacion.disabled = true;

    try {
        const datosFormulario = new FormData(formCreacionDocumento);
        datosFormulario.append('accion', 'crear');

        const respuesta = await fetch(ENDPOINT_DOCUMENTOS, {
            method: 'POST',
            body: datosFormulario,
        });

        const resultado = await respuesta.json();

        if (resultado.exito) {
            window.location.reload();
            return;
        }

        mensajeErrorCreacion.textContent = resultado.mensaje;
    } catch (error) {
        mensajeErrorCreacion.textContent = 'No fue posible conectar con el servidor. Intente nuevamente.';
    } finally {
        btnGuardarCreacion.disabled = false;
    }
});


// Modal de edición


const editModal = document.getElementById('editModal');
const formEdicionDocumento = document.getElementById('formEdicionDocumento');
const mensajeErrorEdicion = document.getElementById('mensajeErrorEdicion');
const btnGuardarEdicion = document.getElementById('btnGuardarEdicion');

function abrirModalEdicion(idDocumento, titulo, descripcion, archivoUrl, idCategoria) {
    document.getElementById('editIdDocumento').value = idDocumento;
    document.getElementById('editTitulo').value = titulo;
    document.getElementById('editDescripcion').value = descripcion;
    document.getElementById('editArchivoUrl').value = archivoUrl;
    document.getElementById('editCategoria').value = idCategoria;

    mensajeErrorEdicion.textContent = '';

    editModal.classList.remove('hidden');
    setTimeout(() => {
        editModal.querySelector('.modal-window').classList.add('show');
    }, 10);
}

function cerrarModalEdicion() {
    editModal.querySelector('.modal-window').classList.remove('show');
    setTimeout(() => {
        editModal.classList.add('hidden');
    }, 300);
}

editModal.addEventListener('click', (evento) => {
    if (evento.target === editModal) cerrarModalEdicion();
});

formEdicionDocumento.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    mensajeErrorEdicion.textContent = '';
    btnGuardarEdicion.disabled = true;

    try {
        const datosFormulario = new FormData(formEdicionDocumento);
        datosFormulario.append('accion', 'actualizar');

        const respuesta = await fetch(ENDPOINT_DOCUMENTOS, {
            method: 'POST',
            body: datosFormulario,
        });

        const resultado = await respuesta.json();

        if (resultado.exito) {
            window.location.reload();
            return;
        }

        mensajeErrorEdicion.textContent = resultado.mensaje;
    } catch (error) {
        mensajeErrorEdicion.textContent = 'No fue posible conectar con el servidor. Intente nuevamente.';
    } finally {
        btnGuardarEdicion.disabled = false;
    }
});


// Suspender / Reactivar

async function confirmarCambioEstado(idDocumento, accion) {
    const mensajeConfirmacion = accion === 'suspender'
        ? '¿Confirma que desea suspender este documento? Dejará de estar visible en el repositorio.'
        : '¿Confirma que desea reactivar este documento?';

    if (!window.confirm(mensajeConfirmacion)) {
        return;
    }

    try {
        const datosFormulario = new FormData();
        datosFormulario.append('accion', accion);
        datosFormulario.append('id_documento', idDocumento);

        const respuesta = await fetch(ENDPOINT_DOCUMENTOS, {
            method: 'POST',
            body: datosFormulario,
        });

        const resultado = await respuesta.json();

        if (resultado.exito) {
            window.location.reload();
            return;
        }

        window.alert(resultado.mensaje);
    } catch (error) {
        window.alert('No fue posible conectar con el servidor. Intente nuevamente.');
    }
}

// Cerrar cualquier modal abierto con Escape
document.addEventListener('keydown', (evento) => {
    if (evento.key !== 'Escape') return;
    if (!uploadModal.classList.contains('hidden')) cerrarModalCreacion();
    if (!editModal.classList.contains('hidden')) cerrarModalEdicion();
});
