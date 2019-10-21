<?php
require_once('clases/reporte.class.php');
require_once('rep_venta_nota.class.php');

$est = new REP_VENTA_NOTA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
?>