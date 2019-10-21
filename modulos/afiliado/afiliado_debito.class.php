<?php

class AFILIADO_DEBITO extends AFILIADO {

    function AFILIADO_DEBITO() {
        parent::__construct();
    }

    function autorizacion_debito() {

        $afiliado = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$_GET[id]'");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$afiliado->vdo_venta_inicial'");

        if ($_POST) {

            $resp = $this->guardar_autorizacion($venta, $afiliado);
            if (!$resp->ok) {
                $this->formulario->ventana_volver($resp->mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=AUTORIZACION&id=$afiliado->vdo_id", '', 'Error');
                return FALSE;
            }
            echo "<p>paso guardar_autorizacion</p>";
            
            $debito_periodo = FUNCIONES::objeto_bd_sql("select * from debito_periodo where dp_id='$_POST[dp_id]'");
//            $dp_id = $debito_periodo->dp_id;

            $cobro = FUNCIONES::objeto_bd_sql("select *  from venta_cobro where vcob_ven_id='$venta->ven_id'");
            $resp2 = $this->pagar_cuota($venta, $afiliado, $cobro, $debito_periodo->dp_descripcion);
            if (!$resp2->ok) {
                $this->formulario->ventana_volver($resp2->mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=AUTORIZACION&id=$afiliado->vdo_id", '', 'Error');
                return FALSE;
            }
            echo "<p>paso pagar_cuota</p>";

            $datos3 = (object) array(
                        'venta' => $venta,
                        'afiliado' => $afiliado,
                        'cobro' => $cobro,
                        'pag_id' => $resp2->datos,
                        'comision_acumulada' => $_POST[comision_acumulada],
                        /**/
                        'periodo' => $_POST[pdo_id],
                        'dp_id' => $_POST[dp_id],
                        'datos_cmp' => $resp2->datos_cmp,
                        'fecha_pago' => $cobro->vcob_fecha_pago,
            );

            $resp3 = $this->generar_debito($datos3);
            if (!$resp3->ok) {
                $this->formulario->ventana_volver($resp3->mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=AUTORIZACION&id=$afiliado->vdo_id", '', 'Error');
                return FALSE;
            }
            echo "<p>paso generar_debito</p>";
            $this->formulario->ventana_volver("Debito realizado exitosamente.", $this->link . '?mod=' . $this->modulo);
        } else {
            $this->frm_autorizar_debito($venta, $afiliado);
        }
    }

    function generar_debito($datos) {
//        echo "<p>entrando a generar_debito</p>";

        $venta = $datos->venta;
        $afiliado = $datos->afiliado;
        $cobro = $datos->cobro;
        $pag_id = $datos->pag_id;
        $comision_acumulada = $datos->comision_acumulada;
        $periodo = $datos->periodo;
        $dp_id = $datos->dp_id;
        $glosa = $datos->glosa;
        $datos_cmp = $datos->datos_cmp;

        $conec = new ADO();
        $conec->begin_transaccion();

        $ven_id = $venta->ven_id;
        $vdo_id = $afiliado->vdo_id;
        $monto = $cobro->vcob_monto;

        $sql = "select * from comision
        inner join comision_periodo on (com_pdo_id=pdo_id)
        left join venta on (com_ven_id=ven_id) 
        left join urbanizacion on (ven_urb_id=urb_id)
        where pdo_estado='Cerrado' and com_estado='Pendiente'
        and com_vdo_id='$vdo_id' and com_pdo_id='$periodo'
        order by com_pdo_id asc,com_fecha_cre asc";

//        echo "<p>entrando a select generar_debito:$sql</p>";

        $lista_comisiones = FUNCIONES::objetos_bd_sql($sql);

        $arr_comision = array();
        $arr_com_ids = array();
        $arr_coms_asoc = array();
        $arr_tipo_une = array();
        $arr_une_ids = array();
        $arr_tipos = array('BIR', 'BVI', 'BRA', 'FED');

        for ($i = 0; $i < $lista_comisiones->get_num_registros(); $i++) {
            $com = $lista_comisiones->get_objeto();
            $arr_com_ids[] = $com->com_id;
//            echo "<p>entrando al foreach generar_debito</p>";
            if ($monto > 0) {

                $saldo_comision = $com->com_monto - $com->com_pagado;
                $ademas = '';
                if ($saldo_comision > $monto) {
                    $saldo_comision = $monto;
                } else {
                    $ademas = ",com_estado='Pagado'";
                }

                $sql_upd = "update comision 
                set com_pagado=(com_pagado + $saldo_comision)
                $ademas
                where com_id='$com->com_id'";
//                echo "<p>$sql_upd</p>";
                $conec->ejecutar($sql_upd);
                $arr_comision[] = (object) array('id' => $com->com_id, 'monto' => $saldo_comision);
                $arr_coms_asoc[$com->com_id] = $saldo_comision;
                $arr_tipo_une[$com->com_tipo][$com->urb_une_id * 1] += $saldo_comision;
                $arr_une_ids[] = $com->urb_une_id * 1;
                $monto -= $saldo_comision;
            } else {
                break;
            }
            $lista_comisiones->siguiente();
        }

        if (count($arr_com_ids) > 0) {

            $urb = FUNCIONES::objeto_bd_sql("select urb.* from urbanizacion urb
                inner join venta v on (urb.urb_id=v.ven_urb_id)
                where v.ven_id='$ven_id'");

//        echo "<p>llegando al insert generar_debito</p>";
            $s_coms = json_encode($arr_comision);
            $hoy = date('Y-m-d H:i:s');
            $fecha_pago = $datos->fecha_pago;
            $saldo = $comision_acumulada - $cobro->vcob_monto;
            $sql_ins = "insert into debito(deb_vdo_id,deb_ven_id,deb_vcob_codigo,
            deb_vpag_id,deb_usu_cre,deb_fecha_cre,deb_comision_pagos,
            deb_monto,deb_moneda,deb_comision_inicial,deb_comision_final,
            deb_pdo_id,deb_dp_id,deb_fecha_pago)values";

            $sql_insertar = $sql_ins . "('$vdo_id','$ven_id','$cobro->vcob_codigo',
            '$pag_id','$_SESSION[id]','$hoy','$s_coms','$cobro->vcob_monto',
            '$cobro->vcob_moneda','$comision_acumulada','$saldo','$periodo','$dp_id',
            '$fecha_pago')";

//        echo "<p>$sql_insertar</p>";

            $conec->ejecutar($sql_insertar);

            $arr_cuentas_pas = array(
                'BIR' => '2.1.2.06.2.12',
                'BVI' => '2.1.2.06.2.13',
                'BRA' => '2.1.2.06.2.14',
                'FED' => '2.1.2.06.2.15'
            );

            $detalles = array();

            $arr_une_ids = array_unique($arr_une_ids);
            foreach ($arr_tipos as $tipo) {
                foreach ($arr_une_ids as $une_id) {

                    $monto = $arr_tipo_une[$tipo][$une_id];

                    if ($monto > 0) {
                        $cuenta_pas = $arr_cuentas_pas[$tipo];
                        $detalles[] = array(
                            "cuen" => FUNCIONES::get_cuenta($_SESSION[ges_id], $cuenta_pas),
                            "debe" => $monto,
                            "haber" => 0,
                            "glosa" => $glosa,
                            "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                        );
                    }
                }
            }

            $datos_cmp['detalles'] = $detalles;

            /*
            echo "<pre>arr_tipos:";
            print_r($arr_tipos);
            echo "</pre>";

            echo "<pre>arr_comision:";
            print_r($arr_comision);
            echo "</pre>";

            echo "<pre>arr_coms_asoc:";
            print_r($arr_coms_asoc);
            echo "</pre>";

            echo "<pre>arr_tipo_une:";
            print_r($arr_tipo_une);
            echo "</pre>";

            echo "<pre>arr_une_ids:";
            print_r($arr_une_ids);
            echo "</pre>";

            echo "<pre>detalles:";
            print_r($detalles);
            echo "</pre>";
            */

            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';

            if ($urb->urb_tipo == 'Interno') {
                $comprobante = MODELO_COMPROBANTE::pago_cuota($datos_cmp);
            } elseif ($urb->urb_tipo == 'Externo') {
                $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($datos_cmp);
            }

            COMPROBANTES::registrar_comprobante($comprobante);

            $exito = $conec->commit();
            $resp = new stdClass();
        }

        if ($exito) {
            $resp->ok = TRUE;
        } else {
            $resp->ok = FALSE;
            $resp->mensaje = implode('<br>', $conec->get_errores());
        }

        return $resp;
    }

    function frm_autorizar_debito($venta, $afiliado) {
//        $this->barra_opciones($venta, 'AUTORIZACION');
        echo "<br>";
        $comision_acumulada = $this->comision_total($afiliado->vdo_id);
        $this->formulario->dibujar_titulo("AUTORIZAR DEBITO");
//        $ven_id=$_GET[id];
        $ven_id = $venta->ven_id;
        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago 
            where vpag_estado='activo' and vpag_ven_id=$ven_id 
                order by vpag_id desc limit 1");
        if (!$upago) {
            $upago = new stdClass();
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_fecha_pago = $venta->ven_fecha;
        }
//        $pagado=  $this->total_pagado($ven_id);
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        ?>
        <style>
            .img-boton{
                margin-left: 2px; float: left;cursor: pointer;
            }
            .img-boton:hover{opacity: 0.7}
            .tablaReporte thead tr th{ padding: 0 10px;}

            .nav-paso{ float: left; }
            .nav-pasos{ width: 100%; margin: 0 auto; }
            .num-paso{
                width: 35px; height: 33px; color: #fff; line-height: 32px; margin-bottom: 8px; border-radius: 17px; font-size: 25px;
            }
            .estado-espera{ background-color: #727272; }
            .estado-activo{ background-color: #3066ff; }
            .estado-success{ background-color: #068400; }
            .box-input-read{background: #ededed; border: 1px solid #bfc4c9; float: left; font-size: 12px; height: 23px; line-height: 22px; padding: 0 4px; width: 140px; font-style: italic;}
            .fwbold{font-weight: bold;}
        </style>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <form id="frm_sentencia" name="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=<?php echo $this->modulo; ?>&tarea=AUTORIZACION&id=<?php echo $afiliado->vdo_id ?>" >
            <input type="hidden" name="ven_id" id="ven_id" value="<?php echo $venta->ven_id; ?>">
            <input type="hidden" name="vdo_id" id="vdo_id" value="<?php echo $afiliado->vdo_id; ?>">
            <input type="hidden" name="interes_anual" id="interes_anual" value="<?php echo $venta->ven_val_interes; ?>">
            <input type="hidden" name="saldo" id="saldo" value="<?php echo $upago->vpag_saldo_final; ?>">
            <input type="hidden" name="plazo" id="plazo" value="<?php echo $venta->ven_plazo; ?>">
            <input type="hidden" name="cuota" id="cuota" value="<?php echo $venta->ven_cuota; ?>">
            <!--<input type="hidden" name="comision_acumulada" id="comision_acumulada" value="<?php // echo $comision_acumulada; ?>">-->
            <input type="hidden" name="comision_acumulada" id="comision_acumulada" value="0">
            <input type="hidden" name="pdo_id" id="pdo_id" value="0">
            <input type="hidden" name="u_fecha_valor" id="u_fecha_valor" value="<?php echo $upago->vpag_fecha_valor; ?>">
            <input type="hidden" name="u_fecha_pago" id="u_fecha_pago" value="<?php echo $upago->vpag_fecha_pago; ?>">

            <div class="nav-pasos" >
                <div id="nav-paso-1" class="nav-paso" style="width: 50%">
                    <div class="num-paso estado-activo">1</div>
                    <div class="estado-activo">&nbsp;</div>
                </div>
                <div id="nav-paso-2" class="nav-paso" style="width: 50%">
                    <div class="num-paso estado-espera">2</div>
                    <div class="estado-espera">&nbsp;</div>
                </div>
                <div style="clear: both;"></div>
            </div>
            <div class="cont-pasos">
                <div class="box-paso" id="frm_paso1">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width:80%" >
                            <div class="Subtitulo">INGRESE LA FECHA DE PAGO Y FECHA VALOR</div>
                            <div id="ContenedorSeleccion">
        <?php
        $def_fecha = date('d/m/Y');
//                                $def_fecha='26/12/2014';
        ?>
                                <?php $str_cliente = FUNCIONES::interno_nombre($venta->ven_int_id); ?>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro. Venta</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_id; ?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $str_cliente; ?></div>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Concepto</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Moneda</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_moneda($venta->ven_moneda); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $upago->vpag_saldo_final; ?></div>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Debitacion Automatica:</div>
                                    <div id="CajaInput">
                                        <select id="dp_id" name="dp_id" style="width: 200px;">
                                            <option>- Seleccion -</option>
        <?php
        $sql = "select * from debito_periodo 
                                        inner join con_periodo on (dp_pdo_id=pdo_id)
                                        inner join con_gestion on (pdo_ges_id=ges_id)
                                        where dp_eliminado='No' order by dp_id desc";
        $l_dps = FUNCIONES::lista_bd_sql($sql);

        foreach ($l_dps as $ele) {
            ?>
                                                <option value="<?php echo $ele->dp_id; ?>">
                                                <?php
                                                echo "$ele->ges_descripcion  - $ele->pdo_descripcion - $ele->dp_descripcion";
                                                ?>
                                                </option>
                                                    <?php
                                                }
                                                ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Comision Acumulada</div>
                                    <div id="CajaInput">
                                        <div class="read-input div_comision_acumulada">0</div>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Fecha Pago</div>
                                    <div id="CajaInput">
        <?php FORMULARIO::cmp_fecha('fecha_pago'); ?>
                                        <!--<input id="fecha_pago" name="fecha_pago" class="caja_texto" type="text" value="<?php // echo $def_fecha; ?>" size="20" autocomplete="off">-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Fecha Valor</div>
                                    <div id="CajaInput">
                                        <input id="fecha_valor" name="fecha_valor" class="caja_texto" type="text" value="<?php echo $def_fecha; ?>" size="20" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div id="CajaBotones">
                                        <center>
                                            <input class="boton" type="button" value="Siguiente >>" onclick="javascript:frm_paso(2);">
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-paso" id="frm_paso2">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width:80%">
                            <div class="Subtitulo">AUTORIZACION DE DEBITO</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro. Venta</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_id; ?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $str_cliente; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago); ?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input" id="txt_fecha_pago">&nbsp;</div>
                                        <!--<input type="text" size="20" name="fecha_pago" id="fecha_pago" value="<?php // echo date('d/m/Y'); ?>">-->
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor); ?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input" id="txt_fecha_valor">&nbsp;</div>
                                        <!--<input type="text" size="20" name="fecha_valor" id="fecha_valor" value="<?php // echo date('d/m/Y'); ?>">-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Concepto</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Moneda</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_moneda($venta->ven_moneda); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $upago->vpag_saldo_final; ?></div>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Comision Acumulada</div>
                                    <div id="CajaInput">
                                        <div class="read-input div_comision_acumulada">0</div>
        <? ?>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Dias Interes</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="dias_interes" id="dias_interes" value="">
                                        <input type="hidden" name="interes_ini" id="interes_ini" value="" >
                                        <div class="read-input" id="txt_dias_interes">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Interes</div>
                                    <div id="CajaInput">
        <?php
        $desc_interes_usu_ids = FUNCIONES::ad_parametro('par_desc_interes_usu_ids');
        $usu_ids = explode(',', $desc_interes_usu_ids);
        $usu_descint = in_array($_SESSION[id], $usu_ids);
        ?>
                                        <?php if ($usu_descint) { ?>
                                            <input type="text" class="caja_texto" name="interes" id="interes" value="" autocomplete="off">
                                        <?php } else { ?>
                                            <input id="interes" type="hidden" autocomplete="off" value="" name="interes">
                                            <div id="cmp_lab_interes" class="read-input"></div>
        <?php } ?>



                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
                                    <div class="Etiqueta">Formulario Pendiente</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="ap_form_ids" id="ap_form_ids" value="">
                                        <input type="hidden" name="ap_form_montos" id="ap_form_montos" value="">
                                        <input type="hidden" name="ap_form" id="ap_form" value="">
                                        <div class="read-input" id="txt_ap_form">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
        <?php
        $sql_cuotas_mora = "select * from interno_deuda 
                                                        where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                                                        ind_capital_pagado > 0 and
                                                        ind_mora_pagado < ind_mora and ind_tipo='pcuota' order by ind_id asc
                                                    ";
//                                    $cuotas_mora=  FUNCIONES::lista_bd_sql($sql_cuotas_mora);
        $cuotas_mora = array();

        $ap_mora_con = 0;

        $ap_mora_con_ids = array();
        $ap_mora_con_montos = array();
        foreach ($cuotas_mora as $cu) {
            $mmora = $cu->ind_mora - $cu->ind_mora_pagado;
            $ap_mora_con+=$mmora;
            $ap_mora_con_ids[] = $cu->ind_id;
            $ap_mora_con_montos[] = $mmora;
        }
        ?>
                                    <div class="Etiqueta">Mora Pendiente</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="ap_mora_con_ids" id="ap_mora_con_ids" value="<?php echo implode(',', $ap_mora_con_ids); ?>">
                                        <input type="hidden" name="ap_mora_con_montos" id="ap_mora_con_montos" value="<?php echo implode(',', $ap_mora_con_montos); ?>">
                                        <input type="hidden" name="ap_mora_con" id="ap_mora_con" value="<?php echo $ap_mora_con; ?>">
                                        <div class="read-input" id="txt_ap_mora_con"><?php echo $ap_mora_con; ?></div>
                                    </div>
                                </div>

                                <div id="ContenedorDiv" style="display: block;">
                                    <div class="Etiqueta">Nro. de Cuotas</div>
                                    <div id="CajaInput">
                                        <input type="text" size="20" name="sol_nro_cuotas" id="sol_nro_cuotas" value="" autocomplete="off">
                                    </div>
                                    <img src="images/enter.png" width="25" class="img-boton" id="enter-cuotas">
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Cuotas</div>
                                    <div id="CajaInput">
                                        <table class="tablaReporte" id="list_cuotas" width="100%" cellspacing="0" cellpadding="0">
                                            <thead>
                                                <tr>
                                                    <th>Nro</th>
                                                    <th>Fecha Prog.</th>
                                                    <th>Interes</th>
                                                    <th>Capital</th>
                                                    <th>Monto</th>
                                                    <th>Saldo</th>
                                                    <!--<th>formulario</th>-->
                                                    <th>Mora</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <input type="hidden" id="nro_cuota_sig" name="nro_cuota_sig">
                                                        <input type="hidden" id="fecha_cuota_sig" name="fecha_cuota_sig">
                                                        <input type="hidden" id="s_cuota" name="s_cuota">
                                                        <input type="hidden" id="nro_cuotas" name="nro_cuotas">
                                                        <input type="hidden" id="u_capital" name="u_capital">
                                                        <input type="hidden" id="u_saldo" name="u_saldo" value="<?php echo $upago->vpag_saldo_final; ?>">
                                                        <input type="hidden" id="nu_capital_ids" name="nu_capital_ids">
                                                        <input type="hidden" id="nu_capital_montos" name="nu_capital_montos">
                                                        <input type="hidden" id="nu_capital" name="nu_capital">
                                                        <span id="txt_nu_capital"></span>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
        <!--                                                    <td>
                                                        <input type="hidden" id="nu_form_ids" name="nu_form_ids">
                                                        <input type="hidden" id="nu_form_montos" name="nu_form_montos">
                                                        <input type="hidden" id="nu_form" name="nu_form">
                                                        <span id="txt_nu_form"></span>
                                                    </td>-->
                                                    <td>
                                                        <input type="hidden" id="nu_mora_gen_ids" name="nu_mora_gen_ids">
                                                        <input type="hidden" id="nu_mora_gen_montos" name="nu_mora_gen_montos">
                                                        <input type="hidden" id="nu_mora_gen_dias" name="nu_mora_gen_dias">
                                                        <input type="hidden" id="nu_mora_gen" name="nu_mora_gen">
                                                        <span id="txt_nu_mora_gen"></span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Capital</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="capital_ids" id="capital_ids" value="">
                                        <input type="hidden" name="capital_montos" id="capital_montos" value="">
                                        <!--<input type="text" class="caja_texto" name="capital" id="capital" value="" autocomplete="off">-->
                                        <input type="hidden" name="capital" id="capital" value="">
                                        <div class="read-input" id="txt_capital">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Formulario</div>
                                    <div id="CajaInput">
        <!--                                        <input type="hidden" name="form_ids" id="form_ids" value="">
                                        <input type="hidden" name="form_montos" id="form_montos" value="">-->

                                        <input type="hidden" name="moneda" id="moneda" value="<?php echo $venta->ven_moneda; ?>" >
                                        <input type="hidden" name="par_val_form" id="par_val_form" value="<?php echo $venta->ven_form; ?>" >
                                        <input type="text" class="caja_texto" name="form" id="form" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Envio</div>
                                    <div id="CajaInput">

                                        <input type="hidden" name="par_val_envio" id="par_val_envio" value="" >
                                        <input type="text" class="caja_texto" name="envio" id="envio" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
                                    <div class="Etiqueta fwbold">Mora</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="mora_con_ids" id="mora_con_ids" value="">
                                        <input type="hidden" name="mora_con_montos" id="mora_con_montos" value="">
                                        <input type="hidden" name="mora_gen_ids" id="mora_gen_ids" value="">
                                        <input type="hidden" name="mora_gen_montos" id="mora_gen_montos" value="">
                                        <input type="hidden" name="mora_gen_dias" id="mora_gen_dias" value="">

                                        <input type="hidden" name="mora_ids" id="mora_ids" value="">
                                        <input type="hidden" name="mora_montos" id="mora_montos" value="">
        <!--                                        <input type="hidden" name="mora" id="mora" value="">
                                        <div class="read-input" id="txt_mora">0</div>-->
                                        <input type="text" class="caja_texto" name="mora" id="mora" value="" autocomplete="off">
                                    </div>
                                </div>
                                <!--                                <div id="ContenedorDiv">
                                                                    <div class="Etiqueta fwbold">Monto</div>
                                                                    <div id="CajaInput">
                                                                        <input type="hidden" name="monto" id="monto" value="">
                                                                        <div class="read-input" id="txt_monto">&nbsp;</div>
                                                                    </div>
                                                                </div>-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Monto</div>
                                    <div id="CajaInput">
                                        <input type="text" class="caja_texto" name="monto" id="monto" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="saldo_final" id="saldo_final" value="">
                                        <div class="read-input" id="txt_saldo_final">&nbsp;</div>
                                    </div>
                                </div>

                                <div style="clear: both;"></div>
                                <div id="ContenedorDiv">
                                    <div id="CajaBotones">
                                        <center>
                                            <input class="boton" type="button" onclick="frm_paso(1);" value="<< Anterior" name="">
                                            <input class="boton" type="button" onclick="enviar_frm_pagos();" value="Guardar" name="">
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <style>
            .cmp_edit_input,.cmp_ico_edit,read-input{
                float: left;
            }
            .cmp_ico_edit{cursor: pointer;}
            .cmp_ico_edit img{height: 23px;}
            .dnone{display: none;}
        </style>
        <script>
                                                _NEXT_PASO = false;
                                                _MESSAGE = 'Seleccione una Debitacion Automatica.';
                                                $('#dp_id').change(function() {
                                                    mostrar_ajax_load();
                                                    var dp_id = $('#dp_id option:selected').val() * 1;

                                                    if (dp_id == 0) {
        //                    $.prompt('El ');
                                                        return false;
                                                    }

                                                    $.get('ajax_comisiones.php',
                                                            {peticion: 'comision_periodo', vdo_id: $('#vdo_id').val(), dp_id: dp_id}, function(respuesta) {
                                                        ocultar_ajax_load();
                                                        var res = JSON.parse(respuesta);
                                                        var com_acum = res.comision_mensual;

                                                        if (res.exito == 'si') {

                                                            $('.div_comision_acumulada').html(com_acum);
                                                            $('#comision_acumulada').val(com_acum);
                                                            $('#pdo_id').val(res.pdo_id);
                                                            _NEXT_PASO = true;
                                                            _IND_ID = res.ind_id;
                                                            console.log(respuesta);
                                                        } else {
                                                            _NEXT_PASO = false;
                                                            _MESSAGE = res.mensaje;
                                                            $.prompt(res.mensaje);
                                                            return false;
                                                        }
                                                    });
                                                });

                                                $("#fecha_pago").select();
                                                $("#fecha_pago").mask("99/99/9999");
                                                $("#fecha_valor").mask("99/99/9999");
                                                $('#frm_sentencia').submit(function() {
                                                    var fecha_p = $('#fecha_pago').val();
                                                    var fecha_v = $('#fecha_valor').val();
                                                    if (fecha_p !== '' && fecha_v !== '') {
                                                        mostrar_ajax_load();
                                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
                                                            ocultar_ajax_load();
                                                            var dato = JSON.parse(respuesta);
                                                            if (dato.response === "ok") {
                                                                document.frm_sentencia.submit();
                                                            } else if (dato.response === "error") {
                                                                $.prompt(dato.mensaje);
                                                                return false;
                                                            }
                                                        });
                                                    } else {
                                                        $.prompt('Ingrese la Fecha de Pago y la Fecha Valor');
                                                    }
                                                    return false;
                                                });

                                                $('#fecha_valor').keypress(function(e) {
                                                    if (e.keyCode === 13) {
                                                        frm_paso(2);
                                                    }
                                                });

                                                function frm_paso(nro_paso) {
                                                    if (nro_paso === 1) {
                                                        habilitar_formulario(1);
                                                    } else if (nro_paso === 2) {
                                                        if (_NEXT_PASO === false) {
                                                            $.prompt(_MESSAGE);
                                                            return false;
                                                        }
//                                                        cargar_cuotas();
                                                        var fecha_p = $('#fecha_pago').val();
                                                        var fecha_v = $('#fecha_valor').val();

                                                        if (fecha_p !== '' && fecha_v !== '') {
                                                            var ufecha_p = $('#u_fecha_pago').val();
                                                            var ufecha_v = $('#u_fecha_valor').val();
                                                            var afecha_p = fecha_mysql(fecha_p);
                                                            var afecha_v = fecha_mysql(fecha_v);

                                                            if (afecha_p >= ufecha_p && afecha_v >= ufecha_v) {
                                                                mostrar_ajax_load();
                                                                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {

                                                                    var dato = JSON.parse(respuesta);
                                                                    if (dato.response === "ok") {
                                                                        _CAMBIOS = dato.cambios;
                                                                        var interes_anual = $('#interes_anual').val(); // formato mysql
                                                                        var saldo = $('#saldo').val(); // formato mysql

                                                                        var u_fecha = fecha_latina($('#u_fecha_valor').val()); // formato mysql
                                                                        var n_fecha = $('#fecha_valor').val(); // formato lat to mysql

                                                                        $.get('AjaxRequest.php', {peticion: 'dif_fechas', fecha1: u_fecha, fecha2: n_fecha}, function(respuesta) {
                                                                            ocultar_ajax_load();
                                                                            var resp = JSON.parse(respuesta);
                                                                            if (resp.type === 'success') {
                                                                                var dias = resp.res;
                                                                                ;
                                                                                //                console.log(dias);
                                                                                //                console.log('('+interes_anual+'/'+360+'/'+100+')'+'*'+dias);
                                                                                var interes = (interes_anual / 360 / 100) * dias * saldo;
                                                                                $('#dias_interes').val(dias);
                                                                                $('#interes_ini').val(interes.toFixed(2));
                                                                                $('#interes').val(interes.toFixed(2));
                                                                                $('#interes').trigger('change');
        //                                            $('#txt_interes').text(interes.toFixed(2));
                                                                                $('#txt_dias_interes').text(dias);
        //                                        calcular_monto();
                                                                                habilitar_formulario(2);
                                                                            } else {
                                                                                $.prompt('Dato incorrecto');
                                                                                return false;
                                                                            }
                                                                        });


                                                                    } else if (dato.response === "error") {
                                                                        ocultar_ajax_load();
                                                                        $.prompt(dato.mensaje);
                                                                        return false;
                                                                    }
                                                                });
                                                            } else {
                                                                $.prompt('La fecha valor y la fecha de pago actual debe ser mayor o igual a la ultima fecha de valor y fecha de pago');
                                                            }
                                                        } else {
                                                            $.prompt('Ingrese la Fecha de Pago y la Fecha Valor');
                                                        }
                                                        return false;
                                                    }
                                                }
                                                var _FECHA_PAGO = '';
                                                var _FECHA_VALOR = '';
                                                var _CAMBIOS = null;
                                                function habilitar_formulario(form) {
                                                    if (form === 1) {
                                                        $('.box-paso').hide();
                                                        $('#frm_paso1').show();
                                                        $('#nav-paso-2 .estado-activo').each(function() {
                                                            $(this).removeClass('estado-activo');
                                                            $(this).addClass('estado-espera');
                                                        });
                                                        $('#nav-paso-1 .estado-success').each(function() {
                                                            $(this).removeClass('estado-success');
                                                            $(this).addClass('estado-activo');
                                                        });
                                                    } else if (form === 2) {
                                                        _FECHA_PAGO = $('#fecha_pago').val();
                                                        _FECHA_VALOR = $('#fecha_valor').val();
                                                        $('#txt_fecha_pago').text(_FECHA_PAGO);
                                                        $('#txt_fecha_valor').text(_FECHA_VALOR);
                                                        $('.box-paso').hide();
                                                        $('#frm_paso2').show();
                                                        $('#nav-paso-2 .estado-espera').each(function() {
                                                            $(this).removeClass('estado-espera');
                                                            $(this).addClass('estado-activo');
                                                        });
                                                        $('#nav-paso-1 .estado-activo').each(function() {
                                                            $(this).removeClass('estado-activo');
                                                            $(this).addClass('estado-success');
                                                        });

                                                        reset_capital();
                                                        reset_form();
                                                        reset_mora();
                                                        reset_envio();
                                                        calcular_monto();
        //                    calcular_interes();

                                                    }
                                                }



                                                mask_decimal('#interes', null);
                                                mask_decimal('#form', null);
                                                mask_decimal('#mora', null);
                                                mask_decimal('#monto', null);
                                                mask_decimal('#envio', null);
        //            cmp_edit("#capital",
        //                {
        //                    keyup:function(obj,e){
        //                        var objcap=get_ini_capital();
        //                        var capital=$(obj).val()*1;
        //                        
        //                        var capital_ids=new Array();
        //                        var capital_montos=new Array();
        //                        
        //                        var icapital_ids=objcap.capital_ids.split(',');
        //                        var icapital_montos=objcap.capital_montos.split(',');;
        //
        //                        for(var i=0;i<icapital_ids.length;i++){
        //                            var icap_id=icapital_ids[i];
        //                            var icap_monto=icapital_montos[i]*1;
        //                            var res_cap=0;
        //                            if(capital>=icap_monto){
        //                                if(i===icapital_ids.length-1){
        //                                    capital_ids.push(icap_id);
        //                                    capital_montos.push(capital);
        //                                    res_cap=capital;
        //                                }else{
        //                                    capital_ids.push(icap_id);
        //                                    capital_montos.push(icap_monto);
        //                                    res_cap=icap_monto;
        //                                }
        //                                
        //                            }else if(capital>0 && capital<icap_monto){
        //                                capital_ids.push(icap_id);
        //                                capital_montos.push(capital);
        //                                res_cap=capital;
        //                            }
        //                            capital=(capital-res_cap).toFixed(2)*1;
        //                        }
        //                        $('#capital_ids').val(capital_ids.join(','));
        //                        $('#capital_montos').val(capital_montos.join(','));
        //                        calcular_monto();
        //                    },
        //                    cancel:function(){
        //                        var capital=reset_capital();
        //                        calcular_monto();
        //                        return capital.toFixed(2);
        //                    }
        //                }
        //            );
        <?php if ($usu_descint) { ?>
                                                    cmp_edit("#interes",
                                                            {
                                                                keyup: function(obj, e) {
                                                                    calcular_monto();

                                                                },
                                                                cancel: function() {
                                                                    var form = reset_interes();
                                                                    calcular_monto();
                                                                    return form.toFixed(2) * 1;
                                                                }
                                                            }
                                                    );
        <?php } else { ?>
                                                    $('#interes').change(function() {
                                                        $('#cmp_lab_interes').text($(this).val());
                                                    });
        <?php } ?>
                                                cmp_edit("#form",
                                                        {
                                                            keyup: function(obj, e) {
                                                                calcular_monto();

                                                            },
                                                            cancel: function() {
                                                                var form = reset_form();
                                                                calcular_monto();
                                                                return form.toFixed(2) * 1;
                                                            }
                                                        }
                                                );
                                                cmp_edit("#envio",
                                                        {
                                                            keyup: function(obj, e) {
                                                                calcular_monto();

                                                            },
                                                            cancel: function() {
                                                                var form = reset_envio();
                                                                calcular_monto();
                                                                return form.toFixed(2) * 1;
                                                            }
                                                        }
                                                );
                                                cmp_edit("#mora",
                                                        {
                                                            keyup: function(obj, e) {
                                                                var objmora = get_ini_mora();
                                                                var mora = $(obj).val() * 1;

                                                                var mora_ids = new Array();
                                                                var mora_montos = new Array();

                                                                var imora_ids = objmora.mora_ids.split(',');
                                                                var imora_montos = objmora.mora_montos.split(',');
                                                                ;

                                                                for (var i = 0; i < imora_ids.length; i++) {
                                                                    var imora_id = imora_ids[i];
                                                                    var imora_monto = imora_montos[i] * 1;
                                                                    var res_mora = 0;
                                                                    if (mora >= imora_monto) {
                                                                        if (i === imora_ids.length - 1) {
                                                                            mora_ids.push(imora_id);
                                                                            mora_montos.push(mora);
                                                                            res_mora = mora;
                                                                        } else {
                                                                            mora_ids.push(imora_id);
                                                                            mora_montos.push(imora_monto);
                                                                            res_mora = imora_monto;
                                                                        }
                                                                    } else if (mora > 0 && mora < imora_monto) {
                                                                        mora_ids.push(imora_id);
                                                                        mora_montos.push(mora);
                                                                        res_mora = mora;
                                                                    }
                                                                    mora = (mora - res_mora).toFixed(2) * 1;
                                                                }
                                                                $('#mora_ids').val(mora_ids.join(','));
                                                                $('#mora_montos').val(mora_montos.join(','));
                                                                calcular_monto();
                                                            },
                                                            cancel: function() {
                                                                var mora = reset_mora();
                                                                calcular_monto();
                                                                return mora.toFixed(2);
                                                            }
                                                        }
                                                );

                                                cmp_edit("#monto",
                                                        {
                                                            keyup: function(obj, e) {

                                                                var interes = $('#interes').val() * 1;
                                                                var form = $('#form').val() * 1;
                                                                var mora = $('#mora').val() * 1;
                                                                var objcap = get_ini_capital();
        //                        console.log($(obj).val()+'-'+interes+'-'+form+'-'+mora);
                                                                var capital = $(obj).val() * 1 - interes - form - mora;
        //                        console.log(capital);
                                                                $('#capital').val(capital.toFixed(2));
                                                                $('#txt_capital').text(capital.toFixed(2));
                                                                var capital_ids = new Array();
                                                                var capital_montos = new Array();

                                                                var icapital_ids = objcap.capital_ids.split(',');
                                                                var icapital_montos = objcap.capital_montos.split(',');
                                                                ;

                                                                for (var i = 0; i < icapital_ids.length; i++) {
                                                                    var icap_id = icapital_ids[i];
                                                                    var icap_monto = icapital_montos[i] * 1;
                                                                    var res_cap = 0;
                                                                    if (capital >= icap_monto) {
                                                                        if (i === icapital_ids.length - 1) {
                                                                            capital_ids.push(icap_id);
                                                                            capital_montos.push(capital);
                                                                            res_cap = capital;
                                                                        } else {
                                                                            capital_ids.push(icap_id);
                                                                            capital_montos.push(icap_monto);
                                                                            res_cap = icap_monto;
                                                                        }

                                                                    } else if (capital > 0 && capital < icap_monto) {
                                                                        capital_ids.push(icap_id);
                                                                        capital_montos.push(capital);
                                                                        res_cap = capital;
                                                                    }
                                                                    capital = (capital - res_cap).toFixed(2) * 1;
                                                                }
                                                                $('#capital_ids').val(capital_ids.join(','));
                                                                $('#capital_montos').val(capital_montos.join(','));
                                                                calcular_saldo_final();
        //                        calcular_monto();
                                                            },
                                                            cancel: function() {
                                                                var interes = $('#interes').val() * 1;
                                                                var form = $('#form').val() * 1;
                                                                var mora = $('#mora').val() * 1;
                                                                var capital = reset_capital();
                                                                calcular_monto();
        //                        calcular_saldo_final();
                                                                return (capital + interes + form + mora).toFixed(2) * 1;

                                                            }
                                                        }
                                                );

                                                habilitar_formulario(1);

                                                mask_decimal('#sol_nro_cuotas', null);

                                                $('#enter-cuotas').click(function() {
                                                    cargar_cuotas();
                                                });

                                                $('#sol_nro_cuotas').keypress(function(e) {
                                                    if (e.keyCode === 13) {
                                                        cargar_cuotas();
                                                    }
                                                });

                                                $('#fecha_valor').change(function() {
        //                calcular_interes();
                                                });

                                                function cargar_cuotas() {
                                                    var sol_nro_cuotas=$('#sol_nro_cuotas').val()*1;
                                                    var params = {};
                                                    params.tarea = 'vcuotas';
                                                    params.fecha_pago = _FECHA_PAGO;
                                                    params.ven_id = $('#ven_id').val();
                                                    params.nro_cuotas=sol_nro_cuotas;
//                                                    params.ind_id = _IND_ID;
                                                    mostrar_ajax_load();
                                                    $.post('ajax.php', params, function(respuesta) {
                                                        ocultar_ajax_load();

                                                        var resp = JSON.parse(trim(respuesta));
        //                    console.log(respuesta);

                                                        var nu_capital_ids = new Array();
                                                        var nu_capital_montos = new Array();
                                                        var sum_nu_capital = 0;

                                                        var nu_form_ids = new Array();
                                                        var nu_form_montos = new Array();
                                                        var sum_nu_form = 0;

                                                        var nu_mora_gen_ids = new Array();
                                                        var nu_mora_gen_montos = new Array();
                                                        var nu_mora_gen_dias = new Array();
                                                        var sum_nu_mora_gen = 0;

                                                        $('#list_cuotas tbody tr').remove();
                                                        var trows = '';
                                                        var u_capital = 0;
                                                        var lista = resp.listac;
                                                        for (var i = 0; i < lista.length; i++) {
                                                            var fil = lista[i];
                                                            trows += '<tr>';
                                                            trows += '     <td>';
                                                            trows += '        <input type="hidden" name="cuotas[]" value="' + fil.id + '">';
                                                            trows += '        ' + fil.nro;
                                                            trows += '     </td>';
                                                            trows += '     <td>' + fecha_latina(fil.fecha_prog) + '</td>';
                                                            trows += '     <td>' + fil.interes + '</td>';
                                                            trows += '     <td>' + fil.capital + '</td>';
                                                            trows += '     <td>' + fil.monto + '</td>';
                                                            trows += '     <td>' + fil.saldo + '</td>';
        //                        trows+='     <td>'+fil.form+'</td>';
                                                            trows += '     <td>' + fil.mora + '</td>';
                                                            trows += '</tr>';

                                                            nu_capital_ids.push(fil.id);
                                                            nu_capital_montos.push(fil.capital);
                                                            sum_nu_capital += fil.capital * 1;

                                                            if (fil.form * 1 > 0) {
                                                                nu_form_ids.push(fil.id);
                                                                nu_form_montos.push(fil.form);
                                                                sum_nu_form += fil.form * 1;
                                                            }

                                                            if (fil.mora * 1 > 0) {
                                                                nu_mora_gen_ids.push(fil.id);
                                                                nu_mora_gen_montos.push(fil.mora);
                                                                nu_mora_gen_dias.push(fil.mora_dias);
                                                                sum_nu_mora_gen += fil.mora * 1;
                                                            }
                                                            u_capital = fil.capital;

                                                        }
                                                        sum_nu_capital = sum_nu_capital.toFixed(2);
                                                        sum_nu_form = sum_nu_form.toFixed(2);
                                                        sum_nu_mora_gen = sum_nu_mora_gen.toFixed(2);

                                                        $('#nro_cuotas').val(lista.length);
                                                        $('#u_capital').val(u_capital);
                                                        var saldo = $('#saldo').val() * 1;
                                                        var u_saldo = saldo - sum_nu_capital;
                                                        $('#u_saldo').val(u_saldo.toFixed(2));

                                                        $('#nu_capital_ids').val(nu_capital_ids.join(','));
                                                        $('#nu_capital_montos').val(nu_capital_montos.join(','));
                                                        $('#nu_capital').val(sum_nu_capital);
                                                        $('#txt_nu_capital').text(sum_nu_capital);

                                                        $('#nu_form_ids').val(nu_form_ids.join(','));
                                                        $('#nu_form_montos').val(nu_form_montos.join(','));
                                                        $('#nu_form').val(sum_nu_form);
                                                        $('#txt_nu_form').text(sum_nu_form);

                                                        $('#nu_mora_gen_ids').val(nu_mora_gen_ids.join(','));
                                                        $('#nu_mora_gen_montos').val(nu_mora_gen_montos.join(','));
                                                        $('#nu_mora_gen_dias').val(nu_mora_gen_dias.join(','));
                                                        $('#nu_mora_gen').val(sum_nu_mora_gen);
                                                        $('#txt_nu_mora_gen').text(sum_nu_mora_gen);
                                                        $('#list_cuotas tbody').append(trows);

                                                        var s_cuota = resp.scuota;
                                                        if (s_cuota === null) {
                                                            var u_fil = lista[lista.length - 1];
                                                            s_cuota = {};
                                                            s_cuota.nro = u_fil.nro * 1 + 1;
                                                            s_cuota.fecha_prog = sumar_dias(u_fil.fecha_prog, 30);
                                                        }
        //                    console.log(s_cuota);
                                                        $('#s_cuota').val(JSON.stringify(s_cuota));
                                                        $('#nro_cuota_sig').val(s_cuota.nro);
                                                        $('#fecha_cuota_sig').val(s_cuota.fecha_prog);

        //                    $('#capital').val(sum_nu_capital);
        //                    $('#txt_capital').text(sum_nu_capital);

                                                        reset_capital();
                                                        reset_form();
                                                        reset_mora();
                                                        calcular_monto();
                                                    });
                                                }

                                                function get_ini_capital() {
                                                    var capital_ids = trim($('#nu_capital_ids').val());
                                                    var capital_montos = trim($('#nu_capital_montos').val());
                                                    var capital = $('#nu_capital').val() * 1;
                                                    return {'capital_ids': capital_ids, 'capital_montos': capital_montos, 'capital': capital};
                                                }

                                                function get_ini_form() {
                                                    var ap_form_ids = trim($('#ap_form_ids').val());
                                                    var ap_form_montos = trim($('#ap_form_montos').val());
                                                    var ap_form = $('#ap_form').val() * 1;
                                                    var nu_form_ids = trim($('#nu_form_ids').val());
                                                    var nu_form_montos = trim($('#nu_form_montos').val());
                                                    var nu_form = $('#nu_form').val() * 1;

                                                    var form_ids = '';
                                                    var form_montos = '';
                                                    if (ap_form_ids !== '' && nu_form_ids !== '') {
                                                        form_ids = ap_form_ids + ',' + nu_form_ids;
                                                        form_montos = ap_form_montos + ',' + nu_form_montos;
                                                    } else if (ap_form_ids !== '' && nu_form_ids === '') {
                                                        form_ids = ap_form_ids;
                                                        form_montos = ap_form_montos;
                                                    } else if (ap_form_ids === '' && nu_form_ids !== '') {
                                                        form_ids = nu_form_ids;
                                                        form_montos = nu_form_montos;
                                                    }
                                                    var form = ap_form + nu_form;
                                                    return {'form_ids': form_ids, 'form_montos': form_montos, 'form': form};
                                                }

                                                function get_ini_mora() {
                                                    var ap_mora_con_ids = trim($('#ap_mora_con_ids').val());
                                                    var ap_mora_con_montos = trim($('#ap_mora_con_montos').val());
                                                    var ap_mora_con = $('#ap_mora_con').val() * 1;

                                                    var nu_mora_gen_ids = trim($('#nu_mora_gen_ids').val());
                                                    var nu_mora_gen_montos = trim($('#nu_mora_gen_montos').val());
                                                    var nu_mora_gen_dias = trim($('#nu_mora_gen_dias').val());
                                                    var nu_mora_gen = $('#nu_mora_gen').val() * 1;


                                                    var mora_con_ids = ap_mora_con_ids;
                                                    var mora_con_montos = ap_mora_con_montos;
                                                    var mora_con = ap_mora_con;

                                                    var mora_gen_ids = nu_mora_gen_ids;
                                                    var mora_gen_montos = nu_mora_gen_montos;
                                                    var mora_gen_dias = nu_mora_gen_dias;
                                                    var mora_gen = nu_mora_gen;

                                                    var mora_ids = '';
                                                    var mora_montos = '';
                                                    if (mora_con_ids !== '' && mora_gen_ids !== '') {
                                                        mora_ids = mora_con_ids + ',' + mora_gen_ids;
                                                        mora_montos = mora_con_montos + ',' + mora_gen_montos;
                                                    } else if (mora_con_ids !== '' && mora_gen_ids === '') {
                                                        mora_ids = mora_con_ids;
                                                        mora_montos = mora_con_montos;
                                                    } else if (mora_con_ids === '' && mora_gen_ids !== '') {
                                                        mora_ids = mora_gen_ids;
                                                        mora_montos = mora_gen_montos;
                                                    }
                                                    var mora = mora_con + mora_gen;
                                                    return {
                                                        'mora_ids': mora_ids, 'mora_montos': mora_montos, 'mora': mora,
                                                        'mora_con_ids': mora_con_ids, 'mora_con_montos': mora_con_montos, 'mora_con': mora_con,
                                                        'mora_gen_ids': mora_gen_ids, 'mora_gen_montos': mora_gen_montos, 'mora_gen_dias': mora_gen_dias, 'mora_gen': mora_gen,
                                                    };
                                                }

                                                function reset_capital() {
                                                    var obcap = get_ini_capital();

                                                    $('#capital_ids').val(obcap.capital_ids);
                                                    $('#capital_montos').val(obcap.capital_montos);

                                                    $('#capital').val(obcap.capital.toFixed(2));
                                                    $('#txt_capital').text(obcap.capital.toFixed(2));
                                                    $('#capital').trigger('change');
                                                    return obcap.capital;

                                                }

                                                function reset_interes() {
                                                    var interes = $('#interes_ini').val();
                                                    $('#interes').val(interes);
                                                    $('#interes').trigger('change');
                                                    return interes;
                                                }
                                                function reset_form() {
                                                    var moneda = $('#moneda').val() * 1;
                                                    var cambio_usd = 1;
                                                    if (moneda == 1) {
                                                        for (var i = 0; i < _CAMBIOS.length; i++) {
                                                            var cambio = _CAMBIOS[i];
                                                            if (cambio.id == 2) {
                                                                cambio_usd = cambio.val;
                                                            }
                                                        }
                                                    }
        //                $('#form_ids').val(obform.form_ids);
        //                $('#form_montos').val(obform.form_montos);
                                                    var val_form = $('#par_val_form').val() * cambio_usd;
                                                    $('#form').val(val_form.toFixed(2));
                                                    $('#form').trigger('change');
                                                    return val_form;
                                                }
                                                function reset_envio() {
                                                    $('#envio').val(0);
                                                    $('#envio').trigger('change');
                                                    return 0;
                                                }

                                                function reset_mora() {
                                                    var obmora = get_ini_mora();
                                                    $('#mora_con_ids').val(obmora.mora_con_ids);
                                                    $('#mora_con_montos').val(obmora.mora_con_montos);
                                                    $('#mora_con').val(obmora.mora_con);
                                                    $('#mora_gen_ids').val(obmora.mora_gen_ids);
                                                    $('#mora_gen_montos').val(obmora.mora_gen_montos);
                                                    $('#mora_gen_dias').val(obmora.mora_gen_dias);
                                                    $('#mora_gen').val(obmora.mora_gen);

                                                    $('#mora_ids').val(obmora.mora_ids);
                                                    $('#mora_montos').val(obmora.mora_montos);
                                                    $('#mora').val(obmora.mora.toFixed(2));
                                                    $('#mora').trigger('change');
                                                    return obmora.mora;
                                                }

        //            function calcular_interes(){
        //                var interes_anual=$('#interes_anual').val(); // formato mysql
        //                var saldo=$('#saldo').val(); // formato mysql
        //                var u_fecha=$('#u_fecha_valor').val(); // formato mysql
        //                var n_fecha=fecha_mysql($('#fecha_valor').val()); // formato lat to mysql
        //                
        //                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
        //                    var dias=diferencia_dias(u_fecha,n_fecha);
        //    //                console.log(dias);
        //    //                console.log('('+interes_anual+'/'+360+'/'+100+')'+'*'+dias);
        //                    var interes=(interes_anual/360/100)*dias*saldo;
        //                    $('#dias_interes').val(dias);
        //                    $('#interes').val(interes.toFixed(2));
        //                    $('#txt_interes').text(interes.toFixed(2));
        //                    $('#txt_dias_interes').text(dias);
        //                    calcular_monto();
        //                });
        //                    
        //                
        //            }

                                                function calcular_monto() {
                                                    var interes = $('#interes').val() * 1;
                                                    var capital = $('#capital').val() * 1;
                                                    var form = $('#form').val() * 1;
                                                    var envio = $('#envio').val() * 1;
                                                    var mora = $('#mora').val() * 1;
        //                console.log(interes+'+'+capital);
                                                    var monto = interes + capital + form + mora + envio;
        //                console.log(monto);
                                                    $('#monto').val(monto.toFixed(2));
                                                    $('#monto').trigger('change');
        //                $('#txt_monto').text(monto.toFixed(2));
                                                    calcular_saldo_final();
                                                }
                                                function calcular_saldo_final() {
                                                    var nro_cuotas = $('#nro_cuotas').val() * 1;
                                                    var saldo = $('#saldo').val() * 1;
                                                    var capital = $('#capital').val() * 1;
                                                    var saldo_final = (saldo - capital).toFixed(2) * 1;
                                                    $('#saldo_final').val(saldo_final.toFixed(2));
                                                    $('#txt_saldo_final').text(saldo_final.toFixed(2));
                                                    var u_saldo = $('#u_saldo').val() * 1;
        //                console.log(saldo_final+'==='+u_saldo);
                                                    if (saldo_final === u_saldo || nro_cuotas === 0) {
                                                        $('#box-reformular').hide();
                                                    } else {
                                                        var str_scuota = $('#s_cuota').val();
                                                        if (str_scuota !== 'null') {
                                                            var scuota = JSON.parse(str_scuota);
                                                        }

                                                        $('#def_plan_efectivo option:selected').removeAttr('selected')

        //                    $('#box-reformular').hide();
                                                        if (saldo_final < u_saldo) { // pago MAS capital (mantiene la cuota)
                                                            $('#box-reformular').show();
                                                            $('#def_plan_efectivo option:[value="cm"]').attr('selected', 'true');
                                                            $('#cuota_mensual').val($('#cuota').val());
                                                        } else if (saldo_final > u_saldo) { // pago MENOS capital (mantiene el plazo)
                                                            $('#box-reformular').hide();
        //                        $('#def_plan_efectivo option:[value="cm"]').attr('selected','true');
        //                        $('#cuota_mensual').val($('#cuota').val());

        //                        $('#def_plan_efectivo option:[value="mp"]').attr('selected','true');
        //                        var plazo=$('#plazo').val()*1;
        //                        var plazo_res=plazo-(scuota.nro*1)+1;
        //                        if(plazo_res<=0){
        //                            plazo_res=1;
        //                        }
        //                        $('#meses_plazo').val(plazo_res);
                                                        }
                                                        $('#def_plan_efectivo').trigger('change');
                                                        $('#fecha_pri_cuota').val(fecha_latina(scuota.fecha_prog));
                                                    }
                                                }

                                                function enviar_frm_pagos() {
                                                    var capital_ids = trim($('#capital_ids').val()).split(',');
                                                    var capital_montos = trim($('#capital_montos').val()).split(',');
                                                    var capital = $('#capital').val() * 1;
                                                    var nro_cuotas = $('#nro_cuotas').val() * 1;

                                                    var comision_acumulada = $('#comision_acumulada').val() * 1;
                                                    var monto = $('#monto').val() * 1;
                                                    console.info('comision_acumulada => ' + comision_acumulada + ' - monto => ' + monto);
                                                    if (comision_acumulada < monto) {

                                                        $.prompt('El monto a debitar no debe ser mayor a la comision acumulada.');
                                                        return false;

                                                    }


                                                    if (nro_cuotas > 0) {
                                                        var objcap = get_ini_capital();
                                                        var icapital_ids = (objcap.capital_ids).split(',');
                                                        var icapital_montos = (objcap.capital_montos).split(',');
                                                        if (!(capital_ids.length === capital_montos.length &&
                                                                icapital_ids.length === icapital_montos.length &&
                                                                capital_ids.length === icapital_ids.length)) {
                                                            $.prompt("El total capital a pagar no cubre con el Capital de la ultima Cuota");
                                                            return false;
                                                        }
                                                    }
                                                    if (nro_cuotas <= 0) {
                                                        if (capital !== 0) {
                                                            $.prompt('El Capital no debe variar ya que el Numero de Cuotas es 0');
                                                            return false;
                                                        }
                                                    }

        //                var objform=get_ini_form();
        //                var form=$('#form').val()*1;
        //                var iform=objform.form*1;

        //                if(form>iform){
        //                    $.prompt('El pago del Formulario no debe exceder a '+iform);
        //                    return false;
        //                }
                                                    var objmora = get_ini_mora();
                                                    var mora = $('#mora').val() * 1;
                                                    var imora = objmora.mora * 1;

                                                    if (mora > imora) {
                                                        $.prompt('El pago de la Mora no debe exceder a ' + imora);
                                                        return false;
                                                    }

                                                    var saldo_final = $('#saldo_final').val() * 1;

                                                    if (saldo_final < 0) {
                                                        $.prompt("El Saldo Final debe ser Mayor a 0");
                                                        return false;
                                                    }
        //                return false;

                                                    var fecha = $('#fecha_pago').val();
                                                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                        var dato = JSON.parse(respuesta);
                                                        if (dato.response === "ok") {
        //                        console.info('OK');
                                                            console.info('ENVIANDO EL FORMULARIO...');
                                                            document.frm_sentencia.submit();
                                                            
                                                        } else if (dato.response === "error") {
                                                            $.prompt(dato.mensaje);
                                                            return false;
                                                        }
                                                    });
                                                }

        //            calcular_interes();

                                                $('#def_plan_efectivo').change(function() {
                                                    var def = $(this).val();
                                                    if (def === 'mp') {
                                                        $('#meses_plazo').parent().show();
                                                        $('#cuota_mensual').parent().hide();
                                                        $('#cuota_interes').parent().hide();
                                                        $('#ver_plan_efectivo').show();
                                                        $('#add_cuota_efectivo').hide();
                                                        $('#fecha_pri_cuota').prev('span').text('Fecha de la Primer Cuota: ');
                                                        $('#plan_manual_efectivo').hide();
                                                        $('#def_cuota').parent().hide();

                                                    } else if (def === 'cm') {
                                                        $('#meses_plazo').parent().hide();
                                                        $('#cuota_mensual').parent().show();
                                                        $('#cuota_interes').parent().hide();
                                                        $('#ver_plan_efectivo').show();
                                                        $('#add_cuota_efectivo').hide();
                                                        $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                                                        $('#cuota_mensual').prev('span').show();
                                                        $('#fecha_pri_cuota').prev('span').text('Fecha de la Primer Cuota: ');
                                                        $('#plan_manual_efectivo').hide();
                                                        $('#def_cuota').parent().hide();
                                                    } else if (def === 'manual') {
                                                        $('#meses_plazo').parent().hide();
                                                        $('#cuota_mensual').parent().show();
                                                        $('#cuota_interes').parent().show();
                                                        $('#ver_plan_efectivo').hide();
                                                        $('#add_cuota_efectivo').show();
                                                        $('#cuota_mensual').prev('span').hide();
                                                        $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                                                        $('#plan_manual_efectivo').show();
                                                        $('#def_cuota').parent().show();
                                                    }
                                                });

                                                $('#def_plan_efectivo').trigger('change');

                                                function ver_plan_pago() {
                                                    var saldo_financiar = 0;
                                                    var ncuotas = 0;
                                                    var fecha_pri_cuota = 0;
                                                    var monto_cuota = 0;
                                                    var def = $('#def_plan_efectivo option:selected').val();
                                                    if (def === 'mp') {
                                                        ncuotas = $('#meses_plazo').val();
                                                        monto_cuota = '';
                                                    } else if (def === 'cm') {
                                                        ncuotas = '';
                                                        monto_cuota = $('#cuota_mensual').val();
                                                    }
                                                    saldo_financiar = $('#saldo_final').val() * 1;
                                                    fecha_pri_cuota = $('#fecha_pri_cuota').val();
                                                    var fecha_inicio = $('#fecha_valor').val();

                                                    var interes = $('#interes_anual').val();

                                                    if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && saldo_financiar > 0 && fecha_pri_cuota !== '') {
                                                        var moneda = 2;//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                                                        var par = {};
                                                        par.tarea = 'plan_pagos';
                                                        par.saldo_financiar = saldo_financiar;
                                                        par.monto_total = saldo_financiar;
                                                        par.meses_plazo = ncuotas;
                                                        par.cuota_mensual = monto_cuota;
                                                        par.ven_moneda = moneda;
                                                        par.nro_inicio = $('#nro_cuota_sig').val();
                                                        par.fecha_inicio = fecha_inicio;
                                                        par.fecha_pri_cuota = fecha_pri_cuota;
                                                        par.interes = interes;
                                                        mostrar_ajax_load();
                                                        $.post('ajax.php', par, function(resp) {
                                                            ocultar_ajax_load();
                                                            abrir_popup(resp);
                                                        });

                                                    } else {
                                                        $('#tprueba tbody').remove();
                                                        $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                                                    }

                                                }
                                                var popup = null;
                                                function abrir_popup(html) {
                                                    if (popup !== null) {
                                                        popup.close();
                                                    }
                                                    popup = window.open('about:blank', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                                                    var extra = '';
                                                    extra += '<html><head><title>Vista Previa</title><head>';
                                                    extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                                                    extra += '</head> <body> <div id=imprimir> <div id=status> <p>';
                                                    extra += '<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                                                    popup.document.write(extra);
                                                    popup.document.write(html);
                                                    popup.document.write('</center></body></html>');
                                                    popup.document.close();

                                                }

        //            mostrar_ajax_load();
        //            setTimeout('ocultar_ajax_load()',500);
        //            ocultar_ajax_load();
        </script>
        <?php
    }

