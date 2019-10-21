<?php
require_once('clases/reporte.class.php');
require_once('rep_saldos_venta.class.php');

$est = new REP_SALDOS_VENTA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
?>