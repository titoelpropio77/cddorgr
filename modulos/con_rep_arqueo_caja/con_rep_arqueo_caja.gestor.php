<?php
    require_once('con_rep_arqueo_caja.class.php');
    $est = new CON_REP_ARQUEO_CAJA();
    $tform=isset($_GET['tform'])?$_GET['tform']:'';
    
    if(!(isset($_POST['moneda_reporte']))){
            $_POST['moneda_reporte']=2;
    }
    
    if($tform==""){
        $est->dibujar_busqueda();
    }else{
        $est->dibujar_popup();
    }
?>