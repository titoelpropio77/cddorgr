<?php

class AUX_REPORTE {

    public static function cargar_historia_ventas($arr_ventas, $fecha_limite, $token_user, $user) {

//        FUNCIONES::bd_query("truncate table aux_reporte_saldos;");
        $conec = new ADO();
        $conec->ejecutar("delete from aux_reporte_saldos where ars_token_user like '$user%';", FALSE);        

        $arr_aux = $arr_ventas;
        $s_arr_aux = implode(',', $arr_aux);
//        echo "<p>$s_arr_aux</p>";
//        return FALSE;
        $sql_ins_plan = "insert into aux_reporte_saldos(ars_ven_id,ars_vneg_id,ars_tipo,ars_fecha_ini,ars_fecha_fin,ars_token_user)values";
        $buffer = "";
        $arr_ops = array();
        $cont_ops = 0;
        $lim_ops = 200;
        foreach ($arr_ventas as $ven_id) {
            $sql = "select * from venta_negocio where vneg_ven_id='$ven_id'
            and vneg_estado='Activado' and vneg_tipo not in ('importacion','fusion')
            and vneg_fecha<='$fecha_limite'
            union
            select * from venta_negocio where vneg_ven_ori='$ven_id'
            and vneg_estado='Activado' and vneg_tipo='fusion'
            and vneg_fecha<='$fecha_limite'
            order by vneg_fecha desc,vneg_id desc limit 0,1;";

            $operaciones = FUNCIONES::lista_bd_sql($sql);

            $fecha_fin = $fecha_limite;
            
            if (count($operaciones) > 0) {
                foreach ($operaciones as $op) {
                    $obj_op = new stdClass();
                    $obj_op->fecha_fin = $fecha_fin;
                    $obj_op->fecha_ini = $op->vneg_fecha;
                    $obj_op->ven_id = ($op->vneg_tipo != 'fusion') ? $op->vneg_ven_id : $op->vneg_ven_ori;
                    $obj_op->vneg_id = $op->vneg_id;
                    $obj_op->tipo = $op->vneg_tipo;
                    $fecha_fin = $op->vneg_fecha;
                    array_push($arr_ops, $obj_op);
                    $cont_ops++;
                }
            } else {
                $obj_op = new stdClass();
                $obj_op->fecha_fin = $fecha_limite;
                $obj_op->fecha_ini = $fecha_limite;
                $obj_op->ven_id = $ven_id;
                $obj_op->vneg_id = 0;
                $obj_op->tipo = 'sin_operacion';                
                array_push($arr_ops, $obj_op);
                $cont_ops++;
            }

            if ($cont_ops >= $lim_ops) {

                $i = 0;
                foreach ($arr_ops as $op2) {

                    if ($i == 0) {
                        $buffer .= "('$op2->ven_id','$op2->vneg_id','$op2->tipo','$op2->fecha_ini','$op2->fecha_fin','$token_user')";
                    } else {
                        $buffer .= ",('$op2->ven_id','$op2->vneg_id','$op2->tipo','$op2->fecha_ini','$op2->fecha_fin','$token_user')";
                    }
                    $i++;
                }
                $sql_ins = "$sql_ins_plan $buffer";                
                $conec->ejecutar($sql_ins, FALSE);
//                echo "<p>$sql_ins;</p>";
                $cont_ops = 0;
                $buffer = "";
                $arr_ops = array();
            }
        }

        if ($cont_ops > 0) {

            $i = 0;
            foreach ($arr_ops as $op2) {

                if ($i == 0) {
                    $buffer .= "('$op2->ven_id','$op2->vneg_id','$op2->tipo','$op2->fecha_ini','$op2->fecha_fin','$token_user')";
                } else {
                    $buffer .= ",('$op2->ven_id','$op2->vneg_id','$op2->tipo','$op2->fecha_ini','$op2->fecha_fin','$token_user')";
                }
                $i++;
            }
            $sql_ins = "$sql_ins_plan $buffer";
            $conec->ejecutar($sql_ins, FALSE);
//            echo "<p>$sql_ins;</p>";
            $cont_ops = 0;
            $buffer = "";
            $arr_ops = array();
        }
    }
}
