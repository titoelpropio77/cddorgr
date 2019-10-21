<?php
require_once('uv.class.php');
require_once('uv.plano.php');

$uv = new UV();
ADO::$modulo = $uv->modulo;
ADO::$modulo_id = $_GET[id];
ADO::$tarea = $_GET[tarea];
$plano = new Plano();

// Plano Validacion superior
if ($_GET['tarea'] <> "") {
    if (!($uv->verificar_permisos($_GET['tarea']))) {
        if (($_GET['tarea'] != "CONFIGURACION") && ($_GET['tarea'] != "PUNTOS") && ($_GET['tarea'] != "PLANO")) {
            ?>
            <script>
                location.href = "log_out.php";
            </script>
            <?php
        }
    }
}

$encode = base64_encode($_SESSION['id']);
$encodeLimpio = str_replace(array('+', '/', '='), array('-', '_', ''), $encode);
$url_actual = "http://" . $_SERVER["SERVER_NAME"] . "/modulos/uv/?mapa=" . $encodeLimpio . "&u=";
if ($_GET['tarea'] == 'ACCEDER') {
    ?>
    <script>
        function getUrlVarss(url) {
            var vars = {};
            var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                vars[key] = value;
            });
            return vars;
        }
        var url_actual = "<?php echo $url_actual; ?>";
        $(document).ready(function() {
            $(".tablaLista tbody tr").each(function(i) {
                var auxUrl = $(this).find(":eq(3) a.linkOpciones[title='PLANO COMPLETO']").attr("href");
                $(this).find(":eq(3) a.linkOpciones[title='PLANO COMPLETO']").attr({"href": url_actual + getUrlVarss(auxUrl)["id"], "target": "_blank"});
            });
        });
    </script>
    <?php
}

switch ($_GET['tarea']) {
    case 'AGREGAR': {
            if ($_GET['acc'] == 'Emergente') {
                $uv->emergente();
            } else {
                if ($uv->datos()) {
                    $uv->insertar_tcp();
                } else {
                    $uv->formulario_tcp('blanco');
                }
            }


            break;
        }
    case 'VER': {
            $uv->cargar_datos();

            $uv->formulario_tcp('ver');

            break;
        }
    case 'MODIFICAR': {

            if ($uv->datos()) {
                $uv->modificar_tcp();
            } else {
                if (!($_POST)) {
                    $uv->cargar_datos();
                }
                $uv->formulario_tcp('cargar');
            }

            break;
        }
    case 'ELIMINAR': {
            if (isset($_POST['urb_id'])) {
                if (trim($_POST['urb_id']) <> "") {
                    $uv->eliminar_tcp();
                } else {
                    $uv->dibujar_busqueda();
                }
            } else {
                $uv->formulario_confirmar_eliminacion();
            }
            break;
        }
    case 'ESTRUCTURA': {
//            $uv->ver_estructura();
//            break;
            require_once('uv_parametros.class.php');
            $vnegocio = new UV_PARAMETROS();
            $vnegocio->estructura();
            break;
        }
    case 'ZONAS': {
            $uv->ver_zonas();
            break;
        }
    case 'UV': {
            $uv->ver_uv();
            break;
        }
        
    case 'CUENTAS': {
            require_once('uv_parametros.class.php');
            $parametros = new UV_PARAMETROS();
            $parametros->cuentas();
            break;
        }
    case 'PARAMETROS MLM': {
            require_once('uv_parametros.class.php');
            $parametros = new UV_PARAMETROS();
            $popup=$_GET[popup];
            
            $parametros->parametros_mlm();
            
            
            break;
        }
case 'MULTINIVEL':{
            require_once 'uv_multinivel.class.php';	
            $uvm = new UV_MULTINIVEL();

            $uvm->formulario_configuracion_comisiones();
            $uvm->formulario_configuracion_comisiones3();
            break;
    }

    //======= TAREAS PLANO =====//
    case 'CONFIGURACION': {
            if (isset($_POST['formAcion'])) {
                if ($_POST['formAcion'] == "ACTUALIZAR") {
                    $plano->plano_configuracion_modificar();
                } else {
                    $plano->plano_configuracion_insertar();
                }
            } else {
                $plano->plano_configuracion();
            }
            break;
        }
    case 'PUNTOS': {
            $plano->plano_puntos();
            break;
        }
    case 'PLANO': {
            $plano->plano_ver();
            break;
        }
    //======= TAREAS PLANO FIN =====//
    default:
        $uv->dibujar_busqueda();
        break;
}
?>