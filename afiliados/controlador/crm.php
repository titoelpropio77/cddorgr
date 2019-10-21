<?php

class CRM {
    var $Usuario;
    var $Mensaje;
	var $Encryp;
    function CRM() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
		$this->Encryp = new Encryption();
    }
	
    function gestor() {
        if ($this->Usuario->sesion_iniciada()) {
			$tarea = $_GET['uri3'];
			$modulo = $_GET['uri2'];
			
			// Seguimiento
			if($modulo=="seguimiento") {
				switch ($tarea) {
					case "agregar": 
						if($_POST) {
							$convertir = new convertir();
							$seg_fecha = $convertir->get_fecha_mysql($_POST['seg_fecha']);
							$datosPost = array(
								"seg_int_id"=>$_POST['seg_int_id'],
								"seg_fecha"=>$seg_fecha,
								"seg_hora"=>$_POST['seg_hora'],
								"seg_stc_id"=>$_POST['seg_stc_id'],
								"seg_situacion"=>$_POST['seg_situacion'],
								"seg_app_tempid"=>0
							);
							$objeto = new stdClass();
							$objeto->datosForm = json_encode($datosPost);
							$data = (array) $objeto;
							$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=agregar&token=" . $this->Usuario->get_token(), $data);
							$jsonResp = json_decode($resp);                         
							$jsonResp = $jsonResp[0]->respuesta;
							if($jsonResp->accion=="correcto") {
								$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje); 
								FUNCIONES::redirect(_base_url . "crm/seguimiento/lista");
							} else {
								$this->Mensaje->set_mensaje("error", $jsonResp->mensaje); 
								$this->seguimiento_formulario_view();
							}
						} else {
							$this->seguimiento_formulario_view();
						}
					break; 
					case "modificar": 
						if($_POST) {
							$convertir = new convertir();
							$seg_fecha = $convertir->get_fecha_mysql($_POST['seg_fecha']);
							$datosPost = array(
								"seg_id" => $this->Encryp->decode($_POST['seg_id']),
								"seg_int_id" => $_POST['seg_int_id'],
								"seg_fecha" => $seg_fecha,
								"seg_hora" => $_POST['seg_hora'],
								"seg_stc_id" => $_POST['seg_stc_id'],
								"seg_situacion" => $_POST['seg_situacion']
							); 
							$objeto = new stdClass();
							$objeto->datosForm = json_encode($datosPost);
							$data = (array) $objeto;
							$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=modificar&token=" . $this->Usuario->get_token(), $data);
							$jsonResp = json_decode($resp);                      
							$jsonResp = $jsonResp[0]->respuesta;
							if($jsonResp->accion=="correcto") {
								$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje); 
								FUNCIONES::redirect(_base_url . "crm/seguimiento/lista");
							} else {
								$this->Mensaje->set_mensaje("error", $jsonResp->mensaje); 
								$this->seguimiento_formulario_view();
							}
						} else {
							
							$datosPost = array("id"=> $this->Encryp->decode($_GET['uri4']));
							$objeto = new stdClass();
							$objeto->datosForm = json_encode($datosPost);
							$data = (array) $objeto;
							
							$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=ver&token=" . $this->Usuario->get_token(), $data);
							$jsonResp = json_decode($resp);                         
							$jsonResp = $jsonResp[0]->respuesta;
							if($jsonResp->accion=="correcto"){
								$jsonResp->datos->seg_id = $this->Encryp->encode($jsonResp->datos->seg_id);
								SISTEMA::obj_post($jsonResp->datos);
								$this->seguimiento_formulario_view();
							} else { 
								$this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
								FUNCIONES::redirect(_base_url . "crm/seguimiento/agregar");
							}
						}
					break;
					case "lista":
						$this->seguimiento_lista_view();
					break;
					case "eliminar":
					
						$datosPost = array("id"=> $this->Encryp->decode($_GET['uri4']));
						$objeto = new stdClass();
						$objeto->datosForm = json_encode($datosPost);
						$data = (array) $objeto; 
						
						$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=eliminar&token=" . $this->Usuario->get_token(), $data);
						$jsonResp = json_decode($resp);                         
						$jsonResp = $jsonResp[0]->respuesta;
						if($jsonResp->accion=="correcto"){ 
							$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
							FUNCIONES::redirect(_base_url . "crm/seguimiento/lista");
						} else { 
							$this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
							FUNCIONES::redirect(_base_url . "crm/seguimiento/lista");
						}
					case "ver":
						$datosPost = array("id"=> $this->Encryp->decode($_GET['uri4']));
						$objeto = new stdClass();
						$objeto->datosForm = json_encode($datosPost);
						$data = (array) $objeto; 
						
						$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=ver&token=" . $this->Usuario->get_token(), $data);
						$jsonResp = json_decode($resp);                         
						$jsonResp = $jsonResp[0]->respuesta;
						if($jsonResp->accion=="correcto"){
							$jsonResp->datos->seg_id = $this->Encryp->encode($jsonResp->datos->seg_id);
							SISTEMA::obj_post($jsonResp->datos);
							$this->seguimiento_formulario_view();
						} else { 
							$this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
							FUNCIONES::redirect(_base_url . "portada"); 
						} 
					break;
					
					case "acciones":
						//echo $_GET['uri7'];
						if($_GET['uri6']=="modificar") {
							$sac_id = $this->Encryp->decode($_GET['uri7']); 
							if($_POST){
								$convertir = new convertir();
								$sac_fecha = $convertir->get_fecha_mysql($_POST['sac_fecha']);
								$datosPost = array(
									"sac_id" => $sac_id, 
									"sac_fecha"=>$sac_fecha,
									"sac_hora"=>$_POST['sac_hora'].":00",
									"sac_accion"=>$_POST['sac_accion'],
									"sac_estado"=>$_POST['sac_estado'], 
									"sac_alerta"=>$_POST['sac_alerta']
								); 
								$objeto = new stdClass(); 
								$objeto->datosForm = json_encode($datosPost);
								$data = (array) $objeto;
								$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimientoAccion&tarea=modificar&token=" . $this->Usuario->get_token(), $data); 
								$jsonResp = json_decode($resp);
								$jsonResp = $jsonResp[0]->respuesta;
								if($jsonResp->accion=="correcto") { 
									$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje); 
									FUNCIONES::redirect(_base_url . "crm/seguimiento/acciones/".$_GET['uri4']);
								} else {
									$this->Mensaje->set_mensaje("error", $jsonResp->mensaje); 
									$this->seguimiento_acciones_view($sac_id);
								} 
							} else {
								$this->seguimiento_acciones_view($sac_id);
							} 
						} elseif ($_GET['uri6']=="eliminar") {
							$datosPost = array("id"=> $this->Encryp->decode($_GET['uri7']));
							$objeto = new stdClass();
							$objeto->datosForm = json_encode($datosPost);
							$data = (array) $objeto;
							$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimientoAccion&tarea=eliminar&token=" . $this->Usuario->get_token(), $data);
							$jsonResp = json_decode($resp);
							$jsonResp = $jsonResp[0]->respuesta;
							if($jsonResp->accion=="correcto"){
								$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
								FUNCIONES::redirect(_base_url . "crm/seguimiento/acciones/".$_GET['uri4']);
							} else {
								$this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
								FUNCIONES::redirect(_base_url . "crm/seguimiento/acciones/".$_GET['uri4']);
							}
						} else{
							if($_POST){
								$convertir = new convertir();
								$sac_fecha = $convertir->get_fecha_mysql($_POST['sac_fecha']);
								$datosPost = array(
									"sac_usu_id"=>$_POST['sac_usu_id'],
									"sac_int_id"=>$_POST['sac_int_id'],
									"sac_fecha"=>$sac_fecha,
									"sac_hora"=>$_POST['sac_hora'].":00",
									"sac_accion"=>$_POST['sac_accion'],
									"sac_seg_id"=>$this->Encryp->decode($_POST['sac_seg_id']),
									"sac_estado"=>"Pendiente", 
									"sac_alerta"=>$_POST['sac_alerta']
								);
								$objeto = new stdClass();
								$objeto->datosForm = json_encode($datosPost);
								$data = (array) $objeto;
								$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimientoAccion&tarea=agregar&token=" . $this->Usuario->get_token(), $data);
								$jsonResp = json_decode($resp);
								$jsonResp = $jsonResp[0]->respuesta;
								if($jsonResp->accion=="correcto") { 
									$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje); 
									FUNCIONES::redirect(_base_url . "crm/seguimiento/acciones/".$_GET['uri4']);
								} else {
									$this->Mensaje->set_mensaje("error", $jsonResp->mensaje); 
									$this->seguimiento_acciones_view();
								}
							} else {
								$this->seguimiento_acciones_view();
							}
						}
					break;
					default:
						$this->seguimiento_formulario_view();
				}
			}
			if($modulo=="calendario") {
				switch ($tarea) {
					case "view":
						$this->calendario_view();
					break;
				}
			}
			
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }
	
	function calendario_view(){
		/*
		$datosPost = array("id"=> $this->Encryp->decode($_GET['uri4']));
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);
        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimientoAccion&tarea=ver&token=" . $this->Usuario->get_token(), $data);
		$jsonResp = json_decode($resp);
		$jsonResp = $jsonResp[0]->respuesta;
		
		if($id >0 ){
			$modificar = $this->seguimiento_acciones_modificar($jsonResp->opciones, $id);
			if($modificar->estado){
				SISTEMA::obj_post($modificar->datos);
			}
		}
		*/
        $data = array(
			//'datos' => $jsonResp->datos,
			//'opciones' => $jsonResp->opciones,
            'view' => "calendario_view.php"
        ); 
        View::create('theme.php', $data); 
	}
	
	function seguimiento_lista_view(){
		
		$datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);
        $data = (array) $objeto;
		
		//Paginacion
		$pagina = "";
		if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
			$pagina = "&pagina=".$_GET['pagina'];
		} else {
			$pagina = "&pagina=0"; 
		}
		
		//buscar 
		$buscar = "";
		if (isset($_GET['buscar'])) {
			$buscar = "&buscar=".$_GET['buscar'];
		}
		
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=lista&token=" . $this->Usuario->get_token().$pagina.$buscar, $data);
		$jsonResp = json_decode($resp);                         
        $jsonResp = $jsonResp[0]->respuesta;
        $data = array(
			'opciones' => $jsonResp->opciones,
			'datos' => $jsonResp->datos,
            'view' => "seguimiento_listar.php"
        );
        View::create('theme.php', $data);
    }
	
    function seguimiento_formulario_view($form_visible="Si"){
		$datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);
        $data = (array) $objeto;
				
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimiento&tarea=tipoContacto&token=" . $this->Usuario->get_token(), $data);
		//echo $resp;
		
		$jsonResp = json_decode($resp);                         
        $jsonResp = $jsonResp[0]->respuesta;
        $data = array(
			'datos' => $jsonResp->datos,
			'form_visible' => $form_visible,
            'view' => "seguimiento_form.php"
        ); 
        View::create('theme.php', $data);
    }
	
	function seguimiento_acciones_view($id=0){
		$datosPost = array("id"=> $this->Encryp->decode($_GET['uri4']));
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);
        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=seguimientoAccion&tarea=ver&token=" . $this->Usuario->get_token(), $data);
		$jsonResp = json_decode($resp);
		$jsonResp = $jsonResp[0]->respuesta;
		
		if($id >0 ){
			$modificar = $this->seguimiento_acciones_modificar($jsonResp->opciones, $id);
			if($modificar->estado){
				SISTEMA::obj_post($modificar->datos);
			}
		}
		
        $data = array(
			'datos' => $jsonResp->datos,
			'opciones' => $jsonResp->opciones,
            'view' => "seguimiento_acciones.php"
        ); 
        View::create('theme.php', $data); 
	}
	function seguimiento_acciones_modificar($obj, $id){
		$respuesta = new stdClass();
		$respuesta->estado = FALSE;
		foreach ($obj as $key => $value) {
			if($value->sac_id == $id){
				$respuesta->datos = $value;
				$respuesta->estado = TRUE;
			}
		}
		return $respuesta;
	}
}


$controlador = new CRM();
$controlador->gestor();
