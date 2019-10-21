<?php

class REPVENDIDOS extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REPVENDIDOS() {
        //permisos
        $this->ele_id = 137;

        $this->busqueda();

        //fin permisos

        $this->coneccion = new ADO();

        $this->link = 'gestor.php';

        $this->modulo = 'repvendidos';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('ESTADO DE LOTES');

        $this->usu = new USUARIO;
//        echo '<pre>';
//        print_r($_SESSION);
//        echo '</pre>';
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function obtener_grupo_id($usu_id) {
        $conec = new ADO();

        $sql = "SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_gru_id;
    }

    function obtener_id_interno_tbl_usuario($usu_id) {
        $conec = new ADO();

        $sql = "SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_per_id;
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

                function mostrar_filtros_vendedor_y_estado_reserva() {
                    var estado = $('#estado option:selected').val();
                    if (estado == 'reservados') {
                        $('.filtro_reservados').css('display', 'block');
                    } else {
                        $('.filtro_reservados').css('display', 'none');
                    }
                }

                function cargar_uv(id)
                {
                    //cargar_lote(0);
                    var valores = "tarea=uv&urb=" + id;
                    ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
                }
                function cargar_manzano(id, uv)
                {
                    //var	valores="tarea=manzanos_reporte&urb="+id;
                    //var urb_id= document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                    var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;
                    ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
                }

                function obtener_valor_manzano() {
                    var auxUrb = $('#ven_urb_id option:selected').val();
                    var auxUv = $('#ven_uv_id').val();
                    //                                                    alert(auxUrb + '-' + auxUv);
                    cargar_manzano(auxUrb, auxUv);
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
                        $.prompt('Para generar el reporte debe seleccionar la Urbanizaci�n', {opacity: 0.8});
                    }
                }
            </script>	
            <script src="js/util.js"></script>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repvendidos" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizaci�n</div>
                                <div id="CajaInput">
                                             <!--<select style="width:200px;" name="ven_urb_id" class="caja_texto" onchange="cargar_manzano(this.value);">-->
                                    <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">    
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("SELECT urb_id AS id,urb_nombre AS nombre FROM urbanizacion", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->

                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >UV</div>
                                <div id="CajaInput">
                                    <div id="uv">
                                        <select style="width:200px;" name="ven_uv_id" class="caja_texto" onchange="obtener_valor_manzano();">
                                            <option value="">Seleccione</option>
                                            <?php
                                            if ($_POST['ven_urb_id'] <> "") {
                                                $fun = NEW FUNCIONES;
                                                $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_uv_id']);
                                            }
                                            ?>
                                        </select>
                                    </div>
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
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Estado Lote</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="estado" id="estado" class="caja_texto" onchange="mostrar_filtros_vendedor_y_estado_reserva();">
                                        <option value="libres">Libres</option>
                                        <option value="reservados">Reservados</option>
                                        <option value="vendidos">Vendidos</option>
                                        <option value="bloqueados">Bloqueados</option>
                                        <option value="todo">Todos</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->

                            <!--Inicio-->
                            <?php if ($this->usu->get_gru_id() !== "Vendedores") { ?>
                                <div id="ContenedorDiv" class="filtro_reservados" style="display: none">
                                    <div class="Etiqueta">Vendedor</div>
                                    <div id="CajaInput">
                                        <?php
                                        if ($this->usu->get_gru_id() == "Vendedores") {
                                            $id_interno = $this->obtener_id_interno_tbl_usuario($this->usu->get_id());
                                            ?>
                                            <select style="width:200px;" name="vendedor" class="caja_texto">
                                            <?php
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' AND vdo_int_id='$id_interno'", $_POST['vendedor']);
                                            ?>
                                            </select>
                                                <?php
                                            } else {
                                                ?>
                                            <select style="width:200px;" name="vendedor" class="caja_texto">
                                                <option value="">Seleccione</option>
                                            <?php
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['vendedor']);
                                            ?>
                                            </select>
                                                <?php
                                            }
                                            ?>
                                    </div>
                                </div>
                                    <?php } ?>
                            <!--Fin-->

