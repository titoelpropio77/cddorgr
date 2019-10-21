<?php

class REP_PROYECCION_ANUAL extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_PROYECCION_ANUAL() {
        $this->ele_id = 213;
        $this->busqueda();
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_proyeccion_anual';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('PROYECCION ANUAL');
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
            $filtro .= " and ven_urb_id in (".  implode(',', $_POST[urb_ids]).")";
            $info[]=array('label'=>'Urbanizacion','valor'=>implode(',', $_POST[urb_nombres]));
        }
        
        
        if ($_POST['inicio'] <> "") {
            $filtro.=" and ind_fecha_programada>= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $info[]=array('label'=>'Fecha Inicio','valor'=> $_POST['inicio']);
        } 
        if ($_POST['fin'] <> "") {
            $filtro.=" and ind_fecha_programada <='" . FUNCIONES::get_fecha_mysql($_POST['fin']) . "' ";
            $info[]=array('label'=>'Fecha Fin','valor'=> $_POST['fin']);
        }


        $sql = "select year(ind_fecha_programada) as anio, ven_urb_id , ind_moneda as moneda,sum(ind_monto) as monto,sum(ind_monto_pagado) as monto_pagado,count(*) as cantidad 
                from interno_deuda ,venta
                where 
                ind_tabla='venta' and ind_estado='Pendiente' $filtro
                and ven_id=ind_tabla_id
                group by year(ind_fecha_programada),ven_urb_id , ind_moneda
                order by anio asc,ven_urb_id, ind_estado asc, ind_moneda asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

//        $fecha_act=date('Y-m-d');
        $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='".date('Y-m-d')."' and tca_mon_id=2")*1;
        if(!$tca){
            $tca=6.96;
        }
        $proyecciones=array();
        $num=$conec->get_num_registros();
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            $monto=$obj->monto-$obj->monto_pagado;
            if($monto>0){
                $mon=$obj->moneda;
                $urb_id=$obj->ven_urb_id;
                $anio=$obj->anio;
                $cantidad=$obj->cantidad;
                $this->sumar_monto($anio,$urb_id, $mon,$monto,$cantidad,$tca, $proyecciones);
            }
            $conec->siguiente();
        }
//        echo "<pre>";
//        print_r($proyecciones);
//        echo "</pre>";
//        return; 
        $result=array();
        $nro=1;
        $total=new stdClass();

        $head=array('#','A&ntilde;o','Proyecto','Monto Bs.','Monto $us','Convertido a Bs.','Convertido a $us');
        $num=  count($proyecciones);
        
        
        for ($i = 0; $i < $num; $i++) {
            $proy=$proyecciones[$i];
            $monto=$obj->monto-$obj->monto_pagado;
            $total_1=round($proy->monto_1+($proy->monto_2*$tca),2);
            $total_2=round($proy->monto_2+($proy->monto_1/$tca),2);
            
//            $total->monto_1+=$proy->monto_1;
//            $total->monto_2+=$proy->monto_2;
//            $total->total_1+=$total_1;
//            $total->total_2+=$total_2;
            $urb_nombre=  FUNCIONES::atributo_bd_sql("select urb_nombre_corto as campo from urbanizacion where urb_id='$proy->urb_id'");
            
            
            
            $result[]=array(
                $nro,$proy->anio,$urb_nombre,round($proy->monto_1,2),round($proy->monto_2,2),$total_1,$total_2
            );
            $total->monto_1+=$proy->monto_1;
            $total->monto_2+=$proy->monto_2;
            $total->total_1+=$total_1;
            $total->total_2+=$total_2;
            $nro++;
            
        }


        $data=array(
            'type'=>'success',
            'titulo'=>$this->formulario->titulo,
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>$head,
            'result'=>$result,
            'foot'=>array(
                        array(
                                array('texto'=>'Total','attr'=>'colspan="3"'),
                                round($total->monto_1,2),round($total->monto_2,2),round($total->total_1,2),round($total->total_2,2),
                            ),
            ),
        );
        return $data;
    }
    
    function sumar_monto($anio, $urb_id, $mon, $monto, $cantidad, $tca, &$proyecciones){
        $form_pago=7;
//        $head=array('#','A&ntilde;o','Mes','Monto Bs.','Monto $us','Convertido a Bs.','Convertido a $us');
        foreach ($proyecciones as $proy) {
//            if($proy->anio==$anio && $proy->mes==$mes){
            if($proy->anio==$anio && $proy->urb_id==$urb_id){
                if($mon==1){
                    $tca=1;
                }
                $form =$cantidad*($tca*$form_pago);
                $proy->{"monto_$mon"}+=$monto+$form;
                return;
            }
        }
        $proy=new stdClass();
        $proy->anio=$anio;
        $proy->urb_id=$urb_id;
        $form =$cantidad*($tca*$form_pago);
        if($mon==1){
            $proy->monto_1=$monto+$form;
            $proy->monto_2=0;
        }else{
            $proy->monto_1=0;
            $proy->monto_2=$monto+$form;
        }
        $proyecciones[]=$proy;
//        $result[]=array(
//                    $nro,$obj->ven_id,$cliente,"$obj->int_telefono/$obj->int_celular",$obj->int_direccion,$obj->urb_nombre_corto,$obj->uv_nombre,$obj->man_nro,$obj->lot_nro,$obj->ven_ubicacion,
//                    $fecha,$obj->vpag_usu_cre,$obj->suc_nombre,$obj->vpag_recibo,$est_cartera,$obj->vpag_dias_interes,$str_moneda,$obj->vpag_capital,$obj->vpag_interes,$obj->vpag_form,$obj->vpag_envio,$obj->vpag_monto,$str_fpagos
//                );
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
        if (!($_POST['formu'] == 'ok')) {
//            $_POST['moneda_hecho']=2;
            ?>

            <?php
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
                <?php
            }
            ?>
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <script type="text/javascript" src="js/util.js"></script>
            <!--MaskedInput-->
            <script>
                $("#frm_sentencia").live('submit',function (){
                    $(".error").remove();
                    var fecha_inicio= fecha_mysql($("#inicio").val());
                    var fecha_fin=fecha_mysql($("#fin").val());
                    if(fecha_inicio>fecha_fin){
                        $.prompt('La Fecha Fin debe ser mayor a la Fecha Inicio');
                        return false;
                    }
                    
                });

            </script>
            <style>
                .error{
                    color: #ff0000;
                    margin-left: 5px;
                }
            </style>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo; ?>" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->                            
                            <input type="hidden" id="ges_fecha_ini" value=""/>
                            <input type="hidden" id="ges_fecha_fin" value=""/>
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
                                <?php
                                $mes=  date('m');
                                $anio=  date('Y');
                                $dia_max=  FUNCIONES::dia_max($anio, $mes);                                
                                ?>
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo "01/$mes/$anio"?>" type="text">                                    
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo "$dia_max/$mes/$anio"?>" type="text">                                    
                                </div>		
                            </div>
                            
                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="submit" class="boton" name="" value="Generar Reporte" >
                                </center>
                            </div>
                        </div>
                    </div>
                </form>	
                <div>
                    <script>
                        jQuery(function($) {
                            $("#inicio").mask("99/99/9999");
                            $("#fin").mask("99/99/9999");
                        });
                    </script>
                    <?php
                }
            }


        }
        ?>