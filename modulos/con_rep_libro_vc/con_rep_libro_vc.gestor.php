<?php

	require_once('con_rep_libro_vc.class.php');
	
	$est = new CON_REP_LIBRO_VC();
        if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=1;
        }
	if(isset($_GET['davinci']) && $_GET['davinci']=='ok'){
            $est->generar_davinci();
        }else{
            $est->dibujar_busqueda();
        }
	
?>