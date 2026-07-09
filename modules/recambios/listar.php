<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../../config/database.php';

    $sesion_id = $_GET['sesion'];

    $sql = $conectar->prepare("SELECT s.fecha_sesion, p.nombre, p.apellido FROM sesiones_dialisis s
                                INNER JOIN pacientes p ON p.id = s.paciente_id
                                WHERE s.id = ?");
    $sql->bind_param('i', $sesion_id);
    $sql->execute();
    $sesion = $sql->get_result()->fetch_assoc();
    $sql->close();

    $sql = $conectar->prepare("SELECT * FROM recambios WHERE sesion_id = ? ORDER BY numero_recambio");
    $sql->bind_param('i', $sesion_id);
    $sql->execute();
    $recambios = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Recambios - <?php echo mostrar($sesion['nombre'] . ' ' . $sesion['apellido']); ?> - <?php echo mostrar($sesion['fecha_sesion']); ?></h2>

    <table class="tabla">
        <tr><th>#</th><th>Concentracion</th><th>Infusion</th><th>Drenaje</th><th>Balance</th><th>Cualidad</th></tr>
        <?php while ($fila = $recambios->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['numero_recambio']); ?></td>
            <td><?php echo mostrar($fila['concentracion']); ?></td>
            <td><?php echo mostrar($fila['infusion_ml']); ?></td>
            <td><?php echo mostrar($fila['drenaje_ml']); ?></td>
            <td><?php echo mostrar($fila['balance_ml']); ?></td>
            <td><?php echo mostrar($fila['cualidad']); ?></td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    $sql->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
