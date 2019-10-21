<?php

define('_env', 'PROD');

if (_env == 'DEV') {
    define('_sistema_url', 'http://127.0.0.1/cdd_mlm');
} else {
    define('_sistema_url', 'http://cdd.sistema.com.bo');
}

define('_copy', 'www.orangegroup.com.bo');
define('_nombre_empresa', 'Ciudad de Dios');
define('_datos_empresa', 'Av.Japon # 3750. entre Mutualista y Paragua, Santa Cruz - Bolivia');
// url amigables
define('_base_url', 'http://192.185.93.162/~/apps/');
?>