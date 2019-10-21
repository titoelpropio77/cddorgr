<?php

require_once('actualizar_lote.class.php');

$caja = new ACTUALIZAR_LOTE();

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
                $caja->listado_busqueda_lote();
//                if($_POST[tipo_tran]=='venta'){
//                    echo "<b style='font-size:16px'>LISTADO DE VENTAS</b>";
//                    
//                }else{
//                    echo "<b style='font-size:16px'>LISTADO DE RESERVAS</b>";
//                    $caja->listado_busqueda_reserva();
//                }
            }
            break;
        }
    

    default: {

        }
}
?>