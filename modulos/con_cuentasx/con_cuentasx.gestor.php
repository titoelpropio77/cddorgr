<?php

require_once('con_cuentasx.class.php');
$cuentasx = new CON_CUENTASX();
if ($_GET['tarea'] <> "") {
    if (!($cuentasx->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}
switch ($_GET['tarea']) {
    case 'AGREGAR': {
            if ($cuentasx->datos()) {
                $cuentasx->insertar_tcp();
            } else {
                $cuentasx->formulario_tcp('blanco');
            }
            break;
        }
    case 'ANULAR': {
            $cuentasx->anular();
            break;
        }
    case 'VER': {
//            echo "select * from con_cuentasx where cux_id='".$_GET['id']."'";
            $cuentax=  FUNCIONES::objeto_bd_sql("select * from con_cuentasx where cux_id='".$_GET['id']."'");//();
//            _PRINT::pre($cuentax);
            if ($cuentax->cux_tipo_plan === 'Normal' || $cuentax->cux_tipo_plan === 'Especial') {
                $cuentasx->nota_de_cuentax($cuentax->cux_id);
            } elseif ($cuentax->cux_tipo_plan == "Sin Programar") {
                $cuentasx->comprobante_cuenta_x($cuentax->cux_id);
            }
            break;
        }
    case 'CUENTAS': {
            if ($cuentasx->obtener_tipo_plan($_GET['id']) == 'Sin Programar') {
                if ($_GET['acc'] == 'ver') {
                    $cuentasx->mostrar_nota_pago_historial($_GET['cup_id'], $_GET['cup_fecha']);
                } else {
                    if ($_GET['acc'] == 'anular') {
                        $cuentasx->anular_pago_anulado($_GET['cup_id']);
                    } else {
                        $cuentasx->cuentas();
                    }
                }
            } else {
                $cuentasx->pagos();
            }
            break;
        }
    case 'RESUMEN': {
            $cuentasx->mostrar_resumen();
            break;
        }
    default: $cuentasx->dibujar_busqueda();
        break;
}
?>