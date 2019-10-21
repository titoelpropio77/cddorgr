<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';

$sql = "select cmp.cmp_id,cmp.cmp_peri_id,cmp.cmp_fecha,pdo.pdo_id,pdo.pdo_descripcion,pdo.pdo_fecha_inicio,pdo.pdo_fecha_fin from con_comprobante cmp
inner join con_periodo pdo on(cmp.cmp_peri_id=pdo.pdo_id)
where  (cmp.cmp_fecha< pdo.pdo_fecha_inicio or cmp.cmp_fecha > pdo.pdo_fecha_fin)
and cmp_eliminado='No'";

$cmps = FUNCIONES::lista_bd_sql($sql);

foreach($cmps as $c){
    $pdo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_eliminado='No' and pdo_fecha_inicio<='$c->cmp_fecha' "
            . "and pdo_fecha_fin>='$c->cmp_fecha'");
    
    if ($pdo == NULL) {
        echo "<p style='color:red;'>-- No existe el periodo de la fecha $c->cmp_fecha.</p>";
        continue;
    }
    $pdo_id = $pdo->pdo_id;
    $sql_upd = "update con_comprobante set cmp_peri_id='$pdo_id' where cmp_id='$c->cmp_id'";
    echo "<p>$sql_upd;</p>";
}