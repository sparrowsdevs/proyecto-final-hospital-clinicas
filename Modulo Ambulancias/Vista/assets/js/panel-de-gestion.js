/*
 * Panel de Gestión de Traslados.
 * Filtro, búsqueda y paginación en el navegador; edición, cancelación y
 * reactivación mediante fetch a Controlador/procesar-traslado.php.
 */
(function () {
    'use strict';

    const URL_CONTROLADOR = '../Controlador/procesar-traslado.php';
    const POR_PAGINA = 10;
    const ESTADO_CANCELADO = 'Cancelado';

    const filas = Array.from(document.querySelectorAll('.fila-traslado'));
    const filaVacia = document.querySelector('.fila-vacia');
    const textoVacio = document.getElementById('textoVacio');
    const textoConteo = document.getElementById('textoConteo');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const barraFiltros = document.getElementById('barraFiltros');
    const filtroBusqueda = document.getElementById('filtroBusqueda');
    const filtroEstado = document.getElementById('filtroEstado');

    let paginaActual = 1;
    let filasFiltradas = filas.slice();

    // ------------------------------------------------------------------
    // Filtro y paginación
    // ------------------------------------------------------------------
    function aplicarFiltro() {
        const texto = filtroBusqueda.value.trim().toLowerCase();
        const estado = filtroEstado.value;

        filasFiltradas = filas.filter(function (fila) {
            const estadoFila = fila.dataset.estado;
            if (estado === 'activos' && estadoFila === ESTADO_CANCELADO) return false;
            if (estado !== 'activos' && estado !== 'todos' && estadoFila !== estado) return false;
            return texto === '' || fila.dataset.busqueda.indexOf(texto) !== -1;
        });
        paginaActual = 1;
        dibujar();
    }

    function dibujar() {
        const total = filasFiltradas.length;
        const paginas = Math.max(1, Math.ceil(total / POR_PAGINA));
        if (paginaActual > paginas) paginaActual = paginas;

        const desde = (paginaActual - 1) * POR_PAGINA;
        const hasta = Math.min(desde + POR_PAGINA, total);
        const visibles = new Set(filasFiltradas.slice(desde, hasta));

        filas.forEach(function (fila) { fila.hidden = !visibles.has(fila); });

        if (filaVacia) {
            filaVacia.hidden = total !== 0;
            if (textoVacio && filas.length > 0) textoVacio.textContent = 'Ningún traslado coincide con el filtro.';
        }
        textoConteo.textContent = total === 0
            ? 'Mostrando 0 traslados'
            : 'Mostrando ' + (desde + 1) + '–' + hasta + ' de ' + total + (total === 1 ? ' traslado' : ' traslados');
        btnAnterior.disabled = paginaActual <= 1;
        btnSiguiente.disabled = paginaActual >= paginas;
    }

    btnAnterior.addEventListener('click', function () { paginaActual--; dibujar(); });
    btnSiguiente.addEventListener('click', function () { paginaActual++; dibujar(); });
    filtroBusqueda.addEventListener('input', aplicarFiltro);
    filtroEstado.addEventListener('change', aplicarFiltro);

    document.getElementById('btnFiltrar').addEventListener('click', function () {
        const abierta = barraFiltros.hidden;
        barraFiltros.hidden = !abierta;
        this.setAttribute('aria-expanded', String(abierta));
        if (abierta) filtroBusqueda.focus();
    });

    // ------------------------------------------------------------------
    // Exportar CSV (lo que muestra el filtro actual)
    // ------------------------------------------------------------------
    function celdaCsv(valor) {
        let texto = String(valor == null ? '' : valor);
        // Evita que Excel interprete el texto como fórmula
        if (/^[=+\-@\t\r]/.test(texto)) texto = "'" + texto;
        return '"' + texto.replace(/"/g, '""') + '"';
    }

    document.getElementById('btnExportar').addEventListener('click', function () {
        if (filasFiltradas.length === 0) {
            mostrarAviso('No hay traslados para exportar.', false);
            return;
        }
        const lineas = [['ID', 'Paciente', 'CI', 'Elemento', 'Origen', 'Destino', 'Fecha prevista', 'Vehículo', 'Chofer', 'Estado']];
        filasFiltradas.forEach(function (f) {
            const d = f.dataset;
            lineas.push([d.codigo, d.paciente, d.ci, d.elemento, d.origen, d.destino, d.horaTexto, d.matricula, d.choferTexto, d.estado]);
        });
        const csv = '﻿' + lineas.map(function (l) { return l.map(celdaCsv).join(';'); }).join('\r\n');
        const enlace = document.createElement('a');
        enlace.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        enlace.download = 'traslados.csv';
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
        URL.revokeObjectURL(enlace.href);
    });

    // ------------------------------------------------------------------
    // Utilidades de interfaz
    // ------------------------------------------------------------------
    const aviso = document.getElementById('aviso');
    let temporizadorAviso = null;

    function mostrarAviso(mensaje, exito) {
        aviso.textContent = mensaje;
        aviso.className = 'aviso-flotante ' + (exito ? 'aviso-exito' : 'aviso-error');
        aviso.hidden = false;
        clearTimeout(temporizadorAviso);
        temporizadorAviso = setTimeout(function () { aviso.hidden = true; }, 4000);
    }

    function abrirModal(modal) {
        modal.hidden = false;
        document.body.classList.add('modal-abierto');
    }

    function cerrarModales() {
        document.querySelectorAll('.modal-fondo').forEach(function (m) { m.hidden = true; });
        document.body.classList.remove('modal-abierto');
    }

    document.querySelectorAll('[data-cerrar]').forEach(function (b) { b.addEventListener('click', cerrarModales); });
    document.querySelectorAll('.modal-fondo').forEach(function (fondo) {
        fondo.addEventListener('mousedown', function (e) { if (e.target === fondo) cerrarModales(); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarModales(); });

    async function enviar(datos) {
        const respuesta = await fetch(URL_CONTROLADOR, { method: 'POST', body: datos, credentials: 'same-origin' });
        if (respuesta.status === 403) {
            window.location.reload(); // protegerRuta() redirige al login
            throw new Error('Sesión expirada');
        }
        return respuesta.json();
    }

    function mostrarError(elemento, mensaje) {
        elemento.textContent = mensaje;
        elemento.hidden = !mensaje;
    }

    // Tras guardar se recarga el panel para refrescar tarjetas y tabla
    function recargarConAviso(mensaje) {
        try { sessionStorage.setItem('panelAviso', mensaje); } catch (e) { /* sin almacenamiento */ }
        window.location.reload();
    }
    try {
        const pendiente = sessionStorage.getItem('panelAviso');
        if (pendiente) {
            sessionStorage.removeItem('panelAviso');
            mostrarAviso(pendiente, true);
        }
    } catch (e) { /* sin almacenamiento */ }

    // ------------------------------------------------------------------
    // Editar
    // ------------------------------------------------------------------
    const modalEditar = document.getElementById('modalEditar');
    const formEditar = document.getElementById('formEditar');
    const editError = document.getElementById('editError');
    const editEstado = document.getElementById('editEstado');
    const editVehiculo = document.getElementById('editVehiculo');
    const ayudaVehiculo = document.getElementById('ayudaVehiculo');
    const ayudaEstado = document.getElementById('ayudaEstado');
    const btnGuardar = document.getElementById('btnGuardar');

    function abrirEdicion(fila) {
        const d = fila.dataset;
        mostrarError(editError, '');
        document.getElementById('editId').value = d.id;
        document.getElementById('tituloEditar').textContent = 'Editar traslado ' + d.codigo;
        document.getElementById('editResumen').textContent =
            d.paciente + ' (CI ' + d.ci + ') · ' + d.elemento + ' · ' + d.origen + ' → ' + d.destino;
        document.getElementById('editHora').value = d.hora;
        document.getElementById('editChofer').value = d.chofer !== '0' ? d.chofer : '';
        document.getElementById('editEnfermero').value = d.enfermero !== '0' ? d.enfermero : '';

        // Estados: el actual + las transiciones permitidas
        editEstado.innerHTML = '';
        [d.estado].concat(d.transiciones ? d.transiciones.split('|') : []).forEach(function (nombre, i) {
            const opcion = document.createElement('option');
            opcion.value = nombre;
            opcion.textContent = i === 0 ? nombre + ' (actual)' : nombre;
            editEstado.appendChild(opcion);
        });
        ayudaEstado.textContent = 'Si asigna o quita el vehículo, el estado se ajusta automáticamente.';

        // Vehículos: solo los compatibles con el elemento (se conserva el actual)
        let ocultos = 0;
        Array.from(editVehiculo.options).forEach(function (op) {
            if (op.value === '') return;
            const compatibles = (op.dataset.compatibles || '').split(',').filter(Boolean);
            const ok = compatibles.indexOf(d.idElemento) !== -1 || op.value === d.vehiculo;
            op.hidden = !ok;
            op.disabled = !ok;
            if (!ok) ocultos++;
        });
        editVehiculo.value = d.vehiculo !== '0' ? d.vehiculo : '';
        ayudaVehiculo.textContent = ocultos > 0
            ? ocultos + ' vehículo(s) no compatibles con «' + d.elemento + '» no se muestran.'
            : '';

        abrirModal(modalEditar);
        document.getElementById('editHora').focus();
    }

    formEditar.addEventListener('submit', async function (e) {
        e.preventDefault();
        mostrarError(editError, '');
        if (!document.getElementById('editHora').value) {
            mostrarError(editError, 'Indique la fecha y hora prevista del traslado.');
            return;
        }
        const datos = new FormData(formEditar);
        datos.append('accion', 'actualizar');
        btnGuardar.disabled = true;
        try {
            const r = await enviar(datos);
            if (r.exito) {
                recargarConAviso(r.mensaje);
            } else {
                mostrarError(editError, r.mensaje);
            }
        } catch (err) {
            mostrarError(editError, 'No se pudo conectar con el servidor. Intente nuevamente.');
        } finally {
            btnGuardar.disabled = false;
        }
    });

    // ------------------------------------------------------------------
    // Cancelar / reactivar (con confirmación)
    // ------------------------------------------------------------------
    const modalConfirmar = document.getElementById('modalConfirmar');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const confirmarError = document.getElementById('confirmarError');
    let accionPendiente = null;

    function pedirConfirmacion(fila, accion) {
        const d = fila.dataset;
        accionPendiente = { accion: accion, id: d.id };
        mostrarError(confirmarError, '');
        const cancelar = accion === 'cancelar';
        document.getElementById('tituloConfirmar').textContent = cancelar ? 'Cancelar traslado' : 'Reactivar traslado';
        document.getElementById('textoConfirmar').textContent = cancelar
            ? '¿Desea cancelar el traslado ' + d.codigo + ' de ' + d.paciente + '? Quedará registrado como «Cancelado» y podrá reactivarse más adelante.'
            : '¿Desea reactivar el traslado ' + d.codigo + ' de ' + d.paciente + '?';
        btnConfirmar.textContent = cancelar ? 'Cancelar traslado' : 'Reactivar';
        abrirModal(modalConfirmar);
        btnConfirmar.focus();
    }

    btnConfirmar.addEventListener('click', async function () {
        if (!accionPendiente) return;
        const datos = new FormData();
        datos.append('accion', accionPendiente.accion);
        datos.append('id_traslado', accionPendiente.id);
        btnConfirmar.disabled = true;
        try {
            const r = await enviar(datos);
            if (r.exito) {
                recargarConAviso(r.mensaje);
            } else {
                mostrarError(confirmarError, r.mensaje);
            }
        } catch (err) {
            mostrarError(confirmarError, 'No se pudo conectar con el servidor. Intente nuevamente.');
        } finally {
            btnConfirmar.disabled = false;
        }
    });

    // Acciones de cada fila (delegación de eventos)
    document.getElementById('cuerpoTraslados').addEventListener('click', function (e) {
        const boton = e.target.closest('button[data-accion]');
        if (!boton) return;
        const fila = boton.closest('.fila-traslado');
        if (boton.dataset.accion === 'editar') abrirEdicion(fila);
        else pedirConfirmacion(fila, boton.dataset.accion);
    });

    aplicarFiltro();
})();
