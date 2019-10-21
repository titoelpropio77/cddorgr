<?php
class PARAMETROS {
    var $mensaje;
    function PARAMETROS() {
        $this->link = 'gestor.php';
        $this->modulo = 'parametros';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('PARAMETROS DEL SISTEMA');
    }

    function datos() {
        if ($_POST)
            return true;
        else
            return false;
    }

    function actualizar() {
        


        $this->mensaje = "Los parametros fueron actualizados correctamente";

        $conec = new ADO();
        
//        $par_gastos="";
        
        $par_gastos="[";
        $une_ids=$_POST[une_ids];
        $une_porcs=$_POST[une_porcs];
        for ($i = 0; $i < count($une_ids); $i++) {
            if($i>0){
                $par_gastos.=',';
            }
            $par_gastos.="{\"une_id\":\"$une_ids[$i]\",\"une_porc\":\"$une_porcs[$i]\"}";
        }
        $par_gastos.=']';
        
        $par_suc_envio="[";
        $suc_ids=$_POST[suc_ids];
        $suc_porcs=$_POST[suc_porcs];
        for ($i = 0; $i < count($suc_ids); $i++) {
            if($i>0){
                $par_suc_envio.=',';
            }
            $par_suc_envio.="{\"suc_id\":\"$suc_ids[$i]\",\"suc_porc\":\"$suc_porcs[$i]\"}";
        }
        $par_suc_envio.=']';
        $par_fmod_usu_ids=  implode(',', $_POST[fmod_usu_ids]);
        $par_desc_interes_usu_ids=  implode(',', $_POST[desc_interes_usu_ids]);
        
        
        $sql = "update ad_parametro set 
		par_smtp='" . $_POST['smtp'] . "',
		par_salida='" . $_POST['correo'] . "',
		par_pas_salida='" . $_POST['password'] . "',
		par_entrada='" . $_POST['recepcion'] . "',
		par_interes_mensual='" . $_POST['interes_mensual'] . "',
		par_cuota_inicial='" . $_POST['cuota_inicial'] . "',
		par_cpc_cue='" . $_POST['par_cpc_cue'] . "',
		par_cpc_cc='" . $_POST['par_cpc_cc'] . "',
		par_cpp_cue='" . $_POST['par_cpp_cue'] . "',
		par_cpp_cc='" . $_POST['par_cpp_cc'] . "',
		par_bloq_cuota='" . $_POST['par_bloq_cuota'] . "',
		
		par_cuenta_ingreso_compra='" . $_POST['par_cuenta_ingreso_compra'] . "',
		par_cuenta_egreso_compra='" . $_POST['par_cuenta_egreso_compra'] . "',
		par_cc_compra_divisa='" . $_POST['par_cc_compra_divisa'] . "',
		par_cuenta_ingreso_venta='" . $_POST['par_cuenta_ingreso_venta'] . "',
		par_cuenta_egreso_venta='" . $_POST['par_cuenta_egreso_venta'] . "',
		par_cc_venta_divisa='" . $_POST['par_cc_venta_divisa'] . "',
		par_pagocomisiones_cc='" . $_POST['par_pagocomisiones_cc'] . "',    
		par_vigencia_reserva='" . $_POST['vigencia_reserva'] . "',    
		par_importcobro_caja='" . $_POST['par_importcobro_caja'] . "',
		
		par_vdo_gergeneral='" . $_POST['par_vdo_gergeneral'] . "',
		par_comi_gercom='" . $_POST['par_comi_gercom'] . "',
                par_val_dias_mora='" . $_POST['val_dias_mora'] . "',
                par_valor_form='" . $_POST['par_valor_form'] . "',
                par_facturar='" . $_POST['par_facturar'] . "',
                par_cambio_titular='" . $_POST['par_cambio_titular'] . "',
                par_cambio_ubicacion='" . $_POST['par_cambio_ubicacion'] . "',
                par_eliminar_dia='" . $_POST['par_eliminar_dia'] . "',
                par_modificar_fecha='" . $_POST['par_modificar_fecha'] . "',
                par_gastos='" . $par_gastos . "',
                par_suc_envio='" . $par_suc_envio . "',
                par_fmod_usu_ids='" . $par_fmod_usu_ids . "',
                par_desc_interes_usu_ids='$par_desc_interes_usu_ids',
                par_mostrar_comisiones='$_POST[par_mostrar_comisiones]',    
		par_comi_gergeneral='" . $_POST['par_comi_gergeneral'] . "'";
//        echo $sql;
        $conec->ejecutar($sql);
    }

