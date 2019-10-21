<?php

	require_once('repcuentasx.class.php');
	
	$est = new REPDEUDASINTERNOS();
	
	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>