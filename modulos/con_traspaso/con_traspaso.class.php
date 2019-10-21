<?php

class con_traspaso extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function con_traspaso() {  //permisos
        $this->ele_id = 200;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();
        $num=0;
        $this->arreglo_campos[$num]["nombre"] = "tras_fecha";
        $this->arreglo_campos[$num]["texto"] = "Fecha";
        $this->arreglo_campos[$num]["tipo"] = "fecha";
        $this->arreglo_campos[$num]["tamanio"] = 25;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "tras_glosa";
        $this->arreglo_campos[$num]["texto"] = "Glosa";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "tras_usu_cre";
        $this->arreglo_campos[$num]["texto"] = "Usuario";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'con_traspaso';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('TRASPASO CAJAS');

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

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select * from con_traspaso where tras_eliminado='No'";
        $this->set_sql($sql, 'order by tras_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        
        ?>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
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
            echo $objeto->tras_id;
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->tras_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->tras_glosa;
            echo "</td>";
            echo "<td>";
            echo $objeto->tras_usu_cre;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->tras_id);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();
        $sql = "select * from con_traspaso 
				where tras_id = '" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $_POST['tras_id'] = $objeto->tras_id;
        $_POST['tras_nombre'] = $objeto->tras_nombre;
        $_POST['tras_descripcion'] = $objeto->tras_descripcion;
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
//            $num = 0;
//            $valores[$num]["etiqueta"] = "Nombre";
//            $valores[$num]["valor"] = $_POST['tras_nombre'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;
//            $valores[$num]["etiqueta"] = "Descripcion";
//            $valores[$num]["valor"] = $_POST['tras_descripcion'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;

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
    
