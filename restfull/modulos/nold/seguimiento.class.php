<?php

class SEGUIMIENTO {
	var $usuDatos;
	var $encript;
	var $registros_limit = 5;
	
	function SEGUIMIENTO(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
	}
	
	function sincronizar($jsonDatos) {
		for ($i=0; $i < count($jsonDatos); $i++) 
		{
			if($jsonDatos[$i]->seg_registro=="Nuevo")
			{
				$this->agregar($jsonDatos[$i]);
			}
			if($jsonDatos[$i]->seg_registro=="Antiguo")
			{
				$this->modificar($jsonDatos[$i]);
			}
		}
	}
	
	function validar($jsonDatos) {
		
		//texto,  numero,  real,  fecha,  mail.
		$num = 0;
		$valores[$num]["etiqueta"] = "Tipo Contacto";
		$valores[$num]["valor"] = $jsonDatos->seg_stc_id;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		$valores[$num]["etiqueta"] = "Cliente";
		$valores[$num]["valor"] = $jsonDatos->seg_int_id;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		$valores[$num]["etiqueta"] = "Situacion";
		$valores[$num]["valor"] = $jsonDatos->seg_situacion;
		$valores[$num]["tipo"] = "todo";
		$valores[$num]["requerido"] = true;
		$num++;
		
		//for_archivo_url
		$val = NEW VALIDADOR;
		$respuesta = new stdClass();
		if ($val->validar($valores)) {
			$respuesta->estado = TRUE;
		} else {
			$respuesta->mensaje = $val->mensaje;
			$respuesta->estado = FALSE;
		}
        return $respuesta;
    }
	