                            <!--Inicio-->
                            <div id="ContenedorDiv" class="filtro_reservados" style="display: none">
                                <div class="Etiqueta" >Estado de la Reserva</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" id="estado_reserva" name="estado_reserva" class="caja_texto">
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Pendiente">Pendiente</option>
                                    </select>
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
                          <style>
                                .tablaLista tfoot tr th{
                                  padding :0 10px;
                    
                                }
                          </style>
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
                <?php echo '	<table align=right border=0><tr>
				<td><a href="#" id="importar_excel"><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL"></a></td>
				<td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repvendidos\';"></td></tr></table><br><br>
				'; ?>


                <style>
                    .tablaLista tfoot tr th{
                        padding :0 10px;
                        /*color: red !important;*/ 
                    }
                </style>
                <script src="js/jquery.thfloat-0.7.2.min.js"></script>
                <div id="contenido_reporte" style="clear:both;">
                    <center>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="30%" >
                                    <strong><?php echo _nombre_empresa; ?></strong></br>
                                    <strong>Santa Cruz - Bolivia</strong></td>
                                <td  width="40%" ><p align="center" ><strong><h3><center>REPORTE DE LOTES 
        <?php
        if ($_POST['estado'] != 'todo') {
            echo strtoupper($_POST['estado']);
        }
        ?></br><?php echo strtoupper($this->nombre_uv()); ?></center></h3></strong></p></td>
                                <td  width="30%" ><div align="right"><img src="imagenes/micro.png" /></div><br/><br/></td>
                            </tr>
                        </table>
                                                    <?php
                                                    $conec = new ADO();

                                                    $cad = "";

                                                    if ($_POST['ven_urb_id'] <> "")
                                                        $cad .= " and urb_id='" . $_POST['ven_urb_id'] . "' ";

                                                    if ($_POST['ven_uv_id'] <> "")
                                                        $cad .= " and uv_id='" . $_POST['ven_uv_id'] . "' ";

                                                    if ($_POST['ven_man_id'] <> "")
                                                        $cad .= " and man_id='" . $_POST['ven_man_id'] . "' ";


