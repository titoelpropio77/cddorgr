<?php

require_once('con_brasil_traspaso.class.php');

$con_brasil_traspaso = new con_brasil_traspaso();

if ($_GET['tarea'] <> "") {
    if (!($con_brasil_traspaso->verificar_permisos($_GET['tarea']))) {
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
                $con_brasil_traspaso->emergente();
            } else {
                if ($con_brasil_traspaso->datos()) {
                    $con_brasil_traspaso->insertar_tcp();
                } else {
                    $con_brasil_traspaso->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_brasil_traspaso->cargar_datos();

            $con_brasil_traspaso->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_brasil_traspaso->datos()) {
                $con_brasil_traspaso->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_brasil_traspaso->cargar_datos();
                }
                $con_brasil_traspaso->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['btras_id'])) {
                if (trim($_POST['btras_id']) <> "") {
                    $con_brasil_traspaso->eliminar_tcp();
                } else {
                    $con_brasil_traspaso->dibujar_busqueda();
                }
            } else {
                $con_brasil_traspaso->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_brasil_traspaso->dibujar_busqueda();
}
?>