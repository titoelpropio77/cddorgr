<?php
require_once('config/database.conf.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('mysql.php');
?>
<form id="frm" name="frm" method="GET" action="imp_verificacion_estado_lote.php">
    <p>URBANIZACION:</p>
    <select id="urb" name="urb">
      <?php
      $fun = new FUNCIONES();
      $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion order by id asc","");
      ?>  
    </select>
    
    <p>ESTADO LOTE:</p>
    <select id="estado" name="estado">
        <option value="Disponible">Disponible</option>
        <option value="Reservado">Reservado</option>
        <option value="Vendido">Vendido</option>
        <option value="Bloqueado">Bloqueado</option>
    </select>
    
    <p>MODO:</p>
    <select id="modo" name="modo">
        <option value="html">HTML</option>
        <option value="csv">CSV</option>        
    </select>
    
    <input type="hidden" id="exp" name="exp" value="algo" />
    <input type="submit" id="btn" name="btn" value="Enviar" />
</form>