<?php

ini_set('display_errors', 'On');
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once("clases/mytime_int.php");
require_once('config/constantes.php');
require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');

//$arr_directores = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);//cods de legado
$arr_directores = array(85, 86);

foreach ($arr_directores as $dir_id) {
    $dir = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$dir_id");

    if ($dir) {

        $nivel = 1000;
        $profundidad = $dir->vdo_nivel + 1000;
        $red = MLM::obtener_red($dir->vdo_id, $nivel, TRUE, $profundidad);

        if (count($red) > 0) {
//            foreach ($red as $vdo_id) {
//
//                $vdo = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");
//
//                if ($vdo) {
//                    $sql_upd = "update vendedor set vdo_director_id=$dir->vdo_id where vdo_id = $vdo->vdo_id";
//                    FUNCIONES::bd_query($sql_upd);
//                }
//            }

            $s_ids = implode(',', $red);
            if ($s_ids != '') {
                $sql_upd = "update vendedor set vdo_director_id=$dir->vdo_id where vdo_id in ($s_ids)";
                echo "<p>$sql_upd;</p>";
//                FUNCIONES::bd_query($sql_upd);
            }
        }
    }
}
?>