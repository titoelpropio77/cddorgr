<?php
session_start();
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');

if (!($_SESSION[id] && $_SESSION[usu_per_id] && $_SESSION[ges_id])) {
    echo '{"response":"error", "mensaje":"No tiene permisos"}';
    return;
}

$conec=new ADO();
$consulta=  trim($_POST[consulta]);
if($consulta){
    $consulta=  str_replace("\'", "'", $consulta);
    $conec->ejecutar($consulta);
}
frm_consulta($consulta);
$num = $conec->get_num_registros();
echo "Cantidad de registro $num <br>";
if($num>0){
    mostrar_registros($conec);
}

function mostrar_registros($conec) {
    $num=$conec->get_num_registros();
    $objeto = $conec->get_objeto();
    $sum_campo=$_POST[sum_campo];
    $tsuma=new stdClass();
    foreach ($sum_campo as $campo) {
        $tsuma->{$campo}=0;
    }
    ?>
    <style>
    .tab_lista{border-collapse: collapse;}
    .tab_lista thead th,.tab_lista tfoot td{border: 1px solid #dadada; background-color: #878787; padding: 3px;}
    .tab_lista tbody td{border:1px solid #878787; padding: 3px}
    </style>
    <table class="tab_lista">
        <thead>
            <tr>
                <th>#</th>
                <?php foreach ($objeto as $key => $value) {?>
                    <th><?php echo $key;?></th>
                <?php }?>
            <tr>
        </thead>
        <tbody>
            <?php for($i=0;$i<$num;$i++){?>
            <?php $objeto=$conec->get_objeto();?>
                <tr>
                    <td><?php echo ($i+1);?></td>
                <?php foreach ($objeto as $key => $value) {?>
                    <td><?php echo $value;?></td>
                    <?php if(in_array($key, $sum_campo)){?>
                        <?php $tsuma->{$key}+=$value;?>
                    <?php }?>
                <?php }?>
                <tr>
            <?php $conec->siguiente();?>
                <?php if($i==1000){break;}?>
            <?php }?>
        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <?php foreach ($objeto as $key => $value) {?>
                    <td><?php echo $tsuma->{$key};?></td>
                <?php }?>
            </tr>
        </tfoot>
    </table>
    <?php
}
function frm_consulta($consulta) {
    ?>
<style>
    textarea{
        border: 1px solid #000; width: 75%; height: 50px; padding: 5px; font-size: 12px;
    }
    .sum_campo{
        border: 1px solid #dadada; width: 120px;
    }
    #add,box-sum-campos{float: left;}
    .div-input{overflow: auto;}
</style>
 <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
<form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="script_consulta_bd.php?" name="frm_sentencia">
    <div class="div-input">
        <label>Sumar Campos:</label><br>
        <input type="button" value="ADD" id="add">
        <div id="box-sum-campos">
            <?php foreach ($_POST[sum_campo] as $campo) {?>
                <input type="text" name="sum_campo[]" class="sum_campo" value="<?php echo $campo?>">
            <?php }?>
        </div>
        
    </div>
    <div class="div-input">
        <label>Consulta:</label><br>
        <textarea type="text" name="consulta" ><?php echo $consulta?></textarea>
        
    </div>
    <div class="div-input">
        <input type="submit" value="GO">
    </div>
    <script>
        $('#add').click(function(){
            var input='<input type="text" name="sum_campo[]" class="sum_campo">';
            $('#box-sum-campos').append(input);
        });
    </script>
</form>
    <?php
}