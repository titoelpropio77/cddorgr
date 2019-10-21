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

}

?>