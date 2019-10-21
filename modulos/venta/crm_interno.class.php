<?php
class CRM_INTERNO {
	function CRM_INTERNO(){
		
	}
	
	function insertar($datos){
		$conec = new ADO();
		$sql = "INSERT INTO crm_interno (crmint_int_id,crmint_vdolider_id,crmint_vdo_id,crmint_urb_id, 
		crmint_tipo,crmint_mdi_id,crmint_observacion) VALUES (
		'$datos->crmint_int_id','$datos->crmint_vdolider_id','$datos->crmint_vdo_id','$datos->crmint_urb_id','$datos->crmint_tipo',
		'$datos->crmint_mdi_id','$datos->crmint_observacion')";
		$conec->ejecutar($sql,true);
	}

	function modificar($datos){
		$conec = new ADO();
		$sql = "UPDATE crm_interno SET  
		crmint_vdo_id 		= '$datos->crmint_vdo_id', 
		crmint_urb_id 		= '$datos->crmint_urb_id', 
		crmint_mdi_id 		= '$datos->crmint_mdi_id',
		crmint_observacion 	= '$datos->crmint_observacion'
		WHERE crmint_int_id = '$datos->crmint_int_id'";
		$conec->ejecutar($sql,true);
	}
	
	function eliminar($datos){
		$conec = new ADO();
		$sql = "DELETE FROM crm_interno WHERE crmint_int_id = '$datos->crmint_int_id'";
		$conec->ejecutar($sql,true);
	}
	
	function cargar_datos($int_id){
		$conec = new ADO();
		$sql = "SELECT * FROM crm_interno where crmint_int_id = '" . $int_id . "'";
		$conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
		return $objeto; 
	}
	
	function verificar_existencia($datos) {		
		$respuesta = new StdClass();
		$respuesta->estado = false;
		$conec = new ADO();
		$sqlWhere = ""; 
		
		$int_id ="";
		if($datos->int_id !=""){
			$int_id = " and int_id!=".$datos->int_id;
		}
		
		if($datos->int_celular!=""){
			$sql = "SELECT * FROM interno where int_eliminado='No' and int_celular='".$datos->int_celular."' ".$int_id;
			$conec->ejecutar($sql);
			$num = $conec->get_num_registros();
			if ($num > 0) {
				$respuesta->estado = true;
			} else {
				$respuesta->estado = false;
			}
		}
		
		if($datos->int_telefono!=""){
			$sql = "SELECT * FROM interno where int_eliminado='No' and int_telefono='".$datos->int_telefono."' ".$int_id;
			$conec->ejecutar($sql);
			$num = $conec->get_num_registros();
			if ($num > 0) {
				$respuesta->estado = true;
			} else {
				if($respuesta->estado){
				} else {
					$respuesta->estado = false;
				}
			}
		}
		
		return $respuesta; 
	}
}
?>