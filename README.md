# S.I.G.S.M. — Sistema Informático de Gestión de Servicios Médicos

Proyecto de egreso del Bachillerato Tecnológico en Tecnologías de la Información (I.S.B.O.), generación 2026.

**Desarrollado por:** Sparrows Devs
**Cliente:** Hospital de Clínicas, Montevideo, Uruguay

## 1. De qué trata el proyecto

El Hospital de Clínicas maneja hoy de forma manual varios procesos que se prestan a ser digitalizados. Nosotros tomamos tres:

- los **instructivos para pacientes** (ayuno previo a un estudio, cuidados después de una cirugía, etc.), que se entregan en papel;
- los **traslados no urgentes** de pacientes, insumos y órganos, que se coordinan sin un registro central;
- la **opinión de los pacientes** sobre esos instructivos, que hoy no se recoge de ninguna forma.

El sistema responde a eso con tres módulos: Documentación, Ambulancias y Encuestas. El personal del hospital ingresa con su cédula y contraseña. El paciente, en cambio, no tiene usuario: escanea un código QR impreso, llega a un repositorio con los documentos vigentes, puede descargarlos y, si quiere, dejar su opinión de forma anónima.

El proyecto está escrito en PHP puro, sin frameworks, con arquitectura MVC y MySQL como base de datos. Esa fue una decisión de cátedra y de equipo: queríamos entender qué hace un framework por debajo antes de usar uno.

## 2. Lo que el sistema no hace

Estos límites se definieron con el cliente y los respetamos en todo el código.

- **No es un gestor de historias clínicas.** No hay campos de diagnóstico, antecedentes ni notas médicas. De un paciente solo se guarda cédula, nombre y apellido, y únicamente cuando se registra un traslado.
- **No es un sistema de emergencias.** No atiende Código Rojo ni reemplaza al SAME 105. Los traslados son exclusivamente no urgentes: altas, insumos, órganos y similares.
- **No reemplaza ningún sistema clínico del hospital.** Los documentos que se cargan son material informativo en PDF.

## 3. Estructura del repositorio

Cada módulo tiene sus propias carpetas `Modelo`, `Vista` y `Controlador`. Lo que comparten todos (conexión a la base, sesiones, configuración, menú de navegación) está en `Servicios Comunes`, para no copiar código de un módulo a otro.

```
proyecto-final-hospital-clinicas/
├── index.php                       Login y punto de entrada del sistema
├── assets/
│   ├── css/inicio-sesion.css
│   └── js/login.js
│
├── Servicios Comunes/
│   ├── Conexion BD/conexion.php            Conexión PDO (patrón Singleton)
│   ├── Configuracion/configuracion.php     URL pública y token del QR
│   ├── Seguridad/AccesoPublico.php         Validación del token que llega por QR
│   ├── Autenticacion/
│   │   ├── AuthController.php              Sesión, protección de rutas y roles
│   │   ├── usuarioModelo.php               Consultas de usuarios y roles
│   │   ├── procesar-login.php
│   │   ├── procesar-usuario.php            Alta, edición y suspensión (AJAX)
│   │   └── logout.php
│   └── Vista General/                      navbar.php, compartido por todos los paneles
│
├── Modulo Documentacion/
│   ├── Modelo/DocumentoModelo.php
│   ├── Controlador/
│   │   ├── procesar-documento.php          Alta, edición y suspensión de documentos
│   │   └── servir-documento.php            Única vía para ver o descargar un PDF
│   ├── Vista/
│   │   ├── panel-documentacion.php         Consulta de documentos y QR
│   │   ├── cargar-documento.php            Gestión de documentos (Administrador)
│   │   ├── gestion-usuarios.php            Gestión de usuarios (Administrador)
│   │   └── repositorio-publico.php         Lo que ve el paciente al escanear el QR
│   └── uploads/documentos/                 PDF subidos (acceso directo bloqueado)
│
├── Modulo Ambulancias/
│   ├── Modelo/TrasladoModelo.php
│   ├── Controlador/procesar-traslado.php
│   └── Vista/
│       ├── form-traslado.php               Registro de un traslado
│       ├── panel-de-gestion.php            Listado, edición, estados y cancelación
│       └── seguimiento.php                 Maqueta del seguimiento (sin GPS)
│
├── Modulo Encuestas/
│   ├── Modelo/EncuestaModelo.php
│   ├── Controlador/procesar-encuesta.php
│   └── Vista/
│       ├── encuesta.php                    Encuesta anónima del paciente
│       └── resultados-encuestas.php        Resultados para el personal
│
├── database/
│   ├── query/sigsm-docker.sql              Script completo: esquema y datos iniciales
│   └── DER-final/
│       ├── DER Clinicas.png                Diagrama entidad-relación
│       └── DER-estado-y-correcciones.md    Diferencias entre el diagrama y la base real
│
├── docker/                                 Ajustes de Apache y PHP para el contenedor
├── Dockerfile
├── docker-compose.yml
├── .env.example                            Plantilla de variables (el .env real no se sube)
└── .gitignore / .dockerignore
```

