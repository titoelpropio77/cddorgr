<?php

class CON_REP_RETENCIONES extends BUSQUEDA {
    var $formulario;
    var $mensaje;

    function CON_REP_RETENCIONES() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_retenciones';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('INFORME DE RETENCIONES');
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
                    };

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
//                $("#btn_ver").live("click",function (){
//                    alert($("#fecha_fin").val()+" | "+$("#fecha_inicio").val());
//                });
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
                            <!--Inicio-->
                            <div id="ContenedorDiv" hidden="">
                                <div class="Etiqueta" >Centro de Costo</div>
                                <div id="CajaInput">
                                    <input name="id_centroc" id="id_centroc" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                    <input name="nombre_centroc" id="nombre_centroc"  type="text" class="caja_texto" value="<?php ?>" size="25">
                                </div>							   							   								
                            </div>
                            <script>
                                function complete_cuenta_cc(){
//                                    var options_cc = {
//                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cc&gesid="+$("#gestion").val()+"&",
//                                        varname: "input",
//                                        json: true,
//                                        shownoresults: false,
//                                        maxresults: 6,
//                                        callback: function(obj) {
//                                            $("#id_centroc").val(obj.id);
//                                        }
//                                    };
//                                    var as_jsoncc = new _bsn.AutoSuggest('nombre_centroc', options_cc);
                                }
                                
