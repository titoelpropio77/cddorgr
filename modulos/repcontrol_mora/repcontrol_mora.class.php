<?php
ini_set('display_erros', 'On');
class REPCONTROL_MORA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPCONTROL_MORA() {
        //permisos
        $this->ele_id = 152;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'repcontrol_mora';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('CONTROL DE MORA');
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
//            $filtro.=" and ven_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $fecha_inicio=FUNCIONES::get_fecha_mysql($_POST['inicio']);
            $filtro.=" and ven_sfecha_prog <='$fecha_inicio' and ven_sfecha_prog!='0000-00-00'";
            $filtro.=" and ind_fecha_programada<='$fecha_inicio'";
            $info[]=array('label'=>'Fecha Inicio','valor'=> $_POST['inicio']);
            
        } 

        $promotor = '';
        if ($_POST[ven_vdo_id] != '') {
            $filtro .= " and ven_vdo_id='$_POST[ven_vdo_id]'";
            $promotor = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido)as campo from interno
                inner join vendedor on (int_id=vdo_int_id)
                where vdo_id='$_POST[ven_vdo_id]'");
            $info[]=array('label'=>'Promotor','valor'=> $promotor);
        }
        
        if ($_POST[multinivel] != '') {
            $filtro .= " and ven_multinivel='$_POST[multinivel]'";
            $modalidad = ($_POST[multinivel] == 'si')?"Multinivel":"Tradicional";
            $info[]=array('label'=>'Modalidad','valor'=> $modalidad);
        }
        
        if ($_POST[ven_suc_id]){
            $filtro .= " and ven_suc_id='$_POST[ven_suc_id]'";
            $info[]=array('label'=>'Sucursal','valor'=> FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$_POST[ven_suc_id]'"));
        }
        $having="";
        if($_POST[nro_cuotas]*1>0){
            $op_rel=$_POST[op_rel];
            $having = "  having(count(*){$op_rel}{$_POST[nro_cuotas]})";
        }

        $filtro .= " and ven_estado='Pendiente' and ven_tipo='Credito'";
        
        $fecha_act=date('Y-m-d');
        
        $sql = "SELECT 
                urb_id,ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,
                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,
                    ven_cuota,int_nombre,int_apellido,man_nro,lot_nro,ven_comision,
                    cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,
                    suc_nombre,ven_vdo_id,ven_urb_id,uv_nombre,ven_valor,ven_metro,ven_res_anticipo,
                    ven_val_interes,ven_superficie,ven_decuento,ven_lot_ids,ven_monto_intercambio,
                    ven_monto_efectivo,urb_nombre_corto,
                    ven_ufecha_pago,ven_ufecha_valor,ven_cuota_pag,ven_capital_pag,
                    ven_capital_desc,ven_capital_inc,ven_usaldo,ven_multinivel,
                    int_telefono,int_celular,int_direccion,ven_ubicacion,ven_sfecha_prog, urb_val_form,
                    sum(ind_capital-ind_capital_pagado) as capital_pendiente,count(*) as cuotas_pendientes
                FROM 
                venta
                inner join ter_sucursal on (ven_suc_id=suc_id)
                inner join interno on (ven_int_id=int_id)
                inner join lote on (ven_lot_id=lot_id)
                inner join manzano on (lot_man_id=man_id)
                inner join urbanizacion on (man_urb_id=urb_id)
                inner join uv on (lot_uv_id=uv_id)
                inner join interno_deuda on (ind_tabla='venta' and ind_tabla_id=ven_id)
                where ven_estado='Pendiente' and ven_usaldo>0 $filtro
                and ind_estado='Pendiente' and ind_capital_pagado < ind_capital and ind_tipo='pcuota'
                group by ven_id
                $having
                order by ven_sfecha_prog asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);
        

        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $total=new stdClass();
//        $estados_carteras=$_POST[estados_carteras];
        $head=array('#','Nro Venta','Cliente','Telef.','Direccion','Ubicacion','Promotor','Modalidad','Proyecto','Moneda','UV','MZ','Lote',
                    'Cuotas Mora','Capitales Mora','U. Fecha Pago','Dias Interes','Interes','Formulario','Total a Pagar','Saldo Capital'
                    );
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            $ufecha_pago=  FUNCIONES::get_fecha_latina($obj->ven_ufecha_pago);//FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);
            
            $cliente="$obj->int_nombre $obj->int_apellido";
