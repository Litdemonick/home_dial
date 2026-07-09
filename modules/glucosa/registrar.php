<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(2));
    require_once __DIR__ . '/../../config/database.php';

    $consulta = $conectar->prepare("SELECT id, nombre, apellido FROM pacientes WHERE usuario_id = ?");
    $consulta->bind_param('i', $_SESSION['usuario_id']);
    $consulta->execute();
    $paciente = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if (!$paciente) {
        mysqli_close($conectar);
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: /home_dial/index.php?error=Su cuenta no tiene una ficha de paciente asociada. Contacte al administrador.");
        exit();
    }

    verificar_ficha_completa($conectar, $paciente['id']);

    $paciente_id = $paciente['id'];

    $errores = array();
    $mensaje_ok = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $fecha_medicion = $_POST['fecha_medicion'];
        $glucosa_mgdl = $_POST['glucosa_mgdl'];
        $momento = $_POST['momento'];

        if ($fecha_medicion == '') { $errores[] = 'La fecha es obligatoria'; }
        if ($glucosa_mgdl == '') { $errores[] = 'La glucosa es obligatoria'; }
        if ($glucosa_mgdl != '' && !ctype_digit($glucosa_mgdl)) { $errores[] = 'La glucosa debe ser un numero'; }

        if (count($errores) == 0) {

            $glucosa_mgdl = intval($glucosa_mgdl);

            if ($momento == '2h_despues') {
                if ($glucosa_mgdl < 70) {
                    $estado = 'Hipoglucemia';
                } elseif ($glucosa_mgdl <= 139) {
                    $estado = 'Normal';
                } elseif ($glucosa_mgdl <= 199) {
                    $estado = 'Prediabetes';
                } else {
                    $estado = 'Hiperglucemia';
                }
            } else {
                if ($glucosa_mgdl < 70) {
                    $estado = 'Hipoglucemia';
                } elseif ($glucosa_mgdl <= 99) {
                    $estado = 'Normal';
                } elseif ($glucosa_mgdl <= 125) {
                    $estado = 'Prediabetes';
                } else {
                    $estado = 'Hiperglucemia';
                }
            }

            $consulta = $conectar->prepare("INSERT INTO registros_glucosa (paciente_id, fecha_medicion, glucosa_mgdl, momento, estado_glucemico)
                                        VALUES (?, ?, ?, ?, ?)");
            $consulta->bind_param('isiss', $paciente_id, $fecha_medicion, $glucosa_mgdl, $momento, $estado);
            $consulta->execute();
            $consulta->close();

            if ($estado == 'Hipoglucemia' || $estado == 'Hiperglucemia') {
                $mensaje_alerta = 'Glucosa ' . $glucosa_mgdl . ' mg/dL (' . $momento . ') - ' . $estado;
                $consulta = $conectar->prepare("INSERT INTO alertas (paciente_id, tipo_alerta, mensaje) VALUES (?, ?, ?)");
                $consulta->bind_param('iss', $paciente_id, $estado, $mensaje_alerta);
                $consulta->execute();
                $consulta->close();

                echo '<script>alert("ALERTA: su glucosa registrada esta en ' . addslashes($estado) . '.");</script>';
            }

            $mensaje_ok = 'Medicion guardada correctamente. Estado: ' . $estado;
        }
    }

    $fecha_hoy = date('Y-m-d\TH:i');

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $fecha_medicion = $fecha_hoy;
        $glucosa_mgdl = '';
        $momento = 'ayunas';
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div style="display:flex; gap:20px; flex-wrap:wrap;">

        <div class="panel" style="flex:1; min-width:280px;">
            <h2>Registrar medicion de glucosa</h2>

            <?php if (count($errores) > 0) { ?>
                <div class="aviso excesiva">
                    <ul>
                    <?php foreach ($errores as $error) { ?>
                        <li><?php echo mostrar($error); ?></li>
                    <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($mensaje_ok != '') { ?>
                <div class="aviso favorable"><?php echo mostrar($mensaje_ok); ?></div>
            <?php } ?>

            <form class="formulario" method="POST">
                <label>Paciente</label>
                <input type="text" value="<?php echo mostrar($paciente['nombre'] . ' ' . $paciente['apellido']); ?>" readonly>

                <label>Fecha y hora</label>
                <input type="datetime-local" name="fecha_medicion" value="<?php echo mostrar($fecha_medicion); ?>">

                <label>Glucosa (mg/dL) <span class="obligatorio">*</span></label>
                <input type="text" name="glucosa_mgdl" value="<?php echo mostrar($glucosa_mgdl); ?>">

                <label>Momento <span class="obligatorio">*</span></label>
                <select name="momento">
                    <option value="ayunas" <?php echo ($momento == 'ayunas') ? 'selected' : ''; ?>>Ayunas</option>
                    <option value="antes_comida" <?php echo ($momento == 'antes_comida') ? 'selected' : ''; ?>>Antes de comida</option>
                    <option value="2h_despues" <?php echo ($momento == '2h_despues') ? 'selected' : ''; ?>>2h despues de comida</option>
                </select>

                <div class="botones">
                    <button type="submit" class="boton">Guardar</button>
                </div>
            </form>
        </div>

        <div class="panel" style="flex:1; min-width:280px;">
            <h3>Tabla de referencia (diagnostico automatico)</h3>
            <table class="tabla">
                <tr><th>Momento</th><th>Hipoglucemia</th><th>Normal</th><th>Prediabetes</th><th>Hiperglucemia</th></tr>
                <tr><td>Ayunas</td><td>&lt; 70</td><td>70-99</td><td>100-125</td><td>&gt;= 126</td></tr>
                <tr><td>Antes de comida</td><td>&lt; 70</td><td>70-99</td><td>100-125</td><td>&gt;= 126</td></tr>
                <tr><td>2h despues</td><td>&lt; 70</td><td>70-139</td><td>140-199</td><td>&gt;= 200</td></tr>
            </table>
        </div>

    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
