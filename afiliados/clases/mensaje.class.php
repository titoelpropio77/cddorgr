<?php

class MENSAJES {

    function MENSAJES() {
        
    }

    function set_mensaje($tipo, $mensaje) {
        switch ($tipo) {
            case 'error':
                $_SESSION['mensajes'] = '<div class="msMensaje bg-danger"><h4>Error</h4> ' . $mensaje . '</div>';
                break;
            case 'alerta':
                $_SESSION['mensajes'] = '<div class="msMensaje bg-warning"><h4>Alerta</h4>' . $mensaje . '</div>';
                break;
            case 'correcto':
                $_SESSION['mensajes'] = '<div class="msMensaje bg-success"><h4> Correcto</h4> ' . $mensaje . '</div>';
                break;
            case 'limpieza':
                $_SESSION['mensajes'] = '<div class="msMensaje bg-primary"><h4> Limpieza</h4> ' . $mensaje . '</div>';
                break;
            default:
                $_SESSION['mensajes'] = '<div class="msMensaje bg-info"><h4> Info</h4>' . $mensaje . '</div>';
        }
    }

    function get_mostrar() {
        $mensaje = $_SESSION['mensajes'];
        $_SESSION['mensajes'] = '';
        return $mensaje;
    }

}
