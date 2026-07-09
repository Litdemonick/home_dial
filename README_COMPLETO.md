# Home Dial - Documento tecnico completo

Este documento es la version extendida del README. Sirve de base para armar
el documento Word y las diapositivas de la sustentacion: explica cada
tecnica de PHP usada, cada archivo, cada tabla de la base, y el porque de
cada decision. Todo esta basado en el material que dio el profesor
(modulo de PHP/MySQL, ejemplos de sesiones/cookies/validacion) y en el
enunciado del proyecto (PROYECTO_DSVII_2026).

---

## 1. Descripcion general

Home Dial es una plataforma para que un paciente con dialisis peritoneal
y/o diabetes lleve el registro de su tratamiento desde la casa: cuanto
liquido le infunden y le drenan en cada sesion, y como esta su glucosa.
El sistema calcula solo si el balance es favorable o preocupante, y avisa
si hace falta (retencion de liquidos, glucosa fuera de rango, drenaje
turbio). Tiene 3 roles (administrador, medico, paciente) y cada uno ve
una parte distinta del sistema.

---

## 2. Tecnologias usadas y de donde salieron

| Tecnologia | Para que se uso | De donde salio |
|---|---|---|
| PHP procedural | Toda la logica del servidor | Modulo PHP/MySQL del profesor |
| MySQLi (`mysqli_connect`, `prepare`, `bind_param`) | Conexion y consultas a la base | `dbconn.php`, `registro.php`, `consulta.php`, `update.php` del PDF del profesor |
| Sesiones PHP (`$_SESSION`) | Login y control de acceso por rol | `ej_session.php` del zip de ejercicios |
| `password_hash` / `password_verify` (bcrypt) | Guardar y verificar contraseñas | `ej_hash.php` del zip |
| `htmlspecialchars`, `strip_tags`, `trim`, `filter_var` | Sanitizar y validar formularios | `ej_FrmSanit.php`, `ej_FrmValidacion.php`, `ej_XSS.php`, `registro.php` |
| MySQL (triggers, vista) | Calculo automatico de balance y alertas | Ya venia armado en la base `homedial` (diagrama ER entregado) |
| Chart.js (CDN) | Grafico de barras de la analitica visual | No la dio el profesor, la pide el enunciado del proyecto directamente |
| HTML5 + CSS propio | Interfaz | Sin frameworks (nada de Bootstrap/Tailwind) |

No se uso: PDO, POO (clases), namespaces, Composer, ningun framework de
PHP (Laravel, Symfony, etc), ningun framework de CSS/JS.

---

## 3. Etiquetas y sintaxis de PHP usadas (con ejemplos reales del proyecto)

### 3.1 Apertura y cierre de PHP
Todos los archivos usan la etiqueta larga `<?php ... ?>`, nunca la corta
`<? ?>` ni `<?=`. Ejemplo de `config/database.php`:

```php
<?php
    $host     = "127.0.0.1";
    $usuario  = "root";
    $password = "";
    $database = "homedial";

    $conectar = mysqli_connect($host, $usuario, $password, $database);
?>
```

### 3.2 Mezcla de PHP y HTML
En las paginas visibles se entra y se sale de PHP dentro del mismo
archivo, tal como lo hace el material del profesor (no se separa la
logica de la vista en archivos distintos):

```php
<?php while ($fila = $resultado->fetch_assoc()) { ?>
<tr>
    <td><?php echo mostrar($fila['nombre']); ?></td>
</tr>
<?php } ?>
```

### 3.3 Sesiones
`session_start()` al inicio de cada pagina protegida, `$_SESSION[...]`
para guardar quien esta logueado:

```php
$_SESSION['usuario_id'] = $fila['id'];
$_SESSION['nombre_usuario'] = $fila['nombre_usuario'];
$_SESSION['rol_id'] = $fila['rol_id'];
```

Y para cerrar sesion (`logout.php`), en el mismo orden que enseña el
profesor: primero `session_unset()` (borra las variables), despues
`session_destroy()` (mata la sesion):

```php
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header("Location: index.php");
exit();
```

### 3.4 Conexion y consultas con MySQLi
Conexion procedural (`mysqli_connect`), pero las consultas usan el estilo
orientado a objetos de los statements (`->prepare()`, `->bind_param()`),
que es exactamente como lo muestra el PDF del profesor:

