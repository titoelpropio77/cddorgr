<?php

class OFERTA {

//    function OFERTA() {
//        
//    }

    public static function guardar_venta_oferta($data) {        
        $data = (object) $data;
        
        print_r($data);
//        return false;
        
        $oferta = FUNCIONES::objeto_bd_sql("select * from oferta where of_id=$data->oferta");
        $obj_json = new stdClass();
        $obj_json->of_id = $oferta->of_id;
        $obj_json->of_periodos_sin_bra = $oferta->of_periodos_sin_bra;
        $obj_json->of_periodos_con_bir = $oferta->of_periodos_con_bir;
        
        $pdos_bra = OFERTA::obtener_periodos($data->venta, $oferta->of_periodos_sin_bra, 'BRA');
        $oferta->pdos_bra = $pdos_bra;
        $pdos_bir = OFERTA::obtener_periodos($data->venta, $oferta->of_periodos_con_bir, 'BIR');
        $oferta->pdos_bir = $pdos_bir;
        
        $s_json = json_encode($obj_json);
        $s_json_completo = json_encode($oferta);
        $sql_ins = "insert into venta_oferta(
                        vof_ven_id,vof_of_id,
                        vof_parametros_cre,vof_parametros_mod
                    )values(
                        '$data->venta','$data->oferta',
                        '$s_json_completo','$s_json'
                    )";
//        echo "<p>$sql_ins</p>";
        FUNCIONES::bd_query($sql_ins);
    }
    
    public static function actualizar_venta_oferta($data){
        $data = (object) $data;
        $oferta = FUNCIONES::objeto_bd_sql("select * from oferta where of_id=$data->oferta");
        
        $pdos_bra = OFERTA::obtener_periodos($data->venta, $oferta->of_periodos_sin_bra, 'BRA');
        $oferta->pdos_bra = $pdos_bra;
        $pdos_bir = OFERTA::obtener_periodos($data->venta, $oferta->of_periodos_con_bir, 'BIR');
        $oferta->pdos_bir = $pdos_bir;
        
        $obj_json = new stdClass();
        $obj_json->of_id = $oferta->of_id;
        $obj_json->of_periodos_sin_bra = $oferta->of_periodos_sin_bra;
        $obj_json->of_periodos_con_bir = $oferta->of_periodos_con_bir;
                
        $s_json_completo = json_encode($oferta);
        $s_json = json_encode($obj_json);
        
        $sql_upd = "update venta_oferta set vof_estado='Pendiente',
            vof_parametros_cre='$s_json_completo',
            vof_parametros_mod='$s_json'
        where vof_ven_id='$data->venta' and vof_of_id='$data->oferta' and vof_eliminado='No'";
        FUNCIONES::bd_query($sql_upd);
    }


    public static function obtener_periodos($ven_id, $cant_pdos, $tipo_bono){
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");
        $arr_pdos = array();        
        
        $cont = $cant_pdos;
        if ($tipo_bono == 'BIR') {
            $arr_pdos[] = substr($venta->ven_fecha, 0, 7);
            $cont--;
        }
        
        $fecha = $venta->ven_fecha;
        for ($i = $cont; $i > 0; $i--) {
            $sql = "select left(DATE_ADD('$fecha',INTERVAL 1 month),7)as campo";
            $s_pdo = FUNCIONES::atributo_bd_sql($sql);
            $arr_pdos[] = $s_pdo;
            $fecha = $s_pdo . '-01';
        }
        
        return $arr_pdos;        
    }

}

?>