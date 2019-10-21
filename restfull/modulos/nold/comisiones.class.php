<?php

class COMISIONES {

    var $usuDatos;
    var $encript;
    var $vdo_id;

    function COMISIONES() {
        $this->encript = new Encryption();
        $this->usuDatos = explode("|", $this->encript->decode($_GET['token']));
        $this->vdo_id = $this->vendedor_id();
    }

    function todos() {

        $opcionesDatos = array();
        $opcionesDatos['pen'] = $this->pendientes();
        $opcionesDatos['pag'] = $this->pagadas();
        $opcionesDatos['ina'] = $this->inactivas();

        return array($opcionesDatos);
    }

    function gestiones() {
        $sql = "select * from con_gestion where ges_estado='Abierto' and ges_eliminado='No'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $arr = array();
        for ($i = 0; $i < $num; $i++) {
            $obj = mysql_fetch_object($result);
            $elem = new stdClass();
            $elem->ges_id = $obj->ges_id;
            $elem->ges_descripcion = $obj->ges_descripcion;
            $arr[] = $elem;
        }

        return $arr;
    }

    function historial($params) {
        // echo "entrando a historial";

        $coms_per = array();
        for ($i = $params->pdo_ini * 1; $i <= $params->pdo_fin * 1; $i++) {
            // echo "entrando al bucle";
            $com_per = new stdClass();
            $com_per->BIR = $this->comisiones_generadas($i, "BIR", $params->vdo_id);
            $com_per->BVI = $this->comisiones_generadas($i, "BVI", $params->vdo_id);
            $com_per->BRA = $this->comisiones_generadas($i, "BRA", $params->vdo_id);
            $elem = new stdClass();
            $elem->comisiones = $com_per;
            $elem->periodo = $this->descripcion_periodo($i);
            $coms_per[] = $elem;
        }
        return $coms_per;
    }

    function descripcion_periodo($pdo_id) {
        $sql = "select * from con_periodo where pdo_id='$pdo_id'";
//        echo $sql;
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $des = "";
        if ($num > 0) {
            $periodo = mysql_fetch_object($result);
            $des = $periodo->pdo_descripcion;
        }
        return $des;
    }

    private function comisiones_generadas($pdo_id, $tipo_comision, $vdo_id) {
        $sql = "select * from con_periodo where pdo_id='$pdo_id'";
        // echo "<p style='red'>$sql</p>";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $total = 0;
        if ($num > 0) {
            $periodo = mysql_fetch_object($result);
            $sql_com = "select sum(com_monto)as total from comision where com_tipo='$tipo_comision'
                        and com_estado in ('Pendiente','Pagado')
                        and com_vdo_id='$vdo_id'
                        and com_fecha_cre >= '$periodo->pdo_fecha_inicio'
                        and com_fecha_cre <= '$periodo->pdo_fecha_fin'";

            // echo "<p style='red'>$sql_com</p>";
            $result2 = mysql_query($sql_com);
            $t = mysql_fetch_object($result2);
            $total = $t->total ? $t->total : 0;
        }
        return $total;
    }

    function periodos($ges_id) {
        $sql = "select * from con_periodo where pdo_estado='Abierto' and pdo_eliminado='No' and pdo_ges_id='$ges_id'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $arr = array();
        for ($i = 0; $i < $num; $i++) {
            $obj = mysql_fetch_object($result);
            $elem = new stdClass();
            $elem->pdo_id = $obj->pdo_id;
            $elem->pdo_descripcion = $obj->pdo_descripcion;
            $arr[] = $elem;
        }

        return $arr;
    }

    function descripcion_lote($lote) {
        $desc = "";
        $sql = "select urb_nombre,man_nro,lot_nro from lote
            inner join manzano on(lot_man_id=man_id)
            inner join urbanizacion on(man_urb_id=urb_id)
            where lot_id='$lote'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);

        if ($num > 0) {
            $obj = mysql_fetch_object($result);
            $desc = "Urb:$obj->urb_nombre - Mza:$obj->man_nro - Lote:$obj->lot_nro";
        }

