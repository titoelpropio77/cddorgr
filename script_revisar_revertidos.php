<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/modelo_comprobantes.class.php';

revisar_revertidos();

function revisar_revertidos() {
    $conec = new ADO();
    $ven_ids = FUNCIONES::atributo_bd_sql("select group_concat(distinct vneg_ven_id ) as campo from venta_negocio where vneg_tipo ='reversion' and vneg_estado='Activado';");

    $ventas = FUNCIONES::lista_bd_sql("select * from venta where ven_id in ($ven_ids)");
    $num_max = 0;
    $det_2 = 0;
    $det_1 = 0;
    $cmp_cero = 0;
    $cmp_sincero = 0;
    foreach ($ventas as $venta) {
        $vnegocios = FUNCIONES::lista_bd_sql("select * from venta_negocio where vneg_ven_id='$venta->ven_id' and vneg_tipo in ('reversion','activacion')");
        if (count($vnegocios) > $num_max) {
            $num_max = count($vnegocios);
        }
        if (count($vnegocios) == 2) {
            /*
              $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' and cmp_tabla_id='$venta->ven_id'");
              if ($cmp) {
              if ($cmp->cmp_tarea_id == 0) {

              $vneg_id = $vnegocios[0]->vneg_id;
              $sql_up = "update con_comprobante set cmp_tarea_id=$vneg_id where cmp_id=$cmp->cmp_id";
              $conec->ejecutar($sql_up);
              $cmp_cero++;
              } else {
              $cmp_sincero++;
              }
              echo "CMP TAREA REVERSION $cmp->cmp_tarea_id<br>";
              } else {
              echo "NO EXISTE REVERSION $venta->ven_id <br>";
              }
             */

            $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_activacion' and cmp_tabla_id='$venta->ven_id'");

            if ($cmp) {
                if ($cmp->cmp_tarea_id == 0) {
                    //                    $vneg_id=$vnegocios[1]->vneg_id;
                    //                    $sql_up="update con_comprobante set cmp_tarea_id=$vneg_id where cmp_id=$cmp->cmp_id";
                    //                    $conec->ejecutar($sql_up);
                    $cmp_cero++;
                } else {
                    $cmp_sincero++;
                }
                echo "CMP TAREA REACTIVACION $cmp->cmp_tarea_id<br>";
            } else {
                echo "NO EXISTE REACTIVACION $venta->ven_id <br>";

                $vnreversion = FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_tipo='reversion' and vneg_ven_id=$venta->ven_id order by vneg_id desc limit 1");
                $vneg_id = 0;
                if ($vnreversion) {
                    $vneg_id = $vnreversion->vneg_id;
                }
                $_cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' and cmp_tabla_id='$venta->ven_id' and cmp_tarea_id='$vneg_id'");
                if ($_cmp) {

                    $vneg = $vnegocios[1];
                    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$vneg->vneg_ven_id'");
                    $glosa = "Reactivacion de la Venta Nro $venta->ven_id : $venta->ven_concepto";
                    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
                    $rdetalles = FUNCIONES::lista_bd_sql("select * from con_comprobante_detalle where cde_cmp_id=$_cmp->cmp_id and cde_mon_id=$_cmp->cmp_mon_id");
                    $data = array(
                        'moneda' => $venta->ven_moneda,
                        'ges_id' => 16,
                        'fecha' => $vneg->vneg_fecha,
                        'glosa' => $glosa,
                        'interno' => $_cmp->cmp_referido,
                        'tabla_id' => $venta->ven_id,
                        'tarea_id' => $vneg->vneg_id,
                        'urb' => $urb,
                        'rdetalles' => $rdetalles
                    );

                    $comprobante = MODELO_COMPROBANTE::venta_reactivacion($data);
                    $comprobante->usu_per_id = FUNCIONES::atributo_bd_sql("select usu_per_id as campo from ad_usuario where usu_id='$vneg->vneg_usu_cre'");
                    $comprobante->usu_id = $vneg->vneg_usu_cre;
                    COMPROBANTES::registrar_comprobante($comprobante);
                }
            }

            $det_2++;
        } elseif (count($vnegocios) == 1) {
            /*
              $det_1++;
              $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' and cmp_tabla_id='$venta->ven_id'");
              if ($cmp) {
              if ($cmp->cmp_tarea_id == 0) {
              echo "CMP TAREA $cmp->cmp_tarea_id<br>";
              $vneg_id = $vnegocios[0]->vneg_id;
              $sql_up = "update con_comprobante set cmp_tarea_id=$vneg_id where cmp_id=$cmp->cmp_id";
              $conec->ejecutar($sql_up);
              $cmp_cero++;
              } else {
              $cmp_sincero++;
              }
              echo "CMP TAREA $cmp->cmp_tarea_id<br>";
              } else {
              echo "NO EXISTE $venta->ven_id <br>";
              }
             */
        }
//        echo "------------------------- ".  count($vnegocios)." -------------------------<br>";
//        foreach ($vnegocios as $vneg) {
//            echo "$vneg->vneg_id,$vneg->vneg_tipo,$vneg->vneg_ven_id,$vneg->vneg_fecha <br>";
//        }
//        echo "--------------------------------------------------------<br>";
    }
    echo count($ventas) . '<br>';
    echo "DET 1: $det_1<br>";
    echo "DET 2: $det_2<br>";
    echo "SIN TAREA: $cmp_cero<br>";
    echo "CON TAREA: $cmp_sincero<br>";
    echo "MAX: $num_max<br>";
}

