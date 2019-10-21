<?php
$cuentas=  explode(',', $_POST['cuentas']) ;
$ges_id=$_POST['ges_id'];

$conec = new ADO();
$sql = "delete from con_detalle_cf where dcf_ges_id=$ges_id";
$conec->ejecutar($sql);

foreach ($cuentas as $cuenta) {
    $sql = "INSERT INTO con_detalle_cf(dcf_cue_id,dcf_ges_id) VALUES($cuenta,$ges_id)";
    $conec->ejecutar($sql);
}
echo 'Se ha registrado exitosamente los detalles de cuentas para flujo';

?>
