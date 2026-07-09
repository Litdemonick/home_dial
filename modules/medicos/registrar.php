<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nombre = sanitizar($_POST['nombre']);
        $apellido = sanitizar($_POST['apellido']);
        $especialidad = sanitizar($_POST['especialidad']);
        $idoneidad = sanitizar($_POST['idoneidad']);
        $telefono = sanitizar($_POST['telefono']);
        $email = sanitizar($_POST['email']);
        $usuario_login = sanitizar($_POST['usuario_login']);
        $clave_login = trim($_POST['clave_login']);
        $clave_confirmar = trim($_POST['clave_confirmar']);

        if ($nombre == '') { $errores[] = 'El nombre es obligatorio'; }
        if ($apellido == '') { $errores[] = 'El apellido es obligatorio'; }
        if ($especialidad == '') { $errores[] = 'La especialidad es obligatoria'; }
        if ($idoneidad == '') { $errores[] = 'La idoneidad es obligatoria'; }
        if ($telefono == '') { $errores[] = 'El telefono es obligatorio'; }
        if ($email == '') { $errores[] = 'El email es obligatorio'; }
        if ($usuario_login == '') { $errores[] = 'El usuario de acceso es obligatorio'; }
        if ($clave_login == '') { $errores[] = 'La contraseña de acceso es obligatoria'; }
        if (strlen($clave_login) > 0 && strlen($clave_login) < 6) { $errores[] = 'La contraseña debe tener minimo 6 caracteres'; }
        if ($clave_login != $clave_confirmar) { $errores[] = 'Las contraseñas no coinciden'; }
        if ($email != '' && !validar_email($email)) { $errores[] = 'El correo no es valido'; }
        if ($telefono != '' && !validar_telefono($telefono)) { $errores[] = 'El telefono no tiene un formato valido, use solo numeros y guiones (ej: 6600-0000)'; }

        if (count($errores) == 0) {
            $consulta = $conectar->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
            $consulta->bind_param('s', $usuario_login);
            $consulta->execute();
            if ($consulta->get_result()->num_rows > 0) {
                $errores[] = 'Ese usuario de acceso ya existe';
            }
            $consulta->close();
        }

        if (count($errores) == 0) {

            $hash = password_hash($clave_login, PASSWORD_BCRYPT);

            $consulta = $conectar->prepare("INSERT INTO usuarios (nombre_usuario, password_hash, rol_id) VALUES (?, ?, 3)");
            $consulta->bind_param('ss', $usuario_login, $hash);
            $consulta->execute();
            $usuario_id = $consulta->insert_id;
            $consulta->close();

            $consulta = $conectar->prepare("INSERT INTO medicos (usuario_id, nombre, apellido, especialidad, idoneidad, telefono, email)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            $consulta->bind_param('issssss', $usuario_id, $nombre, $apellido, $especialidad, $idoneidad, $telefono, $email);

            if ($consulta->execute()) {
                $consulta->close();
                mysqli_close($conectar);
                header("Location: listar.php");
                exit();
            } else {
                $errores[] = 'Error al guardar el medico';
            }
            $consulta->close();
        }
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Registrar medico</h2>

        <?php if (count($errores) > 0) { ?>
            <div class="aviso excesiva">
                <ul>
                <?php foreach ($errores as $error) { ?>
                    <li><?php echo mostrar($error); ?></li>
                <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form class="formulario" method="POST">

            <div class="fila">
                <div>
                    <label>Nombre <span class="obligatorio">*</span></label>
                    <input type="text" name="nombre" placeholder="nombre del medico" value="<?php echo isset($_POST['nombre']) ? mostrar($_POST['nombre']) : ''; ?>">
                </div>
                <div>
                    <label>Apellido <span class="obligatorio">*</span></label>
                    <input type="text" name="apellido" placeholder="apellido del medico" value="<?php echo isset($_POST['apellido']) ? mostrar($_POST['apellido']) : ''; ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Especialidad <span class="obligatorio">*</span></label>
                    <input type="text" name="especialidad" value="<?php echo isset($_POST['especialidad']) ? mostrar($_POST['especialidad']) : 'Nefrologia'; ?>">
                </div>
                <div>
                    <label>Idoneidad <span class="obligatorio">*</span></label>
                    <input type="text" name="idoneidad" placeholder="numero de idoneidad" value="<?php echo isset($_POST['idoneidad']) ? mostrar($_POST['idoneidad']) : ''; ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Telefono <span class="obligatorio">*</span></label>
                    <input type="text" name="telefono" placeholder="6600-0000" value="<?php echo isset($_POST['telefono']) ? mostrar($_POST['telefono']) : ''; ?>">
                </div>
                <div>
                    <label>Email <span class="obligatorio">*</span></label>
                    <input type="text" name="email" placeholder="correo@ejemplo.com" value="<?php echo isset($_POST['email']) ? mostrar($_POST['email']) : ''; ?>">
                </div>
            </div>

            <h3 style="margin-top:15px;">Acceso al sistema</h3>
            <div class="fila">
                <div>
                    <label>Usuario <span class="obligatorio">*</span></label>
                    <input type="text" name="usuario_login" placeholder="usuario para iniciar sesion" value="<?php echo isset($_POST['usuario_login']) ? mostrar($_POST['usuario_login']) : ''; ?>">
                </div>
                <div>
                    <label>Contraseña <span class="obligatorio">*</span></label>
                    <input type="password" name="clave_login" placeholder="minimo 6 caracteres" minlength="6">
                </div>
                <div>
                    <label>Confirmar contraseña <span class="obligatorio">*</span></label>
                    <input type="password" name="clave_confirmar" placeholder="repita la contraseña" minlength="6">
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="boton">Guardar</button>
                <a class="boton secundario" href="listar.php">Cancelar</a>
            </div>

        </form>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
