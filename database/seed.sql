-- seed.sql
-- Crea las cuentas de prueba que pide el enunciado del proyecto:
-- admin / admin2026
-- paciente / paciente2026
-- medico1 / medico2026 (medico de prueba)
--
-- Los hash de abajo son de ejemplo, hay que generar los propios antes de
-- correr este script. Para generar un hash desde la terminal:
--
--   php -r "echo password_hash('admin2026', PASSWORD_BCRYPT);"
--
-- Reemplazar cada 'PEGAR_HASH_AQUI' por el resultado de ese comando
-- (correrlo una vez por cada contraseña: admin2026, medico2026, paciente2026).

UPDATE usuarios SET password_hash = 'PEGAR_HASH_AQUI'
WHERE nombre_usuario = 'admin';

INSERT INTO usuarios (nombre_usuario, password_hash, rol_id, activo)
VALUES ('medico1', 'PEGAR_HASH_AQUI', 3, 1);

INSERT INTO medicos (usuario_id, nombre, apellido, especialidad, idoneidad, telefono, email, activo)
VALUES (
    (SELECT id FROM usuarios WHERE nombre_usuario = 'medico1'),
    'Carlos', 'Miranda', 'Nefrologia', 'MED-4521', '6600-1111', 'cmiranda@homedial.com', 1
);

INSERT INTO usuarios (nombre_usuario, password_hash, rol_id, activo)
VALUES ('paciente', 'PEGAR_HASH_AQUI', 2, 1);

INSERT INTO pacientes (usuario_id, medico_id, nombre, apellido, fecha_nacimiento, sexo, cedula, telefono, direccion, tipo_sistema_dp, peso_kg, talla_cm, tipo_sangre, fecha_inicio_dp, activo)
VALUES (
    (SELECT id FROM usuarios WHERE nombre_usuario = 'paciente'),
    (SELECT id FROM medicos WHERE usuario_id = (SELECT id FROM usuarios WHERE nombre_usuario = 'medico1')),
    'Juan', 'Perez', '1978-04-12', 'M', '8-123-456', '6600-1234', 'Via Boquete, David',
    'Baxter', 78.50, 172.0, 'O+', '2025-02-01', 1
);
