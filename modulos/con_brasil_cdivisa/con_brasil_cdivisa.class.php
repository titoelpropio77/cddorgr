<?php

class con_brasil_cdivisa extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function con_brasil_cdivisa() {  //permisos
        $this->ele_id = 195;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "bdiv_fecha";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "bdiv_glosa";
        $this->arreglo_campos[1]["texto"] = "Glosa";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;



        $this->link = 'gestor.php';

        $this->modulo = 'con_brasil_cdivisa';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('COMPRA DIVISA BRASIL');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

//        if ($this->verificar_permisos('VER')) {
//            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
//            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
//            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
//            $nun++;
//        }

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select * from con_brasil_cdivisa where 1  ";
        $this->set_sql($sql, 'order by bdiv_id desc');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Observacion</th>		
            <th>Cuenta Origen</th>		
            <th>Cuenta destino</th>		
            <th>Usuario</th>
            <th>Estado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $color=array('Activo'=>'#019721','Anulado'=>'#ff0000');
        $max_id=  FUNCIONES::atributo_bd_sql("select max(bdiv_id) as campo from con_brasil_cdivisa where bdiv_estado='Activo'");
        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            
            $operciones=array();
            
            if($max_id!=$objeto->bdiv_id && $objeto->bdiv_estado=='Activo'){
                $operciones[]='ELIMINAR';
            }
            if($objeto->bdiv_estado=='Anulado'){
                $operciones[]='ELIMINAR';
            }
            
            echo '<tr>';
            echo "<td>";
            echo $objeto->bdiv_id;
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->bdiv_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->bdiv_glosa;
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_id='$objeto->bdiv_cue_ori'");
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_id='$objeto->bdiv_cue_des'");
            echo "</td>";
            echo "<td>";
            echo $objeto->bdiv_usu_cre;
            echo "</td>";
            echo "<td id='estado-$objeto->bdiv_id'>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->bdiv_estado]}'>$objeto->bdiv_estado</span>";
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->bdiv_id,'',$operciones);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();
        $sql = "select * from con_brasil_cdivisa 
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
        $acuentas = array();
        $acodigos = FUNCIONES::lista_bd_sql("select * from con_cajero_detalle where cjadet_usu_id='$usu_id'");
