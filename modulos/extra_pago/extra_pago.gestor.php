<?php

require_once('extra_pago.class.php');

$epago = new EXTRA_PAGO();

if ($_GET['tarea'] <> "") {
    if (!($epago->verificar_permisos($_GET['tarea']))) {
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
                $epago->emergente();
            } else {
                if ($epago->datos()) {
                    $epago->insertar_tcp();
                } else {
                    $epago->formulario_tcp('blanco');
                }
            }
            break;
        }
    case 'VER': {
            $epago->nota_pago($_GET['id']);
            break;
        }

    case 'ANULAR': {
            $epago->anular();
            break;
        }
    case 'PAGOS': {
            $epago->pagos();
            break;
        }
    
    default: $epago->dibujar_busqueda();
}
?>