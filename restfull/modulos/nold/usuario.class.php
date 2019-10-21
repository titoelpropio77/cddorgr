<?php
class USUARIO {
	var $usuDatos;
	var $encript;
	
	function USUARIO(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function verificar_estado($usu_id)
	{
		$sql="SELECT * FROM ad_usuario WHERE usu_id='".$usu_id."' AND usu_estado='1'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result); 
		if($num > 0){
			return true;
		} else{
			return false;
		}
	}
	
	function contrasena_antiguo($usu_password)
	{
		$sql="SELECT * FROM ad_usuario WHERE usu_password='".md5($usu_password)."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result); 
		if($num > 0){
			return true;
		} else{
			return false;
		}
	}
	
	function cambiar_contrasenia($jsonDatos)
	{
		$respuesta = new stdClass();
		if($jsonDatos->usu_password !=""){
			if($this->verificar_estado($this->usuDatos[0])) {
			
				if($this->contrasena_antiguo($jsonDatos->usu_password_antiguo)) {
				
					$sql = "UPDATE ad_usuario SET usu_password='".$jsonDatos->usu_password."' WHERE usu_id='".$this->usuDatos[0]."'"; 
					$result = mysql_query($sql); 
					
					//registrar logs
					logs("APLICACION", "Modifico la contrase&ntilde;a", $this->usuDatos[0]);
					
					$respuesta->mensaje = "Contrase&ntilde;a modificada correctamente"; 
					$respuesta->estado = TRUE; 
					$respuesta->accion = "correcto";
				} else {
					$respuesta->accion = "error";
					$respuesta->mensaje = "La contrase&ntilde;a antigua no coincide";
					$respuesta->estado = FALSE;
				}
			} else {
				$respuesta->accion = "error";
				$respuesta->mensaje = "El usuario esta temporalmente deshabilitado";
				$respuesta->estado = FALSE;
			}
		} else {
			$respuesta->accion = "error";
			$respuesta->mensaje = "Escriba la contrase&ntilde;a"; 
			$respuesta->estado = FALSE;
		}
		return $respuesta; 
	}
}