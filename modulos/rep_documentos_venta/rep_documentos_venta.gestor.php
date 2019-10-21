<?php

require_once('clases/reporte.class.php');
require_once('rep_documentos_venta.class.php');

$est = new REP_DOCUMENTOS_VENTA();

if ($est->verificar_permisos('ACCEDER')) {
    $est->procesar_reporte();
}