Cada carpeta principal (`database`, `docker`, `Servicios Comunes`, `Modulo Documentacion`, `Modulo Ambulancias` y `Modulo Encuestas`) tiene su propio `README.md` con el detalle de sus archivos y de cómo funciona.

## 4. Módulos

### 4.1 Documentación

Permite cargar documentos informativos en PDF, clasificarlos por categoría y ponerlos a disposición del paciente mediante un QR. Las categorías corresponden a los servicios del hospital (Cardiología, Neurología, Oncología, etc.).

- **Carga de archivos** (solo Administrador): alta, edición, suspensión y reactivación de documentos. Se valida el tipo y el tamaño del archivo (hasta 10 MB) antes de guardarlo.
- **Panel de Documentación** (todos los usuarios con sesión): búsqueda, filtro por categoría, vista y descarga de cada PDF, y una ventana que muestra el QR listo para mostrar o imprimir.
- **Repositorio público** (paciente, sin sesión): al que se llega escaneando el QR. Muestra solo los documentos activos, con buscador y filtro por categoría, y un botón para dejar la opinión de cada uno.
- **Gestión de usuarios** (solo Administrador): alta, edición, suspensión y reactivación de las cuentas del personal.

### 4.2 Ambulancias

Gestiona los traslados no urgentes. Los choferes y los acompañantes se eligen entre los usuarios activos del sistema.

- **Formulario de traslado.** Se ingresa la cédula del paciente; si ya existe en la base, nombre y apellido se completan solos. Después se elige el elemento a trasladar (camilla, silla de ruedas o ninguno), origen, destino, fecha prevista, vehículo, chofer y acompañante. Solo se ofrecen los vehículos compatibles con el elemento elegido, y si falta una ubicación se puede agregar desde el mismo formulario. Todo se guarda en una única transacción: o se registra completo o no se registra nada.
- **Panel de gestión.** Tarjetas con el total de traslados, vehículos disponibles y choferes activos, y una tabla con paciente, origen, destino, fecha, vehículo, chofer, acompañante (se muestra «S/A», sin acompañante, cuando no hay) y estado. Tiene búsqueda, filtro por estado, paginación y exportación a CSV. Desde cada fila se puede editar el traslado, cambiar solo su estado, cancelarlo o reactivarlo.
- **Estados.** Solicitado, Vehículo Asignado, En Tránsito y Completado. Cancelar es un borrado lógico (queda en estado «Cancelado» y se puede revertir). Cada cambio de estado queda registrado en `historial_estado` junto con el usuario que lo hizo. Para pasar a «En Tránsito» hacen falta vehículo y chofer, y ninguno de los dos puede estar en otro traslado en tránsito al mismo tiempo. Los traslados completados o cancelados no se editan.
- **Seguimiento.** Por ahora es una maqueta con un mapa de Leaflet y OpenStreetMap, por lo que necesita conexión a internet para cargar los mosaicos. El rastreo por GPS está previsto para una etapa posterior.

### 4.3 Encuestas

