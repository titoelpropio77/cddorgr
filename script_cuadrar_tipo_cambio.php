<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/registrar_comprobantes.class.php';
include_once 'clases/modelo_comprobantes.class.php';


cuadrar_comprobantes();

function cuadrar_comprobantes() {
    $conec=new ADO();
	
	$fecha_inicio = $_GET[ini];
	$fecha_fin = $_GET[fin];
    
	$sql_cmp = "select cde_cmp_id from con_comprobante_detalle 
	inner join con_comprobante on (cde_cmp_id=cmp_id)
	where cmp_eliminado='No' and cmp_fecha>='$fecha_inicio' and cmp_fecha<='$fecha_fin'	
	group by cde_cmp_id having (abs(sum(cde_valor))>0);";
	
	echo "<p style='color:green'>$sql_cmp</p>";
	
    $lcmps_des=  FUNCIONES::lista_bd_sql($sql_cmp);
    
    $acmps_ids=array();
    foreach ($lcmps_des as $key => $row) {
        $acmps_ids[] = $row->cde_cmp_id;
    }
    if(count($acmps_ids)==0){
        echo "NO EXITEN COMPROBANTES DESCUADRADOS";
        return;
    }
    $str_cmps_ids = implode(',', $acmps_ids);
    
    $comprobantes=  FUNCIONES::lista_bd_sql("select * from con_comprobante where cmp_eliminado='No' and cmp_id in ($str_cmps_ids)");
    
    $dif_cambio_egr=  "6.3.1.01.1.04";
    $dif_cambio_ing=  "6.3.1.01.1.04";
    
//    echo count($comprobantes);
//    return;
    foreach ($comprobantes as $cmp) {
        $detalles=  FUNCIONES::lista_bd_sql("select * from con_comprobante_detalle where cde_cmp_id='$cmp->cmp_id' order by cde_mon_id asc, cde_secuencia asc");
        $sum_valor=array();
        foreach ($detalles as $det) {
            $sum_valor[$det->cde_mon_id][valor]+=$det->cde_valor;
            $sum_valor[$det->cde_mon_id][secuencia]=$det->cde_secuencia;
        }
       // echo "<pre>";
       // print_r($sum_valor);
       // echo "</pre>";
        $cmp->cmp_glosa=  str_replace("'", "\'", $cmp->cmp_glosa);
        foreach ($sum_valor as $mon_id => $arr) {
            $dif_valor=  round($arr[valor], 6)*(-1);
            if($dif_valor!=0){
                
                $secuencia=($arr[secuencia]*1)+1;
                if($dif_valor>0){
                    $cue_id=  FUNCIONES::get_cuenta($cmp->cmp_ges_id, $dif_cambio_egr);
                }else{
                    $cue_id=  FUNCIONES::get_cuenta($cmp->cmp_ges_id, $dif_cambio_ing);
                }

                $sql = "insert into con_comprobante_detalle (
                        cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                        cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                )values(
                        '$cmp->cmp_id', '$mon_id', '$secuencia', '0', '0', '0', '$cue_id', '$dif_valor',
                        '$cmp->cmp_glosa', 'No','0','Efectivo','','','','$cmp->cmp_une_id',''
                )";

               echo "$sql;<br>";
                // $conec->ejecutar($sql, false, false);
            }
        }
    }
    
    echo "TOTAL VERIFICADOS ". count($comprobantes);
}

