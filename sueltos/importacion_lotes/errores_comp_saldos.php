<?php
// exit();
ini_set('display_errors','On');
require_once('mysql.php');
require_once 'excel/reader.php';
require_once('../../config/database.conf.php');

mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or
									die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);




$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
// $data->read("excel/lujan.xls");
$data->read("excel/ventas_error_lujan_2016.xls");
$filasTotal = $data->sheets[0]['numRows'];

echo "Total Filas = ".$filasTotal."<br>";

$urbanizacion=2;

$conec = new QUERY();

for ($i = 2; $i <= $filasTotal; $i++){
	list(
		$ven_id,
		$proyecto,
		$moneda,
		$estado_actual,
		$estado_calculado,
		$saldo_comercial,
		$saldo_contable,
	) = array(
		trim($data->sheets[0]['cells'][$i][1]),
		trim($data->sheets[0]['cells'][$i][2]),
		trim($data->sheets[0]['cells'][$i][3]),
		trim($data->sheets[0]['cells'][$i][4]),
		trim($data->sheets[0]['cells'][$i][5]),
		trim($data->sheets[0]['cells'][$i][6]),		
		trim($data->sheets[0]['cells'][$i][7]),		
	);

	$sql_ins = "insert into ventas_error(ve_ven_id, ve_proyecto, ve_moneda, ve_estado_actual, 
		ve_estado_calculado, ve_saldo_comercial, ve_saldo_contable, ve_gestion)
	values('$ven_id','$proyecto','$moneda','$estado_actual','$estado_calculado','$saldo_comercial','$saldo_contable','2016');";
		
	$conec->consulta($sql_ins);
} 

?>