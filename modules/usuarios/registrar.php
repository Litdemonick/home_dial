<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nombre_usuario = sanitizar($_POST['nombre_usuario']);
        $clave = trim($_POST['clave']);
        $clave_confirmar = trim($_POST['clave_confirmar']);
        $rol_id = 1;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($nombre_usuario == '') { $errores[] = 'El nombre de usuario es obligatorio'; }
        if ($clave == '') { $errores[] = 'La contraseña es obligatoria'; }
        if (strlen($clave) > 0 && strlen($clave) < 6) { $errores[] = 'La contraseña debe tener minimo 6 caracteres'; }
        if ($clave != $clave_confirmar) { $errores[] = 'Las contraseñas no coinciden'; }

        if (count($errores) == 0) {
            $sql = $conectar->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
            $sql->bind_param('s', $nombre_usuario);
            $sql->execute();
            if ($sql->get_result()->num_rows > 0) {
                $errores[] = 'Ese usuario ya existe';
            }
            $sql->close();
        }

        if (count($errores) == 0) {

            $hash = password_hash($clave, PASSWORD_BCRYPT);

            $sql = $conectar->prepare("INSERT INTO usuarios (nombre_usuario, password_hash, rol_id, activo) VALUES (?, ?, ?, ?)");
            $sql->bind_param('ssii', $nombre_usuario, $hash, $rol_id, $activo);

            if ($sql->execute()) {
                $sql->close();
                mysqli_close($conectar);
                header("Location: listar.php");
                exit();
            } else {
                $errores[] = 'Error al guardar el usuario';
            }
            $sql->close();
        }
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Registrar usuario administrador</h2>
        <p style="font-size:13px; color:#555; margin-bottom:10px;">
            Esta pantalla solo crea cuentas de administrador. Para pacientes usar
            <a href="../pacientes/registrar.php">Pacientes &gt; Nuevo paciente</a>, y para medicos usar
            <a href="../medicos/registrar.php">Medicos &gt; Nuevo medico</a> (ahi se crea el login junto con la ficha).
        </p>

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

            <label>Nombre de usuario <span class="obligatorio">*</span></label>
            <input type="text" name="nombre_usuario" placeholder="usuario para iniciar sesion" value="<?php echo isset($_POST['nombre_usuario']) ? mostrar($_POST['nombre_usuario']) : ''; ?>">

            <label>Contraseña <span class="obligatorio">*</span></label>
            <input type="password" name="clave" placeholder="minimo 6 caracteres" minlength="6">

            <label>Confirmar contraseña <span class="obligatorio">*</span></label>
            <input type="password" name="clave_confirmar" placeholder="repita la contraseña" minlength="6">

            <label>
                <input type="checkbox" name="activo" style="width:auto;" <?php echo (!isset($_POST['nombre_usuario']) || isset($_POST['activo'])) ? 'checked' : ''; ?>>
                Activo
            </label>

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
