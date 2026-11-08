# Módulo Encuestas

Recoge la opinión de los pacientes sobre los documentos del repositorio y la muestra al personal en un panel de resultados.

## Estructura

| Archivo                             | Función                                          |
| ----------------------------------- | ------------------------------------------------ |
| `Modelo/EncuestaModelo.php`         | Guarda las respuestas y calcula los indicadores. |
| `Controlador/procesar-encuesta.php` | Recibe y valida el envío del paciente.           |
| `Vista/encuesta.php`                | Encuesta pública. Sin sesión, con token del QR.  |
| `Vista/resultados-encuestas.php`    | Resultados. Requiere sesión (cualquier rol).     |

## La encuesta

Tres preguntas por documento:

1. **Facilidad de acceso:** de 1 a 5.
2. **Utilidad de la información:** muy útil, sí, regular, no, para nada.
3. **Comentario:** opcional, hasta 500 caracteres.

Se llega desde el botón «Dejar mi opinión» de cada documento del repositorio público.

## Anonimato

La tabla `encuesta_documento` guarda únicamente el documento evaluado, las respuestas y la fecha. **No se guarda nombre, cédula ni dirección IP.** Se pide que los comentarios no incluyan datos personales ni médicos.

## Protecciones contra abuso

- Token anti-CSRF en una sesión aparte (`sigsm_publico`).
- Una respuesta por documento y por navegador.
- Tope de 15 encuestas por sesión.
- Campo trampa (honeypot) oculto: si se completa, el envío se descarta.
- Validación estricta del lado servidor de la calificación, la utilidad y el largo del comentario.

## Panel de resultados

Muestra la cantidad de respuestas, el promedio de facilidad de acceso y el porcentaje de pacientes que consideró útil el documento, gráficos de distribución, una tabla por documento y los comentarios más recientes. Todo se puede filtrar por documento. Los cálculos están en `EncuestaModelo`.

## Notas

- Las respuestas históricas no se borran: la clave foránea hacia `documento` usa `RESTRICT`, y los documentos se dan de baja con borrado lógico.
- Las tablas `encuesta`, `pregunta`, `opcion_respuesta`, `respuesta_encuesta` y `respuesta_pregunta` del diseño original no se usan en esta versión.
- Los archivos `encuestas.css` y `encuesta-publica.css` son los estilos en uso. Las maquetas HTML que había en `Vista/` se retiraron.
