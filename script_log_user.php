<?php

session_start();
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

if ($_GET[token] != 'admorange') {
    return;
}

cambiar_usuario();

function cambiar_usuario() {
    
    if ($_POST) {
        guardar_cambiar_usuario();
    }else{
        frm_cambiar_usuario();
    }
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

function guardar_cambiar_usuario(){
    if($_POST[usu_id]){
        $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_POST[usu_id]'");
        if($usuario){
            $_SESSION[id] = $usuario->usu_id;
            $_SESSION[usu_per_id] = $usuario->usu_per_id;
            $_SESSION[usu_gru_id] = $usuario->usu_gru_id;
            $_SESSION[nombre] = $usuario->usu_id;
//            $_SESSION[foto] = ;
            $_SESSION[nombre_completo] = FUNCIONES::interno_nombre($usuario->usu_per_id);
            $_SESSION[suc_id] = $usuario->usu_suc_id;
        }else{
            echo "NO EXISTE EL USUARIO<BR>";
        }
    }else{
        echo "INGRESE EL USUARIO<BR>";
    }
    frm_cambiar_usuario();
}

function frm_cambiar_usuario(){
    ?>
    <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="" name="frm_sentencia">
        Usuario<input type="text" name="usu_id">
        <input type="submit" value="Cambiar">
    </form>
<?php
}