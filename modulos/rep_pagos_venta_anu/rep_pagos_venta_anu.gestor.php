<?php

require_once('clases/reporte.class.php');
require_once('rep_pagos_venta_anu.class.php');

$est = new rep_pagos_venta_anu();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
