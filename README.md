# S.I.G.S.M. — Sistema Informático de Gestión de Servicios Médicos

Proyecto de Egreso — BT en Tecnologías de la Información (2026)
I.S.B.O.

**Empresa desarrolladora:** Sparrows Devs
**Cliente:** Hospital de Clínicas — Montevideo, Uruguay

## De qué se trata

Proyecto final para el Hospital de Clínicas. La idea es digitalizar algunos de los procesos internos del hospital que hoy se manejan de forma manual: gestión de documentación clínica (con acceso vía QR para pacientes), traslados en ambulancia y encuestas de satisfacción.

Todo arranca desde un login por cédula, y de ahí cada usuario accede a los módulos según su rol.

> ⚠️ Fuera de alcance: el sistema **no** maneja historias clínicas ni datos médicos sensibles, y **no** es un sistema de emergencias (no atiende Código Rojo ni SAME 105).

## Cómo está armado

Separamos el proyecto en módulos, y cada uno sigue la lógica de Modelo-Vista-Controlador. Aparte hay una carpeta de "Servicios Comunes" con todo lo que se comparte entre módulos (conexión a la base, autenticación, navegación), para no repetir código.

```
proyecto-final-hospital-clinicas/
├── index.php                          # Login del sistema (punto de entrada único)
├── assets/js/login.js
├── styles/inicio-sesion.css
│
├── Servicios Comunes/
│   ├── Conexion BD/conexion.php       # Singleton de conexión PDO
│   ├── Autenticacion/
│   │   ├── AuthController.php         # Sesión, protegerRuta(), verificación de roles
│   │   ├── UsuarioModelo.php          # CRUD de usuarios, autenticación, roles
│   │   ├── procesar-login.php
│   │   ├── procesar-usuario.php       # Endpoint AJAX: crear/editar/suspender/reactivar
│   │   └── logout.php
│   └── Vista General/
│       ├── navbar.php                 # Sidebar + topbar único, compartido por todos los paneles
│       └── assets/css/navbar.css
│
├── Modulo Documentacion/
│   └── Vista/
│       ├── panel-administrador.php    # Panel Principal, exclusivo Administrador
│       ├── panel-documentacion.php    # Consulta de documentos, todos los roles
│       ├── cargar-documento.php       # CRUD de documentos (exclusivo Administrador) — vista lista, backend pendiente
│       ├── gestion-usuarios.php       # Gestión de usuarios (exclusivo Administrador) — funcional
│       └── assets/{css,js}/
│
├── Modulo Ambulancias/                # Esqueleto de carpetas, sin desarrollo todavía
└── Modulo Encuestas/                  # Esqueleto de carpetas, sin desarrollo todavía
```

## Módulos

- **Documentación** *(en desarrollo activo)* — cargar y categorizar documentos informativos para pacientes (no historias clínicas), con un panel de consulta y otro de administración. Los pacientes acceden escaneando un QR desde el celular.

- **Ambulancias** *(no iniciado)* — pedir un traslado no urgente, asignarle una unidad y hacerle seguimiento hasta destino.
- **Encuestas** *(no iniciado)* — armar encuestas de satisfacción y ver/exportar resultados.

## Roles

Hoy el sistema maneja dos niveles de acceso reales:

- **Administrador**: acceso total — Panel Principal, Carga de Archivos, Gestión de Usuarios, y todo lo que ve un Usuario Básico.
- **Usuario Básico**: solo el Panel de Documentación (consulta + QR). Agrupa conceptualmente a Médico, Enfermería, Ayudante de Médico, etc.

La tabla `rol` sigue siendo un catálogo abierto (VARCHAR, no ENUM) con relación **N:M** contra `usuario` (tabla intermedia `usuario_rol`), pensado para poder sumar roles a futuro sin migrar el esquema. El rol `Chofer` ya existe en la tabla pero está **excluido a propósito** de los formularios de usuario hasta que se desarrolle el Módulo de Ambulancias.

## Backend / autenticación

El login funciona con cédula y contraseña, vía AJAX (fetch) sin recargar la página, con validación de formato en tiempo real. Las contraseñas se guardan hasheadas (`password_hash`) y nunca se guarda la contraseña en la sesión, solo los datos del usuario y su rol.

La conexión a la base es un Singleton en PHP con PDO (`Servicios Comunes/Conexion BD/conexion.php`), así todos los módulos usan la misma instancia en vez de abrir conexiones nuevas por todos lados.

Las rutas protegidas (`AuthController::protegerRuta()`) exigen sesión activa y, si corresponde, un rol específico, con cabeceras anti-caché para que el botón "Atrás" del navegador no muestre paneles después de cerrar sesión.

## Qué está funcionando hoy

- ✅ Login, logout y protección de rutas por rol.
- ✅ Navegación única (sidebar + topbar) compartida por todos los paneles, con nombre y rol reales del usuario logueado.
- ✅ **Gestión de Usuarios completa**: listar, crear (con selector de rol), editar (datos personales, contraseña, estado y rol), suspender/reactivar. Con protecciones para que un Administrador no se autosuspenda ni se quite su propio rol.
- 🚧 Módulo de Documentación: vistas listas, pero el CRUD de documentos todavía no está conectado a la base real (datos de ejemplo en `cargar-documento.php`).
- 🚧 Pendiente: QR único que lleve a un repositorio público de documentos (sin login), con botón para generarlo/imprimirlo desde el Panel de Documentación.
- ⏸️ Pausado: funcionalidad real de "¿Olvidó su contraseña?" y "Solicitar Acceso" en el login (hoy son solo UI). Falta definir si la solicitud se guarda en la base, se manda por email, o ambas.
- ❌ Módulos de Ambulancias y Encuestas: sin desarrollo, solo esqueleto de carpetas.

## Base de datos

En `database/` está el modelo: usuarios, roles (relación N:M por si un usuario llega a tener más de un rol), categorías y documentos. Motor InnoDB, charset utf8mb4, base de datos `database_clinicas`. Todas las tablas usan borrado lógico (`activo BOOLEAN`), nunca `DELETE` físico sobre usuarios ni documentos.

## Tecnologías

- HTML5 + CSS3 puro para las vistas (sin Bootstrap ni Tailwind, todo maquetado a mano con Grid y Flexbox)
- PHP orientado a objetos para el backend, sin frameworks
- MySQL para la base de datos, vía PDO con prepared statements
- JavaScript vanilla (fetch/AJAX) para el dinamismo del frontend
- XAMPP como entorno de desarrollo local

## Cómo levantarlo en local

1. Copiar la carpeta del proyecto dentro de `htdocs/` (XAMPP).
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Crear la base `database_clinicas` y correr el script de `database/` para crear las tablas.
4. Cargar al menos un usuario Administrador de prueba (contraseña hasheada con `password_hash`, con su fila en `usuario_rol`).
5. Entrar a `http://localhost/<carpeta-del-proyecto>/index.php`.


## Equipo — Sparrows Devs

 - Ricardo Boggio 
- Facundo Brun
- Fernando Aguirre
- Miguel Duarte

## Próximos pasos

1. Definir mecanismo de contacto (BD / email) y activar los botones de recuperación de acceso del login.