- **Encuesta pública.** El paciente llega desde el botón «Dejar mi opinión» de cada documento del repositorio. Son tres preguntas: facilidad para acceder a la información (de 1 a 5), utilidad del documento y un comentario opcional de hasta 500 caracteres. Es anónima: se guarda el documento evaluado, las respuestas y la fecha. No se guarda nombre, cédula ni dirección IP.
- **Resultados.** Para el personal con sesión. Muestra la cantidad de respuestas, el promedio de facilidad, el porcentaje de pacientes que consideró útil el documento, gráficos de distribución, una tabla por documento y los comentarios más recientes, con filtro por documento.

## 5. Roles y permisos

| Rol                | Qué puede hacer                                                                                                                                                        |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Administrador**  | Todo lo del Usuario Básico, más Carga de Archivos y Gestión de Usuarios.                                                                                               |
| **Usuario Básico** | Panel de Documentación (consulta y QR), las tres pantallas de Ambulancias y los resultados de Encuestas. Agrupa en la práctica a médicos, enfermería y demás personal. |
| **Paciente**       | No tiene cuenta. Solo accede al repositorio público y a la encuesta, y únicamente con el token del QR.                                                                 |

La relación entre usuarios y roles es de muchos a muchos (tabla `usuario_rol`), de modo que se pueden sumar roles más adelante sin cambiar el esquema de usuarios.

## 6. Seguridad

- **Contraseñas** guardadas con `password_hash()` y verificadas con `password_verify()`. La contraseña nunca se guarda en la sesión.
- **Consultas preparadas** con PDO en todo el sistema. En ningún punto se concatenan datos del usuario dentro del SQL, y lo que se muestra en pantalla se escapa.
- **Rutas protegidas.** `AuthController::protegerRuta()` exige sesión activa y, cuando corresponde, un rol determinado. Las respuestas llevan cabeceras anti-caché para que el botón «Atrás» no muestre un panel después de cerrar sesión.
- **Borrado lógico** en usuarios, documentos y traslados. No se hace `DELETE` físico sobre esos datos.
- **Acceso por QR.** El QR contiene una URL con un token secreto que se compara con `hash_equals`. Ese token solo habilita el repositorio, la entrega de los PDF y la encuesta, y nada más.
- **PDF protegidos.** Nadie puede abrir la carpeta de uploads por su URL. Los archivos solo salen a través de `servir-documento.php`, que verifica sesión o token y que el documento esté activo.
- **Encuesta pública.** Token anti-CSRF, una respuesta por documento y por navegador, tope de envíos por sesión, un campo trampa contra envíos automáticos y validación estricta de cada respuesta.
- **Transacciones** en el alta y los cambios de estado de traslados, con rollback ante cualquier error.
- **Contenedor.** Apache oculta su versión, no lista directorios y bloquea el acceso a la carpeta de uploads. PHP no muestra errores al público y las cookies de sesión llevan `HttpOnly`, `SameSite=Lax` y modo estricto.

## 7. Base de datos

Base `database_clinicas`, motor InnoDB, charset `utf8mb4`. Son 24 tablas, que se pueden ver en el diagrama `database/DER-final/DER Clinicas.png`. Las diferencias entre ese diagrama y la base real están explicadas en [`DER-estado-y-correcciones.md`](database/DER-final/DER-estado-y-correcciones.md).

Todo está en **un solo script**: `database/query/sigsm-docker.sql`. Crea la base y las tablas, y carga los datos iniciales: el usuario Administrador, los dos roles, las 44 categorías, los estados de traslado, los tipos de vehículo y elemento, la compatibilidad entre ambos, dos vehículos de ejemplo y tres ubicaciones.

Dos cosas a tener en cuenta:

- La parte de datos iniciales no es repetible. Si se ejecuta dos veces sobre la misma base, duplica usuarios y categorías. Para empezar de cero hay que recrear la base.
- Las tablas `encuesta`, `pregunta`, `opcion_respuesta`, `respuesta_encuesta` y `respuesta_pregunta` vienen del diseño original, pero la encuesta que está en funcionamiento guarda todo en `encuesta_documento`. Las dejamos en el modelo por si más adelante se quiere una encuesta con preguntas configurables.

Requiere MySQL 8.0.16 o superior (o MariaDB 10.2 o superior), porque usa restricciones `CHECK`.

