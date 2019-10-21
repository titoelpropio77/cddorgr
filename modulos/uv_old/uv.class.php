<?php

class UV extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function UV() {
//		ini_set('display_errors', 'On');
		
        //permisos
        $this->ele_id = 128;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "urb_nombre";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;
		
        $this->link = 'gestor.php';
        $this->modulo = 'uv';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('URBANIZACIONES');
        $this->usu = new USUARIO();
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

        if ($this->verificar_permisos('UV')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'UV';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/uv.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'UV';
            $nun++;
        }

        if ($this->verificar_permisos('ZONAS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ZONAS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/zona.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ZONA';
            $nun++;
        }

        if ($this->verificar_permisos('ESTRUCTURA')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ESTRUCTURA';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/mapplus.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ESTRUCTURA';
            $nun++;
        }
		
		if($_SESSION['nombre']=='admin'){
		
			$this->arreglo_opciones[$nun]["tarea"] = 'CONFIGURACION';
			$this->arreglo_opciones[$nun]["imagen"]='modulos/uv/editor/img/config.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'CONFIGURACIONES';
			$nun++;
			
			$this->arreglo_opciones[$nun]["tarea"] = 'PUNTOS';
			$this->arreglo_opciones[$nun]["imagen"]='modulos/uv/editor/img/puntos.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'AGREGAR PUNTOS';
			$nun++;
			
			$this->arreglo_opciones[$nun]["tarea"] = 'PLANO';
			$this->arreglo_opciones[$nun]["imagen"]='modulos/uv/editor/img//plano.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'PLANO COMPLETO';
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
		/*
        $sql = "SELECT urb_id,urb_nombre,urb_interes_anual,urb_banco,urb_bolivianos,urb_dolares,cco_descripcion,cue_descripcion 
		FROM 
		urbanizacion
		inner join centrocosto on (urb_cco_id=cco_id)
		inner join cuenta on (urb_cue_id=cue_id)
		";
		*/
		
		$sql = "SELECT *
		FROM 
		urbanizacion
		";

        $this->set_sql($sql);

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nombre</th>
            <th>Monto Minimo Anticipo</th>
            <th>Interes Anual</th>
            <th>Unidad de Negocio</th>
            <th class="tOpciones" width="230px">Opciones</th>
        </tr>

        <?PHP
    }

    function mostrar_busqueda() {
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';
            echo "<td>";
            echo $objeto->urb_nombre;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->urb_monto_anticipo. ' $us';
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->urb_interes_anual. ' %';
            echo "&nbsp;</td>";
            echo "<td>";
            echo FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$objeto->urb_une_id'");
            echo "&nbsp;</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->urb_id);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from urbanizacion
				where urb_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['urb_nombre'] = $objeto->urb_nombre;
        $_POST['urb_fcosto'] = $objeto->urb_fcosto;
        $_POST['urb_cco_codigo'] = $objeto->urb_cco_codigo;
        $_POST['txt_urb_cco_codigo'] = FUNCIONES::atributo_bd_sql("select cco_descripcion as campo from con_cuenta_cc where cco_ges_id='$_SESSION[ges_id]' and cco_codigo='$objeto->urb_cco_codigo'");
        $_POST['urb_une_id'] = $objeto->urb_une_id;
//        echo FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='$objeto->urb_cco_codigo'");
        

        
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Nombre";
            $valores[$num]["valor"] = $_POST['urb_nombre'];
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

    function formulario_tcp($tipo) {
        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }

        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }


        $this->formulario->dibujar_tarea("URBANIZACION");

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script src="js/util.js"></script>
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="urb_nombre" id="urb_nombre" size="60" maxlength="250" value="<?php echo $_POST['urb_nombre']; ?>">
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Factor Costo</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="urb_fcosto" id="urb_fcosto" size="10" maxlength="250" value="<?php echo $_POST['urb_fcosto']; ?>">&nbsp; $us
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                            <div id="CajaInput">
                                <select id="urb_une_id" name="urb_une_id">
                                    <option value="">Seleccione</option>
                                    <?php $fun=new FUNCIONES();?>
                                    <?php $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[urb_une_id]);?>
                                </select>
                            </div>
                        </div>
                        <script>
                            function complete_cuenta_cc(){
                                var options_ca = {
                                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cc&",
                                    varname: "input",
                                    json: true,
                                    shownoresults: false,
                                    maxresults: 6,
                                    callback: function(obj) {
                                        $("#urb_cco_codigo").val(obj.info);
                                    }
                                };
                                var as_json2 = new _bsn.AutoSuggest('txt_urb_cco_codigo', options_ca);
                            }
                            complete_cuenta_cc();

                        </script>
                        <script>
                            mask_decimal('#urb_fcosto',null);
                        </script>

                    </div>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
        <?php
        if (!($ver)) {
            ?>
                                    <input type="submit" class="boton" name="" value="Guardar">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
            <?php
        } else {
            ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
            <?php
        }
        ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    function insertar_tcp() {
        $conec = new ADO();

        $sql = "insert into urbanizacion(urb_nombre,urb_fcosto,urb_une_id) values 
							('" . $_POST['urb_nombre'] . "','" . $_POST['urb_fcosto'] . "','$_POST[urb_une_id]')";

        $conec->ejecutar($sql);

        $mensaje = 'Urbanizaci&oacute;n Agregada Correctamente!!!';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function modificar_tcp() {
        $conec = new ADO();

        $sql = "update urbanizacion set 
							urb_nombre='" . $_POST['urb_nombre'] . "',
							urb_fcosto='" . $_POST['urb_fcosto'] . "',
                                                        urb_une_id='$_POST[urb_une_id]'
							where urb_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $mensaje = 'Urbanizaci?n Modificada Correctamente!!!';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar la Urbanizaci?n?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'urb_id');
    }

