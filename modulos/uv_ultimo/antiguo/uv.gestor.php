<?php

require_once('uv.class.php');
require_once('uv.plano.php');

$uv = new UV();
$plano = new Plano();

if ($_GET['tarea'] <> "") {
    if (!($uv->verificar_permisos($_GET['tarea']))) {
        if (($_GET['tarea'] != "CONFIGURACION") && ($_GET['tarea'] != "PUNTOS") && ($_GET['tarea'] != "PLANO")) {
            ?>
            <script>
                location.href = "log_out.php";
            </script>
            <?php

        }
    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            if ($_GET['acc'] == 'Emergente') {
                $uv->emergente();
            } else {
                if ($uv->datos()) {
                    $uv->insertar_tcp();
                } else {
                    $uv->formulario_tcp('blanco');
                }
            }


            break;
        }
    case 'VER': {
            $uv->cargar_datos();

            $uv->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {

            if ($uv->datos()) {
                $uv->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $uv->cargar_datos();
                }
                $uv->formulario_tcp('cargar');
            }

            break;
        }
    case 'ELIMINAR': {
            if (isset($_POST['urb_id'])) {
                if (trim($_POST['urb_id']) <> "") {
                    $uv->eliminar_tcp();
                } else {
                    $uv->dibujar_busqueda();
                }
            } else {
                $uv->formulario_confirmar_eliminacion();
            }
            break;
        }
    case 'ESTRUCTURA': {
            $uv->ver_estructura();
            break;
        }
    case 'ZONAS': {
            $uv->ver_zonas();
            break;
        }
    case 'UV': {
            $uv->ver_uv();
            break;
        }

    case 'CUENTAS': {
            if ($_POST[form] == 'ok') {
                $uv->modificar_cuentas();
            } else {
                $uv->ver_cuentas();
            }
            break;
        }
    //======= TAREAS PLANO =====//
    case 'CONFIGURACION': {
            if (isset($_POST['formAcion'])) {
                if ($_POST['formAcion'] == "ACTUALIZAR") {
                    $plano->plano_configuracion_modificar();
                } else {
                    $plano->plano_configuracion_insertar();
                }
            } else {
                $plano->plano_configuracion();
            }
            break;
        }
    case 'PUNTOS': {
            $plano->plano_puntos();
            break;
        }
    case 'PLANO': {
            $plano->plano_ver();
            break;
        }
    //======= TAREAS PLANO FIN =====//


    default:
        $uv->dibujar_busqueda();
        break;
}
?>