//                                $("#nombre_centroc").live("keyup",function(){
//                                    if($(this).val()===''){
//                                        $("#id_centroc").val("");
//                                    }
//                                });
//                                complete_cuenta_cc();
                            </script>
                            <!--Fin-->
                            <div id="ContenedorDiv">                                
                                <div class="Etiqueta" >Tipo de Retenci&oacute;n</div>
                                <div id="CajaInput">
                                    <select name="t_retencion" class="caja_texto" id="t_retencion" style="min-width: 140px">                                        
                                        <option value="" selected="">Bienes y Servicios</option>
                                        <option value="Bienes" >Bienes</option>
                                        <option value="Servicios">Servicios</option>                                        
                                    </select>
                                </div>		
                            </div>
                            
                            <script>
                                asignar_fechas();
                            </script>                           
		
                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="submit" class="boton" name="" value="Generar Reporte" >
                                    <!--<input type="button" class="boton" id="btn_ver" value="ver fechas" >-->
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

                if ($_POST['formu'] == 'ok'){
//                    if($_POST['t_retencion']!=''){
                        $this->mostrar_reporte();
//                    }else{
//                        $_POST['t_retencion']='Bienes';
//                        $this->mostrar_reporte();
//                        $_POST['t_retencion']='Servicios';
//                        $this->mostrar_reporte();
//                    }                    
                }
                
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
                $extra1.=" <a href=javascript:imprimir_libro();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                $extra2 = "'</center></body></html>'";

                $myday = setear_fecha(strtotime(date('Y-m-d')));

                echo '	<table align=right border=0><tr><td>
                
                <td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
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
                    <script src="js/jquery-1.3.2.min.js"></script>
                    <script>                
                        $(".det_cmp").live('click',function (){
                            var ruta='gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
                            var id=$(this).attr('data-id');
                            window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes')
                        });
                        function imprimir_libro(){
                            $(".det_cmp").hide();
                            $(".no_print").hide();
                            window.print();
                            $(".no_print").show();
                            $(".det_cmp").show();
                        }                
                    </script>
                    <?php
                    if($_POST['t_retencion']!=''){                        
                        $contFolio=1;
                        $t='';
                        if($_POST['t_retencion']=='Bienes'){
                            $t='b';
                        }elseif($_POST['t_retencion']=='Servicios'){
                            $t='s';
                        }
                        $_POST['t'.$t.'_neto']=0;
                        $_POST['t'.$t.'_it']=0;
                        $_POST['t'.$t.'_iue']=0;
                        $_POST['t'.$t.'_it_iue']=0;
                        $_POST['t'.$t.'_res']=0;
                        
                        $this->cargar_retenciones($contFolio);
                    }else{
                        $contFolio=1;
                        
                        $_POST['tb_neto']=0;
                        $_POST['tb_it']=0;
                        $_POST['tb_iue']=0;
                        $_POST['tb_it_iue']=0;
                        $_POST['tb_res']=0;
                        $_POST['t_retencion']='Bienes';
                        $this->cargar_retenciones($contFolio);
                        $_POST['ts_neto']=0;
                        $_POST['ts_it']=0;
                        $_POST['ts_iue']=0;
                        $_POST['ts_it_iue']=0;
                        $_POST['ts_res']=0;
                        $_POST['t_retencion']='Servicios';                     
                        $this->cargar_retenciones($contFolio);
                        $this->resumen_retenciones();
                    }
                    ?>
                    </div>                
                <?php
                }
            function resumen_retenciones(){
                ?>
                <table style="font-size:12px;" width="99%" cellpadding="5" cellspacing="0" >                            
                        <tr>                                
                            <td width="100%" colspan="2">                                    
                                <div style="position: relative">
                                    <center>
                                        <strong><h3>TOTAL RETENCIONES</h3></strong>
                                        <?php
                                        if($_POST['f_filtro']=='r'){
                                            if ($_POST['fecha_inicio'] <> "")
                                                echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
                                            if ($_POST['fecha_fin'] <> "")
                                                echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'].'<br>';
                                        }elseif($_POST['f_filtro']=='p'){
                                            echo "<b>Periodo: </b>".substr($_POST['fecha_inicio'], 3);
                                        }
                                        ?>
                                    </center>                                    
                                </div>                                    
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                        </tr>
                    </table>
                    <table   width="99%"  class="tablaReporte" cellpadding="0" cellspacing="0" >                            
                        <thead >
                            <tr>
                                <th width="" ><b>Tipo de Retencion</b></th>
                                <th width="" ><b>Neto</b></th>
                                <th width="" ><b>IT </b></th>
                                <th width="" ><b>IUE </b></th>                                    
                                <th width="" ><b>IT + IUE</b></th>                                    
                                <th width="" ><b>Total</b></th>
                            </tr>
                        </thead>
                        <tbody>                               
                            <tr>
                                <td>                        
                                    BIENES
                                </td>
                                <td >                                    
                                    <?php echo FUNCIONES::numformat($_POST['tb_neto']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['tb_it']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['tb_iue']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['tb_it_iue']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['tb_res']);?>
                                </td>                                
                            </tr>
                            <tr>
                                <td>                        
                                    SERVICIOS
                                </td>
                                <td >                                    
                                    <?php echo FUNCIONES::numformat($_POST['ts_neto']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['ts_it']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['ts_iue']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['ts_it_iue']);?>
                                </td>
                                <td>
                                    <?php echo FUNCIONES::numformat($_POST['ts_res']);?>
                                </td>                                
                            </tr>
                        </tbody>
                        <tfoot class="foot_lib_vc" style="border:1px solid #000;">
                            <tr >
                                <td  >
                                    TOTAL
                                </td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($_POST['tb_neto']+$_POST['ts_neto']);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($_POST['tb_it']+$_POST['ts_it']);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($_POST['tb_iue']+$_POST['ts_iue']);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($_POST['tb_it_iue']+$_POST['ts_it_iue']);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($_POST['tb_res']+$_POST['ts_res']);?></div></td>
                            </tr>                            
                        </tfoot>
                    </table>
        <?php
                echo $_POST['tg_neto']."<br>";
                echo $_POST['tg_it']."<br>";
                echo $_POST['tg_iue']."<br>";
                echo $_POST['tg_it_iue']."<br>";
                echo $_POST['tg_res']."<br>";
            }
            function cargar_retenciones(&$contFolio){            
                $filtro = "";
                $conec=new ADO();
                if ($_POST['fecha_inicio'] <> "") {
                    $filtro.=" and ret_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']) . "' ";
                    if ($_POST['fecha_fin'] <> "") {
                        $filtro.=" and ret_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fecha_fin']) . "' ";
                    }
                } else {
                    if ($_POST['fecha_fin'] <> "") {
                        $filtro.=" and ret_fecha <='" . FUNCIONES::get_fecha_mysql($_POST['fecha_fin']) . "' ";
                    }
                }
                $ges_id=$_POST['gestion'];
                $f_retencion="";
                if($_POST['t_retencion']){
                    $f_retencion="and r.ret_tipo='".$_POST['t_retencion']."'";
                }
                
                $sql = "select 
                            *
                        from
                            con_retencion r
                        where
                            r.ret_ges_id='$ges_id' and r.ret_eliminado='No' $f_retencion $filtro order by ret_fecha";    
                $conec->ejecutar($sql);

                $tg_neto = 0;
                $tg_it = 0;
                $tg_iue = 0;
                $tg_it_iue = 0;
                $tg_res = 0;
                $nro_filas=  FUNCIONES::parametro("cant_fil_vc", $ges_id);
                $porc_it=  FUNCIONES::parametro("porc_ret_it", $ges_id);
                $porc_iue=0;
                if($_POST['t_retencion']=='Servicios'){
                    $porc_iue=FUNCIONES::parametro("porc_ret_iue_serv", $ges_id);
                }elseif($_POST['t_retencion']=='Bienes'){
                    $porc_iue=FUNCIONES::parametro("porc_ret_iue_bien", $ges_id);
                }
                $i=0;
                $contador=1;
                while($i<$conec->get_num_registros() || $contador==1){
                    $contador++;
                ?>
                <center>
                    <?php $tlibro=$_POST['libro'];?>
                    <table style="font-size:12px;" width="99%" cellpadding="5" cellspacing="0" >                            
                        <tr>                                
                            <td width="100%" colspan="2">
                                <div style="position: relative">
                                    <center>
                                        <strong><h3>INFORME DE RETENCIONES</h3></strong>
                                        <?php
                                        if($_POST['f_filtro']=='r'){
                                            if ($_POST['fecha_inicio'] <> "")
                                                echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
                                            if ($_POST['fecha_fin'] <> "")
                                                echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'].'<br>';
                                        }elseif($_POST['f_filtro']=='p'){
                                            echo "<b>Periodo: </b>".substr($_POST['fecha_inicio'], 3);
                                        }
                                        ?>
                                    </center>
                                    <div style="position: absolute; right: 0; top: 10px; font-size: 14px;">
                                        <span><b>Folio:</b></span> <?php echo $contFolio;?>
                                        <?php $contFolio++;?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                &nbsp;
                            </td>
                        </tr>
                    </table>
                    <table   width="99%"  class="tablaReporte" cellpadding="0" cellspacing="0" >                            
                        <thead >
                            <tr>
                                <th width="70px" rowspan="2" class="tdoblefila"><b>Fecha</b></th>
                                <th width="40px" rowspan="2" class="tdoblefila"><b>Nro</b></th>
                                <th width="" rowspan="2" class="tdoblefila"><b>Cuenta</b></th>
                                <th width="" rowspan="2" class="tdoblefila"><b>Glosa</b></th>
                                <th  colspan="5"><b><?php echo $_POST['t_retencion']; ?></b></th>
                            </tr>
                            <tr>
                                <th width="" ><b>Neto</b></th>
                                <th width="" ><b>IT (<?=$porc_it?>%)</b></th>
                                <th width="" ><b>IUE (<?=$porc_iue?>%)</b></th>                                    
                                <th width="" ><b><?=($porc_it*1+$porc_iue*1)?>%</b></th>                                    
                                <th width="" ><b>Total</b></th>
                            </tr>
                        </thead>
                        <tbody>                               
                            <?php
                            $tp_neto = 0;
                            $tp_it = 0;
                            $tp_iue = 0;
                            $tp_it_iue = 0;
                            $tp_res = 0;

                            $cont=1;
                            $sw=true;
                            while($sw && $i<$conec->get_num_registros()){
                                $retencion=$conec->get_objeto();                                    
                                if($retencion->ret_asumido*1>0){
                                    $tp_neto+=$retencion->ret_neto;
                                    $tp_res+=$retencion->ret_result;
                                }else{
                                    $tp_neto+=$retencion->ret_result;
                                    $tp_res+=$retencion->ret_neto;
                                }

                                $tp_it+=$retencion->ret_it;
                                $tp_iue+=$retencion->ret_iue;
                                $tp_it_iue+=$retencion->ret_it*1+$retencion->ret_iue*1;

                                $this->movimiento($retencion,$tlibro,$cont);
                                if(($i+1)%$nro_filas==0){
                                    $sw=false;
                                }
                                $cont++;
                                $conec->siguiente();
                                $i++;
                            }                                
                            ?>                                
                        </tbody>
                        <tfoot class="foot_lib_vc" style="border:1px solid #000;">
                            <tr >

                                <td rowspan="2" colspan="4">
                                    <div class="tfoot_info" style="text-align: right;width: 25%; float: right">Totales Parciales<br>Totales Generales</div>
                                </td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($tp_neto);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($tp_it);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($tp_iue);?></div></td>                                    
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($tp_it_iue);?></div></td>
                                <td ><div class="tot_parcial"><?php echo FUNCIONES::numformat($tp_res);?></div></td>
                                <?php 
                                $tg_neto+=$tp_neto;
                                $tg_it+=$tp_it;
                                $tg_iue+=$tp_iue;
                                $tg_it_iue+=$tp_it_iue;
                                $tg_res+=$tp_res;

                                ?>
                            </tr>
                            <tr>                                    
                                <td ><div class="tot_general"><?php echo FUNCIONES::numformat($tg_neto);?></div></td>
                                <td ><div class="tot_general"><?php echo FUNCIONES::numformat($tg_it);?></div></td>
                                <td ><div class="tot_general"><?php echo FUNCIONES::numformat($tg_iue);?></div></td>                                    
                                <td ><div class="tot_general"><?php echo FUNCIONES::numformat($tg_it_iue);?></div></td>
                                <td ><div class="tot_general"><?php echo FUNCIONES::numformat($tg_res);?></div></td>
                            </tr>
                        </tfoot>
                    </table>
                        <?php
                        if($i>=$conec->get_num_registros()){
                        ?>
                            <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr>
                            </table>
                        <?php
                        }
                        ?>
                    <p style="page-break-after:always;"></p>
                    <?php
                    }
                    $t='';
                    if($_POST['t_retencion']=='Bienes'){
                        $t='b';
                    }elseif($_POST['t_retencion']=='Servicios'){
                        $t='s';
                    }
                    $_POST['t'.$t.'_neto']+=$tg_neto ;
                    $_POST['t'.$t.'_it']+=$tg_it;
                    $_POST['t'.$t.'_iue']+=$tg_iue;
                    $_POST['t'.$t.'_it_iue']+=$tg_it_iue;
                    $_POST['t'.$t.'_res']+=$tg_res;
                    ?>
                    <style>
                        .foot_lib_vc tr td{
                            border:0px !important;                                
                            background-color: #fff;
                            font-size: 11px;
                            font-weight: bold;
                            text-align: left !important;
                        }
                        .tfoot_info{
                            float: left;                                
                            line-height: 22px;
                        }
                        .tot_parcial{
                            border-bottom: 1px solid #103955;
                        }
                        .tot_general{
                            border-bottom: 3px double #103955;
                        }
                    </style>
                </center><br><br>
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

            function movimiento($retencion) {
                ?>
                <tr>
                    <td>                        
                        <?php echo FUNCIONES::get_fecha_latina($retencion->ret_fecha);?>
                    </td>
                    <td >
                        <a style="float: left; padding-right: 3px" href="javascript:void(0);" class="det_cmp" data-id="<?php echo $retencion->ret_cmp_id?>"><img src="images/b_browse.png" width="14px" /></a>
                        <?php echo $retencion->cmp_nro;?>
                    </td>
                    <td>
                        <?php echo $retencion->cue_descripcion;?>
                    </td>
                    <td>
                        <?php echo $retencion->ret_glosa;?>
                    </td>
                    <?php if($retencion->ret_asumido*1>0){?>
                        <td><?php echo FUNCIONES::numformat($retencion->ret_neto);?></td>
                    <?php }else{?>
                        <td><?php echo FUNCIONES::numformat($retencion->ret_result);?></td>
                    <?php } ?>
                    <td><?php echo FUNCIONES::numformat($retencion->ret_it);?></td>
                    <td><?php echo FUNCIONES::numformat($retencion->ret_iue);?></td>
                    <td><?php echo FUNCIONES::numformat($retencion->ret_it*1+$retencion->ret_iue*1);?></td>                    
                    <?php if($retencion->ret_asumido*1>0){?>
                        <td><?php echo FUNCIONES::numformat($retencion->ret_result);?></td>
                    <?php }else{?>
                        <td><?php echo FUNCIONES::numformat($retencion->ret_neto);?></td>
                    <?php } ?>
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