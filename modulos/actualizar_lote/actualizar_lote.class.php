<?php
class ACTUALIZAR_LOTE extends BUSQUEDA {
	var $formulario;
	var $mensaje;
	var $usu;

        var $ver_estado;
        var $ver_pagado;
        var $ver_saldo_capital;

	function ACTUALIZAR_LOTE(){
		//permisos
		$this->ele_id=188;
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
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=actualizar_lote&tarea=ACCEDER" name="frm_sentencia">
                <div id="ContenedorSeleccion" style="width: 98%">
                    <div id="ContenedorDiv">
                        <div id="CajaInput">
                            <select name="urb_id">
                                <option value=""></option>
                                <?php 
                                $fun=new FUNCIONES();
                                $fun->combo("select urb_nombre as nombre, urb_id as id from urbanizacion where urb_id!=5", $_POST[urb_id]);
                                ?>
                                
                            </select>
                        </div>
                        <div class="Etiqueta" style="width: 40px">Mz.:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="man_nro" id="man_nro" value="<?php echo $_POST[man_nro];?>" autocomplete="off" size="7">
                        </div>
                        <div class="Etiqueta" style="width: 40px">Lote.:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="lot_nro" id="lot_nro" value="<?php echo $_POST[lot_nro];?>" autocomplete="off" size="7">
                        </div>
                        <input type="hidden" name="cambiar" id="cambiar" value="">
                        <input type="hidden" name="nestado" id="nestado" value="">
                        <input type="hidden" name="lote_id" id="lote_id" value="">
                        <div id="CajaInput" style="margin-left: 5px;">
                            <input class="boton" type="submit"  value="Buscar" name="">
                        </div>
                    </div>
                </div>
                    
            </form>
            <?php
        }
        function listado_busqueda_lote() {
            if($_POST[cambiar]){
                $lote_id=$_POST[lote_id];
                $estado=$_POST[nestado];
                $sql_up="update lote set lot_estado='$estado' where lot_id=$lote_id";
                $conec=new ADO();
                $conec->ejecutar($sql_up);
            }
            $and_filtro="";
            
            $urb_id=trim($_POST[urb_id]);
            if($urb_id){
                $and_filtro.=" and urb_id='$urb_id'";
            }
            $man_nro=trim($_POST[man_nro]);
            if($man_nro){
                $and_filtro.=" and man_nro='$man_nro'";
            }
            $lot_nro=trim($_POST[lot_nro]);
            if($lot_nro){
                $and_filtro.=" and lot_nro='$lot_nro'";
            }
            $sql_sel="select 
                            *
                        from 
                           lote,manzano,urbanizacion
                        where lot_man_id=man_id and man_urb_id=urb_id
                        $and_filtro
                        order by lot_id desc limit 0,50
                        ";
//            echo $sql_sel;    
//        FUNCIONES::print_pre($_POST);
            $a_est=array('Disponible'=>'#00c705','Vendido'=>'#ed0b00','Reservado'=>'#0018ff','Bloqueado'=>'#505050');
            $lista=  FUNCIONES::lista_bd_sql($sql_sel);
            if(count($lista)>0){
            ?>
            <table class="tablaLista" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>URBANIZACION</th>
                        <th>MZ</th>
                        <th>LOTE</th>
                        <th>SUPERFICIE</th>
                        <th>ESTADO</th>
                        <th>CAMBIAR</th>
                        <th class="tOpciones" width=""></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $fila) {?>
                        <tr>
                            <td><?php echo $fila->lot_id;?></td>
                            <td><?php echo $fila->urb_nombre;?></td>
                            <td><?php echo $fila->man_nro;?></td>
                            <td><?php echo "$fila->lot_nro";?></td>
                            <td><?php echo $fila->lot_superficie;?></td>
                            <td><span style="color: #fff; padding:3px 5px; background-color: <?php echo $a_est[$fila->lot_estado];?>"><?php echo $fila->lot_estado;?></span></td>
                            <td>
                                <input type="hidden" class="lotes_id" value="<?php echo $fila->lot_id;?>">
                                <select name="" class="sel_estado">
                                    <option value=""></option>
                                    <option value="Vendido">Vendido</option>
                                    <option value="Reservado">Reservado</option>
                                    <option value="Bloqueado">Bloqueado</option>
                                    <option value="Disponible">Disponible</option>
                                </select>
                            </td>
                            <td>
                                <a class="aceptar_cambio linkOpciones" title="CAMBIAR" href="javascript:void(0)" >
                                    <img width="16" border="0" alt="OK" src="images/publicar.png">
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <script>
                $('.aceptar_cambio').click(function(){
                    var lote_id=$(this).parent().parent().find('.lotes_id').val()*1;
                    var estado=$(this).parent().parent().find('.sel_estado option:selected').val();
                    if(lote_id===0 || estado===''){
                        $.prompt('SELECCIONE EL NUEVO ESTADO DEL LOTE');
                        return false;
                    }
                    $('#cambiar').val('1');
                    $('#nestado').val(estado);
                    $('#lote_id').val(lote_id);
                    document.frm_sentencia.submit();
                    
                });
            </script>
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
                        <th class="tOpciones" width="140px">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $fila) {?>
                        <tr>
                            <td><?php echo $fila->ven_id;?></td>                            
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
        
        
        
}
?>