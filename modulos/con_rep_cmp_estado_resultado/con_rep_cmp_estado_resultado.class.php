<?php

class CON_REP_CMP_ESTADO_RESULTADO extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_REP_CMP_ESTADO_RESULTADO() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_cmp_estado_resultado';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('ESTADO DE RESULTADOS COMPARADOS');
        $this->usu = new USUARIO;
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
            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->						
            <script>
                $("#frm_sentencia").live('submit', function() {
                    return true;
                });
                function mostrar_error(input, mensaje) {
                    $(input).after('<span class="error">' + mensaje + '</span>');
                }
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
                                        $fun->combo_data("select ges_id as id,ges_descripcion as nombre, concat(ges_fecha_ini,',',ges_fecha_fin) as fechas from con_gestion where ges_eliminado='No' order by ges_fecha_ini desc", "fechas", $_SESSION['ges_id']);
                                        ?>
                                    </select>
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                                <div id="CajaInput">
                                    <select id="cde_une_id" name="cde_une_id">
                                        <option value="">Seleccione</option>
                                        <?php $fun = new FUNCIONES(); ?>
                                        <?php $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[cde_une_id]); ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Centro de Costo</div>
                                <div id="CajaInput">
                                    <input name="id_centroc" id="id_centroc" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                    <input name="nombre_centroc" id="nombre_centroc"  type="text" class="caja_texto" value="<?php ?>" size="25">
                                </div>							   							   								
                            </div>
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

                if ($_POST['formu'] == 'ok'){
                    
                    $this->mostrar_reporte();
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
                $extra1.=" <a href=javascript:imprimir_estado();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                $extra2 = "'</center></body></html>'";

                $myday = setear_fecha(strtotime(date('Y-m-d')));

                echo '	<table align=right border=0><tr>
                        <td>
                            <a href="javascript:document.formulario.submit();">
                                <img src="images/actualizar.png" width="20" title="ACTUALIZAR"/>
                            <a/>
                        </td>
                        <td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                        c.document.write(' . $extra1 . ');
                        var dato = document.getElementById(' . $pagina . ').innerHTML;
                        c.document.write(dato);
                        c.document.write(' . $extra2 . '); c.document.close();
                        ">
                      <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                      </a></td><td><a href="javascript:void(0);" id="importar_excel"><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL" ></a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '\';"></td></tr></table><br><br>
		';
            }

            function mostrar_reporte() {
                $this->barra_de_impresion();
                $parametros = 'gestor.php?mod=con_rep_libro_mayor&tform=popup&mh=' . $_POST['moneda_hecho'] . '&mr=' . $_POST['moneda_reporte'] . '&ges=' . $_POST['gestion'] . "&une_id=$_POST[cde_une_id]";
                //&fi='.$_POST['fecha_inicio'].'&ff='.$_POST['fecha_fin'].'
//                FUNCIONES::print_pre($_POST);
//                return;
                ?>
                <div id="contenido_reporte" style="clear:both;">
                    <script src="js/jquery-1.3.2.min.js"></script>
                    <script>
                        $(".det_estado").live('click', function() {
                            var ruta = $("#paramentros").val();
                            var id = $(this).attr('data-id');
                            var fi = $(this).attr('data-fi');
                            var ff = $(this).attr('data-ff');
                            ruta=ruta+ '&id=' + id+'&fi='+fi+'&ff='+ff;
                            window.open(ruta, 'Libro Mayor', 'width=900, height=500, scrollbars=yes')
                        });

                        function imprimir_estado() {
                            $(".det_estado").hide();
                            window.print();
                            $(".det_estado").show();
                        }
                        
                        
                    </script>
                    <input type="hidden" id="paramentros" value="<?php echo $parametros ?>"/>
                    <div style="text-align: left;">
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
                                <strong><h3>ESTADO DE RESULTADOS COMPARADOS</h3></strong>
                                <strong><?php echo $_POST[cde_une_id] ? FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$_POST[cde_une_id]'") : ''; ?></strong>
                                <br><br>
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
                        $parametro = FUNCIONES::parametro("formula_est_res", $ges_id);
                        $formula = json_decode($parametro);
                        $formula_val = array();
                        
                        $list_periodos= FUNCIONES::lista_bd_sql("select * from con_periodo where pdo_ges_id='$ges_id' order by pdo_fecha_inicio asc");
                        $periodos=array();
                        foreach ($list_periodos as $peri) {
                            $periodos[$peri->pdo_id]=array('fi'=>$peri->pdo_fecha_inicio, 'ff'=>$peri->pdo_fecha_fin,'nombre'=>$peri->pdo_descripcion);
                        }
                        $periodos[0]=array('fi'=>$list_periodos[0]->pdo_fecha_inicio, 'ff'=>$list_periodos[count($list_periodos)-1]->pdo_fecha_fin,'nombre'=>'TOTAL');
//                        _PRINT::pre($periodos);
                        $GLOBALS['periodos']=$periodos;
                        
                        for ($i = 0; $i < count($formula); $i++) {
                            $form = $formula[$i];
                            $sql = "SELECT * FROM con_cuenta WHERE cue_eliminado='No' and cue_ges_id='$ges_id' and cue_codigo='$form->cuenta';";
                            $cuenta = FUNCIONES::objeto_bd_sql($sql);
                            $_cu = new stdClass();
                            $_cu->id = $cuenta->cue_id;
                            $_cu->codigo = $cuenta->cue_codigo;
                            $_cu->descripcion = $cuenta->cue_descripcion;
                            $_cu->level = $cuenta->cue_tree_level;
                            $_cu->tipo = $cuenta->cue_tipo;
                            $calculo = $this->obtener_sumas_cuenta($cuenta->cue_id);
                            $_cu->tmonto = $calculo->tmonto;
                            $_cu->detalles = $calculo->detalles;
                            $campo = new stdClass();

                            $campo->cuenta = $_cu;
                            $campo->signo = $form->signo;
                            $campo->op = $form->op;

                            $formula_val[] = $campo;
                        }
//                        _PRINT::pre($formula_val);
//                        $this->mostrar_tabla_resultados($cu_ingreso,$cu_costo,$cu_gasto);
                        $this->mostrar_tabla_resultados($formula_val);
                        ?>
                    </div>
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
//                        var imgs = $(report).find(".det_estado").remove();
                        var url = '<?php echo $this->url_actual() ?>';
                        var image = $(report).find('img');
                        var src = $(image).attr('src');
                        $(image).attr('src', url + '/' + src);

                        window.open('data:application/vnd.ms-excel; charset=utf-8, ' + encodeURIComponent($(report).html()));

                        e.preventDefault();
                    });
                </script>
                <form name="formulario" action="gestor.php?mod=<?php echo $this->modulo; ?>" method="POST">                            
                    <input type="hidden" name="formu" value="ok">
                    <input type="hidden" name="fecha_inicio" value="<?php echo $_POST['fecha_inicio']; ?>">
                    <input type="hidden" name="fecha_fin" value="<?php echo $_POST['fecha_fin']; ?>">
                    <input type="hidden" name="moneda_hecho" value="<?php echo $_POST['moneda_hecho']; ?>">
                    <input type="hidden" name="moneda_reporte" value="<?php echo $_POST['moneda_reporte']; ?>">                            
                    <input type="hidden" name="gestion" value="<?php echo $_POST['gestion']; ?>">                            
                    <input type="hidden" name="lista_cuentas" value="<?php echo $_POST['lista_cuentas']; ?>">                            
                </form>
        <?php
    }

