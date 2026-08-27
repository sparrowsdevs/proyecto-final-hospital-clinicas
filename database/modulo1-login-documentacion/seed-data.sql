-- Ingresar datos en la tabla usuario.
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