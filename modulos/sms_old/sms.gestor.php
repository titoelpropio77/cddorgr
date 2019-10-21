<?php

require_once('sms.class.php');

$com = new SMS();

if (!($com->verificar_permisos($_GET['tarea']))) {
    ?>
    <script>
        location.href = "log_out.php";
    </script>
    <?php

}

switch ($_GET['tarea']) {
    case 'ELIMINAR': {

            $com->eliminar_tcp();

            break;
        }
    case 'AGREGAR': {
            if ($com->datos()) {
                $com->insertar_tcp();
            } else {
                $com->formulario_tcp('blanco');
            }
            break;
        }
    case 'MODIFICAR': {
            if ($com->datos()) {
                $com->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $com->cargar_datos();
                }
                $com->formulario_tcp('cargar');
            }
            break;
        }
    case 'INICIAR': {

            $com->iniciar();

            break;
        }
    case 'PAUSAR': {

            $com->pausar();

            break;
        }

    case 'ENVIAR_SMS': {

            $com->enviar_sms();

            break;
        }
    case 'LECTORES': {

            $com->leidos();

            break;
        }

    case 'ACCEDER': {
            $com->dibujar_busqueda();
            break;
        }
case 'VER': {
            $com->ver_mensajes_generados();

            break;
        }
case 'CANCELAR ENVIO': {
            $com->cancelar_envio();
            break;
        }
}
?>