# S.I.G.S.M. — Sistema Informático de Gestión de Servicios Médicos

Proyecto de Egreso — BT en Tecnologías de la Información (2026)
I.S.B.O.

**Empresa desarrolladora:** Sparrows Devs
**Cliente:** Hospital de Clínicas — Montevideo, Uruguay

## De qué se trata

Proyecto final para el Hospital de Clínicas. La idea es digitalizar algunos de los procesos internos del hospital que hoy se manejan de forma manual: documentación informativa para pacientes (con acceso vía QR), traslados no urgentes en ambulancia y encuestas de satisfacción sobre los documentos.

Todo arranca desde un login por cédula, y de ahí cada usuario accede a los módulos según su rol. Los pacientes, en cambio, **no** inician sesión: llegan escaneando un QR.

> ⚠️ Fuera de alcance: el sistema **no** maneja historias clínicas ni datos médicos sensibles, y **no** es un sistema de emergencias (no atiende Código Rojo ni SAME 105). Los traslados son exclusivamente **no urgentes** (altas, insumos, órganos, etc.).

## Cómo está armado

Separamos el proyecto en módulos, y cada uno sigue la lógica de Modelo-Vista-Controlador. Aparte hay una carpeta de "Servicios Comunes" con todo lo que se comparte entre módulos (conexión a la base, autenticación, configuración, seguridad, navegación), para no repetir código.

```
proyecto-final-hospital-clinicas/
├── index.php                          # Login del sistema (punto de entrada único)
├── assets/js/login.js
├── styles/inicio-sesion.css
│
├── Servicios Comunes/
│   ├── Conexion BD/conexion.php       # Singleton de conexión PDO
│   ├── Configuracion/configuracion.php# Token del QR y URL pública (variables de entorno)
│   ├── Seguridad/AccesoPublico.php    # Validación del token que llega por QR
│   ├── Autenticacion/
│   │   ├── AuthController.php         # Sesión, protegerRuta(), verificación de roles
│   │   ├── usuarioModelo.php          # CRUD de usuarios, autenticación, roles
│   │   ├── procesar-login.php
│   │   ├── procesar-usuario.php       # Endpoint AJAX: crear/editar/suspender/reactivar
│   │   └── logout.php
│   └── Vista General/
│       ├── navbar.php                 # Sidebar + topbar único, compartido por todos los paneles
│       └── assets/css/navbar.css
│
├── Modulo Documentacion/
│   ├── Modelo/DocumentoModelo.php
│   ├── Controlador/
│   │   ├── procesar-documento.php     # Alta/edición/suspensión de documentos (AJAX)
│   │   └── servir-documento.php       # Única vía para ver/descargar los PDF
│   ├── Vista/
│   │   ├── panel-documentacion.php    # Consulta de documentos + código QR, todos los roles
│   │   ├── cargar-documento.php       # CRUD de documentos (exclusivo Administrador)
│   │   ├── gestion-usuarios.php       # Gestión de usuarios (exclusivo Administrador)
│   │   ├── repositorio-publico.php    # Repositorio al que llega el paciente por QR (sin login)
│   │   └── assets/{css,js}/
│   └── uploads/documentos/            # PDF cargados (acceso directo bloqueado por .htaccess)
│
├── Modulo Ambulancias/
│   ├── Modelo/TrasladoModelo.php
│   ├── Controlador/procesar-traslado.php
│   └── Vista/
│       ├── form-traslado.php          # Registro de traslados
│       ├── panel-de-gestion.php       # Listado, edición, cambio de estado y cancelación
│       ├── seguimiento.php            # Maqueta del seguimiento (sin GPS por ahora)
│       └── assets/{css,js}/
│
├── Modulo Encuestas/
│   ├── Modelo/EncuestaModelo.php
│   ├── Controlador/procesar-encuesta.php
│   └── Vista/
│       ├── encuesta.php               # Encuesta pública del paciente (sin login)
│       ├── resultados-encuestas.php   # Dashboard de resultados (usuarios con login)
│       └── assets/{css,js}/
│
└── database/                          # Scripts SQL (ver sección Base de datos)
```

## Módulos

### Documentación

