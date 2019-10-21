<?php
ini_set("display_errors", "On");
function agregar_zip_($dir, $zip, $no_archivo = '') {
echo "//verificamos si $dir es un directorio<br>";
    // if (is_dir($dir) && $dir != 'huguito/js/') {
	if (is_dir($dir) && $dir != 'huguito/js/') {
        echo "//abrimos el directorio y lo asignamos a da<br>";
        if (($da = opendir($dir)) == TRUE) {
            echo "//leemos del directorio hasta que termine<br>";
            while (($archivo = readdir($da)) !== false) {
                echo "archivo => '$archivo' ...<br>";
                echo "//Si dentro del directorio hallamos otro directorio<br>"; 
                echo "//llamamos recursivamente esta función<br>";
                echo "//para que verifique dentro del nuevo directorio<br>";
                $dir_archivo = $dir . $archivo;
//                if (is_dir($dir . $archivo) && $archivo != "." && $archivo != "..") {
                if (is_dir($dir_archivo) && $archivo != "." && $archivo != ".." && $dir_archivo != $no_archivo) {    
                    echo "Vamos agregar:" . $dir_archivo . "/" . "<br>";
//                    agregar_zip($dir . $archivo . "/", $zip);
                    agregar_zip($dir_archivo . "/", $zip, $no_archivo);
//                } elseif (is_file($dir . $archivo) && $archivo != "." && $archivo != "..") {
                } elseif (is_file($dir_archivo) && $archivo != "." && $archivo != ".." && $dir_archivo != $no_archivo) {    
                    echo "Agregando archivo: $dir_archivo <br>";                                    
//                    $zip->addFile($dir . $archivo, $dir . $archivo);
                    $zip->addFile($dir_archivo, $dir_archivo);
                }
            }
            echo "//cerramos el directorio abierto en el momento<br>";
            closedir($da);
        }
    }
}

function agregar_zip($dir, $zip, $no_archivo = NULL) {
echo "//verificamos si $dir es un directorio<br>";
    if (is_dir($dir) && $dir != 'huguito/js/') {
        echo "//abrimos el directorio y lo asignamos a da<br>";
        if (($da = opendir($dir)) == TRUE) {
            echo "//leemos del directorio hasta que termine<br>";
            while (($archivo = readdir($da)) !== false) {
                echo "archivo => '$archivo' ...<br>";
                echo "//Si dentro del directorio hallamos otro directorio<br>"; 
                echo "//llamamos recursivamente esta función<br>";
                echo "//para que verifique dentro del nuevo directorio<br>";
                $dir_archivo = $dir . $archivo;
//                if (is_dir($dir . $archivo) && $archivo != "." && $archivo != "..") {
//                if (is_dir($dir_archivo) && $archivo != "." && $archivo != ".." && $dir_archivo != $no_archivo) {    
                if (is_dir($dir_archivo) && $archivo != "." && $archivo != ".." && in_array($dir_archivo, $no_archivo) === FALSE) {        
                    echo "Vamos agregar:" . $dir_archivo . "/" . "<br>";
//                    agregar_zip($dir . $archivo . "/", $zip);
                    agregar_zip($dir_archivo . "/", $zip, $no_archivo);
//                } elseif (is_file($dir . $archivo) && $archivo != "." && $archivo != "..") {
                } elseif (is_file($dir_archivo) && $archivo != "." && $archivo != ".." && in_array($dir_archivo, $no_archivo) === FALSE) {    
                    echo "Agregando archivo: $dir_archivo <br>";                                    
//                    $zip->addFile($dir . $archivo, $dir . $archivo);
                    $zip->addFile($dir_archivo, $dir_archivo);
                }
            }
            echo "//cerramos el directorio abierto en el momento<br>";
            closedir($da);
        }
    }
}

//creamos una instancia de ZipArchive      
$zip = new ZipArchive();

//directorio a comprimir
//la barra inclinada al final es importante
//la ruta debe ser relativa no absoluta      
//$dir = '../cdd/';
//$no_archivo = "../cdd/docs";


$dir = '../mas/';

$no_archivo = array(
	'../mas/doc_imp',
	'../mas/restfull',
	'../mas/afiliados',
);

// $dir = '../urubogolf/';
// $no_archivo = array('../urubogolf/imagenes/lotes_archivos','../urubogolf/pdf_contratos');

//ruta donde guardar los archivos zip, ya debe existir
//$rutaFinal = "backup/";
$rutaFinal = "../ZIP/";

$archivoZip = "mas.zip";

if ($zip->open($archivoZip, ZIPARCHIVE::CREATE) === true) {
    agregar_zip($dir, $zip, $no_archivo);
//    $zip->addFile('oferta.class.php');
//    $zip->addFile('oferta.gestor.php');
    $zip->close();

    //Muevo el archivo a la ruta definida
    @rename($archivoZip, "$rutaFinal$archivoZip");

    //Hasta aqui el archivo zip ya esta creado
    echo "Parece que todo bien...";
} else {
    echo "No pasa naranjas...";
}
?>