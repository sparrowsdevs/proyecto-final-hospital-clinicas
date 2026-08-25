/*
 *
 * Lógica de la pantalla Gestión de Usuarios: apertura/cierre del modal de
 * edición, envío del formulario vía AJAX, y confirmación de suspender/
 * reactivar. Todas las acciones se procesan en procesar-usuario.php.
 */

const ENDPOINT_USUARIOS = '../../Servicios Comunes/Autenticacion/procesar-usuario.php';

const modalEdicion = document.getElementById('modalEdicion');
const formEdicionUsuario = document.getElementById('formEdicionUsuario');
const mensajeErrorEdicion = document.getElementById('mensajeErrorEdicion');
const btnGuardarEdicion = document.getElementById('btnGuardarEdicion');


function abrirModalEdicion(idUsuario, nombre, apellido, email, activo, esCuentaPropia, idRolActual) {
    document.getElementById('editIdUsuario').value = idUsuario;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editApellido').value = apellido;
    document.getElementById('editEmail').value = email;
    document.getElementById('editContrasena').value = '';

    const checkboxActivo = document.getElementById('editActivo');
    checkboxActivo.checked = activo;
    checkboxActivo.disabled = esCuentaPropia;

    const selectRol = document.getElementById('editRol');
    selectRol.value = idRolActual !== null ? String(idRolActual) : '';
    selectRol.disabled = esCuentaPropia;

    const rolHint = document.getElementById('editRolHint');
    rolHint.textContent = esCuentaPropia ? 'No puede cambiar su propio rol.' : '';

    mensajeErrorEdicion.textContent = '';

    modalEdicion.classList.remove('hidden');
    setTimeout(() => {
        modalEdicion.querySelector('.modal-window').classList.add('show');
    }, 10);
}

function cerrarModalEdicion() {
    modalEdicion.querySelector('.modal-window').classList.remove('show');
    setTimeout(() => {
        modalEdicion.classList.add('hidden');
    }, 300);
}

// Cerrar al hacer click fuera del modal o con Escape
modalEdicion.addEventListener('click', (evento) => {
    if (evento.target === modalEdicion) cerrarModalEdicion();
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && !modalEdicion.classList.contains('hidden')) {
        cerrarModalEdicion();
    }
});

/**
 * Envía el formulario de edición vía AJAX.
 */
formEdicionUsuario.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    mensajeErrorEdicion.textContent = '';
    btnGuardarEdicion.disabled = true;

    try {
        const datosFormulario = new FormData(formEdicionUsuario);
        datosFormulario.append('accion', 'actualizar');

        // El checkbox solo envía su valor si está marcado; si está deshabilitado
        // (cuenta propia) forzamos activo=1 igual, ya que no se puede desactivar.
        const checkboxActivo = document.getElementById('editActivo');
        datosFormulario.set('activo', checkboxActivo.checked ? '1' : '0');

        // Un <select disabled> no se incluye en FormData; si está deshabilitado
        // (cuenta propia), forzamos igual su valor actual para no perderlo.
        const selectRol = document.getElementById('editRol');
        datosFormulario.set('id_rol', selectRol.value);

        const respuesta = await fetch(ENDPOINT_USUARIOS, {
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


async function confirmarCambioEstado(idUsuario, accion) {
    const mensajeConfirmacion = accion === 'suspender'
        ? '¿Confirma que desea suspender a este usuario? No podrá iniciar sesión hasta ser reactivado.'
        : '¿Confirma que desea reactivar a este usuario?';

    if (!window.confirm(mensajeConfirmacion)) {
        return;
    }

    try {
        const datosFormulario = new FormData();
        datosFormulario.append('accion', accion);
        datosFormulario.append('id_usuario', idUsuario);

        const respuesta = await fetch(ENDPOINT_USUARIOS, {
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


// Modal de creación de usuario


const modalCreacion = document.getElementById('modalCreacion');
const formCreacionUsuario = document.getElementById('formCreacionUsuario');
const mensajeErrorCreacion = document.getElementById('mensajeErrorCreacion');
const btnGuardarCreacion = document.getElementById('btnGuardarCreacion');
const crearCedulaInput = document.getElementById('crearCedula');

function abrirModalCreacion() {
    formCreacionUsuario.reset();
    mensajeErrorCreacion.textContent = '';

    modalCreacion.classList.remove('hidden');
    setTimeout(() => {
        modalCreacion.querySelector('.modal-window').classList.add('show');
    }, 10);
}

function cerrarModalCreacion() {
    modalCreacion.querySelector('.modal-window').classList.remove('show');
    setTimeout(() => {
        modalCreacion.classList.add('hidden');
    }, 300);
}

modalCreacion.addEventListener('click', (evento) => {
    if (evento.target === modalCreacion) cerrarModalCreacion();
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && !modalCreacion.classList.contains('hidden')) {
        cerrarModalCreacion();
    }
});

// Solo permite dígitos en el campo de cédula, igual que en el login
crearCedulaInput.addEventListener('input', () => {
    crearCedulaInput.value = crearCedulaInput.value.replace(/\D/g, '');
});

formCreacionUsuario.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    mensajeErrorCreacion.textContent = '';
    btnGuardarCreacion.disabled = true;

    try {
        const datosFormulario = new FormData(formCreacionUsuario);
        datosFormulario.append('accion', 'crear');

        const respuesta = await fetch(ENDPOINT_USUARIOS, {
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
