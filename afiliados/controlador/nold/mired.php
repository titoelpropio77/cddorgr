<?php

class MIRED {

    var $Usuario;
	var $Mensaje;

    function MIRED() {
        $this->Usuario = new USUARIO();
		$this->Mensaje = new MENSAJES();
    }

    function gestor() {
        if ($this->Usuario->sesion_iniciada()) {

            if ($_GET['uri2'] == "estructura") {
                $this->mostrar_red();
            } else if ($_GET['uri2'] == "datos-linea") {
                $this->datos_linea();
            }
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }
    
    function datos_linea(){
//        echo "mostrando datos de 1 y 2 linea...";
        
        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=mired&tarea=datos-linea&token=" . $this->Usuario->get_token(), $data); 
        
//        echo "$resp";
//        exit;
        $jsonResp = json_decode($resp);      
        
//        var_dump($jsonResp);
//        exit;
        $jsonResp = $jsonResp[0]->respuesta; 

//        echo $jsonResp;
//        exit;
        if ($jsonResp->accion == "correcto") {            
//        if (true) {             

            $data = array(                
                'datos' => $jsonResp->datos,
                'view' => "datos_linea.php"
            ); 
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "mired.php"
            );
            View::create('theme.php', $data);
        }
    }
    
    function mostrar_red(){
        $datosPost = array();
        $objeto = new stdClass();
        $objeto->datosForm = json_encode($datosPost);

        $data = (array) $objeto;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=mired&tarea=listar&token=" . $this->Usuario->get_token(), $data); 
        
//        echo "$resp";
//        exit;
        $jsonResp = json_decode($resp);      
//        var_dump($jsonResp);
//        exit;
        $jsonResp = $jsonResp[0]->respuesta; 

//        echo $jsonResp;
//        exit;
//        if ($jsonResp->accion == "correcto") {            
        if (true) {             

            $data = array(                
                'datos' => json_encode($jsonResp->datos),
                'view' => "mired.php"
            ); 
            View::create('theme.php', $data);
        } else {
            $this->Mensaje->set_mensaje("error", $jsonResp->mensaje);
            $data = array(
                'view' => "mired.php"
            );
            View::create('theme.php', $data);
        }
    }

}

$controlador = new MIRED();
$controlador->gestor();

