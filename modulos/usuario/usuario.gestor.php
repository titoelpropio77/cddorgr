<?php

require_once('usuario.class.php');

$usuario = new USU();

if ($_GET['tarea'] <> "") {
    if (!($usuario->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($_GET['acc'] == 'Emergente') {
                $usuario->emergente();
            } else {

                if ($usuario->datos()) {
                    $usuario->insertar_tcp();
                } else {
                    $usuario->formulario_tcp('blanco');
                }
            }

            break;
        }
    case 'VER': {
            $usuario->cargar_datos();

            $usuario->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($usuario->datos()) {
                $usuario->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $usuario->cargar_datos();
                }
                $usuario->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['usu_id'])) {
                if (trim($_POST['usu_id']) <> "") {
                    $usuario->eliminar_tcp();
                } else {
                    $usuario->dibujar_busqueda();
                }
            } else {
                $usuario->formulario_confirmar_eliminacion();
            }

            break;
        }
    case 'PARAMETROS': {
            $usuario->paramentros();
            break;
        }
    case 'CONFIGURAR RECIBO': {
            $usuario->configurar_recibo();
            break;
        }

    default: $usuario->dibujar_busqueda();
}
?>