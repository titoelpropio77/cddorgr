<?php

	require_once('repterrenos.class.php');
	
	$est = new REPTERRENOS();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>