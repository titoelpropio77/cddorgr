<?php

require_once('ter_ubicacion.class.php');

$ubicacion = new ter_ubicacion();

if ($_GET['tarea'] <> "") {
    if (!($ubicacion->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            $ubicacion->agregar();
            
            break;
        }
    case 'VER': {
            $ubicacion->cargar_datos();

            $ubicacion->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            $ubicacion->modificar();
            
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $ubicacion->eliminar_tcp();
                } else {
                    $ubicacion->dibujar_busqueda();
                }
            } else {
                $ubicacion->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $ubicacion->main();
//    default: $ubicacion->dibujar_busqueda();
}
?>