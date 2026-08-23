const ENDPOINT_USUARIOS = '../../Servicios Comunes/Autenticacion/procesar-usuario.php';

const modalEdicion = document.getElementById('modalEdicion');
const formEdicionUsuario = document.getElementById('formEdicionUsuario');
const mensajeErrorEdicion = document.getElementById('mensajeErrorEdicion');
const btnGuardarEdicion = document.getElementById('btnGuardarEdicion');


function abrirModalEdicion(idUsuario, nombre, apellido, email, activo, esCuentaPropia) {
    document.getElementById('editIdUsuario').value = idUsuario;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editApellido').value = apellido;
    document.getElementById('editEmail').value = email;
    document.getElementById('editContrasena').value = '';

    const checkboxActivo = document.getElementById('editActivo');
    checkboxActivo.checked = activo;
    checkboxActivo.disabled = esCuentaPropia;

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

/**
 * Pide confirmación antes de suspender o reactivar un usuario, y ejecuta
 * la acción vía AJAX si el administrador confirma.
 */
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
