<?php

require_once('con_tipo_cambio.class.php');

$gestion = new CON_TIPO_CAMBIO();

if ($_GET['tarea'] <> "") {
    if (!($gestion->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($gestion->datos()) {
                $gestion->insertar_tcp();
            } else {
                $gestion->formulario_tcp('blanco');
            }
            break;
        }

    case 'MODIFICAR': {
            if ($gestion->datos()) {
                $gestion->modificar_tcp();
            } else {

                $gestion->formulario_modificar('cargar');
            }
            break;
        }
    default: $gestion->dibujar_busqueda();
}
?>