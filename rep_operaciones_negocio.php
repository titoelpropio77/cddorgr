<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

function rep_reversiones() {
    
    $sql = "select * from venta_negocio "
            . "inner join venta on(vneg_ven_id=ven_id) "
            . "inner join interno on(ven_int_id=int_id) "
            . "inner join urbanizacion on(ven_urb_id=urb_id) "
            . "inner join lote on(ven_lot_id=lot_id) "
            . "inner join manzano on(lot_man_id=man_id) "
            . "inner join con_moneda on(ven_moneda=mon_id) "
            . "where vneg_tipo='reversion' and vneg_estado='Activado'";
    $reversiones = FUNCIONES::objetos_bd_sql($sql);

    

    if (isset($_GET[excel])) {
        $filename = "reporte.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
    } else {
        ?>
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.thfloat-0.7.2.min.js"></script>
        <?php
    }
    ?>
    <table id="tabla" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>VENTA</th>
                <th>PROYECTO</th>
                <th>MANZANO</th>
                <th>LOTE</th>            
                <th>TITULAR</th>
                <th>FECHA DE REVERSION</th>
                <th>USUARIO</th>
                <th>MONTO VENTA</th>
                <th>CUOTA INICIAL</th>                                
                <th>CAP. DESCONTADO</th>
                <th>CAP. INCREMENTADO</th>
                <th>CAP. PAGADO</th>
                <th>SALDO</th>
                <th>MONEDA</th>
                <th>OBSERVACION</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            
            for ($i = 0; $i < $reversiones->get_num_registros(); $i++) {
                $rev = $reversiones->get_objeto();
                $params = json_decode($rev->vneg_parametros);
                
                $saldo_financiar = $rev->ven_monto_efectivo;                
                $cuota_inicial = $rev->ven_res_anticipo;
                $monto_venta = $saldo_financiar + $cuota_inicial;
                $cap_desc = $rev->ven_capital_desc;
                $cap_inc = $rev->ven_capital_inc;
                $moneda = $rev->mon_Simbolo;
                ?>
                <tr>
                    <td><?php echo $i + 1;?></td>
                    <td><?php echo $rev->vneg_ven_id;?></td>
                    <td><?php echo $rev->urb_nombre;?></td>
                    <td><?php echo $rev->man_nro;?></td>
                    <td><?php echo $rev->lot_nro;?></td>
                    <td><?php echo "$rev->int_nombre $rev->int_apellido";?></td>
                    <td><?php echo FUNCIONES::get_fecha_latina($rev->vneg_fecha);?></td>
                    <td><?php echo $rev->vneg_usu_cre;?></td>                                        
                    <td><?php echo $monto_venta * 1;?></td>
                    <td><?php echo $cuota_inicial * 1;?></td>
                    <td><?php echo $cap_desc * 1;?></td>
                    <td><?php echo $cap_inc * 1;?></td>
                    <td><?php echo $params->total_pagado * 1;?></td>
                    <td><?php echo $params->saldo_efectivo * 1;?></td>
                    <td><?php echo $moneda;?></td>
                    <td><?php echo $rev->vneg_observacion;?></td>
                </tr>    
                <?php
                $reversiones->siguiente();
            }                                    
            ?>        
        </tbody>
    </table>
    <?php
    if (!isset($_GET[excel])) {
        ?>
        <script>
            $("#tabla").thfloat();
        </script>
        <?php
    }
}

