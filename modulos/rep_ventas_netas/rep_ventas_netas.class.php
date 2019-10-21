<?php

class REP_VENTAS_NETAS extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_VENTAS_NETAS() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_ventas_netas';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('VENTAS NETAS');
        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {
        if (!isset($_POST['info'])) {
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
            <script type="text/javascript" src="js/util.js"></script>
            <script>
                function esFecha(strValue) {
                    //check to see if its in a correct format
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
                    
                    if ($('#urb_id option:selected').val() == '') {
                        $.prompt('Seleccione un proyecto.');
                        return false;
                    }
                    mostrar_ajax_load();
                });
                function mostrar_error(input, mensaje) {
                    $(input).after('<span class="error">' + mensaje + '</span>');
                }

                $("#fecha_inicio, #fecha_fin").live('keydown', function(evt) {
                    $(".error").fadeOut(500, function() {
                        $(this).remove();
                    });
                });

                function asignar_fechas() {
                    var filtro = $("#f_filtro").val();
                    var fechas = "";
                    if (filtro === 'p') {
                        fechas = $("#periodo option:selected").val();
                    } else if (filtro === 'r') {
                        fechas = $("#gestion option:selected").attr("data_fechas");
                    }
                    var afechas = fechas.split(',');
                    var afechai = afechas[0].split('-');
                    var afechaf = afechas[1].split('-');
                    var fechai = afechai[2] + '/' + afechai[1] + '/' + afechai[0];
                    var fechaf = afechaf[2] + '/' + afechaf[1] + '/' + afechaf[0];
                    $("#ges_fecha_ini").val(fechai);
                    $("#ges_fecha_fin").val(fechaf);
                    $("#fecha_inicio").val(fechai);
                    $("#fecha_fin").val(fechaf);
                }

                function llenar_periodos(id) {
                    $.get("AjaxRequest.php?peticion=periodos&gesid=" + id, function(respuesta) {
                        var periodos = JSON.parse(respuesta);
                        $("#periodo").children().remove();
                        for (var i = 0; i < periodos.length; i++) {
                            var pdo = periodos[i];
                            var txt = '<option value="' + pdo.fechai + ',' + pdo.fechaf + '">' + pdo.descripcion + '</option>';
                            $("#periodo").append(txt);

                        }
                        asignar_fechas();
                    });
                }
                $(document).ready(function() {
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
            <div class="aTabsCont" style="display: none;">
                <div class="aTabsCent">
                    <ul class="aTabs">
                        <li><a href="gestor.php?mod=rep_ventas_netas&tarea=ACCEDER&form=c" <?php if ($_GET['form'] == "c" || $_GET['form'] == "") { ?>class="activo" <?php } ?>>Cuenta</a></li>
                        <li><a href="gestor.php?mod=rep_ventas_netas&tarea=ACCEDER&form=ac" <?php if ($_GET['form'] == "ac") { ?>class="activo" <?php } ?>>Anal&iacute;tico - Cuenta</a></li>
                        <li><a href="gestor.php?mod=rep_ventas_netas&tarea=ACCEDER&form=cf" <?php if ($_GET['form'] == "cf") { ?>class="activo" <?php } ?>>Cuenta - Flujo</a></li>
                        <li><a href="gestor.php?mod=rep_ventas_netas&tarea=ACCEDER&form=ca" <?php if ($_GET['form'] == "ca") { ?>class="activo" <?php } ?>>Cuenta - Anal&iacute;tico</a></li>
                        <li><a href="gestor.php?mod=rep_ventas_netas&tarea=ACCEDER&form=fp" <?php if ($_GET['form'] == "fp") { ?>class="activo" <?php } ?>>Forma de Pago</a></li>
                    </ul>
                </div>
            </div>
            <?
            $tf = isset($_GET['form']) ? $_GET['form'] : 'c';
            ?>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo; ?>&form=<?php echo $tf; ?>" method="POST" enctype="multipart/form-data">  
                    <input type="hidden" id="tf" value="<?php echo $tf; ?>">
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
                                        $fun->combo_data("select ges_id as id,ges_descripcion as nombre, concat(ges_fecha_ini,',',ges_fecha_fin) as fechas from con_gestion where ges_eliminado='No' order by ges_fecha_ini desc", "fechas", $_SESSION['ges_id']);
                                        ?>
                                    </select>
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Proyecto</div>
                                <div id="CajaInput">
                                    <select id="urb_id" name="urb_id">
                                        <option value="">Seleccione</option>
                                        <?php $fun = new FUNCIONES(); ?>
                                        <?php $fun->combo("select urb_id as id, urb_nombre as nombre from urbanizacion order by id asc", ''); ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">                                
                                <div class="Etiqueta" >Opci&oacute;n de Filtro</div>
                                <div id="CajaInput">
                                    <select name="f_filtro" class="caja_texto" id="f_filtro" style="min-width: 140px">                                        
                                        <option value="r">Toda la gestion</option>
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

                            <script>
                                function complete_cuenta_cc() {
                                    var options_cc = {
                                        script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cc&gesid=" + $("#gestion").val() + "&",
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

                                $("#nombre_centroc").live("keyup", function() {
                                    if ($(this).val() === '') {
                                        $("#id_centroc").val("");
                                    }
                                });
                                complete_cuenta_cc();
                            </script>
                            <!--Fin-->

                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Modalidad de Ventas:</div>
                                <div id="CajaInput">
                                    <select name="modalidad" id="modalidad" class="caja_texto">
                                        <option value="no">Tradicional</option>
                                        <option value="si">Multinivel</option>                                        
                                    </select>
                                </div>
                            </div>                                                        
                            <!--Fin-->


                            <script>
                                $("#gestion").live('change', function() {
                                    definir_filtro();
                                    var tf = $("#tf").val();
                                    if (tf === "c" || tf === "ac") {
                                        complete_cuenta();
                                        complete_cuenta_ca();
                                    }
                                    if (tf === "ca") {
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

                                function definir_filtro() {
                                    var filtro = $("#f_filtro").val();
                                    if (filtro === 'p') {
                                        llenar_periodos($("#gestion option:selected").val());
                                    } else if (filtro === 'r') {
                                        asignar_fechas();
                                    }
                                }
                                $("#periodo").live('change', function() {
                                    asignar_fechas();
                                });
                                $("#f_filtro").live('change', function() {
                                    var filtro = $("#f_filtro option:selected").val();
                                    if (filtro === 'p') {
                                        $(".por_periodo").show();
                                        $(".por_rango").hide();
                                    } else if (filtro === 'r') {
                                        $(".por_periodo").hide();
//                                        $(".por_rango").show();
                                    }
                                    definir_filtro();
                                });

                                function agregar_cuenta(cuenta, txt_cuenta) {
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
                  </a></td><td><a><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL" id="importar_excel"></a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '&form=' . $_GET['form'] . '\';"></td></tr></table><br><br>
                  ';
    }

    function mostrar_reporte() {
        
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        
        if (!isset($_POST['info'])) {
            $this->barra_de_impresion();
        } else {
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
        $tf = isset($_GET['form']) ? $_GET['form'] : 'c';
        ?>
        <div id="contenido_reporte" style="clear:both;">                    
            <script src="js/jquery-1.3.2.min.js"></script>
            <script>
                $(".det_cmp").live('click', function() {
                    var ruta = 'gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
                    var id = $(this).attr('data-id');
                    window.open(ruta + id, 'Comprobante', 'width=900, height=500, scrollbars=yes')
                });
                function imprimir_mayor() {
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
                $nombre_empresa = FUNCIONES::parametro('razon_social', $ges_id);
                $datos_empresa = FUNCIONES::parametro('direccion');
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
                        <strong><h3>VENTAS NETAS</h3></strong>
                        <br>
                        <?php
//                        if ($_POST['fecha_inicio'] <> "")
//                            echo '<strong>Del:</strong> ' . $_POST['fecha_inicio'];
//                        if ($_POST['fecha_fin'] <> "")
//                            echo ' <strong>Al:</strong> ' . $_POST['fecha_fin'] . '<br>';
                        ?>
                        <strong><?php echo FUNCIONES::atributo_bd("con_gestion", "ges_id=" . $_POST['gestion'], "ges_descripcion") ?></strong>
                        <br>
                        
                        <strong>Proyecto: </strong><?php echo FUNCIONES::atributo_bd("urbanizacion", "urb_id=" . $_POST['urb_id'], "urb_nombre") ?>
                        <br>
                        
                        <strong>Modalidad: </strong><?php echo ($_POST[modalidad] == 'no')?"Tradicional":"Multinivel";?>
                        <br>

                    </center>
                    </p>
                    </td>
                    <td width="40%"><div align="right"><img id="logo_micro" src="imagenes/micro.png" width="" /></div></td>
                    </tr> 
                </table>
                <style>
                    .venta_bruta{
                        font-weight: bold;
                    }
                    
                    .venta_neta{
                        font-weight: bold;
                    }
                    
                    #total_bruta{
                        font-weight: bold;
                    }
                    
                    #total_neta{
                        font-weight: bold;
                    }
                </style>
                <table width="100%" class="tablaReporte">
                    <thead>
                        <tr>
                            <th>Estructura Estado de Ventas</th>                            
                        <?php
                        if ($_POST[f_filtro] == 'r'){
                            $sql_pdos = "select * from con_periodo where pdo_ges_id='$_POST[gestion]'
                                    and pdo_eliminado='No' order by pdo_id asc";
                            $arr_pdo_ids = FUNCIONES::lista_bd_sql($sql_pdos);
                            

                            $length = count($arr_pdo_ids);

                            if ($length > 0) {
                                foreach ($arr_pdo_ids as $pdo) {
                                    ?>
                            <th data-pdo_id="<?php echo $pdo->pdo_id;?>"><?php echo strtoupper($pdo->pdo_descripcion);?></th>
                                    <?php
                                }
                            }
                        } else {
                            $fechas = explode(',', $_POST[periodo]);
                            
                            $sql_pdo = "select * from con_periodo where pdo_fecha_inicio='$fechas[0]'
                            and pdo_fecha_fin='$fechas[1]'
                            and pdo_eliminado='No' order by pdo_id asc";
                            
                            $pdo = FUNCIONES::objeto_bd_sql($sql_pdo);
                            ?>
                            <th data-pdo_id="<?php echo $pdo->pdo_id;?>"><?php echo strtoupper($pdo->pdo_descripcion);?></th>
                            <?php
                        }
                        ?>
                            <th>Totales</th>
                        </tr>    
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Ventas Brutas</b></td>
                            <?php
                        if ($_POST[f_filtro] == 'r'){
                            $sql_pdos = "select * from con_periodo where pdo_ges_id='$_POST[gestion]'
                                    and pdo_eliminado='No' order by pdo_id asc";
                            $arr_pdo_ids = FUNCIONES::lista_bd_sql($sql_pdos);
                            

                            $length = count($arr_pdo_ids);

                            if ($length > 0) {
                                foreach ($arr_pdo_ids as $pdo) {
                                    ?>
                            <td class="venta_bruta" data-vbr_id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-ingresos_pdo="ingr_pdo_<?php echo $pdo->pdo_id;?>" id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-pdo_id="<?php echo $pdo->pdo_id;?>"><b><?php echo "";?></b></td>
                                    <?php
                                }
                            }
                        } else {
                            $fechas = explode(',', $_POST[periodo]);
                            
                            $sql_pdo = "select * from con_periodo where pdo_fecha_inicio='$fechas[0]'
                            and pdo_fecha_fin='$fechas[1]'
                            and pdo_eliminado='No' order by pdo_id asc";
                            
                            $pdo = FUNCIONES::objeto_bd_sql($sql_pdo);
                            ?>
                            <td class="venta_bruta" data-vbr_id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-ingresos_pdo="ingr_pdo_<?php echo $pdo->pdo_id;?>" id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-pdo_id="<?php echo $pdo->pdo_id;?>"><b><?php echo "";?></b></td>
                            
                            <?php
                        }
                        ?>
                            <td id="total_bruta"><?php echo "";?></td>
                        </tr>
                        <?php
                        echo $this->ventas('contado');
                        echo $this->ventas('ci');
                        echo $this->ventas('cm');
                        echo $this->descuentos('descuento');
                        echo $this->descuentos('rebaja');
                        echo $this->descuentos('bonificacion');
                        echo $this->descuentos('promocion');
                        ?>   
                        <tr>
                            <td><b>Ventas Netas</b></td>
                            <?php
                        if ($_POST[f_filtro] == 'r'){
                            $sql_pdos = "select * from con_periodo where pdo_ges_id='$_POST[gestion]'
                                    and pdo_eliminado='No' order by pdo_id asc";
                            $arr_pdo_ids = FUNCIONES::lista_bd_sql($sql_pdos);
                            

                            $length = count($arr_pdo_ids);

                            if ($length > 0) {
                                foreach ($arr_pdo_ids as $pdo) {
                                    ?>
                            <td class="venta_neta" data-vbr_id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-costos_pdo="cost_pdo_<?php echo $pdo->pdo_id;?>" id="venta_neta_pdo_<?php echo $pdo->pdo_id;?>" data-pdo_id="<?php echo $pdo->pdo_id;?>"><b><?php echo "";?></b></td>
                                    <?php
                                }
                            }
                        } else {
                            $fechas = explode(',', $_POST[periodo]);
                            
                            $sql_pdo = "select * from con_periodo where pdo_fecha_inicio='$fechas[0]'
                            and pdo_fecha_fin='$fechas[1]'
                            and pdo_eliminado='No' order by pdo_id asc";
                            
                            $pdo = FUNCIONES::objeto_bd_sql($sql_pdo);
                            ?>
                            <td class="venta_neta" data-vbr_id="venta_bruta_pdo_<?php echo $pdo->pdo_id;?>" data-costos_pdo="cost_pdo_<?php echo $pdo->pdo_id;?>" id="venta_neta_pdo_<?php echo $pdo->pdo_id;?>" data-pdo_id="<?php echo $pdo->pdo_id;?>"><b><?php echo "";?></b></td>
                            
                            <?php
                        }
                        ?>
                            <td id="total_neta"><?php echo "";?></td>
                        </tr>
                    </tbody>
                </table>

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

//                var src = $(image).attr('src');
//                var image = $(report).find('img');
//                $(image).attr('src', url + '/' + src);
                $(report).find('#logo_micro').remove();
                $(report).find('.tablaReporte').attr('border','1');
                

                window.open('data:application/vnd.ms-excel; charset=utf-8, ' + encodeURIComponent($(report).html()));

                e.preventDefault();
            });


            function sumar_ventas_brutas(){
                $('.venta_bruta').each(function(){
                    var obj = $(this);
    //                console.log($(obj).attr('data-ingresos_pdo'));
                    var clase_ingreso = $(obj).attr('data-ingresos_pdo');
                    var ingresos = $('.' + clase_ingreso);
                    var vbr_id = $(obj).attr('data-vbr_id');
                    console.log('vbr_id => ' + vbr_id);
                    var total_bruta = 0;
                    for (var i = 0; i < ingresos.length; i++) {
                        var td = ingresos[i];
                        var monto = parseFloat($(td).text());
                        console.log(monto);                    
                        total_bruta += monto;
                    }

                    $('#'+ vbr_id).text(total_bruta.toFixed(2));
//                    $(obj).text(total_bruta.toFixed(2));
                });
            }
            
            function total_ventas_brutas(){
                var total_bruta = 0;
                $('.venta_bruta').each(function(){
                    var obj = $(this);    
                    total_bruta += parseFloat($(obj).text());
                                        
                });
                $('#total_bruta').text(total_bruta.toFixed(2));
            }
            
            function sumar_ventas_netas(){
                $('.venta_neta').each(function(){
                    var obj = $(this);
    //                console.log($(obj).attr('data-ingresos_pdo'));
                    var id = $(obj).attr('id');
                    var clase_costo = $(obj).attr('data-costos_pdo');
                    var costos = $('.' + clase_costo);
                    var vbr_id = $(obj).attr('data-vbr_id');
                    console.log('vbr_id => ' + vbr_id);
                    var total_bruta = parseFloat($('#'+ vbr_id).text());
                    for (var i = 0; i < costos.length; i++) {
                        var td = costos[i];
                        var monto = parseFloat($(td).text());
                        console.log(monto);                    
                        total_bruta -= monto;
                    }

//                    $('#'+ vbr_id).text(total_bruta.toFixed(2));
                    $('#' + id).text(total_bruta.toFixed(2));
                });
            }
            
            function total_ventas_netas(){
                var total_bruta = 0;
                $('.venta_neta').each(function(){
                    var obj = $(this);    
                    total_bruta += parseFloat($(obj).text());
                                        
                });
                $('#total_neta').text(total_bruta.toFixed(2));
            }
            
            sumar_ventas_brutas();
            total_ventas_brutas();
            sumar_ventas_netas();
            total_ventas_netas();
        </script>


        <?php
    }

    private function url_actual() {
        $server = $_SERVER;
        $nombre_proyecto = $server['REQUEST_URI'];
        $nombre_proyecto = explode('/', $nombre_proyecto);
        $nombre_proyecto = $nombre_proyecto[1];
        return 'http://' . $server['SERVER_NAME'] . '/' . $nombre_proyecto;
    }

    function fila_periodos() {
        
    }

    function ventas($tipo) {
        ob_start();
        ?>
        <tr>
        <?php
        if ($tipo == 'contado') {
            ?>        
        <td>Contado</td>
            <?php
        } elseif ($tipo == 'ci') {
                ?>        
        <td>Cuotas Iniciales</td>
            <?php
        } elseif ($tipo == 'cm') {
                ?>        
        <td>Cuotas Pagadas</td>
            <?php
        }
        
        $total = 0;

        if ($_POST[f_filtro] == 'r') {
            $sql_pdos = "select group_concat(pdo_id)as campo from con_periodo where pdo_ges_id='$_POST[gestion]'
                    and pdo_eliminado='No' order by pdo_id asc";
            $s_pdo_ids = FUNCIONES::atributo_bd_sql($sql_pdos);
            $arr_pdo_ids = explode(',', $s_pdo_ids);

            $length = count($arr_pdo_ids);

            if ($length > 0) {

                if ($tipo == 'contado') {
                                        
                    foreach ($arr_pdo_ids as $pdo_id) {
                        $sql = "select pdo_id,pdo_descripcion,sum(ven_valor)as valor from con_periodo 
                        left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                        where ven_estado='Pagado'
                        and ven_tipo='Contado' and ven_urb_id='$_POST[urb_id]'
                        and ven_multinivel='$_POST[modalidad]'
                        and pdo_id = '$pdo_id'
                        group by pdo_id";
                        $fila = FUNCIONES::objeto_bd_sql($sql);
                        
                        $valor = 0.00;
                        if ($fila) {
                            $valor = $fila->valor;
                        }
                        $total += $valor;
                        ?>
                        <td class="ingr_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }                                            
                                            
                } else if ($tipo == 'ci') {

                    foreach ($arr_pdo_ids as $pdo_id) {
                        $sql = "select pdo_id,pdo_descripcion,sum(ven_res_anticipo)as valor from con_periodo 
                        left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                        where ven_estado in('Pendiente','Pagado','Retenido')
                        and ven_tipo='Credito' and ven_urb_id='$_POST[urb_id]'
                        and ven_multinivel='$_POST[modalidad]'
                        and pdo_id = '$pdo_id'
                        group by pdo_id";
                        $fila = FUNCIONES::objeto_bd_sql($sql);
                        
                        $valor = 0.00;
                        if ($fila) {
                            $valor = $fila->valor;
                        }
                        $total += $valor;
                        ?>
                        <td class="ingr_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }                                            

                } else if ($tipo == 'cm') {
                    
                    foreach ($arr_pdo_ids as $pdo_id) {
                        
                        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_id='$pdo_id'");
                        $sql_cm = "SELECT 
                            sum(vpag_monto) as valor
                            FROM 
                              venta_pago                              
                              inner join venta on (ven_id=vpag_ven_id)	                              
                            where vpag_monto>0 and vpag_estado='Activo' 
                            and ven_estado in('Pendiente','Pagado','Retenido')
                            and ven_multinivel='$_POST[modalidad]'
                            and vpag_fecha_pago >= '$periodo->pdo_fecha_inicio' 
                            and vpag_fecha_pago <='$periodo->pdo_fecha_fin'
                            and ven_urb_id='$_POST[urb_id]'";
                        $fila = FUNCIONES::objeto_bd_sql($sql_cm);
                        
                        
                        $valor = 0.00;
                        if ($fila) {
                            $valor = $fila->valor;
                        }
                        $total += $valor;
                        ?>
                        <td class="ingr_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }
                                        
                }
            }
        } else {
            
            $fechas = explode(',', $_POST[periodo]);
            $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_fecha_inicio='$fechas[0]'
            and pdo_fecha_fin='$fechas[1]'");
            
            if ($tipo == 'contado') {            
                            
                $sql = "select pdo_id,pdo_descripcion,sum(ven_valor)as valor from con_periodo 
                left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                where ven_estado='Pagado'
                and ven_tipo='Contado' and ven_urb_id='$_POST[urb_id]'
                and ven_multinivel='$_POST[modalidad]'
                and pdo_id='$periodo->pdo_id'
                group by pdo_id";

    //            echo $sql;

                $fila = FUNCIONES::objeto_bd_sql($sql);

                $valor = 0.00;
                if ($fila) {
                    $valor = $fila->valor;
                }

                $total += $valor;
                ?>
                <td class="ingr_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            
            
            } else if ($tipo == 'ci') {
                
                $sql = "select pdo_id,pdo_descripcion,sum(ven_res_anticipo)as valor from con_periodo 
                left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                where ven_estado in('Pendiente','Pagado','Retenido')
                and ven_tipo='Credito' and ven_urb_id='$_POST[urb_id]'
                and ven_multinivel='$_POST[modalidad]'
                and pdo_id='$periodo->pdo_id'
                group by pdo_id";

//                echo $sql;

                $fila = FUNCIONES::objeto_bd_sql($sql);

                $valor = 0.00;
                if ($fila) {
                    $valor = $fila->valor;
                }

                $total += $valor;
                ?>
                <td class="ingr_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            } else if ($tipo == 'cm') {
                
                $sql_cm = "SELECT 
                    sum(vpag_monto) as valor
                    FROM 
                      venta_pago                              
                      inner join venta on (ven_id=vpag_ven_id)	                              
                    where vpag_monto>0 and vpag_estado='Activo' 
                    and ven_estado in('Pendiente','Pagado','Retenido')
                    and ven_multinivel='$_POST[modalidad]'
                    and vpag_fecha_pago >= '$periodo->pdo_fecha_inicio' 
                    and vpag_fecha_pago <='$periodo->pdo_fecha_fin'
                    and ven_urb_id='$_POST[urb_id]'";
                $fila = FUNCIONES::objeto_bd_sql($sql_cm);

                $valor = 0.00;
                if ($fila) {
                    $valor = $fila->valor;
                }
                $total += $valor;
                ?>
                <td class="ingr_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>    
                    <?php
            }
        }
        ?>
                <td><?php echo number_format($total, 2, '.', '');?></td>
                </tr>
        <?php
        $respuesta = ob_get_contents();
        ob_end_clean();
        return $respuesta;
    }
    
    function descuentos($tipo){
        ob_start();
        ?>
        <tr>
        <?php
        if ($tipo == 'descuento') {
            ?>        
            <td style="text-align: right">Descuentos</td>
            <?php
        } elseif ($tipo == 'rebaja') {
                ?>        
        <td style="text-align: right">Rebajas</td>
            <?php
        } elseif ($tipo == 'bonificacion') {
                ?>        
        <td style="text-align: right">Bonificacion</td>
            <?php
        } elseif ($tipo == 'promocion') {
                ?>        
        <td style="text-align: right">Promocion</td>
            <?php
        }
        
        $total = 0;

        if ($_POST[f_filtro] == 'r') {
            
            $sql_pdos = "select group_concat(pdo_id)as campo from con_periodo where pdo_ges_id='$_POST[gestion]'
                    and pdo_eliminado='No' order by pdo_id asc";
            $s_pdo_ids = FUNCIONES::atributo_bd_sql($sql_pdos);
            $arr_pdo_ids = explode(',', $s_pdo_ids);

            $length = count($arr_pdo_ids);

            if ($length > 0) {

                if ($tipo == 'descuento') {
                                        
                    foreach ($arr_pdo_ids as $pdo_id) {
                        $sql = "select pdo_id,pdo_descripcion,sum(ven_decuento)as valor from con_periodo 
                        left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                        where ven_estado in ('Pendiente','Pagado','Retenido')
                        and ven_urb_id='$_POST[urb_id]'
                        and ven_multinivel='$_POST[modalidad]'
                        and pdo_id = '$pdo_id'
                        group by pdo_id";
                        $fila = FUNCIONES::objeto_bd_sql($sql);
                        
                        $valor = 0.00;
                        if ($fila) {
                            $valor = $fila->valor;
                        }
                        $total += $valor;
                        ?>
                        <td style="text-align: right" class="cost_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }                                            
                                            
                } elseif ($tipo == 'rebaja') {
                    foreach ($arr_pdo_ids as $pdo_id) {
                                                
                        $valor = 0.00;                        
                        $total += $valor;
                        ?>
                        <td style="text-align: right" class="cost_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }
                } elseif ($tipo == 'bonificacion') {
                    foreach ($arr_pdo_ids as $pdo_id) {
                                                
                        $valor = 0.00;                        
                        $total += $valor;
                        ?>
                        <td style="text-align: right" class="cost_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }
                } elseif ($tipo == 'promocion') {
                    foreach ($arr_pdo_ids as $pdo_id) {
                                                
                        $valor = 0.00;                        
                        $total += $valor;
                        ?>
                        <td style="text-align: right" class="cost_pdo_<?php echo $pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
                            <?php
                    }
                }
            }
        } else {
            
            $fechas = explode(',', $_POST[periodo]);
            $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_fecha_inicio='$fechas[0]'
            and pdo_fecha_fin='$fechas[1]'");
            
            if ($tipo == 'descuento') {            
                            
                $sql = "select pdo_id,pdo_descripcion,sum(ven_decuento)as valor from con_periodo 
                left join venta on (ven_fecha>=pdo_fecha_inicio and ven_fecha<=pdo_fecha_fin)                    
                where ven_estado in ('Pendiente','Pagado','Retenido')
                and ven_urb_id='$_POST[urb_id]'
                and ven_multinivel='$_POST[modalidad]'
                and pdo_id='$periodo->pdo_id'
                group by pdo_id";

    //            echo $sql;

                $fila = FUNCIONES::objeto_bd_sql($sql);

                $valor = 0.00;
                if ($fila) {
                    $valor = $fila->valor;
                }

                $total += $valor;
                ?>
                <td style="text-align: right" class="cost_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            
            
            } elseif ($tipo == 'rebaja') {
                $valor = 0.00;                
                $total += $valor;
                ?>
                <td style="text-align: right" class="cost_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            } elseif ($tipo == 'bonificacion') {
                $valor = 0.00;                
                $total += $valor;
                ?>
                <td style="text-align: right" class="cost_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            } elseif ($tipo == 'promocion') {
                $valor = 0.00;                
                $total += $valor;
                ?>
                <td style="text-align: right" class="cost_pdo_<?php echo $periodo->pdo_id;?>"><?php echo number_format($valor, 2, '.', ''); ?></td>
            <?php
            }
        }
        ?>
                <td style="text-align: right"><?php echo number_format($total, 2, '.', '');?></td>
                </tr>
        <?php
        $respuesta = ob_get_contents();
        
        ob_end_clean();
        return $respuesta;
    }

}
?>