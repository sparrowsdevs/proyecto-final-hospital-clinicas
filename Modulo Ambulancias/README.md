# Módulo Ambulancias

Registro y gestión de **traslados no urgentes**: altas de pacientes, insumos y órganos.

**Límite del módulo:** no es un sistema de emergencias. No atiende Código Rojo ni reemplaza al SAME 105. Del paciente solo se guarda cédula, nombre y apellido; no hay campos de diagnóstico ni notas clínicas.

## Estructura

| Archivo                             | Función                                                                        |
| ----------------------------------- | ------------------------------------------------------------------------------ |
| `Modelo/TrasladoModelo.php`         | Toda la lógica de traslados: consultas, estados, validaciones y transacciones. |
| `Controlador/procesar-traslado.php` | Endpoint AJAX con las acciones del módulo (ver abajo).                         |
| `Vista/form-traslado.php`           | Formulario para registrar un traslado.                                         |
| `Vista/panel-de-gestion.php`        | Tarjetas de resumen y tabla de traslados con sus acciones.                     |
| `Vista/seguimiento.php`             | Maqueta de seguimiento con mapa (sin GPS).                                     |

Pueden usar el módulo el Administrador y el Usuario Básico.

## Acciones del controlador

`buscar-paciente`, `crear-ubicacion`, `crear-vehiculo`, `crear`, `actualizar`, `cambiar-estado`, `cancelar` y `reactivar`. Todas llegan por POST con el campo `accion` y responden en JSON.

## Estados de un traslado

```
Solicitado ⇄ Vehículo Asignado → En Tránsito → Completado
```

- Desde **Solicitado** se pasa a **Vehículo Asignado**. Desde ahí se puede volver a Solicitado o avanzar a **En Tránsito**, y de ahí a **Completado**.
- **Cancelado** es el borrado lógico y se alcanza con la acción _cancelar_. Se puede revertir con _reactivar_.
- **Completado** y **Cancelado** son estados finales: el traslado ya no se edita.
- Cada cambio queda en `historial_estado` con el usuario y la hora. El estado actual de un traslado es el último registro de ese historial.

## Reglas de asignación

- Para pasar a **En Tránsito** hacen falta vehículo y chofer.
- Un mismo vehículo o un mismo chofer no puede estar en dos traslados «En Tránsito» a la vez.
- Solo se pueden asignar vehículos compatibles con el elemento a trasladar (tabla `compatibilidad`). El elemento «Ninguno» (el paciente no necesita camilla ni silla de ruedas) es compatible con cualquier vehículo; esta regla también está aplicada en el código.
- Chofer y acompañante se eligen entre los usuarios activos. Si no hay acompañante, el panel muestra «S/A».

## Transacciones

Registrar un traslado crea, según haga falta, el paciente, la solicitud, la ruta, el traslado y su primer estado. Todo ocurre en una única transacción: si algo falla, no queda nada a medias. Lo mismo vale para editar, cancelar y reactivar.

## Panel de gestión

Búsqueda, filtro por estado, paginación y exportación a CSV. La tabla ocupa todo el ancho; en pantallas donde no entra (por ejemplo, un celular) se desliza en horizontal. Las tarjetas muestran el total de traslados, los vehículos disponibles y los choferes activos. En cada fila: editar, cambiar solo el estado (ventana chica), cancelar y reactivar.

## Pendiente

- El **seguimiento** es una maqueta. El rastreo por GPS no está implementado.
- El mapa usa Leaflet y OpenStreetMap, así que necesita conexión a internet.