    function formulario_tcp() {
        if ($this->datos()) {
            $this->actualizar();
        }

        $conec = new ADO();
        $sql = "select * from ad_parametro";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $smtp = $objeto->par_smtp;
        $correo = $objeto->par_salida;
        $password = $objeto->par_pas_salida;
        $recepcion = $objeto->par_entrada;
        $cuota_inicial = $objeto->par_cuota_inicial;
        $interes_mensual = $objeto->par_interes_mensual;
        $vigencia_reserva = $objeto->par_vigencia_reserva;
        $par_cpc_cue = $objeto->par_cpc_cue;
        $par_cpc_cc = $objeto->par_cpc_cc;
        $par_cpp_cue = $objeto->par_cpp_cue;
        $par_cpp_cc = $objeto->par_cpp_cc;
        $par_bloq_cuota = $objeto->par_bloq_cuota;

        $par_cuenta_ingreso_compra = $objeto->par_cuenta_ingreso_compra;
        $par_cuenta_egreso_compra = $objeto->par_cuenta_egreso_compra;
        $par_cc_compra_divisa = $objeto->par_cc_compra_divisa;
        $par_cuenta_ingreso_venta = $objeto->par_cuenta_ingreso_venta;
        $par_cuenta_egreso_venta = $objeto->par_cuenta_egreso_venta;
        $par_cc_venta_divisa = $objeto->par_cc_venta_divisa;
        $par_importcobro_caja = $objeto->par_importcobro_caja;
        $par_pagocomisiones_cc = $objeto->par_pagocomisiones_cc;
        $par_pagocomisiones_cue = $objeto->par_pagocomisiones_cue;
		
        $par_vdo_gergeneral = $objeto->par_vdo_gergeneral;
        $par_comi_gercom = $objeto->par_comi_gercom;
        $par_comi_gergeneral = $objeto->par_comi_gergeneral;
        $val_dias_mora = $objeto->par_val_dias_mora;
        $par_valor_form = $objeto->par_valor_form;
        $par_facturar = $objeto->par_facturar;
        $par_cambio_titular= $objeto->par_cambio_titular;
        $par_cambio_ubicacion= $objeto->par_cambio_ubicacion;
        $par_eliminar_dia = $objeto->par_eliminar_dia*1;
        $par_modificar_fecha = $objeto->par_modificar_fecha*1;
        $par_gastos = $objeto->par_gastos;
        $par_suc_envio = $objeto->par_suc_envio;
        $par_fmod_usu_ids = $objeto->par_fmod_usu_ids;
        $par_desc_interes_usu_ids = $objeto->par_desc_interes_usu_ids;

        $url = $this->link . '?mod=' . $this->modulo;

        $this->formulario->dibujar_tarea();

        if ($this->mensaje <> "") {

            $this->formulario->mensaje('Correcto', $this->mensaje);
        }
        ?>
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
                width:200px;min-width: 200px;background-color:#F2F2F2;overflow:auto;
                border: 1px solid #8ec2ea; min-height: 100px
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
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div hidden="">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Servidor SMTP</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="smtp" id="smtp" size="25" value="<?php echo $smtp; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Servidor SMTP</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="smtp" id="smtp" size="25" value="<?php echo $smtp; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Correo de Salida</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="correo" id="correo" size="25" value="<?php echo $correo; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Contraseña Correo Salida</div>
                                <div id="CajaInput">
                                    <input type="password" class="caja_texto" name="password" id="password" size="25" value="<?php echo $password; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Correo de Recepción</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="recepcion" id="recepcion" size="25" value="<?php echo $recepcion; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Valor Formulario</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="par_valor_form" id="par_valor_form" size="15" value="<?php echo $par_valor_form; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Mora por dia</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="val_dias_mora" id="val_dias_mora" size="15" value="<?php echo $val_dias_mora; ?>">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Con Factura</div>
                                <div id="CajaInput">
                                    <select name="par_facturar" style="width: 100px">
                                        <option value="1" <?php echo $par_facturar=='1'?'selected="true"':''?>>Si</option>
                                        <option value="0" <?php echo $par_facturar=='0'?'selected="true"':''?>>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                            
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Dias de Vigencia de la Reserva</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="vigencia_reserva" id="vigencia_reserva" size="25" value="<?php echo $vigencia_reserva; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Costo Cambio Titular</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="par_cambio_titular" id="par_cambio_titular" size="15" value="<?php echo $par_cambio_titular; ?>" > $us
                            </div>
                        </div>
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Costo Cambio Ubicacion</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="par_cambio_ubicacion" id="par_cambio_ubicacion" size="15" value="<?php echo $par_cambio_ubicacion; ?>" > $us
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Eliminar Pagos solo del Dia</div>
                            <div id="CajaInput">
                                <select name="par_eliminar_dia" style="width: 100px">
                                    <option value="1" <?php echo $par_eliminar_dia=='1'?'selected="true"':''?>>Si</option>
                                    <option value="0" <?php echo $par_eliminar_dia=='0'?'selected="true"':''?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Modificar Fecha</div>
                            <div id="CajaInput">
                                <select name="par_modificar_fecha" style="width: 100px">
                                    <option value="1" <?php echo $par_modificar_fecha=='1'?'selected="true"':''?>>Si</option>
                                    <option value="0" <?php echo $par_modificar_fecha=='0'?'selected="true"':''?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Usuarios Mod. Fecha</div>
                            <div id="CajaInput">
                                <?php                            
                                
                                $lusuarios=  FUNCIONES::lista_bd_sql("select usu_id,int_nombre,int_apellido from ad_usuario, interno where int_id=usu_per_id");
                                ?>
                                <select style="min-width: 320px;"  name="fmod_usu_id" id="fmod_usu_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($lusuarios as $user): ?>
                                        <?php $txt_usu="$user->int_nombre $user->int_apellido ($user->usu_id)";?>
                                        <option value="<?php echo $user->usu_id?>"><?php echo $txt_usu;?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lista Usuarios Mod. Fecha</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta" style="min-width: 320px;"> 
                                    <table id="tab_fmod_usu_ids" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Cuenta</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if($par_fmod_usu_ids){
                                                $afm_users=  explode(',', $par_fmod_usu_ids);
                                            }else{
                                                $afm_users= array();
                                            }
                                            
                                            ?>
                                            <?php for ($i=0;$i<count($afm_users) ;$i++) {?>
                                            <?php $_usu_id=$afm_users[$i];?>
                                            <?php $obuser=  FUNCIONES::objeto_bd_sql("select usu_id,int_nombre,int_apellido from ad_usuario, interno where int_id=usu_per_id and usu_id='$_usu_id'");?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="fmod_usu_ids[]" class="fmod_usu_ids" value="<?php echo $obuser->usu_id;?>">
                                                    <?php echo "$obuser->int_nombre $obuser->int_apellido ($obuser->usu_id)";?>
                                                </td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            //tab_cuentas_fmod_user
                            $('#fmod_usu_id').chosen({
                                allow_single_deselect:true
                            }).change(function(){
//                                console.log($(this).val());
                                var id=trim($('#fmod_usu_id option:selected').val());
                                var texto=trim($('#fmod_usu_id option:selected').text());
                                
                                agregar_cuenta_fmod_usu_ids({id:id, texto:texto},'fmod_usu_ids');
                                $(this).val('');
                                $('#fmod_usu_id option:[value=""]').attr('selected','true');
                                $('#fmod_usu_id').trigger('chosen:updated');
                            });
                            
                            function agregar_cuenta_fmod_usu_ids(user,input) {
                                console.log(user);
                                if (!$('.fmod_usu_ids[value='+user.id+']').length) {
                                    
                                    var fila='';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" name="fmod_usu_ids[]" class="fmod_usu_ids" value="'+user.id+'">';
                                    fila += '       ' + user.texto;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    
                                    $("#tab_"+input+' tbody').append(fila);                                
                                }
                            }
                        </script>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Usuarios Desc. Interes</div>
                            <div id="CajaInput">
                                <?php                            
                                //desc_interes_usu_ids
                                $lusuarios=  FUNCIONES::lista_bd_sql("select usu_id,int_nombre,int_apellido from ad_usuario, interno where int_id=usu_per_id");
                                ?>
                                <select style="min-width: 320px;"  name="desc_interes_usu_ids" id="desc_interes_usu_ids" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($lusuarios as $user): ?>
                                        <?php $txt_usu="$user->int_nombre $user->int_apellido ($user->usu_id)";?>
                                        <option value="<?php echo $user->usu_id?>"><?php echo $txt_usu;?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lista Usuarios Desc. Interes</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta" style="min-width: 320px;"> 
                                    <table id="tab_desc_interes_usu_ids" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Cuenta</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if($par_desc_interes_usu_ids){
                                                $afm_users=  explode(',', $par_desc_interes_usu_ids);
                                            }else{
                                                $afm_users= array();
                                            }
                                            
                                            ?>
                                            <?php for ($i=0;$i<count($afm_users) ;$i++) {?>
                                            <?php $_usu_id=$afm_users[$i];?>
                                            <?php $obuser=  FUNCIONES::objeto_bd_sql("select usu_id,int_nombre,int_apellido from ad_usuario, interno where int_id=usu_per_id and usu_id='$_usu_id'");?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="desc_interes_usu_ids[]" class="desc_interes_usu_ids" value="<?php echo $obuser->usu_id;?>">
                                                    <?php echo "$obuser->int_nombre $obuser->int_apellido ($obuser->usu_id)";?>
                                                </td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            //tab_cuentas_fmod_user
                            $('#desc_interes_usu_ids').chosen({
                                allow_single_deselect:true
                            }).change(function(){
//                                console.log($(this).val());
                                var id=trim($('#desc_interes_usu_ids option:selected').val());
                                var texto=trim($('#desc_interes_usu_ids option:selected').text());
                                agregar_cuenta_desc_interes_usu_ids({id:id, texto:texto},'desc_interes_usu_ids');
                                $(this).val('');
                                $('#desc_interes_usu_ids option:[value=""]').attr('selected','true');
                                $('#desc_interes_usu_ids').trigger('chosen:updated');
                            });
                            
                            function agregar_cuenta_desc_interes_usu_ids(user,input) {
                                if (!$('.desc_interes_usu_ids[value='+user.id+']').length) {
                                    
                                    var fila='';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" name="desc_interes_usu_ids[]" class="desc_interes_usu_ids" value="'+user.id+'">';
                                    fila += '       ' + user.texto;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $("#tab_"+input+' tbody').append(fila);                                
                                }
                            }
                        </script>
                        <div id="ContenedorDiv" style="height: 10px;"></div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Unidad de Negocios</div>
                            <div id="CajaInput">
                                <?php                            
//                                $gesid=$_SESSION[ges_id];
                                $lista = FUNCIONES::lista_bd_sql("select * from con_unidad_negocio where une_eliminado='no'");
                                ?>
                                <select style="min-width: 200px;"  id="sel_une_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($lista as $objfil){ ?>
                                        <option value="<?php echo $objfil->une_id?>"><?php echo $objfil->une_nombre;?></option>
                                    <? } ?>
                                </select>
                            </div>
                            <img id="add_det" class="add_det" height="18" src="images/boton_agregar.png" style="display: none;">
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >U.N. a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_unegocios" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>U. Negocio</th>
                                                <th width="8%">%</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
//                                            echo $par_gastos.'----';
                                            $une_gastos=  json_decode($par_gastos);
//                                            FUNCIONES::print_pre($une_gastos);
                                            ?>
                                            <?php for ($i=0;$i<count($une_gastos) ;$i++) {?>
                                            <?php $uneg=$une_gastos[$i];?>                                            
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="une_ids[]" class="une_ids" value="<?php echo $uneg->une_id;?>">
                                                    <?php echo FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$uneg->une_id'");?>
                                                </td>
                                                <td width="8%"><input type="text" class="une_porcs" name="une_porcs[]" value="<?php echo $uneg->une_porc;?>" size="2" autocomplete="off" ></td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Sucursal Envio</div>
                            <div id="CajaInput">
                                <?php                            
//                                $gesid=$_SESSION[ges_id];
                                $lista = FUNCIONES::lista_bd_sql("select * from ter_sucursal where suc_eliminado='no'");
                                ?>
                                <select style="min-width: 200px;"  id="sel_suc_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($lista as $objfil){ ?>
                                        <option value="<?php echo $objfil->suc_id?>"><?php echo $objfil->suc_nombre;?></option>
                                    <? } ?>
                                </select>
                            </div>
                            <img id="add_det" class="add_det" height="18" src="images/boton_agregar.png" style="display: none;">
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Sucursal a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_sucursal" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Sucursal</th>
                                                <th width="8%">%</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
//                                            echo $par_gastos.'----';
                                            $suc_envio=  json_decode($par_suc_envio);
//                                            FUNCIONES::print_pre($une_gastos);
                                            ?>
                                            <?php for ($i=0;$i<count($suc_envio) ;$i++) {?>
                                            <?php $esuc=$suc_envio[$i];?>                                            
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="suc_ids[]" class="suc_ids" value="<?php echo $esuc->suc_id;?>">
                                                    <?php echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$esuc->suc_id'");?>
                                                </td>
                                                <td width="8%"><input type="text" class="suc_porcs" name="suc_porcs[]" value="<?php echo $esuc->suc_porc;?>" size="2" autocomplete="off" ></td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <?php
                        $_POST[par_mostrar_comisiones] = FUNCIONES::atributo_bd_sql("select par_mostrar_comisiones as campo from ad_parametro limit 1");
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Mostrar Comisiones Periodos Abiertos(Of. Afiliados)</div>
                            <div id="CajaInput">
                                <select id="par_mostrar_comisiones" name="par_mostrar_comisiones">
                                    <option value="No" <?php echo ($_POST[par_mostrar_comisiones] == 'No')?'selected':'';?>>No</option>
                                    <option value="Si" <?php echo ($_POST[par_mostrar_comisiones] == 'Si')?'selected':'';?>>Si</option>
                                </select>
                                    
                            </div>
                        </div>    

                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="button" class="boton" name="" value="Guardar" id="btn-guardar">
                                </center>
                            </div>
                        </div>
                    </div>
                    <script>
                            $('#sel_une_id').change(function(){
                                console.log($(this).val());
                                var une_id=trim($('#sel_une_id option:selected').val());
                                var une_desc=trim($('#sel_une_id option:selected').text());
                                agregar_detalle_unegocio({une_id:une_id, une_desc: une_desc},'tab_unegocios');
                                $(this).val('');
                                $('#cja_cue_id option:[value=""]').attr('selected','true');
                                $('#cja_cue_id').trigger('chosen:updated');
                            });
                            function agregar_detalle_unegocio(unegocio) {
                                if (!$('#tab_unegocios tr[data-id='+unegocio.une_id+']').length) {
//                                if (!existe_en_lista(unegocio.une_id,input)) {
//                                    var cue_cod=cuenta.codigo.replace(/\./gi,'');
                                    var fila='';
                                    fila += '<tr data-id="'+unegocio.une_id+'">';
                                    fila += '   <td >';
                                    fila += '       <input type="hidden" name="une_ids[]" class="une_ids" value="'+unegocio.une_id+'">';
                                    fila += '       ' + unegocio.une_desc;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><input type="text" class="une_porcs" name="une_porcs[]" value="" size="2" autocomplete="off" ></td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $('#tab_unegocios tbody').append(fila);                                
                                }else{
//                                    $.prompt('La cuenta ya existe en la lista');
                                }
                            }
                            $('#sel_suc_id').change(function(){
                                console.log($(this).val());
                                var suc_id=trim($('#sel_suc_id option:selected').val());
                                var suc_desc=trim($('#sel_suc_id option:selected').text());
                                agregar_detalle_sucursal({suc_id:suc_id, suc_desc: suc_desc},'tab_sucursal');
                                $(this).val('');
                                $('#cja_cue_id option:[value=""]').attr('selected','true');
                                $('#cja_cue_id').trigger('chosen:updated');
                            });
                            function agregar_detalle_sucursal(sucursal) {
                                if (!$('#tab_sucursal tr[data-id='+sucursal.suc_id+']').length) {
//                                if (!existe_en_lista(sucursal.suc_id,input)) {
//                                    var cue_cod=cuenta.codigo.replace(/\./gi,'');
                                    var fila='';
                                    fila += '<tr data-id="'+sucursal.suc_id+'">';
                                    fila += '   <td >';
                                    fila += '       <input type="hidden" name="suc_ids[]" class="suc_ids" value="'+sucursal.suc_id+'">';
                                    fila += '       ' + sucursal.suc_desc;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><input type="text" class="suc_porcs" name="suc_porcs[]" value="" size="2" autocomplete="off" ></td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $('#tab_sucursal tbody').append(fila);                                
                                }else{
//                                    $.prompt('La cuenta ya existe en la lista');
                                }
                            }
                            
                            $(".img_del_cuenta").live('click', function() {
                                $(this).parent().parent().remove();
                            });
                            mask_decimal('.une_porcs,.suc_porcs',null);
                            
                            $('#btn-guardar').click(function(){
                                var une_porcs=$('.une_porcs');
                                var sum=0;
                                for(var i=0;i<une_porcs.size();i++){
                                    sum+=$(une_porcs[i]).val()*1;
                                }
                                if(sum!==100){
                                    $.prompt('La sumatoria de los porcentajes de las U. de Negocios es diferente al 100%');
                                    return;
                                }
                                document.frm_sentencia.submit();
                            });
                        </script>
            </form>
        </div>	
                                    <?php
                                }

                            }
                            ?>