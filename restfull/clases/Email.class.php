<?php
class EMAIL {
    var $config;
    function EMAIL() {
        // Iniciar Configuracion
        $sql = "SELECT par_smtp,par_salida,par_pas_salida,par_entrada FROM ad_parametro";
        $resultado = mysql_query($sql);
        $this->config = mysql_fetch_object($resultado);
    }
	
    function enviar($datos) {
        /*
         $datos->FromName
         $datos->Subject
         $datos->AddAddress
         $datos->Titulo
         $datos->Subtitulo
         $datos->Body 
        */
        require_once("phpmailer/class.phpmailer.php");
        $mail = new PHPMailer();
        $mail->Host = $this->config->par_smtp;
        $mail->Mailer = "smtp";
        $mail->Port = 26; 
        $mail->CharSet = "UTF-8"; 
        $mail->SMTPAuth = true;
        $mail->IsHTML(true);
        $mail->Username = $this->config->par_salida; 
        $mail->Password = $this->config->par_pas_salida;
        $mail->From = $this->config->par_salida;
        $mail->FromName = $datos->FromName;
        $mail->Subject = $datos->Subject;
		
        // nuevos 
        if($datos->AddBCC !=''){
                $mail->AddBCC(trim($datos->AddBCC)); 
        }

        if($datos->AddReplyTo !=''){
                $mail->AddReplyTo(trim($datos->AddReplyTo), "correo");
        }
		
        $titulos = '';
        if($datos->Titulo !=''){
            $titulos .='<h2 style="margin:0px;padding:0px;text-transform: capitalize;">'.$datos->Titulo.'</h2><br />';
        }
        if($datos->Subtitulo !=''){
            $titulos .='<h3 style="margin:0px;padding:0px;">'.$datos->Subtitulo.'</h3><br />';
        }
        
	//Verificar si hay que enviar a mas de un correo
        $buscarComa = strpos($datos->AddAddress, ",");
        if ($buscarComa === false) {
                $mail->AddAddress($datos->AddAddress, "Correo"); 
        } else {
            $correos = explode(",", $datos->AddAddress);
            foreach ($correos as $value) {
                $mail->AddAddress(trim($value), "Correo");  
            }
        }
		//http://www.ciudaddedios.com.bo/img/logo-ciudad-de-dios.jpg
		// <img src="' . _base_url. 'imagenes/micro_puertosantacruz.png" width="150" alt="Logo"/> 
        $mail->Body =   '<div style="background:#f2f2f2; width:100%; min-height:700px; padding: 20px 0px; "> 
                            <div style="box-shadow:1px 1px 10px 0px rgba(50, 50, 50, 0.67);-webkit-box-shadow: 1px 1px 10px 0px rgba(50, 50, 50, 0.67);-moz-box-shadow:1px 1px 10px 0px rgba(50, 50, 50, 0.67);width:700px; margin:0 auto; -webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px; border:1px solid #ccc; padding:30px;  overflow: hidden; height: auto; background:#FFFFFF;"> 
                                <table width="100%" cellpadding="0" cellspacing="0" border="0"  style=" ">
                                    <thead> 
                                        <tr>
                                            <td bgcolor="#F9F9F9" style="background: #F9F9F9; padding:20px; border-bottom:10px solid #E5E3E3;">
                                                <img src="http://cdd.sistema.com.bo/imagenes/logo.png" width="150" alt="Logo"/>
                                            </td>
                                            <td bgcolor="#F9F9F9" style="background: #F9F9F9; padding:20px; border-bottom:10px solid #E5E3E3;" align="right">
                                                <b>' . _nombre_empresa . '</b>
                                            </td> 
                                        </tr>
                                    </thead> 
                                    <tbody>
                                        <tr>
                                            <td colspan="2" style="border-bottom:10px solid #E5E3E3; padding:10px;">
                                                ' .$titulos.'
                                                <p>'.$datos->Body. '</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" style="background:#f6f6f6; color:#666; font-size:11px; text-align: center;">' ._datos_empresa. '</d> 
                                        </tr>
                                    </tfoot>
                                </table> 
                            </div> 
                            <div style="text-align: center; font-size: 11px; color: #444; padding: 10px 0;"> &copy; Desarollado por: <a href="http://www.orangegroup.com.bo" target="_blank" style="color: #666;">www.orangegroup.com.bo</a></div> 
                        </div>';
        
        $respuesta = new stdClass();
        if (!$mail->Send()) {
            $respuesta->estado = FALSE;
            $respuesta->mensaje = $mail->ErrorInfo;
        } else {
            $respuesta->estado = TRUE;
            $respuesta->mensaje = 'Correo enviado correctamente';
        }
        return $respuesta;
    }
}
?>