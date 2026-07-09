<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 3));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();

    if ($_SESSION['rol_id'] == 3) {
        $id = obtener_medico_id($conectar);
    } else {
        $id = $_GET['id'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nombre = sanitizar($_POST['nombre']);
        $apellido = sanitizar($_POST['apellido']);
        $especialidad = sanitizar($_POST['especialidad']);
        $idoneidad = sanitizar($_POST['idoneidad']);
        $telefono = sanitizar($_POST['telefono']);
        $email = sanitizar($_POST['email']);

        if ($nombre == '') { $errores[] = 'El nombre es obligatorio'; }
        if ($apellido == '') { $errores[] = 'El apellido es obligatorio'; }
        if ($email != '' && !validar_email($email)) { $errores[] = 'El correo no es valido'; }
        if ($telefono != '' && !validar_telefono($telefono)) { $errores[] = 'El telefono no tiene un formato valido, use solo numeros y guiones (ej: 6600-0000)'; }

        if ($_SESSION['rol_id'] == 1) {
            $activo = isset($_POST['activo']) ? 1 : 0;
        }

        if (count($errores) == 0) {

            if ($_SESSION['rol_id'] == 1) {
                $consulta = $conectar->prepare("UPDATE medicos SET nombre=?, apellido=?, especialidad=?, idoneidad=?, telefono=?, email=?, activo=? WHERE id=?");
                $consulta->bind_param('ssssssii', $nombre, $apellido, $especialidad, $idoneidad, $telefono, $email, $activo, $id);
            } else {
                $consulta = $conectar->prepare("UPDATE medicos SET nombre=?, apellido=?, especialidad=?, idoneidad=?, telefono=?, email=? WHERE id=?");
                $consulta->bind_param('ssssssi', $nombre, $apellido, $especialidad, $idoneidad, $telefono, $email, $id);
            }

            if ($consulta->execute()) {
                $consulta->close();
                mysqli_close($conectar);
                if ($_SESSION['rol_id'] == 1) {
                    header("Location: ver.php?id=" . $id);
                } else {
                    header("Location: ver.php");
                }
                exit();
            } else {
                $errores[] = 'Error al actualizar el medico';
            }
            $consulta->close();
        }
    }

    $consulta = $conectar->prepare("SELECT * FROM medicos WHERE id = ?");
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $medico = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($errores) > 0) {
        $medico['nombre'] = $nombre;
        $medico['apellido'] = $apellido;
        $medico['especialidad'] = $especialidad;
        $medico['idoneidad'] = $idoneidad;
        $medico['telefono'] = $telefono;
        $medico['email'] = $email;
        if ($_SESSION['rol_id'] == 1) {
            $medico['activo'] = $activo;
        }
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Editar medico</h2>

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
                    <input type="text" name="nombre" value="<?php echo mostrar($medico['nombre']); ?>">
                </div>
                <div>
                    <label>Apellido <span class="obligatorio">*</span></label>
                    <input type="text" name="apellido" value="<?php echo mostrar($medico['apellido']); ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Especialidad</label>
                    <input type="text" name="especialidad" value="<?php echo mostrar($medico['especialidad']); ?>">
                </div>
                <div>
                    <label>Idoneidad</label>
                    <input type="text" name="idoneidad" value="<?php echo mostrar($medico['idoneidad']); ?>" placeholder="numero de idoneidad">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Telefono</label>
                    <input type="text" name="telefono" value="<?php echo mostrar($medico['telefono']); ?>" placeholder="6600-0000">
                </div>
                <div>
                    <label>Email</label>
                    <input type="text" name="email" value="<?php echo mostrar($medico['email']); ?>" placeholder="correo@ejemplo.com">
                </div>
            </div>

            <?php if ($_SESSION['rol_id'] == 1) { ?>
            <label>
                <input type="checkbox" name="activo" style="width:auto;" <?php if ($medico['activo'] == 1) echo 'checked'; ?>>
                Activo
            </label>
            <?php } ?>

            <div class="botones">
                <button type="submit" class="boton">Guardar</button>
                <?php if ($_SESSION['rol_id'] == 1) { ?>
                    <a class="boton secundario" href="ver.php?id=<?php echo $id; ?>">Cancelar</a>
                <?php } else { ?>
                    <a class="boton secundario" href="ver.php">Cancelar</a>
                <?php } ?>
            </div>

        </form>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
