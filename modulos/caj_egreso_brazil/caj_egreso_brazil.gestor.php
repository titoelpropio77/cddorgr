<?php

require_once('caj_egreso_brazil.class.php');

$caj_egreso_brazil = new caj_egreso_brazil();

if ($_GET['tarea'] <> "") {
    if (!($caj_egreso_brazil->verificar_permisos($_GET['tarea']))) {
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
                $caj_egreso_brazil->emergente();
            } else {
                if ($caj_egreso_brazil->datos()) {
                    $caj_egreso_brazil->insertar_tcp();
                } else {
                    $caj_egreso_brazil->formulario();
                }
            }
            break;
        }
    case 'VER': {
            $caj_egreso_brazil->cargar_datos();

            $caj_egreso_brazil->formulario_tcp();

            break;
        }
    case 'MODIFICAR': {
            if ($caj_egreso_brazil->datos()) {
                $caj_egreso_brazil->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $caj_egreso_brazil->cargar_datos();
                }
                $caj_egreso_brazil->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $caj_egreso_brazil->eliminar_tcp();
                } else {
                    $caj_egreso_brazil->dibujar_busqueda();
                }
            } else {
                $caj_egreso_brazil->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $caj_egreso_brazil->dibujar_busqueda();
}
?>