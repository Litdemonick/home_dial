<?php
    session_start();
    require_once __DIR__ . '/../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../config/database.php';
    include __DIR__ . '/../includes/header.php';
    include __DIR__ . '/../includes/navbar.php';
?>

<div class="contenido">

<?php if ($_SESSION['rol_id'] == 1) { ?>

    <?php
        $resultado = mysqli_query($conectar, "SELECT COUNT(*) AS total FROM pacientes WHERE activo = 1");
        $fila = mysqli_fetch_assoc($resultado);
        $total_pacientes = $fila['total'];

        $resultado = mysqli_query($conectar, "SELECT COUNT(*) AS total FROM medicos WHERE activo = 1");
        $fila = mysqli_fetch_assoc($resultado);
        $total_medicos = $fila['total'];

        $resultado = mysqli_query($conectar, "SELECT COUNT(*) AS total FROM alertas WHERE leida = 0");
        $fila = mysqli_fetch_assoc($resultado);
        $total_alertas = $fila['total'];
    ?>

    <div class="tarjetas">
        <div class="tarjeta">
            <div class="numero"><?php echo $total_pacientes; ?></div>
            <div class="etiqueta">Pacientes activos</div>
        </div>
        <div class="tarjeta">
            <div class="numero"><?php echo $total_medicos; ?></div>
            <div class="etiqueta">Medicos</div>
        </div>
        <div class="tarjeta">
            <div class="numero"><?php echo $total_alertas; ?></div>
            <div class="etiqueta">Alertas sin leer</div>
        </div>
    </div>

    <div class="panel">
        <h3>Ultimas alertas</h3>
        <table class="tabla">
            <tr><th>Paciente</th><th>Tipo</th><th>Mensaje</th><th>Fecha</th></tr>
            <?php
                $resultado = mysqli_query($conectar, "SELECT a.tipo_alerta, a.mensaje, a.generada_en, p.nombre, p.apellido
                                                        FROM alertas a
                                                        INNER JOIN pacientes p ON p.id = a.paciente_id
                                                        ORDER BY a.generada_en DESC LIMIT 5");
                while ($fila = mysqli_fetch_assoc($resultado)) {
            ?>
                <tr>
                    <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                    <td><?php echo mostrar($fila['tipo_alerta']); ?></td>
                    <td><?php echo mostrar($fila['mensaje']); ?></td>
                    <td><?php echo mostrar($fila['generada_en']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

<?php } elseif ($_SESSION['rol_id'] == 3) { ?>

    <?php
        $medico_id = obtener_medico_id($conectar);

        $sql = $conectar->prepare("SELECT COUNT(*) AS total FROM pacientes WHERE medico_id = ?");
        $sql->bind_param('i', $medico_id);
        $sql->execute();
        $total_pacientes = $sql->get_result()->fetch_assoc()['total'];
        $sql->close();

        $sql = $conectar->prepare("SELECT COUNT(*) AS total FROM alertas a
                                    INNER JOIN pacientes p ON p.id = a.paciente_id
                                    WHERE p.medico_id = ? AND a.leida = 0");
        $sql->bind_param('i', $medico_id);
        $sql->execute();
        $total_alertas = $sql->get_result()->fetch_assoc()['total'];
        $sql->close();
    ?>

    <div class="tarjetas">
        <div class="tarjeta">
            <div class="numero"><?php echo $total_pacientes; ?></div>
            <div class="etiqueta">Pacientes asignados</div>
        </div>
        <div class="tarjeta">
            <div class="numero"><?php echo $total_alertas; ?></div>
            <div class="etiqueta">Alertas de mis pacientes</div>
        </div>
    </div>

    <div class="panel">
        <h3>Mis pacientes</h3>
        <table class="tabla">
            <tr><th>Nombre</th><th>Cedula</th><th>Sistema DP</th></tr>
            <?php
                $sql = $conectar->prepare("SELECT nombre, apellido, cedula, tipo_sistema_dp FROM pacientes WHERE medico_id = ?");
                $sql->bind_param('i', $medico_id);
                $sql->execute();
                $resultado = $sql->get_result();
                while ($fila = $resultado->fetch_assoc()) {
            ?>
                <tr>
                    <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                    <td><?php echo mostrar($fila['cedula']); ?></td>
                    <td><?php echo mostrar($fila['tipo_sistema_dp']); ?></td>
                </tr>
            <?php } $sql->close(); ?>
        </table>
    </div>

<?php } else { ?>

    <?php
        $paciente_id = obtener_paciente_id($conectar);

        $sql = $conectar->prepare("SELECT m.nombre, m.apellido, m.especialidad, m.telefono
                                    FROM pacientes p
                                    LEFT JOIN medicos m ON m.id = p.medico_id
                                    WHERE p.id = ?");
        $sql->bind_param('i', $paciente_id);
        $sql->execute();
        $medico = $sql->get_result()->fetch_assoc();
        $sql->close();

        $sql = $conectar->prepare("SELECT balance_final, estado_balance FROM balance_diario_resumen
                                    WHERE paciente_id = ? ORDER BY fecha_sesion DESC LIMIT 1");
        $sql->bind_param('i', $paciente_id);
        $sql->execute();
        $balance_hoy = $sql->get_result()->fetch_assoc();
        $sql->close();

        $sql = $conectar->prepare("SELECT glucosa_mgdl, momento FROM registros_glucosa
                                    WHERE paciente_id = ? ORDER BY fecha_medicion DESC LIMIT 1");
        $sql->bind_param('i', $paciente_id);
        $sql->execute();
        $glucosa_ultima = $sql->get_result()->fetch_assoc();
        $sql->close();
    ?>

    <div class="tarjetas">
        <div class="tarjeta">
            <div class="numero"><?php echo $balance_hoy ? $balance_hoy['balance_final'] . ' ml' : '-'; ?></div>
            <div class="etiqueta">Ultimo balance registrado</div>
        </div>
        <div class="tarjeta">
            <div class="numero"><?php echo $glucosa_ultima ? $glucosa_ultima['glucosa_mgdl'] . ' mg/dL' : '-'; ?></div>
            <div class="etiqueta">Ultima glucosa</div>
        </div>
    </div>

    <?php if ($balance_hoy) { ?>
    <div class="panel">
        <h3>Estado del ultimo balance</h3>
        <p><?php echo mostrar($balance_hoy['estado_balance']); ?></p>
    </div>
    <?php } ?>

    <div class="panel">
        <h3>Mi medico</h3>
        <?php if ($medico && $medico['nombre']) { ?>
            <p><b><?php echo mostrar($medico['nombre'] . ' ' . $medico['apellido']); ?></b></p>
            <p><?php echo mostrar_dato($medico['especialidad']); ?> &nbsp; <?php echo mostrar_dato($medico['telefono']); ?></p>
        <?php } else { ?>
            <p style="color:#999;">Todavia no tiene un medico asignado. Contacte al administrador.</p>
        <?php } ?>
    </div>

    <div class="panel">
        <a class="boton" href="recambios/registrar.php">+ Nuevo balance del dia</a>
        <a class="boton secundario" href="glucosa/registrar.php">+ Nueva medicion de glucosa</a>
    </div>

<?php } ?>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../includes/footer.php';
?>
