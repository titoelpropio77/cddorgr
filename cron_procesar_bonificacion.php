<?php

ini_set('display_errors', 'On');
echo "shit";
//exit();

/*
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
 */

require_once('clases/bitacora.class.php');
require_once('clases/session.class.php');
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');

require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once('config/constantes.php');

require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');
require_once('config/constantes.php');
require_once('clases/log.class.php');
date_default_timezone_set("America/La_Paz");

$path = getcwd();
$log = new LOG($path, 'bonificacion');

//$hoy = date('Y-m-d');
 $hoy = "2017-12-01";

$sql_pdo = "select * from con_periodo 
where pdo_fecha_inicio<='$hoy' and pdo_fecha_fin>='$hoy'
and pdo_eliminado='No'";
$log->escribir_entrada("INICIO: 'Procesar Bonificacion'.", "INFO");

$periodo = FUNCIONES::objeto_bd_sql($sql_pdo);
$log->escribir_entrada("$sql_pdo", "SQL-SCRIPT");
echo "<p>$sql_pdo</p>";
//
$s = "0";
if ($periodo) {
            
    $s .= "-1";
    $pdo_id = $periodo->pdo_id;
    $_SESSION[id] = "cron";
    $_SESSION[ges_id] = $periodo->pdo_ges_id;
    $comision_periodo = FUNCIONES::objeto_bd_sql("select * from comision_periodo 
    where pdo_id = '$pdo_id'");
//    
    if ($comision_periodo) {
        $s .= "-2";
        if ($comision_periodo->pdo_estado == 'Abierto') {            
            $s .= "-3";
                        
            $sql = "update comision_periodo set pdo_usu_mod='{$_SESSION[id]}',
            pdo_fecha_mod='$hoy'
            where pdo_id = '$pdo_id'";
           
            FUNCIONES::bd_query($sql);
            $log->escribir_entrada("$sql", "SQL-SCRIPT");
            
            $data = array('pdo_id' => $pdo_id, 'origen' => 'modificar', 'vdo_id' => 0);
            MLM::generar_bonos($data);
        }
    } else {
        
        $s .= "-4";
        
        $sql_ins = "insert into comision_periodo(pdo_id,pdo_usu_cre,pdo_fecha_cre)
        values('$pdo_id','$_SESSION[id]','$hoy')";
        FUNCIONES::bd_query($sql_ins);
        $log->escribir_entrada("$sql_ins", "SQL-SCRIPT");
                        
        $data = array('pdo_id' => $pdo_id, 'origen' => 'agregar', 'vdo_id' => 0);
        MLM::generar_bonos($data);
    }        
    
    $cant_coms = FUNCIONES::atributo_bd_sql("select count(com_id)as campo from comision "
        . "where com_estado in('Pendiente','Pagado') and com_pdo_id='$periodo->pdo_id'") * 1;
    
    $log->escribir_entrada("$cant_coms comisiones generadas para el periodo $periodo->pdo_descripcion.", "INFO");
} else {
    $hoy = FUNCIONES::get_fecha_latina($hoy);
    $log->escribir_entrada("No existe el periodo de la fecha proporcionada($hoy).", "ERROR");
}


$log->escribir_entrada("RASTRO:$s", "TRACKING");
echo "<p>rastro:$s</p>";
$log->escribir_entrada("FIN: 'Procesar Bonificacion'.", "INFO");
$log->notificar_a_correo();
?>