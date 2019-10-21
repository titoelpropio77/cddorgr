<?php

require_once('ter_sucursal.class.php');

$ter_sucursal = new ter_sucursal();

if ($_GET['tarea'] <> "") {
    if (!($ter_sucursal->verificar_permisos($_GET['tarea']))) {
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
                $ter_sucursal->emergente();
            } else {
                if ($ter_sucursal->datos()) {
                    $ter_sucursal->insertar_tcp();
                } else {
                    $ter_sucursal->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $ter_sucursal->cargar_datos();

            $ter_sucursal->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($ter_sucursal->datos()) {
                $ter_sucursal->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $ter_sucursal->cargar_datos();
                }
                $ter_sucursal->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $ter_sucursal->eliminar_tcp();
                } else {
                    $ter_sucursal->dibujar_busqueda();
                }
            } else {
                $ter_sucursal->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $ter_sucursal->dibujar_busqueda();
}
?>