function rep_fusiones() {
    
    $sql = "select * from venta_negocio "
            . "inner join venta on(vneg_ven_ori=ven_id) "
            . "inner join interno on(ven_int_id=int_id) "
            . "inner join urbanizacion on(ven_urb_id=urb_id) "
            . "inner join lote on(ven_lot_id=lot_id) "
            . "inner join manzano on(lot_man_id=man_id) "
            . "inner join con_moneda on(ven_moneda=mon_id) "
            . "where vneg_tipo='fusion' and vneg_estado='Activado'";
    $reversiones = FUNCIONES::objetos_bd_sql($sql);

    

    if (isset($_GET[excel])) {
        $filename = "reporte.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
    } else {
        ?>
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.thfloat-0.7.2.min.js"></script>
        <?php
    }
    ?>
    <table id="tabla" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>VENTA</th>
                <th>PROYECTO</th>
                <th>MANZANO</th>
                <th>LOTE</th>            
                <th>TITULAR</th>
                <th>FECHA DE FUSION</th>
                <th>USUARIO</th>
                <th>MONTO VENTA</th>
                <th>CUOTA INICIAL</th>                                
                <th>CAP. DESCONTADO</th>
                <th>CAP. INCREMENTADO</th>
                <th>CAP. PAGADO</th>
                <th>SALDO</th>
                <th>MONEDA</th>
                <th>VENTA DESTINO</th>                
                <th>OBSERVACION</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            
            for ($i = 0; $i < $reversiones->get_num_registros(); $i++) {
                $rev = $reversiones->get_objeto();
                $params = json_decode($rev->vneg_parametros);
                $cap_cuotas = $params->ori_tot_capital - $rev->ven_res_anticipo;                
                $saldo_efectivo = $rev->ven_monto_efectivo - $cap_cuotas;
                $total_pagado = $params->ori_tot_capital;
                $saldo_financiar = $rev->ven_monto_efectivo;                
                $cuota_inicial = $rev->ven_res_anticipo;
                $monto_venta = $saldo_financiar + $cuota_inicial;
                $cap_desc = $rev->ven_capital_desc;
                $cap_inc = $rev->ven_capital_inc;
                $moneda = $rev->mon_Simbolo;
                ?>
                <tr>
                    <td><?php echo $i + 1;?></td>
                    <td><?php echo $rev->vneg_ven_ori?></td>
                    <td><?php echo $rev->urb_nombre;?></td>
                    <td><?php echo $rev->man_nro;?></td>
                    <td><?php echo $rev->lot_nro;?></td>
                    <td><?php echo "$rev->int_nombre $rev->int_apellido";?></td>
                    <td><?php echo FUNCIONES::get_fecha_latina($rev->vneg_fecha);?></td>
                    <td><?php echo $rev->vneg_usu_cre;?></td>
                    <td><?php echo $monto_venta * 1;?></td>
                    <td><?php echo $cuota_inicial * 1;?></td>                    
                    <td><?php echo $cap_desc * 1;?></td>
                    <td><?php echo $cap_inc * 1;?></td>
                    <td><?php echo $total_pagado * 1;?></td>
                    <td><?php echo $saldo_efectivo * 1;?></td>                                        
                    <td><?php echo $moneda;?></td>                                        
                    <td><?php echo $rev->vneg_ven_id;?></td>
                    <td><?php echo $rev->vneg_observacion;?></td>
                </tr>    
                <?php
                $reversiones->siguiente();
            }                                    
            ?>        
        </tbody>
    </table>
    <?php
    if (!isset($_GET[excel])) {
        ?>
        <script>
            $("#tabla").thfloat();
        </script>
        <?php
    }
}

function rep_activaciones() {
    
    $sql = "select * from venta_negocio "
            . "inner join venta on(vneg_ven_id=ven_id) "
            . "inner join interno on(ven_int_id=int_id) "
            . "inner join urbanizacion on(ven_urb_id=urb_id) "
            . "inner join lote on(ven_lot_id=lot_id) "
            . "inner join manzano on(lot_man_id=man_id) "
            . "inner join con_moneda on(ven_moneda=mon_id) "
            . "where vneg_tipo='activacion' and vneg_estado='Activado'";
    $reversiones = FUNCIONES::objetos_bd_sql($sql);

    

    if (isset($_GET[excel])) {
        $filename = "reporte.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
    } else {
        ?>
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.thfloat-0.7.2.min.js"></script>
        <?php
    }
    ?>
    <table id="tabla" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>VENTA</th>
                <th>PROYECTO</th>
                <th>MANZANO</th>
                <th>LOTE</th>            
                <th>TITULAR</th>
                <th>FECHA DE ACTIVACION</th>
                <th>USUARIO</th>
                <th>MONEDA</th>
                <th>OBSERVACION</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            
            for ($i = 0; $i < $reversiones->get_num_registros(); $i++) {
                $rev = $reversiones->get_objeto();                
                $moneda = $rev->mon_Simbolo;
                ?>
                <tr>
                    <td><?php echo $i + 1;?></td>
                    <td><?php echo $rev->vneg_ven_id?></td>
                    <td><?php echo $rev->urb_nombre;?></td>
                    <td><?php echo $rev->man_nro;?></td>
                    <td><?php echo $rev->lot_nro;?></td>
                    <td><?php echo "$rev->int_nombre $rev->int_apellido";?></td>
                    <td><?php echo FUNCIONES::get_fecha_latina($rev->vneg_fecha);?></td>
                    <td><?php echo $rev->vneg_usu_cre;?></td>
                    <td><?php echo $moneda;?></td>
                    <td><?php echo $rev->vneg_observacion;?></td>
                </tr>    
                <?php
                $reversiones->siguiente();
            }                                    
            ?>        
        </tbody>
    </table>
    <?php
    if (!isset($_GET[excel])) {
        ?>
        <script>
            $("#tabla").thfloat();
        </script>
        <?php
    }
}

