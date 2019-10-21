<?php

require_once('interno.class.php');

$interno = new INTERNO();

if ($_GET['tarea'] <> "") {
    if (!($interno->verificar_permisos($_GET['tarea']))) {
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
                $interno->emergente();
            } else {
                if ($interno->datos()) {
                    $interno->insertar_tcp();
                } else {
                    $interno->formulario_tcp('blanco');
                }
            }


            break;
        }
    case 'VER': {
            $interno->cargar_datos();

            $interno->formulario_tcp('ver');

            break;
        }
    case 'CUENTAS': {
            $interno->cuentas();

            break;
        }
    case 'MODIFICAR': {
            if ($_GET['acc'] == 'Imagen') {
                $interno->eliminar_imagen();
            } else {
                if ($interno->datos()) {
                    $interno->modificar_tcp();
                } else {
                    if (!($_POST)) {
                        $interno->cargar_datos();
                    }
                    $interno->formulario_tcp('cargar');
                }
            }

            break;
        }
    case 'ELIMINAR': {

            if (isset($_POST['int_id'])) {
                if (trim($_POST['int_id']) <> "") {
                    $interno->eliminar_tcp();
                } else {
                    $interno->dibujar_busqueda();
                }
            } else {
                $interno->formulario_confirmar_eliminacion();
            }


            break;
        }

    default: {
        if($_GET[acc]=='buscar' && $_GET[popup]=='1' ){
            $interno->buscar_interno();
        }else{
            $interno->dibujar_busqueda();
        }
        break;
    }
        
}
?>