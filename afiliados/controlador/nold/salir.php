<?php

class SALIR {

    var $Usuario;

    function SALIR() {
        $this->Usuario = new USUARIO();
    }

    function gestor() {
        if ($this->Usuario->sesion_iniciada()) {
            $this->Usuario->cerrar_sesion();
            FUNCIONES::redirect(_base_url . "inicio");
        } else {
            //FUNCIONES::redirect(_base_url . "inicio");
        }
    }
}

$controlador = new SALIR();
$controlador->gestor();