    function eliminar_tcp() {
        $conec = new ADO();

        $sql = "delete from urbanizacion where urb_id='" . $_POST['urb_id'] . "'";

        $conec->ejecutar($sql);

        $mensaje = 'Urbanizaci?n Eliminada Correctamente!!!';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function ver_zonas() {
        $this->formulario->dibujar_titulo("ESTRUCTURA : " . strtoupper($this->nombre_uv()));

        if ($_GET['gz'] == 'ok')
            $this->guardar_zona();

        if ($_GET['ez'] == 'ok')
            $this->eliminar_zona();

        if ($_GET['gmz'] == 'ok')
            $this->modificar_zona();

        if ($_GET['mz'] == 'ok') {
            $this->frm_editar_zona();
        } else {
            $this->frm_nueva_zona();

            $this->listado_de_zonas();
        }
    }

    function ver_uv() {
        $this->formulario->dibujar_titulo("ESTRUCTURA : " . strtoupper($this->nombre_uv()));
        if($_GET[acc]=='precio'){
            if($_POST){
                $this->guardar_zona_precios($_GET[uv_id]);
            }else{
                $this->tabla_precio($_GET[uv_id]);
            }
            
        }else{
            if ($_GET['gu'] == 'ok')
            $this->guardar_uv();

            if ($_GET['eu'] == 'ok')
                $this->eliminar_uv();


            $this->frm_nueva_uv();

            $this->listado_de_uv();
        }
        
    }
    function guardar_zona_precios($uv_id){
        if($uv_id){
            $sql="insert into uv_zona_precio(uvz_uv_id,uvz_zon_id,uvz_precio_cont,uvz_precio_max)values";
            $precios_cont=$_POST[uvz_precio_cont];
            $precios_max=$_POST[uvz_precio_max];
            $zonas=$_POST[uvz_zon_id];
            for($i=0;$i<  count($zonas);$i++){
                $zon_id=$zonas[$i];
                $zon_prec=  FUNCIONES::objeto_bd_sql("select * from uv_zona_precio where uvz_uv_id='$uv_id' and uvz_zon_id='$zon_id'");
                if($zon_prec){
                    $sql_update="update uv_zona_precio set uvz_precio_cont='$precios_cont[$i]', uvz_precio_max='$precios_max[$i]' where uvz_uv_id='$uv_id' and uvz_zon_id='$zon_id' ";
                    FUNCIONES::bd_query($sql_update);
                }else{
                    $sql_insert=$sql."('$uv_id','$zon_id','$precios_cont[$i]','$precios_max[$i]')";
                    FUNCIONES::bd_query($sql_insert);
                }
                
                
            }
            $this->formulario->ventana_volver('Precios Guardados Exitosamente.', $this->link . "?mod=" . $this->modulo . "&tarea=UV&id=" . $_GET['id'],'');
        }else{
            $this->formulario->ventana_volver('No exisiste la Fase.', $this->link . "?mod=" . $this->modulo . "&tarea=UV&id=" . $_GET['id'],'','error');
        }
        
    }
    function tabla_precio($uv_id){
        $objeto_uv=  FUNCIONES::objeto_bd_sql("select * from uv where uv_id='$uv_id'");
        $zonas=  FUNCIONES::objetos_bd_sql("select * from zona where zon_urb_id='$objeto_uv->uv_urb_id' order by zon_id");
        $precios=  FUNCIONES::objetos_bd_sql("select * from uv_zona_precio where uvz_uv_id='$uv_id'");
        $lprecios=array();
        for($i=0;$i<$precios->get_num_registros();$i++){
            $prec=$precios->get_objeto();
            $_precio=new stdClass();
            $_precio->cont=$prec->uvz_precio_cont;
            $_precio->max=$prec->uvz_precio_max;
            $lprecios[$prec->uvz_zon_id]=$_precio;
            $precios->siguiente();
        }
        ?>
        <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=uv&tarea=gestor.php?mod=uv&tarea=UV&id=<?php echo $_GET[id];?>&id=<?php echo $_GET[id];?>&acc=precio&uv_id=<?php echo $_GET[uv_id];?>" method="POST" enctype="multipart/form-data">  
            <br>
            <h3>Precios de Zonas por la Fase <?php echo $objeto_uv->uv_nombre;?></h3>
            <br>
            <table class="tablaLista" cellspacing="0" cellpadding="0" style="width:500px;">
                <thead>
                    <tr>
                        <th width="300px" >Zona</th>
                        <th width="200px" >Precio   </th>
                        <!--<th width="200px" >Precio a 8 A&ntilde;os</th>-->
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $zonas->get_num_registros(); $i++) {?>
                        <?php $zona=$zonas->get_objeto();?>
                        <tr>
                            <td>
                                <?php echo $zona->zon_nombre;?>
                                <input type="hidden" name="uvz_zon_id[]" value="<?php echo $zona->zon_id;?>">
                            </td>
                            <td><input type="text" name="uvz_precio_cont[]" class="uvzon_precio_cont" value="<?php echo $lprecios[$zona->zon_id]->cont;?>"></td>
                            <!--<td><input type="text" name="uvz_precio_max[]" class="uvzon_precio_max" value="<?php // echo $lprecios[$zona->zon_id]->max;?>"></td>-->
                        </tr>
                        <?php $zonas->siguiente();?>
                    <?php }?>
                </tbody>
            </table>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>
                        <input type="button" class="boton" name="" value="Guardar" id="btn_guardar">
                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=uv&tarea=UV&id=<?php echo $_GET[id];?>';">
                    </center>
                </div>
            </div>
            <script>
                $('#btn_guardar').click(function(){
                    var precios_cont=$('.uvzon_precio_cont');
                    
                    for(var i=0;i<precios_cont.size();i++){
                        var precio_cont=$(precios_cont[i]).val()*1;
                        if($(precios_cont[i]).val()==='' || precio_cont===0){
                            $.prompt('Ingrese todos los precios al contado');
                            return false;
                        }
                    }
//                    var precios_max=$('.uvzon_precio_max');
//                    for(var i=0;i<precios_max.size();i++){
//                        var precio_max=$(precios_max[i]).val()*1;
//                        if($(precios_max[i]).val()==='' || precio_max===0){
//                            $.prompt('Ingrese todos los precios a 8 A&ntilde;os');
//                            return false;
//                        }
//                    }
                    document.frm_sentencia.submit();
                });
            </script>
        </form>
        <?php
    }

    function ver_estructura() {
        $this->formulario->dibujar_titulo("ESTRUCTURA : " . strtoupper($this->nombre_uv()));

        if ($_GET['gm'] == 'ok')
            $this->guardar_manzano();

        if ($_GET['gl'] == 'ok')
            $this->guardar_lote();

        if ($_GET['el'] == 'ok')
            $this->eliminar_lote();

        if ($_GET['em'] == 'ok')
            $this->eliminar_manzano();

        if ($_GET['gml'] == 'ok')
            $this->modificar_lote();

        if ($_GET['mpl'] == 'ok') {
            $this->frm_modificar_precio_lote();
            return;
        }

        if ($_GET['gmpl'] == 'ok') {
            $this->guardar_precio_lote();
            return;
        }

        if ($_GET['ml'] == 'ok') {
            $this->frm_editar_lote();
        } else {
            // phpinfo();
            // exit;

            $this->frm_nuevo_manzano();

            $this->listado_de_manzanos();
        }
    }

    function guardar_precio_lote() {
        $sql = "select lot_zon_id from lote where lot_id='$_GET[lot]'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $obj = $conec->get_objeto();

        if ($conec->get_num_registros() > 0) {
            $sql = "update zona set zon_precio='$_POST[zon_precio]' where zon_id='$obj->lot_zon_id'";
            $conec->ejecutar($sql);
//                $this->formulario->mensaje('Correcto','Precio del Lote Modificado Correctamente.');
            $this->formulario->ventana_volver('Precio del Lote Modificado Correctamente.', $this->link . "?mod=" . $this->modulo . "&tarea=ESTRUCTURA&id=" . $_GET['id']);
        }
    }

    function puede_modificar_valorM2() {
        if ($this->usu->get_gru_id() == 'Gerencia General') {    
            return true;
        } else {
            return false;
        }
    }

    function frm_modificar_precio_lote() {
//            echo "formulario para modificar el precio del lote";
        $conec = new ADO();

        $sql = "select * from lote inner join zona on (lot_zon_id=zon_id) where lot_id='" . $_GET['lot'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();
        ?>
        <script type="text/javascript">
                                                            function actualizar_precio_m2() {
                                                                var precio_total = parseFloat(document.getElementById('lot_precio').value);
                                                                var superficie = parseFloat(document.getElementById('lot_superficie').value);
                                                                document.getElementById('zon_precio').value = precio_total / superficie;
                                                            }

                                                            function ValidarNumero(e) {
                                                                evt = e ? e : event;
                                                                tcl = (window.Event) ? evt.which : evt.keyCode;
                                                                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0))
                                                                {
                                                                    return false;
                                                                }

                                                                return true;
                                                            }

