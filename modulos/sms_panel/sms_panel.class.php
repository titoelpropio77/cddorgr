<?php

class SMS_PANEL extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function SMS_PANEL() {
        //permisos
        $this->ele_id = 303;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 10;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "ban_fecha_cre";
        $this->arreglo_campos[0]["texto"] = "Fecha";
        $this->arreglo_campos[0]["tipo"] = "fecha";
        $this->arreglo_campos[0]["tamanio"] = 40;

        


        $this->link = 'gestor.php';

        $this->modulo = 'sms_automatico';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('SMS PANEL');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario->dibujar_cabecera();
    ?>
    <table class="tablaLista" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th>LISTO</th>
                <th>DESPACHADO</th>
                <th>ENVIADO</th>                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td id="sms_listo"><?php echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='LISTO'");?></td>
                <td id="sms_despachado"><?php echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='DESPACHADO'");?></td>
                <td id="sms_enviado"><?php echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='ENVIADO'");?></td>
            </tr>
        </tbody>
    </table>
    <br><br><br>
    <table class="tablaLista" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th>ULTIMO DESPACHADO</th>
                <th>ULTIMO ENVIADO</th>                
            </tr>
        </thead>
        <tbody>
            <?php $ultimo_desp=  FUNCIONES::objeto_bd_sql("select max(ban_fecha_desp) as fecha,max(ban_hora_desp) as hora from bandeja");?>
            <?php $ultimo_env=  FUNCIONES::objeto_bd_sql("select max(ban_fecha_env) as fecha,max(ban_hora_env) as hora from bandeja");?>
            <tr>
                <?php $fecha_desp=  FUNCIONES::get_fecha_latina($ultimo_desp->fecha);?>
                
                <td id="ult_desp"><?php echo "$fecha_desp $ultimo_desp->hora";?></td>
                <?php $fecha_env=  FUNCIONES::get_fecha_latina($ultimo_env->fecha);?>
                <td id="ult_env"><?php echo "$fecha_env $ultimo_env->hora";?></td>
            </tr>
        </tbody>
    </table>    
    <script>
        
        setInterval("cargar_panel()",10000);
        function cargar_panel(){
            $.get('AjaxRequest.php',{peticion:'sms_panel'},function (respuesta){                
                var objeto=JSON.parse(respuesta);
                $('#sms_listo').text(objeto.listo);
                $('#sms_despachado').text(objeto.despachado);
                $('#sms_enviado').text(objeto.enviado);
                $('#ult_desp').text(objeto.ult_desp);
                $('#ult_env').text(objeto.ult_env);
            });
        }
    </script>
    <?php
    }

    

}
?>