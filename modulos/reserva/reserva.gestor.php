<?php

require_once('reserva.class.php');

$reserva = new RESERVA();
ADO::$modulo = $reserva->modulo;
ADO::$modulo_id = $_GET[id];
ADO::$tarea = $_GET[tarea];

if ($_GET['tarea'] <> "") {
    if (!($reserva->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            ADO::$modulo_id = -1;
            if ($_GET['acc'] == 'Emergente') {
                $reserva->emergente();
            } else {
                if ($reserva->datos()) {
                    $reserva->insertar_tcp();
                } else {
                    $reserva->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
//            $reserva->cargar_datos();
//            $reserva->formulario_tcp('ver');
            $reserva->nota_reserva($_GET['id']);
            break;
        }
    case 'MODIFICAR': {
            if ($reserva->datos()) {
                $reserva->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $reserva->cargar_datos();
                }
                $reserva->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['usu_id'])) {
                if (trim($_POST['usu_id']) <> "") {
                    $reserva->eliminar_tcp();
                } else {
                    $reserva->dibujar_busqueda();
                }
            } else {
                $reserva->formulario_confirmar_eliminacion();
            }

            break;
        }
    case 'ANULAR': {
            $reserva->anular();
            break;
        }
    case 'CONCRETAR VENTA': {
            $reserva->concretar_venta_reserva();
            break;
        }
    case 'PAGAR ANTICIPO': {
            if (!$_POST) {
                $reserva->formulario_anticipo('cargar');
            } else {
                $reserva->guardar_anticipo();
            }
            break;
        }
    case 'ANTICIPOS RESERVA': {
            if ($_GET['acc'] == 'ver_comprobante') {
                $reserva->ver_comprobante($_GET['id']);
            } else {
                if ($_GET['acc'] == 'anular') {
                    $pag_id = $_POST[pag_id];
                    if ($pag_id) {

                        $reserva->anular_anticipo($pag_id);
                    } else {
                        $reserva->dibujar_encabezado_anticipo();
                        $reserva->mostrar_busqueda_anticipo();
                    }
                } else {
                    if ($reserva->datos_anticipo()) {
//                        $reserva->guardar_anticipo();
                    } else {
//                        $reserva->formulario_anticipo('cargar');
                        $reserva->dibujar_encabezado_anticipo();
                        $reserva->mostrar_busqueda_anticipo();
                    }
                }
            }

            break;
        }
    case 'DEVOLVER': {
            $reserva->devolver_anticipo();
            break;
        }
    case 'VER DEVOLUCION': {
            $reserva->ver_nota_devolucion($_GET[id]);
            break;
        }
    case 'CAMBIAR LOTE': {
            $reserva->cambiar_lote();
            break;
        }
    case 'CAMBIAR PROPIETARIO': {
            $reserva->cambio_propietario();
            break;
        }
//    case 'CAMBIAR VENDEDOR': {
//            $reserva->cambiar_vendedor();
//            break;
//        }
    case 'CAMBIAR VENDEDOR': {
            include_once 'reserva_parametros.class.php';
            $res_params = new RESERVA_PARAMETROS();
            $res_params->parametros_varios();
            break;
        }
    case 'DOCUMENTOS': {
        require_once('reserva_negocio.class.php');
        $resneg = new RESERVA_NEGOCIO();
        $resneg->documentos();
        break;
    }
    default: $reserva->dibujar_busqueda();
}
?>