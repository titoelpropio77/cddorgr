<?php

class SEGUIMIENTO {
	var $usuDatos;
	var $encript;
	
	function SEGUIMIENTO(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos)
	{
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->seg_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->seg_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]);
			}
		}
	}
	
	function eliminar($jsonDatos)
	{
		$respuesta = new stdClass();
		// Eliminar seguimiento
		$sql = "DELETE FROM seguimiento WHERE seg_id='".$jsonDatos->id."'";
		$result = mysql_query($sql);
		
		//Registrar logs 
		logs("APLICACION", $sql, $this->usuDatos[0]);
		
		
		// Eliminar seguimiento
		$sql = "DELETE FROM seguimiento_accion WHERE sac_seg_id='".$jsonDatos->id."'";
		$result = mysql_query($sql);

		$respuesta->mensaje = "Seguimiento eliminada correctamente";
		$respuesta->estado = TRUE;
		
		return $respuesta;
	}
	
	function agregar($jsonDatos)
	{
		// Verificar Interno Id
		$seg_int_id = _cliente_tempid($jsonDatos->seg_int_id);
		
		$respuesta = new stdClass();
		$sql = "insert into seguimiento (
									seg_usu_id,
									seg_int_id, 	
									seg_fecha,	
									seg_hora,	
									seg_tipo_contacto,	
									seg_situacion,
									seg_app_tempid
							) values ( 
								'" . $this->usuDatos[0] . "',
								'" . $seg_int_id . "',
								'" . date('Y-m-d') . "',
								'" . date('H:i:s') . "', 
								'" . strip_tags(utf8_decode($jsonDatos->seg_tipo_contacto)) . "',
								'" . strip_tags(utf8_decode($jsonDatos->seg_situacion)) . "',
								'" . $jsonDatos->seg_id . "'
							)";
		logs("APLICACION", $sql, $this->usuDatos[0]);
		$result = mysql_query($sql);
		
		$respuesta->id = mysql_insert_id(); 
		$respuesta->mensaje = "Seguimiento agregada correctamente";
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
	function modificar($jsonDatos)
	{
		$seg_int_id = _cliente_tempid($jsonDatos->seg_int_id);
		$respuesta = new stdClass();
		$sql = "UPDATE seguimiento SET 
									seg_usu_id='".$jsonDatos->seg_usu_id."',
									seg_int_id='".$seg_int_id."',
									seg_tipo_contacto='".strip_tags(utf8_decode($jsonDatos->seg_tipo_contacto))."',
									seg_situacion='".strip_tags(utf8_decode($jsonDatos->seg_situacion))."'
								WHERE seg_id='".$jsonDatos->seg_id."'";
		$result = mysql_query($sql); 
		//Registrar logs 
		logs("APLICACION", $sql, $this->usuDatos[0]);
		
		
		$respuesta->mensaje = "Seguimiento modificada correctamente"; 
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
}