Cargar y categorizar documentos informativos para pacientes (instructivos de ayuno, preparación para estudios, cuidados post-operatorios), siempre PDF y nunca historias clínicas.

- **Carga de Archivos** (Administrador): alta, edición y suspensión/reactivación de documentos, con validación del archivo subido.
- **Panel de Documentación** (todos los roles): búsqueda, filtro por categoría, ver y descargar cada PDF, y una ventana con el **código QR** para mostrarle al paciente.
- **Repositorio público** (paciente, sin login): se llega escaneando el QR. Permite buscar, ver y descargar los documentos activos, y dejar la opinión sobre cada uno (módulo Encuestas).

### Ambulancias

Gestión de **traslados no urgentes**. Los choferes y el personal acompañante se eligen entre los usuarios activos del sistema.

- **Formulario de Traslado**: se ingresa la cédula del paciente (si ya existe se autocompletan nombre y apellido), el elemento a trasladar (camilla, silla de ruedas o ninguno), origen y destino (con posibilidad de agregar ubicaciones nuevas), fecha prevista, vehículo, chofer y acompañante. Solo se ofrecen los vehículos compatibles con el elemento. El registro se guarda en una única transacción.
- **Panel de Gestión**: tarjetas con el total de traslados, vehículos disponibles y choferes activos; tabla con paciente, origen, destino, fecha, vehículo, chofer, acompañante («S/A» si no hay) y estado; búsqueda, filtro por estado, paginación y exportación a CSV. Desde la tabla se puede **editar** el traslado, **modificar solo el estado** (ventana chica), **cancelar** y **reactivar**.
- **Estados**: Solicitado → Vehículo Asignado → En Tránsito → Completado. Cancelar es un borrado lógico (estado «Cancelado», reversible). Cada cambio de estado queda en `historial_estado` con el usuario que lo hizo. Un traslado «En Tránsito» exige vehículo y chofer, y ni el vehículo ni el chofer pueden estar en otro traslado en tránsito a la vez. Los traslados Completados y Cancelados no se editan.
- **Seguimiento**: por ahora es una maqueta (mapa con Leaflet/OpenStreetMap, que necesita conexión a internet). El rastreo GPS real queda para una etapa posterior.
- Por privacidad, del paciente solo se guarda cédula, nombre y apellido: no hay campos de diagnóstico ni notas clínicas.

### Encuestas

- **Encuesta pública** (paciente, sin login): se accede desde el botón «Dejar mi opinión» de cada documento del repositorio. Tres preguntas: facilidad de acceso (1 a 5 estrellas), utilidad de la información y comentario opcional (máx. 500 caracteres). Es **anónima**: solo se guarda el documento, las respuestas y la fecha; no se guarda nombre, cédula ni IP.
- **Resultados de Encuestas** (usuarios con login): dashboard con respuestas recibidas, promedio de facilidad, porcentaje que considera útil la información, gráficos de distribución, tabla por documento, comentarios recientes y filtro por documento.

## Roles

El sistema maneja dos niveles de acceso:

- **Administrador**: todo lo que ve un Usuario Básico, más Carga de Archivos y Gestión de Usuarios.
- **Usuario Básico**: Panel de Documentación (consulta + QR), las tres pantallas de Ambulancias (Formulario, Panel de Gestión y Seguimiento) y Resultados de Encuestas. Agrupa conceptualmente a Médico, Enfermería, Ayudante de Médico, etc.

La tabla `rol` es un catálogo abierto (VARCHAR, no ENUM) con relación **N:M** contra `usuario` (tabla intermedia `usuario_rol`), pensado para poder sumar roles a futuro sin migrar el esquema. El rol `Chofer` existe en la tabla pero está **excluido a propósito** de los formularios de usuario.

## Backend, autenticación y seguridad