//            $vendedor="$comision->int_nombre $comision->int_apellido";
            
            if ($_POST[ven_vdo_id] == '') {
                $promotor = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido)as campo from interno
                inner join vendedor on (int_id=vdo_int_id)
                where vdo_id='$obj->ven_vdo_id'");
            }                        
            
            $modalidad = ($obj->ven_multinivel == 'si')?"Multinivel":"Tradicional";
            
            $str_moneda='Bolivianos';
            if($obj->ven_moneda=='2'){
                $str_moneda='Dolares';
            }
            
            $interes_dia=(($obj->ven_val_interes/360)/100)*$obj->ven_usaldo;
            $ndias=  FUNCIONES::diferencia_dias($obj->ven_ufecha_valor,$fecha_inicio);
            $interes=round($interes_dia*$ndias,2);
            $form=$obj->urb_val_form;


            $capital=$obj->capital_pendiente;

            
            $monto=round($interes+$form+$capital,2);

            $saldo_financiar=$obj->ven_monto_efectivo;
            
            $cuota_ini=$obj->ven_monto_intercambio+$obj->ven_res_anticipo;
            $mon=$obj->ven_moneda;

            $ultimo_pago = FUNCIONES::objeto_bd_sql("select *
            from venta_pago where vpag_estado='Activo'
            and vpag_ven_id='$obj->ven_id' order by vpag_fecha_pago desc limit 0,1");
            
            $saldo_capital = $obj->ven_monto_efectivo;
            if ($ultimo_pago) {
                $saldo_capital = $ultimo_pago->vpag_saldo_final;
            }
            
            $total->{"capital_$mon"}+=$capital;            
            $total->{"interes_$mon"}+=$interes;
            $total->{"form_$mon"}+=$form;
            $total->{"monto_$mon"}+=$monto;
            $total->{"saldo_capital_$mon"} += $saldo_capital;
            //            'Interes Pagar','Form. Pagar','Capital Pagar','Monto Pagar'
            //            
                                                
            if(!$obj->ven_lot_ids){
                $result[]=array(
                    $nro,$obj->ven_id,$cliente,"$obj->int_telefono/$obj->int_celular",
                    $obj->int_direccion,$obj->ven_ubicacion,$promotor,$modalidad,
                    $obj->urb_nombre_corto,$str_moneda,$obj->uv_nombre,$obj->man_nro,$obj->lot_nro,
                    $obj->cuotas_pendientes,$capital,$ufecha_pago,$ndias,$interes,$form,$monto,$saldo_capital
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
                    
                    $t_interes=0;
                    $t_form=0;
                    $t_capital=0;
                    $t_monto=0;
                    $t_ndias=0;
                    $t_ncuotas=0;

                    if($j==$num_lotes-1){
                        
                        $t_interes=$interes;
                        $t_form=$form;
                        $t_capital=$obj->capital_pendiente;
                        $t_monto=$monto;
                        $t_ndias=$ndias;
                        $t_ncuotas=$obj->cuotas_pendientes;
                        
                    }
                    $result[]=array(    
                        $nro,$obj->ven_id,$cliente,"$obj->int_telefono/$obj->int_celular",$obj->int_direccion,$obj->ven_ubicacion,$promotor,$modalidad,$obj->urb_nombre_corto,$str_moneda,$obj->uv_nombre,$obj->man_nro,$obj->lot_nro,
                        $t_ncuotas,$t_capital,$ufecha_pago,$t_ndias,$t_interes,$t_form,$t_monto,$saldo_capital
                    );
                    $nro++;
                }
                
            }
            $conec->siguiente();
        }

        $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='".date('Y-m-d')."' and tca_mon_id=2");
        //            $head=array('#','Nro Venta','Cliente','Telef.','Direccion','Ubicacion','Proyecto','Moneda','UV','MZ','Lote',
