<?php

class REP_VENTA_NOTA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_VENTA_NOTA() {
        //permisos
        $this->ele_id = 204;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_venta_nota';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('NOTAS DE VENTAS');
        $this->usu = new USUARIO;
    }
    
    function obtener_resultados() {
        $conec = new ADO();
        $info = array();
        $filtro = '';
        if($_POST[urb_ids]){
            $filtro .= " and ven_urb_id in (".  implode(',', $_POST[urb_ids]).")";
            $info[]=array('label'=>'Urbanizacion','valor'=>implode(',', $_POST[urb_nombres]));
        }
        
        if ($_POST['inicio'] <> "") {
            $filtro.=" and vnot_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $info[]=array('label'=>'Fecha Inicio','valor'=> $_POST['inicio']);
        } 
        if ($_POST['fin'] <> "") {
            $filtro.=" and vnot_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fin']) . "' ";
            $info[]=array('label'=>'Fecha Fin','valor'=> $_POST['fin']);
        }
        
        if ($_POST[ven_suc_id]){
            $filtro .= " and ven_suc_id='$_POST[ven_suc_id]'";
            $info[]=array('label'=>'Sucursal','valor'=> FUNCIONES::atributo_bd_sql("select suc_nombre as campo where suc_id='$_POST[ven_suc_id]'"));
        }

        if ($_POST['tipo'] <> ""){
            $filtro .= " and ven_tipo='" . $_POST['tipo'] . "'";
            $info[]=array('label'=>'Tipo','valor'=> $_POST['tipo']);
        }

        if ($_POST['estado'] <> "") {
            if ($_POST['estado'] == 'Pendiente_Pagado'){
                $filtro .= " and (ven_estado ='Pendiente' or ven_estado ='Pagado')";
                $info[]=array('label'=>'Estado','va lor'=> 'Pendiente o Pagado');
            }
            else{
                $filtro .= " and ven_estado='" . $_POST['estado'] . "'";
                $info[]=array('label'=>'Estado','valor'=> $_POST['estado']);
            }
        }

        $sql = "select * 
                from venta , venta_nota,interno 
                where vnot_ven_id=ven_id and ven_int_id=int_id
                $filtro
                order by ven_fecha asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $head=array('#','Nro Venta','Cliente','Proyecto','Concepto','Estado','Fecha Venta','Moneda',
                    'Observacion','Fecha','Hora','Usuario',
                    'U. Fecha Pag.','U. Fecha Valor','Est. Cartera');
        $result=array();
        $nro=1;
        $total=new stdClass();
        $estados_carteras=$_POST[estados_carteras];
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$obj->ven_id order by vpag_id desc limit 1");
            if(!$upago){
                $upago=new stdClass();
                $upago->vpag_saldo_final=$obj->ven_monto_efectivo;
                $upago->vpag_fecha_valor=$obj->ven_fecha;
                $upago->vpag_fecha_pago=$obj->ven_fecha;
            }
            
            
            $ufecha_pago=  FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);
            $ufecha_valor=  FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);
            $dif_dias=  FUNCIONES::diferencia_dias($upago->vpag_fecha_valor, date('Y-m-d'));
            $est_cartera= FUNCIONES::estado_cartera($dif_dias);// 'Vigente';

            if($estados_carteras && !in_array($est_cartera, $estados_carteras)){
                $conec->siguiente();
                continue;
            }
            
//            $comision = FUNCIONES::objeto_bd_sql("select * from comision,vendedor,interno where com_vdo_id=vdo_id and vdo_int_id=int_id and com_ven_id='$obj->ven_id' and com_vdo_id='$obj->ven_vdo_id'");
                        //            ('#','Vendedor','Nro Venta','Valor Terreno','Proyecto','UV','MZ','Lote','Cliente','Fecha','Precio M2','Cuota Inicial','Capital','Cuotas','Interes','Formulario','Cuota','Total Pagar','Pagado','Saldo','Superficie','Descuento'),
            $cliente="$obj->int_nombre $obj->int_apellido";
//            $vendedor="$comision->int_nombre $comision->int_apellido";
            $fecha=  FUNCIONES::get_fecha_latina($obj->ven_fecha);
//            $head=array('#','Nro Venta','Cliente','Proyecto','Descripcion','Estado','Fecha Venta','Moneda',
//                    'Observacion','Fecha','Hora','Usuario',
//                    'U. Fecha Pag.','U. Fecha Valor','Est. Cartera');
            $fecha_cre=  explode(' ', $obj->vnot_fecha_cre);
            $_fecha=  FUNCIONES::get_fecha_latina($fecha_cre[0]);
            $_hora=  $fecha_cre[1];
            $result[]=array(
                $nro,$obj->ven_id,$cliente,$obj->urb_nombre_corto,$obj->ven_concepto,$obj->ven_estado,$fecha,$str_moneda,
                $obj->vnot_observacion,$_fecha,$_hora,$obj->vnot_usu_cre,
                $ufecha_pago,$ufecha_valor,$est_cartera
            );
            $nro++;
            $conec->siguiente();
        }

//        $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='".date('Y-m-d')."' and tca_mon_id=2");
        $data=array(
            'type'=>'success',
            'titulo'=>$this->formulario->titulo,
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>$head,
            'result'=>$result,
            'foot'=>null
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
                                <div class="Etiqueta" ><span class="flechas1">*</span>Estado Cartera</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="estado_cartera" id="estado_cartera" class="caja_texto">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Vigente">Vigente</option>
                                        <option value="Vencido">Vencido</option>
                                        <option value="Mora">Mora</option>
                                        <option value="Ejecucion">Ejecucion</option>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >&nbsp;</div>
                                <div id="CajaInput">
                                    <div class="box_lista_cuenta"> 
                                        <table id="tab_cartera" class="tab_lista_cuentas">
                                            <tbody>
                                            </tbody>
                                        </table>                                    
                                    </div>
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
                        var dif_dias=$('#dif_dias').val()*1;
                        if(dif_dias!==-1){
                            var fecha_ini=fecha_mysql($('#inicio').val());
                            var fecha_fin=fecha_mysql($('#fin').val());
                            var _dif_dias=diferencia_dias(fecha_ini,fecha_fin);
                            if(_dif_dias>dif_dias){
                                $.prompt('Usted no tiene permiso para sacar un Reporte mas de '+dif_dias+' dias');
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