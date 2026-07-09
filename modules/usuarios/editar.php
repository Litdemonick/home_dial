<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();
    $id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $activo = isset($_POST['activo']) ? 1 : 0;
        $clave = trim($_POST['clave']);
        $clave_confirmar = trim($_POST['clave_confirmar']);

        if ($clave != '' && strlen($clave) < 6) {
            $errores[] = 'La contraseña debe tener minimo 6 caracteres';
        }

        if ($clave != '' && $clave != $clave_confirmar) {
            $errores[] = 'Las contraseñas no coinciden';
        }

        if (count($errores) == 0) {

            if ($clave != '') {
                $hash = password_hash($clave, PASSWORD_BCRYPT);
                $sql = $conectar->prepare("UPDATE usuarios SET activo=?, password_hash=? WHERE id=?");
                $sql->bind_param('isi', $activo, $hash, $id);
            } else {
                $sql = $conectar->prepare("UPDATE usuarios SET activo=? WHERE id=?");
                $sql->bind_param('ii', $activo, $id);
            }

            if ($sql->execute()) {
                $sql->close();
                mysqli_close($conectar);
                header("Location: listar.php");
                exit();
            } else {
                $errores[] = 'Error al actualizar el usuario';
            }
            $sql->close();
        }
    }

    $sql = $conectar->prepare("SELECT * FROM usuarios WHERE id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $usuario = $sql->get_result()->fetch_assoc();
    $sql->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($errores) > 0) {
        $usuario['activo'] = $activo;
    }

    if ($usuario['rol_id'] == 1) {
        $rol_nombre = 'admin';
    } elseif ($usuario['rol_id'] == 2) {
        $rol_nombre = 'paciente';
    } else {
        $rol_nombre = 'medico';
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Editar usuario: <?php echo mostrar($usuario['nombre_usuario']); ?></h2>

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

            <label>Rol</label>
            <input type="text" value="<?php echo $rol_nombre; ?>" readonly>
            <p style="font-size:12px; color:#666; margin-top:3px;">El rol no se puede cambiar aca porque paciente y medico tienen una ficha ligada al usuario. Si se equivocaron de rol, eliminen la cuenta y creenla de nuevo desde el modulo correcto.</p>

            <label>Nueva contraseña (dejar en blanco para no cambiarla)</label>
            <input type="password" name="clave" placeholder="minimo 6 caracteres" minlength="6">

            <label>Confirmar nueva contraseña</label>
            <input type="password" name="clave_confirmar" placeholder="repita la contraseña" minlength="6">

            <label>
                <input type="checkbox" name="activo" style="width:auto;" <?php if ($usuario['activo'] == 1) echo 'checked'; ?>>
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
