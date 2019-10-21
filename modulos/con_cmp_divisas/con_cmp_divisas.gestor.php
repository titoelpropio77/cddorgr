<?php

require_once('con_cmp_divisas.class.php');

$con_cmp_divisas = new con_cmp_divisas();

if ($_GET['tarea'] <> "") {
    if (!($con_cmp_divisas->verificar_permisos($_GET['tarea']))) {
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
                $con_cmp_divisas->emergente();
            } else {
                if ($con_cmp_divisas->datos()) {
                    $con_cmp_divisas->insertar_tcp();
                } else {
                    $con_cmp_divisas->formulario();
                }
            }
            break;
        }
    case 'VER': {
            $con_cmp_divisas->cargar_datos();

            $con_cmp_divisas->formulario_tcp();

            break;
        }
    case 'MODIFICAR': {
            if ($con_cmp_divisas->datos()) {
                $con_cmp_divisas->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_cmp_divisas->cargar_datos();
                }
                $con_cmp_divisas->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $con_cmp_divisas->eliminar_tcp();
                } else {
                    $con_cmp_divisas->dibujar_busqueda();
                }
            } else {
                $con_cmp_divisas->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_cmp_divisas->dibujar_busqueda();
}
?>