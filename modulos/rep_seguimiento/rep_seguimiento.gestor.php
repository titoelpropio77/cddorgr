<?php

	require_once('rep_seguimiento.class.php');
	
	$est = new REP_SEGUIMIENTO();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>