    function guardar_autorizacion($venta, $afiliado) {
//        $this->barra_opciones($venta, 'AUTORIZACION');
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
//        return;

        $resp = new stdClass();
        $conec = new ADO();
        $conec->begin_transaccion();

        $cob_aut = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id=$venta->ven_id");
        if ($cob_aut) {
            if ($cob_aut->vcob_aut == '0') {
                $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id'";
                $conec->ejecutar($sql_del);
            } else if ($cob_aut->vcob_aut == '1') {
                $resp->mensaje = "Ya existe un cobro Autorizado para la Venta Nro $venta->ven_id";
//                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                $resp->ok = FALSE;
                return $resp;
            }
        }
        $saldo_inicial = $_POST[saldo];
        $codigo = FUNCIONES::fecha_codigo();
        $fecha_pago = FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
        $fecha_valor = FUNCIONES::get_fecha_mysql($_POST[fecha_valor]);
        $interes = $_POST[interes] * 1;
        $capital = $_POST[capital] * 1;
        $form = $_POST[form] * 1;
        $envio = $_POST[envio] * 1;
        $mora = $_POST[mora] * 1;
        $monto = $capital + $interes + $form + $mora + $envio;

        $fecha_cre = date('Y-m-d H:i:s');

        $capital_ids = $_POST[capital_ids];
        $capital_montos = $_POST[capital_montos];

        $interes_ids = '';
        $interes_montos = '';
        if ($interes > 0) {
            $interes_montos = $interes;
            if ($capital > 0) {
                $arcap_ids = explode(',', $capital_ids);
                $interes_ids = $arcap_ids[0];
            } else {
                $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $interes_ids = $scuota->ind_id;
            }
        }

        $form_ids = '';
        $form_montos = '';
        if ($form > 0) {
            $form_montos = $form;
            if ($capital > 0) {
                $arcap_ids = explode(',', $capital_ids);
                $form_ids = $arcap_ids[0];
            } else {
                $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $form_ids = $scuota->ind_id;
            }
        }
        $envio_ids = '';
        $envio_montos = '';
        if ($envio > 0) {
            $envio_montos = $envio;
            if ($capital > 0) {
                $arcap_ids = explode(',', $capital_ids);
                $envio_ids = $arcap_ids[0];
            } else {
                $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $envio_ids = $scuota->ind_id;
            }
        }

        $saldo_final = $_POST[saldo_final];
        $u_saldo = $_POST[u_saldo];
        $refor = 0;
        $tipo_plan = 'cuota';
        $plazo = '';
        $cuota = '';
        $nro_cuota_sig = '0';
        $fecha_pri_cuota = '0000-00-00';
        if ($saldo_final < $u_saldo) { /// saldo final menor al ultimo saldo por que pago mas y se descuenta la ultima cuota
            $refor = 1;
            $tipo_plan = 'cuota';
            $plazo = '';
            $cuota = $venta->ven_cuota;
            $nro_cuota_sig = $_POST[nro_cuota_sig];
            $fecha_pri_cuota = $_POST[fecha_cuota_sig];
        }

        $dias_interes = $_POST[dias_interes];

//        $form_ids=$_POST[form_ids];
//        $form_montos=$_POST[form_montos];

        $mora_con_ids = $_POST[mora_con_ids];
        $mora_con_montos = $_POST[mora_con_montos];
        $mora_gen_ids = $_POST[mora_gen_ids];
        $mora_gen_montos = $_POST[mora_gen_montos];
        $mora_gen_dias = $_POST[mora_gen_dias];
        $mora_ids = $_POST[mora_ids];
        $mora_montos = $_POST[mora_montos];

        $interes_desc = $_POST[interes_ini] - $interes;


        $sql_ins = "insert into venta_cobro(
                        vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,vcob_int_id,vcob_moneda,vcob_saldo_inicial,
                        vcob_dias_interes,vcob_interes,vcob_capital,vcob_form,vcob_envio,vcob_mora,vcob_monto,vcob_saldo_final,
                        vcob_interes_ids,vcob_interes_montos,vcob_capital_ids,vcob_capital_montos,vcob_form_ids,vcob_form_montos,
                        vcob_envio_ids,vcob_envio_montos,vcob_mora_ids,vcob_mora_montos,vcob_mora_con_ids,vcob_mora_con_montos,
                        vcob_mora_gen_ids,vcob_mora_gen_montos,vcob_mora_gen_dias,
                        vcob_fecha_cre,vcob_usu_cre,vcob_aut,vcob_reformular,vcob_tipo_plan,vcob_plazo,vcob_cuota,
                        vcob_fecha_inicio,vcob_fecha_pri_cuota,vcob_nro_cuota_sig,vcob_interes_desc
                    )values(
                        $venta->ven_id,'$codigo','$fecha_pago','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda','$saldo_inicial',
                        '$dias_interes','$interes','$capital','$form','$envio','$mora','$monto','$saldo_final',
                        '$interes_ids','$interes_montos','$capital_ids','$capital_montos','$form_ids','$form_montos',
                        '$envio_ids','$envio_montos',
                        '$mora_ids','$mora_montos','$mora_con_ids','$mora_con_montos',
                        '$mora_gen_ids','$mora_gen_montos','$mora_gen_dias',
                        '$fecha_cre','$_SESSION[id]','1','$refor','$tipo_plan','$plazo','$cuota',
                        '$fecha_valor','$fecha_pri_cuota','$nro_cuota_sig','$interes_desc'
                    )";
//        echo $sql_ins.'<br>';
        $conec->ejecutar($sql_ins);

