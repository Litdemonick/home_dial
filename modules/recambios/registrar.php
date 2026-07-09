<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(2));
    require_once __DIR__ . '/../../config/database.php';

    $sql = $conectar->prepare("SELECT id, nombre, apellido FROM pacientes WHERE usuario_id = ?");
    $sql->bind_param('i', $_SESSION['usuario_id']);
    $sql->execute();
    $paciente = $sql->get_result()->fetch_assoc();
    $sql->close();

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
    $mensaje_analisis = '';
    $clase_aviso = '';
    $mostrar_analisis = false;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $fecha_sesion = $_POST['fecha_sesion'];
        $hora_inicio = $_POST['hora_inicio'];
        $tipo_sistema_dp = $_POST['tipo_sistema_dp'];
        $presion_sistol = trim($_POST['presion_sistol']);
        $presion_diast = trim($_POST['presion_diast']);
        $pulso = trim($_POST['pulso']);

        if ($fecha_sesion == '') { $errores[] = 'La fecha es obligatoria'; }
        if ($tipo_sistema_dp == '') { $errores[] = 'El sistema DP es obligatorio'; }
        if ($presion_sistol != '' && !ctype_digit($presion_sistol)) { $errores[] = 'La presion sistolica debe ser un numero'; }
        if ($presion_diast != '' && !ctype_digit($presion_diast)) { $errores[] = 'La presion diastolica debe ser un numero'; }
        if ($pulso != '' && !ctype_digit($pulso)) { $errores[] = 'El pulso debe ser un numero'; }

        $presion_sistol = $presion_sistol == '' ? null : $presion_sistol;
        $presion_diast = $presion_diast == '' ? null : $presion_diast;
        $pulso = $pulso == '' ? null : $pulso;

        $concentraciones = $_POST['concentracion'];
        $drenajes = $_POST['drenaje'];
        $cualidades = $_POST['cualidad'];

        for ($i = 0; $i < 4; $i++) {
            if (!ctype_digit($drenajes[$i])) {
                $errores[] = 'El drenaje del recambio ' . ($i + 1) . ' debe ser numerico';
            }
        }

        if (count($errores) == 0) {
            $sql = $conectar->prepare("SELECT id FROM sesiones_dialisis WHERE paciente_id = ? AND fecha_sesion = ?");
            $sql->bind_param('is', $paciente_id, $fecha_sesion);
            $sql->execute();
            if ($sql->get_result()->num_rows > 0) {
                $errores[] = 'Ya existe un registro de balance para ese dia';
            }
            $sql->close();
        }

        if (count($errores) == 0) {

            $sql = $conectar->prepare("INSERT INTO sesiones_dialisis (paciente_id, fecha_sesion, hora_inicio, tipo_sistema_dp, presion_sistol, presion_diast, pulso)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param('isssiii', $paciente_id, $fecha_sesion, $hora_inicio, $tipo_sistema_dp, $presion_sistol, $presion_diast, $pulso);
            $sql->execute();
            $sesion_id = $sql->insert_id;
            $sql->close();

            for ($i = 0; $i < 4; $i++) {
                $infusion = 2000;
                $drenaje = intval($drenajes[$i]);
                $concentracion = $concentraciones[$i];
                $cualidad = $cualidades[$i];
                $numero = $i + 1;

                $sql = $conectar->prepare("INSERT INTO recambios (sesion_id, numero_recambio, concentracion, infusion_ml, drenaje_ml, cualidad)
                                            VALUES (?, ?, ?, ?, ?, ?)");
                $sql->bind_param('iisiis', $sesion_id, $numero, $concentracion, $infusion, $drenaje, $cualidad);
                $sql->execute();
                $sql->close();
            }

            $mostrar_analisis = true;

            $sql = $conectar->prepare("SELECT balance_final, estado_balance FROM balance_diario_resumen WHERE sesion_id = ?");
            $sql->bind_param('i', $sesion_id);
            $sql->execute();
            $resumen = $sql->get_result()->fetch_assoc();
            $sql->close();

            $balance_final = $resumen['balance_final'];

            if ($resumen['estado_balance'] == 'Favorable') {
                $mensaje_analisis = 'Balance Hidrico Favorable. Total balance del dia: ' . $balance_final . ' ml.';
                $clase_aviso = 'favorable';
            } elseif ($resumen['estado_balance'] == 'Retencion_leve') {
                $mensaje_analisis = 'Retencion de liquidos considerable. Balance Final del dia = ' . $balance_final . ' ml.';
                $clase_aviso = 'retencion';
            } else {
                $mensaje_analisis = 'ALERTA: Excesiva retencion de liquidos. Balance Final del dia = ' . $balance_final . ' ml.';
                $clase_aviso = 'excesiva';
            }

            $sql = $conectar->prepare("SELECT id FROM alertas WHERE sesion_id = ? AND tipo_alerta = 'Turbidez_peritonitis'");
            $sql->bind_param('i', $sesion_id);
            $sql->execute();
            if ($sql->get_result()->num_rows > 0) {
                $mensaje_analisis = $mensaje_analisis . '<br>ALERTA: Consulte de inmediato con su medico, se detecto drenaje turbio.';
                $clase_aviso = 'medico';
            }
            $sql->close();
        }
    }

    $fecha_hoy = date('Y-m-d');

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $fecha_sesion = $fecha_hoy;
        $hora_inicio = '';
        $tipo_sistema_dp = '';
        $presion_sistol = '';
        $presion_diast = '';
        $pulso = '';
        $concentraciones = array('1.5%', '1.5%', '1.5%', '1.5%');
        $drenajes = array('', '', '', '');
        $cualidades = array('Claro', 'Claro', 'Claro', 'Claro');
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Registro de Balance Hidrico</h2>

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
                    <label>Paciente <span class="obligatorio">*</span></label>
                    <input type="text" value="<?php echo mostrar($paciente['nombre'] . ' ' . $paciente['apellido']); ?>" readonly>
                </div>
                <div>
                    <label>Fecha <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_sesion" value="<?php echo mostrar($fecha_sesion); ?>">
                </div>
                <div>
                    <label>Sistema DP <span class="obligatorio">*</span></label>
                    <select name="tipo_sistema_dp">
                        <option value="">--</option>
                        <option value="Baxter" <?php echo ($tipo_sistema_dp == 'Baxter') ? 'selected' : ''; ?>>Baxter</option>
                        <option value="Fresenius Medical Care" <?php echo ($tipo_sistema_dp == 'Fresenius Medical Care') ? 'selected' : ''; ?>>Fresenius Medical Care</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Presion sistolica</label>
                    <input type="text" name="presion_sistol" value="<?php echo mostrar($presion_sistol); ?>">
                </div>
                <div>
                    <label>Presion diastolica</label>
                    <input type="text" name="presion_diast" value="<?php echo mostrar($presion_diast); ?>">
                </div>
                <div>
                    <label>Pulso</label>
                    <input type="text" name="pulso" value="<?php echo mostrar($pulso); ?>">
                </div>
                <div>
                    <label>Hora de inicio</label>
                    <input type="time" name="hora_inicio" value="<?php echo mostrar($hora_inicio); ?>">
                </div>
            </div>

            <h3 style="margin-top:20px;">Recambios</h3>
            <table class="tabla">
                <tr>
                    <th>#</th>
                    <th>Concentracion</th>
                    <th>Infusion (ml)</th>
                    <th>Drenaje (ml)</th>
                    <th>Balance (ml)</th>
                    <th>Cualidad</th>
                </tr>
                <?php for ($i = 1; $i <= 4; $i++) { ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td>
                        <select name="concentracion[]">
                            <option value="1.5%" <?php echo ($concentraciones[$i - 1] == '1.5%') ? 'selected' : ''; ?>>1.5%</option>
                            <option value="2.5%" <?php echo ($concentraciones[$i - 1] == '2.5%') ? 'selected' : ''; ?>>2.5%</option>
                            <option value="7.5%" <?php echo ($concentraciones[$i - 1] == '7.5%') ? 'selected' : ''; ?>>7.5%</option>
                        </select>
                    </td>
                    <td>2000</td>
                    <td><input type="text" name="drenaje[]" id="drenaje<?php echo $i; ?>" onkeyup="calcularRecambios()" value="<?php echo mostrar($drenajes[$i - 1]); ?>"></td>
                    <td id="balance<?php echo $i; ?>">0</td>
                    <td>
                        <select name="cualidad[]">
                            <option value="Claro" <?php echo ($cualidades[$i - 1] == 'Claro') ? 'selected' : ''; ?>>Claro</option>
                            <option value="Turbio" <?php echo ($cualidades[$i - 1] == 'Turbio') ? 'selected' : ''; ?>>Turbio</option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
                <tr style="font-weight:bold; background-color:#dfe9ec;">
                    <td colspan="2">TOTAL</td>
                    <td id="totalInfusion">8000</td>
                    <td id="totalDrenaje">0</td>
                    <td id="balanceFinal">0</td>
                    <td></td>
                </tr>
            </table>

            <div class="botones">
                <button type="button" class="boton" onclick="calcularRecambios()">Calcular</button>
                <button type="submit" class="boton secundario">Guardar registro</button>
            </div>

        </form>

        <?php if ($mostrar_analisis) { ?>
        <div class="panel" style="margin-top:20px; background-color:#f7fafb;">
            <h3>Analisis de resultados para el paciente: <?php echo mostrar($paciente['nombre'] . ' ' . $paciente['apellido']); ?></h3>
            <div class="aviso <?php echo $clase_aviso; ?>">
                <?php echo $mensaje_analisis; ?>
            </div>
        </div>
        <script>
            alert("<?php echo addslashes(strip_tags(str_replace('<br>', ' ', $mensaje_analisis))); ?>");
        </script>
        <?php } ?>

    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
