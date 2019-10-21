<?php

class REPMORA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPMORA() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'repmora';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('MORAS');
        $this->busqueda();
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
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=cuotasxdia" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio
                                                                    <div id="ContenedorDiv">
                                               <div class="Etiqueta" ><span class="flechas1">* </span>Fecha</div>
                                                 <div id="CajaInput">
                                                                                    <input readonly="readonly" class="caja_texto" name="fecha" id="fecha" size="12" value="<?php echo date('Y-m-d'); ?>" type="text">
                                                                                            <input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
                                                                                            <script type="text/javascript">
                                                                                                                            Calendar.setup({inputField     : "fecha"
                                                                                                                                                            ,ifFormat     :     "%Y-%m-%d",
                                                                                                                                                            button     :    "but_fecha_pago2"
                                                                                                                                                            });
                                                                                            </script>
                                                                            </div>		
                                            </div>
                        Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha" id="fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
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
                            $("#fecha").mask("99/99/9999");
                        });
                    </script>
                    <?php
                }

                if ($_POST['formu'] == 'ok')
                    $this->mostrar_reporte();
            }

            function mostrar_reporte() {
                $conversor = new convertir();

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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuotasxdia\';"></td></tr></table><br><br>
				'; ?>



                <div id="contenido_reporte" style="clear:both;">
                    <center>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="35%" >
                                    <strong><?php echo _nombre_empresa; ?></strong><BR>
                                </td>
                                <?php
                                if ($_POST['fecha'] == '') {
                                    $_POST['fecha'] = date('d/m/Y');
                                }
                                ?>
                                <td  width="30%" ><p align="center" ><strong><h3><center>CUOTAS X DIA<br/><?php echo setear_fecha(strtotime($conversor->get_fecha_mysql($_POST['fecha']))); ?></center></h3></strong></p></td>
                                <td  width="35%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
                            </tr>
                        </table>
                        <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="20%">Persona</th>
                                    <th width="20%">Telefonos</th>
                                    <th width="20%">Observacion</th>
                                    <th width="10%">F. Programada</th>                                    
                                    <th>Dias de Mora</th>                                    
                                </tr>	
                            </thead>
                            <tbody>
                                <?php
                                $conec = new ADO();

                                if ($_POST['fecha'] == '') {
                                    $_POST['fecha'] = date('d/m/Y');
                                    $cad1 = " and ind_fecha_programada< '" . $conversor->get_fecha_mysql($_POST['fecha']) . "' ";
                                } else {
                                    $cad1 = " and ind_fecha_programada < '" . $conversor->get_fecha_mysql($_POST['fecha']) . "' ";
                                }
                                $sql = "SELECT ind_monto,ind_moneda,ind_concepto,ind_fecha_programada,ind_fecha_pago,ind_estado,int_id,int_nombre,int_apellido,int_email,int_telefono,int_celular
		FROM 
		interno_deuda inner join interno on ( ind_int_id=int_id)
		where ind_estado <> 'Anulado' and ind_estado <> 'Retenido' and ind_estado='Pendiente' $cad1
		order by int_apellido,int_nombre,ind_id asc";
                                echo $sql;

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

                                for ($i = 0; $i < $num; $i++) {
                                    $objeto = $conec->get_objeto();

                                    $dato = " <br><b>Tel:</b>$objeto->int_telefono, <b>Cel:</b> $objeto->int_celular, <b>Email:</b> $objeto->int_email";

                                    if ($i == 0)
                                        $ant_num = $objeto->int_id;

                                    if ($ant_num <> $objeto->int_id) {
                                        ?>				
                                        <tr>						
                                            <td colspan="5" style="text-align:right;"><b>Subtotal&nbsp;&nbsp;&nbsp;</b></td>					
                                            <td><b><? echo round($stbol, 2); ?></b></td>					
                                            <td><b><? echo round($stsus, 2); ?></b></td>				
                                        </tr>				
                                        <?php
                                        $stbol = 0;
                                        $stsus = 0;
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
                                    echo $objeto->int_telefono . '/' . $objeto->int_celular;
                                    echo "&nbsp;</td>";

                                    echo "<td>";
                                    echo $objeto->ind_concepto;
                                    echo "&nbsp;</td>";


                                    echo '<td>';
                                    if ($objeto->ind_fecha_programada <> '0000-00-00')
                                        echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
                                    echo "&nbsp;</td>";



                                    echo '<td >';
                                    echo $this->dif_fechas(FUNCIONES::get_fecha_mysql($_POST['fecha']), $objeto->ind_fecha_programada);
                                    echo "&nbsp;</td>";
                                    $conec->siguiente();
                                }
                                ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><b><? echo round($tbol, 2); ?></b></td>
                                    <td><b><? echo round($tsus, 2); ?></b></td>
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

            function dif_fechas($fechaF,$fechaI) {                
                $segundos = strtotime($fechaF) - strtotime($fechaI);
                $diferencia_dias = intval($segundos / 60 / 60 / 24);
                return $diferencia_dias;
            }

        }
        ?>