        $exito = $conec->commit();

        if ($exito) {
            $resp->ok = TRUE;
            $mensaje = "Cobro Autorizado Guardado Exitosamente";
            $resp->mensaje = $mensaje;
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=AUTORIZACION&id=$venta->ven_id",'Ver Cobro','Correcto');
        } else {
            $exito = false;
            $mensajes = $conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $resp->ok = FALSE;
            $resp->mensaje = $mensaje;
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }

        return $resp;
    }

    function comision_total($vdo_id) {
        $sql = "select sum(com_monto-com_pagado)as campo from comision
            inner join comision_periodo on (com_pdo_id=pdo_id)
            where pdo_estado='Cerrado' and com_estado='Pendiente'
            and com_vdo_id='$vdo_id'";
        $saldo_comision = FUNCIONES::atributo_bd_sql($sql) * 1;
        return $saldo_comision;
    }

    function pagar_cuota($venta, $afiliado, $cob, $desc) {

        include_once 'modulos/venta/venta.class.php';
        include_once 'modulos/venta/venta_cuotas.class.php';
        $vcuota = new VENTA_CUOTAS();

        $resp = new stdClass();
        $conec = new ADO();
        $conec->begin_transaccion();
        $codigo = $cob->vcob_codigo;

//        $cob = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id='$venta->ven_id' and vcob_codigo='$codigo'");
        if (!$cob) {
//            $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//            $this->formulario->ventana_volver("No existe nigun cobro a ejecutarse por la venta del afiliado.", "$url", 'Volver', 'Error');
            $resp->ok = FALSE;
            $resp->mensaje = "No existe nigun cobro a ejecutarse por la venta del afiliado.";
            return $resp;
        }

        $vpago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_cob_codigo='$codigo'");
        if ($vpago) {
//            $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//            $this->formulario->ventana_volver("Ya existe un pago con el mismo codigo en la Venta", "$url", 'Volver', 'Error');
            $resp->ok = FALSE;
            $resp->mensaje = "Ya existe un pago con el mismo codigo en la Venta";
            return $resp;
        }

        $str_interes_ids = trim($cob->vcob_interes_ids);
        if ($str_interes_ids && $cob->vcob_interes > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
            $interes_ids = explode(',', trim($cob->vcob_interes_ids));
            if (count($interes_ids) != count($cuotas)) {
//                $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//                $this->formulario->ventana_volver("No existen las cuotas para cobrar el interes", "$url", 'Volver', 'Error');
                $resp->ok = FALSE;
                $resp->mensaje = "No existen las cuotas para cobrar el interes";
                return $resp;
            }
        }
        $str_capital_ids = trim($cob->vcob_capital_ids);
        if ($str_capital_ids && $cob->vcob_capital > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
            $capital_ids = explode(',', trim($cob->vcob_capital_ids));
            if (count($capital_ids) != count($cuotas)) {
//                $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//                $this->formulario->ventana_volver("No existen las cuotas para cobrar el capital", "$url", 'Volver', 'Error');
                $resp->ok = FALSE;
                $resp->mensaje = "No existen las cuotas para cobrar el capital";
                return $resp;
            }
        }
        $str_form_ids = trim($cob->vcob_form_ids);
        if ($str_form_ids && $cob->vcob_form > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
            $form_ids = explode(',', trim($cob->vcob_form_ids));
            if (count($form_ids) != count($cuotas)) {
//                $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//                $this->formulario->ventana_volver("No existen las cuotas para cobrar el formulario", "$url", 'Volver', 'Error');
                $resp->ok = FALSE;
                $resp->mensaje = "No existen las cuotas para cobrar el formulario";
                return $resp;
            }
        }
        $str_envio_ids = trim($cob->vcob_envio_ids);
        if ($str_envio_ids && $cob->vcob_envio > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
            $envio_ids = explode(',', trim($cob->vcob_envio_ids));
            if (count($envio_ids) != count($cuotas)) {
                $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
//                $this->formulario->ventana_volver("No existen las cuotas para cobrar el envio", "$url", 'Volver', 'Error');
                $resp->ok = FALSE;
                $resp->mensaje = "No existen las cuotas para cobrar el envio";
                return $resp;
            }
        }
//        echo "<pre>";
//        print_r($cob);
//        echo "</pre>";

        $val_monto = 0;
        $interes = $cob->vcob_interes;

        $saldo_descuento = FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$venta->ven_int_id and val_estado='Activo'") * 1;
        $descuento = $val_monto;
        if ($descuento > 0) {
            if ($descuento > $interes || $descuento > $saldo_descuento) {
                $url = "$this->link?mod=$this->modulo&tarea=AUTORIZACION&id=$afiliado->vdo_id";
                $this->formulario->ventana_volver("El descuento de debe ser menor al interes y al saldo de descuento", "$url", 'Volver', 'Error');
                return false;
            }
        }

        $fecha_cre = date('Y-m-d H:i:s');
        $usuario_id = $this->usu->get_id();
        $fecha_pago = $cob->vcob_fecha_pago;
        $pag_codigo = FUNCIONES::fecha_codigo();

        $nro_recibo = FUNCIONES::nro_recibo($fecha_pago);

        $monto = $cob->vcob_monto;
        if ($val_monto > 0) {
            $interes = $cob->vcob_interes - $val_monto;
            $monto = $cob->vcob_monto - $val_monto;
        }
        $cob->vcob_interes = $interes;
        $cob->vcob_interes_montos = $interes . '';



        //crear registro venta pago
        $sql_pago = "insert into venta_pago(
                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,
                        vpag_int_id,vpag_moneda,vpag_saldo_inicial,vpag_dias_interes,
                        vpag_interes,vpag_capital,vpag_mora,vpag_form,vpag_envio,vpag_monto,vpag_saldo_final,
                        vpag_interes_ids,vpag_capital_ids,vpag_form_ids,vpag_envio_ids,vpag_mora_ids,vpag_mora_con_ids,vpag_mora_gen_ids,
                        vpag_interes_montos,vpag_capital_montos,vpag_form_montos,vpag_envio_montos,vpag_mora_montos,vpag_mora_con_montos,vpag_mora_gen_montos,vpag_mora_gen_dias,
                        vpag_fecha_cre,vpag_usu_cre,vpag_estado,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,vpag_recibo,vpag_suc_id,vpag_interes_desc,
                        vpag_val_monto
                    )values(
                        '$venta->ven_id','$pag_codigo','$fecha_pago','$cob->vcob_fecha_valor',
                        '$venta->ven_int_id','$venta->ven_moneda','$cob->vcob_saldo_inicial','$cob->vcob_dias_interes',
                        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_envio','$monto','$cob->vcob_saldo_final',
                        '$cob->vcob_interes_ids','$cob->vcob_capital_ids','$cob->vcob_form_ids','$cob->vcob_envio_ids','$cob->vcob_mora_ids','$cob->vcob_mora_con_ids','$cob->vcob_mora_gen_ids',
                        '$cob->vcob_interes_montos','$cob->vcob_capital_montos','$cob->vcob_form_montos','$cob->vcob_envio_montos','$cob->vcob_mora_montos','$cob->vcob_mora_con_montos','$cob->vcob_mora_gen_montos','$cob->vcob_mora_gen_dias',
                        '$fecha_cre','$usuario_id','Activo','$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut','$nro_recibo','$_SESSION[suc_id]','$cob->vcob_interes_desc',
                        '$val_monto'
                        )";
        $conec->ejecutar($sql_pago, true, true);
        $pago_id = ADO::$insert_id;
        $vcuota->guardar_vale($val_monto, $venta, $nro_recibo, $pago_id, $fecha_pago);
