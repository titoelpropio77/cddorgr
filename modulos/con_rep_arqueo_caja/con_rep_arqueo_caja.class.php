<?php

class CON_REP_ARQUEO_CAJA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_REP_ARQUEO_CAJA() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_arqueo_caja';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('ARQUEO CAJA');
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
                    
                    if($('#cja_usu_id option:selected').val()===''){
                        mostrar_error("#cja_usu_id","Seleccione un Usuario");
                        return false;
                    }
                    
                    var cuentas=$('#tab_cuentas_act_disp tr');
                    if(cuentas.size()===0){
                        mostrar_error("#add_det","Agrege por lo menos una Caja");
                        return false;
                    }
//                    return false;
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
                    if(filtro==='p'){
                        $("#fecha_inicio").val(fechai);
                        $("#fecha_fin").val(fechaf);
                    }
                    
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
            <?            
            $tf=  isset($_GET['form'])?$_GET['form']:'c';
            ?>
            <script src="js/util.js"></script>
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
                                        <option value="r" selected="">Por rango</option>
                                        <option value="p" >Por periodo</option>
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
                                    <input class="caja_texto" name="fecha_inicio" id="fecha_inicio" size="12" value="<?php echo date('d/m/Y');?>" type="text">
                                    <!--<span class="flechas1">(DD/MM/AAAA)</span>-->
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv" class="por_rango">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_fin" id="fecha_fin" size="12" value="<?php echo date('d/m/Y');?>" type="text">                                    
                                </div>		
                            </div>
                            <!--Fin-->
                            <script>
                                    asignar_fechas();
                            </script>
                            <!--Inicio-->
                            
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
                            
                            
                             <script>
                                $("#gestion").live('change',function (){
                                    definir_filtro();
                                    var tf=$("#tf").val();
                                    if(tf==="c"||tf==="ac" ){
//                                        complete_cuenta();
//                                        complete_cuenta_ca();
                                    }
                                    if(tf==="ca"){
//                                        complete_cuenta_ac_a();
//                                        complete_cuenta_ac_c();                                        
                                    }
//                                    complete_cuenta_cc();
                                    $("#tab_lista_cuentas").children().remove();
                                    $("#id_cuenta_a").val();
                                    $("#nombre_cuenta_a").val('');
                                    $("#id_cuenta_f").val();
                                    $("#nombre_cuenta_f").val('');
                                    $("#id_cuenta").val('');
                                    $("#nombre_cuenta").val('');
                                    $("#id_centroc").val('');
                                    $("#nombre_centroc").val('');
                                    $('#cja_usu_id').trigger('change');
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
                            
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Usuario </div>
                                <div id="CajaInput">
                                    <?php $grupo=FUNCIONES::atributo_bd_sql("select usu_gru_id as campo from ad_usuario where usu_id='$_SESSION[id]'"); ?>
                                    <?php if ($grupo=='Administradores' || $grupo=='JEFECARTERA' || $_SESSION[id]=='mzurita'){?>
                                        <script src="js/chosen.jquery.min.js"></script>
                                        <link href="css/chosen.min.css" rel="stylesheet"/>
                                        <select style="width: 350px" name="cja_usu_id"  id="cja_usu_id" data-placeholder="-- Seleccione --" class="caja_texto" <?php if ($_GET['tarea']=="MODIFICAR") echo "disabled";?>>
                                             <option value=""></option>
                                             <?php 		
                                                 $fun=NEW FUNCIONES;
                                                 $fun->combo("select usu_id as id,concat(int_nombre,' ',int_apellido) as nombre from ad_usuario,interno,ad_grupo where usu_per_id=int_id and usu_estado = 1 and usu_gru_id=gru_id and usu_gru_id != 'AFILIADOS' order by usu_id asc",$_SESSION[id]);		
                                             ?>
                                        </select>
                                        <script>
                                            $('#cja_usu_id').chosen({
                                                allow_single_deselect:true
                                            }).change(function(){
//                                                $('#cja_usu_id').trigger('change');
                                            });
                                        </script>
                                    <?php }else{?>
                                        <select name="cja_usu_id" id="cja_usu_id" class="caja_texto" <?php if ($_GET['tarea']=="MODIFICAR") echo "disabled";?>>
                                             <?php 		
                                                 $fun=NEW FUNCIONES;
                                                 $fun->combo("select usu_id as id,concat(int_nombre,' ',int_apellido) as nombre from ad_usuario ,interno where usu_per_id=int_id and usu_id = '$_SESSION[id]' order by usu_id asc",$_SESSION[id]);
                                             ?>
                                        </select>
                                    <?php }?>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Caja</div>
                                <div id="CajaInput">
                                    <select style="width: 300px;" name="cja_cue_id" id="cja_cue_id" class="caja_texto">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                                <!--<img style="float: left;"id="add_det" class="add_det" height="18" src="images/boton_agregar.png">-->
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuentas a listar</div>
                                <div id="CajaInput">
                                    <div class="box_lista_cuenta"> 
                                        <table id="tab_cuentas_act_disp" class="tab_lista_cuentas">
                                        </table>                                    
                                    </div>
                                </div>							   							   								
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Hecho</div>
                                <div id="CajaInput">
                                    <select style="width: 200px;" name="usu_creado" id="usu_creado" class="caja_texto">
                                        <option value="">Por Todos</option>
                                        <option value="1">Solo por el Usuario</option>
                                    </select>
                                </div>
                            </div>
                            <script>
                                $('#cja_usu_id').change(function (){
                                    var usu_id=$(this).val();
                                    var ges_id=$('#gestion option:selected').val();
                                    $('#tab_cuentas_act_disp').children().remove();
                                    $.get('AjaxRequest.php',{peticion:'cajas',usu_id:usu_id, ges_id:ges_id},function(respuesta){
                                        var lista=(JSON.parse(trim(respuesta)));
                                        $('#cja_cue_id').children().remove();
                                        var options='';
                                        options+='<option value="">Seleccione</option>';
                                        for(var i=0;i<lista.length;i++){
                                            var cu=lista[i];
                                            options+='<option data-moneda="'+cu.moneda+'" value="'+cu.id+'">'+cu.nombre+'</option>';
                                        }
                                        $('#cja_cue_id').append(options);
                                        
                                    });
                                    
                                });
                                $('#cja_usu_id').trigger('change');
                                $('#cja_cue_id').change(function (){
                                    var codigo=trim($('#cja_cue_id option:selected').val());
                                    var moneda=trim($('#cja_cue_id option:selected').attr('data-moneda'));
                                    var valor=trim($('#cja_cue_id option:selected').text());

                                    if(codigo!==""){
                                        agregar_cuenta({codigo:codigo, valor:valor,moneda:moneda},'cuentas_act_disp');
                                        $('#cja_cue_id option:[value=""]').attr('selected','true');                                
                                    }else{
//                                        $.prompt('Seleccione una Cuenta');
                                    }
                                });
//                                $('#add_det').click(function(){
//                                    var codigo=trim($('#cja_cue_id option:selected').val());
//                                    var moneda=trim($('#cja_cue_id option:selected').attr('data-moneda'));
//                                    var valor=trim($('#cja_cue_id option:selected').text());
//
//                                    if(codigo!==""){
//                                        agregar_cuenta({codigo:codigo, valor:valor,moneda:moneda},'cuentas_act_disp');
//                                        $('#cja_cue_id option:[value=""]').attr('selected','true');                                
//                                    }else{
//                                        $.prompt('Seleccione una Cuenta');
//                                    }
//                                });
                                function agregar_cuenta(cuenta,input) {
                                    if (!existe_en_lista(cuenta.codigo,input)) {
                                        var fila='';
                                        fila += '<tr>';
                                        fila += '   <td>';
                                        fila += '       <input type="hidden" name="monedas[]" value="'+cuenta.moneda+'">';
                                        fila += '       <input type="hidden" name="txt_cod_cuentas[]" value="'+cuenta.valor+'">';
                                        fila += '       <input type="hidden" name="cod_cuentas[]" class="h_cuentas_act_disp" value="'+cuenta.codigo+'">';
                                        fila += '       ' + cuenta.valor;
                                        fila += '   </td>';
                                        fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                        fila += '</tr>';
                                        $("#tab_"+input).append(fila);                                
                                    }else{
//                                        $.prompt('La cuenta ya existe en la lista');
                                    }
                                }

                                function existe_en_lista(id_cuenta,input) {
                                    var lista = $(".h_"+input+"");
                                    for (var i = 0; i < lista.size(); i++) {
                                        var cuenta = lista[i];
                                        var id = $(cuenta).val();
                                        if (id === id_cuenta) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }
                                $(".img_del_cuenta").live('click', function() {
                                    $(this).parent().parent().remove();
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

                if ($_POST['formu'] == 'ok'){
                    if($_POST[cod_cuentas]){
                        $this->mostrar_reporte();
                    }else{
                    ?>
                    <div class="ancho100">
                        <div class="msInformacion limpiar">
                            Ingrese las Cuentas a listar 
                        </div>
                        <input type="button" value="Volver" onclick="location.href='<?php echo "$this->link?mod=$this->modulo"?>'">
                    </div>
                    <?php
                    }
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
                  </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '&form='.$_GET['form'].'\';"></td></tr></table><br><br>
                  ';
              }

            function mostrar_reporte() {
//                echo '<pre>';
//                print_r($_GET);
//                echo '</pre>';
//                echo '<pre>';
//                print_r($_POST);
//                echo '</pre>';
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
                                        <strong><h3>ARQUEO DE CAJA</h3></strong></br>
                                        <?php
                                        if ($_POST['fecha_inicio'] <> "")
                                            echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
                                        if ($_POST['fecha_fin'] <> "")
                                            echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'].'<br>';
                                        ?>
                                        <strong><?php echo FUNCIONES::atributo_bd("con_gestion", "ges_id=".$_POST['gestion'], "ges_descripcion")?></strong>
                                        <!--</br><strong>Expresado en: <?php // echo $this->descripcion_moneda($_POST['moneda_reporte']); ?></strong>-->
                                    </center>
                                </p>
                                </td>
                                <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                            </tr> 
                        </table>
                        <?php
                        //ingresos
                        $and_une_id="";
                        if($_POST[cmp_une_id]){
                            $and_une_id=" and cmp_une_id='$_POST[cmp_une_id]'";
                        }
                        $and_usu_cre="";
                        if($_POST[usu_creado]){
                            $and_usu_cre=" and cmp_usu_cre='$_POST[cja_usu_id]'";
                        }
                        
                        $fecha_inicio=  FUNCIONES::get_fecha_mysql($_POST[fecha_inicio]);
                        $fecha_fin=  FUNCIONES::get_fecha_mysql($_POST[fecha_fin]);
                        
                        $lista_cajas=  implode(',', $_POST[cod_cuentas]);
//                        usu_id='$_POST[cja_usu_id]' and 
//                        $sql="select usu_id,cmp_id, cmp_fecha,cde_valor, cde_mon_id,cde_cue_id,cde_glosa,
//                                    cue_descripcion,cde_fpago,cde_fpago_descripcion,cmp_usu_cre
//                                from con_comprobante,ad_usuario,con_comprobante_detalle ,con_cuenta
//                                where cmp_usu_id=usu_per_id and cmp_id=cde_cmp_id and cde_cue_id=cue_id and cde_mon_id=cue_mon_id and cmp_eliminado='No' and 
//                                        cmp_fecha>='$fecha_inicio' and cmp_fecha<='$fecha_fin' and cue_id in($lista_cajas) $and_une_id $and_usu_cre
//                                ;";
//                        echo $sql;
                        $sql="select usu_id,cmp_id, cmp_fecha,cde_valor, cde_mon_id,
                                    cde_cue_id,cde_glosa,
                                    cue_descripcion,cde_fpago,cde_fpago_descripcion,cmp_usu_cre
                                from con_comprobante inner join ad_usuario on (                                    
                                    cmp_fecha>='$fecha_inicio' 
                                    and cmp_fecha<='$fecha_fin'
                                    and cmp_usu_id=usu_per_id     
                                )
                                inner join con_comprobante_detalle on(cmp_id=cde_cmp_id)
                                inner join con_cuenta on (cde_cue_id=cue_id and cde_mon_id=cue_mon_id)
                                inner join ad_grupo on (usu_gru_id=gru_id)
                                where  cmp_eliminado='No'                
                                and gru_id != 'AFILIADOS'
                                and cue_id in($lista_cajas) $and_une_id $and_usu_cre order by cmp_fecha asc";
                        
                        
                        $result=FUNCIONES::objetos_bd_sql($sql);
                        $this->mostrar_resultado($result);

                        ?>                 
                    </center>
                    <br>
                    <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr>
                    </table>
                </div></br>
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
            
            function mostrar_resultado($result){
                ?>
                <style>
                    .tablaReporte tbody tr td{                            
                            border-right:none !important;
                    }
                    .tablaReporte{
                            border:none !important;
                    }
                </style>
                <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr class="fil_header">
                            <td widtd="100%" colspan="9"><b style="font-size: 16px;">INGRESOS</b></td>
                        </tr>
                        <tr class="fil_header">
                            <td widtd="1"><b>#</b></td>
                            <td widtd="2%"><b>fecha</b></td>
                            <td widtd="25%"><b>Detalle</b></td>
                            <td widtd="5%"><b>Bolivianos</b></td>
                            <td widtd="5%"><b>Reales</b></td>
                            <td widtd="5%"><b>Dolares</b></td>
                            <td widtd="15%"><b>Caja</b></td>
                            <td widtd="5%"><b>Forma de Pago</b></td>
                            <td widtd="5%"><b>Usuario</b></td>
                        </tr>
                        <?php $sum_in_bs=0;?>
                        <?php $sum_in_usd=0;?>
                        <?php $sum_in_real=0;?>
                        <?php $acmps=array();?>
                        <?php $nro=1;?>
                        <?php for($i=0;$i<$result->get_num_registros();$i++){?>
                        <?php $fila=$result->get_objeto();?>
                            <?php if($fila->cde_valor>0){?>
                            <tr>
                                <td><?php echo $nro;$nro++?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($fila->cmp_fecha);?></td>
                                <td><?php echo $fila->cde_glosa;?></td>
                                <?php if($fila->cde_mon_id==1){?>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <?php $sum_in_bs+=$fila->cde_valor;?>
                                <?php }elseif($fila->cde_mon_id==2){?>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <?php $sum_in_usd+=$fila->cde_valor;?>
                                <?php }elseif($fila->cde_mon_id==3){?>
                                    <td>&nbsp;</td>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <td>&nbsp;</td>
                                    <?php $sum_in_real+=$fila->cde_valor;?>
                                <?php }?>
                                <td><?php echo $fila->cue_descripcion;?></td>
                                <td><?php echo "$fila->cde_fpago";?>
                                <?php echo trim($fila->cde_fpago_descripcion)?": ".trim($fila->cde_fpago_descripcion):"";?></td>
                                <td><?php echo $fila->cmp_usu_cre;?></td>
                            </tr>
                            <?php $acmps[$fila->cmp_id]=$acmps[$fila->cmp_id]+1; ?>
                            <?php }?>
                        
                        <?php $result->siguiente(); ?>
                        <?php }?>
                        <tr class="fil_footer">
                            <td colspan="3">Total</td>
                            <td ><?php echo number_format($sum_in_bs,2);?></td>
                            <td ><?php echo number_format($sum_in_real,2);?></td>
                            <td ><?php echo number_format($sum_in_usd,2);?></td>
                            <td colspan="3">&nbsp;</td>
                        </tr>
                        <tr class="fil_header">
                            <td widtd="100%" colspan="9"><b style="font-size: 16px;">EGRESOS</b></td>
                        </tr>
                        <tr class="fil_header">
                            <td widtd="1%"><b>#</b></td>
                            <td widtd="2%"><b>fecha</b></td>
                            <td widtd="25%"><b>Detalle</b></td>
                            <td widtd="5%"><b>Bolivianos</b></td>
                            <td widtd="5%"><b>Reales</b></td>
                            <td widtd="5%"><b>Dolares</b></td>
                            <td widtd="15%"><b>Caja</b></td>
                            <td widtd="5%"><b>Forma de Pago</b></td>
                            <td widtd="5%"><b>Usuario</b></td>
                        </tr>
                        <?php $result->reset();?>
                        <?php $sum_eg_bs=0;?>
                        <?php $sum_eg_usd=0;?>
                        <?php $sum_eg_real=0;?>
                        <?php $nro=1;?>
                        <?php for($i=0;$i<$result->get_num_registros();$i++){?>
                        <?php $fila=$result->get_objeto();?>
                            <?php if($fila->cde_valor<0){?>
                            <?php $fila->cde_valor=$fila->cde_valor*(-1);?>
                            <tr>
                                <td><?php echo $nro;$nro++?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($fila->cmp_fecha);?></td>
                                <td><?php echo $fila->cde_glosa;?></td>
                                <?php if($fila->cde_mon_id==1){?>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <?php $sum_eg_bs+=$fila->cde_valor;?>
                                <?php }elseif($fila->cde_mon_id==2){?>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <?php $sum_eg_usd+=$fila->cde_valor;?>
                                <?php }elseif($fila->cde_mon_id==3){?>
                                    <td>&nbsp;</td>
                                    <td><?php echo number_format($fila->cde_valor,2);?></td>
                                    <td>&nbsp;</td>
                                    <?php $sum_eg_real+=$fila->cde_valor;?>
                                <?php }?>
                                <td><?php echo $fila->cue_descripcion;?></td>
                                <td><?php echo "$fila->cde_fpago: $fila->cde_fpago_descripcion";?></td>
                                <td><?php echo $fila->cmp_usu_cre;?></td>
                            </tr>
                            <?php }?>
                        <?php $result->siguiente(); ?>
                        <?php }?>
                        <tr class="fil_footer">
                            <td colspan="3">Total</td>
                            <td ><?php echo number_format($sum_eg_bs,2)?></td>
                            <td ><?php echo number_format($sum_eg_real,2)?></td>
                            <td ><?php echo number_format($sum_eg_usd,2)?></td>
                            
                            <td colspan="3">&nbsp;</td>
                        </tr>
                        <tr class="fil_footer">
                            <td colspan="3">SALDO ACTUAL</td>
                            <td ><?php echo number_format($sum_in_bs-$sum_eg_bs,2);?></td>
                            <td ><?php echo number_format($sum_in_real-$sum_eg_real,2);?></td>
                            <td ><?php echo number_format($sum_in_usd-$sum_eg_usd,2);?></td>
                            
                            <td colspan="3">&nbsp;</td>
                        </tr>
                    </tbody>
                    
                </table>
                <div style="text-align: left; font-size:  12px; margin-top: 3px">
                    Cantidad de Comprobantes <b><?php echo count($acmps);?></b>
                </div>
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
                $sql = "
                        select 
                                cmp_id, cmp_nro, cmp_fecha, cde_glosa, cde_valor,cde_cue_id,cde_can_id, cde_cco_id, cde_cfl_id, cmp_peri_id, cmp_tco_id,
                                cmp_forma_pago, cmp_ban_id, cmp_ban_char, cmp_ban_nro
                        from
                                con_comprobante c, con_comprobante_detalle cd
                        where
                                c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and $col_c=$id_cuenta $filtro $fcentro_c $fcuenta_f $fforma_pago 
                                and c.cmp_ges_id=$ges_id and cde_mon_id='" . $_POST['moneda_reporte']."'".$filtro_c. $filtro_cli." order by cmp_fecha asc;";
//                echo $sql.'<br>';
                $conec_det->ejecutar($sql);
                $num = $conec_det->get_num_registros();
                if(!($num>0)){
                    return false;
                }
                ?>
                <div style="font-size: 13px; margin: 15px 25px 0 25px">
                    <div style="float: left;"><b>CODIGO: </b>&nbsp; <?php echo $codigo ?>  &nbsp;&nbsp;<b>CUENTA: </b>&nbsp; <?php echo strtoupper($descripcion); ?>&nbsp;&nbsp;
                        <?php if($_POST[cli_interno]){?>
                            <b>CLIENTE: </b>&nbsp; <?php echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido)as campo from interno where int_id='$_POST[cli_interno]'"); ?>
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
                    &nbsp;
                </td>							
                <td>
                    &nbsp;
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
                        <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>&nbsp;
                    </td>
                    <td>
                        <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>&nbsp;
                    </td>
                    <?
                    if($tf!='fp'){
                    ?>
                    <td>
                        <?php echo number_format(round($saldo, 2),2,".",","); ?>&nbsp;
                    </td>	
                    <td class="no_print">
                        <?php echo $_c; ?>&nbsp;
                    </td>	
                    <?php }?>
                    <td class="no_print">
                        <?php echo $cc; ?>&nbsp;
                    </td>
                    <td class="no_print">
                        <?php echo $cf; ?>&nbsp;
                    </td>
                    <?php
                    if($tf=='fp'){
                    ?>
                    <td >
                        <?php echo $dat_banco; ?>&nbsp;
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

    function totales($debe, $haber,$tf) {
        ?>
                <tfoot>
                    <tr>
                        <td colspan="3" align="right">
                            &nbsp;
                        </td>                        
                        <td>
                            <?php if ($debe > 0) echo number_format($debe, 2, ".", ","); ?>&nbsp;
                        </td>
                        <td>
                                <?php if ($haber > 0) echo number_format($haber, 2, ".", ","); ?>&nbsp;
                        </td>	
                        <td class="no_print">
                            &nbsp;
                        </td>
                        <td class="no_print">
                            &nbsp;
                        </td>                        
                        <td class="no_print">
                            &nbsp;
                        </td>	
                        <?php if($tf!='fp'){ ?> 
                        <td class="no_print">
                            &nbsp;
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

}
?>