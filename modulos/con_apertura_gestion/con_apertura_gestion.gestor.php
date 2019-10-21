<?php

require_once('con_apertura_gestion.class.php');

$con_apertura_gestion = new con_apertura_gestion();

if ($_GET['tarea'] <> "") {
    if (!($con_apertura_gestion->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

$con_apertura_gestion->main();

//switch ($_GET['tarea']) {
//    case 'ACCEDER': {
//            $con_apertura_gestion->main();
//            break;
//        }
//    
//    default: echo '';
//}
?>