<?php
require_once('clases/reporte.class.php');
require_once('represumen_reserva.class.php');

$est = new REPRESUMEN_RESERVA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
