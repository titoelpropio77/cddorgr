<?php

class AJAX {

    var $Usuario;
    var $Mensaje;

    function AJAX() {
        $this->Usuario = new USUARIO();
        $this->Mensaje = new MENSAJES();
    }

    function gestor() {

        if ($this->Usuario->sesion_iniciada()) {
            $this->index($_REQUEST);
        } else {
            FUNCIONES::redirect(_base_url . "inicio");
        }
    }

    function index($params) {

        $tarea = $params[tarea];

        $datosPost = array();
        switch ($tarea) {
            case "listar": {
                    $que = $params[que];
                    $str = "mod=urbanizacion&tarea=obtener&que=$que";
                    if ($que == 'uv') {
                        $str .= "&urb_id=$params[urb_id]";
                    }
                    if ($que == 'manzano') {
                        $str .= "&urb_id=$params[urb_id]&uv_id=$params[uv_id]";
                    }
                    if ($que == 'lote') {
                        $str .= "&urb_id=$params[urb_id]&uv_id=$params[uv_id]&man_id=$params[man_id]";
                    }
                    break;
                }

            case "personas": {
                    $criterio = $_GET[input];
                    $str = "mod=cliente&tarea=clientes&criterio=$criterio";
                    break;
                }

            case "periodos": {
                    $ges_id = $params[ges_id];
                    $str = "mod=comisiones&tarea=periodos&ges_id=$ges_id";
                    break;
                }

            case "add_cliente": {
                    $str = "mod=cliente&tarea=agregar";
                    // $datosPost = $_POST;
                    $datosPost = $_POST[datosForm];
                    
                    //añadido aqui por conflictos
                    $datosPost = $this->html_encode($datosPost);
                    $datosPost = str_replace('\"', '"', $datosPost);
                    break;
                }

            case "leer_notificacion": {
                    $str = "mod=login&tarea=leer_notificacion&not_id=" . $params[not_id];
                    break;
                }
        }

//        $datosPost = $this->html_encode($datosPost);
//        $datosPost = str_replace('\"', '"', $datosPost);

        $data = array();
        $data['datosForm'] = $datosPost;
        $resp = SISTEMA::send_post(_servicio_url . "gestor.php?" . $str . "&token=" . $this->Usuario->get_token(), $data);

        echo $resp;
    }
    public function html_encode($str) {
        $str = str_replace('Á', '&Aacute;', $str);
        $str = str_replace('á', '&aacute;', $str);
        $str = str_replace('É', '&Eacute;', $str);
        $str = str_replace('é', '&eacute;', $str);
        $str = str_replace('Í', '&Iacute;', $str);
        $str = str_replace('í', '&iacute;', $str);
        $str = str_replace('Ó', '&Oacute;', $str);
        $str = str_replace('ó', '&oacute;', $str);
        $str = str_replace('Ú', '&Uacute;', $str);
        $str = str_replace('ú', '&uacute;', $str);
        $str = str_replace('Ñ', '&Ntilde;', $str);
        $str = str_replace('ñ', '&ntilde;', $str);
        return $str;
    }

    public function html_decode($str) {
        $str = str_replace('&Aacute;', 'Á', $str);
        $str = str_replace('&aacute;', 'á', $str);
        $str = str_replace('&Eacute;', 'É', $str);
        $str = str_replace('&eacute;', 'é', $str);
        $str = str_replace('&Iacute;', 'Í', $str);
        $str = str_replace('&iacute;', 'í', $str);
        $str = str_replace('&Oacute;', 'Ó', $str);
        $str = str_replace('&oacute;', 'ó', $str);
        $str = str_replace('&Uacute;', 'Ú', $str);
        $str = str_replace('&uacute;', 'ú', $str);
        $str = str_replace('&Ntilde;', 'Ñ', $str);
        $str = str_replace('&ntilde;', 'ñ', $str);
        return $str;
    }

}

$controlador = new AJAX();
$controlador->gestor();