//            function mostrar_tabla_resultados($cu_ingreso,$cu_costo,$cu_gasto){
    function mostrar_tabla_resultados($formula_val) {
        $ges_id = $_POST["gestion"];
        $sql = "select max(cue_tree_level) as max from con_cuenta where cue_ges_id=$ges_id;";
        $conect = new ADO();
        $conect->ejecutar($sql);
        $objeto = $conect->get_objeto();
        $max_level = $objeto->max;
        $periodos=$GLOBALS[periodos];
        ?>
                <style>
                    .tablaReporte thead tr th {background-color: #dadada;border: 1px solid #000}
                    .det_estado{color: #000;text-decoration: none;}
                    .det_estado:hover{color: #0000ff;font-weight: bold}
                </style>
                <table  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>                        
                        <tr>
                            <th rowspan="2"><b>CODIGO</b></th>
                            <th rowspan="2"><b>CUENTAS</b></th>
                            <th colspan="<?php echo $max_level *  count($periodos) ?>"><b>RESULTADO</b></th>
                        </tr>
                        <tr>
                            <?php foreach($periodos as $peri){?>
                                <th colspan="<?php echo $max_level ?>"><b><?php echo $peri[nombre];?></b></th>
                            <?php }?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formula_val as $campo) { ?>
                            <?php $this->movimiento($campo->cuenta, $max_level, $campo->op) ?>
                            <tr style="border: 1px solid #000"><td colspan="<?php echo 2 + $max_level *  count($periodos) ?>"></td></tr>                        
                        <?php } ?>
                    </tbody>
                        <?php // $this->totales($cu_ingreso,$cu_costo,$cu_gasto,$max_level); ?>
                    <?php $this->totales($formula_val, $max_level); ?>
                </table>
                    <?php
                }

                function espacio($n) {
//                    echo $n.',';
                    $sp = "";
                    for ($i = 0; $i < $n; $i++) {
                        $sp.="&nbsp;";
                    }
                    return $sp;
                }

                function obtener_sumas_cuenta($id_cuenta) {
                    $cuentas = new ADO();
                    $sql = "select cue_id, cue_codigo, cue_descripcion, cue_tipo, cue_padre_id, cue_tree_level 
                      from con_cuenta where cue_padre_id=$id_cuenta order by cue_codigo;";
                    $cuentas->ejecutar($sql);
//                    $monto = 0;
                    $amonto=array();
                    $detalles = array();
                    for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
                        $cuenta = $cuentas->get_objeto();
                        $cuenta_m = new stdClass();
                        $cuenta_m->id = $cuenta->cue_id;
                        $cuenta_m->codigo = $cuenta->cue_codigo;
                        $cuenta_m->descripcion = $cuenta->cue_descripcion;
                        $cuenta_m->level = $cuenta->cue_tree_level;
                        $cuenta_m->tipo = $cuenta->cue_tipo;
                        $cuenta_m->tmonto=array();
                        
                        if ($cuenta->cue_tipo == "Movimiento") {
                            $periodos=$GLOBALS[periodos];
                            foreach ($periodos as $id => $obj) {
                                if($id>0){
                                    $_POST['fecha_inicio']=  FUNCIONES::get_fecha_latina($obj[fi]);
                                    $_POST['fecha_fin']=FUNCIONES::get_fecha_latina($obj[ff]);
                                    $suma = $this->total_debe_haber_cuenta($cuenta->cue_id);
                                    $saldo = $suma->tdebe - $suma->thaber;
                                    $cuenta_m->tmonto[$id] = $saldo;
                                    $amonto[$id]+=$saldo;
                                }
//                                break;
                            }
                            $amonto[0]+=array_sum($cuenta_m->tmonto);
                            $cuenta_m->tmonto[0]=array_sum($cuenta_m->tmonto);
                            
                            $cuenta_m->detalles = array();
//                            $monto+=$saldo;
                            if ($i == $cuentas->get_num_registros() - 1) {
                                $cuenta_m->ultimo = true;
                            }
                        } else {
                            $calculo = $this->obtener_sumas_cuenta($cuenta->cue_id);
                            $cuenta_m->tmonto = $calculo->tmonto;
                            $cuenta_m->detalles = $calculo->detalles;
                            
//                            $monto+=$calculo->tmonto;
                            foreach ($calculo->tmonto as $id => $_monto) {
                                $amonto[$id]+=$_monto;
                            }
                            
                        }
                        
                        $detalles[] = $cuenta_m;
                        $cuentas->siguiente();
                    }
                    
                    $resp = new stdClass();
//                    $resp->tmonto = $monto;
                    $resp->tmonto = $amonto;
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
                    $fcentro_c = $_POST['id_centroc'] != '' ? ' and cde_cco_id=' . $_POST['id_centroc'] : '';

                    /*                     * *** INI - IDS UNIDADES DE NEGOCIO DEL USUARIO **** */
//                    $ids_une_id = '';
//                    $fun = NEW FUNCIONES;
//                    $array = $fun->lista_bd_sql("select * from usuario_unidadnegocio where uune_usu_id='" . $this->usu->get_id() . "'");
//
//                    $num = count($array);
//                    $i = 0;
//                    foreach ($array as $product) {
//                        if ($i == 0)
//                            $ids_une_id.=$product->uune_une_id;
//                        if ($i > 0)
//                            $ids_une_id.=',' . $product->uune_une_id;
//
//                        $i++;
//                    }
                    /*                     * *** FIN - IDS UNIDADES DE NEGOCIO DEL USUARIO **** */

                    $and_une_id = "";
                    if ($_POST[cde_une_id]) {
                        $and_une_id = " and cde_une_id='$_POST[cde_une_id]'";
                    } 
//                    else {
//                        if ($ids_une_id == '')
//                            $ids_une_id = '0';
//                        $and_une_id = " and cde_une_id in ($ids_une_id)";
//                    }


                    $sql = "
                        select 
                            cde_valor
                        from
                            con_comprobante c, con_comprobante_detalle cd
                        where
                            c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and cd.cde_cue_id=$id_cuenta $filtro $fcentro_c
                            and c.cmp_ges_id=$ges_id and cde_mon_id='" . $_POST['moneda_reporte'] . "' $and_une_id
                        ";
//                    echo $sql." <br>";
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

                </td>							
                <td>

                </td>							
                </tr>
        <?php
    }

    function movimiento($cuenta, $max_level, $signo = 1) {
        $sw = true;
        if (!isset($_POST['saldo_cero']) || !$_POST['saldo_cero']) {
            $suma_tmontos=  array_sum($cuenta->tmonto);
            if ($suma_tmontos == 0) {
                $sw = false;
            }
        }
        if ($sw) {
            ?>
                <tr>
                    <td style="border-right: 1px solid #000;" > <?php echo $cuenta->codigo; ?> </td>                    
                    <td style="border-right: 1px solid #000; <?php if ($cuenta->tipo != "Movimiento") echo 'font-weight: bold' ?>" >
                        <div style="float: left"><?php echo $this->espacio(($cuenta->level - 1) * 2) . $cuenta->descripcion; ?></div>                    
                    </td>
                    <?php $periodos=$GLOBALS[periodos];?>
                    
                    <?php foreach ($periodos as $per_id => $obj) { ?>
                        <?php for ($i = 1; $i <= $max_level; $i++) { ?>
                            <? if ($i == ($max_level + 1) - $cuenta->level) { ?>
                                <td style="border-left: 1px solid #d6d8d7; <?php echo $i==$max_level?'border-right: 1px solid #000;':'';?><?php echo $cuenta->tipo != "Movimiento"?'font-weight: bold;':'border-bottom: 1px solid #000;';  ?>" >
                                    <?php if ($cuenta->tipo == "Movimiento") { ?> 
                                        <a href="javascript:void(0);" class="det_estado" data-id="<?php echo $cuenta->id; ?>" data-fi="<?php echo FUNCIONES::get_fecha_latina($obj[fi]);?>" data-ff="<?php echo FUNCIONES::get_fecha_latina($obj[ff]);?>">
                                            <?php echo number_format($cuenta->tmonto[$per_id] * $signo, 2); ?>
                                        </a>
                                    <?php }else{ ?>
                                        <?php echo number_format($cuenta->tmonto[$per_id] * $signo, 2); ?>
                                    <?php } ?>
                                    <?php if ($cuenta->tipo == "Movimiento") { ?> 
                                    <!--<a style="float: left; padding-right: 3px" href="javascript:void(0);" class="det_estado" data-id="<?php echo $cuenta->id; ?>"><img src="images/b_browse.png" width="14px" /></a>--> 
                                    <?php } ?>
                                    
                                </td>
                            <?php } else { ?>
                            <td style="border-left: 1px solid #d6d8d7; <?php echo $i==$max_level?'border-right: 1px solid #000':'';?>"> </td>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </tr>
            <?php }
            $detalles = $cuenta->detalles;
            for ($i = 0; $i < count($detalles); $i++) {
                $cuenta_h = $detalles[$i];
                $this->movimiento($cuenta_h, $max_level, $signo);
            }
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

    function totales($formula_val, $max_level) {
        ?>
                <tfoot>
                    <tr>
                        <td colspan="2" align="right">
                            T O T A L
                        </td>
                        
                        <?php
                        $periodos=$GLOBALS[periodos];
                        foreach ($periodos as $per_id => $obj) {
                            $total=0;
                            foreach ($formula_val as $campo) {
                                $cuenta = $campo->cuenta;
                                $total+=($cuenta->tmonto[$per_id] * $campo->op) * $campo->signo;
                            }
                            ?>
                            <td colspan="<?php echo $max_level;?>">
                                <?php echo number_format($total, 2);?>
                            </td>
                        <?php } ?>
                        
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
    private function url_actual() {
                $server = $_SERVER;
                $nombre_proyecto = $server['REQUEST_URI'];
                $nombre_proyecto = explode('/', $nombre_proyecto);
                $nombre_proyecto = $nombre_proyecto[1];
                return 'http://' . $server['SERVER_NAME'] . '/' . $nombre_proyecto;
            }

}
?>