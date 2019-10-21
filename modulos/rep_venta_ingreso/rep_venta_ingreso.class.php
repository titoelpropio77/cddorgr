<?php

class REP_VENTA_INGRESO extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function REP_VENTA_INGRESO() {
        //permisos
        $this->ele_id = 201;
        $this->busqueda();
        //fin permisos
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'rep_venta_ingreso';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('INGRESO DE VENTAS');
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
            <?php } ?>
            <script>
                function cargar_manzano(id) {
                    var valores = "tarea=manzanos&urb=" + id;
                    ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
                }

                function enviar_formulario() {
                    var fini= $('#inicio').val();
                    var ffin=$('#fin').val();
                    if(fini==='' || ffin===''){
                        $.prompt('Ingrese la Fecha Inicio y la Fecha Fin', {opacity: 0.8});
                        return;
                    }
                    fini=fecha_mysql(fini);
                    ffin=fecha_mysql(ffin);
                    var dif_dias=diferencia_dias(fini,ffin);
                    
//                    var urbanizacion = document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                    if (dif_dias<=366) {
                        document.frm_sentencia.submit();
                    } else {
                        $.prompt('El Rango de Fechas no debe sobrepar el A&ntilde;o Calendario', {opacity: 0.8});
                    }
                }
                function reset_fecha_inicio() {
                    document.frm_sentencia.inicio.value = '';
                }
                function reset_fecha_fin() {
                    document.frm_sentencia.fin.value = '';
                }
            </script>

            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <script src="js/chosen.jquery.min.js"></script>
            <script src="js/util.js"></script>
            <link href="css/chosen.min.css" rel="stylesheet"/>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=rep_venta_ingreso" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_urb_id" class="caja_texto">
                                        <option value="">-- Todos --</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d-m-Y'); ?>" type="text" autocomplete="off">
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo date('d-m-Y'); ?>" type="text" autocomplete="off">
                                </div>		
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Tipo</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        <select style="width:200px;" name="tipo" class="caja_texto">
                                            <option value="">Todos</option>
                                            <option value="Contado">Contado</option>
                                            <option value="Credito">Credito</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Vendedor</div>
                                <div id="CajaInput">
                                    <select style="width:400px;" name="ven_vdo_id" id="ven_vdo_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                        <option value=""></option>
                                        <?php
                                        $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor,interno where vdo_estado='Habilitado' and vdo_int_id=int_id;", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Sucursal</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_suc_id" id="ven_suc_id" class="caja_texto" >
                                        <option value="">-- Seleccione --</option>
                                        <?php
                                        $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal", 0);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Estado</div>
                                <div id="CajaInput">
                                    <div id="manzano">
                                        <select style="width:200px;" name="estado" class="caja_texto">
                                            <option value="">Todos</option>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Pagado">Pagado</option>
                                            <option value="Pendiente_Pagado">Pendiente - Pagado</option>
                                            <option value="Retenido">Retenido</option>
                                            <option value="Anulado">Anulado</option>
                                            <option value="Cambiado">Cambiado</option>
                                            <option value="Fusionado">Fusionado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
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
                    <script>
                    $('#ven_vdo_id').chosen({
                        allow_single_deselect: true
                    })
            //                    .change(function(){
            //                        
            //                    })
                            ;
                    jQuery(function($) {
                        $("#inicio").mask("99/99/9999");
                        $("#fin").mask("99/99/9999");
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

                $features = "'left=100,width=900,height=500,top=0,scrollbars=yes'";

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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=rep_venta_ingreso\';"></td></tr></table><br><br>
				'; ?>


                <script src="js/jquery.thfloat-0.7.2.min.js"></script>
                <div id="contenido_reporte" style="clear:both;">
                    <center>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="30%" >
                                    </br><strong><?php echo _nombre_empresa; ?></strong></br>

                                    <?php echo _datos_empresa; ?></br></br>
                                </td>
                                <td  width="40%" >
                                    <p align="center" >
                                        <strong>
                                            
                                        </strong>
                                    </p>
                                    <p align="center">
                                        <BR><?php if ($_POST['inicio'] <> "") echo '<strong>Del:</strong> ' . date('d/m/Y', strtotime($conversor->get_fecha_mysql($_POST['inicio']))) ?><?php if ($_POST['fin'] <> "") echo ' <strong>Al:</strong> ' . date('d/m/Y', strtotime($conversor->get_fecha_mysql($_POST['fin']))) ?>
                                    </p>
                                    <?php if ($_POST['tipo'] != '') { ?>
                                        <br />
                                        <p align="center">
                                            <strong>Tipo de Venta:</strong><?php echo $_POST['tipo']; ?>
                                        </p>
                                    <?php } ?>

                                    <?php if ($_POST['estado'] != '') { ?>
                                        <br />
                                        <p align="center">
                                            <strong>Estado:</strong><?php echo $_POST['estado']; ?>
                                        </p>
                                    <?php } ?>
                                </td>
                                <td  width="30%" ><div align="right"></br><img src="imagenes/micro.png" /></div><br/><br/></td>
                            </tr>
                        </table>
                        <?php
                        $conec = new ADO();
                        $filtro = "";
                        if ($_POST['ven_urb_id'] <> "") {
                            $filtro .= " and urb_id='" . $_POST['ven_urb_id'] . "' ";
                        } else {
//                            FORMULARIO::mostrar_mensaje("Seleccione la Urbanizacion", 'Error');
//                            return;
                        }
                        if ($_POST[ven_vdo_id]){
                            $filtro .= " and ven_vdo_id='$_POST[ven_vdo_id]'";
                        }
                        if ($_POST[ven_suc_id]){
                            $filtro .= " and ven_suc_id='$_POST[ven_suc_id]'";
                        }
                        
                        if ($_POST['inicio'] <> "") {
                            $filtro.=" and ven_fecha >= '" . $conversor->get_fecha_mysql($_POST['inicio']) . "' ";

                            if ($_POST['fin'] <> "") {
                                $filtro.=" and ven_fecha <='" . $conversor->get_fecha_mysql($_POST['fin']) . "' ";
                            }
                        } else {
                            if ($_POST['fin'] <> "") {
                                $filtro.=" and ven_fecha <='" . $conversor->get_fecha_mysql($_POST['fin']) . "' ";
                            }
                        }

                        if ($_POST['tipo'] <> "")
                            $filtro = " and ven_tipo='" . $_POST['tipo'] . "'";

                        if ($_POST['estado'] <> "") {
                            if ($_POST['estado'] == 'Pendiente_Pagado')
                                $filtro = " and (ven_estado ='Pendiente' or ven_estado ='Pagado')";
                            else
                                $filtro = " and ven_estado='" . $_POST['estado'] . "'";
                        }

                        $sql = "SELECT 
				urb_id,ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,ven_lot_id,ven_lot_ids,
                                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,urb_nombre,ven_res_anticipo,
                                    ven_cuota,int_nombre,int_apellido,suc_nombre,ven_vdo_id
				FROM 
				venta
				inner join ter_sucursal on (ven_suc_id=suc_id)
				inner join interno on (ven_int_id=int_id)
				inner join urbanizacion on (ven_urb_id=urb_id)
				where 1 $filtro
				order by int_nombre,int_apellido asc";
//        echo "$sql;<br>";

                        $conec->ejecutar($sql);

                        $num = $conec->get_num_registros();
                        ?>
                        <table class="tablaLista" style="100%" cellpadding="0" cellspacing="0" >
                            <thead>
                                <tr>
                                    <th>#</th>
                    <!--<th>Nro Venta</th>-->
                                    <th>Venta</th>
                                    <th>Titular</th>
                                    <th>Urbanizacion</th>
                                    <th>Terreno</th>
                                    <th>Tipo</th>
                                    <th>Moneda</th>
                                    <th>Monto</th>
                                    <th>Reserva</th>
                                    <th>F. Venta</th>
                                    <th>Vendedor</th>
                                    <th>Comision</th>
                                    <th>Sucursal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $suma = 0;
                                $suma_ci = 0;
                                $cuotat = 0;
                                $spla = 0;
                                $suma_monto = 0;
                                $suma_anticipo = 0;
                                $total=new stdClass();
                                for ($i = 0; $i < $num; $i++) {
                                    $objeto = $conec->get_objeto();

                                    if ($objeto->ven_meses_plazo <> 0) {
                                        if ($objeto->ven_moneda == '1') {
                                            //$cuota_bs=round(($objeto->ven_monto - $objeto->ven_cuota_inicial)/$objeto->ven_meses_plazo,2);
                                            //$cuota_sus=round((($objeto->ven_monto - $objeto->ven_cuota_inicial)/$objeto->ven_meses_plazo)/$objeto->ven_tipo_cambio,2);
                                            $cuota_bs = round($objeto->ven_cuotam_aux, 2);
                                            $cuota_sus = round(($objeto->ven_cuotam_aux / $objeto->ven_tipo_cambio), 2);
                                        } else {
                                            $cuota_sus = round($objeto->ven_cuotam_aux, 2);
                                            $cuota_bs = round(($objeto->ven_cuotam_aux * $objeto->ven_tipo_cambio), 2);
                                        }
                                    } else {
                                        $cuota_bs = 0;
                                        $cuota_sus = 0;
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo ($i + 1); ?></td>
                                        <td><?php echo $objeto->ven_id; ?></td>
                                        <td><?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></td>
                                        <td><?php echo $objeto->urb_nombre; ?></td>
                                        <td><?php echo $this->ge_lote($objeto->ven_lot_id,$objeto->ven_lot_ids) ; ?></td>
                                        <td>
                                            <?php 
                                            if ($objeto->ven_tipo == 'Credito')
                                                echo 'Credito Directo';
                                            else
                                                echo $objeto->ven_tipo;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($objeto->ven_moneda == '1')
                                                echo "Bolivianos";
                                            else
                                                echo "Dolares";
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $objeto->ven_monto; ?>
                                        </td>
                                        <td>
                                            <?php echo $objeto->ven_res_anticipo;
                                            ?>
                                        </td>
                                    <td><?php echo $conversor->get_fecha_latina($objeto->ven_fecha); ?></td>
                                    <?php $comision = FUNCIONES::objeto_bd_sql("select * from comision,vendedor,interno where com_vdo_id=vdo_id and vdo_int_id=int_id and com_ven_id='$objeto->ven_id' and com_vdo_id='$objeto->ven_vdo_id'");?>
                                    <td><?php echo "$comision->int_nombre $comision->int_apellido";?></td>
                                    <td><?php echo $comision->com_monto; ?></td>
                                    <td><?php echo $objeto->suc_nombre; ?></td>
                                    
                                    <?php
                                        $mon=$objeto->ven_moneda;
                                        $total->{'monto_'.$mon}+=$objeto->ven_monto;
                                        $total->{'anticipo_'.$mon}+=$objeto->ven_res_anticipo;
                                    ?>
                                    </tr>

                                    <?php
                                    $conec->siguiente();
                                }
                                ?>
                            </tbody>
                            <tfoot>
        <?php
        if ($num > 0) {
            ?>
                                        <tr>
                                            <td colspan="7" >Total Ventas en Bs.</td>
                                            <td><?php echo number_format($total->monto_1, 2) ?> Bs.</td>
                                            <td><?php echo number_format($total->anticipo_1, 2) ?> Bs.</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="7" >Total Ventas en $us.</td>
                                            <td><?php echo number_format($total->monto_2, 2) ?> Bs.</td>
                                            <td><?php echo number_format($total->anticipo_2, 2) ?> Bs.</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <?php $tca=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='2015-11-11' and tca_mon_id=2");?>
                                        <tr>
                                            <td colspan="7" >Convertido a Bs</td>
                                            <td><?php echo number_format(($total->monto_2*$tca)+$total->monto_1, 2); ?> Bs.</td>
                                            <td><?php echo number_format(($total->anticipo_2*$tca)+$total->anticipo_1, 2); ?> Bs.</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="7" >Convertido a Bs</td>
                                            <td><?php echo number_format(($total->monto_2)+($total->monto_1/$tca), 2) ?> $us</td>
                                            <td><?php echo number_format(($total->anticipo_2)+($total->anticipo_1/$tca), 2) ?> $us</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                            <?php
                        }
                        ?>
                            </tfoot>	
                        </table>
                        <?php
                        if ($num == 0) {
                            echo "<center>No se encontraron registros</center>";
                        }
                        ?>
                    </center>
                    <br/><br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
                </div>
                    <script>
                        $(".tablaLista").thfloat();
                    </script>
                <br>
                <?php
            }

            function ge_lote($lot_id="",$lot_ids="") {
                if($lot_id || $lot_ids){
                    if($lot_id){
                        $lote=  FUNCIONES::objeto_bd_sql("select * from lote, manzano where lot_man_id=man_id and lot_id='$lot_id'");
                        return "M$lote->man_nro:L$lote->lot_nro";
                    }elseif($lot_ids){
                        $lotes =  FUNCIONES::lista_bd_sql("select * from lote, manzano where lot_man_id=man_id and lot_id in ($lot_ids)");
                        $str_lotes="";
                        $i=0;
                        foreach ($lotes as $lote) {
                            if($i>0){
                                $str_lotes.=", ";
                            }
                            $str_lotes.="M$lote->man_nro:L$lote->lot_nro";
                            $i++;
                        }
                        return $str_lotes;
                    }
                }else{
                    return "";
                }
            }
            function obtener_comisionados($ven_id) {
                $sql = "select com_vdo_id, com_monto, com_moneda from comision where com_ven_id = $ven_id and com_estado!='Anulado'";
                $conec = new ADO();
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $cad = '';
                if ($num > 0) {
                    $cad = '';
                    for ($i = 0; $i < $num; $i++) {
                        $com = $conec->get_objeto();
                        $nombre = $this->nombre_vendedor($com->com_vdo_id);
                        $monto = $com->com_monto;
                        if ($com->com_moneda == '1') {
                            $mon = " Bs.";
                        } else {
                            $mon = ' $us.';
                        }
                        $cad .= $nombre . " (" . $monto . ")<br>";

                        $conec->siguiente();
                    }
                }
                return $cad;
            }

            function nombre_vendedor($vdo_id) {
                $conec = new ADO();

                $sql = "select int_nombre,int_apellido from vendedor inner join interno on (vdo_int_id=int_id and vdo_id='$vdo_id')";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->int_nombre . ' ' . $objeto->int_apellido;
            }

            function nombre_uv() {
                $conec = new ADO();

                $sql = "select urb_nombre from urbanizacion where urb_id='" . $_POST['ven_urb_id'] . "'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->urb_nombre;
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

            function obtener_id_vendedor($id_interno) {
                $conec = new ADO();

                $sql = "SELECT vdo_id,vdo_int_id FROM vendedor WHERE vdo_int_id=$id_interno";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->vdo_id;
            }

            function obtener_id_vendedor_tbl_comision($id_venta) {
                $conec = new ADO();

                $sql = "SELECT * from comision
		WHERE com_ven_id=$id_venta";

                echo $sql;

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->vdo_id;
            }

            function nombre_persona($id_interno) {
                $conec = new ADO();

                $sql = "select int_nombre,int_apellido from interno where int_id=$id_interno";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->int_nombre . ' ' . $objeto->int_apellido;
            }

            function montopagado($id) {
                $conec = new ADO();
                $sql = "select sum(ind_monto) as monto from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->monto;
            }

        }
        ?>