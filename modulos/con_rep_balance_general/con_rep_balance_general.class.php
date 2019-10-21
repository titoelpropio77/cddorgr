<?php

class CON_REP_BALANCE_GENERAL extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_REP_BALANCE_GENERAL() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_balance_general';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('BALANCE GENERAL');
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
                function esFecha(strValue) {
                    var objRegExp = /\d{1,2}\/\d{1,2}\/\d{4}/;

                    if (!objRegExp.test(strValue))
                        return false; //doesn't match pattern, bad date
                    else {
                        var strSeparator = strValue.substring(2, 3)
                        //create a lookup for months not equal to Feb.
                        var arrayDate = strValue.split(strSeparator);

                        var arrayLookup = {'01': 31, '03': 31,
                            '04': 30, '05': 31,
                            '06': 30, '07': 31,
                            '08': 31, '09': 30,
                            '10': 31, '11': 30, '12': 31
                        }

                        var intDay = parseInt(arrayDate[0], 10);
                        var intMonth = parseInt(arrayDate[1], 10);
                        var intYear = parseInt(arrayDate[2], 10);

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



                    if (db === da && mb === ma && yb === ya) {
                        return 0;
                    }

                    if (ya > yb) {
                        return 1;
                    } else {
                        if (ya === yb) {
                            if (ma > mb) {
                                return 1;
                            } else {
                                if (ma === mb) {
                                    if (da > db) {
                                        return 1;
                                    } else {
                                        return -1;
                                    }
                                } else {
                                    return -1;
                                }
                            }
                        } else {
                            return -1;
                        }
                    }
                }

                $("#frm_sentencia").live('submit', function() {
                    $(".error").remove();
                    var fecha_inicio = $("#fecha_inicio").val();
                    var fecha_fin = $("#fecha_fin").val();
                    var ges_fecha_ini = $("#ges_fecha_ini").val();
                    var ges_fecha_fin = $("#ges_fecha_fin").val();
                    if (esFecha(fecha_inicio)) {
                        if (!(comparar_fechas(fecha_inicio, ges_fecha_ini) >= 0)) {
                            mostrar_error("#fecha_inicio", "La Fecha Inicio debe ser mayor o igual a la Fecha Inicial de la Gestion (" + ges_fecha_ini + ")");
                            return false;
                        } else {
                            if (!(comparar_fechas(fecha_inicio, ges_fecha_fin) <= 0)) {//// aqui esta mi idea principal......
                                mostrar_error("#fecha_inicio", "La Fecha Inicio debe ser menor o igual a la Fecha Final de la Gestion (" + ges_fecha_fin + ")");
                                return false;
                            }
                        }
                    } else {
                        if (fecha_inicio !== "") {
                            mostrar_error("#fecha_inicio", "La Fecha Inicio no es una fecha valida");
                            return false;
                        }
                    }

                    if (esFecha(fecha_fin)) {
                        if (!(comparar_fechas(fecha_fin, ges_fecha_ini) >= 0)) {
                            mostrar_error("#fecha_fin", "La Fecha Fin debe ser mayor o igual a la Fecha Inicial de la Gestion (" + ges_fecha_ini + ")");
                            return false;
                        } else {
                            if (!(comparar_fechas(fecha_fin, ges_fecha_fin) <= 0)) {//// aqui esta mi idea principal......
                                mostrar_error("#fecha_inicio", "La Fecha Fin debe ser menor o igual a la Fecha Final de la Gestion (" + ges_fecha_fin + ")");
                                return false;
                            }
                        }
                    } else {
                        if (fecha_fin !== "") {
                            mostrar_error("#fecha_fin", "La Fecha Fin no es una fecha valida");
                            return false;
                        }
                    }

                    if (!(comparar_fechas(fecha_fin, fecha_inicio) >= 0) && fecha_fin !== "" && fecha_inicio !== "") {
                        mostrar_error("#fecha_fin", "La Fecha Fin debe ser mayor o igual a la Fecha Inicio");
                        return false;
                    }
                });
                function mostrar_error(input, mensaje) {
                    $(input).after('<span class="error">' + mensaje + '</span>');
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
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuentas en Cero:</div>
                                <div id="CajaInput">
                                    <select name="saldo_cero" class="caja_texto">
                                        <option value="0">No Mostrar</option>
                                        <option value="1">Mostrar</option>
                                    </select>
                                </div>
                            </div>
                            <style>
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
                            <!--Inicio-->

                            <script>
                                var options = {
                                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&",
                                    varname: "input",
                                    json: true,
                                    shownoresults: false,
                                    maxresults: 6,
                                    callback: function(obj) {
                                        agregar_cuenta(obj);
                                    }
                                };
                                var as_json1 = new _bsn.AutoSuggest('nombre_cuenta', options);

                                function agregar_cuenta(cuenta) {
                                    if (!existe_en_lista(cuenta.id)) {
                                        var fila = '<tr data-id="' + cuenta.id + '">';
                                        fila += '<td>' + cuenta.value + '</td>';
                                        fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                        fila += '</tr>';
                                        $("#tab_lista_cuentas").append(fila);
                                        $("#nombre_cuenta").val("");
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
                <div>
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
                $extra1.=" <a href=javascript:imprimir_balance();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                $extra2 = "'</center></body></html>'";
                $myday = setear_fecha(strtotime(date('Y-m-d')));
                echo '	<table align=right border=0><tr>
                        <td><a href="#" id="importar_excel"><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL"></a></td>
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
                        </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '\';"></td></tr></table><br><br>
		';
            }

            function mostrar_reporte() {
                $this->barra_de_impresion();
                $parametros= 'gestor.php?mod=con_rep_libro_mayor&tform=popup&fi='.$_POST['fecha_inicio'].'&ff='.$_POST['fecha_fin'].'&mh='.$_POST['moneda_hecho'].'&mr='.$_POST['moneda_reporte'].'&ges='.$_POST['gestion'];
                ?>
                <div id="clone" style="display:none;"></div>
                <div id="contenido_reporte" style="clear:both;">
                    <script src="js/jquery-1.3.2.min.js"></script>
                    <script>
                        $(".det_balance").live('click',function (){                            
                            var ruta=$("#paramentros").val();
                            var id=$(this).attr('data-id');
                            window.open(ruta+'&id='+id,'Libro Mayor','width=900, height=500, scrollbars=yes')
                        });

                        function imprimir_balance(){
                            $(".det_balance").hide();
                            window.print();
                            $(".det_balance").show();
                        }
                        
                        $('#importar_excel').click(function(e) {
						
                                var copy = $('#contenido_reporte').clone();
                                $('#clone').html(copy);	
                                $('#clone').find("img").remove();

                                $('.tablaReporte').attr('border', '1');
                                window.open('data:application/vnd.ms-excel,' + escape($('#clone').html()));
                                e.preventDefault();
                                $('.tablaReporte').attr('border', '0');
                        });
                                        
                    </script>
                    <input type="hidden" id="paramentros" value="<?php echo $parametros?>"/>
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
                                <strong><h3>BALANCE GENERAL</h3></strong></br>
                                <?php
                                if ($_POST['fecha_inicio'] <> "")
                                    echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
                                if ($_POST['fecha_fin'] <> "")
                                    echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'] . '<br>';
                                ?>
                                <strong><?php echo FUNCIONES::atributo_bd("con_gestion", "ges_id=" . $_POST['gestion'], "ges_descripcion") ?></strong>
                                </br><strong>Expresado en: <?php echo $this->descripcion_moneda($_POST['moneda_reporte']); ?></strong>
                            </center>
                            </p>
                            </td>
                            <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                            </tr> 
                        </table>
                        <?php
  
                        
                        $parametro=  FUNCIONES::parametro("formula_bal_gral",$ges_id);
                        $formula=  json_decode($parametro);
                        $equ1=$formula->{'1'};
                        $formula_val_1=array();
                        for ($i = 0; $i < count($equ1); $i++) {
                            $form=$equ1[$i];
                            $sql = "SELECT * FROM con_cuenta WHERE cue_eliminado='No' and cue_ges_id=' $_POST[gestion]' and cue_codigo='$form->cuenta';";
//                            echo $sql.'<br>';
                            $cuenta=  FUNCIONES::objeto_bd_sql($sql);                            
                            $_cu = new stdClass();                            
                            $_cu->id = $cuenta->cue_id;
                            $_cu->codigo = $cuenta->cue_codigo;
                            $_cu->descripcion = $cuenta->cue_descripcion;
                            $_cu->level = $cuenta->cue_tree_level;
                            $_cu->tipo = $cuenta->cue_tipo;
                            $calculo = $this->obtener_sumas_cuenta($cuenta->cue_id);
                            $_cu->tmonto = $calculo->tmonto;
                            $_cu->detalles = $calculo->detalles;
                            $campo=new stdClass();                            
                            $campo->cuenta=$_cu;
                            $campo->signo=$form->signo;
                            $campo->op=$form->op;                            
                            $campo->sum=$form->sum;                            
                            $formula_val_1[]=$campo;                                                    
                        }
                        $equ2=$formula->{'2'};
                        $formula_val_2=array();
                        for ($i = 0; $i < count($equ2); $i++) {
                            $form=$equ2[$i];
                            $sql = "SELECT * FROM con_cuenta WHERE cue_eliminado='No' and cue_ges_id=' $_POST[gestion]' and cue_codigo='$form->cuenta';";
                            $cuenta=  FUNCIONES::objeto_bd_sql($sql);                            
                            $_cu = new stdClass();                            
                            $_cu->id = $cuenta->cue_id;
                            $_cu->codigo = $cuenta->cue_codigo;
                            $_cu->descripcion = $cuenta->cue_descripcion;
                            $_cu->level = $cuenta->cue_tree_level;
                            $_cu->tipo = $cuenta->cue_tipo;
                            $calculo = $this->obtener_sumas_cuenta($cuenta->cue_id);
                            $_cu->tmonto = $calculo->tmonto;
                            $_cu->detalles = $calculo->detalles;
                            $campo=new stdClass();                            
                            $campo->cuenta=$_cu;
                            $campo->signo=$form->signo;
                            $campo->op=$form->op;                            
                            $campo->sum=$form->sum;                            
                            $formula_val_2[]=$campo;                                                    
                        }
                        $monto_res=  $this->obtener_total_estado_resultado();                        
                        $this->mostrar_tabla_resultados($formula_val_1,$formula_val_2,$monto_res);
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
                </form>
                <?php
            }
            
            function mostrar_tabla_resultados($formula_val_1,$formula_val_2,$monto_res){
                $ges_id=$_POST["gestion"];
                $sql="select max(cue_tree_level) as max from con_cuenta where cue_ges_id=$ges_id;";
                $conect=new ADO();
                $conect->ejecutar($sql);
                $objeto=$conect->get_objeto();
                $max_level=$objeto->max;
                ?>
                <table   width="95%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>                        
                        <tr>                                    
                            <th ><b>CODIGO</b></th>
                            <th ><b>CUENTAS</b></th>
                            <th colspan="<?php echo $max_level?>"><b>RESULTADO</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach ($formula_val_1 as $campo) { ?>
                            <?php $cuenta=$campo->cuenta;?>
                            <?php $this->movimiento($cuenta,$max_level,$campo->op); ?>                            
                            <?php if($campo->sum){?>
                                <?php $this->add_fila($monto_res,$max_level); ?>
                                <?php $this->total(($cuenta->tmonto*$campo->op)+$monto_res,$this->toUpper_space($cuenta->descripcion),$max_level); ?>
                            <?php }else{?>
                                <?php $this->total($cuenta->tmonto*$campo->op,$this->toUpper_space($cuenta->descripcion),$max_level); ?>
                            <?php }?>                            
                            <tr style="border: 1px solid #000"><td colspan="<?php echo 2+$max_level;?>">&nbsp;</td></tr>
                        <?php }?>
                        <?php $i=0;?>
                        <?php $monto_total=0;?>
                        <?php $txt_total="";?>
                        <?php foreach ($formula_val_2 as $campo) { ?>
                            <?php if($i>0){?>
                                <tr style="border: 1px solid #000"><td colspan="<?php echo 2+$max_level?>">&nbsp;</td></tr>
                                <?php $txt_total.='&nbsp;+&nbsp;';?>
                            <?php }?>                            
                            <?php $i++;?>                            
                            <?php $cuenta=$campo->cuenta;?>
                            <?php $txt_total.=$this->toUpper_space($cuenta->descripcion);?>
                            <?php $this->movimiento($cuenta,$max_level,$campo->op) ?>
                            <?php if($campo->sum){?>
                                <?php $this->add_fila($monto_res,$max_level) ?>
                                <?php $this->total(($cuenta->tmonto*$campo->op)+$monto_res,$this->toUpper_space($cuenta->descripcion),$max_level); ?>
                                <?php $monto_total+=(($cuenta->tmonto*$campo->op)+$monto_res)*$campo->signo; ?>
                            <?php }else{?>
                                <?php $this->total($cuenta->tmonto*$campo->op,$this->toUpper_space($cuenta->descripcion),$max_level); ?>
                                <?php $monto_total+=($cuenta->tmonto*$campo->op)*$campo->signo; ?>
                            <?php }?>                            
                            
                        <?php }?>
                        
                        <?php $this->total($monto_total,$txt_total,$max_level) ?>                        
                    </tbody>
                    <?//php $this->totales($cu_activo,$cu_pasivo,$cu_patrimonio,$max_level,$monto_res); ?>
                </table>
                <?php
            }
            
            
                    
            function espacio($n){
                $sp="";
                for ($i = 0; $i < $n; $i++) {
                    $sp.="&nbsp;";
                }
                return $sp;
            }
            function toUpper_space($txt){
                $txt=  strtoupper($txt);
                $cad="";
                for ($i = 0; $i < strlen($txt); $i++) {
                    $cad.=$txt[$i]."&nbsp;";
                }
                return "&nbsp;".$cad;
            }
            
            function obtener_sumas_cuenta($id_cuenta) {
                $cuentas = new ADO();
                $sql = "select cue_id, cue_codigo, cue_descripcion, cue_tipo, cue_padre_id, cue_tree_level 
                      from con_cuenta where cue_padre_id=$id_cuenta order by cue_codigo;";

                $cuentas->ejecutar($sql);
                $monto = 0;
                $detalles = array();
                for ($i=0; $i < $cuentas->get_num_registros(); $i++) {
                    $cuenta = $cuentas->get_objeto();
                    $cuenta_m = new stdClass();
                    $cuenta_m->id = $cuenta->cue_id;
                    $cuenta_m->codigo = $cuenta->cue_codigo;
                    $cuenta_m->descripcion = $cuenta->cue_descripcion;
                    $cuenta_m->level = $cuenta->cue_tree_level;
                    $cuenta_m->tipo = $cuenta->cue_tipo;
                    if ($cuenta->cue_tipo == "Movimiento") {                        
                        $suma = $this->total_debe_haber_cuenta($cuenta->cue_id);
                        $saldo = $suma->tdebe - $suma->thaber;
                        $cuenta_m->tmonto = $saldo;
                        $cuenta_m->detalles = array();
                        $monto+=$saldo;
                        if($i==$cuentas->get_num_registros()-1){
                            $cuenta_m->ultimo=true; 
                        }
                    }else{
                        $calculo=$this->obtener_sumas_cuenta($cuenta->cue_id);
                        $cuenta_m->tmonto = $calculo->tmonto;
                        $cuenta_m->detalles = $calculo->detalles;
                        $monto+=$calculo->tmonto;
                    }
                    $detalles[]=$cuenta_m;
                    $cuentas->siguiente();
                }
                $resp = new stdClass();
                $resp->tmonto = $monto;
                $resp->detalles = $detalles;
                return $resp;
            }

            function total_debe_haber_cuenta($id_cuenta) {
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

                $ges_id = $_POST['gestion'];
                $fcentro_c=$_POST['id_centroc']!=''?' and cde_cco_id='.$_POST['id_centroc']:'';
                
                $and_une_id="";
                if($_POST[cmp_une_id]){
                    $and_une_id=" and cmp_une_id='$_POST[cmp_une_id]'";
                }							
                
                $sql = "
                        select 
                            cde_valor
                        from
                            con_comprobante c, con_comprobante_detalle cd
                        where
                            c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and cd.cde_cue_id=$id_cuenta $filtro $fcentro_c
                            and c.cmp_ges_id=$ges_id and cde_mon_id='" . $_POST['moneda_reporte'] . "' $and_une_id
                        ";
//                echo $sql." <br>";
                $conec_det->ejecutar($sql);
                $num = $conec_det->get_num_registros();
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
                    $conec_det->siguiente();
                }
                $tdh = new stdClass();
                $tdh->tdebe = $total_debe;
                $tdh->thaber = $total_haber;
                return $tdh;
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

    function movimiento($cuenta,$max_level,$signo=1) {
        $sw=true;
        if(!isset($_POST['saldo_cero']) || !$_POST['saldo_cero']){
            if($cuenta->tmonto==0){
                $sw=false;
            }
        }
        if($sw){
        ?>
            <tr>
                <td style="" >
                    <?php echo $cuenta->codigo; ?>
                </td>                    
                <td style=" <?php if($cuenta->tipo!="Movimiento") echo 'font-weight: bold'?>" >
                    <?php
                    if($cuenta->tipo=="Movimiento"){
                    ?>
                    <a style="float: left; padding-right: 3px" href="javascript:void(0);" class="det_balance" data-id="<?php echo $cuenta->id;?>"><img src="images/b_browse.png" width="14px" /></a>
                    <?php
                    }
                    ?>
                    <div style="float: left"><?php echo $this->espacio(($cuenta->level-1)*2). $cuenta->descripcion; ?></div>                    
                </td>
                <?php
                    for($i=1;$i<=$max_level;$i++){
                ?>
                    <?if($i==($max_level+1)-$cuenta->level){?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7; <?php if($cuenta->tipo!="Movimiento") {echo 'font-weight: bold;';} if($cuenta->ultimo){echo 'border-bottom: 1px solid #000;';} ?>" >
                        <?php echo number_format($cuenta->tmonto*$signo, 2, ".", ","); ?>&nbsp;
                    </td>
                    <?php }else{?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7;">
                        &nbsp;
                    </td>	
                    <?php }?>
               <?php
                    }
               ?>                    
            </tr>
    <?php
        }
        $detalles=$cuenta->detalles;
        for($i=0;$i<  count($detalles);$i++){
            $cuenta_h=$detalles[$i];
            $this->movimiento($cuenta_h, $max_level,$signo);
        }
    }                   
    function add_fila($monto,$max_level) {
        ?>
            <tr>
                <td style="" >
                    &nbsp;
                </td>                    
                <td style="font-weight: bold" >
                    <?php                     
                    if($monto>=0){
                        echo "GANANCIA DE LA GESTION";
                    }else{
                        echo "PERDIDA DE LA GESTION";
                    }
                    ?>
                </td>
                <?php
                    for($i=1;$i<=$max_level;$i++){
                ?>
                    <?if($i==$max_level){?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7; <?php if($cuenta->tipo!="Movimiento") {echo 'font-weight: bold;';} if($cuenta->ultimo){echo 'border-bottom: 1px solid #000;';} ?>" >
                        <?php echo number_format($monto, 2, ".", ","); ?>&nbsp;
                    </td>
                    <?php }else{?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7;">
                        &nbsp;
                    </td>	
                    <?php }?>
               <?php
                    }
               ?>                    
            </tr>
    <?php
    }                   
    function total($total,$msj ,$max_level) {
        ?>
        <tr class="total_suma">
            <td colspan="<?php echo 1+$max_level?>" align="right" style="font-size: 14px">
                T O T A L (<?php echo $msj?>)
            </td>
            <td style="font-size: 14px">
                <?php echo number_format($total,2); ?>&nbsp;
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

    function totales($cu_activo,$cu_pasivo,$cu_patrimonio,$max_level,$monto_res) {
        ?>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo 1+$max_level?>" align="right">
                            T O T A L 
                        </td>
                        <td>
                            <?php echo number_format(($cu_activo->tmonto)-$cu_pasivo->tmonto-($cu_patrimonio->tmonto+$monto_res),2); ?>&nbsp;
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
    
    
    function obtener_total_estado_resultado(){
//        $ges_id = $_POST['gestion'];
//        $conec_cue = new ADO();
//        $sql = "SELECT * FROM con_cuenta WHERE cue_eliminado='No' and cue_ges_id=$ges_id and cue_tree_level=1 order by cue_codigo limit 3,6;";
//        $conec_cue->ejecutar($sql);
//        
//        $cuenta = $conec_cue->get_objeto();        
//        $monto_ingreso = $this->obtener_sumas_cuenta_resultado($cuenta->cue_id);
//        $conec_cue->siguiente();
//        
//        $cuenta = $conec_cue->get_objeto();       
//        $monto_costo = $this->obtener_sumas_cuenta_resultado($cuenta->cue_id);        
//        $conec_cue->siguiente();
//        
//        $cuenta = $conec_cue->get_objeto();                
//        $monto_gasto= $this->obtener_sumas_cuenta_resultado($cuenta->cue_id);
        
        
//        $monto_res=$monto_ingreso*(-1)-$monto_costo-$monto_gasto;
        $ges_id = $_POST['gestion'];
        $parametro=  FUNCIONES::parametro("formula_est_res",$ges_id);
        $formula=  json_decode($parametro);
        
        $monto_res=0;
        for ($i = 0; $i < count($formula); $i++) {
            $form=$formula[$i];
            $sql = "SELECT * FROM con_cuenta WHERE cue_eliminado='No' and cue_ges_id=' $_POST[gestion]' and cue_codigo='$form->cuenta';";
            $cuenta=  FUNCIONES::objeto_bd_sql($sql);
            $monto_res += ($this->obtener_sumas_cuenta_resultado($cuenta->cue_id)*$form->op)*$form->signo;
        }
        return $monto_res;
    }
    
    function obtener_sumas_cuenta_resultado($id_cuenta) {
        $cuentas = new ADO();
        $sql = "select cue_id, cue_codigo, cue_descripcion, cue_tipo, cue_padre_id, cue_tree_level 
              from con_cuenta where cue_padre_id=$id_cuenta;";
        $cuentas->ejecutar($sql);
        $monto = 0;
        
        for ($i=0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();        
            if ($cuenta->cue_tipo == "Movimiento") {                        
                $suma = $this->total_debe_haber_cuenta($cuenta->cue_id);
                $saldo = $suma->tdebe - $suma->thaber;                
                $monto+=$saldo;                
            }else{
                $calculo=$this->obtener_sumas_cuenta_resultado($cuenta->cue_id);                
                $monto+=$calculo;
            }            
            $cuentas->siguiente();
        }
        return $monto;
    }
}