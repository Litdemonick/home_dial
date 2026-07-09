<div class="menu-container">
<?php if ($_SESSION['rol_id'] == 1) { ?>
    <a class="menu-link" href="/home_dial/modules/dashboard.php">Inicio</a>
    <a class="menu-link" href="/home_dial/modules/pacientes/listar.php">Pacientes</a>
    <a class="menu-link" href="/home_dial/modules/medicos/listar.php">Medicos</a>
    <a class="menu-link" href="/home_dial/modules/usuarios/listar.php">Usuarios</a>
    <a class="menu-link" href="/home_dial/modules/analitica/vista.php">Analitica</a>
    <a class="menu-link" href="/home_dial/modules/alertas/listar.php">Alertas</a>
<?php } elseif ($_SESSION['rol_id'] == 3) { ?>
    <a class="menu-link" href="/home_dial/modules/dashboard.php">Inicio</a>
    <a class="menu-link" href="/home_dial/modules/medicos/ver.php">Mi perfil</a>
    <a class="menu-link" href="/home_dial/modules/pacientes/listar.php">Mis pacientes</a>
    <a class="menu-link" href="/home_dial/modules/analitica/vista.php">Analitica</a>
    <a class="menu-link" href="/home_dial/modules/alertas/listar.php">Alertas</a>
<?php } else { ?>
    <a class="menu-link" href="/home_dial/modules/dashboard.php">Inicio</a>
    <a class="menu-link" href="/home_dial/modules/pacientes/ver.php">Mi ficha</a>
    <a class="menu-link" href="/home_dial/modules/recambios/registrar.php">Registrar balance</a>
    <a class="menu-link" href="/home_dial/modules/glucosa/registrar.php">Registrar glucosa</a>
    <a class="menu-link" href="/home_dial/modules/balance/vista.php">Mi balance</a>
    <a class="menu-link" href="/home_dial/modules/analitica/vista.php">Analitica</a>
    <a class="menu-link" href="/home_dial/modules/alertas/listar.php">Alertas</a>
<?php } ?>
</div>
