<?php
class CON_CAJERO extends BUSQUEDA {
    var $formulario;
    var $mensaje;	
    function CON_CAJERO(){
        //permisos
        $this->ele_id=169;
        $this->busqueda();		
        if(!($this->verificar_permisos('AGREGAR'))){
            $this->ban_agregar=false;
        }
        //fin permisos		
        $this->num_registros=14;		
        $this->coneccion= new ADO();		
        $this->arreglo_campos[0]["nombre"] = "int_nombre";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "int_apellido";
        $this->arreglo_campos[1]["texto"] = "Apellido";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;

        $this->arreglo_campos[2]["nombre"] = "usu_id";
        $this->arreglo_campos[2]["texto"] = "Usuario";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 25;

        $this->link='gestor.php';		
        $this->modulo='con_cajero';		
        $this->formulario = new FORMULARIO();		
        $this->formulario->set_titulo('CAJEROS');
    }

    function dibujar_busqueda(){		
        $this->formulario->dibujar_cabecera();		
        $this->dibujar_listado();
    }

    function set_opciones(){
            $nun=0;		
            if($this->verificar_permisos('VER')){
                $this->arreglo_opciones[$nun]["tarea"]='VER';
                $this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
                $this->arreglo_opciones[$nun]["nombre"]='VER';
                $nun++;
            }

            if($this->verificar_permisos('MODIFICAR')){
                $this->arreglo_opciones[$nun]["tarea"]='MODIFICAR';
                $this->arreglo_opciones[$nun]["imagen"]='images/b_edit.png';
                $this->arreglo_opciones[$nun]["nombre"]='MODIFICAR';
                $nun++;
            }

            if($this->verificar_permisos('ELIMINAR')){
                $this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
                $this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
                $this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
                $nun++;
            }
    }

    function dibujar_listado(){
        $sql="SELECT CONCAT(int_nombre,' ',int_apellido,'  ','(',cja_usu_id,')') as nombre,cja_usu_id,cja_estado 
                    FROM con_cajero inner join ad_usuario on (cja_usu_id = usu_id)
                    inner join interno on (usu_per_id = int_id)";
//            echo $sql;
        $this->set_sql($sql,' order by nombre asc ');		
        $this->set_opciones();		
        $this->dibujar();		
    }

    function dibujar_encabezado(){
            ?>
                    <tr>
                    <th>Usuario</th>
                            <th>Caja</th>
                            <th>Estado</th>
                <th class="tOpciones" width="100px">Opciones</th>
                    </tr>

            <?PHP
    }

