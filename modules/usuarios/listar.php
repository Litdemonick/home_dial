<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $like = "%" . $buscar . "%";

    $sql = $conectar->prepare("SELECT u.id, u.nombre_usuario, u.activo, u.ultimo_acceso, r.nombre AS rol_nombre
                                FROM usuarios u
                                INNER JOIN roles r ON r.id = u.rol_id
                                WHERE u.nombre_usuario LIKE ?
                                ORDER BY u.nombre_usuario");
    $sql->bind_param('s', $like);
    $sql->execute();
    $resultado = $sql->get_result();

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <h2 style="margin-bottom:10px;">Usuarios del sistema</h2>

    <div class="buscador">
        <form method="GET">
            <input type="text" name="buscar" placeholder="buscar por usuario..." value="<?php echo mostrar($buscar); ?>">
            <button type="submit" class="boton">Buscar</button>
            <a class="boton secundario" href="registrar.php">+ Nuevo usuario (admin)</a>
        </form>
    </div>

    <table class="tabla">
        <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Activo</th>
            <th>Ultimo acceso</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
        <tr>
            <td><?php echo mostrar($fila['nombre_usuario']); ?></td>
            <td><?php echo mostrar($fila['rol_nombre']); ?></td>
            <td><?php echo $fila['activo'] == 1 ? 'Si' : 'No'; ?></td>
            <td><?php echo mostrar($fila['ultimo_acceso']); ?></td>
            <td><a href="editar.php?id=<?php echo $fila['id']; ?>">editar</a></td>
        </tr>
        <?php } ?>
    </table>

</div>

<?php
    $sql->close();
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
