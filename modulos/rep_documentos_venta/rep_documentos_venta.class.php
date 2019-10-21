<?php

class REP_DOCUMENTOS_VENTA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_DOCUMENTOS_VENTA() {
        //permisos
        $this->ele_id = 151;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_documentos_venta';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('REPORTE DE DOCUMENTOS');
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
                $info[]=array('label'=>'Estado','va lor'=> 'Pendiente o Pagado');
            }
            else{
                $filtro .= " and ven_estado='" . $_POST['estado'] . "'";
                $info[]=array('label'=>'Estado','valor'=> $_POST['estado']);
            }
        }
        
        $sql = "SELECT 
                    ven_id,int_nombre,int_apellido, int_ci,int_ci_exp,int_telefono,int_celular,
                    urb_nombre_corto,ven_moneda,uv_nombre,man_nro,lot_nro
                
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
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $tipo_documentos=$_POST[tipo_documentos];
        $tdoc_filtro="";
        if(count($tipo_documentos)>0){
            $str_tdoc_ids=  implode(',', $tipo_documentos);
            $tdoc_filtro=" and tdoc_id in ($str_tdoc_ids)";
        }
        $ltdocumentos = FUNCIONES::lista_bd_sql("select * from ter_tipo_documento where tdoc_eliminado='No' $tdoc_filtro");
//        $total=new stdClass();
//        $estados_carteras=$_POST[estados_carteras];
        $head=array('#','Nro Venta','Cliente','C.I.','Telef.','Proyecto','Moneda','UV','MZ','Lote');
        foreach ($ltdocumentos as $tdoc) {
            $head[]=$tdoc->tdoc_nombre;
        }
        
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            
            $cliente="$obj->int_nombre $obj->int_apellido";

            $str_moneda='Bolivianos';
            if($obj->ven_moneda=='2'){
                $str_moneda='Dolares';
            }
            $doc_ci="$obj->int_ci-$obj->int_ci_exp";
            $lvdoc=  $this->tipo_documentos_venta($obj->ven_id);
            $ven_id = $obj->ven_id.'<img class="btn_ver_documentos" src="images/documentos.png" width="16" data-id="'.$obj->ven_id.'">';
            $fila=array(
                    $nro,$ven_id,$cliente,$doc_ci,"$obj->int_telefono/$obj->int_celular",$obj->urb_nombre_corto,$str_moneda,
                    $obj->uv_nombre,$obj->man_nro,$obj->lot_nro
                );
            foreach ($ltdocumentos as $tdoc) {
                if(in_array($tdoc->tdoc_id, $lvdoc)){
                    $fila[]='<b style="color:#fff; background-color: #00AF1A; padding: 1px 3px">SI<b>';
                }else{
                    $fila[]='<b style="color:#fff; background-color: #FF0000; padding: 1px 3px">NO<b>';
                }
            }
            $result[]=$fila;
            $nro++;
            $conec->siguiente();
        }

        $data=array(
            'type'=>'success',
            'titulo'=>$this->formulario->titulo,
            'info'=>$info,
            'modulo'=>  $this->modulo,
            'head'=>$head,
            'result'=>$result,
            'foot'=>array(),
            
        );
        return $data;
    }
    
    function tipo_documentos_venta($ven_id){
        $lvdocumentos=  FUNCIONES::objetos_bd_sql("select distinct vdoc_tdoc_id as id from venta_documento where vdoc_ven_id='$ven_id'");
        $lvdoc=array();
        for ($i = 0; $i < $lvdocumentos->get_num_registros(); $i++) {
            $obj=$lvdocumentos->get_objeto();
            $lvdoc[]=$obj->id;
            $lvdocumentos->siguiente();
        }
        return $lvdoc;
    }
    
    function procesar_reporte() {
        if($_POST){
            $data=  $this->obtener_resultados();
            if($data[type]=='success'){
                if($_POST[imprimir]=='excel'){
                    REPORTE::excel($data);
                }else{
                    REPORTE::html($data);
                    ?>
                    <style>
                        .btn_ver_documentos{display: inline; margin-left: 5px; cursor: pointer;}
                    </style>
                    <script>
                        $('.btn_ver_documentos').click(function(){
                            var ven_id=$(this).attr('data-id');
                            window.open('gestor.php?mod=venta&tarea=DOCUMENTOS&id='+ven_id,'reportes','left=150,width=1000,height=500,top=0,scrollbars=yes');
                        });
                    </script>
                    <?php
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
                                <div class="Etiqueta" ><span class="flechas1">*</span>Mostrar Documentos</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="tipo_documento" id="tipo_documento" class="caja_texto">
                                        <option value="">-- Seleccione --</option>
                                        <?php
                                        $fun->combo("select tdoc_id as id, tdoc_nombre as nombre from ter_tipo_documento where tdoc_eliminado='No'",'')
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >&nbsp;</div>
                                <div id="CajaInput">
                                    <div class="box_lista_cuenta"> 
                                        <table id="tab_tipo_documento" class="tab_lista_cuentas">
                                            <tbody>
                                            </tbody>
                                        </table>                                    
                                    </div>
                                </div>							   							   								
                            </div>
                            <script>
                                $('#tipo_documento').change(function(){
//                                    console.log($(this).val());
                                    var id=trim($('#tipo_documento option:selected').val());
                                    var nombre=trim($('#tipo_documento option:selected').text());
                                    agregar_detalle_cartera({id:id, nombre:nombre},'tipo_documento');
                                    $(this).val('');
//                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
//                                    $('#cja_cue_id').trigger('chosen:updated');
                                });
                                
                                function agregar_detalle_cartera(objeto,input) {
                                    if (!$("#tab_"+input+' .tipo_documentos[value='+objeto.id+']').length) {
                                        var fila='';
                                        fila += '<tr>';
                                        fila += '   <td>';
                                        fila += '       <input type="hidden" class="tipo_documentos" name="tipo_documentos[]" value="'+objeto.id+'">';
                                        fila += '       ' + objeto.nombre;
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

                    $('#ven_vdo_id').chosen({
                        allow_single_deselect: true
                    }).change(function(){
                                    
                        })
                    ;

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