```php
$sql = $conectar->prepare("SELECT id, nombre_usuario, password_hash, rol_id
                            FROM usuarios WHERE nombre_usuario = ?");
$sql->bind_param('s', $usuario);
$sql->execute();
$resultado = $sql->get_result();
$fila = $resultado->fetch_assoc();
$sql->close();
```

Los tipos del `bind_param` son los mismos que enseña el profesor:
`s` = string, `i` = integer, `d` = double.

Para INSERT/UPDATE se usa el mismo patron:

```php
$sql = $conectar->prepare("INSERT INTO usuarios (nombre_usuario, password_hash, rol_id)
                            VALUES (?, ?, 2)");
$sql->bind_param('ss', $usuario_nuevo, $hash);
$sql->execute();
$usuario_id = $sql->insert_id;
$sql->close();
```

Para consultas sin parametros de usuario (por ejemplo, traer la lista de
medicos activos para un `<select>`) se usa la version simple, sin
`prepare`, tal como en los ejemplos basicos:

```php
$medicos = mysqli_query($conectar, "SELECT id, nombre, apellido FROM medicos WHERE activo = 1");
while ($fila = mysqli_fetch_assoc($medicos)) { ... }
```

### 3.5 Contraseñas
```php
$hash = password_hash($clave, PASSWORD_BCRYPT);
...
if (password_verify($clave_ingresada, $hash_guardado)) { ... }
```

### 3.6 Validacion y sanitizacion de formularios
```php
function sanitizar($valor) {
    $valor = trim($valor);
    $valor = strip_tags($valor);
    return $valor;
}

function validar_email($correo) {
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}
```

Y el patron de juntar errores en un arreglo y mostrarlos, calcado del
`registro.php` del profesor:

```php
$errores = array();

if ($nombre == '') { $errores[] = 'El nombre es obligatorio'; }
if ($clave != $clave_confirmar) { $errores[] = 'Las contraseñas no coinciden'; }

if (count($errores) == 0) {
    // recien aca se guarda en la base
}
```

### 3.7 Proteccion contra XSS
Todo lo que sale de la base o de `$_POST`/`$_GET` se imprime pasando por
`htmlspecialchars()` (via la funcion `mostrar()`), tal como en `ej_XSS.php`:

```php
function mostrar($valor) {
    if ($valor === null) { $valor = ''; }
    return htmlspecialchars($valor);
}
```

### 3.8 Redireccion y control de acceso
```php
function verificar_sesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /home_dial/index.php");
        exit();
    }
}

function verificar_rol($roles_permitidos) {
    if (!in_array($_SESSION['rol_id'], $roles_permitidos)) {
        header("Location: /home_dial/modules/dashboard.php");
        exit();
    }
}
```

### 3.9 Estructuras de control usadas
`if / elseif / else`, `while`, `for`, `foreach`, arreglos indexados y
asociativos (`array()`), operador ternario corto (`? :`). No se uso
`switch`, no se uso `match` (PHP 8), no se usaron funciones flecha
(`fn()`), no se uso programacion orientada a objetos propia (solo los
objetos que devuelve mysqli, como `$sql->execute()`).

---

## 4. Base de datos: diccionario de datos completo

Base `homedial`, motor InnoDB, collation `utf8mb4_unicode_ci`.

### roles
| Campo | Tipo | Notas |
|---|---|---|
| id | TINYINT UNSIGNED, PK | 1=admin, 2=paciente, 3=medico |
| nombre | VARCHAR(30) UNICO | |

### usuarios
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| nombre_usuario | VARCHAR(60) UNICO | |
| password_hash | VARCHAR(255) | bcrypt |
| rol_id | TINYINT UNSIGNED, FK -> roles | default 2 |
| activo | TINYINT(1) | default 1 |
| creado_en | DATETIME | automatico |
| ultimo_acceso | DATETIME | se actualiza en cada login |

### medicos
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| usuario_id | INT UNSIGNED, FK -> usuarios | login del medico |
| nombre, apellido | VARCHAR(100) | obligatorios |
| especialidad | VARCHAR(80) | default "Nefrologia" |
| idoneidad | VARCHAR(30) | opcional |
| telefono | VARCHAR(20) | opcional |
| email | VARCHAR(120) | opcional |
| activo | TINYINT(1) | |
| creado_en | DATETIME | |

