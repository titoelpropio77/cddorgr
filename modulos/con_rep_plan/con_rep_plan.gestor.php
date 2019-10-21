<?php

	require_once('con_rep_plan.class.php');
	
	$est = new CON_REP_PLAN();	
        if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
        }
	$est->dibujar_busqueda();
	
?>