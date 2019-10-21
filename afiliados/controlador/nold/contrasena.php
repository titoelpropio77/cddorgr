<?php

class CONTRASENA {

    var $Usuario;
    var $Mensaje;

    function CONTRASENA() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }

    function gestor() {
        if ($this->Usuario->sesion_iniciada()) {
            if ($_POST) {
                $this->modificar();
            } else {
                $this->view_formulario();
            }
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }

    function modificar() {

        $datosPost = array();
        $datosPost['usu_password_antiguo'] = trim($_POST['usu_password_antiguo']);
        $datosPost['usu_password'] = md5(trim($_POST['usu_password']));

        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=usuario&tarea=cambiar_contrasenia&token=" . $this->Usuario->get_token(), $data);
        $jsonResp = json_decode($resp);
		$jsonResp = $jsonResp[0]->respuesta;

        if ($jsonResp->accion == "correcto") {
            $this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
            $this->view_formulario();
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "contrasena_form.php"
            );
            View::create('theme.php', $data);
        }
    }

    function view_formulario() {
        $data = array(
            'view' => "contrasena_form.php"
        );
        View::create('theme.php', $data);
    }

}

$controlador = new CONTRASENA();
$controlador->gestor();

