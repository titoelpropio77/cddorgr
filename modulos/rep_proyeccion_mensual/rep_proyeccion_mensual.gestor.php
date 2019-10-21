<?php

require_once('clases/reporte.class.php');
require_once('rep_proyeccion_mensual.class.php');;

$est = new REP_PROYECCION_MENSUAL();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
