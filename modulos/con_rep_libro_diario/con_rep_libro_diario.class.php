<?php

class CON_REP_LIBRO_DIARIO extends BUSQUEDA {
    var $formulario;
    var $mensaje;

    function CON_REP_LIBRO_DIARIO() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_libro_diario';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('LIBRO DIARIO');
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {
        $this->formulario->dibujar_cabecera();
        if (!($_POST['formu'] == 'ok')) {
            
            
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
            <!--AutoSuggest-->
            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
            <!--AutoSuggest-->
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->
            <script>
                function esFecha (strValue){
                  //check to see if its in a correct format
                  var objRegExp = /\d{1,2}\/\d{1,2}\/\d{4}/;

                  if(!objRegExp.test(strValue))
                    return false; //doesn't match pattern, bad date
                  else {
                    var strSeparator = strValue.substring(2,3)
                    //create a lookup for months not equal to Feb.
                    var arrayDate = strValue.split(strSeparator);

                    var arrayLookup = { '01' : 31,'03' : 31,
                      '04' : 30,'05' : 31,
                      '06' : 30,'07' : 31,
                      '08' : 31,'09' : 30,
                      '10' : 31,'11' : 30,'12' : 31
                    }

                    var intDay = parseInt(arrayDate[0],10);
                    var intMonth = parseInt(arrayDate[1],10);
                    var intYear = parseInt(arrayDate[2],10);

                    if (arrayLookup[arrayDate[1]] !== null) {
                      if (intDay <= arrayLookup[arrayDate[1]] && intDay !== 0
                        && intYear > 1975 && intYear < 2050)
                        return true;     //found in lookup table, good date
                    }
                    if (intMonth === 2) {
                      var intYear = parseInt(arrayDate[2]);
                      if (intDay > 0 && intDay < 29) {
                        return true;
                      }
                      else if (intDay === 29) {
                        if ((intYear % 4 === 0) && (intYear % 100 !== 0) ||
                            (intYear % 400 === 0)) {
                          // year div by 4 and ((not div by 100) or div by 400) ->ok
                          return true;
                        }
                      }
                    }
                  }

                  return false; //any other values, bad date
                }

                function comparar_fechas(fechaA, fechaB) {                
                    var afechaA = fechaA.split('/');
                    var afechaB = fechaB.split('/');

                    var da = parseInt(afechaA[0], 10);
                    var ma = parseInt(afechaA[1], 10);
                    var ya = parseInt(afechaA[2], 10);

                    var db = parseInt(afechaB[0], 10);
                    var mb = parseInt(afechaB[1], 10);
                    var yb = parseInt(afechaB[2], 10);



                    if(db===da && mb===ma && yb===ya){
                        return 0;
                    }

                    if(ya>yb){
                        return 1;
                    }else{
                        if(ya===yb){
                            if(ma>mb){
                                return 1;
                            }else{
                                if(ma===mb){
                                    if(da>db){
                                        return 1;
                                    }else{
                                        return -1;
                                    }
                                }else{
                                    return -1;
                                }
                            }
                        }else{
                            return -1;
                        }
                    }
                }

                $("#frm_sentencia").live('submit',function (){
                    $(".error").remove();
                    var fecha_inicio=$("#fecha_inicio").val();
                    var fecha_fin=$("#fecha_fin").val();
                    var ges_fecha_ini=$("#ges_fecha_ini").val();
                    var ges_fecha_fin=$("#ges_fecha_fin").val();
                    if(esFecha(fecha_inicio)){
                        if(!(comparar_fechas(fecha_inicio,ges_fecha_ini)>=0)){
                            mostrar_error("#fecha_inicio","La Fecha Inicio debe ser mayor o igual a la Fecha Inicial de la Gestion ("+ges_fecha_ini+")");
                            return false;
                        }else{
                            if(!(comparar_fechas(fecha_inicio,ges_fecha_fin)<=0)){//// aqui esta mi idea principal......
                                mostrar_error("#fecha_inicio","La Fecha Inicio debe ser menor o igual a la Fecha Final de la Gestion ("+ges_fecha_fin+")");
                                return false;
                            }
                        }
                    }else{
                        if(fecha_inicio!==""){
                            mostrar_error("#fecha_inicio","La Fecha Inicio no es una fecha valida");
                            return false;
                        }                    
                    }

                    if(esFecha(fecha_fin)){
                        if(!(comparar_fechas(fecha_fin,ges_fecha_ini)>=0)){
                            mostrar_error("#fecha_fin","La Fecha Fin debe ser mayor o igual a la Fecha Inicial de la Gestion ("+ges_fecha_ini+")");
                            return false;
                        }else{
                            if(!(comparar_fechas(fecha_fin,ges_fecha_fin)<=0)){//// aqui esta mi idea principal......
                                mostrar_error("#fecha_inicio","La Fecha Fin debe ser menor o igual a la Fecha Final de la Gestion ("+ges_fecha_fin+")");
                                return false;
                            }
                        }
                    }else{
                        if(fecha_fin!==""){
                            mostrar_error("#fecha_fin","La Fecha Fin no es una fecha valida");
                            return false;
                        }
                    }

                    if(!(comparar_fechas(fecha_fin,fecha_inicio)>=0)&& fecha_fin!=="" && fecha_inicio!==""){
                        mostrar_error("#fecha_fin","La Fecha Fin debe ser mayor o igual a la Fecha Inicio");
                        return false;
                    }
                });
                function mostrar_error(input, mensaje){
                    $(input).after('<span class="error">'+mensaje+'</span>');
                }

                $("#fecha_inicio, #fecha_fin").live('keydown',function (evt){
                    $(".error").fadeOut(500,function (){
                        $(this).remove();
                    });
                });
            
                $("#gestion").live('change',function (){
                    definir_filtro();
                    complete_cuenta_cc();
                    $("#id_centroc").val('');
                    $("#nombre_centroc").val('');
                });
                
                function definir_filtro(){
                    var filtro=$("#f_filtro").val();
                    if(filtro==='p'){
                        llenar_periodos($("#gestion option:selected").val());
                    }else if (filtro==='r'){
                        asignar_fechas();
                    }
                }
                $("#periodo").live('change',function (){                    
                   asignar_fechas();
                });
                $("#f_filtro").live('change',function (){
                   var filtro=$("#f_filtro option:selected").val();
                   if (filtro==='p'){
                       $(".por_periodo").show();
                       $(".por_rango").hide();
                   }else if (filtro==='r'){
                       $(".por_periodo").hide();
                       $(".por_rango").show();
                   }
                   definir_filtro();
                });
                
                function asignar_fechas(){
                    var filtro=$("#f_filtro").val();
                    var fechas="";
                    if(filtro==='p'){
                        fechas=$("#periodo option:selected").val();
                    }else if(filtro==='r'){
                        fechas=$("#gestion option:selected").attr("data_fechas");
                    }                    
                    var afechas=fechas.split(',');                    
                    var afechai=afechas[0].split('-');
                    var afechaf=afechas[1].split('-');
                    var fechai=afechai[2]+'/'+afechai[1]+'/'+afechai[0];
                    var fechaf=afechaf[2]+'/'+afechaf[1]+'/'+afechaf[0];
                    $("#ges_fecha_ini").val(fechai);
                    $("#ges_fecha_fin").val(fechaf);
                    $("#fecha_inicio").val(fechai);
                    $("#fecha_fin").val(fechaf);
                }
                
                function llenar_periodos(id){
                    $.get("AjaxRequest.php?peticion=periodos&gesid="+id,function (respuesta){
                        var periodos=JSON.parse(respuesta);
                        $("#periodo").children().remove();
                        for(var i=0;i<periodos.length;i++){
                            var pdo=periodos[i];
                            var txt='<option value="'+pdo.fechai+','+pdo.fechaf+'">'+pdo.descripcion+'</option>';
                            $("#periodo").append(txt);
                            
                        }
                        asignar_fechas();
                    });
                }
                $(document).ready(function (){
                    $("#f_filtro").trigger("change");
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
                                <div class="Etiqueta" >Gesti&oacute;n</div>
                                <div id="CajaInput">
                                    <select name="gestion" class="caja_texto" id="gestion" style="min-width: 140px">                                        
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo_data("select ges_id as id,ges_descripcion as nombre, concat(ges_fecha_ini,',',ges_fecha_fin) as fechas from con_gestion where ges_eliminado='No' order by ges_fecha_ini desc","fechas", $_SESSION['ges_id']);
                                        ?>
                                    </select>
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                                <div id="CajaInput">
                                    <select id="cmp_une_id" name="cmp_une_id">
                                        <option value="">Seleccione</option>
                                        <?php $fun=new FUNCIONES();?>
                                        <?php $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[cmp_une_id]);?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">                                
                                <div class="Etiqueta" >Opci&oacute;n de Filtro</div>
                                <div id="CajaInput">
                                    <select name="f_filtro" class="caja_texto" id="f_filtro" style="min-width: 140px">                                        
                                        <option value="r">Por rango</option>
                                        <option value="p" selected="">Por periodo</option>
                                    </select>
                                </div>		
                            </div>                            
                            <div id="ContenedorDiv" class="por_periodo">                                
                                <div class="Etiqueta" >Periodo</div>
                                <div id="CajaInput">
                                    <select name="periodo" class="caja_texto" id="periodo" style="min-width: 140px">
                                        
                                    </select>
                                </div>		
                            </div>
                            
                            <div id="ContenedorDiv" class="por_rango">                                
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_inicio" id="fecha_inicio" size="12" value="" type="text">
                                    <!--<span class="flechas1">(DD/MM/AAAA)</span>-->
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv" class="por_rango">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_fin" id="fecha_fin" size="12" value="" type="text">                                    
                                </div>		
                            </div>
                            <!--Fin-->
                            <script>
                                    asignar_fechas();
                            </script>
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Centro de Costo</div>
                                <div id="CajaInput">
                                    <input name="id_centroc" id="id_centroc" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                    <input name="nombre_centroc" id="nombre_centroc"  type="text" class="caja_texto" value="<?php ?>" size="25">
                                </div>							   							   								
                            </div>
                            <script>
                                function complete_cuenta_cc(){
                                    var options_cc = {
                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cc&gesid="+$("#gestion").val()+"&",
                                        varname: "input",
                                        json: true,
                                        shownoresults: false,
                                        maxresults: 6,
                                        callback: function(obj) {
                                            $("#id_centroc").val(obj.id);
                                        }
                                    };
                                    var as_jsoncc = new _bsn.AutoSuggest('nombre_centroc', options_cc);
                                }
                                
                                $("#nombre_centroc").live("keyup",function(){
                                    if($(this).val()===''){
                                        $("#id_centroc").val("");
                                    }
                                });
                                complete_cuenta_cc();
                            </script>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Comprobantes hechos en:</div>
                                <div id="CajaInput">
                                    <select name="moneda_hecho" class="caja_texto">
                                        <option value="">Todas la monedas</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda where mon_eliminado='No'", $_POST['moneda_hecho']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Mostrar reporte en:</div>
                                <div id="CajaInput">
                                    <select name="moneda_reporte" class="caja_texto">
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda where mon_eliminado='No'", $_POST['moneda_reporte']);
                                        ?>
                                    </select>
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
                            $("#fecha_inicio").mask("99/99/9999");
                            $("#fecha_fin").mask("99/99/9999");
                        });
                    </script>
                    <script>
                        var options1 = {
                            script: "sueltos/cuenta.php?json=true&",
                            varname: "input",
                            minchars: 1,
                            timeout: 10000,
                            noresults: "No se encontro ninguna persona",
                            json: true,
                            callback: function(obj) {
                                document.getElementById('id_cuenta').value = obj.id;
                                f_particular();
                            }
                        };
                        var as_json1 = new _bsn.AutoSuggest('nombre_cuenta', options1);
                    </script>
                    <?php
                }

