<?php

require_once('proforma2.class.php');

$proforma2 = new PROFORMA2();

if ($_GET['tarea'] <> "") {
    if (!($proforma2->verificar_permisos($_GET['tarea']))) {
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
                $proforma2->emergente();
            } else {
                if ($proforma2->datos()) {
                    $proforma2->insertar_tcp();
                } else {
                    $proforma2->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
//            $reserva->cargar_datos();
//            $reserva->formulario_tcp('ver');
            $proforma2->nota_reserva($_GET['id']);
            break;
        }
    case 'MODIFICAR': {
            if ($proforma2->datos()) {
                $proforma2->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $proforma2->cargar_datos();
                }
                $proforma2->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['usu_id'])) {
                if (trim($_POST['usu_id']) <> "") {
                    $proforma2->eliminar_tcp();
                } else {
                    $proforma2->dibujar_busqueda();
                }
            } else {
                $proforma2->formulario_confirmar_eliminacion();
            }

            break;
        }
    case 'ANULAR': {
            if($_POST[fecha]){
                $proforma2->anular();
            }else{
                $proforma2->frm_anular();
            }
            break;
        }
    case 'CONCRETAR VENTA': {
            $proforma2->concretar_venta_reserva();
            break;
        }
    case 'ANTICIPOS RESERVA': {
            if ($_GET['acc'] == 'ver_comprobante') {
                $proforma2->ver_comprobante($_GET['id']);
            } else {
                if ($_GET['acc'] == 'anular') {
                    $proforma2->anular_anticipo($_GET['id']);
                } else {
                    if ($proforma2->datos_anticipo()) {
                        $proforma2->guardar_anticipo();
                    } else {
                        $proforma2->formulario_anticipo('cargar');
                        $proforma2->dibujar_encabezado_anticipo();
                        $proforma2->mostrar_busqueda_anticipo();
                    }
                }
            }

            break;
        }
    case 'DEVOLVER': {
            if ($_POST) {
                $proforma2->guardar_devolucion();
            } else {
                $proforma2->formulario_devolucion('cargar');                
            }
            break;
        }
    case 'VER DEVOLUCION': {            
                $proforma2->ver_nota_devolucion($_GET[id]);
            break;
        }
    case 'VENCIDOS': {          
                $proforma2->vencidos();
//                echo 'VENCIDOS';
            break;
        }
    case 'CAMBIAR FECHA': {          
                $proforma2->ampliar_fecha();
//                echo 'VENCIDOS';
            break;
        }
    default: $proforma2->dibujar_busqueda();
}
?>