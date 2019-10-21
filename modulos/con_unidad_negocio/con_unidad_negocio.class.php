<?php
class con_unidad_negocio extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function con_unidad_negocio()
	{		//permisos
		$this->ele_id=172;		
		$this->busqueda();		
		if(!($this->verificar_permisos('AGREGAR'))){
			$this->une_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="une_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;
		
		$this->arreglo_campos[1]["nombre"]="une_descripcion";
		$this->arreglo_campos[1]["texto"]="Descripcion";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=25;
		
		$this->link='gestor.php';
		
		$this->modulo='con_unidad_negocio';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('UNIDAD DE NEGOCIO');
		
		$this->usu=new USUARIO;
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
	
	function dibujar_listado()
	{
		$sql="select  
				*
			  from 
				con_unidad_negocio
                            where une_eliminado='No' ";
		$this->set_sql($sql,'');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
                            <th>Nombre</th>		
                            <th>Descripci&oacute;n</th>
                            <th class="tOpciones" width="100px">Opciones</th>
			</tr>
		<?PHP
	}
	
	function mostrar_busqueda()
	{
		$conversor = new convertir();
		
		for($i=0;$i<$this->numero;$i++)
			{
				
				$objeto=$this->coneccion->get_objeto();
				echo '<tr>';
									
					echo "<td>";
						echo $objeto->une_nombre;
						echo "&nbsp;";
					echo "</td>";
					
					echo "<td>";
                                                echo $objeto->une_descripcion;
						echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->une_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from con_unidad_negocio 
				where une_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['une_id']=$objeto->une_id;
		
		$_POST['une_nombre']=$objeto->une_nombre;
		$_POST['une_descripcion']=$objeto->une_descripcion;
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['une_nombre'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Descripcion";
			$valores[$num]["valor"]=$_POST['une_descripcion'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			
			$val=NEW VALIDADOR;
			
			$this->mensaje="";
			
			if($val->validar($valores)){
                            return true;
			}
				
			else{
                            $this->mensaje=$val->mensaje;
                            return false;
			}
		}
			return false;
		
	}
	
	function formulario_tcp($tipo)
	{
//		$conec= new ADO();
//		
//		$sql="select * from interno";
//		$conec->ejecutar($sql);		
//		$nume=$conec->get_num_registros();
//		$personas=0;
//		if($nume > 0)
//		{
//			$personas=1;
//		}
				
		switch ($tipo)
		{
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
		
		if(!($ver))
		{
			$url.="&tarea=".$_GET['tarea'];
		}
		
		if($cargar)
		{
			$url.='&id='.$_GET['id'];
		}
		$page="'gestor.php?mod=con_unidad_negocio&tarea=AGREGAR&acc=Emergente'";
		$extpage="'persona'";
		$features="'left=325,width=600,top=200,height=420,scrollbars=yes'";
		
		$this->formulario->dibujar_tarea('USUARIO');
		
		if($this->mensaje<>"")
		{
			$this->formulario->mensaje('Error',$this->mensaje);
		}
		
			?>
            <script>
            function reset_interno()
			{
				document.frm_con_unidad_negocio.une_int_id.value="";
				document.frm_con_unidad_negocio.une_nombre_persona.value="";
			}
            </script>
            
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_con_unidad_negocio" name="frm_con_unidad_negocio" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
                                                    <div id="ContenedorDiv">
                                                        <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                                                        <div id="CajaInput">
                                                            <input name="une_nombre" id="une_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['une_nombre'];?>" size="50">
                                                        </div>
                                                    </div>
                                                    <div id="ContenedorDiv">
                                                        <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                                                        <div id="CajaInput">
                                                            <textarea id="une_descripcion" cols="33" rows="2" name="une_descripcion"><?php echo $_POST['une_descripcion'];?></textarea>                                                            
                                                        </div>
                                                    </div>
						</div>
                        
							
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver))
								{
									?>
									<!--<input type="submit" class="boton" name="" value="Guardar">-->
                                                                        <input type="submit" class="boton" name="" value="Guardar" >
									<input type="reset" class="boton" name="" value="Cancelar">
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
									<?php
								}
								else
								{
									?>
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
									<?php
								}
								?>
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
        <?php if(!($ver||$cargar)){?>
        <script>
		var options1 = {
			script:"sueltos/persona.php?json=true&",
			varname:"input",
			minchars:1,
			timeout:10000,
			noresults:"No se encontro ninguna persona",
			json:true,
			callback: function (obj) {
				document.getElementById('une_int_id').value = obj.id;
			}
		};
		var as_json1 = new _bsn.AutoSuggest('une_nombre_persona', options1);
		</script>
        <?php } ?>
        <script type="text/javascript">
			$(document).ready(function(){
				$("a.group").fancybox({
					'hideOnContentClick': false,
					'overlayShow'	: true,
					'zoomOpacity'	: true,
					'zoomSpeedIn'	: 300,
					'zoomSpeedOut'	: 200,
					'overlayOpacity':0.5,
					
					'frameWidth'	:700,
					'frameHeight'	:350,
					'type'			:'iframe'
				});
				
				$('a.close').click(function(){
				 $(this).fancybox.close();
				});

			});
		</script>
		<?php
	}
	
	
	
	function insertar_tcp()
	{

                $conec= new ADO();		
                $sql="insert into con_unidad_negocio (une_nombre,une_descripcion,une_eliminado)
                    values ('".$_POST['une_nombre']."','".$_POST['une_descripcion']."','No')";
                //echo $sql.'<br>';
                $conec->ejecutar($sql);
                $mensaje='Banco Agregado Correctamente';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	

	
	function modificar_tcp()
	{
            $conec= new ADO();
            $sql="update con_unidad_negocio set 
                        une_nombre='".$_POST['une_nombre']."',
                        une_descripcion='".$_POST['une_descripcion']."'
                        where une_id='".$_GET['id']."'";
            //echo $sql;	
            $conec->ejecutar($sql);
            $mensaje='Banco Modificado Correctamente';		
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function formulario_confirmar_eliminacion(){
            $mensaje='Esta seguro de eliminar el Banco?';
            $this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'une_id');
	}
	
	function eliminar_tcp()
	{            
            $cantidad=  FUNCIONES::atributo_bd("con_comprobante", "cmp_une_id='".$_POST['une_id']."' and cmp_eliminado='No'", 'count(*)');
//            echo$cantidad."<br>";
            if($cantidad==0){
//                echo "elimino";
//                return;
                $conec= new ADO();		
                $sql="update con_unidad_negocio set une_eliminado='Si' where une_id='".$_POST['une_id']."'";			 
                $conec->ejecutar($sql);			                
                $mensaje='Banco Eliminado Correctamente.';
            }
            else
            {
                $mensaje='El Banco no puede ser eliminado, por que ya fue referenciado en algunos comprobantes.';
            }
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
        
        function total_comisionado($con_unidad_negocio){
            $sql = "select sum(com_monto)as total from comision where com_estado='Pendiente' and com_une_id='".$con_unidad_negocio."'";
            $conec = new ADO();
            $conec->ejecutar($sql);
            $num = $conec->get_num_registros();
            $total = 0;
            if($num > 0){
                if($conec->get_objeto()->total != NULL){
                    $total = $conec->get_objeto()->total;
                }
            }
            return $total;
        }
        
        function comision_pagada($con_unidad_negocio){
            //$sql = "select sum(com_monto)as total from comision where com_estado='Pagado' and com_une_id='".$con_unidad_negocio."'";
            $sql = "select sum(pve_monto)as total from pago_con_unidad_negocioes where pve_estado='Activo' and pve_une_id='".$con_unidad_negocio."'";
            $conec = new ADO();
            $conec->ejecutar($sql);
            $num = $conec->get_num_registros();
            $total = 0;
            if($num > 0){
                if($conec->get_objeto()->total != NULL){
                    $total = $conec->get_objeto()->total;
                }
            }
            return $total;
        }
        
        function formulario_pago_comision() {
                $url = $this->link . '?mod=' . $this->modulo;
                ?>

                <script>
                    function ValidarNumero(e) {
                        evt = e ? e : event;
                        tcl = (window.Event) ? evt.which : evt.keyCode;
                        if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                        {
                            return false;
                        }
                        return true;
                    }

                    function validar_campos_descuento() {
                        var comdes_motivo = document.getElementById('comdes_motivo').value;
                        var comdes_monto = document.getElementById('comdes_monto').value;
                        var total_comisionado = parseFloat(document.getElementById('total_comisionado').value);
                        var total_descuento = parseFloat(document.getElementById('total_descuento').value);

                        if (comdes_motivo === '' || comdes_monto === '') {
                            $.prompt('Existen datos en blanco para el descuento.');
                        } else {
                            if ((total_comisionado - total_descuento) >= parseFloat(comdes_monto)) {
                                location.href = 'gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&acc=descuentos&acc2=add&comdes_motivo=' + comdes_motivo + '&comdes_monto=' + comdes_monto + '&id=<?php echo $_GET['id']; ?>&com=<?php echo $_GET['com']; ?>&ven=<?php echo $_GET['ven']; ?>';
                            } else {
                                $.prompt('El monto a descontar no pueder ser mayor a: ' + (total_comisionado - total_descuento));
                            }
                        }
                    }
                    
                    function enviar_formulario_pago_comision(){
                        var comision_a_pagar = document.getElementById('comision_a_pagar').value;
                        var glosa = document.getElementById('pag_glosa').value;                        
                        var fecha = document.getElementById('pag_fecha').value;                        
                        if(comision_a_pagar === ''){                                                                                    
                            $.prompt('El monto de la comision a pagar no debe ser vacio.');
                            return false;
                        } 
                        if(glosa === ''){                            
                            $.prompt('La glosa de la comision a pagar no debe ser vacio.');
                            return false;
                        }                        
                        if(fecha!==''){
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response !== "ok") {
                                    $.prompt(dato.mensaje);                                    
                                    
                                }else{
                                    document.frm_con_unidad_negocio.submit();
                                }
                            });
                        }else{
                            $.prompt('La fecha del pago de la comision no debe ser vacio.');
                        }
                        
                        
                        
                    }
                </script>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <div id="Contenedor_NuevaSentencia">
                    <form id="frm_con_unidad_negocio" name="frm_con_unidad_negocio" action="<?php echo $url.'&tarea=PAGOS COMISIONES&acc=pagar&id='.$_GET['id']; ?>" method="POST" enctype="multipart/form-data">  
                        <div id="FormSent">

                            <div class="Subtitulo">Datos</div>
                            <div id="ContenedorSeleccion">
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Total Comisionado</div>
                                    <div id="CajaInput">
                                        <input name="total_comisionado" id="total_comisionado" readonly="readonly" type="text" class="caja_texto" value="<?php $total_comisionado = $this->total_comisionado($_GET['id']); echo number_format($total_comisionado,2,'.',','); ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                        <?php                                        
                                            echo '&nbsp;$us.';
                                        ?>
                                    </div>                                    

                                </div>
                                <!--Fin-->
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta"><span class="flechas1"></span>Comision Pagada</div>                                    
                                    <div id="CajaInput">                                        
                                        <input name="comision_pagada" id="comision_pagada" readonly="readonly" type="text" class="caja_texto" value="<?php $comision_pagada = $this->comision_pagada($_GET['id']); echo number_format($comision_pagada,2,'.',','); ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                    </div>
                                </div>
                                <!--Fin-->
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Saldo Comision</div>
                                    <div id="CajaInput">
                                        <input name="saldo_comision" id="saldo_comision" readonly="readonly" type="text" class="caja_texto" value="<?php echo number_format(($total_comisionado - $comision_pagada),2,'.',',');?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                        <?php
//                                        if ($obj->com_moneda == '1')
//                                            echo '&nbsp;Bs.';
//                                        else
                                            echo '&nbsp;$us.';
                                        ?>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Comision a Pagar</div>
                                    <div id="CajaInput">
                                        <input name="comision_a_pagar" id="comision_a_pagar" type="text" class="caja_texto" value="" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                        <?php
//                                        if ($obj->com_moneda == '1')
//                                            echo '&nbsp;Bs.';
//                                        else
                                            echo '&nbsp;$us.';
                                        ?>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                    <div id="CajaInput">
                                        <input  name="pag_glosa" type="text" id="pag_glosa" value="<?php echo $_POST['pag_glosa']; ?>" size="70"/>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                    <div id="CajaInput">
                                        <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha'])) echo $_POST['pag_fecha'];else echo date("d/m/Y"); ?>" type="text">
                                    </div>
                                </div>
                                <!--Fin-->

                                <!--Inicio-->
                                <div id="ContenedorDiv">

                                </div>
                                <!--Fin-->

                                <script>
                                    jQuery(function($) {
                                        $("#pag_fecha").mask("99/99/9999");
                                    });
                                </script>
                            </div>



                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>
                                        <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_pago_comision();">
                                        <input type="reset" class="boton" name="" value="Cancelar">
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=con_unidad_negocio&tarea=ACCEDER';">                                            
                                    </center>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
            }
            
        function ver_comprobante_pago_comision($pve_id){
            $sql = "select pve_une_id,pve_monto,pve_moneda,pve_fecha,pve_glosa,int_nombre,int_apellido 
                    from pago_con_unidad_negocioes 
                    inner join con_unidad_negocio on(une_id=pve_une_id)
                    inner join interno on(int_id=une_int_id)
                    where pve_id ='".$pve_id."'";
            $conec= new ADO();
            $conec->ejecutar($sql);
		
            $objeto=$conec->get_objeto();

            ////
            $pagina="'contenido_reporte'";

            $page="'about:blank'";

            $extpage="'reportes'";

            $features="'left=100,width=800,height=500,top=0,scrollbars=yes'";

            $extra1="'<html><head><title>Vista Previa</title><head>
                            <link href=css/estilos.css rel=stylesheet type=text/css />
                      </head>
                      <body>
                      <div id=imprimir>
                      <div id=status>
                      <p>";
            $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
                              <a href=javascript:self.close();>Cerrar</a></td>
                              </p>
                              </div>
                              </div>
                              <center>'";
            $extra2="'</center></body></html>'"; 


                    echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                              c.document.write('.$extra1.');
                              var dato = document.getElementById('.$pagina.').innerHTML;
                              c.document.write(dato);
                              c.document.write('.$extra2.'); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=con_unidad_negocio&tarea=ACCEDER\';"></td></tr></table>
                            ';
            $conversor = new convertir();
            ?>
                
                <br><br><div id="contenido_reporte" style="clear:both">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="30%" >
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
					</td>
				    <td  width="40%" ><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO DE COMISION<center></h3></strong></p></td>
				    <td  width="30%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>con_unidad_negocio: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br/><br/>
					
	
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Fecha</th>
					<th>Concepto</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $conversor->get_fecha_latina($objeto->pve_fecha);?></td>
					<td><?php echo $objeto->pve_glosa;?></td>
					<td><?php echo $objeto->pve_monto;?></td>
					<td><?php if($objeto->pve_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody>
				</table>
				
				<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
				
				</center>
				<br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
				</div>
                
                <?php
        }    
        
        function listar_pagos_comisiones() {
                ?>
                <script>
                    function anular_pago_comision(id) {
                        var txt = '¿Está seguro de querer anular el pago de la comisión?';

                        $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {

                                if (v) {
                                    location.href = 'gestor.php?mod=con_unidad_negocio&tarea=PAGOS COMISIONES&acc=anular&pag_id=' + id;
                                }

                            }
                        });
                    }
                    
                    function ver_comprobante_pago_comision(id){
                        location.href = 'gestor.php?mod=con_unidad_negocio&tarea=PAGOS COMISIONES&acc=ver&pag_id=' + id;
                    }
                </script>
                <?php
                $sql = "select * from pago_con_unidad_negocioes where pve_une_id='" . $_GET['id'] . "'";
                $conec = new ADO();
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $conv = new convertir();
                ?>
                <br><br><center><h2>HISTORIAL DE PAGOS</h2><table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>                                
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th class="tOpciones">Opciones</th> 
                            </tr>	
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 0; $i < $num; $i++) {
                                $obj = $conec->get_objeto();
                                echo '<tr>';

                                echo '<td>';
                                echo $conv->get_fecha_latina($obj->pve_fecha);
                                echo '&nbsp;</td>';

                                echo '<td style="text-align:right">';
                                echo $obj->pve_monto;
                                echo '&nbsp;</td>';

                                echo '<td>';
                                echo $obj->pve_usu_id;
                                echo '&nbsp;</td>';

                                echo '<td>';
                                echo $obj->pve_estado;
                                echo '&nbsp;</td>';                                

                                echo '<td>';
                                ?>
                            <center>
                                <table>
                                    <tr>
                                        <td><a class="linkOpciones" href="javascript:ver_comprobante_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                    <img src="images/ver.png" border="0" title="VER COMPROBANTE" alt="ver">
                                                </a>
                                        </td>
                                        <?php
                                        if ($obj->pve_estado != 'Anulado') {
                                            ?>
                                            <td><a class="linkOpciones" href="javascript:anular_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                    <img src="images/anular.png" border="0" title="ANULAR PAGO COMISION" alt="anular">
                                                </a>
                                            </td>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                </table>
                            </center>

                            <?php
                            echo '</td>';

                            echo '</tr>';

                            $conec->siguiente();
                        }
                        ?>

                        </tbody>
                    </table>
                    <?php
                }
        
        function pagos_comisiones(){
            if($_GET['acc'] <> ""){
                if($_GET['acc'] == 'pagar'){
                    $this->pagar_monto_comision($_GET['id']);
                }
                if($_GET['acc'] == 'anular'){
                    $this->anular_monto_comision($_GET['pag_id']);
                }
                if($_GET['acc'] == 'ver'){
                    $this->ver_comprobante_pago_comision($_GET['pag_id']);
                }
            }else{
                $this->formulario_pago_comision();
                $this->listar_pagos_comisiones();
            }
        }
        
        function pagar_monto_comision($une_id){
            
            $conec = new ADO();
            $sql_caja = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "'";
            $conec->ejecutar($sql_caja);
            $num = $conec->get_num_registros();
            
            if($num > 0){
                $caja = $conec->get_objeto()->cja_cue_id;
                $sql = "select int_nombre,int_apellido,int_id,une_can_id from interno inner join con_unidad_negocio on (une_int_id=int_id) where une_id='".$une_id."'";                        
                $conec->ejecutar($sql);            
                $obj = $conec->get_objeto();
                $interesado = $obj->int_nombre . " " .$obj->int_apellido;
                $ca_con_unidad_negocio=$obj->une_can_id;
                $moneda = '2';                
                $monto = $_POST['comision_a_pagar'];
                $glosa =$_POST['pag_glosa'] ;//'Pago de Comision - con_unidad_negocio: ' . $interesado . " - Monto: " . $monto;
                $pag_fecha=  FUNCIONES::get_fecha_mysql($_POST['pag_fecha']);
                
                $sql_insert = "insert into pago_con_unidad_negocioes(pve_fecha,pve_hora,
                                                            pve_usu_id,pve_une_id,
                                                            pve_monto,pve_moneda,
                                                            pve_estado,pve_glosa)
                                                    values('".date('Y-m-d')."','".date('H:i')."','".
                                                            $this->usu->get_id()."','".$une_id."','".
                                                            $monto."','".$moneda."','Activo','".$glosa."')";
                $conec2 = new ADO();
                $conec2->ejecutar($sql_insert,false);
                $llave = mysql_insert_id();
                
                ///generar comprobante..
                //$ca_con_unidad_negocio=  FUNCIONES::atributo_bd("con_unidad_negocio", "une_id=".$une_id, "une_can_id");
                include_once 'clases/registrar_comprobantes.class.php';
                $ges_id=$_SESSION['ges_id'];
                $comprobante = new stdClass();
                $comprobante->tipo = "Egreso";
                $comprobante->mon_id = 2;
                $comprobante->nro_documento = "PLANILLA";
                $comprobante->fecha = $pag_fecha;
                $comprobante->ges_id = $_SESSION['ges_id'];
                $comprobante->peri_id = FUNCIONES::obtener_periodo($pag_fecha);
                $comprobante->forma_pago="Efectivo";
                $comprobante->une_id=0;
                $comprobante->une_char='';
                $comprobante->une_nro='';
                $comprobante->glosa = $glosa;
                $comprobante->referido = $interesado;
                $comprobante->tabla = "pago_con_unidad_negocioes";
                $comprobante->tabla_id = $llave;
                
                $comprobante->detalles=array(
                            array("ca"=>$ca_con_unidad_negocio,"cc"=>"","cf"=>"","cuen"=>FUNCIONES::get_cuenta($ges_id,'5.1.1.01.005'),
                                "glosa"=>$glosa,"debe"=>$monto,"haber"=>0
                                ),
                            array("ca"=>"","cc"=>"","cf"=>"","cuen"=>FUNCIONES::get_cuenta($ges_id,'1.1.1.01.001'),
                                "glosa"=>$glosa,"debe"=>0,"haber"=>$monto
                                ),

                            array("ca"=>$ca_con_unidad_negocio,"cc"=>"","cf"=>"","cuen"=>FUNCIONES::get_cuenta($ges_id,'2.1.1.02.002'),
                                "glosa"=>$glosa,"debe"=>$monto,"haber"=>0
                                ),
                            array("ca"=>$ca_con_unidad_negocio,"cc"=>"","cf"=>"","cuen"=>FUNCIONES::get_cuenta($ges_id,'2.1.1.02.003'),
                                "glosa"=>$glosa,"debe"=>0,"haber"=>$monto
                                )

                                                
                            );                

                COMPROBANTES::registrar_comprobante($comprobante);
                $this->ver_comprobante_pago_comision($llave);
                
            }else{
                $mensaje = 'No puedes realizar ningun cobro, por que no estas registrado como cajero.';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=ACCEDER");   
            }            
        }
        
        function obtener_datos_para_pago_comision(&$cco){
            $sql = "select par_pagocomisiones_cc from ad_parametro";
            $conec = new ADO();
            $conec->ejecutar($sql);
            $cco = $conec->get_objeto()->par_pagocomisiones_cc;
        }
        
        function anular_monto_comision($pag_id){
            $pago=  FUNCIONES::objeto_bd_sql("select * from pago_con_unidad_negocioes where pve_id='$pag_id'");
            $fecha = $pago->pve_fecha;
            $ges_id = $_SESSION['ges_id'];
            $condicion = "pdo_ges_id='$ges_id' and '$fecha'>=pdo_fecha_inicio and '$fecha'<=pdo_fecha_fin and pdo_eliminado='No' and pdo_estado='Abierto'";
            $periodo = FUNCIONES::objetos_bd("con_periodo", $condicion);
            $fecha_lat=  FUNCIONES::get_fecha_latina($pago->pve_fecha);
            if ($periodo->get_num_registros() == 0) {
                $mensaje="La comision no puede ser Anulada por que el periodo de la fecha  $fecha_lat, ya fue cerrado.";
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
                return;
            }
//            echo "anulando el pago de comision: " . $pag_id;
            //$sql = "update comprobante set cmp_estado=2 where cmp_tabla='pago_con_unidad_negocioes' and cmp_tabla_id='".$pag_id."'";
            $conec = new ADO();
//            $conec->ejecutar($sql);
            $sql2 = "update pago_con_unidad_negocioes set pve_estado='Anulado' where pve_id='".$pag_id."'";
            $conec->ejecutar($sql2);
            include_once 'clases/registrar_comprobantes.class.php';
            COMPROBANTES::anular_comprobante('pago_con_unidad_negocioes', $pag_id);
            $mensaje='Pago de comision anulado Correctamente';
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo . "&tarea=ACCEDER");
            
            
            
        }
	
	function cuentas()
	{
		$this->formulario->dibujar_tarea();
		
		if($_GET['acc']=="comision")
		{
			$this->pagar_comision();
		}
		else
		{
			if($_GET['acc']=="frm_cambiar")
			{
				$this->frm_cambiar();
			}
			else
			{
				if($_GET['acc']=="cambiar")
				{
					$this->cambiar();
					$this->listado_cuentas();
				}
				else
				{
					if($_GET['acc']=="imprimir")
					{
						$this->imprimir_pago($_GET['id']);
					}
					else
					{
						$this->listado_cuentas();
					}	
				}	
			}	
		}	
			
	}
	
	function cambiar()
	{
		$conec= new ADO();
		
		$sql="update comision set com_une_id='".$_POST['con_unidad_negocio']."' where com_id='".$_POST['comision']."'";
			
		$conec->ejecutar($sql);		
		
		$sql="select com_ven_id from comision where com_id='".$_POST['comision']."'";
			
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
		
		$venta=$objeto->com_ven_id;
		
		$sql="update venta set ven_une_id='".$_POST['con_unidad_negocio']."' where ven_id='".$venta."'";
			
		$conec->ejecutar($sql);	
		
		$this->formulario->mensaje('Correcto','La comisión fue asignada a otro con_unidad_negocio.');
	}
	
	function frm_cambiar()
	{
		?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_con_unidad_negocio" name="frm_con_unidad_negocio" action="gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&id=<?php echo $_GET['id']; ?>&acc=cambiar" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Seleccione el con_unidad_negocio al que asignara la comisión</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >con_unidad_negocio</div>
								<div id="CajaInput">
									<select style="width:200px;" name="con_unidad_negocio" class="caja_texto">
										   <?php 		
											$fun=NEW FUNCIONES;		
											$fun->combo("select une_id as id,concat(int_nombre,' ',int_apellido) as nombre from con_unidad_negocio inner join interno on (une_int_id=int_id) where une_estado='Habilitado'",$_POST['con_unidad_negocio']);				
											?>
								   </select>
								   <input name="comision" type="hidden" value="<?php echo $_GET['com']; ?>">
							   </div>
							</div>
							<!--Fin-->
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
							
									<input type="submit" class="boton" name="" value="Cambiar">
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&id=<?php echo $_GET['id']; ?>';">
								
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
		<?php
	}
	
	function listado_cuentas()
	{
		?>
		<script>		
		function pagar_comision(id){
				var txt = 'Estas seguro que realizaras el pago de la comisión?';
				
				$.prompt(txt,{ 
					buttons:{Pagar:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&acc=comision&id='+id;
						}
												
					}
				});
			}
			
		function cambiar_comision(id){
			var txt = 'Estas seguro que cambiara la comisión a otro con_unidad_negocio?';
			
			$.prompt(txt,{ 
				buttons:{Cambiar:true, Cancelar:false},
				callback: function(v,m,f){
					
					if(v){
							location.href='gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&acc=frm_cambiar&id=<?php echo $_GET['id']; ?>&com='+id;
					}
											
				}
			});
		}	

		</script>
        <?php
		$conec= new ADO();
		
        $sql="select * from con_unidad_negocio
		inner join interno on (int_id=une_int_id) where une_id=".$_GET['id'];
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();

		$objeto=$conec->get_objeto();
		?>
        <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
        	<tr>
        		<td width="40%" ></td>
        		<td  width="20%" ><p align="center" ><strong><h3><center></center></h3></strong></p></td>
        		<td  width="40%" ><div align="right"></div></td>
      		</tr>
       		<tr>
        		<td colspan="2">
                	<br />
        			<strong>con_unidad_negocio: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?>
        		</td>
        		<td align="right">
        		</td>
      		</tr> 
        </table>
		<br><br><center><h2>COMISIONES PENDIENTES</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
                                    <th>Venta Nro</th>
                                    <th>Tipo Venta</th>
                                    <th>Monto Venta</th>
                                    <th>Fecha</th>
                                    <th>Urbanización</th>
                                    <th>Manzano</th>					
                                    <th>Lote</th>
                                    <th>Monto Bs</th>
                                    <th>Monto $us</th>
                                    <!--<th class="tOpciones" width="70px">Opciones</th>-->
				</tr>	
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		
		$sql="SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha,ven_tipo,ven_monto 
		FROM 
		comision inner join venta on (com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id) 
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join con_unidad_negocio on (com_une_id=une_id)
		inner join interno on (une_int_id=int_id)
		where
		com_estado='Pendiente' and com_une_id='".$_GET['id']."' and ven_estado!='Anulado'
		order by ven_id asc";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		$totalbs=0;
		$totalsus=0;
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			echo '<tr>';
									
					echo "<td>";
						echo $objeto->com_ven_id;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->ven_tipo;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->ven_monto;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->ven_fecha);
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->urb_nombre;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->man_nro;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->lot_nro;
					echo "&nbsp;</td>";
					if($objeto->com_moneda=='1')
					{	
						$bs=$objeto->com_monto;
						$sus=round($objeto->com_monto/$this->tc,2);
						
						$totalbs=$totalbs+$bs;
						$totalsus=$totalsus+$sus;
					}
					else
					{
						$sus=$objeto->com_monto;
						$bs=round($objeto->com_monto*$this->tc,2);
						$totalbs=$totalbs+$bs;
						$totalsus=$totalsus+$sus;
					}
					
					echo "<td>";
						//echo $objeto->com_monto;
						echo $bs;
					echo "&nbsp;</td>";
						//$tot+=$objeto->com_monto;
						
					echo "<td>";
						//if($objeto->com_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
						echo $sus;
					echo "&nbsp;</td>";
					
