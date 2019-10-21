<?php

require_once '../Factura.php';

if ($_POST['accion'] == 'imprimir' && $_POST['factura_id']) {
	procesar_imprimir($_POST['factura_id']);
}


function procesar_imprimir($factura_id) {
	$sql_factura = "select * from fac_factura where fac_id=$factura_id";
	$conexion = new Factura();
	echo $sql_factura;exit;
	$conexion->ejecutar($sql_factura);
	$factura = $conexion->get_objeto();
	if ($factura->fac_id) {
		$cantidad_impresion = $factura->fac_nro_impresion;
		$cantidad_impresion++;
		$sql_update_factura = "update fac_factura set fac_nro_impresion=$cantidad_impresion where fac_id=$factura_id";
		echo $sql_update_factura;
	}
	echo 'factura: ' . $factura_id;
}