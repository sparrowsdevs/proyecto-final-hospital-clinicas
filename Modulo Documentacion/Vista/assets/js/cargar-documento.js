 // Modal Logic
        const modal = document.getElementById('uploadModal');
        const modalWindow = document.querySelector('.modal-window');

        function openModal() {
            modal.classList.remove('hidden');
            // Timeout para la transición de entrada
            setTimeout(() => {
                modalWindow.classList.add('show');
            }, 10);
        }

        function closeModal() {
            modalWindow.classList.remove('show');
            // Timeout para la transición de salida antes de ocultar
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Cerrar modal haciendo click fuera de la ventana
        modal.addEventListener('click', (e) => {
            if(e.target === modal) {
                closeModal();
            }
        });

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });