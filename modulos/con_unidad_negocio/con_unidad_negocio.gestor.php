<?php

require_once('con_unidad_negocio.class.php');

$con_unidad_negocio = new con_unidad_negocio();

if ($_GET['tarea'] <> "") {
    if (!($con_unidad_negocio->verificar_permisos($_GET['tarea']))) {
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
                $con_unidad_negocio->emergente();
            } else {
                if ($con_unidad_negocio->datos()) {
                    $con_unidad_negocio->insertar_tcp();
                } else {
                    $con_unidad_negocio->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_unidad_negocio->cargar_datos();

            $con_unidad_negocio->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_unidad_negocio->datos()) {
                $con_unidad_negocio->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_unidad_negocio->cargar_datos();
                }
                $con_unidad_negocio->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $con_unidad_negocio->eliminar_tcp();
                } else {
                    $con_unidad_negocio->dibujar_busqueda();
                }
            } else {
                $con_unidad_negocio->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_unidad_negocio->dibujar_busqueda();
}
?>