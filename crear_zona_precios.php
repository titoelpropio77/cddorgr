<?php
ini_set('display_errors', 'On');
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once('config/constantes.php');



$zonas = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,
26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,
53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,
80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,
105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,
125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,
145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,
165,166,167,168,169,170);

$conec = new ADO();
foreach ($zonas as $zon_id) {
    $sql_ins = "insert into zona_precio(
        zpr_zon_id,zpr_meses_min,zpr_meses_max,zpr_precio,zpr_clave
    )values";
    
    $s_ins = $sql_ins . "('$zon_id','1','60','0','1')";
    
    $conec->ejecutar($s_ins);
    
    $s_ins = $sql_ins . "('$zon_id','61','72','0','2')";
    
    $conec->ejecutar($s_ins);
    
    $s_ins = $sql_ins . "('$zon_id','73','84','0','3')";
    
    $conec->ejecutar($s_ins);
    
    $s_ins = $sql_ins . "('$zon_id','85','96','0','4')";
    
    $conec->ejecutar($s_ins);
        
}
?>