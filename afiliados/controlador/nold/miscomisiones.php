<?php

class MISCOMISIONES {

    var $Usuario;
    var $Mensaje;

    function MISCOMISIONES() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }

    function gestor() {

        if ($this->Usuario->sesion_iniciada()) {
            
            if ($_POST) {
                $this->historial();
            } else if ($_GET['uri2'] == "historial") {
                $this->frm_historial();
            } else {
                $this->index();
            }
                        
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }
    
    function frm_historial(){
        $data = array();
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=comisiones&tarea=gestiones&token=" . $this->Usuario->get_token(), $data); 
        
        $jsonResp = json_decode($resp);      
        $jsonResp = $jsonResp[0]->respuesta; 
		
        if ($jsonResp->accion == "correcto") {            
            $data = array(
                'datos' => $jsonResp->datos,
                'view' => "miscomisiones_frm_historial.php"
            ); 
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "miscomisiones_frm_historial.php"
            );
            View::create('theme.php', $data);
        }
        
        
        //
        /*$data = array(
            'view' => "miscomisiones_frm_historial.php"
        );
        View::create('theme.php', $data);*/
    }
    
    function historial() {
//        echo "historial";
        $datosPost = array();
        $datosPost[ges_id] = $_POST[ges_id];
        $datosPost[pdo_ini] = $_POST[pdo_ini];
        $datosPost[pdo_fin] = $_POST[pdo_fin];        
        $datosPost[vdo_id] = $this->Usuario->get_vdo_id();
						
        $objeto = new stdClass();
						
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;		
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=comisiones&tarea=historial&token=" . $this->Usuario->get_token(), $data); 
        
       // echo $resp;
       // exit;
        
        $jsonResp = json_decode($resp);      
        $jsonResp = $jsonResp[0]->respuesta; 
        
        if ($jsonResp->accion == "correcto") {            
            
//            $this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
//            FUNCIONES::redirect(_base_url . "reserva/agregar");
            $data = array(
                'datos' => $jsonResp->datos,
                'view' => "historial.php"
            );
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "miscomisiones_frm_historial.php"
            );
            View::create('theme.php', $data);
        }
    }

    function index() {
		
        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=comisiones&tarea=listar&estado=".$_GET['uri2']."&token=" . $this->Usuario->get_token(), $data); 			
        
        $jsonResp = json_decode($resp);      
        $jsonResp = $jsonResp[0]->respuesta; 
		
        if ($jsonResp->accion == "correcto") {            
            $data = array(
                'datos' => $jsonResp->datos,
                'view' => "miscomisiones.php"
            ); 
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "miscomisiones.php"
            );
            View::create('theme.php', $data);
        }
    }

    function view() {
        $data = array(
            'view' => "planodisponibilidad.php"
        );
        View::create('theme.php', $data);
    }

}

$controlador = new MISCOMISIONES();
$controlador->gestor();
