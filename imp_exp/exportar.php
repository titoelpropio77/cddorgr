<?php

 $host = "localhost";
 $username = "root";
 $password = "";
 $dbName = "sistema_com_bo_agroforce";

 /*$host = "localhost";
 $username = "sistema.com.bo";
 $password = "wfnuxEHbH5";
 $dbName = "sistema_com_bo_agroforce";*/

 include_once(dirname(__FILE__) . '/mysqldump/src/Ifsnop/Mysqldump/Mysqldump.php');
 //$dumpSettings = array("no-data" => array("ad_logs"), "compress" => "Gzip");
 $dumpSettings = array("include-tables" => array("com_venta"), "compress" => "Gzip");

 $dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=localhost;dbname=$dbName", $username, $password, $dumpSettings);
 $dump->start('sistema_com_bo_agroforce.gz');
 echo "Base de datos exportada Exitosamente!";
 exit();

?>