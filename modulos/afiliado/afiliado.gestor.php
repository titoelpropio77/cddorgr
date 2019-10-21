<?php
ini_set('display_errors', 'On');
require_once('afiliado.class.php');

$vendedor = new AFILIADO();

if ($_GET['tarea'] <> "") {
    if (!($vendedor->verificar_permisos($_GET['tarea']))) {
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
                $vendedor->emergente();
            } else {

                if ($vendedor->datos()) {
                    $vendedor->insertar_tcp();
                } else {
                    $vendedor->formulario_tcp('blanco');
                }
            }

            break;
        }
    case 'VER': {
            $vendedor->cargar_datos();

            $vendedor->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($vendedor->datos()) {
                $vendedor->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $vendedor->cargar_datos();
                }
                $vendedor->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['vdo_id'])) {
                if (trim($_POST['vdo_id']) <> "") {
                    $vendedor->eliminar_tcp();
                } else {
                    $vendedor->dibujar_busqueda();
                }
            } else {
                $vendedor->formulario_confirmar_eliminacion();
            }

            break;
        }
    case 'CUENTAS': {
            $vendedor->cuentas();
            break;
        }
    case 'PAGOS COMISIONES': {
            $vendedor->pagos_comisiones();

            break;
        }
        
    case 'VER HISTORIAL': {
            include_once 'afiliado_historial.class.php';
            $ah = new AFILIADO_HISTORIAL();
            $ah->historial();
            break;
        }
        
    case 'AUTORIZACION': {
            include_once 'afiliado_debito.class.php';
            $ah = new AFILIADO_DEBITO();
            $ah->autorizacion_debito();
            break;
        } 
        
    case 'DEBITO': {
            include_once 'afiliado_debito.class.php';
            $ah = new AFILIADO_DEBITO();            
            $ah->debito();
            break;
        }     

    default: $vendedor->dibujar_busqueda();
}
?>