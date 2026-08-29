-- S.I.G.S.M. - Hospital de Clínicas (V2 - 23 Entidades)
-- Motor: MySQL | Charset: utf8mb4


-- BLOQUE 1: AUTENTICACIÓN
-- ==========================================
CREATE TABLE usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    cedula          CHAR(8) NOT NULL UNIQUE,
    contrasena      VARCHAR(255) NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    activo          BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rol (
    id_rol          INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol      ENUM('Administrador', 'Usuario Básico') NOT NULL,
    descripcion     VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuario_rol (
    id_usuario      INT NOT NULL,
    id_rol          INT NOT NULL,
    fecha_asignacion DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- BLOQUE 2: DOCUMENTACIÓN Y ENCUESTAS
-- ==========================================

CREATE TABLE categoria (
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

CREATE TABLE documento (
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

CREATE TABLE encuesta (
    id_encuesta     INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria    INT NOT NULL,
    segmento        VARCHAR(150),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pregunta (
    id_pregunta     INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta     INT NOT NULL,
    tipo_pregunta   ENUM('Opcion Multiple', 'Seleccion Unica', 'Texto Abierto') NOT NULL,
    texto_pregunta  VARCHAR(300) NOT NULL,
    FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE opcion_respuesta (
    id_opcion       INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta     INT NOT NULL,
    texto_opcion    VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE respuesta_encuesta (
    id_resp_encuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_encuesta      INT NOT NULL,
    fecha_envio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE respuesta_pregunta (
    id_resp_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_resp_encuesta INT NOT NULL,
    id_pregunta      INT NOT NULL,
    id_opcion        INT NULL,
    texto_respuesta  VARCHAR(1000) NULL,
    FOREIGN KEY (id_resp_encuesta) REFERENCES respuesta_encuesta(id_resp_encuesta) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_opcion) REFERENCES opcion_respuesta(id_opcion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- BLOQUE 3: TRASLADOS Y AMBULANCIAS
-- ==========================================

CREATE TABLE tipo_vehiculo (
    id_tipo_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tipo_elemento (
    id_tipo_elemento INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE compatibilidad (
    id_tipo_vehiculo INT NOT NULL,
    id_tipo_elemento INT NOT NULL,
    PRIMARY KEY (id_tipo_vehiculo, id_tipo_elemento),
    FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo(id_tipo_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_elemento) REFERENCES tipo_elemento(id_tipo_elemento) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehiculo (
    id_vehiculo      INT AUTO_INCREMENT PRIMARY KEY,
    matricula        VARCHAR(50) NOT NULL,
    id_tipo_vehiculo INT NOT NULL,
    FOREIGN KEY (id_tipo_vehiculo) REFERENCES tipo_vehiculo(id_tipo_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ubicacion (
    id_ubicacion     INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ruta (
    id_ruta          INT AUTO_INCREMENT PRIMARY KEY,
    id_origen        INT NOT NULL,
    id_destino       INT NOT NULL,
    FOREIGN KEY (id_origen) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_destino) REFERENCES ubicacion(id_ubicacion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE paciente (
    ci               VARCHAR(20) PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE canal_solicitud (
    id_canal         INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE proveedor_externo (
    id_proveedor     INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE estado_traslado (
    id_estado        INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE solicitud_traslado (
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

CREATE TABLE traslado (
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

CREATE TABLE historial_estado (
    id_historial     INT AUTO_INCREMENT PRIMARY KEY,
    id_traslado      INT NOT NULL,
    id_estado        INT NOT NULL,
    id_usuario       INT NOT NULL,
    fecha_hora       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_traslado) REFERENCES traslado(id_traslado) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_estado) REFERENCES estado_traslado(id_estado) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;