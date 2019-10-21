<?php

class RESERVA_NEGOCIO extends RESERVA {

    function RESERVA_NEGOCIO() {
        parent::__construct();
    }

    function cambiar_lote() {
        $c_lotes = FUNCIONES::objetos_bd_sql("select * from venta_negocio where vneg_ven_id=$_GET[id] and vneg_estado='Pendiente'");
        if ($c_lotes->get_num_registros() > 0) {
            $this->formulario->ventana_volver('Existe un cambio pendite a ser Pagado', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        if ($_POST) {
            $this->guardar_cambio_lote($venta);
        } else {
            $this->frm_cambio_lote($venta);
        }
    }

    function guardar_cambio_lote($venta) {
        $conec = new ADO();
        $extra_pago_id = 2;
//        $tipo=  FUNCIONES::objeto_bd_sql("select * from extra_pago_tipo where ept_id=$extra_pago_id");//ing_cambio_ubi
        $_costo = $_POST[epag_costo]; //$tipo->ept_costo;
        $moneda = 2;
        $observacion = $_POST[observacion];
        ;
        unset($_POST[observacion]);
        unset($_POST[ticket]);
        $parmetros = json_encode($_POST);
        $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);

        $fecha_cre = date('Y-m-d H:i:s');
        $usu_cre = $_SESSION[id];
        $sql_insert = "insert into venta_negocio(
                            vneg_tipo,vneg_ven_id,vneg_ven_ori,vneg_observacion,vneg_fecha,
                            vneg_costo,vneg_moneda,vneg_parametros,vneg_estado,vneg_fecha_cre,vneg_usu_cre
                        )values(
                            'cambio_lote','$venta->ven_id','0','$observacion','$fecha',
                            '$_costo','$moneda','$parmetros','Pendiente','$fecha_cre','$usu_cre'
                        )";
        $conec->ejecutar($sql_insert, false);
        $llave = mysql_insert_id();

        $fecha_cre = date('Y-m-d');
        $concepto = "Venta Nro. $venta->ven_id de $venta->ven_concepto";

        $sql_ins_cobro = "insert into extra_pago(
                            epag_ept_id, epag_tabla, epag_tabla_id, epag_int_id, epag_urb_id, epag_modulo, epag_concepto,
                            epag_nota, epag_fecha_programada, epag_monto, epag_moneda, epag_monto_detalle, epag_fecha_cre,
                            epag_usu_cre, epag_estado
                        )values(
                            '$extra_pago_id','ven_neg','$llave','$venta->ven_int_id','$venta->ven_urb_id','venta-$venta->ven_id','$concepto',
                            '$observacion','$fecha','$_costo','$moneda','costo:$_costo','$fecha_cre',
                            '$_SESSION[id]','Pendiente'
                        )";
        //epag_recibo, epag_fecha_pago, epag_usu_pago, epag_fecha_mod
        $conec->ejecutar($sql_ins_cobro);