## 8. Instalación en un entorno local (XAMPP)

Es el entorno con el que desarrollamos.

1. Instalar XAMPP con Apache y MySQL, y arrancar ambos servicios.
2. Copiar o clonar el repositorio dentro de `htdocs`, por ejemplo `htdocs/proyecto-final-hospital-clinicas`.
3. Abrir phpMyAdmin, ir a la pestaña **Importar** y cargar `database/query/sigsm-docker.sql`. Eso crea la base `database_clinicas`.
4. Revisar los datos de conexión. Si no hay variables de entorno, `Servicios Comunes/Conexion BD/conexion.php` usa estos valores por defecto: servidor `localhost`, puerto `3306`, base `database_clinicas`, usuario `root` y contraseña `mysql`. Si en su XAMPP el usuario `root` tiene otra contraseña (por defecto viene vacía), hay que ajustarla en ese archivo.
5. Abrir `http://localhost/proyecto-final-hospital-clinicas/` e ingresar.

**Usuario inicial.** El script crea un Administrador con cédula `12345678`. Hay que cambiarle la contraseña en el primer ingreso. Para generar el hash de una contraseña nueva y actualizarla en la base:

```bash
php -r "echo password_hash('NuevaContraseñaSegura', PASSWORD_DEFAULT);"
```

```sql
UPDATE usuario SET contrasena = '<hash generado>' WHERE cedula = '12345678';
```

**Una advertencia sobre mayúsculas y minúsculas.** Windows no distingue entre `usuarioModelo.php` y `UsuarioModelo.php`, pero Linux sí. El código hace `require` con el nombre exacto del archivo, así que si el nombre en el repositorio cambia de mayúsculas en una computadora y no en otra, funciona en XAMPP y falla en el servidor. Nos pasó. Conviene ejecutar una vez `git config core.ignorecase false` en cada copia del repositorio.

## 9. Despliegue en un servidor Linux con Docker

El repositorio incluye un `Dockerfile` y un `docker-compose.yml` que levantan dos servicios:

- **app**: PHP 8.3 con Apache, con la extensión `pdo_mysql` y los módulos `rewrite` y `headers`.
- **db**: MySQL 8.0, que ejecuta `database/query/sigsm-docker.sql` automáticamente la primera vez que arranca.

Los datos persisten en dos volúmenes: `db_data` (la base) y `documentos_pdf` (los PDF subidos). Reconstruir o recrear los contenedores no los borra.

### 9.1 Requisitos del servidor

- Debian con Docker Engine y el plugin Docker Compose instalados (`docker compose version` tiene que responder).
- Git.
- Un dominio apuntando a la IP del servidor, si se quiere que los pacientes entren desde sus celulares.
- Los puertos 80 y 443 abiertos para el proxy; el 3306 **no** se abre nunca.

### 9.2 Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/sparrowsdevs/proyecto-final-hospital-clinicas.git
cd proyecto-final-hospital-clinicas

# 2. Crear el archivo de configuración a partir de la plantilla
cp .env.example .env
chmod 600 .env
nano .env

# 3. Generar el token del QR y pegarlo en SIGSM_TOKEN
openssl rand -hex 24

# 4. Construir y levantar
docker compose up -d --build

# 5. Verificar
docker compose ps
docker compose logs -f db
```

El servicio `db` tiene que quedar en estado `healthy`. Si falla la carga del script SQL, el error aparece en los logs de `db`.

### 9.3 Variables del archivo `.env`

| Variable           | Qué es                                                                                                            |
| ------------------ | ----------------------------------------------------------------------------------------------------------------- |
| `DB_ROOT_PASSWORD` | Contraseña de root de MySQL. Obligatoria.                                                                         |
| `DB_PASSWORD`      | Contraseña del usuario `sigsm` que usa la aplicación. Obligatoria.                                                |
| `SIGSM_TOKEN`      | Token secreto que va dentro del QR. Obligatorio; si se cambia, los QR impresos dejan de funcionar.                |
| `SIGSM_URL_BASE`   | URL pública con la que los celulares llegan al sistema, sin barra final. Ejemplo: `https://sigsm.ejemplo.com.uy`. |
| `APP_BIND`         | Dirección en la que escucha el contenedor. Por defecto `127.0.0.1`, es decir, solo desde el propio servidor.      |
| `APP_PORT`         | Puerto local de la aplicación. Por defecto `8080`.                                                                |

