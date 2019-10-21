<?php

//require_once('venta.class.php');


require_once('venta_simulacion.class.php');

$est = new VENTA_SIMULACION();

if ($est->verificar_permisos('ACCEDER')) {
    $est->formulario_tcp('');
}
