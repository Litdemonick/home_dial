<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 2, 3));
    require_once __DIR__ . '/../../config/database.php';

    if ($_SESSION['rol_id'] == 2) {
        $paciente_id = obtener_paciente_id($conectar);

        $consulta = $conectar->prepare("SELECT medico_id FROM pacientes WHERE id = ?");
        $consulta->bind_param('i', $paciente_id);
        $consulta->execute();
        $fila = $consulta->get_result()->fetch_assoc();
        $consulta->close();

        $id = $fila['medico_id'];

        if (!$id) {
            include __DIR__ . '/../../includes/header.php';
            include __DIR__ . '/../../includes/navbar.php';
            echo '<div class="contenido"><div class="panel"><p style="color:#999;">Todavia no tiene un medico asignado. Contacte al administrador.</p></div></div>';
            mysqli_close($conectar);
            include __DIR__ . '/../../includes/footer.php';
            exit();
        }
    } elseif ($_SESSION['rol_id'] == 3) {
        $id = obtener_medico_id($conectar);
    } else {
        $id = $_GET['id'];
    }

    $consulta = $conectar->prepare("SELECT * FROM medicos WHERE id = ?");
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $medico = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) {
        $consulta = $conectar->prepare("SELECT id, nombre, apellido, cedula, tipo_sistema_dp FROM pacientes WHERE medico_id = ?");
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $pacientes = $consulta->get_result();
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2><?php echo mostrar($medico['nombre'] . ' ' . $medico['apellido']); ?>
            <?php if ($_SESSION['rol_id'] == 1) { ?>
                <a href="editar.php?id=<?php echo $medico['id']; ?>" style="font-size:13px;">(editar)</a>
            <?php } elseif ($_SESSION['rol_id'] == 3) { ?>
                <a href="editar.php" style="font-size:13px;">(completar mis datos)</a>
            <?php } ?>
        </h2>
        <p><b>Especialidad:</b> <?php echo mostrar_dato($medico['especialidad']); ?> &nbsp; <b>Idoneidad:</b> <?php echo mostrar_dato($medico['idoneidad']); ?></p>
        <p><b>Telefono:</b> <?php echo mostrar_dato($medico['telefono']); ?> &nbsp; <b>Email:</b> <?php echo mostrar_dato($medico['email']); ?></p>
    </div>

    <?php if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) { ?>
    <div class="panel">
        <h3>Pacientes asignados</h3>
        <table class="tabla">
            <tr><th>Nombre</th><th>Cedula</th><th>Sistema DP</th></tr>
            <?php while ($fila = $pacientes->fetch_assoc()) { ?>
            <tr>
                <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                <td><?php echo mostrar_dato($fila['cedula']); ?></td>
                <td><?php echo mostrar_dato($fila['tipo_sistema_dp']); ?></td>
            </tr>
            <?php } $consulta->close(); ?>
        </table>
    </div>
    <?php } ?>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
