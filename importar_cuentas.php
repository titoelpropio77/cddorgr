<?php
require_once 'excel/reader.php';
//require_once('./config2/mysql.php');
//require_once './config2/constantes.php';
class VARIABLES {
    public static $i;
    public static $array=array();
    public static $bd;
    
    function __construct() {
        
    }
    public static function definir_bd($bd){
        VARIABLES::$bd=$bd;
    }
    public static function incrementar_i(){
        VARIABLES::$i++;
    }
    public static function get_id(){
        return VARIABLES::$i;
    }
    
    public static function set($variable, $valor){
        VARIABLES::$array[$variable]=$valor;
    }
    public static function get($variable){
        return VARIABLES::$array[$variable];
    }
    public static function reset($numero){
        VARIABLES::$i=$numero;
    }
}

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read("plan_de_cuentas.xls");
$filasTotal = $data->sheets[0]['numRows'];
VARIABLES::set("max_level", 5);
VARIABLES::set("ges_id", 1);
VARIABLES::set("formato", "0.0.0.00.000");
//$ges_id = 1;
echo $filasTotal."<br>";

$list_excel = array();

for ($i = 2; $i <= $filasTotal; $i++) {
    $fila=new stdClass();
    $fila->cuenta=$data->sheets[0]['cells'][$i][1];
    if($fila->cuenta==''){
        break;        
    }
    $fila->moneda=$data->sheets[0]['cells'][$i][2];
    $fila->nivel=$data->sheets[0]['cells'][$i][3];
//    echo "$cuenta - $moneda - $nivel <br>";
    $list_excel[]=$fila;
}
//return; 

$list_cuentas=array();
for ($i = 0; $i <= count($list_excel); $i++) {
    $fila=$list_excel[$i];
    
    $cuenta = new stdClass();
//    echo "pos ".strpos($fila->cuenta, ' ')."<br>";
    $txtcodigo = substr($fila->cuenta, 0, strpos($fila->cuenta, ' '));
    $descripcion = substr($fila->cuenta, strpos($fila->cuenta, ' '));
    $arrcodigo = explode('.', $txtcodigo);
    
    $codigo = new stdClass();
    for($j=0;$j<count($arrcodigo);$j++){
        $codigo->{"c".($j+1)}=$arrcodigo[$j];
    }
    for($m=$j;$m<VARIABLES::get("max_level");$m++){
        $formato= explode('.',VARIABLES::get("formato"));
        $codigo->{"c".($m+1)}=$formato[$m];
    }
   
    $nivel = obtener_nivel($codigo);
    $cuenta->txtcodigo = implode(".", (array)$codigo);
    $cuenta->codigo = $codigo;
    $cuenta->nivel = $nivel;
    $cuenta->descripcion = trim($descripcion);
    $mon= str_replace('.', '', $fila->moneda);
    if($mon=='Bs'){
        $cuenta->moneda='1';
    }elseif ($mon=='$us') {
        $cuenta->moneda='2';
    }elseif($mon=='ufv'){
        $cuenta->moneda='3';
    }else{
        $cuenta->moneda='0';
    }
    $list_cuentas[] = $cuenta;
    
}

$lisn1 = obtener_cuenta_nivel($list_cuentas, 1);

$pos = count($lisn1);
VARIABLES::reset(3);
VARIABLES::set('id_padre',2);
foreach ($lisn1 as $cuenta) {
    $cuenta->id = VARIABLES::get_id();
    VARIABLES::incrementar_i();
    $cuenta->idpadre =  VARIABLES::get('id_padre');
    $cuenta->pos = $pos;
    cargar_hijos($cuenta, $list_cuentas);
    $pos--;
}
$izq = 1;
foreach ($lisn1 as $cuenta) {
    $cuenta->izq = $izq;
    $der = obtener_derecha($cuenta);
    $cuenta->der = $der;
    $izq = $der + 1;
}

$ges_id=  VARIABLES::get("ges_id");
foreach ($lisn1 as $cuenta_h) {
    $insert = "INSERT INTO `con_cuenta` (`cue_id`, `cue_ges_id`, `cue_codigo`, `cue_descripcion`, 
                `cue_tipo`, `cue_padre_id`, `cue_eliminado`, `cue_mon_id`, `cue_tree_left`, `cue_tree_right`, `cue_tree_position`, `cue_tree_level`) 
                VALUES ('$cuenta_h->id', '$ges_id', '$cuenta_h->txtcodigo', '$cuenta_h->descripcion', 
                        'Titulo', '$cuenta_h->idpadre', 'No', '$cuenta_h->moneda', '$cuenta_h->izq', '$cuenta_h->der', '$cuenta_h->pos', '$cuenta_h->nivel');";
            echo $insert;
            echo '<br>';
    imprimir_cuenta($cuenta_h,$ges_id);
}

