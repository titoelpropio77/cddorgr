<?php
//ini_set('display_errors', 'On');

// echo "shit";
// exit();

//require_once('clases/pagina.class.php');
//require_once("clases/busqueda.class.php");
//require_once("clases/formulario.class.php");
require_once('bitacora.class.php');
require_once('session.class.php');
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
//require_once("clases/mytime_int.php");
require_once('config/constantes.php');
//require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');

$hoy = date('Y-m-d');

$sql_pdo = "SELECT *  FROM `interno_deuda_producto` WHERE `idpr_venta_id` = 9898 and idpr_estado='Pendiente' order by idpr_num_correlativo asc";

echo "<p>$sql_pdo</p>";
$cuotas = FUNCIONES::objetos_bd_sql($sql_pdo);

$fecha_ini = "2018-10-28";
for ($i = 0; $i < $cuotas->get_num_registros(); $i++) {
	$cuota = $cuotas->get_objeto();
	$sql_upd = "update interno_deuda_producto set idpr_fecha_programada='$fecha_ini' where idpr_id='$cuota->idpr_id'";
	echo "<p>$sql_upd;</p>";
	$fecha_ini = FUNCIONES::sumar_meses($fecha_ini);
	$cuotas->siguiente();
}

?>