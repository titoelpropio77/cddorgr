<?php
//$str="hola mundo me llamo richard";
//$pos=  strpos($str, 'richard');
//if($pos===false){
//    
//}
//require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';
//_db::open();
main();

function main() {
    ?>
    <a href="javascript:void(0);" id="partida_doble" class="link_tabs">REVISAR DOBLE PARTIDA</a>
    <a href="?tarea=cuentas_cero" class="link_tabs">CUENTAS EN CERO</a>
    <a href="?tarea=cuentas_raiz" class="link_tabs">CUENTAS RAIZ</a>
    <!--<a href="?tarea=cuentas_gestion" id="cuentas_gestion" class="link_tabs">CUENTAS GESTION</a>-->
    <a href="javascript:void(0);" id="cuentas_gestion" class="link_tabs">CUENTAS GESTION</a>
    <style>
        .link_tabs{
            color: #fff;
            background-color: #0000FF;
            text-decoration: none; padding: 1px 5px;
        }
        .link_tabs:hover{
            background-color: #0054ff;
        }
        .lista_tabla{
            border-collapse: collapse;
        }
        .lista_tabla thead th, .lista_tabla tbody td{
            border: 1px solid #000;
        }
    </style>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $('#partida_doble').click(function () {
            var rango = prompt('Ingrese rango:', '<?php echo $_GET[rango] * 1; ?>');
            if (rango) {
                location.href = '?tarea=doble_partida&rango=' + rango;
            }
        });

        $('#cuentas_gestion').click(function () {
            var ges_id = prompt('Ingrese la gestion de los comprobantes:', '<?php echo $_GET[ges_id] * 1; ?>');
            if (true) {
                location.href = '?tarea=cuentas_gestion&ges_id=' + ges_id;
            }
        });
    </script>
    <br><br>
    <?php
    $tarea = $_GET[tarea];
    if ($tarea == 'doble_partida') {
        doble_partida();
    } elseif ($tarea == 'cuentas_cero') {
        cuentas_cero();
    } elseif ($tarea == 'cuentas_raiz') {
        cuentas_raiz();
    } elseif ($tarea == 'cuentas_gestion') {
        corregir_cuenta_gestion();
    } elseif ($tarea == 'cmp_det') {
        detalle_comprobante();
    }
}

function corregir_cuenta_gestion() {
//    $sql_sel="select * from con_comprobante, con_comprobante_detalle, con_cuenta where 
//                cmp_ges_id!=cue_ges_id and cmp_id=cde_cmp_id and cde_cue_id=cue_id ";
//    $f_cmp_id = ($_GET[cmp_id] * 1 > 0) ? " and cmp_id='$_GET[cmp_id]'" : '';
    $f_ges_id = ($_GET[ges_id] * 1 > 0) ? " and cmp_ges_id='$_GET[ges_id]'" : '';
    $sql_sel = "select * from con_comprobante, con_comprobante_detalle, con_cuenta where 
                cmp_ges_id!=cue_ges_id and cmp_id=cde_cmp_id and cde_cue_id=cue_id and cmp_eliminado='No' $f_ges_id";

    $detalles = FUNCIONES::objetos_bd_sql($sql_sel);
    $conec = new ADO();
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det = $detalles->get_objeto();
        $_cuenta = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$det->cde_cue_id'");
        $cuenta = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_codigo='$_cuenta->cue_codigo' and cue_ges_id='$det->cmp_ges_id'");
        $sql_update = "update con_comprobante_detalle set cde_cue_id=$cuenta->cue_id where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='$det->cde_mon_id' and cde_secuencia='$det->cde_secuencia'";
        echo $sql_update . ';<br>';
//        $conec->ejecutar($sql_update, false, false);
        $detalles->siguiente();
    }
    echo "-- " . $detalles->get_num_registros() . "<br>";
}

