<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';




//insertar_factura_test();
//generar_factura();
function insertar_factura_test() {
    require("clases/factura/Factura.php");
    $factura = new Factura();
    $datos = new stdClass();
    $sucursal = $factura->get_sucursal($factura::SUCURSAL_CASA_MATRIZ_ID);
    // Datos
    $nombre_cliente = 'orange factura ' . md5('orange' . rand(0, 99999)) . '_test';
    $nit_cliente = '10000' . mt_rand(250, 9999);
    $monto = mt_rand(10, 9999) + (mt_rand(1, 99)) / 100;
    $datos->nombre_cliente = $nombre_cliente;
    $datos->nit_cliente = $nit_cliente;
    $datos->fecha_transicion = date('Y-m-d');
    $datos->monto_transicion = $monto;
    $respuesta = $factura->guardar_factura($datos, $sucursal->suc_id);
    if (!$respuesta) {
        echo 'Fallo la prueba de guardar factura';
    } else {
        echo '<br> Guardo factura exitosamente';
    }
}

function generar_factura() {
    require_once ("clases/factura/Factura.php");
    $fac = new Factura();
    
    
    // Obtener sumatoria de monto total

    $sucursal = $fac->get_sucursal($fac::SUCURSAL_CASA_MATRIZ_ID);


    
    $factura = new stdClass();
    $factura->nombre_cliente = 'Orange Gruoup';
    $factura->nit_cliente = 0;
    $factura->sucursal_id = $sucursal->suc_id;
    
    $factura->fecha_transicion = $interno_deuda->ind_fecha_pago;
    $factura->monto_transicion = 200;    
    $factura->importe = 120;
    $factura->tabla = 'venta_pago';
    $factura->tabla_id = 0;
    
    $factura->detalles = array();
    $factura->detalles[] = array('concepto'=>'Capital','importe'=>80);
    $factura->detalles[] = array('concepto'=>'Multa','importe'=>50);
    $factura->detalles[] = array('concepto'=>'Interes','importe'=>50);
    $factura->detalles[] = array('concepto'=>'Formulario','importe'=>20);


    $factura_id = $fac->guardar_factura($factura);

    if ($factura_id) {
        echo "GUARDO CON EXITO";
    } else {
        echo "FALLO LA TRANSACCION";
//        // Registrar Detalle factura
//        foreach ($pagos_ids as $pago_id) {
//            $sql_detalle = "INSERT INTO `fac_factura_detalle` (`fde_fac_id`, `fde_tabla`, `fde_tabla_id`) VALUES ($factura_id, 'interno_deuda', $pago_id);";
//            $conexion->ejecutar($sql_detalle);
//        }
//        ?>
        <!--<table border="0" align="right"><tbody><tr><td><a title="VOLVER" href="gestor.php?mod=//<?php echo $this->modulo ?>&tarea=CUENTAS&id=<?php echo $interno_deuda->ind_int_id ?>"><img width="20" border="0" src="images/back.png"></a></td></tr></tbody></table>-->
        //<?php
//        echo $fac->get_html_factura($factura_id);
    }
}