        return $desc;
    }

    function listar($tipo, $estado) {
        $sql = "SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,com_pagado,com_fecha_cre,com_estado,
				cli.int_nombre,cli.int_apellido, CONCAT(inte.int_nombre,' ',inte.int_apellido) AS _vendedor,
				ven_tipo,ven_monto,ven_lot_id,mon_titulo
				FROM comision
				INNER JOIN venta ON(com_ven_id=ven_id)
				INNER JOIN interno cli ON(ven_int_id=cli.int_id)
				LEFT JOIN vendedor ON(ven_vdo_id=vdo_id)
				LEFT JOIN interno inte ON(vdo_int_id=inte.int_id)
				INNER JOIN con_moneda ON(com_moneda=mon_id)
				WHERE 1=1 and com_estado in ($estado)
                        and com_tipo='{$tipo}'
                        and com_vdo_id='{$this->vdo_id}'
                        and com_fecha_cre >= '2017-06-01'
                        order by com_fecha_cre desc";

        //echo "<p style='color:red'>$sql</p>";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $comisiones = new stdClass();
        $comisiones->lista = array();
        $comisiones->titulo = $tipo;
//        $comisiones->estado = "Pendientes";
        $comisiones->total_monto = 0;
        $comisiones->total_pagado = 0;
        $comisiones->total_saldo = 0;
        for ($i = 0; $i < $num; $i++) {
            $objeto = mysql_fetch_object($result);
            $elem = new stdClass();
            $elem->venta = $objeto->com_ven_id;
            $elem->titular = $objeto->int_nombre . " " . $objeto->int_apellido;
            $elem->fecha = $this->fecha_latina($objeto->com_fecha_cre);
            $elem->observacion = $this->descripcion_lote($objeto->ven_lot_id);
            $elem->monto = $objeto->com_monto;
            $elem->vendedor = $objeto->_vendedor;
            $elem->moneda = $objeto->mon_titulo;
            $elem->pagado = $objeto->com_pagado;
            $elem->estado = $objeto->com_estado;
            $elem->saldo = ($objeto->com_monto - $objeto->com_pagado);

            $comisiones->total_monto += $elem->monto;
            $comisiones->total_pagado += $elem->pagado;
            $comisiones->total_saldo += $elem->saldo;
            $comisiones->lista[] = $elem;
        }
        return $comisiones;
    }

    function pendientes($tipo) {
        $param = (object) $param;
        $sql = "SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,com_pagado,com_fecha_cre,
				cli.int_nombre,cli.int_apellido, CONCAT(inte.int_nombre,' ',inte.int_apellido) AS _vendedor,
				ven_tipo,ven_monto,ven_lot_id,mon_titulo
				FROM comision
				INNER JOIN venta ON(com_ven_id=ven_id)
				INNER JOIN interno cli ON(ven_int_id=cli.int_id)
				LEFT JOIN vendedor ON(ven_vdo_id=vdo_id)
				LEFT JOIN interno inte ON(vdo_int_id=inte.int_id)
				INNER JOIN con_moneda ON(com_moneda=mon_id)
				WHERE com_estado='Pendiente'
                        and com_tipo='{$tipo}'
                        and com_vdo_id='{$this->vdo_id}'
                        order by com_fecha_cre desc";

        //echo "<p style='color:red'>$sql</p>";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $pendientes = new stdClass();
        $pendientes->lista = array();
        $pendientes->titulo = $tipo;
        $pendientes->estado = "Pendientes";
        $pendientes->total_monto = 0;
        $pendientes->total_pagado = 0;
        $pendientes->total_saldo = 0;
        for ($i = 0; $i < $num; $i++) {
            $objeto = mysql_fetch_object($result);
            $elem = new stdClass();
            $elem->venta = $objeto->com_ven_id;
            $elem->titular = $objeto->int_nombre . " " . $objeto->int_apellido;
            $elem->fecha = $this->fecha_latina($objeto->com_fecha_cre);
            $elem->observacion = $this->descripcion_lote($objeto->ven_lot_id);
            $elem->monto = $objeto->com_monto;
            $elem->vendedor = $objeto->_vendedor;
            $elem->moneda = $objeto->mon_titulo;
            $elem->pagado = $objeto->com_pagado;
            $elem->saldo = ($objeto->com_monto - $objeto->com_pagado);

            $pendientes->total_monto += $elem->monto;
            $pendientes->total_pagado += $elem->pagado;
            $pendientes->total_saldo += $elem->saldo;
            $pendientes->lista[] = $elem;
        }
        return $pendientes;
    }

    function fecha_latina($fecha) {
        $datos = explode('-', $fecha);
        return $datos[2] . "/" . $datos[1] . "/" . $datos[0];
    }

    function pagadas($tipo) {
        $param = (object) $param;
        $sql = "SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,com_pagado,com_fecha_cre,
                        int_nombre,int_apellido,
                        ven_tipo,ven_monto,ven_lot_id,mon_titulo
                        FROM comision 
                        inner join venta on(com_ven_id=ven_id)
                        inner join interno on(ven_int_id=int_id)
                        inner join con_moneda on(com_moneda=mon_id)
                        where com_estado='Pagado'
                        and com_tipo='{$tipo}'
                        and com_vdo_id='{$this->vdo_id}'
                        order by com_fecha_cre desc";
//                        echo "<br/>$sql<br/>";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $pendientes = new stdClass();
        $pendientes->lista = array();
        $pendientes->titulo = $tipo;
        $pendientes->estado = "Pagadas";
        for ($i = 0; $i < $num; $i++) {
            $objeto = mysql_fetch_object($result);
            $elem = new stdClass();
            $elem->venta = $objeto->com_ven_id;
            $elem->titular = $objeto->int_nombre . " " . $objeto->int_apellido;
            $elem->fecha = $this->fecha_latina($objeto->com_fecha_cre);
            $elem->observacion = $this->descripcion_lote($objeto->ven_lot_id);
            $elem->monto = $objeto->com_monto;
            $elem->moneda = $objeto->mon_titulo;
            $elem->pagado = $objeto->com_pagado;
            $elem->saldo = ($objeto->com_monto - $objeto->com_pagado);

            $pendientes->total_monto += $elem->monto;
            $pendientes->total_pagado += $elem->pagado;
            $pendientes->total_saldo += $elem->saldo;

            $pendientes->lista[] = $elem;
        }
        return $pendientes;
    }

    function inactivas() {


        $html .= '<table class="table" data-filter-text-only="true" cellpadding="0" cellspacing="0">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Nro</th>';
        $html .= '<th>Venta Nro</th>';
        $html .= '<th>Cliente</th>';
        $html .= '<th  data-hide="phone,tablet">Vendedor</th>';
        $html .= '<th  data-hide="phone,tablet">Tipo Venta</th>';
        $html .= '<th  data-hide="phone,tablet">Monto Valor Terreno</th>';
        $html .= '<th  data-hide="phone,tablet">Periodo</th>';
        $html .= '<th  data-hide="phone,tablet">Urbanizaci&oacute;n</th>';
        $html .= '<th  data-hide="phone,tablet">Manzano</th>';
        $html .= '<th data-hide="phone,tablet">Lote</th>';
        $html .= '<th>Monto Bs</th>';
        $html .= '<th>Monto $us</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $sql = "SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha,ven_tipo,ven_valor, com_vdo_id
		FROM
		comision inner join venta on (com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id)
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join vendedor on (com_vdo_id=vdo_id)
		inner join interno on (vdo_int_id=int_id)
		where
		com_estado='Pendiente' and com_vdo_id='" . $this->vdo_id . "' and ven_estado!='Anulado'
		order by com_id asc,ven_fecha asc";

        $resul = mysql_query($sql);
        $num = mysql_num_rows($resul);

        $totalbs = 0;
        $totalsus = 0;
        for ($i = 0; $i < $num; $i++) {

            //echo $i;

            $objeto = mysql_fetch_object($resul);

            $sql_venta_propietario = "select * from venta where ven_id=$objeto->com_ven_id and ven_vdo_id=$objeto->com_vdo_id";
            $resul_venta_propietario = mysql_query($sql_venta_propietario);
            $registro_ventas = mysql_num_rows($resul_venta_propietario);


            $sql_venta = "select * from venta where ven_id=$objeto->com_ven_id";
            $resul_venta = mysql_query($sql_venta);
            $venta = mysql_fetch_object($resul_venta);

            $fecha_venta = $venta->ven_fecha;
            $date = new DateTime($fecha_venta);

            $date->modify('first day of this month');
            $primer_dia = $date->format('Y-m-d');
            $date->modify('last day of this month');
            $ultimo_dia = $date->format('Y-m-d');

            $sql_venta_mes = "select * from venta where ven_vdo_id=$objeto->com_vdo_id and ven_fecha between '$primer_dia' and '$ultimo_dia'";
            $resul_venta_propietario = mysql_query($sql_venta_mes);
            $num_venta_propietario = mysql_num_rows($resul_venta_propietario);

            if ($registro_ventas > 0 || $num_venta_propietario > 0) {
                continue;
            }

            // particionamos la fecha
            $fecha = '';
            $fecha = $objeto->ven_fecha;
            $porciones_fecha = explode("-", $fecha);
            $mes = $porciones_fecha[1]; // mes
            $anio = $porciones_fecha[0]; // anio

            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . ' &nbsp;</td>';
            $html .= '<td>' . $objeto->com_ven_id . ' &nbsp;</td>';
            $html .= '<td>' . utf8_encode($this->get_nombre_propietario($objeto->com_ven_id)) . ' &nbsp;</td>';
            $html .= '<td>' . utf8_encode($this->get_nombre_vendedor($objeto->com_ven_id)) . ' &nbsp;</td>';
            $html .= '<td>' . $objeto->ven_tipo . ' &nbsp;</td>';
            $html .= '<td>' . $objeto->ven_valor . ' &nbsp;</td>';
            $html .= '<td>' . $mes . '/' . $anio . ' &nbsp;</td>';
            $html .= '<td>' . utf8_encode($objeto->urb_nombre) . ' &nbsp;</td>';
            $html .= '<td>' . $objeto->man_nro . ' &nbsp;</td>';
            $html .= '<td>' . $objeto->lot_nro . ' &nbsp;</td>';

            if ($objeto->com_moneda == '1') {
                $bs = $objeto->com_monto;
                $sus = round($objeto->com_monto / $this->tc, 2);

                $totalbs = $totalbs + $bs;
                $totalsus = $totalsus + $sus;
            } else {
                $sus = $objeto->com_monto;
                $bs = round($objeto->com_monto * $this->tc, 2);
                $totalbs = $totalbs + $bs;
                $totalsus = $totalsus + $sus;
            }
            $html .= '<td>' . $bs . ' &nbsp;</td>';
            $html .= '<td>' . $sus . ' &nbsp;</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>&nbsp;</td>';
        $html .= '<td>Totales:</td>';
        $html .= '<td> ' . $totalbs . ' Bs </td>';
        $html .= '<td> ' . $totalsus . ' $us </td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '</table>';

        if ($num > 0) {
            
        } else {
            $html = '';
        }

        return $html;
    }

    function vendedor_id() {
        $sql = "select vdo_id from vendedor where vdo_int_id='" . $this->usuDatos[2] . "';";
        //$sql = "select vdo_id from vendedor where vdo_int_id='2';"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);
            return $objeto->vdo_id;
        } else {
            return 0;
        }
    }

    function get_nombre_propietario($venta_id) {
        $sql_venta = "select interno.* from interno
    	inner join venta on (ven_int_id=int_id)
    	where ven_id=$venta_id";
        $result = mysql_query($sql_venta);
        $propietario = mysql_fetch_object($result);
        return $propietario->int_nombre . ' ' . $propietario->int_apellido;
    }

    function get_nombre_vendedor($venta_id) {
        $sql_venta = "select interno.* from venta
    	inner join vendedor on (ven_vdo_id=vdo_id)
    	inner join interno on (vdo_int_id=int_id)
    	where ven_id=$venta_id";
        $result = mysql_query($sql_venta);
        $vendedor = mysql_fetch_object($result);

        return $vendedor->int_nombre . ' ' . $vendedor->int_apellido;
    }

}
