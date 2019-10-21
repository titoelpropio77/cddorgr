<?php
include_once 'clases/mlm.class.php';

class CARTERA {

    public static function calcular_estado_venta($estado) {
//        $sql_ven = "select distinct(ven_id) as ven_id,max(estado)as estado from temp_estado_cartera
//                    where ven_id=$ven_id
//                    group by ven_id";
//        $estado = FUNCIONES::objeto_bd_sql($sql_ven);

        $s_estado = "";

        if ($estado >= 0) {
            switch ($estado) {

                case 0: {
//                        $s_estado = "INICIO";
                        $s_estado = "inicio";
                    }
                    break;
                case 1: {
//                        $s_estado = "CUOTA INICIAL PAGADA";
                        $s_estado = "ci_pagada";
                    }
                    break;
                case 2: {
//                        $s_estado = "CUOTA MENSUAL PAGADA";
                        $s_estado = "cm_pagada";
                    }
                    break;
                case 3: {
//                        $s_estado = "CUOTA MENSUAL POR VENCER";
                        $s_estado = "cm_vencer";
                    }
                    break;
                case 4: {
//                        $s_estado = "CUOTA MENSUAL VENCIDA";
                        $s_estado = "cm_vencida";
                    }
                    break;
                case 5: {
//                        $s_estado = "ASOCIADO EN MORA";
                        $s_estado = "mora";
                    }
                    break;
                case 6: {
//                        $s_estado = "REVERSION";
                        $s_estado = "reversion";
                    }
                    break;
            }
        } else {
            $s_estado = "person";
        }
        return $s_estado;
    }

    public static function calcular_estado_venta_cartera($descripcion) {
        $s_estado = "";

        if ($descripcion) {
            switch ($descripcion) {

                case "inicio": {
//                        $s_estado = "CUOTA INICIAL PAGADA";
                        $s_estado = "Observacion";
                    }
                    break;

                case "ci_pagada": {
//                        $s_estado = "CUOTA INICIAL PAGADA";
                        $s_estado = "Cuota Inicial Pagada";
                    }
                    break;
                case "cm_pagada": {
//                        $s_estado = "CUOTA MENSUAL PAGADA";
                        $s_estado = "Cuota Mensual Pagada";
                    }
                    break;
                case "cm_vencer": {
//                        $s_estado = "CUOTA MENSUAL POR VENCER";
                        $s_estado = "Cuota Mensual Por Vencer";
                    }
                    break;
                case "cm_vencida": {
//                        $s_estado = "CUOTA MENSUAL VENCIDA";
                        $s_estado = "Cuota Mensual Vencida (+ DE 30 DIAS)";
                    }
                    break;
                case "mora": {
//                        $s_estado = "ASOCIADO EN MORA";
                        $s_estado = "Asociado en Mora (+ DE 60 DIAS)";
                    }
                    break;
                case "reversion": {
//                        $s_estado = "REVERSION";
                        $s_estado = "Proceso de Reversion (+ DE 90 DIAS)";
                    }
                    break;
                case "person": {
//                        $s_estado = "REVERSION";
                        $s_estado = "NN";
                    }
                    break;
            }
        } else {
            $s_estado = "person";
        }
        return $s_estado;
    }

    public static function obtener_estados() {
        $sql_ven = "select distinct(ven_id) as ven_id,max(estado)as estado from temp_estado_cartera                    
                    group by ven_id";
        $estados = FUNCIONES::lista_bd_sql($sql_ven);

        $arr_estados = array();

        foreach ($estados as $est) {

            $arr_estados[$est->ven_id] = CARTERA::calcular_estado_venta($est->estado);
//            echo "venta: {$est->ven_id} - {$arr_estados[$est->ven_id]}";
        }

        return $arr_estados;
    }

