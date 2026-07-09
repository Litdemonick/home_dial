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

    $consulta = $conectar->prepare("SELECT * FROM registros_glucosa WHERE paciente_id = ? ORDER BY fecha_medicion DESC");
    $consulta->bind_param('i', $paciente_id);
    $consulta->execute();
    $resultado = $consulta->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Historial de glucosa</h2>

    <table class="tabla">
        <tr><th>Fecha</th><th>mg/dL</th><th>Momento</th><th>Estado</th></tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['fecha_medicion']); ?></td>
            <td><?php echo $fila['glucosa_mgdl']; ?></td>
            <td><?php echo mostrar($fila['momento']); ?></td>
            <td><?php echo mostrar($fila['estado_glucemico']); ?></td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    $consulta->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
