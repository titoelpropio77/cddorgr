<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';

date_default_timezone_set("America/La_Paz");

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read("pagos_ana_isabel.xls");
$colum = 0;
$filasTotal = $data->sheets[$colum]['numRows'];
//$columnas = $data->sheets[$colum]['numCols'];
echo "'$filasTotal'" . "<br>";
//return;
//echo $columnas."<br><br>";
//return;
$list_pagos = array();
$i = 3;
while ($i <= $filasTotal) {
    $cuota= trim($data->sheets[$colum]['cells'][$i][1]);
    $fecha= trim($data->sheets[$colum]['cells'][$i][2]);
    $sql_up="update tmp_plan set fecha='$fecha' where ven_id= '148'and cuota='$cuota' ";
    echo $sql_up.';<br>'; 
    $i++;
}
//echo'<pre>';
//print_r($list_pagos);
//echo'</pre>';



?>