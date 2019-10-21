<?php

	require_once('con_rep_libro_diario.class.php');
	
	$est = new CON_REP_LIBRO_DIARIO();
	$_POST['moneda_reporte']=2;
	$est->dibujar_busqueda();
	
?>