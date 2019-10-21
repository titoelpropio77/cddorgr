<?php

require_once('con_brasil_cdivisa.class.php');

$con_brasil_cdivisa = new con_brasil_cdivisa();

if ($_GET['tarea'] <> "") {
    if (!($con_brasil_cdivisa->verificar_permisos($_GET['tarea']))) {
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
                $con_brasil_cdivisa->emergente();
            } else {
                if ($con_brasil_cdivisa->datos()) {
                    $con_brasil_cdivisa->insertar_tcp();
                } else {
                    $con_brasil_cdivisa->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_brasil_cdivisa->cargar_datos();

            $con_brasil_cdivisa->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_brasil_cdivisa->datos()) {
                $con_brasil_cdivisa->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_brasil_cdivisa->cargar_datos();
                }
                $con_brasil_cdivisa->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['bdiv_id'])) {
                if (trim($_POST['bdiv_id']) <> "") {
                    $con_brasil_cdivisa->eliminar_tcp();
                } else {
                    $con_brasil_cdivisa->dibujar_busqueda();
                }
            } else {
                $con_brasil_cdivisa->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_brasil_cdivisa->dibujar_busqueda();
}
?>