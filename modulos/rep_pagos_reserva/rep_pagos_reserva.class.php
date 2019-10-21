<?php

class REP_PAGOS_RESERVA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_PAGOS_RESERVA() {
        //permisos
        $this->ele_id = 207;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_pagos_reserva';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('PAGOS DE RESERVA');
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
            $filtro.=" and respag_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $info[]=array('label'=>'Fecha Inicio','valor'=> $_POST['inicio']);
        } 
        if ($_POST['fin'] <> "") {
            $filtro.=" and respag_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fin']) . "' ";
            $info[]=array('label'=>'Fecha Fin','valor'=> $_POST['fin']);
        }

        if ($_POST[ven_suc_id]){
            $filtro .= " and respag_suc_id='$_POST[ven_suc_id]'";
            $info[]=array('label'=>'Sucursal','valor'=> FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$_POST[ven_suc_id]'"));
        }

        $sql = "SELECT 
                    respag_id,res_id,int_nombre,int_apellido,int_telefono,int_celular,
                    int_direccion,urb_nombre_corto,uv_nombre,man_nro,lot_nro,respag_fecha,
                    respag_moneda,respag_monto,respag_usu_id,respag_recibo,suc_nombre
                FROM 
                    reserva_pago
                    inner join reserva_terreno on (res_id=respag_res_id)
                    inner join ter_sucursal on (respag_suc_id=suc_id)
                    inner join interno on (res_int_id=int_id)    
                    inner join lote on (res_lot_id=lot_id)
                    inner join manzano on (lot_man_id=man_id)
                    inner join urbanizacion on (man_urb_id=urb_id)
                    inner join uv on (lot_uv_id=uv_id)
                where respag_monto>0 $filtro
                order by respag_fecha asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $total=new stdClass();
//        $estados_carteras=$_POST[estados_carteras];
        $head=array('#','Nro Reserva','Cliente','Telef.','Direccion','Proyecto','UV','MZ','Lote',
                    'F. Pago','Usu. Cobro','Sucursal','Recibo','Moneda','Monto Pag.','Forma. Pago'
                    );
        
//        $fecha_act=date('Y-m-d');
        
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();                
            
            $cliente="$obj->int_nombre $obj->int_apellido";

            $fecha=  FUNCIONES::get_fecha_latina($obj->respag_fecha);
            $str_moneda='Bolivianos';
            if($obj->respag_moneda=='2'){
                $str_moneda='Dolares';
            }
            $mon=$obj->respag_moneda;
            
            $total->{"monto_$mon"}+=$obj->respag_monto;
//            FUNCIONES::print_pre($obj);
            $pagos=  FUNCIONES::lista_bd_sql("select * from con_pago where fpag_tabla='reserva_pago' and fpag_tabla_id=$obj->respag_id and fpag_estado='Activo'");
            $afpagos=array();
            for ($j = 0; $j < count($pagos); $j++) {
                $pag=$pagos[$j];
                if(!in_array($pag->fpag_forma_pago,$afpagos)){
                    $afpagos[]=$pag->fpag_forma_pago;
                }
            }
            $str_fpagos=  implode(',', $afpagos);
//            ('#','Nro Reserva','Cliente','Telef.','Direccion','Proyecto','UV','MZ','Lote',
//            'F. Pago','Usu. Cobro','Sucursal','Recibo','Moneda','Monto Pag.','Forma. Pago'
//            );
            $result[]=array(
                $nro,$obj->res_id,$cliente,"$obj->int_telefono/$obj->int_celular",$obj->int_direccion,$obj->urb_nombre_corto,$obj->uv_nombre,$obj->man_nro,$obj->lot_nro,
                $fecha,$obj->respag_usu_id,$obj->suc_nombre,$obj->respag_recibo,$str_moneda,$obj->respag_monto,$str_fpagos
            );
            $nro++;
            $conec->siguiente();
        }

        $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='".date('Y-m-d')."' and tca_mon_id=2");

        $data=array(
            'type'=>'success',
            'titulo'=>$this->formulario->titulo,
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>$head,
            'result'=>$result,
            'foot'=>array(
                        array(
                                array('texto'=>'Total en Bs.','attr'=>'colspan="14"'),
                                $total->monto_1*1,''
                            ),
                        array(
                                array('texto'=>'Total en $us.','attr'=>'colspan="14"'),
                                $total->monto_2*1,''
                            ),
                        array(
                                array('texto'=>'Convertido a Bs.','attr'=>'colspan="14"'),
                                $total->monto_1+($total->monto_2*$tca),''
                                
                            ),
                        array(
                                array('texto'=>'Convertido a $us.','attr'=>'colspan="14"'),
                                $total->monto_2+($total->monto_1/$tca),''
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
                                <div class="Etiqueta" >Urbanizacion</div>
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
                                <div class="Etiqueta" >Sucursal</div>
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