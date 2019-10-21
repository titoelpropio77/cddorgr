<?php

class URBANIZACION {

    var $usuDatos;
    var $encript;

    function URBANIZACION() {
        $this->encript = new Encryption();
        $this->usuDatos = explode("|", $this->encript->decode($_GET['token']));
    }

    function listar() {
        $arr = array();
        if ($this->usuDatos[0] != '') {
//            $urlPlano = "http://" . $_SERVER["SERVER_NAME"] . "/modulos/uv/?mapa=&u=";
            
            $encode = base64_encode($this->usuDatos[0]);
            $encodeLimpio = str_replace(array('+','/','='),array('-','_',''),$encode);
            
            $urlPlano = _sistema_url . "/modulos/uv/?mapa=&u=";
//            $urlPlano = _sistema_url . "/modulos/uv/?mapa=".$encodeLimpio."&u=";

            $sql = "SELECT urb_id,urb_nombre,urb_maxplazo,urb_interes_anual,urb_val_form FROM urbanizacion where urb_multinivel='si' order by urb_id asc";
            $result = mysql_query($sql);
            $num = mysql_num_rows($result);
            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
                    $objeto = mysql_fetch_object($result);
                    $o = new stdClass;
                    $o->i = $objeto->urb_id;
                    $o->n = utf8_encode($objeto->urb_nombre);
                    $o->m = $objeto->urb_maxplazo;
                    $o->pd = $urlPlano . $objeto->urb_id;
                    $o->ia = $objeto->urb_interes_anual . "|" . $objeto->urb_val_form;
                    array_push($arr, $o);
                }
            }
        }
        return $arr;
    }

    function obtener_urbanizaciones() {
        $sql = "select * from urbanizacion where urb_multinivel='si' order by urb_id asc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $urbs = array();
        for ($i = 0; $i < $num; $i++) {
            $urbs[] = mysql_fetch_object($result);
        }

        return $urbs;
    }

    function obtener_uvs($urb_id) {
        $sql = "select * from uv where uv_urb_id='$urb_id'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $uvs = array();
        for ($i = 0; $i < $num; $i++) {
            $uvs[] = mysql_fetch_object($result);
        }

        return $uvs;
    }

    function obtener_manzanos($urb_id, $uv_id) {
        $sql = "select lot_uv_id,lot_man_id from lote where lot_uv_id = '$uv_id' group by lot_man_id";
//            echo "$sql<br/>";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $manzanos = '';
        for ($i = 1; $i <= $num; $i++) {
            $objeto = mysql_fetch_object($result);
            if ($i < $num)
                $manzanos = $manzanos . $objeto->lot_man_id . ',';
            if ($i == $num)
                $manzanos = $manzanos . $objeto->lot_man_id;
        }

        $sql = "select man_id,man_nro from manzano where man_urb_id='$urb_id' and man_id in (" . $manzanos . ") order by man_nro asc";
//            echo "$sql<br/>";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $manzanos = array();
        for ($i = 0; $i < $num; $i++) {
            $manzanos[] = mysql_fetch_object($result);
        }

        return $manzanos;
    }

    function obtener_lotes($urb_id, $uv_id, $man_id) {
        $sql = "select urb_porc_incremento from urbanizacion,uv where urb_id=uv_urb_id and uv_id='$uv_id'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $obj = mysql_fetch_object($result);
            $urb_porc_incremento = $obj->urb_porc_incremento;
        }

//        $sql_lotes = "select concat(lot_id,'-',lot_superficie,'-',zon_precio + ((zon_precio * $urb_porc_incremento)/100),'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,
//                                    concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,
//                                    zon_color,cast(lot_nro as SIGNED) as numero 
//                                    from lote inner join zona on (lot_zon_id=zon_id) 
//                                    inner join uv on (lot_uv_id=uv_id) 
//                                    where lot_man_id='" . $man_id .
//                "'  and lot_uv_id='" . $uv_id . "' and lot_estado='Disponible' 
//                                    order by numero asc";
        
        $sql_lotes = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,
                                    concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,
                                    zon_color,cast(lot_nro as SIGNED) as numero 
                                    from lote inner join zona on (lot_zon_id=zon_id) 
                                    inner join uv on (lot_uv_id=uv_id) 
                                    where lot_man_id='" . $man_id .
                "'  and lot_uv_id='" . $uv_id . "' and lot_estado='Disponible' 
                                    order by numero asc";
//            echo $sql_lotes;
        $result = mysql_query($sql_lotes);
        $num = mysql_num_rows($result);
        $lotes = array();
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $lotes[] = mysql_fetch_object($result);
            }
        }

        return $lotes;
    }

}