### pacientes
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| usuario_id | INT UNSIGNED, FK -> usuarios | login del paciente |
| medico_id | INT UNSIGNED, FK -> medicos, NULL | puede no tener medico asignado todavia |
| nombre, apellido | VARCHAR(100) | obligatorios |
| fecha_nacimiento | DATE, NULL | |
| sexo | ENUM('M','F','O'), NULL | |
| cedula | VARCHAR(20) UNICO, NULL | |
| telefono | VARCHAR(20), NULL | |
| direccion | TEXT, NULL | |
| tipo_sistema_dp | ENUM('Baxter','Fresenius Medical Care'), NULL | |
| peso_kg | DECIMAL(5,2), NULL | |
| talla_cm | DECIMAL(5,1), NULL | |
| tipo_sangre | VARCHAR(5), NULL | |
| fecha_inicio_dp | DATE, NULL | |
| activo | TINYINT(1) | |
| creado_en / actualizado_en | DATETIME | |

### sesiones_dialisis
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| paciente_id | INT UNSIGNED, FK -> pacientes | |
| fecha_sesion | DATE | unico por paciente+fecha |
| hora_inicio | TIME | |
| tipo_sistema_dp | ENUM('Baxter','Fresenius Medical Care') | |
| presion_sistol, presion_diast, pulso | SMALLINT UNSIGNED, NULL | opcionales |
| registrado_en | DATETIME | automatico |

### recambios
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| sesion_id | INT UNSIGNED, FK -> sesiones_dialisis | |
| numero_recambio | TINYINT UNSIGNED | 1 a 4 |
| concentracion | ENUM('1.5%','2.5%','7.5%') | |
| infusion_ml | SMALLINT UNSIGNED | siempre 2000 |
| drenaje_ml | SMALLINT UNSIGNED | lo carga el paciente |
| balance_ml | SMALLINT | **lo calcula un trigger**, infusion - drenaje |
| cualidad | ENUM('Claro','Turbio') | default Claro |

### registros_glucosa
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| paciente_id | INT UNSIGNED, FK -> pacientes | |
| fecha_medicion | DATETIME | |
| glucosa_mgdl | SMALLINT UNSIGNED | |
| momento | ENUM('ayunas','antes_comida','2h_despues') | |
| estado_glucemico | ENUM('Hipoglucemia','Normal','Prediabetes','Hiperglucemia') | **lo calcula PHP**, no un trigger |
| registrado_en | DATETIME | automatico |

### alertas
| Campo | Tipo | Notas |
|---|---|---|
| id | INT UNSIGNED, PK | |
| paciente_id | INT UNSIGNED, FK -> pacientes | |
| sesion_id | INT UNSIGNED, FK -> sesiones_dialisis, NULL | solo si la genero un balance |
| tipo_alerta | ENUM('Retencion_leve','Retencion_severa','Turbidez_peritonitis','Hipoglucemia','Hiperglucemia') | |
| mensaje | TEXT | |
| leida | TINYINT(1) | default 0 |
| generada_en | DATETIME | automatico |

### balance_diario_resumen (VISTA)
Junta `sesiones_dialisis` con `recambios`, agrupa por sesion y devuelve:
`total_infusion`, `total_drenaje`, `balance_final`, `recambios_turbios`
(cuenta cuantos salieron "Turbio"), y `estado_balance` (Favorable /
Retencion_leve / Retencion_severa), calculado con un `CASE` dentro de la
misma vista.

### Triggers (automatizacion dentro de la base, no en PHP)

```sql
CREATE TRIGGER trg_recambio_before_insert BEFORE INSERT ON recambios
FOR EACH ROW
BEGIN
    SET NEW.balance_ml = CAST(NEW.infusion_ml AS SIGNED) - CAST(NEW.drenaje_ml AS SIGNED);
END
```
(Existe tambien `trg_recambio_before_update`, igual pero en UPDATE.) Se le
agrego el `CAST ... AS SIGNED` durante las pruebas porque la resta
original con numeros sin signo se rompia cuando el drenaje era mayor que
la infusion (el caso normal, balance favorable).

