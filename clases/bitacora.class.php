<?PHP
require_once('config/database.conf.php');
require_once('clases/adodb/adodb.inc.php');
require_once('clases/coneccion.class.php');
class bitacora
{
	//detalle arreglo de datos para la bitacora
	
	/*
		[0]= id_modulo
		[1]=accion
		[2]=tipo de accion
		[3]=usuario
		
	*/
		
	function bitacora()
	{
		
	}
	
	function get_mod_id()
	{
		return $_GET['mod_id'];
	}
	
	function agregar_accion($datos)
	{
	
		$coneccion=new ADO;
		
		$sql="insert into ad_logs 
				(log_fecha,log_hora,log_tipo_accion,log_accion,log_usu_id)
				values
				('".date("Y-m-d")."','".date("H:i:s")."','".$datos['tipo_accion']."','".$datos['accion']."','".$datos['usuario']."')";
		
		$coneccion->ejecutar($sql,false);
	}
	
	function get_num_accion($usuario,$fecha,$tipo='INICIO',$modulo='1')
	{
		$coneccion=new ADO;
		
		$sql="select * from bitacora where bit_usu_id = '".$usuario."' 
											and bit_fecha ='".$fecha."' 
											and bit_tipo_accion ='".$tipo."'
											and bit_mod_id = '".$modulo."'";
		
		
		
		$coneccion->ejecutar($sql);
		
		return $coneccion->get_num_registros();
	
	}
}
?>