        $mensaje = "Cambio de Ubicacion guardada Correctamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);

//        FUNCIONES::print_pre($_POST);
    }

    function frm_cambio_lote($venta) {
        $this->formulario->dibujar_titulo("CAMBIO DE UBICACION");
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=CAMBIAR LOTE&id=<?php echo $venta->ven_id; ?>" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nro Venta:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Cliente:</div>
                            <div id="CajaInput">
                                <input type="hidden" name="lote_id_ant" id="lote_id_ant" value="<?php echo $venta->ven_lot_id; ?>">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Concepto:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Moneda:</div>
                            <div id="CajaInput">
                                <input type="hidden" name="ven_moneda" id="ven_moneda" value="<?php echo $venta->ven_moneda; ?>">
                                <div class="read-input"><?php echo $venta->ven_moneda == '2' ? 'Dolares' : 'Bolivianos'; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Superficie:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_superficie; ?></div>
                            </div>
                            <div class="Etiqueta">Metro:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_metro; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Valor Terreno:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_valor; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Descuento:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_decuento; ?></div>
                            </div>
                            <div class="Etiqueta">Incremento:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_incremento; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Monto a Pagar:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Intercambio:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_intercambio; ?></div>
                            </div>
                            <?php
                            $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
                            $sum_inter_pag = 0;
                            foreach ($amontos_pag as $ipag) {
                                $sum_inter_pag+=$ipag->monto;
                            }
                            ?>
                            <div class="Etiqueta">Intercambio Consumido:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $sum_inter_pag; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Anticipo:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_res_anticipo; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Saldo Efectivo:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_efectivo; ?></div>
                            </div>
                        </div>
        <?php $pagado = $this->total_pagado($venta->ven_id); ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Capital Pagado:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->capital * 1; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Desc. Plan:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->descuento * 1; ?></div>
                            </div>
                            <div class="Etiqueta">Inc. Plan:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->incremento * 1; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Total Pagado</b></div>
                            <div id="CajaInput">
        <?php
        $tot_pagado = $pagado->capital + $venta->ven_res_anticipo + $sum_inter_pag + $venta->ven_venta_pagado;
        ?>
                                <input type="hidden" name="ven_pagado" id="ven_pagado" value="<?php echo $tot_pagado; ?>">
                                <div class="read-input" id="txt_ven_pagado"><?php echo $tot_pagado; ?></div>
                            </div>
                            <div class="Etiqueta" ><b>Ultima Fecha Valor</b></div>
                            <div id="CajaInput">
                                <?php
                                $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
                                $fecha_valor = $venta->ven_fecha;
                                if ($upago) {
                                    $fecha_valor = $upago->vpag_fecha_valor;
                                }
                                ?>
                                <input type="hidden" name="u_fecha_valor" id="u_fecha_valor" value="<?php echo $txt_fecha_val = FUNCIONES::get_fecha_latina($fecha_valor); ?>">
                                <div class="read-input" id="txt_u_fecha_valor"><?php echo $txt_fecha_val; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos Nuevo Terreno</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                            <div id="CajaInput">
                                <!--<input type="text" name="fecha" id="fecha" value="<?php echo date('d/m/Y'); ?>">-->
        <?php FORMULARIO::cmp_fecha('fecha'); ?>
                                <div class="read-input" id="txt_fecha" hidden=""></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Nuevo Terreno</div>
                            <div id="CajaInput">
                                <input type="hidden" id="new_urb_id" name="new_urb_id">
                                <input type="hidden" id="new_uv_id" name="new_uv_id">
                                <input type="hidden" id="new_man_id" name="new_man_id">
                                <input type="hidden" id="new_lot_id" name="new_lot_id">
                                <input type="hidden" id="new_lot_sup" name="new_lot_sup">
                                <input type="hidden" id="new_zon_precio" name="new_zon_precio">
                                <input type="hidden" id="new_zon_moneda" name="new_zon_moneda">
                                <div class="read-input" id="txt_terreno">&nbsp;</div><img id="img-buscar"style="margin: 4px 0 0 3px; cursor: pointer;"src="images/b_search.png">
                                &nbsp;&nbsp;&nbsp;<input id="btn_pagos" hidden="" class="boton" type="button" onclick="habilitar_pagos();" value="Pagos">
                                &nbsp;&nbsp;&nbsp;<input id="btn_editar" hidden="" class="boton" type="button" onclick="habilitar_edicion();" value="Editar">
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion" class="box-new-pagos" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >superficie</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_superficie"></div>
                            </div>
                            <div class="Etiqueta" >Metro</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_metro" id="ven_metro" value="" readonly="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Valor</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_valor" id="ven_valor" value="" readonly="">
                            </div>
                            <div class="Etiqueta" >Total Pagado</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ven_pagado"><?php echo $tot_pagado; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv" >
                            <div class="Etiqueta" >Descuento</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_descuento" id="ven_descuento" value="">
                            </div>
                            <div hidden="">
                                <div class="Etiqueta" hidden="" >Incremento</div>
                                <div id="CajaInput" hidden="" >
                                    <input type="text" name="ven_incremento" id="ven_incremento" value="">
                                </div>
                            </div>
                        </div>

                        <?php
                        $a_saldo_inter = FUNCIONES::saldo_intercambio($venta->ven_id);
                        $n_interes_montos = array_values($a_saldo_inter);
                        $saldo_intercambio = array_sum($a_saldo_inter);
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo Intercambio</div>
                            <div id="CajaInput">
                                <input type="hidden" name="saldo_intercambio" id="saldo_intercambio" value="<?php echo $saldo_intercambio; ?>" readonly="">
                                <div class="read-input" id="txt_ven_pagado"><?php echo $saldo_intercambio; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo Efectivo</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_monto_efectivo" id="ven_monto_efectivo" value="" readonly="">
                            </div>
                        </div>
                        <?php
                        $extra_pago_id = 2;
                        $tipo = FUNCIONES::objeto_bd_sql("select * from extra_pago_tipo where ept_id=$extra_pago_id"); //ing_cambio_ubi
                        $_costo = $tipo->ept_costo;
                        ?>
                        <div id="ContenedorDiv" >
                            <div class="Etiqueta" >Costo</div>
                            <div id="CajaInput">
                                <input type="text" name="epag_costo" id="epag_costo" value="<?php echo $_costo; ?>">
                            </div>
                        </div>
                        <div id="box-reformular" style="">
                            <div style="text-align: left; border-bottom: 1px solid #3a3a3a; color: #3a3a3a; font-weight: bold;margin-bottom: 10px;">
                                Adecuar Saldo 
                            </div>
                            <div id="ContenedorDiv">
                                <input type="hidden" name="ven_id" id="ven_id" value="<?php echo $venta->ven_id; ?>">
                                <input type="hidden" name="interes_anual" id="interes_anual" value="<?php echo $venta->ven_val_interes; ?>">
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px;">Definir Plan de Pagos por: &nbsp;</span>
                                    <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo" style="width: 100px;">
                                        <option value="mp">Meses Plazo</option>
                                        <option value="cm">Cuota Mensual</option>

                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <select  id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                        <option value="dcuota">Monto Cuota</option>
                                        <option value="dcapital">Monto Capital</option>
                                    </select>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" style="margin-right: 5px;">
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                    <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" autocomplete="off">
                                </div>
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                    <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="" autocomplete="off">
                                </div>
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha de la Primer Cuota: </span>
                                    <input class="caja_texto" name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo FUNCIONES::get_fecha_latina($scuota->ind_fecha_programada); ?>" type="text">
                                    <script>
                                        $("#fecha_pri_cuota").mask("99/99/9999");
                                    </script>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                    <select id="ven_rango" name="ven_rango">
                                        <option value="1" <?php echo $venta->ven_rango == '1' ? 'selected=""' : ''; ?>>Mensual</option>
                                        <option value="2" <?php echo $venta->ven_rango == '2' ? 'selected=""' : ''; ?>>Bimestral</option>
                                        <option value="3" <?php echo $venta->ven_rango == '3' ? 'selected=""' : ''; ?>>Trimestral</option>
                                        <option value="4" <?php echo $venta->ven_rango == '4' ? 'selected=""' : ''; ?>>Cuatrimestral</option>
                                        <option value="6" <?php echo $venta->ven_rango == '6' ? 'selected=""' : ''; ?>>Semestral</option>
                                    </select>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                    <select id="ven_frecuencia" name="ven_frecuencia">
                                        <option value="30_dias" <?php echo $venta->ven_frecuencia == '30_dias' ? 'selected=""' : ''; ?>>Cada 30 dias</option>
                                        <option value="dia_mes" <?php echo $venta->ven_frecuencia == 'dia_mes' ? 'selected=""' : ''; ?>>Mantener el dia</option>
                                    </select>
                                </div>
                                <div id="CajaInput">
                                    <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                                </div>
                                <div id="CajaInput">
                                    <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                                </div>
                            </div>
                            <div style="clear: both"></div>
                            <div class="ContenedorDiv" id="plan_manual_efectivo">
                                <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nro. Cuota</th>
                                            <th>Fecha de Pago</th>                                                            
                                            <th>Mondeda</th>
                                            <th>Interes</th>
                                            <th>Capital</th>
                                            <th>Monto a Pagar</th>
                                            <th>Saldo</th>
                                            <th></th>
                                        </tr>							
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>
                                                <input type="hidden" id="c_total_efectivo" value="0">
                                                <input type="hidden" id="pag_total_efectivo" value="0">
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                                <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                                    $('#fecha').mask('99/99/9999');
                                    mask_decimal('#ven_metro', null);
                                    mask_decimal('#ven_descuento', null);
                                    mask_decimal('#ven_incremento', null);
                                    mask_decimal('#interes_anual', null);
                                    mask_decimal('#cuota_mensual', null);
                                    mask_decimal('#meses_plazo', null);
                                    var popup = null;
                                    $('#img-buscar').click(function() {

                                        var par = {};
                                        par.tarea = 'buscar_lote';
                                        par.lote_id = $('#lote_id_ant').val();

                                        $.post('ajax.php', par, function(resp) {
                                            var html = resp;
                                            if (popup !== null) {
                                                popup.close();
                                            }
                                            popup = window.open('about:blank', 'reportes', 'left=100,width=800,height=400,top=0,scrollbars=yes');
                                            var extra = '';
                                            popup.document.write(extra);
                                            popup.document.write(html);
                                            popup.document.close();
                                        });
                                    });

                                    function cargar_lote_cambio(data) {
                                        console.log(data);
                                        $('#new_urb_id').val(data.urb_id);
                                        $('#new_uv_id').val(data.uv_id);
                                        $('#new_man_id').val(data.man_id);
                                        $('#new_lot_id').val(data.lot_id);
                                        $('#new_lot_sup').val(data.lot_sup);
                                        $('#new_zon_precio').val(data.zon_precio);
                                        $('#new_zon_moneda').val(data.zon_moneda);
                                        $('#txt_terreno').text(data.lot_descripcion);

                                        $('#btn_pagos').show();
                                        $('#btn_editar').hide();
                                    }
                                    pagos = false;
                                    function habilitar_edicion() {
                                        if (!$('#txt_cmp_fecha').length) {
                                            $('#fecha').show();
                                            $('#txt_fecha').hide();
                                        }
                                        $('#btn_pagos').show();
                                        $('#btn_editar').hide();
                                        $('#img-buscar').show();
                                        $('.box-new-pagos').hide();
                                        pagos = false;

                                    }

                                    function habilitar_pagos() {
                                        if ($('#new_lot_id').val() === '') {
                                            $.prompt("Seleccion el Nuevo Lote");
                                            return false;
                                        }
                                        var fecha = $('#fecha').val();
                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                            var dato = JSON.parse(respuesta);
                                            if (dato.response === "error") {
                                                $.prompt(dato.mensaje);
                                                return false;
                                            }
                                            if (!$('#txt_cmp_fecha').length) {
                                                $('#txt_fecha').text($('#fecha').val());
                                                $('#fecha').hide();
                                                $('#txt_fecha').show();
                                            }

                                            habilitar_montos();

                                            $('#btn_pagos').hide();
                                            $('#btn_editar').show();
                                            $('#img-buscar').hide();
                                            $('.box-new-pagos').show();
                                            var _fecha = fecha_mysql(fecha);
                                            var nfecha = siguiente_mes(_fecha);
                                            $('#fecha_pri_cuota').val(fecha_latina(nfecha));
                                            pagos = true;

                                        });

                                    }

                                    function habilitar_montos() {
                                        var lot_sup = $('#new_lot_sup').val();
                                        var zon_precio = $('#new_zon_precio').val();
                                        $('#txt_superficie').text(lot_sup);
                                        $('#ven_metro').val(zon_precio);
                                        calcular_valor();


                                    }
                                    function calcular_valor() {
                                        var lot_sup = $('#new_lot_sup').val() * 1;
                                        var ven_metro = $('#ven_metro').val() * 1;
                                        var ven_valor = (lot_sup * ven_metro).toFixed(2);
                                        $('#ven_valor').val(ven_valor);
                                        calcular_monto();
                                    }
                                    function calcular_monto() {
                                        var valor = $('#ven_valor').val() * 1;
                                        var desc = $('#ven_descuento').val() * 1;
                                        var inc = $('#ven_incremento').val() * 1;
                                        var ven_pagado = $('#ven_pagado').val() * 1;
                                        var saldo_intercambio = $('#saldo_intercambio').val() * 1;
                                        var monto = valor + inc - desc - ven_pagado - saldo_intercambio;
                                        $('#ven_monto_efectivo').val(monto.toFixed(2));
                                        if (monto > 0) {
                                            $('#box-reformular').show();
                                        } else {
                                            $('#box-reformular').hide();
                                        }
                                    }
                                    $('#ven_metro').keyup(function() {
                                        calcular_valor();
                                    });
                                    $('#ven_descuento, #ven_incremento').keyup(function() {
                                        calcular_monto();
                                    });


                                    $('#fecha_pri_cuota').mask('99/99/9999');
                                    $('#def_plan_efectivo').change(function() {
                                        var def = $(this).val();
                                        if (def === 'mp') {
                                            $('#meses_plazo').parent().show();
                                            $('#cuota_mensual').parent().hide();
                                            $('#cuota_interes').parent().hide();
                                            $('#ver_plan_efectivo').show();
                                            $('#add_cuota_efectivo').hide();
                                            $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
                                            $('#plan_manual_efectivo').hide();
                                            $('#def_cuota').parent().hide();
                                        } else if (def === 'cm') {
                                            $('#meses_plazo').parent().show();
                                            $('#cuota_mensual').parent().show();
                                            $('#cuota_interes').parent().hide();
                                            $('#ver_plan_efectivo').show();
                                            $('#add_cuota_efectivo').hide();
                                            $('#meses_plazo').prev('span').text('Maximo Plazo: ');
                                            $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                                            $('#cuota_mensual').prev('span').show();
                                            $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
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
        //                        var cu_pagadas=$('#nro_cuota_sig').val()*1-1;
                                            ncuotas = ($('#meses_plazo').val() * 1);
                                            monto_cuota = $('#cuota_mensual').val();
                                        }
                                        saldo_financiar = $('#ven_monto_efectivo').val() * 1;
                                        fecha_pri_cuota = $('#fecha_pri_cuota').val();
                                        var fecha_inicio = $('#fecha').val();
                                        var fp_mysql = fecha_mysql(fecha_pri_cuota);
                                        var fi_mysql = fecha_mysql(fecha_inicio);
                                        if (fi_mysql > fp_mysql) {
                                            $.prompt('La fecha de la Primera cuota no puede ser menor a la Ultima fecha Valor');
                                            return;
                                        }

                                        var rango = $('#ven_rango option:selected').val();
                                        var frec = $('#ven_frecuencia option:selected').val();

                                        var interes = $('#interes_anual').val();

                                        if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && saldo_financiar > 0 && fecha_pri_cuota !== '') {
                                            var moneda = $('#ven_moneda').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
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
                                            par.rango = rango;
                                            par.frecuencia = frec;
                                            par.ven_id = $('#ven_id').val();
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


                                    $('#btn_guardar').click(function() {
                                        var lote_id = $('#new_lot_id').val() * 1;
                                        if (lote_id <= 0) {
                                            $.prompt("Seleccione el lote");
                                            return false;
                                        }
                                        if (!pagos) {
                                            $.prompt("Habilite los Pagos");
                                            return false;
                                        }
                                        var saldo_efectivo = $('#ven_monto_efectivo').val() * 1;
                                        if (saldo_efectivo > 0) {
                                            var def = $('#def_plan_efectivo option:selected').val();
                                            if (def === 'mp') {
                                                var mp = $('#meses_plazo').val() * 1;
                                                var fpc = $('#fecha_pri_cuota').val();
                                                if (!(mp > 0 && fpc !== '')) {
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La meses plazo <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }
                                            } else if (def === 'cm') {
                                                var cm = $('#cuota_mensual').val() * 1;
                                                var fpc = $('#fecha_pri_cuota').val();
                                                if (!(cm > 0 && fpc !== '')) {
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La cuota Mensual <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }
                                            } else if (def === 'manual') {
                                                var capital_total = $('#c_total_efectivo').val() * 1;
                                                var saldo = $('#saldo_final').val();
                                                if (capital_total !== saldo) {
                                                    $.prompt('en el plan de pagos manual del monto en efectivo falta definir mas cuotas para igualar al monto en efectivo de la venta');
                                                    return false;
                                                }
                                            }
                                        }

                                        var fecha = $('#fecha').val();
                                        var fecha_valor = $('#u_fecha_valor').val();
                                        if (fecha !== fecha_valor) {
                                            $.prompt('La fecha Valor no es igual a la fecha de la transaccion');
                                            return;
                                        }
        //                    
                                        revisar_ufecha_prog(function() {
                                            var fecha = $('#fecha').val();
                                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                ocultar_ajax_load();
                                                var dato = JSON.parse(respuesta);
                                                if (dato.response === "ok") {
        //                                 console.info('OK');
                                                    document.frm_sentencia.submit();
                                                } else if (dato.response === "error") {
                                                    $.prompt(dato.mensaje);
                                                    return false;
                                                }
                                            });
                                        });

        //                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
        //                        var dato = JSON.parse(respuesta);
        //                        if (dato.response === "ok") {
        //                            document.frm_sentencia.submit();
        //                        } else if (dato.response === "error") {
        //                            $.prompt(dato.mensaje);
        //                            return false;
        //                        }
        //                    });
                                    });

                                    function revisar_ufecha_prog(funcion) {
                                        var ncuotas = 0;
                                        var monto_cuota = 0;
                                        var def = $('#def_plan_efectivo option:selected').val();
                                        if (def === 'mp') {
                                            ncuotas = $('#meses_plazo').val();
                                            monto_cuota = '';
                                        } else if (def === 'cm') {
                                            ncuotas = $('#meses_plazo').val();
                                            monto_cuota = $('#cuota_mensual').val();
                                        }
                                        var saldo_financiar = $('#saldo_final').val() * 1;

                                        var rango = $('#ven_rango option:selected').val();
                                        var frec = $('#ven_frecuencia option:selected').val();

                                        var interes = $('#interes_anual').val();
                                        var fecha_pri_cuota = $('#fecha_pri_cuota').val();
                                        var fecha_inicio = $('#fecha_inicio').val();

                                        var moneda = $('#ven_moneda').val();
                                        var par = {};
                                        par.tarea = 'rev_ufecha_prog';
                                        par.saldo_financiar = saldo_financiar;
                                        par.monto_total = saldo_financiar;
                                        par.meses_plazo = ncuotas;
                                        par.cuota_mensual = monto_cuota;
                                        par.ven_moneda = moneda;
                                        par.nro_inicio = $('#nro_cuota_sig').val();
                                        par.fecha_inicio = fecha_inicio;
                                        par.fecha_pri_cuota = fecha_pri_cuota;
                                        par.interes = interes;
                                        par.rango = rango;
                                        par.frecuencia = frec;
                                        par.ven_id = $('#ven_id').val();
                                        mostrar_ajax_load();
                                        $.post('ajax.php', par, function(resp) {
                                            var r = JSON.parse(trim(resp));
                                            if (r.type === 'success') {
                                                funcion();
                                            } else {
                                                ocultar_ajax_load();
                                                $.prompt(r.msj);
                                            }

                                        });

                                    }
            </script>
        </form>
        <?php
    }

    function fusion_ventas() {
        $c_lotes = FUNCIONES::objetos_bd_sql("select * from venta_negocio where vneg_ven_id=$_GET[id] and vneg_estado='Pendiente'");
        if ($c_lotes->get_num_registros() > 0) {
            $this->formulario->ventana_volver('Existe una Fusion Pendiente pendite a ser Pagado', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        if ($_POST) {
            $this->guardar_fusion_ventas($venta);
        } else {
            $this->frm_fusion_ventas($venta);
        }
    }

    function guardar_fusion_ventas($venta) {
        include_once 'clases/eventos.class.php';
//        ini_set('display_errors', 'On');
        $conec = new ADO();
        $extra_pago_id = 3; // fusion de ventas
        $tipo = FUNCIONES::objeto_bd_sql("select * from extra_pago_tipo where ept_id=$extra_pago_id"); //ing_cambio_ubi
        $_costo = $tipo->ept_costo;
        $moneda = 2;
        $observacion = $_POST[observacion];
        ;
        unset($_POST[observacion]);
        unset($_POST[ticket]);
        $parmetros = json_encode($_POST);
        $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $fecha_cre = date('Y-m-d H:i:s');
        $usu_cre = $_SESSION[id];
        $sql_insert = "insert into venta_negocio(
                            vneg_tipo,vneg_ven_id,vneg_ven_ori,vneg_observacion,vneg_fecha,
                            vneg_costo,vneg_moneda,vneg_parametros,vneg_estado,vneg_fecha_cre,vneg_usu_cre
                        )values(
                            'fusion','$venta->ven_id','$_POST[ori_ven_id]','$observacion','$fecha',
                            '$_costo','$moneda','$parmetros','Pendiente','$fecha_cre','$usu_cre'
                        )";
        $conec->ejecutar($sql_insert, true, true);
        $llave = ADO::$insert_id;

        $fecha_cre = date('Y-m-d');
        $concepto = "Venta Nro. $venta->ven_id de $venta->ven_concepto";

        $sql_ins_cobro = "insert into extra_pago(
                            epag_ept_id, epag_tabla, epag_tabla_id, epag_int_id, epag_urb_id, epag_modulo, epag_concepto,
                            epag_nota, epag_fecha_programada, epag_monto, epag_moneda, epag_monto_detalle, epag_fecha_cre,
                            epag_usu_cre, epag_estado
                        )values(
                            '$extra_pago_id','ven_neg','$llave','$venta->ven_int_id','$venta->ven_urb_id','venta-$venta->ven_id','$concepto',
                            '$observacion','$fecha','$_costo','$moneda','costo:$_costo','$fecha_cre',
                            '$_SESSION[id]','Pendiente'
                        )";

//        $conec->ejecutar($sql_ins_cobro);


        $vneg = FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_id=$llave");
        $data = array(
            'objeto' => $vneg,
            'fecha' => $fecha,
        );

        EVENTOS::fusion_venta($data, 'P');



        $mensaje = "Fusion de Ventas guardada Correctamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);

//        FUNCIONES::print_pre($_POST);
    }

    function frm_fusion_ventas($venta) {
        $this->formulario->dibujar_titulo("FUSION DE VENTAS");
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=FUSION&id=<?php echo $venta->ven_id; ?>" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nro Venta:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Cliente:</div>
                            <div id="CajaInput">

                                <input type="hidden" name="interno_id" id="interno_id" value="<?php echo $venta->ven_int_id; ?>">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Concepto:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Moneda:</div>
                            <div id="CajaInput">
                                <input type="hidden" name="ven_moneda" id="ven_moneda" value="<?php echo $venta->ven_moneda; ?>">
                                <div class="read-input"><?php echo $venta->ven_moneda == '2' ? 'Dolares' : 'Bolivianos'; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Superficie:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_superficie; ?></div>
                            </div>
                            <div class="Etiqueta">Metro:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_metro; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Valor Terreno:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_valor; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Descuento:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_decuento; ?></div>
                            </div>
                            <div class="Etiqueta">Incremento:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_incremento; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Monto a Pagar:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Anticipo:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_res_anticipo; ?></div>
                            </div>
                            <div class="Etiqueta">Intercambio:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_intercambio; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Saldo Efectivo:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_efectivo; ?></div>
                            </div>
                        </div>
        <?php $pagado = $this->total_pagado($venta->ven_id); ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Capital Pagado:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->capital * 1; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Desc. Plan:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->descuento * 1; ?></div>
                            </div>
                            <div class="Etiqueta">Inc. Plan:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->incremento * 1; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Saldo</b></div>
                            <div id="CajaInput">
                                <input type="hidden" name="saldo_inicial" id="saldo_inicial" value="<?php echo $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento; //$venta->ven_res_anticipo+$venta->ven_monto_intercambio;?>">
                                <div class="read-input" id="txt_saldo_inicial"><?php echo $saldo; ?></div>
                            </div>
                            <div class="Etiqueta" ><b>Ultima Fecha Valor</b></div>
                            <div id="CajaInput">
                                <?php
                                $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
                                $fecha_valor = $venta->ven_fecha;
                                if ($upago) {
                                    $fecha_valor = $upago->vpag_fecha_valor;
                                }
                                ?>
                                <input type="hidden" name="u_fecha_valor" id="u_fecha_valor" value="<?php echo $txt_fecha_val = FUNCIONES::get_fecha_latina($fecha_valor); ?>">
                                <div class="read-input" id="txt_u_fecha_valor"><?php echo $txt_fecha_val; ?></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la venta Anterior</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                            <div id="CajaInput">
                                <input type="text" name="fecha" id="fecha" value="<?php echo date('d/m/Y'); ?>">
                                <div class="read-input" id="txt_fecha" hidden=""></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Venta</div>
                            <div id="CajaInput">
                                <input type="hidden" id="ori_capital" name="ori_capital">
                                <input type="hidden" id="ori_descuento" name="ori_descuento">
                                <input type="hidden" id="ori_incremento" name="ori_incremento">
                                <input type="hidden" id="ori_ven_id" name="ori_ven_id">
                                <input type="hidden" id="ori_moneda" name="ori_moneda">
                                <input type="hidden" id="ori_fecha_valor" name="ori_fecha_valor">
                                <input type="hidden" id="ori_intercambio" name="ori_intercambio">
                                <input type="hidden" id="ori_tot_capital" name="ori_tot_capital">
                                <input type="hidden" id="ori_comision" name="ori_comision">

                                <div class="read-input" id="txt_ori_venta">&nbsp;</div><img id="img-buscar"style="margin: 4px 0 0 3px; cursor: pointer;"src="images/b_search.png">
                                &nbsp;&nbsp;&nbsp;<input id="btn_pagos" hidden="" class="boton" type="button" onclick="habilitar_pagos();" value="Pagos">
                                &nbsp;&nbsp;&nbsp;<input id="btn_editar" hidden="" class="boton" type="button" onclick="habilitar_edicion();" value="Editar">
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion" class="box-new-pagos" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Efectivo Pagado</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ori_capital"></div>
                            </div>
                            <div class="Etiqueta" >Intercambio Consumido</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ori_intercambio"></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Tot. Capital Pagado</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ori_tot_capital"></div>
                            </div>
                            <div class="Etiqueta" >Ingreso por Gastos</div>
                            <div id="CajaInput">
                                <input type="text" name="ret_ingreso" id="ret_ingreso" value="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Capital a Amortizar</div>
                            <div id="CajaInput">
                                <input type="hidden" id="tot_capital" name="tot_capital">
                                <div class="read-input" id="txt_tot_capital">&nbsp;</div>
                            </div>

                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Desc. Venta</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ori_descuento"></div>
                            </div>
                            <div class="Etiqueta" >Inc. Venta</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_ori_incremento"></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" >Descuento</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_descuento" id="ven_descuento" value="">
                            </div>
                            <div class="Etiqueta" >Incremento</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_incremento" id="ven_incremento" value="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo Efectivo</div>
                            <div id="CajaInput">
                                <input type="text" name="ven_monto_efectivo" id="ven_monto_efectivo" value="" readonly="">
                            </div>
                        </div>
                        <div id="box-reformular" style="display: none;">
                            <div style="text-align: left; border-bottom: 1px solid #3a3a3a; color: #3a3a3a; font-weight: bold;margin-bottom: 10px;">
                                Adecuar Saldo 
                            </div>
                            <div id="ContenedorDiv">
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px;">Interes: &nbsp;</span>
                                    <input type="text" name="interes_anual" id="interes_anual" value="<?php echo $venta->ven_val_interes * 1; ?>" size="5">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px;">Definir Plan de Pagos por: &nbsp;</span>
                                    <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo">
                                        <option value="mp">Meses Plazo</option>
                                        <option value="cm">Cuota Mensual</option>

                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv"  >
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                    <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="" onKeyPress="return ValidarNumero(event);">
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <select  id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                        <option value="dcuota">Monto Cuota</option>
                                        <option value="dcapital">Monto Capital</option>
                                    </select>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                    <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" onKeyPress="return ValidarNumero(event);">
                                </div>
                                <div id="CajaInput">
                                    <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha de la Primer Cuota: </span>
                                    <input class="caja_texto" name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo FUNCIONES::get_fecha_latina($scuota->ind_fecha_programada); ?>" type="text">
                                    <script>
                                    $("#fecha_pri_cuota").mask("99/99/9999");
                                    </script>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                    <select id="ven_rango" name="ven_rango">
                                        <option value="1" <?php echo $venta->ven_rango == '1' ? 'selected=""' : ''; ?>>Mensual</option>
                                        <option value="2" <?php echo $venta->ven_rango == '2' ? 'selected=""' : ''; ?>>Bimestral</option>
                                        <option value="3" <?php echo $venta->ven_rango == '3' ? 'selected=""' : ''; ?>>Trimestral</option>
                                        <option value="4" <?php echo $venta->ven_rango == '4' ? 'selected=""' : ''; ?>>Cuatrimestral</option>
                                        <option value="6" <?php echo $venta->ven_rango == '6' ? 'selected=""' : ''; ?>>Semestral</option>
                                    </select>
                                </div>
                                <div id="CajaInput" name="divCuotaMensual" >
                                    <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                    <select id="ven_frecuencia" name="ven_frecuencia">
                                        <option value="30_dias" <?php echo $venta->ven_frecuencia == '30_dias' ? 'selected=""' : ''; ?>>Cada 30 dias</option>
                                        <option value="dia_mes" <?php echo $venta->ven_frecuencia == 'dia_mes' ? 'selected=""' : ''; ?>>Mantener el dia</option>
                                    </select>
                                </div>
                                <div id="CajaInput">
                                    <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                                </div>
                                <div id="CajaInput">
                                    <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                                </div>
                            </div>
                            <div style="clear: both"></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                                <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                                    $('#fecha').mask('99/99/9999');
                                    mask_decimal('#ven_metro', null);
                                    mask_decimal('#ven_descuento', null);
                                    mask_decimal('#ven_incremento', null);
                                    mask_decimal('#interes_anual', null);
                                    mask_decimal('#ret_ingreso', null);
                                    var popup = null;
                                    $('#img-buscar').click(function() {

                                        var par = {};
                                        par.tarea = 'buscar_venta';
                                        par.int_id = $('#interno_id').val();
                                        par.ven_id = $('#ven_id').val();

                                        $.post('ajax.php', par, function(resp) {
                                            var html = resp;
                                            if (popup !== null) {
                                                popup.close();
                                            }
                                            popup = window.open('about:blank', 'reportes', 'left=100,width=900,height=400,top=0,scrollbars=yes');
                                            var extra = '';
                                            popup.document.write(extra);
                                            popup.document.write(html);
                                            popup.document.close();
                                        });
                                    });

        //                <input type="hidden" id="ori_capital" name="ori_capital">
        //                <input type="hidden" id="ori_descuento" name="ori_descuento">
        //                <input type="hidden" id="ori_incremento" name="ori_incremento">
        //                <input type="hidden" id="ori_ven_id" name="ori_ven_id">
        //                <input type="hidden" id="ori_moneda" name="ori_moneda">

                                    function cargar_venta_fusion(data) {
                                        console.log(data);
                                        $('#ori_capital').val(data.capital);
                                        $('#ori_descuento').val(data.descuento);
                                        $('#ori_incremento').val(data.incremento);
                                        $('#ori_ven_id').val(data.ven_id);
                                        $('#ori_moneda').val(data.moneda1);
                                        $('#ori_fecha_valor').val(data.fecha_valor);
                                        $('#ori_intercambio').val(data.intercambio);
                                        $('#ori_tot_capital').val(data.intercambio * 1 + data.capital * 1);
                                        $('#ori_comision').val(data.comision * 1);

                                        $('#txt_ori_venta').text('Nro. Venta: ' + data.ven_id + ', ' + data.concepto + ', Fech. Valor ' + data.fecha_valor);

                                        $('#btn_pagos').show();
                                        $('#btn_editar').hide();
                                    }
                                    pagos = false;
                                    function habilitar_edicion() {
                                        if (!$('#txt_cmp_fecha').length) {
                                            $('#fecha').show();
                                            $('#txt_fecha').hide();
                                        }

                                        $('#btn_pagos').show();
                                        $('#btn_editar').hide();
                                        $('#img-buscar').show();
                                        $('.box-new-pagos').hide();
                                        console.log('edicion');
                                        pagos = false;

                                    }

                                    function habilitar_pagos() {
                                        if ($('#ori_ven_id').val() === '') {
                                            $.prompt("Seleccion La venta Origen");
                                            return false;
                                        }
                                        var fecha = $('#fecha').val();
                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                            var dato = JSON.parse(respuesta);
                                            if (dato.response === "error") {
                                                $.prompt(dato.mensaje);
                                                return false;
                                            }
                                            if (!$('#txt_cmp_fecha').length) {
                                                $('#txt_fecha').text($('#fecha').val());
                                                $('#fecha').hide();
                                                $('#txt_fecha').show();
                                            }
                                            console.log('pagos');

                                            habilitar_montos();

                                            $('#btn_pagos').hide();
                                            $('#btn_editar').show();
                                            $('#img-buscar').hide();
                                            $('.box-new-pagos').show();
        //                        var _fecha=fecha_mysql(fecha);
                                            var _fecha = fecha_mysql($('#u_fecha_valor').val());
                                            var nfecha = siguiente_mes(_fecha);
                                            $('#fecha_pri_cuota').val(fecha_latina(nfecha));
                                            pagos = true;

                                        });

                                    }

                                    function habilitar_montos() {
                                        var capital_pag = $('#ori_capital').val() * 1;
                                        var intercambio = $('#ori_intercambio').val() * 1;
                                        var tot_capital = $('#ori_tot_capital').val() * 1;
                                        var descuento = $('#ori_descuento').val() * 1;
                                        var incremento = $('#ori_incremento').val() * 1;
                                        var comision = $('#ori_comision').val() * 1;
                                        $('#txt_ori_capital').text(capital_pag);
                                        $('#txt_ori_intercambio').text(intercambio);
                                        $('#txt_ori_tot_capital').text(tot_capital);
                                        $('#txt_ori_descuento').text(descuento);
                                        $('#txt_ori_incremento').text(incremento);
                                        $('#ret_ingreso').val(comision);
        //                    $('#ret_ingreso').val('0');
        //                    $('#ven_metro').val(zon_precio);
                                        calcular_capital();



                                    }
                                    function calcular_capital() {
                                        var capital_pag = $('#ori_tot_capital').val() * 1;
                                        var ret_ing = $('#ret_ingreso').val() * 1;

                                        var capital = capital_pag - ret_ing;
                                        $('#tot_capital').val(capital.toFixed(2));
                                        $('#txt_tot_capital').text(capital.toFixed(2));

                                        calcular_saldo();
                                    }

                                    function calcular_saldo() {
                                        var saldo_inicial = $('#saldo_inicial').val() * 1;
                                        var capital = $('#ori_tot_capital').val() * 1;
                                        var desc = $('#ven_descuento').val() * 1;
                                        var ret_ing = $('#ret_ingreso').val() * 1;
                                        var inc = $('#ven_incremento').val() * 1;

                                        var saldo = saldo_inicial + inc - desc - capital + ret_ing;
                                        $('#ven_monto_efectivo').val(saldo.toFixed(2));
                                        if (saldo > 0) {
        //                        $('#box-reformular').show();
                                        } else {
        //                        $('#box-reformular').hide();   
                                        }
                                    }

                                    $('#ret_ingreso').keyup(function() {
                                        calcular_capital();
                                    });
                                    $('#ven_descuento, #ven_incremento').keyup(function() {
                                        calcular_saldo();
                                    });
                                    $('#fecha_pri_cuota').mask('99/99/9999');
                                    $('#def_plan_efectivo').change(function() {
                                        var def = $(this).val();
                                        if (def === 'mp') {
                                            $('#meses_plazo').parent().show();
                                            $('#cuota_mensual').parent().hide();
                                            $('#cuota_interes').parent().hide();
                                            $('#ver_plan_efectivo').show();
                                            $('#add_cuota_efectivo').hide();
                                            $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
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
                                            $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
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
        //                        ncuotas = $('#ven_plazo').val(); /////// ELIMINAR
        //                        monto_cuota = $('#ven_cuota').val(); /////// ELIMINAR
                                            ncuotas = $('#meses_plazo').val();
                                            monto_cuota = '';
                                        } else if (def === 'cm') {
                                            ncuotas = '';
                                            monto_cuota = $('#cuota_mensual').val();
                                        }

                                        saldo_financiar = $('#ven_monto_efectivo').val() * 1;
                                        fecha_pri_cuota = $('#fecha_pri_cuota').val();
                                        var fecha_inicio = $('#u_fecha_valor').val();

                                        var rango = $('#ven_rango option:selected').val();
                                        var frec = $('#ven_frecuencia option:selected').val();

                                        var interes = $('#interes_anual').val();

                                        if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && saldo_financiar > 0 && fecha_pri_cuota !== '') {
                                            var moneda = $('#ven_moneda').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
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
                                            par.rango = rango;
                                            par.frecuencia = frec;
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


                                    $('#btn_guardar').click(function() {
                                        var lote_id = $('#ori_ven_id').val() * 1;
                                        if (lote_id <= 0) {
                                            $.prompt("Seleccione la Venta Origen");
                                            return false;
                                        }
                                        if (!pagos) {
                                            $.prompt("Habilite los Pagos");
                                            return false;
                                        }
                                        var saldo_efectivo = $('#ven_monto_efectivo').val() * 1;
        //                    if(saldo_efectivo>0){
                                        if (false) {
                                            var def = $('#def_plan_efectivo option:selected').val();
                                            if (def === 'mp') {
                                                var mp = $('#meses_plazo').val() * 1;
                                                var fpc = $('#fecha_pri_cuota').val();
                                                if (!(mp > 0 && fpc !== '')) {
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La meses plazo <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }
                                            } else if (def === 'cm') {
                                                var cm = $('#cuota_mensual').val() * 1;
                                                var fpc = $('#fecha_pri_cuota').val();
                                                if (!(cm > 0 && fpc !== '')) {
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La cuota Mensual <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }
                                            } else if (def === 'manual') {
                                                var capital_total = $('#c_total_efectivo').val() * 1;
                                                var saldo = $('#saldo_final').val();
                                                if (capital_total !== saldo) {
                                                    $.prompt('en el plan de pagos manual del monto en efectivo falta definir mas cuotas para igualar al monto en efectivo de la venta');
                                                    return false;
                                                }
                                            }
                                        }

                                        var fecha = $('#fecha').val();
                                        var fecha_valor = $('#u_fecha_valor').val();
                                        console.log(fecha + ' ' + fecha_valor);
                                        if (fecha !== fecha_valor) {
                                            $.prompt('La fecha Valor de la venta Actual no es igual a la fecha de la transaccion');
                                            return;
                                        }
                                        var fecha_valor = $('#ori_fecha_valor').val();
                                        if (fecha !== fecha_valor) {
                                            $.prompt('La fecha Valor de la Venta a Anterior no es igual a la fecha de la transaccion');
                                            return;
                                        }

                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                            var dato = JSON.parse(respuesta);
                                            if (dato.response === "ok") {

                                                document.frm_sentencia.submit();
                                            } else if (dato.response === "error") {
                                                $.prompt(dato.mensaje);
                                                return false;
                                            }
                                        });
                                    });
            </script>
        </form>
        <?php
    }

    function retencion() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        if ($venta->ven_estado != 'Pendiente' && $venta->ven_estado != 'Pagado') {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La venta ya no se encuentra en estado </>Pendiente</b> para ser Retenida", "$url", 'Volver', 'Error');
            return;
        }
        if ($_POST) {
            $this->guardar_retencion($venta);
        } else {
            $this->frm_retencion($venta);
        }
    }

    function frm_retencion($venta) {
        $this->formulario->dibujar_titulo("RETENER VENTA");
        $monto_intercambio = $venta->ven_monto_intercambio;

//        $amontos=  FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
        $sum_inter_pag = 0;
        foreach ($amontos_pag as $ipag) {
            $sum_inter_pag+=$ipag->monto;
        }
        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        if (!$upago) {
            $upago = new stdClass();
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_fecha_pago = $venta->ven_fecha;
        }
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=RETENER&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro. Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>

                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha Ult. Pago</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago); ?></div>
                            </div>
                            <div class="Etiqueta" >Fecha Ult. Valor</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Anticipo</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_res_anticipo; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Intercambio</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_intercambio; ?></div>
                            </div>
                            <div class="Etiqueta" >Intercambio Consumido</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $sum_inter_pag; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Aporte de Venta Anterior</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_venta_pagado; ?></div>
                            </div>
                        </div>
        <?php $pagado = $this->total_pagado($venta->ven_id); ?>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo a Financiar</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_efectivo; ?></div>
                            </div>
                            <div class="Etiqueta" >Capital Pagado</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->capital; ?></div>
                            </div>
                        </div>

        <?php $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo Capital</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $saldo; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha de Retencion</div>
                            <div id="CajaInput">
        <?php FORMULARIO::cmp_fecha('fecha'); ?>
                                <!--<input type="text" id="fecha" name="fecha" value="<?php // echo date('d/m/Y'); ?>">-->
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Total Aportado</b></div>
                            <div id="CajaInput">
        <?php $tot_pagado = $venta->ven_res_anticipo + $venta->ven_venta_pagado + $pagado->capital; ?>
                                <div class="read-input"><?php echo $tot_pagado; ?></div>
                                <img id="btn_ver_venta" style="display: inline; cursor: pointer; "src="images/b_browse.png" width="16">
                                <img id="btn_ver_venta_nota" style="display: inline; cursor: pointer; "src="images/notas.png" width="16">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Observacion</div>
                            <div id="CajaInput">
                                <textarea id="observacion" name="observacion"></textarea>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="button" class="boton" name="" value="Retener Venta" onclick="retener_venta();">
                                    <input type="button" class="boton" name="" value="Volver" onClick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                                    $('#fecha').mask('99/99/9999');
                                    function retener_venta() {
                                        var fecha = $('#fecha').val();
                                        if (trim(fecha) === '') {
                                            $.prompt('Ingrese la fecha de Retencion');
                                            return false;
                                        }

                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                            var dato = JSON.parse(respuesta);
                                            if (dato.response === "ok") {
                                                document.frm_sentencia.submit();
                                            } else if (dato.response === "error") {
                                                $.prompt(dato.mensaje);
                                                return false;
                                            }
                                        });
                                    }
                                    $('#btn_ver_venta').click(function() {
                                        window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id=<?php echo $venta->ven_id ?>', 'reportes', 'left=150,width=1000,height=500,top=0,scrollbars=yes');
                                    });
                                    $('#btn_ver_venta_nota').click(function() {
                                        window.open('gestor.php?mod=venta&tarea=NOTA&id=<?php echo $venta->ven_id ?>', 'reportes', 'left=150,width=1000,height=500,top=0,scrollbars=yes');
                                    });
        </script>

        <?php
    }

    function guardar_retencion($venta) {
//        FUNCIONES::print_pre($_POST);
//            return;
        $conec = new ADO();

        $lote_id = $venta->ven_lot_id;

        if ($venta->ven_estado == 'Pendiente' || $venta->ven_estado == 'Pagado') {
//                    $interes_ganado= FUNCIONES::atributo_bd_sql("select sum(ind_interes_pagado)as campo from interno_deuda where ind_tabla_id='$venta->ven_id' and ind_estado='Pagado'");
//                    $interes_saldo=  FUNCIONES::atributo_bd_sql("select sum(ind_interes)as campo from interno_deuda where ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente'");
            $fecha_cmp = FUNCIONES::get_fecha_mysql($_POST[fecha]);
            $sql = "update venta set ven_estado='Retenido' where ven_id = '$venta->ven_id'";
            $conec->ejecutar($sql);

            $sql = "update lote set lot_estado='Disponible' where lot_id = '$lote_id'";
            $conec->ejecutar($sql);

            $sql = "update interno_deuda set  ind_estado='Retenido' where  ind_tabla = 'venta' and ind_tabla_id = '$venta->ven_id' and ind_estado = 'Pendiente' ";
            $conec->ejecutar($sql);



            $pagado = $this->total_pagado($venta->ven_id);
            $tot_pagado = $venta->ven_res_anticipo + $venta->ven_venta_pagado + $pagado->capital;
            $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;

            $monto_intercambio = $venta->ven_monto_intercambio;

            $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
            $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");


            $observacion = trim($_POST[observacion]);
            $params = array(
                'costo' => $venta->ven_costo,
                'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
                'saldo_efectivo' => $saldo,
                'total_pagado' => $tot_pagado,
                'intercambio' => $monto_intercambio,
                'inter_montos' => $amontos,
                'inter_montos_pag' => $amontos_pag,
            );

            $str_params = json_encode($params);
            $fecha_cre = date('Y-m-d H:i:s');
            $usu_cre = $_SESSION[id];
            $sql_insert = "insert into venta_negocio(
                                vneg_tipo,vneg_ven_id,vneg_ven_ori,vneg_observacion,vneg_fecha,vneg_costo,vneg_moneda,vneg_parametros,vneg_estado,vneg_fecha_cre,vneg_usu_cre
                            )values(
                                'reversion','$venta->ven_id','0','$observacion','$fecha_cmp','0','$venta->ven_moneda','$str_params','Activado','$fecha_cre','$usu_cre'
                            )";
            $conec->ejecutar($sql_insert, true, true);
            $tarea_id = ADO::$insert_id;
            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';

            $referido = FUNCIONES::interno_nombre($venta->ven_int_id);
            $glosa = "Reversion de la Venta Nro. $venta->ven_id, $venta->ven_concepto";
            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");


            $data = array(
                'moneda' => $venta->ven_moneda,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $fecha_cmp,
                'glosa' => $glosa,
                'interno' => $referido,
                'tabla_id' => $venta->ven_id,
                'tarea_id' => $tarea_id,
                'urb' => $urb,
                'costo' => $venta->ven_costo,
                'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
                'saldo_efectivo' => $saldo,
                'total_pagado' => $tot_pagado,
                'intercambio' => $monto_intercambio,
                'inter_montos' => $amontos,
                'inter_montos_pag' => $amontos_pag,
            );

            $comprobante = MODELO_COMPROBANTE::venta_retencion($data);

            COMPROBANTES::registrar_comprobante($comprobante);



//                    $this->anular_comision($conec);


            $tipo = 'Correcto';
            $mensaje = 'Venta Revertida Correctamente!!!';
        } else {
            $tipo = 'Error';
            $mensaje = 'La venta no puede ser retenida por que ya fue Pagada, Anulada o Retenida anteriormente.';
        }

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, "", $tipo);
    }

    function devolver_pagado() {
        $venta = FUNCIONES::objeto_bd_sql("SELECT * FROM venta WHERE ven_id='$_GET[id]'");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
//            $monto_pagado= $this->total_anticipo($reserva->res_id);
        if ($venta->ven_estado == 'Retenido' && $venta->ven_devuelto == '0') {
            if ($_POST) {
                $this->guardar_devolucion($venta);
            } else {
                $this->formulario_devolucion($venta);
            }
        } else {
            $this->formulario->dibujar_titulo("DEVOLUCION PAGOS DE RESERVA");
            $mensaje = 'La venta no esta en estado Retenido o ya ha sido devuelto';
            $this->mensaje = $mensaje;
            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
            }
        }
    }

    function formulario_devolucion($venta) {
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=DEVOLVER&id=$venta->ven_id";
        $this->formulario->dibujar_titulo("DEVOLUCION PAGOS DE VENTA");

        if ($this->mensaje <> "" and $this->tipo_mensaje <> "") {
            $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
        }
        ?>


        <div id="Contenedor_NuevaSentencia">
            <script>
                function ValidarNumero(e)
                {
                    evt = e ? e : event;
                    tcl = (window.Event) ? evt.which : evt.keyCode;
                    if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                    {
                        return false;
                    }
                    return true;
                }



                function enviar_formulario_anticipo() {
                    var monto_dev = $('#monto').val() * 1;
                    var tpagado = $('#total_pagado').val() * 1;
                    console.log(monto_dev + ' > ' + tpagado);
                    if (monto_dev > tpagado) {
                        $.prompt('El monto a Devolver no debe ser mayor al monto Pagado');
                        return false;
                    }
                    var fecha = $('#fecha').val();

                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response !== "ok") {
                            $.prompt(dato.mensaje);
                        } else {
                            if (!validar_fpag_montos(dato.cambios)) {
                                $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                return false;
                            }

                            document.frm_sentencia.submit();
                        }
                    });
                }
            </script>
            <script type="text/javascript" src="js/util.js"></script>
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  

                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <div id="FormSent">
                    <div class="Subtitulo">Devoluci&oacute;n de Venta</div>
                    <div id="ContenedorSeleccion" style="width: 100%">

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Anticipo</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_res_anticipo; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Intercambio</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_intercambio; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Aporte de Venta Anterior</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_venta_pagado; ?></div>
                            </div>
                        </div>

                        <?php
                        $pagado = $this->total_pagado($venta->ven_id);
