<?php

class con_compra extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function con_compra() {  //permisos
        $this->ele_id = 199;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "com_referido";
        $this->arreglo_campos[0]["texto"] = "Referido";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "com_glosa";
        $this->arreglo_campos[1]["texto"] = "Glosa";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;
        
        $this->arreglo_campos[2]["nombre"] = "com_usu_cre";
        $this->arreglo_campos[2]["texto"] = "Usuario";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 25;
        
        $this->arreglo_campos[3]["nombre"] = "com_fecha";
        $this->arreglo_campos[3]["texto"] = "Fecha";
        $this->arreglo_campos[3]["tipo"] = "fecha";
        $this->arreglo_campos[3]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'con_compra';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('COMPRA');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }

        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select  
				*
			  from 
				con_compra 
                            where com_eliminado='No' ";
        $this->set_sql($sql, 'order by com_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Fecha</th>		
            <th>Referido</th>		
            <th>Glosa</th>
            <th>Usuario</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';
                echo "<td>";
                echo FUNCIONES::get_fecha_latina($objeto->com_fecha);
                echo "</td>";
                echo "<td>";
                echo $objeto->com_referido;
                echo "</td>";
                echo "<td>";
                echo $objeto->com_glosa;
                echo "</td>";
                echo "<td>";
                echo $objeto->com_usu_cre;
                echo "</td>";
                echo "<td>";
                echo $this->get_opciones($objeto->com_id);
                echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Nombre";
            $valores[$num]["valor"] = $_POST['com_nombre'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Glosa";
            $valores[$num]["valor"] = $_POST['com_glosa'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;

            $val = NEW VALIDADOR;

            $this->mensaje = "";

            if ($val->validar($valores)) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;
                return false;
            }
        }
        return false;
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from con_compra 
				where com_id = '" . $_GET['id'] . "'";
        $com_id=$_GET[id];
        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['com_id'] = $objeto->com_id;

        $_POST['com_nombre'] = $objeto->com_referido;
        $_POST['com_glosa'] = $objeto->com_glosa;
        $_POST['com_fecha'] = FUNCIONES::get_fecha_latina($objeto->com_fecha);
        
        
    }
    function detalle_compras($com_id) {
        $dcompras=array();
        $detalle = FUNCIONES::objetos_bd_sql("select * from con_compra_detalle where dcom_com_id='$com_id'");
        for ($i = 0; $i < $detalle->get_num_registros(); $i++) {
            $obj=$detalle->get_objeto();
            $fecha_lat=  FUNCIONES::get_fecha_latina($obj->dcom_fecha);
            
            $agastos = json_decode(FUNCIONES::limpiar_cadena($obj->dcom_gastos));
            $str_gastos='';
            $j=0;
            foreach ($agastos as $gast) {
                $cue=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$gast->cue_id'");
                $cue_descripcion=  FUNCIONES::limpiar_cadena($cue->cue_descripcion);
                $gast->cue_desc="$cue->cue_codigo | $cue_descripcion";
                if($j>0)
                    $str_gastos.=", <br>";
                $str_gastos.="$cue->cue_codigo | $cue_descripcion";
                $j++;
            }
            $json_gastos = FUNCIONES::html_decode(json_encode($agastos));
            
            
            $aunegocios = json_decode(FUNCIONES::limpiar_cadena($obj->dcom_unegocios));
            $str_aunegocios ="";
            $j=0;
            foreach ($aunegocios as $uneg) {
                $une=  FUNCIONES::objeto_bd_sql("select * from con_unidad_negocio where une_id='$uneg->une_id'");
                $une_txt=  FUNCIONES::limpiar_cadena($une->une_nombre);
                $uneg->une_txt=$une_txt;
                if($j>0)
                    $str_aunegocios.=", ";
                $str_aunegocios.="$une_txt: $uneg->une_porc %";
                $j++;
            }
            $json_unegocios = FUNCIONES::html_decode(json_encode($aunegocios));
            
            
            $apagos = json_decode(FUNCIONES::limpiar_cadena($obj->dcom_pagos));
            $str_pagos ="";
            $j=0;
            foreach ($apagos as $pag) {
                $cue=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$pag->cue_id'");
                $cue_descripcion=  FUNCIONES::limpiar_cadena($cue->cue_descripcion);
                $pag->cue_desc=$cue_descripcion;
                if($j>0)
                    $str_pagos.=",<br>";
                $str_pagos.="$cue_descripcion: $pag->monto";
                $j++;
            }
            $json_pagos = FUNCIONES::html_decode(json_encode($apagos));
            
            
            $str_json="{";
            $str_json.="\"tipo_nota\":\"$obj->dcom_tipo_nota\",";
            $str_json.="\"moneda\":\"$obj->dcom_moneda\",";
            $str_json.="\"fecha\":\"$fecha_lat\",";
            $str_json.="\"proveedor\":\"$obj->dcom_proveedor\",";
            $str_json.="\"importe\":\"$obj->dcom_importe\",";
            $str_json.="\"glosa\":\"$obj->dcom_glosa\",";
            $str_json.="\"gastos\":$json_gastos,";
            $str_json.="\"unegocios\":$json_unegocios,";
            $str_json.="\"pagos\":$json_pagos,";
            $str_json.="\"dif_cambio\":\"$obj->dcom_dif_cambio\",";
            $str_json.="\"com_tipo\":\"$obj->dcom_com_tipo\",";
            $str_json.="\"nit\":\"$obj->dcom_nit\",";
            $str_json.="\"aut\":\"$obj->dcom_aut\",";
            $str_json.="\"control\":\"$obj->dcom_control\",";
            $str_json.="\"nro_fac\":\"$obj->dcom_nro_fac\",";
            $str_json.="\"poliza\":\"$obj->dcom_poliza\",";
            $str_json.="\"ice\":\"$obj->dcom_ice\",";
            $str_json.="\"excento\":\"$obj->dcom_excento\",";
            $str_json.="\"imp_neto\":\"$obj->dcom_imp_neto\",";
            $str_json.="\"iva\":\"$obj->dcom_iva\",";
            $str_json.="\"tipo_ret\":\"$obj->dcom_tipo_ret\",";
            $str_json.="\"it\":\"$obj->dcom_it\",";
            $str_json.="\"iue\":\"$obj->dcom_iue\",";
            $str_json.="\"ret_res\":\"$obj->dcom_ret_res\"";
//            $str_json.="\"num_fil\":\"$obj->dcom_\"";
            $str_json.="}";
            
            $objdet=new stdClass();
            $objdet->str_json=$str_json;
            $objdet->str_moneda=$obj->dcom_moneda==1?'Bs.':'$us';
            $objdet->tipo_nota=$obj->dcom_tipo_nota;
            $objdet->fecha=$fecha_lat;
            $objdet->proveedor=$obj->dcom_proveedor;
            $objdet->com_tipo=$obj->dcom_com_tipo;
            $objdet->nit=$obj->dcom_nit;
            $objdet->importe=$obj->dcom_importe;
            $objdet->gastos=$str_gastos;
            $objdet->unegocios=$str_aunegocios;
            $objdet->pagos=$str_pagos;
            
            $dcompras[]=$objdet;
            
            $detalle->siguiente();
        }
        return $dcompras;
    }
    
    function formulario_tcp($tipo) {
        $url = $this->link . "?mod=$this->modulo&tarea=$_GET[tarea]" ;
        $red = $this->link . "?mod=$this->modulo";
        if ($_GET[tarea]=='MODIFICAR' && $_GET[id]) {
            $url.='&id=' . $_GET['id'];
        }
        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos Generales</div>
                    <div id="ContenedorSeleccion" >
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha:</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="com_fecha" id="com_fecha" size="12" value="<?php echo $fecha=$_POST['com_fecha'] ? $_POST['com_fecha'] : date('d/m/Y');?>" type="text" >
                                <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value=""/>
                                <span id="label-id-periodo"></span>
                            </div>
                            <div class="Etiqueta" >Moneda:</div>
                            <div id="CajaInput">
                                <div class="read-input">Bolivianos</div>
                            </div>
                            
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Pagado a:</div>
                            <div id="CajaInput">
                                <input name="com_nombre" id="com_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['com_nombre']; ?>" size="50">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Glosa:</div>
                            <div id="CajaInput">
                                <textarea id="com_glosa" cols="33" rows="2" name="com_glosa"><?php echo $_POST['com_glosa']; ?></textarea>                                                            
                            </div>
                        </div>
                        
                        <div class="titulo-detalle" >
                            <span>Detalles Compra </span>
                        </div>

                        <div  class="detalle-tipos">
                            <input type="hidden" id="com_tipo" name="com_tipo" value="">
                            <a href="javascript:void(0);" data-tipo="recibo">Recibo</a>
                            <a href="javascript:void(0);" data-tipo="factura">Factura</a>
                            <a href="javascript:void(0);" data-tipo="retencion"> Retencion</a>
                        </div>

                        <div id="box-det-compra" style="display: none;" >
                            <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
                            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                            <script type="text/javascript" src="js/util.js"></script>
                            
                            <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
                            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
                            
                            <script type="text/javascript" src="js/jquery-ui.min.js"></script>
                            <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
                            
                            <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
                            <br>
                            <?php FORMULARIO::detalle_compra();?>
                        </div>

                        <h3 style="font-size: 16px">Recibos</h3>
                        <table class="tablaLista" id="lista_compras" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Tipo Nota</th>
                                    <th>Moneda</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Com. Tipo</th>
                                    <th>NIT</th>
                                    
                                    <th>Importe</th>
                                    
                                    <th>Gasto</th>
                                    <th>U.N.</th>
                                    <th>Pagos</th>
                                    <th class="tOpciones"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $dcompras=  $this->detalle_compras($_GET[id]);?>
                                <?php $num=1;?>
                                <?php $arraytn=array('recibo'=>'bgcgreen','factura'=>'bgcred','retencion'=>'bgcblue');?>
                                <?php foreach($dcompras as $det){?>
                                    <tr data-num="<?php echo $num;?>">
                                        <td>
                                            <input class="det_tipo_nota" type="hidden" value="<?php echo $det->tipo_nota;?>">
                                            <input class="det_compra" type="hidden" name="det_compra[]" value='<?php echo $det->str_json;?>'>
                                            <span class="<?php echo $arraytn[$det->tipo_nota];?>"><?php echo strtoupper($det->tipo_nota);?></span>
                                        </td>
                                        <td><?php echo $det->str_moneda;?></td>
                                        <td><?php echo $det->fecha;?></td>
                                        <td><?php echo $det->proveedor;?></td>
                                        <td><?php echo $det->com_tipo;?></td>
                                        <td><?php echo $det->nit;?></td>
                                        <td><?php echo $det->importe;?></td>
                                        <td><?php echo $det->gastos;?></td>
                                        <td><?php echo $det->unegocios;?></td>
                                        <td><?php echo $det->pagos;?></td>
                                        <td>
                                            <!--<img class="btn_opcion op_ver" src="images/b_search.png">-->
                                            <img class="btn_opcion op_edit" src="images/b_edit.png">
                                            <img class="btn_opcion op_del" src="images/retener.png">
                                        </td>
                                    </tr>
                                    <?php $num++;?>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="button" class="boton" name="" value="Guardar" id="btn_guardar" >
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <style>
                .bgcblue{background-color: #0054ff; color: #fff;padding: 2px 5px;}
                .bgcgreen{background-color: #05bf0e; color: #fff;padding: 2px 5px;}
                .bgcred{background-color: #ff0000; color: #fff;padding: 2px 5px;}
                .btn_opcion{float: left; margin-right: 3px; cursor: pointer;}
            </style>
            <script>
                
                $("#com_fecha").mask("99/99/9999");
                $('.detalle-tipos a').click(function (){
                    var tipo=$(this).attr('data-tipo');
                    cargar_frm_detalle(tipo,'',0);
                });
                var num_fil=<?php echo $num-1;?>;
                var popup=null;
                function abrir_popup(html){
                    if(popup!==null){
                        popup.close();
                    }
                    popup = window.open('about:blank','reportes','left=100,width=950,height=600,top=0,scrollbars=yes');
                    var extra='';
                    extra+='<html><head><title>Vista Previa</title>';
                    extra+='</head>';
                    extra+='<link href=css/estilos.css rel=stylesheet type=text/css />';
                    extra+='</head> <body> <div id=imprimir> <div id=status> <p>';

                    extra+='<a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                    popup.document.write(extra);
                    popup.document.write(html);
                    popup.document.write('</center></body></html>');
                    popup.document.close();
                }

                function cargar_detalle_compra(data){
                    console.log(data);
                    
                    if(data.num_fil>0 && $('#lista_compras tbody tr[data-num='+data.num_fil+']').length){ // modificar
                        modificar_detalle_compra(data);
                    }else{ //nuevo
                        agregar_detalle_compra(data);
                    }
                    
                }
                function modificar_detalle_compra(data){
                    var str_data=JSON.stringify(data);
                    var objfila=$('#lista_compras tbody tr[data-num='+data.num_fil+']');
                    var fila='';
                    var bgcolor='bgcgreen';
                    if(data.tipo_nota==='factura'){
                        bgcolor='bgcred';
                    }else if(data.tipo_nota==='retencion'){
                        bgcolor='bgcblue';
                    }
                    var txt_gastos='';
                    for(var i=0;i<data.gastos.length;i++){
                        if(i>0)
                            txt_gastos+=',<br>';
                        txt_gastos+=data.gastos[i].cue_desc;
                    }
                    var txt_unes='';
                    for(var i=0;i<data.unegocios.length;i++){
                        if(i>0)
                            txt_unes+=', ';
                        txt_unes+=data.unegocios[i].une_txt+': '+data.unegocios[i].une_porc+'%';
                    }
                    var txt_pagos='';
                    for(var i=0;i<data.pagos.length;i++){
                        if(i>0)
                            txt_pagos+=',<br>';
                        txt_pagos+=data.pagos[i].cue_desc+': '+data.pagos[i].monto;
                    }
                    var txt_moneda='';
                    if(data.moneda==='1'){
                        txt_moneda='Bs.';
                    }else{
                        txt_moneda='$us';
                    }
                    
                    fila+='<tr data-num="'+data.num_fil+'">';
                    fila+='	<td>';
                    fila+='	<input type="hidden" class="det_tipo_nota" value=\''+data.tipo_nota+'\'>';
                    fila+='	<input type="hidden" class="det_compra" name="det_compra[]" value=\''+str_data+'\'>';
                    fila+='	<span class="'+bgcolor+'">'+data.tipo_nota.toUpperCase()+'</span>';
                    fila+='	</td>';
                    fila+='	<td>'+txt_moneda+'</td>';
                    fila+='	<td>'+data.fecha+'</td>';
                    fila+='	<td>'+data.proveedor+'</td>';
                    fila+='	<td>'+data.com_tipo+'</td>';
                    fila+='	<td>'+data.nit+'</td>';

                    fila+='	<td>'+data.importe+'</td>';
                    
                    fila+='	<td>'+txt_gastos+'</td>';
                    fila+='	<td>'+txt_unes+'</td>';
                    fila+='	<td>'+txt_pagos+'</td>';
                    fila+='	<td>';
//                    fila+='         <img class="btn_opcion op_ver" src="images/b_search.png">';
                    fila+='         <img class="btn_opcion op_edit" src="images/b_edit.png">';
                    fila+='         <img class="btn_opcion op_del" src="images/retener.png">';
                    fila+='	</td>';
                    fila+='</tr>';
                    $(objfila).after(fila);
                    $(objfila).remove();
                }
                function agregar_detalle_compra(data){
                    var str_data=JSON.stringify(data);
                    var txt_moneda='';
                    if(data.moneda==='1'){
                        txt_moneda='Bs.';
                    }else{
                        txt_moneda='$us';
                    }
                    var txt_gastos='';
                    for(var i=0;i<data.gastos.length;i++){
                        if(i>0)
                            txt_gastos+=',<br>';
                        txt_gastos+=data.gastos[i].cue_desc;
                    }
                    var txt_unes='';
                    for(var i=0;i<data.unegocios.length;i++){
                        if(i>0)
                            txt_unes+=', ';
                        txt_unes+=data.unegocios[i].une_txt+': '+data.unegocios[i].une_porc+'%';
                    }
                    var txt_pagos='';
                    for(var i=0;i<data.pagos.length;i++){
                        if(i>0)
                            txt_pagos+=',<br>';
                        txt_pagos+=data.pagos[i].cue_desc+': '+data.pagos[i].monto;
                    }
                    var bgcolor='bgcgreen';
                    if(data.tipo_nota==='factura'){
                        bgcolor='bgcred';
                    }else if(data.tipo_nota==='retencion'){
                        bgcolor='bgcblue';
                    }
                    
//                    data.com_tipo=com_tipo;
//                    data.nit=nit;
//                    data.aut=aut;
//                    data.control=control;
//                    data.nro_fac=nro_fac;
//                    data.poliza=poliza;
//                    data.ice=tice;
//                    data.excento=excento;
//                    data.imp_neto=ineto;
//                    data.cred_fiscal=cred_fiscal;
                    var fila='';
                    num_fil=num_fil+1;
                    fila+='<tr data-num="'+num_fil+'">';
                    fila+='	<td>';
                    fila+='	<input type="hidden" class="det_tipo_nota" value=\''+data.tipo_nota+'\'>';
                    fila+='	<input type="hidden" class="det_compra" name="det_compra[]" value=\''+str_data+'\'>';
                    fila+='	<span class="'+bgcolor+'">'+data.tipo_nota.toUpperCase()+'</span>';
                    fila+='	</td>';
                    fila+='	<td>'+txt_moneda+'</td>';
                    fila+='	<td>'+data.fecha+'</td>';
                    fila+='	<td>'+data.proveedor+'</td>';
                    fila+='	<td>'+data.com_tipo+'</td>';
                    fila+='	<td>'+data.nit+'</td>';
//                    fila+='	<td>'+data.aut+'</td>';
//                    fila+='	<td>'+data.control+'</td>';
//                    fila+='	<td>'+data.nro_fac+'</td>';
//                    fila+='	<td>'+data.poliza+'</td>';
                    
                    fila+='	<td>'+data.importe+'</td>';
                    
//                    fila+='	<td>'+data.ice+'</td>';
//                    fila+='	<td>'+data.excento+'</td>';
//                    fila+='	<td>'+data.imp_neto+'</td>';
//                    fila+='	<td>'+data.iva+'</td>';
                    
//                    fila+='	<td>'+data.tipo_ret+'</td>';
//                    fila+='	<td>'+data.it+'</td>';
//                    fila+='	<td>'+data.iue+'</td>';
//                    fila+='	<td>'+data.ret_res+'</td>';
                    
                    fila+='	<td>'+txt_gastos+'</td>';
                    fila+='	<td>'+txt_unes+'</td>';
                    fila+='	<td>'+txt_pagos+'</td>';
                    fila+='	<td>';
//                    fila+='         <img class="btn_opcion op_ver" src="images/b_search.png">';
                    fila+='         <img class="btn_opcion op_edit" src="images/b_edit.png">';
                    fila+='         <img class="btn_opcion op_del" src="images/retener.png">';
                    fila+='	</td>';
                    fila+='</tr>';
                    $('#lista_compras').append(fila);
                }
                $(document).on('click','.op_edit',function (){
                    var fila=$(this).parent().parent();
                    var str_data=$(fila).find('.det_compra').val();
                    var tipo=$(fila).find('.det_tipo_nota').val();
                    var tr_num=$(fila).attr('data-num');
                    cargar_frm_detalle(tipo,str_data,tr_num);
                });
                $(document).on('click','.op_del',function (){
                    var fila=$(this).parent().parent();
                    $(fila).remove();
                });
                
                function cargar_frm_detalle(tipo,str_data,tr_num){
                    $('#box-det-compra #tipo_nota').val(tipo);
                    $('#box-det-compra #data_objeto').val(str_data);
                    $('#box-det-compra #num_fil').val(tr_num);
                    $('#box-det-compra #rec_glosa').text($('#com_glosa').val());
                    $('#box-det-compra .div-rec').hide();
                    $('#box-det-compra .div-'+tipo).show();
                    var html=$('#box-det-compra').html();
                    abrir_popup(html);
                }
                $('#btn_guardar').click(function(){
                    var descripcion=$('#com_glosa').val();
                    if(descripcion===''){
                        $.prompt('Ingrese la Glosa');
                        return;
                    }
                    var lista=$('#lista_compras tbody tr');
                    if(lista.size()===0){
                        $.prompt('Ingres al menos un detalle de compra');
                        return;
                    }
                    var fecha=$('#com_fecha').val();
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
            <?php if($_GET[auto_save]=='ok') {?>
                <script>
                    $('#btn_guardar').trigger('click');
                </script>
            <?php }?>
        </div>
        <?php
    }

    function insertar_tcp() {
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
//                return;
        include_once 'clases/modelo_comprobantes.class.php';
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[com_fecha]);
        $conec = new ADO();
        $fecha_cre=  date('Y-m-d H:i:s');
        $glosa=$_POST[com_glosa];
        $referido=$_POST[com_nombre];
        $sql = "insert into con_compra (com_fecha,com_referido,com_glosa,com_usu_cre,com_fecha_cre,com_eliminado)
            values ('$fecha','$_POST[com_nombre]','$_POST[com_glosa]','$_SESSION[id]','$fecha_cre','No')";
        //echo $sql.'<br>';
        $conec->ejecutar($sql,true,true);
        $llave=ADO::$insert_id;
        $com_moneda=1;
        $detalles=array();
        $det_compra=$_POST[det_compra];
        $sql_ins_det="insert into con_compra_detalle(
                        dcom_com_id,dcom_tipo_nota,dcom_moneda,dcom_fecha,dcom_proveedor,dcom_importe,dcom_glosa,dcom_gastos,
                        dcom_unegocios,dcom_pagos,dcom_dif_cambio,dcom_com_tipo,dcom_nit,dcom_aut,dcom_control,dcom_nro_fac,dcom_poliza,
                        dcom_ice,dcom_excento,dcom_imp_neto,dcom_iva,dcom_tipo_ret,dcom_it,dcom_iue,dcom_ret_res
                    )values";
        $cambios=  FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
        $ges_id=$_SESSION[ges_id];


        $cue_credito_fiscal = FUNCIONES::parametro('cred_fiscal', $ges_id);
        $cue_cf_id= FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
        $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);

        
        $porc_ret_it = FUNCIONES::parametro('porc_ret_it', $ges_id);

//                if ($obj->tipo_ret == 'Bienes') { // bienes
        $bcue_ret_it = FUNCIONES::parametro('ret_it_bien', $ges_id);                
        $bcue_it_id=FUNCIONES::get_cuenta($ges_id, $bcue_ret_it);
        $bcue_ret_iue = FUNCIONES::parametro('ret_iue_bien', $ges_id);                    
        $bcue_iue_id=FUNCIONES::get_cuenta($ges_id, $bcue_ret_iue );
        $bporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_bien', $ges_id);
//                } elseif ($obj->tipo_ret == 'Servicios') { // servicio
        $scue_ret_it = FUNCIONES::parametro('ret_it_serv', $ges_id);                
        $scue_it_id=FUNCIONES::get_cuenta($ges_id, $scue_ret_it);
        $scue_ret_iue = FUNCIONES::parametro('ret_iue_serv', $ges_id);
        $scue_iue_id=FUNCIONES::get_cuenta($ges_id, $scue_ret_iue);
        $sporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_serv', $ges_id);
//                }

        foreach ($det_compra as $str_det) { /// limpiar ñ,Ñ tildes 
            $str_det=FUNCIONES::limpiar_cadena($str_det);
//                    $str_det=  htmlentities($str_det);
//                    $str_det=str_replace("&quot;", '"', $str_det);
            $str_det=str_replace('\"', '"', $str_det);
            $obj= json_decode($str_det);

            $dmoneda=$obj->moneda;
//                    $dglosa=$obj->glosa;
            $dglosa=  FUNCIONES::html_decode($obj->glosa);
//                    $dglosa=  html_entity_decode($obj->glosa);

            $aunegocios=$obj->unegocios;
            $str_unegocios="[";
            $i=0;
            foreach ($aunegocios as $uneg) {
                if($i>0){
                    $str_unegocios.=",";
                }
                $str_unegocios.="{\"une_id\":\"$uneg->une_id\",\"une_porc\":\"$uneg->une_porc\"}"; // desc
                $i++;
            }
            $str_unegocios.="]";

            $mimporte =  FORMULARIO::convertir_fpag_monto($obj->importe, $dmoneda, $com_moneda, $cambios);
            $mexento=  FORMULARIO::convertir_fpag_monto($obj->excento, $dmoneda, $com_moneda, $cambios);
            
            $agastos=$obj->gastos;
            $str_gastos="[";
            $i=0;
            foreach ($agastos as $gast) {
                if($i>0){
                    $str_gastos.=",";
                }
                $str_gastos.="{\"cue_id\":\"$gast->cue_id\",\"monto\":\"$gast->monto\"}"; // cue_desc

                $gmonto =  FORMULARIO::convertir_fpag_monto($gast->monto, $dmoneda, $com_moneda, $cambios);

                $gasto_neto=$gmonto;
//                        $cue_cf_id= FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
//                        $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);        
                if($obj->tipo_nota=='factura'){
                    $gexcento=($mexento*$gmonto)/$mimporte;
                    $monto_iva = ($gmonto-$gexcento) * $porc_iva / 100;
                    $gasto_neto = $gmonto - $monto_iva;
                }else if($obj->tipo_nota=='retencion'){
                    if ($obj->tipo_ret == 'Bienes') {
                        $porc_ret_iue=$bporc_ret_iue;
                    } elseif ($obj->tipo_ret == 'Servicios') { 
                        $porc_ret_iue=$sporc_ret_iue;
                    }
                    $n_monto = $gmonto / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
                    $monto_ret_it = $n_monto * $porc_ret_it / 100;
                    $monto_ret_iue = $n_monto * $porc_ret_iue / 100;
                    $gasto_neto = $gmonto + $monto_ret_it + $monto_ret_iue;
                }
//                        $gmonto=$gast->monto;

                $sum_gmontos=0;
                for($j=0;$j<count($aunegocios);$j++){
                    $une=$aunegocios[$j];
                    $_nmonto = 0 ;
                    if($j == count($aunegocios)-1){ //ultimos
                        $_nmonto=$gasto_neto-$sum_gmontos;
                    }else{
                        $_nmonto=($gasto_neto*$une->une_porc)/100;
                    }
                    $sum_gmontos+=$_nmonto;
                    if($_nmonto>0){
                        $detalles[]=array("cuen"=>$gast->cue_id,"debe"=>$_nmonto,"haber"=>0,
                                    "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                                    'fpago'=>'','ban_nombre'=>'','ban_nro'=>'','descripcion'=>'',
                                    'une_id'=>$une->une_id
                            );
                    }
                }
                $i++;
            }
            $str_gastos.="]";

            
            if($obj->tipo_nota=='factura'){ //iva
//                $excento=$obj->excento*1;
                $monto_iva = ($mimporte-$mexento) * $porc_iva / 100;

                if ($monto_iva > 0) {
                    $detalles[] = array("cuen" => $cue_cf_id, "debe" => $monto_iva, "haber" => 0,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
            }else if($obj->tipo_nota=='retencion'){
                if ($obj->tipo_ret == 'Bienes') {
                    $cue_it_id=$bcue_it_id;
                    $cue_ret_iue=$bcue_iue_id;
                    $porc_ret_iue=$bporc_ret_iue;
                } elseif ($obj->tipo_ret == 'Servicios') { 
                    $cue_it_id=$scue_it_id;
                    $cue_ret_iue=$scue_iue_id;
                    $porc_ret_iue=$sporc_ret_iue;
                }
                $n_monto = $mimporte / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
                $monto_ret_it = $n_monto * $porc_ret_it / 100;
                $monto_ret_iue = $n_monto * $porc_ret_iue / 100;

                if ($monto_ret_iue > 0) {
                    $detalles[] = array("cuen" => $cue_ret_iue, "debe" => 0, "haber" => $monto_ret_iue,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
                if ($monto_ret_it > 0) {
                    $detalles[] = array("cuen" => $cue_it_id, "debe" => 0, "haber" => $monto_ret_it,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
            }

            $apagos=$obj->pagos;
            $str_pagos="[";
            $i=0;
            foreach ($apagos as $pag) {
                if($i>0){
                    $str_pagos.=",";
                }
                $descripcion=  FUNCIONES::html_decode($pag->descripcion);
                $str_pagos.="{\"cue_id\":\"$pag->cue_id\",\"monto\":\"$pag->monto\",\"mon_id\":\"$pag->mon_id\",\"fpago\":\"$pag->fpago\",\"ban_nombre\":\"$pag->ban_nombre\",\"ban_nro\":\"$pag->ban_nro\",\"descripcion\":\"$descripcion\"}"; // cue_desc

                $nmonto =  FORMULARIO::convertir_fpag_monto($pag->monto, $pag->mon_id, $com_moneda, $cambios);

                $detalles[]=array("cuen"=>$pag->cue_id,"debe"=>0,"haber"=>$nmonto,
                        "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                        'fpago'=>$pag->fpago,'ban_nombre'=>$pag->ban_nombre,'ban_nro'=>$pag->ban_nro,
                        'descripcion'=>$descripcion,'une_id'=>0
                );
                $i++;

            }
            $str_pagos.=']';
            if($obj->dif_cambio*1>0){
                $dif_cambio=$obj->dif_cambio*1;
                $detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, MODELO_COMPROBANTE::$dif_cambio_diponibilidades),"debe"=>$dif_cambio,"haber"=>0,
                            "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,'une_id'=>0
                    );
            }
            $dfecha=  FUNCIONES::get_fecha_mysql($obj->fecha);
            $sql_ins_values=$sql_ins_det."(
                '$llave','$obj->tipo_nota','$obj->moneda','$dfecha','$obj->proveedor','$obj->importe','$dglosa','$str_gastos',
                '$str_unegocios','$str_pagos','$obj->dif_cambio','$obj->com_tipo','$obj->nit','$obj->aut','$obj->control','$obj->nro_fac','$obj->poliza',
                '$obj->ice','$obj->excento','$obj->imp_neto','$obj->iva','$obj->tipo_ret','$obj->it','$obj->iue','$obj->ret_res'
                )";
            $conec->ejecutar($sql_ins_values);

            if($obj->tipo_nota=='factura'){
                 $sql = "insert into con_libro (
                                    lib_tipo, lib_fecha, lib_nit, lib_nro_autorizacion, lib_cod_control, lib_nro_factura, 
                                    lib_nro_poliza, lib_cliente, lib_tot_factura, lib_ice, lib_imp_exentos, lib_imp_neto, 
                                    lib_iva,lib_estado,lib_libro, lib_com_id,lib_ges_id, lib_eliminado
                            )values(
                                    '$obj->com_tipo','$dfecha', '$obj->nit', '$obj->aut', '$obj->control', '$obj->nro_fac',
                                    '$obj->poliza', '$obj->proveedor', '$obj->importe','$obj->ice', '$obj->excento','$obj->imp_neto',
                                    '$obj->iva','V','Compra','$llave','$ges_id','no'
                            )";

                $conec->ejecutar($sql);
            }else if($obj->tipo_nota=='retencion'){
                $sql = "insert into con_retencion (
                                ret_fecha, ret_neto, ret_it, ret_iue,ret_result, ret_tipo, 
                                ret_asumido, ret_com_id,ret_ges_id, ret_eliminado
                            )values(
                                '$dfecha', '$obj->importe', '$obj->it', '$obj->iue', '$obj->ret_res','$obj->tipo_ret', 
                                '1','$llave','$ges_id','No')";
                $conec->ejecutar($sql);
            }

//                    $cliente=html_entity_decode($libro->cliente);
        }
        
        include_once 'clases/registrar_comprobantes.class.php';

        $data=array(
            'moneda'=>$com_moneda,
            'ges_id'=>$_SESSION[ges_id],
            'fecha'=>$fecha,
            'glosa'=>$glosa,
            'interno'=>$referido,
            'tabla_id'=>$llave,
            'detalles'=>$detalles,
        );

        $comprobante = MODELO_COMPROBANTE::compra($data);

        $cmp_id=COMPROBANTES::registrar_comprobante($comprobante);
        $sql_up="update con_libro set lib_cmp_id='$cmp_id' where lib_com_id='$llave'";
        $conec->ejecutar($sql_up);
        $sql_up="update con_retencion set ret_cmp_id='$cmp_id' where ret_com_id='$llave'";
        $conec->ejecutar($sql_up);

        $mensaje = 'Compra Agregado Correctamente';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function modificar_tcp() {
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
//                return;
        include_once 'clases/modelo_comprobantes.class.php';
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[com_fecha]);
        $conec = new ADO();
        $fecha_mod=  date('Y-m-d H:i:s');
        $glosa=$_POST[com_glosa];
        $referido=$_POST[com_nombre];
        $com_id=$_GET[id];
        $sql_up_compra = "update con_compra set 
                        com_fecha='$fecha',
                        com_referido='$referido',
                        com_glosa='$glosa',
                        com_usu_mod='$_SESSION[id]',
                        com_fecha_mod='$fecha_mod'
                    where com_id='$com_id'
                    ";
        //echo $sql.'<br>';
        $conec->ejecutar($sql_up_compra);

        $com_moneda=1;
        $detalles=array();
        $det_compra=$_POST[det_compra];
        $sql_ins_det="insert into con_compra_detalle(
                        dcom_com_id,dcom_tipo_nota,dcom_moneda,dcom_fecha,dcom_proveedor,dcom_importe,dcom_glosa,dcom_gastos,
                        dcom_unegocios,dcom_pagos,dcom_dif_cambio,dcom_com_tipo,dcom_nit,dcom_aut,dcom_control,dcom_nro_fac,dcom_poliza,
                        dcom_ice,dcom_excento,dcom_imp_neto,dcom_iva,dcom_tipo_ret,dcom_it,dcom_iue,dcom_ret_res
                    )values";
        $cambios=  FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
        $ges_id=$_SESSION[ges_id];


        $cue_credito_fiscal = FUNCIONES::parametro('cred_fiscal', $ges_id);
        $cue_cf_id= FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
        $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);

//        $cue_ret_it = FUNCIONES::parametro('ret_it', $ges_id);                
//        $cue_it_id=FUNCIONES::get_cuenta($ges_id, $cue_ret_it);
        $porc_ret_it = FUNCIONES::parametro('porc_ret_it', $ges_id);

        //                if ($obj->tipo_ret == 'Bienes') { // bienes
        $bcue_ret_it = FUNCIONES::parametro('ret_it_bien', $ges_id);                
        $bcue_it_id=FUNCIONES::get_cuenta($ges_id, $bcue_ret_it);
        $bcue_ret_iue = FUNCIONES::parametro('ret_iue_bien', $ges_id);                    
        $bcue_iue_id=FUNCIONES::get_cuenta($ges_id, $bcue_ret_iue );
        $bporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_bien', $ges_id);
//                } elseif ($obj->tipo_ret == 'Servicios') { // servicio
        $scue_ret_it = FUNCIONES::parametro('ret_it_serv', $ges_id);                
        $scue_it_id=FUNCIONES::get_cuenta($ges_id, $scue_ret_it);
        $scue_ret_iue = FUNCIONES::parametro('ret_iue_serv', $ges_id);
        $scue_iue_id=FUNCIONES::get_cuenta($ges_id, $scue_ret_iue);
        $sporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_serv', $ges_id);
        
        $sql_del="delete from con_compra_detalle where dcom_com_id='$com_id'";
        $conec->ejecutar($sql_del);
        $sql_del="delete from con_libro where lib_com_id='$com_id'";
        $conec->ejecutar($sql_del);
        $sql_del="delete from con_retencion where ret_com_id='$com_id'";
        $conec->ejecutar($sql_del);

        $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='con_compra' and cmp_tabla_id='$com_id'");


        foreach ($det_compra as $str_det) { /// limpiar ñ,Ñ tildes 
            $str_det=FUNCIONES::limpiar_cadena($str_det);
//                    $str_det=  htmlentities($str_det);
//                    $str_det=str_replace("&quot;", '"', $str_det);
            $str_det=str_replace('\"', '"', $str_det);
            $obj= json_decode($str_det);

            $dmoneda=$obj->moneda;
//                    $dglosa=$obj->glosa;
            $dglosa=  FUNCIONES::html_decode($obj->glosa);
//                    $dglosa=  html_entity_decode($obj->glosa);

            $aunegocios=$obj->unegocios;
            $str_unegocios="[";
            $i=0;
            foreach ($aunegocios as $uneg) {
                if($i>0){
                    $str_unegocios.=",";
                }
                $str_unegocios.="{\"une_id\":\"$uneg->une_id\",\"une_porc\":\"$uneg->une_porc\"}"; // desc
                $i++;
            }
            $str_unegocios.="]";
            
            $mimporte =  FORMULARIO::convertir_fpag_monto($obj->importe, $dmoneda, $com_moneda, $cambios);
            $mexento=  FORMULARIO::convertir_fpag_monto($obj->excento, $dmoneda, $com_moneda, $cambios);

            $agastos=$obj->gastos;
            $str_gastos="[";
            $i=0;
            foreach ($agastos as $gast) {
                if($i>0){
                    $str_gastos.=",";
                }
                $str_gastos.="{\"cue_id\":\"$gast->cue_id\",\"monto\":\"$gast->monto\"}"; // cue_desc

                $gmonto =  FORMULARIO::convertir_fpag_monto($gast->monto, $dmoneda, $com_moneda, $cambios);

                $gasto_neto=$gmonto;
//                        $cue_cf_id= FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
//                        $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);        
                if($obj->tipo_nota=='factura'){
                    $gexcento=($mexento*$gmonto)/$mimporte;
                    $monto_iva = ($gmonto-$gexcento) * $porc_iva / 100;
//                    $monto_iva = $gmonto * $porc_iva / 100;
                    $gasto_neto = $gmonto - $monto_iva;
                }else if($obj->tipo_nota=='retencion'){
                    if ($obj->tipo_ret == 'Bienes') {
                        $porc_ret_iue=$bporc_ret_iue;
                    } elseif ($obj->tipo_ret == 'Servicios') { 
                        $porc_ret_iue=$sporc_ret_iue;
                    }
                    $n_monto = $gmonto / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
                    $monto_ret_it = $n_monto * $porc_ret_it / 100;
                    $monto_ret_iue = $n_monto * $porc_ret_iue / 100;
                    $gasto_neto = $gmonto + $monto_ret_it + $monto_ret_iue;
                }
//                        $gmonto=$gast->monto;

                $sum_gmontos=0;
                for($j=0;$j<count($aunegocios);$j++){
                    $une=$aunegocios[$j];
                    $_nmonto = 0 ;
                    if($j == count($aunegocios)-1){ //ultimos
                        $_nmonto=$gasto_neto-$sum_gmontos;
                    }else{
                        $_nmonto=($gasto_neto*$une->une_porc)/100;
                    }
                    $sum_gmontos+=$_nmonto;
                    $detalles[]=array("cuen"=>$gast->cue_id,"debe"=>$_nmonto,"haber"=>0,
                                "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                                'fpago'=>'','ban_nombre'=>'','ban_nro'=>'','descripcion'=>'',
                                'une_id'=>$une->une_id
                        );
                }
                $i++;
            }
            $str_gastos.="]";

            
            if($obj->tipo_nota=='factura'){ //iva
//                $monto_iva = $mimporte * $porc_iva / 100;
                $monto_iva = ($mimporte-$mexento) * $porc_iva / 100;
                if ($monto_iva > 0) {
                    $detalles[] = array("cuen" => $cue_cf_id, "debe" => $monto_iva, "haber" => 0,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
            }else if($obj->tipo_nota=='retencion'){
                if ($obj->tipo_ret == 'Bienes') {
                    $cue_it_id=$bcue_it_id;
                    $cue_ret_iue=$bcue_iue_id;
                    $porc_ret_iue=$bporc_ret_iue;
                } elseif ($obj->tipo_ret == 'Servicios') { 
                    $cue_it_id=$scue_it_id;
                    $cue_ret_iue=$scue_iue_id;
                    $porc_ret_iue=$sporc_ret_iue;
                }
                $n_monto = $mimporte / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
                $monto_ret_it = $n_monto * $porc_ret_it / 100;
                $monto_ret_iue = $n_monto * $porc_ret_iue / 100;

                if ($monto_ret_iue > 0) {
                    $detalles[] = array("cuen" => $cue_ret_iue, "debe" => 0, "haber" => $monto_ret_iue,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
                if ($monto_ret_it > 0) {
                    $detalles[] = array("cuen" => $cue_it_id, "debe" => 0, "haber" => $monto_ret_it,
                        "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                    );
                }
            }

            $apagos=$obj->pagos;
            $str_pagos="[";
            $i=0;
            foreach ($apagos as $pag) {
                if($i>0){
                    $str_pagos.=",";
                }
                $descripcion=  FUNCIONES::html_decode($pag->descripcion);
                $str_pagos.="{\"cue_id\":\"$pag->cue_id\",\"monto\":\"$pag->monto\",\"mon_id\":\"$pag->mon_id\",\"fpago\":\"$pag->fpago\",\"ban_nombre\":\"$pag->ban_nombre\",\"ban_nro\":\"$pag->ban_nro\",\"descripcion\":\"$descripcion\"}"; // cue_desc

                $nmonto =  FORMULARIO::convertir_fpag_monto($pag->monto, $pag->mon_id, $com_moneda, $cambios);

                $detalles[]=array("cuen"=>$pag->cue_id,"debe"=>0,"haber"=>$nmonto,
                        "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                        'fpago'=>$pag->fpago,'ban_nombre'=>$pag->ban_nombre,'ban_nro'=>$pag->ban_nro,
                        'descripcion'=>$descripcion,'une_id'=>0
                );
                $i++;

            }
            $str_pagos.=']';
            if($obj->dif_cambio*1>0){
                $dif_cambio=$obj->dif_cambio*1;
                $detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, MODELO_COMPROBANTE::$dif_cambio_diponibilidades),"debe"=>$dif_cambio,"haber"=>0,
                            "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,'une_id'=>0
                    );
            }
            $dfecha=  FUNCIONES::get_fecha_mysql($obj->fecha);
            $sql_ins_values=$sql_ins_det."(
                '$com_id','$obj->tipo_nota','$obj->moneda','$dfecha','$obj->proveedor','$obj->importe','$dglosa','$str_gastos',
                '$str_unegocios','$str_pagos','$obj->dif_cambio','$obj->com_tipo','$obj->nit','$obj->aut','$obj->control','$obj->nro_fac','$obj->poliza',
                '$obj->ice','$obj->excento','$obj->imp_neto','$obj->iva','$obj->tipo_ret','$obj->it','$obj->iue','$obj->ret_res'
                )";
            $conec->ejecutar($sql_ins_values);

            if($obj->tipo_nota=='factura'){
                 $sql = "insert into con_libro (
                                    lib_tipo, lib_fecha, lib_nit, lib_nro_autorizacion, lib_cod_control, lib_nro_factura, 
                                    lib_nro_poliza, lib_cliente, lib_tot_factura, lib_ice, lib_imp_exentos, lib_imp_neto, 
                                    lib_iva,lib_estado,lib_libro, lib_com_id,lib_cmp_id,lib_ges_id, lib_eliminado
                            )values(
                                    '$obj->com_tipo','$dfecha', '$obj->nit', '$obj->aut', '$obj->control', '$obj->nro_fac',
                                    '$obj->poliza', '$obj->proveedor', '$obj->importe','$obj->ice', '$obj->excento','$obj->imp_neto',
                                    '$obj->iva','V','Compra','$com_id','$cmp->cmp_id','$ges_id','no'
                            )";

                $conec->ejecutar($sql);
            }else if($obj->tipo_nota=='retencion'){
                $sql = "insert into con_retencion (
                                ret_fecha, ret_neto, ret_it, ret_iue,ret_result, ret_tipo, 
                                ret_asumido, ret_com_id, ret_cmp_id, ret_ges_id, ret_eliminado
                            )values(
                                '$dfecha', '$obj->importe', '$obj->it', '$obj->iue', '$obj->ret_res','$obj->tipo_ret', 
                                '1','$com_id','$cmp->cmp_id','$ges_id','No')";
                $conec->ejecutar($sql);
            }

//                    $cliente=html_entity_decode($libro->cliente);
        }
        
        include_once 'clases/registrar_comprobantes.class.php';

        $data=array(
            'cmp_id'=>$cmp->cmp_id,
            'moneda'=>$com_moneda,
            'ges_id'=>$ges_id,
            'fecha'=>$fecha,
            'glosa'=>$glosa,
            'interno'=>$referido,
            'tabla_id'=>$com_id,
            'detalles'=>$detalles,
        );

        $comprobante = MODELO_COMPROBANTE::compra($data);

        COMPROBANTES::modificar_comprobante($comprobante);
//                $sql_up="update con_libro set lib_cmp_id='$cmp_id' where lib_com_id='$com_id'";
//                $conec->ejecutar($sql_up);
//                $sql_up="update con_retencion set ret_cmp_id='$cmp_id' where ret_com_id='$com_id'";
//                $conec->ejecutar($sql_up);

        $mensaje = 'Sucursal Modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {
        $mensaje = 'Esta seguro de eliminar la Compra?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'com_id');
    }

    function eliminar_tcp() {
        $com_id=$_POST[com_id];
        include_once 'clases/registrar_comprobantes.class.php';
        $bool=COMPROBANTES::anular_comprobante('con_compra', $com_id);
        if(!$bool){
            $mensaje="El pago de la cuota no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
            $tipo='Error';			
            $this->formulario->ventana_volver($mensaje,$this->link . '?mod=' . $this->modulo ,'',$tipo);
            return;
        }

        $conec = new ADO();
        $sql = "update con_compra set com_eliminado='si' where com_id='" . $com_id. "'";
        $conec->ejecutar($sql);
        $sql = "update con_libro set lib_eliminado='si' where lib_com_id='" . $com_id. "'";
        $conec->ejecutar($sql);
        $sql = "update con_retencion set ret_eliminado='Si' where ret_com_id='" . $com_id. "'";
        $conec->ejecutar($sql);
        $mensaje = 'Compra Eliminado Correctamente.';
        
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

}
        ?>