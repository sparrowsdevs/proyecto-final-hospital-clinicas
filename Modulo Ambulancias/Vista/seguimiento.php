<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Seguimiento de Estado de Traslado - S.I.G.S.M.</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Enlace al archivo CSS Puro -->
    <link rel="stylesheet" href="assets/css/seguimiento.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</head>
<body>

    <!-- SIDEBAR (Desktop) -->
    <aside class="sidebar">
        <div class="brand-header">
            <img alt="Hospital de Clinicas Logo" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3gXemotEj85WDWYVVyjxbDMZ-KmEbvGUwGw1IzGiGChUujtYyodVAxz6KqKlTkbXZ15U-prR4rdwM02GnYEMSSW1jU3AHmZFpWhjYaxHTbrweY8B7PNxzt_dZ7JefxngByMxXO1xYkegGFWwZB8fNFGKoG2vI3LQKziAbdYi1pRQXLngNdpZsYUENcePp8Rp1lPw1L7mnvQpFcUR6w9EdWfdQiJioovhVzuP1GzHIFoi3pJ_tQgowd4JrFpZNzR-AaY_ynyBkuiY">
            <h2>Hospital de Clínicas</h2>
            <p>Módulo de Logística</p>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-links">
                <a class="nav-link" href="#"><span class="material-symbols-outlined">dashboard</span> Panel de Control</a>
                <a class="nav-link active" href="#"><span class="material-symbols-outlined">local_shipping</span> Traslados</a>
                <a class="nav-link" href="#"><span class="material-symbols-outlined">minor_crash</span> Gestión de Flota</a>
                <a class="nav-link" href="#"><span class="material-symbols-outlined">map</span> Planificación de Rutas</a>
                <a class="nav-link" href="#"><span class="material-symbols-outlined">assessment</span> Reportes</a>
            </div>
            <div class="nav-cta">
                <button class="btn btn-primary btn-full">
                    <span class="material-symbols-outlined">add</span> Nuevo Traslado
                </button>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <a class="nav-link" href="#"><span class="material-symbols-outlined">settings</span> Configuración</a>
            <a class="nav-link" href="#"><span class="material-symbols-outlined">contact_support</span> Soporte</a>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-wrapper">
        
        <!-- TOP NAVBAR -->
        <header class="topbar">
            <div class="search-container">
                <span class="material-symbols-outlined search-icon">search</span>
                <input class="search-input" placeholder="Buscar traslados, vehículos..." type="text">
            </div>
            
            <div class="topbar-actions">
                <button class="icon-btn">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="badge-dot"></span>
                </button>
                <div class="divider-vertical"></div>
                <button class="profile-btn">
                    <span class="material-symbols-outlined profile-icon">account_circle</span>
                    <div class="profile-info">
                        <p class="profile-name">Perfil Administrador</p>
                        <p class="profile-role">Depto. Logística</p>
                    </div>
                </button>
            </div>
        </header>

        <!-- PAGE CANVAS -->
        <main class="page-canvas">
            
            <div class="map-section">
                <div id="map" style="width: 100%; height: 100%;"></div>
            </div>

            <!-- CONTENT AREA -->
            <div class="tracking-content">
                
                <!-- LEFT PANEL: Main Transfer Info -->
                <div class="tracking-main">
                    
                    <!-- Header Card -->
                    <div class="card">
                        <div class="tracking-header">
                            <div class="header-titles">
                                <a class="back-link" href="#">
                                    <span class="material-symbols-outlined text-sm">arrow_back</span> Volver a Traslados
                                </a>
                                <h1>Seguimiento de Estado de Traslado</h1>
                                <div class="badges-row">
                                    <span class="badge badge-id">TR-2023-8842</span>
                                    <span class="badge badge-status">
                                        <span class="material-symbols-outlined text-sm">sync</span> En Tránsito
                                    </span>
                                </div>
                            </div>
                            
                            <!-- ETA Box -->
                            <div class="eta-box">
                                <h3>Llegada Estimada</h3>
                                <div class="eta-time">
                                    <span class="time-big">14:30</span> <span class="time-small">hrs</span>
                                </div>
                                <p class="eta-rem">
                                    <span class="material-symbols-outlined text-sm">schedule</span> 18 min. restantes
                                </p>
                            </div>
                        </div>

                        <!-- Grid de Detalles -->
                        <div class="details-grid">
                            <!-- Ubicaciones -->
                            <div class="detail-block">
                                <h3 class="block-title">Ubicaciones</h3>
                                <div class="location-list">
                                    <div class="location-item">
                                        <div class="icon-circle bg-gray"><span class="material-symbols-outlined text-sm">hail</span></div>
                                        <div class="location-text">
                                            <p class="label">Origen</p>
                                            <p class="title">Hospital Español</p>
                                            <p class="subtitle">Garibaldi 1729, Montevideo</p>
                                        </div>
                                    </div>
                                    <div class="location-item">
                                        <div class="icon-circle bg-blue"><span class="material-symbols-outlined text-sm">location_on</span></div>
                                        <div class="location-text">
                                            <p class="label">Destino</p>
                                            <p class="title">Hospital de Clínicas</p>
                                            <p class="subtitle">Av. Italia s/n, Montevideo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Paciente -->
                            <div class="detail-block">
                                <h3 class="block-title">
                                    <span class="material-symbols-outlined text-blue text-md">person</span> Información del Paciente
                                </h3>
                                <div class="patient-grid">
                                    <div>
                                        <p class="label">Nombre</p>
                                        <p class="title fw-normal">Carlos Rodríguez</p>
                                    </div>
                                    <div>
                                        <p class="label">C.I.</p>
                                        <p class="title fw-normal">3.456.789-0</p>
                                    </div>
                                    <div class="span-2">
                                        <p class="label">Prioridad</p>
                                        <span class="badge badge-warning">Alta (Código Amarillo)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Card -->
                    <div class="card">
                        <h3 class="block-title mb-lg">
                            <span class="material-symbols-outlined text-blue text-md">group</span> Personal a bordo
                        </h3>
                        <div class="staff-grid">
                            <div class="staff-card">
                                <div class="icon-large bg-dark-blue"><span class="material-symbols-outlined">drive_eta</span></div>
                                <div class="staff-info">
                                    <p class="label">Chofer</p>
                                    <p class="title">Roberto Gómez</p>
                                    <button class="btn btn-dark-blue btn-sm">
                                        <span class="material-symbols-outlined text-sm">call</span> Llamar Chofer
                                    </button>
                                </div>
                            </div>
                            <div class="staff-card">
                                <div class="icon-large bg-secondary-blue"><span class="material-symbols-outlined">medical_services</span></div>
                                <div class="staff-info">
                                    <p class="label">Enfermera</p>
                                    <p class="title">Lucía Méndez</p>
                                    <button class="btn btn-secondary-blue btn-sm">
                                        <span class="material-symbols-outlined text-sm">chat</span> Mensaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: Sidebar Timeline -->
                <div class="tracking-sidebar">
                    <div class="card">
                        <h3 class="block-title mb-lg">Registro de Actividad</h3>
                        
                        <div class="timeline">
                            <!-- Item 1 -->
                            <div class="timeline-item">
                                <div class="timeline-line"></div>
                                <div class="timeline-dot dot-success">
                                    <span class="material-symbols-outlined text-sm fw-bold">check</span>
                                </div>
                                <div class="timeline-content">
                                    <span class="time text-muted">13:45</span>
                                    <span class="title">Solicitud Recibida</span>
                                    <span class="desc">Procesada por sistema central</span>
                                </div>
                            </div>

                            <!-- Item 2 -->
                            <div class="timeline-item">
                                <div class="timeline-line"></div>
                                <div class="timeline-dot dot-success">
                                    <span class="material-symbols-outlined text-sm fw-bold">check</span>
                                </div>
                                <div class="timeline-content">
                                    <span class="time text-muted">13:55</span>
                                    <span class="title">Unidad Despachada</span>
                                    <span class="desc">Unidad 42 asignada al servicio</span>
                                </div>
                            </div>

                            <!-- Item 3 (Current) -->
                            <div class="timeline-item">
                                <div class="timeline-line"></div>
                                <div class="timeline-dot dot-current">
                                    <div class="inner-dot animate-pulse"></div>
                                </div>
                                <div class="timeline-content">
                                    <span class="time text-blue fw-bold">14:12 (Actual)</span>
                                    <span class="title">En Tránsito</span>
                                    <span class="desc">Circulando por zona céntrica</span>
                                </div>
                            </div>

                            <!-- Item 4 (Pending) -->
                            <div class="timeline-item opacity-dim">
                                <div class="timeline-dot dot-pending"></div>
                                <div class="timeline-content">
                                    <span class="time text-muted">Pendiente</span>
                                    <span class="title fw-normal">Llegada a Destino</span>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-footer">
                            <button class="btn btn-gray btn-full">
                                <span class="material-symbols-outlined text-md">history</span> Ver Historial Completo
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="app-footer">
            <p>© 2023 Hospital de Clínicas - Sistema Integrado de Gestión de Traslados</p>
        </footer>
    </div>

    <script>
        // 1. Inicializar el mapa y establecer la vista (Coordenadas de Montevideo, cerca del Clínicas)
        const map = L.map('map', {
            zoomControl: false // Desactivamos el control por defecto para ponerlo abajo a la derecha
        }).setView([-34.8841, -56.1408], 14);

        // Mover los controles de zoom abajo a la derecha
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // 2. Cargar los tiles (las imágenes del mapa) desde OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 3. Crear el marcador HTML personalizado (para mantener tu diseño original)
        const customIcon = L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [36, 36],
            iconAnchor: [18, 18], // Punto del icono que corresponde a la coordenada
            html: `
                <div class="map-marker-wrapper">
                    <div class="marker-ping"></div>
                    <div class="marker-icon">
                        <span class="material-symbols-outlined" style="font-size: 20px;">ambulance</span>
                    </div>
                    <div class="marker-tooltip">
                        <p class="tooltip-title" style="font-size: 12px; font-weight: 700; color: #1b1c1b;">Unidad 42</p>
                        <p class="tooltip-subtitle" style="font-size: 10px; color: #43474e;">En camino • 45 km/h</p>
                    </div>
                </div>
            `
        });

        // 4. Añadir el marcador al mapa
        L.marker([-34.8841, -56.1408], {icon: customIcon}).addTo(map);
    </script>
</body>
</html>