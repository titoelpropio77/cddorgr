<?php

class CLIENTE {
	var $usuDatos;
	var $encript;
	
	function CLIENTE(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos){
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->int_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->int_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]);
			}
		}
	}
	
	function verificar_interno($int_nombre, $int_apellido, $int_id=0){
		$sqlFiltro = "";
		if($int_id !=0){
			$sqlFiltro = " AND int_id NOT IN (".$int_id.") ";
		}
		
		$sql = "SELECT int_id FROM interno WHERE int_nombre='".trim($int_nombre)."' and int_apellido='".trim($int_apellido)."' ".$sqlFiltro;	
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if ($num > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function verificar_unico($campos, $valor, $int_id=0) {
	
		 $sqlFiltro = ""; 
		 if($int_id !=0){
			$sqlFiltro = " AND int_id NOT IN (".$int_id.") ";
		 }
		 
		if($valor !=""){
			//int_email int_ci
			$sql = "SELECT int_id FROM interno WHERE ".$campos."='".trim($valor)."' ".$sqlFiltro;		
			$result = mysql_query($sql);
			$num = mysql_num_rows($result);
			if ($num > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function agregar($jsonDatos)
	{
		$respuesta = new stdClass();
		// campos requeridos
		if($jsonDatos->int_nombre !="" && $jsonDatos->int_apellido !="" && $jsonDatos->int_ci !="") { 
		
			if($this->verificar_interno($jsonDatos->int_nombre, $jsonDatos->int_apellido) == false) {
			
				if($this->verificar_unico("int_email", $jsonDatos->int_email)===false) {
				
					if($this->verificar_unico("int_ci", $jsonDatos->int_ci)===false) {
					
						
							$sql = "insert into interno (
													int_nombre,
													int_apellido,
													int_email,
													int_telefono,
													int_celular,
													int_direccion,
													int_ci,
													int_estado_civil,
													int_fecha_ingreso,
													int_fecha_nacimiento,
													int_ci_exp,
													int_app_tempid,
													int_usu_id
												) values ( 
													'" . strip_tags(utf8_decode($jsonDatos->int_nombre)). "',
													'" . strip_tags(utf8_decode($jsonDatos->int_apellido)) . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_email)) . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_telefono)) . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_celular)) . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_direccion)) . "', 
													'" . strip_tags(utf8_decode($jsonDatos->int_ci)) . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_estado_civil)). "', 
													'" . date('Y-m-d') . "',
													'" . $jsonDatos->int_fecha_nacimiento . "',
													'" . strip_tags(utf8_decode($jsonDatos->int_ci_exp)) . "',
													'" . $jsonDatos->int_id . "',
													'" . $this->usuDatos[0] . "' 
												)"; 
							//Registrar logs 
							logs("APLICACION", $sql, $this->usuDatos[0]);
						
							$result = mysql_query($sql);
							$respuesta->id = mysql_insert_id();
							$respuesta->mensaje = "Cliente agregado correctamente"; 
							$respuesta->estado = TRUE;
						
					} else {
						$respuesta->mensaje = "El documento C.I. ".$jsonDatos->int_ci." <br>ya ha sido registrado anteriormente.";
						$respuesta->estado = FALSE;
					}
				} else {
					$respuesta->mensaje = "El correo ".$jsonDatos->int_email." <br>ya ha sido registrado anteriormente."; 
					$respuesta->estado = FALSE; 
				}
			} else{
				$respuesta->mensaje = "No se pudo registrar por que <br>el cliente ya se encuentra registrado"; 
				$respuesta->estado = FALSE;
			}
		} else{
			$respuesta->mensaje = "Por favor complete los campos requeridos (*)."; 
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	
	function modificar($jsonDatos)
	{
		if($jsonDatos->int_nombre !="" && $jsonDatos->int_apellido !="" && $jsonDatos->int_ci !="") {
		
			if($this->verificar_interno($jsonDatos->int_nombre, $jsonDatos->int_apellido, $jsonDatos->int_id) == false) {
			
				if($this->verificar_unico("int_email", $jsonDatos->int_email, $jsonDatos->int_id)===false){
				
					if($this->verificar_unico("int_ci", $jsonDatos->int_ci, $jsonDatos->int_id)===false) {
					
						$respuesta = new stdClass();							
						$sql = "UPDATE interno SET 
													int_nombre='".strip_tags(utf8_decode($jsonDatos->int_nombre))."',
													int_apellido='".strip_tags(utf8_decode($jsonDatos->int_apellido))."',
													int_email='".strip_tags(utf8_decode($jsonDatos->int_email))."',
													int_telefono='".strip_tags(utf8_decode($jsonDatos->int_telefono))."',
													int_celular='".strip_tags(utf8_decode($jsonDatos->int_celular))."',
													int_direccion='".strip_tags(utf8_decode($jsonDatos->int_direccion))."',
													int_ci='".$jsonDatos->int_ci."',
													int_estado_civil='".$jsonDatos->int_estado_civil."',
													int_fecha_nacimiento='".$jsonDatos->int_fecha_nacimiento."',
													int_ci_exp='".strip_tags(utf8_decode($jsonDatos->int_ci_exp))."'
												WHERE int_id='".$jsonDatos->int_id."'";
						
						$result = mysql_query($sql); 
						
						//Registrar logs 
						logs("APLICACION", $sql, $this->usuDatos[0]);
						
						$respuesta->mensaje = "Cliente modificado correctamente"; 
						$respuesta->estado = TRUE;
						
					} else {
						$respuesta->mensaje = "El documento C.I. ".$jsonDatos->int_ci." <br>ya ha sido registrado anteriormente.";
						$respuesta->estado = FALSE;
					}
				} else {
					$respuesta->mensaje = "El correo ".$jsonDatos->int_email." <br>ya ha sido registrado anteriormente."; 
					$respuesta->estado = FALSE;
				}
			} else{
				$respuesta->mensaje = "No se pudo registrar por que <br>el cliente ya se encuentra registrado"; 
				$respuesta->estado = FALSE;
			}
		} else{
			$respuesta->mensaje = "Por favor complete los campos requeridos (*)."; 
			$respuesta->estado = FALSE;
		}
		
		return $respuesta;
	}
}