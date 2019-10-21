<?php

require_once('ter_reversion_masiva.class.php');

$ter_reversion_masiva = new ter_reversion_masiva();

if ($_GET['tarea'] <> "") {
    if (!($ter_reversion_masiva->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
        $ter_reversion_masiva->agregar_reversion_masiva();
        break;
    }
    case 'VER': {
            $ter_reversion_masiva->mostrar_listado_reversion($_GET[id]);

            break;
        }

    default: $ter_reversion_masiva->dibujar_busqueda();
}
?>