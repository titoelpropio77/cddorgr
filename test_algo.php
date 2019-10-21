<?php
//ini_set('display_errors', 'On');

echo "shit";
exit();

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

$sql_pdo = "select pdo_id as campo from con_periodo 
where pdo_fecha_inicio>='$hoy' and pdo_fecha_fin<='$hoy'
and pdo_eliminado='No'";

echo "<p>$sql_pdo</p>";

$periodo = FUNCIONES::objeto_bd_sql($sql_pdo);

if ($periodo) {
    $pdo_id = $periodo->pdo_id;
    $_SESSION[id] = "cron";
    $_SESSION[ges_id] = $periodo->pdo_ges_id;
    $comision_periodo = FUNCIONES::objeto_bd_sql("select * from comision_periodo 
    where pdo_id = '$pdo_id'");
    
    if ($comision_periodo) {
        if ($comision_periodo->pdo_estado == 'Abierto') {            
            $data = array('pdo_id' => $pdo_id, 'origen' => 'modificar', 'vdo_id' => 0);
            MLM:generar_bonos($data);
        }
    } else {
        
        $data = array('pdo_id' => $pdo_id, 'origen' => 'agregar', 'vdo_id' => 0);
        MLM:generar_bonos($data);
    }        
}
?>