    function mostrar_busqueda(){
        $conversor = new convertir();		
        for($i=0;$i<$this->numero;$i++){				
            $objeto=$this->coneccion->get_objeto();
            echo '<tr>';									
            echo "<td>";
                echo $objeto->nombre;
            echo "&nbsp;</td>";
            echo "<td>";
                echo $this->obtener_cajas($objeto->cja_usu_id);
            echo "&nbsp;</td>";
            echo "<td>";
                if ($objeto->cja_estado == 1) echo "Habilitado"; else echo "Deshabilitado";
            echo "&nbsp;</td>";
            echo "<td>";
                echo $this->get_opciones($objeto->cja_usu_id);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function obtener_cajas($usu_id){
        $sql="select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo in(select cjadet_cue_id from con_cajero_detalle where cjadet_usu_id='$usu_id')";
//            echo $sql;
        $cajas=  FUNCIONES::objetos_bd_sql($sql);
        $txt_cajas="";
        for ($i = 0; $i < $cajas->get_num_registros(); $i++) {
            $objeto=$cajas->get_objeto();
            if($i>0){
                $txt_cajas.=', ';
            }                    
            $txt_cajas.=$objeto->cue_descripcion;
            $cajas->siguiente();
        }
        return $txt_cajas;
    }

    function cargar_datos(){
        $conec=new ADO();
        $sql="select * from con_cajero
                        where cja_usu_id = '".$_GET['id']."'";		
        $conec->ejecutar($sql);		
        $objeto=$conec->get_objeto();		
        $_POST['cja_usu_id']=$objeto->cja_usu_id;            
        $_POST['cja_estado']=$objeto->cja_estado;

//        $sql="select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo in(select cjadet_cue_id from con_cajero_detalle where cjadet_usu_id='$objeto->cja_usu_id')";
        $sql="select cue_codigo,cue_descripcion,cjadet_pago from con_cuenta, con_cajero_detalle where cue_codigo=cjadet_cue_id and cue_ges_id='$_SESSION[ges_id]' and cjadet_usu_id='$objeto->cja_usu_id';";
//            echo $sql;
        $cajas=  FUNCIONES::objetos_bd_sql($sql);
        $txt_cajas="";
        $cod_cuentas=array();
        $txt_cod_cuentas=array();
        for ($i = 0; $i < $cajas->get_num_registros(); $i++) {
            $objeto=$cajas->get_objeto();
            $cod_cuentas[]=$objeto->cue_codigo;
            $txt_cod_cuentas[]=$objeto->cue_descripcion;
            $_POST['ie_caja_'.str_replace('.', '', $objeto->cue_codigo)]=$objeto->cjadet_pago;
            $cajas->siguiente();
        }
        $_POST['cod_cuentas']=$cod_cuentas;
        $_POST['txt_cod_cuentas']=$txt_cod_cuentas;
    }

    function datos(){
        if($_POST){
            //texto,  numero,  real,  fecha,  mail.
            $num=0;
            $valores[$num]["etiqueta"]="Usuario";
            $valores[$num]["valor"]=$_POST['cja_usu_id'];
            $valores[$num]["tipo"]="texto";
            $valores[$num]["requerido"]=true;                
            $num++;                
            $valores[$num]["etiqueta"]="Estado";
            $valores[$num]["valor"]=$_POST['cja_estado'];
            $valores[$num]["tipo"]="numero";
            $valores[$num]["requerido"]=true;                
            $cuentas=$_POST['cod_cuentas'];                
            $val=NEW VALIDADOR;
            $b=true;
            if(count($cuentas)==0){
                $val->mensaje.="<li>Debe asignarle una o varias <b>cajas</b> al Usuario</li>";
                $b=false;
            }                
            $this->mensaje="";
            if($val->validar($valores) && $b){
                return true;
            }else{
                $this->mensaje=$val->mensaje;
                return false;
            }
        }
        return false;
    }

    function formulario_tcp($tipo){
        switch ($tipo){
            case 'ver':{
                        $ver=true;
                        break;
                    }

            case 'cargar':{
                        $cargar=true;
                        break;
                    }
        }
        $url=$this->link.'?mod='.$this->modulo;				
        $red=$url;				
        if(!($ver)){
            $url.="&tarea=".$_GET['tarea'];
        }

        if($cargar){
            $url.='&id='.$_GET['id'];
        }

        $this->formulario->dibujar_tarea('CAJERO');

        if($this->mensaje<>""){
            $this->formulario->mensaje('Error',$this->mensaje);
        }
    ?>
        <script type="text/javascript">
            function enviar(form){
                document.frm_sentencia.cja_usu_id.disabled = false;
                form.submit();
            }
        </script>
        <style>
            .tab_lista_cuentas{
                list-style: none;                                    
                width: 100%;                                    
                overflow:scroll ;
                background-color: #ededed;
                border-collapse: collapse;  
                font-size: 12px;
            }
            .tab_lista_cuentas tr th{
                background-color: #dadada; 
            }
            .tab_lista_cuentas tr td{
                padding: 3px 3px;
            }
            .tab_lista_cuentas tr:hover{
                background-color: #f9e48c;
            }                                
            .img_del_cuenta{                                    
                font-weight: bold;
                cursor: pointer;
                width: 12px;
            }
            .box_lista_cuenta{
                width:400px;min-width: 200px;background-color:#F2F2F2;overflow:auto;
                border: 1px solid #8ec2ea; min-height: 150px
            }
            .add_det{
                cursor: pointer;
            }
            .tab_lista_cuentas input[type="checkbox"]{height: 0;}
        </style>
        <script src="js/util.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">				  
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Usuario</div>
                            <div id="CajaInput">
                                <select style="min-width: 300px;" name="cja_usu_id" class="caja_texto" <?php if ($_GET['tarea']=="MODIFICAR") echo "disabled";?>>
                                     <option value="">Seleccione</option>
                                     <?php 		
                                         $fun=NEW FUNCIONES;
                                         $fun->combo("select usu_id as id,concat(int_nombre,' ',int_apellido, '(',usu_id,')') as nombre from ad_usuario,interno where int_id=usu_per_id and usu_estado = 1 order by usu_id asc",$_POST['cja_usu_id']);		
                                     ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Caja</div>
                            <div id="CajaInput">
                                <?php                            
                                $gesid=$_SESSION[ges_id];
                                $obj_act = FUNCIONES::atributo_bd_sql("select conf_valor as campo from con_configuracion where conf_ges_id='$gesid' and conf_nombre='cuentas_act_disp'");
                                $cuentas_act_disp=$obj_act!=''?explode(',',$obj_act):array();                                    
                                ?>
                                <select style="min-width: 400px;"  name="cja_cue_id" id="cja_cue_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($cuentas_act_disp as $act_disp): ?>
                                    <tr data-id="<?=$act_disp?>">
                                        <?php $txt_cu=FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$act_disp' and cue_ges_id='$gesid'", "cue_descripcion");?>
                                        <option value="<?php echo $act_disp?>"><?=$txt_cu;?></option>
                                    </tr>                                        
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <img id="add_det" class="add_det" height="18" src="images/boton_agregar.png" style="display: none;">
                        </div>                            
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuentas a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_cuentas_act_disp" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Cuenta</th>
                                                <th width="8%">I.E.</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $cod_cuentas=$_POST['cod_cuentas'];
                                            $txt_cod_cuentas=$_POST['txt_cod_cuentas'];
                                            ?>
                                            <?php for ($i=0;$i<count($_POST['cod_cuentas']) ;$i++) {?>
                                            <?php $codigo=$cod_cuentas[$i];?>                                            
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="txt_cod_cuentas[]" value="<?php echo $txt_cod_cuentas[$i];?>">
                                                    <input type="hidden" name="cod_cuentas[]" class="h_cuentas_act_disp" value="<?php echo $codigo;?>">
                                                    <?php echo $txt_cod_cuentas[$i];?>
                                                </td>
                                                <td width="8%"><input type="checkbox" class="ie_caja" name="ie_caja_<?php echo str_replace('.', '', $codigo)?>" value="1" <?php echo $_POST['ie_caja_'.str_replace('.', '', $codigo)]?'checked="true"':'';?>></td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            $('#cja_cue_id').chosen({
                                allow_single_deselect:true
                            }).change(function(){
                                console.log($(this).val());
                                var codigo=trim($('#cja_cue_id option:selected').val());
                                var valor=trim($('#cja_cue_id option:selected').text());
                                agregar_cuenta({codigo:codigo, valor:valor},'cuentas_act_disp');
                                $(this).val('');
                                $('#cja_cue_id option:[value=""]').attr('selected','true');
                                $('#cja_cue_id').trigger('chosen:updated');
                            });
                            $('#add_det').click(function(){
                                var codigo=trim($('#cja_cue_id option:selected').val());
                                var valor=trim($('#cja_cue_id option:selected').text());

                                if(codigo!==""){
                                    agregar_cuenta({codigo:codigo, valor:valor},'cuentas_act_disp');
                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
                                    
                                }else{
                                    $.prompt('Seleccione una Cuenta');
                                }
                            });
                            function agregar_cuenta(cuenta,input) {
                                if (!existe_en_lista(cuenta.codigo,input)) {
                                    var cue_cod=cuenta.codigo.replace(/\./gi,'');
                                    var fila='';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" name="txt_cod_cuentas[]" value="'+cuenta.valor+'">';
                                    fila += '       <input type="hidden" name="cod_cuentas[]" class="h_cuentas_act_disp" value="'+cuenta.codigo+'">';
                                    fila += '       ' + cuenta.valor;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><input type="checkbox" class="ie_caja" name="ie_caja_'+cue_cod+'" value="1"></td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $("#tab_"+input+' tbody').append(fila);                                
                                }else{
                                    $.prompt('La cuenta ya existe en la lista');
                                }
                            }

                            function existe_en_lista(id_cuenta,input) {
                                var lista = $(".h_"+input+"");
                                for (var i = 0; i < lista.size(); i++) {
                                    var cuenta = lista[i];
                                    var id = $(cuenta).val();
                                    if (id === id_cuenta) {
                                        return true;
                                    }
                                }
                                return false;
                            }
                            $(".img_del_cuenta").live('click', function() {
                                $(this).parent().parent().remove();
                            });
                        </script>                                
                        <div id="ContenedorDiv">
                           <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                           <div id="CajaInput">
                                <select name="cja_estado" class="caja_texto">
                                    <option value="" >Seleccione</option>
                                    <option value="1" <?php if($_POST['cja_estado']=='1') echo 'selected="selected"'; ?>>Habilitado</option>
                                    <option value="0" <?php if($_POST['cja_estado']=='0') echo 'selected="selected"'; ?>>Deshabilitado</option>
                                </select>
                           </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                       <div id="CajaBotones">
                            <center>
                            <?php if(!($ver)){ ?>
                                <input type="button" class="boton" name="guardar" value="Guardar" onclick="enviar(this.form);">
                                <input type="reset" class="boton" name="" value="Cancelar">
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
                            <?php }else{?>
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
                            <?php } ?>
                            </center>
                       </div>
                    </div>
                </div>
            </form>
        </div>
            <?php
    }

    function insertar_tcp(){
        $conec= new ADO();
        $conec->begin_transaccion();
        $sql = "Select * from con_cajero where cja_usu_id = '".$_POST['cja_usu_id']."'";		
        $conec->ejecutar($sql);		
        $num=$conec->get_num_registros();
        if ($num == 0){
            $usuario=$_POST[cja_usu_id];
            $sql="insert into con_cajero(cja_usu_id, cja_estado) 
                    values('$usuario','$_POST[cja_estado]')";
            $conec->ejecutar($sql);
            foreach ($_POST[cod_cuentas] as $codigo) {
                $pago=0;
                if($_POST['ie_caja_'.  str_replace('.', '', $codigo)]){
                    $pago=1;
                }
                $sql_det="insert into con_cajero_detalle(cjadet_usu_id, cjadet_cue_id,cjadet_pago) 
                            values('$usuario','$codigo','$pago')";
                $conec->ejecutar($sql_det);
            }
            $success=$conec->commit();
            if($success){
                $mensaje='Cajero Agregado Correctamente!!!';
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
            }else{
                $mensaje=  implode('<br>', $conec->get_errores()) ;                
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo."&meta=$_GET[meta]&data=$_GET[data]");
            }
        }else{
            $mensaje='El Usuario '.$_POST['cja_usu_id'].' ya esta registrado como con_cajero!!!';                
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);		
        }
    }

    function modificar_tcp(){
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        $conec= new ADO();		
        $conec->begin_transaccion();
        $sql="update con_cajero set cja_estado='".$_POST['cja_estado']."'
                    where cja_usu_id = '".$_GET['id']."'";
        $conec->ejecutar($sql);
        $sql_del="delete from con_cajero_detalle where cjadet_usu_id='$_GET[id]'";
        $conec->ejecutar($sql_del);
        foreach ($_POST[cod_cuentas] as $codigo) {
            $pago=0;
            if($_POST['ie_caja_'.  str_replace('.', '', $codigo)]){
                $pago=1;
            }
            $sql_det="insert into con_cajero_detalle(cjadet_usu_id, cjadet_cue_id,cjadet_pago) 
                        values('$_GET[id]','$codigo','$pago')";
            $conec->ejecutar($sql_det);
        }            
        $success=$conec->commit();            
        if($success){
            $mensaje='Cajero Modificado Correctamente!!!';
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
        }else{
            echo '<br>'.$conec->get_errores();
            $mensaje=  implode('<br>', $conec->get_errores()) ;                
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo."&meta=$_GET[meta]&data=$_GET[data]");
        }            
    }

    function formulario_confirmar_eliminacion(){		
        $mensaje='Esta seguro de eliminar el Cajero?';		
        $this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'cja_usu_id');
    }

    function eliminar_tcp(){
        $conec= new ADO();
        $sql="delete from con_cajero where cja_usu_id='".$_POST['cja_usu_id']."'";		
        $conec->ejecutar($sql);		
        $mensaje='Cajero Eliminado Correctamente!!!';		
        $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
    }
}
?>