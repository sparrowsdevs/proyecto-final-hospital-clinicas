document.addEventListener('DOMContentLoaded', () => {
    const boton = document.querySelector('.boton-menu');
    const menu = document.getElementById('menu-lateral');
    const fondo = document.querySelector('.fondo-menu');

    if (!boton || !menu || !fondo) {
        return;
    }

    const abrir = () => {
        menu.classList.add('abierta');
        fondo.hidden = false;
        document.body.classList.add('menu-abierto');
        boton.setAttribute('aria-expanded', 'true');
        boton.setAttribute('aria-label', 'Cerrar menú');
    };

    const cerrar = () => {
        menu.classList.remove('abierta');
        fondo.hidden = true;
        document.body.classList.remove('menu-abierto');
        boton.setAttribute('aria-expanded', 'false');
        boton.setAttribute('aria-label', 'Abrir menú');
    };

    boton.addEventListener('click', () => {
        menu.classList.contains('abierta') ? cerrar() : abrir();
    });

    fondo.addEventListener('click', cerrar);

    menu.querySelectorAll('a').forEach((enlace) => {
        enlace.addEventListener('click', cerrar);
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && menu.classList.contains('abierta')) {
            cerrar();
        }
    });

    window.matchMedia('(min-width: 768px)').addEventListener('change', (evento) => {
        if (evento.matches) {
            cerrar();
        }
    });
});
