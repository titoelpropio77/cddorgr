<?php

require_once('clases/reporte.class.php');
require_once('rep_pagos_proyeccion.class.php');

$est = new REP_PAGOS_PROYECCION();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
