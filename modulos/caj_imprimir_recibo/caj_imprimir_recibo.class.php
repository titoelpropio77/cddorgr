<?php

class caj_imprimir_recibo {

    var $mensaje;

    function caj_imprimir_recibo() {
        $this->link = 'gestor.php';
        $this->modulo = 'caj_imprimir_recibo';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('caj_imprimir_recibo DEL SISTEMA');
    }

    function datos() {
        if ($_POST)
            return true;
        else
            return false;
    }

    

    function formulario_tcp() {
        if ($_POST) {
            $this->mostrar_recibo();
        } else {
            $this->frm_recibo();
        }
    }
    
    function mostrar_recibo_old_version() {
        $nro_recibo=  trim($_POST[nro_recibo]);
        $pago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_recibo='$nro_recibo' and vpag_estado='Activo'");
        if($pago){
            $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
            include 'modulos/venta/venta.class.php';
            include 'modulos/venta/venta_cuotas.class.php';
            $vcuotas = new VENTA_CUOTAS();
            $vcuotas->imprimir_pago($venta, $pago->vpag_id);
        }else{
            $this->frm_recibo("No existe el Nro de Recibo");
        }
            
        
        
    }
    
    function mostrar_recibo() {
        $nro_recibo=  trim($_POST[nro_recibo]);
        $pago=  FUNCIONES::objeto_bd_sql("
            select vpag_id as id,vpag_ven_id,'venta' as tabla from venta_pago where vpag_recibo='$nro_recibo' and vpag_estado='Activo'
                union
            select respag_id as id,'0' as vpag_ven_id,'reserva' as tabla from reserva_pago where respag_recibo='$nro_recibo' and respag_estado='Pagado'
                union 
            select epag_id as id,'0' as vpag_ven_id, 'extra_pago' as tabla from extra_pago where epag_recibo='$nro_recibo' and epag_estado='Pagado'");
        if($pago){
            if ($pago->tabla == 'venta') {
                $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
                include 'modulos/venta/venta.class.php';
                include 'modulos/venta/venta_cuotas.class.php';
                $vcuotas = new VENTA_CUOTAS();
                $vcuotas->imprimir_pago($venta, $pago->id);
            } else if ($pago->tabla == 'reserva') {
                include 'modulos/reserva/reserva.class.php';
                $reserva = new RESERVA();
                $reserva->nota_comprobante_reserva2($pago->id);
            } else if ($pago->tabla == 'extra_pago') {
                include 'modulos/extra_pago/extra_pago.class.php';
                $ep = new EXTRA_PAGO();
                $ep->nota_pago($pago->id);
            }
        }else{
            $this->frm_recibo("No existe el Nro de Recibo");
        }
            
        
        
    }
    
    function frm_recibo($msj='') {
        $this->formulario->dibujar_titulo('Imprimir Recibo');
        ?>
        <script src="js/util.js"></script>
            
         <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <?php if($msj!=''){?>
                            <div id="ContenedorDiv" style="color: #ff0000; font-size: 14px">
                                <em><?php echo $msj;?></em>
                            </div>
                                
                        <?php } ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nro Recibo</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="nro_recibo" id="nro_recibo" size="25" value="<?php echo $_POST[nro_recibo];?>">
                            </div>
                        </div>   
                    </div>   
                </div>   
            </form>   
        </div>   
        <script>
            $('#frm_sentencia').submit(function(){
                var nro_rec=trim($('#nro_recibo').val());
                if(nro_rec===''){
                    return false;
                }
            });
        </script>
        <?php
    }
}

?>