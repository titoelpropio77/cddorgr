<?php
class MIRED {
	var $usuDatos;
	var $encript;
	var $vdo_id;
	
	function MIRED(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
		//$this->vdo_id = 1;
		$this->vdo_id = $this->vendedor_id();
	}
	
	function listar(){
		return $this->tree_vendedores($this->vdo_id,10);
	}
	
	function get_comisiones($interno_id, $vendedor_id) 
	{
		$sql_comision = "select com_porcentaje from comision
			inner join venta on (ven_id=com_ven_id)
			where ven_int_id='$interno_id' and com_vdo_id ='$vendedor_id' and com_estado !='Anulado'";
		$result = mysql_query($sql_comision);
		$registros = mysql_num_rows($result);
		$datos = array();
		for ($i = 0; $i < $registros; $i++) {
			$comision = mysql_fetch_object($result);
			$datos[] = ($comision->com_porcentaje * 1);
		}
		return implode('%, ', $datos);
	}
	
	function tree_vendedores($padre_id, $nivel_limite=0, $nivel=0) 
	{
		if($padre_id>0){
			$vendedor = $this->objeto_bd_sql("select int_nombre, int_apellido, int_id, vendedor.* from vendedor, interno where vdo_id='$padre_id' and vdo_int_id=int_id");
			$nombre=  explode(' ', $vendedor->int_nombre);
			$apellido=  explode(' ', $vendedor->int_apellido);
			$porcentaje = $this->get_comisiones($vendedor->int_id, $this->vdo_id);
			$txt_porc='';
			if($porcentaje){
				$txt_porc=" ($porcentaje%)";
			}
			$name= $this->limpiar_cadena($nombre[0]).' '.$this->limpiar_cadena($apellido[0]) . $txt_porc;
		}else{
			$vendedor=null;
			$name='Raiz';
		}
		$tree=new stdClass();
		$tree->name= utf8_encode($name);
		
		$hijosSql = "select * from vendedor where vdo_vendedor_id='$padre_id'";
		$result = mysql_query($hijosSql);
		$registros = mysql_num_rows($result);
		if($registros>0){
			$children=array();
			for ($i = 0; $i < $registros; $i++) {
				$_vendedor = mysql_fetch_object($result);
				if($nivel_limite == 0 || $nivel < $nivel_limite){
					$children[]=$this->tree_vendedores($_vendedor->vdo_id,$nivel_limite,$nivel+1);
				}
			}
			$tree->children=$children;
		}else{

			$tree->size=0;
		}
		return $tree;
	}
	
	function objeto_bd_sql($sql) {
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
        if ($num > 0) {
            return mysql_fetch_object($result);
        } else {
            return null;
        }
    }
	
	function vendedor_id() {
		$sql = "select vdo_id from vendedor where vdo_int_id='".$this->usuDatos[2]."';";
		$result = mysql_query($sql); 
		$num = mysql_num_rows($result);
		if($num >0 ) {
			$objeto = mysql_fetch_object($result); 
			return $objeto->vdo_id;
		} else {
			return 0;
		}
	}
	
	function limpiar_cadena($cadena){
		$cadena= str_replace('á', 'a', $cadena);
		$cadena= str_replace('é', 'e', $cadena);
		$cadena= str_replace('í', 'i', $cadena);
		$cadena= str_replace('ó', 'o', $cadena);
		$cadena= str_replace('ú', 'u', $cadena);
		$cadena= str_replace('�?', 'A', $cadena);
		$cadena= str_replace('É', 'E', $cadena);
		$cadena= str_replace('�?', 'I', $cadena);
		$cadena= str_replace('Ó', 'O', $cadena);
		$cadena= str_replace('Ú', 'U', $cadena);
		$cadena= str_replace('ñ', 'n', $cadena);
		$cadena= str_replace('Ñ', 'N', $cadena);
		return $cadena;
	}
}