                                                            function validar_precio_lote() {
                                                                var precio_lote = parseFloat(document.getElementById('lot_precio').value);
                                                                var precio_m2 = parseFloat(document.getElementById('zon_precio').value);

                                                                if (precio_lote != '' && precio_lote > 0 && precio_m2 != '' && precio_m2 > 0) {
        //                                    alert('enviando el formulario');
                                                                    document.frm_lote.submit();
                                                                }
                                                            }
        </script>            
        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_lote" name="frm_lote" action="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&gmpl=ok&lot=<?php echo $_GET['lot']; ?>" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                    <tr>							                            
                        <td style="color: #006239; text-align:right;" width="">Nro de Lote</td>
                        <td width=""><input type="text" class="caja_texto" readonly name="lot_nro" id="lot_nro" size="10" maxlength="10" value="<?php echo $objeto->lot_nro; ?>"</td>


                        <td style="color: #006239; text-align:right;" width=""><span id="titulo_etiqueta1">Superficie</span></td>
                        <td width=""><input type="text" class="caja_texto" name="lot_superficie" id="lot_superficie" readonly size="10" maxlength="10" value="<?php echo $objeto->lot_superficie; ?>" ></td>

                        <td style="color: #006239; text-align:right;" width="">Precio Actual M2</td>
                        <td width="">
                            <input type="text" readonly id="zon_precio_ref" name="zon_precio_ref" value="<?php echo $objeto->zon_precio ?>" />
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Precio Total Actual</td>
                        <td width=""><input type="text" class="caja_texto" readonly name="lot_precio_ref" id="lot_precio_ref" size="8" maxlength="10" value="<?php echo $objeto->zon_precio * $objeto->lot_superficie; ?>"></td>

                    </tr>
                    <tr>
                        <td colspan="8">&nbsp;</td>
                    </tr>
                    <tr>							                                                                                    							
                        <td colspan="2"></td>
                        <td colspan="2"></td>

                        <td style="color: #006239; text-align:right;" width="">Nuevo Precio M2</td>
                        <td width="">
                            <input type="text" readonly id="zon_precio" name="zon_precio" value="<?php echo $objeto->zon_precio ?>" />
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Nuevo Precio Total</td>
                        <td width=""><input type="text" class="caja_texto" name="lot_precio" id="lot_precio" size="8" maxlength="10" value="<?php echo $objeto->zon_precio * $objeto->lot_superficie; ?>" onkeyup="javascript:actualizar_precio_m2();" onkeypress="return ValidarNumero(event);"></td>

                    </tr>
                    <tr>
                        <td colspan="8">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td><a href="javascript: validar_precio_lote();">Guardar Cambios</a></td>
                        
                        <td><a href="<?php echo $this->link . "?mod=" . $this->modulo . "&tarea=ESTRUCTURA&id=" . $_GET['id'];?>">Volver Atras</a></td>
                    </tr>

                </table>
                <br />

