<?php

require_once('vendedor_grupo.class.php');

$vendedor_grupo = new VENDEDOR_GRUPO();

if ($_GET['tarea'] <> "") {
    if (!($vendedor_grupo->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            if ($vendedor_grupo->datos()) {
                $vendedor_grupo->insertar_tcp();
            } else {
                $vendedor_grupo->formulario_tcp('blanco');
            }
            break;
        }
    case 'VER': {
            $vendedor_grupo->cargar_datos();

            $vendedor_grupo->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($vendedor_grupo->datos()) {
                $vendedor_grupo->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $vendedor_grupo->cargar_datos();
                }
                $vendedor_grupo->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            $vendedor_grupo->eliminar_tcp();
//            $vendedor_grupo->dibujar_busqueda();
            break;
        }
    default: $vendedor_grupo->dibujar_busqueda();
}
?>