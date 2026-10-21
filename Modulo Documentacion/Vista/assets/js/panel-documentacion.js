/*
 * Panel de Documentación: filtrado en vivo (sin recargar la página)
 * por texto de búsqueda y por categoría. Las tarjetas ya vienen
 * renderizadas desde PHP con los atributos data-categoria y data-busqueda.
 */
(function () {
    'use strict';

    const campoBusqueda = document.getElementById('buscadorDocumentos');
    const contenedorFiltros = document.getElementById('filtrosCategoria');
    const tarjetas = document.querySelectorAll('#listaDocumentos .doc-card');
    const estadoVacio = document.getElementById('estadoVacio');

    let categoriaActiva = 'todas';

    /** Muestra u oculta cada tarjeta según búsqueda y categoría. */
    function aplicarFiltros() {
        const texto = campoBusqueda.value.trim().toLowerCase();
        let visibles = 0;

        tarjetas.forEach(function (tarjeta) {
            const coincideCategoria = categoriaActiva === 'todas' ||
                tarjeta.dataset.categoria === categoriaActiva;
            const coincideTexto = texto === '' ||
                tarjeta.dataset.busqueda.indexOf(texto) !== -1;

            const mostrar = coincideCategoria && coincideTexto;
            tarjeta.classList.toggle('hidden', !mostrar);
            if (mostrar) {
                visibles++;
            }
        });

        // El mensaje solo aparece si hay tarjetas pero ninguna coincide.
        if (tarjetas.length > 0) {
            estadoVacio.classList.toggle('hidden', visibles > 0);
        }
    }

    campoBusqueda.addEventListener('input', aplicarFiltros);

    contenedorFiltros.addEventListener('click', function (evento) {
        const chip = evento.target.closest('.filter-chip');
        if (!chip) {
            return;
        }
        contenedorFiltros.querySelectorAll('.filter-chip').forEach(function (otro) {
            otro.classList.remove('active');
        });
        chip.classList.add('active');
        categoriaActiva = chip.dataset.categoria;
        aplicarFiltros();
    });
})();
