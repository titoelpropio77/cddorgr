<?php

require_once('clases/reporte.class.php');
require_once('rep_venta_cuotas.class.php');

$est = new REP_VENTA_CUOTAS();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