    public static function ingresar_ventas($ventas, $hoy) {
        $s_ven_ids = implode(',', $ventas);
        $periodo = substr($hoy, 0, 7);
        FUNCIONES::bd_query("truncate table temp_estado_cartera");

        // CUOTA INICIAL PAGADA
        $sql_inicial = "insert into temp_estado_cartera(ven_id,cant,estado)
                            select ven_id,'0'as cant,0 as estado from venta
                            where ven_estado='Pendiente'
                            and ven_multinivel='si'                             
                            and ven_id in ($s_ven_ids)";
        FUNCIONES::bd_query($sql_inicial);

        // CUOTA INICIAL PAGADA
        $sql_ci_pagada = "insert into temp_estado_cartera(ven_id,cant,estado)
                            select ven_id,'0'as cant,1 as estado from venta
                            where ven_estado='Pendiente'
                            and ven_multinivel='si' 
                            and left(ven_fecha,7) = '$periodo'
                            and ven_id in ($s_ven_ids)";
        FUNCIONES::bd_query($sql_ci_pagada);

        // CUOTA PAGADA
        $sql_cuota_pagada = "insert into temp_estado_cartera(ven_id,cant,estado)
                            select ven_id,'0' cant,2 as estado from interno_deuda
                            inner join venta on(ind_tabla='venta' and ind_tabla_id=ven_id)
                            where left(ind_fecha_programada,7) = '$periodo'
                            and ind_estado='Pagado'
                            and ven_estado='Pendiente'
                            and ven_multinivel='si'
                            and ven_id in ($s_ven_ids)";
        FUNCIONES::bd_query($sql_cuota_pagada);

        // CUOTA POR VENCER
        $sql_cuota_por_vencer = "insert into temp_estado_cartera(ven_id,cant,estado)
                                select ven_id,count(ind_id)as cant,3 as estado from interno_deuda
                                inner join venta on(ind_tabla='venta' and ind_tabla_id=ven_id)
                                where ind_fecha_programada >= '$hoy'
                                and left(ind_fecha_programada,7) = '$periodo'
                                and ind_estado='Pendiente'
                                and ven_estado='Pendiente'
                                and ven_multinivel='si'
                                and ven_id in ($s_ven_ids)
                                group by ven_id";
        FUNCIONES::bd_query($sql_cuota_por_vencer);

        // CUOTA VENCIDA
        $sql_cuota_vencida = "insert into temp_estado_cartera(ven_id,cant,estado)
                                select ven_id,count(ind_id)as cant,4 as estado from interno_deuda
                                inner join venta on(ind_tabla='venta' and ind_tabla_id=ven_id)
                                where ind_fecha_programada < '$hoy'
                                and ind_estado='Pendiente'
                                and ven_estado='Pendiente'
                                and ven_multinivel='si'
                                and ven_id in ($s_ven_ids)
                                group by ven_id
                                having cant =1";
        FUNCIONES::bd_query($sql_cuota_vencida);

        // ASOCIADO EN MORA
        $sql_asoc_en_mora = "insert into temp_estado_cartera(ven_id,cant,estado)
                            select ven_id,count(ind_id)as cant,5 as estado from interno_deuda
                            inner join venta on(ind_tabla='venta' and ind_tabla_id=ven_id)
                            where ind_fecha_programada < '$hoy'
                            and ind_estado='Pendiente'
                            and ven_estado='Pendiente'
                            and ven_multinivel='si'
                            and ven_id in ($s_ven_ids)
                            group by ven_id
                            having cant =2";
        FUNCIONES::bd_query($sql_asoc_en_mora);

        // REVERSION
        $sql_reversion = "insert into temp_estado_cartera(ven_id,cant,estado)
                            select ven_id,count(ind_id)as cant,6 as estado from interno_deuda
                            inner join venta on(ind_tabla='venta' and ind_tabla_id=ven_id)
                            where ind_fecha_programada < '$hoy'
                            and ind_estado='Pendiente'
                            and ven_estado='Pendiente'
                            and ven_multinivel='si'
                            and ven_id in ($s_ven_ids)
                            group by ven_id
                            having cant >=3";
        FUNCIONES::bd_query($sql_reversion);
    }

