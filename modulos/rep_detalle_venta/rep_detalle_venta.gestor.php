<?php

	require_once('rep_detalle_venta.class.php');
	
	$est = new rep_detalle_venta();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>