<?php

class ACCION { 
	var $usuDatos;
	var $encript;
	
	function ACCION(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos){
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->sac_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->sac_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]);
			}
		}
	}
	
	function validar($jsonDatos) {
		//texto,  numero,  real,  fecha,  mail.
		$num = 0;
		$valores[$num]["etiqueta"] = "Fecha";
		$valores[$num]["valor"] = $jsonDatos->sac_fecha;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		$valores[$num]["etiqueta"] = "Hora";
		$valores[$num]["valor"] = $jsonDatos->sac_hora;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		$valores[$num]["etiqueta"] = "Accion";
		$valores[$num]["valor"] = $jsonDatos->sac_accion;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		//for_archivo_url
		$val = NEW VALIDADOR;
		$respuesta = new stdClass();
		if ($val->validar($valores)) {
			$respuesta->estado = TRUE;
		} else {
			$respuesta->mensaje = $val->mensaje;
			$respuesta->estado = FALSE;
		}
        return $respuesta;
    }
	
	
	function eliminar($jsonDatos)
	{	
		$respuesta = new stdClass();
		$sql = "DELETE FROM seguimiento_accion WHERE sac_id='".$jsonDatos->id."'";
		$result = mysql_query($sql);
		
		$respuesta->id = mysql_insert_id(); 
		$respuesta->mensaje = "Accion eliminado correctamente";
		$respuesta->estado = TRUE;
		
		return $respuesta;
	}
	
	function agregar($jsonDatos)
	{
		// Verificar seguimiento id
		$validar = $this->validar($jsonDatos);
		if($validar->estado) {
			$sac_seg_id = _seguimiento_tempid($jsonDatos->sac_seg_id);
			$sac_int_id = _cliente_tempid($jsonDatos->sac_int_id); 
			$respuesta = new stdClass();
			$sql = "insert into seguimiento_accion (
													sac_usu_id,
													sac_int_id,
													sac_fecha, 	
													sac_hora, 	
													sac_accion, 	
													sac_seg_id, 	
													sac_estado, 	
													sac_alerta
												) values ( 
													'" . $this->usuDatos[0] . "',
													'" . $sac_int_id . "',
													'" . utf8_decode($jsonDatos->sac_fecha) . "',
													'" . utf8_decode($jsonDatos->sac_hora). ":00', 
													'" . strip_tags(utf8_decode($jsonDatos->sac_accion)) . "',
													'" . $sac_seg_id . "',
													'" . strip_tags(utf8_decode($jsonDatos->sac_estado)) . "',
													'" . $jsonDatos->sac_alerta . "'
												)";
			//Registrar logs 
			logs("APLICACION", $sql, $this->usuDatos[0]);
			
			$result = mysql_query($sql);
			$respuesta->id = mysql_insert_id(); 
			
			$respuesta->mensaje = "Accion agregado correctamente";
			$respuesta->estado = TRUE;
			
		} else {
			
			$respuesta->mensaje = $validar->mensaje;
			$respuesta->estado = FALSE;
		}
		
		return $respuesta;
	}
	
	function modificar($jsonDatos)
	{	
		$respuesta = new stdClass();
		$sql = "UPDATE seguimiento_accion SET 
									sac_fecha='".utf8_decode($jsonDatos->sac_fecha)."',
									sac_hora='".utf8_decode($jsonDatos->sac_hora)."',
									sac_accion='".strip_tags(utf8_decode($jsonDatos->sac_accion))."',
									sac_alerta='".strip_tags(utf8_decode($jsonDatos->sac_alerta))."',
									sac_estado='".strip_tags(utf8_decode($jsonDatos->sac_estado))."'
								WHERE sac_id='".$jsonDatos->sac_id."'";
		$result = mysql_query($sql);
		
		$respuesta->mensaje = "Accion modificado correctamente"; 
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
	function datos($objeto) {
		$convert = new convertir();
		$o = new stdClass;
		$o->sac_id = $objeto->sac_id;
		$o->sac_hora = $objeto->sac_hora;
		$o->sac_fecha = $convert->get_fecha_latina($objeto->sac_fecha);
		$o->sac_accion = utf8_encode($objeto->sac_accion);
		$o->sac_estado = utf8_encode($objeto->sac_estado);
		$o->sac_alerta = utf8_encode($objeto->sac_alerta);
		return $o;
	}
	
	function lista($jsonDatos) { 
		$respuesta = new stdClass(); 
		$arr = array();		 
		$sql = "SELECT * FROM seguimiento_accion WHERE sac_seg_id='".$jsonDatos->id."' ORDER BY sac_id DESC";
		
		$result = mysql_query($sql); 
		$num = mysql_num_rows($result);
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$objeto = mysql_fetch_object($result);
				$o = $this->datos($objeto);
				array_push($arr, $o);
			}
			$respuesta->datos = $arr; 
			$respuesta->estado = TRUE;
		} else {
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
}