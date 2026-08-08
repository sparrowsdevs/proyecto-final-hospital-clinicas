-- S.I.G.S.M. - Módulo Documentación
-- Motor: MySQL/MariaDB | Charset: utf8mb4


CREATE TABLE usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    cedula          CHAR(8) NOT NULL UNIQUE,          -- Credencial de acceso
    contrasena      VARCHAR(255) NOT NULL,            -- Hash generado con password_hash()
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    activo          BOOLEAN NOT NULL DEFAULT TRUE      -- Borrado lógico
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rol (
    id_rol          INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol      VARCHAR(50) NOT NULL,
    descripcion     VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuario_rol (
    id_usuario      INT NOT NULL,
    id_rol          INT NOT NULL,
    fecha_asignacion DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categoria (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion     VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE documento (
    id_documento        INT AUTO_INCREMENT PRIMARY KEY,
    titulo              VARCHAR(200) NOT NULL,
    descripcion         VARCHAR(500),
    archivo_url         VARCHAR(255) NOT NULL,
    fecha_carga         DATE NOT NULL DEFAULT (CURRENT_DATE),
    id_categoria        INT NOT NULL,
    id_usuario_carga    INT NOT NULL,                 -- Quién cargó el documento
    activo              BOOLEAN NOT NULL DEFAULT TRUE, -- Borrado lógico
    fecha_modificacion  DATE,
    qr                  VARCHAR(255),                  -- URL/token del QR generado
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_carga) REFERENCES usuario(id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;