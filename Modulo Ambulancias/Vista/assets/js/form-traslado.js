/*
 * Formulario de Alta de Traslado.
 * - Valida los datos en el navegador (el servidor vuelve a validar todo).
 * - Autocompleta nombre y apellido si la cédula ya está registrada.
 * - Muestra solo los vehículos compatibles con el elemento elegido.
 * - Permite agregar ubicaciones nuevas sin salir del formulario.
 * - Envía el alta por AJAX a ../Controlador/procesar-traslado.php.
 */

const ENDPOINT_TRASLADOS = '../Controlador/procesar-traslado.php';

const formulario = document.getElementById('transfer-form');
const mensajeError = document.getElementById('mensajeError');
const mensajeExito = document.getElementById('mensajeExito');
const textoExito = document.getElementById('textoExito');
const btnRegistrar = document.getElementById('btnRegistrar');

const campoCi = document.getElementById('ciPaciente');
const campoNombre = document.getElementById('nombrePaciente');
const campoApellido = document.getElementById('apellidoPaciente');
const ayudaPaciente = document.getElementById('ayudaPaciente');
const AYUDA_CI_INICIAL = ayudaPaciente.textContent;

const selectElemento = document.getElementById('idTipoElemento');
const selectVehiculo = document.getElementById('idVehiculo');
const ayudaVehiculo = document.getElementById('ayudaVehiculo');
const estadoInicial = document.getElementById('estadoInicial');
const selectOrigen = document.getElementById('idOrigen');
const selectDestino = document.getElementById('idDestino');
const selectChofer = document.getElementById('idChofer');
const selectEnfermero = document.getElementById('idEnfermero');
const campoFecha = document.getElementById('horaPrevista');

let pacienteAutocompletado = false;

/* ---------- Mensajes ---------- */

function mostrarError(texto) {
    mensajeExito.hidden = true;
    mensajeError.textContent = texto;
    mensajeError.hidden = false;
    mensajeError.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function ocultarMensajes() {
    mensajeError.hidden = true;
    mensajeExito.hidden = true;
}

/* ---------- Paciente ---------- */

function liberarDatosPaciente() {
    campoNombre.readOnly = false;
    campoApellido.readOnly = false;
    if (pacienteAutocompletado) {
        campoNombre.value = '';
        campoApellido.value = '';
    }
    pacienteAutocompletado = false;
    ayudaPaciente.textContent = AYUDA_CI_INICIAL;
}

async function buscarPaciente() {
    const ci = campoCi.value;
    if (!/^\d{7,8}$/.test(ci)) {
        liberarDatosPaciente();
        return;
    }

    try {
        const datos = new FormData();
        datos.append('accion', 'buscar-paciente');
        datos.append('ci_paciente', ci);
        const respuesta = await fetch(ENDPOINT_TRASLADOS, { method: 'POST', body: datos });
        const resultado = await respuesta.json();

        // La cédula pudo cambiar mientras se esperaba la respuesta
        if (campoCi.value !== ci) return;

        if (resultado.exito && resultado.paciente) {
            campoNombre.value = resultado.paciente.nombre;
            campoApellido.value = resultado.paciente.apellido;
            campoNombre.readOnly = true;
            campoApellido.readOnly = true;
            pacienteAutocompletado = true;
            ayudaPaciente.textContent = 'Paciente ya registrado: se cargaron sus datos.';
        } else {
            liberarDatosPaciente();
        }
    } catch (error) {
        // Si falla la búsqueda, el usuario puede completar los datos a mano
        liberarDatosPaciente();
    }
}

campoCi.addEventListener('input', () => {
    campoCi.value = campoCi.value.replace(/\D/g, '').slice(0, 8);
    if (campoCi.value.length >= 7) {
        buscarPaciente();
    } else {
        liberarDatosPaciente();
    }
});

/* ---------- Vehículos compatibles con el elemento ---------- */

function filtrarVehiculos() {
    const idElemento = selectElemento.value;
    let disponibles = 0;

    Array.from(selectVehiculo.options).forEach((opcion) => {
        if (opcion.value === '') return;
        const compatibles = (opcion.dataset.compatibles || '').split(',');
        const esCompatible = idElemento === '' || compatibles.includes(idElemento);
        opcion.hidden = !esCompatible;
        opcion.disabled = !esCompatible;
        if (esCompatible) disponibles++;
    });

    const seleccionado = selectVehiculo.selectedOptions[0];
    if (seleccionado && seleccionado.disabled) {
        selectVehiculo.value = '';
    }

    if (idElemento === '') {
        ayudaVehiculo.textContent = '';
    } else if (disponibles === 0) {
        ayudaVehiculo.textContent = 'No hay vehículos compatibles con este elemento.';
    } else {
        ayudaVehiculo.textContent = 'Solo se muestran vehículos compatibles con el elemento elegido.';
    }
    actualizarEstadoInicial();
}

function actualizarEstadoInicial() {
    estadoInicial.textContent = selectVehiculo.value !== '' ? 'Vehículo Asignado' : 'Solicitado';
}

selectElemento.addEventListener('change', filtrarVehiculos);
selectVehiculo.addEventListener('change', actualizarEstadoInicial);

/* ---------- Origen y destino distintos ---------- */

function bloquearMismaUbicacion() {
    Array.from(selectDestino.options).forEach((opcion) => {
        opcion.disabled = opcion.value !== '' && opcion.value === selectOrigen.value;
    });
    Array.from(selectOrigen.options).forEach((opcion) => {
        opcion.disabled = opcion.value !== '' && opcion.value === selectDestino.value;
    });
}

selectOrigen.addEventListener('change', bloquearMismaUbicacion);
selectDestino.addEventListener('change', bloquearMismaUbicacion);

/* ---------- Modal: agregar ubicación ---------- */

const modalUbicacion = document.getElementById('modalUbicacion');
const formUbicacion = document.getElementById('formUbicacion');
const campoNombreUbicacion = document.getElementById('nombreUbicacion');
const errorUbicacion = document.getElementById('errorUbicacion');
const btnGuardarUbicacion = document.getElementById('btnGuardarUbicacion');
let selectDestinoModal = null; // select donde quedará elegida la nueva ubicación

function abrirModalUbicacion(idSelect) {
    selectDestinoModal = document.getElementById(idSelect);
    formUbicacion.reset();
    errorUbicacion.textContent = '';
    modalUbicacion.hidden = false;
    campoNombreUbicacion.focus();
}

function cerrarModalUbicacion() {
    modalUbicacion.hidden = true;
}

document.querySelectorAll('.btn-nueva-ubicacion').forEach((boton) => {
    boton.addEventListener('click', () => abrirModalUbicacion(boton.dataset.destino));
});
document.getElementById('btnCancelarUbicacion').addEventListener('click', cerrarModalUbicacion);
modalUbicacion.addEventListener('click', (evento) => {
    if (evento.target === modalUbicacion) cerrarModalUbicacion();
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && !modalUbicacion.hidden) cerrarModalUbicacion();
});

