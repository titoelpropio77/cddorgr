<?php
require_once('con_rep_retenciones.class.php');
$est = new CON_REP_RETENCIONES();
if (!(isset($_POST['moneda_reporte']))) {
    $_POST['moneda_reporte'] = 1;
}
$est->dibujar_busqueda();
?>