                                                    if ($_POST['estado'] == "vendidos") {
                                                        $sql = "SELECT 
					lot_id,ven_superficie,ven_id,ven_monto,ven_valor,ven_metro,
                                        ven_tipo,ven_decuento,ven_moneda,ven_cuota_inicial,
                                        ven_vdo_id,ven_fecha,ven_plazo,ven_observacion,
                                        int_nombre,int_apellido,uv_nombre,man_nro,
                                        lot_nro,cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,
										zon_precio,zon_moneda
					FROM 
					venta 
					inner join interno on (ven_int_id=int_id)
					inner join lote on (ven_lot_id=lot_id)
					inner join zona on(lot_zon_id=zon_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					
					where lot_nro not like 'NET-%' and
					ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar')
					
					$cad
					order by uv_nombre,manzano,lote asc
					";
                                                    }

                                                    if ($_POST['estado'] == "libres") {
                                                        $sql = "SELECT 
					lot_id,lot_superficie,zon_precio,zon_moneda,uv_nombre,man_nro,lot_nro,cast(man_nro as SIGNED) 
                                        as manzano ,cast(lot_nro as SIGNED) as lote, zon_nombre
					FROM 
					lote 
					
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join zona on (lot_zon_id=zon_id)
					
					where lot_nro not like 'NET-%' and lot_estado='Disponible' and lot_id >1
					$cad
					order by uv_nombre,manzano,lote asc";
                                                    }

                                                    if ($_POST['estado'] == "reservados") {

                                                        $cad_vdo = '';
                                                        if ($_POST['vendedor'] <> "") {
                                                            //$cad_vdo = "inner join vendedor on(res_vdo_id=$_POST[vendedor] and vdo_id=res_vdo_id)";
                                                            $cad_vdo = "inner join vendedor on(vdo_id=res_vdo_id and res_vdo_id=$_POST[vendedor])";
                                                        }

                                                        $cad_est_res = '';
                                                        if ($_POST['estado_reserva'] <> "") {
                                                            $cad_est_res = " and res_estado='$_POST[estado_reserva]'";
                                                        }


                                                        $sql = "SELECT 
					lot_id,lot_superficie,uv_nombre,man_nro,lot_nro,cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,
                                        res_int_id,res_vdo_id,res_id,res_fecha,res_plazo_fecha,res_nota,zon_precio,  zon_nombre
					FROM 
					lote 
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join zona on (lot_zon_id=zon_id)
					
                                        inner join reserva_terreno on (res_lot_id=lot_id $cad_est_res)
                                        $cad_vdo
					where lot_nro not like 'NET-%' and lot_estado='Reservado' and lot_id>1
					$cad
					
                                        order by uv_nombre,manzano,lote asc";
                                                    }

                                                    //order by uv_nombre,manzano,lote asc";

                                                    if ($_POST['estado'] == "bloqueados") {
                                                        $sql = "SELECT 
					lot_id,lot_superficie,uv_nombre,man_nro,lot_nro,bloq_int_id,bloq_vdo_id,bloq_nota,cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote, 
                                        bloq_fecha,zon_precio,zon_moneda,bloq_id,bloq_hora,bloq_estado,bloq_usu_id
					FROM 
					lote 
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join zona on (lot_zon_id=zon_id)
                                        inner join bloquear_terreno on (bloq_lot_id=lot_id and bloq_estado='Habilitado')                                       
					where lot_nro not like 'NET-%' and lot_estado='Bloqueado'
					$cad
					order by bloq_id desc,uv_nombre,manzano,lote asc";
                                                    }

                                                    if ($_POST['estado'] == "todo") {
                                                        $sql = "SELECT distinct
					lot_id,lot_superficie,uv_nombre,man_nro,lot_nro,lot_estado,cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,zon_precio,zon_moneda,
					lot_col_norte, lot_col_sur, lot_col_este, lot_col_oeste,  zon_nombre,lot_codigo
					FROM 
					lote 
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join uv on (lot_uv_id=uv_id)
                                        
					inner join zona on (lot_zon_id=zon_id) 
					where lot_nro not like 'NET-%' and 1=1
					$cad
					order by uv_nombre,manzano,lote, lot_estado asc";
                                                    }

                                                    $conec->ejecutar($sql);
//        echo $sql;

                                                    $num = $conec->get_num_registros();
                                                    ?>            


                        <script src="js/jquery.table2excel/jquery.table2excel.js"></script>
                        <script>
        $("#importar_excel").click(function() {
        $("#contenido_reporte").table2excel({
        // exclude CSS class
        exclude: ".noExl",
        name: "Estado de Lotes",
        filename: "Estado de Lotes" //do not include extension
        });
        });
                        </script>

                        <table id="MyTable" class="tablaLista tablesorter tabla_reporte" style="width:100%;" cellpadding="0" cellspacing="0" >

        <?php if ($_POST['estado'] == "vendidos") { ?>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nro. Venta</th>
                                        <th>Tipo de Venta</th>
                                        <th>Fecha</th>
                                        <th>Comprador</th>
                                        <th>Vendedor</th>
                                        <th>Uv</th>
                                        <th>Manzano</th>
                                        <th>Lote</th>
                                        <th>Moneda</th>
                                        <th>Sup. m2</th>
                                        <th>Precio m2</th>
                                        <th>Precio del Terreno</th>
                                        <th>Descuento</th>
                                        <th>Precio de la Venta</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $suma = 0;
            $cuotat = 0;
            $cuotatbs = 0;
            $spla = 0;

            $total_precio_terreno_sus = 0;
            $total_precio_terreno_bs = 0;
            $total_descuentos_sus = 0;
            $total_descuentos_bs = 0;

            $total = new stdClass();

            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();

                if ($objeto->ven_meses_plazo <> 0)
                    $cuota = round(($objeto->ven_monto - $objeto->ven_cuota_inicial) / $objeto->ven_meses_plazo, 2);
                else
                    $cuota = 0;
                ?>
                                        <tr>
                                            <td><?php echo ($i + 1); ?></td>
                                            <td><?php echo $objeto->ven_id; ?></td>
                                            <td><?php echo $objeto->ven_tipo; ?></td>
                                            <td>
                <?php
                $conv = new convertir();
                echo $conv->get_fecha_latina($objeto->ven_fecha);
                ?>
                                            </td>
                                            <td><?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></td>
                                            <td><?php echo $objeto->ven_vdo_id == 0 ? $objeto->ven_promotor : $this->nombre_persona_vendedor($objeto->ven_vdo_id); ?></td>
                                            <td><?php echo $objeto->uv_nombre; ?></td>
                                            <td><?php echo $objeto->man_nro; ?></td>
                                            <td><?php echo $objeto->lot_nro; ?></td>
                                            <td><?php echo $objeto->ven_moneda == 1 ? 'Bolivianos' : ($objeto->ven_moneda == 2 ? 'Dolares' : ''); ?></td>
                                            <td><?php echo $objeto->ven_superficie; ?></td>
                                            <td><?php echo $objeto->ven_metro; ?></td>
                                            <td><?php echo $objeto->ven_valor; ?></td>
                                            <td><?php echo $objeto->ven_decuento; ?></td>
                                            <td><?php echo $objeto->ven_monto; ?></td>
                                            <td><?php echo $objeto->ven_observacion; ?></td>
                <?php
                $mon = $objeto->ven_moneda;
                $total->superficie+=$objeto->ven_superficie;
                $total->{'valor_metro_' . $mon}+=$objeto->ven_metro;
                $total->{'valor_' . $mon}+=$objeto->ven_valor;
                $total->{'descuento_' . $mon}+=$objeto->ven_decuento;
                $total->{'monto_' . $mon}+=$objeto->ven_monto;
                ?>


                                        </tr>

                                            <?php
                                            $conec->siguiente();
                                        }
                                        ?>
                                </tbody>
                                <tfoot >
                                    <?php
                                    if ($num > 0) {
                                        ?>
                                        <tr>

                                            <td colspan="10" >Total Ventas en BS.</td>
                                            <td><?php echo $total->superficie; ?> m2</td>
                                            <td><?php echo number_format($total->valor_metro_1, 2) ?> Bs.</td>
                                            <td><?php echo number_format($total->valor_1, 2) ?> Bs.</td>
                                            <td><?php echo number_format($total->descuento_1, 2) ?> Bs.</td>
                                            <td><?php echo number_format($total->monto_1, 2) ?> Bs.</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="10" >Total Ventas en $us</td>
                                            <td>&nbsp;</td>
                                            <td><?php echo number_format($total->valor_metro_2, 2) ?> $us</td>
                                            <td><?php echo number_format($total->valor_2, 2) ?> $us</td>
                                            <td><?php echo number_format($total->descuento_2, 2) ?> $us</td>
                                            <td><?php echo number_format($total->monto_2, 2) ?> $us</td>
                                            <td>&nbsp;</td>
                                        </tr>
                <?php $tca = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='2015-11-11' and tca_mon_id=2"); ?>
                                        <tr>
                                            <td colspan="10" >Convertido a Bs</td>
                                            <td>&nbsp;</td>
                                            <td><?php echo number_format(($total->valor_metro_2 * $tca) + $total->valor_metro_1, 2) ?> $us</td>
                                            <td><?php echo number_format(($total->valor_2 * $tca) + $total->valor_1, 2) ?> $us</td>
                                            <td><?php echo number_format(($total->descuento_2 * $tca) + $total->descuento_1, 2) ?> $us</td>
                                            <td><?php echo number_format(($total->monto_2 * $tca) + $total->monto_1, 2) ?> $us</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="10" >Convertido a $us</td>
                                            <td>&nbsp;</td>
                                            <td><?php echo number_format(($total->valor_metro_2) + ($total->valor_metro_1 / $tca), 2) ?> $us</td>
                                            <td><?php echo number_format(($total->valor_2) + ($total->valor_1 / $tca), 2) ?> $us</td>
                                            <td><?php echo number_format(($total->descuento_2) + ($total->descuento_1 / $tca), 2) ?> $us</td>
                                            <td><?php echo number_format(($total->monto_2) + ($total->monto_1 / $tca), 2) ?> $us</td>
                                            <td>&nbsp;</td>
                                        </tr>
                <?php
            }
            ?>
                                </tfoot>
                                    <?php
                                }

