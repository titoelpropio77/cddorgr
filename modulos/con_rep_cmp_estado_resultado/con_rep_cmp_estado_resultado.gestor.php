<?php

	require_once('con_rep_cmp_estado_resultado.class.php');
	
	$est = new CON_REP_CMP_ESTADO_RESULTADO();	
        if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
        }
	$est->dibujar_busqueda();
	
?>