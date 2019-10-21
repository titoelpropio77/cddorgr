<?php

require_once('clases/reporte.class.php');
require_once('repcontrol_mora.class.php');

$est = new REPCONTROL_MORA();

if ($est->verificar_permisos('ACCEDER')) {
    
    $est->procesar_reporte();
}
