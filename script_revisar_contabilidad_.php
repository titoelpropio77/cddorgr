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
        $('#partida_doble').click(function(){
            var rango=prompt('Ingrese rango:','<?php echo $_GET[rango]*1;?>');
            if(rango){
                location.href='?tarea=doble_partida&rango='+rango;
            }
        });
    </script>
        <br><br>
    <?php
    $tarea=$_GET[tarea];
    if($tarea=='doble_partida'){
        doble_partida();
    }elseif($tarea=='cuentas_cero'){
        cuentas_cero();
    }elseif($tarea=='cuentas_raiz'){
        cuentas_raiz();
    }
    
}
function cuentas_raiz() {
    $sql_sel_grupo="select group_concat(cue_id) as campo from con_cuenta where cue_codigo = ''";
    $str_cuentas=  FUNCIONES::atributo_bd_sql($sql_sel_grupo);
    $sql_sel="select distinct cde_cmp_id from con_comprobante_detalle where cde_cue_id in ($str_cuentas)";
    $listado=  FUNCIONES::lista_bd_sql($sql_sel);
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
                <!--<th>DIFERENCIA</th>-->
            </tr>
        </thead>
        <tbody>
    <?php
    $array_tipo=array('1'=>'ING','2'=>'EGR','3'=>'DIARIO');
    $i=1;
    foreach ($listado as $fila) {
        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
        ?>
            <tr>
                <td><?php echo $i;?></td>
                <td><?php echo $cmp->cmp_id;?></td>
                <td><?php echo $cmp->cmp_nro;?></td>
                <td><?php echo $array_tipo[$cmp->cmp_tco_id];?></td>
                <td><?php echo $cmp->cmp_fecha;?></td>
                <td><?php echo $cmp->cmp_glosa;?></td>
                <td><?php echo $cmp->cmp_tabla;?></td>
                <td><?php echo $cmp->cmp_tabla_id;?></td>
                <td><?php echo $cmp->cmp_usu_cre;?></td>
                <td><?php echo $cmp->cmp_eliminado;?></td>
                <!--<td><?php echo $fila->valor;?></td>-->
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
    $sql_sel="select distinct cde_cmp_id from con_comprobante_detalle where cde_cue_id=0";
    $listado=  FUNCIONES::lista_bd_sql($sql_sel);
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
                <!--<th>DIFERENCIA</th>-->
            </tr>
        </thead>
        <tbody>
    <?php
    $array_tipo=array('1'=>'ING','2'=>'EGR','3'=>'DIARIO');
    $i=1;
    foreach ($listado as $fila) {
        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
        ?>
            <tr>
                <td><?php echo $i;?></td>
                <td><?php echo $cmp->cmp_id;?></td>
                <td><?php echo $cmp->cmp_nro;?></td>
                <td><?php echo $array_tipo[$cmp->cmp_tco_id];?></td>
                <td><?php echo $cmp->cmp_fecha;?></td>
                <td><?php echo $cmp->cmp_glosa;?></td>
                <td><?php echo $cmp->cmp_tabla;?></td>
                <td><?php echo $cmp->cmp_tabla_id;?></td>
                <td><?php echo $cmp->cmp_usu_cre;?></td>
                <td><?php echo $cmp->cmp_eliminado;?></td>
                <!--<td><?php echo $fila->valor;?></td>-->
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
    $sql_sel="select cde_cmp_id, abs(sum(cde_valor)) as valor from con_comprobante_detalle group by cde_cmp_id
        having (abs(sum(cde_valor))>$_GET[rango]);";
    $listado=  FUNCIONES::lista_bd_sql($sql_sel);
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
                <th>DIFERENCIA</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $array_tipo=array('1'=>'ING','2'=>'EGR','3'=>'DIARIO');
    $i=1;
    foreach ($listado as $fila) {
        $cmp= FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id=$fila->cde_cmp_id");
        ?>
            <tr>
                <td><?php echo $i;?></td>
                <td><?php echo $cmp->cmp_id;?></td>
                <td><?php echo $cmp->cmp_nro;?></td>
                <td><?php echo $array_tipo[$cmp->cmp_tco_id];?></td>
                <td><?php echo $cmp->cmp_fecha;?></td>
                <td><?php echo $cmp->cmp_glosa;?></td>
                <td><?php echo $cmp->cmp_tabla;?></td>
                <td><?php echo $cmp->cmp_tabla_id;?></td>
                <td><?php echo $cmp->cmp_usu_cre;?></td>
                <td><?php echo $cmp->cmp_eliminado;?></td>
                <td><?php echo $fila->valor;?></td>
            </tr>
        <?php
        $i++;
    }
    ?>
        </tbody>
    </table>
    <?php
}