function agregarOpcionUbicacion(select, ubicacion) {
    const yaExiste = Array.from(select.options).some((o) => o.value === String(ubicacion.id_ubicacion));
    if (yaExiste) return;
    const opcion = document.createElement('option');
    opcion.value = String(ubicacion.id_ubicacion);
    opcion.textContent = ubicacion.nombre; // textContent evita inyectar HTML
    select.appendChild(opcion);
}

formUbicacion.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    errorUbicacion.textContent = '';

    const nombre = campoNombreUbicacion.value.trim();
    if (nombre.length < 3) {
        errorUbicacion.textContent = 'Ingrese un nombre de al menos 3 caracteres.';
        return;
    }

    btnGuardarUbicacion.disabled = true;
    try {
        const datos = new FormData();
        datos.append('accion', 'crear-ubicacion');
        datos.append('nombre', nombre);
        const respuesta = await fetch(ENDPOINT_TRASLADOS, { method: 'POST', body: datos });
        const resultado = await respuesta.json();

        if (!resultado.exito) {
            errorUbicacion.textContent = resultado.mensaje;
            return;
        }

        agregarOpcionUbicacion(selectOrigen, resultado.ubicacion);
        agregarOpcionUbicacion(selectDestino, resultado.ubicacion);
        selectDestinoModal.value = String(resultado.ubicacion.id_ubicacion);
        bloquearMismaUbicacion();
        cerrarModalUbicacion();
    } catch (error) {
        errorUbicacion.textContent = 'No fue posible conectar con el servidor. Intente nuevamente.';
    } finally {
        btnGuardarUbicacion.disabled = false;
    }
});

/* ---------- Envío del formulario ---------- */

/** Devuelve el primer mensaje de error de validación, o '' si todo está bien. */
function validarFormulario() {
    const patronNombre = /^\p{L}[\p{L}' .-]{1,99}$/u;

    if (!/^\d{7,8}$/.test(campoCi.value)) return 'La cédula debe tener 7 u 8 dígitos (sin puntos ni guión).';
    if (!patronNombre.test(campoNombre.value.trim())) return 'Ingrese un nombre válido para el paciente.';
    if (!patronNombre.test(campoApellido.value.trim())) return 'Ingrese un apellido válido para el paciente.';
    if (selectElemento.value === '') return 'Seleccione el elemento a trasladar.';
    if (campoFecha.value === '') return 'Indique la fecha y hora prevista del traslado.';
    if (new Date(campoFecha.value).getTime() < Date.now() - 5 * 60 * 1000) {
        return 'La fecha y hora prevista no puede estar en el pasado.';
    }
    if (selectOrigen.value === '') return 'Seleccione la ubicación de origen.';
    if (selectDestino.value === '') return 'Seleccione la ubicación de destino.';
    if (selectOrigen.value === selectDestino.value) return 'El origen y el destino no pueden ser la misma ubicación.';
    if (selectChofer.value !== '' && selectChofer.value === selectEnfermero.value) {
        return 'El chofer y el personal acompañante deben ser personas distintas.';
    }
    return '';
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    ocultarMensajes();

    const errorValidacion = validarFormulario();
    if (errorValidacion !== '') {
        mostrarError(errorValidacion);
        return;
    }

    btnRegistrar.disabled = true;
    try {
        const datos = new FormData(formulario);
        datos.append('accion', 'crear');

        const respuesta = await fetch(ENDPOINT_TRASLADOS, { method: 'POST', body: datos });
        const resultado = await respuesta.json();

        if (!resultado.exito) {
            mostrarError(resultado.mensaje);
            return;
        }

        textoExito.textContent = `Traslado ${resultado.codigo} registrado correctamente (estado: ${resultado.estado}).`;
        mensajeExito.hidden = false;
        mensajeExito.scrollIntoView({ behavior: 'smooth', block: 'center' });

        formulario.reset();
        pacienteAutocompletado = false;
        liberarDatosPaciente();
        filtrarVehiculos();
        bloquearMismaUbicacion();
    } catch (error) {
        mostrarError('No fue posible conectar con el servidor. Intente nuevamente.');
    } finally {
        btnRegistrar.disabled = false;
    }
});

// Estado inicial de la pantalla
filtrarVehiculos();
