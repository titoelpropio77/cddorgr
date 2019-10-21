<?php

require_once('caj_traspaso_brazil.class.php');
$caj_traspaso_brazil = new caj_traspaso_brazil();
if ($_GET['tarea'] <> "") {
    if (!($caj_traspaso_brazil->verificar_permisos($_GET['tarea']))) {
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
                $caj_traspaso_brazil->emergente();
            } else {
                if ($caj_traspaso_brazil->datos()) {
                    $caj_traspaso_brazil->insertar_tcp();
                } else {
                    $caj_traspaso_brazil->formulario();
                }
            }
            break;
        }
    case 'VER': {
            $caj_traspaso_brazil->cargar_datos();

            $caj_traspaso_brazil->formulario_tcp();

            break;
        }
    case 'MODIFICAR': {
            if ($caj_traspaso_brazil->datos()) {
                $caj_traspaso_brazil->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $caj_traspaso_brazil->cargar_datos();
                }
                $caj_traspaso_brazil->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['btras_id'])) {
                if (trim($_POST['btras_id']) <> "") {
                    $caj_traspaso_brazil->eliminar_tcp();
                } else {
                    $caj_traspaso_brazil->dibujar_busqueda();
                }
            } else {
                $caj_traspaso_brazil->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $caj_traspaso_brazil->dibujar_busqueda();
}
?>