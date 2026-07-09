<?php

function sanitizar($valor) {
    $valor = trim($valor);
    $valor = strip_tags($valor);
    return $valor;
}

function mostrar($valor) {
    if ($valor === null) {
        $valor = '';
    }
    return htmlspecialchars($valor);
}

function mostrar_dato($valor) {
    if ($valor === null || $valor === '') {
        return '<span style="color:#999;">sin datos</span>';
    }
    return htmlspecialchars($valor);
}

function obtener_paciente_id($conectar) {
    $sql = $conectar->prepare("SELECT id FROM pacientes WHERE usuario_id = ?");
    $sql->bind_param('i', $_SESSION['usuario_id']);
    $sql->execute();
    $fila = $sql->get_result()->fetch_assoc();
    $sql->close();

    if (!$fila) {
        mysqli_close($conectar);
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: /home_dial/index.php?error=Su cuenta no tiene una ficha de paciente asociada. Contacte al administrador.");
        exit();
    }

    return $fila['id'];
}

function obtener_medico_id($conectar) {
    $sql = $conectar->prepare("SELECT id FROM medicos WHERE usuario_id = ?");
    $sql->bind_param('i', $_SESSION['usuario_id']);
    $sql->execute();
    $fila = $sql->get_result()->fetch_assoc();
    $sql->close();

    if (!$fila) {
        mysqli_close($conectar);
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: /home_dial/index.php?error=Su cuenta no tiene una ficha de medico asociada. Contacte al administrador.");
        exit();
    }

    return $fila['id'];
}

function verificar_ficha_completa($conectar, $paciente_id) {
    $sql = $conectar->prepare("SELECT medico_id, cedula, fecha_nacimiento, sexo, telefono, direccion, tipo_sistema_dp, peso_kg, talla_cm, tipo_sangre, fecha_inicio_dp FROM pacientes WHERE id = ?");
    $sql->bind_param('i', $paciente_id);
    $sql->execute();
    $ficha = $sql->get_result()->fetch_assoc();
    $sql->close();

    $falta = false;
    if ($ficha['cedula'] === null || $ficha['cedula'] === '') { $falta = true; }
    if ($ficha['fecha_nacimiento'] === null || $ficha['fecha_nacimiento'] === '') { $falta = true; }
    if ($ficha['sexo'] === null || $ficha['sexo'] === '') { $falta = true; }
    if ($ficha['telefono'] === null || $ficha['telefono'] === '') { $falta = true; }
    if ($ficha['direccion'] === null || $ficha['direccion'] === '') { $falta = true; }
    if ($ficha['tipo_sistema_dp'] === null || $ficha['tipo_sistema_dp'] === '') { $falta = true; }
    if ($ficha['peso_kg'] === null || $ficha['peso_kg'] === '') { $falta = true; }
    if ($ficha['talla_cm'] === null || $ficha['talla_cm'] === '') { $falta = true; }
    if ($ficha['tipo_sangre'] === null || $ficha['tipo_sangre'] === '') { $falta = true; }
    if ($ficha['fecha_inicio_dp'] === null || $ficha['fecha_inicio_dp'] === '') { $falta = true; }

    if ($falta) {
        mysqli_close($conectar);
        header("Location: /home_dial/modules/pacientes/editar.php?error=Complete todos los datos de su ficha antes de continuar");
        exit();
    }

    if ($ficha['medico_id'] === null) {
        mysqli_close($conectar);
        header("Location: /home_dial/modules/pacientes/ver.php?error=Todavia no tiene un medico asignado. Contacte al administrador antes de registrar datos.");
        exit();
    }
}

function validar_email($correo) {
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

function validar_cedula($cedula) {
    // formato tipo cedula panameña: grupos alfanumericos separados por un solo guion, sin espacios (ej: 8-123-456, PE-8-1234)
    if (preg_match('/^[A-Za-z0-9]+(-[A-Za-z0-9]+){1,3}$/', $cedula)) {
        return true;
    } else {
        return false;
    }
}

function validar_telefono($telefono) {
    // solo numeros y guiones, sin espacios (ej: 6123-4567)
    if (preg_match('/^[0-9]+(-[0-9]+)*$/', $telefono)) {
        return true;
    } else {
        return false;
    }
}

function redirigir($url) {
    header("Location: " . $url);
    exit();
}

function tiempo_atras($fecha) {
    $ahora = new DateTime();
    $fechaDato = new DateTime($fecha);
    $diferencia = $ahora->diff($fechaDato);

    if ($diferencia->days == 0) {
        return "hoy";
    } elseif ($diferencia->days == 1) {
        return "hace 1 dia";
    } else {
        return "hace " . $diferencia->days . " dias";
    }
}

function verificar_sesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /home_dial/index.php");
        exit();
    }
}

function verificar_rol($roles_permitidos) {
    if (!in_array($_SESSION['rol_id'], $roles_permitidos)) {
        header("Location: /home_dial/modules/dashboard.php");
        exit();
    }
}

?>
