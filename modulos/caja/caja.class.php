<?php
class VENTA extends BUSQUEDA {
	var $formulario;
	var $mensaje;
	var $usu;

        var $ver_estado;
        var $ver_pagado;
        var $ver_saldo_capital;

	function VENTA(){
		//permisos
		$this->ele_id=173;
		$this->busqueda();
                
		//fin permisos
		$this->num_registros=14;
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='caja';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CAJA');
		
		$this->usu=new USUARIO;
		
		
	}
        
        function formulario_busqueda() {
            $this->formulario->dibujar_titulo('CAJA');
            ?>
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=caja&tarea=ACCEDER" name="frm_sentencia">
                <div id="ContenedorSeleccion" style="width: 98%">
                    <div id="ContenedorDiv">
                        <div id="CajaInput">
                            <select name="tipo_tran">
                                <option value="venta" <?php echo $_POST[tipo_tran]=='venta'?'selected="true"':'';?>>VENTA</option>
                                <option value="reserva" <?php echo $_POST[tipo_tran]=='reserva'?'selected="true"':'';?>>RESERVA</option>
                                <option value="extra_pago" <?php echo $_POST[tipo_tran]=='extra_pago'?'selected="true"':'';?>>PAGOS EXTRA</option>
                            </select>
                        </div>
                        <div class="Etiqueta" style="width: 40px">Nombre:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="nombre" id="nombre" value="<?php echo $_POST[nombre];?>" autocomplete="off" size="27">
                        </div>
                        <div class="Etiqueta" style="width: 40px">C.I.:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="ci" id="ci" value="<?php echo $_POST[ci];?>" autocomplete="off" size="10">
                        </div>
                        <div class="Etiqueta" style="width: 40px">Urb:</div>
                        <div id="CajaInput">
                            <select name="urb_id" id="urb_id">
                                <option value="">&nbsp;</option>
                                <?php
                                $fun=new FUNCIONES();
                                $fun->combo("select urb_id as id, urb_nombre as nombre from urbanizacion", $_POST[urb_id]);
                                ?>
                            </select>
                        </div>
                        <div class="Etiqueta" style="width: 40px">Mz.:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="man_nro" id="man_nro" value="<?php echo $_POST[man_nro];?>" autocomplete="off" size="2">
                        </div>
                        <div class="Etiqueta" style="width: 40px">Lote.:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="lot_nro" id="lot_nro" value="<?php echo $_POST[lot_nro];?>" autocomplete="off" size="2">
                        </div>
                        <div id="CajaInput" style="margin-left: 5px;">
                            <input class="boton" type="submit"  value="Buscar" name="">
                        </div>
                    </div>
                </div>
                    
            </form>
            <?php
        }
        function listado_busqueda_venta() {
            $and_filtro="";
            $nombre=trim($_POST[nombre]);
            if($nombre){
                $and_filtro.=" and concat(int_nombre,' ',int_apellido) like '%$nombre%'";
            }
            $ci=trim($_POST[ci]);
            if($ci){
                $and_filtro.=" and int_ci like '$ci%'";
            }
            $urb_id=trim($_POST[urb_id]);
            if($urb_id){
                $and_filtro=" and urb_id='$urb_id'";
            }
            $man_nro=trim($_POST[man_nro]);
            if($man_nro){
                $and_filtro=" and man_nro='$man_nro'";
            }
            $lot_nro=trim($_POST[lot_nro]);
            if($lot_nro){
                $and_filtro=" and lot_nro='$lot_nro'";
            }
            $sql_sel="select 
                            ven_id,ven_codigo,ven_fecha,int_nombre,int_apellido,ven_tipo,
                            ven_moneda,urb_nombre,man_nro,lot_nro,ven_estado
                        from 
                            venta,interno,lote,manzano,urbanizacion
                        where ven_int_id=int_id and ven_lot_id=lot_id and lot_man_id=man_id and man_urb_id=urb_id
                        $and_filtro
                        order by ven_id desc limit 0,30
                        ";
            
            $lista=  FUNCIONES::lista_bd_sql($sql_sel);
            if(count($lista)>0){
            ?>
            <table class="tablaLista" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Persona</th>
                        <th>Tipo</th>
                        <th>Moneda</th>
                        <th>Urbanización</th>
                        <th>Manzano</th>
                        <th>Lote</th>
                        <th>Estado</th>
                        <th class="tOpciones" width="50px">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $fila) {?>
                        <tr>
                            <td><?php echo $fila->ven_id;?></td>
                            <td><?php echo $fila->ven_codigo;?></td>
                            <td><?php echo FUNCIONES::get_fecha_latina($fila->ven_fecha);?></td>
                            <td><?php echo "$fila->int_nombre $fila->int_apellido";?></td>
                            <td><?php echo $fila->ven_tipo;?></td>
                            <td><?php echo $fila->ven_moneda;?></td>
                            <td><?php echo $fila->urb_nombre;?></td>
                            <td><?php echo $fila->man_nro;?></td>
                            <td><?php echo $fila->lot_nro;?></td>
                            <td><?php echo $fila->ven_estado;?></td>
                            <td>
                                <a class="linkOpciones" title="PAGOS" href="gestor.php?mod=venta&tarea=PAGOS&ori=caja&id=<?php echo $fila->ven_id;?>" >
                                    <img width="16" border="0" alt="PAGOS" src="images/cuenta.png">
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php }else{ ?>
                <br><br><br><br>
                <em class="fwbold" style="color: #ff0000;">No existen resultados con los parametros de busqueda.</em>
            <?
            }
        }
        