    function saldos_cajas($usu_id) {
        $acuentas=array();
        $acodigos=  FUNCIONES::lista_bd_sql("select * from con_cajero_detalle where cjadet_usu_id='$usu_id'");
        for ($i = 0; $i < count($acodigos); $i++) {
            $cuenta =  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='{$acodigos[$i]->cjadet_cue_id}'") ;
            if($cuenta->cue_id){
                $tdh=  FUNCIONES::total_debe_haber_cuenta($cuenta->cue_id,$cuenta->cue_mon_id);
                $debe=$tdh->tdebe;
                $haber=$tdh->thaber;
                $saldo=$debe-$haber;
                $acuentas[]=(object)array('id'=>$cuenta->cue_id,'nombre'=>$cuenta->cue_descripcion,'moneda'=>$cuenta->cue_mon_id,'saldo'=>$saldo);
            }
        }
        return $acuentas;
    }
    function formulario_tcp($tipo) {
        $url = $this->link . "?mod=$this->modulo&tarea=$_GET[tarea]";
        $red = $this->link . "?mod=$this->modulo";
        if ($_GET[tarea] == 'MODIFICAR' && $_GET[id]) {
            $url.='&id=' . $_GET['id'];
        }
        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        $acuentas=  $this->saldos_cajas($_SESSION[id]);
//        echo "<pre>";
//        print_r($acuentas);
//        echo "</pre>";
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <?php
                $str_json="[";
                $i=0;
                foreach ($acuentas as $cue) {
                    if($i>0){
                        $str_json.=',';
                    }
                    $str_json.="{\"id\":\"$cue->id\",\"nombre\":\"$cue->nombre\",\"moneda\":\"$cue->moneda\",\"saldo\":\"$cue->saldo\"}";
                    $i++;
                }
                $str_json.="]";
                ?>
                <input type="hidden" id="lcuentas" value='<?php echo $str_json;?>'>
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos Generales</div>
                    <div id="ContenedorSeleccion" >
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha:</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="tras_fecha" id="tras_fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text" >
                                <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value=""/>
                                <span id="label-id-periodo"></span>
                            </div>
                            <div class="Etiqueta" >Moneda:</div>
                            <div id="CajaInput">
                                <div class="read-input">Bolivianos</div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Glosa:</div>
                            <div id="CajaInput">
                                <textarea id="tras_glosa" cols="33" rows="2" name="tras_glosa"><?php echo $_POST['tras_glosa']; ?></textarea>                                                            
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Origen:</div>
                            <div id="CajaInput">
                                <select id="cue_origen">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($acuentas as $cue) {?>
                                        <option data-moneda="<?php echo $cue->moneda;?>" data-saldo="<?php echo $cue->saldo;?>" value="<?php echo $cue->id;?>"><?php echo $cue->nombre;?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div id="CajaInput">
                                <span style="float: left; margin: 3px">Saldo: </span>
                                <div class="read-input" id="txt_saldo" style="width: 50px; min-width: 20px;margin-right: 5px;">&nbsp;</div>
                                <input type="hidden" id="tras_saldo_hidden" value="" >
                            </div>
                            <div id="CajaInput">
                                <span style="float: left; margin: 3px">Monto Tras.: </span>
                                <input type="text" id="tras_monto" value="" size="15">
                            </div>
                            <div id="CajaInput">
                                <span style="float: left; margin: 3px">Destino</span>
                                <select id="cue_destino">
                                    <option value="">-- Seleccione --</option>
                                </select>
                            </div>
                            <div id="CajaInput">
                                <img id="add_det" class="add_det" style="cursor: pointer;margin-left: 5px;" height="22" src="images/boton_agregar.png">
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Detalle:</div>
                            <div id="CajaInput">
                                <table class="tablaLista" id="lista_traspasos" cellspacing="0" cellpadding="0" style="width: 600px">
                                    <thead>
                                        <tr>
                                            <th>Caja Origen</th>
                                            <th>Monto Tras.</th>
                                            <th>Caja Destino</th>
                                            <th>Moneda</th>
                                            <th class="tOpciones"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                        
                        <div id="box-det-compra" style="display: none;" >
                            <!--<script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>-->
                            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                            <script type="text/javascript" src="js/util.js"></script>
                            <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
                            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

                        </div>
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
                .op_del{cursor: pointer;}
            </style>
            <script>
                mask_decimal('#tras_monto',null);
                $('#cue_origen').change(function(){
                    var saldo=$(this).find('option:selected').attr('data-saldo')*1;
                    var moneda=$(this).find('option:selected').attr('data-moneda');
                    var id=$(this).val();
                    
                    var lista=$('#lista_traspasos tbody tr');
                    var sum=0;
                    for(var i=0;i<lista.size();i++){
                        var _id=$(lista[i]).find('.cue_origenes').val();
                        if(id===_id){
                            var _monto=$(lista[i]).find('.tras_montos').val()*1;
                            sum+=_monto;
                        }
                    }
                    saldo=(saldo-sum).toFixed(2)*1;
                    
                    $('#txt_saldo').text(saldo);
                    $('#tras_monto').val(saldo);
                    $('#tras_saldo_hidden').val(saldo);
                    
                    var cajas= JSON.parse(trim($('#lcuentas').val()));
                    
                    $('#cue_destino').children().remove();
                    
                    var options='<option value="">-- Seleccione --</option>';
                    for(var i=0;i<cajas.length;i++){
                        var caj=cajas[i];
                        if(moneda===caj.moneda && caj.id!==id){
                            options+='<option data-moneda="'+caj.moneda+'" data-saldo="'+caj.saldo+'" value="'+caj.id+'">'+caj.nombre+'</option>';
                        }
                    }
                    $('#cue_destino').append(options); 
                    $('#tras_monto').select();
                });
                $('#add_det').click(function(){
                    agregar_detalle();
                });
                function agregar_detalle(){
//                    console.log(trim($('#lcuentas').val()));
//                    var cajas= JSON.parse(trim($('#lcuentas').val()));
                    var tras_saldo=$('#tras_saldo_hidden').val()*1;
                    var moneda=$('#cue_origen option:selected').attr('data-moneda');
                    var cue_ori_id=$('#cue_origen option:selected').val()*1;
                    var cue_ori_txt=$('#cue_origen option:selected').text();
                    var tras_monto=$('#tras_monto').val()*1;
                    var cue_des_id=$('#cue_destino option:selected').val()*1;
                    var cue_des_txt=$('#cue_destino option:selected').text();

                    if(!(cue_ori_id>0 && cue_des_id>0 && tras_monto>0)){
                        $.prompt('Ingrese correctamente los Datos')
                        return false;
                    }
                    if(tras_monto>tras_saldo){
                        $.prompt('El monto tiene que ser menor o igual al Saldo');
                        return false;
                    }
                    var txt_moneda='Bolivianos';
                    if(moneda==='2'){
                        txt_moneda='Dolares';
                    }
                    
                    var ntr='';
                    
                    ntr+='<tr>';
                    ntr+='	<td>';
                    ntr+='          <input type="hidden" name="cue_origenes[]" class="cue_origenes" value="'+cue_ori_id+'">';
                    ntr+='          <input type="hidden" name="cue_destinos[]" class="cue_destinos" value="'+cue_des_id+'">';
                    ntr+='          <input type="hidden" name="tras_montos[]" class="tras_montos" value="'+tras_monto+'">';
                    ntr+='          <input type="hidden" name="tras_monedas[]" class="tras_monedas" value="'+moneda+'">';
                    ntr+='          '+cue_ori_txt;
                    ntr+='	</td>';
                    ntr+='	<td>'+tras_monto+'</td>';
                    ntr+='	<td>'+cue_des_txt+'</td>';
                    ntr+='	<td>'+txt_moneda+'</td>';
                    ntr+='	<td><img class="btn_opcion op_del" src="images/retener.png"></td>';
                    ntr+='</tr>';
                    $('#lista_traspasos tbody').append(ntr);
                    
                    $('#cue_origen option[value=""]').prop('selected',true);
                    $('#cue_destino').children().remove();
                    $('#cue_destino').append('<option value="">-- Seleccione --</option>');
                    $('#txt_saldo').html('&nbsp;');
                    $('#tras_monto').val('');
                }
                
                $("#tras_fecha").mask("99/99/9999");
                

                $(document).on('click', '.op_del', function() {
                    var fila = $(this).parent().parent();
                    $(fila).remove();
                    $('#cue_origen option[value=""]').prop('selected',true);
                    $('#cue_destino').children().remove();
                    $('#cue_destino').append('<option value="">-- Seleccione --</option>');
                    $('#txt_saldo').html('&nbsp;');
                    $('#tras_monto').val('');
                });
                

                $('#btn_guardar').click(function() {
                    var descripcion = $('#tras_glosa').val();
                    if (descripcion === '') {
                        $.prompt('Ingrese la Glosa');
                        return;
                    }
                    var lista = $('#lista_traspasos tbody tr');
                    if (lista.size() === 0) {
                        $.prompt('Ingres al menos un detalle de traspasos');
                        return;
                    }
                    var fecha = $('#tras_fecha').val();
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
        </div>




        <?php
    }

function insertar_tcp() {
//        echo "<pre>";
//        print_r($_SESSION);
//        echo "</pre>";
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
//        return;
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[tras_fecha]);
        $conec = new ADO();
        $fecha_cre=  date('Y-m-d H:i:s');
        $glosa=$_POST[tras_glosa];
        
        $sql = "insert into con_traspaso (tras_fecha,tras_glosa,tras_usu_cre,tras_fecha_cre,tras_eliminado)
            values ('$fecha','$glosa','$_SESSION[id]','$fecha_cre','No')";
        //echo $sql.'<br>';
        $conec->ejecutar($sql,true,true);
        $llave=ADO::$insert_id;
        $tras_moneda=1;
        
        $sql_ins_det="insert into con_traspaso_detalle(
                        dtras_tras_id,dtras_cue_ori,dtras_cue_des,dtras_monto,dtras_moneda
                    )values";
        $cambios=  FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
        
        $cue_origenes=$_POST[cue_origenes];
        $cue_destinos=$_POST[cue_destinos];
        $tras_montos=$_POST[tras_montos];
        $tras_monedas=$_POST[tras_monedas];
//        foreach ($det_compra as $str_det) { /// limpiar ñ,Ñ tildes 
        $dglosa="Traspaso de Cajas - ".$glosa;
        $detalles=array();
        for($i=0;$i<count($cue_origenes);$i++) { /// limpiar ñ,Ñ tildes 
            
            $str_det=FUNCIONES::limpiar_cadena($str_det);
            $_cue_ori=$cue_origenes[$i];
            $_cue_des=$cue_destinos[$i];
            $_monto=$tras_montos[$i];
            $_moneda=$tras_monedas[$i];
            
            $trmonto =  FORMULARIO::convertir_fpag_monto($_monto, $_moneda, $tras_moneda, $cambios);
            
            $detalles[]=array("cuen"=>$_cue_des,"debe"=>$trmonto,"haber"=>0,
                    "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                    'fpago'=>'','ban_nombre'=>'','ban_nro'=>'','descripcion'=>'',
                    'une_id'=>0
            );
            $detalles[]=array("cuen"=>$_cue_ori,"debe"=>0,"haber"=>$trmonto,
                    "glosa"=>$dglosa,"ca"=>0,"cf"=>0,"cc"=>0,
                    'fpago'=>'','ban_nombre'=>'','ban_nro'=>'','descripcion'=>'',
                    'une_id'=>0
            );
//            dtras_tras_id,dtras_cue_ori,dtras_cue_des,dtras_monto,dtras_moneda
            $sql_ins_values=$sql_ins_det."(
                '$llave','$_cue_ori','$_cue_des','$_monto','$_moneda'
                )";
            $conec->ejecutar($sql_ins_values);
//                    $cliente=html_entity_decode($libro->cliente);
        }
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';

        $data=array(
            'moneda'=>$tras_moneda,
            'ges_id'=>$_SESSION[ges_id],
            'fecha'=>$fecha,
            'glosa'=>$dglosa,
            'interno'=>  FUNCIONES::interno_nombre($_SESSION[usu_per_id]),
            'tabla_id'=>$llave,
            'detalles'=>$detalles,
        );

        $comprobante = MODELO_COMPROBANTE::traspaso($data);

        $cmp_id=COMPROBANTES::registrar_comprobante($comprobante);

        $mensaje = 'Compra Agregado Correctamente';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }
    
    function modificar_tcp() {
        $conec = new ADO();
        $sql = "update con_traspaso set 
                        tras_nombre='" . $_POST['tras_nombre'] . "',
                        tras_descripcion='" . $_POST['tras_descripcion'] . "'
                        where tras_id='" . $_GET['id'] . "'";
        //echo $sql;	
        $conec->ejecutar($sql);
        $mensaje = 'Sucursal Modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {
        $mensaje = 'Esta seguro de eliminar el Traspaso?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'tras_id');
    }

    function eliminar_tcp() {
        $tras_id=$_POST[tras_id];
        include_once 'clases/registrar_comprobantes.class.php';
        $bool=COMPROBANTES::anular_comprobante('con_traspaso', $tras_id);
        if(!$bool){
            $mensaje="El pago de la cuota no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
            $tipo='Error';			
            $this->formulario->ventana_volver($mensaje,$this->link . '?mod=' . $this->modulo ,'',$tipo);
            return;
        }

        $conec = new ADO();
        $sql = "update con_traspaso set tras_eliminado='si' where tras_id='" . $tras_id. "'";
        $conec->ejecutar($sql);
        
        $mensaje = 'Traspaso Eliminado Correctamente.';
        
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

}
?>