<?php

require_once('con_banco.class.php');

$con_banco = new con_banco();

if ($_GET['tarea'] <> "") {
    if (!($con_banco->verificar_permisos($_GET['tarea']))) {
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
                $con_banco->emergente();
            } else {
                if ($con_banco->datos()) {
                    $con_banco->insertar_tcp();
                } else {
                    $con_banco->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_banco->cargar_datos();

            $con_banco->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_banco->datos()) {
                $con_banco->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_banco->cargar_datos();
                }
                $con_banco->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $con_banco->eliminar_tcp();
                } else {
                    $con_banco->dibujar_busqueda();
                }
            } else {
                $con_banco->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_banco->dibujar_busqueda();
}
?>