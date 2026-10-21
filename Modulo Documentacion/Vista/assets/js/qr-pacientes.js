/*
 * Genera el código QR en el navegador (librería qrcode.js, incluida en el
 * proyecto, sin depender de internet) a partir de la URL pública que
 * entrega el servidor en el atributo data-url del modal.
 */
(function () {
    'use strict';

    const modal = document.getElementById('modalQr');
    const botonAbrir = document.getElementById('btnMostrarQr');
    if (!modal || !botonAbrir || typeof qrcode !== 'function') {
        return;
    }

    const lienzo = document.getElementById('lienzoQr');
    let qrGenerado = false;

    /** Dibuja el QR (SVG escalable) la primera vez que se abre el modal. */
    function generarQr() {
        if (qrGenerado) {
            return;
        }
        const qr = qrcode(0, 'M'); // tipo automático, corrección de errores media
        qr.addData(modal.dataset.url);
        qr.make();
        lienzo.innerHTML = qr.createSvgTag({ cellSize: 6, margin: 2, scalable: true });
        qrGenerado = true;
    }

    function abrir() {
        generarQr();
        modal.classList.remove('hidden');
        document.getElementById('btnImprimirQr').focus();
    }

    function cerrar() {
        modal.classList.add('hidden');
        botonAbrir.focus();
    }

    botonAbrir.addEventListener('click', abrir);
    document.getElementById('btnCerrarQr').addEventListener('click', cerrar);
    document.getElementById('btnFinalizarQr').addEventListener('click', cerrar);
    document.getElementById('btnImprimirQr').addEventListener('click', function () {
        window.print();
    });

    // Cerrar con Escape o haciendo clic fuera de la tarjeta
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !modal.classList.contains('hidden')) {
            cerrar();
        }
    });
    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) {
            cerrar();
        }
    });
})();
