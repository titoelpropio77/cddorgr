<?php

require_once('clases/reporte.class.php');
require_once('represumen_venta_informe.class.php');

$est = new REPRESUMEN_VENTA_INFORME();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
?>