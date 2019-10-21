<?php

    if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'phpqrcode.sourceforge.net')) {
        // sourceforge server TEMP dir
        define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__).'/../../persistent/temp_qr_image/');
        // proxy file to display files from TEMP
        define('EXAMPLE_TMP_URLRELPATH', 'sfproxy.php?file=');
    
    } else {

        define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__).'/temp_qr_image/');
        define('EXAMPLE_TMP_URLRELPATH', 'temp_qr_image/');
        
    }
    