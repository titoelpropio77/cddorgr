<?php

	require_once('repvendedor_ramificacion.class.php');

	$est = new REPVENDEDOR_RAMIFICACION();

	if($est->verificar_permisos('ACCEDER'))
	{
		$est->dibujar_busqueda();
	}
?>