//        echo '<pre>';
//        print_r($acodigos);
//        echo '</pre>';
        for ($i = 0; $i < count($acodigos); $i++) {
            $cuenta = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='{$acodigos[$i]->cjadet_cue_id}'");
//            echo "select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='{$acodigos[$i]->cjadet_cue_id}'; <br>";
//            echo "<pre>";
//            print_r($cuenta);
//            echo "</pre>";
            if($cuenta){
                if ($cuenta->cue_mon_id != 3) {
                    $tdh = FUNCIONES::total_debe_haber_cuenta($cuenta->cue_id, $cuenta->cue_mon_id);
                    $debe = $tdh->tdebe;
                    $haber = $tdh->thaber;
                    $saldo = $debe - $haber;
                    $acuentas[] = (object) array('id' => $cuenta->cue_id, 'nombre' => $cuenta->cue_descripcion, 'moneda' => $cuenta->cue_mon_id, 'saldo' => $saldo);
                } else {
                    $sreales = $this->saldos_reales($cuenta->cue_id);
                    $saldo = $sreales->sum_monto;
                    $acuentas[] = (object) array('id' => $cuenta->cue_id, 'nombre' => $cuenta->cue_descripcion, 'moneda' => $cuenta->cue_mon_id, 'saldo' => $saldo, 'dventas' => $sreales->dventas);
                }
            }
        }
        return $acuentas;
    }

    function saldos_reales($cue_id) {
        $mon_des = 3;
        $sql_sel = "select * from con_cv_divisa
                    where
                        cvd_tipo='Venta' and cvd_cue_des='$cue_id' and 
                        cvd_estado='Activo' and cvd_mon_des='$mon_des' and cvd_monto_des>cvd_monto_tras order by cvd_id asc";
        $dventas = FUNCIONES::objetos_bd_sql($sql_sel);
        $sum_mtras = 0;
        $adventas = array();
        for ($i = 0; $i < $dventas->get_num_registros(); $i++) {
            $dven = $dventas->get_objeto();
            $mtras = $dven->cvd_monto_des - $dven->cvd_monto_tras;
            $sum_mtras+=$mtras;
            $adventas[] = (object) array('id' => $dven->cvd_id, 'monto' => $mtras, 'tc' => $dven->cvd_tc);
            $dventas->siguiente();
        }
        return (object) array('sum_monto' => $sum_mtras, 'dventas' => $adventas);
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
        $acuentas = $this->saldos_cajas($_SESSION[id]);
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <?php
                $str_json = "[";
                $i = 0;
                foreach ($acuentas as $cue) {
                    if ($i > 0) {
                        $str_json.=',';
                    }
                    $str_json.="{\"id\":\"$cue->id\",\"nombre\":\"$cue->nombre\",\"moneda\":\"$cue->moneda\",\"saldo\":\"$cue->saldo\"}";
                    $i++;
                }
                $str_json.="]";
                ?>
                <input type="hidden" id="lcuentas" value='<?php echo $str_json; ?>'>
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
                            <div class="Etiqueta" ><span class="flechas1">*</span>TC:</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="tras_tc" id="tras_tc" size="12" value="" type="text" autocomplete="off">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta Origen:</div>
                            <div id="CajaInput">
                                <select id="cue_origen" name="cue_origen">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($acuentas as $cue) { ?>
                                        <?php if($cue->moneda==3){?>
                                        <option data-moneda="<?php echo $cue->moneda; ?>" data-saldo="<?php echo $cue->saldo; ?>" value="<?php echo $cue->id; ?>"><?php echo $cue->nombre; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Saldo: </div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_saldo" style="width: 50px; min-width: 20px;margin-right: 5px;">&nbsp;</div>
                                <input type="hidden" id="tras_saldo_hidden" value="" >
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto Origen: </div>
                            <div id="CajaInput">
                                <input type="text" id="monto_ori" name="monto_ori" value="" size="15" autocomplete="off">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta Destino: </div>
                            <div id="CajaInput">
                                <select id="cue_destino" name="cue_destino">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($acuentas as $cue) { ?>
                                        <?php if($cue->moneda==2){?>
                                        <option data-moneda="<?php echo $cue->moneda; ?>" data-saldo="<?php echo $cue->saldo; ?>" value="<?php echo $cue->id; ?>"><?php echo $cue->nombre; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto Destino: </div>
                            <div id="CajaInput">
                                <input type="text" id="monto_des" name="monto_des" value="" size="15" autocomplete="off" readonly="">
                            </div>
                        </div>

                        <div id="box-det-compra" style="display: none;" >
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
                mask_decimal('#monto_ori', null);
                mask_decimal('#monto_des', null);
                mask_decimal('#tras_tc', null);
                $('#cue_origen').change(function() {
                    var saldo = $(this).find('option:selected').attr('data-saldo') * 1;
                    var moneda = $(this).find('option:selected').attr('data-moneda');
                    var id = $(this).val();
                    $('#txt_saldo').text(saldo);
                    $('#tras_saldo_hidden').val(saldo);
                    $('#monto_ori').val(saldo);
                    $('#monto_ori').select();
                    calcular_monto_destino();
                });
                
                function calcular_monto_destino(){
                    var tc=$('#tras_tc').val();
                    if(tc>0){
                        var monto_ori=$('#monto_ori').val();
                        var monto_des=(monto_ori/tc).toFixed(2);
                        $('#monto_des').val(monto_des);
                    }else{
                        $('#monto_des').val('');
                    }
                }

                $("#tras_fecha").mask("99/99/9999");

                $('#tras_tc,#monto_ori').focusout(function (){
                    calcular_monto_destino();
                });
                
                $('#btn_guardar').click(function() {
                    var descripcion = $('#tras_glosa').val();
                    if (descripcion === '') {
                        $.prompt('Ingrese la Glosa');
                        return;
                    }
                    var tras_saldo = $('#tras_saldo_hidden').val() * 1;
//                    var moneda=$('#cue_origen option:selected').attr('data-moneda');
                    var cue_ori_id = $('#cue_origen option:selected').val() * 1;
//                    var cue_ori_txt=$('#cue_origen option:selected').text();
                    var monto_ori = $('#monto_ori').val() * 1;
                    var monto_des = $('#monto_des').val() * 1;
                    
                    var cue_des_id = $('#cue_destino option:selected').val() * 1;
                    var tras_tc = $('#tras_tc').val() * 1;
//                    var cue_des_txt=$('#cue_destino option:selected').text();

                    if (!(cue_ori_id > 0 && cue_des_id > 0 && monto_ori > 0 && tras_tc>0)) {
                        $.prompt('Ingrese correctamente los Datos');
                        return false;
                    }
                    if (monto_ori > tras_saldo) {
                        $.prompt('El monto origen tiene que ser menor o igual al Saldo');
                        return false;
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

    function detalle_dventas($cue_id,$monto) {
        $mon_des = 3;
        $sql_sel = "select * from con_cv_divisa
                    where
                        cvd_tipo='Venta' and cvd_cue_des='$cue_id' and 
                        cvd_estado='Activo' and cvd_mon_des='$mon_des' and cvd_monto_des>cvd_monto_tras order by cvd_id asc";
        $dventas = FUNCIONES::objetos_bd_sql($sql_sel);
        
        $adventas = array();
        $monto_cons=$monto;
        $i = 0;
        $a_ids=array();
        $ar_montos=array();
        $ad_montos=array();
        $sum_real_mtras = 0;
        $sum_usd_mtras = 0;
        while($i < $dventas->get_num_registros() && $monto_cons>0){
            echo "$monto_cons<br>";
            $dven = $dventas->get_objeto();
            $mtras = $dven->cvd_monto_des - $dven->cvd_monto_tras;
            if($mtras>0 && $monto_cons>$mtras){
                $_id=$dven->cvd_id;
                $_monto=$mtras;
            }else{
                $_id=$dven->cvd_id;
                $_monto=$monto_cons;
            }
            $a_ids[]=$_id;
            $ar_montos[]=$_monto;
            $dmonto=($dven->cvd_monto_ori*$_monto)/$dven->cvd_monto_des;
            
//            $tca_usd=  FUNCIONES::objeto_bd_sql("select * from con_tipo_cambio where tca_fecha='$dven->cvd_fecha' and tca_mon_id=2");
            
//            $bmonto=$dmonto*$tca_usd;
            $ad_montos[]=round($dmonto,2);
            
            $monto_cons=round($monto_cons-$_monto,2);
            
            $sum_real_mtras+=$_monto;
            $sum_usd_mtras+=$dmonto;
//            $sum_bs_mtras+=$bmonto;
            
//            $adventas[] = (object) array('id' => $dven->cvd_id, 'monto' => $mtras, 'tc' => $dven->cvd_tc);
            $dventas->siguiente();
            $i++;
        }
//        echo "$monto_cons<br>";
        if($monto_cons>0){
            return (object) array('type'=>'error','msj'=>'El monto a traspasar es mayor al que se encuentra actualmente en Caja');
        }else{
            return (object) array('type'=>'success','monto_real' => $sum_real_mtras,'monto_usd'=>$sum_usd_mtras, 'ids'=>  implode(',', $a_ids),'rmontos'=>  implode(',', $ar_montos),'dmontos'=>  implode(',', $ad_montos));
        }
        
    }

    function insertar_tcp() {
//        echo "<pre>";
//        print_r($_SESSION);
//        echo "</pre>";
        $sw=true;
        
        $cuenta_ori = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$_POST[cue_origen]'");
        $cuenta_des = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$_POST[cue_destino]'");
        
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        
        $monto_ori = $_POST[monto_ori];
        $monto_des = $_POST[monto_des];
        $dventas=new stdClass();
        if($cuenta_ori->cue_mon_id==3){
            $dventas = $this->detalle_dventas($cuenta_ori->cue_id, $monto_ori);
//            echo "<pre>";
//            print_r($dventas);
//            echo "</pre>";
            if($dventas->type=='error'){
                $sw=false;
                $mensaje = $dventas->msj;// 'Error de compra de divisa';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','error');
                return false;
            }
        }
//        return (object) array('type'=>'success','monto_real' => $sum_real_mtras,'monto_usd'=>$sum_usd_mtras, 'ids'=>  implode(',', $a_ids),'rmontos'=>  implode(',', $ar_montos),'dmontos'=>  implode(',', $ad_montos));
//        return;
        $fecha = FUNCIONES::get_fecha_mysql($_POST[tras_fecha]);
        $conec = new ADO();
        $fecha_cre = date('Y-m-d H:i:s');
        $glosa = $_POST[tras_glosa];
        $tc=$_POST[tras_tc];
        $cue_origen=$_POST[cue_origen];
        $cue_destino=$_POST[cue_destino];
//        $monto_ori='';
        $sql = "insert into con_brasil_cdivisa (
                        bdiv_fecha,bdiv_glosa,bdiv_tc,bdiv_cue_ori,bdiv_monto_ori,bdiv_cue_des,bdiv_monto_des,
                        bdiv_cv_ids,bdiv_cv_montos,bdiv_usu_cre,bdiv_fecha_cre,bdiv_eliminado
                )values (
                        '$fecha','$glosa','$tc','$cue_origen','$monto_ori','$cue_destino','$monto_des',
                        '$dventas->ids','$dventas->rmontos','$_SESSION[id]','$fecha_cre','No'
                )";
        //echo $sql.'<br>';
        $conec->ejecutar($sql, true, true);
        $llave = ADO::$insert_id;
        $tras_moneda = 1;

        if($cuenta_ori->cue_mon_id==3){
            $_cvids=  explode(',', $dventas->ids);
            $_cvrmontos=  explode(',', $dventas->rmontos);
            for ($i = 0; $i < count($_cvids); $i++) {
                $_id=$_cvids[$i];
                $_rmonto=$_cvrmontos[$i];
                $sql_up="update con_cv_divisa set cvd_monto_tras=cvd_monto_tras+$_rmonto where cvd_id=$_id";
                $conec->ejecutar($sql_up);
            }
        }

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';

        $sum_usd = $dventas->monto_usd;
//        $sum_bs = $dventas->monto_bs;
        

        $cambios = FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
//        $tcambios=array();
        $tca_real=$tc;
        $tca_usd=  FUNCIONES::objeto_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=2");
        $tca_br=$tca_usd->tca_valor/$tca_real;
        
        $tcambios=array('3'=>$tca_br);
        
//        $trmonto=$monto_ori*$tca_br;
        $trmonto = FORMULARIO::convertir_fpag_monto($monto_des, $cuenta_des->cue_mon_id, $tras_moneda, $cambios);
        $sum_bs = FORMULARIO::convertir_fpag_monto($sum_usd, $cuenta_des->cue_mon_id, $tras_moneda, $cambios);
        
        $dif_usd = $monto_des - $sum_usd;
        
        $dif_bs=FORMULARIO::convertir_fpag_monto($dif_usd, $cuenta_des->cue_mon_id, $tras_moneda, $cambios);
//        echo "<br>";
//        echo "$trmonto - $sum_bs - $dif_bs";
//        echo "<br>";
        $dglosa = "Compra de Divisas Brasil - " . $glosa;
        
        $ges_id=$_SESSION[ges_id];
        
        $detalles[] = array("cuen" => $cue_destino, "debe" => $trmonto, "haber" => 0,
            "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
            'fpago' => '', 'ban_nombre' => '', 'ban_nro' => '', 'descripcion' => '',
            'une_id' => 0
        );
        $detalles[] = array("cuen" => $cue_origen, "debe" => 0, "haber" => $sum_bs,
            "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
            'fpago' => '', 'ban_nombre' => '', 'ban_nro' => '', 'descripcion' => '',
            'une_id' => 0
        );
        if($dif_bs>0){
            $detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.2.1.01.1.01'), "debe" => 0, "haber" => $dif_bs,
                "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
                'fpago' => '', 'ban_nombre' => '', 'ban_nro' => '', 'descripcion' => '',
                'une_id' => 0
            );
        }elseif($dif_bs<0){
            $dif_bs=$dif_bs*(-1);
            $detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '6.3.1.01.1.04'), "debe" => $dif_bs, "haber" => 0,
                "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
                'fpago' => '', 'ban_nombre' => '', 'ban_nro' => '', 'descripcion' => '',
                'une_id' => 0
            );
        }
        
        $data = array(
            'moneda' => $tras_moneda,
            'ges_id' => $ges_id,
            'fecha' => $fecha,
            'glosa' => $dglosa,
            'interno' => FUNCIONES::interno_nombre($_SESSION[usu_per_id]),
            'tabla_id' => $llave,

            'detalles' => $detalles,

            'tcambios'=>$tcambios,
        );

        $comprobante = MODELO_COMPROBANTE::brasil_cdivisa($data);

        $cmp_id = COMPROBANTES::registrar_comprobante($comprobante);

        $mensaje = 'Compra de Divisa Agregado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {
        $mensaje = 'Esta seguro de Anular la Compra de Divisa?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'bdiv_id');
    }

    function eliminar_tcp() {
        $tras_id = $_POST[bdiv_id];
        include_once 'clases/registrar_comprobantes.class.php';
        $bool = COMPROBANTES::anular_comprobante('con_brasil_cdivisa', $tras_id);
        if (!$bool) {
            $mensaje = "El Registro no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $conec = new ADO();

        $traspaso=  FUNCIONES::objeto_bd_sql("select * from con_brasil_cdivisa where bdiv_id='$tras_id'");

        $_cvids=  explode(',', $traspaso->bdiv_cv_ids);
        $_cvrmontos=  explode(',', $traspaso->bdiv_cv_montos);
        for ($i = 0; $i < count($_cvids); $i++) {
            $_id=$_cvids[$i];
            $_rmonto=$_cvrmontos[$i];
            $sql_up="update con_cv_divisa set cvd_monto_tras=cvd_monto_tras-$_rmonto where cvd_id=$_id";
            $conec->ejecutar($sql_up);
        }

        $sql = "update con_brasil_cdivisa set bdiv_estado='Anulado' where bdiv_id='$tras_id'";
        $conec->ejecutar($sql);

        $mensaje = 'Traspaso Eliminado Correctamente.';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

}
?>