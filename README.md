# Home Dial

Sistema web de seguimiento en casa para pacientes con dialisis peritoneal
y diabetes. Proyecto de Desarrollo de Software VII, UTP Chiriqui, 2026.

Permite registrar el balance hidrico de cada sesion de dialisis (4
recambios: infusion, drenaje, cualidad del drenaje), llevar el control de
glicemias, y genera alertas automaticas cuando algo sale de rango
(retencion de liquidos, glucosa alta/baja, drenaje turbio).

## Como esta hecho

PHP puro (procedural, sin frameworks) + MySQL (MySQLi con sentencias
preparadas). Sin Composer, sin librerias de PHP externas. La unica
dependencia externa es Chart.js (CDN) para el grafico de analitica
visual.

## Requisitos

- [Laragon](https://laragon.org/) (o cualquier stack Apache + PHP 8 +
  MySQL/MariaDB equivalente)
- PHP 8.1 o superior
- MySQL 8 o MariaDB 10.6+

## Instalacion

1. Cloná el repo dentro de la carpeta `www` de Laragon:

   ```
   cd C:\laragon\www
   git clone <url-de-este-repo> home_dial
   ```

2. Iniciá Laragon (Apache + MySQL).

3. Armá la base de datos. Abrí una terminal en la carpeta del proyecto y
   corré:

   ```
   mysql -u root database/schema.sql
   ```

   O importalo desde phpMyAdmin/HeidiSQL si preferís interfaz grafica:
   el archivo es `database/schema.sql`, crea la base `homedial` con las
   8 tablas, la vista `balance_diario_resumen` y los triggers que
   calculan el balance y las alertas automaticamente.

4. Generá las cuentas de prueba. El script `database/seed.sql` trae la
   estructura pero con los hash de contraseña vacios (por seguridad no se
   suben hash reales al repo). Para generar uno:

   ```
   php -r "echo password_hash('admin2026', PASSWORD_BCRYPT);"
   ```

   Corré ese comando por cada contraseña que necesites (`admin2026`,
   `medico2026`, `paciente2026`), pegá el resultado en
   `database/seed.sql` donde dice `PEGAR_HASH_AQUI`, y despues corré el
   script:

   ```
   mysql -u root homedial database/seed.sql
   ```

   Alternativa mas rapida: entrá como admin al sistema (usuario `admin`,
   sin contraseña configurada todavia no vas a poder entrar hasta hacer
   este paso) y desde ahi date de alta los medicos/pacientes que
   necesites, sin usar el seed.

5. Revisá `config/database.php` — por defecto apunta a
   `127.0.0.1`, usuario `root`, sin contraseña (la config por defecto de
   Laragon). Si tu MySQL tiene otra contraseña, cambiala ahi.

6. Entrá a `http://localhost/home_dial/` en el navegador.

## Estructura del proyecto

```
home_dial/
├── index.php              login + autoregistro de paciente
├── logout.php
├── config/database.php    conexion a MySQL
├── includes/               header, footer, navbar (por rol), funciones compartidas
├── assets/                 css, js, imagenes
├── database/                schema.sql (estructura completa) y seed.sql (datos de prueba)
├── modules/
│   ├── auth/                login
│   ├── dashboard.php        panel principal, distinto por rol
│   ├── pacientes/            CRUD de pacientes
│   ├── medicos/               CRUD de medicos
│   ├── sesiones/               historial de sesiones de dialisis
│   ├── recambios/               formulario de balance hidrico (el modulo central)
│   ├── glucosa/                  registro y diagnostico de glucosa
│   ├── balance/                   vista resumen (consulta la vista de la BD)
│   ├── alertas/                    bandeja de alertas
│   └── usuarios/                    gestion de cuentas (solo admin)
└── uploads/                 archivos subidos (vacio, se ignora en git)
```

## Roles

- **Administrador**: gestiona pacientes, medicos y usuarios admin. Ve
  todas las alertas.
- **Medico**: ve (solo lectura) a sus pacientes asignados y sus alertas.
- **Paciente**: registra su balance hidrico y su glucosa, ve su propio
  historial y a su medico asignado.

Los pacientes se dan de alta solos desde la pantalla de login
("Registrarme como paciente"), o los crea el administrador. Los medicos
solo los puede crear el administrador (desde ahi mismo se les genera el
usuario de acceso).

## Notas de seguridad

- Contraseñas con `password_hash()` (bcrypt), nunca en texto plano.
- Todas las consultas con datos de formularios usan sentencias
  preparadas (`prepare` + `bind_param`), no se arma SQL concatenando
  texto.
- Toda salida a HTML pasa por `htmlspecialchars()` para evitar XSS.
- Cada pagina valida sesion y rol antes de mostrar nada.
- El `database/seed.sql` de este repo no trae contraseñas reales
  precalculadas — hay que generarlas localmente (paso 4 de instalacion).

## Documentacion

Para el detalle tecnico completo (diccionario de datos, reglas de
negocio de cada modulo, ejemplos de codigo, triggers de la base) ver
`README_COMPLETO.md`.
