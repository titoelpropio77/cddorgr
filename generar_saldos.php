<?php
ini_set('display_errors', 'On');
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once('config/zona_horaria.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/formulario.class.php';

    function monto_pagado($ven_id) {
        $objeto = FUNCIONES::objeto_bd_sql("select sum(vpag_monto) as monto from venta_pago where vpag_ven_id='$ven_id' and vpag_estado='Activo'");
        return $objeto;
    }
	
	function crear_temporales($filtro = '', $fecha_limite) {
		$sql = "select ven_id as ven_id_producto,
		vprod_monto_efectivo as monto_efectivo_producto,
		vprod_fecha as fecha_producto from venta_producto 	
		inner join venta on(vprod_ven_id=ven_id)
		inner join urbanizacion on(ven_urb_id=urb_id)
		inner join con_comprobante on (vprod_id=cmp_tabla_id and cmp_tabla='venta_producto' and cmp_eliminado='No')
		where vprod_estado!='Anulado' $filtro and vprod_fecha<='$fecha_limite';";
		
		$sql_crear_tmp = "CREATE TEMPORARY TABLE tmp_venta_producto as $sql";					
		FUNCIONES::bd_query($sql_crear_tmp);
		
		if ($_SESSION[id] == 'admin') {
			echo "<p>$sql_crear_tmp</p>";
		}
	}

    function obtener_resultados() {
        $conec = new ADO();
        $hoy = date('Y-m-d');
        $info = array();
        $filtro = '';
        if ($_POST[urb_ids]) {
            $filtro .= " and urb_id in (" . implode(',', $_POST[urb_ids]) . ")";
            $info[] = array('label' => 'Urbanizacion', 'valor' => implode(',', $_POST[urb_nombres]));
        }

        if ($_POST['inicio'] <> "") {
            $filtro .= " and ven_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $info[] = array('label' => 'Fecha Inicio', 'valor' => $_POST['inicio']);
        }
        if ($_POST['fin'] <> "") {
            $filtro .= " and ven_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fin']) . "' ";
            $info[] = array('label' => 'Fecha Fin', 'valor' => $_POST['fin']);
        }
        if ($_POST['flimite'] <> "") {
            $fecha_limite = FUNCIONES::get_fecha_mysql($_POST['flimite']);
//            $filtro.=" and vpag_fecha_pago <='" . FUNCIONES::get_fecha_mysql($_POST['flimite']) . "' ";
            $info[] = array('label' => 'Hasta la Fecha', 'valor' => $_POST['flimite']);
        }

        if ($_POST['tipo'] <> "") {
            $filtro .= " and ven_tipo='" . $_POST['tipo'] . "'";
            $info[] = array('label' => 'Tipo', 'valor' => $_POST['tipo']);
        }

        if ($_POST['estado'] <> "") {
            if ($_POST['estado'] == 'Pendiente_Pagado') {
                $filtro .= " and (ven_estado ='Pendiente' or ven_estado ='Pagado')";
                $info[] = array('label' => 'Estado', 'va lor' => 'Pendiente o Pagado');
            } else {
                $filtro .= " and ven_estado='" . $_POST['estado'] . "'";
                $info[] = array('label' => 'Estado', 'valor' => $_POST['estado']);
            }
        } else {
            $filtro .= " and ven_estado!='Anulado' ";
        }
        
        if ($fecha_limite < $hoy) {
            FUNCIONES::bd_query("set group_concat_max_len=100000000;");
            $sql_ventas = "SELECT 
                        ifnull(group_concat(distinct(ven_id)),'')as ventas
                    FROM 
                        venta
                        left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')
                        inner join interno on (ven_int_id=int_id)
                        inner join urbanizacion on (ven_urb_id=urb_id)
                        where 1 $filtro                     
                        order by ven_fecha asc";

            $obj_ventas = FUNCIONES::objeto_bd_sql($sql_ventas);

            if ($obj_ventas->ventas == '') {
                return false;
            }

            include_once 'clases/aux_reporte_saldos.class.php';
            $arr_ventas = explode(',', $obj_ventas->ventas);
            $token_user = $_SESSION[id] . "_" . date('dmY_His');
            AUX_REPORTE::cargar_historia_ventas($arr_ventas, $fecha_limite, $token_user, $_SESSION[id]);
            
            $join_aux = " inner join aux_reporte_saldos on(ven_id=ars_ven_id and ars_token_user='$token_user')";
            $campos_aux = " if(ars_tipo='cambio_lote','Cambiado',
                                    if(ars_tipo='reversion','Retenido',
                                        if(ars_tipo='activacion','Pendiente',
                                            if(ars_tipo='fusion','Fusionado',if(ars_tipo='sin_operacion','Sin_Operacion','Pendiente'))
                                        )
                                    )
                                )as estado_calculado,";
        }
        
//        return false;
        
		$this->crear_temporales($filtro, $fecha_limite);
		
        $sql = "SELECT 
                    urb_id,ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,ven_lot_id,
                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,
                    ven_cuota,int_nombre,int_apellido,ven_comision,
                    ven_vdo_id,ven_urb_id,ven_valor,ven_metro,ven_res_anticipo,
                    ven_val_interes,ven_superficie,ven_decuento,ven_lot_ids,ven_monto_intercambio,
                    ven_monto_efectivo,urb_nombre_corto,
                    ven_ufecha_pago,ven_ufecha_valor,ven_cuota_pag,ven_capital_pag,
                    ven_capital_desc,ven_capital_inc,ven_usaldo,
                    int_telefono,int_celular,int_direccion,ven_ubicacion,ven_monto_producto,ven_prod_id,
					ifnull(monto_efectivo_producto, 0) as monto_efectivo_producto,
                    $campos_aux
                    ifnull(sum(vpag_capital_inc),0) as incremento,
                    ifnull(sum(vpag_capital_desc),0) as descuento,
                    ifnull(sum(vpag_interes),0) as interes_pagado, 
                    ifnull(sum(vpag_capital + ifnull(vpag_capital_prod,0)),0) as capital_pagado, count(*) as cuotas_pagadas, 
                    max(vpag_fecha_pago) as ufecha_pago, max(vpag_fecha_valor) as ufecha_valor
                FROM 
                    venta
                    left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')                    
                    $join_aux
                    inner join interno on (ven_int_id=int_id)
                    inner join urbanizacion on (ven_urb_id=urb_id)
					left join tmp_venta_producto on(ven_id_producto=ven_id)
                    where 1 $filtro 
                    group by ven_id
                    order by ven_fecha asc";
        
		if ($_SESSION[id] == 'admin') {
			echo "<p>$sql</p>";
		}
        
//        return false;

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $result = array();
        $nro = 1;
        $total = new stdClass();
//        $estados_carteras=$_POST[estados_carteras];
        $head = array('#', 'Nro Venta', 'Cliente', 'Telef.', 'Direccion', 'Proyecto', 'Moneda', 'Lote', 'Estado Actual', 'Estado Al Cierre', 'Fecha',
            'Superficie', 'Precio Prom', 'Valor Terreno', 'Descuento', 'Monto', 'Cuota Inicial',
            'Saldo Financiar', 'Interes', 'Plazo', 'Cuota', 'Nro. Cuota Pag.', 'Int. Pagado', 'Cap. Pagado', 'Cap. Desc', 'Cap. Inc.'
            , 'Saldo', 'U. Fecha Pag.', 'U. Fecha Valor');
			
		$buff = "";	
		$lim_ins = 100;
		$arr_sql_ins = array();
			
		$sql_plan_ins = "insert into tb_saldos_proyectos(ven_id,ven_urb_id,ven_fecha,fecha_corte,saldo,cap_pag,cap_desc,cap_inc)values";
        for ($i = 0; $i < $num; $i++) {
            $obj = $conec->get_objeto();           
            
            $arr_para_sumar_saldos = array('Pendiente', 'Sin_Operacion', 'Pagado');

            if ($fecha_limite >= $hoy) {
                $estado_calculado = $obj->ven_estado;
            } else if ($obj->estado_calculado == 'Sin_Operacion') {
                if ($obj->ven_tipo == 'Contado') {
                    $estado_calculado = 'Pagado';
                } else {
                    $estado_calculado = 'Pendiente';
                }
            } else {
                $estado_calculado = $obj->estado_calculado;
            }
            
            $capital_cuotas = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $obj->capital_pagado : 0;
            $interes_cuotas = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $obj->interes_pagado : 0;
			$descuentos_cuotas = $obj->descuento;
			$incrementos_cuotas = $obj->incremento;
			
			
            $cuota_ini = $obj->ven_monto_intercambio + $obj->ven_res_anticipo;
            $capital_pagado = $cuota_ini + $capital_cuotas;
            $monto_venta = $obj->ven_monto;
                        
			$saldo_financiar = (in_array($estado_calculado, $arr_para_sumar_saldos)) 
			? $obj->ven_monto_efectivo + $obj->ven_monto_producto + $obj->monto_efectivo_producto 
			: 0;
            
            if ($capital_cuotas < ($saldo_financiar + $obj->incremento)) {			
                $saldo_capital = ($saldo_financiar + $obj->incremento) - ($capital_cuotas + $obj->descuento);
            } else {
                if ($obj->ven_tipo == 'Contado' && 
                        ($cuota_ini >= $saldo_financiar) && 
                        ($cuota_ini >= $obj->capital_pagado)) {
                    
                    $capital_pagado = $cuota_ini;
                    $capital_cuotas = 0;
                }
                
                $saldo_capital = 0;
            }
            
            $saldo_capital = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $saldo_capital: 0;
            $saldo_capital = round($saldo_capital, 2);
            
            $mon = $obj->ven_moneda;
            $total->superficie += $obj->ven_superficie;
            $total->{"valor_$mon"} += $obj->ven_valor;
            $total->{"descuento_$mon"} += $obj->ven_decuento;
            $total->{"monto_$mon"} += $monto_venta;
            $total->{"cuota_ini_$mon"} += $cuota_ini;
            $total->{"saldo_financiar_$mon"} += $saldo_financiar;
            $total->{"interes_pagado_$mon"} += $interes_cuotas;
            $total->{"capital_pagado_$mon"} += $capital_cuotas;
            $total->{"cap_descuento_$mon"} += $descuentos_cuotas;
            $total->{"cap_incremento_$mon"} += $incrementos_cuotas;
            $total->{"saldo_capital_$mon"} += $saldo_capital;
			
			

            $result[] = array(
                $nro, $obj->ven_id, $cliente, "$obj->int_telefono/$obj->int_celular", 
				$obj->int_direccion, $obj->urb_nombre_corto, $str_moneda, $des_lotes, $obj->ven_estado, 
				$estado_calculado, $fecha, $obj->ven_superficie, $obj->ven_metro * 1, $obj->ven_valor, 
				$obj->ven_decuento, $obj->ven_monto, $cuota_ini, $saldo_financiar, $obj->ven_val_interes * 1, 
				$obj->ven_plazo, $obj->ven_cuota, $obj->cuotas_pagadas, $interes_cuotas, $capital_cuotas, 
				$descuentos_cuotas, $incrementos_cuotas, $saldo_capital, $ufecha_pago, $ufecha_valor
            );
            $nro++;
			
			$sql_ins = "('$obj->ven_id','$obj->ven_urb_id','$obj->ven_fecha','$fecha_limite','$saldo_capital','$capital_cuotas','$descuentos_cuotas','$incrementos_cuotas')";
			$arr_sql_ins[] = $sql_ins;
			
			if (count($arr_sql_ins) >= $lim_ins) {
			
				$s_ins = implode(',', $arr_sql_ins);
				$sql_ins_reg = $sql_plan_ins . $s_ins;
				FUNCIONES::bd_query($sql_ins_reg, false);
				$arr_sql_ins = array();
			}

            $conec->siguiente();
        }              
		
		if (count($arr_sql_ins) > 0 && count($arr_sql_ins) <= $lim_ins) {
			$s_ins = implode(',', $arr_sql_ins);
			$sql_ins_reg = $sql_plan_ins . $s_ins;
			FUNCIONES::bd_query($sql_ins_reg, false);
			$arr_sql_ins = array();
		}
    }

    function procesar_reporte() {
        if ($_POST) {
            $data = $this->obtener_resultados();
            if ($data[type] == 'success') {
                if ($_POST[imprimir] == 'excel') {
                    REPORTE::excel($data);
                } else {
                    REPORTE::html($data);
                }
            } else {
                echo $data->msj;
            }
        } else {
            $this->formulario();
        }			
    }
	
	$_POST[urb_ids] = "2";
	$_POST['estado'] = '';
	$_POST['inicio'] = "01/01/2000";
	$_POST['fin'] = "28/02/2018";
	$_POST['flimite'] = "28/02/2018";
	
	function obtener_resultados();
?>