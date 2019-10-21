<?php

function mostrar_mensaje($ticket) {
    $ar = new FORMULARIO();
//$ar->ventana_volver("hola", "gestor.php?", "titulo", "Alerta");

    $mensaje = "<div style='clear:both'></div><div style='display:block;font-size:16px;font-family:Arial'><p style='text-align:left'>La <b>&uacute;ltima</b> transacci&oacute;n($ticket) ya fu&eacute; procesada, y se recibió una nueva solicitud para la misma transacci&oacute;n, esto puede deberse por:</p><br/><br/>
<p style='text-align:left'>a)Presion&oacute; m&aacute;s de una vez el boton de env&iacute;o.</p>
<p style='text-align:left'>b)Recarg&oacute; la p&aacute;gina actual.</p>
<p style='text-align:left'>c)Presion&oacute; la tecla F5.</p>
<p style='text-align:left'>d)Presion&oacute; el bot&oacute;n volver atr&aacute;s del navegador.</p>
<p style='text-align:left'>e)Su conexi&oacute;n a internet fu&eacute; interrumpida y usted recarg&oacute; la p&aacute;gina actual.</p>


<br/><br/><p style='text-align:left'>Por favor verifique que la transacci&oacute;n haya sido procesada correctamente.</p></div>";

//$mensaje = '';

    $ar->mensaje("Informacion", $mensaje);
   
}
?>


