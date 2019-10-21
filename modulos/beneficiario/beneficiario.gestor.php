<?php

require_once('beneficiario.class.php');

$beneficiario = new beneficiario();

if ($_GET['tarea'] <> "") {
    if (!($beneficiario->verificar_permisos($_GET['tarea']))) {
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
                $beneficiario->emergente();
            } else {
                if ($beneficiario->datos()) {
                    $beneficiario->insertar_tcp();
                } else {
                    $beneficiario->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $beneficiario->cargar_datos();

            $beneficiario->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($beneficiario->datos()) {
                $beneficiario->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $beneficiario->cargar_datos();
                }
                $beneficiario->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ben_id'])) {
                if (trim($_POST['ben_id']) <> "") {
                    $beneficiario->eliminar_tcp();
                } else {
                    $beneficiario->dibujar_busqueda();
                }
            } else {
                $beneficiario->formulario_confirmar_eliminacion();
            }

            break;
        }
    case 'VALE': {
            $beneficiario->descuentos();
            break;
        }

    default: $beneficiario->dibujar_busqueda();
}
?>