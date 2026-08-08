S.I.G.S.M. - Sistema Institucional de Gestión y Seguimiento Médico

Proyecto final para el Hospital de Clínicas. La idea es digitalizar algunos de los procesos internos del hospital que hoy se manejan de forma manual: traslados en ambulancia, gestión de documentación clínica y encuestas de satisfacción a pacientes.

Todo arranca desde un login por cédula, y de ahí cada usuario accede a los módulos según su rol (administrador, personal médico, chofer, etc).

Cómo está armado

Decidimos separar el proyecto en módulos, y cada módulo sigue más o menos la lógica de Modelo-Vista-Controlador. Después hay una carpeta de "Servicios Comunes" con todo lo que se comparte entre módulos (conexión a la base, autenticación, permisos), para no repetir código.

Módulos

Ambulancias — pedir un traslado, asignarle una unidad y hacerle seguimiento hasta que llega a destino.

Documentación — subir y categorizar historias clínicas y otros documentos de pacientes, con un panel para el personal de documentación y otro para administración.

Encuestas — armar encuestas de satisfacción, ver/exportar los resultados y dejar instrucciones para el personal que las usa.

Backend / autenticación

El login funciona con cédula y contraseña. Las contraseñas se guardan hasheadas (password_hash) y nunca se guarda la contraseña en la sesión, solo los datos del usuario y sus roles.

La conexión a la base es un Singleton en PHP con PDO (Servicios Comunes/Conexion BD/conexion.php), así todos los módulos usan la misma instancia en vez de abrir conexiones nuevas por todos lados.

Base de datos

En database/modulo1.sql está el modelo inicial: usuarios, roles (relación N:M por si un usuario tiene más de un rol), categorías y documentos. Motor InnoDB, charset utf8mb4.

Tecnologías
HTML5 + CSS3 puro para las vistas (sin Bootstrap ni Tailwind, todo maquetado a mano con Grid y Flexbox)
PHP orientado a objetos para el backend
MySQL para la base de datos
