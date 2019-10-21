<?php

class REP_SALDOS_VENTA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_SALDOS_VENTA() {
        //permisos
        $this->ele_id = 151;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_saldos_venta';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('SALDOS DE VENTAS');
        $this->usu = new USUARIO;
    }

    function monto_pagado($ven_id) {
        $objeto = FUNCIONES::objeto_bd_sql("select sum(vpag_monto) as monto from venta_pago where vpag_ven_id='$ven_id' and vpag_estado='Activo'");
        return $objeto;
    }

    function obtener_resultados() {
        $conec = new ADO();
        $hoy = date('Y-m-d');
        $info = array();
        $filtro = '';
        if ($_POST[urb_ids]) {
            $filtro .= " and urb_id in (" . implode(',', $_POST[urb_ids]) . ")";
            $info[] = array('label' => 'Urbanizacion', 'valor' => implode(',', $_POST[urb_nombres]));
        } else {
//            $une_ids=  FUNCIONES::usuario_une_ids($_SESSION[id]);
//            $and_une_id=" and urb_une_id=-1";
//            $str_une= "NINGUNO";
//            if($une_ids){
//                $and_une_id=" and urb_une_id in ($une_ids)";
//                $urbanizaciones=  FUNCIONES::lista_bd_sql("select * from urbanizacion where urb_une_id in ($une_ids)");
//                $str_une= "";
//                for($i=0;$i<count($urbanizaciones);$i++){
//                    if($i>0){
//                        $str_une .= ",";
//                    }
//                    $str_une .= $urbanizaciones[$i]->urb_nombre;
//                }
//            }
//            $filtro .= $and_une_id;
//            $info[]=array('label'=>'Urbanizacion','valor'=>$str_une);
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
        
        $sql = "SELECT 
                    urb_id,ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,ven_lot_id,
                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,
                    ven_cuota,int_nombre,int_apellido,ven_comision,
                    ven_vdo_id,ven_urb_id,ven_valor,ven_metro,ven_res_anticipo,
                    ven_val_interes,ven_superficie,ven_decuento,ven_lot_ids,ven_monto_intercambio,
                    ven_monto_efectivo,urb_nombre_corto,
                    ven_ufecha_pago,ven_ufecha_valor,ven_cuota_pag,ven_capital_pag,
                    ven_capital_desc,ven_capital_inc,ven_usaldo,
                    int_telefono,int_celular,int_direccion,ven_ubicacion,
                    $campos_aux
                    ifnull(sum(vpag_capital_inc),0) as incremento,
                    ifnull(sum(vpag_capital_desc),0) as descuento,
                    ifnull(sum(vpag_interes),0) as interes_pagado, 
                    ifnull(sum(vpag_capital),0) as capital_pagado, count(*) as cuotas_pagadas, 
                    max(vpag_fecha_pago) as ufecha_pago, max(vpag_fecha_valor) as ufecha_valor
                FROM 
                    venta
                    left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')                    
                    $join_aux
                    inner join interno on (ven_int_id=int_id)
                    inner join urbanizacion on (ven_urb_id=urb_id)
                    where 1 $filtro 
                    group by ven_id
                    order by ven_fecha asc";
        
//        echo "$sql;<br>";
        
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
        for ($i = 0; $i < $num; $i++) {
            $obj = $conec->get_objeto();

            if ($obj->ufecha_pago) {
                $ufecha_pago = FUNCIONES::get_fecha_latina($obj->ufecha_pago);
            } else {
                $ufecha_pago = FUNCIONES::get_fecha_latina($obj->ven_fecha);
            }
            //FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);
            if ($obj->ufecha_valor) {
                $ufecha_valor = FUNCIONES::get_fecha_latina($obj->ufecha_valor);
            } else {
                $ufecha_valor = FUNCIONES::get_fecha_latina($obj->ven_fecha);
            }


            $cliente = "$obj->int_nombre $obj->int_apellido";

            $fecha = FUNCIONES::get_fecha_latina($obj->ven_fecha);

            $str_moneda = 'Bolivianos';
            if ($obj->ven_moneda == '2') {
                $str_moneda = 'Dolares';
            }

            $arr_para_sumar_saldos = array('Pendiente', 'Sin_Operacion', 'Pagado');
            
//            $estado_calculado = ($fecha_limite >= $hoy) ? $obj->ven_estado: ($obj->estado_calculado == 'Sin_Operacion') ? $obj->ven_estado: $obj->estado_calculado;
            
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
            
            $capital_cuotas = $obj->capital_pagado;
            $interes_cuotas = $obj->interes_pagado;
            $cuota_ini = $obj->ven_monto_intercambio + $obj->ven_res_anticipo;
            $capital_pagado = $cuota_ini + $capital_cuotas;
            $monto_venta = $obj->ven_monto;
            
            $saldo_financiar = $obj->ven_monto_efectivo;
            
            if ($capital_cuotas < $saldo_financiar) {
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
            
//            $saldo_capital=$saldo_financiar+$obj->incremento*1-$obj->capital_pagado*1-$obj->descuento*1;
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
            $total->{"cap_descuento_$mon"} += $obj->descuento;
            $total->{"cap_incremento_$mon"} += $obj->incremento;
            $total->{"saldo_capital_$mon"} += $saldo_capital;
            $lot_ids = $obj->ven_lot_id;
            if ($obj->ven_lot_ids) {
                $lot_ids = $obj->ven_lot_ids;
            }
            $alotes = FUNCIONES::lista_bd_sql("select man_nro,lot_nro from manzano,lote where man_id=lot_man_id and lot_id in ($lot_ids)");
            $des_lotes = "";
            $j = 0;
            foreach ($alotes as $lot) {
                if ($j > 0) {
                    $des_lotes .= ", ";
                }
                $des_lotes .= "M{$lot->man_nro}L{$lot->lot_nro}";
                $j++;
            }
//            $head=array('#','Nro Venta','Cliente','Telef.','Direccion','Proyecto','Moneda','Lote','Estado','Fecha',
//                            'Superficie','Precio Prom','Valor Terreno','Descuento','Monto','Cuota Inicial',
//                            'Saldo Financiar','Interes','Plazo','Cuota','Nro. Cuota Pag.','Cap. Pagado','Cap. Desc','Cap. Inc.'
//                            ,'Saldo','U. Fecha Pag.','U. Fecha Valor');
            $result[] = array(
                $nro, $obj->ven_id, $cliente, "$obj->int_telefono/$obj->int_celular", $obj->int_direccion, $obj->urb_nombre_corto, $str_moneda, $des_lotes, $obj->ven_estado, $estado_calculado, $fecha,
                $obj->ven_superficie, $obj->ven_metro * 1, $obj->ven_valor, $obj->ven_decuento, $obj->ven_monto, $cuota_ini,
                $saldo_financiar, $obj->ven_val_interes * 1, $obj->ven_plazo, $obj->ven_cuota, $obj->cuotas_pagadas, $obj->interes_pagado, $obj->capital_pagado, $obj->descuento, $obj->incremento,
                $saldo_capital, $ufecha_pago, $ufecha_valor
            );
            $nro++;

            $conec->siguiente();
        }

        $tca = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$hoy' and tca_mon_id=2");
        $data = array(
            'type' => 'success',
            'titulo' => $this->formulario->titulo,
            'info' => $info,
            'modulo' => $this->modulo,
            'head' => $head,
            'result' => $result,
            'foot' => array(
                array(
                    array('texto' => 'Total en Bs.', 'attr' => 'colspan="11"'), $total->superficie, '', $total->valor_1 * 1,
                    $total->descuento_1 * 1, $total->monto_1 * 1, $total->cuota_ini_1 * 1, $total->saldo_financiar_1 * 1, '', '', '', '',
                    $total->interes_pagado_1 * 1, $total->capital_pagado_1 * 1, $total->cap_descuento_1 * 1, $total->cap_incremento_1 * 1, $total->saldo_capital_1 * 1,
                    '', ''
                ),
                array(
                    array('texto' => 'Total en $us.', 'attr' => 'colspan="11"'), 0, '', $total->valor_2 * 1,
                    $total->descuento_2 * 1, $total->monto_2 * 1, $total->cuota_ini_2 * 1, $total->saldo_financiar_2 * 1, '', '', '', '',
                    $total->interes_pagado_2 * 1, $total->capital_pagado_2 * 1, $total->cap_descuento_2 * 1, $total->cap_incremento_2 * 1, $total->saldo_capital_2 * 1,
                    '', ''
                ),
                array(
                    array('texto' => 'Convertido a Bs.', 'attr' => 'colspan="11"'), 0, '', $total->valor_1 + ($total->valor_2 * $tca),
                    $total->descuento_1 + ($total->descuento_2 * $tca), $total->monto_1 + ($total->monto_2 * $tca), $total->cuota_ini_1 + ($total->cuota_ini_2 * $tca), $total->saldo_financiar_1 + ($total->saldo_financiar_2 * $tca), '', '', '', '',
                    $total->interes_pagado_1 + ($total->interes_pagado_2 * $tca), $total->capital_pagado_1 + ($total->capital_pagado_2 * $tca), $total->cap_descuento_1 + ($total->cap_descuento_2 * $tca), $total->cap_incremento_1 + ($total->cap_incremento_2 * $tca), $total->saldo_capital_1 + ($total->saldo_capital_2 * $tca),
                    '', ''
                ),
                array(
                    array('texto' => 'Convertido a $us.', 'attr' => 'colspan="11"'), 0, '', $total->valor_2 + ($total->valor_1 / $tca),
                    $total->descuento_2 + ($total->descuento_1 / $tca), $total->monto_2 + ($total->monto_1 / $tca), $total->cuota_ini_2 + ($total->cuota_ini_1 / $tca), $total->saldo_financiar_2 + ($total->saldo_financiar_1 / $tca), '', '', '', '',
                    $total->interes_pagado_2 + ($total->interes_pagado_1 / $tca), $total->capital_pagado_2 + ($total->capital_pagado_1 / $tca), $total->cap_descuento_2 + ($total->cap_descuento_1 / $tca), $total->cap_incremento_2 + ($total->cap_incremento_1 / $tca), $total->saldo_capital_2 + ($total->saldo_capital_1 / $tca),
                    '', ''
                ),
            ),
        );
        return $data;
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

    function formulario() {
        $this->formulario->dibujar_cabecera();
        if ($this->mensaje <> "") {
            ?>
            <table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #DD3C10; color:#DD3C10;">
                <tr bgcolor="#FFEBE8">
                    <td align="center">
            <?php
            echo $this->mensaje;
            ?>
                    </td>
                </tr>
            </table>
                    <?php } ?>
        <script>

        </script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <script src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <div class="Subtitulo">Filtro del Reporte</div>
                    <div id="ContenedorSeleccion">
                        <input type="hidden" id="dif_dias" value="<?php echo FUNCIONES::get_usuario_rango_fecha(); ?>">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizacion</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto">
                                    <option value="">-- Seleccione --</option>
        <?php
//                                        $une_ids=  FUNCIONES::usuario_une_ids($_SESSION[id]);
//                                        $filtro_une=" and urb_une_id=-1";
//                                        if($une_ids){
//                                            $filtro_une=" and urb_une_id in ($une_ids)";
//                                        }
        $fun = NEW FUNCIONES;
//                                        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where 1 $filtro_une", $_POST['ven_urb_id']);
        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where 1 ", $_POST['ven_urb_id']);
        ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >&nbsp;</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_urbanizacion" class="tab_lista_cuentas">
                                        <tbody>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            $('#ven_urb_id').change(function () {
        //                                    console.log($(this).val());
                                var id = trim($('#ven_urb_id option:selected').val());
                                var valor = trim($('#ven_urb_id option:selected').text());
                                agregar_cuenta({id: id, valor: valor}, 'urbanizacion');
                                $(this).val('');
        //                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
        //                                    $('#cja_cue_id').trigger('chosen:updated');
                            });

                            function agregar_cuenta(objeto, input) {

                                if (!$("#tab_" + input + ' .urb_ids[value=' + objeto.id + ']').length) {
                                    var fila = '';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" class="urb_ids" name="urb_ids[]" value="' + objeto.id + '">';
                                    fila += '       <input type="hidden" class="urb_nombres" name="urb_nombres[]" value="' + objeto.valor + '">';
                                    fila += '       ' + objeto.valor;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $("#tab_" + input + ' tbody').append(fila);
                                }
                            }
                            $(".img_del_cuenta").live('click', function () {
                                $(this).parent().parent().remove();
                            });
                        </script>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha Inicio</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha Fin</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Hasta la Fecha</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="flimite" id="flimite" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Tipo</div>
                            <div id="CajaInput">
                                <div id="manzano">
                                    <select style="width:200px;" name="tipo" class="caja_texto">
                                        <option value="">Todos</option>
                                        <option value="Contado">Contado</option>
                                        <option value="Credito">Credito</option>

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Estado</div>
                            <div id="CajaInput">
                                <div id="manzano">
                                    <select style="width:200px;" name="estado" class="caja_texto">
                                        <option value="">Todos</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Pagado">Pagado</option>
                                        <option value="Pendiente_Pagado">Pendiente - Pagado</option>
                                        <option value="Retenido">Retenido</option>
                                        <option value="Anulado">Anulado</option>
                                        <option value="Cambiado">Cambiado</option>
                                        <option value="Fusionado">Fusionado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Mostrar</div>
                            <div id="CajaInput">
                                <div id="manzano">
                                    <input type="hidden" name="frame" id="frame" value="">
                                    <select style="width:200px;" name="imprimir" id="imprimir"class="caja_texto">
                                        <option value="">Pagina</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="hidden" class="boton" name="formu" value="ok">
                                <input type="button" class="boton" name="" onclick="javascript:enviar_formulario();" value="Generar Reporte">
                            </center>
                        </div>
                    </div>
                </div>
            </form>	
        </div>
        <script>

            $('#ven_vdo_id').chosen({
                allow_single_deselect: true
            }).change(function () {

            })
                    ;

            jQuery(function ($) {
                $("#inicio").mask("99/99/9999");
                $("#fin").mask("99/99/9999");
                $("#flimite").mask("99/99/9999");
            });

            $('#imprimir').change(function () {
                var val = $(this).val();
                if (val === 'excel') {
                    $('#frame').val('false');
                } else {
                    $('#frame').val('');
                }
            });

            function enviar_formulario() {
                var dif_dias = $('#dif_dias').val() * 1;
                if (dif_dias !== -1) {
                    var fecha_ini = fecha_mysql($('#inicio').val());
                    var fecha_fin = fecha_mysql($('#fin').val());
                    var _dif_dias = diferencia_dias(fecha_ini, fecha_fin);
                    if (_dif_dias > dif_dias) {
                        $.prompt('Usted no tiene permiso para sacar un Reporte mas de ' + dif_dias + ' dias');
                        return false;
                    }
                }
                document.frm_sentencia.submit();
            }
        </script>
        <?php
    }

}
?>