//                                    $monto_intercambio=$venta->ven_monto_intercambio;
//                                    $amontos=  FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
                        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

                        $sum_inter_pag = 0;
                        foreach ($amontos_pag as $ipag) {
                            $sum_inter_pag+=$ipag->monto;
                        }
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Capital Pagado</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado->capital; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Total Pagado</b></div>
                            <div id="CajaInput">
                                <input type="hidden" name="total_pagado" id="total_pagado" value="<?php echo $tpagado = $pagado->capital + $venta->ven_venta_pagado + $venta->ven_res_anticipo + $sum_inter_pag ?>">
                                <div class="read-input"><?php echo $tpagado; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto a devolver</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="monto" id="monto" size="12" value="<?php echo $devolver = $tpagado - $venta->ven_comision >= 0 ? $tpagado - $venta->ven_comision : 0; ?>" type="text" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                            </div>							
                        </div>
                        <input type="hidden" id="moneda" name="moneda" value="<?php echo $venta->ven_moneda; ?>">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto a Retener</div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_monto_retener"><?php echo $tpagado - $devolver; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="fecha" id="fecha" size="12" value="<?php if (isset($_POST['respag_fecha'])) echo $_POST['respag_fecha'];
                else echo date("d/m/Y"); ?>" type="text">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                            <div id="CajaInput">
                                <textarea class="caja_texto" name="glosa" id="glosa"  ></textarea>
                            </div>
                        </div>
                        <script>
                $('#moneda').change(function() {
                    var val = $(this).val();
                    var pagado = $('#monto_pagado').attr('data-mp' + val) * 1;
                    $('#monto_pagado').val(pagado.toFixed(2));
                    $('#txt_monto_pagado').val(pagado.toFixed(2));
        //                                            $('monto_pagado').trigger('focusout');
                });
                $('#moneda').trigger('change');
                jQuery(function($) {
                    $("#respag_fecha").mask("99/99/9999");
                });

                $('#monto').keyup(function() {
                    var val = $(this).val();
                    var tpag = $('#total_pagado').val();
                    var ret = tpag - val;
                    $('#txt_monto_retener').text(ret.toFixed(2) * 1)
                });
                        </script>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Pagos</b></div>                                
                            <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'fecha', 'cmp_monto' => 'monto', 'cmp_moneda' => 'moneda')); ?>
                        </div>
                    </div>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">
                                <input type="button" class="boton" name="" value="Volver" onclick="location.href = 'gestor.php?mod=venta'" >
                            </center>
                        </div>
                    </div>
                </div>
        </div>
        <?php
    }

    function guardar_devolucion($venta) {
        $conec = new ADO();

//            $reserva = FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
        $pagado = $this->total_pagado($venta->ven_id);
        $tpagdo = $pagado->capital + $venta->ven_venta_pagado + $venta->ven_res_anticipo;
        $dev_monto = $_POST[monto];
        $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $dev_ingreso = $tpagdo - $dev_monto;
        $sql_update = "update venta set 
                                ven_devuelto='1',
                                ven_dev_monto='$dev_monto',
                                ven_dev_ingreso='$dev_ingreso',
                                ven_dev_fecha='$fecha',
                                ven_dev_usu='$_SESSION[id]'
                            where
                                ven_id=$venta->ven_id";
//                echo $sql_update;
        $conec->ejecutar($sql_update);
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $moneda = $venta->ven_moneda;

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $referido = FUNCIONES::interno_nombre($venta->ven_int_id);

        if ($dev_monto > 0) {
            $glosa = "Pago de Devolucion de la Venta Nro $venta->ven_id, $venta->ven_concepto ";
            $params = array(
                'tabla' => 'venta_devolucion',
                'tabla_id' => $venta->ven_id,
                'fecha' => $fecha,
                'moneda' => $moneda,
                'ingreso' => false,
                'une_id' => $urb->urb_une_id,
                'glosa' => $glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
            );
            $detalles = FORMULARIO::insertar_pagos($params);
        } else {
            $glosa = "Ingreso del Pago de la Venta Nro $venta->ven_id, $venta->ven_concepto";
            $detalles = array();
        }


        $data = array(
            'moneda' => $moneda,
            'ges_id' => $_SESSION[ges_id],
            'fecha' => $fecha,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $venta->ven_id,
            'urb' => $urb,
            'dev_monto' => $dev_monto,
            'dev_ingreso' => $dev_ingreso,
            'monto_pagado' => $tpagdo,
            'detalles' => $detalles,
        );

        $comprobante = MODELO_COMPROBANTE::devolucion_venta($data);

        COMPROBANTES::registrar_comprobante($comprobante);


//                $sqlres = "update reserva_terreno set res_estado='Devuelto' where res_id='".$venta->res_id."'";
//                $conec->ejecutar($sqlres);
//                $this->nota_comprobante_reserva($cmp_id);                
        $this->nota_comprobante_devolucion($llave);
//                    $this->ver_comprobante($llave);
    }

    function nota_comprobante_devolucion() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        $moneda = 2;
        $conec = new ADO();


        $monto = $venta->ven_dev_monto;