function rep_cambios_lote() {
    
    $sql = "select * from venta_negocio "
            . "inner join venta on(vneg_ven_id=ven_id) "
            . "inner join interno on(ven_int_id=int_id) "
            . "inner join urbanizacion on(ven_urb_id=urb_id) "
            . "inner join lote on(ven_lot_id=lot_id) "
            . "inner join manzano on(lot_man_id=man_id) "
            . "inner join con_moneda on(ven_moneda=mon_id) "
            . "where vneg_tipo='cambio_lote' and vneg_estado='Activado'";
    $reversiones = FUNCIONES::objetos_bd_sql($sql);

    

    if (isset($_GET[excel])) {
        $filename = "reporte.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
    } else {
        ?>
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.thfloat-0.7.2.min.js"></script>
        <?php
    }
    ?>
    <table id="tabla" border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>VENTA</th>
                <th>PROYECTO</th>
                <th>MANZANO</th>
                <th>LOTE</th>            
                <th>NUEVO PROYECTO</th>
                <th>NUEVO MANZANO</th>
                <th>NUEVO LOTE</th>            
                <th>TITULAR</th>
                <th>FECHA DE CAMBIO</th>
                <th>USUARIO</th>
                <th>MONEDA</th>
                <th>OBSERVACION</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            
            for ($i = 0; $i < $reversiones->get_num_registros(); $i++) {
                $rev = $reversiones->get_objeto();                
                $moneda = $rev->mon_Simbolo;
                $params = json_decode($rev->vneg_parametros);
                $sql_lot_ant = "select * from lote "
                        . "inner join manzano on(lot_man_id=man_id) "
                        . "inner join urbanizacion on (man_urb_id=urb_id) "
                        . "where lot_id='$params->new_lot_id' "
                        . "and urb_id='$params->new_urb_id' "
                        . "and man_id='$params->new_man_id'";
                $lot_new = FUNCIONES::objeto_bd_sql($sql_lot_ant);
                ?>
                <tr>
                    <td><?php echo $i + 1;?></td>
                    <td><?php echo $rev->vneg_ven_id?></td>
                    <td><?php echo $rev->urb_nombre;?></td>
                    <td><?php echo $rev->man_nro;?></td>
                    <td><?php echo $rev->lot_nro;?></td>
                    <td><?php echo $lot_new->urb_nombre;?></td>
                    <td><?php echo $lot_new->man_nro;?></td>
                    <td><?php echo $lot_new->lot_nro;?></td>
                    <td><?php echo "$rev->int_nombre $rev->int_apellido";?></td>
                    <td><?php echo FUNCIONES::get_fecha_latina($rev->vneg_fecha);?></td>
                    <td><?php echo $rev->vneg_usu_cre;?></td>
                    <td><?php echo $moneda;?></td>
                    <td><?php echo $rev->vneg_observacion;?></td>
                </tr>    
                <?php
                $reversiones->siguiente();
            }                                    
            ?>        
        </tbody>
    </table>
    <?php
    if (!isset($_GET[excel])) {
        ?>
        <script>
            $("#tabla").thfloat();
        </script>
        <?php
    }
}

if ($_GET[operacion] == 'reversiones'){
    rep_reversiones();
} else if ($_GET[operacion] == 'fusiones') {
    rep_fusiones();
} else if ($_GET[operacion] == 'activaciones') {
    rep_activaciones();
} else if ($_GET[operacion] == 'cambio_lote') {
    rep_cambios_lote();
}
?>
<p><a href="<?php echo "rep_operaciones_negocio.php?operacion=reversiones&excel=si";?>">REVERSIONES</a></p>
<p><a href="<?php echo "rep_operaciones_negocio.php?operacion=fusiones&excel=si";?>">FUSIONES</a></p>
<p><a href="<?php echo "rep_operaciones_negocio.php?operacion=activaciones&excel=si";?>">ACTIVACIONES</a></p>
<p><a href="<?php echo "rep_operaciones_negocio.php?operacion=cambio_lote&excel=si";?>">CAMBIOS DE LOTE</a></p>