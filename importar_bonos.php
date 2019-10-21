<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');
require_once("clases/log.class.php");

function fecha_mysql($fecha) {
    $date = DateTime::createFromFormat("Ymd", $fecha);
    return $date->format("Y-m-d");
}

function fecha_creacion($val) {
    $anio = "20" . substr($val, 0, 2);
    $mes = substr($val, 2, 2);
    $val = "$anio-$mes-01";
    $sql_sel = "select LAST_DAY('$val')as campo";
    return FUNCIONES::atributo_bd_sql($sql_sel);
}

function resetear_fecha_activa() {
    $sql = "update venta set ven_fecha_act_mlm='0000-00-00' where ven_multinivel='si'";
    $conec = new ADO();
    $conec->ejecutar($sql, FALSE, FALSE);
}

function establecer_fecha_activa() {

//    $ventas = FUNCIONES::lista_bd_sql("select ven_id,max(ind_fecha_programada) as ven_fecha_act_mlm
//        from venta
//    inner join interno_deuda on(ven_id=ind_tabla_id and ind_tabla='venta')
//    where ven_estado in ('Pendiente','Pagado')
//    and ven_multinivel='si'
//    and ind_estado in ('Pendiente','Pagado')
//    group by ven_id");

    $ventas = FUNCIONES::lista_bd_sql("select *
        from venta    
        where ven_estado in ('Pendiente','Pagado')
        and ven_multinivel='si'");

    foreach ($ventas as $ven) {
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$ven->ven_urb_id");
        $fecha_act_mlm = FUNCIONES::atributo_bd_sql("select 
        DATE_ADD('{$ven->ven_fecha}',INTERVAL {$urb->urb_nro_cuotas_multinivel} month)as campo");

        $sql_upd = "update venta set ven_fecha_act_mlm='$fecha_act_mlm'
                where ven_id=$ven->ven_id";
        $conec = new ADO();
        $conec->ejecutar($sql_upd, FALSE, FALSE);
    }
}

function importar_temporal() {

    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('CP1251');
    $data->read("fuentes_excel/BONOS.xls");
    $colum = 0;
    $filasTotal = $data->sheets[$colum]['numRows'];
    echo $filasTotal;

//$filasTotal = 10;

    for ($i = 2; $i <= $filasTotal; $i++) {
        $tb_afi = new stdClass();
        $tb_afi->int_vendedor = trim($data->sheets[$colum]['cells'][$i][1]);
        $tb_afi->ven_id = trim($data->sheets[$colum]['cells'][$i][2]);
        $tb_afi->importe = trim($data->sheets[$colum]['cells'][$i][3]);
        $tb_afi->monto = trim($data->sheets[$colum]['cells'][$i][4]);
        $tb_afi->moneda = trim($data->sheets[$colum]['cells'][$i][5]);
//    $tb_afi->fecha_creacion = fecha_mysql(trim($data->sheets[$colum]['cells'][$i][6]));
        $tb_afi->fecha_creacion = fecha_creacion(trim($data->sheets[$colum]['cells'][$i][9]));
        $tb_afi->tipo_bono = trim($data->sheets[$colum]['cells'][$i][7]);
        $tb_afi->porcentaje = trim($data->sheets[$colum]['cells'][$i][8]);
        $tb_afi->periodo = trim($data->sheets[$colum]['cells'][$i][9]);
        $tb_afi->gestion = trim($data->sheets[$colum]['cells'][$i][10]);

        $sql_ins = "insert into temp_bonos(
        int_vendedor,ven_id,importe,monto,
        moneda,fecha_creacion,tipo_bono,
        porcentaje,periodo,gestion
        ) values (
            '$tb_afi->int_vendedor','$tb_afi->ven_id','$tb_afi->importe','$tb_afi->monto',
            '$tb_afi->moneda','$tb_afi->fecha_creacion','$tb_afi->tipo_bono',
            '$tb_afi->porcentaje','$tb_afi->periodo','$tb_afi->gestion'
        )";
        FUNCIONES::bd_query($sql_ins);
    }
}

function establecer_bonos($ven_id = 0) {

    if ($ven_id > 0) {
        $filtro = " and ven_id=$ven_id";
    }

    $ventas = FUNCIONES::lista_bd_sql("select * from venta where ven_multinivel='si' $filtro");

    foreach ($ventas as $ven) {
        echo "<p>EJECUTANDO ...</p>";
        $data = array('ven_id' => $ven->ven_id);
        MLM::calcular_montos_bonos($data);

        $data_cobro = array('venta' => $ven->ven_id, 'vendedor' => $ven->ven_vdo_id);
        MLM::insertar_comisiones_cobro($data_cobro);
    }
}

function cargar_bonos_BIR_BVI($ven_id = 0) {

    if ($ven_id > 0) {
        $filtro = " and ven_id=$ven_id";
    }

    $sql = "select * from temp_bonos where tipo_bono like 'INGRESO%' $filtro
        ORDER BY ven_id asc";
    $bonos = FUNCIONES::lista_bd_sql($sql);

    $aux_ven_id = 0;
    foreach ($bonos as $b) {
        $numero = "NET-" . $b->ven_id;
        $bb = FALSE;
        if ($aux_ven_id != $b->ven_id) {
            $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_numero_cliente='$numero'");
            $aux_ven_id = $b->ven_id;
            $bb = TRUE;
        }

        if ($venta) {
            $tipo_bono = explode(' ', $b->tipo_bono);
            $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado=$b->int_vendedor");

            if ($vendedor) {
                $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
                    where pdo_fecha_inicio<='$b->fecha_creacion'
                        and pdo_fecha_fin>='$b->fecha_creacion'");
                if ($tipo_bono[2] == '1') {
                    $tb = "BIR";
                } else {
                    $tb = "BVI";
                }
                $monto_comision = $b->monto;
                $porc = $b->porcentaje;

                $datos = array(
                    'venta' => $venta,
                    'vendedor' => $vendedor->vdo_id,
                    'monto' => $monto_comision,
                    'moneda' => 2,
                    'usuario' => 'admin',
//                    'glosa' => 'Bono de Inicio Rapido por la Venta Nro. ' . $venta->ven_id,
                    'glosa' => $b->tipo_bono . " ($tb Venta $venta->ven_id)",
                    'fecha' => $b->fecha_creacion,
                    'estado' => "Pagado",
                    'tipo' => $tb,
                    'periodo' => $periodo->pdo_id,
                    'fecha_imp' => date("Y-m-d H:i:s"),
                    'porcentaje' => $porc);
                include_once 'clases/comisiones.class.php';
                COMISION::insertar_comision($datos);
            } else {
                FUNCIONES::eco("no existe el afiliado $b->int_vendedor...");
                $sql_upd = "update temp_bonos set obs='no existe el afiliado $b->int_vendedor ($tb)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
                FUNCIONES::bd_query($sql_upd);
            }
        } else {
//            if ($bb) {
            if (TRUE) {
                FUNCIONES::eco("no existe la venta $numero...");
                $sql_upd = "update temp_bonos set obs='no existe la venta $numero ($tb)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
                FUNCIONES::bd_query($sql_upd);
            }
        }
    }
}

function cargar_bonos_BRA($ven_id = 0) {
    if ($ven_id > 0) {
        $filtro = " and ven_id=$ven_id";
    }

    $sql = "select * from temp_bonos where tipo_bono like 'RESIDUAL%' $filtro
        ORDER BY ven_id asc";
    $bonos = FUNCIONES::lista_bd_sql($sql);

    $aux_ven_id = 0;
    foreach ($bonos as $b) {
        $numero = "NET-" . $b->ven_id;
        $bb = FALSE;
        if ($aux_ven_id != $b->ven_id) {
            $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_numero_cliente='$numero'");
            $aux_ven_id = $b->ven_id;
            $bb = TRUE;
        }

        if ($venta) {
            $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado=$b->int_vendedor");

            if ($vendedor) {
                $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
                    where pdo_fecha_inicio<='$b->fecha_creacion'
                        and pdo_fecha_fin>='$b->fecha_creacion'");

                $monto_comision = $b->monto;
//                $porc = $monto_comision * 100 / $venta->ven_bono_bra;
                $porc = 100;

                $datos = array('venta' => $venta,
                    'vendedor' => $vendedor->vdo_id,
                    'monto' => $monto_comision,
                    'moneda' => 2,
                    'usuario' => 'admin',
//                    'glosa' => 'Bono Residual Abierto por la Venta Nro. ' . $venta->ven_id,
                    'glosa' => $b->tipo_bono . " (BRA Venta $venta->ven_id)",
                    'fecha' => $b->fecha_creacion,
                    'estado' => "Pagado",
                    'tipo' => 'BRA',
                    'periodo' => $periodo->pdo_id,
                    'fecha_imp' => date("Y-m-d H:i:s"),
                    'porcentaje' => $porc);

                include_once 'clases/comisiones.class.php';
                COMISION::insertar_comision($datos);
            } else {
                FUNCIONES::eco("no existe el afiliado $b->int_vendedor...");
                $sql_upd = "update temp_bonos set obs='no existe el afiliado $b->int_vendedor (BRA)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
                FUNCIONES::bd_query($sql_upd);
            }
        } else {
//            if ($bb) {
            if (TRUE) {
                FUNCIONES::eco("no existe la venta $numero...(BRA)");
                $sql_upd = "update temp_bonos set obs='no existe la venta $numero (BRA)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
                FUNCIONES::bd_query($sql_upd);
            }
        }
    }
}

function cargar_bonos_BEV() {


    $sql = "select * from temp_bonos where tipo_bono like 'ESTILO%'
        ORDER BY ven_id asc";
    $bonos = FUNCIONES::lista_bd_sql($sql);

    foreach ($bonos as $b) {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado=$b->int_vendedor");

        if ($vendedor) {
            $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
                    where pdo_fecha_inicio<='$b->fecha_creacion'
                        and pdo_fecha_fin>='$b->fecha_creacion'");

            $monto_comision = $b->monto;
            $porc = 100;

            $datos = array(
                'vendedor' => $vendedor->vdo_id,
                'monto' => $monto_comision,
                'moneda' => 2,
                'usuario' => 'admin',
                'glosa' => "ESTILO DE VIDA (BEV)",
                'fecha' => $b->fecha_creacion,
                'estado' => "Pagado",
                'tipo' => 'BEV',
                'periodo' => $periodo->pdo_id,
                'fecha_imp' => date("Y-m-d H:i:s"),
                'porcentaje' => $porc);

            include_once 'clases/comisiones.class.php';
            COMISION::insertar_comision($datos);
        } else {
            FUNCIONES::eco("no existe el afiliado $b->int_vendedor...");
            $sql_upd = "update temp_bonos set obs='no existe el afiliado $b->int_vendedor (BEV)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
            FUNCIONES::bd_query($sql_upd);
        }
    }
}

function cargar_bonos_FED() {
    $sql = "select * from temp_bonos where tipo_bono like '%DIAMANTE%'
        ORDER BY ven_id asc";
    $bonos = FUNCIONES::lista_bd_sql($sql);

    foreach ($bonos as $b) {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado=$b->int_vendedor");

        if ($vendedor) {
            $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
                    where pdo_fecha_inicio<='$b->fecha_creacion'
                        and pdo_fecha_fin>='$b->fecha_creacion'");

            $monto_comision = $b->monto;
            $porc = 100;

            $datos = array(
                'vendedor' => $vendedor->vdo_id,
                'monto' => $monto_comision,
                'moneda' => 2,
                'usuario' => 'admin',
                'glosa' => "FONDO DIAMANTE (FED)",
                'fecha' => $b->fecha_creacion,
                'estado' => "Pagado",
                'tipo' => 'FED',
                'periodo' => $periodo->pdo_id,
                'fecha_imp' => date("Y-m-d H:i:s"),
                'porcentaje' => $porc);

            include_once 'clases/comisiones.class.php';
            COMISION::insertar_comision($datos);
        } else {
            FUNCIONES::eco("no existe el afiliado $b->int_vendedor...");
            $sql_upd = "update temp_bonos set obs='no existe el afiliado $b->int_vendedor (FED)'
                        where int_vendedor={$b->int_vendedor} 
                            and ven_id={$b->ven_id}
                            and tipo_bono='{$b->tipo_bono}'";
            FUNCIONES::bd_query($sql_upd);
        }
    }
}

function actualizar_saldos_BRA() {
//    $sql = "select * from comision_cobro";
    $sql = "select comcob_vdo_id as vdo_id,comcob_ven_id as ven_id,sum(com_monto)as pagado from comision 
            inner join comision_cobro on (com_ven_id=comcob_ven_id and com_vdo_id=comcob_vdo_id)
                        where com_estado='Pagado' and com_tipo='BRA'
            group by comcob_vdo_id,comcob_ven_id
            ";
    $regs = FUNCIONES::lista_bd_sql($sql);

    foreach ($regs as $reg) {
        $pagado = $reg->pagado;

        $sql_upd = "update comision_cobro set comcob_pagado=$pagado,
            comcob_saldo=(comcob_saldo - $pagado)
            where comcob_ven_id=$reg->ven_id and comcob_vdo_id=$reg->vdo_id";
        FUNCIONES::bd_query($sql_upd);
    }
}

function insertar_comision_periodo() {
    $conec = new ADO();
    $sql = "select com_pdo_id,pdo_descripcion,ges_descripcion,count(com_id)as comisiones from comision
            inner join con_periodo on (com_pdo_id=pdo_id)
            inner join con_gestion on (pdo_ges_id=ges_id)
            where com_estado != 'Anulado'
            and com_pdo_id is not null
            group by com_pdo_id";
    $periodos = FUNCIONES::lista_bd_sql($sql);

    foreach ($periodos as $pdo) {
        $hoy = FUNCIONES::atributo_bd_sql("select pdo_fecha_fin as campo from con_periodo 
            where pdo_id=$pdo->com_pdo_id");
        $sql_ins = "insert into comision_periodo(pdo_id,pdo_usu_cre,pdo_fecha_cre,pdo_estado)
                values('$pdo->com_pdo_id','admin','$hoy','Cerrado')";
        $conec->ejecutar($sql_ins, FALSE, FALSE);
    }
}

function depurar_observados() {
    $sql = "select distinct(ven_id),periodo as cant from temp_bonos
            where obs is not null";

    $ventas = FUNCIONES::lista_bd_sql($sql);
    $conec = new ADO();
    foreach ($ventas as $ven) {

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_numero='NET-{$ven->ven_id}'");

        if ($venta) {
            $conec->ejecutar("update temp_bonos set obs=null where ven_id=$ven->ven_id");
            $conec->ejecutar("update comision set com_estado='Anulado' where com_ven_id=$venta->ven_id");

            establecer_bonos($venta->ven_id);
            cargar_bonos_BIR_BVI($ven->ven_id);
            cargar_bonos_BRA($ven->ven_id);
        }
    }
}

//importar_temporal();
//resetear_fecha_activa();
//establecer_fecha_activa();
//establecer_bonos($_GET[venta]);
//cargar_bonos_BIR_BVI($_GET[cliente]);
//cargar_bonos_BRA($_GET[cliente]);
//cargar_bonos_BEV();
//cargar_bonos_FED();
insertar_comision_periodo();




// depurar_observados();

/* actualizar_saldos_BRA(); */
?>