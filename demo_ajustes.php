<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$usar_reex=FUNCIONES::parametro('usar_reex');
echo $usar_reex;
return;
$ges_id = 1;
$mon_id = 3;
$per_ajuste = 6;
$fecha='2013-03-31';
$txt_ufv=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=3;");
$ufv_f=0;


if($txt_ufv){
    $ufv_f=$txt_ufv*1;
}
$sql_cu = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' and cue_codigo>='5.0.0.00.000' order by cue_codigo;";
$cuentas = FUNCIONES::objetos_bd_sql($sql_cu);

$sql_pdos = "select * from con_periodo where pdo_ges_id='$ges_id' and pdo_eliminado='No' order by pdo_fecha_inicio asc;";
$lis_periodos = FUNCIONES::objetos_bd_sql($sql_pdos);
$periodos = array();


for ($i = 0; $i < $lis_periodos->get_num_registros(); $i++) {
    $pdo = $lis_periodos->get_objeto();
    $sql = "select cmp_fecha, tca_valor as campo
            from con_comprobante , con_tipo_cambio 
            where cmp_fecha=tca_fecha and cmp_tco_id=4 and tca_mon_id=3 and cmp_peri_id='$pdo->pdo_id' and cmp_eliminado='No';";
    $val = FUNCIONES::atributo_bd_sql($sql);
    $periodo=new stdClass();
    $periodo->id=$pdo->pdo_id;
    if ($val) {        
        $periodo->val=$val;
    } else {        
        $periodo->val=-1;        
    }

    $periodos[] = $periodo;
    $lis_periodos->siguiente();
}
//FUNCIONES::print_pre($periodos);
//echo "'" . $periodos[pos_periodo($periodos, $per_ajuste)]->val . "'";
//return;

$sum_cuentas = array();
$pos_per_ajuste=pos_periodo($periodos, $per_ajuste);
$periodo= $periodos[$pos_per_ajuste];

if ($periodo->val * 1 == -1) {
    if (existen_ajustes($per_ajuste, $periodos)) {
        $periodos[$pos_per_ajuste]->val=$ufv_f;
        for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();            
            for ($j = 0; $j <= $pos_per_ajuste; $j++) {
                $periodo=$periodos[$j];
                $sql = "select cde_cue_id, cde_valor as monto, cmp_fecha , cmp_peri_id , cmp_ges_id ,tca_valor as ufv
                    from con_comprobante_detalle, con_comprobante, con_tipo_cambio
                    where cde_cmp_id=cmp_id and tca_fecha=cmp_fecha and tca_mon_id=3 and cmp_peri_id='$periodo->id' and cde_cue_id='$cuenta->cue_id' and cde_mon_id=1 ;";
                $comprobantes = FUNCIONES::objetos_bd_sql($sql);
                for($m=0;$m<$comprobantes->get_num_registros();$m++) {
                    $cmp=$comprobantes->get_objeto();
                    $monto=$cmp->monto;
                    $ufv_i=$cmp->ufv;
                    $res=0;
                    for ($k = $j; $k <= $pos_per_ajuste; $k++) {
                        $_periodo=$periodos[$k];
                        $_monto=$monto;
                        $monto= calcular($monto, $ufv_i, $_periodo->val);
                        $ufv_i=$_periodo->val;
                        $res=$monto-$_monto;                        
                    }
                    $sum_cuentas[$cuenta->cue_id]=$sum_cuentas[$cuenta->cue_id]+$res;
                    $comprobantes->get_num_registros();
                }
                    
                    
                    
            }
            
//            $sql = "select cde_cue_id, cde_valor as monto, cmp_fecha , cmp_peri_id , cmp_ges_id ,tca_valor as ufv
//                    from con_comprobante_detalle, con_comprobante, con_tipo_cambio
//                    where cde_cmp_id=cmp_id and tca_fecha=cmp_fecha and tca_mon_id=3 and cmp_peri_id='$per_ajuste' and cde_cue_id='$cuenta->cue_id' and cde_mon_id=1 ;";
////            echo $sql . "<br>";
//            $comprobantes = FUNCIONES::objetos_bd_sql($sql);
//            for ($j = 0; $j < $comprobantes->get_num_registros(); $j++) {
//                $cmp=$comprobantes->get_objeto();
//                $res=  calcular($cmp->monto, $cmp->ufv, $ufv_f)-$cmp->monto;
//                $sum_cuentas[$cuenta->cue_id]=$sum_cuentas[$cuenta->cue_id]+$res;
//                $comprobantes->siguiente();
//            }
            
            
            
            
            $cuentas->siguiente();
        }
        FUNCIONES::print_pre($sum_cuentas);
        echo number_format(sumatoria($sum_cuentas), 2) ;
        
    } else {
        echo "no existen";
    }
} else {
    echo "ya tiene ajuste";
}

function existen_ajustes($id_peri, $periodos) {
    $sw = true;
    foreach ($periodos as $periodo) {
        if ($periodo->id != $id_peri) {
            if ($periodo->valor * 1 == -1) {
                return false;
            }
        } else {
            break;
        }
    }
    return $sw;
}

function calcular($monto, $ufv_i, $ufv_f) {
    return ($monto * ($ufv_f / $ufv_i));
}
function pos_periodo($periodos,$id){
    for ($i = 0; $i < count($periodos); $i++) {
        if($periodos[$i]->id==$id){
            return $i;
        }
    }    
    return null;
}

function sumatoria($sum_cuentas) {
    $total=0;
    foreach ($sum_cuentas as $valor) {
        $total+=$valor;
    }
    return $total;
}

//$sql="select * from con_comprobante where cmp_eliminado='No' and cmp_tco_id=2";
//$comprobantes=FUNCIONES::objetos_bd_sql($sql);
//for($i=0;$i<$comprobantes->get_num_registros();$i++){
//    FUNCIONES::print_pre($comprobantes->get_objeto());
//    $comprobantes->siguiente();
//}
//return;
?>