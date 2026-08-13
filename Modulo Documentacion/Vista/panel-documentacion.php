<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Panel Médico: Documentación Clínica</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel-documentacion.css">
</head>
<body>

    <header class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-left">
                <img alt="Hospital de Clínicas Logo" class="brand-logo" src="https://www.hc.edu.uy/images/imagenesarticulos/Logo_Hc.png">
                
                <nav class="desktop-nav">
                    <a class="nav-link active" href="#">Dashboard</a>
                    <a class="nav-link" href="#">Pacientes</a>
                    <a class="nav-link" href="#">Agenda</a>
                    <a class="nav-link" href="#">Reportes</a>
                </nav>
            </div>
            
            <div class="topbar-right">
                <div class="user-profile">
                    <span class="material-symbols-outlined text-primary">account_circle</span>
                    <span class="user-name">Dr. Garcia</span>
                </div>
                <button class="btn-logout">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="logout-text">Cerrar Sesión</span>
                </button>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            
            <section class="hero-section">
                <h1 class="page-title">Panel Médico: Documentación Clínica</h1>
                
                <div class="search-wrapper">
                    <div class="search-input-group">
                        <input class="search-input" placeholder="Buscar documento explicativo o preparación médica..." type="text">
                        <button class="btn-search">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </div>
                
                <div class="quick-filters">
                    <button class="filter-chip active">Radiología</button>
                    <button class="filter-chip">Cirugía</button>
                    <button class="filter-chip">Laboratorio</button>
                    <button class="filter-chip">Cardiología</button>
                </div>
            </section>

            <section class="results-grid">
                
                <div class="doc-card border-blue">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-blue">Radiología</span>
                            <span class="material-symbols-outlined text-muted">description</span>
                        </div>
                        <h3 class="doc-title">Preparación para Ecografía Abdominal</h3>
                        <p class="doc-desc">Instrucciones detalladas sobre ayuno y consumo de líquidos previos al estudio de abdomen superior.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Preparación para Ecografía Abdominal', 'Radiología')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-success">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-success">Laboratorio</span>
                            <span class="material-symbols-outlined text-muted">biotech</span>
                        </div>
                        <h3 class="doc-title">Ayuno para Análisis de Sangre</h3>
                        <p class="doc-desc">Protocolo de ayuno de 8 a 12 horas para perfiles lipídicos y glucemia basal. Recomendaciones generales.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Ayuno para Análisis de Sangre', 'Laboratorio')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-tertiary">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-tertiary">Cardiología</span>
                            <span class="material-symbols-outlined text-muted">monitor_heart</span>
                        </div>
                        <h3 class="doc-title">Preparación para Prueba de Esfuerzo</h3>
                        <p class="doc-desc">Requisitos de vestimenta y medicación previa para el test de ergometría graduada.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Preparación para Prueba de Esfuerzo', 'Cardiología')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

                <div class="doc-card border-error">
                    <div class="doc-card-body">
                        <div class="doc-header">
                            <span class="badge badge-error">Cirugía</span>
                            <span class="material-symbols-outlined text-muted">medication</span>
                        </div>
                        <h3 class="doc-title">Indicaciones Pre-operatorias Generales</h3>
                        <p class="doc-desc">Guía de higiene y suspensión de anticoagulantes para cirugías programadas de baja complejidad.</p>
                    </div>
                    <button class="btn-outline-primary w-full" onclick="openModal('Indicaciones Pre-operatorias Generales', 'Cirugía')">
                        <span class="material-symbols-outlined icon-sm">qr_code_2</span>
                        Mostrar código QR
                    </button>
                </div>

            </section>
        </div>
    </main>

    <div class="modal-overlay hidden" id="qrModal">
        <div class="modal-container" id="modalContainer">
            <div class="modal-header">
                <h2 class="modal-title">Código de Acceso</h2>
                <button class="btn-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                
                <div class="qr-representation">
                    <div class="qr-pattern">
                        <span class="material-symbols-outlined qr-icon">qr_code</span>
                    </div>
                    <div class="qr-corner top-left"></div>
                    <div class="qr-corner top-right"></div>
                    <div class="qr-corner bottom-left"></div>
                    <div class="qr-corner bottom-right"></div>
                </div>

                <p class="modal-doc-name" id="modalDocName">Nombre del Documento</p>
                <p class="modal-doc-category" id="modalDocCategory">Categoría</p>
                
                <div class="modal-actions">
                    <button class="btn-primary flex-1">Imprimir Etiqueta</button>
                    <button class="btn-secondary flex-1" onclick="closeModal()">Finalizar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Lógica del Modal
        function openModal(name, category) {
            const modal = document.getElementById('qrModal');
            const container = document.getElementById('modalContainer');
            
            document.getElementById('modalDocName').innerText = name;
            document.getElementById('modalDocCategory').innerText = category;
            
            modal.classList.remove('hidden');
            
            // Timeout para permitir la animación CSS
            setTimeout(() => {
                container.classList.add('show');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('qrModal');
            const container = document.getElementById('modalContainer');
            
            container.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); // Espera a que termine la animación
        }

        // Cierra el modal con la tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>