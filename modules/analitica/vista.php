<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../../config/database.php';

    if ($_SESSION['rol_id'] == 2) {
        $paciente_id = obtener_paciente_id($conectar);
    } else {
        $paciente_id = isset($_GET['paciente']) ? $_GET['paciente'] : '';
    }

    $sql = $conectar->prepare("SELECT id, fecha_sesion FROM sesiones_dialisis WHERE paciente_id = ? ORDER BY fecha_sesion DESC");
    $sql->bind_param('i', $paciente_id);
    $sql->execute();
    $sesiones = $sql->get_result();
    $sql->close();

    $sesion_id = isset($_GET['sesion']) ? $_GET['sesion'] : '';
    $recambios = array();

    if ($sesion_id != '') {
        $sql = $conectar->prepare("SELECT numero_recambio, infusion_ml, drenaje_ml, balance_ml FROM recambios WHERE sesion_id = ? ORDER BY numero_recambio");
        $sql->bind_param('i', $sesion_id);
        $sql->execute();
        $resultado = $sql->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $recambios[] = $fila;
        }
        $sql->close();
    }

    if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) {
        if ($_SESSION['rol_id'] == 3) {
            $medico_id = obtener_medico_id($conectar);
            $pacientes = $conectar->prepare("SELECT id, nombre, apellido FROM pacientes WHERE medico_id = ? ORDER BY nombre");
            $pacientes->bind_param('i', $medico_id);
            $pacientes->execute();
            $pacientes = $pacientes->get_result();
        } else {
            $pacientes = mysqli_query($conectar, "SELECT id, nombre, apellido FROM pacientes ORDER BY nombre");
        }
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Analitica visual</h2>

        <form class="formulario" method="GET">

            <?php if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) { ?>
            <label>Paciente</label>
            <select name="paciente" onchange="this.form.submit()">
                <option value="">-- elegir paciente --</option>
                <?php while ($fila = mysqli_fetch_assoc($pacientes)) { ?>
                    <option value="<?php echo $fila['id']; ?>" <?php if ($fila['id'] == $paciente_id) echo 'selected'; ?>>
                        <?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?>
                    </option>
                <?php } ?>
            </select>
            <?php } ?>

            <?php if ($paciente_id != '') { ?>
            <label>Sesion (por fecha)</label>
            <select name="sesion" onchange="this.form.submit()">
                <option value="">-- elegir fecha --</option>
                <?php while ($fila = $sesiones->fetch_assoc()) { ?>
                    <option value="<?php echo $fila['id']; ?>" <?php if ($fila['id'] == $sesion_id) echo 'selected'; ?>>
                        <?php echo mostrar($fila['fecha_sesion']); ?>
                    </option>
                <?php } ?>
            </select>
            <?php if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) { ?>
                <input type="hidden" name="paciente" value="<?php echo $paciente_id; ?>">
            <?php } ?>
            <?php } ?>

        </form>

        <?php if (count($recambios) == 0 && $sesion_id != '') { ?>
            <p style="color:#999; margin-top:10px;">No hay recambios para esa sesion.</p>
        <?php } elseif (count($recambios) == 0) { ?>
            <p style="color:#999; margin-top:10px;">Elegi un paciente y una fecha para ver el grafico.</p>
        <?php } ?>

    </div>

    <?php if (count($recambios) > 0) { ?>
    <div class="panel">
        <h3>Infusion / drenaje / balance por recambio</h3>
        <canvas id="graficoRecambios" height="100"></canvas>
    </div>
    <?php } ?>

</div>

<?php if (count($recambios) > 0) { ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var etiquetas = [];
    var infusion = [];
    var drenaje = [];
    var balance = [];

    <?php foreach ($recambios as $fila) { ?>
        etiquetas.push("Recambio <?php echo $fila['numero_recambio']; ?>");
        infusion.push(<?php echo $fila['infusion_ml']; ?>);
        drenaje.push(<?php echo $fila['drenaje_ml']; ?>);
        balance.push(<?php echo $fila['balance_ml']; ?>);
    <?php } ?>

    var ctx = document.getElementById('graficoRecambios');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [
                { label: 'Infusion (ml)', data: infusion, backgroundColor: '#3f8792' },
                { label: 'Drenaje (ml)', data: drenaje, backgroundColor: '#2b6777' },
                { label: 'Balance (ml)', data: balance, backgroundColor: '#c0392b' }
            ]
        }
    });
</script>
<?php } ?>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
