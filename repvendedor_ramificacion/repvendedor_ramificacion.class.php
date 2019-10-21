<?php

class REPVENDEDOR_RAMIFICACION extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPVENDEDOR_RAMIFICACION() {
        //permisos
        $this->ele_id = 170;

        $this->busqueda();

        //fin permisos

        $this->coneccion = new ADO();

        $this->link = 'repvendedor_ramificacion.gestor.php';

        $this->modulo = 'repvendedor_ramificacion';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('VENDEDORES RAMIFICACI&Oacute;N');
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {
        $this->formulario->dibujar_cabecera();

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
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $this->link;?>?mod=<?php echo $this->modulo ?>" method="POST" enctype="multipart/form-data">
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">

                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Nombre Vendedor</div>
                                <div id="CajaInput">

                                    <input name="interno" id="interno" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['interno'] ?>" size="2">
                                    <input name="int_nombre_persona" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onClick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                </div>
                            </div>
                            <!--Fin-->

                            <!--Inicio -->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta">Nivel</div>
                                <div id="CajaInput">
                                    <input type="text" onKeyPress="return ValidarNumero(event);" name="nivel" id="nivel" value="5">
                                </div>
                            </div>
                            <!--Fin-->
                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="submit" class="boton" name="" value="Generar Reporte">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <script>
                                        $('#nivel').change(function() {
                                            var value = $(this).val();
                                            $(this).val(parseInt(value));
                                        });
                                        function ValidarNumero(e) {
                                            evt = e ? e : event;
                                            tcl = (window.Event) ? evt.which : evt.keyCode;
                                            if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46)) {
                                                return false;
                                            }
                                            return true;
                                        }
                                        var options1 = {
                                            script: "sueltos/suggest_persona_vendedor.php?json=true&",
                                            varname: "input",
                                            minchars: 1,
                                            timeout: 10000,
                                            noresults: "No se encontro ninguna persona, vendedor",
                                            json: true,
                                            callback: function(obj) {
                                                document.getElementById('interno').value = obj.id;
                                            }
                                        };
                                        var as_json1 = new _bsn.AutoSuggest('int_nombre_persona', options1);
                    </script>
                    <?php
                }

                if ($_POST['formu'] == 'ok')
                    $this->mostrar_reporte();
            }

            function limpiar_cadena($cadena){
                $cadena= str_replace('Ã¡', 'a', $cadena);
                $cadena= str_replace('Ã©', 'e', $cadena);
                $cadena= str_replace('Ã­', 'i', $cadena);
                $cadena= str_replace('Ã³', 'o', $cadena);
                $cadena= str_replace('Ãº', 'u', $cadena);
                $cadena= str_replace('Ã�', 'A', $cadena);
                $cadena= str_replace('Ã‰', 'E', $cadena);
                $cadena= str_replace('Ã�', 'I', $cadena);
                $cadena= str_replace('Ã“', 'O', $cadena);
                $cadena= str_replace('Ãš', 'U', $cadena);
                $cadena= str_replace('Ã±', 'n', $cadena);
                $cadena= str_replace('Ã‘', 'N', $cadena);
                return $cadena;
            }

            private function get_comisiones($interno_id, $vendedor_id) {
            	$sql_comision = "select com_porcentaje from comision
					inner join venta on (ven_id=com_ven_id)
					where ven_int_id='$interno_id' and com_vdo_id ='$vendedor_id' and com_estado !='Anulado'";
            	$conex = new ADO();
//             	echo $sql_comision . '<br>';
            	$conex->ejecutar($sql_comision);

            	$registros = $conex->get_num_registros();
            	$datos = array();
            	for ($i = 0; $i < $registros; $i++) {
            		$comision = $conex->get_objeto();
            		$datos[] = ($comision->com_porcentaje * 1);
            		$conex->siguiente();
            	}

            	return implode('%, ', $datos);
            }

            function tree_vendedores($padre_id,$nivel_limite=0,$nivel=0) {

                if($padre_id>0){

                    $vendedor=  FUNCIONES::objeto_bd_sql("select int_nombre, int_apellido, int_id,vendedor.* from vendedor,interno where vdo_id='$padre_id' and vdo_int_id=int_id");
                    $nombre=  explode(' ', $vendedor->int_nombre);
                    $apellido=  explode(' ', $vendedor->int_apellido);

//                     $porcentaje= FUNCIONES::atributo_bd_sql("select com_porcentaje as campo from comision where com_ven_id='$vendedor->vdo_venta_inicial' and com_vdo_id='$_POST[vdo_analisis]'");
                    $porcentaje = $this->get_comisiones($vendedor->int_id, $_POST[vdo_analisis]);
                    $txt_porc='';
                    if($porcentaje){
                        $txt_porc=" ($porcentaje%)";
                    }
//                    $name= $this->limpiar_cadena($nombre[0]).' '.$this->limpiar_cadena($apellido[0]) . $txt_porc;
                    $name= $this->limpiar_cadena($vendedor->int_nombre ." " . $vendedor->int_apellido) . $vendedor->vdo_id;
                }else{
                    $vendedor=null;
                    $name='Raiz';
                }
                $tree=new stdClass();
                $tree->name= utf8_encode($name);
//                $ob_json="{";
//                $ob_json.="\"name\":\"$name\",";
                $hijos=  FUNCIONES::objetos_bd_sql("select * from vendedor where vdo_vendedor_id='$padre_id'");

                if($hijos->get_num_registros()>0){

//                    $ob_json.='"children":[';
                    $children=array();
                    for ($i = 0; $i < $hijos->get_num_registros(); $i++) {
                        $_vendedor=$hijos->get_objeto();

                        if($nivel_limite == 0 || $nivel < $nivel_limite){
                            $children[]=$this->tree_vendedores($_vendedor->vdo_id,$nivel_limite,$nivel+1);
                        }
                        $hijos->siguiente();
                    }
                    $tree->children=$children;
//                    $ob_json.=']}';
                }else{

                    $tree->size=0;
                }
                return $tree;

            }

            function altura($nodo) {
                if (count($nodo->children)== 0) {
                    return 1;
                }else {
                    $hm = 0;
                    for ($i = 0; $i < count($nodo->children); $i++) {
                        $h = $this->altura($nodo->children[$i]) + 1;
                        if ($h > $hm) {
                            $hm = $h;
                        }
                    }
                    return $hm;
                }
            }
            function ancho($tree) {
                $alturaNodos = $this->altura($tree);
                $hijos_max = 0;
                for ($i = 0; $i < $alturaNodos; $i++) {
                    $hijosnivel = $this->obtenerHijosNivel($tree, 0, $i);

                    if ($hijosnivel > $hijos_max) {
                        $hijos_max = $hijosnivel;
                    }
                }
                return $hijos_max;
            }
            function obtenerHijosNivel($nodos, $na, $nc) {
                if ($na === $nc)
                    return 1;
                else {
                    $contador = 0;
                    for ($i = 0; $i < count($nodos->children); $i++) {
                        $contador += $this->obtenerHijosNivel($nodos->children[$i], $na + 1, $nc);
                    }
                    return $contador;
                }
            }

            function mostrar_reporte() {
//                echo '<pre>';
//                print_r($_POST);
//                echo '</pre>';
                if($_POST[interno]){
                    $vendedor_id=$_POST[interno];
                }else{
                    $vendedor_id=0;
                }
                $_POST[vdo_analisis]=$_POST[interno];
                $tree_vendedores=  $this->tree_vendedores($vendedor_id,$_POST[nivel]*1);

                $altura=  $this->altura($tree_vendedores);
                $ancho= $this->ancho($tree_vendedores);
//                echo "altura : $altura -- ancho : $ancho <br>";
                $altura=  $altura * 250;
                $ancho= $ancho*40;
//                echo "altura : $altura -- ancho : $ancho<br>";
//                echo '<pre>';
//                print_r($tree_vendedores);
//                echo '</pre>';
//                return;
                $json_vendedores= json_encode($tree_vendedores);
// 		exit;
                ////
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
                ////
                ?>
                <?php echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repdeudasinternos\';"></td></tr></table><br><br>
				'; ?>


                <!--<link rel="stylesheet" href="js/jsplumb/css/jsplumb.css">-->
                <!-- JS -->
                <script src="js/jsplumb/external/jquery-1.9.0-min.js"></script>
                <div align="center" id="contenido_reporte" style="clear:both;">
                    <style>

                        .node {
                            cursor: pointer;
                        }

                        .node circle {
                            fill: #fff;
                            stroke: steelblue;
                            stroke-width: 1.5px;
                        }

                        .node text {
                            font: 10px sans-serif;;
                        }

                        .link {
                            fill: none;
                            stroke: #ccc;
                            stroke-width: 1.5px;
                        }

                    </style>
                </div>
                <script src="js/d3.v3.min.js"></script>
                <script>

//                    var margin = {top: 20, right: 120, bottom: 20, left: 120},
                    var margin = {top: 10, right: 120, bottom: 10, left: 120},
                    width = <?php echo $altura;?> - margin.right - margin.left;
                    height = <?php echo $ancho;?> - margin.top - margin.bottom;

                    var i = 0,
                            duration = 750,
                            root;

                    var tree = d3.layout.tree()
                            .size([height, width]);

                    var diagonal = d3.svg.diagonal()
                            .projection(function(d) {
                        return [d.y, d.x];
                    });

                    var svg = d3.select("#contenido_reporte").append("svg")
                            .attr("width", width + margin.right + margin.left)
                            .attr("height", height + margin.top + margin.bottom)
                            .append("g")
                            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

//                    d3.json("treevendedores.php", function(error, flare) {
//                        root = flare;
//                        root.x0 = height / 2;
//                        root.y0 = 0;
//
//                        function collapse(d) {
//                            if (d.children) {
//                                d._children = d.children;
//                                d._children.forEach(collapse);
//                                d.children = null;
//                            }
//                        }
//
//                        root.children.forEach(collapse);
//                        update(root);
//                    });

                    root = JSON.parse('<?php echo $json_vendedores;?>') ;
//                    root = JSON.parse('{ "name": "flare","children": [{"name": "AggloÃ±erativeClust&eacute;r", "size": 3938},{"name": "CommunityStructure", "size": 3812},{"name": "HierarchicalCluster", "size": 6714},{"name": "MergeEdge", "size": 743}]}') ;
                    root.x0 = height / 2;
                    root.y0 = 0;

                    function collapse(d) {
                        if (d.children) {
                            d._children = d.children;
                            d._children.forEach(collapse);
                            d.children = null;
                        }
                    }

//                    root.children.forEach(collapse);
                    update(root);

                    d3.select(self.frameElement).style("height", "800px");

                    function update(source) {

                        // Compute the new tree layout.
                        var nodes = tree.nodes(root).reverse(),
                                links = tree.links(nodes);

                        // Normalize for fixed-depth.
                        nodes.forEach(function(d) {
                            d.y = d.depth * 180;
                        });

                        // Update the nodesï¿½
                        var node = svg.selectAll("g.node")
                                .data(nodes, function(d) {
                            return d.id || (d.id = ++i);
                        });

                        // Enter any new nodes at the parent's previous position.
                        var nodeEnter = node.enter().append("g")
                                .attr("class", "node")
                                .attr("transform", function(d) {
                            return "translate(" + source.y0 + "," + source.x0 + ")";
                        }).on("click", click);

                        nodeEnter.append("circle")
                                .attr("r", 1e-6)
                                .style("fill", function(d) {
                            return d._children ? "lightsteelblue" : "#fff";
                        });

                        nodeEnter.append("text")
                                .attr("x", function(d) {
                            return d.children || d._children ? -10 : 10;
                        })
                                .attr("dy", ".35em")
                                .attr("text-anchor", function(d) {
                            return d.children || d._children ? "end" : "start";
                        })
                                .text(function(d) {
                            return d.name;
                        }).style("fill-opacity", 1e-6);

                        // Transition nodes to their new position.
                        var nodeUpdate = node.transition()
                                .duration(duration)
                                .attr("transform", function(d) {
                            return "translate(" + d.y + "," + d.x + ")";
                        });

                        nodeUpdate.select("circle")
                                .attr("r", 4.5)
                                .style("fill", function(d) {
                            return d._children ? "lightsteelblue" : "#fff";
                        });

                        nodeUpdate.select("text")
                                .style("fill-opacity", 1);

                        // Transition exiting nodes to the parent's new position.
                        var nodeExit = node.exit().transition()
                                .duration(duration)
                                .attr("transform", function(d) {
                            return "translate(" + source.y + "," + source.x + ")";
                        })
                                .remove();

                        nodeExit.select("circle")
                                .attr("r", 1e-6);

                        nodeExit.select("text")
                                .style("fill-opacity", 1e-6);

                        // Update the linksï¿½
                        var link = svg.selectAll("path.link")
                                .data(links, function(d) {
                            return d.target.id;
                        });

                        // Enter any new links at the parent's previous position.
                        link.enter().insert("path", "g")
                                .attr("class", "link")
                                .attr("d", function(d) {
                            var o = {x: source.x0, y: source.y0};
                            return diagonal({source: o, target: o});
                        });

                        // Transition links to their new position.
                        link.transition()
                                .duration(duration)
                                .attr("d", diagonal);

                        // Transition exiting nodes to the parent's new position.
                        link.exit().transition()
                                .duration(duration)
                                .attr("d", function(d) {
                            var o = {x: source.x, y: source.y};
                            return diagonal({source: o, target: o});
                        })
                                .remove();

                        // Stash the old positions for transition.
                        nodes.forEach(function(d) {
                            d.x0 = d.x;
                            d.y0 = d.y;
                        });
                    }

                    // Toggle children on click.
                    function click(d) {
                        if (d.children) {
                            d._children = d.children;
                            d.children = null;
                        } else {
                            d.children = d._children;
                            d._children = null;
                        }
                        update(d);
                    }
                </script>
                <?php

            }


            function json_vendedores($padre_id) {

                if($padre_id>0){
//                    echo "select int_nombre, int_apellido, vendedor.* from vendedor,interno where vdo_id='$padre_id' and vdo_int_id=int_id";
                    $vendedor=  FUNCIONES::objeto_bd_sql("select int_nombre, int_apellido, vendedor.* from vendedor,interno where vdo_id='$padre_id' and vdo_int_id=int_id");
                    $name="$vendedor->int_nombre $vendedor->int_apellido";
                }else{
                    $vendedor=null;
                    $name='Raiz';
                }
                $ob_json="{";
                $ob_json.="\"name\":\"$name\",";
                $hijos=  FUNCIONES::objetos_bd_sql("select * from vendedor where vdo_vendedor_id='$padre_id'");
                if($hijos->get_num_registros()>0){
                    $ob_json.='"children":[';
                    for ($i = 0; $i < $hijos->get_num_registros(); $i++) {
                        $_vendedor=$hijos->get_objeto();
                        if($i>0){
                            $ob_json.=',';
                        }
                        $ob_json.=$this->json_vendedores($_vendedor->vdo_id);
                        $hijos->siguiente();
                    }
                    $ob_json.=']}';
                }else{
                    $ob_json.='"size":0}';
                }
                return $ob_json;

            }

            function obtener_ultimos_datos_interno($interno, &$telefono, &$celular, &$email) {
                $conec = new ADO();

                //Ultimo Telefono de la Persona
                $sql = "select int_telefono from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $telefono = $objeto->inttelf_telefono;

                //Ultimo Celular de la Persona
                $sql = "select int_celular from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $celular = $objeto->intcel_celular;

                //Ultimo Email de la Persona
                $sql = "select int_email from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $email = $objeto->intemail_email;
            }

            function obtener_total_pagos_acumulados($ind_id) {
                $sql = "select * from interno_deuda_pagos where idp_ind_id='" . $ind_id . "' and idp_estado='Activo'";

                //echo $sql;
                $conec = new ADO();
                $conec->ejecutar($sql);
                $total = 0;
                $num = $conec->get_num_registros();

                if ($num > 0) {

                    for ($i = 0; $i < $num; $i++) {
                        $obj = $conec->get_objeto();
                        $total += $obj->idp_monto;
                        $conec->siguiente();
                    }
                }

                return $total;
            }

        }
        ?>