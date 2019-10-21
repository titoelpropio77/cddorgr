<?php

class LOG {

    var $prefijo;
    var $path;
    var $separador;
    var $archivo_log;
    static $arch_log;

    public function LOG() {
        
    }

    function __construct($path, $prefijo) {

        $this->prefijo = $prefijo;
        $this->path = $path;
//        echo "<p style='color:blue;'>{$this->path}</p>";
        chdir('../');
        echo "<p style='color:blue;'>ENV:" . _ENTORNO . "</p>";
        if (_ENTORNO == 'DEV') {
            $this->separador = '\\';
        } else {
            $this->separador = '/';
        }
    }

    function escribir_entrada($texto, $tipo) {

        $texto = str_replace(PHP_EOL, " ", $texto);
        
        $hoy = date("Ymd");
        $archivo = $this->path . $this->separador . "logs" . $this->separador .
                $this->prefijo . $this->separador .
                $this->prefijo . "_" . $hoy . ".log";
        $this->archivo_log = $archivo;
//        $archivo = realpath('.');
//        echo "<p style='color:red'>$archivo</p>";
//        return;
        $arch = fopen($archivo, "a+");
        fwrite($arch, "[" . date("Y-m-d H:i:s.u") . " " . $_SERVER['REMOTE_ADDR'] . " " .
                $_SERVER['HTTP_X_FORWARDED_FOR'] . " - $tipo ] " . $texto  . "\n");
        fclose($arch);
    }

    public static function set_archivo_log($arc) {
        self::$arch_log = $arc;
        echo self::$arch_log;
    }

    public static function add_log($texto, $tipo) {
        $archivo = self::$arch_log;
        $arch = fopen($archivo, "a+");
        fwrite($arch, "[" . date("Y-m-d H:i:s.u") . " " . $_SERVER['REMOTE_ADDR'] . " " .
                $_SERVER['HTTP_X_FORWARDED_FOR'] . " - $tipo ] " . $texto . "\n");
        fclose($arch);
    }
    
    function notificar_a_correo(){
        $arch = fopen($this->archivo_log, 'r');
        $cant_lectura = filesize($this->archivo_log);
        $contenido = fread($arch, $cant_lectura);
        
        require_once("class.phpmailer.php");

        $mail = new PHPMailer();
        $mail->Mailer = "smtp";
        $mail->IsHTML(true);
        $mail->Port = 25;
        $mail->CharSet = "UTF-8";
        $mail->Host = "mail.sistema.com.bo";
        $mail->SMTPAuth = true;
        $mail->Username = "jardinesdelurubo@sistema.com.bo";
        $mail->Password = "qw5DaebJ";
        $mail->From = "jardinesdelurubo@sistema.com.bo";
        $mail->FromName = "LOGS - CDD";
        $mail->Subject = utf8_encode($this->archivo_log);
        $mail->AddAddress("hugo.ribera.p@gmail.com");
  
        $contenido = str_replace(PHP_EOL, "<br/>", $contenido);
        $mail->Body = $contenido;

        $mail->Send();
    }

}
?>