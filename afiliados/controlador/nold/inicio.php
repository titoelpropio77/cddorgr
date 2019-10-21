<?php

class INICIO {

    var $Usuario; 
    var $Mensaje;

    function INICIO() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }
    
    function gestor() {
        if ($this->Usuario->sesion_iniciada()) {
            FUNCIONES::redirect(_base_url . "portada");
        } else {
            if ($_POST) {
                $this->login();
            } else {
                $this->view_formulario();
            }
        }
    }
    
    function login() {

        $data = (array) $_POST;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=login&tarea=login", $data);
		$jsonResp = json_decode($resp);
		$jsonResp = $jsonResp[0]->respuesta;

        if ($jsonResp->accion == "correcto") {

            // Iniciar session
            $this->Usuario->iniciar_sesion($jsonResp);
            $this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);

            FUNCIONES::redirect(_base_url . "portada");
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "login_formulario.php"
            );
            View::create('theme_login.php', $data);
        }
    }

    function view_formulario() {
        $data = array(
            'view' => "login_formulario.php"
        );
        View::create('theme_login.php', $data);
    }

}

$controlador = new INICIO();
$controlador->gestor();