	function eliminar($jsonDatos) {
		$respuesta = new stdClass();
		if($jsonDatos->id){
			// Eliminar seguimiento
			$sql = "DELETE FROM seguimiento WHERE seg_id='".$jsonDatos->id."'";
			$result = mysql_query($sql);
			
			// Eliminar seguimiento
			$sql = "DELETE FROM seguimiento_accion WHERE sac_seg_id='".$jsonDatos->id."'";
			$result = mysql_query($sql);
			
			//Registrar logs
			logs("APLICACION", $sql, $this->usuDatos[0]);
			
			$respuesta->mensaje = "Seguimiento eliminada correctamente";
			$respuesta->estado = TRUE;
		} else {
			$respuesta->mensaje = "No se encontro el registro";
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	function agregar($jsonDatos) {
				
		$respuesta = new stdClass();
		if($jsonDatos->seg_fecha ==""){
			$jsonDatos->seg_fecha = date('Y-m-d');
		}
		
		if($jsonDatos->seg_hora ==""){
			$jsonDatos->seg_hora = date('H:i:s');
		}
		
		$validar = $this->validar($jsonDatos);
		if($validar->estado) {
			// Verificar Interno Id
			$seg_int_id = _cliente_tempid($jsonDatos->seg_int_id);
			$sql = "insert into seguimiento (
										seg_usu_id,
										seg_int_id, 	
										seg_fecha,	
										seg_hora,	
										seg_stc_id,	
										seg_situacion,
										seg_app_tempid
								) values ( 
									'" . $this->usuDatos[0] . "',
									'" . $seg_int_id . "',
									'" . $jsonDatos->seg_fecha. "',
									'" . $jsonDatos->seg_hora . "', 
									'" . strip_tags(utf8_decode($jsonDatos->seg_stc_id)) . "',
									'" . strip_tags(utf8_decode($jsonDatos->seg_situacion)) . "',
									'" . $jsonDatos->seg_id . "'
								)";
			//Registrar logs 
			logs("APLICACION", $sql, $this->usuDatos[0]);
				
			$result = mysql_query($sql);
			$respuesta->id = mysql_insert_id();
			
			$respuesta->mensaje = "Seguimiento agregada correctamente";
			$respuesta->estado = TRUE;
		} else {
			$respuesta->mensaje = $validar->mensaje;
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	function modificar($jsonDatos) {
		
		$seg_int_id = _cliente_tempid($jsonDatos->seg_int_id);
		$respuesta = new stdClass();
		
		$validar = $this->validar($jsonDatos);
		if($validar->estado) {
			$sql = "UPDATE seguimiento SET 
										seg_int_id='".$seg_int_id."',
										seg_fecha='".$jsonDatos->seg_fecha."',
										seg_hora='".$jsonDatos->seg_hora."',
										seg_stc_id='".strip_tags(utf8_decode($jsonDatos->seg_stc_id))."',
										seg_situacion='".strip_tags(utf8_decode($jsonDatos->seg_situacion))."'
									WHERE seg_id='".$jsonDatos->seg_id."'";
			
			$result = mysql_query($sql); 
			
			//Registrar logs 
			logs("APLICACION", $sql, $this->usuDatos[0]);
			
			$respuesta->mensaje = "Seguimiento modificada correctamente ";
			$respuesta->estado = TRUE;
		} else {
			$respuesta->mensaje = $validar->mensaje;
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	//=============== Nuevas funciones ==============//
	function lista() {
		
		$respuesta = new stdClass();
		$arr = array();
		
		if (isset($_GET['pagina'])) {
			$pages = $_GET['pagina'];
		} else {
			$pages = 0;
		}
		
		$buscar = clean_input(trim($_GET["buscar"])); 
		if($buscar != "") {
			$sqlBuscar .= " and CONCAT_WS(' ', int_nombre, int_apellido,int_ci,seg_situacion,stc_tipo) LIKE '%".$buscar."%' ";  
		}
		
		$pagesActual = (($pages)*($this->registros_limit));
		$sql = "SELECT *, CONCAT(int_nombre,' ',int_apellido) as cliente FROM seguimiento 
				INNER JOIN interno ON (seg_int_id=int_id)  
				INNER JOIN seguimiento_tipo_contacto ON (seg_stc_id=stc_id) 
				WHERE seg_usu_id='".$this->usuDatos[0]."' ".$sqlBuscar." ORDER BY seg_id DESC";
		$sqlLimit = " LIMIT $pagesActual, $this->registros_limit";
		
		$result = mysql_query($sql.$sqlLimit);
		$num = mysql_num_rows($result);
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$objeto = mysql_fetch_object($result);
				$o = $this->datos($objeto);
				array_push($arr, $o);
			}
			
			//Paginacion 
			$registrosTotal = $this->lista_total($sql); 
			$pageTotal = round(($registrosTotal) / ($this->registros_limit));
			$pageActivo = $pages; 
			
			$respuesta->datos = $arr; 
			$respuesta->opciones = array("registrosTotal"=>$registrosTotal, "pageTotal"=>$pageTotal,"pageActivo"=>$pageActivo);
			$respuesta->estado = TRUE;
			
		} else {
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	function lista_total($sql) {
		$result = mysql_query($sql);
		$num = mysql_num_rows($result); 
		if ($num > 0) {
			return $num;
		} else {
			return 0;
		}
	}
	
	function datos($objeto) {
		$convert = new convertir();
		//seg_int_id
		$o = new stdClass;
		$o->seg_id = $objeto->seg_id;
		$o->seg_usu_id = $objeto->seg_usu_id;
		$o->cliente = utf8_encode($objeto->cliente);
		$o->seg_int_id = $objeto->seg_int_id;
		$o->seg_stc_id = $objeto->seg_stc_id;
		$o->stc_tipo = utf8_encode($this->tipo_contacto($objeto->seg_stc_id)); 
		$o->seg_fecha = $convert->get_fecha_latina($objeto->seg_fecha);
		$o->seg_hora = substr($objeto->seg_hora, 0, -3);
		$o->acciones = $this->accion_contar($objeto->seg_id);
		$o->seg_situacion = utf8_encode($objeto->seg_situacion);
		return $o;
	}
	
	function accion_contar($seg_id){
		$sql="select count(*) as total from seguimiento_accion  where sac_seg_id = '".$seg_id."'";
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);
		return $objeto->total;
	}
	
	function buscar($jsonDatos) {
		$arr = array();
		$respuesta = new stdClass();
		$sqlBuscar = "";
		
		$buscar = clean_input(trim($_POST["buscar"]));
		
		if($buscar != "") { 
			$sqlBuscar .= "and CONCAT_WS(' ', int_nombre, int_apellido,int_ci, intcel_celular, inttelf_telefono,intemail_email,seg_situacion) LIKE '%".$buscar."%' ";  
		}
		
		$vendedor = obtener_id_vendedor($this->usuDatos[2]);
		$sql = "SELECT DISTINCT seg_id,seg_usu_id,seg_int_id, CONCAT(int_nombre,' ',int_apellido) as cliente,seg_fecha,seg_hora,seg_stc_id,seg_situacion FROM interno 
									INNER JOIN seguimiento ON(seg_int_id=int_id)
								WHERE int_vdo_id='".$vendedor."' ".$sqlBuscar." order by seg_id desc limit 0,".$this->registros_limit; 
								
		$result = mysql_query($sql);
		$num = mysql_num_rows($result); 
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$objeto = mysql_fetch_object($result);
				$o = $this->datos($objeto);
				array_push($arr, $o);
			}
			
			$respuesta->datos = $arr;
			$respuesta->accion = "correcto";
			$respuesta->mensaje = "Datos encontrados"; 
			$respuesta->estado = TRUE;
		} else {
			$respuesta->accion = "error";
			$respuesta->mensaje = "No se encontró ningún resultado de búsqueda"; 
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	
	function ver($jsonDatos) {
		$respuesta = new stdClass();
		if(is_numeric($jsonDatos->id)) {			
			$sql = "SELECT *, CONCAT(int_nombre,' ',int_apellido) as cliente FROM seguimiento 
					INNER JOIN interno ON (seg_int_id=int_id)  
					INNER JOIN seguimiento_tipo_contacto ON (seg_stc_id=stc_id)
				WHERE seg_id='".$jsonDatos->id."' and seg_usu_id='".$this->usuDatos[0]."'";
		
			$result = mysql_query($sql);
			$num = mysql_num_rows($result);
			if ($num > 0) { 
				$objeto = mysql_fetch_object($result);
				$o = $this->datos($objeto); 
				$respuesta->datos = $o;
				$respuesta->accion = "correcto";
				$respuesta->mensaje = "Datos encontrados"; 
				$respuesta->estado = TRUE; 
				
			} else {
				$respuesta->accion = "error";
				$respuesta->mensaje = "No se encontro el registro"; 
				$respuesta->estado = FALSE;
			}
		} else {
			$respuesta->accion = "error";
			$respuesta->mensaje = "Parámetro incorrecto"; 
			$respuesta->estado = FALSE;
		}
		return $respuesta;
	}
	function tipo_contacto_lista(){ 
		$sql = "select * from seguimiento_tipo_contacto order by stc_tipo asc";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$tipos = array();
		for ($i = 0; $i < $num; $i++) {
			$tipos[] = mysql_fetch_object($result);
		}
		return $tipos;
	}
	
	function tipo_contacto($stc_id){
		$sql = "SELECT stc_tipo FROM seguimiento_tipo_contacto WHERE stc_id=$stc_id";
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);	
		$num = mysql_num_rows($result);
		if ($num > 0) {
			return $objeto->stc_tipo;
		} else {
			return "";
		}
	}
	
}