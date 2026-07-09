<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $like = "%" . $buscar . "%";

    $consulta = $conectar->prepare("SELECT id, nombre, apellido, especialidad, telefono, email, activo
                                FROM medicos WHERE nombre LIKE ? OR apellido LIKE ? ORDER BY nombre");
    $consulta->bind_param('ss', $like, $like);
    $consulta->execute();
    $resultado = $consulta->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Medicos</h2>

    <div class="buscador">
        <form method="GET">
            <input type="text" name="buscar" placeholder="buscar por nombre..." value="<?php echo mostrar($buscar); ?>">
            <button type="submit" class="boton">Buscar</button>
            <a class="boton secundario" href="registrar.php">+ Nuevo medico</a>
        </form>
    </div>

    <table class="tabla">
        <tr>
            <th>Nombre</th>
            <th>Especialidad</th>
            <th>Telefono</th>
            <th>Email</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
            <td><?php echo mostrar($fila['especialidad']); ?></td>
            <td><?php echo mostrar($fila['telefono']); ?></td>
            <td><?php echo mostrar($fila['email']); ?></td>
            <td><?php echo $fila['activo'] == 1 ? 'Si' : 'No'; ?></td>
            <td>
                <a href="ver.php?id=<?php echo $fila['id']; ?>">ver</a> |
                <a href="editar.php?id=<?php echo $fila['id']; ?>">editar</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    $consulta->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
