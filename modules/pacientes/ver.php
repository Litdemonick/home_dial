<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 2, 3));
    require_once __DIR__ . '/../../config/database.php';

    if ($_SESSION['rol_id'] == 2) {
        $id = obtener_paciente_id($conectar);
    } else {
        $id = $_GET['id'];
    }

    $sql = $conectar->prepare("SELECT p.*, m.nombre AS medico_nombre, m.apellido AS medico_apellido
                                FROM pacientes p
                                LEFT JOIN medicos m ON m.id = p.medico_id
                                WHERE p.id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $paciente = $sql->get_result()->fetch_assoc();
    $sql->close();

    $sql = $conectar->prepare("SELECT * FROM sesiones_dialisis WHERE paciente_id = ? ORDER BY fecha_sesion DESC LIMIT 5");
    $sql->bind_param('i', $id);
    $sql->execute();
    $sesiones = $sql->get_result();

    $sql2 = $conectar->prepare("SELECT * FROM registros_glucosa WHERE paciente_id = ? ORDER BY fecha_medicion DESC LIMIT 5");
    $sql2->bind_param('i', $id);
    $sql2->execute();
    $glucosas = $sql2->get_result();

    $sql3 = $conectar->prepare("SELECT * FROM alertas WHERE paciente_id = ? ORDER BY generada_en DESC LIMIT 5");
    $sql3->bind_param('i', $id);
    $sql3->execute();
    $alertas = $sql3->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <?php if (isset($_GET['error'])) { ?>
        <div class="aviso excesiva"><?php echo mostrar($_GET['error']); ?></div>
    <?php } ?>

    <div class="panel">
        <h2><?php echo mostrar($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
            <?php if ($_SESSION['rol_id'] == 1) { ?>
                <a href="editar.php?id=<?php echo $paciente['id']; ?>" style="font-size:13px;">(editar)</a>
            <?php } elseif ($_SESSION['rol_id'] == 2) { ?>
                <a href="editar.php" style="font-size:13px;">(completar mis datos)</a>
            <?php } ?>
        </h2>
        <p><b>Cedula:</b> <?php echo mostrar_dato($paciente['cedula']); ?> &nbsp; <b>Sexo:</b> <?php echo mostrar_dato($paciente['sexo']); ?> &nbsp; <b>Fecha nacimiento:</b> <?php echo mostrar_dato($paciente['fecha_nacimiento']); ?></p>
        <p><b>Sistema DP:</b> <?php echo mostrar_dato($paciente['tipo_sistema_dp']); ?> &nbsp; <b>Fecha inicio DP:</b> <?php echo mostrar_dato($paciente['fecha_inicio_dp']); ?></p>
        <p><b>Peso:</b> <?php echo mostrar_dato($paciente['peso_kg']); ?> <?php echo $paciente['peso_kg'] !== null ? 'kg' : ''; ?> &nbsp; <b>Talla:</b> <?php echo mostrar_dato($paciente['talla_cm']); ?> <?php echo $paciente['talla_cm'] !== null ? 'cm' : ''; ?> &nbsp; <b>Tipo sangre:</b> <?php echo mostrar_dato($paciente['tipo_sangre']); ?></p>
        <p><b>Telefono:</b> <?php echo mostrar_dato($paciente['telefono']); ?> &nbsp; <b>Direccion:</b> <?php echo mostrar_dato($paciente['direccion']); ?></p>
        <p><b>Medico:</b> <?php echo $paciente['medico_nombre'] ? mostrar($paciente['medico_nombre'] . ' ' . $paciente['medico_apellido']) : mostrar_dato(null); ?></p>
    </div>

    <div class="panel">
        <h3>Sesiones recientes</h3>
        <?php if ($sesiones->num_rows == 0) { ?>
            <p style="color:#999;">Todavia no hay sesiones registradas.</p>
        <?php } else { ?>
        <table class="tabla">
            <tr><th>Fecha</th><th>Hora</th><th>Sistema</th><th>P/A</th><th>Pulso</th></tr>
            <?php while ($fila = $sesiones->fetch_assoc()) { ?>
            <tr>
                <td><?php echo mostrar($fila['fecha_sesion']); ?></td>
                <td><?php echo mostrar($fila['hora_inicio']); ?></td>
                <td><?php echo mostrar($fila['tipo_sistema_dp']); ?></td>
                <td><?php echo mostrar_dato($fila['presion_sistol'] . '/' . $fila['presion_diast']); ?></td>
                <td><?php echo mostrar_dato($fila['pulso']); ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } $sql->close(); ?>
    </div>

    <div class="panel">
        <h3>Glucosa reciente</h3>
        <?php if ($glucosas->num_rows == 0) { ?>
            <p style="color:#999;">Todavia no hay mediciones de glucosa registradas.</p>
        <?php } else { ?>
        <table class="tabla">
            <tr><th>Fecha</th><th>mg/dL</th><th>Momento</th><th>Estado</th></tr>
            <?php while ($fila = $glucosas->fetch_assoc()) { ?>
            <tr>
                <td><?php echo mostrar($fila['fecha_medicion']); ?></td>
                <td><?php echo mostrar($fila['glucosa_mgdl']); ?></td>
                <td><?php echo mostrar($fila['momento']); ?></td>
                <td><?php echo mostrar($fila['estado_glucemico']); ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } $sql2->close(); ?>
    </div>

    <div class="panel">
        <h3>Alertas</h3>
        <?php if ($alertas->num_rows == 0) { ?>
            <p style="color:#999;">Sin alertas por el momento.</p>
        <?php } else { ?>
        <table class="tabla">
            <tr><th>Tipo</th><th>Mensaje</th><th>Fecha</th><th>Leida</th></tr>
            <?php while ($fila = $alertas->fetch_assoc()) { ?>
            <tr>
                <td><?php echo mostrar($fila['tipo_alerta']); ?></td>
                <td><?php echo mostrar($fila['mensaje']); ?></td>
                <td><?php echo mostrar($fila['generada_en']); ?></td>
                <td><?php echo $fila['leida'] == 1 ? 'Si' : 'No'; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } $sql3->close(); ?>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
