<?php
	//$_GET['id'] = "1";
	$_GET['mapa'] = "puplico"; 
	
	require_once('uv.plano.php');
	$plano = new Plano();
	$plano->plano_ver();
?>