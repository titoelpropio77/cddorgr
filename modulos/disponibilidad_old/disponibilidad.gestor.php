<?php
	require_once('disponibilidad.class.php');
	require_once('modulos/uv/uv.plano.php');
	$bal = new DISPONIBILIDAD();
	$plano = new Plano();
	if($_POST['oculto']=='ok'){
		$_GET['id'] = $_POST['urb_id'];
		$plano->plano_ver();
	}
	else
	{
		$bal->formulario();
	}
?>