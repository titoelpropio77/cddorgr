<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$usuario = new USUARIO;
if (!($usuario->get_aut() == _sesion)) {
    echo '{"response":"error", "mensaje":"No tiene permisos"}';
    return;
}

$dir=$_POST['dir'];
require_once 'sueltos/'.$dir;
?>
