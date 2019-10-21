<?php

require_once('clases/reporte.class.php');
require_once('represumen_venta.class.php');

$est = new REPRESUMEN_VENTA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
