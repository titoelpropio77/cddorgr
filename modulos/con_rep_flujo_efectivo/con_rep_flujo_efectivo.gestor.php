<?php

	require_once('con_rep_flujo_efectivo.class.php');
	
	$est = new CON_REP_FLUJO_EFECTIVO();
	if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
        }
	$est->dibujar_busqueda();
	
?>