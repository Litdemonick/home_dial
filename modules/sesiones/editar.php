<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 2));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();
    $id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $fecha_sesion = $_POST['fecha_sesion'];
        $hora_inicio = $_POST['hora_inicio'];
        $tipo_sistema_dp = $_POST['tipo_sistema_dp'];
        $presion_sistol = trim($_POST['presion_sistol']);
        $presion_diast = trim($_POST['presion_diast']);
        $pulso = trim($_POST['pulso']);

        if ($fecha_sesion == '') { $errores[] = 'La fecha es obligatoria'; }
        if ($presion_sistol != '' && !ctype_digit($presion_sistol)) { $errores[] = 'La presion sistolica debe ser un numero'; }
        if ($presion_diast != '' && !ctype_digit($presion_diast)) { $errores[] = 'La presion diastolica debe ser un numero'; }
        if ($pulso != '' && !ctype_digit($pulso)) { $errores[] = 'El pulso debe ser un numero'; }

        $presion_sistol = $presion_sistol == '' ? null : $presion_sistol;
        $presion_diast = $presion_diast == '' ? null : $presion_diast;
        $pulso = $pulso == '' ? null : $pulso;

        if (count($errores) == 0) {
            $sql = $conectar->prepare("UPDATE sesiones_dialisis SET fecha_sesion=?, hora_inicio=?, tipo_sistema_dp=?, presion_sistol=?, presion_diast=?, pulso=? WHERE id=?");
            $sql->bind_param('sssiiii', $fecha_sesion, $hora_inicio, $tipo_sistema_dp, $presion_sistol, $presion_diast, $pulso, $id);

            if ($sql->execute()) {
                $sql->close();
                mysqli_close($conectar);
                header("Location: ver.php?id=" . $id);
                exit();
            } else {
                $errores[] = 'Error al actualizar la sesion';
            }
            $sql->close();
        }
    }

    $sql = $conectar->prepare("SELECT * FROM sesiones_dialisis WHERE id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $sesion = $sql->get_result()->fetch_assoc();
    $sql->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($errores) > 0) {
        $sesion['fecha_sesion'] = $fecha_sesion;
        $sesion['hora_inicio'] = $hora_inicio;
        $sesion['tipo_sistema_dp'] = $tipo_sistema_dp;
        $sesion['presion_sistol'] = $presion_sistol;
        $sesion['presion_diast'] = $presion_diast;
        $sesion['pulso'] = $pulso;
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Editar sesion</h2>

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
                    <label>Fecha <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_sesion" value="<?php echo mostrar($sesion['fecha_sesion']); ?>">
                </div>
                <div>
                    <label>Hora</label>
                    <input type="time" name="hora_inicio" value="<?php echo mostrar($sesion['hora_inicio']); ?>">
                </div>
                <div>
                    <label>Sistema DP</label>
                    <select name="tipo_sistema_dp">
                        <option value="Baxter" <?php if ($sesion['tipo_sistema_dp'] == 'Baxter') echo 'selected'; ?>>Baxter</option>
                        <option value="Fresenius Medical Care" <?php if ($sesion['tipo_sistema_dp'] == 'Fresenius Medical Care') echo 'selected'; ?>>Fresenius Medical Care</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Presion sistolica</label>
                    <input type="text" name="presion_sistol" value="<?php echo mostrar($sesion['presion_sistol']); ?>">
                </div>
                <div>
                    <label>Presion diastolica</label>
                    <input type="text" name="presion_diast" value="<?php echo mostrar($sesion['presion_diast']); ?>">
                </div>
                <div>
                    <label>Pulso</label>
                    <input type="text" name="pulso" value="<?php echo mostrar($sesion['pulso']); ?>">
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="boton">Guardar</button>
                <a class="boton secundario" href="ver.php?id=<?php echo $id; ?>">Cancelar</a>
            </div>

        </form>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
