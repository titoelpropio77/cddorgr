<?php
class USUARIO {
	var $usuDatos;
	var $encript;
	
	function USUARIO(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function cambiar_contrasenia($jsonDatos)
	{
		$respuesta = new stdClass();
		if($jsonDatos->usu_password !=""){
			$sql = "UPDATE ad_usuario SET 
											usu_password='".$jsonDatos->usu_password."'
									  WHERE usu_id='".$this->usuDatos[0]."'"; 
			$result = mysql_query($sql);
			
			//registrar logs
			logs("APLICACION", "Modifico la contrase&ntilde;a", $this->usuDatos[0]);
			
			$respuesta->mensaje = "Contraseña modificada correctamente"; 
			$respuesta->estado = TRUE;
		} else { 
			$respuesta->mensaje = "Error al modificar contraseña"; 
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
}