-- Tabla de Usuarios con datos personales
-- Fecha de creación: 24 de abril de 2026

CREATE TABLE IF NOT EXISTS usuarios_datos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nro_cedula VARCHAR(20) UNIQUE NOT NULL,
    nro_socio VARCHAR(20) UNIQUE NOT NULL,
    nacionalidad VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    celular VARCHAR(20) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar 10 registros de ejemplo
INSERT INTO usuarios_datos (nombre, apellido, nro_cedula, nro_socio, nacionalidad, fecha_nacimiento, celular, correo) VALUES
('Juan', 'Pérez', '1234567', 'SOC-001', 'Paraguaya', '1985-03-15', '0981123456', 'juan.perez@email.com'),
('María', 'González', '2345678', 'SOC-002', 'Paraguaya', '1990-07-22', '0982234567', 'maria.gonzalez@email.com'),
('Carlos', 'Rodríguez', '3456789', 'SOC-003', 'Argentina', '1982-11-08', '0983345678', 'carlos.rodriguez@email.com'),
('Ana', 'López', '4567890', 'SOC-004', 'Paraguaya', '1995-01-30', '0984456789', 'ana.lopez@email.com'),
('Pedro', 'Martínez', '5678901', 'SOC-005', 'Uruguaya', '1988-06-12', '0985567890', 'pedro.martinez@email.com'),
('Laura', 'Fernández', '6789012', 'SOC-006', 'Paraguaya', '1992-09-25', '0986678901', 'laura.fernandez@email.com'),
('Jorge', 'Sánchez', '7890123', 'SOC-007', 'Boliviana', '1978-12-03', '0987789012', 'jorge.sanchez@email.com'),
('Carmen', 'Torres', '8901234', 'SOC-008', 'Paraguaya', '1998-04-18', '0988890123', 'carmen.torres@email.com'),
('Miguel', 'Ramírez', '9012345', 'SOC-009', 'Paraguaya', '1983-08-07', '0989901234', 'miguel.ramirez@email.com'),
('Sofia', 'Benítez', '1234568', 'SOC-010', 'Paraguaya', '1997-02-14', '0981012345', 'sofia.benitez@email.com');