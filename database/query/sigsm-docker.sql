SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS database_clinicas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE database_clinicas;


CREATE TABLE IF NOT EXISTS usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    cedula          CHAR(8) NOT NULL UNIQUE,
    contrasena      VARCHAR(255) NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    activo          BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rol (
    id_rol          INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol      ENUM('Administrador', 'Usuario Básico') NOT NULL,
    descripcion     VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuario_rol (
    id_usuario      INT NOT NULL,
    id_rol          INT NOT NULL,
    fecha_asignacion DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





CREATE TABLE IF NOT EXISTS categoria (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria ENUM(
        'Unidad de Cuidados Paliativos', 'Emergencia', 'Neonatología', 
        'Dpto. Clínico de medicina', 'Cardiología', 'Centro cardiovascular', 
        'Hemodinamia', 'Crujía cardíaca', 'Clínica médica a', 'Clínica médica b', 
        'Clínica médica c', 'Dermatología', 'Endocrinología', 'Gastroenterología', 
        'Unidad de ostomías', 'Geriatría', 'Hematología', 'Infectología', 
        'Medicina física, rehabilitación y medicina del deporte', 'Nefrología', 
        'Neurología', 'Oncología', 'Psiquiatría', 'U.e. Autoinmunes sistémicas', 
        'Unidad de tabaquismo', 'Depto. Clínico de cirugía', 'Anestesiología', 
        'Cirugía plástica y quemados', 'Cirugía vascular periférica', 
        'Ginecotocologica b', 'Neurocirugía', 'Odontología', 'Oftalmología', 
        'Otorrinolaringología', 'Quirúrgica a', 'Quirúrgica b', 'Quirúrgica f', 
        'Urología', 'Traumatología y ortopedia de adultos', 'Radioterapia', 
        'Medicina Nuclear', 'Hemoterapia', 'Imagenología', 'Ginecobstetricia'
    ) NOT NULL,
    descripcion     VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documento (
    id_documento        INT AUTO_INCREMENT PRIMARY KEY,
    titulo              VARCHAR(200) NOT NULL,
    descripcion         VARCHAR(500),
    archivo_url         VARCHAR(255) NOT NULL,
    fecha_carga         DATE NOT NULL DEFAULT (CURRENT_DATE),
    id_categoria        INT NOT NULL,
    id_usuario_carga    INT NOT NULL,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_modificacion  DATE,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_carga) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta (
    id_encuesta     INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria    INT NOT NULL,
    segmento        VARCHAR(150),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pregunta (
    id_pregunta     INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta     INT NOT NULL,
    tipo_pregunta   ENUM('Opcion Multiple', 'Seleccion Unica', 'Texto Abierto') NOT NULL,
    texto_pregunta  VARCHAR(300) NOT NULL,
    FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS opcion_respuesta (
    id_opcion       INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta     INT NOT NULL,
    texto_opcion    VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS respuesta_encuesta (
    id_resp_encuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta      INT NOT NULL,
    fecha_envio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS respuesta_pregunta (
    id_resp_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_resp_encuesta INT NOT NULL,
    id_pregunta      INT NOT NULL,
    id_opcion        INT NULL,
    texto_respuesta  VARCHAR(1000) NULL,
    FOREIGN KEY (id_resp_encuesta) REFERENCES respuesta_encuesta(id_resp_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_opcion) REFERENCES opcion_respuesta(id_opcion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





CREATE TABLE IF NOT EXISTS tipo_vehiculo (
    id_tipo_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tipo_elemento (
    id_tipo_elemento INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS compatibilidad (
    id_tipo_vehiculo INT NOT NULL,
    id_tipo_elemento INT NOT NULL,
    PRIMARY KEY (id_tipo_vehiculo, id_tipo_elemento),
    FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo(id_tipo_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_elemento) REFERENCES tipo_elemento(id_tipo_elemento) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vehiculo (
    id_vehiculo      INT AUTO_INCREMENT PRIMARY KEY,
    matricula        VARCHAR(50) NOT NULL,
    id_tipo_vehiculo INT NOT NULL,
    FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo(id_tipo_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ubicacion (
    id_ubicacion     INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ruta (
    id_ruta          INT AUTO_INCREMENT PRIMARY KEY,
    id_origen        INT NOT NULL,
    id_destino       INT NOT NULL,
    FOREIGN KEY (id_origen) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS paciente (
    ci               VARCHAR(20) PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS canal_solicitud (
    id_canal         INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proveedor_externo (
    id_proveedor     INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estado_traslado (
    id_estado        INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitud_traslado (
    id_solicitud        INT AUTO_INCREMENT PRIMARY KEY,
    ci_paciente         VARCHAR(20) NOT NULL,
    id_tipo_elemento    INT NOT NULL,
    id_origen           INT NOT NULL,
    id_destino          INT NOT NULL,
    id_canal            INT NOT NULL,
    id_usuario_solicita INT NOT NULL,
    estado_solicitud    VARCHAR(50) NOT NULL,
    FOREIGN KEY (ci_paciente) REFERENCES paciente(ci) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_elemento) REFERENCES tipo_elemento(id_tipo_elemento) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_origen) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_canal) REFERENCES canal_solicitud(id_canal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_solicita) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS traslado (
    id_traslado      INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud     INT NOT NULL,
    id_vehiculo      INT NULL,
    id_chofer        INT NULL,
    id_enfermero     INT NULL,
    id_proveedor     INT NULL,
    id_ruta          INT NOT NULL,
    hora_prevista    DATETIME,
    hora_real        DATETIME,
    hora_retorno     DATETIME,
    FOREIGN KEY (id_solicitud) REFERENCES solicitud_traslado(id_solicitud) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculo(id_vehiculo) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_chofer) REFERENCES usuario(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_enfermero) REFERENCES usuario(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_proveedor) REFERENCES proveedor_externo(id_proveedor) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_ruta) REFERENCES ruta(id_ruta) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historial_estado (
    id_historial     INT AUTO_INCREMENT PRIMARY KEY,
    id_traslado      INT NOT NULL,
    id_estado        INT NOT NULL,
    id_usuario       INT NOT NULL,
    fecha_hora       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_traslado) REFERENCES traslado(id_traslado) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_estado) REFERENCES estado_traslado(id_estado) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO 

    usuario(cedula, contrasena, nombre, apellido, email,activo)

VALUES

    ('12345678','$2y$10$bQHP3R2bHe5VgdxJ3WwRZuwBzPgg0y4Ny4SypXDDElrLrlgJcmgs6','Todo','Poderoso','root@gmail.com',TRUE);

    

    
-- Ingresar roles validos en el sistema por el momento.
INSERT INTO

    rol (nombre_rol, descripcion) 

VALUES 

    ('Administrador', 'Administrador del sistema con acceso total'),

    ('Usuario Básico', 'Usuario regular del sistema');

    
-- Asignarle un rol al usuario.
INSERT INTO 

    usuario_rol (id_usuario, id_rol)

SELECT 

    u.id_usuario, r.id_rol

FROM

    usuario u

JOIN

    rol r 

ON

    r.nombre_rol = 'Administrador'

WHERE

    u.cedula = '12345678';


-- Ingresar algunas categorías recomendadas por el clínicas.

INSERT INTO 

    categoria (nombre_categoria, descripcion) 

VALUES

    ('Unidad de Cuidados Paliativos', NULL),

    ('Emergencia', NULL),

    ('Neonatología', NULL),

    ('Dpto. Clínico de medicina', NULL),

    ('Cardiología', NULL),

    ('Centro cardiovascular', NULL),

    ('Hemodinamia', NULL),

    ('Crujía cardíaca', NULL),

    ('Clínica médica a', NULL),

    ('Clínica médica b', NULL),

    ('Clínica médica c', NULL),

    ('Dermatología', NULL),

    ('Endocrinología', NULL),

    ('Gastroenterología', NULL),

    ('Unidad de ostomías', NULL),

    ('Geriatría', NULL),

    ('Hematología', NULL),

    ('Infectología', NULL),

    ('Medicina física, rehabilitación y medicina del deporte', NULL),

    ('Nefrología', NULL),

    ('Neurología', NULL),

    ('Oncología', NULL),

    ('Psiquiatría', NULL),

    ('U.e. Autoinmunes sistémicas', NULL),

    ('Unidad de tabaquismo', NULL),

    ('Depto. Clínico de cirugía', NULL),

    ('Anestesiología', NULL),

    ('Cirugía plástica y quemados', NULL),

    ('Cirugía vascular periférica', NULL),

    ('Ginecotocologica b', NULL),

    ('Neurocirugía', NULL),

    ('Odontología', NULL),

    ('Oftalmología', NULL),

    ('Otorrinolaringología', NULL),

    ('Quirúrgica a', NULL),

    ('Quirúrgica b', NULL),

    ('Quirúrgica f', NULL),
    
    ('Urología', NULL),
    
    ('Traumatología y ortopedia de adultos', NULL),
    
    ('Radioterapia', NULL),
    
    ('Medicina Nuclear', NULL),
    
    ('Hemoterapia', NULL),
    
    ('Imagenología', NULL),
    
    ('Ginecobstetricia', NULL);


-- Estados del traslado ('Cancelado' es el borrado lógico)
INSERT INTO estado_traslado (nombre)
SELECT 'Solicitado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM estado_traslado WHERE nombre = 'Solicitado');

INSERT INTO estado_traslado (nombre)
SELECT 'Vehículo Asignado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM estado_traslado WHERE nombre = 'Vehículo Asignado');

INSERT INTO estado_traslado (nombre)
SELECT 'En Tránsito' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM estado_traslado WHERE nombre = 'En Tránsito');

INSERT INTO estado_traslado (nombre)
SELECT 'Completado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM estado_traslado WHERE nombre = 'Completado');

INSERT INTO estado_traslado (nombre)
SELECT 'Cancelado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM estado_traslado WHERE nombre = 'Cancelado');

-- Canal de solicitud usado por el formulario
INSERT INTO canal_solicitud (nombre)
SELECT 'App Interna' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM canal_solicitud WHERE nombre = 'App Interna');

-- Elementos que se trasladan junto al paciente
INSERT INTO tipo_elemento (nombre)
SELECT 'Camilla' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_elemento WHERE nombre = 'Camilla');

INSERT INTO tipo_elemento (nombre)
SELECT 'Silla de Ruedas' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_elemento WHERE nombre = 'Silla de Ruedas');

INSERT INTO tipo_elemento (nombre)
SELECT 'Ninguno' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_elemento WHERE nombre = 'Ninguno');

-- Tipos de vehículo
INSERT INTO tipo_vehiculo (nombre)
SELECT 'Ambulancia Especializada' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_vehiculo WHERE nombre = 'Ambulancia Especializada');

INSERT INTO tipo_vehiculo (nombre)
SELECT 'Ambulancia Común' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_vehiculo WHERE nombre = 'Ambulancia Común');

INSERT INTO tipo_vehiculo (nombre)
SELECT 'Minibús' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tipo_vehiculo WHERE nombre = 'Minibús');

-- Compatibilidad vehículo / elemento
INSERT IGNORE INTO compatibilidad (id_tipo_vehiculo, id_tipo_elemento)
SELECT tv.id_tipo_vehiculo, te.id_tipo_elemento
FROM tipo_vehiculo tv
JOIN tipo_elemento te
  ON (tv.nombre = 'Ambulancia Especializada' AND te.nombre = 'Camilla')
  OR (tv.nombre = 'Ambulancia Común'         AND te.nombre = 'Camilla')
  OR (tv.nombre = 'Ambulancia Común'         AND te.nombre = 'Silla de Ruedas')
  OR (tv.nombre = 'Minibús'                  AND te.nombre = 'Silla de Ruedas')
  OR (tv.nombre = 'Minibús'                  AND te.nombre = 'Ninguno');

-- Vehículos de la flota (reemplazar por la flota real cuando corresponda)
INSERT INTO vehiculo (matricula, id_tipo_vehiculo)
SELECT 'SAM-1234', id_tipo_vehiculo FROM tipo_vehiculo WHERE nombre = 'Ambulancia Especializada'
  AND NOT EXISTS (SELECT 1 FROM vehiculo WHERE matricula = 'SAM-1234');
INSERT INTO vehiculo (matricula, id_tipo_vehiculo)
SELECT 'SAM-5678', id_tipo_vehiculo FROM tipo_vehiculo WHERE nombre = 'Ambulancia Común'
  AND NOT EXISTS (SELECT 1 FROM vehiculo WHERE matricula = 'SAM-5678');

-- Ubicaciones iniciales (el formulario permite agregar más)
INSERT INTO ubicacion (nombre)
SELECT 'Hospital de Clínicas - Emergencia' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM ubicacion WHERE nombre = 'Hospital de Clínicas - Emergencia');

INSERT INTO ubicacion (nombre)
SELECT 'Hospital Maciel' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM ubicacion WHERE nombre = 'Hospital Maciel');

INSERT INTO ubicacion (nombre)
SELECT 'Domicilio Paciente (Centro)' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM ubicacion WHERE nombre = 'Domicilio Paciente (Centro)');

INSERT INTO compatibilidad (id_tipo_vehiculo, id_tipo_elemento)
SELECT tv.id_tipo_vehiculo, te.id_tipo_elemento
FROM tipo_vehiculo tv
CROSS JOIN tipo_elemento te
WHERE te.nombre = 'Ninguno'
  AND NOT EXISTS (
        SELECT 1 FROM compatibilidad c
        WHERE c.id_tipo_vehiculo = tv.id_tipo_vehiculo
          AND c.id_tipo_elemento = te.id_tipo_elemento
  );


CREATE TABLE IF NOT EXISTS encuesta_documento (
    id_encuesta         INT AUTO_INCREMENT PRIMARY KEY,
    id_documento        INT NOT NULL,                      -- Documento evaluado
    calificacion_acceso TINYINT UNSIGNED NOT NULL,         -- 1 a 5: facilidad para acceder a la información
    utilidad            ENUM('muy_util', 'si', 'regular', 'no', 'para_nada') NOT NULL,
    comentario          VARCHAR(500) NULL,                 -- Opcional; texto libre, sin datos personales
    fecha_hora          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_encuesta_calificacion CHECK (calificacion_acceso BETWEEN 1 AND 5),

    -- RESTRICT: los documentos se dan de baja con borrado lógico, y las
    -- respuestas históricas no deben perderse.
    CONSTRAINT fk_encuesta_documento FOREIGN KEY (id_documento)
        REFERENCES documento(id_documento)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_encuesta_documento (id_documento),
    INDEX idx_encuesta_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;