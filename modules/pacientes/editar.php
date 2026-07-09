<?php
    session_start();
    require_once __DIR__ . '/../../includes/funciones.php';
    verificar_sesion();
    verificar_rol(array(1, 2));
    require_once __DIR__ . '/../../config/database.php';

    $errores = array();

    if ($_SESSION['rol_id'] == 2) {
        $id = obtener_paciente_id($conectar);
    } else {
        $id = $_GET['id'];
    }

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

        if ($nombre == '') { $errores[] = 'El nombre es obligatorio'; }
        if ($apellido == '') { $errores[] = 'El apellido es obligatorio'; }
        if ($cedula == '') { $errores[] = 'La cedula es obligatoria'; }
        if ($cedula != '' && !validar_cedula($cedula)) { $errores[] = 'La cedula no tiene un formato valido, use solo numeros y guiones (ej: 8-123-456)'; }
        if ($fecha_nacimiento == '') { $errores[] = 'La fecha de nacimiento es obligatoria'; }
        if ($sexo == '') { $errores[] = 'El sexo es obligatorio'; }
        if ($telefono == '') { $errores[] = 'El telefono es obligatorio'; }
        if ($telefono != '' && !validar_telefono($telefono)) { $errores[] = 'El telefono no tiene un formato valido, use solo numeros y guiones (ej: 6600-0000)'; }
        if ($direccion == '') { $errores[] = 'La direccion es obligatoria'; }
        if ($tipo_sistema_dp == '') { $errores[] = 'El sistema DP es obligatorio'; }
        if ($peso_kg == '') { $errores[] = 'El peso es obligatorio'; }
        if ($talla_cm == '') { $errores[] = 'La talla es obligatoria'; }
        if ($tipo_sangre == '') { $errores[] = 'El tipo de sangre es obligatorio'; }
        if ($tipo_sangre != '' && !in_array($tipo_sangre, array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'))) { $errores[] = 'El tipo de sangre no es valido'; }
        if ($fecha_inicio_dp == '') { $errores[] = 'La fecha de inicio DP es obligatoria'; }
        if ($peso_kg != '' && !is_numeric($peso_kg)) { $errores[] = 'El peso debe ser un numero'; }
        if ($peso_kg != '' && is_numeric($peso_kg) && ($peso_kg < 1 || $peso_kg > 300)) { $errores[] = 'El peso debe estar entre 1 y 300 kg'; }
        if ($talla_cm != '' && !is_numeric($talla_cm)) { $errores[] = 'La talla debe ser un numero'; }
        if ($talla_cm != '' && is_numeric($talla_cm) && ($talla_cm < 30 || $talla_cm > 250)) { $errores[] = 'La talla debe estar entre 30 y 250 cm'; }
        if ($fecha_nacimiento != '' && $fecha_nacimiento > date('Y-m-d')) { $errores[] = 'La fecha de nacimiento no puede ser futura'; }

        if (count($errores) == 0 && $cedula != '') {
            $sql = $conectar->prepare("SELECT id FROM pacientes WHERE cedula = ? AND id != ?");
            $sql->bind_param('si', $cedula, $id);
            $sql->execute();
            if ($sql->get_result()->num_rows > 0) {
                $errores[] = 'Ya existe otro paciente registrado con esa cedula';
            }
            $sql->close();
        }

        if ($_SESSION['rol_id'] == 1) {
            $medico_id = $_POST['medico_id'];
            $medico_id = $medico_id == '' ? null : $medico_id;
            $activo = isset($_POST['activo']) ? 1 : 0;
        }

        if (count($errores) == 0) {

            if ($_SESSION['rol_id'] == 1) {
                $sql = $conectar->prepare("UPDATE pacientes SET medico_id=?, nombre=?, apellido=?, fecha_nacimiento=?, sexo=?, cedula=?, telefono=?, direccion=?, tipo_sistema_dp=?, peso_kg=?, talla_cm=?, tipo_sangre=?, fecha_inicio_dp=?, activo=?
                                            WHERE id=?");
                $sql->bind_param('issssssssddssii', $medico_id, $nombre, $apellido, $fecha_nacimiento, $sexo, $cedula, $telefono, $direccion, $tipo_sistema_dp, $peso_kg, $talla_cm, $tipo_sangre, $fecha_inicio_dp, $activo, $id);
            } else {
                $sql = $conectar->prepare("UPDATE pacientes SET nombre=?, apellido=?, fecha_nacimiento=?, sexo=?, cedula=?, telefono=?, direccion=?, tipo_sistema_dp=?, peso_kg=?, talla_cm=?, tipo_sangre=?, fecha_inicio_dp=?
                                            WHERE id=?");
                $sql->bind_param('ssssssssddssi', $nombre, $apellido, $fecha_nacimiento, $sexo, $cedula, $telefono, $direccion, $tipo_sistema_dp, $peso_kg, $talla_cm, $tipo_sangre, $fecha_inicio_dp, $id);
            }

            if ($sql->execute()) {
                $sql->close();
                mysqli_close($conectar);
                if ($_SESSION['rol_id'] == 1) {
                    header("Location: ver.php?id=" . $id);
                } else {
                    header("Location: ver.php");
                }
                exit();
            } else {
                $errores[] = 'Error al actualizar el paciente';
            }
            $sql->close();
        }
    }

    $sql = $conectar->prepare("SELECT * FROM pacientes WHERE id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $paciente = $sql->get_result()->fetch_assoc();
    $sql->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($errores) > 0) {
        $paciente['nombre'] = $nombre;
        $paciente['apellido'] = $apellido;
        $paciente['cedula'] = $cedula;
        $paciente['fecha_nacimiento'] = $fecha_nacimiento;
        $paciente['sexo'] = $sexo;
        $paciente['telefono'] = $telefono;
        $paciente['direccion'] = $direccion;
        $paciente['tipo_sistema_dp'] = $tipo_sistema_dp;
        $paciente['peso_kg'] = $peso_kg;
        $paciente['talla_cm'] = $talla_cm;
        $paciente['tipo_sangre'] = $tipo_sangre;
        $paciente['fecha_inicio_dp'] = $fecha_inicio_dp;
        if ($_SESSION['rol_id'] == 1) {
            $paciente['medico_id'] = $medico_id;
            $paciente['activo'] = $activo;
        }
    }

    if ($_SESSION['rol_id'] == 1) {
        $medicos = mysqli_query($conectar, "SELECT id, nombre, apellido FROM medicos WHERE activo = 1 ORDER BY nombre");
    }

    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
?>

<div class="contenido">

    <div class="panel">
        <h2>Editar paciente</h2>

        <?php if (count($errores) > 0) { ?>
            <div class="aviso excesiva">
                <ul>
                <?php foreach ($errores as $error) { ?>
                    <li><?php echo mostrar($error); ?></li>
                <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <?php if (isset($_GET['error'])) { ?>
            <div class="aviso excesiva"><?php echo mostrar($_GET['error']); ?></div>
        <?php } ?>

        <form class="formulario" method="POST">

            <div class="fila">
                <div>
                    <label>Nombre <span class="obligatorio">*</span></label>
                    <input type="text" name="nombre" value="<?php echo mostrar($paciente['nombre']); ?>">
                </div>
                <div>
                    <label>Apellido <span class="obligatorio">*</span></label>
                    <input type="text" name="apellido" value="<?php echo mostrar($paciente['apellido']); ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Cedula <span class="obligatorio">*</span></label>
                    <input type="text" name="cedula" value="<?php echo mostrar($paciente['cedula']); ?>" placeholder="8-123-456">
                </div>
                <div>
                    <label>Fecha de nacimiento <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo mostrar($paciente['fecha_nacimiento']); ?>">
                </div>
                <div>
                    <label>Sexo <span class="obligatorio">*</span></label>
                    <select name="sexo">
                        <option value="">--</option>
                        <option value="M" <?php if ($paciente['sexo'] == 'M') echo 'selected'; ?>>M</option>
                        <option value="F" <?php if ($paciente['sexo'] == 'F') echo 'selected'; ?>>F</option>
                        <option value="O" <?php if ($paciente['sexo'] == 'O') echo 'selected'; ?>>O</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Telefono <span class="obligatorio">*</span></label>
                    <input type="text" name="telefono" value="<?php echo mostrar($paciente['telefono']); ?>" placeholder="6600-0000">
                </div>
                <div>
                    <label>Tipo de sangre <span class="obligatorio">*</span></label>
                    <select name="tipo_sangre">
                        <option value="">--</option>
                        <?php foreach (array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') as $tipo) { ?>
                            <option value="<?php echo $tipo; ?>" <?php echo ($paciente['tipo_sangre'] == $tipo) ? 'selected' : ''; ?>><?php echo $tipo; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <label>Direccion <span class="obligatorio">*</span></label>
            <textarea name="direccion" rows="2"><?php echo mostrar($paciente['direccion']); ?></textarea>

            <div class="fila">
                <div>
                    <label>Tipo de sistema DP <span class="obligatorio">*</span></label>
                    <select name="tipo_sistema_dp">
                        <option value="">--</option>
                        <option value="Baxter" <?php if ($paciente['tipo_sistema_dp'] == 'Baxter') echo 'selected'; ?>>Baxter</option>
                        <option value="Fresenius Medical Care" <?php if ($paciente['tipo_sistema_dp'] == 'Fresenius Medical Care') echo 'selected'; ?>>Fresenius Medical Care</option>
                    </select>
                </div>
                <div>
                    <label>Fecha inicio DP <span class="obligatorio">*</span></label>
                    <input type="date" name="fecha_inicio_dp" value="<?php echo mostrar($paciente['fecha_inicio_dp']); ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Peso (kg) <span class="obligatorio">*</span></label>
                    <input type="text" name="peso_kg" value="<?php echo mostrar($paciente['peso_kg']); ?>" placeholder="70.5">
                </div>
                <div>
                    <label>Talla (cm) <span class="obligatorio">*</span></label>
                    <input type="text" name="talla_cm" value="<?php echo mostrar($paciente['talla_cm']); ?>" placeholder="170">
                </div>
                <?php if ($_SESSION['rol_id'] == 1) { ?>
                <div>
                    <label>Medico asignado</label>
                    <select name="medico_id">
                        <option value="">--</option>
                        <?php while ($fila = mysqli_fetch_assoc($medicos)) { ?>
                            <option value="<?php echo $fila['id']; ?>" <?php if ($fila['id'] == $paciente['medico_id']) echo 'selected'; ?>><?php echo mostrar($fila['nombre'] . ' ' . $fila['apellido']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
            </div>

            <?php if ($_SESSION['rol_id'] == 1) { ?>
            <label>
                <input type="checkbox" name="activo" style="width:auto;" <?php if ($paciente['activo'] == 1) echo 'checked'; ?>>
                Activo
            </label>
            <?php } ?>

            <div class="botones">
                <button type="submit" class="boton">Guardar</button>
                <?php if ($_SESSION['rol_id'] == 1) { ?>
                    <a class="boton secundario" href="ver.php?id=<?php echo $id; ?>">Cancelar</a>
                <?php } else { ?>
                    <a class="boton secundario" href="ver.php">Cancelar</a>
                <?php } ?>
            </div>

        </form>
    </div>

</div>

<?php
    mysqli_close($conectar);
    include __DIR__ . '/../../includes/footer.php';
?>
