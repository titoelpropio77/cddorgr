<?php

class VENTA_PARAMETROS extends VENTA {

    function VENTA_PARAMETROS() {
        parent::__construct();
    }

    function parametros_varios() {

        if ($_POST) {
            $this->guardar_cambio_parametros_varios($_GET, $_POST);
        } else {
            $venta = FUNCIONES::objeto_bd_sql("select * from venta 
                inner join comision_cobro on (ven_id=comcob_ven_id)
                where ven_id=$_GET[id]");
            $this->frm_cambio_parametros_varios($venta);
        }
    }

    function guardar_cambio_parametros_varios($get, $post) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $get = (object) $get;
        $post = (object) $post;
        $conec = new ADO();

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$get->id");
        $ven_fecha = $venta->ven_fecha;

        $fecha_act_mlm = FUNCIONES::atributo_bd_sql("select DATE_ADD('{$ven_fecha}',INTERVAL {$post->meses_bra} month)as campo");

        $sql_upd = "update venta set             
            ven_fecha_act_mlm='$fecha_act_mlm'    
            where ven_id='$get->id'";
//        echo "<p>$sql_upd;</p>";
        $conec->ejecutar($sql_upd);
        
        $sql_upd_cob = "update comision_cobro set comcob_monto_cuota='$post->bra' where comcob_ven_id='$get->id'";
//        echo "<p>$sql_upd_cob;</p>";
        $conec->ejecutar($sql_upd_cob);
        
        $mensaje = "Parametros de la Venta modificados exitosamente.";
        $_GET[tarea] = "PARAMETROS DE VENTA";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_cambio_parametros_varios($venta) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $fecha_act = DateTime::createFromFormat('Y-m-d', $venta->ven_fecha_act_mlm)->format('Ym');
        $fecha = DateTime::createFromFormat('Y-m-d', $venta->ven_fecha)->format('Ym');

        $sql_fec = "select PERIOD_DIFF('$fecha_act','$fecha')as campo";
//        echo $sql_fec;
        $meses_bra = FUNCIONES::atributo_bd_sql($sql_fec) * 1;

        $this->formulario->dibujar_titulo("CAMIBIAR PARAMETROS VENTA");
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $venta->ven_id; ?></div>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha de Venta</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($venta->ven_fecha); ?></div>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cliente</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id); ?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Concepto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::get_concepto($venta->ven_lot_id); // $reserva->ven_concepto;         ?></div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Bono BRA Mensual por Afiliado(USD):</div>
                            <div id="CajaInput">
                                <input type="text" id="bra" name="bra" value="<?php echo $venta->comcob_monto_cuota; ?>">
                            </div>
                        </div>

                        <div id="ContenedorDiv" class="div_vendedor">
                            <div class="Etiqueta"><span class="flechas1">*</span>Meses Para Pago de Bono BRA:</div>
                            <div id="CajaInput">
                                <input type="text" id="meses_bra" name="meses_bra" value="<?php echo $meses_bra; ?>">
                            </div>
                        </div>

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

                                mask_decimal('#bra', null);
                                mask_decimal('#meses_bra', null);
                                $('#btn_guardar').click(function() {
                                    var bra = $('#bra').val() * 1;
                                    if (bra === 0) {
                                        $.prompt('Indique un valor para el Bono BRA mayor a <b>0(cero)</b>.');
                                        return false;
                                    }
                                    
                                    var meses_bra = $('#meses_bra').val() * 1;
                                    if (meses_bra === 0) {
                                        $.prompt('Indique un valor para los Meses de Pago del Bono BRA mayor a <b>0(cero)</b>.');
                                        return false;
                                    }
                                    document.frm_sentencia.submit();
                                });
        </script>
        <?php
    }

}