//					echo "<td>&nbsp;";
//						
//						?>
<!--						<center>
						<table>
							<tr>
								<td><a class="linkOpciones" href="javascript:pagar_comision('<?php // echo $objeto->com_id;?>');">
										<img src="images/pagar.png" border="0" title="PAGAR COMISION" alt="pagar">
									</a>
								</td>
								<td>
								<a class="linkOpciones" href="javascript:cambiar_comision('<?php // echo $objeto->com_id;?>');">
									<img src="images/cambiar.png" border="0" title="CAMBIAR COMISION" alt="Cambiar">
								</a>
								</td>
							</tr>
						</table>
						
						</center>-->
						<?php
//						
//					echo "</td>";
				echo "</tr>";
			
			$conec->siguiente();
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
                <td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>					
				<td>&nbsp;</td>
				<td><?php echo $totalbs.' Bs'; ?></td>
				<td><?php echo $totalsus.' $us'; ?></td>
				<!--<td>&nbsp;</td>-->
			</tr>	
		</tfoot>
		</table></center>
		
		<br><br><center><h2>COMISIONES PAGADAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Venta Nro</th>
                    <th>Tipo Venta</th>
                    <th>Monto Venta</th>
					<th>Urbanización</th>
					<th>Manzano</th>					
					<th>Lote</th>
					<th>Monto</th>
					<th>Moneda</th>
					<!--<th class="tOpciones" width="70px">Opciones</th>-->
				</tr>	
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		
		$sql="SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_tipo,ven_monto 
		FROM 
		comision inner join venta on (com_estado='Pagado' and com_une_id='".$_GET['id']."' and com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id) 
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join con_unidad_negocio on (com_une_id=une_id)
		inner join interno on (une_int_id=int_id)";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		
		$tot=0;
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			echo '<tr>';
									
					echo "<td>";
						echo $objeto->com_ven_id;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->ven_tipo;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->ven_monto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->urb_nombre;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->man_nro;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->lot_nro;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->com_monto;
					echo "&nbsp;</td>";
					$tot+=$objeto->com_monto;
					echo "<td>";
						if($objeto->com_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
					echo "&nbsp;</td>";
				
//					echo "<td>&nbsp;";
						
						?>
<!--						<center>
						<table>
							<tr>
								<td><a class="linkOpciones" href="gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&acc=imprimir&id=<?php // echo $objeto->com_id; ?>">
										<img src="images/imprimir.png" border="0" title="Imprimir" alt="Imprimir">
									</a>
								</td>
							</tr>
						</table>
						
						</center>-->
						<?php
						
//					echo "</td>";
				
				echo "</tr>";
			
			$conec->siguiente();
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
                <td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>					
				<td>&nbsp;</td>
				<td><?php echo $tot; ?></td>
				<td>&nbsp;</td>
				<!--<td>&nbsp;</td>-->
			</tr>	
		</tfoot>
		</table></center>
		<?php
	}
	
	function pagar_comision()
	{
		
		$conec= new ADO();
		
		$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";
			
		$conec->ejecutar($sql);		

		$nume=$conec->get_num_registros();

		if($nume > 0)
		{
			$obj = $conec->get_objeto();
			
			$caja=$obj->cja_cue_id;
			
			$sql="update comision set 
							com_estado='Pagado',
							com_fecha_pag='".date('Y-m-d')."'
							where com_id = '".$_GET['id']."'";
		
			
			$conec->ejecutar($sql);
			
			/**REFLEJO EN LAS CUENTAS**///
			/*
			$sql="SELECT com_ven_id,com_monto,com_moneda,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,une_cco_id,une_cue_id,une_int_id 
			FROM 
			comision inner join venta on (com_id='".$_GET['id']."' and com_ven_id=ven_id)
			inner join lote on (ven_lot_id=lot_id) 
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
			inner join con_unidad_negocio on (com_une_id=une_id)
			inner join interno on (une_int_id=int_id)		
			";
			
			$conec->ejecutar($sql);
			
			$objeto=$conec->get_objeto();
			
			include_once("clases/registrar_comprobantes.class.php");
			 
			$comp = new COMPROBANTES();	
			
			$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$this->tc,$objeto->com_moneda,'',$objeto->une_int_id,$this->usu->get_id(),'2','1','comision',$_GET['id']);			   
			
			//if($objeto->com_moneda=='1')
				$mde=$objeto->com_monto;
			//else
				//$mde=$objeto->com_monto*$this->tc;
			
			$cco=$this->obtener_cco_urbanizacion($objeto->com_ven_id);
			
			if($objeto->com_moneda=='1')
			{
				
				$comp->ingresar_detalle($cmp_id,$mde*(-1),$caja,0);
			
				$comp->ingresar_detalle($cmp_id,$mde,$objeto->une_cue_id,$cco,"Comisión por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro");
				
			}
			else
			{
				
				$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mde*(-1));
			
				$comp->ingresar_detalle($cmp_id,0,$objeto->une_cue_id,$cco,"Comisión por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro",$mde);
			
			}
			*/
			///**REFLEJO EN LAS CUENTAS**///
			
			$this->imprimir_pago($_GET['id']);
			
		}
		else
		{
			$mensaje='No puedes realizar ninguna cobro, por que no estas registrado como cajero.';
			
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		}
	}
	
	function imprimir_pago($id)
	{		
		$conec= new ADO();
		
		$sql="SELECT com_une_id,com_monto,com_moneda,com_fecha_pag,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,une_cco_id,une_cue_id,une_int_id 
		FROM 
		comision inner join venta on (com_id='".$id."' and com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id) 
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join con_unidad_negocio on (com_une_id=une_id)
		inner join interno on (une_int_id=int_id)		
		";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		////
		$pagina="'contenido_reporte'";
		
		$page="'about:blank'";
		
		$extpage="'reportes'";
		
		$features="'left=100,width=800,height=500,top=0,scrollbars=yes'";
		
		$extra1="'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
		$extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
		$extra2="'</center></body></html>'"; 
		
		
			echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=con_unidad_negocio&tarea=CUENTAS&id='.$objeto->com_une_id.'\';"></td></tr></table>
				';
		$conversor = new convertir();
		
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="30%" >
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
					</td>
				    <td  width="40%" ><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO DE COMISIÓN<center></h3></strong></p></td>
				    <td  width="30%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>con_unidad_negocio: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br/><br/>
					
	
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Fecha</th>
					<th>Concepto</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $conversor->get_fecha_latina($objeto->com_fecha_pag);?></td>
					<td><?php echo "Comisión por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro";?></td>
					<td><?php echo $objeto->com_monto;?></td>
					<td><?php if($objeto->com_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody>
				</table>
				
				<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
				
				</center>
				<br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
				</div>
				
		<?php		
		
	}
	
	function obtener_cco_urbanizacion($id_venta)
	{
		$conec= new ADO();
		
		$sql="SELECT urb_id,urb_cco_id from urbanizacion
			inner join uv on (uv_urb_id=urb_id)
			inner join lote on (lot_uv_id=uv_id)
			inner join venta on (ven_lot_id=lot_id)
			inner join centrocosto on (cco_id=urb_cco_id)
			where ven_id=$id_venta";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->urb_cco_id;
	}
}
?>