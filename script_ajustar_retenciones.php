<?php
session_start();
ini_set('display_errors', 'On');
//ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';

if ($_POST) {
    procesar();
} else {
    formulario();
}

function formulario() {
    ?>
    <form id="frm_ejecutar" name="frm_ejecutar" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER[PHP_SELF]; ?>">

        VENTA:<input type="text" id="venta" name="venta" value="" /><br/>
        CMP:<input type="text" id="cmp" name="cmp" value="" />
        <input type="button" id="btn_enviar" name="btn_enviar" value="EJECUTAR" />
        <input type="hidden" id="ejecutar" name="ejecutar" value="ok" />
    </form>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#btn_enviar').click(function () {
                if ($('#venta').val() * 1 === 0) {
                    alert('Ingrese la venta...');
                    return false;
                }
                if ($('#cmp').val() * 1 === 0) {
                    alert('Ingrese el comprobante...');
                    return false;
                }
                $('#frm_ejecutar').submit();
            });
        });
    </script>
    <?php
}

function procesar() {

    FUNCIONES::print_pre($_POST);

    $ven_id = $_POST[venta];
    $cmp_id = $_POST[cmp];

    $conec = new ADO();

    $obj_cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_id='$cmp_id'");

    if ($obj_cmp == NULL) {
        echo "<p style='color:red;'>NO EXISTE EL COMPROBANTE A MODIFICAR...</p>";
        return false;
    }

    $sql = "select * from venta where ven_id='$ven_id'";
    $venta = FUNCIONES::objeto_bd_sql($sql);

    if ($venta == NULL) {
        echo "<p style='color:red;'>NO EXISTE LA VENTA DE LA RETENCION...</p>";
        return false;
    }

    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

    $pagado = total_pagado($venta->ven_id);

    $tot_pagado = $venta->ven_res_anticipo + $venta->ven_venta_pagado + $pagado->capital;
    $saldo = ($venta->ven_monto_efectivo + $pagado->incremento) - ($pagado->capital + $pagado->descuento);
    $saldo_costo = $venta->ven_costo - $venta->ven_costo_cub;
    $costo_pagado_cuotas = $pagado->capital * ($saldo_costo / $venta->ven_monto_efectivo);

    $monto_intercambio = $venta->ven_monto_intercambio;

    $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto 
        from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");

    $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto 
        from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id 
            group by vipag_inter_id order by inter_id asc");

    $data = array(
        'moneda' => $obj_cmp->cmp_mon_id,
        'ges_id' => $obj_cmp->cmp_ges_id,
        'fecha' => $obj_cmp->cmp_fecha,
        'glosa' => $obj_cmp->cmp_glosa,
        'interno' => $obj_cmp->cmp_referido,
        'tabla_id' => $obj_cmp->cmp_tabla_id,
        'tarea_id' => $obj_cmp->cmp_tarea_id,
        'urb' => $urb,
        'costo' => $venta->ven_costo,
        'costo_pagado' => $venta->ven_costo_cub + $costo_pagado_cuotas,
        'saldo_efectivo' => $saldo,
        'total_pagado' => $tot_pagado,
        'intercambio' => $monto_intercambio,
        'inter_montos' => $amontos,
        'inter_montos_pag' => $amontos_pag,
    );


    if ($urb->urb_tipo == 'Interno') {
        $comprobante = MODELO_COMPROBANTE::venta_retencion($data);
    } else if ($urb->urb_tipo == 'Externo') {
        $comprobante = MODELO_COMPROBANTE::venta_retencion_ext($data);
    }
    $comprobante->cmp_id = $obj_cmp->cmp_id;
    $comprobante->usu_per_id = $obj_cmp->cmp_usu_id;
    $comprobante->usu_id = $obj_cmp->cmp_usu_cre;

    COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
}

function total_pagado($ven_id) {

    $sql_pag = "select sum(vpag_interes)as interes, sum(vpag_capital) as capital, sum(vpag_monto) as monto, 
    sum(vpag_capital_desc) as descuento, sum(vpag_capital_inc) as incremento ,
    sum(vpag_costo) as costo
    from venta_pago where vpag_ven_id=$ven_id";

    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    return $pagado;
}
