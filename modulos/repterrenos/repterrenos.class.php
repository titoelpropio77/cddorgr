<?php

class REPTERRENOS extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPTERRENOS() {
        //permisos
        $this->ele_id = 135;

        $this->busqueda();

        //fin permisos

        $this->coneccion = new ADO();

        $this->link = 'gestor.php';

        $this->modulo = 'repterrenos';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('ESTADO DE URBANIZACION');
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
            <script>
                function cargar_manzano(id)
                {
                    var valores = "tarea=manzanos_reporte&urb=" + id;

                    ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
                }

                function enviar_formulario()
                {
                    var urbanizacion = document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;

                    if (urbanizacion != '')
                    {
                        document.frm_sentencia.submit();
                    }
                    else
                    {
                        $.prompt('Para generar el reporte debe seleccionar la Urbanización', {opacity: 0.8});
                    }
                }
            </script>	
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repterrenos" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
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
                                <div class="Etiqueta" >Manzano</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        <select style="width:200px;" name="ven_man_id" class="caja_texto">
                                            <option value="">Seleccione</option>
            <?php
            if ($_POST['ven_urb_id'] <> "") {
                $fun = NEW FUNCIONES;
                $fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_man_id']);
            }
            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!--Fin-->

                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="button" class="boton" name="" onclick="javascript:enviar_formulario();" value="Generar Reporte">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>	
                <div>
            <?php
        }

        if ($_POST['formu'] == 'ok')
            $this->mostrar_reporte();
    }

    function mostrar_reporte() {
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repterrenos\';"></td></tr></table><br><br>
				'; ?>
                <div id="contenido_reporte" style="clear:both;">
                    <center>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="30%" >
                                    </br><strong><?php echo _nombre_empresa; ?></strong></br>
        <?php echo _datos_empresa; ?></br></br>
                                </td>
                                <td  width="40%" ><p align="center" ><strong><h3><center>ESTADO DE URBANIZACIÓN</br><?php echo strtoupper($this->nombre_uv()); ?></center></h3></strong></p></td>
                                <td  width="30%" ><div align="right"></br><img src="imagenes/micro.png" /></br></div></td>
                            </tr>
                        </table>

        <?php
        $conec = new ADO();
        $conec2 = new ADO();

        $sql = "select man_id,man_nro,cast(man_nro as SIGNED) as numero from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "'";

        if ($_POST['ven_man_id'] <> '') {
            $sql.=" and man_id ='" . $_POST['ven_man_id'] . "'";
        }

        $sql.=" order by numero asc";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $st_man = 0;
        $st_ven = 0;
        $st_dis = 0;
        $nt_man = 0;
        $nt_ven = 0;
        $nt_dis = 0;

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
                            <div style="margin:10px 0px 0px 0px; float:left; clear:both; ">
                                <table style="font-family: tahoma; font-size: 11px;" width="750px" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="color: #006239;" width="800px" ><b>MANZANO NRO <?php echo $objeto->man_nro; ?></b></td>
                                    </tr>
                                    <tr>
                                        <td  style="color: #006239; padding:3px 0px 0px 0px;"><?php $this->listado_de_lotes($objeto->man_id, $conec2, $st_man, $st_ven, $st_dis, $nt_man, $nt_ven, $nt_dis); ?></td>
                                    </tr>	
                                </table>
                            </div>

            <?php
            $conec->siguiente();
        }
        $this->resumen($st_man, $st_ven, $st_dis, $nt_man, $nt_ven, $nt_dis);
        ?>
                    </center>
                    <br/><br><br><div style="clear:both;"><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table></div>
                </div>
                <br>	
                        <?php
                    }

                    function nombre_uv() {
                        $conec = new ADO();

                        $sql = "select urb_nombre from urbanizacion where urb_id='" . $_POST['ven_urb_id'] . "'";

                        $conec->ejecutar($sql);

                        $objeto = $conec->get_objeto();

                        return $objeto->urb_nombre;
                    }

                    function resumen($st_man, $st_ven, $st_dis, $nt_man, $nt_ven, $nt_dis) {
                        ?>
                <div style="margin:10px 0px 0px 0px; float:left; clear:both; ">
                    <h1><br/><center>RESUMEN GENERAL</center></h1>
                    <table class="tablaLista" style="width:750px;" cellpadding="0" cellspacing="0" >
                        <thead>
                            <tr>
                                <th width="">Sup Total</th>
                                <th width="">Sup Vendida</th>
                                <th width="">Sup Disponible</th>
                                <th width="">Nro Lotes</th>
                                <th width="">Lotes Vendidos</th>
                                <th width="">Lotes Disponibles</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td style="text-align:left; font-size:12;"><?php echo $st_man;
        $ptven = round($st_ven * 100 / $st_man, 2); ?> m2</td>
                                <td style="text-align:left; font-size:12;"><?php echo $st_ven; ?> m2 (<?php echo $ptven; ?> %)</td>
                                <td style="text-align:left; font-size:12;"><?php echo $st_dis; ?> m2 (<?php echo 100 - $ptven; ?> %)</td>
                                <td style="text-align:left; font-size:12;"><?php echo $nt_man;
        $ptlot = round($nt_ven * 100 / $nt_man, 2); ?></td>
                                <td style="text-align:left; font-size:12;"><?php echo $nt_ven; ?> (<?php echo $ptlot; ?> %)</td>
                                <td style="text-align:left; font-size:12;"><?php echo $nt_dis; ?> (<?php echo 100 - $ptlot; ?> %)</td>
                            </tr>
                        </tfoot>
                    </table>
                    <br/><br/>
                </div>
        <?php
    }

    function listado_de_lotes($man, $conec, &$st_man, &$st_ven, &$st_dis, &$nt_man, &$nt_ven, &$nt_dis) {
        $sql = "select lot_id,lot_nro,lot_estado,lot_superficie,cast(lot_nro as SIGNED) as numero,lot_tipo,lot_sup_vivienda
		from 
		lote 
		where 
		lot_man_id='" . $man . "' order by numero asc";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $s_man = 0;
        $s_ven = 0;
        $s_dis = 0;
        $n_man = 0;
        $n_ven = 0;
        $n_dis = 0;
        ?>		
                <table class="tablaLista" cellpadding="0" cellspacing="0" width="800px">


                <?php
                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec->get_objeto();
                    ?>

                        <?php
                        //echo $objeto->lot_superficie;
                        if ($objeto->lot_tipo == 'Lote') {
                            $s_man+=$objeto->lot_superficie;
                            $n_man++;
                        } else {
                            $s_man+=$objeto->lot_sup_vivienda;
                            $n_man++;
                        }

                        if ($objeto->lot_estado == 'Vendido') {

                            if ($objeto->lot_tipo == 'Lote') {
                                $s_ven+=$objeto->lot_superficie;
                                $n_ven++;
                            } else {
                                $s_ven+=$objeto->lot_sup_vivienda;
                                $n_ven++;
                            }
                        } else {
                            if ($objeto->lot_tipo == 'Lote') {
                                $s_dis+=$objeto->lot_superficie;
                                $n_dis++;
                            } else {
                                $s_dis+=$objeto->lot_sup_vivienda;
                                $n_dis++;
                            }
                        }
                        ?>


                        <?php
                        $conec->siguiente();
                    }
                    ?>
                    <tfoot>
                        <tr>
                            <td style="text-align:left; font-size:12;">&nbsp;

                            </td>
                            <td style="text-align:left; font-size:12;">
                                Total <?php echo $n_man;
            $nt_man+=$n_man; ?>: <?php
                    echo $s_man;
                    $st_man+=$s_man;
                    $pven = round($s_ven * 100 / $s_man, 2)
                    ?> m2
                            </td>
                            <td style="text-align:left; font-size:12;">
                                Vendido <?php echo $n_ven;
            $nt_ven+=$n_ven; ?>: <?php echo $s_ven;
            $st_ven+=$s_ven; ?> m2 (<?php echo $pven; ?> %)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Disponible <?php echo $n_dis;
            $nt_dis+=$n_dis; ?>: <?php echo $s_dis;
            $st_dis+=$s_dis; ?> m2 (<?php echo 100 - $pven; ?> %)
                            </td>
                        </tr>
                    </tfoot>
                </table>
        <?php
    }

}
?>