```sql
CREATE TRIGGER trg_alerta_balance_after_insert AFTER INSERT ON recambios
FOR EACH ROW
BEGIN
    -- cuando ya se insertaron los 4 recambios de una sesion:
    -- suma los balance_ml, cuenta los "Turbio"
    -- si el balance total > 2000 -> inserta alerta Retencion_severa
    -- si el balance esta entre 1 y 2000 -> inserta alerta Retencion_leve
    -- si hay 2 o mas Turbio -> inserta alerta Turbidez_peritonitis
END
```

Estos triggers ya venian en la base cuando se empezo a programar (no son
parte del PHP), pero es importante explicarlos en la sustentacion porque
son los que realmente calculan el balance y generan la alerta — el PHP
de `recambios/registrar.php` solo lee el resultado (`balance_diario_resumen`
y la tabla `alertas`) para mostrarlo en pantalla y disparar el `alert()`.

---

## 5. Modulo por modulo

### 5.1 `config/database.php`
Un solo archivo con los datos de conexion (host, usuario, password, base).
Se hace `require_once` desde cada pagina que necesita la base. Nunca se
cierra ahi adentro — cada pagina hace `mysqli_close($conectar)` al final,
como en el patron del profesor.

### 5.2 `includes/funciones.php`
Funciones compartidas:
- `sanitizar($valor)` — limpia texto de formularios.
- `mostrar($valor)` — escapa HTML para mostrar en pantalla (protege XSS).
- `mostrar_dato($valor)` — igual que `mostrar()` pero si esta vacio
  muestra "sin datos" en vez de dejarlo en blanco.
- `validar_email($correo)` — valida formato de correo.
- `redirigir($url)` — atajo para `header("Location: ...")`.
- `tiempo_atras($fecha)` — convierte una fecha en "hoy" / "hace X dias".
- `verificar_sesion()` / `verificar_rol($roles)` — control de acceso.
- `obtener_paciente_id($conectar)` / `obtener_medico_id($conectar)` —
  busca la ficha del usuario logueado; si no la encuentra, cierra la
  sesion y manda al login con un mensaje en vez de romper la pagina.

### 5.3 `includes/header.php`, `navbar.php`, `footer.php`
`header.php` pone el `<head>`, el link al CSS, y la barra de arriba con
el nombre de usuario y el boton de salir. `navbar.php` arma el menu segun
`$_SESSION['rol_id']` (admin ve Pacientes/Medicos/Usuarios/Alertas, medico
ve Mis pacientes/Alertas, paciente ve Mi ficha/Registrar balance/Registrar
glucosa/Mi balance/Alertas). `footer.php` cierra la pagina y carga
`main.js` (y Chart.js por CDN en la pagina de analitica).

### 5.4 `index.php` / `modules/auth/login.php` / `logout.php`
`index.php` muestra el formulario de login y el de autoregistro de
paciente (ambos apuntan a `login.php`). `login.php` hace dos cosas segun
el campo oculto `accion`:
- Si `accion=registro`: valida los campos (incluye que las 2 contraseñas
  coincidan y que tenga minimo 6 caracteres), crea el usuario (rol 2) y
  la ficha de paciente juntos.
- Si no: busca el usuario en la base, compara la contraseña con
  `password_verify()`, y si esta bien arma la sesion y manda al
  dashboard.

### 5.5 `modules/dashboard.php`
Un solo archivo con 3 bloques segun el rol (`if/elseif/else` sobre
`$_SESSION['rol_id']`): admin ve tarjetas de conteo + ultimas alertas,
medico ve sus pacientes + alertas de ellos, paciente ve su ultimo balance,
su ultima glucosa, y ahora tambien su medico asignado (con mensaje si
todavia no le asignaron uno).

### 5.6 `modules/pacientes/`
- `listar.php`: tabla con buscador (por nombre/cedula). El admin ve todos,
  el medico ve solo los suyos.
- `registrar.php`: crea el usuario de acceso y la ficha del paciente en un
  mismo paso (2 INSERT, y si el segundo falla se borra el primero para no
  dejar cuentas sueltas).
- `editar.php`: mismo formulario pero con UPDATE.
- `ver.php`: ficha completa + ultimas 5 sesiones, 5 mediciones de glucosa
  y 5 alertas. La usa tanto el admin/medico (con `?id=`) como el propio
  paciente (sin `id`, usa su propia ficha).

### 5.7 `modules/medicos/`
Igual que pacientes pero con datos profesionales. `ver.php` ahora tambien
la puede ver el paciente (para ver a su propio medico), pero sin el link
de editar ni la lista de "pacientes asignados" (eso lo sigue viendo solo
el admin, por privacidad de los demas pacientes de ese medico).

