<?php

require_once('sorteo.class.php');

$cierre = new SORTEO();
ADO::$modulo = $cierre->modulo;
ADO::$modulo_id = $_GET[id];
ADO::$tarea = $_GET[tarea];

if ($_GET['tarea'] <> "") {
    if (!($cierre->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($cierre->datos()) {
                $cierre->insertar_tcp();
            } else {
                $cierre->formulario_tcp('blanco');
            }

            break;
        }
    case 'VER': {
            $cierre->cargar_datos();

            $cierre->ver_bonos2($_GET[id]);

            break;
        }
    case 'MODIFICAR': {

            if ($_POST) {
                $cierre->modificar_tcp();
            } else {
//                if (!($_POST)) {
//                    $cierre->cargar_datos();
//                }
                $cierre->formulario_tcp($_GET[id]);
            }

            break;
        }

    case 'IMPRIMIR': {
            $cierre->formulario_tcp($_GET[id]);
            break;
        }
    case 'ELIMINAR': {

            if (isset($_POST['cja_usu_id'])) {
                if (trim($_POST['cja_usu_id']) <> "") {
                    $cierre->eliminar_tcp();
                } else {
                    $cierre->dibujar_busqueda();
                }
            } else {
                $cierre->formulario_confirmar_eliminacion();
            }


            break;
        }
        
    case 'PARAMETROS': {

            if ($_POST) {
                $cierre->actualizar_parametros($_GET, $_POST);
            } else {
                $cierre->frm_parametros((object)$_GET, (object)$_POST);
            }


            break;
        }

    default:
        if ($cierre->verificar_permisos('ACCEDER')) {
            $cierre->dibujar_busqueda();
        } else {
            ?>
            <script>
                location.href = "log_out.php";
            </script>
            <?php

        }
}
?>