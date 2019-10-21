<?php

$frame = $_POST[frame];
if ($frame != 'false') {
    date_default_timezone_set("America/La_Paz");
    require_once('clases/pagina.class.php');
    require_once("clases/busqueda.class.php");
    require_once("clases/formulario.class.php");
    require_once('clases/coneccion.class.php');
    require_once('clases/conversiones.class.php');
    require_once('clases/usuario.class.php');
    require_once('clases/validar.class.php');
    require_once('clases/verificar.php');
    require_once('clases/funciones.class.php');
    require_once("clases/mytime_int.php");
    require_once('config/constantes.php');
    require_once('config/variables.php');
    require_once ("clases/Ticket.php");
} else {
    require_once('clases/pagina.class.php');
    require_once("clases/formulario.class.php");
    require_once("clases/busqueda.class.php");
    require_once('clases/coneccion.class.php');
    require_once('clases/usuario.class.php');
    require_once('clases/funciones.class.php');
    require_once('clases/conversiones.class.php');
    require_once('config/constantes.php');
    require_once('config/variables.php');
}

ADO::$token = date('YmdHis');
//require_once('config/zona_horaria.php');
//ini_set('short_open_tag', 0);
//ini_set('display_errors', 1);
//	require_once('clases/pagina.class.php');
//	require_once("clases/busqueda.class.php");
//	require_once("clases/formulario.class.php");
//	require_once('clases/coneccion.class.php');
//	require_once('clases/conversiones.class.php');
//	require_once('clases/usuario.class.php');
//	require_once('clases/validar.class.php');
//	require_once('clases/verificar.php');
//	require_once('clases/funciones.class.php');
//	require("clases/mytime_int.php");
//	require_once('config/constantes.php');
//	require_once('config/zona_horaria.php');
//****//



$modulo = $_GET['mod'];



$tarea = $_GET['tarea'];



$pagina = new PAGINA();
$pagina->verificar();
if ($frame != 'false') {
    $pagina->abrir_contenido();
}
//if($_SESSION[id]!='admin'){
//    return;
//}
//FUNCIONES::print_pre($_SESSION);


$b = false;

if (isset($_REQUEST['ticket'])) {
    $_SESSION[suc_id]=  FUNCIONES::atributo_bd_sql("select usu_suc_id as campo from ad_usuario where usu_id='$_SESSION[id]'");
    if (Ticket::fueAtendido($_REQUEST['ticket'])) {
//        echo "ya fue atendido este ticket";
        require_once('clases/MensajeDoble.php');
        mostrar_mensaje($_REQUEST['ticket']);
    } else {
        $b = true;
    }
} else {
    $b = true;
}
if ($b) {
    $archivo = "modulos/$modulo/$modulo.gestor.php";
    if (is_file($archivo))
        require_once($archivo);
    else
        echo "<br><br><center><b>NO EXISTE EL ARCHIVO</b> --> modulos/$modulo/$modulo.gestor.php</center>";
}
if ($frame != 'false') {
    $pagina->cerrar_contenido();
}
?>