- **Login** con cédula y contraseña, vía AJAX (fetch) sin recargar la página, con validación de formato en tiempo real. Las contraseñas se guardan hasheadas (`password_hash`) y nunca se guarda la contraseña en la sesión.
- **Conexión** a la base con un Singleton en PHP con PDO, para que todos los módulos usen la misma instancia. Todas las consultas son **prepared statements** (sin concatenar datos del usuario en el SQL) y los datos se escapan al mostrarlos.
- **Rutas protegidas** (`AuthController::protegerRuta()`): exigen sesión activa y, si corresponde, un rol específico, con cabeceras anti-caché para que el botón «Atrás» no muestre paneles tras cerrar sesión.
- **Borrado lógico** en usuarios, documentos y traslados; nunca se hace `DELETE` físico sobre esos datos.
- **Acceso del paciente por QR**: el QR contiene una URL con un token secreto (`Servicios Comunes/Configuracion/configuracion.php`, comparado con `hash_equals`). Ese token solo da acceso al repositorio, a la entrega de PDF y a la encuesta; nada más del sistema lo acepta.
- **PDF protegidos**: la carpeta `uploads/documentos/` está bloqueada por `.htaccess`; los archivos solo se entregan a través de `servir-documento.php` (con sesión o token válido) y solo si el documento está activo.
- **Encuesta pública**: token anti-CSRF, una respuesta por documento y por navegador, tope de envíos por sesión, campo trampa contra envíos automáticos y validación estricta de cada respuesta.
- **Cambios de estado de traslados** dentro de transacciones (rollback ante cualquier error).

## Qué está funcionando hoy

- ✅ Login, logout y protección de rutas por rol.
- ✅ Navegación única (sidebar + topbar) compartida por todos los paneles, con nombre y rol reales del usuario.
- ✅ Gestión de Usuarios completa (listar, crear, editar, suspender/reactivar), con protecciones para que un Administrador no se autosuspenda ni se quite su propio rol.
- ✅ Módulo Documentación: CRUD de documentos conectado a la base, panel de consulta, QR y repositorio público.
- ✅ Módulo Ambulancias: formulario, panel de gestión (editar, cambiar estado, cancelar, reactivar, filtros y CSV).
- ✅ Módulo Encuestas: encuesta pública anónima y dashboard de resultados.
- 🎭 Seguimiento de traslados: maqueta, sin GPS.
- ⏸️ «¿Olvidó su contraseña?» y «Solicitar acceso» en el login: hoy son solo UI. Falta definir si la solicitud se guarda en la base, se manda por email, o ambas.

## Base de datos

Base `database_clinicas`, motor InnoDB, charset utf8mb4. Scripts en `database/`:

| Script                                             | Para qué sirve                                                                                                        |
| -------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| `DER-final/ddl.sql`                                | Esquema completo (usuarios, roles, documentos, traslados, etc.)                                                       |
| `DER-final/dml.sql`                                | Datos de ejemplo                                                                                                      |
| `modulo1-login-documentacion/modulo1.sql`          | Esquema solo de usuarios, roles, categorías y documentos                                                              |
| `modulo1-login-documentacion/seed-data.sql`        | Usuario Administrador de prueba y roles del sistema                                                                   |
| `modulo-ambulancias/01-preparar-traslados.sql`     | Tablas y catálogos de traslados (estados, canal, elementos, vehículos, ubicaciones). Se puede ejecutar más de una vez |
| `modulo-ambulancias/02-compatibilidad-ninguno.sql` | Deja al elemento «Ninguno» compatible con todos los tipos de vehículo (opcional: el código ya lo aplica)              |
| `modulo-encuestas/01-encuesta-documento.sql`       | Tabla `encuesta_documento` de la encuesta pública                                                                     |

Requiere MySQL 8.0.16+ o MariaDB 10.2+ (por los `CHECK` de la encuesta). Las tablas `encuesta`, `pregunta` y `respuesta_*` del DER original no se usan en la versión actual: la encuesta pública guarda sus respuestas en `encuesta_documento`.

## Tecnologías

- HTML5 + CSS3 puro para las vistas (todo maquetado a mano con Grid y Flexbox)
- PHP orientado a objetos para el backend, sin frameworks
- MySQL para la base de datos, vía PDO con prepared statements
- JavaScript vanilla (fetch/AJAX) para el dinamismo del frontend
- Código QR generado en el navegador con `qrcode.js`; mapa de Seguimiento con Leaflet/OpenStreetMap
- XAMPP como entorno de desarrollo local

## Equipo — Sparrows Devs

- Ricardo Boggio
- Facundo Brun
- Fernando Aguirre
- Miguel Duarte