//                    'Cuotas Mora','Capitales Mora','U. Fecha Pago','Dias Interes','Interes','Formulario','Total a Pagar'
//                    );
        $data=array(
            'type'=>'success',
            'titulo'=>$this->formulario->titulo,
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>$head,
            'result'=>$result,
            'foot'=>array(
                        array(
                                array('texto'=>'Total en Bs.','attr'=>'colspan="14"'),$total->capital_1*1,'','',
                                $total->interes_1*1,$total->form_1*1,$total->monto_1*1,$total->saldo_capital_1*1
                            ),
                        array(
                                array('texto'=>'Total en $us.','attr'=>'colspan="14"'),$total->capital_2*1,'','',
                                $total->interes_2*1,$total->form_2*1,$total->monto_2*1,$total->saldo_capital_2*1
                            ),
                        array(
                                array('texto'=>'Convertido a Bs.','attr'=>'colspan="14"'),$total->capital_1+($total->capital_2*$tca),'','',
                                $total->interes_1+($total->interes_2*$tca),$total->form_1+($total->form_2*$tca),$total->monto_1+($total->monto_2*$tca),
                                $total->saldo_capital_1+($total->saldo_capital_2*$tca)
                            ),
                        array(
                                array('texto'=>'Convertido a $us.','attr'=>'colspan="14"'),$total->capital_2+($total->capital_1/$tca),'','',
                                $total->interes_2+($total->interes_1/$tca),$total->form_2+($total->form_1/$tca),$total->monto_2+($total->monto_1/$tca),
                                $total->saldo_capital_2+($total->saldo_capital_1/$tca)
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
                            <input type="hidden" id="dif_dias" value="<?php echo FUNCIONES::get_usuario_rango_fecha();?>">
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
//                                    console.log($(this).val());
                                    var id=trim($('#ven_urb_id option:selected').val());
                                    var valor=trim($('#ven_urb_id option:selected').text());
                                    agregar_cuenta({id:id, valor:valor},'urbanizacion');
                                    $(this).val('');
//                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
//                                    $('#cja_cue_id').trigger('chosen:updated');
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
                                <div class="Etiqueta" >Fecha</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
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
                            <script>
                                $('#estado_cartera').change(function(){
//                                    console.log($(this).val());
                                    var id=trim($('#estado_cartera option:selected').val());
                                    
                                    agregar_detalle_cartera({id:id},'cartera');
                                    $(this).val('');
//                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
//                                    $('#cja_cue_id').trigger('chosen:updated');
                                });
                                
                                function agregar_detalle_cartera(objeto,input) {
                                    if (!$("#tab_"+input+' .estados_carteras[value='+objeto.id+']').length) {
                                        var fila='';
                                        fila += '<tr>';
                                        fila += '   <td>';
                                        fila += '       <input type="hidden" class="estados_carteras" name="estados_carteras[]" value="'+objeto.id+'">';
                                        fila += '       ' + objeto.id;
                                        fila += '   </td>';
                                        fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                        fila += '</tr>';
                                        $("#tab_"+input+' tbody').append(fila);
                                    }
                                }
                            </script>
                            
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Modalidad Venta</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        
                                        <select style="width:200px;" name="multinivel" id="multinivel"class="caja_texto">
                                            <option value="">-- Seleccione--</option>
                                            <option value="no">Tradicional</option>
                                            <option value="si">Multinivel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="ContenedorDiv" class="div_vendedor">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="vendedor" id="vendedor" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre!='AFILIADOS';"; ?>
                                    <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                    <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                        <?php $objeto = $vendedores1->get_objeto(); ?>
                                        <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php $vendedores1->siguiente(); ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!--Inicio-->
                        <div id="ContenedorDiv" class="div_afiliado">
                            <div class="Etiqueta"><span class="flechas1">*</span>Patrocinador</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="afiliado" id="afiliado" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where vdo_estado='Habilitado'
                                               and ven_estado in ('Pendiente','Pagado')
                                               and vgru_nombre='AFILIADOS'"; ?>
                                    <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                    <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                        <?php $objeto = $vendedores1->get_objeto(); ?>
                                        <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php $vendedores1->siguiente(); ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->
                        <input type="hidden" name="ven_vdo_id" id="ven_vdo_id" value="" />
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Nro de Cuotas</div>
                                <div id="CajaInput">
                                    <select id="op_rel" name="op_rel" style="width: 50px">
                                        <option value="=">=</option>
                                        <option value=">=">>=</option>
                                        <option value="<="><=</option>
                                        <option value=">">></option>
                                        <option value="<"><</option>
                                    </select>
                                    <input class="caja_texto" name="nro_cuotas" id="nro_cuotas" size="12" value="" type="text">
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

                    $('#vendedor').chosen({
                        allow_single_deselect: true
                    }).change(function(){
                        $('#ven_vdo_id').val($('#vendedor option:selected').val());
                    });

                    $('#afiliado').chosen({
                        allow_single_deselect: true
                    }).change(function(){
                        $('#ven_vdo_id').val($('#afiliado option:selected').val());
                    });
                    
                    $('#multinivel').change(function() {
//                        var datos = $('#ven_lot_id option:selected').val();
//                        if (typeof datos !== 'undefined') {
//                            cargar_datos(datos);
//                        }
                        $('#ven_vdo_id').val('');
                        var op_mul = $('#multinivel option:selected').val();

                        if (op_mul === 'si') {
                            $('.div_vendedor').hide();
                            $('.div_afiliado').show();
                            $('#afiliado option[value=""]').attr('selected', true);
                        } else {
                            $('.div_afiliado').hide();
                            $('.div_vendedor').show();
                            $('#vendedor option[value=""]').attr('selected', true);
                        }
                    });
                    $('#multinivel option[value="no"]').attr('selected', true);
                    $('#multinivel').trigger('change');

//                    $('#ven_vdo_id').chosen({
//                        allow_single_deselect: true
//                    }).change(function(){
//                                    
//                        })
//                    ;

                    jQuery(function($) {
                        $("#inicio").mask("99/99/9999");
                        $("#fin").mask("99/99/9999");
                    });
                    
                    $('#imprimir').change(function(){
                        var val=$(this).val();
                        if(val==='excel'){
                            $('#frame').val('false');
                        }else{
                            $('#frame').val('');
                        }
                    });
                    
                    function enviar_formulario() {
                        if($('#inicio').val()===''){
                            $.prompt('Ingrese la Fecha');
                            return false;
                        }
//                        var dif_dias=$('#dif_dias').val()*1;
//                        if(dif_dias!==-1){
//                            var fecha_ini=fecha_mysql($('#inicio').val());
//                            var fecha_fin=fecha_mysql($('#fin').val());
//                            
//                            var _dif_dias=diferencia_dias(fecha_ini,fecha_fin);
//                            if(_dif_dias>dif_dias){
//                                $.prompt('Usted no tiene permiso para sacar un Reporte mas de '+dif_dias+' dias');
//                                return false;
//                            }
//                        }
                        document.frm_sentencia.submit();
                    }
                    </script>
                    <?php

        } 

}
