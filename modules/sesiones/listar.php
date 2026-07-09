<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../../config/database.php';

    if ($_SESSION['rol_id'] == 2) {
        $paciente_id = obtener_paciente_id($conectar);
    } else {
        $paciente_id = $_GET['paciente'];
    }

    $sql = $conectar->prepare("SELECT s.id, s.fecha_sesion, s.hora_inicio, s.tipo_sistema_dp, s.presion_sistol, s.presion_diast, s.pulso,
                                       b.balance_final, b.estado_balance
                                FROM sesiones_dialisis s
                                LEFT JOIN balance_diario_resumen b ON b.sesion_id = s.id
                                WHERE s.paciente_id = ?
                                ORDER BY s.fecha_sesion DESC");
    $sql->bind_param('i', $paciente_id);
    $sql->execute();
    $resultado = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Sesiones de dialisis</h2>

    <table class="tabla">
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Sistema DP</th>
            <th>P/A</th>
            <th>Pulso</th>
            <th>Balance final</th>
            <th>Estado</th>
            <th></th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['fecha_sesion']); ?></td>
            <td><?php echo mostrar($fila['hora_inicio']); ?></td>
            <td><?php echo mostrar($fila['tipo_sistema_dp']); ?></td>
            <td><?php echo mostrar($fila['presion_sistol'] . '/' . $fila['presion_diast']); ?></td>
            <td><?php echo mostrar($fila['pulso']); ?></td>
            <td><?php echo mostrar($fila['balance_final']); ?></td>
            <td><?php echo mostrar($fila['estado_balance']); ?></td>
            <td><a href="ver.php?id=<?php echo $fila['id']; ?>">ver</a></td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    $sql->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