                if ($_POST['formu'] == 'ok')
                    $this->mostrar_reporte();
            }

            function barra_de_impresion() {
                $pagina = "'contenido_reporte'";

                $page = "'about:blank'";

                $extpage = "'reportes'";

                $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

                $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
                $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                $extra2 = "'</center></body></html>'";

                $myday = setear_fecha(strtotime(date('Y-m-d')));

                echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
		  c.document.write(' . $extra1 . ');
		  var dato = document.getElementById(' . $pagina . ').innerHTML;
		  c.document.write(dato);
		  c.document.write(' . $extra2 . '); c.document.close();
		  ">
		<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
		</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '\';"></td></tr></table><br><br>
		';
            }

            function mostrar_reporte() {
                $this->barra_de_impresion();
                ?>
                <div id="contenido_reporte" style="clear:both;">
                    <center>
                        <?php
                        $ges_id = $_POST['gestion'];
                        $nombre_empresa=  FUNCIONES::parametro('razon_social',$ges_id);
                        $datos_empresa=  FUNCIONES::parametro('direccion');
                        ?>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="40%">
                                    <strong><?php echo $nombre_empresa; ?></strong></br>
                                    <?php echo $datos_empresa; ?></br></br>
                                </td>
                                <td width="20%">
                                    <p align="center" >
                            <center>
                                <strong><h3>LIBRO DIARIO</h3></strong></br>
                                <?php
                                if ($_POST['fecha_inicio'] <> "")
                                    echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
                                if ($_POST['fecha_fin'] <> "")
                                    echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'].'<br>';
                                ?>
                                <strong><?php echo FUNCIONES::atributo_bd("con_gestion", "ges_id=".$_POST['gestion'], "ges_descripcion")?></strong>
                                </br><strong>Expresado en: <?php echo $this->descripcion_moneda($_POST['moneda_reporte']); ?></strong>
                            </center>
                            </p>
                            </td>
                            <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                            </tr> 
                        </table>
                        <?php
                        $conversor = new convertir();
                        $conec = new ADO();
                        $conec2 = new ADO();
                        ?>

                        <table   width="95%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><b>C&oacute;digo</b></th>
                                    <th ><b>Detalle</b></th>
                                    <th><b>Dévitos</b></th>
                                    <th><b>Créditos</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $filtro = "";

                                if ($_POST['fecha_inicio'] <> "") {
                                    $filtro.=" and cmp_fecha >= '" . $conversor->get_fecha_mysql($_POST['fecha_inicio']) . "' ";
                                    if ($_POST['fecha_fin'] <> "") {
                                        $filtro.=" and cmp_fecha <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "' ";
                                    }
                                } else {
                                    if ($_POST['fecha_fin'] <> "") {
                                        $filtro.=" and cmp_fecha <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "' ";
                                    }
                                }
                                if ($_POST['moneda_hecho'] <> "") {
                                    $filtro.=" and cmp_mon_id = '" . $_POST['moneda_hecho']. "' ";
                                }
                                
                                $fcentro_c=$_POST['id_centroc']!=''?' and cde_cco_id='.$_POST['id_centroc']:'';
                                
                                $and_une_id="";
                                if($_POST[cmp_une_id]){
                                    $and_une_id=" and cmp_une_id='$_POST[cmp_une_id]'";
                                }							
                                
                                $sql = "
					select 
						cmp_id, cmp_tco_id, cmp_nro,cmp_fecha, cmp_glosa, cmp_peri_id
					from
						con_comprobante
					where
						cmp_ges_id=$ges_id and cmp_eliminado='No' $filtro $and_une_id order by cmp_fecha
					";
//                                echo $sql;
                                $conec->ejecutar($sql);
                                $num = $conec->get_num_registros();
                                $total_debe = 0;
                                $total_haber = 0;
                                for ($i = 0; $i < $num; $i++) {
                                    $objeto = $conec->get_objeto();
                                    $tco_id=$objeto->cmp_tco_id;
                                    $tipo="";
                                    if($tco_id==1){
                                        $tipo="I";
                                    }elseif($tco_id==2){
                                        $tipo="E";
                                    }elseif ($tco_id==3) {
                                        $tipo="D";
                                    }elseif($tco_id==4){
                                        $tipo="A";
                                    }
                                    $this->titulo($conversor->get_fecha_latina($objeto->cmp_fecha), '-------------------- ' .$tipo.' - '. $objeto->cmp_nro.' - '.FUNCIONES::atributo_bd("con_periodo", "pdo_id=$objeto->cmp_peri_id", "pdo_descripcion") . ' --------------------');                                    
                                    $sql2 = "select 
                                                cde_cue_id,cde_valor,cde_glosa,cue_codigo,cue_descripcion
                                            from
                                                con_comprobante_detalle
                                                inner join con_cuenta on (cde_cue_id=cue_id)
                                            where
                                                cde_cmp_id='" . $objeto->cmp_id . "' and
                                                cde_mon_id='" . $_POST['moneda_reporte'] . "'
                                                $fcentro_c
                                            ";
                                    $conec2->ejecutar($sql2);
                                    $num2 = $conec2->get_num_registros();
                                    for ($i2 = 0; $i2 < $num2; $i2++) {
                                        $objeto2 = $conec2->get_objeto();

                                        $debe = "";
                                        $haber = "";

                                        if ($objeto2->cde_valor > 0) {
                                            $debe = $objeto2->cde_valor;
                                            $total_debe+=$debe;
                                        } else {
                                            $haber = $objeto2->cde_valor * (-1);
                                            $total_haber+=$haber;
                                        }

                                        $this->movimiento($objeto2->cue_codigo, $objeto2->cue_descripcion, $debe, $haber);
                                        
                                        $conec2->siguiente();
                                    }
                                    ///
                                    $this->descripcion($objeto->cmp_glosa);

                                    $conec->siguiente();
                                }

                                $this->totales($total_debe, $total_haber);
                                ?>	
                            </tbody>                            
                        </table>
                    </center>
                    <br>
                    <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr>
                    </table>
                </div></br>
                <?php
            }

            function titulo($fecha, $detalle) {
                ?>
                <tr>
                    <td>
                        <?php echo $fecha; ?>
                    </td>
                    <td>
                <center><?php echo $detalle; ?></center>
                </td>
                <td>
                    &nbsp;
                </td>							
                <td>
                    &nbsp;
                </td>							
                </tr>
                <?php
            }

            function movimiento($codigo, $detalle, $debe, $haber) {
                ?>
                <tr>
                    <td align="right">
                        <?php echo $codigo; ?>
                    </td>                    
                    <td>
                        <?php 
                        if($debe-$haber<0){
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        }   
                        ?>
                        <span style="text-decoration: underline"><?php echo $detalle;?></span>
                    </td>
                    <td>
                        <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>&nbsp;
                    </td>
                    <td>
                        <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>&nbsp;
                    </td>	
                </tr>
                <?php
            }
            
            function descripcion($descripcion) {
                ?>
                <tr>
                    <td align="right">
                        &nbsp;
                    </td>                    
                    <td>
                        <div style="font-style: italic">
                            <?php echo $descripcion; ?>
                        </div>
                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>	
                </tr>
                <?php
            }

            function totales($debe, $haber) {
                ?>
                <tfoot>
                    <tr>
                        <td colspan="2" align="right">
                            &nbsp;
                        </td>                        
                        <td>
                            <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>&nbsp;
                        </td>
                        <td>
                            <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>&nbsp;
                        </td>	
                    </tr>
                </tfoot>
                <?php
            }

            function descripcion_moneda($moneda) {
                $conec = new ADO();

                $sql = "
		select 
			mon_titulo
		from
			con_moneda
		where
			mon_id='" . $moneda . "'
		";
                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();
                return $objeto->mon_titulo;
            }
        }
        ?>