        function listado_busqueda_reserva() {
            $and_filtro="";
            $nombre=trim($_POST[nombre]);
            if($nombre){
                $and_filtro.=" and concat(int_nombre,' ',int_apellido) like '%$nombre%'";
            }
            $ci=trim($_POST[ci]);
            if($ci){
                $and_filtro.=" and int_ci like '$ci%'";
            }
            $urb_id=trim($_POST[urb_id]);
            if($urb_id){
                $and_filtro=" and urb_id='$urb_id'";
            }
            $man_nro=trim($_POST[man_nro]);
            if($man_nro){
                $and_filtro=" and man_nro='$man_nro'";
            }
            $lot_nro=trim($_POST[lot_nro]);
            if($lot_nro){
                $and_filtro=" and lot_nro='$lot_nro'";
            }
            $sql_sel="select 
                            res_id,res_fecha,int_nombre,int_apellido,
                            urb_nombre,man_nro,lot_nro,res_estado
                        from 
                            reserva_terreno,interno,lote,manzano,urbanizacion
                        where res_estado in ('Habilitado','Pendiente') and res_int_id=int_id and res_lot_id=lot_id and lot_man_id=man_id and man_urb_id=urb_id 
                        $and_filtro
                        order by res_id desc limit 0,30
                        ";
//            echo "$sql_sel";
            
            $lista=  FUNCIONES::lista_bd_sql($sql_sel);
            if(count($lista)>0){
            ?>
            <table class="tablaLista" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Fecha</th>
                        <th>Persona</th>
                        <th>Urbanización</th>
                        <th>Manzano</th>
                        <th>Lote</th>
                        <th>Estado</th>
                        <th class="tOpciones" width="50px">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $fila) {?>
                        <tr>
                            <td><?php echo $fila->res_id;?></td>                            
                            <td><?php echo FUNCIONES::get_fecha_latina($fila->res_fecha);?></td>
                            <td><?php echo "$fila->int_nombre $fila->int_apellido";?></td>
                            
                            <td><?php echo $fila->urb_nombre;?></td>
                            <td><?php echo $fila->man_nro;?></td>
                            <td><?php echo $fila->lot_nro;?></td>
                            <td><?php echo $fila->res_estado;?></td>
                            <td>
                                <a class="linkOpciones" title="PAGOS" href="gestor.php?mod=reserva&tarea=PAGAR ANTICIPO&ori=caja&id=<?php echo $fila->res_id;?>" >
                                    <img width="16" border="0" alt="PAGOS" src="images/cuenta.png">
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php }else{ ?>
                <br><br><br><br>
                <em class="fwbold" style="color: #ff0000;">No existen resultados con los parametros de busqueda.</em>
            <?
            }
        }
        
        function listado_busqueda_extra_pago() {
            $and_filtro="";
            $nombre=trim($_POST[nombre]);
            if($nombre){
                $and_filtro.=" and concat(int_nombre,' ',int_apellido) like '%$nombre%'";
            }
            $ci=trim($_POST[ci]);
            if($ci){
                $and_filtro.=" and int_ci like '$ci%'";
            }
            
            $sql_sel="select 
                            *
                        from 
                            extra_pago,interno
                        where epag_estado in ('Pendiente') and epag_int_id=int_id
                        $and_filtro
                        order by epag_id desc limit 0,30
                        ";
//            echo "$sql_sel";
            
            $lista=  FUNCIONES::lista_bd_sql($sql_sel);
            if(count($lista)>0){
            ?>
            <table class="tablaLista" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Fecha</th>
                        <th>Persona</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Moneda</th>
                        <th>Estado</th>
                        <th class="tOpciones" width="50px">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $fila) {?>
                        <tr>
                            <td><?php echo $fila->epag_id;?></td>                            
                            <td><?php echo FUNCIONES::get_fecha_latina($fila->epag_fecha_programada);?></td>
                            <td><?php echo "$fila->int_nombre $fila->int_apellido";?></td>
                            <td><?php echo $fila->epag_ept_id>0?FUNCIONES::atributo_bd_sql("select ept_nombre as campo from extra_pago_tipo where ept_id='$fila->epag_ept_id'"):'';?></td>
                            <td><?php echo $fila->epag_monto;?></td>
                            <td><?php echo $fila->epag_moneda;?></td>
                            <td><?php echo $fila->res_estado;?></td>
                            <td>
                                <a class="linkOpciones" title="PAGOS" href="gestor.php?mod=extra_pago&tarea=PAGOS&id=<?php echo $fila->epag_id;?>&ori=caja" >
                                    <img width="16" border="0" alt="PAGOS" src="images/cuenta.png">
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php }else{ ?>
                <br><br><br><br>
                <em class="fwbold" style="color: #ff0000;">No existen resultados con los parametros de busqueda.</em>
            <?
            }
        }
        
        
        
}
?>