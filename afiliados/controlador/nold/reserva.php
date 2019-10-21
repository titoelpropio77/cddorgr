<?php

class RESERVAS {

    var $Usuario;
    var $Mensaje;

    function RESERVAS() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }

    function gestor() {

        if ($this->Usuario->sesion_iniciada()) {
            if ($_GET['uri2'] == "agregar") {
                $this->agregar();
            } else if ($_POST) {
                $this->guardar();
            } else if ($_GET['uri2'] == "listar") {
                $this->listar();
            }
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }

    function agregar() {

        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=urbanizacion&tarea=obtener&que=urbanizacion&token=" . $this->Usuario->get_token(), $data);

        $jsonResp = json_decode($resp);
        $jsonResp = $jsonResp[0]->respuesta;
//        print_r($jsonResp);
        if ($jsonResp->accion == "correcto") {
            $data = array(
                'vdo_id' => $this->Usuario->get_vdo_id(),
                'datos' => $jsonResp->datos,
                'view' => "reserva_agregar.php"
            );
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "reserva_agregar.php"
            );
            View::create('theme.php', $data);
        }
    }

    function guardar() {
        
//        echo "<pre>POST";
//        print_r($_POST);
//        echo "</pre>";
//        
//        
//        
//        echo "<pre>GET";
//        print_r($_GET);
//        echo "</pre>";
//        
//        echo "<pre>FILES";
//        print_r($_FILES);
//        echo "</pre>";
        
        $nombre_archivo = "";
        if ($_FILES['cargar_archivo']['name'] <> "") {
//            echo "<p>entrando a subir la foto</p>";
            include_once 'clases/util.class.php';
            $resultado = UTIL::subir_archivo($_FILES['cargar_archivo']['name'], $_FILES['cargar_archivo']['tmp_name'], UTIL::$_FOTOS);

            if ($resultado->exito == 'si') {
                $nombre_archivo = $resultado->nombre_archivo;
            } else {
                echo "<p>mensaje => $resultado->mensaje</p>";
            }
        } else {
//            echo "<p>no entro a subir la foto</p>";
        }
        

        $datosPost = array();
        $datosPost[res_int_id] = $_POST[int_id];
        $datosPost[int_foto] = $nombre_archivo;
        $datosPost[res_lot_id] = $_POST[lot_id];
        $datosPost[res_nota] = $_POST[res_nota];
        $datosPost[res_moneda] = $_POST[res_moneda];
        $datosPost[res_urb_id] = $_POST[urb_id];
        $datosPost[res_monto_m2] = $_POST[m2];
        $datosPost[res_vdo_id] = $this->Usuario->get_vdo_id();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        echo $objeto->datosForm;
//        exit;
        
        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=reserva&tarea=agregar&token=" . $this->Usuario->get_token(), $data);

//        echo "<p style='color:green'>$resp</p>";
//        exit;

        $jsonResp = json_decode($resp);
        $jsonResp = $jsonResp[0]->respuesta;

        if ($jsonResp->accion == "correcto") {

            $this->Mensaje->set_mensaje("correcto", $jsonResp->mensaje);
            FUNCIONES::redirect(_base_url . "reserva/agregar");
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "reserva_agregar.php"
            );
            View::create('theme.php', $data);
        }
    }

    function listar() {

        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=reserva&tarea=listar&token=" . $this->Usuario->get_token(), $data);

        // var_dump($resp);
        // exit;

        $jsonResp = json_decode($resp);
        $jsonResp = $jsonResp[0]->respuesta;

        if ($jsonResp->accion == "correcto") {
            $data = array(
                'datos' => $jsonResp->datos,
                'view' => "reserva_listar.php"
            );
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "reserva_listar.php"
            );
            View::create('theme.php', $data);
        }
    }

    function index() {

        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=comisiones&tarea=listar&estado=" . $_GET['uri2'] . "&token=" . $this->Usuario->get_token(), $data);

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

$controlador = new RESERVAS();
$controlador->gestor();
