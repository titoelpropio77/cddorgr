<?php

	require_once('represumen_asesores.class.php');
	
	$est = new REPRESUMEN_ASESORES();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>