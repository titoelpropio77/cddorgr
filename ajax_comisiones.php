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
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');


if ($_POST['tarea'] == 'fondos') {
    cargar_fondos($_POST);
}

if ($_POST['tarea'] == 'bonos') {
    cargar_bonos($_POST);
}

if ($_POST['tarea'] == 'periodos') {
//    echo "shit";
    cargar_periodos($_POST);
}

if ($_POST['tarea'] == 'periodos_debito') {
//    echo "shit";
    cargar_periodos_debito($_POST);
}

if ($_POST['tarea'] == 'debitos') {
//    echo "shit";
    lista_debitos($_POST[pdo_id]);
}

if ($_POST['tarea'] == 'fond') {
    $fondo = FUNCIONES::objeto_bd_sql("select * from fondo_diamante where fd_id='$_POST[fd_id]'");
    $params[utilidad] = $fondo->fd_utilidad;
    $params[porc_equipo] = $fondo->fd_porc_equipo;
    cargar_fondos($params);
}

if ($_POST['tarea'] == 'info_afiliado') {
    obtener_info();
}

if ($_GET[peticion] == 'info') {
    info_burbuja($_GET);
}

if (isset($_GET[red])) {
    $token = base64_decode($_GET[p]);
    $datos = explode('-', $token);

    if ($datos[1] != 'lapropia') {
        echo "ACCESO DENEGADO";
        return false;
    }

    $_POST[interno] = $datos[0];
    $_POST[nivel] = 7;
    require_once 'modulos/repvendedor_ramificacion/repvendedor_ramificacion.class.php';
    $rep = new REPVENDEDOR_RAMIFICACION();
    ?>
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <?php
    $rep->mostrar_reporte();
}

if ($_GET[tarea] == 'parametros_mlm') {
    obtener_parametros_mlm($_GET[lot_id]);
}

if ($_GET[peticion] == 'comision_periodo') {
    obtener_comision_periodo($_GET[vdo_id], $_GET[dp_id]);
}

function obtener_parametros_mlm($lot_id){
    $params = FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id=$lot_id");
    $res = new stdClass();
    
    if ($params) {
        $res->response = "ok";
        $obj_params = new stdClass();
        $obj_params->anticipo_minimo = $params->lm_anticipo_min;
        $obj_params->anticipo_tipo = $params->lm_anticipo_tipo;
        $obj_params->comision_base = $params->lm_comision_base;
        $obj_params->comision_contado = $params->lm_comision_contado;
        $obj_params->bra = $params->lm_bra;
        
        $res->data = $obj_params;
    } else {
        $res->response = "no";
    }
    echo json_encode($res);
}

function cargar_periodos_debito($params) {

//    $periodos = FUNCIONES::lista_bd_sql();
    $sql = "select p.pdo_id as id,p.pdo_descripcion as nombre from con_periodo p
        inner join comision_periodo cp on (p.pdo_id=cp.pdo_id)
    where p.pdo_ges_id=$params[gestion] and cp.pdo_estado='Cerrado'";
    
    $sql = "select p.pdo_id as id,p.pdo_descripcion as nombre from con_periodo p
        inner join comision_periodo cp on (p.pdo_id=cp.pdo_id)
    where p.pdo_ges_id=$params[gestion] and cp.pdo_estado in ('Abierto','Cerrado')";
    
    $fun = new FUNCIONES();
    echo "<select id='pdo_id' name='pdo_id'>";
    $fun->combo($sql, $valor);
    echo "</select>";
}

function cargar_periodos($params) {

//    $periodos = FUNCIONES::lista_bd_sql();
    $sql = "select pdo_id as id,pdo_descripcion as nombre from con_periodo where pdo_ges_id=$params[gestion]";
    $fun = new FUNCIONES();
    echo "<select id='pdo_id' name='pdo_id'>";
    $fun->combo($sql, $valor);
    echo "</select>";
}

