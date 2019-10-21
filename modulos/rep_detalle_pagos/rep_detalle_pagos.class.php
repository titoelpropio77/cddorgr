<?php

class rep_detalle_pagos extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function rep_detalle_pagos() {
        //permisos
        $this->ele_id = 185;

        $this->busqueda();

        //fin permisos

        $this->coneccion = new ADO();

        $this->link = 'gestor.php';

        $this->modulo = 'rep_detalle_pago';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('RESUMEN DE VENTAS');

        $this->usu = new USUARIO;
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
                    var valores = "tarea=manzanos&urb=" + id;

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
                function reset_fecha_inicio()
                {
                    document.frm_sentencia.inicio.value = '';
                }
                function reset_fecha_fin()
                {
                    document.frm_sentencia.fin.value = '';
                }
            </script>
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->	
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=rep_detalle_pagos" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
                                <div id="CajaInput">
                                    <select style="width:200px;" name="ven_urb_id" class="caja_texto">
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
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d-m-Y'); ?>" type="text">
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
                                    <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo date('d-m-Y'); ?>" type="text">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_fin();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            
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
                    <script>
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=represumen_venta\';"></td></tr></table><br><br>
				'; ?>



                <div id="contenido_reporte" style="clear:both;";>
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
                                            <h3>
                                                <center>RESUMEN DE VENTAS</br>URBANIZACION <?php echo strtoupper($this->nombre_uv()); ?></center>
                                            </h3>
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

        $cad = "";

        if ($_POST['ven_urb_id'] <> "")
            $cad = "urb_id='" . $_POST['ven_urb_id'] . "' ";

        if ($_POST['inicio'] <> "") {
            $fecha.=" and vpag_fecha_pago >= '" . $conversor->get_fecha_mysql($_POST['inicio']) . "' ";

            if ($_POST['fin'] <> "") {
                $fecha.=" and vpag_fecha_pago <='" . $conversor->get_fecha_mysql($_POST['fin']) . "' ";
            }
        } else {
            if ($_POST['fin'] <> "") {
                $fecha.=" and vpag_fecha_pago <='" . $conversor->get_fecha_mysql($_POST['fin']) . "' ";
            }
        }



        
        ?>
                        <table class="tablaLista" style="100%" cellpadding="0" cellspacing="0" >
                            <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Venta</th>
                                    <th>URBANIZACION</th>
                                    <td>TIPO DE COBRO</td>
                                    <td>COD/USU/CAJA</td>
                                    <td>Fecha de cobro</td>
                                    <td>Lotes</td>
                                    <td>Dias Vencidos  a la fecha de pago</td>
                                    <td>ESTADO CARTERA </td>
                                    <td>CONCEPTO DE PAGO</td>
                                    <td>Cliente</td>
                                    <td>CAPITAL PAG.</td>
                                    <td>INTERES. PAGADO</td>
                                    <td>FORM. PAGA.</td>
                                    <td>DESCUENTO</td>
                                    <td> T. Pag </td>

                                </tr>
                            </thead>
                            <tbody>
        <?php
        
        $sql = "SELECT 
				*
				FROM 
				venta_pago
				inner join venta on (vpag_ven_id=ven_id)
				inner join interno on (ven_int_id=int_id)
				inner join urbanizacion on (ven_urb_id=urb_id)
				where " . $cad . $fecha . "
				order by vpag_fecha_pago asc";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();
        
        $suma = 0;
        $suma_ci = 0;
        $cuotat = 0;
        $spla = 0;
        $suma_monto = 0;
        
