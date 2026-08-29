
-- 1. DATOS DE AUTENTICACIÓN
-- ==========================================
INSERT INTO usuario (cedula, contrasena, nombre, apellido, email, activo) VALUES
('11111111', 'prueba', 'Admin', 'Sistema', 'admin@clinicas.uy', TRUE),
('22222222', 'prueba', 'Enfermera', 'Pérez', 'eperez@clinicas.uy', TRUE),
('33333333', 'prueba', 'Chofer', 'Gómez', 'cgomez@clinicas.uy', TRUE);

INSERT INTO rol (nombre_rol, descripcion) VALUES 
('Administrador', 'Acceso total al sistema'),
('Usuario Básico', 'Personal médico y operativo');

INSERT INTO usuario_rol (id_usuario, id_rol) VALUES 
(1, 1), -- Admin es Administrador
(2, 2), -- Enfermera es Usuario Básico
(3, 2); -- Chofer es Usuario Básico

                                                                                                                                                
-- 2. DATOS DE DOCUMENTACIÓN Y ENCUESTAS
-- ==========================================
INSERT INTO categoria (nombre_categoria, descripcion) VALUES 
('Emergencia', 'Documentos de emergencia'),
('Cardiología', 'Protocolos cardiológicos'),
('Radioterapia', 'Guías de radioterapia');

INSERT INTO documento (titulo, descripcion, archivo_url, id_categoria, id_usuario_carga, activo) VALUES 
('Protocolo Triage', 'Guía rápida de triage', 'https://storage/doc1.pdf', 1, 1, TRUE),
('Cuidados Post-Infarto', 'Folleto para paciente', 'https://storage/doc2.pdf', 2, 2, TRUE);

INSERT INTO encuesta (id_categoria, segmento) VALUES 
(1, 'Pacientes dados de alta de Emergencia');

INSERT INTO pregunta (id_encuesta, tipo_pregunta, texto_pregunta) VALUES 
(1, 'Seleccion Unica', '¿Cómo califica la atención recibida?'),
(1, 'Texto Abierto', '¿Tiene alguna sugerencia de mejora?');

INSERT INTO opcion_respuesta (id_pregunta, texto_opcion) VALUES 
(1, 'Excelente'), (1, 'Buena'), (1, 'Mala');

INSERT INTO respuesta_encuesta (id_encuesta, fecha_envio) VALUES 
(1, NOW());

INSERT INTO respuesta_pregunta (id_resp_encuesta, id_pregunta, id_opcion, texto_respuesta) VALUES 
(1, 1, 1, NULL), -- Eligió 'Excelente'
(1, 2, NULL, 'Fueron muy amables y rápidos.'); -- Texto abierto


-- 3. DATOS DE TRASLADOS Y AMBULANCIAS
-- ==========================================
INSERT INTO tipo_vehiculo (nombre) VALUES 
('Ambulancia Especializada'), ('Ambulancia Común'), ('Minibús');

INSERT INTO tipo_elemento (nombre) VALUES 
('Camilla'), ('Silla de Ruedas'), ('Ninguno');

INSERT INTO compatibilidad (id_tipo_vehiculo, id_tipo_elemento) VALUES 
(1, 1), (2, 1), (2, 2), (3, 2), (3, 3);

INSERT INTO vehiculo (matricula, id_tipo_vehiculo) VALUES 
('SAM-1234', 1), ('SAM-5678', 2);

INSERT INTO ubicacion (nombre) VALUES 
('Hospital de Clínicas - Emergencia'), 
('Hospital Maciel'), 
('Domicilio Paciente (Centro)');

INSERT INTO ruta (id_origen, id_destino) VALUES 
(1, 2), (1, 3);

INSERT INTO paciente (ci, nombre, apellido) VALUES 
('44444444', 'Juan', 'Rodríguez'),
('55555555', 'María', 'López');

INSERT INTO canal_solicitud (nombre) VALUES 
('App Interna'), ('Llamada Telefónica');

INSERT INTO proveedor_externo (nombre) VALUES 
('ASSE - SAME 105'), ('SUAT');

INSERT INTO estado_traslado (nombre) VALUES 
('Solicitado'), ('Vehículo Asignado'), ('En Tránsito'), ('Completado');

INSERT INTO solicitud_traslado (ci_paciente, id_tipo_elemento, id_origen, id_destino, id_canal, id_usuario_solicita, estado_solicitud) VALUES 
('44444444', 1, 1, 2, 1, 2, 'En Tránsito'),
('55555555', 2, 1, 3, 2, 2, 'Solicitado');

INSERT INTO traslado (id_solicitud, id_vehiculo, id_chofer, id_enfermero, id_proveedor, id_ruta, hora_prevista, hora_real) VALUES 
(1, 1, 3, 2, NULL, 1, '2023-11-01 10:00:00', '2023-11-01 10:15:00');

INSERT INTO historial_estado (id_traslado, id_estado, id_usuario, fecha_hora) VALUES 
(1, 1, 2, '2023-11-01 09:30:00'),
(1, 2, 1, '2023-11-01 09:45:00'),
(1, 3, 3, '2023-11-01 10:15:00');