<?php

	require_once('con_rep_sumas_saldos.class.php');
	
	$est = new CON_REP_SUMAS_SALDOS();
	if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=1;
        }
	$est->dibujar_busqueda();
	
?>