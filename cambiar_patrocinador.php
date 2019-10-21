<?php
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once("clases/mytime_int.php");
require_once('config/constantes.php');
require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');

function procesar($ven_id_patr, $afils) {

    $obj_patr = FUNCIONES::objeto_bd_sql("select * from venta inner join vendedor on (ven_id=vdo_venta_inicial)
        where ven_id=$ven_id_patr");

    if ($obj_patr === NULL) {
        echo "<p style='color:red'>NO EXISTE EL PATROCINADOR</p>";
        return false;
    }

    if ($afils === '') {
        echo "<p style='color:red'>INDIQUE LOS AFILIADOS</p>";
        return false;
    }

    $nivel = $obj_patr->vdo_nivel + 1;

    $sql_upd = "update vendedor set 
        vdo_vendedor_id=$obj_patr->vdo_id,
        vdo_nivel=$nivel 
        where vdo_venta_inicial in ($afils)";
    FUNCIONES::bd_query($sql_upd);
    echo "<p style='blue'>$sql_upd;</p>";
    echo "<p style='color:green'>-- AFILIADOS ACTUALIZADOS</p>";


    $sql_upd_com_cob = "update comision_cobro set 
        comcob_vdo_id=$obj_patr->vdo_id 
            where comcob_ven_id in ($afils)";
    FUNCIONES::bd_query($sql_upd_com_cob);
    echo "<p style='blue'>$sql_upd_com_cob;</p>";
    echo "<p style='color:green'>-- COMISIONES COBRO ACTUALIZADAS</p>";

    $sql_upd_venta = "update venta set 
        ven_vdo_id=$obj_patr->vdo_id 
            where ven_id in ($afils)";
    FUNCIONES::bd_query($sql_upd_venta);
    echo "<p style='blue'>$sql_upd_venta;</p>";
    echo "<p style='color:green'>-- VENTAS ACTUALIZADAS</p>";

    $sql_upd_com = "update comision set 
        com_vdo_id=$obj_patr->vdo_id 
            where com_ven_id in ($afils)";
    FUNCIONES::bd_query($sql_upd_com);
    echo "<p style='blue'>$sql_upd_com;</p>";
    echo "<p style='color:green'>-- COMISIONES ACTUALIZADAS</p>";

    $sql_res = "SELECT convert(group_concat(ven_res_id)using utf8) as campo from venta where ven_id in ($afils)";
    $reservas = FUNCIONES::atributo_bd_sql($sql_res);

    if ($reservas !== NULL) {
        $sql_upd_reservas = "update reserva_terreno set 
        res_vdo_id=$obj_patr->vdo_id 
            where res_id in ($reservas)";
        FUNCIONES::bd_query($sql_upd_reservas);
        echo "<p style='blue'>$sql_upd_reservas;</p>";
        echo "<p style='color:green'>-- RESERVAS ACTUALIZADAS</p>";
    }
}

function formulario_procesar() {
    ?>
    <form id="frm_ejecutar" name="frm_ejecutar" method="POST" enctype="multipart/form-data" action="cambiar_patrocinador.php">
        AFILIADOS:<textarea id="afiliados" name="afiliados" cols="20" rows="2"></textarea><br/><br/>
        PATROCINADOR:<input type="text" id="patrocinador" name="patrocinador" value="" /><br/>
        <input type="button" id="btn_enviar" name="btn_enviar" value="EJECUTAR" />
        
    </form>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btn_enviar').click(function() {
                if ($('#afiliados').val() === '') {
                    alert('Ingrese los afiliados...');
                    return false;
                }
                
                if ($('#patrocinador').val() === '') {
                    alert('Ingrese el patrocinador...');
                    return false;
                }
                
                $('#frm_ejecutar').submit();
            });
        });
    </script>
    <?php
}

if ($_POST) {
    procesar($_POST[patrocinador], $_POST[afiliados]);
} else {
    formulario_procesar();
}
?>