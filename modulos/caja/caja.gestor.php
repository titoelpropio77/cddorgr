<?php

require_once('caja.class.php');

$caja = new VENTA();

if ($_GET['tarea'] <> "") {
    if (!($caja->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php
    }
}

switch ($_GET['tarea']) {
    case 'ACCEDER': {
            $caja->formulario_busqueda();
            if ($_POST) { // LISTAR
                
                if($_POST[tipo_tran]=='venta'){
                    echo "<b style='font-size:16px'>LISTADO DE VENTAS</b>";
                    $caja->listado_busqueda_venta();
                }else if ($_POST[tipo_tran]=='reserva'){
                    echo "<b style='font-size:16px'>LISTADO DE RESERVAS</b>";
                    $caja->listado_busqueda_reserva();
                }else if ($_POST[tipo_tran]=='extra_pago'){
                    echo "<b style='font-size:16px'>LISTADO DE PAGOS EXTRA</b>";
                    $caja->listado_busqueda_extra_pago();
                }
            }
            break;
        }
    

    default: {

        }
}
?>