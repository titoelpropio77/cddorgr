<?php
 class HISTORIAL

 {

	function HISTORIAL()
	{

		

	}

	function agregar_historial($fecha,$hora,$accion,$persona)
	{
		$coneccion=new ADO;
		
		$sql="insert into interno_historial 
				(inthisto_fecha,inthisto_hora,inthisto_accion,inthisto_int_id,inthisto_ip,inthisto_pc)
				values
				('".date("Y-m-d")."','".date("H:i:s")."','".$accion."','".$persona."','".$_SERVER['REMOTE_ADDR']."','".gethostbyaddr($_SERVER['REMOTE_ADDR'])."')";
		
		$coneccion->ejecutar($sql,false);
	}
	
	function agregar_mensaje($mensaje,$persona,$tipo)
	{
		$coneccion=new ADO;
		
		$sql="insert into mensaje 
				(men_int_id,men_mensaje,men_fecha,men_hora,men_tipo)
				values
				('".$persona."','".$mensaje."','".date("Y-m-d")."','".date("H:i:s")."','".$tipo."')";
		
		$coneccion->ejecutar($sql,false);
	}
 }
?>