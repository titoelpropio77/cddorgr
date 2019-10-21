<?php

require_once('ter_tipo_documento.class.php');

$ter_tipo_documento = new ter_tipo_documento();

if ($_GET['tarea'] <> "") {
    if (!($ter_tipo_documento->verificar_permisos($_GET['tarea']))) {
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
                $ter_tipo_documento->emergente();
            } else {
                if ($ter_tipo_documento->datos()) {
                    $ter_tipo_documento->insertar_tcp();
                } else {
                    $ter_tipo_documento->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $ter_tipo_documento->cargar_datos();

            $ter_tipo_documento->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($ter_tipo_documento->datos()) {
                $ter_tipo_documento->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $ter_tipo_documento->cargar_datos();
                }
                $ter_tipo_documento->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['ban_id'])) {
                if (trim($_POST['ban_id']) <> "") {
                    $ter_tipo_documento->eliminar_tcp();
                } else {
                    $ter_tipo_documento->dibujar_busqueda();
                }
            } else {
                $ter_tipo_documento->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $ter_tipo_documento->dibujar_busqueda();
}
?>