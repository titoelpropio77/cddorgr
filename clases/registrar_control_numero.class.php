<?php
 class CONTROL_NUMERO
 {
	function CONTROL_NUMERO()
	{
	}

	function insertar_control_numero($periodo,$tipo_comprobante,$valor)
	{
		$conec=new ADO();

		$sql="insert into con_contador_comprobante(coc_nro,coc_pdo_id,coc_tco_id) value ('$valor','$periodo','$tipo_comprobante')";
		
		$conec->ejecutar($sql);
	}
	
	function Encontrar_nro($periodo,$tipo_comprobante)
	{
		$conec= new ADO();
		
		$sql="select coc_nro from con_contador_comprobante where coc_pdo_id='$periodo' and coc_tco_id=$tipo_comprobante";
		 
		$conec->ejecutar($sql);
	
		$objeto=$conec->get_objeto();
		
		return $objeto->coc_nro;
	}
	function actualizar_contador($periodo,$tipo_comprobante,$nuevo_nro)
	{
		$conec= new ADO();
		
		$sql="update con_contador_comprobante set coc_nro=$nuevo_nro where coc_pdo_id='$periodo' and coc_tco_id=$tipo_comprobante";
		 
		$conec->ejecutar($sql);
	}
 }
?>