<?php

/**
 * GENERAR URL AMIGABLES
 * @author    quispeisaac@gmail.com
 * @copyright Intertribe limited
 * @include      class

  Esta clase requiere del archivo htaccess

  ".htaccess"
  RewriteEngine on
  RewriteCond $1 !^(index\.php|img|sistema|conten|cache|js|css|fonts|timthumb|clases|robots\.txt)
  RewriteRule ^(.*)$ /index.php/$1 [L]

 * */
class URL {

    var $uri;
    var $segmento;
    var $segmentoCount;
    var $serverHost;

    function URL() {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->segmento = explode("/", $this->uri);
        $this->segmentoCount = count($this->segmento);
        $this->serverHost = $_SERVER["SERVER_NAME"];

        $this->uri_generar();
    }

    function uri_generar() {
        
        //valor por defecto
        $_GET['uri1'] = "inicio";
        
        $contador = 1;
        foreach ($this->segmento as $key => $valor) {
            if ($valor != null) {
                // Verificar el servidor donde esta montado
                if ($this->serverHost == "localhost") {
                    if ($key > 1) {
                        $this->uri_agregar($valor, $contador);
                        $contador++;
                    }
                } else {
                    $this->uri_agregar($valor, $contador);
                    $contador++;
                }
            }
        }
        
    }

    function uri_agregar($valor, $contador) {
        $existeGet = explode("?", $valor);
        if (count($existeGet) > 1) {
            $_GET['uri' . $contador] = $existeGet[0];
        } else {
            $_GET['uri' . $contador] = $valor;
        }
    }

    function quitar_extension($url) {
        $str = str_replace(".html", "", $url);
        return $str;
    }

}

$urlseo = new URL();

// if(isset($_GET['uri3'])){
	// $_GET['uri1'] = $_GET['uri3'];
	// $_GET['uri2'] = $_GET['uri4'];
	// $_GET['uri3'] = $_GET['uri5'];
	// $_GET['uri4'] = $_GET['uri6'];
	// $_GET['uri5'] = $_GET['uri7'];
	// $_GET['uri6'] = $_GET['uri8'];
	// $_GET['uri7'] = $_GET['uri9'];
	// $_GET['uri7'] = $_GET['uri10'];
	// $_GET['uri9'] = $_GET['uri11'];
	// $_GET['uri10'] = $_GET['uri12'];
// } else { 
	// $_GET['uri1'] = "inicio"; 
// }


if(isset($_GET['uri2'])){
	$_GET['uri1'] = $_GET['uri2'];
	$_GET['uri2'] = $_GET['uri3'];
	$_GET['uri3'] = $_GET['uri4'];
	$_GET['uri4'] = $_GET['uri5'];
	$_GET['uri5'] = $_GET['uri6'];
	// $_GET['uri6'] = $_GET['uri8'];
	// $_GET['uri7'] = $_GET['uri9'];
	// $_GET['uri7'] = $_GET['uri10'];
	// $_GET['uri9'] = $_GET['uri11'];
	// $_GET['uri10'] = $_GET['uri12'];
} else { 
	$_GET['uri1'] = "inicio"; 
}

?>