                                if ($_POST['estado'] == "libres") {
                                    ?>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Uv</th>
                                        <th>Zona</th>
                                        <th>Nro Manzano</th>
                                        <th>Nro Lote</th>
                                        <th>Superficie m2</th>
                                        <th>Precio m2</th>
                                        <th>Precio del Terreno</th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $suma = 0;
            $cuotat = 0;
            $spla = 0;

            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();
//                                        if ($i % 20 == 0 && $i <> 0)
//                                            $this->dibujar_cabecera();
                ?>
                                        <tr>
                                            <td><?php echo ($i + 1); ?></td>
                                            <td><?php echo $objeto->uv_nombre; ?></td>
                                            <td><?php echo $objeto->zon_nombre; ?></td>
                                            <td><?php echo $objeto->man_nro; ?></td>
                                            <td><?php echo $objeto->lot_nro; ?></td>
                                            <td><?php
                        echo $objeto->lot_superficie;
                        $suma+=$objeto->lot_superficie;
                        ?></td>
                                            <td><?php echo number_format($objeto->zon_precio, 2, '.', ','); ?></td>
                                            <td>
                                                <?php
                                                echo number_format($objeto->lot_superficie * $objeto->zon_precio, 2, '.', ',');
                                                if ($objeto->zon_moneda == '1') {
                                                    echo " Bs.";
                                                } else {
                                                    echo ' $us.';
                                                }
                                                ?>
                                            </td>

                                        </tr>

                                                <?php
                                                $conec->siguiente();
                                            }
                                            ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td><?php echo "$suma <em>m2</em>"; ?></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
            <?php
        }

        if ($_POST['estado'] == "reservados") {
            ?>
                                <thead>
                                    <tr>
                                        <th>#</th>                                        
                                        <th>Uv</th>
                                        <th>Zona</th>
                                        <th>Nro Manzano</th>
                                        <th>Nro Lote</th>
                                        <th>Superficie m2</th>
                                        <th>Precio m2</th>
                                        <th>Precio Total</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Fecha Reserva</th>
                                        <th>Fecha Plazo</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $suma = 0;
            $cuotat = 0;
            $spla = 0;
            $sum_monto = 0;
            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();
//                                        if ($i % 20 == 0 && $i <> 0)
//                                            $this->dibujar_cabecera();

                $rojo = "";
                if ($objeto->res_plazo_fecha < date('Y-m-d') && $_POST[estado_reserva] == 'Pendiente') {
                    $rojo = 'style="color:red"';
                }
                ?>
                                        <tr>
                                            <td><?php echo ($i + 1); ?></td>

                                            <td><?php echo $objeto->uv_nombre; ?></td>
                                            <td><?php echo $objeto->zon_nombre; ?></td>
                                            <td><?php echo $objeto->man_nro; ?></td>
                                            <td><?php echo $objeto->lot_nro; ?></td>
                                            <td><?php
                        echo $objeto->lot_superficie;
                        $suma+=$objeto->lot_superficie;
                        ?></td>
                                            <td><?php echo number_format($objeto->zon_precio, 2, '.', ','); ?></td>
                                                <?php $monto = $objeto->lot_superficie * $objeto->zon_precio; ?>
                                            <td><?php
                                                echo number_format($monto, 2, '.', ',');
                                                if ($objeto->zon_moneda == '1') {
                                                    echo " Bs.";
                                                } else {
                                                    echo ' $us.';
                                                }
                                                ?></td>

                                            <td><?php echo $this->nombre_persona($objeto->res_int_id); ?></td>
                                            <td><?php echo $this->nombre_persona_vendedor($objeto->res_vdo_id); ?></td>
                                            <td><?php
                                $conv = new convertir();
                                echo $conv->get_fecha_latina($objeto->res_fecha);
                                ?></td>
                                            <td <?php echo $rojo; ?>>
                                                <?php
                                                echo $conv->get_fecha_latina($objeto->res_plazo_fecha);
                                                ?>
                                            </td>
                                            <td><?php
                                                if ($objeto->res_nota != '')
                                                    echo $objeto->res_nota;
                                                else
                                                    echo "&nbsp;";
                                                ?></td>
                                        </tr>


                <?php
                $sum_monto+=$monto;
                $conec->siguiente();
            }
            ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td><?php echo "$suma <em>m2</em>" ?></td>
                                        <td>&nbsp;</td>
                                        <td><?php echo $sum_monto; ?></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>

                                <div style="text-align: left; width: 65%">
                                    <img height="10" width="10" style="background-color: red; display: inline" />
                                    <p style="color: red; display: inline">      Lotes cuya Reserva Pendiente ha vencido el plazo.</p>
                                </div><br /><br />

            <?php
        }
        ?>


                            <?php
                            if ($_POST['estado'] == "bloqueados") {
                                ?>
                                <thead>
                                    <tr>
<!--                                        <th>#</th>
                                        <th>Uv</th>
                                        <th>Nro Manzano</th>
                                        <th>Nro Lote</th>
                                        <th>Superficie m2</th>
                                        <th>Precio m2</th>                                        
                                        <th>Precio Total</th>
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Nota</th>
                                        <th>Fecha</th>-->
                                        
                                        <th>#</th>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Uv</th>
                                        <th>Manzano</th>
                                        <th>Lote</th>                                        
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Nota</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $suma = 0;
            $cuotat = 0;
            $spla = 0;
            $total_precio = 0;
            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();
//                                        if ($i % 20 == 0 && $i <> 0)
//                                            $this->dibujar_cabecera();
                ?>
                                        <tr>
                                            <td><?php echo ($i + 1); ?></td>
                                            <td><?php echo $objeto->bloq_id; ?></td>
                                            <td><?php
                                            $conv = new convertir();
                                            echo $conv->get_fecha_latina($objeto->bloq_fecha);?>
                                            </td>
                                            <td><?php echo $objeto->bloq_hora; ?></td>
                                            <td><?php echo $objeto->uv_nombre; ?></td>
                                            <td><?php echo $objeto->man_nro; ?></td>
                                            <td><?php echo $objeto->lot_nro; ?></td>
                                            <!--<td><?php
//                        echo $objeto->lot_superficie;
//                        $suma+=$objeto->lot_superficie;
                        ?></td>-->
                                            <!--<td><?php // echo number_format($objeto->zon_precio, 2, '.', ','); ?></td>-->
<!--                                            <td><?php
//                                                echo number_format($objeto->lot_superficie * $objeto->zon_precio, 2, '.', ',');
//                                                if ($objeto->zon_moneda == '1') {
//                                                    echo " Bs.";
//                                                } else {
//                                                    echo ' $us.';
//                                                }
//                                                $total_precio += $objeto->lot_superficie * $objeto->zon_precio;
                                                ?></td>-->
                                            <td><?php echo $this->nombre_persona_vendedor($objeto->bloq_vdo_id); ?></td>
                                            <td><?php echo $this->nombre_persona($objeto->bloq_int_id); ?></td>
                                            <td><?php
                                if ($objeto->bloq_nota != '')
                                    echo $objeto->bloq_nota;
                                else
                                    echo "&nbsp;";
                                                ?></td>
                                            <td><?php echo $objeto->bloq_estado;?></td>
                                            <td><?php echo $objeto->bloq_usu_id;?></td>

                                        </tr>

                <?php
                $conec->siguiente();
            }
            ?>
                                </tbody>
