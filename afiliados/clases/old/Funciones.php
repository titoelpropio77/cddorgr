<?php

class FUNCIONES {

    public static function redirect($url, $permanent = false) {
        if (headers_sent() === false) {
            header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
        }

        exit();
    }

    public static function log($datos) {
        echo "<pre>";
        print_r($datos);
        echo "<pre>";
    }

}
