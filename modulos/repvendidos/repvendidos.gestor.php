<?php

	require_once('repvendidos.class.php');
	
	$est = new REPVENDIDOS();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>