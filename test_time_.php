<?php
ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';
require_once 'config/zona_horaria.php';

$arr_no_dirs = array('docs/','doc_imp/');
$root = '';
if ($_POST) {
    procesar();
} else {
    formulario();
}

function formulario() {
    ?>
    <form id="frm_ejecutar" name="frm_ejecutar" method="POST" enctype="application/x-www-form-urlencoded" action="<?php echo $_SERVER['PHP_SELF']; ?>">

        <p>MODO:</p>
        <select id="modo" name="modo">
            <option value="html">HTML</option>
            <option value="csv">CSV</option>        
        </select>

        <input type="hidden" id="exp" name="exp" value="algo" />
        <input type="submit" id="btn" name="btn" value="Enviar" />
    </form>
    <?php
}

function procesar() {
    if (PHP_OS == 'WINNT') {
        $dir = $_SERVER["DOCUMENT_ROOT"];
        $dir .= basename(getcwd()) . '/';
    } else if (PHP_OS == 'Linux') {
        $dir = $_SERVER["DOCUMENT_ROOT"] . '/';
    } else {
        exit();
    }

    global $root;
    $root = $dir;
    
    $length_dir = strlen($dir);
    $arr_lineas_csv = array();

    listar_archivos($dir, $arr_lineas_csv, $length_dir);
    $nombre_archivo = "test.csv";

    if ($_POST[modo] == 'csv') {
        exportar_csv(array('nom_archivo' => $nombre_archivo, 'datos' => $arr_lineas_csv));
    } elseif ($_POST[modo] == 'html') {
        echo "dir:$dir";
        mostrar_html(array('nom_archivo' => $nombre_archivo, 'datos' => $arr_lineas_csv));
    }
        
}

function es_evaluable($dir){
    global $arr_no_dirs;
    global $root;
    $dir = str_replace($root, '', $dir);
    if (in_array($dir, $arr_no_dirs) === TRUE) {
//        echo "<p>no es evaluable $dir.</p>";
        return FALSE;
    } else {
//        echo "<p>es evaluable $dir.</p>";
        return TRUE;
    }
}

function mostrar_html($datos) {
    
    $sql_parcial = "
        CREATE TEMPORARY TABLE tmp_cambios(            
            directorio text not null, 
            archivo text not null,             
            fecha date not null,
            hora time not null
         );";
    FUNCIONES::bd_query($sql_parcial, FALSE);
    
    $datos = (object) $datos;
    $nombre_archivo = $datos->nom_archivo;
    $arr_lineas_html = $datos->datos;
    
    $sql_ins = "insert into tmp_cambios(directorio,archivo,fecha,hora)values";
    $cont = 0;
	$cont_ins = 0;
    $s_acum = "";
	$dim = count($arr_lineas_html);
    foreach ($arr_lineas_html as $linea) {
        $linea = (object) $linea;
        $fecha = FUNCIONES::get_fecha_mysql($linea->fecha);
        
        if ($s_acum == "") {
            $s_acum .= "('$linea->directorio','$linea->archivo','$fecha','$linea->hora')";
        } else {
            $s_acum .= ",('$linea->directorio','$linea->archivo','$fecha','$linea->hora')";
        }        
        
        $cont++;
        if ($cont == 100) {
            FUNCIONES::bd_query($sql_ins.$s_acum,FALSE);
            $s_acum = '';
			$cont_ins += $cont;
            $cont = 0;			
        }
    }
	
	if ($cont < 100) {
		FUNCIONES::bd_query($sql_ins.$s_acum,FALSE);
		$s_acum = '';
		$cont_ins += $cont;
		$cont = 0;			
	}
	
	echo "<p style='color:green;'>evaluados:$dim - insertardos:$cont_ins - contador:$cont - acumulador:$s_acum</p>";
    
    $sql_sel = "select * from tmp_cambios order by fecha desc,hora desc";
    // $arr_lineas_html = FUNCIONES::lista_bd_sql($sql_sel);
	$arr_lineas_html = FUNCIONES::objetos_bd_sql($sql_sel);
    ?>
<style>
        .link_tabs{
            color: #fff;
            background-color: #0000FF;
            text-decoration: none; padding: 1px 5px;
        }
        .link_tabs:hover{
            background-color: #0054ff;
        }
        .lista_tabla{
            border-collapse: collapse;
        }
        .lista_tabla thead th, .lista_tabla tbody td{
            border: 1px solid #000;
        }
        .tablaListaFila2{
            background:#ebebeb;
        }
    </style>
    <table class="lista_tabla">
        <thead>
            <tr>
				<th>#</th>
                <th>DIRECTORIO</th>
                <th>ARCHIVO</th>
                <th>FECHA MOD.</th>
                <th>HORA</th>                
            </tr>
        </thead>
        <tbody>
            <?php
            $b = true;
            // foreach ($arr_lineas_html as $linea) {
			for ($i = 0; $i < $arr_lineas_html->get_num_registros(); $i++) {
				$linea = $arr_lineas_html->get_objeto();
                $clase = ($b)?"class='tablaListaFila2'":"";
				$nro = $i + 1;
                ?>
                <tr <?php echo $clase;?>>
					<td><?php echo $nro; ?></td>
                    <td><?php echo $linea->directorio; ?></td>
                    <td><?php echo $linea->archivo; ?></td>
                    <td><?php echo FUNCIONES::get_fecha_latina($linea->fecha); ?></td>
                    <td><?php echo $linea->hora; ?></td>                
                </tr>
                <?php
                $b = !$b;
				$arr_lineas_html->siguiente();
            }
            ?>
        </tbody>
    </table>
    <?php
}

function listar_archivos($dir, &$arr_lineas, $length_dir) {
    $b = es_evaluable($dir);
    
    if ($b == FALSE) {
        return FALSE;
    }
    
    if (($da = opendir($dir)) == TRUE) {
        while (($archivo = readdir($da)) !== false) {
            $dir_archivo = $dir . $archivo;

            if (is_dir($dir_archivo) && $archivo != '.' && $archivo != '..') {

                listar_archivos($dir_archivo . '/', $arr_lineas, $length_dir);
            } elseif (is_file($dir_archivo) && $archivo != '.' && $archivo != '..') {

                $time = filemtime($dir_archivo);
                $fecha_mod = date("d/m/Y", $time);
                $hora = date("H:i:s", $time);
                $carpeta = substr($dir, $length_dir);
                $datos = array(
                    'directorio' => $carpeta,
                    'archivo' => $archivo,
                    'fecha' => $fecha_mod,
                    'hora' => $hora
                );
                $arr_lineas[] = $datos;
            }
        }
        closedir($da);
    }
}

function obtener_linea($arr_datos) {
    $s = '"' . implode('";"', $arr_datos) . '"' . "\n";
    return $s;
}

function exportar_csv($datos) {
    $datos = (object) $datos;
    $nombre_archivo = $datos->nom_archivo;
    $arr_lineas_csv = $datos->datos;
    header("Content-Type: text/csv");
    header("content-disposition: attachment;filename=" . $nombre_archivo);
    echo '"DIRECTORIO";"ARCHIVO";"FECHA DE MODIFICACION";"HORA"' . "\n";

    for ($i = 0; $i < count($arr_lineas_csv); $i++) {
        echo obtener_linea($arr_lineas_csv[$i]);
    }
}