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

    $sql = $conectar->prepare("SELECT * FROM balance_diario_resumen WHERE paciente_id = ? ORDER BY fecha_sesion DESC");
    $sql->bind_param('i', $paciente_id);
    $sql->execute();
    $resultado = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Resumen de balance diario</h2>

    <table class="tabla">
        <tr>
            <th>Fecha sesion</th>
            <th>Sistema DP</th>
            <th>Total infusion</th>
            <th>Total drenaje</th>
            <th>Balance final</th>
            <th>Recambios turbios</th>
            <th>Estado</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['fecha_sesion']); ?></td>
            <td><?php echo mostrar($fila['tipo_sistema_dp']); ?></td>
            <td><?php echo mostrar($fila['total_infusion']); ?> ml</td>
            <td><?php echo mostrar($fila['total_drenaje']); ?> ml</td>
            <td><?php echo mostrar($fila['balance_final']); ?> ml</td>
            <td><?php echo mostrar($fila['recambios_turbios']); ?></td>
            <td><?php echo mostrar($fila['estado_balance']); ?></td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    include __DIR__ . '/../../includes/footer.php';
?>
