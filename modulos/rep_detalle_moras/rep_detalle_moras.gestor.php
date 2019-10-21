<?php
    require_once('rep_detalle_moras.class.php');
    $est = new REP_DETALLE_MORAS();    
    if($est->verificar_permisos('ACCEDER')){
        $tform=isset($_GET['tform'])?$_GET['tform']:'';
        if($tform==""){
            $est->dibujar_busqueda();
        }else{
            $_POST[ven_id]=$_GET[id];
            $_POST[ven_codigo]='';
            $_POST['formu']='ok';
            $_POST['info']='ok';
            $est->dibujar_busqueda();
        }
    }
?>