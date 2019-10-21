<?php

	require_once('con_rep_balance_general.class.php');
	
	$est = new CON_REP_BALANCE_GENERAL();
	if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
        }
	$est->dibujar_busqueda();
	
?>