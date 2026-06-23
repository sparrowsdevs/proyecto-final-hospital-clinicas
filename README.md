# 🏥 S.I.G.S.M. - Frontend del Sistema de Gestión Hospitalaria

## 📋 Descripción del Proyecto

Este repositorio contiene la arquitectura Frontend completa y las interfaces de usuario para el **S.I.G.S.M.** (Sistema Institucional de Gestión y Seguimiento Médico), diseñado para optimizar los flujos operativos del **Hospital de Clínicas**.

El proyecto abarca desde el índice centralizado de navegación (Dashboard) hasta las ventanas funcionales de cada área del hospital. Todo el diseño fue pensado bajo criterios de accesibilidad, usabilidad clínica y un enfoque visual minimalista.

## 🚀 Tecnologías Utilizadas

La maquetación de las vistas fue desarrollada desde cero utilizando tecnologías web estándar, demostrando dominio en la construcción de interfaces sin dependencia de frameworks externos (como Bootstrap o Tailwind):

- **HTML5 Semántico:** Estructuración clara de la información y formularios.
- **CSS3 Puro (Vanilla):**
  - Implementación de **CSS Grid** para la disposición de tarjetas y layouts complejos.
  - Uso de **Flexbox** para la alineación responsiva de barras de navegación, menús y formularios.
  - Uso de variables CSS (`:root`) para mantener la coherencia de la paleta de colores institucionales.
- **Recursos Visuales:** Tipografía _Inter_ para máxima legibilidad en pantallas y _Material Symbols Outlined_ para la iconografía médica.

## 📂 Estructura de Módulos y Vistas

El repositorio se divide en los siguientes módulos operativos, cada uno con sus respectivas ventanas de interfaz:

### 1. 🚑 Módulo Ambulancias

- `form-traslado.html`: Interfaz para el registro y solicitud de traslados de pacientes.
- `panel-de-gestion.html`: Tablero de control para la asignación y estado de los vehículos.
- `seguimiento.html`: Vista de monitoreo y geolocalización de unidades en tiempo real.

### 2. 📁 Módulo Documentación

- `cargar-documento.html`: Formulario para la subida y categorización de historias clínicas y resultados.
- `panel-administrador.html`: Vista gerencial para la supervisión de documentos y validaciones.
- `panel-documentacion.html`: Grilla de acceso rápido a los archivos de los pacientes.

### 3. 📊 Módulo Encuestas

- `encuestas.html`: Interfaz para la gestión y creación de formularios de satisfacción.
- `descarga-documento.html`: Vista para la exportación de reportes y métricas de calidad.
- `instrucciones.html`: Documentación operativa para el personal del área.

**Mas información:** https://deepwiki.com/riseboe/proyecto-final-hospital-clinicas
