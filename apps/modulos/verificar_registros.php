<?php
//$ids = "441,442,442";
//$ids = "448,449,450";
//$ids = "452,453";
//$ids = "455,456";
//$ids = "457,458"; 
//$ids = "459,460"; 
//$ids = "451,462"; 

$ids = "10752,10753,10754,10755,10756";

echo  $ids."<br>";
echo "====== ad_usuario =========<br>";

$sql = "SELECT usu_per_id FROM  ad_usuario WHERE usu_per_id in(".$ids.") "; 
$result = mysql_query($sql);
$num = mysql_num_rows($result);
echo $num."<br>"; 
if ($num > 0) {
	for ($i = 0; $i < $num; $i++) {
		$objeto = mysql_fetch_object($result);
		echo $objeto->usu_per_id."<br/>";
	}
}

echo "====== proforma =========<br>";
$sql = "SELECT pro_id, pro_int_id FROM  proforma WHERE pro_int_id in(".$ids.") "; 
$result = mysql_query($sql);
$num = mysql_num_rows($result);
echo $num."<br>"; 
if ($num > 0) {
	for ($i = 0; $i < $num; $i++) {
		$objeto = mysql_fetch_object($result);
		echo $objeto->pro_id." ".$objeto->pro_int_id."<br/>";
	}
}

echo "====== reserva_terreno =========<br>";
$sql = "SELECT res_id, res_int_id FROM  reserva_terreno WHERE res_int_id in(".$ids.") "; 
$result = mysql_query($sql);
$num = mysql_num_rows($result);
echo $num."<br>"; 
if ($num > 0) {
	for ($i = 0; $i < $num; $i++) {
		$objeto = mysql_fetch_object($result);
		echo $objeto->res_id." ".$objeto->res_int_id."<br/>";
	}
}

echo "====== venta =========<br>";
$sql = "SELECT ven_id, ven_int_id FROM  venta WHERE ven_int_id in(".$ids.") "; 
$result = mysql_query($sql);
$num = mysql_num_rows($result);
echo $num."<br>"; 
if ($num > 0) {
	for ($i = 0; $i < $num; $i++) {
		$objeto = mysql_fetch_object($result);
		echo $objeto->ven_id." ".$objeto->ven_int_id."<br/>";
	}
}

