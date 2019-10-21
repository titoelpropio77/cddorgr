<?php
require_once 'excel/reader.php';
require_once('gPoint.php');
require_once('../config/database.conf.php');

mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or
									die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);


$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read("excel/coordenadas.xls");

$filasTotal = $data->sheets[0]['numRows'];

echo "Total Filas = ".$filasTotal."<br>";


$puntos = new gPoint();

for ($i = 1; $i <= $filasTotal; $i++)
{
	list(
		$lote,
		$coordX,
		$coordY,
		$lot_id
	) = array(
		$data->sheets[0]['cells'][$i][1],
		$data->sheets[0]['cells'][$i][2],
		$data->sheets[0]['cells'][$i][3],
		$data->sheets[0]['cells'][$i][4]
	);
	
	$puntos->setUTM(trim($coordX),trim($coordY), 20);
	$puntos->convertTMtoLL();
	
	$latitud = $puntos->Lat();
	$longitud = $puntos->Long();
	
	if($lote == "C")
	{
		echo "Center: ".$lot_id." Latitud=".$latitud." Longitud=".$longitud."___________________________<br/>";
		
		$sql="UPDATE lote SET lot_latitud='".$latitud."', lot_longitud='".$longitud."' WHERE lot_id='".$lot_id."'";
		$resultado = mysql_query($sql);
		
	} else {
		echo "lote: ".$lot_id." Latitud=".$latitud." Longitud=".$longitud."<br/>";
		
		$sql="INSERT INTO lote_cordenada (lco_lot_id, lco_latitud, lco_longitud ) VALUES ('".$lot_id."', '".$latitud."', '".$longitud."')";
		$resultado = mysql_query($sql);
	}
}
?>