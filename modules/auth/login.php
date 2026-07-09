<?php
    session_start();
    require_once __DIR__ . '/../../config/database.php';

    if (isset($_POST['accion']) && $_POST['accion'] == 'registro') {

        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $usuario_nuevo = trim($_POST['usuario_nuevo']);
        $clave_nueva = trim($_POST['clave_nueva']);
        $clave_confirmar = trim($_POST['clave_confirmar']);

        $datos_previos = '&nombre=' . urlencode($nombre) . '&apellido=' . urlencode($apellido) . '&usuario_nuevo=' . urlencode($usuario_nuevo);

        if ($nombre == '' || $apellido == '' || $usuario_nuevo == '' || $clave_nueva == '') {
            header("Location: ../../index.php?error=Complete todos los campos para registrarse" . $datos_previos);
            exit();
        }

        if ($clave_nueva != $clave_confirmar) {
            header("Location: ../../index.php?error=Las contraseñas no coinciden" . $datos_previos);
            exit();
        }

        if (strlen($clave_nueva) < 6) {
            header("Location: ../../index.php?error=La contraseña debe tener minimo 6 caracteres" . $datos_previos);
            exit();
        }

        $sql = $conectar->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $sql->bind_param('s', $usuario_nuevo);
        $sql->execute();
        $resultado = $sql->get_result();

        if ($resultado->num_rows > 0) {
            $sql->close();
            mysqli_close($conectar);
            header("Location: ../../index.php?error=Ese usuario ya existe" . $datos_previos);
            exit();
        }
        $sql->close();

        $hash = password_hash($clave_nueva, PASSWORD_BCRYPT);

        $sql = $conectar->prepare("INSERT INTO usuarios (nombre_usuario, password_hash, rol_id) VALUES (?, ?, 2)");
        $sql->bind_param('ss', $usuario_nuevo, $hash);
        $sql->execute();
        $usuario_id = $sql->insert_id;
        $sql->close();

        $sql = $conectar->prepare("INSERT INTO pacientes (usuario_id, nombre, apellido) VALUES (?, ?, ?)");
        $sql->bind_param('iss', $usuario_id, $nombre, $apellido);
        $sql->execute();
        $sql->close();

        mysqli_close($conectar);
        header("Location: ../../index.php?registrado=1");
        exit();
    }

    $usuario = trim($_POST['usuario']);
    $clave = trim($_POST['clave']);

    $sql = $conectar->prepare("SELECT u.id, u.nombre_usuario, u.password_hash, u.rol_id, r.nombre AS rol_nombre, u.activo
                                FROM usuarios u
                                INNER JOIN roles r ON r.id = u.rol_id
                                WHERE u.nombre_usuario = ?");
    $sql->bind_param('s', $usuario);
    $sql->execute();
    $resultado = $sql->get_result();
    $fila = $resultado->fetch_assoc();
    $sql->close();

    if ($fila && $fila['activo'] == 1 && password_verify($clave, $fila['password_hash'])) {

        $_SESSION['usuario_id'] = $fila['id'];
        $_SESSION['nombre_usuario'] = $fila['nombre_usuario'];
        $_SESSION['rol_id'] = $fila['rol_id'];
        $_SESSION['rol_nombre'] = $fila['rol_nombre'];

        $sql = $conectar->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $sql->bind_param('i', $fila['id']);
        $sql->execute();
        $sql->close();

        mysqli_close($conectar);
        header("Location: ../dashboard.php");
        exit();

    } else {
        mysqli_close($conectar);
        header("Location: ../../index.php?error=Usuario o contraseña incorrectos&usuario=" . urlencode($usuario));
        exit();
    }
?>
