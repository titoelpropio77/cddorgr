<?php

class EMAIL {

    public static function enviar_correo($datos) {
        
        $datos = (object) $datos;
        $asunto = $datos->asunto;
        $email = $datos->email;
        $mensaje = $datos->mensaje;
        
        $conec2 = new ADO();
        $sql2 = "SELECT * FROM ad_parametro";
        $conec2->ejecutar($sql2);
        $objeto2 = $conec2->get_objeto();

        require_once("clases/class.phpmailer.php");

        $mail = new PHPMailer();
        $mail->Mailer = "smtp";
        $mail->IsHTML(true);
        $mail->Port = 25;
        $mail->CharSet = "UTF-8";
        $mail->Host = "$objeto2->par_smtp";
        $mail->SMTPAuth = true;
        $mail->Username = "$objeto2->par_salida";
        $mail->Password = "$objeto2->par_pas_salida";
        $mail->From = "$objeto2->par_salida";
        $mail->FromName = "Sistema " . strip_tags(_nombre_empresa);
        $mail->Subject = utf8_encode($asunto);
        $mail->AddAddress($email);

        ob_start();
        ?>
        <div class="cuerpo"><?php echo $mensaje; ?></div>
        <?php
        $body = ob_get_contents();
        ob_clean();
        $mail->Body = $body;

        $mail->Send();
    }

}
?>