<?php

class PORTADA {

    var $Usuario;

    function PORTADA() { 
        $this->Usuario = new USUARIO();
    }

    function gestor() {
        
        if ($this->Usuario->sesion_iniciada()) {
            $this->index();
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }

    function index() {
        $data = array(
            'view' => "portada.php"
        );
        View::create('theme.php', $data);
    }

}

$controlador = new PORTADA();
$controlador->gestor();

