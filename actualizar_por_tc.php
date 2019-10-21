<?php
ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/modelo_comprobantes.class.php';

//require_once 'clases/comision.class.php';

if ($_POST) {
    procesar();
} else {
    formulario();
}

function formulario() {
    ?>
    <script src="js/jquery-1.7.1.min.js"></script>
    <script src="js/util.js"></script>
    <form id="frm_datos" name="frm_datos" method="POST" enctype="">
        <select id="gestion" name="gestion">
            <?php
            for ($i = 2015; $i <= 2018; $i++) {
                ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php
            }
            ?>
        </select>
        <select id="periodo" name="periodo">    
            <option value="01">ENERO</option>
            <option value="02">FEBRERO</option>
            <option value="03">MARZO</option>
            <option value="04">ABRIL</option>
            <option value="05">MAYO</option>
            <option value="06">JUNIO</option>
            <option value="07">JULIO</option>
            <option value="08">AGOSTO</option>
            <option value="09">SEPTIEMBRE</option>
            <option value="10">OCTUBRE</option>
            <option value="11">NOVIEMBRE</option>
            <option value="12">DICIEMBRE</option>
        </select>
        <input type="hidden" id="ges_pdo" name="ges_pdo" value=""/>
        <input type="button" id="btn_enviar" name="btn_enviar" value="Procesar"/>
    </form>
    <script>
        $('#btn_enviar').click(function () {
            console.log('sdjflkdjfkjd');

            var gestion = $('#gestion option:selected').val();
            if (gestion == '') {
                $.prompt('Elija la gestion');
            }
            var periodo = $('#periodo option:selected').val();

            if (periodo == '') {
                $.prompt('Elija el periodo');
            }
            var ges_pdo = gestion + '-' + periodo;
            $('#ges_pdo').val(ges_pdo);
            console.log('enviando el formulario...' + ges_pdo);
            $('#frm_datos').submit();
        });
    </script>
    <?php
}

function procesar() {

//    FUNCIONES::print_pre($_POST);
//    return;
    $ges_pdo = $_POST[ges_pdo];
    $tc = 6.96;
//$f_mon_id = " and cmp_mon_id='1'";
//$f_tco_id = " and cmp_tco_id='1'";
//$f_fecha = " and cmp_fecha='2017-05-05'";
// $f_cmp_id = "and cmp_id='22920'";

    $sql = "SELECT *
    FROM con_comprobante
    WHERE 1
    $f_mon_id $f_tco_id $f_fecha $f_cmp_id
    AND LEFT(cmp_fecha,7)='$ges_pdo'
    AND cmp_eliminado='No'";

// $sql = "SELECT *
// FROM con_comprobante
// WHERE 1
// $f_mon_id $f_tco_id $f_fecha $f_cmp_id
// and cmp_id='103009'";

    $nom_archivo = "actualizar_por_tc.sql";
    $fp = fopen($nom_archivo, "w");
    /*
      $nombre_archivo = "actualizar_por_tc.sql";
      header("Content-Type: text/plain");
      header("content-disposition: attachment;filename=" . $nombre_archivo);
     */

    echo "<p>-- $sql;</p>";
    
    ob_start();


    $comprobantes = FUNCIONES::objetos_bd_sql($sql);

    $cont = 0;
    $cont_regs = 0;
    for ($i = 0; $i < $comprobantes->get_num_registros(); $i++) {
        $cont++;
        $cont_regs++;
        $cmp = $comprobantes->get_objeto();
        if ($cmp->cmp_mon_id == '1') {
            convertir_bs($cmp);
        } else if ($cmp->cmp_mon_id == '2') {
            convertir_usd($cmp);
        } else {
            echo "<p>NO SE ENCUENTRA NI EN BS. NI USD.</p>";
        }
        $comprobantes->siguiente();

        if ($cont_regs == 500) {
            $cont_regs = 0;
            $s_res = ob_get_contents();
            ob_end_clean();

            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace(PHP_EOL, ' ', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);

            fputs($fp, $s_res);
            ob_start();
        }
    }
    echo "<p> -- PROCESADOS $cont comprobantes...</p>";


    $s_res = ob_get_contents();
    ob_end_clean();

    $s_res = str_replace('<p>', '', $s_res);
    $s_res = str_replace(PHP_EOL, ' ', $s_res);
    $s_res = str_replace('</p>', PHP_EOL, $s_res);

    fputs($fp, $s_res);
    fclose($fp);

    /*
      echo $s_res;
     */
}

function convertir_bs($cmp) {
    global $tc;
//    echo "<p>tc desde bs.:$tc</p>";
    $sql_det = "select * from con_comprobante_detalle where cde_cmp_id='$cmp->cmp_id'
    and cde_mon_id='$cmp->cmp_mon_id'";
    $detalles = FUNCIONES::objetos_bd_sql($sql_det);

    echo "<p> -- CMP_ID:$cmp->cmp_id.</p>";
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det = $detalles->get_objeto();
        $valor_sus = FUNCIONES::atributo_bd_sql("select cde_valor as campo from con_comprobante_detalle "
                        . "where cde_cmp_id='$det->cde_cmp_id'
        and cde_mon_id='2' and cde_secuencia='$det->cde_secuencia'") * 1;

        // $valor = round($det->cde_valor / $tc, 6);        
        $valor = $det->cde_valor / $tc;
        // if ($valor != $valor_sus) {
        if (true) {
            echo "<p>-- valor:$valor - valor_sus:$valor_sus</p>";
            $sql_upd = "update con_comprobante_detalle set cde_valor='$valor' where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='2' and cde_secuencia='$det->cde_secuencia'";
            echo "<p> $sql_upd;</p>";
        }
        $detalles->siguiente();
    }
    echo "<p> -- CMP_ID:$cmp->cmp_id.</p>";
}

function convertir_usd($cmp) {
    global $tc;
//    echo "<p>tc desde usd.:$tc</p>";
    $sql_det = "select * from con_comprobante_detalle where cde_cmp_id='$cmp->cmp_id'
    and cde_mon_id='$cmp->cmp_mon_id'";
    $detalles = FUNCIONES::objetos_bd_sql($sql_det);

    echo "<p> -- CMP_ID:$cmp->cmp_id.</p>";
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det = $detalles->get_objeto();
        $valor_bs = FUNCIONES::atributo_bd_sql("select cde_valor as campo from con_comprobante_detalle "
                        . "where cde_cmp_id='$det->cde_cmp_id'
        and cde_mon_id='1' and cde_secuencia='$det->cde_secuencia'") * 1;

        // $valor = round($det->cde_valor * $tc, 6);        
        $valor = $det->cde_valor * $tc;
        // if ($valor != $valor_bs) {
        if (true) {
            echo "<p>-- valor:$valor - valor_sus:$valor_bs</p>";
            $sql_upd = "update con_comprobante_detalle set cde_valor='$valor' where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='1' and cde_secuencia='$det->cde_secuencia'";
            echo "<p> $sql_upd;</p>";
        }
        $detalles->siguiente();
    }
    echo "<p> -- CMP_ID:$cmp->cmp_id.</p>";
}
?>
