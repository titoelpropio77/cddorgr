<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

enviar_cuentas_analiticas();
//eliminar_cuentas();
function eliminar_cuentas(){
    $sql="delete from con_cuenta_ca where can_ges_id=13 and can_codigo>='50.00010'";
    FUNCIONES::bd_query($sql);
    $sql="alter table con_cuenta_ca auto_increment 240";
    FUNCIONES::bd_query($sql);
}

function enviar_cuentas_analiticas(){
    $vendedores=  FUNCIONES::objetos_bd_sql("select * from vendedor, interno where int_id=vdo_int_id and vdo_id>=14");
    $num_cod=21;
    for ($i = 0; $i < $vendedores->get_num_registros(); $i++) {
        $vdo=$vendedores->get_objeto();
    ?>
        <span class="vendedores" data-codigo="<?php echo "50.000$num_cod"?>" data-nombre="<?php echo "$vdo->int_nombre $vdo->int_apellido";?>">  
            <?php echo "update vendedor set vdo_can_id='50.000$num_cod' where vdo_id='$vdo->vdo_id';";?>          
        </span><br>
    <?php
        $vendedores->siguiente();
        $num_cod++;
    }
    ?>
        <script src="js/jquery-1.10.2.min.js"></script>
        <script >
            var vendedores=$('.vendedores');
//            enviar_cuenta_analitica(0,vendedores);
            
            function enviar_cuenta_analitica(pos,_vendedores){
                if(pos>=$(_vendedores).size()){
                    return ;
                }
                var params={};
                params.can_codigo=$(_vendedores[pos]).data('codigo');
                params.can_descripcion=$(_vendedores[pos]).data('nombre');
                params.can_ges_id=13;
                params.can_id=189;
                params.can_mon_id=0;	
                params.can_tipo='Movimiento';
                params.can_tree_position=0;
                params.operation='create_node';
                $.post('sueltos/tree/con_cuenta_ca.php',params,function (r){
                    if(r.status){
                        enviar_cuenta_analitica(pos+1,_vendedores);
                    }
                });
            }            
            console.info($('.vendedores').size());
        </script>
    <?php
}
?>