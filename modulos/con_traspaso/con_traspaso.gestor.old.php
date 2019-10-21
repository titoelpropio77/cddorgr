<?php

require_once('con_traspaso.class.php');

$con_traspaso = new con_traspaso();

if ($_GET['tarea'] <> "") {
    if (!($con_traspaso->verificar_permisos($_GET['tarea']))) {
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
                $con_traspaso->emergente();
            } else {
                if ($con_traspaso->datos()) {
                    $con_traspaso->insertar_tcp();
                } else {
                    $con_traspaso->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_traspaso->cargar_datos();

            $con_traspaso->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_traspaso->datos()) {
                $con_traspaso->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_traspaso->cargar_datos();
                }
                $con_traspaso->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['tras_id'])) {
                if (trim($_POST['tras_id']) <> "") {
                    $con_traspaso->eliminar_tcp();
                } else {
                    $con_traspaso->dibujar_busqueda();
                }
            } else {
                $con_traspaso->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_traspaso->dibujar_busqueda();
}
?>