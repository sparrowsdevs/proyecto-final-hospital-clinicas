# Base de datos

Acá está todo lo necesario para crear la base `database_clinicas`: el script SQL y el diagrama entidad-relación.

## Contenido

| Ruta                         | Para qué sirve                                                         |
| ---------------------------- | ---------------------------------------------------------------------- |
| `query/sigsm-docker.sql`     | Script único. Crea la base, las 24 tablas y carga los datos iniciales. |
| `DER-final/DER Clinicas.png` | Diagrama entidad-relación del modelo completo (24 entidades).          |

## Qué carga el script

- El usuario Administrador inicial (cédula `12345678`) y los dos roles del sistema.
- Las 44 categorías de documentos, que corresponden a servicios del hospital.
- Los estados de traslado: Solicitado, Vehículo Asignado, En Tránsito, Completado y Cancelado.
- Los tipos de elemento (Camilla, Silla de Ruedas, Ninguno), los tipos de vehículo y la compatibilidad entre ambos.
- Dos vehículos de ejemplo y tres ubicaciones, para poder probar el formulario de traslados.

No se cargan pacientes, traslados ni documentos de ejemplo.

## Cómo usarlo

**Con Docker.** No hay que hacer nada: `docker-compose.yml` monta este archivo y MySQL lo ejecuta solo la primera vez que arranca con el volumen vacío.

**Con XAMPP.** Entrar a phpMyAdmin, pestaña _Importar_, elegir `query/sigsm-docker.sql` y ejecutar.

**Desde la consola:**

```bash
mysql -u root -p < database/query/sigsm-docker.sql
```

## Cosas a tener en cuenta

- **No ejecutarlo dos veces sobre la misma base.** Las tablas se crean con `IF NOT EXISTS`, pero la parte de datos iniciales no es repetible y duplicaría usuarios y categorías. Para empezar de cero hay que borrar la base y volver a importar.
- **Cambiar la contraseña del Administrador** apenas se ingresa por primera vez.
- Hace falta MySQL 8.0.16 o superior (o MariaDB 10.2 o superior) por las restricciones `CHECK`.
- Las tablas `encuesta`, `pregunta`, `opcion_respuesta`, `respuesta_encuesta` y `respuesta_pregunta` pertenecen al diseño original y hoy no se usan. La encuesta en funcionamiento guarda sus respuestas en `encuesta_documento`.
- Todas las claves foráneas usan `RESTRICT` sobre los datos que tienen borrado lógico, para no perder historial por error.
