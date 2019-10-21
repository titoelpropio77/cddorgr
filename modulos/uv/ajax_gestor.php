<?php

	header('Access-Control-Allow-Origin: *');
	$mensaje = "Error desconocido";
	$accion = "error";
	$datos = ""; 
	
	if(isset($_GET["ajaxTarea"])) {
		require_once('../../config/database.conf.php');
		mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or
												die("Could not connect: " . mysql_error());
		mysql_select_db(_BASE_DE_DATOS);
		
		// Gestionar tareas  manzano_puntos
		switch($_GET['ajaxTarea'])
		{
			case "actualizar":
				lote_actualizar($mensaje, $accion, $datos);
			break;
			case "agregar": 
				if(lote_verificar($_POST['lot_id'])) {
					lote_actualizar($mensaje, $accion, $datos);
				} else {
					lote_agregar($mensaje, $accion, $datos);
				}
			break;
			case "lotes":
				lote_array($mensaje, $accion, $datos);
			break;
			case "agregar_manzano":
				manzano_puntos($mensaje, $accion, $datos);
			break;
			case "agregar_equipamiento":
				agregar_equipamiento($mensaje, $accion, $datos);
			break;
		}
	}
	
	function lote_array(&$mensaje, &$accion, &$datos){
	
		$sql = "SELECT *, CAST(lot_nro as signed) as num FROM lote INNER JOIN uv ON (lot_uv_id  = uv_id)
				WHERE lot_uv_id ='".$_GET['uv_id']."' AND lot_man_id='".$_GET['man_id']."' ORDER BY num ASC";
				
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		for( $i=0; $i<$num;  $i++) 
		{
			$objeto = mysql_fetch_object($result);
			
			$verificar = lote_verificar($objeto->lot_id);
			$class = "";
			if($verificar){
				$class= "classOption"; 
			}
			$datos .='{"lot_id": "'.$objeto->lot_id.'","lot_nro":"'.$objeto->lot_nro.'", "class":"'.$class.'","estado": "'.$objeto->lot_estado.'"}'; 
			if($i < $num-1)
			{
				$datos .=',';
			}
		}
		$mensaje = "Lotes cargados correctamente"; 
		$accion = "correcto"; 
	}
	
	function lote_agregar(&$mensaje, &$accion, &$datos){
		if((is_numeric($_POST['lot_id'])) && ($_POST['lot_coorX'] !="") && ($_POST['lot_coorY'] !="")) { 
			$sql="insert into lote_cordenada_imagen (cor_lot_id, cor_lot_coorX, cor_lot_coorY, cor_urb_id, cor_uv_id) values ('".intval($_POST['lot_id'])."', '".intval($_POST['lot_coorX'])."', '".intval($_POST['lot_coorY'])."', '".$_POST['urb_id']."', '".$_POST['uv']."')";
			$result = mysql_query($sql);
			$datos = mysql_insert_id();
			$mensaje = "Coordenada agregado correctamente"; 
			$accion = "correcto"; 
		} else {
			$mensaje = "Por favor seleccione Uv, Manzano y Lote";
		}
	}
	
	function lote_actualizar(&$mensaje, &$accion, &$datos){
		if((is_numeric($_POST['lot_id'])) && ($_POST['lot_coorX'] !="") && ($_POST['lot_coorY'] !="")) {
			$sql="UPDATE lote_cordenada_imagen SET cor_lot_coorX='".intval($_POST['lot_coorX'])."', cor_lot_coorY='".intval($_POST['lot_coorY'])."',cor_uv_id='".$_POST['uv']."' WHERE cor_lot_id='".$_POST['lot_id']."'";
			$result = mysql_query($sql);
			$mensaje = "Coordenada actualizado correctamente"; 
			$accion = "correcto"; 
		} else {
			$mensaje = "Por favor seleccione Uv, Manzano y Lote";
		} 
	}
	
	function lote_estado($lot_id)
	{
		$sql="select * from lote where lot_id='".$lot_id."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$estado = "";  
		if($num > 0)
		{
			$objeto = mysql_fetch_object($result);
			$estado = $objeto->lot_estado;
		}
		return $estado;
	}
	
	function lote_verificar($lot_id){
		$sql="SELECT * FROM lote_cordenada_imagen WHERE cor_lot_id='".$lot_id."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if($num > 0){
			return true;
		} else {
			return false;
		}
	}
	
	function agregar_equipamiento(&$mensaje, &$accion, &$datos){ 
		if(($_POST['equipamiento'] !="") && ($_POST['lot_coorX'] !="") && ($_POST['lot_coorY'] !="")) {
			$sql="insert into urbanizacion_plano_equipamiento (ueq_urb_id, ueq_img_x, ueq_img_y, ueq_nombre) values ('".intval($_POST['urb_id'])."', '".intval($_POST['lot_coorX'])."', '".intval($_POST['lot_coorY'])."', '".$_POST['equipamiento']."')";
			$result = mysql_query($sql); 
			$mensaje = "Coordenada <b>equipamiento</b> agregado correctamente"; 
			$accion = "correcto";
		} else {
			$mensaje = "Por favor escriba el nombre del equipamiento o area verde";
		} 
	}
	
	function manzano_puntos(&$mensaje, &$accion, &$datos){ 
		if((is_numeric($_POST['man_id'])) && ($_POST['lot_coorX'] !="") && ($_POST['lot_coorY'] !="")) {
			$sql="UPDATE manzano SET man_img_x='".intval($_POST['lot_coorX'])."', man_img_y='".intval($_POST['lot_coorY'])."' WHERE man_id='".$_POST['man_id']."'";
			$result = mysql_query($sql);
			$mensaje = "Coordenada <b>manzano</b> actualizado correctamente"; 
			$accion = "correcto"; 
		} else {
			$mensaje = "Por favor seleccione el <b>manzano</b>";
		} 
	}
	
	// Enviar respuesta JSONP 
	$json = '{
		"respuesta":
		{
			"mensaje": "'.$mensaje.'",
			"accion": "'.$accion.'",
			"datos":['.$datos.']
		}
	}';
	
	echo '['.$json.']';
	
?>
