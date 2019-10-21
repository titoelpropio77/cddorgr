<?php

class RESERVA_PARAMETROS extends RESERVA {

    function RESERVA_PARAMETROS() {
        parent::__construct();
    }

    function parametros_varios() {

        if ($_POST) {
            $this->guardar_cambio_parametros_varios($_GET, $_POST);
        } else {
            $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
            $this->frm_cambio_parametros_varios($reserva);
        }
    }

    function guardar_cambio_parametros_varios($get, $post) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $get = (object) $get;
        $post = (object) $post;
        $conec = new ADO();

        $sql_up = "update reserva_terreno set 
            res_vdo_id='$post->vendedor',
            res_multinivel='$post->multinivel',
            res_of_id='$post->of_id',
            res_ci = '$post->res_ci',    
            res_cm = '$post->res_cm'
            where res_id='$get->id'";
//        echo "<p>$sql_up</p>";
        $conec->ejecutar($sql_up);
        $mensaje = "Parametros de la Reserva modificados exitosamente.";
        $_GET[tarea] = "PARAMETROS DE RESERVA";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_cambio_parametros_varios($reserva) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("CAMIBIAR PARAMETROS RESERVA");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=reserva&tarea=CAMBIAR VENDEDOR&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $reserva->res_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Reserva</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Reserva</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $reserva->res_id; ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_concepto($reserva->res_lot_id); // $reserva->ven_concepto;        ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Modalidad</div>
                            <div id="CajaInput">
                                <select name="multinivel" id="multinivel">
                                    <option value="">--Seleccione--</option>
                                    <option style="color:#ffffff;background-color:#db7093" <?php echo ($reserva->res_multinivel == 'no') ? 'selected' : ''; ?> value="no">Tradicional</option>
                                    <option style="color:#ffffff;background-color:#32cd32" <?php echo ($reserva->res_multinivel == 'si') ? 'selected' : ''; ?> value="si">Multinivel</option>
                                </select>

                            </div>
                        </div>
                       
                        <div id="ContenedorDiv" class="div_vendedor">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="vendedor_id" id="vendedor_id" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php
                                    $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre!='AFILIADOS';";
                                    $vendedores1 = FUNCIONES::objetos_bd_sql($sql);
                                    for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) {
                                        $objeto = $vendedores1->get_objeto();
                                        $selected = ($objeto->id == $reserva->res_vdo_id) ? 'selected' : '';
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php
                                        $vendedores1->siguiente();
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!--Inicio-->
                        <div id="ContenedorDiv" class="div_afiliado">
                            <div class="Etiqueta"><span class="flechas1">*</span>Patrocinador</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="afiliado" id="afiliado" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>                                    
                                    <?php
                                    $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where vdo_estado='Habilitado'
                                               and ven_estado in ('Pendiente','Pagado')
                                               and vgru_nombre='AFILIADOS'
                                               
                                               union
                                               
                                               select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               
                                                where vdo_cod_legado=1";

                                    $vendedores1 = FUNCIONES::objetos_bd_sql($sql);
                                    for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) {
                                        $objeto = $vendedores1->get_objeto();
                                        $selected = ($objeto->id == $reserva->res_vdo_id) ? 'selected' : '';
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php
                                        $vendedores1->siguiente();
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->   
                        
                        <div id="div_datos_oferta">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Oferta:</div>
                                <div id="CajaInput">
                                    <select name="of_id" id="of_id">
                                        <option value="">--Seleccione--</option>
                                        <?php
                                        $fun = new FUNCIONES();
                                        $fun->combo("select of_id as id,of_nombre as nombre from oferta where of_eliminado='No'", $reserva->res_of_id);
                                        ?>
                                    </select>

                                </div>
                            </div>
                            
							<div id="ContenedorDiv">
								<div class="Etiqueta" >Cuota Inicial:</div>
                                <div id="CajaInput">
                                    <input id="res_ci" name="res_ci" value="<?php echo $reserva->res_ci;?>" />
                                </div>
							</div>
							
                            <div id="ContenedorDiv" class="cuotas">                                                               
                                <div class="Etiqueta" >Cuota Mensual:</div>
                                <div id="CajaInput">
                                    <input id="res_cm" name="res_cm" value="<?php echo $reserva->res_cm;?>" />
                                </div>
                            </div>    
                        
                        </div>

                        <input readonly="true" type="hidden" name="vendedor" id="vendedor" size="5" value="" >
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=<?php echo $this->modulo; ?>&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
            
        mask_decimal('#res_cm', null);
        mask_decimal('#res_ci', null);
            
        $('#of_id').change(function(){
            var oferta = $('#of_id option:selected').val() * 1;
            
            if (oferta > 0) {
                $('.cuotas').show();
            } else {
                $('.cuotas').hide();
            }
        });
        
        $('#of_id').trigger('change');

        $('#vendedor_id').chosen({
            allow_single_deselect: true
        });

        $('#afiliado').chosen({
            allow_single_deselect: true
        });

        $('#multinivel').change(function() {

            var op_mul = $('#multinivel option:selected').val();

            if (op_mul === 'si') {
                $('.div_vendedor').hide();
                $('.div_afiliado').show();
                $('#div_datos_oferta').show();
//                                        $('#afiliado option[value=""]').attr('selected', true);
                console.log('multinivel si...');
                
            } else {
                $('.div_afiliado').hide();
                $('.div_vendedor').show();
                $('#div_datos_oferta').hide();
//                                        $('#vendedor option[value=""]').attr('selected', true);
                console.log('multinivel no...');
                
            }
        });
        
        $('#multinivel').trigger('change');


        $('#ven_ufecha_prog').mask('99/99/9999');
        $('#frm_sentencia').submit(function() {
            return false;
        });
        $('#btn_guardar').click(function() {

            var op_mul = $('#multinivel').val();

            if (op_mul === '') {
                $.prompt('Indique la modadlidad de la reserva.');
                return false;
            }

            var s_vdo = "Vendedor";
            if (op_mul === 'si') {
                $('#vendedor').val($('#afiliado option:selected').val());
                s_vdo = "Patrocinador"
            } else {
                $('#vendedor').val($('#vendedor_id option:selected').val());
            }

            var vendedor = $('#vendedor').val();

            if (vendedor === '') {
                $.prompt('Indique el ' + s_vdo + " de la reserva.");
                return false;
            }
            document.frm_sentencia.submit();
        });
        </script>
        <?php
    }

}
