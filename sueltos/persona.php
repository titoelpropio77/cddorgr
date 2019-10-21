<?php
	require_once('../config/database.conf.php');
	mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
	mysql_select_db(_BASE_DE_DATOS);
	
	$input = $_REQUEST['input'];
	$len = strlen($input);

	$sql ="select int_id as id,CONCAT(int_nombre, ' ', int_apellido) as nombre from interno where CONCAT(int_nombre, ' ', int_apellido) like '%$input%' limit 15";
	$result = mysql_query($sql);	
	$num=mysql_num_rows($result);
	
	$aResults = array();

	for($j=0;$j<$num;$j++)
	{
		$objeto=mysql_fetch_object($result);
		
		
			$aResults[] = array( "id"=>($objeto->id) ,"value"=>htmlspecialchars(utf8_encode($objeto->nombre)), "info"=>htmlspecialchars(utf8_encode($objeto->nombre)) );
		
	}
	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header ("Pragma: no-cache"); // HTTP/1.0
	
	
	
	if (isset($_REQUEST['json']))
	{
		header("Content-Type: application/json");
	
		echo "{\"results\": [";
		$arr = array();
		for ($i=0;$i<count($aResults);$i++)
		{
			$arr[] = "{\"id\": \"".$aResults[$i]['id']."\", \"value\": \"".$aResults[$i]['value']."\", \"info\": \"\"}";
		}
		echo implode(", ", $arr);
		echo "]}";
	}
	else
	{
		header("Content-Type: text/xml");

		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?><results>";
		for ($i=0;$i<count($aResults);$i++)
		{
			echo "<rs id=\"".$aResults[$i]['id']."\" info=\"".$aResults[$i]['info']."\">".$aResults[$i]['value']."</rs>";
		}
		echo "</results>";
	}
?>