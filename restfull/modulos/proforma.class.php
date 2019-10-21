<?php

class PROFORMA {
	var $usuDatos;
	var $encript;
	
	function PROFORMA(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos){
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->pro_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			/*
			if($jsonDatos[$i]->pro_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]); 
			}
			*/
			if($jsonDatos[$i]->pro_registro=="Eliminado")
			{
				$this->eliminar($jsonDatos[$i]);
			}
		}
	}
	
	function eliminar($jsonDatos)
	{	
		$respuesta = new stdClass();
		$sql = "DELETE FROM proforma WHERE pro_id='".$jsonDatos->pro_id."'";
		$result = mysql_query($sql);
		
		$respuesta->mensaje = "Proforma eliminado correctamente";
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
	function agregar($jsonDatos)
	{
		// Verificar Interno Id
		$pro_int_id = _cliente_tempid($jsonDatos->pro_int_id);
		
		$respuesta = new stdClass();
		$sql = "insert into proforma (
									pro_usu_id,
									pro_int_id, 	
									pro_fecha,	
									pro_hora,	
									pro_lote_precio,
									pro_parametros,
									pro_lot_id,
									pro_urb_id,
									pro_uv,
									pro_manzano,
									pro_ci,
									pro_urb_interes_anual,
									pro_app_tempid
							) values ( 
								'" . $this->usuDatos[0] . "',
								'" . $pro_int_id . "',
								'" . date('Y-m-d') . "',
								'" . date('H:i:s') . "', 
								'" . $jsonDatos->pro_lote_precio . "',
								'" . $jsonDatos->pro_parametros . "', 
								'" . $jsonDatos->pro_lot_id . "',
								'" . $jsonDatos->pro_urb_id . "',
								'" . $jsonDatos->pro_uv . "',
								'" . $jsonDatos->pro_manzano . "',
								'" . $jsonDatos->pro_ci . "',
								'" . $jsonDatos->pro_urb_interes_anual . "', 
								'" . $jsonDatos->pro_id . "'
							)";
		//Registrar logs 
		logs("APLICACION", $sql, $this->usuDatos[0]);
		
		
		$result = mysql_query($sql);
		$respuesta->id = mysql_insert_id();
		
		// Actualizar estado
		/* 
		require_once('../clases/interno.php');
		$cambio_estado = new INTERNO();
		$cambio_estado->actualizar_prospecto($jsonDatos->pro_int_id, 'Proforma');
		*/ 
		
		$respuesta->mensaje = "Proforma agregado correctamente";
		$respuesta->estado = TRUE;
		return $respuesta;
	}
	
	function comprobar_email($email) {
		$mail_correcto = 0;
		//compruebo unas cosas primeras
		if ((strlen($email) >= 6) && (substr_count($email, "@") == 1) && (substr($email, 0, 1) != "@") && (substr($email, strlen($email) - 1, 1) != "@")) {
			if ((!strstr($email, "'")) && (!strstr($email, "\"")) && (!strstr($email, "\\")) && (!strstr($email, "\$")) && (!strstr($email, " "))) {
				//miro si tiene caracter .
				if (substr_count($email, ".") >= 1) {
					//obtengo la terminacion del dominio
					$term_dom = substr(strrchr($email, '.'), 1);
					//compruebo que la terminaci?n del dominio sea correcta
					if (strlen($term_dom) > 1 && strlen($term_dom) < 5 && (!strstr($term_dom, "@"))) {
						//compruebo que lo de antes del dominio sea correcto
						$antes_dom = substr($email, 0, strlen($email) - strlen($term_dom) - 1);
						$caracter_ult = substr($antes_dom, strlen($antes_dom) - 1, 1);
						if ($caracter_ult != "@" && $caracter_ult != ".") {
							$mail_correcto = 1;
						}
					}
				}
			}
		}
		
		if ($mail_correcto) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	
	function reenviar_correo($jsonDatos){
		$respuesta = new stdClass();
		$usuarios = new USUARIOS();
		$sql = "SELECT pro_int_id FROM proforma WHERE pro_id='".$jsonDatos->id."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);

		if ($num > 0) {
			$objeto = mysql_fetch_object($result);
			$usuarios = new USUARIOS();
			
			$correo_cliente = $usuarios->get_interno_correo($objeto->pro_int_id);
			$correo_usuario = $usuarios->get_usuario_correo($this->usuDatos[0]);
			
			if ($this->comprobar_email($correo_cliente))
			{
				if($this->comprobar_email($correo_usuario)) 
				{
					$datos->FromName = _nombre_empresa;
					$datos->Subject = "PROFORMA COD: ".$jsonDatos->id;
					$datos->AddAddress = $correo_cliente;
					$datos->Titulo = "PROFORMA";
					$datos->AddReplyTo = $correo_usuario; 
					
					$limpio = preg_replace('/\\\/', '', urldecode($_GET['body']));
					$limpio1 = preg_replace('/&quot;/', '', $limpio);
					$datos->Body =  $limpio1;
					
					//Enviar correo a los encargados de invitados
					$envio = new EMAIL();
					$resulta = $envio->enviar($datos);
					if ($resulta->estado) {
						//Registrar logs 
						logs("APLICACION", $datos->Subject." (Envio correo electronico al cliente)", $this->usuDatos[0]);
					
						$respuesta->mensaje = "Correo enviado correctamente";
						$respuesta->accion = "correcto";
						$respuesta->estado = TRUE;
					} else {
						$respuesta->mensaje = "Error en el servidor SMTP";
						$respuesta->accion = "error";
						$respuesta->estado = FALSE;
					}
				} else {
					$respuesta->mensaje = "El correo del usuario es invalido<br/> no esta registrado."; 
					$respuesta->accion = "error";
					$respuesta->estado = FALSE;
				}
			} else {
				$respuesta->mensaje = "El correo del cliente es invalido<br/> no esta registrado."; 
				$respuesta->accion = "error";
				$respuesta->estado = FALSE;
			}
			
		} else {
			$respuesta->mensaje = "No existe la proforma <br>o a&uacute;n no ha sido sincronizado"; 
			$respuesta->accion = "error";
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
}