<?php

require_once('venta.class.php');

$venta = new VENTA();
ADO::$modulo=$venta->modulo;
ADO::$modulo_id=$_GET[id];
ADO::$tarea=$_GET[tarea];
if ($_GET['tarea'] <> "") {
    if (!($venta->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            ADO::$modulo_id=-1;
            if ($_GET['acc'] == 'Emergente') {
                $venta->emergente();
            } else {
                if ($_GET['concretar'] == 'ok') {
                    $venta->concretar_venta_por_reserva();
                } else {
                    if ($venta->datos()) {
                        $venta->insertar_tcp();
                    } else {
                        $venta->formulario_tcp('blanco');
                    }
                }
            }
            break;
        }
    case 'VER': {
            $venta->ver();

            break;
        }
    case 'ANULAR': {
            $venta->anular();
            break;
        }


//    case 'PLAN PAGOS': {
//            require_once('venta_cuotas.class.php');
//            $vcuotas = new VENTA_CUOTAS();
//            $vcuotas->plan_pagos();
//            break;
//        }
    case 'AUTORIZACION': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->autorizacion_pago();
            break;
        }
    case 'PAGOS': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->pagos();
            break;
        }
    case 'SEGUIMIENTO': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->seguimiento();
            break;
        }
    case 'EXTRACTOS': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->extractos();
            break;
        }
    case 'REFORMULAR': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->reformular();
            break;
        }
    case 'IMPRIMIR': {
            $venta->imprimir_documento();
            break;
        }


//    case 'MORAS': {
//            $venta->moras();
//            break;
//        }

    case 'CAMBIAR PROPIETARIO': {
            $venta->cambio_propietario();
            break;
        }
    case 'CAMBIAR LOTE': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->cambiar_lote();
            break;
        }
    case 'FUSION': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->fusion_ventas();
            break;
        }
    case 'FIRMA CONTRATO': {
            $venta->firma_contrato();
            break;
        }

    case 'RETENER': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->retencion();
            break;
        }

    case 'DEVOLVER': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->devolver_pagado();
            break;
        }
    case 'VER DEVOLUCION': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->nota_comprobante_devolucion();
            break;
        }
    case 'INTERCAMBIO': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->intercambio();
            break;
        }
    case 'NOTA': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->nota();
            break;
        }
    case 'FECHA VALOR': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->fecha_valor();
            
            break;
        }
    case 'EDITAR DOCUMENTO': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->editar_documento();
            break;
        }
    case 'ACTIVAR': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->reactivar();
            break;
        }
    case 'DESBLOQUEAR CUOTAS': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->desbloquear_cuotas();
            break;
        }
    case 'DOCUMENTOS': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->documentos();
            break;
        }
    case 'IMPORTAR EXCEL': {
            require_once('venta_importar.class.php');
            $vnegocio = new VENTA_IMPORTAR();
            $vnegocio->importar_excel();
            break;
        }
        
    case 'PARAMETROS VARIOS': {
            require_once('venta_parametros.class.php');
            $vparams = new VENTA_PARAMETROS();
            $vparams->parametros_varios();
            break;
        }
        case 'CAMBIAR VENDEDOR': {
            require_once('venta_negocio.class.php');
            $vnegocio = new VENTA_NEGOCIO();
            $vnegocio->cambio_vendedor();
            break;
        }
        
        case 'CUPON': {
            require_once('venta_cuotas.class.php');
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->cupon();
            break;
        }
        
        case 'PRODUCTO': {
            include_once 'venta_producto.class.php';
            $vprod = new VENTA_PRODUCTO();
            $vprod->producto();
            break;
        }
        
        case 'RESERVA PRODUCTO': {
            include_once 'venta_producto.class.php';
            $vprod = new VENTA_PRODUCTO();
            $vprod->reserva_producto();
            break;
        }
    default: {
            if ($venta->verificar_permisos('ACCEDER')) {
                $venta->dibujar_busqueda();
            } else {
                ?>
                <script>
                    location.href = "log_out.php";
                </script>
                <?php
            }
            break;
        }
}
?>