        $mora_ejec=5;
        
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
                                        <td><?php echo $objeto->urb_nombre; ?></td>
                                        <?php $fpagos=  FUNCIONES::lista_bd_sql("select * from con_pago where fpag_tabla='venta_pago' and fpag_estado='Activo' and fpag_tabla_id=$objeto->vpag_id");?>
                                        <?php 
                                        $formas='';
                                        foreach ($fpagos as $fpag) {
                                            $formas.=$fpag->fpag_forma_pago;
                                        };?>
                                        <td><?php echo $formas; ?></td>
                                        <td><?php echo FUNCIONES::get_fecha_latina($objeto->ven_fecha); ?></td>
                                        <td><?php echo $objeto->ven_res_anticipo; ?></td>
                                        <td><?php echo $objeto->ven_monto_efectivo; ?></td>
                                        <?php $pagado=  $this->total_pagado($objeto->ven_id);?>
                                        <td><?php echo $saldo=$objeto->ven_monto_efectivo-$pagado->capital-$pagado->descuento+$pagado->incremento; ?></td>
                                        <td><?php echo $pagado->capital*1; ?></td>
                                        <td><?php echo $pagado->descuento*1; ?></td>
                                        <td><?php echo $pagado->incremento*1; ?></td>
                                        
                                        <td><?php echo $objeto->uv_nombre; ?></td>
                                        <td><?php echo $objeto->man_nro; ?></td>
                                        <td><?php echo $objeto->lot_nro; ?></td>
                                        <td><?php echo $objeto->lot_superficie; ?></td>
                                        <td><?php echo "$objeto->int_nombre $objeto->int_apellido"; ?></td>
                                        <td><?php echo "$objeto->int_ci"; ?></td>
                                        <td><?php echo "$objeto->ven_ubicacion"; ?></td>
                                        <td><?php echo "$objeto->int_direccion"; ?></td>
                                        <td><?php echo "$objeto->int_telefono"; ?></td>
                                        <td><?php echo "$objeto->int_celular"; ?></td>
                                        <?php $moras=  $this->estado_moras($objeto->ven_id);?>
                                        <?php 
                                        $upago= FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$objeto->ven_id' and vpag_estado='Activo' order by vpag_id desc limit 1");
                                        if(!$upago){
                                            $upago=new stdClass();
                                            $upago->vpag_fecha_valor=$objeto->ven_fecha;
                                        }
                                        ?>
                                        <td><?php echo $moras->num_mora==0?'Vigente':($moras->num_mora<$mora_ejec?'Mora':'Ejecucion'); ?></td>
                                        <td><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor); ?></td>
                                        <td><?php echo "***"; ?></td>
                                        <td><?php echo $upago->vpag_fecha_pago?FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago):'00/00/0000'; ?></td>
                                        <td><?php echo $moras->dias_dif; ?></td>
                                        <td><?php echo $objeto->ven_cuota; ?></td>
                                        <td><?php echo $objeto->ven_plazo; ?></td>
                                        
                                        <td><?php echo $cuotas_pag=  FUNCIONES::atributo_bd_sql("select count(*) from interno_deuda where ind_tabla='venta' and ind_tabla_id='$objeto->ven_id' and ind_estado='Pagado'")*1; ?></td>
                                        <td><?php echo $plazo_rest=($objeto->ven_plazo-$cuotas_pag)*1; ?></td>
                                        
                                        <td><?php echo "***"; ?></td>
                                        <td><?php echo $moras->num_mora; ?></td>
                                        <td><?php echo "***"; ?></td>
                                        
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
<!--                                    <tr style="font-size:12px;">
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>

                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;<?php // echo number_format($suma_monto, 2, ',', '.'); ?> $us&nbsp;</th>

                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>

                                    </tr>-->
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
                <br>	
        <?php
    }

    function total_pagado($ven_id) {
//        $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, 
//                            sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento ,
//                            sum(ind_costo_pagado) as costo
//                            from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
//                    ";
//        $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
        $pagado = FUNCIONES::total_pagado($ven_id);
        return $pagado;
    }

    function estado_moras($ven_id) {
        $fecha=date('Y-m-d');
        $sql_pag = "select * from interno_deuda where ind_estado='Pendiente'  and ind_tabla='venta' and ind_tabla_id=$ven_id and ind_fecha_programada<'$fecha'";
        $lista = FUNCIONES::lista_bd_sql($sql_pag);
        $dias_dif=0;
        if(count($lista)>0){
            $id=$lista[0];
            $dias_dif=  FUNCIONES::diferencia_dias($id->ind_fecha_programada, $fecha);
        }
        
        return (Object) array('num_mora'=>  count($lista),'dias_dif'=>$dias_dif);
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