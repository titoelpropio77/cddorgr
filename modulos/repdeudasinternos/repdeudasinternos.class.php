<?php
class REPDEUDASINTERNOS extends BUSQUEDA {
    var $formulario;
    var $mensaje;
    function REPDEUDASINTERNOS() {
        //permisos
        $this->ele_id = 126;
        $this->busqueda();
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'repdeudasinternos';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('MORA URBANIZACIONES');
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
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->
            <script>
                function reset_fecha_inicio()
                {
                    document.frm_sentencia.fecha_inicio.value = '';
                }
                function reset_fecha_fin()
                {
                    document.frm_sentencia.fecha_fin.value = '';
                }
            </script>					
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repdeudasinternos" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Urbanización</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_urb_id" class="caja_texto" onchange="cargar_manzano(this.value);">
                                        <option value="">Seleccione</option>
            <?php
            $fun = NEW FUNCIONES;
            $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
            ?>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Tipo</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        <select style="width:200px;" name="tipo" class="caja_texto">
                                            <option value="">Todos</option>
                                            <option value="Credito">Credito Directo</option>
                                            <option value="Credito Iberocoop">Credito Iberocoop</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Persona</div>
                                <div id="CajaInput">
                                    <select name="interno" class="caja_texto">
                                        <option value="">Todas</option>
            <?php
            $fun = NEW FUNCIONES;
            $fun->combo("select int_id as id,CONCAT(int_apellido,' ',int_nombre) as nombre from interno order by int_apellido,int_nombre asc", $_POST['interno']);
            ?>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Estado</div>
                                <div id="CajaInput">
                                    <select name="estado" class="caja_texto">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Pagado">Pagado</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_inicio" id="fecha_inicio" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_inicio();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_fin" id="fecha_fin" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_fin();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cuotas</div>
                                <div id="CajaInput">
                                    <select name="cuotas" class="caja_texto">
                                        <option value="T">Todas</option>
                                        <option value="M">En Mora</option>
                                    </select>
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

    function mostrar_reporte() {
        $conversor = new convertir();
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
                <div id="contenido_reporte" style="clear:both;";>
                    <center>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="40%" >
                                    </br><strong><?php echo _nombre_empresa; ?></strong></br>
        <?php echo _datos_empresa; ?></br></br>
        <?php
        if ($_POST['ven_urb_id'] == '') {
            $nombre_urbanizacion = 'Todas';
        } else {
            $conec = new ADO();

            $sql = "SELECT urb_nombre from urbanizacion where urb_id =" . $_POST['ven_urb_id'];

            $conec->ejecutar($sql);

            $objeto = $conec->get_objeto();

            $nombre_urbanizacion = $objeto->urb_nombre;
        }
        ?>
                                    <strong>Urbanizacion: </strong> <?php echo $nombre_urbanizacion; ?><BR>
                                </td>
                                <td  width="20%" ><p align="center" ><strong><h3><center>MORA</center></h3></strong></p></td>
                                <td  width="40%" ><br/><div align="right"><img src="imagenes/micro.png" /></div></td>
                            </tr>
                        </table>
                        <center><?php if ($_POST['estado'] <> "") {
                                echo '<b>ESTADO: </b>' . strtoupper($_POST['estado']);
                            } ?></center><br />
                        <center><?php if ($_POST['fecha_inicio'] <> "") echo '<strong>Del:</strong> ' . date('d/m/Y', strtotime($conversor->get_fecha_mysql($_POST['fecha_inicio']))) ?><?php if ($_POST['fecha_fin'] <> "") echo ' <strong>Al:</strong> ' . date('d/m/Y', strtotime($conversor->get_fecha_mysql($_POST['fecha_fin']))) ?></center>
                        <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Persona</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Vencimiento</th>
                                    <th>Moneda</th>
                                    <th>Dias Mora</th>                                                                        
                                    <th class="tOpciones">M. mora $us</th>
                                    <th class="tOpciones">M. cuota $us</th>
                                    <th class="tOpciones">T. a pagar $us</th>
                                </tr>	
                            </thead>
                            <tbody>
        <?php
        $conec = new ADO();

        $fecha = date('Y-m-d');

        if ($_POST['interno'] <> "") {
            $cad = "int_id='" . $_POST['interno'] . "' and ";
        } else {
            $cad = "";
        }

        if ($_POST['ven_urb_id'] <> "") {
            $cadurb = "man_urb_id='" . $_POST['ven_urb_id'] . "' and ";
        } else {
            $cadurb = "";
        }

        if ($_POST['tipo'] <> "") {
            $cadtipo = " and ven_tipo='" . $_POST['tipo'] . "'";
        } else {
            $cadtipo = "";
        }


        if ($_POST['estado'] <> '') {
            $cad1 = " and ind_estado = '" . $_POST['estado'] . "'";
        } else {
            $cad1 = "";
        }

        if ($_POST['fecha_inicio'] <> '' && $_POST['fecha_fin'] == '') {
            $cad1 = $cad1 . " and ind_fecha_programada >= '" . $conversor->get_fecha_mysql($_POST['fecha_inicio']) . "'";

            if ($_POST['cuotas'] == "M") {
                $cad1 = $cad1 . " and ind_fecha_programada <= '" . date('Y-m-d') . "'";
            }
        } else {
            if ($_POST['fecha_inicio'] == '' && $_POST['fecha_fin'] <> '') {
                $cad1 = $cad1 . " and ind_fecha_programada <= '" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "'";

                if ($_POST['cuotas'] == "M") {
                    $cad1 = $cad1 . " and ind_fecha_programada <= '" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "'";
                } else {
                    $cad1 = $cad1 . " and ind_fecha_programada <= '" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "'";
                }
            } else {
                if ($_POST['fecha_inicio'] <> '' && $_POST['fecha_fin'] <> '') {
                    if ($_POST['cuotas'] == "M") {
                        $cad1 = $cad1 . " and ind_fecha_programada >= '" . $conversor->get_fecha_mysql($_POST['fecha_inicio']) . "' and ind_fecha_programada <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "'";
                    } else {
                        $cad1 = $cad1 . " and ind_fecha_programada >= '" . $conversor->get_fecha_mysql($_POST['fecha_inicio']) . "' and ind_fecha_programada <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "'";
                    }
                } else {
                    if ($_POST['cuotas'] == "M") {
                        $cad1 = $cad1 . " and ind_fecha_programada < '$fecha' ";
                    }
                }
            }
        }
        $sql = "
		SELECT 
			ind_monto,ind_moneda,ind_concepto,
			ind_fecha_programada,ind_monto_parcial,int_id,int_nombre,
			int_apellido,int_email,int_telefono,int_celular,ven_tipo
		FROM 
			interno_deuda inner join interno on ($cad ind_int_id=int_id)
			inner join venta on (ind_tabla_id=ven_id)
			inner join lote on (ven_lot_id=lot_id)
			inner join manzano on ($cadurb lot_man_id=man_id)
		where 1=1 
			$cad1 $cadtipo
		order by 
			int_apellido,int_nombre,ind_fecha_programada asc";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $conversor = new convertir();
        $tipocambio = $this->tc;
        $tbol = 0;
        $tsus = 0;
        $stbol = 0;
        $stsus = 0;
        $ant_num = 0;
        $datog = "";
        $sum_dias_mora=0;
        $fecha_now=date('Y-m-d');
        $sum_tmonto=0;        
        
        $sub_stmonto=0;
        $sub_sdmora=0;
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            $dato = " (<b>Tel:</b> $objeto->int_telefono, <b>Cel:</b> $objeto->int_celular, <b>Email:</b> $objeto->int_email)";

            if ($i == 0)
                $ant_num = $objeto->int_id;

            if ($ant_num <> $objeto->int_id) {
                ?>				
                                        <tr>						
                                            <td colspan="6" style="text-align:right;"><b>Subtotal&nbsp;&nbsp;&nbsp;</b></td>					
                                            <td><b><? echo number_format($sub_sdmora,2); ?></b></td>					
                                            <!--<td><b><? // echo number_format(round($stbol, 2), 2); ?></b></td>-->					
                                            <td><b><? echo number_format(round($stsus, 2), 2); ?></b></td>				
                                            <td><b><? echo number_format(round($sub_stmonto, 2), 2); ?></b></td>				
                                        </tr>				
                                        <?php
                                        $stsus = 0;
                                        $sub_stmonto=0;
                                        $sub_sdmora=0;
                                        $ant_num = $objeto->int_id;
                                    }

                                    echo '<tr>';

                                    echo "<td>";
                                    echo $objeto->int_nombre . ' ' . $objeto->int_apellido;
                                    if ($datog <> $dato) {
                                        echo $dato;
                                        $datog = $dato;
                                    }
                                    echo "&nbsp;</td>";
                                    echo "<td>";
                                    if ($objeto->ven_tipo == 'Credito')
                                        echo 'Credito Directo';
                                    else
                                        echo $objeto->ven_tipo;
                                    echo "</td>";
                                    echo "<td>";
                                    echo $objeto->ind_concepto;
                                    echo "&nbsp;</td>";
                                    $color = "#000000";

                                    if ($objeto->ind_fecha_programada < date('Y-m-d'))
                                        $color = "#FB0404;";

                                    echo '<td style="color:' . $color . '">';
                                    if ($objeto->ind_fecha_programada <> '0000-00-00')
                                        echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
                                    echo "&nbsp;</td>";
                                    echo "<td>";
                                    if ($objeto->ind_moneda == '1')
                                        echo 'Bolivianos';
                                    else
                                        echo 'Dolares';
                                    echo "&nbsp;</td>";
                                    
                                    echo "<td>";
                                        $fecha_sig=  FUNCIONES::siguiente_mes($objeto->ind_fecha_programada);
                                        if($fecha_sig>$fecha_now){
                                            $fecha_sig=$fecha_now;
                                        }
                                        $dias_mora=FUNCIONES::diferencia_dias($objeto->ind_fecha_programada, $fecha_sig);
                                         echo $dias_mora;                                        
                                    echo "</td>";
                                    
                                    echo "<td>";
                                    echo $dias_mora;
                                    $sub_sdmora+=$dias_mora;
                                    $sum_dias_mora+=$dias_mora;
                                    echo "</td>";
                                    
//                                    $osaldo = round((($objeto->ind_monto - $objeto->ind_monto_parcial) * $tipocambio), 2);
//                                    $tbol+=($objeto->ind_monto - $objeto->ind_monto_parcial) * $tipocambio;
                                    $monto=$objeto->ind_monto - $objeto->ind_monto_parcial;
                                    $tsus+=($monto);

                                    echo "<td>";
                                    echo number_format(round(($monto), 2), 2);
                                    echo "&nbsp;</td>";
                                    $stsus+=($objeto->ind_monto - $objeto->ind_monto_parcial);                                   

                                    echo "<td>";
                                    $monto_total=$dias_mora+$monto;
                                    echo $monto_total;
                                    $sum_tmonto+=$monto_total;
                                    $sub_stmonto+=$monto_total;
                                    echo "</td>";
//                                    if ($objeto->ind_moneda == '1') {
//                                        $tbol+=($objeto->ind_monto - $objeto->ind_monto_parcial);
//
//
////                                        echo "<td>";
////                                        echo number_format(round(($objeto->ind_monto - $objeto->ind_monto_parcial), 2), 2);
////                                        echo "&nbsp;</td>";
//
//                                        $tsus+=(($objeto->ind_monto - $objeto->ind_monto_parcial) / $tipocambio);
//
//                                        $osaldo = round((($objeto->ind_monto - $objeto->ind_monto_parcial) / $tipocambio), 2);
//
//                                        echo "<td>";
//                                        echo number_format(round(($osaldo), 2), 2);
//                                        echo "&nbsp;</td>";
//
//                                        $stbol+=($objeto->ind_monto - $objeto->ind_monto_parcial);
//                                        $stsus+=$osaldo;
//                                    } else {
//                                        $osaldo = round((($objeto->ind_monto - $objeto->ind_monto_parcial) * $tipocambio), 2);
//
//                                        $tbol+=($objeto->ind_monto - $objeto->ind_monto_parcial) * $tipocambio;
//
//                                        $tsus+=($objeto->ind_monto - $objeto->ind_monto_parcial);
//
////                                        echo "<td>";
////                                        echo number_format(round(($osaldo), 2), 2);
////                                        echo "&nbsp;</td>";
//
//                                        echo "<td>";
//                                        echo number_format(round(($objeto->ind_monto - $objeto->ind_monto_parcial), 2), 2);
//                                        echo "&nbsp;</td>";
//
//                                        $stsus+=($objeto->ind_monto - $objeto->ind_monto_parcial);
//                                        $stbol+=$osaldo;
//                                    }

                                    if ($i == $num - 1 && $_POST['interno'] == "") {
                                        ?>				
                                        <tr>						
                                            <td colspan="6" style="text-align:right;"><b>Subtotal&nbsp;&nbsp;&nbsp;</b></td>					
                                            <td><b><? echo number_format($sub_sdmora,2); ?></b></td>					
                                            <!--<td><b><? // echo number_format(round($stbol, 2), 2); ?></b></td>-->					
                                            <td><b><? echo number_format(round($stsus, 2), 2); ?></b></td>				
                                            <td><b><? echo number_format(round($sub_stmonto, 2), 2); ?></b></td>				
                                        </tr>				
                                        <?php                                        
                                        $stsus = 0;
                                        $sub_stmonto=0;
                                        $sub_sdmora=0;
                                        $ant_num = $objeto->int_numero;
                                    }


                                    $conec->siguiente();
                                }
                                ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><b><? echo number_format($sum_dias_mora,2); ?></b></td>					
                                    <!--<td><b><? // echo number_format(round($tbol, 2), 2); ?></b></td>-->
                                    <td><b><? echo number_format(round($tsus, 2), 2); ?></b></td>
                                    <td><b><? echo number_format(round($sum_tmonto, 2), 2); ?></b></td>				
                                </tr>
                            </tbody>
                        </table>
                    </center>
                    <br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
                </div>
                <br>	
                                <?php
                                echo "";
                            }

                        }
                        ?>