<!--                                <tfoot>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th><?php // echo number_format($suma, 2, '.', ','); ?> m2.</th>
                                        <th><?php // echo number_format($total_precio, 2, '.', ','); ?> ($us)</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    <tr><td colspan="12">&nbsp;</td></tr>
                                </tfoot>-->
            <?php
        }
        ?>
                            <?php
                            if ($_POST['estado'] == "todo") {
                                ?>
                                <thead>
                                    <tr>    
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Uv</th>
                                        <th>Zona</th>
                                        <th>Nro Manzano</th>
                                        <th>Nro Lote</th>
                                        <th>Superficie m2</th>
                                        <th>Precio m2</th>
                                        <th>Precio Total</th>
                                        <th>Estado</th>
                                        <th>Cliente</th>
            <!--                                        <th>Norte</th>
                                        <th>Sur</th>
                                        <th>Este</th>
                                        <th>Oeste</th>-->
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $suma = 0;
            $cuotat = 0;
            $spla = 0;
            $tsuperficie = new stdClass();
            $tcantidad = new stdClass();
            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();
//                                        if ($i % 20 == 0 && $i <> 0)
//                                            $this->dibujar_cabecera();
                ?>
                                        <tr>
                                            <td><?php echo ($i + 1); ?></td>
                                            <td><?php echo $objeto->lot_codigo; ?></td>
                                            <td><?php echo $objeto->uv_nombre; ?></td>
                                            <td><?php echo $objeto->zon_nombre; ?></td>
                                            <td><?php echo $objeto->man_nro; ?></td>
                                            <td><?php echo $objeto->lot_nro; ?></td>
                                            <td><?php echo $objeto->lot_superficie; ?></td>
                                            <td><?php echo number_format($objeto->zon_precio, 2, '.', ','); ?></td>
                                            <td>
                <?php
                echo number_format($objeto->lot_superficie * $objeto->zon_precio, 2, '.', ',');
                if ($objeto->zon_moneda == '1') {
                    echo " Bs.";
                } else {
                    echo ' $us.';
                }
                ?>
                                            </td>
                                            <td><?php echo $objeto->lot_estado; ?></td>
                                            <td>
                                                <?php
                                                if ($objeto->lot_estado == 'Vendido') {
                                                    echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from venta,interno where ven_lot_id='$objeto->lot_id' and ven_estado in ('Pendiente','Pagado') and ven_int_id=int_id");
                                                } elseif ($objeto->lot_estado == 'Reservado') {
                                                    echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from reserva_terreno,interno where res_lot_id='$objeto->lot_id' and res_estado in ('Pendiente','Habilitado') and res_int_id=int_id");
                                                } elseif ($objeto->lot_estado == 'Bloqueado') {
                                                    echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from bloquear_terreno,interno where bloq_lot_id='$objeto->lot_id' and bloq_int_id=int_id");
                                                } else {
                                                    echo "&nbsp;";
                                                }
                                                ?>
                                            </td>
                                                <?php
                                                $tsuperficie->{$objeto->lot_estado}+=$objeto->lot_superficie;
                                                $tcantidad->{$objeto->lot_estado}+=1;
                                                ?>

                                        </tr>

                                            <?php
                                            $conec->siguiente();
                                        }
                                        ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="11">&nbsp;</th>

                                    </tr>
                                </tfoot>
            <?php
        }
        $cuadro = ($_POST['estado'] == "todo") ? TRUE : FALSE;
        ?>

                        </table>

                            <?php if ($cuadro) { ?>
                            <table class="tablaLista" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Disponible</th>
                                        <th>Reservado</th>
                                        <th>Bloqueado</th>
                                        <th>Vendido</th>
                                        <th class="tOpciones">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>                                    
                                        <td><b>Cantidad</b></td>
                                        <td><?php echo $tcantidad->Disponible * 1; ?></td>
                                        <td><?php echo $tcantidad->Reservado * 1; ?></td>
                                        <td><?php echo $tcantidad->Bloqueado * 1; ?></td>
                                        <td><?php echo $tcantidad->Vendido * 1; ?></td>
                                        <td><?php echo $ttotal_cant = array_sum((array) $tcantidad); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>Superficie</b></td>
                                        <td><?php echo $tsuperficie->Disponible * 1; ?></td>
                                        <td><?php echo $tsuperficie->Reservado * 1; ?></td>
                                        <td><?php echo $tsuperficie->Bloqueado * 1; ?></td>
                                        <td><?php echo $tsuperficie->Vendido * 1; ?></td>
                                        <td><?php echo array_sum((array) $tsuperficie); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>Porcentaje</b></td>
                                        <td><?php echo round($tcantidad->Disponible * 100 / $ttotal_cant * 1, 2) . ' %'; ?></td>
                                        <td><?php echo round($tcantidad->Reservado * 100 / $ttotal_cant * 1, 2) . ' %'; ?></td>
                                        <td><?php echo round($tcantidad->Bloqueado * 100 / $ttotal_cant * 1, 2) . ' %'; ?></td>
                                        <td><?php echo round($tcantidad->Vendido * 100 / $ttotal_cant * 1, 2) . ' %'; ?></td>
                                        <td><?php echo "100 %"; ?></td>
                                    </tr>
                                </tbody>
                            </table>
        <?php } ?>


                        <script>
                            $(".tabla_reporte").thfloat();
                        </script>
                    </center>
                    <br/><br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
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

    function nombre_persona($int_id) {
        $sql = "select concat(int_nombre,' ',int_apellido)as persona from interno where int_id='" . $int_id . "'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $nombre = '&nbsp;';
        if ($conec->get_num_registros() > 0) {
            $nombre = $conec->get_objeto()->persona;
        }
        return $nombre;
    }

    function nombre_persona_vendedor($vdo_id) {
        $conec = new ADO();

        $sql = "SELECT concat(int_nombre,' ',int_apellido)as persona FROM interno
				inner join vendedor on (vdo_int_id=int_id) 
				WHERE vdo_id='$vdo_id'";

        $nombre = '&nbsp;';

        $conec->ejecutar($sql);

        if ($conec->get_num_registros() > 0) {
            $nombre = $conec->get_objeto()->persona;
        }

        return $nombre;
    }

    function dibujar_cabecera() {
        if ($_POST['estado'] == "libres") {
            ?>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Uv</th>
                            <th>Nro Manzano</th>
                            <th>Nro Lote</th>
                            <th>Superficie m2</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
            <?php
        } else {
            if ($_POST['estado'] == "vendidos") {
                ?>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nro. Venta</th>
                                <th>Tipo de Venta</th>
                                <th>Fecha</th>
                                <th>Comprador</th>
                                <th>Vendedor</th>
                                <th>Uv</th>
                                <th>Manzano</th>
                                <th>Lote</th>
                                <th>Sup. m2</th>
                                <th>Precio del Terreno</th>
                                <th>Descuento</th>
                                <th>Precio de la Venta</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                <?php
            } else {
                if ($_POST['estado'] == "reservados") {
                    ?>
                            <thead>
                                <tr>
                                    <th>#</th>                                    
                                    <th>Uv</th>
                                    <th>Nro Manzano</th>
                                    <th>Nro Lote</th>
                                    <th>Superficie m2</th>
                                    <th>Precio</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Fecha Reserva</th>
                                    <th>Fecha Plazo</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                    <?php
                } else {
                    if ($_POST['estado'] == 'bloqueados') {
                        ?>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Uv</th>
                                        <th>Nro Manzano</th>
                                        <th>Nro Lote</th>
                                        <th>Superficie m2</th>
                                        <th>Precio($us)</th>
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Nota</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                        <?php
                    }
                }
            }
        }
    }

}
?>