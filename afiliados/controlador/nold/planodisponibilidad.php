<?php

class PLANODISPONIBILIDAD {

    var $Usuario;
    var $Mensaje;

    function PLANODISPONIBILIDAD() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }

    function gestor() {

        if ($this->Usuario->sesion_iniciada()) {
            $this->index();
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }

    function index() {

        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=urbanizacion&tarea=lista&token=" . $this->Usuario->get_token(), $data);
        $jsonResp = json_decode($resp);
		$jsonResp = $jsonResp[0]->respuesta; 
		
        if ($jsonResp->accion == "correcto") {
            //$this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
            $data = array(
                'datos' => $jsonResp->datos,
                'view' => "planodisponibilidad.php"
            ); 
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "planodisponibilidad.php"
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

$controlador = new PLANODISPONIBILIDAD();
$controlador->gestor();
