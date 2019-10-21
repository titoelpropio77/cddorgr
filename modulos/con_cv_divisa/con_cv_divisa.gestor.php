<?php

require_once('con_cv_divisa.class.php');
$con_cv_divisa = new con_cv_divisa();

if ($_GET['tarea'] <> "") {
    if (!($con_cv_divisa->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {

            if ($con_cv_divisa->datos()) {
                $con_cv_divisa->insertar_tcp();
            } else {
                $con_cv_divisa->formulario_tcp();
            }

            break;
        }
    case 'VER': {
            require_once('modulos/con_comprobante/con_comprobante.class.php');
            $con_comprobante = new con_comprobante();
            $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='cv_divisa' and cmp_tabla_id='$_GET[id]'");
            $con_comprobante->imprimir_cmp($cmp->cmp_id);
            
            break;
        }
    case 'ANULAR': {
            
            $con_cv_divisa->anular();
                

            break;
        }

    default: $con_cv_divisa->dibujar_busqueda();
}
?>