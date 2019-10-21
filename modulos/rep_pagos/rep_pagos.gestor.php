<?php

require_once('clases/reporte.class.php');
require_once('rep_pagos.class.php');

$est = new REP_PAGOS();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
