<?php

require_once('clases/reporte.class.php');
require_once('rep_pagos_extra.class.php');

$est = new REP_PAGOS_EXTRA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
