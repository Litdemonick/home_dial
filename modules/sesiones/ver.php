<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../../config/database.php';

    $id = $_GET['id'];

    $sql = $conectar->prepare("SELECT s.*, p.nombre, p.apellido
                                FROM sesiones_dialisis s
                                INNER JOIN pacientes p ON p.id = s.paciente_id
                                WHERE s.id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $sesion = $sql->get_result()->fetch_assoc();
    $sql->close();

    $sql = $conectar->prepare("SELECT * FROM recambios WHERE sesion_id = ? ORDER BY numero_recambio");
    $sql->bind_param('i', $id);
    $sql->execute();
    $recambios = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Sesion del <?php echo mostrar($sesion['fecha_sesion']); ?> - <?php echo mostrar($sesion['nombre'] . ' ' . $sesion['apellido']); ?></h2>
        <p><b>Hora:</b> <?php echo mostrar($sesion['hora_inicio']); ?> &nbsp; <b>Sistema:</b> <?php echo mostrar($sesion['tipo_sistema_dp']); ?></p>
        <p><b>Presion:</b> <?php echo mostrar($sesion['presion_sistol'] . '/' . $sesion['presion_diast']); ?> &nbsp; <b>Pulso:</b> <?php echo mostrar($sesion['pulso']); ?></p>
    </div>

    <div class="panel">
        <h3>Recambios</h3>
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
            <?php } $sql->close(); ?>
        </table>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