function imprimir_cuenta($cuenta,$ges_id) {
    $cuenta_hz = $cuenta->hijos;
    foreach ($cuenta_hz as $cuenta_h) {
        if ($cuenta_h->nivel == 5) {
            $insert = "INSERT INTO `con_cuenta` (`cue_id`, `cue_ges_id`, `cue_codigo`, `cue_descripcion`, 
                `cue_tipo`, `cue_padre_id`, `cue_eliminado`, `cue_mon_id`, `cue_tree_left`, `cue_tree_right`, `cue_tree_position`, `cue_tree_level`) 
                VALUES ('$cuenta_h->id', '$ges_id', '$cuenta_h->txtcodigo', '$cuenta_h->descripcion', 
                        'Movimiento', '$cuenta_h->idpadre', 'No', '$cuenta_h->moneda', '$cuenta_h->izq', '$cuenta_h->der', '$cuenta_h->pos', '$cuenta_h->nivel');";
            echo $insert;
            echo '<br>';
        } else {
            $insert = "INSERT INTO `con_cuenta` (`cue_id`, `cue_ges_id`, `cue_codigo`, `cue_descripcion`, 
                `cue_tipo`, `cue_padre_id`, `cue_eliminado`, `cue_mon_id`, `cue_tree_left`, `cue_tree_right`, `cue_tree_position`, `cue_tree_level`) 
                VALUES ('$cuenta_h->id', '$ges_id', '$cuenta_h->txtcodigo', '$cuenta_h->descripcion', 
                        'Titulo', '$cuenta_h->idpadre', 'No', '$cuenta_h->moneda', '$cuenta_h->izq', '$cuenta_h->der', '$cuenta_h->pos', '$cuenta_h->nivel');";
            echo $insert;
            echo '<br>';
            imprimir_cuenta($cuenta_h,$ges_id);
        }
    }    
}

function obtener_derecha($cuenta) {
    $cuenta_hs = $cuenta->hijos;
    $izq = $cuenta->izq;
    $der = 0;
    foreach ($cuenta_hs as $cuenta_h) {
        if ($cuenta_h->nivel == 5) {
            $cuenta_h->izq = $izq + 1;
            $cuenta_h->der = $izq + 2;
            $izq+=2;
            $der = $izq + 2;
        } else {
            $cuenta_h->izq = $izq + 1;
            $der = obtener_derecha($cuenta_h);
            $cuenta_h->der = $der;
            $izq = $der + 1;
        }
    }
    return $der;
}

function cargar_hijos($cuenta, $list_cuentas) {
    $cuentas_hs = obtener_hijos($cuenta, $list_cuentas);
    $pos = count($cuentas_hs);
    foreach ($cuentas_hs as $cuenta_h) {
        $cuenta_h->pos = $pos;
        $cuenta_h->id = VARIABLES::get_id();
        $cuenta_h->idpadre = $cuenta->id;
        VARIABLES::incrementar_i();
        if ($cuenta_h->nivel == 5) {
            $cuenta_h->hijos = null;
        } else {
            cargar_hijos($cuenta_h, $list_cuentas);
        }
        $pos--;
    }
    $cuenta->hijos = $cuentas_hs;
}

function obtener_hijos($cuenta, $list_cuentas) {
    $childs = array();
    $codigo = obtener_codigo_nivel($cuenta->codigo, $cuenta->nivel);
    foreach ($list_cuentas as $_cuenta) {
        $_codigo = obtener_codigo_nivel($_cuenta->codigo, $cuenta->nivel);
//        echo "|$codigo - $codigo|";
        if ($codigo == $_codigo && ($cuenta->nivel + 1 == $_cuenta->nivel) && $cuenta != $_cuenta) {
            $childs[] = $_cuenta;
        }
    }
    return $childs;
}

function obtener_codigo_nivel($codigo, $nivel) {
    $cad = "";
    for($i=1;$i<=$nivel;$i++){
        if($i>1) $cad.='.';
        $cad.=$codigo->{'c'.$i};
    }
    return $cad;
}

function obtener_cuenta_nivel($list_cuentas, $nivel) {
    $listN = array();
    foreach ($list_cuentas as $cuenta) {
        if ($cuenta->nivel == $nivel) {
            $listN[] = $cuenta;
        }
    }
    return $listN;
}

function obtener_nivel($codigo) {
    $codigo = (array) $codigo;
    for ($i = 1; $i <= count($codigo); $i++) {
        $cod = intval($codigo['c' . $i]);
        if ($cod == 0) {
            return $i - 1;
        }
    }
    return count($codigo);
}

function limpiar($cadena) {
    return preg_replace("[\s+]", '', $cadena);
}

?>