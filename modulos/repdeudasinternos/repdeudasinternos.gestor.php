<?php

	require_once('repdeudasinternos.class.php');
	
	$est = new REPDEUDASINTERNOS();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>