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
		logs("APLICACION", $sql, $this->usuDatos[0]);
		
		$result = mysql_query($sql);
		
		$respuesta->id = mysql_insert_id(); 
		
		$respuesta->mensaje = "Accion agregado correctamente";
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
	function modificar($jsonDatos)
	{	
		$respuesta = new stdClass();
		$sql = "UPDATE seguimiento_accion SET 
									sac_fecha='".utf8_decode($jsonDatos->sac_fecha)."',
									sac_hora='".utf8_decode($jsonDatos->sac_hora)."',
									sac_accion='".strip_tags(utf8_decode($jsonDatos->sac_accion))."',
									sac_alerta='".strip_tags(utf8_decode($jsonDatos->sac_alerta))."'
								WHERE sac_id='".$jsonDatos->sac_id."'";
		$result = mysql_query($sql);
		
		$respuesta->mensaje = "Accion modificado correctamente"; 
		$respuesta->estado = TRUE;
		return $respuesta;
	}
}