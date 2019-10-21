<?php

require_once('con_compra.class.php');

$con_compra = new con_compra();
ADO::$modulo=$con_compra->modulo;
ADO::$modulo_id=$_GET[id];
ADO::$tarea=$_GET[tarea];
if ($_GET['tarea'] <> "") {
    if (!($con_compra->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            ADO::$modulo_id=-1;
            if ($_GET['acc'] == 'Emergente') {
                $con_compra->emergente();
            } else {
                if ($con_compra->datos()) {
                    $con_compra->insertar_tcp();
                } else {
                    $con_compra->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $con_compra->cargar_datos();

            $con_compra->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {
            if ($con_compra->datos()) {
                $con_compra->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_compra->cargar_datos();
                }
                $con_compra->formulario_tcp('cargar');
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['com_id'])) {
                if (trim($_POST['com_id']) <> "") {
                    $con_compra->eliminar_tcp();
                } else {
                    $con_compra->dibujar_busqueda();
                }
            } else {
                $con_compra->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_compra->dibujar_busqueda();
}
?>