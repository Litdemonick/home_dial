<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Home Dial</title>
<link rel="stylesheet" href="/home_dial/assets/css/style.css">
</head>
<body>

<div class="topbar">
    <div class="titulo">Home Dial</div>
    <div class="usuario">
        <?php echo mostrar($_SESSION['nombre_usuario']); ?>
        <span class="etiqueta-rol"><?php echo mostrar($_SESSION['rol_nombre']); ?></span>
        - <a href="/home_dial/logout.php" style="color:#fff;">salir</a>
    </div>
</div>
