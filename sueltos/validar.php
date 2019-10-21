<?php
$s=array(
   array('une_id'=>2,'nombre'=>'CDD LUJAN','porc'=>50), 
   array('une_id'=>3,'nombre'=>'CDD BISITO','porc'=>30), 
   array('une_id'=>4,'nombre'=>'CDD NORTE','porc'=>20), 
);
echo serialize($s);
return;
$array=array(1,2,3,4,5,6,7,8,9,10);
array_push($array, 11);
echo array_pop($array);
echo "<br>";
echo array_shift($array);

echo "<pre>";
print_r($array);
echo "</pre>";
return;
class VALIDAR {

    public static $tablas=array(
        'venta'=>array(
            'persona_id'=>'persona',
            'vendedor_id'=>'vendedor'
        ),
        'traspaso'=>array(
            'almacen_ori_id'=>'almacen',
            'almacen_des_id'=>'almacen'
        )
    );
    
    
}
?>
