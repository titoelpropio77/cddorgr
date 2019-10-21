<?php


require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');


$usuario = new USUARIO;
if (!($usuario->get_aut() == _sesion)) {
    echo '{"response":"error", "mensaje":"No tiene permisos"}';
    return;
}



$filtro = "";
$conec = new ADO();
if ($_POST['fecha_inicio'] <> "") {
    $filtro.=" and lib_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']) . "' ";
    if ($_POST['fecha_fin'] <> "") {
        $filtro.=" and lib_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fecha_fin']) . "' ";
    }
} else {
    if ($_POST['fecha_fin'] <> "") {
        $filtro.=" and lib_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fecha_fin']) . "' ";
    }
}
$tlibro = $_POST['libro'];
$ges_id = $_POST['gestion'];
if ($tlibro == 'v') {
    $filtro.=" and lib_libro='Venta'";
} elseif ($tlibro == 'c') {
    $filtro.=" and lib_libro='Compra'";
}
$sql = "
                            select 
                                    lib_id, lib_tipo, lib_fecha,lib_fecha, lib_nit, lib_nro_autorizacion, lib_cod_control, lib_nro_factura, 
                                    lib_nro_poliza, lib_cliente, lib_tot_factura, lib_ice, lib_imp_exentos, lib_imp_neto, lib_iva,lib_estado,
                                    lib_libro, lib_cmp_id, lib_ges_id
                            from
                                    con_libro
                            where
                                    lib_ges_id='$ges_id' and lib_eliminado='No' $filtro order by lib_fecha";
//                    echo $sql;
$conec->ejecutar($sql);

$tit_libro = "Ventas";
if ($tlibro == 'v') {
    $tit_libro = "ventas";
} elseif ($tlibro == 'c') {
    $tit_libro = "compras";
}
$tit_periodo = substr($_POST['fecha_inicio'], 3);
$tit_periodo = str_replace("/", "", $tit_periodo);
$nit = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='nit'", "conf_valor");
$file = fopen('gen_davinci/' . $tit_libro . '_' . $tit_periodo . '_' . $nit . '.txt', "w+");
for ($i = 0; $i < $conec->get_num_registros(); $i++) {
    $lib = $conec->get_objeto();
    $texto = "";
    if ($tlibro == 'c')
        $texto.=$lib->lib_tipo . "|";
    $texto.=$lib->lib_nit . "|";
    $texto.=$lib->lib_cliente . "|";
    $texto.=$lib->lib_nro_factura . "|";
    if ($tlibro == 'c')
        $texto.=$lib->lib_nro_poliza . "|";
    $texto.=$lib->lib_nro_autorizacion . "|";
    $texto.=FUNCIONES::get_fecha_latina($lib->lib_fecha) . "|";
    $texto.=round($lib->lib_tot_factura, 2) . "|";
    $texto.=round($lib->lib_ice, 2) . "|";
    $texto.=round($lib->lib_imp_exentos, 2) . "|";
    $texto.=round($lib->lib_imp_neto, 2) . "|";
    $texto.=round($lib->lib_imp_iva, 2) . "|";
    if ($tlibro == 'v')
        $texto.=$lib->lib_estado . "|";
    if ($lib->lib_cod_control != '')
        $texto.=$lib->lib_cod_control;
    else
        $texto.="0";
    fwrite($file, $texto . "\n");
    $conec->siguiente();
}
fclose($file);
//                    echo 'Location: gen_davinci/'.$tit_libro.'_'.$tit_periodo.'_'.$nit.'.txt';
//                    header('Location: gen_davinci/'.$tit_libro.'_'.$tit_periodo.'_'.$nit.'.txt');
header("Content-type: application/x-file");
header('Content-Disposition: attachment; filename=' . $tit_libro . '_' . $tit_periodo . '_' . $nit . '.txt');
readfile('gen_davinci/' . $tit_libro . '_' . $tit_periodo . '_' . $nit . '.txt');
?>
