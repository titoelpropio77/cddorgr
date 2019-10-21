<?php

require_once('bloquear_terreno.class.php');

$bloquear_terreno = new BLOQUEAR_TERRENO();

if ($_GET['tarea'] <> "") {
    if (!($bloquear_terreno->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($_GET['acc'] == 'Emergente') {
                $bloquear_terreno->emergente();
            } else {

                if ($bloquear_terreno->datos()) {
                    $bloquear_terreno->insertar_tcp();
                } else {
                    $bloquear_terreno->formulario_tcp('blanco');
                }
            }

            break;
        }
    case 'VER': {
            $bloquear_terreno->cargar_datos();

            $bloquear_terreno->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($bloquear_terreno->datos()) {
                $bloquear_terreno->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $bloquear_terreno->cargar_datos();
                }
                $bloquear_terreno->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['usu_id'])) {
                if (trim($_POST['usu_id']) <> "") {
                    $bloquear_terreno->eliminar_tcp();
                } else {
                    $bloquear_terreno->dibujar_busqueda();
                }
            } else {
                $bloquear_terreno->formulario_confirmar_eliminacion();
            }

            break;
        }

    case 'ANULAR': {

            $bloquear_terreno->anular();

            break;
        }

    default: $bloquear_terreno->dibujar_busqueda();
}
?>