Las contraseñas y el token tienen que ser distintos entre sí y no se suben nunca al repositorio: `.env` está en el `.gitignore`.

### 9.4 Publicación con HTTPS

Por defecto la aplicación solo escucha en `127.0.0.1:8080`, a propósito: no queremos exponer Apache sin certificado. Para publicarla hay que poner delante un proxy inverso que atienda los puertos 80 y 443, obtenga el certificado y reenvíe a `127.0.0.1:8080`. Con Caddy, por ejemplo, alcanza con un archivo así:

```
sigsm.ejemplo.com.uy {
    reverse_proxy 127.0.0.1:8080
}
```

Nginx con Certbot también sirve. Una vez publicada, el campo `SIGSM_URL_BASE` del `.env` tiene que llevar la dirección `https://` definitiva, porque es la que se codifica dentro del QR.

### 9.5 Mantenimiento

```bash
# Actualizar a la última versión del código
git pull
docker compose up -d --build

# Ver los registros de la aplicación
docker compose logs -f app

# Copia de seguridad de la base
docker compose exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" database_clinicas' > respaldo.sql

# Copia de los PDF subidos
docker run --rm -v sigsm_documentos_pdf:/datos -v "$PWD":/destino alpine \
    tar czf /destino/documentos.tar.gz -C /datos .

# Empezar de cero (BORRA la base y los PDF)
docker compose down -v
```

El script SQL de inicio solo se ejecuta cuando el volumen `db_data` está vacío. Si se modifica el esquema después de la primera puesta en marcha, hay que aplicar el cambio a mano sobre la base en uso, o recrear los volúmenes con `down -v`.

## 10. Cómo probar el flujo del código QR

1. Con el sistema publicado (o, en la red local, con la IP del servidor), ingresar como Administrador.
2. En **Carga de Archivos**, subir un PDF y asignarle una categoría.
3. En **Panel de Documentación**, abrir la ventana del QR.
4. Escanearlo con un celular que **no** tenga sesión iniciada. Tiene que abrir el repositorio público con el documento.
5. Abrir el PDF, volver y tocar «Dejar mi opinión»; completar la encuesta.
6. Volver a ingresar como personal y revisar **Resultados de Encuestas**.

## 11. Estado actual

**Funcionando**

- Login, cierre de sesión y protección de rutas por rol.
- Menú de navegación único para todos los paneles.
- Gestión de usuarios y de documentos de punta a punta.
- QR y repositorio público.
- Formulario y panel de gestión de traslados, con edición, cambio de estado, cancelación, reactivación, filtros y exportación a CSV.
- Encuesta pública anónima y panel de resultados.
- Despliegue con Docker.

## 12. Tecnologías

- **PHP 8.3** orientado a objetos, sin frameworks, con arquitectura MVC.
- **MySQL 8** a través de **PDO** con consultas preparadas.
- **HTML5 y CSS3** escritos a mano (Grid y Flexbox), sin librerías de estilos.
- **JavaScript** sin librerías para las llamadas AJAX (`fetch`).
- **qrcode.js** para generar el QR en el navegador.
- **Leaflet y OpenStreetMap** para el mapa de la maqueta de seguimiento.
- **Docker y Docker Compose**, sobre Apache, para el despliegue.
- **Git y GitHub** para el control de versiones.
- **XAMPP** como entorno de desarrollo.
- **draw.io** para los diagramas UML y el diagrama entidad-relación.

## 13. Equipo

**Sparrows Devs** es el equipo de cuatro estudiantes que desarrolló el sistema. Todos participamos en la programación.

| Integrantes      |
| ---------------- |
| Ricardo Boggio   |
| Facundo Brun     |
| Fernando Aguirre |
| Miguel Duarte    |

Hospital de Clínicas «Dr. Manuel Quintela», Montevideo, Uruguay. Proyecto académico, 2026.