function detalle_comprobante() {

    $sql_sel = "select * from con_comprobante_detalle where cde_cmp_id ='$_GET[cmp_id]'";
    $listado = FUNCIONES::lista_bd_sql($sql_sel);
//    echo "<pre>";
//    print_r($listado);
//    echo "</pre>";
    ?>
    <table class="lista_tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>cde cmp id</th>
                <th>cde mon id</th>
                <th>cde secuencia</th>
                <th>cde can id</th>
                <th>cde cco id</th>
                <th>cde cfl id</th>
                <th>cde cue id</th>
                <th>cde valor</th>
                <th style="min-width: 500px;">cde glosa</th>
                <th>cde eliminado</th>
                <th>cde libro</th>
                <th>cde cu</th>
                <th>cde idpadre</th>
                <th>cde int id</th>
                <th>cde fpago</th>
                <th>cde fpago ban nombre</th>
                <th>cde fpago ban nro</th>
                <th>cde fpago descripcion</th>
                <th>cde une id</th>
                <th>cde doc id</th>
                <th>cde cambiado</th>
                <th>cde cue id ant</th>
                <!--<th>DIFERENCIA</th>-->
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($listado as $fila) {
                ?>
                <tr>
                    <td><b style="color: #0000ff"><?php echo $i; ?></b></td> 
                    <td><?php echo $fila->cde_cmp_id; ?></td> 
                    <td><?php echo $fila->cde_mon_id; ?></td>
                    <td><?php echo $fila->cde_secuencia; ?></td>
                    <td><?php echo $fila->cde_can_id; ?></td>
                    <td><?php echo $fila->cde_cco_id; ?></td>
                    <td><?php echo $fila->cde_cfl_id; ?></td>
                    <td><?php echo $fila->cde_cue_id; ?></td>
                    <td><?php echo $fila->cde_valor; ?></td>
                    <td><?php echo $fila->cde_glosa; ?></td>
                    <td><?php echo $fila->cde_eliminado; ?></td>
                    <td><?php echo $fila->cde_libro; ?></td>
                    <td><?php echo $fila->cde_cu; ?></td>
                    <td><?php echo $fila->cde_idpadre; ?></td>
                    <td><?php echo $fila->cde_int_id; ?></td>
                    <td><?php echo $fila->cde_fpago; ?></td>
                    <td><?php echo $fila->cde_fpago_ban_nombre; ?></td>
                    <td><?php echo $fila->cde_fpago_ban_nro; ?></td>
                    <td><?php echo $fila->cde_fpago_descripcion; ?></td>
                    <td><?php echo $fila->cde_une_id; ?></td>
                    <td><?php echo $fila->cde_doc_id; ?></td>
                    <td><?php echo $fila->cde_cambiado; ?></td>
                    <td><?php echo $fila->cde_cue_id_ant; ?></td>
                </tr>
        <?php
        $i++;
    }
    ?>
        </tbody>
    </table>
    <?php
}

