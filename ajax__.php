<?php
date_default_timezone_set("America/La_Paz");
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
function cuota($monto, $interes, $meses) {
    if ($interes > 0) {
        $interes /= 100;

        $interes /= 12;

        $power = pow(1 + $interes, $meses);

        return ($monto * $interes * $power) / ($power - 1);
    } else {
        return round($monto / $meses, 2);
    }
}

if (isset($_POST['tid'])) {
    if (isset($_POST['t'])) {
        if ($_POST['t'] == 1)
            cargar_cuentas();
        if ($_POST['t'] == 2)
            cargar_plantilla();
        if ($_POST['t'] == 3)
            cargar_cuentas_tabla();
        if ($_POST['t'] == 4)
            cargar_cajeros();
        if ($_POST['t'] == 5)
            cargar_cuentas2();
        if ($_POST['t'] == 6)
            cargar_cuentas3();
        if ($_POST['t'] == 7)
            cargar_cuentas4();
    }
}
else
if (isset($_POST['pid'])) {
    cargar_plantilla_detalle();
}

if ($_POST['tarea'] == 'vcuotas')
    venta_cuotas();

if ($_POST['tarea'] == 'vcuotas_prod')
    venta_cuotas_producto();

if ($_POST['tarea'] == 'grupo')
    cargar_grupos();

if ($_POST['tarea'] == 'uv')
    cargar_uv();

if ($_POST['tarea'] == 'manzanos')
    cargar_manzanos();

if ($_POST['tarea'] == 'lotes')
    cargar_lotes();

if ($_POST['tarea'] == 'plan_pagos')
    generar_plan();
if ($_POST['tarea'] == 'rev_ufecha_prog')
    revisar_ufecha_programada();

if ($_POST['tarea'] == 'cuota_pagos')
    cuota_pagos();

if ($_POST['tarea'] == 'fechas')
    generar_fechas();

if ($_POST['tarea'] == 'cargar_tareas')
    cargar_tareas();

if ($_POST['tarea'] == 'cuota_mensual_amortizar')
    cargar_cuota_mensual_amortizar();

if ($_POST['tarea'] == 'manzanos_reporte')
    cargar_manzanos_reporte();

if ($_POST['tarea'] == 'tipo_cambio_cv')
    obtener_tipo_cambio_cv();

if ($_POST['tarea'] == 'actualizar_nombre_urb')
    actualizar_nombre_urb();

if ($_POST['tarea'] == 'plan_pagos_cuentasx')
    generar_plan_cuentasx();

if ($_POST['tarea'] == 'revisar_anticipos')
    posible_contretar_venta();
if ($_POST['tarea'] == 'buscar_lote')
    buscar_lote();
if ($_POST['tarea'] == 'buscar_venta')
    buscar_venta();
if ($_POST['tarea'] == 'lista_ventas')
    buscar_lista_ventas();
if ($_POST['tarea'] == 'vista_lotes')
    vista_lotes();

function cargar_tareas() {
    ?>
    <select name="tarea" class="caja_texto">
        <?php
        $conec = new ADO();

        $sql = "SELECT tar_nombre
				FROM 
				ad_usuario
				inner join ad_permiso on (usu_gru_id = pmo_gru_id)
				inner join ad_tarea on (pmo_tar_id=tar_id)
				WHERE 
				usu_id='" . $_POST['usuario'] . "' and 
				pmo_ele_id='" . $_POST['modulo'] . "'
				";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            echo '<option value="' . $objeto->tar_nombre . '">' . $objeto->tar_nombre . '</option>';

            $conec->siguiente();
        }
        ?>
    </select>
    <?php
}

