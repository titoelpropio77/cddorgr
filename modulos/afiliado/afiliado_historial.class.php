<?php

class AFILIADO_HISTORIAL extends AFILIADO {

    function AFILIADO_HISTORIAL() {
        parent::__construct();
    }

    function historial() {
        if ($_POST) {
            $this->generar_historial($_POST, $_GET);
        } else {
            $this->frm_historial();
        }
    }

    function barra_de_impresion($data) {
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
                        <td><a onclick="javascript:importar_excel();" href="#" id="importar_excel"><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL"></a></td>
						<td>
                            <a href="javascript:location.reload(true);;">
                                <img src="images/actualizar.png" width="20" title="ACTUALIZAR"/>
                            <a/>
                        </td>
                       <td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'' . $data->url_retorno . '' . '' . '\';"></td></tr></table><br><br>
                  ';
    }

    function generar_historial($post, $get, $barra = TRUE) {
        
        
        
        $post = (object) $post;
        $get = (object) $get;
//        print_r($post);

        include_once 'clases/mlm.class.php';
        
        if ($_SESSION[id] == 'admin') {
            $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$get->id");
            $prof = $vendedor->vdo_nivel + 1;
            $nivel = 1000;
            $arr_ids_hijos_directos = MLM::obtener_red($vendedor->vdo_id, $nivel, FALSE, $prof, '', 'vendedor');

            echo "<pre>";
            print_r(implode(',',$arr_ids_hijos_directos));
            echo "</pre>";
        }

        $pdo_id = $post->pdo_id;
        $tabla = "comision";
        $data = new stdClass();
        $data->url_retorno = $this->link . "?mod=" . $this->modulo . "&tarea=" . $get->tarea . "&id=" . $get->id;
        
        if ($barra) {
            $this->barra_de_impresion($data);
        }
        $com_per = FUNCIONES::objeto_bd_sql("select * from comision_periodo where pdo_id=$pdo_id");

        if ($com_per === NULL) {
            $mensaje = "No existen bonos generados para el periodo proporcionado.";
            $this->formulario->ventana_volver($mensaje, $data->url_retorno, '', 'Error');
            return FALSE;
        }

        if ($com_per->pdo_usu_mod != NULL) {
            $fecha_emision = $com_per->pdo_fecha_mod;
        } else {
            $fecha_emision = $com_per->pdo_fecha_cre;
        }

        $date = DateTime::createFromFormat("Y-m-d H:i:s", $fecha_emision);
        $fecha_emision = $date->format("d/m/Y H:i:s");
        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
            inner join con_gestion on(pdo_ges_id=ges_id)
            where pdo_id=$pdo_id");
        $desc_periodo = strtoupper($periodo->pdo_descripcion) . " - " . strtoupper($periodo->ges_descripcion);
        $sql_com = "select vendedor.*,inte.*,a.ran_nombre as rango_actual,b.ran_nombre as rango_alcanzado from vendedor 
            inner join interno inte on(vdo_int_id=int_id)
            inner join rango a on (vdo_rango_actual=a.ran_id)
            inner join rango b on (vdo_rango_alcanzado=b.ran_id)
            where vdo_id=$get->id";
        
        
        $sql_com = "select vendedor.*,inte.*,b.ran_nombre as rango_alcanzado from vendedor 
            inner join interno inte on(vdo_int_id=int_id)
            
            inner join rango b on (vdo_rango_alcanzado=b.ran_id)
            where vdo_id=$get->id";
        
        $afiliado = FUNCIONES::objeto_bd_sql($sql_com);
        
        $rango_actual = FUNCIONES::objeto_bd_sql("select * from vendedor_rango
            inner join rango on (vran_ran_id=ran_id)
            where vran_vdo_id=$get->id 
                and vran_pdo_id=$pdo_id
                order by vran_ran_id desc limit 0,1");
        
        if ($rango_actual) {
            $afiliado->rango_actual = $rango_actual->ran_nombre;
        }
        ?>


        <div id="clone" style="display:none;"></div>
        <div id="contenido_reporte" style="clear:both;">
            <div style="float: left">            
                <table id="tab_cabecera">
                    <tr>
                        <td colspan="2"><b>REPORTE DE BONIFICACIONES</b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>&nbsp;</b></td>
                    </tr>
                    <tr>
                        <td><b>Fecha y Hora de Emision:</b></td><td><?php echo $fecha_emision; ?></td>
                    </tr>
                    <tr>
                        <td><b>Periodo:</b></td><td><?php echo $desc_periodo; ?></td>
                    </tr>
                    <tr>
                        <td><b>Afiliado:</b></td><td><?php echo utf8_decode(strtoupper($afiliado->int_nombre . " " . $afiliado->int_apellido)); ?>(<?php echo $afiliado->vdo_venta_inicial;?>)</td>
                    </tr>
                    <tr>
                        <td><b>Rango Alcanzado:</b></td><td><?php echo $afiliado->rango_alcanzado; ?></td>
                    </tr>
                    <tr>
                        <td><b>Rango Periodo:</b></td><td><?php echo ($afiliado->rango_actual)?$afiliado->rango_actual:'No Determinado'; ?></td>
                    </tr>

                    <tr>
                        <td><b>Agrupado Por:</b></td><td><?php echo ($post->agrupado_por == 'tipo_bono') ? "Tipo de Bono" : "Sin Agrupar"; ?></td>
                    </tr>
                </table>
                <style>
                    #tab_cabecera{
                        border: 1px black solid;
                        font-family: arial;
                        font-size: 12px;
                    }

                </style>   
            </div>
            <div style="clear:both;"></div>
            <br/><br/>
            <script>


                function importar_excel() {
                    var copy = $('#contenido_reporte').clone();

                    $('#clone').html(copy);


                    $('.tablaReporte').attr('border', '1');
                    window.open('data:application/vnd.ms-excel,' + escape($('#clone').html()));
                    e.preventDefault();
                    // window.open('data:application/vnd.ms-excel,' + escape(modificado));
                    // e.preventDefault();
                    $('.tablaReporte').attr('border', '0');
                }
            </script>
            <style>
                .derecha{
                    text-align: right !important;
                }
            </style>         
            <center>
                <?php
                $total_bonos = 0;
                if ($post->agrupado_por == 'tipo_bono') {
                    ?>
                    <h2>Bono de Inicio Rapido</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
            <!--                        <th class="asociado">
                            Asociado
                        </th>                        -->
                        <th>
                            Descripcion
                        </th>
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            Inicial(BIR + BVI) USD
                        </th>                        
                        <th>
                            %
                        </th>
                        <th>
                            BIR(USD)
                        </th>
                        </thead>
                        <tbody>
                            <?php
//                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
//                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
//                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
//                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
//                        inner join venta ven on (com.com_ven_id=ven.ven_id)
//                        inner join interno afil on (ven.ven_int_id=afil.int_id)
//                        inner join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
//                        where com.com_estado in('Pendiente','Pagado')
//                        and com.com_tipo='BIR'
//                        and com.com_pdo_id=$pdo_id
//                        and com.com_vdo_id=$get->id";
                            
                            
                            $sql = "select com.*,vdo.*,pat.*,ven.*,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)                        
                        where com.com_estado in('Pendiente','Pagado')
                        and com.com_tipo='BIR'
                        and com.com_pdo_id=$pdo_id
                        and com.com_vdo_id=$get->id";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
//                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <!--<td class="asociado"><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>-->
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo utf8_encode($com->afiliado); ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_inicial, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                                    
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
//                            $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="5"><b>Total:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <!--<td>&nbsp;</td>-->
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>

                    <h2>Bono Indirecto de Ventas</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
            <!--                        <th>
                            Asociado
                        </th>                        -->
                        <th>
                            Descripcion
                        </th>
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            Inicial(BIR + BVI) USD
                        </th>                        
                        <th>
                            %
                        </th>
                        <th>
                            BIV(USD)
                        </th>
                        </thead>
                        <tbody>
                            <?php
//                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
//                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
//                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
//                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
//                        inner join venta ven on (com.com_ven_id=ven.ven_id)
//                        inner join interno afil on (ven.ven_int_id=afil.int_id)
//                        inner join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
//                        where com.com_estado in('Pendiente','Pagado')
//                        and com.com_tipo='BVI'
//                        and com.com_pdo_id=$pdo_id
//                        and com.com_vdo_id=$get->id";
                            
                            $sql = "select com.*,vdo.*,pat.*,ven.*,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        
                        where com.com_estado in('Pendiente','Pagado')
                        and com.com_tipo='BVI'
                        and com.com_pdo_id=$pdo_id
                        and com.com_vdo_id=$get->id";

                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
//                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <!--<td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>-->
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo utf8_encode($com->afiliado); ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_inicial, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                                    
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                                $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="5"><b>Total:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <!--<td>&nbsp;</td>-->
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Bono Residual Abierto</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
            <!--                        <th>
                            Asociado
                        </th>                        -->
                        <th>
                            Descripcion
                        </th>                        
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            BRA TOTAL(USD)
                        </th>                        
                        <th>
                            %
                        </th>
                        <th>
                            BRA(USD)
                        </th>
                        </thead>
                        <tbody>
                            <?php
//                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
//                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
//                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
//                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
//                        inner join venta ven on (com.com_ven_id=ven.ven_id)
//                        inner join interno afil on (ven.ven_int_id=afil.int_id)
//                        inner join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
//                        where com.com_estado in('Pendiente','Pagado')
//                        and com.com_tipo='BRA'
//                        and com.com_pdo_id=$pdo_id
//                        and com.com_vdo_id=$get->id";
                            
                            $sql = "select com.*,vdo.*,pat.*,ven.*,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        
                        where com.com_estado in('Pendiente','Pagado')
                        and com.com_tipo='BRA'
                        and com.com_pdo_id=$pdo_id
                        and com.com_vdo_id=$get->id";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
//                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <!--<td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>-->
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo utf8_encode($com->afiliado); ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_bra, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                                    
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                                $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="5"><b>Total:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <!--<td>&nbsp;</td>-->
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Fondo Especial Diamante</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>                                                                        
                        <th>
                            %
                        </th>
                        <th>
                            FED(USD)
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "select * from $tabla 
                        inner join vendedor on (com_vdo_id=vdo_id)
                        inner join interno on (vdo_int_id=int_id)                        
                        where com_estado='Pendiente'
                        and com_tipo='FED'
                        and com_pdo_id=$pdo_id
                        and com_vdo_id=$get->id";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo utf8_encode($com->int_nombre . " " . $com->int_apellido); ?></td>                
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                                    
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="2"><b>Total:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <!--<td>&nbsp;</td>-->
                            </tr>
                        </tfoot>
                    </table>

                    <?php
                }

                if ($post->agrupado_por == 'sin_agrupar') {
                    ?>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
            <!--                        <th>
                            Asociado
                        </th>                        -->
                        <th>
                            Descripcion
                        </th>                        
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>

                        <th>
                            Importe(USD)
                        </th>
                                                
                        <th>
                            %
                        </th>
                        
                        <th>
                            Bono(USD)
                        </th>
                        </thead>
                        <tbody>
                            <?php
//                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
//                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
//                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
//                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
//                        inner join venta ven on (com.com_ven_id=ven.ven_id)
//                        inner join interno afil on (ven.ven_int_id=afil.int_id)
//                        inner join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
//                        where com.com_estado in('Pendiente','Pagado')
//                        and com.com_pdo_id=$pdo_id
//                        and com.com_vdo_id=$get->id";
                            
                            $sql = "select com.*,vdo.*,pat.*,ven.*,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        
                        where com.com_estado in('Pendiente','Pagado')
                        and com.com_pdo_id=$pdo_id
                        and com.com_vdo_id=$get->id";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
//                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                $monto_calculo = ($com->com_tipo == 'BRA')?$com->com_monto:$com->ven_res_anticipo;
                                ?>
                                <tr>
                                    <!--<td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>-->
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo utf8_encode($com->afiliado); ?></td>                                             
                                    <td class="derecha"><?php echo number_format($monto_calculo, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                                $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="5"><b>Total:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <!--<td>&nbsp;</td>-->
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                ?>
                <br/>
                <br/>
                <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                    <tfoot>
                    <tr>
                        <td class="derecha" colspan="5"><b>Total Bonos:</b></td>
                        <td class="derecha"><b><?php echo number_format($total_bonos, 2, '.', ','); ?></b></td>
                    </tr>
                    </tfoot>
                </table>
            </center>
            <br/><br/><hr></hr>
        </div>
        <?php
    }

    function frm_historial() {
        echo "frm_historial";
        $url = $this->link . '?mod=' . $this->modulo;
        $red = $url;
        $url.="&tarea=" . $_GET['tarea'];
        $url.='&id=' . $_GET['id'];
        $this->formulario->dibujar_titulo('VER HISTORIAL DE BONOS');
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript">
            function cargar_periodos(id) {
                if (true) {
                    console.log('entrando para ajax');
                    var valores = "tarea=periodos&gestion=" + id;
                    ejecutar_ajax('ajax_comisiones.php', 'periodos', valores, 'POST');
                } else {
                    console.log('no haciendo nada');
                }
            }

            function ver_bonos() {
                $('#frm_sentencia').submit();
            }

            var popup = null;
            function abrir_popup(html) {
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open('about:blank', 'reportes', 'left=100,width=1024,height=1024,top=0,scrollbars=yes');
                var extra = '';
                extra += '<html><head><title>Vista Previa</title><head>';
                extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra += '</head> <body> <div id=imprimir> <div id=status> <p>';

                extra += '<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();

            }

            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }

            function enviar(form) {
                var fecha = '<?php echo date('d/m/Y'); ?>';
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response !== "ok") {
                        $.prompt(dato.mensaje);
                    } else {
                        if (validar_formulario()) {
                            mostrar_ajax_load();
                            form.submit();
                            ocultar_ajax_load();
                        }
                    }
                });


            }

            function validar_formulario() {
                if ($('#gestion option:selected').val() === '') {
                    $.prompt("Seleccione la gestion.", {opacity: 0.8});
                    return false;
                }
                if ($('#pdo_id option:selected').val() === '') {
                    $.prompt("Seleccione el periodo.", {opacity: 0.8});
                    return false;
                }
                return true;
            }
        </script>
        <script src="js/util.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">				  
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">


                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Gesti&oacute;n<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <select id="gestion" name="gestion" onChange="javascript:cargar_periodos(this.value);">

                                    <?php
                                    if ($tipo == 'cargar') {
                                        $fun = new FUNCIONES();
                                        $fun->combo("select ges_id as id,ges_descripcion as nombre from con_gestion 
                                                    where ges_eliminado='No' and ges_estado='Abierto' 
                                                    and ges_id='{$_POST[gestion]}'", "");
                                    } else {
                                        ?>
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = new FUNCIONES();
                                        $fun->combo("select ges_id as id,ges_descripcion as nombre from con_gestion 
                                                    where ges_eliminado='No' and ges_estado='Abierto'", $_SESSION[ges_id]);
                                        ?>
                                        <script>
                var id = $('#gestion option:selected').val();
                cargar_periodos(id);
                                        </script>    
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Periodo Mensual<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <div id="periodos">
                                    <select id="pdo_id" name="pdo_id">
                                        <?php
                                        if ($tipo == 'cargar') {
                                            $fun = new FUNCIONES();
                                            $fun->combo("select pdo_id as id,pdo_descripcion as nombre from con_periodo
                                                    where pdo_eliminado='No'
                                                    and pdo_id='{$_POST[pdo_id]}'", "");
                                        } else {
                                            
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Agrupado por:<span class="flechas1">*</span></div>
                            <div id="CajaInput">

                                <select id="agrupado_por" name="agrupado_por">
                                    <option value="sin_agrupar">Sin Agrupar</option>
                                    <option value="tipo_bono">Por Tipo Bono</option>
                                </select>

                            </div>
                        </div>
                        <!--Fin-->

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <input type="button" class="boton" name="generar" value="Generar" onclick="javascript:ver_bonos();">
                            </div>
                        </div>


                    </div>
                    <div id="ContenedorDiv" hidden="">
                        <div id="CajaBotones">
                            <center>
                                <?php if (!($ver)) { ?>
                                    <input type="button" class="boton" name="guardar" value="Guardar" onclick="enviar(this.form);">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php } else { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php } ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

}
?>
