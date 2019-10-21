<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://cdd.sistema.com.bo/cron_procesar_bonificacion.php"); 
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xxx = curl_exec($ch);
echo $xxx;
curl_close($ch);
?>
