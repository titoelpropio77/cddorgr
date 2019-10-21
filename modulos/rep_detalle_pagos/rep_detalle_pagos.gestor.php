<?php

	require_once('rep_detalle_pagos.class.php');
	
	$est = new rep_detalle_pagos();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>