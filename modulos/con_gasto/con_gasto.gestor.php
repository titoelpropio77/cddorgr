<?php

require_once('con_gasto.class.php');

$gasto = new con_gasto();

if ($_GET['tarea'] <> "") {
    if (!($gasto->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {            
            if ($gasto->datos()) {
                $gasto->insertar_tcp();
            } else {
                $gasto->formulario_tcp('blanco');
            }
            
            break;
        }
    case 'VER': {
            $gasto->cargar_datos();

            $gasto->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($gasto->datos()) {
                $gasto->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $gasto->cargar_datos();
                }
                $gasto->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['gast_id'])) {
                if (trim($_POST['gast_id']) <> "") {
                    $gasto->eliminar_tcp();
                } else {
                    $gasto->dibujar_busqueda();
                }
            } else {
                $gasto->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $gasto->dibujar_busqueda();
}
?>