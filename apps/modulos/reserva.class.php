<?php

class RESERVA {
	var $usuDatos;
	var $encript;
	
	function RESERVA(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos){
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->res_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->res_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->res_registro=="Eliminado")
			{
				$this->eliminar($jsonDatos[$i]);
			}
		}
	}
	
	function eliminar($jsonDatos)
	{	
		$respuesta = new stdClass();
		if ($this->verificar_estado($jsonDatos->res_id)) {
		
			$sql = "DELETE FROM reserva_terreno WHERE res_id='".$jsonDatos->res_id."'";
			$result = mysql_query($sql);
			
			//Cambiar estado de lote
			if($this->verificar_lote_estado($jsonDatos->res_lot_id)=="Reservado"){
				$sql = "update lote set lot_estado='Disponible' where lot_id='".$jsonDatos->res_lot_id."'"; 
				$result = mysql_query($sql);
			}
			
			$respuesta->mensaje = "Reserva eliminado correctamente";
			$respuesta->estado = TRUE;
			
		} else {
			$respuesta->mensaje = "No se puede eliminar la reserva";
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	function agregar($jsonDatos)
	{
		// Verificar Interno Id
		$res_int_id = _cliente_tempid($jsonDatos->res_int_id);
		
		$respuesta = new stdClass();
		if ($this->verificar($jsonDatos->res_lot_id) == "si") {

	
			$sql = "insert into reserva_terreno (
													res_int_id,
													res_vdo_id,
													res_lot_id,
													res_fecha,
													res_hora,
													res_estado ,
													res_usu_id,
													res_plazo_fecha, 
													res_plazo_hora,
													res_nota,
													res_urb_id,
													res_moneda,
													res_monto_referencial,
													res_monto_m2,
													res_app_tempid
											) values ( 
													'" . $res_int_id . "',
													'" . $jsonDatos->res_vdo_id . "',
													'" . $jsonDatos->res_lot_id . "',
													'" . date('Y-m-d') . "',
													'" . date('H:i') . "',
													'Pendiente',
													'" . $this->usuDatos[0] . "',
													'" . date('Y-m-d') . "',
													'" . date('H:i') . "',
													'" . strip_tags(utf8_decode($jsonDatos->res_nota)). "',
													'" . $jsonDatos->res_urb_id . "',
													'" . $jsonDatos->res_moneda . "',
													'" . $jsonDatos->res_monto_referencial . "',
													'" . $jsonDatos->res_monto_m2 . "',
													'" . $jsonDatos->res_id . "'
											)";
			//Registrar logs 
			logs("APLICACION", $sql, $this->usuDatos[0]);
			
			$result = mysql_query($sql);
			$insertId = mysql_insert_id();
			
			// Actualizar Lote
			$sql = "update lote set lot_estado='Reservado' where lot_id='".$jsonDatos->res_lot_id."'";
			$result = mysql_query($sql); 
			
			$respuesta->id = $insertId; 
			$respuesta->mensaje = "Reserva agregada correctamente";
			$respuesta->estado = TRUE;
			$respuesta->accion = "correcto";
			
		} else {
			$respuesta->mensaje = "No se pudo reservar el Terreno, <br/>por que ya se encuentra Reservado, Bloqueado o Vendido";
			$respuesta->estado = FALSE; 
			$respuesta->accion = "error";
		}
		return $respuesta;
	}
	
	function verificar($lot_id) {
		$sql = "SELECT lot_estado FROM lote WHERE  lot_id='" . $lot_id . "'";
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);

		if ($objeto->lot_estado == 'Disponible') {
			return "si";
		} else {
			return "no";
		}
	}
	
	function verificar_lote_estado($lot_id) { 
		$sql = "SELECT lot_estado FROM lote WHERE  lot_id='" . $lot_id . "'";
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);
		return $objeto->lot_estado;
	}
	
	function verificar_estado($res_id) {
		$sql = "SELECT res_estado FROM reserva_terreno WHERE res_id='" . $res_id . "'";
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);
		if ($objeto->res_estado == 'Pendiente') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function modificar($jsonDatos)
	{							
		$respuesta = new stdClass();
		if ($this->verificar_estado($jsonDatos->res_id)) { 
			if ($this->verificar($jsonDatos->res_lot_id) == "si") {
				$sql = "UPDATE reserva_terreno SET 
											res_int_id='".$jsonDatos->res_int_id."',
											res_vdo_id='".$jsonDatos->res_vdo_id."',
											res_lot_id='".$jsonDatos->res_lot_id."',
											res_fecha='".date('Y-m-d')."',
											res_hora='".date('H:i')."',
											res_plazo_fecha='".date('Y-m-d')."',
											res_plazo_hora='".date('H:i')."',
											res_urb_id='".$jsonDatos->res_urb_id."',
											res_moneda='".$jsonDatos->res_moneda."',
											res_monto_referencial='".$jsonDatos->res_monto_referencial."', 
											res_monto_m2='".$jsonDatos->res_monto_m2."',
											res_nota='".strip_tags(utf8_decode($jsonDatos->res_nota))."'
											
										WHERE res_id='".$jsonDatos->res_id."'";
										
				$result = mysql_query($sql);
				$respuesta->mensaje = "Reserva modificada correctamente"; 
				$respuesta->accion = "correcto";
			} else {
				$respuesta->mensaje = "No se pudo reservar el Terreno, <br/>por que ya se encuentra <br/>Reservado, Bloqueado o Vendido";
				$respuesta->accion = "error";
			}
		} else {
			$respuesta->mensaje = "Esta reserva no se puede modificar<br/> por ya ha sido revisado o cambiado de estado.";
			$respuesta->accion = "error";
		}
		return $respuesta;
	}
}
