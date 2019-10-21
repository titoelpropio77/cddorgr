<?php

class REPRESUMEN_VENTA_INFORME extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPRESUMEN_VENTA_INFORME() {
        //permisos
        $this->ele_id = 203;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'represumen_venta_informe';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('INFORME DE VENTAS');
        $this->usu = new USUARIO;
    }

    
    function monto_pagado($ven_id) {
        $objeto=  FUNCIONES::objeto_bd_sql("select sum(vpag_monto) as monto from venta_pago where vpag_ven_id='$ven_id' and vpag_estado='Activo'");
        return $objeto;
    }
    
    function obtener_resultados() {
        $conec = new ADO();
        $info = array();
        $filtro = '';
        if($_POST[urb_ids]){
            $filtro .= " and urb_id in (".  implode(',', $_POST[urb_ids]).")";
            $info[]=array('label'=>'Urbanizacion','valor'=>implode(',', $_POST[urb_nombres]));
        }
        
        if ($_POST['inicio'] <> "") {
            $filtro.=" and ven_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $info[]=array('label'=>'Fecha Inicio','valor'=> $_POST['inicio']);
        } 
        if ($_POST['fin'] <> "") {
            $filtro.=" and ven_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fin']) . "' ";
            $info[]=array('label'=>'Fecha Fin','valor'=> $_POST['fin']);
        }
        
        if ($_POST[ven_vdo_id]){
            $filtro .= " and ven_vdo_id='$_POST[ven_vdo_id]'";
            $info[]=array('label'=>'Vendedor','valor'=>  FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from vendedor,interno where vdo_id=$_POST[ven_vdo_id] and vdo_estado='Habilitado' and vdo_int_id=int_id "));
        }
        if ($_POST[ven_suc_id]){
            $filtro .= " and ven_suc_id='$_POST[ven_suc_id]'";
            $info[]=array('label'=>'Sucursal','valor'=> FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$_POST[ven_suc_id]'"));
        }

        if ($_POST['tipo'] <> ""){
            $filtro .= " and ven_tipo='" . $_POST['tipo'] . "'";
            $info[]=array('label'=>'Tipo','valor'=> $_POST['tipo']);
        }

        if ($_POST['estado'] <> "") {
            if ($_POST['estado'] == 'Pendiente_Pagado'){
                $filtro .= " and (ven_estado ='Pendiente' or ven_estado ='Pagado')";
                $info[]=array('label'=>'Estado','valor'=> 'Pendiente o Pagado');
            }
            else{
                $filtro .= " and ven_estado='" . $_POST['estado'] . "'";
                $info[]=array('label'=>'Estado','valor'=> $_POST['estado']);
            }
        }
        
        if ($_POST['ven_multinivel'] <> ""){
            $filtro .= " and ven_multinivel='" . $_POST['ven_multinivel'] . "'";
            $modalidad= ($_POST['ven_multinivel'] == 'si')?'Multinivel':'Tradicional';
            $info[]=array('label'=>'Modalidad Venta','valor'=> $modalidad);
        }

        $sql = "SELECT 
                urb_id,ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,
                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,
                    ven_cuota,int_nombre,int_apellido,man_nro,lot_nro,ven_comision,
                    cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,
                    suc_nombre,ven_vdo_id,ven_urb_id,uv_nombre,ven_valor,ven_metro,ven_res_anticipo,
                    ven_val_interes,ven_superficie,ven_decuento,ven_lot_ids,ven_monto_intercambio,ven_monto_efectivo,urb_nombre_corto,ven_multinivel,ven_form
                FROM 
                venta
                inner join ter_sucursal on (ven_suc_id=suc_id)
                inner join interno on (ven_int_id=int_id)
                inner join lote on (ven_lot_id=lot_id)
                inner join manzano on (lot_man_id=man_id)
                inner join urbanizacion on (man_urb_id=urb_id)
                inner join uv on (lot_uv_id=uv_id)
                where 1 $filtro
                order by ven_fecha asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $total=new stdClass();
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            
//            $comision = FUNCIONES::objeto_bd_sql("select * from comision,vendedor,interno where com_vdo_id=vdo_id and vdo_int_id=int_id and com_ven_id='$obj->ven_id' and com_vdo_id='$obj->ven_vdo_id'");
                        //            ('#','Vendedor','Nro Venta','Valor Terreno','Proyecto','UV','MZ','Lote','Cliente','Fecha','Precio M2','Cuota Inicial','Capital','Cuotas','Interes','Formulario','Cuota','Total Pagar','Pagado','Saldo','Superficie','Descuento'),
            $comision = FUNCIONES::objeto_bd_sql("select * from vendedor,interno where vdo_int_id=int_id and vdo_id='$obj->ven_vdo_id'");
            $cliente="$obj->int_nombre $obj->int_apellido";
            $vendedor="$comision->int_nombre $comision->int_apellido";
            $fecha=  FUNCIONES::get_fecha_latina($obj->ven_fecha);
            $form= $obj->ven_form *$obj->ven_plazo;
            $total_pagar=$form+($obj->ven_cuota*$obj->ven_plazo);
            $pagado=  $this->monto_pagado($obj->ven_id);
            $total_pagado=$pagado->monto;//$form+($obj->ven_cuota*$obj->ven_plazo);
            $saldo_pagar=$total_pagar-$total_pagado;
            $str_moneda='Bolivianos';
            if($obj->ven_moneda=='2'){
                $str_moneda='Dolares';
            }
            $saldo_financiar=$obj->ven_monto_efectivo;
            $cuota_ini=$obj->ven_monto_intercambio+$obj->ven_res_anticipo;
            $_total_mpagar=$cuota_ini+$total_pagar;
            $mon=$obj->ven_moneda;
            $total->{"valor_$mon"}+=$obj->ven_valor;
            $total->{"cuota_ini_$mon"}+=$cuota_ini;
            $total->{"saldo_financiar_$mon"}+=$saldo_financiar;
            $total->{"total_pagar_$mon"}+=$_total_mpagar;
            $total->{"total_pagado_$mon"}+=$total_pagado;
            $total->{"total_saldo_$mon"}+=$saldo_pagar;
            $total->superficie+=$obj->ven_superficie;
            $total->{"descuento_$mon"}+=$obj->ven_decuento;
            
            $modalidad= ($obj->ven_multinivel == 'si')?'Multinivel':'Tradicional';
            
            if(!$obj->ven_lot_ids){
                $result[]=array(
                    $nro,$vendedor,$obj->ven_comision,$obj->suc_nombre,$obj->ven_id,$str_moneda,$obj->ven_estado,$obj->ven_valor,$obj->urb_nombre_corto,
                    $obj->uv_nombre,$obj->man_nro,$obj->lot_nro,$cliente,$fecha,$obj->ven_metro*1,$cuota_ini,$saldo_financiar,$obj->ven_plazo,$obj->ven_val_interes*1,
                    $form,$obj->ven_cuota,$_total_mpagar,$total_pagado,$saldo_pagar,$obj->ven_superficie,$obj->ven_decuento,$modalidad
                );
                $nro++;
            }else{
                $sql_sel="select * from venta_lote, lote,manzano,uv
                            where vlot_ven_id='$obj->ven_id' and vlot_lot_id=lot_id and lot_man_id=man_id and lot_uv_id=uv_id order by vlot_orden";

                $venta_lotes=FUNCIONES::lista_bd_sql($sql_sel);
                $num_lotes=  count($venta_lotes);
                $_saldo_financiar=$saldo_financiar;
                for ($j=0;$j<$num_lotes ; $j++) {
                    $vlot=$venta_lotes[$j];
                    $_anticipo=$cuota_ini;
                    if($_anticipo>$vlot->vlot_valor){
                        $_anticipo=$vlot->vlot_valor;
                        $_saldo_financiar=0;
                        $cuota_ini=$cuota_ini-$_anticipo;
                    }else{
                        $cuota_ini=$cuota_ini-$_anticipo;
                        $_saldo_financiar=$vlot->vlot_valor-$cuota_ini;
                    }

                    $ven_plazo=0;
                    $interes=0;
                    $ven_cuota=0;
                    $_form=0;
                    $_total_pagar=0;
                    $_total_pagado=0;
                    $_saldo_pagar=0;
                    $_descuento=0;
                    
                    if($j==$num_lotes-1){
                        $ven_plazo=$obj->ven_plazo;
                        $ven_cuota=$obj->ven_cuota;
                        $interes=$obj->ven_val_interes;
                        $_form=$form;
//                        $_total_pagar=$total_pagar;
                        $_total_pagar=$_total_mpagar;//$form+($obj->ven_cuota*$obj->ven_plazo);
                        $_total_pagado=$pagado->monto;
                        $_saldo_pagar=$_total_pagar-$_total_pagado;
                        $_descuento=$obj->ven_decuento;
                    }
                    
                    $result[]=array(
                        "$nro",$vendedor,$obj->ven_comision,$obj->suc_nombre,$obj->ven_id,$str_moneda,$obj->ven_estado,$vlot->vlot_valor,$vlot->urb_nombre_corto,
                        $vlot->uv_nombre,$vlot->man_nro,$vlot->lot_nro,$cliente,$fecha,$vlot->vlot_metro,$_anticipo,$_saldo_financiar,$ven_plazo,$interes*1,
                        $_form,$ven_cuota,$_total_pagar,$_total_pagado,$_saldo_pagar,$vlot->ven_superficie,$_descuento,$modalidad
                    );
                    $nro++;
                }
                
            }
            
            $conec->siguiente();
        }

        $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='".date('Y-m-d')."' and tca_mon_id=2");
        $data=array(
            'type'=>'success',
            'titulo'=>'INFORME DE VENTAS',
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>array('#','Vendedor','Comision','Sucursal','Nro Venta','Moneda','Estado','Valor Terreno','Proyecto',
                            'UV','MZ','Lote','Cliente','Fecha','Precio M2','Cuota Inicial','Capital','Cuotas','Interes',
                            'Formulario','Cuota','Total Pagar','Pagado','Saldo','Superficie','Descuento','Modalidad Venta'),
            'result'=>$result,
            'foot'=>array(
                        array(
                                array('texto'=>'Total en Bs.','attr'=>'colspan="7"'),$total->valor_1*1,array('texto'=>'','attr'=>'colspan="7"'),$total->cuota_ini_1*1,$total->saldo_financiar_1*1,
                                array('texto'=>'','attr'=>'colspan="4"'),$total->total_pagar_1*1,$total->total_pagado_1*1,$total->total_saldo_1*1,$total->superficie,$total->descuento_1*1
                            ),
                        array(
                                array('texto'=>'Total en $us.','attr'=>'colspan="7"'),$total->valor_2*1,array('texto'=>'','attr'=>'colspan="7"'),$total->cuota_ini_2*1,$total->saldo_financiar_2*1,
                                array('texto'=>'','attr'=>'colspan="4"'),$total->total_pagar_2*1,$total->total_pagado_2*1,$total->total_saldo_2*1,0,$total->descuento_2*1
                            ),
                        array(
                                array('texto'=>'Convertido a Bs.','attr'=>'colspan="7"'),($total->valor_2*$tca)+$total->valor_1,array('texto'=>'','attr'=>'colspan="7"'),($total->cuota_ini_2*$tca)+$total->cuota_ini_1,($total->saldo_financiar_2*$tca)+$total->saldo_financiar_1,
                                array('texto'=>'','attr'=>'colspan="4"'),($total->total_pagar_2*$tca)+$total->total_pagar_1,($total->total_pagado_2*$tca)+$total->total_pagado_1,($total->total_saldo_2*$tca)+$total->total_saldo_1,0,($total->descuento_2*$tca)+$total->descuento_1
                            ),
                        array(
                                array('texto'=>'Convertido a $us.','attr'=>'colspan="7"'),($total->valor_1/$tca)+$total->valor_2,array('texto'=>'','attr'=>'colspan="7"'),($total->cuota_ini_1/$tca)+$total->cuota_ini_2,($total->saldo_financiar_1/$tca)+$total->saldo_financiar_2,
                                array('texto'=>'','attr'=>'colspan="4"'),($total->total_pagar_1/$tca)+$total->total_pagar_2,($total->total_pagado_1/$tca)+$total->total_pagado_2,($total->total_saldo_1/$tca)+$total->total_saldo_2,0,($total->descuento_1/$tca)+$total->descuento_2
                            ),
            ),
            
        );
        return $data;
    }
    
    
    function procesar_reporte() {
        if($_POST){
            $data=  $this->obtener_resultados();
            if($data[type]=='success'){
                if($_POST[imprimir]=='excel'){
                    REPORTE::excel($data);
                }else{
                    REPORTE::html($data);
                }
            }else{
                echo $data->msj;
            }
                
        }else{
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
            function enviar_formulario() {
                document.frm_sentencia.submit();
            }
        </script>
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <script src="js/chosen.jquery.min.js"></script>
            <link href="css/chosen.min.css" rel="stylesheet"/>
            <script src="js/util.js"> </script>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo;?>" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizacion</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto">
                                        <option value="">-- Seleccione --</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
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
                                $('#ven_urb_id').change(function(){
                                    console.log($(this).val());
                                    var id=trim($('#ven_urb_id option:selected').val());
                                    var valor=trim($('#ven_urb_id option:selected').text());
                                    agregar_cuenta({id:id, valor:valor},'urbanizacion');
                                    $(this).val('');
                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
                                    $('#cja_cue_id').trigger('chosen:updated');
                                });
                                
                                function agregar_cuenta(objeto,input) {
                                    
                                    if (!$("#tab_"+input+' .urb_ids[value='+objeto.id+']').length) {
                                        var fila='';
                                        fila += '<tr>';
                                        fila += '   <td>';
                                        fila += '       <input type="hidden" class="urb_ids" name="urb_ids[]" value="'+objeto.id+'">';
                                        fila += '       <input type="hidden" class="urb_nombres" name="urb_nombres[]" value="'+objeto.valor+'">';
                                        fila += '       ' + objeto.valor;
                                        fila += '   </td>';
                                        fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                        fila += '</tr>';
                                        $("#tab_"+input+' tbody').append(fila);
                                    }
                                }
                                $(".img_del_cuenta").live('click', function() {
                                    $(this).parent().parent().remove();
                                });
                            </script>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d-m-Y'); ?>" type="text">
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo date('d-m-Y'); ?>" type="text">
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
                                <div class="Etiqueta" ><span class="flechas1">*</span>Vendedor</div>
                                <div id="CajaInput">
                                    <select style="width:400px;" name="ven_vdo_id" id="ven_vdo_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                        <option value=""></option>
                                        <?php
                                        $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor,interno where vdo_estado='Habilitado' and vdo_int_id=int_id;", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Sucursal</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_suc_id" id="ven_suc_id" class="caja_texto" >
                                        <option value="">-- Seleccione --</option>
                                        <?php
                                        $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal", 0);
                                        ?>
                                    </select>
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
                                <div class="Etiqueta" >Modalidad Venta</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        
                                        <select style="width:200px;" name="ven_multinivel" id="ven_multinivel"class="caja_texto">
                                            <option value="">-- Seleccione--</option>
                                            <option value="no">Tradicional</option>
                                            <option value="si">Multinivel</option>
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
                    $('#imprimir').change(function(){
                        var val=$(this).val();
                        if(val==='excel'){
                            $('#frame').val('false');
                        }else{
                            $('#frame').val('');
                        }
                    });
                    $('#ven_vdo_id').chosen({
                        allow_single_deselect: true
                    }).change(function(){
                                    
                        })
                    ;

                    jQuery(function($) {
                        $("#inicio").mask("99/99/9999");
                        $("#fin").mask("99/99/9999");
                    });
                    </script>
                    <?php

        } 

}
        ?>