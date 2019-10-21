<?php

require_once('rep_venta_ingreso.class.php');

$est = new REP_VENTA_INGRESO();

if ($est->verificar_permisos('ACCEDER')) {
    $est->dibujar_busqueda();
}
?>