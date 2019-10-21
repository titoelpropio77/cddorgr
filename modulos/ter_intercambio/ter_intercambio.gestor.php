<?php

require_once('ter_intercambio.class.php');

$intercambio = new ter_intercambio();

if ($_GET['tarea'] <> "") {
    if (!($intercambio->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {            
            if ($intercambio->datos()) {
                $intercambio->insertar_tcp();
            } else {
                $intercambio->formulario_tcp('blanco');
            }
            
            break;
        }
    case 'VER': {
            $intercambio->cargar_datos();

            $intercambio->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($intercambio->datos()) {
                $intercambio->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $intercambio->cargar_datos();
                }
                $intercambio->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $intercambio->eliminar_tcp();
                } else {
                    $intercambio->dibujar_busqueda();
                }
            } else {
                $intercambio->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $intercambio->dibujar_busqueda();
}
?>