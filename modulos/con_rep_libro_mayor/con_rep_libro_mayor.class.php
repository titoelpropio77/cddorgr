<?php

class CON_REP_LIBRO_MAYOR extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_REP_LIBRO_MAYOR() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_libro_mayor';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('LIBRO MAYOR');
		$this->usu=new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {
        if(!isset($_POST['info'])){
            $this->formulario->dibujar_cabecera();
        }
        if (!($_POST['formu'] == 'ok')) {
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
                .tab_lista_cuentas{
                    list-style: none;                                    
                    width: 100%;                                    
                    overflow:scroll ;
                    background-color: #ededed;
                    border-collapse: collapse;  
                    font-size: 12px;
                }
                .tab_lista_cuentas tr td{
                    padding: 3px 3px;
                }
                .tab_lista_cuentas tr:hover{
                    background-color: #f9e48c;
                }                                
                .img_del_cuenta{                                    
                    font-weight: bold;
                    cursor: pointer;
                    width: 12px;
                }
                .box_lista_cuenta{
                    width:270px;height:170px;background-color:#F2F2F2;overflow:auto;
                    border: 1px solid #8ec2ea;
                }
        </style>
            <div class="aTabsCont">
                <div class="aTabsCent">
                    <ul class="aTabs">
                        <li><a href="gestor.php?mod=con_rep_libro_mayor&tarea=ACCEDER&form=c" <?php if ($_GET['form'] == "c" || $_GET['form'] == "") { ?>class="activo" <?php } ?>>Cuenta</a></li>
                        <li><a href="gestor.php?mod=con_rep_libro_mayor&tarea=ACCEDER&form=ac" <?php if ($_GET['form'] == "ac") { ?>class="activo" <?php } ?>>Anal&iacute;tico - Cuenta</a></li>
                        <li><a href="gestor.php?mod=con_rep_libro_mayor&tarea=ACCEDER&form=cf" <?php if ($_GET['form'] == "cf") { ?>class="activo" <?php } ?>>Cuenta - Flujo</a></li>
                        <li><a href="gestor.php?mod=con_rep_libro_mayor&tarea=ACCEDER&form=ca" <?php if ($_GET['form'] == "ca") { ?>class="activo" <?php } ?>>Cuenta - Anal&iacute;tico</a></li>
                        <li><a href="gestor.php?mod=con_rep_libro_mayor&tarea=ACCEDER&form=fp" <?php if ($_GET['form'] == "fp") { ?>class="activo" <?php } ?>>Forma de Pago</a></li>
                    </ul>
                </div>
            </div>
            <?            
            $tf=  isset($_GET['form'])?$_GET['form']:'c';
            ?>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo; ?>&form=<?php echo $tf;?>" method="POST" enctype="multipart/form-data">  
                    <input type="hidden" id="tf" value="<?php echo $tf;?>">
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
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
                                    <select id="cde_une_id" name="cde_une_id">
                                        <option value="">Seleccione</option>
                                        <?php $fun=new FUNCIONES();?>
                                        <?php $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'",'');?>
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
                            <!--Fin-->
                            
                            <?php
                            if($tf=='fp'){
                            ?>
                            <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Forma de Pago</div>
                                    <div id="CajaInput">
                                        <select name="forma_pago" class="caja_texto">
                                            <option value="0" >Todas</option>
                                            <option value="Efectivo" >Efectivo</option>
                                            <option value="Cheque" >Cheque</option>
                                            <option value="Deposito" >Deposito</option>
                                            <option value="Transferencia" >Transferencia</option>
                                        </select>
                                    </div>							   							   								
                                </div>
                            <!--Fin-->
                            <?php
                            }
                            ?>
                            <?php
                            if($tf=='c' || $tf=='ac' ){
                            ?>
                                <?php
                                if($tf=='ac'){
                                ?>
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Cuenta Anal&iacute;tica</div>
                                    <div id="CajaInput">
                                        <input name="id_cuenta_a" id="id_cuenta_a" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                        <input name="nombre_cuenta_a" id="nombre_cuenta_a"  type="text" class="caja_texto" value="<?php ?>" size="25">                                        
                                    </div>                                    
                                    <div id="CajaInput" class="interno_cliente" style="display: none">
                                        <span style="color: #3a3a3a;">Cliente</span>
                                        <input name="cli_interno" id="cli_interno" type="hidden"  value="" size="2">
                                        <input name="txt_cli_interno" id="txt_cli_interno" type="text" class="caja_texto" value="" size="35">
                                        <span style="color: #3a3a3a;">Cod. Documento</span>                                        
                                        <input name="cde_doc_id" id="cde_doc_id" type="text" class="caja_texto" value="" size="10">
                                        <script>
                                            function complete_interno(){
                                                var options_ca = {
                                                    script: "AjaxRequest.php?peticion=internos&limit=6&",
                                                    varname: "input",
                                                    json: true,
                                                    shownoresults: false,
                                                    maxresults: 6,
                                                    callback: function(obj) {
                                                        $("#cli_interno").val(obj.id);
                                                    }
                                                };
                                                var as_json2 = new _bsn.AutoSuggest('txt_cli_interno', options_ca);
                                            }
                                            complete_interno();
                                        </script>
                                    </div>                                    
                                </div>
                                
                                <!--Fin-->
                                <?php
                                }
                                ?> 
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuenta de Flujo</div>
                                <div id="CajaInput">
                                    <input name="id_cuenta_f" id="id_cuenta_f" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                    <input name="nombre_cuenta_f" id="nombre_cuenta_f"  type="text" class="caja_texto" value="<?php ?>" size="25">
                                </div>							   							   								
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuenta</div>
                                <div id="CajaInput">                                    
                                    <input name="nombre_cuenta" id="nombre_cuenta"  type="text" class="caja_texto" value="<?php //echo $_POST['nombre_cuenta']                ?>" size="25">
                                </div>							   							   								
                            </div>
                            <!--Fin-->                            
                            <!--Inicio-->                           
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuentas a listar</div>
                                <div id="CajaInput">
                                    <div class="box_lista_cuenta"> 
                                        <table id="tab_lista_cuentas" class="tab_lista_cuentas">                                              
                                        </table>
                                        <input type="hidden" value="" name="lista_cuentas" id="lista_cuentas"/>
                                    </div>
                                </div>							   							   								
                            </div>
                            <script>
                                function complete_cuenta(){
                                    var options = {
                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&gesid="+$("#gestion").val()+"&",
                                        varname: "input",
                                        json: true,
                                        shownoresults: false,
                                        maxresults: 6,
                                        callback: function(obj) {
                                            agregar_cuenta(obj,"#nombre_cuenta");
                                        }
                                    };
                                    var as_json1 = new _bsn.AutoSuggest('nombre_cuenta', options);                                      
                                }
                                
                                function complete_cuenta_ca(){
                                    var options_ca = {
                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=ca&gesid="+$("#gestion").val()+"&",
                                        varname: "input",
                                        json: true,
                                        shownoresults: false,
                                        maxresults: 6,
                                        callback: function(obj) {
                                            $("#id_cuenta_a").val(obj.id);
                                            $('#cli_interno').val('');
                                            $('#txt_cli_interno').val('');
                                            if(obj.info==='01.001'){
                                                $('.interno_cliente').show();                                                
                                            }else{
                                                $('.interno_cliente').hide();
                                                
                                            }
                                        }
                                    };
                                    var as_json2 = new _bsn.AutoSuggest('nombre_cuenta_a', options_ca);
                                }
                                
                                function complete_cuenta_cf(){
                                    var options_ca = {
                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cf&gesid="+$("#gestion").val()+"&cue=-1&",
                                        varname: "input",
                                        json: true,
                                        shownoresults: false,
                                        maxresults: 6,
                                        callback: function(obj) {
                                            $("#id_cuenta_f").val(obj.id);
                                        }
                                    };
                                    var as_json2 = new _bsn.AutoSuggest('nombre_cuenta_f', options_ca);
                                }
                                
                                $("#nombre_cuenta_a").live("keyup",function(){
                                    if($(this).val()===''){
                                        $("#id_cuenta_a").val("");
                                    }
                                }) ;
                                complete_cuenta();
                                complete_cuenta_ca();
                                complete_cuenta_cf();
                            </script>
                            <!--Fin-->
                            <?php } ?>
                            <?php
                            if($tf=='ca' || $tf=='fp' || $tf=='cf'){
                            ?>
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuenta</div>
                                <div id="CajaInput">
                                    <input name="id_cuenta" id="id_cuenta" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                    <input name="nombre_cuenta" id="nombre_cuenta"  type="text" class="caja_texto" value="<?php ?>" size="25">
                                </div>							   							   								
                            </div>
                            <?php
                                if($tf=='ca' || $tf=='fp'){
                                ?>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Cuenta Anal&itilde;tica</div>
                                    <div id="CajaInput">
                                        <input name="nombre_cuenta_a" id="nombre_cuenta_a"  type="text" class="caja_texto" value="" size="25">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Cuentas Analiticas a Listar</div>
                                    <div id="CajaInput">
                                        <div class="box_lista_cuenta"> 
                                            <table id="tab_lista_cuentas" class="tab_lista_cuentas">                                              
                                            </table>
                                            <input type="hidden" value="" name="lista_cuentas" id="lista_cuentas"/>
                                        </div>
                                    </div>							   							   								
                                </div>     
                                <!--Fin-->
                                <script>
                                    function complete_cuenta_ac_c(){
                                        var options_ac = {
                                            script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&gesid="+$("#gestion").val()+"&",
                                            varname: "input",
                                            json: true,
                                            shownoresults: false,
                                            maxresults: 6,
                                            callback: function(obj) {
                                                $("#id_cuenta").val(obj.id);
                                            }
                                        };
                                        var as_json3 = new _bsn.AutoSuggest('nombre_cuenta', options_ac);
                                    }
                                    function complete_cuenta_ac_a(){
                                        var options = {
                                            script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=ca&gesid="+$("#gestion").val()+"&",
                                            varname: "input",
                                            json: true,
                                            shownoresults: false,
                                            maxresults: 6,
                                            callback: function(obj) {
                                                agregar_cuenta(obj,"#nombre_cuenta_a");
                                            }
                                        };
                                        var as_json1 = new _bsn.AutoSuggest('nombre_cuenta_a', options);
                                    }                                

                                    $("#nombre_cuenta").live("keyup",function(){
                                        if($(this).val()===''){
                                            $("#id_cuenta").val("");
                                        }
                                    }) ;

                                    complete_cuenta_ac_c();
                                    complete_cuenta_ac_a();                                
                                </script>
                                <?php
                                }
                                if($tf=="cf"){
                                ?>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Cuenta de Flujo</div>
                                        <div id="CajaInput">                                    
                                            <input name="nombre_cuenta_f" id="nombre_cuenta_f"  type="text" class="caja_texto" value="" size="25">
                                        </div>							   							   								
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Cuentas de Flujo a Listar</div>
                                        <div id="CajaInput">
                                            <div class="box_lista_cuenta"> 
                                                <table id="tab_lista_cuentas" class="tab_lista_cuentas">
                                                </table>
                                                <input type="hidden" value="" name="lista_cuentas" id="lista_cuentas"/>
                                            </div>
                                        </div>							   							   								
                                    </div>     
                                    <!--Fin-->
                                    <script>
                                    function complete_cuenta_cf_c(){
                                        var options_ac = {
                                            script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&gesid="+$("#gestion").val()+"&",
                                            varname: "input",
                                            json: true,
                                            shownoresults: false,
                                            maxresults: 6,
                                            callback: function(obj) {
                                                $("#id_cuenta").val(obj.id);
                                            }
                                        };
                                        var as_json3 = new _bsn.AutoSuggest('nombre_cuenta', options_ac);
                                    }
                                    function complete_cuenta_cf_f(){
                                        var options = {
                                            script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cf&cf_list=1&gesid="+$("#gestion").val()+"&",
                                            varname: "input",
                                            json: true,
                                            shownoresults: false,
                                            maxresults: 6,
                                            callback: function(obj) {
                                                agregar_cuenta(obj,"#nombre_cuenta_f");
                                            }
                                        };
                                        var as_json1 = new _bsn.AutoSuggest('nombre_cuenta_f', options);
                                    }                                

                                    $("#nombre_cuenta").live("keyup",function(){
                                        if($(this).val()===''){
                                            $("#id_cuenta").val("");
                                        }
                                    }) ;

                                    complete_cuenta_cf_c();
                                    complete_cuenta_cf_f();                                
                                </script>
                                <?php
                                }
                            }
                            ?>
                             <script>
                                $("#gestion").live('change',function (){
                                    definir_filtro();
                                    var tf=$("#tf").val();
                                    if(tf==="c"||tf==="ac" ){
                                        complete_cuenta();
                                        complete_cuenta_ca();
                                    }
                                    if(tf==="ca"){
                                        complete_cuenta_ac_a();
                                        complete_cuenta_ac_c();                                        
                                    }
                                    complete_cuenta_cc();
                                    $("#tab_lista_cuentas").children().remove();
                                    $("#id_cuenta_a").val();
                                    $("#nombre_cuenta_a").val('');
                                    $("#id_cuenta_f").val();
                                    $("#nombre_cuenta_f").val('');
                                    $("#id_cuenta").val('');
                                    $("#nombre_cuenta").val('');
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
                                
                                function agregar_cuenta(cuenta,txt_cuenta) {
                                    if (!existe_en_lista(cuenta.id)) {
                                        var fila = '<tr data-id="' + cuenta.id + '">';
                                        fila += '<td>' + cuenta.value + '</td>';
                                        fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                        fila += '</tr>';
                                        $("#tab_lista_cuentas").append(fila);
                                        $(txt_cuenta).val("");
                                    }
                                }

                                function existe_en_lista(id_cuenta) {
                                    var lista = $("#tab_lista_cuentas tr");
                                    for (var i = 0; i < lista.size(); i++) {
                                        var cuenta = lista[i];
                                        var id = $(cuenta).attr("data-id");
                                        if (id === id_cuenta) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }

                                $(".img_del_cuenta").live('click', function() {
                                    $(this).parent().parent().remove();
                                });

                                $("#frm_sentencia").submit(function() {
                                    var lista = $("#tab_lista_cuentas tr");
                                    var data = "";
                                    for (var i = 0; i < lista.size(); i++) {
                                        var cuenta = lista[i];
                                        var id = $(cuenta).attr("data-id");
                                        if (i > 0) {
                                            data += "," + id;
                                        } else {
                                            data += id;
                                        }
                                    }
                                    $("#lista_cuentas").val(data);
                                });                                
                                
                            </script>
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
            </div>
                    <script>
                        jQuery(function($) {
                            $("#fecha_inicio").mask("99/99/9999");
                            $("#fecha_fin").mask("99/99/9999");
                        });
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
                $extra1.=" <a href=javascript:imprimir_mayor(); >Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                
                $extra2 = "'</center></body></html>'";

                $myday = setear_fecha(strtotime(date('Y-m-d')));

                echo '	<table align=right border=0>
                    <tr>
                        <td>
                            <a href="javascript:document.formulario.submit();">
                                <img src="images/actualizar.png" width="20" title="ACTUALIZAR"/>
                            <a/>
                        </td>
                        <td>
                        <a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                        c.document.write(' . $extra1 . ');
                        var dato = document.getElementById(' . $pagina . ').innerHTML;
                        c.document.write(dato);
                        c.document.write(' . $extra2 . '); c.document.close();
                        ">
                      <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                  </a></td><td><a><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL" id="importar_excel"></a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '&form='.$_GET['form'].'\';"></td></tr></table><br><br>
                  ';
              }

            function mostrar_reporte() {
                if(!isset($_POST['info'])){
                    $this->barra_de_impresion();
                }else{
                    echo '<div id=imprimir>
                        <div id=status>
                            <p>                                
                                <a href=javascript:document.formulario.submit();>Actualizar</a> 
                                <a href=javascript:imprimir_mayor();>Imprimir</a> 
                                <a href=javascript:self.close();>Cerrar</a></td>
                            </p>
                        </div>
                    </div>';
                }
                $tf=  isset($_GET['form'])?$_GET['form']:'c';                
                ?>
                <div id="contenido_reporte" style="clear:both;">                    
                    <script src="js/jquery-1.3.2.min.js"></script>
                    <script>                
                        $(".det_cmp").live('click',function (){
                            var ruta='gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
                            var id=$(this).attr('data-id');
                            window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes')
                        });
                        function imprimir_mayor(){
                            $(".det_cmp").hide();
                            $(".no_print").hide();
                            window.print();
                            $(".no_print").show();
                            $(".det_cmp").show();
                        }                
                    </script>
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
                                        <strong><h3>LIBRO MAYOR</h3></strong>
                                        <strong><?php echo $_POST[cde_une_id]?FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$_POST[cde_une_id]'"):''; ?></strong>
                                        <br><br>
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
                        $lista_cuentas = $_POST['lista_cuentas'];
                        $cuenta='cue_id';
                        if($tf=='ca' || $tf=='fp'){
                            $cuenta='can_id';
                        }elseif($tf=='cf'){
                            $cuenta='cfl_id';
                        }
                        
                        $filtro = "";
                        if ($lista_cuentas) {
                            $filtro = " and(";
                            $array_cuentas = explode(",", $lista_cuentas);
                            for ($i = 0; $i < count($array_cuentas); $i++) {
                                if ($i > 0)
                                    $filtro.=" or ";
                                $filtro.= " $cuenta=" . $array_cuentas[$i] . " ";
                            }
                            $filtro.=")";
                        }
                        
                        $conec_cue = new ADO();                        
                        $sql = "SELECT cue_id as id, cue_codigo as codigo, cue_descripcion as descripcion FROM con_cuenta WHERE cue_ges_id=$ges_id and cue_tipo='Movimiento' and cue_eliminado='No' $filtro";
                        if($tf=='ca'|| $tf=='fp'){
                            $sql = "SELECT can_id as id, can_codigo as codigo, can_descripcion as descripcion FROM con_cuenta_ca WHERE can_ges_id=$ges_id and can_tipo='Movimiento' and can_eliminado='No' $filtro";
                        }elseif($tf=='cf'){
                            $sql = "SELECT cfl_id as id, cfl_codigo as codigo, cfl_descripcion as descripcion FROM con_cuenta_cf WHERE cfl_ges_id=$ges_id and cfl_tipo='Movimiento' and cfl_eliminado='No' $filtro";
                        }
                        $conec_cue->ejecutar($sql);

                        $index=1;
                        for ($i = 0; $i < $conec_cue->get_num_registros(); $i++) {
                            $cuenta = $conec_cue->get_objeto();
                            $sw=$this->libro_mayor_cuenta($cuenta->id, $cuenta->codigo, $cuenta->descripcion, $index,$tf);
                            if($sw) $index++;
                            $conec_cue->siguiente();
                        }
                        ?>                 


                    </center>
                    <br>
                    <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr>
                    </table>
                </div></br>
                <script>
                    $('#importar_excel').click(function(e) {
                        console.log('imprimiendo a excel...');
                        // Algoritmo para cambiar eliminar espacio en blanco de las columnas que se necesite se aplique sumatorias
                        var report = $('#contenido_reporte').clone();
                        $(report).attr('id', 'contenido_reporte_copia');
                        var tablas = $(report).find('table.tablaReporte');
                        var imgs = $(report).find(".det_cmp").remove();
                        var url = '<?php echo $this->url_actual() ?>';
                        
                        var src = $(image).attr('src');
                        var image = $(report).find('img');
                        $(image).attr('src', url + '/' + src);
                       
                        window.open('data:application/vnd.ms-excel; charset=utf-8, ' + encodeURIComponent($(report).html()));

                        e.preventDefault();
                    });

                </script>
                <form name="formulario" action="gestor.php?mod=<?php echo $this->modulo; ?>" method="POST">                            
                    <input type="hidden" name="formu" value="ok">
                    <input type="hidden" name="fecha_inicio" value="<?php echo $_POST['fecha_inicio'];?>">
                    <input type="hidden" name="fecha_fin" value="<?php echo $_POST['fecha_fin'];?>">
                    <input type="hidden" name="moneda_hecho" value="<?php echo $_POST['moneda_hecho'];?>">
                    <input type="hidden" name="moneda_reporte" value="<?php echo $_POST['moneda_reporte'];?>">                            
                    <input type="hidden" name="gestion" value="<?php echo $_POST['gestion'];?>">                            
                    <input type="hidden" name="lista_cuentas" value="<?php echo $_POST['lista_cuentas'];?>">
                    <?php
                    if(isset($_POST['info'])){
                    ?>
                        <input type="hidden" name="info" value="ok">
                    <?php
                    }
                    ?>
                </form>
                    
                <?php                
            }

            function libro_mayor_cuenta($id_cuenta, $codigo, $descripcion, $index,$tf) {
                $conversor = new convertir();
                $conec_det = new ADO();
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
                    $filtro.=" and cmp_mon_id = '" . $_POST['moneda_hecho'] . "' ";
                }
                $ges_id=$_POST['gestion'];
                
                $fcentro_c=$_POST['id_centroc']!=''?' and cde_cco_id='.$_POST['id_centroc']:'';
                $fcuenta_f=$_POST['id_cuenta_f']!=''?' and cde_cfl_id='.$_POST['id_cuenta_f']:'';
                $fforma_pago=$_POST['forma_pago']?' and c.cmp_forma_pago='."'".$_POST['forma_pago']."'":'';
                
                if($tf=='c' || $tf=='ac' ){
                    $col_c="cd.cde_cue_id";
                    $id_c=isset($_POST['id_cuenta_a'])?$_POST['id_cuenta_a']:0;                
                    $filtro_c="";
                    if($id_c!=0){
                        $filtro_c=" and cd.cde_can_id= $id_c";
                    }
                }elseif ($tf=='ca' || $tf=='fp') {
                    $col_c="cd.cde_can_id";
                    $id_c=isset($_POST['id_cuenta'])?$_POST['id_cuenta']:0;                
                    $filtro_c="";
                    if($id_c!=0){
                        $filtro_c=" and cd.cde_cue_id= $id_c";
                    }
                }elseif($tf=='cf'){
                    $col_c="cd.cde_cfl_id";
                    $id_c=isset($_POST['id_cuenta'])?$_POST['id_cuenta']:0;                
                    $filtro_c="";
                    if($id_c!=0){
                        $filtro_c=" and cd.cde_cue_id= $id_c";
                    }
                }
                $filtro_cli="";
                if($_POST[cli_interno]){
                    $filtro_cli=" and cde_int_id='$_POST[cli_interno]'";
                }
                if($_POST[cde_doc_id]>0){
                    $filtro_cli=" and cde_doc_id='$_POST[cde_doc_id]'";
                }
				
				
				/***** INI - IDS UNIDADES DE NEGOCIO DEL USUARIO *****/
//				$ids_une_id ='';
//				$fun = NEW FUNCIONES;
//				$array = $fun->lista_bd_sql("select * from usuario_unidadnegocio where uune_usu_id='".$this->usu->get_id()."'");
//				
//				$num = count($array);
//				$i=0;
//				foreach ($array as $product) 
//				{
//					if($i==0)
//						$ids_une_id.=$product->uune_une_id;
//					if($i>0)
//						$ids_une_id.=','.$product->uune_une_id;
//					
//					$i++;
//				}
				/***** FIN - IDS UNIDADES DE NEGOCIO DEL USUARIO *****/
                
                $and_une_id="";
                if($_POST[cde_une_id])
				{
                    $and_une_id=" and cde_une_id='$_POST[cde_une_id]'";
                }
//                else
//                {
//                        if($ids_une_id=='')
//                                $ids_une_id='0';
//                        $and_une_id=" and cde_une_id in ($ids_une_id)";
//                }
                        
                $sql = "
                        select 
                                cmp_id, cmp_nro, cmp_fecha, cde_glosa, cde_valor,cde_cue_id,cde_can_id, cde_cco_id, cde_cfl_id, cmp_peri_id, cmp_tco_id,
                                cmp_forma_pago, cmp_ban_id, cmp_ban_char, cmp_ban_nro
                        from
                                con_comprobante c, con_comprobante_detalle cd
                        where
                                c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and $col_c=$id_cuenta $filtro $fcentro_c $fcuenta_f $fforma_pago 
                                and c.cmp_ges_id=$ges_id and cde_mon_id='" . $_POST['moneda_reporte']."'".$filtro_c. $filtro_cli." $and_une_id order by cmp_fecha asc;";
               // echo $sql.'<br>';
                $conec_det->ejecutar($sql);
                $num = $conec_det->get_num_registros();
                if(!($num>0)){
                    return false;
                }
                ?>
                <div style="font-size: 13px; margin: 15px 25px 0 25px">
                    <div style="float: left;"><b>CODIGO: </b> <?php echo $codigo ?>  <b>CUENTA: </b> <?php echo strtoupper($descripcion); ?>
                        <?php if($_POST[cli_interno]){?>
                            <b>CLIENTE: </b> <?php echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido)as campo from interno where int_id='$_POST[cli_interno]'"); ?>
                        <?php }?>                    
                    </div> 
                    <div style="float: right;"><b>Nro.: </b> <?php echo $index ?></div> 
                </div>
                <table   width="95%"  class="tablaReporte" cellpadding="0" cellspacing="0">                    
                    <thead>
                        <tr>
                            <th width="10%"><b>Fecha</b></th>
                            <th width="20%"><b>Detalle</b></th>
                            <th width="13%"><b>Asiento</b></th>
                            <th width="8%"><b>Débitos</b></th>
                            <th width="8%"><b>Créditos</b></th>
                            <?php
                            if($tf!='fp'){
                            ?>
                            <th width="8%"><b>Saldos</b></th>
                            <?php
                            }
                            ?>
                            <?php
                            if($tf=='c' || $tf=='ac'){
                            ?>
                                <th width="12%" class="no_print"><b>C. Anal&iacute;tica</b></th>
                            <?php
                            }elseif($tf=='ca' || $tf=='cf'){
                            ?>
                                <th width="12%" class="no_print"><b>Cuenta</b></th>
                            <?php
                            }
                            ?>
                            <th width="12%" class="no_print"><b>C. de Costos</b></th>                            
                            <th width="12%" class="no_print">C. de Flujo</th>
                            <?php
                            if($tf=='fp'){
                            ?>
                                <th width="20%">Datos del Pago</th>
                            <?php
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_debe = 0;
                        $total_haber = 0;
                        $saldo = 0;
                        for ($i = 0; $i < $num; $i++) {
                            $objeto = $conec_det->get_objeto();
                            $debe = 0;
                            $haber = 0;
                            if ($objeto->cde_valor >= 0) {
                                $debe = floatval($objeto->cde_valor);
                                $saldo = $saldo + $debe;
                                $total_debe+=$debe;
                            }
                            if ($objeto->cde_valor < 0) {
                                $haber = floatval($objeto->cde_valor) * -1;
                                $saldo = $saldo - $haber;
                                $total_haber+=$haber;
                            }
                            $tco_id=$objeto->cmp_tco_id;
                            $tipo="";
                            if($tco_id==1){
                                $tipo="IN";
                            }elseif($tco_id==2){
                                $tipo="EG";
                            }elseif ($tco_id==3) {
                                $tipo="DI";
                            }elseif($tco_id==4){
                                $tipo="AJ";
                            }                            
                            $nro= $tipo.'-'.$objeto->cmp_nro.'-'.FUNCIONES::atributo_bd("con_periodo", "pdo_id=$objeto->cmp_peri_id", "pdo_descripcion");
                            if($tf=='c' || $tf=='ac'){
                                $_c= $objeto->cde_can_id>0?FUNCIONES::atributo_bd('con_cuenta_ca', "can_id=$objeto->cde_can_id", "can_descripcion"):'';
                            }elseif($tf=='ca'||$tf=='fp' || $tf=='cf'){
                                $_c= $objeto->cde_cue_id>0?FUNCIONES::atributo_bd('con_cuenta', "cue_id=$objeto->cde_cue_id", "cue_descripcion"):'';
                            }
                            $cc= $objeto->cde_cco_id>0?FUNCIONES::atributo_bd('con_cuenta_cc', "cco_id=$objeto->cde_cco_id", "cco_descripcion"):'';
                            $cf= $objeto->cde_cfl_id>0?FUNCIONES::atributo_bd('con_cuenta_cf', "cfl_id=$objeto->cde_cfl_id", "cfl_descripcion"):'';
                            $dat_banco=  $this->datos_banco($objeto); 
                            $this->movimiento($objeto->cmp_id,$objeto->cmp_fecha, $objeto->cde_glosa, $nro, $debe, $haber, $saldo,$_c, $cc,$cf,$dat_banco,$tf);
                            $conec_det->siguiente();
                        }
                        ?>	
                    </tbody>                            
                        <?php $this->totales($total_debe, $total_haber,$tf); ?>
                </table>


        <?php
        return true;
    }

    function datos_banco($objeto){
        $txt_datos=$objeto->cmp_forma_pago."<br>";
        if($objeto->cmp_ban_id>0){
            $txt_datos.=FUNCIONES::atributo_bd("con_banco", "ban_id=".$objeto->cmp_ban_id, "ban_nombre");
        }
        if($objeto->cmp_ban_char!=""){
            if($objeto->cmp_ban_id>0){
                $txt_datos.=",";
            }
            $txt_datos.=$objeto->cmp_ban_char."";
        }
        $txt_datos.="<br>Nro: ".$objeto->cmp_ban_nro;
        return $txt_datos;
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
                    
                </td>							
                <td>
                    
                </td>							
                </tr>
        <?php
    }

    function movimiento($cmp_id,$fecha, $detalle, $nro, $debe, $haber, $saldo,$_c, $cc,$cf,$dat_banco,$tf) {
        ?>
                <tr>
                    <td >
                        <?php echo FUNCIONES::get_fecha_latina($fecha); ?>
                    </td>                    
                    <td>
                        <div >
                        <?php
                        echo $detalle;
                        ?>
                        </div>
                    </td>
                    <td>
                        <a style="float: left; padding-right: 3px" href="javascript:void(0);" class="det_cmp" data-id="<?php echo $cmp_id?>"><img src="images/b_browse.png" width="14px" /></a><div style="float: left"><?php echo $nro ?></div>                        
                    </td>
                    <td>
                        <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>
                    </td>
                    <td>
                        <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>
                    </td>
                    <?
                    if($tf!='fp'){
                    ?>
                    <td>
                        <?php echo number_format(round($saldo, 2),2,".",","); ?>
                    </td>	
                    <td class="no_print">
                        <?php echo $_c; ?>
                    </td>	
                    <?php }?>
                    <td class="no_print">
                        <?php echo $cc; ?>
                    </td>
                    <td class="no_print">
                        <?php echo $cf; ?>
                    </td>
                    <?php
                    if($tf=='fp'){
                    ?>
                    <td >
                        <?php echo $dat_banco; ?>
                    </td>
                    <?php
                    }
                    ?>
                </tr>
        <?php
    }

    function descripcion($descripcion) {
        ?>
                <tr>
                    <td align="right">
                        
                    </td>                    
                    <td>
                        <div style="font-style: italic">
        <?php echo $descripcion; ?>
                        </div>
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        
                    </td>	
                </tr>
        <?php
    }

    function totales($debe, $haber,$tf) {
        ?>
                <tfoot>
                    <tr>
                        <td colspan="3" align="right">
                            
                        </td>                        
                        <td>
                            <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>
                        </td>
                        <td>
                                <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>
                        </td>	
                        <td class="no_print">
                            
                        </td>
                        <td class="no_print">
                            
                        </td>                        
                        <td class="no_print">
                            
                        </td>	
                        <?php if($tf!='fp'){ ?> 
                        <td class="no_print">
                            
                        </td>	
                        <?php }?>
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
    
    function dibujar_popup(){
        $form="";
        if(isset($_GET['form'])){
            $form="&form=".$_GET['form'];
        }
        ?>
            <!DOCTYPE html>
            <html>
                <head>        
                </head>
                <body>
                    <div id="iframe">
                        <form name="formulario" action="gestor.php?mod=<?php echo $this->modulo.$form;?>" method="POST">
                            <input type="hidden" name="formu" value="ok">
                            <input type="hidden" name="fecha_inicio" value="<?php echo $_GET['fi'];?>">
                            <input type="hidden" name="fecha_fin" value="<?php echo $_GET['ff'];?>">
                            <input type="hidden" name="moneda_hecho" value="<?php echo $_GET['mh'];?>">
                            <input type="hidden" name="moneda_reporte" value="<?php echo $_GET['mr'];?>">
                            <input type="hidden" name="gestion" value="<?php echo $_GET['ges'];?>">
                            <input type="hidden" name="lista_cuentas" value="<?php echo $_GET['id'];?>">
                            <input type="hidden" name="cde_une_id" value="<?php echo $_GET['une_id'];?>">
                            <input type="hidden" name="info" value="ok">
                        </form>
                    </div>
                </body>
                <script>                        
                    document.formulario.submit();
                </script>
            </html>
        <?php
    }
    
    private function url_actual() {
                $server = $_SERVER;
                $nombre_proyecto = $server['REQUEST_URI'];
                $nombre_proyecto = explode('/', $nombre_proyecto);
                $nombre_proyecto = $nombre_proyecto[1];
                return 'http://' . $server['SERVER_NAME'] . '/' . $nombre_proyecto;
            }

}
?>