//        mysql_insert_id
        include_once 'clases/recibo.class.php';
        $data_recibo = array(
            'recibo' => $nro_recibo,
            'fecha' => $fecha_pago,
            'monto' => $monto,
            'moneda' => $venta->ven_moneda,
            'tabla' => 'venta_pago',
            'tabla_id' => $pago_id
        );
        RECIBO::insertar($data_recibo);

        $data_pago = (object) array(
                    'cob' => $cob,
                    'venta' => $venta,
                    'pago_id' => $pago_id,
                    'fecha_pago' => $fecha_pago,
                    'fecha_cre' => $fecha_cre,
                    'usuario_id' => $usuario_id,
                    'recibo' => $nro_recibo,
                    'pag_codigo' => $pag_codigo,
                    'val_monto' => $val_monto,
        );

        //actualizar interno_deuda
        // *********** PAGAR INTERES        
        $vcuota->pagar_cu_interes($data_pago, $conec);
        // *********** PAGAR CAPITAL        
        $res_cap = (object) $vcuota->pagar_cu_capital($data_pago, $conec);
        //************ PAGAR FORMULARIO        
        $vcuota->pagar_cu_form($data_pago, $conec);
        //****-******* PAGAR ENVIO        
        $vcuota->pagar_cu_envio($data_pago, $conec);
        //************ PAGO MORA        
        $vcuota->pagar_cu_mora($data_pago, $conec);
        //*********************************************

        $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");

        $pagado = $vcuota->total_pagado($venta->ven_id);

        $capital_pag = $pagado->capital;
        $capital_inc = $pagado->incremento;
        $capital_desc = $pagado->descuento;
        $cuota_pag = $ucuota->ind_num_correlativo;

        $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog = $scuota ? $scuota->ind_fecha_programada : '0000-00-00';

        $sql_up = "update venta set 
                            ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', 
                            ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                            ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 
                            ven_usaldo='$cob->vcob_saldo_final', ven_sfecha_prog='$ven_sfecha_prog'
                    where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);