    public static function info($data) {
        $data = (object) $data;

        $sql_ven = "select 
            int_nombre,int_apellido,ven_id,ven_fecha,ven_res_anticipo,ven_observacion,
            ven_cuota,ven_estado,ven_devuelto,
            ran_id,ran_nombre,urb_nombre,man_nro,lot_nro
            from venta 
        inner join interno on (ven_int_id=int_id)    
        inner join vendedor on (ven_id=vdo_venta_inicial)
        left join rango on (vdo_rango_alcanzado=ran_id)
        inner join lote on (ven_lot_id=lot_id)
        inner join urbanizacion on (ven_urb_id=urb_id)
        inner join manzano on (lot_man_id=man_id)
        where ven_id={$data->venta}";

        $venta = FUNCIONES::objeto_bd_sql($sql_ven);

        $ademas = "";

        if ($venta->ven_estado == 'Retenido') {
            $ademas .= "(Retenido";
            $ademas .= ($venta->ven_devuelto == 1) ? " - Devuelto)" : ")";
        }

        if ($_SESSION[id] == 'admin') {
            // echo "<p>$sql_ven</p>";
        }

        $html = "";
        if ($venta == null) {
            ob_start();
            ?>
            <div class="burbuja">
                No existe informacion...
            </div>
            <?php
            $html = ob_get_contents();
            ob_clean();
            return $html;
        }

        $ran_id = (($venta->ran_id + 1) <= 6) ? $venta->ran_id + 1 : 6;

        $ran_cal = FUNCIONES::objeto_bd_sql("select * from rango where ran_id = $ran_id");

        $cuota_pendiente = FUNCIONES::objeto_bd_sql("select * from interno_deuda 
            where ind_tabla='venta'
            and ind_tabla_id=$venta->ven_id                
            and ind_estado in ('Pendiente','Retenido')
            and ind_num_correlativo>0
            order by ind_fecha_programada asc limit 0,1");

        $div_id = "afil" . $venta->ven_id;

        ob_start();
        ?>
        <div id="<?php echo $div_id; ?>" class="burbuja">
            <b><?php echo $data->venta; ?><br><?php echo $venta->int_nombre . " " . $venta->int_apellido; ?></b>
            <br><br>
            <?php
            if ($ademas != '') {
                ?>
                <h3><span style="color:red;"><?php echo $ademas; ?></span></h3>
                <?php
            }
            ?>
            <table style="font-size:12px; width: 85%;" border="0">
                <tbody>
                    <tr>
                        <td><?php echo CARTERA::calcular_estado_venta_cartera($data->desc_estado); ?></td><td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Fecha De Adjudicacion </td>
                        <td  align="right"><?php echo FUNCIONES::get_fecha_latina($venta->ven_fecha); ?></td>
                    </tr>
                    <tr>
                        <td>Proyecto </td>
                        <td  align="right"><?php echo $venta->urb_nombre; ?></td>
                    </tr>
                    <tr>
                        <td>Mz </td>
                        <td  align="right"><?php echo $venta->man_nro; ?></td>
                    </tr>
                    <tr>
                        <td>Lote </td>
                        <td  align="right"><?php echo $venta->lot_nro; ?></td>
                    </tr>
                    <tr>
                        <td>Cuota Inicial </td>
                        <td  align="right"><?php echo number_format($venta->ven_res_anticipo, 2, '.', ','); ?></td>
                    </tr>
                    <tr>
                        <td>Observaciones </td>
                        <td align="right"><?php echo $venta->ven_observacion; ?></td>
                    </tr>
                    <tr>
                        <td>Rango Obtenido </td>
                        <td align="right"><?php echo $venta->ran_id . "-" . $venta->ran_nombre; ?></td>
                    </tr>
                    <tr>
                        <td>Rango A Calificar   </td>
                        <td align="right"><?php echo $ran_cal->ran_id . "-" . $ran_cal->ran_nombre; ?></td>
                    </tr>
                    <tr>
                        <td>Fecha De Vencimiento   </td>
                        <td align="right"><?php echo FUNCIONES::get_fecha_latina($cuota_pendiente->ind_fecha_programada); ?></td>
                    </tr>
                    <tr>
                        <td>Cuota Mensual   </td>
                        <td align="right"><?php echo number_format($venta->ven_cuota, 2, '.', ','); ?></td>
                    </tr>

                </tbody>
            </table>
        </div>
        <?php
        $html = ob_get_contents();
        ob_clean();

        return $html;
    }

    public static function info_afiliado($ven_id) {

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");
        $lote = FUNCIONES::objeto_bd_sql("select * from lote 
        inner join manzano on(lot_man_id=man_id)
        inner join urbanizacion on (man_urb_id=urb_id)
        where lot_id='$venta->ven_lot_id'");
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_venta_inicial=$ven_id");
        $patrocinador = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vendedor->vdo_vendedor_id");
        $fecha_adjudicacion = FUNCIONES::get_fecha_latina($venta->ven_fecha);
        $fecha_registro = FUNCIONES::get_fecha_latina($interno->int_fecha_ingreso);
        $datos_ubic = explode(',', $interno->int_ubicacion_res);
        $pais = $datos_ubic[0];
        $estado_departamento = $datos_ubic[1];
        $ciudad = $datos_ubic[2];
        $direccion = $interno->int_direccion;
        $cliente = $interno->int_nombre . " " . $interno->int_apellido;
        $telefono = $interno->int_telefono;
        $celular = $interno->int_celular;

        $ran_id = (($vendedor->vdo_rango_alcanzado + 1) <= 6) ? $vendedor->vdo_rango_alcanzado + 1 : 6;

        $ran_cal = FUNCIONES::objeto_bd_sql("select * from rango where ran_id = $ran_id");
        $ran_obt = FUNCIONES::objeto_bd_sql("select * from rango where ran_id=$vendedor->vdo_rango_alcanzado");
        $pdo_ran_pago = FUNCIONES::objeto_bd_sql("select * from vendedor_rango 
            inner join rango on (vran_ran_id=ran_id)
            where vran_vdo_id=$vendedor->vdo_id order by vran_pdo_id desc limit 0,1");
        $rango_obtenido = $ran_obt->ran_id . "-" . strtoupper($ran_obt->ran_nombre);
        $rango_calificar = $ran_cal->ran_id . "-" . strtoupper($ran_cal->ran_nombre);
        $rango_pago = $pdo_ran_pago->ran_id . "-" . strtoupper($pdo_ran_pago->ran_nombre);

        $pdo_pago_id = FUNCIONES::atributo_bd_sql("select pdo_id as campo from comision_periodo
        order by pdo_id desc limit 0,1");

        $periodo_pago = FUNCIONES::atributo_bd_sql("select 
        convert(concat(pdo_descripcion,'-',left(pdo_fecha_fin,4))using utf8)as campo from con_periodo
        where pdo_id='$pdo_pago_id'");

        $cuota_mensual = $venta->ven_cuota;
        $plazo = $venta->ven_plazo;
        $director = FUNCIONES::objeto_bd_sql("select * from vendedor 
            inner join interno on(vdo_int_id=int_id)
            where vdo_id=$vendedor->vdo_director_id");
        $nombre_director = $director->int_nombre . " " . $director->int_apellido;

        $ademas = "";

        if ($venta->ven_estado == 'Retenido') {
            $ademas .= "(Retenido";
            $ademas .= ($venta->ven_devuelto == 1) ? " - Devuelto)" : ")";
            $ademas = "<span style='color:red;'><b>$ademas</b></span>";
        }

        $str_info.="{";
        $str_info.="\"id\":\"$ven_id\",";
        $str_info.="\"imagen\":\"\",";
        $str_info.="\"infos\":[";
        $str_info.="{";
        $str_info.="\"label\":\"CODIGO\",";
        $str_info.="\"value\":\"$ven_id $ademas\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"PATROCINADOR\",";
        $str_info.="\"value\":\"$patrocinador->vdo_venta_inicial\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"DIRECTOR\",";
        $str_info.="\"value\":\"$nombre_director\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"PAIS\",";
        $str_info.="\"value\":\"$pais\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"ESTADO/DEPARTAMENTO\",";
        $str_info.="\"value\":\"$estado_departamento\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"CIUDAD\",";
        $str_info.="\"value\":\"$ciudad\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"DIRECCION\",";
        $str_info.="\"value\":\"$direccion\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"FECHA DE REGISTRO\",";
        $str_info.="\"value\":\"$fecha_registro\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"FECHA DE ADJUDICACION\",";
        $str_info.="\"value\":\"$fecha_adjudicacion\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"PROYECTO\",";
        $str_info.="\"value\":\"$lote->urb_nombre\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"MANZANA\",";
        $str_info.="\"value\":\"$lote->man_nro\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"LOTE\",";
        $str_info.="\"value\":\"$lote->lot_nro\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"CLIENTE\",";
        $str_info.="\"value\":\"$cliente\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"TELEFONO\",";
        $str_info.="\"value\":\"$telefono\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"CELULAR\",";
        $str_info.="\"value\":\"$celular\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"RANGO OBTENIDO\",";
        $str_info.="\"value\":\"$rango_obtenido\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"RANGO A CALIFICAR\",";
        $str_info.="\"value\":\"$rango_calificar\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"PERIODO POR PAGAR\",";
        $str_info.="\"value\":\"$periodo_pago\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"RANGO POR PAGAR\",";
        $str_info.="\"value\":\"$rango_pago\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"IMPORTE PENDIENTE\",";
        $str_info.="\"value\":\"$cuota_mensual\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"MESES PLAZO\",";
        $str_info.="\"value\":\"$plazo\"";
        $str_info.="},";
        $str_info.="{";
        $str_info.="\"label\":\"CI\",";
        $str_info.="\"value\":\"$interno->int_ci $interno->int_ci_exp\"";
        $str_info.="}";
        $str_info.="]";
        $str_info.="}";

        return $str_info;
    }

}
?>