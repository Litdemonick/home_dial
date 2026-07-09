<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    require_once __DIR__ . '/../../config/database.php';

    if (isset($_GET['marcar'])) {
        $id_marcar = $_GET['marcar'];
        $sql = $conectar->prepare("UPDATE alertas SET leida = 1 WHERE id = ?");
        $sql->bind_param('i', $id_marcar);
        $sql->execute();
        $sql->close();
        header("Location: listar.php");
        exit();
    }

    if ($_SESSION['rol_id'] == 1) {
        $sql = $conectar->prepare("SELECT a.*, p.nombre, p.apellido FROM alertas a
                                    INNER JOIN pacientes p ON p.id = a.paciente_id
                                    ORDER BY a.generada_en DESC");
        $sql->execute();
        $resultado = $sql->get_result();
    } elseif ($_SESSION['rol_id'] == 3) {
        $sql = $conectar->prepare("SELECT a.*, p.nombre, p.apellido FROM alertas a
                                    INNER JOIN pacientes p ON p.id = a.paciente_id
                                    INNER JOIN medicos m ON m.id = p.medico_id
                                    WHERE m.usuario_id = ?
                                    ORDER BY a.generada_en DESC");
        $sql->bind_param('i', $_SESSION['usuario_id']);
        $sql->execute();
        $resultado = $sql->get_result();
    } else {
        $sql = $conectar->prepare("SELECT a.*, p.nombre, p.apellido FROM alertas a
                                    INNER JOIN pacientes p ON p.id = a.paciente_id
                                    WHERE p.usuario_id = ? AND a.tipo_alerta != 'Turbidez_peritonitis'
                                    ORDER BY a.generada_en DESC");
        $sql->bind_param('i', $_SESSION['usuario_id']);
        $sql->execute();
        $resultado = $sql->get_result();
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Bandeja de alertas</h2>

    <table class="tabla">
        <tr>
            <th>Paciente</th>
            <th>Tipo</th>
            <th>Mensaje</th>
            <th>Fecha</th>
            <th>Leida</th>
            <th></th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr style="<?php echo $fila['leida'] == 0 ? 'background-color:#ffe8e8;' : ''; ?>">
            <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td><?php echo mostrar($fila['tipo_alerta']); ?></td>
            <td><?php echo mostrar($fila['mensaje']); ?></td>
            <td><?php echo mostrar($fila['generada_en']); ?></td>
            <td><?php echo $fila['leida'] == 1 ? 'Si' : 'No'; ?></td>
            <td>
                <?php if ($fila['leida'] == 0) { ?>
                    <a href="listar.php?marcar=<?php echo $fila['id']; ?>">marcar leida</a>
                <?php } else { ?>
                    -
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    include __DIR__ . '/../../includes/footer.php';
?>
