<?php

require_once('con_comprobante.class.php');

$con_comprobante = new con_comprobante();

if ($_GET['tarea'] <> "") {
    if (!($con_comprobante->verificar_permisos($_GET['tarea']))) {
        ?>
        <script>
            location.href = "log_out.php";
        </script>
        <?php

    }
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            if ($_GET['acc'] == 'Emergente') {
                $con_comprobante->emergente();
            } else {

                if ($con_comprobante->datos()) {
                    //echo '<br>INSERTAR ..... <br>';
//                    echo $_POST['generar_ajuste']."<br>";
                    if($_POST['generar_ajuste']){
                        if($_POST['comp_tipo_ajuste']=='aj_ufv'){
                            $con_comprobante->generar_cmp_ajuste_ufv();
                        }elseif ($_POST['comp_tipo_ajuste']=='dif_camb'){
                            $con_comprobante->generar_cmp_ajuste_dif_camb();
                        }elseif ($_POST['comp_tipo_ajuste']=='aj_1'){
                            $con_comprobante->mensaje="Seleccione...";
                        }else{
                            $con_comprobante->mensaje="Seleccione un <b>Tipo de Ajuste</b> para generar";
                        }                            
                        $con_comprobante->formulario_tcp('blanco');
                    }else{
//                        _PRINT::txt("insertar");
                        $con_comprobante->insertar_tcp();
                    }
                } else {
                    $con_comprobante->formulario_tcp('blanco');
                }
            }

            break;
        }
    case 'VER': {
            $con_comprobante->imprimir_cmp($_GET['id']);
            break;
        }
    case 'MODIFICAR': {
            if ($con_comprobante->datos()) {
                $con_comprobante->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $con_comprobante->cargar_datos();
                    $cmp_ges_id=$_POST['cmp_ges_id'];
                }else{
                    $cmp_ges_id=  FUNCIONES::atributo_bd_sql("select pdo_ges_id as campo from con_periodo where pdo_id='$_POST[cmp_peri_id]'");
                }
//                FUNCIONES::print_pre($_POST);
//                $cmp_ges_id=  FUNCIONES::atributo_bd_sql("select pdo_ges_id as campo from con_periodo where pdo_id='$_POST[cmp_peri_id]'");
                if($cmp_ges_id==$_SESSION['ges_id']){
                    $con_comprobante->formulario_tcp('cargar');
                }else{
                    $mensaje='No puede Modificar el comprobante por que fue realizado en una gestion diferente a la gestion en la cual usted esta trabajando ahora.';		
                    $con_comprobante->formulario->ventana_volver($mensaje,$con_comprobante->link.'?mod='.$con_comprobante->modulo.'&tarea=ACCEDER','','error');
                }
            }
            break;
        }

    case 'ELIMINAR': {
            if (isset($_POST['cmp_id'])) {
                if (trim($_POST['cmp_id']) <> "") {
                    $con_comprobante->eliminar_tcp();
                } else {
                    $con_comprobante->dibujar_busqueda();
                }
            } else {
                $con_comprobante->formulario_confirmar_eliminacion();
            }

            break;
        }

    default: $con_comprobante->dibujar_busqueda();
}
?>