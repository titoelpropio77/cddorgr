<?php

	require_once('con_rep_libro_diario.class.php');
	
	$est = new CON_REP_LIBRO_DIARIO();
        if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
        }
	$est->dibujar_busqueda();
	
?>