### 5.8 `modules/sesiones/`
`listar.php` (historial, filtrado por paciente si el que mira es medico o
paciente), `ver.php` (detalle con sus recambios), `editar.php` (solo los
datos de cabecera: fecha, hora, presion, pulso, sistema). `registrar.php`
no tiene formulario propio, redirige a `recambios/registrar.php` porque
en este sistema una sesion siempre se crea junto con sus 4 recambios.

### 5.9 `modules/recambios/registrar.php` (el formulario principal)
Solo lo puede usar el paciente (`verificar_rol(array(2))`). Inserta la
sesion, despues los 4 recambios (sin mandar `balance_ml`, eso lo calcula
el trigger), y despues consulta la vista `balance_diario_resumen` y la
tabla `alertas` para armar el mensaje de analisis y el `alert()` de
JavaScript.

### 5.10 `modules/glucosa/`
`registrar.php` calcula el estado con `if/elseif` segun las tablas de
rango del enunciado, guarda el registro, y si da Hipoglucemia o
Hiperglucemia crea una alerta. `listar.php` muestra el historial.

### 5.11 `modules/balance/vista.php`
Consulta directa a la vista `balance_diario_resumen`, sin logica propia.

### 5.12 `modules/alertas/listar.php`
Bandeja de alertas, filtrada segun el rol (admin ve todas, medico ve las
de sus pacientes incluida la de peritonitis, paciente ve las suyas menos
esa). Boton para marcar como leida (`UPDATE alertas SET leida = 1`).

### 5.13 `modules/usuarios/`
Solo administradores. `registrar.php` quedo limitado a crear solo cuentas
de tipo admin (por el bug que se encontro: crear un paciente/medico sin
su ficha rompia el sistema). `editar.php` deja cambiar la contraseña y
activar/desactivar, pero no el rol.

---

## 6. Seguridad, en detalle

1. **Contraseñas**: bcrypt via `password_hash()`, nunca texto plano, ni
   en la base ni en pantalla.
2. **Inyeccion SQL**: sentencias preparadas en el 100% de las consultas
   que reciben datos de un formulario o de la URL.
3. **XSS**: toda salida pasa por `htmlspecialchars()`.
4. **Control de acceso**: cada pagina valida sesion y rol antes de hacer
   nada. Un paciente no puede entrar a `usuarios/listar.php` aunque
   escriba la URL a mano (lo redirige el `verificar_rol()`).
5. **Cierre de sesion robusto**: `session_unset()` + `session_destroy()`
   + se borra tambien la cookie de sesion en el navegador
   (`setcookie(session_name(), '', time() - 3600, '/')`), para que no
   queden sesiones "fantasma".
6. **Validacion de datos**: contraseñas de minimo 6 caracteres con
   confirmacion, campos numericos (peso, talla, drenaje) validados con
   `is_numeric()` / `ctype_digit()` antes de guardar.
7. **Integridad de datos**: no se puede crear una cuenta de paciente o
   medico sin su ficha asociada (si falla el segundo paso, se deshace el
   primero).

---

## 7. Pruebas realizadas durante el desarrollo

- Login y bloqueo de paginas por rol (los 3 roles).
- Balance favorable, retencion leve, retencion severa.
- Caso de 2+ recambios turbios (dispara 2 alertas a la vez).
- Los 5 casos de glucosa (hipo, normal, prediabetes e hiper en ayunas y en
  2h despues).
- Fichas con datos incompletos (nombre solo) — se maneja con "sin datos"
  en vez de romper.
- Intento de crear cuenta con contraseñas que no coinciden o muy cortas —
  rechazado.
- Cuenta sin ficha asociada (forzado a mano para probar) — el sistema
  cierra la sesion y avisa, en vez de mostrar un error de PHP.
- Bucle de redireccion que se genero por una sesion vieja — corregido.

---

## 8. Lo unico que no vino del material del profesor

Chart.js (grafico de la analitica visual), porque el enunciado del
proyecto lo pide de forma explicita en la parte de "Analitica Visual".
Todo lo demas (conexion, sesiones, hash, validacion, sanitizacion, CRUD)
sale de lo que el profesor enseño en el modulo de PHP/MySQL y de los
ejercicios realizados.
