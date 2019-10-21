<?php

require_once('con_cajero.class.php');

$con_cajero = new CON_CAJERO();

if ($_GET['tarea'] <> "") {
    if (!($con_cajero->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($con_cajero->datos()) {
                $con_cajero->insertar_tcp();
            } else {
                $con_cajero->formulario_tcp('blanco');
            }

            break;
        }
    case 'VER': {
            $con_cajero->cargar_datos();

            $con_cajero->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {

            if ($con_cajero->datos()) {
                $con_cajero->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_cajero->cargar_datos();
                }
                $con_cajero->formulario_tcp('cargar');
            }

            break;
        }
    case 'ELIMINAR': {

            if (isset($_POST['cja_usu_id'])) {
                if (trim($_POST['cja_usu_id']) <> "") {
                    $con_cajero->eliminar_tcp();
                } else {
                    $con_cajero->dibujar_busqueda();
                }
            } else {
                $con_cajero->formulario_confirmar_eliminacion();
            }


            break;
        }

    default:
        if ($con_cajero->verificar_permisos('ACCEDER')) {
            $con_cajero->dibujar_busqueda();
        } else {
            ?>
            <script>
                location.href = "log_out.php";
            </script>
            <?php

        }
}
?>