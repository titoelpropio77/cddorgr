<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$criterio = $_GET[input];
$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=cliente&tarea=clientes&criterio=$criterio&token=" . $this->Usuario->get_token(), $data); 
//echo "$criterio";
echo $resp;

?>
