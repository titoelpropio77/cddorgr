<?php
class VENDEDOR_GRUPO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function VENDEDOR_GRUPO()
	{		//permisos
		$this->ele_id=180;		
		$this->busqueda();		
		if(!($this->verificar_permisos('AGREGAR'))){
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="int_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;
		
		$this->arreglo_campos[1]["nombre"]="int_apellido";
		$this->arreglo_campos[1]["texto"]="Apellido";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=25;
		
		$this->link='gestor.php';
		
		$this->modulo='vendedor_grupo';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('TIPO VENDEDOR');
		
		$this->usu=new USUARIO;
	}
	
	function dibujar_busqueda(){
        ?>
            <script>

                function ejecutar_script(id,tarea){
                    if(tarea==='ELIMINAR'){
                        
                        var txt = 'Esta seguro de Eliminar el Tipo de Vendedor?';

                        $.prompt(txt,{ 
                            buttons:{Eliminar:true, Cancelar:false},
                            callback: function(v,m,f){
                                if(v){
                                    location.href='gestor.php?mod=vendedor_grupo&tarea='+tarea+'&id='+id;
                                }
                            }
                        });
                    }
                }
            </script>
        <?php
		$this->formulario->dibujar_cabecera();
		
		$this->dibujar_listado();
	}
	
	function set_opciones()
	{
		$nun=0;
		
		if($this->verificar_permisos('VER'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='VER';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
			$this->arreglo_opciones[$nun]["nombre"]='VER';
			$nun++;
		}
		
		if($this->verificar_permisos('MODIFICAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='MODIFICAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_edit.png';
			$this->arreglo_opciones[$nun]["nombre"]='MODIFICAR';
			$nun++;
		}
		
		if($this->verificar_permisos('ELIMINAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
			$this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
                        $this->arreglo_opciones[$nun]["script"] = "ok";
			$nun++;
		}
		
		if($this->verificar_permisos('CUENTAS'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='CUENTAS';
			$this->arreglo_opciones[$nun]["imagen"]='images/listado.png';
			$this->arreglo_opciones[$nun]["nombre"]='HISTORIAL';
			$nun++;
		}
                
                if($this->verificar_permisos('PAGOS COMISIONES'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='PAGOS COMISIONES';
			$this->arreglo_opciones[$nun]["imagen"]='images/cuenta.png';
			$this->arreglo_opciones[$nun]["nombre"]='PAGOS COMISIONES';
			$nun++;
		}
	}
	
	function dibujar_listado()
	{
		$sql="SELECT 
				*
			  FROM 
				vendedor_grupo where vgru_eliminado='No'";
		$this->set_sql($sql,'');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado(){
		?>
                    <tr>
	        	<th>Id</th>
	        	<th>Nombre</th>
                        <th>Comision</th>
                        <th>Contado</th>
                        <th>Credito</th>
                        <th>Credito 3 meses</th>
                        <th class="tOpciones" width="100px">Opciones</th>
			</tr>
		<?PHP
	}
	
	function mostrar_busqueda()
	{
		$conversor = new convertir();
		
		for($i=0;$i<$this->numero;$i++){
				$objeto=$this->coneccion->get_objeto();
                                $txt_valor_unidad=$objeto->vgru_comision=='Porcentaje'?'%':($objeto->vgru_comision=='Metro'?'$us x Mt2':'');
				echo '<tr>';
									
					echo "<td>";
						echo $objeto->vgru_id;
						echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						echo $objeto->vgru_nombre;
						echo "&nbsp;";
					echo "</td>";
					
					echo "<td>";
                                            echo $objeto->vgru_comision;
                                            echo "&nbsp;";
					echo "</td>";
					
					echo "<td>";
						echo "$objeto->vgru_contado $txt_valor_unidad";
						echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						echo "$objeto->vgru_credito $txt_valor_unidad";
						echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						echo "$objeto->vgru_credito3m $txt_valor_unidad";
						echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->vgru_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from vendedor_grupo 
				where vgru_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['vgru_id']=$objeto->vgru_id;
		$_POST['vgru_nombre']=$objeto->vgru_nombre;
                $_POST['vgru_descripcion']=$objeto->vgru_descripcion;
                $_POST['vgru_comision']=$objeto->vgru_comision;
                $_POST['vgru_contado']=$objeto->vgru_contado;
                $_POST['vgru_credito']=$objeto->vgru_credito;
                $_POST['vgru_credito3m']=$objeto->vgru_credito3m;
                $_POST['vgru_estado']=$objeto->vgru_estado;
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['vgru_nombre'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['vgru_comision'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['vgru_contado'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['vgru_credito'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['vgru_credito3m'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Estado";
			$valores[$num]["valor"]=$_POST['vgru_estado'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			
			$val=NEW VALIDADOR;
			
			$this->mensaje="";
			
			if($val->validar($valores))
			{
				return true;
			}
				
			else
			{
				$this->mensaje=$val->mensaje;
				return false;
			}
		}
			return false;
		
	}
	
	function formulario_tcp($tipo){
		$conec= new ADO();
				
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
		
		$this->formulario->dibujar_tarea('USUARIO');
		
		if($this->mensaje<>""){
                    $this->formulario->mensaje('Error',$this->mensaje);
		}
		
			?>
            <script>
            function reset_interno()
			{
				document.frm_vendedor_grupo.vdo_int_id.value="";
				document.frm_vendedor_grupo.vdo_nombre_persona.value="";
			}
            </script>
            <!--AutoSuggest-->
            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
            <!--AutoSuggest-->
            <!--FancyBox-->
            <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="js/util.js"></script>
            <!--FancyBox-->
            <script>
                function validar_vendedor_grupo(frm){
                    var nombre = $('#vgru_nombre').val(); 
                    var comision=$('#vgru_comision option:selected').val();
                    var contado=$('#vgru_contado').val();
                    var credito=$('#vgru_credito').val();
                    var credito3m=$('#vgru_credito3m').val();

//                     if (nombre !== '' && comision !== '' && contado*1>0 && credito*1>0 && credito3m*1>0 ){
                     if (nombre !== '' && comision !== ''  ){
                         document.frm_vendedor_grupo.submit();
                     }
                    else{
                         $.prompt('Debes Completar los campos con (*).<br> Debes agregar por lo menos 1 Comision por Urbanizacion.',{ opacity: 0.8 });
                    }
                 }
            </script>
            
            <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor_grupo" name="frm_vendedor_grupo" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                            <div class="Subtitulo">Datos</div>
                            <div id="ContenedorSeleccion">
                                <!--Inicio-->
                                    <div id="ContenedorDiv">
                                       <div class="Etiqueta" ><span class="flechas1">*</span>Nombre</div>
                                       <div id="CajaInput">
                                           <input type="text" class="caja_texto" name="vgru_nombre" id="vgru_nombre" value="<?php echo $_POST[vgru_nombre];?>">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion:</div>
                                        <div id="CajaInput">
                                            <textarea class="caja_texto" name="vgru_descripcion" id="vgru_descripcion" ><?php echo $_POST[vgru_descripcion];?></textarea>
                                        </div>							   							   								
                                    </div>
                                    <div id="ContenedorDiv">
                                       <div class="Etiqueta" ><span class="flechas1">* </span>Comision por:</div>
                                       <div id="CajaInput">
                                            <select name="vgru_comision" id="vgru_comision" class="caja_texto">
                                                <option value="" >Seleccione</option>
                                                <option value="Porcentaje" <?php if($_POST['vgru_comision']=='Porcentaje') echo 'selected="selected"'; ?>>Porcentaje</option>
                                                <option value="Metro" <?php if($_POST['vgru_comision']=='Metro') echo 'selected="selected"'; ?>>Metro</option>
                                            </select>
                                       </div>
                                    </div>
                                    <div id="ContenedorDiv" >
                                       <div class="Etiqueta" ><span class="flechas1">* </span>Valor Contado</div>
                                       <div id="CajaInput">
                                           <input type="text" id="vgru_contado" name="vgru_contado" value="<?php echo $_POST[vgru_contado];?>">
                                           <span class="txt_valor_unidad"></span>
                                       </div>
                                    </div>
                                    <div id="ContenedorDiv" >
                                       <div class="Etiqueta" ><span class="flechas1">* </span>Valor Credito</div>
                                       <div id="CajaInput">
                                           <input type="text" id="vgru_credito" name="vgru_credito" value="<?php echo $_POST[vgru_credito];?>">
                                           <span class="txt_valor_unidad"></span>
                                       </div>
                                    </div>
                                    <div id="ContenedorDiv" >
                                       <div class="Etiqueta" ><span class="flechas1">* </span>Valor Credito 3 Meses</div>
                                       <div id="CajaInput">
                                           <input type="text" id="vgru_credito3m" name="vgru_credito3m" value="<?php echo $_POST[vgru_credito3m];?>">
                                           <span class="txt_valor_unidad"></span>
                                       </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                       <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                                       <div id="CajaInput">
                                            <select name="vgru_estado" class="caja_texto">
                                                <option value="Habilitado" <?php if($_POST['vgru_estado']=='Habilitado') echo 'selected="selected"'; ?>>Habilitado</option>
                                                <option value="Deshabilitado" <?php if($_POST['vgru_estado']=='Deshabilitado') echo 'selected="selected"'; ?>>Deshabilitado</option>
                                            </select>
                                       </div>
                                    </div>

                            </div>
                            <script>
                                $('#vgru_comision').change(function(){
                                    var comision=$(this).val();
                                    if(comision==='Porcentaje'){
                                        $('.txt_valor_unidad').text('%');
                                    }else if(comision==='Metro'){
                                        $('.txt_valor_unidad').text('$us x Mt2');
                                    }else{
                                        $('.txt_valor_unidad').text('');
                                    }
                                });

                                mask_decimal('#vgru_contado, #vgru_credito, #vgru_credito3m',null);
                                $('#vgru_comision').trigger('change');
                            </script>

                        <div id="ContenedorDiv">
                           <div id="CajaBotones">
                                <center>
                                <?php
                                if(!($ver)){
                                    ?>
                                    <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                    <input type="buttom" class="boton" name="" value="Guardar" onclick="javascript:validar_vendedor_grupo(this);">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
                                    <?php
                                }else{
                                    ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
                                    <?php
                                }?>
                                </center>
                           </div>
                        </div>
                    </div>
            </form>
            </div>
        <?php if(!($ver||$cargar)){?>
        
        <?php } ?>
        
		<?php
	}
	
	function insertar_tcp(){
            $conec= new ADO();			
            $sql="insert into vendedor_grupo (
                            vgru_nombre,vgru_descripcion,vgru_comision,vgru_contado,
                            vgru_credito,vgru_credito3m,vgru_estado,vgru_eliminado
                ) values (
                    '$_POST[vgru_nombre]','$_POST[vgru_descripcion]','$_POST[vgru_comision]','$_POST[vgru_contado]',
                    '$_POST[vgru_credito]','$_POST[vgru_credito3m]','$_POST[vgru_estado]','No'
                )";
            $conec->ejecutar($sql,false);
            $mensaje='Vendedor Agregado Correctamente';
            
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function cargar_comision_vendedor_grupo($id_vendedor_grupo){
            $conec= new ADO();

            $sql="select * from vendedor_grupo_comision
            inner join urbanizacion on (urb_id=vdocom_urb_id) where vdocom_vdo_id='".$id_vendedor_grupo."'";

            $conec->ejecutar($sql);
            $num=$conec->get_num_registros();
            $cad="";

            for($i=0;$i<$num;$i++)
            {
                $objeto=$conec->get_objeto();

                $tipo_comision_muestra='';
                if($objeto->vdocom_tipo_comi=='metro_cuadrado')
                        $tipo_comision_muestra='Por M2';
                else
                        if($objeto->vdocom_tipo_comi=='porcentaje')
                                $tipo_comision_muestra='Por Porcentaje';
                        else
                                if($objeto->vdocom_tipo_comi=='monto_especifico')
                                        $tipo_comision_muestra='Monto Fijo';
				
				$moneda_muestra='';
				if($objeto->vdocom_moneda==1)
					$moneda_muestra='Bolivianos';
				if($objeto->vdocom_moneda==2)
					$moneda_muestra='Dolares';
				if($objeto->vdocom_moneda=='')
					$moneda_muestra='S/Moneda';
                ?>
                    <tr>
                        <td><input name="comu[]" type="hidden" value="<?php echo $objeto->vdocom_urb_id; ?>"><input name="comu_urbanizacion[]" type="hidden" value="<?php echo $objeto->vdocom_urb_id; ?>"><?php echo $objeto->urb_nombre; ?></td>
                        <td><input name="comu_tipo_comision[]" type="hidden" value="<?php echo $objeto->vdocom_tipo_comi; ?>"><?php echo $tipo_comision_muestra; ?></td>
                        <td><input name="comu_contado[]" type="hidden" value="<?php echo $objeto->vdocom_contado; ?>"><?php echo $objeto->vdocom_contado; ?></td>
                        <td><input name="comu_credito[]" type="hidden" value="<?php echo $objeto->vdocom_credito; ?>"><?php echo $objeto->vdocom_credito; ?></td>
                        <td><input name="comu_moneda[]" type="hidden" value=""><?php echo $moneda_muestra; ?></td>
                        <td><center><img style="float:none;" src="images/b_drop.png" id="img-del-comisiones" ></center></td></tr>
                <?php

                $conec->siguiente();
            }
            ?>
            <script>
            document.frm_vendedor_grupo.nfilas.value=<?php echo $num; ?>;
            document.frm_vendedor_grupo.nfilasshadown.value=<?php echo $num; ?>;		
            </script>
            <?php
	}
	
	function modificar_tcp()
	{
            $conec= new ADO();
            $sql="update vendedor_grupo set 
                        vgru_nombre='$_POST[vgru_nombre]',
                        vgru_descripcion='$_POST[vgru_descripcion]',
                        vgru_comision='$_POST[vgru_comision]',
                        vgru_contado='$_POST[vgru_contado]',
                        vgru_credito='$_POST[vgru_credito]',
                        vgru_credito3m='$_POST[vgru_credito3m]',
                        vgru_estado='$_POST[vgru_estado]'
                        where vgru_id='".$_GET['id']."'";
//            echo $sql;	
            $conec->ejecutar($sql);

            $mensaje='Vendedor Modificado Correctamente';		
			
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	
	function formulario_confirmar_eliminacion()
	{
            $mensaje='Esta seguro de eliminar el Tipo de Vendedor?';
            $this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'vgru_id');
	}
	
	function eliminar_tcp(){
            $verificar=NEW VERIFICAR;
            $parametros[0]=array('vdo_vgru_id');
            $parametros[1]=array($_POST['vgru_id']);
            $parametros[2]=array('vendedor');
            if($verificar->validar($parametros)){
                $conec= new ADO();		
                $sql="update vendedor_grupo set vgru_eliminado='Si' where vgru_id='".$_GET['id']."'";			 
                $conec->ejecutar($sql);			
                $mensaje='Vendedor Eliminado Correctamente.';
            }else{
                $mensaje='El vendedor_grupo no puede ser eliminado, por que ya realizo algunas ventas.';
            }
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
}
?>