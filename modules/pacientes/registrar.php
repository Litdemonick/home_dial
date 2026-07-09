<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nombre = sanitizar($_POST['nombre']);
        $apellido = sanitizar($_POST['apellido']);
        $cedula = sanitizar($_POST['cedula']);
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $sexo = $_POST['sexo'];
        $telefono = sanitizar($_POST['telefono']);
        $direccion = sanitizar($_POST['direccion']);
        $tipo_sistema_dp = $_POST['tipo_sistema_dp'];
        $peso_kg = trim($_POST['peso_kg']);
        $talla_cm = trim($_POST['talla_cm']);
        $tipo_sangre = sanitizar($_POST['tipo_sangre']);
        $fecha_inicio_dp = $_POST['fecha_inicio_dp'];
        $medico_id = $_POST['medico_id'];
        $usuario_login = sanitizar($_POST['usuario_login']);
        $clave_login = trim($_POST['clave_login']);
        $clave_confirmar = trim($_POST['clave_confirmar']);

        if ($nombre == '') { $errores[] = 'El nombre es obligatorio'; }
        if ($apellido == '') { $errores[] = 'El apellido es obligatorio'; }
        if ($cedula == '') { $errores[] = 'La cedula es obligatoria'; }
        if ($fecha_nacimiento == '') { $errores[] = 'La fecha de nacimiento es obligatoria'; }
        if ($sexo == '') { $errores[] = 'El sexo es obligatorio'; }
        if ($telefono == '') { $errores[] = 'El telefono es obligatorio'; }
        if ($direccion == '') { $errores[] = 'La direccion es obligatoria'; }
        if ($tipo_sistema_dp == '') { $errores[] = 'El sistema DP es obligatorio'; }
        if ($peso_kg == '') { $errores[] = 'El peso es obligatorio'; }
        if ($talla_cm == '') { $errores[] = 'La talla es obligatoria'; }
        if ($tipo_sangre == '') { $errores[] = 'El tipo de sangre es obligatorio'; }
        if ($tipo_sangre != '' && !in_array($tipo_sangre, array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'))) { $errores[] = 'El tipo de sangre no es valido'; }
        if ($fecha_inicio_dp == '') { $errores[] = 'La fecha de inicio DP es obligatoria'; }
        if ($usuario_login == '') { $errores[] = 'El usuario de acceso es obligatorio'; }
        if ($clave_login == '') { $errores[] = 'La contraseña de acceso es obligatoria'; }
        if (strlen($clave_login) > 0 && strlen($clave_login) < 6) { $errores[] = 'La contraseña debe tener minimo 6 caracteres'; }
        if ($clave_login != $clave_confirmar) { $errores[] = 'Las contraseñas no coinciden'; }
        if ($peso_kg != '' && !is_numeric($peso_kg)) { $errores[] = 'El peso debe ser un numero'; }
        if ($peso_kg != '' && is_numeric($peso_kg) && ($peso_kg < 1 || $peso_kg > 300)) { $errores[] = 'El peso debe estar entre 1 y 300 kg'; }
        if ($talla_cm != '' && !is_numeric($talla_cm)) { $errores[] = 'La talla debe ser un numero'; }
        if ($talla_cm != '' && is_numeric($talla_cm) && ($talla_cm < 30 || $talla_cm > 250)) { $errores[] = 'La talla debe estar entre 30 y 250 cm'; }
        if ($fecha_nacimiento != '' && $fecha_nacimiento > date('Y-m-d')) { $errores[] = 'La fecha de nacimiento no puede ser futura'; }
        if ($cedula != '' && !validar_cedula($cedula)) { $errores[] = 'La cedula no tiene un formato valido, use solo numeros y guiones (ej: 8-123-456)'; }
        if ($telefono != '' && !validar_telefono($telefono)) { $errores[] = 'El telefono no tiene un formato valido, use solo numeros y guiones (ej: 6600-0000)'; }

        $peso_kg = $peso_kg == '' ? null : $peso_kg;
        $talla_cm = $talla_cm == '' ? null : $talla_cm;
        $medico_id = $medico_id == '' ? null : $medico_id;
        $fecha_nacimiento = $fecha_nacimiento == '' ? null : $fecha_nacimiento;
        $fecha_inicio_dp = $fecha_inicio_dp == '' ? null : $fecha_inicio_dp;
        $cedula = $cedula == '' ? null : $cedula;
        $sexo = $sexo == '' ? null : $sexo;
        $telefono = $telefono == '' ? null : $telefono;
        $direccion = $direccion == '' ? null : $direccion;
        $tipo_sistema_dp = $tipo_sistema_dp == '' ? null : $tipo_sistema_dp;
        $tipo_sangre = $tipo_sangre == '' ? null : $tipo_sangre;

        if (count($errores) == 0) {

            $sql = $conectar->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
            $sql->bind_param('s', $usuario_login);
            $sql->execute();
            if ($sql->get_result()->num_rows > 0) {
                $errores[] = 'Ese usuario de acceso ya existe';
            }
            $sql->close();

            if ($cedula !== null) {
                $sql = $conectar->prepare("SELECT id FROM pacientes WHERE cedula = ?");
                $sql->bind_param('s', $cedula);
                $sql->execute();
                if ($sql->get_result()->num_rows > 0) {
                    $errores[] = 'Ya existe un paciente registrado con esa cedula';
                }
                $sql->close();
            }
        }

        if (count($errores) == 0) {

            $hash = password_hash($clave_login, PASSWORD_BCRYPT);

            $sql = $conectar->prepare("INSERT INTO usuarios (nombre_usuario, password_hash, rol_id) VALUES (?, ?, 2)");
            $sql->bind_param('ss', $usuario_login, $hash);
            $sql->execute();
            $usuario_id = $sql->insert_id;
            $sql->close();

            $sql = $conectar->prepare("INSERT INTO pacientes
                (usuario_id, medico_id, nombre, apellido, fecha_nacimiento, sexo, cedula, telefono, direccion, tipo_sistema_dp, peso_kg, talla_cm, tipo_sangre, fecha_inicio_dp)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param('iissssssssddss', $usuario_id, $medico_id, $nombre, $apellido, $fecha_nacimiento, $sexo, $cedula, $telefono, $direccion, $tipo_sistema_dp, $peso_kg, $talla_cm, $tipo_sangre, $fecha_inicio_dp);

            if ($sql->execute()) {
                $sql->close();
                mysqli_close($conectar);
                header("Location: listar.php");
                exit();
            } else {
                $errores[] = 'Error al guardar el paciente';
                $sql->close();

                $sql = $conectar->prepare("DELETE FROM usuarios WHERE id = ?");
                $sql->bind_param('i', $usuario_id);
                $sql->execute();
                $sql->close();
            }
        }
    }

    $medicos = mysqli_query($conectar, "SELECT id, nombre, apellido FROM medicos WHERE activo = 1 ORDER BY nombre");

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Registrar paciente</h2>

        <?php if (count($errores) > 0) { ?>
            <div class="aviso excesiva">
                <ul>
                <?php foreach ($errores as $error) { ?>
                    <li><?php echo mostrar($error); ?></li>
                <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form class="formulario" method="POST">

            <div class="fila">
                <div>
                    <label>Nombre <span class="obligatorio">*</span></label>
                    <input type="text" name="nombre" value="<?php echo isset($_POST['nombre']) ? mostrar($_POST['nombre']) : ''; ?>">
                </div>
                <div>
                    <label>Apellido <span class="obligatorio">*</span></label>
                    <input type="text" name="apellido" value="<?php echo isset($_POST['apellido']) ? mostrar($_POST['apellido']) : ''; ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Cedula <span class="obligatorio">*</span></label>
                    <input type="text" name="cedula" placeholder="8-123-456" value="<?php echo isset($_POST['cedula']) ? mostrar($_POST['cedula']) : ''; ?>">
                </div>
                <div>
                    <label>Fecha de nacimiento <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo isset($_POST['fecha_nacimiento']) ? mostrar($_POST['fecha_nacimiento']) : ''; ?>">
                </div>
                <div>
                    <label>Sexo <span class="obligatorio">*</span></label>
                    <select name="sexo">
                        <option value="">--</option>
                        <option value="M" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'M') ? 'selected' : ''; ?>>M</option>
                        <option value="F" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'F') ? 'selected' : ''; ?>>F</option>
                        <option value="O" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'O') ? 'selected' : ''; ?>>O</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Telefono <span class="obligatorio">*</span></label>
                    <input type="text" name="telefono" placeholder="6600-0000" value="<?php echo isset($_POST['telefono']) ? mostrar($_POST['telefono']) : ''; ?>">
                </div>
                <div>
                    <label>Tipo de sangre <span class="obligatorio">*</span></label>
                    <select name="tipo_sangre">
                        <option value="">--</option>
                        <?php foreach (array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') as $tipo) { ?>
                            <option value="<?php echo $tipo; ?>" <?php echo (isset($_POST['tipo_sangre']) && $_POST['tipo_sangre'] == $tipo) ? 'selected' : ''; ?>><?php echo $tipo; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <label>Direccion <span class="obligatorio">*</span></label>
            <textarea name="direccion" rows="2" placeholder="direccion de residencia"><?php echo isset($_POST['direccion']) ? mostrar($_POST['direccion']) : ''; ?></textarea>

            <div class="fila">
                <div>
                    <label>Tipo de sistema DP <span class="obligatorio">*</span></label>
                    <select name="tipo_sistema_dp">
                        <option value="">--</option>
                        <option value="Baxter" <?php echo (isset($_POST['tipo_sistema_dp']) && $_POST['tipo_sistema_dp'] == 'Baxter') ? 'selected' : ''; ?>>Baxter</option>
                        <option value="Fresenius Medical Care" <?php echo (isset($_POST['tipo_sistema_dp']) && $_POST['tipo_sistema_dp'] == 'Fresenius Medical Care') ? 'selected' : ''; ?>>Fresenius Medical Care</option>
                    </select>
                </div>
                <div>
                    <label>Fecha inicio DP <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_inicio_dp" value="<?php echo isset($_POST['fecha_inicio_dp']) ? mostrar($_POST['fecha_inicio_dp']) : ''; ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Peso (kg) <span class="obligatorio">*</span></label>
                    <input type="text" name="peso_kg" placeholder="70.5" value="<?php echo isset($_POST['peso_kg']) ? mostrar($_POST['peso_kg']) : ''; ?>">
                </div>
                <div>
                    <label>Talla (cm) <span class="obligatorio">*</span></label>
                    <input type="text" name="talla_cm" placeholder="170" value="<?php echo isset($_POST['talla_cm']) ? mostrar($_POST['talla_cm']) : ''; ?>">
                </div>
                <div>
                    <label>Medico asignado</label>
                    <select name="medico_id">
                        <option value="">--</option>
                        <?php while ($fila = mysqli_fetch_assoc($medicos)) { ?>
                            <option value="<?php echo $fila['id']; ?>" <?php echo (isset($_POST['medico_id']) && $_POST['medico_id'] == $fila['id']) ? 'selected' : ''; ?>><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <h3 style="margin-top:15px;">Acceso al sistema</h3>
            <div class="fila">
                <div>
                    <label>Usuario <span class="obligatorio">*</span></label>
                    <input type="text" name="usuario_login" placeholder="usuario para iniciar sesion" value="<?php echo isset($_POST['usuario_login']) ? mostrar($_POST['usuario_login']) : ''; ?>">
                </div>
                <div>
                    <label>Contraseña <span class="obligatorio">*</span></label>
                    <input type="password" name="clave_login" placeholder="minimo 6 caracteres" minlength="6">
                </div>
                <div>
                    <label>Confirmar contraseña <span class="obligatorio">*</span></label>
                    <input type="password" name="clave_confirmar" placeholder="repita la contraseña" minlength="6">
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="boton">Guardar</button>
                <a class="boton secundario" href="listar.php">Cancelar</a>
            </div>

        </form>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
