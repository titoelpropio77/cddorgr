<?php

require_once('gestion.class.php');

$gestion = new GESTION();

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
            if ($_GET['acc'] == 'Emergente') {
                $gestion->emergente();
            } else {

                if ($gestion->datos()) {
                    $gestion->insertar_tcp();
                } else {
                    $gestion->formulario_tcp('blanco');
                }
            }

            break;
        }
    case 'ESTRUCTURA': {
            if ($_GET['acc'] == 'GUARDAR_DETALLE_CF') {
                $gestion->guardar_detalles_cf();
            }else{
                $gestion->formulario_tabs();
            }
            break;
        }
    case 'VER': {
            $gestion->cargar_datos();
            $gestion->formulario_tcp('ver');
            break;
        }
    case 'MODIFICAR': {
            if ($gestion->datos()) {
                $gestion->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $gestion->cargar_datos();
                }
                $gestion->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ges_id'])) {
                if (trim($_POST['ges_id']) <> "") {
                    $gestion->eliminar_tcp();
                } else {
                    $gestion->dibujar_busqueda();
                }
            } else {
                $gestion->formulario_confirmar_eliminacion();
            }
            break;
        }

    case 'PERIODOS': {
            if(!isset($_GET['id'])){
                $_GET['id']=$_SESSION['ges_id'];
            }
            if ($_GET['acc'] == 'MODIFICAR_PERIODO') {
                if ($gestion->datos_periodo()) {
                    $gestion->modificar_periodo();
                } else {
                    if (!($_POST)) {
                        $gestion->cargar_datos_periodo();
                    }
                    $gestion->formulario_tcp_periodo('cargar');
                }
            } else {
                if ($_GET['acc'] == 'ELIMINAR_PERIODO') {
                    $gestion->eliminar_periodo($_GET['peri_id']);
                }
                if ($gestion->datos_periodo()) {
                    $gestion->guardar_periodo();
                }
                $gestion->formulario_tcp_periodo('cargar');
                $gestion->dibujar_encabezado_periodo();
                $gestion->mostrar_busqueda_periodo();
            }
            break;
        }
    case 'CUENTAS': {
            $gestion->formulario_tcp_cuenta('cargar');
            break;
        }

    default: $gestion->dibujar_busqueda();
}
?>