            </form>
        </div>    
        <?php
    }

    function frm_editar_lote($man = "") {
        $conec = new ADO();

        $sql = "select * from lote inner join zona on (lot_zon_id=zon_id) where lot_id='" . $_GET['lot'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();
        ?>
        <script>
                                                                    function validar_lote<?php echo $man; ?>()
                                                                    {
                                                                        var zona = document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.selectedIndex].value;

                                                                        var uv = document.frm_lote<?php echo $man; ?>.lot_uv_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_uv_id<?php echo $man; ?>.selectedIndex].value;

                                                                        if (document.frm_lote<?php echo $man; ?>.lot_nro<?php echo $man; ?>.value != '' && document.frm_lote<?php echo $man; ?>.lot_superficie<?php echo $man; ?>.value > 0 && zona != '' && uv != '')
                                                                        {
                                                                            document.frm_lote<?php echo $man; ?>.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el numero del lote (Numero Entero), la Superficie y seleccione la UV,Zona', {opacity: 0.8});


                                                                    }
                                                                    function calcular_precio<?php echo $man; ?>()
                                                                    {
                                                                        var zona = document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.selectedIndex].value;

                                                                        if (zona != '')
                                                                        {
                                                                            var datos = zona.split('|');
                                                                            var sup = parseFloat(document.frm_lote<?php echo $man; ?>.lot_superficie<?php echo $man; ?>.value);
                                                                            document.frm_lote<?php echo $man; ?>.lot_precio<?php echo $man; ?>.value = parseFloat(datos[1] * sup);
                                                                        }
                                                                        else
                                                                            document.frm_lote<?php echo $man; ?>.lot_precio<?php echo $man; ?>.value = 0;


                                                                    }
                                                                    function cambiar_titulo(tipo)
                                                                    {
                                                                        if (tipo == 'Lote')
                                                                        {
                                                                            $('#titulo_etiqueta1').html('Superficie');
                                                                            $('#fila_sup_vivienda').css('visibility', 'hidden');
                                                                            //$('#titulo_etiqueta2').html('Cuenta de Ingreso');
                                                                        }
                                                                        else
                                                                        {
                                                                            $('#titulo_etiqueta1').html('Precio');
                                                                            $('#fila_sup_vivienda').css('visibility', 'visible');
                                                                            //$('#titulo_etiqueta2').html('Cuenta de Egreso');
                                                                        }


                                                                    }
        </script>

        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_lote<?php echo $man; ?>" name="frm_lote<?php echo $man; ?>" action="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&gml=ok&lot=<?php echo $_GET['lot']; ?>" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:right;" width="">Tipo</td>
                        <td width="">
                            <select name="lot_tipo<?php echo $man; ?>" class="caja_texto" onchange="cambiar_titulo(this.value);">
                                <option value="Lote" <?php if ($objeto->lot_tipo == 'Lote') echo "selected='selected'"; ?>>Lote</option>
                                <option value="Vivienda" <?php if ($objeto->lot_tipo == 'Vivienda') echo "selected='selected'"; ?>>Vivienda</option>
                            </select>
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Nro de Lote</td>
                        <td width=""><input type="text" class="caja_texto" name="lot_nro<?php echo $man; ?>" id="lot_nro<?php echo $man; ?>" size="10" maxlength="10" value="<?php echo $objeto->lot_nro; ?>" onKeyPress="return ValidarNumero(event);"></td>

        <?php
        if ($objeto->lot_tipo == 'Lote')
            $titulo_etiqueta1 = 'Superficie';
        else
            $titulo_etiqueta1 = 'Precio';
        ?>
                        <td style="color: #006239; text-align:right;" width=""><span id="titulo_etiqueta1"><?php echo $titulo_etiqueta1; ?></span></td>
                        <td width=""><input type="text" class="caja_texto" name="lot_superficie<?php echo $man; ?>" id="lot_superficie<?php echo $man; ?>" size="10" maxlength="10" value="<?php echo $objeto->lot_superficie; ?>" <?php if($this->usu->get_gru_id() <> 'Gerencia General'){ ?> readonly="readonly" <?php } ?>></td>
                        <td style="color: #006239; text-align:right;" width="">UV</td>
                        <td width="">
                            <select name="lot_uv_id<?php echo $man; ?>" class="caja_texto" onchange="">
                                <option value="">Seleccione</option>
								<?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_GET['id'] . "'", $objeto->lot_uv_id);
                                ?>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">Zona</td>
                        <td width="">
                            <select style="width:100px;" name="lot_zon_id<?php echo $man; ?>" class="caja_texto" onchange="javascript: calcular_precio<?php echo $man; ?>();">
                                <option value="">Seleccione</option>
								<?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select concat(zon_id,'|',zon_precio) as id,concat(zon_nombre,' (M2 ',zon_precio,')') as nombre from zona where zon_urb_id='" . $_GET['id'] . "'", $objeto->lot_zon_id . '|' . $objeto->zon_precio);
                                ?>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">Precio Total</td>
                        <td width=""><input readonly="readonly" type="text" class="caja_texto" name="lot_precio<?php echo $man; ?>" id="lot_precio<?php echo $man; ?>" size="8" maxlength="10" value="<?php echo $objeto->zon_precio * $objeto->lot_superficie; ?>" ></td>
                        <td ><a href="javascript: validar_lote<?php echo $man; ?>(); ">Modificar Lote</a></td>
                    </tr>

                    <tr id="fila_sup_vivienda" <?php if ($objeto->lot_tipo == 'Lote') { ?> style="visibility:hidden;" <?php } else { ?>style="visibility:visible;" <?php } ?>>
                        <td style="color: #006239; text-align:right;" width="">Superficie</td>
                        <td width=""><input type="text" class="caja_texto" name="lot_sup_vivienda<?php echo $man; ?>" id="lot_sup_vivienda<?php echo $man; ?>" size="8" maxlength="10" value="<?php echo $objeto->lot_sup_vivienda; ?>" ></td>
                    </tr>				
                </table>
                <br />
                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:left;">Colindancia&nbsp;&nbsp;</td>
                        <td style="color: #006239; text-align:right;" width="">Norte</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_norte<?php echo $man; ?>" id="lot_col_norte<?php echo $man; ?>" size="10" value="<?php echo $objeto->lot_col_norte; ?>" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Sur</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_sur<?php echo $man; ?>" id="lot_col_sur<?php echo $man; ?>" size="10" value="<?php echo $objeto->lot_col_sur; ?>" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Este</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_este<?php echo $man; ?>" id="lot_col_este<?php echo $man; ?>" size="10" value="<?php echo $objeto->lot_col_este; ?>" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Oeste</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_oeste<?php echo $man; ?>" id="lot_col_oeste<?php echo $man; ?>" size="10" value="<?php echo $objeto->lot_col_oeste; ?>" >
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    function eliminar_manzano() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('lot_man_id');
        $parametros[1] = array($_GET['man']);
        $parametros[2] = array('lote');

        if ($verificar->validar($parametros)) {
            $conec = new ADO();

            $sql = "delete from manzano where man_id='" . $_GET['man'] . "'";

            $conec->ejecutar($sql);

            $this->formulario->mensaje('Correcto', 'Manzano Eliminado Correctamente.');
        } else {
            $this->formulario->mensaje('Error', 'El manzano no puede ser eliminado por que tiene lotes relacionados.');
        }
    }

    function eliminar_zona() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('lot_zon_id');
        $parametros[1] = array($_GET['zon']);
        $parametros[2] = array('lote');

        if ($verificar->validar($parametros)) {
            $conec = new ADO();

            $sql = "delete from zona where zon_id='" . $_GET['zon'] . "'";

            $conec->ejecutar($sql);

            $this->formulario->mensaje('Correcto', 'Zona Eliminado Correctamente.');
        } else {
            $this->formulario->mensaje('Error', 'La zona no puede ser eliminada por que tiene lotes relacionados.');
        }
    }

    function eliminar_uv() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('lot_uv_id');
        $parametros[1] = array($_GET['uv']);
        $parametros[2] = array('lote');

        if ($verificar->validar($parametros)) {
            $conec = new ADO();

            $sql = "delete from uv where uv_id='" . $_GET['uv'] . "'";

            $conec->ejecutar($sql);

            $this->formulario->mensaje('Correcto', 'UV Eliminada Correctamente.');
        } else {
            $this->formulario->mensaje('Error', 'La UV no puede ser eliminada por que tiene lotes relacionados.');
        }
    }

    function eliminar_lote() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('ven_lot_id');
        $parametros[1] = array($_GET['lot']);
        $parametros[2] = array('venta');

        if ($verificar->validar($parametros)) {
            $conec = new ADO();

            $sql = "delete from lote where lot_id='" . $_GET['lot'] . "'";

            $conec->ejecutar($sql);

            $this->formulario->mensaje('Correcto', 'Lote Eliminado Correctamente.');
        } else {
            $this->formulario->mensaje('Error', 'El lote no puede ser eliminado por que tiene ventas relacionados.');
        }
    }

    function guardar_lote() {
        $conec = new ADO();

        $cad = 'lot_nro' . $_GET['man'];
        $cad2 = 'lot_superficie' . $_GET['man'];
        $cad3 = 'lot_zon_id' . $_GET['man'];
        $zona = $_POST[$cad3];
        $datos = explode("|", $zona);
        $cad4 = 'lot_uv_id' . $_GET['man'];
        $cad5 = 'lot_tipo' . $_GET['man'];

        $cad6 = 'lot_col_norte' . $_GET['man'];
        $cad7 = 'lot_col_sur' . $_GET['man'];
        $cad8 = 'lot_col_este' . $_GET['man'];
        $cad9 = 'lot_col_oeste' . $_GET['man'];
        $cad10 = 'lot_sup_vivienda' . $_GET['man'];

        $sql = "select lot_id from lote where lot_nro='" . $_POST[$cad] . "' and lot_man_id='" . $_GET['man'] . "' and lot_uv_id='" . $_POST[$cad4] . "'";

        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();

        if ($num > 0) {
            $this->formulario->mensaje('Error', 'El Nro de Lote ya se encuentra agregado en el Manzano.');
        } else {
            $sql = "insert into lote(lot_nro,lot_estado,lot_man_id,lot_superficie,lot_zon_id,lot_uv_id,lot_tipo,lot_col_norte,lot_col_sur,lot_col_este,lot_col_oeste,lot_sup_vivienda) 
			values ('" . $_POST[$cad] . "','Disponible','" . $_GET['man'] . "','" . $_POST[$cad2] . "','" . $datos[0] . "','" . $_POST[$cad4] . "','" . $_POST[$cad5] . "','" . $_POST[$cad6] . "','" . $_POST[$cad7] . "','" . $_POST[$cad8] . "','" . $_POST[$cad9] . "','" . $_POST[$cad10] . "')";

            $conec->ejecutar($sql, false);

            $this->formulario->mensaje('Correcto', 'Lote Agregado Correctamente.');
        }
    }

    function modificar_lote() {
        if ($_POST['lot_tipo'] == 'Lote') {
            $_POST['lot_sup_vivienda'] = 0;
        }

        $conec = new ADO();

        $sql = "
		update lote set
		lot_nro='" . $_POST['lot_nro'] . "',
		lot_tipo='" . $_POST['lot_tipo'] . "',
		lot_superficie='" . $_POST['lot_superficie'] . "',
		lot_zon_id='" . $_POST['lot_zon_id'] . "',
		lot_uv_id='" . $_POST['lot_uv_id'] . "',
		lot_col_norte='" . $_POST['lot_col_norte'] . "',
		lot_col_sur='" . $_POST['lot_col_sur'] . "',
		lot_col_este='" . $_POST['lot_col_este'] . "',
		lot_col_oeste='" . $_POST['lot_col_oeste'] . "',
		lot_sup_vivienda='" . $_POST['lot_sup_vivienda'] . "'
		where lot_id='" . $_GET['lot'] . "'
		";

        $conec->ejecutar($sql);

        $this->formulario->mensaje('Correcto', 'Lote Modificado Correctamente.');
    }

    function guardar_manzano() {
        $conec = new ADO();

        $sql = "select man_nro from manzano where man_nro='" . $_POST['man_nro'] . "' and man_urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        if ($num > 0) {
            $this->formulario->mensaje('Error', 'El Nro de Manzano ya se encuentra agregado en la Urbanizaci?n.');
        } else {
            $sql = "insert into manzano(man_nro,man_urb_id) values ('" . $_POST['man_nro'] . "','" . $_GET['id'] . "')";

            $conec->ejecutar($sql);

            $this->formulario->mensaje('Correcto', 'Manzano Agregado Correctamente.');
        }
    }

    function listado_de_manzanos() {
        if ($_POST['man_nro'] <> "" && $_POST['uv'] <> "") {

            $conec = new ADO();
            $conec2 = new ADO();

            $sql = "select man_id, man_nro from manzano where man_urb_id='" . $_GET['id'] . "'";

            if ($_POST['man_nro'] <> '')
                $sql.=" and man_nro ='" . $_POST['man_nro'] . "'";

            $conec->ejecutar($sql);

            $num = $conec->get_num_registros();

            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();
                ?>
                <div style="margin:10px 0px 0px 100px; float:left; clear:both; ">
                    <table style="font-family: tahoma; font-size: 11px;" width="700px" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="color: #006239;" width="120px" ><b>MANZANO NRO <?php echo $objeto->man_nro; ?></b></td>
                            <td  style="color: #006239;" width="580px" ><?php $this->frm_nuevo_lote($objeto->man_id); ?></td>
                        </tr>
                        <tr>
                            <td  style="color: #006239;" width="120px" valign="top">
                        <center>
                            <a class="linkOpciones" href="javascript:eliminar_manzano(<?php echo $_GET['id']; ?>,<?php echo $objeto->man_id; ?>);">
                                <img src="images/b_drop.png" border="0" title="ELIMINAR MANZANO" alt="ELIMINAR MANZANO" width="16px" height="16px">
                            </a>
                        </center>
                        </td>
                        <td  style="color: #006239; padding:3px 0px 0px 0px;" width="380px" ><?php $this->listado_de_lotes($objeto->man_id, $_POST['uv'], $conec2); ?></td>
                        </tr>	
                    </table>
                </div>
                <br/><br/>
                <?php
                $conec->siguiente();
            }
        }
    }

    function listado_de_lotes($man, $uv, $conec) {
        $sql = "select lot_id,lot_tipo,lot_nro,lot_estado,lot_superficie,lot_sup_vivienda,zon_nombre,zon_precio,uv_nombre ,lot_codigo
		from 
		lote 
		inner join zona on (lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)		
		where 
		lot_man_id='" . $man . "' and lot_uv_id='$uv'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $s_man = 0;
        ?>		
        <table class="tablaLista" cellpadding="0" cellspacing="0" width="840px">
            <thead>
                <tr>
                    <th width="130px">ID</th>
                    <!--<th width="130px">Codigo</th>-->
                    <th width="130px">Tipo</th>
                    <th width="130px">Nro de Lote</th>
                    <th width="130px">Sup. Lote</th>
                    <th width="130px">Sup. Vivienda</th>
                    <th width="130px">UV</th>
                    <th width="130px">Zona</th>
                    
                    <th width="130px">Precio</th>
                    <th width="130px">Estado</th>
                    <th class="tOpciones" width="55px">OP</th>
                </tr>
            </thead>
            <tbody>
        <?php
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
                    <tr>
                        <td><?php echo $objeto->lot_id; ?></td>
                        <!--<td><?php echo $objeto->lot_codigo; ?></td>-->
                        <td><?php echo $objeto->lot_tipo; ?></td>
                        <td><?php echo $objeto->lot_nro; ?></td>
                        <td><?php if ($objeto->lot_tipo == 'Lote') {
                echo $objeto->lot_superficie;
                $s_man = $s_man + $objeto->lot_superficie;
            } else {
                echo 0;
            } ?></td>
                        <td><?php if ($objeto->lot_tipo == 'Vivienda') {
                echo $objeto->lot_sup_vivienda;
            } else {
                echo 0;
            } ?></td>
                        <td><?php echo $objeto->uv_nombre; ?></td>
                        <td><?php echo $objeto->zon_nombre; ?></td>
                        
                        <td><?php echo ($objeto->zon_precio * $objeto->lot_superficie); ?></td>
                        <td><?php echo $objeto->lot_estado; ?></td>
                        <td>
                <center>
            <?php
            if ($objeto->lot_estado == 'Disponible' || $objeto->lot_estado == 'Reservado') {
                ?>

                        <table width="45px" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>
                                    <?php if($objeto->lot_estado == 'Disponible'){?>
                                        <center>
                                            <a class="linkOpciones" href="javascript:eliminar_lote(<?php echo $_GET['id']; ?>,<?php echo $objeto->lot_id; ?>);">
                                                <img src="images/b_drop.png" border="0" title="ELIMINAR LOTE" alt="ELIMINAR LOTE" width="16px" height="16px">
                                            </a>
                                        </center>
                                    <?php }?>
                            </td>
                            <td>
                                <?php if ($objeto->lot_estado == 'Disponible' || $objeto->lot_estado == 'Reservado') { ?>
                                <center>
                                    <a class="linkOpciones" href="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&ml=ok&lot=<?php echo $objeto->lot_id; ?>">
                                        <img src="images/b_edit.png" border="0" title="MODIFICAR LOTE" alt="MODIFICAR LOTE" width="16px" height="16px">
                                    </a>
                                </center>
                                <?php } ?>
                            </td>
                            <?php if ($this->puede_modificar_valorM2()) {?>
                            <td>
                            <center>
                                <a class="linkOpciones" href="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&mpl=ok&lot=<?php echo $objeto->lot_id; ?>">
                                    <img src="images/precio.png" border="0" title="MODIFICAR PRECIO LOTE" alt="MODIFICAR PRECIO LOTE" width="16px" height="16px">
                                </a>
                            </center>
                            </td>
                            <?php }?>
                            </tr>
                        </table>
                <?php
            }
            ?>
                </center>
            </td>
            </tr>

            <?php
            $conec->siguiente();
        }
        ?>

        </tbody>
        <tfoot>
            <tr>
                <td>
                </td>
                <td>
                </td>
                <td style="text-align:left; ">
        <?php echo $s_man; ?>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
        </tfoot>
        </table>
                <?php
            }

            function frm_nuevo_lote($man) {
                ?>
        <script>
                                                                    function validar_lote<?php echo $man; ?>()
                                                                    {
                                                                        var zona = document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.selectedIndex].value;

                                                                        var uv = document.frm_lote<?php echo $man; ?>.lot_uv_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_uv_id<?php echo $man; ?>.selectedIndex].value;

                                                                        if (document.frm_lote<?php echo $man; ?>.lot_nro<?php echo $man; ?>.value != '' && document.frm_lote<?php echo $man; ?>.lot_superficie<?php echo $man; ?>.value > 0 && zona != '' && uv != '')
                                                                        {
                                                                            document.frm_lote<?php echo $man; ?>.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el numero del lote (Numero Entero), la Superficie y seleccione la UV,Zona', {opacity: 0.8});


                                                                    }
                                                                    function calcular_precio<?php echo $man; ?>()
                                                                    {
                                                                        var zona = document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.options[document.frm_lote<?php echo $man; ?>.lot_zon_id<?php echo $man; ?>.selectedIndex].value;

                                                                        if (zona != '')
                                                                        {
                                                                            var datos = zona.split('|');
                                                                            var sup = parseFloat(document.frm_lote<?php echo $man; ?>.lot_superficie<?php echo $man; ?>.value);
                                                                            document.frm_lote<?php echo $man; ?>.lot_precio<?php echo $man; ?>.value = parseFloat(datos[1] * sup);
                                                                        }
                                                                        else
                                                                            document.frm_lote<?php echo $man; ?>.lot_precio<?php echo $man; ?>.value = 0;


                                                                    }

                                                                    function cambiar_titulo(tipo)
                                                                    {
                                                                        if (tipo == 'Lote')
                                                                        {
                                                                            $('#titulo_etiqueta1').html('Superficie');
                                                                            $('#fila_sup_vivienda').css('visibility', 'hidden');
                                                                            //$('#titulo_etiqueta2').html('Cuenta de Ingreso');
                                                                        }
                                                                        else
                                                                        {
                                                                            $('#titulo_etiqueta1').html('Precio');
                                                                            $('#fila_sup_vivienda').css('visibility', 'visible');
                                                                            //$('#titulo_etiqueta2').html('Cuenta de Egreso');
                                                                        }


                                                                    }
        </script>
        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_lote<?php echo $man; ?>" name="frm_lote<?php echo $man; ?>" action="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&gl=ok&man=<?php echo $man; ?>" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:right;" width="">Tipo</td>
                        <td width="">
                            <select name="lot_tipo<?php echo $man; ?>" class="caja_texto" onchange="cambiar_titulo(this.value);">
                                <option value="Lote">Lote</option>
                                <option value="Vivienda">Vivienda</option>
                            </select>
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Nro de Lote</td>
                        <td width=""><input type="text" class="caja_texto" name="lot_nro<?php echo $man; ?>" id="lot_nro<?php echo $man; ?>" size="10" maxlength="10" value=""></td>

                        <td style="color: #006239; text-align:right;" width=""><span id="titulo_etiqueta1">Superficie</span></td>
                        <td width=""><input type="text" class="caja_texto" name="lot_superficie<?php echo $man; ?>" id="lot_superficie<?php echo $man; ?>" size="10" maxlength="10" value="" ><input type="hidden" class="caja_texto" name="man_nro" id="man_nro" size="10" maxlength="10" value="<?php echo $_POST['man_nro']; ?>" ></td>
                        <td style="color: #006239; text-align:right;" width="">UV</td>
                        <td width="">
                            <select name="lot_uv_id<?php echo $man; ?>" class="caja_texto" onchange="">
                                <option value="">Seleccione</option>
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_GET['id'] . "'", $_POST['lot_uv_id']);
        ?>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">Zona</td>
                        <td width="">
                            <select style="width:100px;" name="lot_zon_id<?php echo $man; ?>" class="caja_texto" onchange="javascript: calcular_precio<?php echo $man; ?>();">
                                <option value="">Seleccione</option>
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select concat(zon_id,'|',zon_precio) as id,concat(zon_nombre,' (M2 ',zon_precio,')') as nombre from zona where zon_urb_id='" . $_GET['id'] . "'", $_POST['lot_zon_id']);
        ?>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">Precio Total</td>
                        <td width=""><input readonly="readonly" type="text" class="caja_texto" name="lot_precio<?php echo $man; ?>" id="lot_precio<?php echo $man; ?>" size="8" maxlength="10" value="0" ></td>
                        <td ><a href="javascript: validar_lote<?php echo $man; ?>(); ">Agregar Lote</a></td>
                    </tr>

                    <tr id="fila_sup_vivienda" style="visibility:hidden;">
                        <td style="color: #006239; text-align:right;" width="">Superf. Vivienda</td>
                        <td width=""><input type="text" class="caja_texto" name="lot_sup_vivienda<?php echo $man; ?>" id="lot_sup_vivienda<?php echo $man; ?>" size="8" maxlength="10" value="0" ></td>
                    </tr>

                </table>


                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:left;">Colindancia&nbsp;&nbsp;</td>
                        <td style="color: #006239; text-align:right;" width="">Norte</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_norte<?php echo $man; ?>" id="lot_col_norte<?php echo $man; ?>" size="10" value="" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Sur</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_sur<?php echo $man; ?>" id="lot_col_sur<?php echo $man; ?>" size="10" value="" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Este</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_este<?php echo $man; ?>" id="lot_col_este<?php echo $man; ?>" size="10" value="" >
                        </td>

                        <td style="color: #006239; text-align:right;" width="">Oeste</td>
                        <td width="">
                            <input type="text" class="caja_texto" name="lot_col_oeste<?php echo $man; ?>" id="lot_col_oeste<?php echo $man; ?>" size="10" value="" >
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    function frm_nuevo_manzano() {
        ?>
        <script>
                                                                    function validar(form)
                                                                    {
                                                                        //if (parseInt(document.frm_manzana.man_nro.value) > 0)
                                                                        if (document.frm_manzana.man_nro.value != '')
                                                                        {
                                                                            form.action = 'gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&gm=ok';
                                                                            form.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el numero del manzano (Numero Entero)', {opacity: 0.8});


                                                                    }
                                                                    function ValidarNumero(e)
                                                                    {
                                                                        evt = e ? e : event;
                                                                        tcl = (window.Event) ? evt.which : evt.keyCode;
                                                                        if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0))
                                                                        {
                                                                            return false;
                                                                        }

                                                                        return true;
                                                                    }
                                                                    function eliminar_lote(uv, lote)
                                                                    {
                                                                        var txt = 'Esta seguro de eliminar el lote?';

                                                                        $.prompt(txt, {
                                                                            buttons: {Eliminar: true, Cancelar: false},
                                                                            callback: function(v, m, f) {

                                                                                if (v) {
                                                                                    location.href = 'gestor.php?mod=uv&tarea=ESTRUCTURA&id=' + uv + '&el=ok&lot=' + lote;
                                                                                }

                                                                            }
                                                                        });
                                                                    }
                                                                    function eliminar_manzano(uv, manzano)
                                                                    {
                                                                        var txt = 'Esta seguro de eliminar el manzano?';

                                                                        $.prompt(txt, {
                                                                            buttons: {Eliminar: true, Cancelar: false},
                                                                            callback: function(v, m, f) {

                                                                                if (v) {
                                                                                    location.href = 'gestor.php?mod=uv&tarea=ESTRUCTURA&id=' + uv + '&em=ok&man=' + manzano;
                                                                                }

                                                                            }
                                                                        });
                                                                    }
                                                                    function buscar(form)
                                                                    {
                                                                        form.submit();
                                                                    }
        </script>
        <div style="margin-top:10px; float:left;">
            <form id="frm_manzanab" name="frm_manzanab" action="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&bm=ok" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="300px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239;" width="100px">UV</td>
                        <td>
                            <select style="width:120px;" id="uv" name="uv" class="caja_texto">
                                <option value=''>Seleccione</option>       
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select uv_id as id,uv_nombre as nombre,cast(uv_nombre as signed)as numero from uv where uv_urb_id='" . $_GET['id'] . "' order by numero asc", $_POST['uv']);
        ?>

                            </select>
                        </td>
                        <td style="color: #006239;" width="100px">Buscar Manzano</td>
                        <td>
                            <select style="width:120px;" name="man_nro" class="caja_texto" onchange="javascript: buscar(this.form);">
                                <option value=''>Seleccione</option>       
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select man_nro as id,concat('Manzano Nro: ',man_nro) as nombre,cast(man_nro as SIGNED) as numero from manzano where man_urb_id='" . $_GET['id'] . "' order by numero asc", $_POST['man_nro']);
        ?>

                            </select>
                        </td>
                    </tr>				
                </table>
            </form>
        </div>
        <div style="margin-top:10px; margin-left:100px; float:left; clear:right;">
            <form id="frm_manzana" name="frm_manzana" action="gestor.php?mod=uv&tarea=ESTRUCTURA&id=<?php echo $_GET['id']; ?>&bm=ok" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="300px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239;" width="100px">Nro de Manzano</td>
                        <td width="100px"><input type="text" class="caja_texto" name="man_nro" id="man_nro" size="10" maxlength="10" value=""></td>
                        <td style="padding-left: 20px;"><input type="button" class="boton" name="" value="Agregar Manzano" onclick="javascript: validar(this.form);"></td>
                    </tr>				
                </table>
            </form>
        </div>
        <?php
    }

    function nombre_uv() {
        $conec = new ADO();

        $sql = "select urb_nombre from urbanizacion where urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->urb_nombre;
    }

    function frm_editar_zona() {
        $conec = new ADO();

        $sql = "select * from zona where zon_id='" . $_GET['zon'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();
        ?>
        <script>
                                                                    function validar(form)
                                                                    {
                                                                        if (document.frm_zona.nombre.value != '' && document.frm_zona.precio.value != '' && document.frm_zona.color.value != '' && document.frm_zona.cuota.value != '')
                                                                        {
                                                                            form.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el nombre de la zona, el precio por metro, el % de cuota inicial y el color', {opacity: 0.8});


                                                                    }

        </script>
        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_zona" name="frm_zona" action="gestor.php?mod=uv&tarea=ZONAS&id=<?php echo $_GET['id']; ?>&zon=<?php echo $_GET['zon']; ?>&gmz=ok" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="800px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:right;" width="">Nombre de la Zona</td>
                        <td width="100px"><input type="text" class="caja_texto" name="nombre" id="nombre" size="30" maxlength="255" value="<?php echo $objeto->zon_nombre; ?>"></td>

                        <td style="color: #006239; text-align:right;" width="">Precio del Metro2</td>
                        <td width=""><input type="text" class="caja_texto" name="precio" id="precio" size="10" maxlength="10" value="<?php echo $objeto->zon_precio; ?>"></td>
                        <td width="">
                            <select name="moneda" class="caja_texto" >
                                <option value="1" <?php if ($objeto->zon_moneda == '1') echo 'selected="selected"'; ?>>Bolivianos</option>
                                <option value="2" <?php if ($objeto->zon_moneda == '2') echo 'selected="selected"'; ?>>Dolares</option>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">% Cuota Inicial</td>
                        <td width=""><input type="text" class="caja_texto" name="cuota" id="cuota" size="10" maxlength="10" value="<?php echo $objeto->zon_cuota_inicial; ?>"></td>
                        <td style="color: #006239; text-align:right;" width="">Color</td>
                        <td width="">
                            <select name="color" class="caja_texto" >
                                <option value="">Seleccione</option>
                                <option style="background-color:#C00000;" value="#C00000" <?php if ($objeto->zon_color == '#C00000') echo 'selected="selected"'; ?>>Rojo Oscuro</option>
                                <option style="background-color:#FF0000;" value="#FF0000" <?php if ($objeto->zon_color == '#FF0000') echo 'selected="selected"'; ?>>Rojo</option>
                                <option style="background-color:#FFC000;" value="#FFC000" <?php if ($objeto->zon_color == '#FFC000') echo 'selected="selected"'; ?>>Naranja</option>
                                <option style="background-color:#FFFF00;" value="#FFFF00" <?php if ($objeto->zon_color == '#FFFF00') echo 'selected="selected"'; ?>>Amarillo</option>
                                <option style="background-color:#92D050;" value="#92D050" <?php if ($objeto->zon_color == '#92D050') echo 'selected="selected"'; ?>>Verde Claro</option>
                                <option style="background-color:#00B050;" value="#00B050" <?php if ($objeto->zon_color == '#00B050') echo 'selected="selected"'; ?>>Verde</option>
                                <option style="background-color:#00B0F0;" value="#00B0F0" <?php if ($objeto->zon_color == '#00B0F0') echo 'selected="selected"'; ?>>Azul Claro</option>
                                <option style="background-color:#0070C0;" value="#0070C0" <?php if ($objeto->zon_color == '#0070C0') echo 'selected="selected"'; ?>>Azul</option>
                                <option style="background-color:#002060;" value="#002060" <?php if ($objeto->zon_color == '#002060') echo 'selected="selected"'; ?>>Azul Oscuro</option>
                                <option style="background-color:#7030A0;" value="#7030A0" <?php if ($objeto->zon_color == '#7030A0') echo 'selected="selected"'; ?>>Purpura</option>
                            </select>
                        </td>
                        <td ><input type="button" class="boton" name="" value="Modificar Zona" onclick="javascript: validar(this.form);"></td>
                    </tr>				
                </table>
            </form>
        </div>
        <?php
    }

    function frm_nueva_zona() {
        ?>
        <script>
                                                                    function validar(form)
                                                                    {
                                                                        if (document.frm_zona.nombre.value != '' && document.frm_zona.precio.value != '' && document.frm_zona.color.value != '' && document.frm_zona.cuota.value != '')
                                                                        {
                                                                            form.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el nombre de la zona, el precio por metro, el % de cuota inicial y el color', {opacity: 0.8});


                                                                    }
                                                                    function eliminar_zona(uv, zona)
                                                                    {
                                                                        var txt = 'Esta seguro de eliminar la zona?';

                                                                        $.prompt(txt, {
                                                                            buttons: {Eliminar: true, Cancelar: false},
                                                                            callback: function(v, m, f) {

                                                                                if (v) {
                                                                                    location.href = 'gestor.php?mod=uv&tarea=ZONAS&id=' + uv + '&ez=ok&zon=' + zona;
                                                                                }

                                                                            }
                                                                        });
                                                                    }
        </script>
        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_zona" name="frm_zona" action="gestor.php?mod=uv&tarea=ZONAS&id=<?php echo $_GET['id']; ?>&gz=ok" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="800px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:right;" width="">Nombre de la Zona</td>
                        <td width="100px"><input type="text" class="caja_texto" name="nombre" id="nombre" size="30" maxlength="255" value=""></td>

                        <td style="color: #006239; text-align:right;" width="">Precio del Metro2</td>
                        <td width=""><input type="text" class="caja_texto" name="precio" id="precio" size="10" maxlength="10" value=""></td>
                        <td width="">
                            <select name="moneda" class="caja_texto" >
                                <option value="1">Bolivianos</option>
                                <option value="2">Dolares</option>
                            </select>
                        </td>
                        <td style="color: #006239; text-align:right;" width="">% Cuota Inicial</td>
                        <td width=""><input type="text" class="caja_texto" name="cuota" id="cuota" size="10" maxlength="10" value=""></td>
                        <td style="color: #006239; text-align:right;" width="">Color</td>
                        <td width="">
                            <select name="color" class="caja_texto" >
                                <option value="">Seleccione</option>
                                <option style="background-color:#C00000;" value="#C00000">Rojo Oscuro</option>
                                <option style="background-color:#FF0000;" value="#FF0000">Rojo</option>
                                <option style="background-color:#FFC000;" value="#FFC000">Naranja</option>
                                <option style="background-color:#FFFF00;" value="#FFFF00">Amarillo</option>
                                <option style="background-color:#92D050;" value="#92D050">Verde Claro</option>
                                <option style="background-color:#00B050;" value="#00B050">Verde</option>
                                <option style="background-color:#00B0F0;" value="#00B0F0">Azul Claro</option>
                                <option style="background-color:#0070C0;" value="#0070C0">Azul</option>
                                <option style="background-color:#002060;" value="#002060">Azul Oscuro</option>
                                <option style="background-color:#7030A0;" value="#7030A0">Purpura</option>
                            </select>
                        </td>
                        <td ><input type="button" class="boton" name="" value="Agregar Zona" onclick="javascript: validar(this.form);"></td>
                    </tr>				
                </table>
            </form>
        </div>
        <?php
    }

    function frm_nueva_uv() {
        ?>
        <script>
                                                                    function validar(form)
                                                                    {
                                                                        if (document.frm_uv.nombre.value)
                                                                        {
                                                                            form.submit();
                                                                        }
                                                                        else
                                                                            $.prompt('Ingrese el nombre de la UV', {opacity: 0.8});

                                                                    }
                                                                    function eliminar_uv(uv, id)
                                                                    {
                                                                        var txt = 'Esta seguro de eliminar la UV?';

                                                                        $.prompt(txt, {
                                                                            buttons: {Eliminar: true, Cancelar: false},
                                                                            callback: function(v, m, f) {

                                                                                if (v) {
                                                                                    location.href = 'gestor.php?mod=uv&tarea=UV&id=' + uv + '&eu=ok&uv=' + id;
                                                                                }

                                                                            }
                                                                        });
                                                                    }
        </script>
        <div style="margin-top:10px; float:left; clear:both;">
            <form id="frm_uv" name="frm_uv" action="gestor.php?mod=uv&tarea=UV&id=<?php echo $_GET['id']; ?>&gu=ok" method="POST" enctype="multipart/form-data">  
                <table style="font-family: tahoma; font-size: 11px;" width="700px" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="color: #006239; text-align:right;" width="">Nombre de la UV</td>
                        <td width="100px"><input type="text" class="caja_texto" name="nombre" id="nombre" size="30" maxlength="255" value=""></td>

                        <td ><input type="button" class="boton" name="" value="Agregar UV" onclick="javascript: validar(this.form);"></td>
                    </tr>				
                </table>
            </form>
        </div>
        <?php
    }

    function guardar_zona() {
        $conec = new ADO();

        $sql = "insert into zona(zon_nombre,zon_precio,zon_urb_id,zon_color,zon_cuota_inicial,zon_moneda) values ('" . $_POST['nombre'] . "','" . $_POST['precio'] . "','" . $_GET['id'] . "','" . $_POST['color'] . "','" . $_POST['cuota'] . "','" . $_POST['moneda'] . "')";

        $conec->ejecutar($sql);

        $this->formulario->mensaje('Correcto', 'Zona Agregada Correctamente.');
    }

    function modificar_zona() {
        $conec = new ADO();

        $sql = "
		update zona set
		zon_nombre='" . $_POST['nombre'] . "',
		zon_precio='" . $_POST['precio'] . "',
		zon_color='" . $_POST['color'] . "',
		zon_cuota_inicial='" . $_POST['cuota'] . "',
		zon_moneda='" . $_POST['moneda'] . "'
		where zon_id='" . $_GET['zon'] . "'
		";

        $conec->ejecutar($sql);

        $this->formulario->mensaje('Correcto', 'Zona Modificada Correctamente.');
    }

    function guardar_uv() {
        $conec = new ADO();

        $sql = "insert into uv(uv_nombre,uv_urb_id) values ('" . $_POST['nombre'] . "','" . $_GET['id'] . "')";

        $conec->ejecutar($sql);

        $this->formulario->mensaje('Correcto', 'UV Agregada Correctamente.');
    }

    function listado_de_zonas() {
        $conec = new ADO();

        $sql = "select zon_id, zon_nombre,zon_precio,zon_color,zon_cuota_inicial,zon_moneda from zona where zon_urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();
        ?>		
        <div style="margin-top:20px; float:left; clear:both;">
            <table class="tablaLista" style="width:500px;" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre de la Zona</th>
                        <th>Precio del Metro2</th>
                        <th>Moneda</th>
                        <th>% Cuota Inicial</th>
                        <th>Color</th>
                        <th class="tOpciones" width="40px">OP</th>
                    </tr>
                </thead>
                <tbody>
        <?php
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
                        <tr>
                            <td><?php echo $objeto->zon_nombre; ?></td>
                            <td><?php echo $objeto->zon_precio; ?></td>
                            <td><?php if ($objeto->zon_moneda == '1') {
                echo 'Bolivianos';
            } else {
                echo 'Dolares';
            } ?></td>
                            <td><?php echo $objeto->zon_cuota_inicial; ?></td>
                            <td>
                                <div style="background-color:<?php echo $objeto->zon_color; ?>;">&nbsp;</div>
                            </td>
                            <td>
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                    <center>
                                        <a class="linkOpciones" href="javascript:eliminar_zona(<?php echo $_GET['id']; ?>,<?php echo $objeto->zon_id; ?>);">
                                            <img src="images/b_drop.png" border="0" title="ELIMINAR ZONA" alt="ELIMINAR ZONA" width="16px" height="16px">
                                        </a>
                                    </center>
                            </td>
                            <td>
                    <center>
                        <a class="linkOpciones" href="gestor.php?mod=uv&tarea=ZONAS&id=<?php echo $_GET['id']; ?>&mz=ok&zon=<?php echo $objeto->zon_id; ?>">
                            <img src="images/b_edit.png" border="0" title="MODIFICAR ZONA" alt="MODIFICAR ZONA" width="16px" height="16px">
                        </a>
                    </center>
                    </td>
                    </tr>
                </table>


            </td>
            </tr>

            <?php
            $conec->siguiente();
        }
        ?>
        </tbody>
        </table>
        </div>
        <?php
    }

    function listado_de_uv() {
        $conec = new ADO();

        $sql = "select uv_id, uv_nombre from uv where uv_urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();
        ?>		
        <div style="margin-top:20px; float:left; clear:both;">
            <table class="tablaLista" style="width:500px;" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre de la UV</th>
                        <th class="tOpciones" width="80px">OP</th>
                    </tr>
                </thead>
                <tbody>
        <?php
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
                        <tr>
                            <td><?php echo $objeto->uv_nombre; ?></td>
                            <td >
                    <center>

                        <a class="linkOpciones" href="javascript:eliminar_uv(<?php echo $_GET['id']; ?>,<?php echo $objeto->uv_id; ?>);">
                            <img src="images/b_drop.png" border="0" title="ELIMINAR UV" alt="ELIMINAR UV" width="16px" height="16px">
                        </a>
                        <a class="linkOpciones" href="gestor.php?mod=uv&tarea=UV&id=<?php echo $_GET[id];?>&acc=precio&uv_id=<?php echo $objeto->uv_id;?>">
                            <img src="images/b_browse.png" border="0" title="ELIMINAR UV" alt="ELIMINAR UV" width="16px" height="16px">
                        </a>

                    </center>
                    </td>
                    </tr>

            <?php
            $conec->siguiente();
        }
        ?>
                </tbody>
            </table>
        </div>
        <?php
    } 
}
?>