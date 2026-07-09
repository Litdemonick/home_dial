<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 3));
    require_once __DIR__ . '/../../config/database.php';

    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

    if ($_SESSION['rol_id'] == 1) {
        $sql = $conectar->prepare("SELECT p.id, p.nombre, p.apellido, p.cedula, p.tipo_sistema_dp, p.activo, m.nombre AS medico_nombre, m.apellido AS medico_apellido
                                    FROM pacientes p
                                    LEFT JOIN medicos m ON m.id = p.medico_id
                                    WHERE p.nombre LIKE ? OR p.apellido LIKE ? OR p.cedula LIKE ?
                                    ORDER BY p.nombre");
        $like = "%" . $buscar . "%";
        $sql->bind_param('sss', $like, $like, $like);
    } else {
        $sql = $conectar->prepare("SELECT p.id, p.nombre, p.apellido, p.cedula, p.tipo_sistema_dp, p.activo, m.nombre AS medico_nombre, m.apellido AS medico_apellido
                                    FROM pacientes p
                                    LEFT JOIN medicos m ON m.id = p.medico_id
                                    WHERE m.usuario_id = ? AND (p.nombre LIKE ? OR p.apellido LIKE ? OR p.cedula LIKE ?)
                                    ORDER BY p.nombre");
        $like = "%" . $buscar . "%";
        $sql->bind_param('isss', $_SESSION['usuario_id'], $like, $like, $like);
    }

    $sql->execute();
    $resultado = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Pacientes</h2>

    <div class="buscador">
        <form method="GET">
            <input type="text" name="buscar" placeholder="buscar por nombre o cedula..." value="<?php echo mostrar($buscar); ?>">
            <button type="submit" class="boton">Buscar</button>
            <?php if ($_SESSION['rol_id'] == 1) { ?>
                <a class="boton secundario" href="registrar.php">+ Nuevo paciente</a>
            <?php } ?>
        </form>
    </div>

    <?php if ($resultado->num_rows == 0) { ?>
        <p style="color:#999;">No hay pacientes registrados todavia.</p>
    <?php } else { ?>
    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Sistema DP</th>
            <th>Medico</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td><?php echo mostrar_dato($fila['cedula']); ?></td>
            <td><?php echo mostrar_dato($fila['tipo_sistema_dp']); ?></td>
            <td><?php echo $fila['medico_nombre'] ? mostrar($fila['medico_nombre'] . ' ' . $fila['medico_apellido']) : mostrar_dato(null); ?></td>
            <td><?php echo $fila['activo'] == 1 ? 'Si' : 'No'; ?></td>
            <td>
                <a href="ver.php?id=<?php echo $fila['id']; ?>">ver</a>
                <?php if ($_SESSION['rol_id'] == 1) { ?>
                    | <a href="editar.php?id=<?php echo $fila['id']; ?>">editar</a>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>

</div>

<?php
    $sql->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
