<?php
    session_start();
    require_once __DIR__ . '/includes/funciones.php';
    if (isset($_SESSION['usuario_id'])) {
        header("Location: modules/dashboard.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Home Dial - Iniciar sesion</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="topbar">
    <div class="titulo">Home Dial</div>
</div>

<div class="contenido">

    <?php if (isset($_GET['error'])) { ?>
        <div class="aviso excesiva"><?php echo mostrar($_GET['error']); ?></div>
    <?php } ?>

    <?php if (isset($_GET['registrado'])) { ?>
        <div class="aviso favorable">Cuenta creada correctamente, ya puede iniciar sesion.</div>
    <?php } ?>

    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-top:15px;">

        <div class="panel" style="flex:1; min-width:280px;">
            <h2>Iniciar sesion</h2>
            <form class="formulario" action="modules/auth/login.php" method="POST">
                <label>Usuario</label>
                <input type="text" name="usuario" placeholder="ingrese su usuario" required value="<?php echo isset($_GET['usuario']) ? mostrar($_GET['usuario']) : ''; ?>">

                <label>Contraseña</label>
                <input type="password" name="clave" placeholder="ingrese su contraseña" required>

                <div class="botones">
                    <button type="submit" class="boton">Entrar</button>
                </div>
            </form>
        </div>

        <div class="panel" style="flex:1; min-width:280px;">
            <h2>Registrarme como paciente</h2>
            <form class="formulario" action="modules/auth/login.php" method="POST">
                <input type="hidden" name="accion" value="registro">

                <label>Nombre</label>
                <input type="text" name="nombre" placeholder="su nombre" required value="<?php echo isset($_GET['nombre']) ? mostrar($_GET['nombre']) : ''; ?>">

                <label>Apellido</label>
                <input type="text" name="apellido" placeholder="su apellido" required value="<?php echo isset($_GET['apellido']) ? mostrar($_GET['apellido']) : ''; ?>">

                <label>Usuario</label>
                <input type="text" name="usuario_nuevo" placeholder="elija un usuario" required value="<?php echo isset($_GET['usuario_nuevo']) ? mostrar($_GET['usuario_nuevo']) : ''; ?>">

                <label>Contraseña</label>
                <input type="password" name="clave_nueva" placeholder="minimo 6 caracteres" minlength="6" required>

                <label>Confirmar contraseña</label>
                <input type="password" name="clave_confirmar" placeholder="repita la contraseña" minlength="6" required>

                <div class="botones">
                    <button type="submit" class="boton secundario">Crear cuenta</button>
                </div>
            </form>
        </div>

    </div>

</div>

<footer class="pie">Home Dial - DSVII 2026</footer>

</body>
</html>