function cuentas_raiz() {
    $sql_sel_grupo = "select * from con_cuenta where cue_codigo = ''";
    $cuentas = FUNCIONES::lista_bd_sql($sql_sel_grupo);
    $a_cuentas = array();
    foreach ($cuentas as $cuenta) {
        $a_cuentas[] = $cuenta->cue_id;
    }


    $str_cuentas = implode(',', $a_cuentas);
    $sql_sel = "select distinct cde_cmp_id from con_comprobante_detalle where cde_cue_id in ($str_cuentas)";
    $listado = FUNCIONES::lista_bd_sql($sql_sel);
//    echo "<pre>";
//    print_r($listado);
//    echo "</pre>";
    ?>
    <table class="lista_tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>NRO</th>
                <th>TIPO</th>
                <th>FECHA</th>
                <th>GLOSA</th>
                <th>TABLA</th>
                <th>TABLA ID</th>
                <th>USU CRE</th>
                <th>ELIMINADO</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $array_tipo = array('1' => 'ING', '2' => 'EGR', '3' => 'DIARIO');
            $i = 1;
            foreach ($listado as $fila) {
//        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
                $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id and cmp_eliminado='No'");
                if ($cmp == NULL) {
                    $i++;
                    continue;
                }
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $cmp->cmp_id; ?></td>
                    <td><?php echo $cmp->cmp_nro; ?></td>
                    <td><?php echo $array_tipo[$cmp->cmp_tco_id]; ?></td>
                    <td><?php echo $cmp->cmp_fecha; ?></td>
                    <td><?php echo $cmp->cmp_glosa; ?></td>
                    <td><?php echo $cmp->cmp_tabla; ?></td>
                    <td><?php echo $cmp->cmp_tabla_id; ?></td>
                    <td><?php echo $cmp->cmp_usu_cre; ?></td>
                    <td><?php echo $cmp->cmp_eliminado; ?></td>
                    <td>
                        <a href="script_revisar_contabilidad.php?tarea=doble_partida&tarea=cmp_det&cmp_id=<?php echo $cmp->cmp_id ?>">DET</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=VER&id=<?php echo $cmp->cmp_id ?>">VER</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=MODIFICAR&id=<?php echo $cmp->cmp_id ?>">MOD</a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </tbody>
    </table>
    <?php
}

function cuentas_cero() {
    $sql_sel = "select distinct cde_cmp_id from con_comprobante_detalle where cde_cue_id=0";
    echo "$sql_sel<br>";
    $listado = FUNCIONES::lista_bd_sql($sql_sel);
//    echo "<pre>";
//    print_r($listado);
//    echo "</pre>";
    ?>
    <table class="lista_tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>NRO</th>
                <th>TIPO</th>
                <th>FECHA</th>
                <th>GLOSA</th>
                <th>TABLA</th>
                <th>TABLA ID</th>
                <th>USU CRE</th>
                <th>ELIMINADO</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $array_tipo = array('1' => 'ING', '2' => 'EGR', '3' => 'DIARIO');
            $i = 1;
            foreach ($listado as $fila) {
//        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
                $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id and cmp_eliminado='No'");
                if ($cmp == NULL) {
                    $i++;
                    continue;
                }
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $cmp->cmp_id; ?></td>
                    <td><?php echo $cmp->cmp_nro; ?></td>
                    <td><?php echo $array_tipo[$cmp->cmp_tco_id]; ?></td>
                    <td><?php echo $cmp->cmp_fecha; ?></td>
                    <td><?php echo $cmp->cmp_glosa; ?></td>
                    <td><?php echo $cmp->cmp_tabla; ?></td>
                    <td><?php echo $cmp->cmp_tabla_id; ?></td>
                    <td><?php echo $cmp->cmp_usu_cre; ?></td>
                    <td><?php echo $cmp->cmp_eliminado; ?></td>
                    <td>
                        <a href="script_revisar_contabilidad.php?tarea=doble_partida&tarea=cmp_det&cmp_id=<?php echo $cmp->cmp_id ?>">DET</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=VER&id=<?php echo $cmp->cmp_id ?>">VER</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=MODIFICAR&id=<?php echo $cmp->cmp_id ?>">MOD</a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </tbody>
    </table>
    <?php
}

function doble_partida() {
    $sql_sel = "select cde_cmp_id, abs(sum(cde_valor)) as valor from con_comprobante_detalle group by cde_cmp_id
        having (abs(sum(cde_valor))>$_GET[rango]);";
    $listado = FUNCIONES::lista_bd_sql($sql_sel);
//    echo "<pre>";
//    print_r($listado);
//    echo "</pre>";
    ?>
    <table class="lista_tabla">
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>NRO</th>
                <th>TIPO</th>
                <th>FECHA</th>
                <th>GESTION</th>
                <th>GLOSA</th>
                <th>TABLA</th>
                <th>TABLA ID</th>
                <th>USU CRE</th>
                <th>ELIMINADO</th>
                <th>FECHA CRE</th>
                <th>DIFERENCIA</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $array_tipo = array('1' => 'ING', '2' => 'EGR', '3' => 'DIARIO');
            $i = 1;
            foreach ($listado as $fila) {
//        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
                $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id and cmp_eliminado='No'");
                if ($cmp == NULL) {
                    // $i++;
                    continue;
                }
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $cmp->cmp_id; ?></td>
                    <td><?php echo $cmp->cmp_nro; ?></td>
                    <td><?php echo $array_tipo[$cmp->cmp_tco_id]; ?></td>
                    <td><?php echo $cmp->cmp_fecha; ?></td>
                    <td><?php echo $cmp->cmp_ges_id; ?></td>
                    <td><?php echo $cmp->cmp_glosa; ?></td>
                    <td><?php echo $cmp->cmp_tabla; ?></td>
                    <td><?php echo $cmp->cmp_tabla_id; ?></td>
                    <td><?php echo $cmp->cmp_usu_cre; ?></td>
                    <td><?php echo $cmp->cmp_eliminado; ?></td>
                    <td><?php echo $cmp->cmp_fecha_cre; ?></td>
                    <td><?php echo $fila->valor; ?></td>
                    <td>
                        <a href="script_revisar_contabilidad.php?tarea=doble_partida&tarea=cmp_det&cmp_id=<?php echo $cmp->cmp_id ?>">DET</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=VER&id=<?php echo $cmp->cmp_id ?>">VER</a>
                        <a href="gestor.php?mod=con_comprobante&tarea=MODIFICAR&id=<?php echo $cmp->cmp_id ?>">MOD</a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </tbody>
    </table>
    <?php
}
