<?php

require_once('sms_panel.class.php');

$com = new SMS_PANEL();

if (!($com->verificar_permisos($_GET['tarea']))) {
    ?>
    <script>
        location.href = "log_out.php";
    </script>
    <?php

}

switch ($_GET['tarea']) {    
    case 'ACCEDER': {
            $com->dibujar_busqueda();
            break;
        }  
	
}
?>