//        $sql_up="update venta set ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', ven_usaldo='$cob->vcob_saldo_final' where ven_id='$venta->ven_id'";
//        $conec->ejecutar($sql_up);

        $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id' and vcob_codigo='$codigo'";
        $conec->ejecutar($sql_del);

        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = $desc . " - Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo";

//        $detalles = array();
//        $detalles[] = array(
//            "cuen" => FUNCIONES::get_cuenta($_SESSION[ges_id], '2.1.2.06.2.01'),
//            "debe" => $monto,
//            "haber" => 0,
//            "glosa" => $glosa,
//            "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//        );

        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $_SESSION[ges_id],
            'fecha' => $fecha_pago,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $pago_id,
            'urb' => $urb,
            'interes' => $interes,
            'capital' => $cob->vcob_capital,
            'form' => $cob->vcob_form,
            'envio' => $cob->vcob_envio,
            'mora' => $cob->vcob_mora,
//            'detalles' => $detalles,
            'costo' => $res_cap->costo,
            'costo_producto'=>$res_cap->costo_producto,
            'prorat_lote' => $res_cap->prorat_lote, 
            'prorat_producto' => $res_cap->prorat_producto,
        );
//        if($urb->urb_tipo=='Interno'){
//            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
//        }elseif($urb->urb_tipo=='Externo'){
//            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
//        }
//
//        COMPROBANTES::registrar_comprobante($comprobante);


        $exito = $conec->commit();
        if ($exito) {
//            $vcuota->imprimir_pago($venta,$pago_id);
            $resp->ok = TRUE;
            $resp->datos = $pago_id;
            $resp->datos_cmp = $data;
            $resp->glosa = $glosa;
        } else {
            $exito = false;
            $resp->ok = FALSE;
            $mensajes = $conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $resp->mensaje = $mensaje;
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }

        return $resp;
    }

    function listar_debitos() {
        $this->formulario->dibujar_titulo("DEBITOS");
        ?>
        <div id="contenido_reporte" style="clear:both;">
        <?php
//            $this->cabecera_venta($venta);
        ?>
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>&acc=anular" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <input type="hidden" id="deb_id" name="deb_id" value="">
                <input type="hidden" id="observacion_anu" name="observacion_anu" value="">
            </form>
            <script>
                function anular_pago(id) {
        //                    var txt = 'Esta seguro de anular el Pago?';
                    var txt = 'Esta seguro de anular el Debito?' +
                            '<br />Observacion:<br /><textarea id="observacion_anu" name="observacion_anu" rows="2" cols="40"></textarea><br />'
                            ;
                    $.prompt(txt, {
                        buttons: {Aceptar: true, Cancelar: false},
                        callback: function(v, m, f) {
                            if (v) {
                                $('#deb_id').val(id);
                                $('#observacion_anu').val(f.observacion_anu);
                                document.frm_sentencia.submit();
                            }
                        }
                    });
                }

                popup = null;
                function ver_comprobante(id) {
                    if (popup !== null) {
                        popup.close();
                    }
                    var ruta = 'gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
                    window.open(ruta + id, 'Comprobante', 'width=900, height=500, scrollbars=yes');
                    popup = window.open(ruta + id, 'Comprobante', 'width=900, height=500, scrollbars=yes');
                    popup.document.close();
                }
                $(document).ready(function() {
                    $('.btn_ver_origen').click(function() {
                        var tabla = $(this).attr('data-tabla');
                        var tabla_id = $(this).attr('data-id');
                        if (tabla === 'Venta') {
                            window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id=' + tabla_id, 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                        }

                    });
                });

            </script>

            <br><br><center><h2>LISTADO DE DEBITOS</h2>
                <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th >Deb. Id</th>
                            <th >Fecha Debito</th>
							<th >Fecha Pago</th>
                            <th >Recibo</th>
                            <!--<th >Comision Anterior</th>-->
                            <th >Monto</th>
                            <!--<th >Comision Posterior</th>-->
                            <th >Usu. Debito</th>                        
                            <th class="tOpciones" width="70px">OP</th>                        
                        </tr>	
                    </thead>
                    <tbody>
        <?php
        $sql = "select * from debito
                        inner join venta_pago on(deb_vpag_id=vpag_id)
                        where deb_vdo_id=$_GET[id] 
                        and deb_estado='Activo' order by deb_id desc";
        $pagos = FUNCIONES::objetos_bd_sql($sql);
        $total_monto = 0;
        ?>
                        <?php
                        for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
                            $pag = $pagos->get_objeto();
                            $total_monto += $pag->deb_monto;
                            ?>
                            <tr>
                                <td><?php echo $pag->deb_id; ?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina(substr($pag->deb_fecha_cre, 0, 10)); ?></td>
								<td><?php echo FUNCIONES::get_fecha_latina($pag->deb_fecha_pago); ?></td>
                                <td><?php echo $pag->vpag_recibo; ?></td>
                                <!--<td><?php //echo number_format($pag->deb_comision_inicial, 2); ?></td>-->
                                <td><?php echo number_format($pag->deb_monto, 2); ?></td>
                                <!--<td><?php //echo number_format($pag->deb_comision_final, 2); ?></td>-->
                                <td><?php echo $pag->deb_usu_cre; ?></td>                                                                                                
                                <td>                                    
            <?php
//                                    if($this->usu->get_gru_id()=="Administradores" && $i == $pagos->get_num_registros()-1){ 
            if ($this->usu->get_gru_id() == "Administradores" && $i == 0) {
                ?>
                                        <a class="linkOpciones" title="ANULAR" onclick="anular_pago('<?php echo $pag->deb_id ?>');" href="javascript:void(0);">
                                            <img width="16" border="0" alt="ANULAR" src="images/anular.png">
                                        </a>
            <?php } ?>
                                </td>

                            </tr>
            <?php $pagos->siguiente(); ?>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">TOTALES</td>
                            <td><?php echo number_format($total_monto, 2); ?></td>                            
                            <td colspan="3">&nbsp;</td>
                        </tr>
                    </tfoot>
        <?php
        ?>
                    </tbody></table>
            </center>
            <br><br><br>
        </div>
        <?php
        $url_volver = $this->link . "?mod=" . $this->modulo . "&tarea=ACCEDER";
        ?>
        <div id="ContenedorDiv">
            <div id="CajaBotones">
                <input class="boton" type="button" value="Volver" onclick="javascript:location.href = '<?php echo $url_volver;?>';" />
            </div>
        </div>
        <script>
                popup = null;
                $('.det_pagos').click(function() {
                    var par = {};
                    par.tarea = 'cuota_pagos';
                    par.tipo = 'pago';
                    par.vpag_id = $(this).attr('data-id');
                    mostrar_ajax_load();
                    $.post('ajax.php', par, function(resp) {
                        ocultar_ajax_load();
                        var html = resp;
                        if (popup !== null) {
                            popup.close();
                        }
                        popup = window.open('about:blank', 'reportes', 'left=100,width=800,height=600,top=0,scrollbars=yes');
                        var extra = '';
                        popup.document.write(extra);
                        popup.document.write(html);
                        popup.document.close();
                    });
                });
                $('.det_venta').click(function() {
                    var ven_id = $(this).attr('data-id');
                    if (popup !== null) {
                        popup.close();
                    }
                    popup = window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id=' + ven_id, 'reportes', 'left=100,width=800,height=600,top=0,scrollbars=yes');
        //                    var extra='';
        //                    popup.document.write(extra);
        //                    popup.document.write(html);
                    popup.document.close();
                });
        </script>
        <?php
    }

    function anular_debito($deb_id) {
        echo "anulando debito $deb_id...";
//        return false;
        $resp = new stdClass();
        $debito = FUNCIONES::objeto_bd_sql("select * from debito where deb_id='$deb_id'");

        if ($debito) {

            include_once 'modulos/venta/venta.class.php';
            include_once 'modulos/venta/venta_cuotas.class.php';
            $vcuota = new VENTA_CUOTAS();
            $resp2 = $vcuota->anular_pago($debito->deb_vpag_id, 'debito');

            if (!$resp2->ok) {
                $resp->ok = FALSE;
                $resp->mensaje = $resp2->mensaje;
                return $resp;
            }

            $comisiones = json_decode($debito->deb_comision_pagos);
            foreach ($comisiones as $com) {
                $sql_upd = "update comision set com_estado='Pendiente',
                    com_pagado=(com_pagado-$com->monto) where com_id='$com->id'";
                FUNCIONES::bd_query($sql_upd);
            }

            $hoy = date('Y-m-d H:i:s');
            $sql_anu_deb = "update debito set deb_estado='Anulado',
            deb_usu_anu='$_SESSION[id]',deb_fecha_anu='$hoy'
            where deb_id='$deb_id'";

            FUNCIONES::bd_query($sql_anu_deb);
            $resp->mensaje = "D&eacute;bito anulado correctamente.";
            $resp->ok = TRUE;
            return $resp;
        }

        $resp->ok = FALSE;
        $resp->mensaje = "No existe el d&eacute;bito especificado.";
        return $resp;
    }

    function debito() {
        if ($_POST) {
//            echo "haciendo alguna accion...";

            if ($_GET[acc] == 'anular') {
                $debito = FUNCIONES::objeto_bd_sql("select * from debito where deb_id='$_POST[deb_id]'");
                $_GET[id] = $debito->deb_ven_id;
                $resp = $this->anular_debito($_POST[deb_id]);
                $link = $this->link . '?mod=' . $this->modulo . "&tarea=DEBITO&id=" . $debito->deb_vdo_id;
                $tipo = ($resp->ok) ? '' : 'Error';
                $mensaje = $resp->mensaje;
                $this->formulario->ventana_volver($mensaje, $link, '', $tipo);
            }
        } else {
//            echo "listando debitos...";
            $this->listar_debitos();
        }
    }

}
?>