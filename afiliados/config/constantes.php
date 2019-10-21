<?php

define('_env', 'PROD');

// url donde esta los archivos fisicos
// url del servicio resfull
if (_env == 'DEV') {
    define('_base_url', 'http://127.0.0.1/cdd_afiliados/');
    define('_servicio_url', 'http://127.0.0.1/cdd_restfull/');
    define('_sistema_url', 'http://pruebas3.sistema.com.bo/');
} else if (_env == 'PROD') {
    define('_base_url', 'http://cdd.sistema.com.bo/afiliados/');
    define('_servicio_url', 'http://cdd.sistema.com.bo/restfull/');
    define('_sistema_url', 'http://cdd.sistema.com.bo/');
}
define('_sistema_root',$_SERVER["DOCUMENT_ROOT"]);
define('_aplicacion', 'afiliados');
// Datos Empresa
define('_empresa_nombre', 'CDD');
define('_empresa_dominio', "santacruztoday.com.bo");
define('_empresa_correo', "info@santacruztoday.com.bo");
define('_empresa_direccion', 'OFICINA CENTRAL <br/>Calle Arenales #451 Tel&eacute;fono: 3-369494 Fax: 3-332917 roghur@roghur.com <br/> Santa Cruz - Bolivia');
define('_sesion', 'a1a2a3a6');
define('_copy', 'Orange Group SRL');
?>