//        echo $monto;
        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
                                <link href=css/recibos.css rel=stylesheet type=text/css>
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";
        $myday = setear_fecha(strtotime($venta->ven_dev_fecha));
        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reserva&tarea=ACCEDER\';"></td></tr></table>
				';
        if ($this->verificar_permisos('ACCEDER')) {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }
        ?>



        <br><br>
        <div id="contenido_reporte" style="clear:both;">
            <link href="css/recibos.css" rel="stylesheet" type="text/css" />
            <!-- recibo inicio -->
            <div class="recibo">
                <div class="reciboTop">

                    <img class="reciboLogo" src="imagenes/micro.png" width="150" height="80"alt="">

                    <div class="reciboTi">
                        <div class="reciboText">RECIBO OFICIAL</div>
                        <div class="reciboNum"><b>Nro.</b> <?php echo $venta->ven_id; ?></div>
                        <div class="reciboText"><h5>(Original)</h5></div>
                    </div>

                    <div class="reciboMoney">
                        <div class="reciboCapa">
                            <div class="reciboLabel">
        <?php
        if ($moneda == '1')
            echo 'Bs.';
        else
            echo '$us.';
        ?>
                            </div>
                            <div class="reciboMonto">
                                <?php echo number_format($monto, 2, '.', ','); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="reciboCont">
                    <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Pagado al Sr.(a):</span> 
                                <span class="reciboTexts"> <?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">La Suma de:</span>
                                <span class="reciboTexts"><?php
                        $aux = intval($monto);

                        if ($aux == ($monto)) {

                            echo strtoupper($this->num2letras($monto)) . '&nbsp;&nbsp;00/100';
                        } else {

                            $val = explode('.', $monto);

                            echo strtoupper($this->num2letras($val[0]));

                            if (strlen($val[1]) == 1)
                                echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                            else
                                echo '&nbsp;&nbsp;' . $val[1] . '/100';
                        }
                                ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <span class="reciboLabels">
                                    <?php
                                    if ($moneda == '1')
                                        echo 'Bolivianos';
                                    if ($moneda == '2')
                                        echo 'Dolares';
                                    ?>
                                </span> 
                            </td>
                        </tr>

        <?php ?>

                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Por concepto de:</span> 
                                <span class="reciboTexts"> <?php echo 'Devoluci&oacute;n de Reserva Nro.' . $venta->ven_id; ?></span>
                            </td>
                        </tr>


                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Forma de Pago:</span> 
                                <span class="reciboTexts"> Efectivo </span>                                                              
                            </td>
                        </tr>
                        <tr>
                            <td class="reciboRight" colspan="4">
        <?php

        function nombremes($mes) {
            setlocale(LC_TIME, 'spanish');
            $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
            return $nombre;
        }
        ?>
                                <span class="reciboLabels">Fecha:</span> 
                                <span class="reciboTextsLinea">
                                <?php
                                $valores = explode('-', $venta->ven_dev_fecha);
                                echo $valores[2];
                                ?></span>
                                <span class="reciboLabels">de</span> 
                                <span class="reciboTextsLinea"><?php echo strtoupper(nombremes($valores[1])); ?></span>
                                <span class="reciboLabels">del</span> 
                                <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                            </td>
                        </tr>

                        <tr>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></b></span>
                            </td>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $this->nombre_persona($venta->ven_dev_usu); ?></b></span>
                            </td>
                        </tr>
                    </table>
                </div><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
            </div>
            <!-- recibo -->

            <p style="page-break-after: always;"></p>

            <!-- recibo copia inicio -->

            <!-- recibo copia-->


        </div><br>
        <?php
    }

    function intercambio() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        if ($_GET[acc] == 'pagar') {
            if ($_POST) {
                $this->guardar_intercambio_pago($venta);
            } else {
                $this->frm_intercambio_pago($venta);
            }
        } elseif ($_GET[acc] == 'ver') {
            
        } elseif ($_GET[acc] == 'anular') {
            $this->anular_intercambio_pago($venta);
        } else {
            $this->listado_intercambio($venta);
        }
    }

    function anular_intercambio_pago($venta) {
        $conec = new ADO();
        include_once 'clases/registrar_comprobantes.class.php';
        $bool = COMPROBANTES::anular_comprobante('venta_intercambio_pago', $_GET[vipag_id]);
        if (!$bool) {
            $mensaje = "El pago de la mora no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $sql_up = "update venta_intercambio_pago set vipag_estado='Anulado' where vipag_id='$_GET[vipag_id]'";
        $conec->ejecutar($sql_up, false);
        $mensaje = "Pago eliminado correctamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=INTERCAMBIO&id=$venta->ven_id");
    }

    function guardar_intercambio_pago($venta) {
        $conec = new ADO();
        $monto = $_POST[monto];
        $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $moneda = $venta->ven_moneda;
        $fecha_cre = date('Y-m-d H:i:s');
        $debito = $_POST[debito];
        $str_gastos_ids = implode(',', $_POST[gastos_une_ids]);
        $str_gastos_porc = implode(',', $_POST[gastos_une_porc]);
        $cta_gasto_id = $_POST[cta_gasto_id];
        $inter_id = $_GET[inter_id];
        $sql_insert = "insert into venta_intercambio_pago(
                        vipag_ven_id,vipag_inter_id,vipag_monto,vipag_moneda,vipag_fecha,vipag_fecha_cre,
                        vipag_usu_cre,vipag_estado,vipag_debito,vipag_gastos_ids,vipag_gastos_porc,vipag_cue_id
                    )values(
                        '$venta->ven_id','$inter_id]','$monto','$moneda','$fecha','$fecha_cre',
                        '$_SESSION[id]','Activo','$debito','$str_gastos_ids','$str_gastos_porc','$cta_gasto_id'
                    )";
        $conec->ejecutar($sql_insert, false);
        $llave = mysql_insert_id();

        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $referido = FUNCIONES::interno_nombre($venta->ven_int_id);
        $motivo_inter = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$inter_id'");
        $glosa = "Cobro de Inercabio de $motivo_inter->inter_nombre Nro $llave, $venta->ven_concepto";
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $_SESSION[ges_id],
            'fecha' => $fecha,
            'glosa' => $glosa,
            'interno' => $referido,
            'int_id' => $venta->ven_int_id,
            'tabla_id' => $llave,
            'urb' => $urb,
            'motivo_inter' => $motivo_inter,
            'cta_gasto_id' => $cta_gasto_id,
            'monto' => $monto,
            'debito' => $debito,
            'une_gastos_ids' => $_POST[gastos_une_ids],
            'une_gastos_porc' => $_POST[gastos_une_porc],
        );

        $comprobante = MODELO_COMPROBANTE::venta_intercambio_pago($data);

        COMPROBANTES::registrar_comprobante($comprobante);

        $mensaje = "Cobro de Intercambio Realizado correctamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=INTERCAMBIO&id=$venta->ven_id");
    }

    function frm_intercambio_pago($venta) {
        $this->formulario->dibujar_titulo("VENTA INTERCAMBIO");
        $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id=$_GET[inter_id]");
        $vint = FUNCIONES::objeto_bd_sql("select * from venta_intercambio where vint_ven_id='$venta->ven_id' and vint_inter_id='$_GET[inter_id]'");
        $pagado = FUNCIONES::atributo_bd_sql("select sum(vipag_monto) as campo from venta_intercambio_pago where vipag_ven_id='$venta->ven_id' and vipag_inter_id='$_GET[inter_id]' and vipag_estado='Activo'") * 1;
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>

        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=INTERCAMBIO&id=<?php echo $venta->ven_id; ?>&inter_id=<?php echo $_GET[inter_id]; ?>&acc=pagar" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nro Venta:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Motivo Intercambio:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $motivo->inter_nombre; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Monto Intercambio:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $vint->vint_monto * 1; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Pagado:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pagado; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Saldo:</div>
                            <div id="CajaInput">
                                <input type="hidden" name="saldo" id="saldo" value="<?php echo $saldo = $vint->vint_monto - $pagado; ?>">
                                <div class="read-input"><?php echo $saldo; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span><b>Consumir:</b></div>
                            <div id="CajaInput">
                                <input type="text" name="monto" id="monto" value="" autocomplete="off">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span><b>Fecha:</b></div>
                            <div id="CajaInput">
        <?php FORMULARIO::cmp_fecha('fecha'); ?>
                                <!--<input type="text" name="fecha" id="fecha" value="<?php // echo date('d/m/Y') ?>">-->

                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span><b>Cuenta de gasto:</b></div>
                            <div id="CajaInput">
                                <input type="hidden" name="cta_gasto_id" id="cta_gasto_id" value="">
                                <input type="text" name="cta_gasto" id="cta_gasto" value="" size="40">

                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span><b>Debito:</b></div>
                            <div id="CajaInput">
                                <select id="debito" name="debito">
                                    <option value="sn">SN</option>
                                    <option value="factura">FACTURA</option>
                                    <option value="retencion">RETENCION</option>
                                </select>
                            </div>
                        </div>
        <?php $gastos = json_decode(FUNCIONES::ad_parametro('par_gastos')); ?>
        <?php foreach ($gastos as $gasto) { ?>
            <?php $gasto = (object) $gasto; ?>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta"><span class="flechas1">*</span><?php echo FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$gasto->une_id'"); ?>:</div>
                                <div id="CajaInput">
                                    <input type="hidden" class="gastos_une_ids"name="gastos_une_ids[]"value="<?php echo $gasto->une_id * 1; ?>" size="15">
                                    <input type="text" class="gastos_une_porc"name="gastos_une_porc[]"value="<?php echo $gasto->une_porc * 1; ?>" size="15"> %
                                </div>
                            </div>   
        <?php } ?>
                    </div> 
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                                    <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta&tarea=INTERCAMBIO&id=<?php echo $venta->ven_id; ?>';" value="Volver" name="">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                            mask_decimal('#monto', null);
                            mask_decimal('.gastos_une_porc', null);
                            $('#fecha').mask('99/99/9999');
                            function complete_cuenta() {
                                var options_ac = {
                                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&",
                                    varname: "input",
                                    json: true,
                                    shownoresults: false,
                                    maxresults: 6,
                                    callback: function(obj) {
                                        $("#cta_gasto_id").val(obj.id);
                                    }
                                };
                                var as_json3 = new _bsn.AutoSuggest('cta_gasto', options_ac);
                            }
                            complete_cuenta();
                            $('#btn_guardar').click(function() {
                                var monto = $('#monto').val() * 1;
                                var fecha = $('#fecha').val() * 1;
                                if (monto === '' && fecha === '') {
                                    $.prompt("Ingrese el monto y la fecha");
                                    return false;
                                }
                                var saldo = $('#saldo').val() * 1;
                                if (monto > saldo) {
                                    $.prompt("El monto no debe ser mayor al saldo");
                                    return false;
                                }
                                var gastos = $('.gastos_une_porc');
                                var sum_porc = 0;
                                for (var i = 0; i < gastos.size(); i++) {
                                    sum_porc += $(gastos[i]).val() * 1;
                                }
                                if (sum_porc !== 100) {
                                    $.prompt("La suma del porcentaje de los gasto debe ser Igual al 100%");
                                    return false;
                                }

                                var fecha = $('#fecha').val();
                                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                    var dato = JSON.parse(respuesta);
                                    if (dato.response === "ok") {
                                        document.frm_sentencia.submit();
        //                        console.log('sdfsdf45');
                                    } else if (dato.response === "error") {
                                        $.prompt(dato.mensaje);
                                        return false;
                                    }
                                });
                            });
        </script>
        <?php
    }

    function listado_intercambio($venta) {
        $this->formulario->dibujar_titulo("VENTA INTERCAMBIO");
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=CAMBIAR LOTE&id=<?php echo $venta->ven_id; ?>" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nro Venta:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Monto de Intercambio:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_monto_intercambio; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Intercambio</div>
                    <div id="ContenedorSeleccion">
        <?php $intercambios = FUNCIONES::lista_bd_sql("select * from venta_intercambio where vint_ven_id='$venta->ven_id'"); ?>
        <?php for ($i = 0; $i < count($intercambios); $i++) { ?>
            <?php $vint = $intercambios[$i]; ?>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta"><?php echo FUNCIONES::atributo_bd_sql("select inter_nombre as campo from ter_intercambio where inter_id='$vint->vint_inter_id'"); ?>:</div>
                                <div id="CajaInput">
                                    <div class="read-input" style="min-width:80px "><?php echo $vint->vint_monto; ?></div>
                                </div>
                                <div class="Etiqueta" style="width: 70px">Pagado:</div>
                                <div id="CajaInput">
                                    <div class="read-input" style="min-width:80px "><?php echo $pagado = FUNCIONES::atributo_bd_sql("select sum(vipag_monto) as campo from venta_intercambio_pago where vipag_ven_id='$venta->ven_id' and vipag_inter_id='$vint->vint_inter_id' and vipag_estado='Activo'") * 1; ?></div>
                                </div>
                                <div class="Etiqueta" style="width: 70px">Saldo:</div>
                                <div id="CajaInput">
                                    <div class="read-input" style="min-width:80px "><?php echo $vint->vint_monto - $pagado; ?></div>
                                </div>
                                <a href="gestor.php?mod=venta&tarea=INTERCAMBIO&id=<?php echo $venta->ven_id ?>&inter_id=<?php echo $vint->vint_inter_id ?>&acc=pagar" style="float: left; margin: 1px 5px;">
                                    <img src="images/comprar.png" width="20px">
                                </a>

                            </div>
        <?php } ?>
                    </div>
                    <div id="ContenedorSeleccion">
                        <table class="tablaLista" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Motivo</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                                    <th>Fecha</th>
                                    <th class="tOpciones"></th>
                                </tr>
                            </thead>
                            <tbody>
        <?php $vi_pagos = FUNCIONES::lista_bd_sql("select * from venta_intercambio_pago,ter_intercambio where vipag_inter_id=inter_id and  vipag_ven_id='$venta->ven_id' and vipag_estado='Activo'"); ?>
        <?php foreach ($vi_pagos as $vi_pag) { ?>
                                    <tr>
                                        <td><?php echo $vi_pag->inter_nombre; ?></td>
                                        <td><?php echo $vi_pag->vipag_monto ?></td>
                                        <td><?php echo $vi_pag->vipag_moneda == '2' ? 'Dolares' : 'Bolivianos' ?></td>
                                        <td><?php echo FUNCIONES::get_fecha_latina($vi_pag->vipag_fecha); ?></td>
                                        <td>
                                            <img width="16" border="0" class="anular_inter" style="cursor: pointer;"data-id="<?php echo $vi_pag->vipag_id; ?>" src="images/anular.png" alt="ANULAR">
                                        </td>
                                    </tr>
        <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                                <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                            $('.anular_inter').click(function() {
                                var id = $(this).attr('data-id');
                                var txt = 'Esta seguro de anular el Pago?';
                                $.prompt(txt, {
                                    buttons: {Pagar: true, Cancelar: false},
                                    callback: function(v, m, f) {
                                        if (v) {
                                            location.href = 'gestor.php?mod=venta&tarea=INTERCAMBIO&id=<?php echo $venta->ven_id ?>&acc=anular&vipag_id=' + id;
                                        }
                                    }
                                });
                            });
        </script>
        <?php
    }

    function nota() {
        $this->formulario->dibujar_titulo("VENTA NOTAS");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        $acc = $_GET[acc];
        if ($acc == 'add') {
            if ($_POST) {
                $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
                $fecha_now = date('Y-m-d H:i:s');
                $sql_insert = "insert into venta_nota(
                                vnot_ven_id, vnot_observacion,vnot_fecha,vnot_usu_cre,vnot_fecha_cre,vnot_eliminado
                            )values(
                                '$venta->ven_id','$_POST[observacion]','$fecha','$_SESSION[id]','$fecha_now','No'
                            )
                            ";
                $conec = new ADO();
                $conec->ejecutar($sql_insert);
                $mensaje = "NOTA GUARDADA EXITOSAMENTE";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=NOTA&id=$venta->ven_id", '', 'Correcto');
            } else {
                $this->frm_nota($venta, 'add');
            }
        } elseif ($acc == 'mod') {
            if ($_POST) {
                $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);

                $sql_up = "update venta_nota set vnot_fecha='$fecha', vnot_observacion='$_POST[observacion]' where vnot_id='$_GET[vnot_id]'
                            ";
                $conec = new ADO();
                $conec->ejecutar($sql_up);
                $mensaje = "NOTA MODIFICADA EXITOSAMENTE";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=NOTA&id=$venta->ven_id", '', 'Correcto');
            } else {
                $this->frm_nota($venta, 'mod', $_GET[vnot_id]);
            }
        } elseif ($acc == 'del') {
            
        } else {
            $this->listado_notas($venta);
        }
    }

    function frm_nota($venta, $acc, $not_id = 0) {
        $and_not_id = "";
        $objeto = new stdClass();
        if ($not_id) {
            $objeto = FUNCIONES::objeto_bd_sql("select * from venta_nota where vnot_id=$not_id");
            $and_not_id = "&vnot_id=$not_id";
        } else {
            $objeto->vnot_fecha = date('Y-m-d');
        }
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=NOTA&id=<?php echo $venta->ven_id; ?>&acc=<?php echo $acc; ?><?php echo $and_not_id; ?>" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de Notas</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nro Venta:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Concepto:</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Fecha:</div>
                            <div id="CajaInput">
                                <input type="text" name="fecha" id="fecha" value="<?php echo FUNCIONES::get_fecha_latina($objeto->vnot_fecha); ?>">

                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Observacion:</div>
                            <div id="CajaInput">
                                <textarea id="observacion" name="observacion"><?php echo $objeto->vnot_observacion; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>
                        <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                        <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                    </center>
                </div>
            </div>
        </form>
        <script>
                            $('#btn_guardar').click(function() {
                                var observacion = $('#observacion').val() * 1;
                                var fecha = $('#fecha').val() * 1;
                                if (observacion === '' && fecha === '') {
                                    $.prompt("Ingrese el monto y la fecha");
                                    return false;
                                }
                                document.frm_sentencia.submit();
                            });
        </script>
        <?php
    }

    function listado_notas($venta) {
        ?>
        <div id="Contenedor_NuevaSentencia">
            <div id="FormSent" style="width: 100%">
                <div class="Subtitulo">Listado de Notas</div>
                <div id="ContenedorSeleccion">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Nro Venta:</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_id; ?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Concepto:</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="button" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=NOTA&id=<?php echo $venta->ven_id ?>&acc=add';" value="Agregar Nota">
        <table class="tablaLista" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Observacion</th>
                    <th class="tOpciones"></th>
                </tr>
            </thead>
            <tbody>
        <?php $notas = FUNCIONES::lista_bd_sql("select * from venta_nota where vnot_ven_id='$venta->ven_id' and vnot_eliminado='No' order by vnot_id desc"); ?>
        <?php foreach ($notas as $not) { ?>
                    <tr>
                        <td><?php echo FUNCIONES::get_fecha_latina($not->vnot_fecha); ?></td>
                        <td><?php echo $not->vnot_usu_cre; ?></td>
                        <td><?php echo $not->vnot_observacion; ?></td>
                        <td >
                            <a href="<?php echo "gestor.php?mod=venta&tarea=NOTA&id=$venta->ven_id&acc=mod&vnot_id=$not->vnot_id" ?>"><img src="images/b_edit.png"></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
        <?php
    }

    function fecha_valor() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        if ($venta->ven_estado != 'Pendiente') {
            $this->formulario->ventana_volver('La venta ya no se encuentra en estado <b>Pendiente</b>', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        if ($_POST) {
            $this->guardar_fecha_valor($venta);
        } else {

            $this->frm_fecha_valor($venta);
        }
    }

    function guardar_fecha_valor($venta) {
        $this->barra_opciones($venta);
        echo "<br><br>";
        $conec = new ADO();
        $fecha_valor = FUNCIONES::get_fecha_mysql($_POST[nfecha_valor]);
        $fecha_pago = FUNCIONES::get_fecha_mysql($_POST[nfecha_pago]);
        $codigo = FUNCIONES::fecha_codigo();
        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        if (!$upago) {
            $upago = new stdClass();
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_fecha_pago = "$venta->ven_fecha";
        }
        $fecha_cre = date('Y-m-d H:i:s');
        $insert_pago = "insert into venta_pago (
                            vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,vpag_int_id,vpag_moneda,
                            vpag_saldo_inicial,vpag_dias_interes,vpag_interes,vpag_capital,vpag_form,
                            vpag_mora,vpag_monto,vpag_saldo_final,vpag_estado,vpag_interes_ids,
                            vpag_interes_montos,vpag_capital_ids,vpag_capital_montos,
                            vpag_form_ids,vpag_form_montos,vpag_mora_ids,vpag_mora_montos,vpag_mora_con_ids,
                            vpag_mora_con_montos,vpag_mora_gen_ids,vpag_mora_gen_montos,vpag_mora_gen_dias,
                            vpag_fecha_cre,vpag_usu_cre,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,
                            vpag_costo,vpag_costo_ids,vpag_costo_montos,vpag_capital_inc,vpag_capital_desc,
                            vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado,vpag_suc_id
                        )values(
                            '$venta->ven_id','$codigo','$fecha_pago','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda',
                            '$upago->vpag_saldo_final',0,0,0,0,
                            0,0,'$upago->vpag_saldo_final','Activo','',
                            '','','0',
                            '','','','','',
                            '','','','',
                           '$fecha_cre','$_SESSION[id]','$_SESSION[id]','','0',
                            0,'','','0','0',
                            0,0,'','0','$_SESSION[suc_id]'
                        )";
//        $this->barra_opciones($venta);
//        echo "<br><br>";

        $conec->ejecutar($insert_pago, true);

        $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");
        $pagado = $this->total_pagado($venta->ven_id);
        $capital_pag = $pagado->capital;
        $capital_inc = $pagado->incremento;
        $capital_desc = $pagado->descuento;
        $cuota_pag = $ucuota->ind_num_correlativo;

        $sql_up = "update venta set 
                        ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$fecha_valor', 
                        ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                        ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 
                        ven_usaldo='$upago->vpag_saldo_final'
                    where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);

//        $sql_up="update venta set ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$fecha_valor', ven_usaldo='$upago->vpag_saldo_final' where ven_id='$venta->ven_id'";
//        $conec->ejecutar($sql_up);

        $mensaje = "Cambio de Fecha Valor realizado Exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_fecha_valor($venta) {
        $this->barra_opciones($venta);
        echo "<br><br>";
        $this->formulario->dibujar_titulo("MODIFICAR FECHA VALOR");
        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        if (!$upago) {
            $upago = new stdClass();
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_fecha_pago = '0000-00-00';
        }
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=FECHA VALOR&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Ultima fecha Valor</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor); ?></div>
                            </div>
                            <div class="Etiqueta" >Ultima fecha Pago</div>
                            <div id="CajaInput">
                                <input type="hidden" id="ufecha_valor" name="ufecha_valor" value="<?php echo $upago->vpag_fecha_pago; ?>">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >fecha de Pago</div>
                            <div id="CajaInput">
                                <input type="text" id="nfecha_pago" name="nfecha_pago" value="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nueva fecha Valor</div>
                            <div id="CajaInput">
                                <input type="text" id="nfecha_valor" name="nfecha_valor" value="">
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                            $('#nfecha_valor').mask('99/99/9999');
                            $('#nfecha_pago').mask('99/99/9999');
                            $('#frm_sentencia').submit(function() {
                                return false;
                            });
                            $('#btn_guardar').click(function() {

                                var ufecha_pago = $('#nfecha_pago').val();
                                var nfecha_valor = $('#nfecha_valor').val();
                                if (ufecha_pago === '' && nfecha_valor === '') {
                                    $.prompt('La Fecha de Pago y la Fecha Valor deben ser diferentes de vacio');
                                    return false;
                                }
        //                console.log('submit');
                                document.frm_sentencia.submit();
                            });
        </script>
        <?php
    }

    function reactivar() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");

        if ($venta->ven_estado != 'Retenido') {
            $msj.="<p>La venta no se encuentra en estado <b>Retenido</b></p>";
        }
        if ($lote->lot_estado != 'Disponible') {
            $msj.="<p>El lote de la venta ya no se encuentra en estado  <b>Disponible</b></p>";
        }
        if ($venta->ven_devuelto == '1') {
            $msj.="<p>Los aportes por la venta ya fueron devueltos </p>";
        }
        if ($msj) {
            $this->formulario->ventana_volver($msj, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }

        if ($_POST) {
            $this->guardar_reactivar($venta);
        } else {
            $this->frm_reactivar($venta);
        }
    }

    function guardar_reactivar($venta) {
        $conec = new ADO();
        $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $observacion = $_POST[observacion];

        $sql_up = "update venta set ven_estado='Pendiente' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_up);
        $sql_up = "update interno_deuda set ind_estado='Pendiente' where ind_tabla='venta' and ind_estado='Retenido' and ind_tabla_id='$venta->ven_id'";
        $conec->ejecutar($sql_up);
        $fecha_cre = date('Y-m-d H:i:s');
        $sql_insert = "insert into venta_negocio (
                            vneg_tipo,vneg_ven_id,vneg_ven_ori,vneg_observacion,vneg_fecha,vneg_costo,
                            vneg_moneda,vneg_estado,vneg_fecha_cre,vneg_usu_cre,vneg_parametros
                        )values(
                            'activacion','$venta->ven_id','0','$observacion','$fecha','',
                            '$venta->ven_moneda','Activado','$fecha_cre','$_SESSION[id]','{}'
                        )";
        $conec->ejecutar($sql_insert, true, true);
        $tarea_id = ADO::$insert_id;

        $sql_up = "update lote set lot_estado='Vendido' where lot_id='$venta->ven_lot_id'";
        $conec->ejecutar($sql_up);

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';

        $vnreversion = FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_tipo='reversion' and vneg_ven_id=$venta->ven_id order by vneg_id desc limit 1");
        $vneg_id = 0;
        if ($vnreversion) {
            $vneg_id = $vnreversion->vneg_id;
        }
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' and cmp_tabla_id='$venta->ven_id' and cmp_tarea_id='$vneg_id'");
        $glosa = "Reactivacion de la Venta Nro $venta->ven_id : $venta->ven_concepto";
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        if ($cmp) {
            $rdetalles = FUNCIONES::lista_bd_sql("select * from con_comprobante_detalle where cde_cmp_id=$cmp->cmp_id and cde_mon_id=$cmp->cmp_mon_id");
            $data = array(
                'moneda' => $venta->ven_moneda,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $fecha,
                'glosa' => $glosa,
                'interno' => $cmp->cmp_referido,
                'tabla_id' => $venta->ven_id,
                'tarea_id' => $tarea_id,
                'urb' => $urb,
                'rdetalles' => $rdetalles
            );

            $comprobante = MODELO_COMPROBANTE::venta_reactivacion($data);

            COMPROBANTES::registrar_comprobante($comprobante);
        }

        $mensaje = "Venta Reactivada Exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_reactivar($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("REACTIVAR VENTA");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=ACTIVAR&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha de Reactivacion</div>
                            <div id="CajaInput">
        <?php echo FORMULARIO::cmp_fecha('fecha'); ?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Observacion</div>
                            <div id="CajaInput">
                                <textarea id="observacion" name="observacion"></textarea>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                            $('#ven_ufecha_prog').mask('99/99/9999');
                            $('#frm_sentencia').submit(function() {
                                return false;
                            });
                            $('#btn_guardar').click(function() {
        //                var ufecha_valor_mysql=$('#ufecha_valor').val();
        //                var nfecha_valor_mysql=fecha_mysql($('#nfecha_valor').val());
        //                if(ufecha_valor_mysql>=nfecha_valor_mysql){
        //                    $.prompt('La Nueva fecha valor debe ser mayor a la Ultima Fecha Valor');
        //                    return false;
        //                }
        //                console.log('submit');
                                var val_interes = $('#ven_val_interes').val();
                                var ufecha_prog = $('#ven_ufecha_prog').val();
                                if (val_interes === '' || ufecha_prog === '') {
                                    $.prompt('Ingrese correctamente el interes y la maxima fecha Programada');
                                    return false;
                                }
                                document.frm_sentencia.submit();
                            });
        </script>
        <?php
    }

    function desbloquear_cuotas() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_estado != 'Pendiente') {
            $this->formulario->ventana_volver('La venta ya no se encuentra en estado <b>Pendiente</b>', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        if ($_POST) {
            $this->guardar_desbloquear_cuotas($venta);
        } else {
            $this->frm_desbloquear_cuotas($venta);
        }
    }

    function editar_documento() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_estado != 'Pendiente') {
            $this->formulario->ventana_volver('La venta ya no se encuentra en estado <b>Pendiente</b>', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        if ($_POST) {
            $this->guardar_editar_documento($venta);
        } else {
            $this->frm_editar_documento($venta);
        }
    }

    function guardar_editar_documento($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $conec = new ADO();
        $ufecha_prog = FUNCIONES::get_fecha_mysql($_POST[ven_ufecha_prog]);
        $set_ven_val_interes = "";
        if (isset($_POST[ven_val_interes])) {
            $ven_val_interes = $_POST[ven_val_interes];
            $set_ven_val_interes = "ven_val_interes='$ven_val_interes',";
        }

        $ven_suc_id = $_POST[ven_suc_id];
        $ven_lug_id = $_POST[lug_id];
        $ven_form = $_POST[ven_form];

        $txt_ubicacion = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id='$ven_lug_id'");

        $sql_up = "update venta set 
                    $set_ven_val_interes
                    ven_ufecha_prog='$ufecha_prog',
                    ven_suc_id='$ven_suc_id',
                    ven_lug_id='$ven_lug_id',
                    ven_form='$ven_form',
                    ven_ubicacion='$txt_ubicacion'
                where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);

        if ($ven_form != $venta->ven_form) {
            $sql_up = "update interno_deuda set ind_form='$ven_form' where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id='$venta->ven_id'";
            $conec->ejecutar($sql_up);
        }

        $mensaje = "Cambio de datos de la Venta modificado correctamente realizado Exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_editar_documento($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("EDITAR DATOS VENTA");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=EDITAR DOCUMENTO&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Interes Anual</div>
                            <div id="CajaInput">
        <?php
        $desc_interes_usu_ids = FUNCIONES::ad_parametro('par_desc_interes_usu_ids');
        $usu_ids = explode(',', $desc_interes_usu_ids);
        $usu_descint = in_array($_SESSION[id], $usu_ids);
        ?>
        <?php if ($usu_descint) { ?>
                                    <input type="text" id="ven_val_interes" name="ven_val_interes" value="<?php echo $venta->ven_val_interes * 1; ?>">
        <?php } else { ?>
                                    <div class="read-input"><?php echo $venta->ven_val_interes * 1; ?></div>
        <?php } ?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Maxima Fecha Prog.</div>
                            <div id="CajaInput">
                                <input type="text" id="ven_ufecha_prog" name="ven_ufecha_prog" value="<?php echo FUNCIONES::get_fecha_latina($venta->ven_ufecha_prog); ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Sucursal</div>
                            <div id="CajaInput">
                                <select name="ven_suc_id">
                                    <option value="">-- Seleccione --</option>
                                <?php
                                $fun = new FUNCIONES();
                                $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal where suc_eliminado='no'", $venta->ven_suc_id);
                                ?>
                                </select>
                                <!--<div class="read-input"><?php // echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal, ad_usuario where usu_suc_id=suc_id and usu_id='$_SESSION[id]'"); ?></div>-->
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Formulario</div>
                            <div id="CajaInput">
                                <input type="text" id="ven_form" name="ven_form" value="<?php echo $venta->ven_form * 1; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lugar de venta</div>
                            <div id="CajaInput">
                                    <?php $paises = FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'"); ?>
                                <div style="float: left;">
                                    <?php
                                    $lug_id = $venta->ven_lug_id;

                                    $sql_sel = "select * from ter_lugar
                                                inner join ter_estado on (lug_est_id=est_id)
                                                inner join ter_pais on (est_pais_id=pais_id)
                                                where lug_id='$lug_id'";
                                    $lugar = FUNCIONES::objeto_bd_sql($sql_sel);
                                    ?>
                                    <select name="pais_id" id="pais_id" style="width: 150px;">
        <?php foreach ($paises as $pais) { ?>
                                            <option value="<?php echo $pais->pais_id; ?>" <?php echo $lugar->pais_id == $pais->pais_id ? 'selected="true"' : ''; ?>><?php echo $pais->pais_nombre; ?></option>
        <?php } ?>
                                    </select>
                                    <select name="est_id" id="est_id" style="width: 150px;">

                                    </select>
                                    <select name="lug_id" id="lug_id" style="width: 150px;">

                                    </select>
                                </div>
                                <div id="json_estados" hidden="">
                                    <?php $estados = FUNCIONES::lista_bd_sql("select * from ter_estado where est_eliminado='No'") ?>
                                    <?php
                                    foreach ($estados as $est) {
                                        $est->est_nombre = FUNCIONES::limpiar_cadena($est->est_nombre);
                                    }
                                    echo json_encode($estados);
                                    ?>
                                </div>
                                <div id="json_lugares" hidden="">
        <?php $lugares = FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_eliminado='No'") ?>
        <?php
        foreach ($lugares as $lug) {
            $lug->lug_nombre = FUNCIONES::limpiar_cadena($lug->lug_nombre);
        }
        echo json_encode($lugares);
        ?>
                                </div>
                                <input type="hidden" id="sel_est_id" value="<?php echo $lugar->est_id; ?>">
                                <input type="hidden" id="sel_lug_id" value="<?php echo $lugar->lug_id; ?>">
                                <script>
                                    $('#pais_id').change(function() {
                                        var pais_id = $(this).val();
                                        $('#est_id').children().remove();
                                        $('#lug_id').children().remove();
                                        var estados = JSON.parse(trim($('#json_estados').text()));
                                        var options = '';
                                        var sel_est_id = $('#sel_est_id').val();
                                        for (var i = 0; i < estados.length; i++) {
                                            var est = estados[i];
                                            if (pais_id === est.est_pais_id) {
                                                var selected = '';
                                                if (sel_est_id === est.est_id) {
                                                    selected = 'selected="true"';
                                                }
                                                options += '<option value="' + est.est_id + '"' + selected + '>' + est.est_nombre + '</option>';
                                            }
                                        }
                                        $('#est_id').append(options);
                                        $('#est_id').trigger('change');

                                    });
                                    $('#est_id').change(function() {
                                        var est_id = $(this).val();
        //                                                       $('#est_id').children().remove();
                                        $('#lug_id').children().remove();
                                        var lugares = JSON.parse(trim($('#json_lugares').text()));
                                        var options = '';
                                        var sel_lug_id = $('#sel_lug_id').val();
                                        for (var i = 0; i < lugares.length; i++) {
                                            var lug = lugares[i];
                                            if (est_id === lug.lug_est_id) {
                                                var selected = '';
                                                if (sel_lug_id === lug.lug_id) {
                                                    selected = 'selected="true"';
                                                }
                                                options += '<option value="' + lug.lug_id + '" ' + selected + '>' + lug.lug_nombre + '</option>';
                                            }
                                        }
                                        $('#lug_id').append(options);

                                    });
                                    $('#pais_id').trigger('change');
                                    $('#est_id').trigger('change');
                                </script>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                                     $('#ven_ufecha_prog').mask('99/99/9999');
                                     $('#frm_sentencia').submit(function() {
                                         return false;
                                     });
                                     $('#btn_guardar').click(function() {
        //                var ufecha_valor_mysql=$('#ufecha_valor').val();
        //                var nfecha_valor_mysql=fecha_mysql($('#nfecha_valor').val());
        //                if(ufecha_valor_mysql>=nfecha_valor_mysql){
        //                    $.prompt('La Nueva fecha valor debe ser mayor a la Ultima Fecha Valor');
        //                    return false;
        //                }
        //                console.log('submit');
                                         var val_interes = $('#ven_val_interes').val();
                                         var ufecha_prog = $('#ven_ufecha_prog').val();
                                         if (val_interes === '' || ufecha_prog === '') {
                                             $.prompt('Ingrese correctamente el interes y la maxima fecha Programada');
                                             return false;
                                         }
                                         document.frm_sentencia.submit();
                                     });
        </script>
        <?php
    }

    function guardar_desbloquear_cuotas($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $conec = new ADO();

        $ven_bloqueado = $_POST[ven_bloqueado];
        $txt_bloqueo = 'Desbloqueo';
        if ($ven_bloqueado) {
            $txt_bloqueo = 'Bloqueo';
        }
        $sql_up = "update venta set ven_bloqueado='$ven_bloqueado' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_up);
        $mensaje = "$txt_bloqueo de Cuotas realizado Exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_desbloquear_cuotas($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("BLOQUEAR CUOTAS");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=DESBLOQUEAR CUOTAS&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Estado de Cuotas</div>
                            <div id="CajaInput">
                                <select id="ven_bloqueado" name="ven_bloqueado">
                                    <option value="0" <?php echo!$venta->ven_bloqueado ? 'selected="true"' : '' ?>>Desbloqueado</option>
                                    <option value="1" <?php echo $venta->ven_bloqueado ? 'selected="true"' : '' ?>>Bloqueado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                                     $('#ven_ufecha_prog').mask('99/99/9999');
                                     $('#frm_sentencia').submit(function() {
                                         return false;
                                     });
                                     $('#btn_guardar').click(function() {
        //                var ufecha_valor_mysql=$('#ufecha_valor').val();
        //                var nfecha_valor_mysql=fecha_mysql($('#nfecha_valor').val());
        //                if(ufecha_valor_mysql>=nfecha_valor_mysql){
        //                    $.prompt('La Nueva fecha valor debe ser mayor a la Ultima Fecha Valor');
        //                    return false;
        //                }
        //                console.log('submit');
                                         var val_interes = $('#ven_val_interes').val();
                                         var ufecha_prog = $('#ven_ufecha_prog').val();
                                         if (val_interes === '' || ufecha_prog === '') {
                                             $.prompt('Ingrese correctamente el interes y la maxima fecha Programada');
                                             return false;
                                         }
                                         document.frm_sentencia.submit();
                                     });
        </script>
        <?php
    }

    function documentos() {
        $s = "";
        if ($_GET[id]) {
            $s .= '-1';
            if ($_GET[op] == 'add') {
                $s .= '-2';
                if ($_POST) {
                    $s .= '-3';
                    $this->guardar_documento();
                } else {
                    $s .= '-4';
                    $this->frm_documento();
                }
            } elseif ($_GET[op] == 'del') {
                $s .= '-5';
                $this->eliminar_documento();
            } else {
                $s .= '-6';
                $this->listado_documentos();
            }
        } else {
            $s .= '-7';
            $this->dibujar_busqueda();
        }
//        echo "s: $s";
    }

    function eliminar_documento() {
        $rdoc_id = $_GET[rdoc_id];
        if ($rdoc_id) {
            $sql_up = "update reserva_documento set rdoc_eliminado='Si' where rdoc_ven_id='$_GET[id]' and rdoc_id='$rdoc_id'";
            $conec = new ADO();
            $conec->ejecutar($sql_up);
            $mensaje = "Documento eliminado correctamente";
            $this->formulario->dibujar_titulo('SUBIR ARCHIVO');
            $this->formulario->dibujar_mensaje($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=DOCUMENTOS&id=' . $_GET[id], "Correcto");
        }
    }

    function guardar_documento() {
        $a_archivos = explode('.', $_FILES['archivo']['name']);
        $ext = $a_archivos[count($a_archivos) - 1];
        $titulo = str_replace(' ', '_', $_POST[titulo]);
        $nombre_archivo = "$_GET[id]-$titulo.$ext";
        move_uploaded_file($_FILES['archivo']['tmp_name'], "docs/reserva/$nombre_archivo");
        $fecha_cre = date('Y-m-d H:i:s');
        $tdoc_id = $_POST[tdoc_id];
        $sql_insert = "insert into reserva_documento(
                rdoc_res_id,rdoc_tdoc_id,rdoc_archivo,rdoc_descripcion,rdoc_fecha_cre,rdoc_usuario,rdoc_eliminado
            )values(
                '$_GET[id]','$tdoc_id','$nombre_archivo','$_POST[descripcion]','$fecha_cre','$_SESSION[id]','No'
            )";
        $conec = new ADO();
        $conec->ejecutar($sql_insert);
//        echo $sql_insert;
        $mensaje = "Documento guardado exitosamente";
        $this->formulario->dibujar_titulo('SUBIR ARCHIVO');
        $this->formulario->dibujar_mensaje($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=DOCUMENTOS&id=' . $_GET[id], "Correcto");
    }

    function frm_documento() {
        $this->formulario->dibujar_titulo('SUBIR ARCHIVO');
        ?>
        <form name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo;?>&tarea=DOCUMENTOS&id=<?php echo $_GET[id]; ?>&op=add" method="post" enctype="multipart/form-data">
            <br>
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <div id="ContenedorDiv">
                <div class="Etiqueta"><span class="flechas1">*</span>Titulo</div>
                <div id="CajaInput">
                    <input type="text" size="70" class="caja_texto" id="titulo" name="titulo" value="">
                </div>
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta"><span class="flechas1">*</span>Descripcion</div>
                <div id="CajaInput">
                    <textarea type="text" class="caja_texto" id="descripcion" name="descripcion" ></textarea>
                </div>
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta"><span class="flechas1">*</span>Tipo de Documento</div>
                <div id="CajaInput">
                    <select id="tdoc_id" name="tdoc_id" style="width: 200px">
                        <option value="">Seleccione</option>
        <?php
        $fun = new FUNCIONES();
        $fun->combo("select tdoc_id as id, tdoc_nombre as nombre from ter_tipo_documento where tdoc_eliminado='No'");
        ?>
                    </select>
                </div>
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta"><span class="flechas1">*</span>Archivo</div>
                <div id="CajaInput">
                    <input type="file" class="caja_texto" id="archivo" name="archivo" >
                </div>
            </div>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <input class="boton" type="button" onclick="javascript:enviar_formulario();" value="Guardar" name="">
                    <input class="boton" type="button" onclick="location.href = 'gestor.php?mod=venta&tarea=DOCUMENTOS&id=<?php echo "$_GET[id]"; ?>'" value="Volver" name="">
                </div>
            </div>
        </form>
        <script>
                                     function enviar_formulario() {
                                         var titulo = $('#titulo').val();
                                         var descripcion = $('#descripcion').val();
                                         var tdoc_id = $('#tdoc_id option:selected').val();
                                         var file = $('#archivo').val();
                                         if (titulo !== '' && descripcion !== '' && file !== '' && tdoc_id !== '') {
                                             document.frm_sentencia.submit();
                                         } else {
                                             $.prompt('Llene todos los campos requeridos');
                                         }
                                     }
        </script>
        <?php
    }

    function listado_documentos() {
        $this->formulario->dibujar_titulo('DOCUMENTOS DE RESERVA');
        ?>
        <style>
            .del_doc{cursor: pointer;}
        </style>
        <div style="text-align: left; margin: 10px;">
            <input type="button" class="boton" value="Agregar" onclick="location.href = 'gestor.php?mod=<?php echo $this->modulo;?>&tarea=DOCUMENTOS&id=<?php echo $_GET[id] ?>&op=add';">
        </div>
        <table class="tablaLista" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo de Documento</th>
                    <th>Descripcion</th>
                    <th>Archivo</th>
                    <th>Usuario</th>
                    <th>Descargar</th>
                    <th class="tOpciones"></th>
                </tr>
            </thead>
            <tbody>
        <?php $documentos = FUNCIONES::objetos_bd_sql("select * from reserva_documento,ter_tipo_documento where rdoc_tdoc_id=tdoc_id and rdoc_res_id=$_GET[id] and rdoc_eliminado='No'"); ?>
        <?php for ($i = 0; $i < $documentos->get_num_registros(); $i++) { ?>
            <?php $doc = $documentos->get_objeto(); ?>
                    <tr data-venta="<?php echo $_GET[id]; ?>" data-id="<?php echo $doc->rdoc_id; ?>">
                        <td><?php echo $doc->rdoc_id; ?></td>
                        <td><?php echo $doc->tdoc_nombre; ?></td>
                        <td><?php echo $doc->rdoc_descripcion; ?></td>
                        <td><img src="images/files.png" style="height: 15px; float: left;margin-right: 2px;"><?php echo $doc->rdoc_archivo; ?></td>
                        <td><?php echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from ad_usuario,interno where usu_per_id=int_id and usu_id='$doc->rdoc_usuario'"); ?></td>
                        <td><a href="docs/reserva/<?php echo $doc->rdoc_archivo; ?>" target="_blank">Descargar</a></td>
                        <td><img src="images/retener.png" class="del_doc"></td>
                    </tr>
            <?php $documentos->siguiente(); ?>
                <?php } ?>
            </tbody>
        </table>
        <script>
                                     $('.del_doc').click(function() {
                                         var doc_id = $(this).parent().parent().attr('data-id');
                                         var venta = $(this).parent().parent().attr('data-venta');
                                         var txt = 'Esta seguro de eliminar el documento';
                                         $.prompt(txt, {
                                             buttons: {Aceptar: true, Cancelar: false},
                                             callback: function(v, m, f) {
                                                 if (v) {
                                                     location.href = 'gestor.php?mod=reserva&tarea=DOCUMENTOS&id=' + venta + '&op=del&rdoc_id=' + doc_id;
                                                 }
                                             }
                                         });

                                     });
        </script>
        <?php
    }

    function cambio_vendedor() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($_POST) {
            $this->guardar_cambio_vendedor($venta, $_POST);
        } else {
            $this->frm_cambio_vendedor($venta);
        }
    }

    function guardar_cambio_vendedor($venta, $post) {
        $post = (object) $post;
//        echo "guardar_cambio_vendedor...";
//        print_r($_POST);
        if ($venta->ven_multinivel == 'si') {
            $obj_patr = FUNCIONES::objeto_bd_sql("select * from venta inner join vendedor on (ven_id=vdo_venta_inicial)
            where vdo_id=$post->vendedor_id");

            $nivel = $obj_patr->vdo_nivel + 1;

            $sql_upd = "update vendedor set 
                        vdo_vendedor_id=$obj_patr->vdo_id,
                        vdo_nivel=$nivel 
                        where vdo_venta_inicial = '$post->ven_id'";
            FUNCIONES::bd_query($sql_upd);

            $sql_upd_com_cob = "update comision_cobro set 
                                comcob_vdo_id=$obj_patr->vdo_id 
                                where comcob_ven_id = '$post->ven_id'";
            FUNCIONES::bd_query($sql_upd_com_cob);
            
            $sql_upd_venta = "update venta set 
                                ven_vdo_id=$obj_patr->vdo_id 
                                where ven_id = '$post->ven_id'";
            FUNCIONES::bd_query($sql_upd_venta);
            
            if ($venta->ven_res_id > 0) {
                $sql_upd_reservas = "update reserva_terreno set 
                                    res_vdo_id=$obj_patr->vdo_id 
                                    where res_id = '$venta->ven_res_id'";
                FUNCIONES::bd_query($sql_upd_reservas);                
            }
        } else {
            $obj_vdo = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$post->vendedor_id");
            
            $sql_upd_venta = "update venta set 
                                ven_vdo_id=$obj_vdo->vdo_id 
                                where ven_id = '$post->ven_id'";
            FUNCIONES::bd_query($sql_upd_venta);
            
            if ($venta->ven_res_id > 0) {
                $sql_upd_reservas = "update reserva_terreno set 
                                    res_vdo_id=$obj_vdo->vdo_id 
                                    where res_id = '$venta->ven_res_id'";
                FUNCIONES::bd_query($sql_upd_reservas);                
            }
            
            $comision = FUNCIONES::objeto_bd_sql("select * from comision 
                                    where com_ven_id='$venta->ven_id' 
                                    and com_vdo_id='$venta->ven_vdo_id'
                                    and com_estado='Pendiente'");
            
            if ($comision) {

                include_once 'clases/registrar_comprobantes.class.php';
                $sql_anul = "update comision set com_estado='Anulado' 
                        where com_ven_id='$venta->ven_id' and com_tipo is null";
                FUNCIONES::bd_query($sql_anul);
                $res = COMPROBANTES::anular_comprobante('comision', $comision->com_id, null, 'cambio_vendedor');

                $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");

                if ($res) {

                    $data_com = array(
                        'ven_id' => $venta->ven_id,
                        'vendedor' => $post->vendedor_id,
                        'superficie' => $venta->ven_superficie,
                        'tipo' => $venta->ven_tipo,
                        'plazo' => $venta->ven_plazo,
                        'monto' => $venta->ven_monto,
                        'moneda' => $venta->ven_moneda,
                        'fecha' => $venta->ven_fecha,
                        'urb' => $urb,
                    );

                    $this->insertar_comision($data_com);
                }
            }
        }
        $figura = ($venta->ven_multinivel == 'si')?'Patrocinador':'Vendedor';
        
        $mensaje = "Cambio de $figura de la Venta realizado exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
        
    }

    function frm_cambio_vendedor($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("EDITAR DATOS VENTA");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=CAMBIAR VENDEDOR&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_concepto($venta->ven_lot_id); // $reserva->ven_concepto;  ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><?php echo ($venta->ven_multinivel == 'si') ? 'Patrocinador' : 'Vendedor'; ?>:</div>
                            <div id="CajaInput">
                                <select name="vendedor_id" id="vendedor_id">
                                    <option value="">--Seleccione--</option>
        <?php
        if ($venta->ven_multinivel == 'si') {
            $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre='AFILIADOS'";
        } else {
            $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre!='AFILIADOS';";
        }
        $fun = new FUNCIONES();
        $fun->combo($sql, $venta->ven_vdo_id);
        ?>
                                </select>
                                <!--<input type="text" id="ven_ufecha_prog" name="ven_ufecha_prog" value="<?php // echo FUNCIONES::get_fecha_latina($reserva->ven_ufecha_prog);?>">-->
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
                             $('#vendedor_id').chosen({
                                 allow_single_deselect: true
                             });
                             $('#ven_ufecha_prog').mask('99/99/9999');
                             $('#frm_sentencia').submit(function() {
                                 return false;
                             });
                             $('#btn_guardar').click(function() {
                                 //                var ufecha_valor_mysql=$('#ufecha_valor').val();
                                 //                var nfecha_valor_mysql=fecha_mysql($('#nfecha_valor').val());
                                 //                if(ufecha_valor_mysql>=nfecha_valor_mysql){
                                 //                    $.prompt('La Nueva fecha valor debe ser mayor a la Ultima Fecha Valor');
                                 //                    return false;
                                 //                }
                                 //                console.log('submit');
                                 var val_interes = $('#ven_val_interes').val();
                                 var ufecha_prog = $('#ven_ufecha_prog').val();
                                 if (val_interes === '' || ufecha_prog === '') {
                                     $.prompt('Ingrese correctamente el interes y la maxima fecha Programada');
                                     return false;
                                 }
                                 document.frm_sentencia.submit();
                             });
        </script>
        <?php
    }

}