function revisar_ufecha_programada() {
    $saldo=$_POST['saldo_financiar'];
    $nro_cuota_inicio=$_POST[nro_inicio];
    if(!$nro_cuota_inicio){
        $nro_cuota_inicio=1;
    }
    
    if ($_POST['cuota_mensual'] == "" && $_POST['meses_plazo'] <> "") {// plazo
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'plazo',
            'plazo'=>$_POST[meses_plazo],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );            
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
    } elseif ($_POST['cuota_mensual'] != "" && $_POST['meses_plazo'] == "" ) { //cuota        
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'cuota',
            'cuota'=>$_POST['cuota_mensual'],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
        

    } elseif ($_POST['cuota_mensual'] != "" && $_POST['meses_plazo'] != "" ) { //cuota        
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'cuota',
            'cuota'=>$_POST['cuota_mensual'],
            'plazo'=>$_POST[meses_plazo],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
    }
    $fecha_ven='9999-99-99';
    if($_POST[ven_id]){
        $fecha_ven =  FUNCIONES::atributo_bd_sql("select ven_ufecha_prog as campo from venta where ven_id='$_POST[ven_id]'");
    }
    $ufila=$list_cuotas[count($list_cuotas)-1];
    if($fecha_ven<$ufila->fecha){
        echo '{"type":"error", "msj":"El Resultado del Plan de Pago da la ultima fecha programada mayor a la Fecha de Vencimiento de la venta"}';
    }else{
        echo '{"type":"success", "msj":"ok"}';
    }
}
function generar_plan() { ?>
    <center>
        <h3>PLAN DE PAGO</h3>
        <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Cuota</th>
                    <th>Moneda</th>
                    <th>Fecha de Pago</th>
                    <th>Dias Int.</th>
                    <th>Interes</th>
                    <th>Capital</th>
                    <th>Cuota</th>
                    <th>Form.</th>
                    <th>Monto a Pagar</th>
                    <th>Saldo</th>
                </tr>							
            </thead>
            <tbody>
    <?php
    // saldo financiar,meses plazo, interes,fecha_pri_cuota,moneda
    //echo FUNCIONES::print_pre($_POST);
    if (isset($_POST[urbanizacion])) {
        echo "<p style='color:white'>urb:$_POST[urbanizacion] - multinivel:$_POST[multinivel]</p>";
        $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion 
            where urb_id={$_POST[urbanizacion]}");
        
        $val_form = $urbanizacion->urb_val_form;
    }    
    
    $saldo=$_POST['saldo_financiar'];
    $nro_cuota_inicio=$_POST[nro_inicio];
    if(!$nro_cuota_inicio){
        $nro_cuota_inicio=1;
    }
    
    if ($_POST['cuota_mensual'] == "" && $_POST['meses_plazo'] <> "") {// plazo
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'plazo',
            'plazo'=>$_POST[meses_plazo],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );            
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
    } elseif ($_POST['cuota_mensual'] != "" && $_POST['meses_plazo'] == "" ) { //cuota        
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'cuota',
            'cuota'=>$_POST['cuota_mensual'],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
        

    } elseif ($_POST['cuota_mensual'] != "" && $_POST['meses_plazo'] != "" ) { //cuota        
        $data=array(
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'tipo'=>'cuota',
            'cuota'=>$_POST['cuota_mensual'],
            'plazo'=>$_POST[meses_plazo],
            'interes_anual'=>$_POST[interes],
            'saldo'=>$saldo,
            'fecha_inicio'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_inicio']),
            'fecha_pri_cuota'=>  FUNCIONES::get_fecha_mysql($_POST['fecha_pri_cuota']),
            'rango'=>  $_POST['rango'],
            'frecuencia'=>  $_POST['frecuencia']
            );
        $list_cuotas=FUNCIONES::plan_de_pagos($data);
    }
    $fecha_ven='9999-99-99';
    if($_POST[ven_id]){
        $fecha_ven =  FUNCIONES::atributo_bd_sql("select ven_ufecha_prog as campo from venta where ven_id='$_POST[ven_id]'");
    }
    ?>
    <div style="font-size: 13px; text-align: left ">
        Monto a Financiar: <b><?php echo number_format($data[saldo],2);?> <em><?php echo $_POST[ven_moneda]=='1'?'Bolivianos':($_POST[ven_moneda]=='2'?'Dolares':'');?></em></b>
        <?php if($fecha_ven!='9999-99-99'){?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Fecha de Vencimiento <b><?php echo FUNCIONES::get_fecha_latina($fecha_ven);?></b>
        <?php }?>
    </div>
    <?php
    if($_POST[form]){
        $form = $_POST[form];
    }else{
        if ($_POST[multinivel] == 'si') {
            $form = 0;
//            $form=  FUNCIONES::ad_parametro('par_valor_form');
        } else {
//            $form=  FUNCIONES::ad_parametro('par_valor_form');
            
            $form = $val_form;
        }
        
    }
    
    
    
    foreach ($list_cuotas as $fila) { ?>
            <tr <?php echo ($fecha_ven < $fila->fecha) ? 'style="color: #ff0000"':'';?>>
                <td>Cuota Nro: <?php echo $fila->nro_cuota;?></td>
                <td><?php echo $_POST[ven_moneda]=='1'?'Bolivianos':($_POST[ven_moneda]=='2'?'Dolares':'');?></td>                
                <td><?php echo FUNCIONES::get_fecha_latina($fila->fecha);?></td>
                <td><?php echo number_format($fila->dias, 2);?></td>
                <td><?php echo number_format($fila->interes, 2);?></td>
                <td><?php echo number_format($fila->capital, 2);?></td>                
                <td><?php echo number_format($fila->monto, 2);?></td>                
                <td><?php echo number_format($form, 2);?></td>                
                <td><?php echo number_format($fila->monto+$form, 2);?></td>
                <td><?php echo number_format($fila->saldo, 2);?></td>
            </tr>
    <?php } ?>
            </tbody>
        </table>
    </center>
                <?php
            }

            function calcular_fecha($meses, $fecha) {
                $fechaComparacion = strtotime($fecha);
                $calculo = strtotime("$meses month", $fechaComparacion);
                return date("Y-m-d", $calculo);
            }
            function calcular_fecha_dias($dias, $fecha) {
                $fechaComparacion = strtotime($fecha);
                $calculo = strtotime("$dias day", $fechaComparacion);
                return date("Y-m-d", $calculo);
            }

            
            function cargar_cuota_mensual_amortizar() {
                $conec = new ADO();

                //$sql="select par_interes_mensual from ad_parametro";

                $sql = "select urb_interes_anual from urbanizacion where urb_id=" . $_POST['urb_id'];

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                //$conversor = new convertir();

                $amortizar = $_POST['amortizar'];
                if ($amortizar == "") {
                    $amortizar = 0;
                }
                $saldo = $_POST['monto'] - $amortizar;
                $moneda = $_POST['moneda_amortizar'];
                $tc = $_POST['tca'];


                if ($_POST['ven_interes'] == 'Si') {
                    //$objeto->par_interes_mensual=$objeto->par_interes_mensual;
                    if ($_POST['rango_mes_amortizar'] == 1)
                        $objeto->urb_interes_anual = $objeto->urb_interes_anual;
                    if ($_POST['rango_mes_amortizar'] == 2)
                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 2);
                    if ($_POST['rango_mes_amortizar'] == 3)
                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 3);
                    if ($_POST['rango_mes_amortizar'] == 6)
                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 6);
                    if ($_POST['rango_mes_amortizar'] == 12)
                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 12);
                }
                else
                    $objeto->urb_interes_anual = 0;




                $interes_mensual = ($objeto->urb_interes_anual / 12) / 100;

                if ($saldo <> "") {
                    $cuota = cuota($saldo, $objeto->urb_interes_anual, $_POST['meses']);
                }

                //Valor 'valor_form' del Formulario de Amortizar.
                $valor_formulario = $_POST['valor_form'];

                echo '<input type="text" name="cuota_mensual_gnr" id="cuota_mensual_gnr" size="10" value="' . round($cuota + $valor_formulario, 2) . '" onKeyPress="return ValidarNumero(event);">';

                //$fecha=$_POST['fecha'];
            }

            function descripcion_terreno(&$des) {
                $conec = new ADO();

                $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
		from 
		lote
		inner join zona on (lot_id='" . $_POST['ven_lote'] . "' and lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)	
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on(man_urb_id=urb_id)
		";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $des = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
            }

            function cargar_lotes() {
                echo '<select style="width:200px;" name="ven_lot_id" id="ven_lot_id" class="caja_texto" onchange="cargar_datos(this.value);">';
                echo "<option value=''>Seleccione</option>";
                $fun = NEW FUNCIONES;
//                $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo,'-',lm_anticipo_min,'-',lm_anticipo_tipo,'-',lm_comision_base,'-',lm_comision_contado,'-',lm_bra) as id,
//                        concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,
//                        zon_color,cast(lot_nro as SIGNED) as numero from lote 
//                        inner join zona on (lot_zon_id=zon_id) 
//                        inner join uv on (lot_uv_id=uv_id) 
//                        left join lote_multinivel on (lot_id=lm_lot_id)
//                        where lot_man_id='" . $_POST['man'] . "' 
//                        and lot_uv_id='" . $_POST['uv'] . "' 
//                        and lot_estado='Disponible' 
//                        order by numero asc";
                
                $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,
                        concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,
                        zon_color,cast(lot_nro as SIGNED) as numero from lote 
                        inner join zona on (lot_zon_id=zon_id) 
                        inner join uv on (lot_uv_id=uv_id)                         
                        where lot_man_id='" . $_POST['man'] . "' 
                        and lot_uv_id='" . $_POST['uv'] . "' 
                        and lot_estado='Disponible' 
                        order by numero asc";
                $fun->combo_lote($sql,'');
                echo '</select>';
            }

            function cargar_uv() {
                echo '<select style="width:200px;" name="ven_uv_id" id="ven_uv_id" class="caja_texto" onchange="obtener_valor_manzano();">';
                echo "<option value=''>Seleccione</option>";
                $fun = NEW FUNCIONES;
                $fun->combo("select uv_id as id,concat('UV ',uv_nombre) as nombre from uv where uv_urb_id='" . $_POST['urb'] . "' order by nombre asc");
                echo '</select>';
            }

            function cargar_manzanos() {
                $conec = new ADO();
                $sql = "select lot_uv_id,lot_man_id from lote where lot_uv_id=" . $_POST['uv'] . " group by lot_man_id";
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $manzanos = '';
                for ($i = 1; $i <= $num; $i++) {
                    $objeto = $conec->get_objeto();
                    if ($i < $num)
                        $manzanos = $manzanos . $objeto->lot_man_id . ',';
                    if ($i == $num)
                        $manzanos = $manzanos . $objeto->lot_man_id;
                    $conec->siguiente();
                }
                echo '<select style="width:200px;" id="ven_man_id" name="ven_man_id" class="caja_texto" onchange="obtener_valor_uv();">';
                echo "<option value=''>Seleccione</option>";
                $fun = NEW FUNCIONES;
                $fun->combo("select man_id as id,concat('Manzano Nro: ',man_nro) as nombre,cast(man_nro as SIGNED) as numero from manzano where man_urb_id='" . $_POST['urb'] . "' and man_id in (" . $manzanos . ") order by numero asc");
                echo '</select>';
            }

            function cargar_grupos() {
                //echo "select  gdi_id as id, CONCAT(gdi_empieza,' - ',gdi_termina,' (',int_apellido,' ',int_nombre,')') as nombre from grupo_disciplina inner join interno on (gdi_int_id=int_id) where gdi_dis_id='".$_POST['dis']."'";
                echo '<select style="width:170px;" name="grupo" id="grupo" class="caja_texto">';
                echo "<option value=''>Seleccione</option>";
                $fun = NEW FUNCIONES;
                $fun->combo("select  gdi_id as id, gdi_descripcion as nombre from grupo_disciplina where gdi_estado='1' and gdi_dis_id='" . $_POST['dis'] . "'");
                echo '</select>';
            }

            function cargar_cuentas() {
                $tco_id = $_POST['tid'];
                // echo '<select style="width:180px;" name="cde_cue_id" id="cde_cue_id" class="caja_texto" >';
                // echo "<option value=''>Seleccione</option>";       
                // $fun=NEW FUNCIONES;	
                // if ($tco_id == 1)	   
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 1 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id'] );		
                // else
                // if ($tco_id == 2) 
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 2 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // else	
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where  cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // echo '</select>'  ;
                /////
                echo '<select name="cde_cue_id" id="cde_cue_id" class="caja_texto">
									   <option value="">Seleccione</option>';
                if ($tco_id <> "") {
                    $conec = new ADO();
                    $sql = "select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre,cue_padre_id  
			 from cuenta where cue_tcu_id = $tco_id and cue_nivel = 3 order by cue_padre_id,cue_descripcion asc";
                    //echo $sql;
                    $conec->ejecutar($sql);
                    $num = $conec->get_num_registros();
                    for ($i = 0; $i < $num; $i++) {
                        $objeto = $conec->get_objeto();
                        if ($i == 0) {
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }
                        if ($fam <> $objeto->cue_padre_id) {
                            echo '</optgroup>';
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }
                        echo '<option value="' . $objeto->id . '">' . $objeto->nombre . '</option>';
                        if ($i == $nun - 1) {
                            echo '</optgroup>';
                        }
                        $conec->siguiente();
                    }
                }
                echo '</select>';
            }

            function cargar_cuentas2() {
                $tco_id = $_POST['tid'];
                // echo '<select style="width:180px;" name="cde_cue_id" id="cde_cue_id" class="caja_texto" >';
                // echo "<option value=''>Seleccione</option>";       
                // $fun=NEW FUNCIONES;	
                // if ($tco_id == 1)	   
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 1 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id'] );		
                // else
                // if ($tco_id == 2) 
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 2 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // else	
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where  cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // echo '</select>'  ;
                /////

                echo '<select name="es" id="es" class="caja_texto">
									   <option value="">Seleccione</option>';

                if ($tco_id <> "") {
                    $conec = new ADO();

                    $sql = "select CONCAT(cue_id) as id, cue_descripcion  as nombre,cue_padre_id  
			 from cuenta where cue_tcu_id = $tco_id and cue_nivel = 3 order by cue_padre_id,cue_descripcion asc";
                    //echo $sql;
                    $conec->ejecutar($sql);

                    $num = $conec->get_num_registros();



                    for ($i = 0; $i < $num; $i++) {
                        $objeto = $conec->get_objeto();


                        if ($i == 0) {
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        if ($fam <> $objeto->cue_padre_id) {
                            echo '</optgroup>';
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        echo '<option value="' . $objeto->id . '">' . $objeto->nombre . '</option>';


                        if ($i == $nun - 1) {
                            echo '</optgroup>';
                        }


                        $conec->siguiente();
                    }
                }

                echo '</select>';
            }

            function cargar_cuentas3() {
                $tco_id = $_POST['tid'];
                // echo '<select style="width:180px;" name="cde_cue_id" id="cde_cue_id" class="caja_texto" >';
                // echo "<option value=''>Seleccione</option>";       
                // $fun=NEW FUNCIONES;	
                // if ($tco_id == 1)	   
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 1 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id'] );		
                // else
                // if ($tco_id == 2) 
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 2 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // else	
                // $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where  cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
                // echo '</select>'  ;
                /////

                echo '<select name="cp" id="cp" class="caja_texto">
									   <option value="">Seleccione</option>';

                if ($tco_id <> "") {
                    $conec = new ADO();

                    $sql = "select CONCAT(cue_id) as id, cue_descripcion  as nombre,cue_padre_id  
			 from cuenta where cue_tcu_id = $tco_id and cue_nivel = 3 order by cue_padre_id,cue_descripcion asc";
                    //echo $sql;
                    $conec->ejecutar($sql);

                    $num = $conec->get_num_registros();



                    for ($i = 0; $i < $num; $i++) {
                        $objeto = $conec->get_objeto();


                        if ($i == 0) {
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        if ($fam <> $objeto->cue_padre_id) {
                            echo '</optgroup>';
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        echo '<option value="' . $objeto->id . '">' . $objeto->nombre . '</option>';


                        if ($i == $nun - 1) {
                            echo '</optgroup>';
                        }


                        $conec->siguiente();
                    }
                }

                echo '</select>';
            }

            function cargar_cuentas4() {
                $tco_id = $_POST['tid'];

                echo '<select name="cux_cue_cpinteres" id="cux_cue_cpinteres" class="caja_texto">
									   <option value="">Seleccione</option>';
                if ($tco_id <> "") {
                    $conec = new ADO();

                    $sql = "select CONCAT(cue_id) as id, cue_descripcion  as nombre,cue_padre_id  
			 from cuenta where cue_tcu_id = $tco_id and cue_nivel = 3 order by cue_padre_id,cue_descripcion asc";
                    //echo $sql;
                    $conec->ejecutar($sql);

                    $num = $conec->get_num_registros();



                    for ($i = 0; $i < $num; $i++) {
                        $objeto = $conec->get_objeto();


                        if ($i == 0) {
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        if ($fam <> $objeto->cue_padre_id) {
                            echo '</optgroup>';
                            echo '<optgroup label="' . nombre_cuenta($objeto->cue_padre_id) . '">';
                            $fam = $objeto->cue_padre_id;
                        }

                        echo '<option value="' . $objeto->id . '">' . $objeto->nombre . '</option>';


                        if ($i == $nun - 1) {
                            echo '</optgroup>';
                        }


                        $conec->siguiente();
                    }
                }

                echo '</select>';
            }

            function nombre_cuenta($id) {
                $conec = new ADO();

                $sql = "select cue_descripcion from cuenta where cue_id='$id'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                return $objeto->cue_descripcion;
            }

            function cargar_cuentas_tabla() {
                $tco_id = $_POST['tid'];
                $conec = new ADO();
                $sql = "select cue_id as id, cue_descripcion  as nombre  from cuenta where cue_tcu_id =" . $tco_id . " and cue_nivel = 3 order by cue_descripcion asc";
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                echo '<center><table style="vertical-align:middle;" width="100%" border="0" cellspacing="2">';

                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec->get_objeto();

                    if (($i % 3 == 0)) {
                        if ($i != 0)
                            echo "</tr><tr>";
                        else
                            echo "<tr>";
                    }


                    echo "<td><input type='checkbox' name='cuenta[]' value='" . $objeto->id . "'/></td>";
                    echo "<td style='vertical-align:middle;'><span style='font-size:10px; color:#005C89;'>" . utf8_encode($objeto->nombre) . "</span></td>";

                    if ($i == ($num - 1))
                        echo "</tr>";
                    $conec->siguiente();
                }
                echo '</table></center>';
            }

            function cargar_plantilla_detalle() {
                $pla_id = $_POST['pid'];

                $conec = new ADO();

                $sql = "Select cue_id,cue_numero,cue_descripcion, cco_id, cco_descripcion,pld_monto,pld_mon_id,pld_tipo
		       From cuenta, centrocosto, plantilla_detalle
			   Where pld_cco_id = cco_id and pld_cue_id = cue_id and pld_pla_id =" . $pla_id;

                $conec->ejecutar($sql);

                $num = $conec->get_num_registros();
                echo '<script>';
                echo 'var pla_detalle = new Array();';
                for ($i = 0; $i < $num; $i++) {
                    $obj = $conec->get_objeto();
                    echo 'var dato = new Array();';
                    echo 'dato.push("' . $obj->cue_id . '");';
                    echo 'dato.push("' . $obj->cue_numero . '");';
                    echo 'dato.push("' . $obj->cue_descripcion . '");';
                    echo 'dato.push("' . $obj->cco_id . '");';
                    echo 'dato.push("' . $obj->cco_descripcion . '");';
                    echo 'dato.push("' . $obj->pld_monto . '");';
                    echo 'dato.push("' . $obj->pld_mon_id . '");';
                    echo 'dato.push("' . $obj->pld_tipo . '");';
                    echo 'pla_detalle.push(dato);';

                    $conec->siguiente();
                }


                echo '</script>';
            }

            function cargar_plantilla() {
                $tco_id = $_POST['tid'];
                echo '<select style="width:180px;" name="pla_id" id="pla_id" class="caja_texto" onChange="cargar_detalle_plantilla(this.value);">';
                echo "<option value='0'>Seleccione</option>";

                $fun = NEW FUNCIONES;

                $fun->combo("select  pla_id as id, pla_descripcion as nombre from plantilla where pla_tco_id = " . $tco_id . " and pla_estado = 1 order by pla_descripcion asc ", $_POST['pla_id']);

                echo '</select>';
            }

            function cargar_cajeros() {
                $cja_id = $_POST['tid'];
                echo '<select name="cajero_id" id="cajero_id" class="caja_texto">';
                echo "<option value=''>Seleccione</option>";

                $fun = NEW FUNCIONES;

                $fun->combo("select  cja_usu_id as id, cja_usu_id as nombre from cajero where cja_cue_id = " . $cja_id . " order by cja_usu_id asc ", $_POST['cajero_id']);

                echo '</select>';
            }

            function generar_fechas() {
                ?>

    <br><br><center>

        <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">

            <thead>

                <tr>

                    <th>Observación</th>

                    <th>Monto</th>

                    <th>Moneda</th>

                    <th>F Generada</th>

                    <th>F Programada</th>

                    <th>F Pagada</th>

                    <th>Saldo</th>

                    <th>Estado</th>

                </tr>	

            </thead>

            <tbody>

    <?php
    $conec = new ADO();

    $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_saldo,ind_estado,int_nombre,int_apellido,cue_descripcion,cco_descripcion,ind_fecha_programada,ind_fecha_pago 

		FROM 

		interno_deuda inner join interno on (ind_int_id='" . $_POST['man'] . "' and ind_int_id=int_id)

		inner join cuenta on (ind_cue_id=cue_id)

		inner join centrocosto on (ind_cco_id=cco_id)
		
		where ind_tabla_id='" . $_POST['deuda'] . "' and ind_estado <> 'Anulado'
		
		order by ind_id asc

		";

    $conec->ejecutar($sql);

    $num = $conec->get_num_registros();

    $conversor = new convertir();

    for ($i = 0; $i < $num; $i++) {

        $objeto = $conec->get_objeto();



        echo '<tr>';

        echo "<td>";

        echo utf8_encode($objeto->ind_concepto);

        echo "&nbsp;</td>";



        echo "<td>";

        echo $objeto->ind_monto;

        echo "&nbsp;</td>";



        echo "<td>";

        if ($objeto->ind_moneda == '1')
            echo 'Bolivianos';
        else
            echo 'Dolares';

        echo "&nbsp;</td>";



        echo "<td>";

        echo $conversor->get_fecha_latina($objeto->ind_fecha);

        echo "&nbsp;</td>";



        echo "<td>";

        if ($objeto->ind_fecha_programada <> '0000-00-00')
            echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);

        echo "&nbsp;</td>";



        echo "<td>";

        if ($objeto->ind_fecha_pago <> '0000-00-00')
            echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);

        echo "&nbsp;</td>";


        echo '<td>';

        echo $objeto->ind_saldo;

        echo "&nbsp;</td>";

        $color = "#000000";

        if ($objeto->ind_estado == 'Pendiente')
            $color = "#FB0404;";

        if ($objeto->ind_estado == 'Pagado')
            $color = "#05A720;";



        echo '<td style="color:' . $color . '">';

        echo $objeto->ind_estado;

        echo "&nbsp;</td>";


        echo "</tr>";

        $conec->siguiente();
    }
    ?>

            </tbody></table></center>

                <?php
            }

            function cargar_manzanos_reporte() {

                echo '<select style="width:200px;" id="ven_man_id" name="ven_man_id" class="caja_texto" onchange="obtener_valor_uv();">';
                echo "<option value=''>Seleccione</option>";
                $fun = NEW FUNCIONES;

                $fun->combo("select man_id as id,concat('Manzano Nro: ',man_nro) as nombre,cast(man_nro as SIGNED) as numero from manzano where man_urb_id='" . $_POST['urb'] . "' order by numero asc");
                echo '</select>';
            }

            function obtener_tipo_cambio_cv() {
                $conec = new ADO();

                $sql = "select *
		from
		tipo_cambio order by tca_id desc LIMIT 0,1
		";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                //$conversor = new convertir();

                $tipo = $_POST['tipo'];

                if ($tipo == "compra") {
                    echo '<input type="text" name="cvd_tipo_cambio" id="cvd_tipo_cambio" size="10" value="' . $objeto->tca_valor_compra . '" onKeyPress="return ValidarNumero(event);">';
                } else {
                    if ($tipo == "venta") {
                        echo '<input type="text" name="cvd_tipo_cambio" id="cvd_tipo_cambio" size="10" value="' . $objeto->tca_valor . '" onKeyPress="return ValidarNumero(event);">';
                    }
                }
                //$fecha=$_POST['fecha'];
            }

            function actualizar_nombre_urb() {
                $conec = new ADO();

                $sql = "select urb_nombre
		from
		urbanizacion where urb_id='" . $_POST['urb_id'] . "'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                echo '<input type="hidden" name="nombre_urbanizacion" value="' . $objeto->urb_nombre . '" size="10" />';
            }

            function generar_plan_cuentasx() {
                ?>
    <center>
        <br>
        <!--
                <table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                <thead>
                                <tr>
                                        
                                        <th>Superficie</th>
                                        <th>Valor del m2</th>
                                        <th>Valor del Terreno</th>
                                        <th>Descuento</th>
                                        <th>Monto a Pagar</th>
                                        <th>Cuota Inicial</th>
                                        <th>Meses Plazo</th>
                                        
                                </tr>		
                                </thead>
                                <tbody>
                                <tr>
                                        
                                        <td><?php echo $_POST['superficie']; ?> m2</td>
                                        <td><?php echo $_POST['valor']; ?></td>
                                        <td><?php echo $_POST['vterreno']; ?></td>
                                        <td><?php echo $_POST['ven_descuento']; ?> <?php if ($_POST['ven_moneda'] == '1')
        echo 'Bolivianos';
    else
        echo 'Dolares';
    ?></td>
                                        <td><?php echo $_POST['valor_terreno']; ?> <?php if ($_POST['ven_moneda'] == '1')
        echo 'Bolivianos';
    else
        echo 'Dolares';
    ?></td>
                                        <td><?php echo $_POST['cuota_inicial']; ?> <?php if ($_POST['ven_moneda'] == '1')
        echo 'Bolivianos';
    else
        echo 'Dolares';
    ?></td>
                                        <td><?php echo $_POST['meses_plazo']; ?></td>
                                </tr>	
                                </tbody>
                        </table>
        -->
        <br>
        <h3>PLAN DE PAGO</h3>
        <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Cuota</th>
                    <th>Fecha de Pago</th>
                    <!--<th>Moneda</th>-->
                    <th>Interes</th>
                    <th>Capital</th>
                    <th>Monto a Pagar</th>
                    <th>Saldo</th>
                </tr>							
            </thead>
            <tbody>
                <?php
                $conec = new ADO();

                // ------------- ----------------- ---------------//

                $conversor = new convertir();
                $saldo = $_POST['monto'];
                $moneda = $_POST['moneda'];
                $interes_anual = ($_POST['interes_mensual'] * 12);
                $interes_mensual = (($interes_anual / 12) / 100);

                $conversor = new convertir();

                $fecha = $conversor->get_fecha_mysql($_POST['fecha']);

                //descripcion_terreno($des);		

                $cuota = cuota($saldo, $interes_anual, $_POST['cux_meses_plazo']);

                for ($i = 1; $i <= $_POST['cux_meses_plazo']; $i++) {
                    $fecha = calcular_fecha(+1, $fecha);

                    echo '<tr>';

                    echo "<td>";
                    echo $i;
                    echo "&nbsp;</td>";
                    echo "<td>";
                    if ($fecha <> '0000-00-00')
                        echo $conversor->get_fecha_latina($fecha);
                    echo "&nbsp;</td>";
//                    echo "<td>";
//                    if ($moneda == '1')
//                        echo 'Bolivianos';
//                    else
//                        echo 'Dolares';
//                    echo "&nbsp;</td>";


                    echo "<td>";
                    $interes = $saldo * $interes_mensual;
                    echo round($interes, 2);
                    echo "&nbsp;</td>";


                    echo "<td>";
                    $capital = $cuota - $interes;
                    echo round($capital, 2);
                    echo "&nbsp;</td>";

                    echo "<td>";
                    echo round($cuota, 2);
                    echo "&nbsp;</td>";

                    $saldo = $saldo - $capital;

                    echo "<td>";
                    echo round($saldo, 2);
                    echo "&nbsp;</td>";

                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </center>
    <?php
}

function primera_cuota(&$i,&$saldo,&$fecha,$txt_moneda,$interes_mensual,$comenzar,$des,$cuota,$valor_form,$dif_dias){
    $dias=$_POST['rango_mes']*30;
//    $fecha = calcular_fecha_dias($dias, $fecha);
    $i++;
    echo '<tr>';
    echo "<td>";
    echo utf8_encode("Cuota Nro: " . ($i + $comenzar) . " - $des ");
    echo "&nbsp;</td>";
    echo "<td>";
    if ($fecha <> '0000-00-00')
        echo FUNCIONES::get_fecha_latina($fecha);
    echo "&nbsp;</td>";
    echo "<td>";
    echo $txt_moneda;
    echo "<td>";
    $interes = $saldo * $interes_mensual;
    $interes_pag=($interes/30)*$dif_dias;
    echo round($interes_pag, 2);
    echo "&nbsp;</td>";
    echo "<td>";
    if ($saldo < $cuota) {
        $capital = $saldo;
    } else {
        $capital = $cuota - $interes;
    }
    echo round($capital, 2);
    echo "&nbsp;</td>";
    echo "<td>";
    $monto_formulario = $valor_form;
    echo round($monto_formulario, 2);
    echo "&nbsp;</td>";
    echo "<td>";
    echo round($capital + $interes_pag + $monto_formulario, 2);
    echo "&nbsp;</td>";
    $saldo = $saldo - $capital;
    echo "<td>";
    echo round($saldo, 2);
    echo "&nbsp;</td>";
    echo "</tr>";
}
function venta_cuotas(){
    $ven_id=$_POST[ven_id];
    $nro_cuotas=$_POST[nro_cuotas];
    $fecha_pago=  FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
    if ($_POST[ind_id]) {
        $sql_cu="select * from interno_deuda where ind_id='$_POST[ind_id]'";
    } else {
        $sql_cu="select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,$nro_cuotas";
    }
//    echo $sql_cu;
    $cuotas=  FUNCIONES::lista_bd_sql($sql_cu);
    $lista=array();
    $u_ind_id=0;
    
    $monto_dia_mora=  FUNCIONES::ad_parametro('par_val_dias_mora');
    foreach ($cuotas as $cu) {
        $fila=new stdClass();
        $fila->id=$cu->ind_id;
        $fila->nro=$cu->ind_num_correlativo;
        $fila->interes=$cu->ind_interes;
        $fila->capital=$cu->ind_capital-$cu->ind_capital_pagado;
        $fila->monto=$cu->ind_monto;
        $fila->saldo=$cu->ind_saldo;
        $fila->fecha_prog=$cu->ind_fecha_programada;
        
        if($cu->ind_capital_pagado>0){
            $fila->form=0;
        }else{
            $fila->form=$cu->ind_form;
        }
        if($cu->ind_capital_pagado>0){
            $fila->mora=0;
            $fila->mora_dias=0;
        }else{
            $mmora=0;
            $num_dias_mora=FUNCIONES::diferencia_dias($cu->ind_fecha_programada, $fecha_pago);
            if($num_dias_mora>0){
                $mmora=$num_dias_mora*$monto_dia_mora;
            }else{
                $num_dias_mora=0;
            }
            $fila->mora=$mmora;
            $fila->mora_dias=$num_dias_mora;
        }
        
        
        $lista[]=$fila;
        $u_ind_id=$cu->ind_id;
    }
    $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id and ind_estado='Pendiente' and ind_id>$u_ind_id order by ind_id asc limit 0,1");
    if($scuota){
        $oscuota=new stdClass();
        $oscuota->id=$scuota->ind_id;
        $oscuota->nro=$scuota->ind_num_correlativo;
        $oscuota->interes=$scuota->ind_interes;
        $oscuota->capital=$scuota->ind_capital;
        $oscuota->monto=$scuota->ind_monto;
        $oscuota->saldo=$scuota->ind_saldo;
        $oscuota->fecha_prog=$scuota->ind_fecha_programada;
        $scuota_json=json_encode($oscuota);
    }else{
        $scuota_json="null";
    }
    echo '{"listac":'.json_encode($lista).',"scuota":'.$scuota_json.'}';
    
}

function venta_cuotas_producto(){
    $ven_id=$_POST[ven_id];
    $nro_cuotas=$_POST[nro_cuotas];
    $fecha_pago=  FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
    $sql_cu="select * from interno_deuda_producto where idpr_tabla='venta_producto' and idpr_tabla_id=$ven_id 
    and idpr_estado='Pendiente' order by idpr_id asc limit 0,$nro_cuotas";
//    echo $sql_cu;
    $cuotas=  FUNCIONES::lista_bd_sql($sql_cu);
    $lista=array();
    $u_idpr_id=0;
    
    $monto_dia_mora=  FUNCIONES::ad_parametro('par_val_dias_mora');
    foreach ($cuotas as $cu) {
        $fila = new stdClass();
        $fila->id = $cu->idpr_id;
        $fila->nro = $cu->idpr_num_correlativo;
        $fila->interes = $cu->idpr_interes;
        $fila->capital = $cu->idpr_capital - $cu->idpr_capital_pagado;
        $fila->monto = $cu->idpr_monto;
        $fila->saldo = $cu->idpr_saldo;
        $fila->fecha_prog = $cu->idpr_fecha_programada;
        
        if($cu->idpr_capital_pagado>0){
            $fila->form=0;
        }else{
            $fila->form=$cu->idpr_form;
        }
        if($cu->idpr_capital_pagado>0){
            $fila->mora=0;
            $fila->mora_dias=0;
        }else{
            $mmora=0;
            $num_dias_mora=FUNCIONES::diferencia_dias($cu->idpr_fecha_programada, $fecha_pago);
            if($num_dias_mora>0){
                $mmora=$num_dias_mora*$monto_dia_mora;
            }else{
                $num_dias_mora=0;
            }
            $fila->mora=$mmora;
            $fila->mora_dias=$num_dias_mora;
        }
        
        
        $lista[]=$fila;
        $u_idpr_id=$cu->idpr_id;
    }
    $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda_producto 
    where idpr_tabla='venta_producto' and idpr_tabla_id=$ven_id 
    and idpr_estado='Pendiente' and idpr_id>$u_idpr_id order by idpr_id asc limit 0,1");
    if($scuota){
        $oscuota = new stdClass();
        $oscuota->id = $scuota->idpr_id;
        $oscuota->nro = $scuota->idpr_num_correlativo;
        $oscuota->interes = $scuota->idpr_interes;
        $oscuota->capital = $scuota->idpr_capital;
        $oscuota->monto = $scuota->idpr_monto;
        $oscuota->saldo = $scuota->idpr_saldo;
        $oscuota->fecha_prog = $scuota->idpr_fecha_programada;
        $scuota_json = json_encode($oscuota);
    }else{
        $scuota_json="null";
    }
    echo '{"listac":'.json_encode($lista).',"scuota":'.$scuota_json.'}';
    
}

function cuota_pagos() {    
        ?>
        <link type="text/css" rel="stylesheet" href="css/estilos.css">
        <link href="css/impromptu.css" rel=stylesheet type=text/css />
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-impromptu.5.2.1.js"></script>
        <?php
        if(!($_POST[tipo] && ($_POST[ind_id] || $_POST[vpag_id]))){?>
            <div class="msInformacion limpiar">Error en envio de Parametros</div>
            <center><input class="boton" type="button" id="btn_cancelar" value="Cerrar" onclick="self.close();return false;"></center>
            <?php 
            return false;
        }
        ?>
        <style>
            .span-label{
                color: #3a3a3a; float: left; width: 50px; margin-top: 4px; text-align: right;
            }
            .span-dato{
                color: #3a3a3a; float: left; width: 50px; margin-top: 4px; font-weight: bold; margin-left: 2px; 
            }
            .read-input{
                background-color: #ededed;
                border: 1px solid #bfc4c9;
                color: #3a3a3a;
                float: left;
                padding: 4px 8px;
                min-width: 107px;
            }
        </style>
        <div class="plan_pago_efectivo div_plan_pagos">
            <div class="Subtitulo"> Pagos de Cuota: </div>
            <div id="ContenedorSeleccion" class="lista_planes" style="margin-bottom: 5px; width: 98%">
                <table class="tablaReporte" width="100%" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>Cuota</th>
                            <th>Recibo</th>
                            <th>Concepto</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Autorizado</th>
                            <th>Usuario Aut.</th>
                        </tr>
                    </thead>
                    <?php 
                        $filtro="ind_id=0";
                        if($_POST[tipo]=='cuota'){
                            $filtro=" and idp_ind_id=$_POST[ind_id]";
                        }else if($_POST[tipo]=='pago'){
                            $filtro=" and idp_vpag_id=$_POST[vpag_id]";
                        }
                    ?>
                    <?php $sql_det="select * from interno_deuda_pago where idp_estado='Activo' and idp_tipo!='costo'$filtro order by idp_fecha asc";?>
                    <?php $total=0;?>
                    <?php $detalles=  FUNCIONES::lista_bd_sql($sql_det)?>
                    <tbody>
                        <?php foreach ($detalles as $det) {?>
                        <tr>
                            <td><?php echo $det->idp_ind_correlativo;?></td>
                            <td><?php echo $det->idp_vpag_recibo;?></td>
                            <td><?php echo $det->idp_tipo;?></td>
                            <td><?php echo FUNCIONES::get_fecha_latina($det->idp_fecha);?></td>
                            <td><?php echo $det->idp_monto;?></td>
                            <td><?php echo $det->idp_usu_cre;?></td>
                            <td><?php echo $det->idp_cob_aut?'Si':'No';?></td>
                            <td><?php echo $det->idp_cob_aut?$det->idp_cob_usu:'&nbsp;';?></td>
                            <?php $total+=$det->idp_monto;?>
                        </tr>
                        <?php }?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                            <td ><?php echo $total;?></td>
                            <td >&nbsp;</td>
                            <td >&nbsp;</td>
                            <td >&nbsp;</td>
                        </tr>
                    </tfoot>
                </table>
                <br><br>
                <center><input class="boton" type="button" id="btn_cancelar" value="Cerrar"></center>
            </div>
        </div>
        <script>
            $("#btn_cancelar").click(function(){
                self.close();
                return false;
            });
        </script>
<?php 
}
function posible_contretar_venta(){
    $res_id=$_POST[res_id]*1;
    $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);// ;
    // $bool=FUNCIONES::posible_concretar_venta($res_id,$fecha);
	$resp = FUNCIONES::posible_concretar_venta($res_id,$fecha);
	$bool = $resp->ok;
//    if($res_id*1>0){
//        
//    }else{
//        $bool=false;
//    }
    if($bool){
        echo '{"type":"success","monto_referencial":"'.$resp->monto_referencial.'","anticipos":"'.$resp->anticipos.'"}';
    }else{
        // echo '{"type":"incorrect"}';
		echo '{"type":"incorrect","monto_referencial":"'.$resp->monto_referencial.'","anticipos":"'.$resp->anticipos.'"}';
    }
}

function buscar_lote() {
        ?>
        <link type="text/css" rel="stylesheet" href="css/estilos.css">
        <link href="css/impromptu.css" rel=stylesheet type=text/css />
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-impromptu.5.2.1.js"></script>
            <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/funciones.js"></script>
        
        <div id="Contenedor_NuevaSentencia">
            <div id="FormSent" style="width: 100%">
                <div class="Subtitulo">Datos</div>
                <div id="ContenedorSeleccion">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizac&oacute;n</div>
                        <div id="CajaInput">
                            <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                <option value="">Seleccione</option>
                                <?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where urb_ventas_internas='Si'", $_POST['ven_urb_id']);
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
                        <div id="CajaInput">
                            <div id="uv">
                                <select style="width:200px;" name="ven_uv_id" class="caja_texto">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
                        <div id="CajaInput">
                            <div id="manzano">
                                <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                        <div id="CajaInput">
                            <div id="lote">
                                <select style="width:200px;" name="ven_lot_id" class="caja_texto">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function cargar_uv(id){
                var valores = "tarea=uv&urb=" + id;
                ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
            }

            function cargar_manzano(id, uv){
                var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;
                ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
            }

            function cargar_lote(id, uv){
                var valores = "tarea=lotes&man=" + id + "&uv=" + uv;
                ejecutar_ajax('ajax.php', 'lote', valores, 'POST');
            }
            function obtener_valor_uv() {
                var axuUv = $('#ven_uv_id').val();
                var axuMan = $('#ven_man_id').val();
                cargar_lote(axuMan, axuUv);
            }

            function obtener_valor_manzano() {
                var auxUrb = $('#ven_urb_id').val();
                var auxUv = $('#ven_uv_id').val();
                cargar_manzano(auxUrb, auxUv);
            }
            function cargar_datos(datos){
                
            }
        </script>
        <input type="hidden" id="lote_id" name="lote_id" value="<?php echo $_POST[lote_id];?>">
        <div id="ContenedorDiv" >
            <input class="boton" type="button" id="btn_aceptar" value="Aceptar">
            <input class="boton" type="button" id="btn_cancelar" value="Cancelar">
        </div>
        <script>
            
            $(document).ready(function(){
                $("#btn_aceptar").click(function(){
                    var urb_id=$('#ven_urb_id option:selected').val();
                    var uv_id=$('#ven_uv_id option:selected').val();
                    var man_id=$('#ven_man_id option:selected').val();
                    // 589-439.48-40.00-300-2-Lote
                    //(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo)
                    var datos_lote=$('#ven_lot_id option:selected').val();
                    var alote=datos_lote.split('-');
                    var lot_id=alote[0];
                    var lot_sup=alote[1];
                    var zon_precio=alote[2];
                    var zon_cuota_ini=alote[3];
                    var zon_moneda=alote[4];
                    var lot_tipo=alote[5];
                    var lot_descripcion='Urb.: '+$('#ven_urb_id option:selected').text()+' - '+$('#ven_man_id option:selected').text()+' - '+$('#ven_lot_id option:selected').text();
                    
                    var data={};
                    
                    data.urb_id=urb_id;
                    data.uv_id=uv_id;
                    data.man_id=man_id;
                    data.lot_id=lot_id;
                    data.lot_sup=lot_sup;
                    data.zon_precio=zon_precio;
                    data.zon_cuota_ini=zon_cuota_ini;
                    data.zon_moneda=zon_moneda;
                    data.lot_tipo=lot_tipo;
                    data.lot_descripcion=lot_descripcion;

                    window.opener.cargar_lote_cambio(data);
                    self.close();
                    return false;
                });
                
                $("#btn_cancelar").click(function(){
                    self.close();
                    return false;
                });
                
                
            }); 
        </script>
                <?php
}
function buscar_venta() {
        ?>
        <link type="text/css" rel="stylesheet" href="css/estilos.css">
        <link href="css/impromptu.css" rel=stylesheet type=text/css />
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-impromptu.5.2.1.js"></script>
            <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/funciones.js"></script>
        <style>
            .btn_aceptar{ cursor: pointer;}
        </style>
        <div id="Contenedor_NuevaSentencia">
            <div id="FormSent" style="width: 100%">
                <div class="Subtitulo">Datos</div>
                <div id="ContenedorSeleccion">
                    
                    Nro. Venta<input type="text" name="bus_nro_venta" id="bus_nro_venta" value="<?php echo $_POST[bus_nro_venta]?>">
                    <input type="button" id="btn_buscar" class="boton" value="Buscar">
                    <input type="hidden" name="ven_id" id="ven_id"value="<?php echo $_POST[ven_id]?>">
                    
                    <table class="tablaReporte" id="tab_ventas" width="100%" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th >Nro. Venta</th>
                                <th >Titular</th>
                                <th >Concepto</th>
                                <th >Estado</th>
                                <th >Capital Pagado</th>
                                <th >Intercambio Consumido</th>
                                <th >Desc. Plan</th>
                                <th >Inc. Plan</th>
                                <th >Comision</th>
                                <th >Moneda</th>
                                <th class="tOpciones"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $ventas =  FUNCIONES::lista_bd_sql("select * from venta where ven_int_id='$_POST[int_id]' and ven_estado='Pendiente' and ven_id!='$_POST[ven_id]'"); ?>
                            <?php foreach($ventas as $venta){?>
                            <?php
                                $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
                                $fecha_valor=$venta->ven_fecha;
                                if($upago){
                                    $fecha_valor=$upago->vpag_fecha_valor;
                                }
                                $sum_inter_pag=0;
                                if($venta->ven_monto_intercambio>0){
                                    $amontos_pag=FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
                                    
                                    foreach ($amontos_pag as $ipag) {
                                        $sum_inter_pag+=$ipag->monto;
                                    }
                                }
                            ?>
                            <?php $pagado=  total_pagado($venta->ven_id);?>
                                <tr data-capital="<?php echo $pagado->capital+$venta->ven_res_anticipo+$venta->ven_venta_pagado;?>" data-descuento="<?php echo $pagado->descuento;?>" data-incremento="<?php echo $pagado->incremento;?>"
                                    data-concepto="<?php echo $venta->ven_concepto;?>" data-id="<?php echo $venta->ven_id;?>" data-moneda="<?php echo $venta->ven_moneda;?>" 
                                    data-intercambio="<?php echo $sum_inter_pag;?>"
                                    data-fecha-valor="<?php echo FUNCIONES::get_fecha_latina($fecha_valor);?>" data-comision="<?php echo $venta->ven_comision_pri;?>"
                                    >
                                    <td ><?php echo $venta->ven_id;?></td>
                                    <td ><?php echo FUNCIONES::interno_nombre($venta->ven_int_id);?></td>
                                    <td ><?php echo $venta->ven_concepto;?></td>
                                    <td ><?php echo $venta->ven_estado;?></td>
                                    <td ><?php echo $pagado->capital;?></td>
                                    <td ><?php echo $sum_inter_pag;?></td>
                                    <td ><?php echo $pagado->descuento;?></td>
                                    <td ><?php echo $pagado->incremento;?></td>
                                    <td ><?php echo $venta->ven_comision_pri;?></td>
                                    <td ><?php echo $venta->ven_moneda=='2'?'Dolares':'Bolivianos';?></td>
                                    <td >
                                        <img src="images/ok.png" width="16px" class="btn_aceptar">
                                    </td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type="hidden" id="lote_id" name="lote_id" value="<?php echo $_POST[lote_id];?>">
        <div id="ContenedorDiv" >
            <!--<input class="boton" type="button" id="btn_aceptar" value="Aceptar">-->
            <input class="boton" type="button" id="btn_cancelar" value="Cancelar">
        </div>
        <script>
            $(document).ready(function(){
                function ini_evento_aceptar(){
                    $(".btn_aceptar").click(function(){
                        var padre=$(this).parent().parent();

                        var capital=$(padre).attr('data-capital');
                        var descuento=$(padre).attr('data-descuento');
                        var incremento=$(padre).attr('data-incremento');
                        var concepto=$(padre).attr('data-concepto');
                        var ven_id=$(padre).attr('data-id');
                        var moneda=$(padre).attr('data-moneda');
                        var fecha_valor=$(padre).attr('data-fecha-valor');
                        var intercambio=$(padre).attr('data-intercambio');
                        var comision=$(padre).attr('data-comision');

                        var data={};

                        data.capital=capital;
                        data.descuento=descuento;
                        data.incremento=incremento;
                        data.concepto=concepto;
                        data.ven_id=ven_id;
                        data.moneda=moneda;
                        data.fecha_valor=fecha_valor;
                        data.intercambio=intercambio;
                        data.comision=comision;

                        window.opener.cargar_venta_fusion(data);
                        self.close();
                        return false;
                    });
                }   
                
                $("#btn_cancelar").click(function(){
                    self.close();
                    return false;
                });
                
                $('#btn_buscar').click(function (){
                    
                    mostrar_ajax_load();
                    var ven_id=$('#ven_id').val();
                    var bus_ven_id=$('#bus_nro_venta').val();
                    $.post('ajax.php',{tarea:'lista_ventas',ven_id:ven_id,bus_ven_id:bus_ven_id},function(resp){
                        ocultar_ajax_load();
                        $('#tab_ventas tbody tr').remove();
                        $('#tab_ventas tbody').append(resp);
                        ini_evento_aceptar();
                    });
                });
                ini_evento_aceptar();
                
            }); 
        </script>
                <?php
}

function buscar_lista_ventas() {
    $ven_id=$_POST[ven_id];
    $bus_ven_id=$_POST[bus_ven_id];
//    echo "select * from venta where ven_estado='Pendiente' and ven_id='$ven_id'";
    $ventas =  FUNCIONES::lista_bd_sql("select * from venta where ven_estado='Pendiente' and ven_id!='$ven_id' and ven_id='$bus_ven_id'"); ?>
    <?php foreach($ventas as $venta){?>
    <?php
        $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        $fecha_valor=$venta->ven_fecha;
        if($upago){
            $fecha_valor=$upago->vpag_fecha_valor;
        }
        $sum_inter_pag=0;
        if($venta->ven_monto_intercambio>0){
            $amontos_pag=FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

            foreach ($amontos_pag as $ipag) {
                $sum_inter_pag+=$ipag->monto;
            }
        }
    ?>
    <?php $pagado=  total_pagado($venta->ven_id);?>
        <tr data-capital="<?php echo $pagado->capital+$venta->ven_res_anticipo+$venta->ven_venta_pagado;?>" data-descuento="<?php echo $pagado->descuento;?>" data-incremento="<?php echo $pagado->incremento;?>"
            data-concepto="<?php echo $venta->ven_concepto;?>" data-id="<?php echo $venta->ven_id;?>" data-moneda="<?php echo $venta->ven_moneda;?>" 
            data-intercambio="<?php echo $sum_inter_pag;?>"
            data-fecha-valor="<?php echo FUNCIONES::get_fecha_latina($fecha_valor);?>" data-comision="<?php echo $venta->ven_comision_pri;?>"
            >
            <td ><?php echo $venta->ven_id;?></td>
            <td ><?php echo FUNCIONES::interno_nombre($venta->ven_int_id);?></td>
            <td ><?php echo $venta->ven_concepto;?></td>
            <td ><?php echo $venta->ven_estado;?></td>
            <td ><?php echo $pagado->capital;?></td>
            <td ><?php echo $sum_inter_pag;?></td>
            <td ><?php echo $pagado->descuento;?></td>
            <td ><?php echo $pagado->incremento;?></td>
            <td ><?php echo $venta->ven_comision_pri;?></td>
            <td ><?php echo $venta->ven_moneda=='2'?'Dolares':'Bolivianos';?></td>
            <td >
                <img src="images/ok.png" width="16px" class="btn_aceptar">
            </td>
        </tr>
    <?php }
}
function total_pagado($ven_id) {
    $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento 
                        from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
                ";
    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    return $pagado;
}

function vista_lotes() { 
    $filtro="";
    $uv_tipo=$_POST[uv_tipo];
    if($uv_tipo=='uno'){
        $filtro.=" and uv_id='$_POST[uv_id]'";
    }else if($uv_tipo=='varios'){
        $filtro.=" and uv_id in ($_POST[uv_ids])";
    }
    
    $man_tipo=$_POST[man_tipo];
    if($man_tipo=='uno'){
        $filtro.=" and man_id='$_POST[man_id]'";
    }else if($man_tipo=='varios'){
        $filtro.=" and man_id in ($_POST[man_ids])";
    }
    
    $zon_tipo=$_POST[zon_tipo];
    if($zon_tipo=='uno'){
        $filtro.=" and zon_id='$_POST[zon_id]'";
    }else if($zon_tipo=='varios'){
        $filtro.=" and zon_id in ($_POST[zon_ids])";
    }
    
    $urb_id=$_POST[urb_id];
    $sql_sel="select * from lote
                inner join manzano on (lot_man_id=man_id)
                inner join uv on (lot_uv_id=uv_id)
                inner join zona on (lot_zon_id=zon_id)
                where man_urb_id='$urb_id' $filtro
            ";
    
    $list_lotes=  FUNCIONES::lista_bd_sql($sql_sel);
    ?>
    <style>
        .box_estado_lote{color: #fff; padding: 2px 5px;}
    </style>
    <center>
        <h3>PLAN DE PAGO</h3>
        <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Zona</th>
                    <th>Superficie</th>
                    <th>Estado</th>
                </tr>							
            </thead>
            <tbody>
                <?php $color=array('Disponible'=>'#008902','Reservado'=>'#0006D1','Vendido'=>'#EF0000','Bloqueado'=>'#727272'); ?>
                <?php $i=1; ?>
                <?php foreach ($list_lotes as $fila) { ?>
                    <tr >
                        <td><em><?php echo $i;?></em></td>
                        <td><?php echo $fila->lot_id;?></td>
                        <td><?php echo $fila->uv_nombre;?></td>
                        <td><?php echo $fila->man_nro;?></td>
                        <td><?php echo $fila->lot_nro;?></td>
                        <td><?php echo $fila->zon_nombre;?></td>
                        <td><?php echo $fila->lot_superficie;?></td>
                        <td><span class="box_estado_lote" style="background-color: <?php echo $color[$fila->lot_estado]?>;"><?php echo $fila->lot_estado;?></span></td>
                        <?php $i++;?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br><br>
    <?php
}