function cargar_fondos($params) {
//    echo "$params[gestion]-$params[utilidad]-$params[porc_equipo]";
    $gestion = FUNCIONES::objeto_bd_sql("select * from con_gestion where ges_id='$params[gestion]'");
//    var_dump($gestion);
    ?>
    <div id="contenido_reporte">
        <style>
            .derecha{
                text-align: right;
            }
        </style> 
        <h2>Distribucion para Equipo Diamante</h2><br/>
        <center>
            <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                <thead>
                <th>
                    Vendedor
                </th>
                <th>
                    Monto
                </th>
                <th>
                    %
                </th>
                </thead>
                <tbody>
                    <?php
                    $equipo = obtener_equipo("PERLA");
                    $monto = $params[utilidad] * 1 * $params[porc_equipo] / 100;
                    $monto /= $equipo->get_num_registros();
                    $porc = $params[porc_equipo] / $equipo->get_num_registros();
                    $totalMonto = 0;
                    $totalPorc = 0;
                    for ($i = 0; $i < $equipo->get_num_registros(); $i++) {
                        $vendedor = $equipo->get_objeto();
                        ?>
                        <tr>
                            <td><?php echo $vendedor->nombre; ?></td>
                            <td class="derecha"><?php echo number_format($monto, 2, '.', ',') ?></td>
                            <td class="derecha"><?php echo number_format($porc, 2, '.', ',') ?></td>
                        </tr>
                        <?php
                        $totalMonto += $monto;
                        $totalPorc += $porc;
                        $equipo->siguiente();
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><b>Totales:</b></td>
                        <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                        <td class="derecha"><?php echo number_format($totalPorc, 2, '.', ',') ?></td>
                    </tr>
                </tfoot>
            </table>
        </center>
    </div>
    <?php
}

function cargar_bonos($params) {
    $pdo_id = $params[pdo_id];
    $vdo_id = $params[vdo_id];
    if ($params[recalcular] == 1) {
        $data = array('pdo_id' => $pdo_id, 'origen' => 'vista_previa', 'vdo_id' => $vdo_id);
        $marca_tmp = MLM::generar_bonos($data);
        $tabla = "comision_tmp";
    } else {
        $tabla = "comision";
    }

    require_once('modulos/bonos/bonos.class.php');
    $obj_bonos = new BONOS();
    $data_bonos = array(
        'pdo_id' => $pdo_id,
        'vdo_id' => $vdo_id,
        'tabla' => $tabla,
        'agrupado_por' => $params[agrupado_por],
        'origen' => 'vista_previa', 
        'marca_tmp' => $marca_tmp
    );
    $obj_bonos->ver_bonos($data_bonos);

    return FALSE;
}

function obtener_equipo($rango) {
    include_once 'modulos/fondo_diamante/fondo_diamante.class.php';
    $cl_obj = new FONDO();
    return $cl_obj->obtener_equipo($rango);
}

function info_burbuja($data) {
    require_once 'clases/cartera.class.php';
    $datos = array(
        'venta' => $data[venta],
        'desc_estado' => $data[desc_estado]
    );
    $html = CARTERA::info($datos);
    $html = base64_encode($html);

    $json = '{"response":"ok","info":"' . $html . '"}';
    echo $json;
}

function obtener_info() {
    require_once 'clases/cartera.class.php';
    echo CARTERA::info_afiliado($_POST[id]);
}

function lista_debitos($pdo_id){
    
    $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
    inner join con_gestion on (pdo_ges_id=ges_id)
    where pdo_id='$pdo_id'");
    $comision_periodo = FUNCIONES::objeto_bd_sql("select * from comision_periodo where pdo_id='$pdo_id'");
    $next_periodo = FUNCIONES::atributo_bd_sql("select left(DATE_ADD('$periodo->pdo_fecha_inicio',INTERVAL 1 month),7)as campo");
        
    $sql = "select concat(int_nombre,' ',int_apellido)as afiliado,
    vdo_id,vdo_venta_inicial,sum(com_monto - com_pagado)as comision_mensual,
    ind_fecha_programada,ind_monto,ind_monto_pagado,ind_estado,ind_id from
    vendedor inner join comision on (vdo_id=com_vdo_id)
    inner join comision_periodo on (com_pdo_id=pdo_id)
    inner join interno on (vdo_int_id=int_id)
    inner join interno_deuda on (ind_tabla='venta' and ind_tabla_id=vdo_venta_inicial)
    where pdo_id='$pdo_id' and pdo_estado in('Cerrado')
    and com_estado='Pendiente'
    and ind_estado='Pendiente'
    and left(ind_fecha_programada,7) = '$next_periodo'
    and vdo_debitar='Si'
    group by vdo_id
    order by afiliado asc";
    
    $lista_afil = FUNCIONES::lista_bd_sql($sql);
    
    if ($_SESSION[id] == 'admin') {
        echo "<p style='color:white'>".$sql."</p>";
    }
    if (count($lista_afil) > 0) {
    ?>
    <!--Inicio-->
    <div id="contenido_reporte" style="clear:both;">
        <center>
            <br/><br/><h3>Lista de Debitos Automaticos</h3>
            <h3>Periodo de Comisiones: <?php echo strtoupper($periodo->pdo_descripcion." - " . $periodo->ges_descripcion);?></h3><br/>
            <br/>
            <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                <thead>
                        <th>
                            ID
                        </th>                        
                        <th>
                            Afiliado
                        </th>                        
                        <th>
                            Codigo
                        </th>
                        <th>
                            Lote
                        </th>
                        <th>
                            Comision del Periodo
                        </th>
                        <th>
                            Fecha de Sig. Cuota
                        </th>
                        <th>
                            Monto de Sig. Cuota
                        </th>
                        <th>
                            Monto a Debitar
                        </th>
                        <th>
                            Saldo de la Cuota
                        </th>
                        <th>
                            Saldo de Com. de Periodo
                        </th>
                        <th>
                            Seleccion: <input type="checkbox" id="chk_todos" name="chk_todos" checked />
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($lista_afil as $elem) {
                                $lote = FUNCIONES::get_concepto_corto($elem->vdo_venta_inicial);
                                $monto_cuota = $elem->ind_monto - $elem->ind_monto_pagado;                                
                                $monto_debito = ($monto_cuota <= $elem->comision_mensual) ? $monto_cuota : $elem->comision_mensual;
                                $saldo_cuota = $monto_cuota - $monto_debito;
                                $saldo_com_pdo = $elem->comision_mensual - $monto_debito;
                                
                                if ($elem->comision_mensual == 0) {
                                    continue;
                                }
                                ?>
                            <tr>
                                <td><?php echo $elem->vdo_id;?></td>
                                <td><?php echo $elem->afiliado;?></td>
                                <td><?php echo $elem->vdo_venta_inicial;?></td>
                                <td><?php echo $lote;?></td>
                                <td><?php echo number_format($elem->comision_mensual, 2, '.', ',');?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($elem->ind_fecha_programada);?></td>
                                <td><?php echo number_format($monto_cuota, 2, '.', ',');?></td>
                                <td>
                                    <input data-ven_id="<?php echo $elem->vdo_venta_inicial;?>" 
                                    data-vdo_id="<?php echo $elem->vdo_id;?>" 
                                    class="montos_debito" type="text" id="debito_<?php echo $elem->vdo_id;?>" name="monto_debito[]" 
                                    value="<?php echo $monto_debito;?>" 
                                    data-debito_maximo="<?php echo $monto_debito;?>" data-ind_id="<?php echo $elem->ind_id;?>" 
                                    data-comision_mensual="<?php echo $elem->comision_mensual;?>" data-afiliado="<?php echo $elem->afiliado;?>"
                                    data-cuota_mensual="<?php echo $monto_cuota;?>" data-terreno="<?php echo $lote;?>" />
                                    <br>                                    
                                    <p style="display: none; color:red;" id="msj_<?php echo $elem->vdo_id;?>"></p>
                                </td>
                                <td data-valor="<?php echo $saldo_cuota;?>" id="saldo_cuota_<?php echo $elem->vdo_id;?>"><?php echo $saldo_cuota;?></td>
                                <td data-valor="<?php echo $saldo_com_pdo;?>" id="saldo_com_pdo_<?php echo $elem->vdo_id;?>"><?php echo $saldo_com_pdo;?></td>
                                <td><input type="checkbox" checked class="chks_debito" name="chk_debito[]" data-debito="debito_<?php echo $elem->vdo_id;?>"></td>
                            </tr>
                                <?php
                            }
                            ?>
                        </tbody>
            </table>
            
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>                        
                        <?php
                        if ($comision_periodo->pdo_estado == 'Cerrado') {
                        ?>
                            <input type="button" class="boton" name="aceptar" value="Aceptar" onclick="enviar();">
                        <?php
                        }
                        ?>    
                            <input type="reset" class="boton" name="" value="Cancelar" onclick="javascript:self.close();">                            
                    </center>
                </div>
            </div>
            
        </center>
    </div>
    <!--Fin-->
    <?php
    } else {
        ?>
        <h2>No existen Afiliados para realizar Debitacion Automatica.</h2>
        <div id="ContenedorDiv">
            <div id="CajaBotones">
                <center>                                            
                    <input type="reset" class="boton" name="" value="Cancelar" onclick="javascript:self.close();">                            
                </center>
            </div>
        </div>
        <?php
    }
    ?>
    <link type="text/css" href="css/impromptu.css">
    
    <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
    <script type="text/javascript" src="js/util.js"></script>
    <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
    <script>
//        var montos_debito = $('.montos_debito');
        $('.montos_debito').each(function () {
            mask_decimal(this, null);
        });        
        
        $('#chk_todos').click(function(){
            if( $('#chk_todos').is(':checked') ){
                $('.chks_debito').attr('checked', true);
            } else {
                $('.chks_debito').attr('checked', false);
            }
        });
        
        
        $('.chks_debito').click(function(){
            var chk = true;
            $('.chks_debito').each(function(){                
                if (!$(this).is(':checked')) {
                    chk = false;
                }
            });
            if (!chk) {
                $('#chk_todos').attr('checked', false);
            } else {
                $('#chk_todos').attr('checked', true);
            }
        });
        
        $('.montos_debito').keyup(function(){

            var debito_maximo = $(this).attr('data-debito_maximo')*1;
            var valor = $(this).val()*1;
            var vdo_id = $(this).attr('data-vdo_id')*1;
            
            var saldo_cuota = $('#saldo_cuota_'+vdo_id).attr('data-valor')*1;
            var saldo_com_pdo = $('#saldo_com_pdo_'+vdo_id).attr('data-valor')*1;
            
            
            if (valor > debito_maximo) {
                
                var th = $(this);                
                $('#saldo_cuota_'+vdo_id).text(saldo_cuota);
                $('#saldo_com_pdo_'+vdo_id).text(saldo_com_pdo);
                $('#msj_'+vdo_id).text('El monto a debitar no puede ser mayor a ' + debito_maximo);

                var opciones = {duration:1000, easing:'linear', complete: function(){                    
                    $('#msj_'+vdo_id).fadeOut(1000);
                    th.val(debito_maximo);                    
                    console.log('llamando al fadeout...');
                }};

                $('#msj_'+vdo_id).fadeIn(opciones);                
            } else {
                var saldo_cuota = ($(this).attr('data-cuota_mensual')*1) - valor;
                var saldo_com_pdo = ($(this).attr('data-comision_mensual')*1) - valor;
                $('#saldo_cuota_'+vdo_id).text(saldo_cuota);
                $('#saldo_com_pdo_'+vdo_id).text(saldo_com_pdo);
            }
        });
                        
        function enviar(){
            
            var debitos = [];
            
            $('.chks_debito').each(function(){
                
                if ($(this).is(':checked')) {
                    var deb_id = $(this).attr('data-debito');
                    var md = $('#'+deb_id);
                    var debito = {
                        vdo_id: md.attr('data-vdo_id'),
                        afiliado: md.attr('data-afiliado'),
                        ind_id: md.attr('data-ind_id'),
                        ven_id: md.attr('data-ven_id'),
                        terreno: md.attr('data-terreno'),
                        debito: md.val(),
                        cuota_mensual: md.attr('data-cuota_mensual')*1,
                        debito_maximo: md.attr('data-debito_maximo')*1,
                        saldo_cuota: $('#saldo_cuota_' + md.attr('data-vdo_id')).text()*1,
                        saldo_comision_periodo: $('#saldo_com_pdo_' + md.attr('data-vdo_id')).text()*1,
                        comision_acumulada: md.attr('data-comision_mensual')*1                        
                    };
                    debitos.push(debito);
                }
            });
            
            window.opener.recibir_debitos(debitos);            
            self.close();
        }
        
    </script>
    <?php
}

function obtener_comision_periodo($vdo_id, $dp_id){
    $sql_per = "select * from debito_periodo where dp_id='$dp_id'";
//    echo "<p>$sql_per</p>";
    
    $debito_periodo = FUNCIONES::objeto_bd_sql($sql_per);
            
    $resp = new stdClass();
    if ($debito_periodo) {
        
        $pdo_id = $debito_periodo->dp_pdo_id;
        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
        inner join con_gestion on (pdo_ges_id=ges_id)
        where pdo_id='$pdo_id'");
        $comision_periodo = FUNCIONES::objeto_bd_sql("select * from comision_periodo where pdo_id='$pdo_id'");
        $next_periodo = FUNCIONES::atributo_bd_sql("select left(DATE_ADD('$periodo->pdo_fecha_inicio',INTERVAL 1 month),7)as campo");
                
        $sql = "select concat(int_nombre,' ',int_apellido)as afiliado,
        vdo_id,vdo_venta_inicial,sum(com_monto - com_pagado)as comision_mensual,
        ind_fecha_programada,ind_monto,ind_monto_pagado,ind_estado,ind_id,pdo_id from
        vendedor inner join comision on (vdo_id=com_vdo_id)
        inner join comision_periodo on (com_pdo_id=pdo_id)
        inner join interno on (vdo_int_id=int_id)
        inner join interno_deuda on (ind_tabla='venta' and ind_tabla_id=vdo_venta_inicial)
        where pdo_id='$pdo_id' and pdo_estado in('Abierto','Cerrado')
        and com_estado='Pendiente'
        and ind_estado in ('Pendiente','Pagado')
        and left(ind_fecha_programada,7) = '$next_periodo'
        and vdo_debitar='Si'
        and vdo_id='$vdo_id'
        group by vdo_id
        order by afiliado asc";
        
//        echo "<p>$sql</p>";
        $datos_comision = FUNCIONES::objeto_bd_sql($sql);
        
        if ($datos_comision == NULL) {            
            $resp->exito = 'no';
            $resp->mensaje = FUNCIONES::limpiar_cadena("No existe la información del afiliado con respecto a la comision y cuota mensual con los datos indicados");            
            echo json_encode($resp);
            return FALSE;
        }
        
        if ($datos_comision->ind_estado == 'Pagado') {
            $resp->exito = 'no';
            $resp->mensaje = FUNCIONES::limpiar_cadena("La cuota mensual del mes siguiente se encuentra pagada.");            
            echo json_encode($resp);
            return FALSE;
        }
        
        if ($datos_comision->comision_mensual == 0) {
            $resp->exito = 'no';
            $resp->mensaje = FUNCIONES::limpiar_cadena("La comision mensual del periodo es 0(cero).");            
            echo json_encode($resp);
            return FALSE;
        }
        
        $resp->exito = 'si';
        $resp->comision_mensual = $datos_comision->comision_mensual;
        $resp->ind_id = $datos_comision->ind_id;
        $resp->pdo_id = $datos_comision->pdo_id;
        echo json_encode($resp);
        return TRUE;
    } else {
        $resp->exito = 'no';
        $resp->mensaje = FUNCIONES::limpiar_cadena("No existe el periodo de debitacion con los datos indicados");
        echo json_encode($resp);
        return FALSE;
    }
}