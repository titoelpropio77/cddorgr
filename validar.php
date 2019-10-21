<?php
$s=array(
   array('une_id'=>1,'nombre'=>'Oficina Central','porc'=>0), 
   array('une_id'=>2,'nombre'=>'CDD LUJAN','porc'=>40), 
   array('une_id'=>3,'nombre'=>'CDD BISITO','porc'=>30), 
   array('une_id'=>4,'nombre'=>'CDD NORTE','porc'=>20), 
   array('une_id'=>6,'nombre'=>'MERCADO JIREH ','porc'=>10), 
);
echo serialize($s);

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
