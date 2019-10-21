<?php
class ter_intercambio extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function ter_intercambio()
	{		//permisos
		$this->ele_id=174;		
		$this->busqueda();		
		if(!($this->verificar_permisos('AGREGAR'))){
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="ban_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;
		
		$this->arreglo_campos[1]["nombre"]="ban_descripcion";
		$this->arreglo_campos[1]["texto"]="Descripcion";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=25;
		
		$this->link='gestor.php';
		
		$this->modulo='ter_intercambio';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('MOTIVO INTERCAMBIO');
		
		$this->usu=new USUARIO;
	}
	
	function dibujar_busqueda()
	{
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
			$nun++;
		}
	}
	
	function dibujar_listado()
	{
		$sql="select  
				*
			  from 
				ter_intercambio 
                            where inter_eliminado='No' ";
		$this->set_sql($sql,'');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
        function dibujar_encabezado(){
        ?>
            <tr>
                <th>Nombre</th>		
                <th>Descripci&oacute;n</th>
                <th>Tipo</th>
                <th>Cuenta $us</th>
                <th>Cuenta Bs</th>
                <th class="tOpciones" width="100px">Opciones</th>
            </tr>
        <?php
	}
	
	function mostrar_busqueda(){
            $conversor = new convertir();
            for($i=0;$i<$this->numero;$i++){
                $objeto=$this->coneccion->get_objeto();
                echo '<tr>';
                        echo "<td>";
                                echo $objeto->inter_nombre;
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->inter_descripcion;
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->inter_tipo;
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->inter_cuenta_debe_usd?FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='$objeto->inter_cuenta_debe_usd'"):'';
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->inter_cuenta_debe_bs?FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='$objeto->inter_cuenta_debe_bs'"):'';
                                echo "&nbsp;";
                        echo "</td>";
//					echo "<td>";
//                                                echo $objeto->inter_cuenta_haber?FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='$objeto->inter_cuenta_haber'"):'';
//						echo "&nbsp;";
//					echo "</td>";
//					echo "<td>";
//                                                echo $objeto->inter_cuenta_baja?FUNCIONES::atributo_bd_sql("select cue_descripcion as campo from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo='$objeto->inter_cuenta_baja'"):'';
//						echo "&nbsp;";
//					echo "</td>";
                        echo "<td>";
                                echo $this->get_opciones($objeto->inter_id);
                        echo "</td>";
                echo "</tr>";
                $this->coneccion->siguiente();
            }
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from ter_intercambio
				where inter_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['inter_id']=$objeto->inter_id;
		
		$_POST['inter_nombre']=$objeto->inter_nombre;
		$_POST['inter_descripcion']=$objeto->inter_descripcion;
		$_POST['inter_tipo']=$objeto->inter_tipo;
		$_POST['inter_cuenta_debe_usd']=$objeto->inter_cuenta_debe_usd;
		$_POST['inter_cuenta_debe_bs']=$objeto->inter_cuenta_debe_bs;
		$_POST['inter_cuenta_haber']=$objeto->inter_cuenta_haber;
		$_POST['inter_cuenta_baja']=$objeto->inter_cuenta_baja;
		
	}
	
	function datos(){
            if($_POST){
//                texto,  numero,  real,  fecha,  mail.
                $num=0;
                $valores[$num]["etiqueta"]="Nombre";
                $valores[$num]["valor"]=$_POST['inter_nombre'];
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
		$this->formulario->dibujar_tarea('USUARIO');		
		if($this->mensaje<>""){
                    $this->formulario->mensaje('Error',$this->mensaje);
		}
                
//                $no_existe="No existe una cuenta con el mismo codigo en esta gesti&oacute;n";
                $no_existe="NO EXISTE UNA CUENTA CON EL MISMO CODIGO EN ESTA GESTI&Oacute;N";
                
                if($_POST['inter_cuenta_debe_usd']!='')
                    $txt_inter_cuenta_debe_usd=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[inter_cuenta_debe_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
                if($_POST['inter_cuenta_debe_bs']!='')
                    $txt_inter_cuenta_debe_bs=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[inter_cuenta_debe_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
                
                if($_POST['inter_cuenta_haber']!='')
                    $txt_inter_cuenta_haber=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[inter_cuenta_haber]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
                if($_POST['inter_cuenta_baja']!='')
                    $txt_inter_cuenta_baja=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[inter_cuenta_baja]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
            ?>
                <style>
                    .txt_rojo{
                        color: #ff0000;
                    }
                </style>
                <script src="js/util.js"></script>
                <!--AutoSuggest-->
                <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
                <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
                <style>
                    .Etiqueta{width: 170px;}
                    .cgreen{color: #059101}
                    .corange{color: #e44b00}
                </style>
                <div id="Contenedor_NuevaSentencia">
                    <form id="formulario" name="formulario" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
                            <div id="FormSent">
                                <div class="Subtitulo">Datos</div>
                                <div id="ContenedorSeleccion">
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                                        <div id="CajaInput">
                                            <input name="inter_nombre" id="inter_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['inter_nombre'];?>" size="50">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                                        <div id="CajaInput">
                                            <textarea id="inter_descripcion" cols="33" rows="2" name="inter_descripcion"><?php echo $_POST['inter_descripcion'];?></textarea>                                                            
                                        </div>
                                    </div>
                                    
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Tipo</div>
                                        <div id="CajaInput">
                                            <select id="inter_tipo" name="inter_tipo" style="width: 150px;" <?php echo $_GET[id]?'disabled="true"':'';?>>
                                                <option value="Servicio" <?php echo $_POST[inter_tipo]=='Servicio'?'selected="true"':'';?>>Servicio</option>
                                                <option value="Bienes" <?php echo $_POST[inter_tipo]=='Bienes'?'selected="true"':'';?>>Bienes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv" class="cta_debe">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta <b class="cgreen">($us.)</b></div>
                                        <div id="CajaInput">
                                            <input type="text" class="caja_texto" name="inter_cuenta_debe_usd" id="inter_cuenta_debe_usd" size="25" value="<?php echo $_POST['inter_cuenta_debe_usd']; ?>" >
                                            <input type="text" class="caja_texto <?php echo $txt_inter_cuenta_debe_usd==''?'txt_rojo':'';?>" name="txt_inter_cuenta_debe_usd" id="txt_inter_cuenta_debe_usd" size="55" value="<?php echo $txt_inter_cuenta_debe_usd==''?$no_existe:$txt_inter_cuenta_debe_usd;?>" readonly="">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv" class="cta_debe">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta <b class="corange">(Bs.)</b></div>
                                        <div id="CajaInput">
                                            <input type="text" class="caja_texto" name="inter_cuenta_debe_bs" id="inter_cuenta_debe_bs" size="25" value="<?php echo $_POST['inter_cuenta_debe_bs']; ?>" >
                                            <input type="text" class="caja_texto <?php echo $txt_inter_cuenta_debe_bs==''?'txt_rojo':'';?>" name="txt_inter_cuenta_debe_bs" id="txt_inter_cuenta_debe_bs" size="55" value="<?php echo $txt_inter_cuenta_debe_bs==''?$no_existe:$txt_inter_cuenta_debe_bs;?>" readonly="">
                                        </div>
                                    </div>
                                    
                                    <div id="ContenedorDiv" class="cta_haber" hidden="">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta Haber</div>
                                        <div id="CajaInput">
                                            <input type="text" class="caja_texto" name="inter_cuenta_haber" id="inter_cuenta_haber" size="25" value="<?php echo $_POST['inter_cuenta_haber']; ?>" >
                                            <input type="text" class="caja_texto <?php echo $txt_inter_cuenta_haber==''?'txt_rojo':'';?>" name="txt_inter_cuenta_haber" id="txt_inter_cuenta_haber" size="55" value="<?php echo $txt_inter_cuenta_haber==''?$no_existe:$txt_inter_cuenta_haber;?>" readonly="">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv" class="cta_baja" hidden="">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta Baja</div>
                                        <div id="CajaInput">
                                            <input type="text" class="caja_texto" name="inter_cuenta_baja" id="inter_cuenta_baja" size="25" value="<?php echo $_POST['inter_cuenta_baja']; ?>" >
                                            <input type="text" class="caja_texto <?php echo $txt_inter_cuenta_baja==''?'txt_rojo':'';?>" name="txt_inter_cuenta_baja" id="txt_inter_cuenta_baja" size="55" value="<?php echo $txt_inter_cuenta_baja==''?$no_existe:$txt_inter_cuenta_baja;?>" readonly="">
                                        </div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                   <div id="CajaBotones">
                                        <center>
                                            <?php
                                            if(!($ver)){
                                                    ?>
                                                    <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                                    <input type="button" class="boton" name="" value="Guardar" id="btn_guardar">
                                                    <input type="reset" class="boton" name="" value="Cancelar">
                                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
                                                    <?php
                                            }else{
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
                    <script>
                        function autosuggest(inputHidden, inputVisible,tipo){
                            var strtipo='';
                            if(tipo!==undefined){
                                strtipo='&cu_tipo='+tipo;
                            }
                            var options = {
                                script: "AjaxRequest.php?peticion=listCuenta&limit=10&tipo=cuenta"+strtipo+"&",
                                //                    script: "test.php?peticion=listCuenta&json=true&limit=6&tipo="+tipocuenta+"&",
                                varname: "input",
                                json: true,
                                shownoresults: false,
                                maxresults: 10,
                                callback: function(obj) {
                                    $("#"+inputVisible).val(obj.info);
                                    $("#"+inputVisible).attr("data-cod",obj.info);
                                    var value=$('<div>').html(obj.value).text();
                                    $("#txt_"+inputVisible).val(value);
                                    $("#txt_"+inputVisible).removeClass('txt_rojo');
                                    $(".msCorrecto").hide();
                                    $("#"+inputVisible).next('input').next('input').focus();
                                    var name=$("#"+inputVisible).parent().parent().next().find('input').eq(0).attr('name');
                                    $("#"+inputVisible).parent().parent().next().find('input').eq(0).select();
                                    console.log(name);
                                }
                            };
                            var as_json = new bsn.AutoSuggest(inputVisible, options);
                        }                        
                        autosuggest("","inter_cuenta_debe_usd");
                        autosuggest("","inter_cuenta_debe_bs");
                        autosuggest("","inter_cuenta_haber");
                        autosuggest("","inter_cuenta_baja");
                        
                        $('#inter_tipo').change(function(){
                            var tipo=$(this).val();
                            
                            if(tipo==='Servicio'){
                                
                                $('.cta_baja').hide();
                                $('.cta_debe').show();
                                $('.cta_haber').hide();
                            }else if(tipo==='Bienes'){
                                $('.cta_baja').hide();
                                $('.cta_debe').show();
                                $('.cta_haber').hide();
                            }
                        });
                        $('#inter_tipo').trigger('change');
                        $('#btn_guardar').click(function(){
                            var tipo=$('#inter_tipo option:selected').val();
                            var debe=$('#inter_cuenta_debe').val();
                            if(debe===''){
                                $.prompt('Ingrese una cuenta Debe.');
                                return false;
                            }
//                            if(tipo==='bienes'){
//                                var debe=$('#inter_cuenta_debe').val();
//                                var haber=$('#inter_cuenta_haber').val();
//                                if(debe===''){
//                                    $.prompt('Ingrese una cuenta Debe.');
//                                    return false;
//                                }
//                                if(haber===''){
//                                    $.prompt('Ingrese una cuenta Haber.');
//                                    return false;
//                                }
//                            }
//                            var baja=$('#inter_cuenta_baja').val();
//                            if(baja===''){
//                                $.prompt('Ingrese una cuenta Baja.');
//                                return false;
//                            }
                            document.formulario.submit();
                        });
                    </script>
            </div>
        
		<?php
	}
	
	function insertar_tcp(){
//            echo '<pre>';
//            print_r($_POST);
//            echo '</pre>';
//            return;
                $conec= new ADO();		
                $sql="insert into ter_intercambio (inter_nombre,inter_descripcion,inter_tipo,inter_cuenta_debe_usd,inter_cuenta_debe_bs,inter_cuenta_haber,inter_cuenta_baja,inter_eliminado)
                    values ('$_POST[inter_nombre]','$_POST[inter_descripcion]','$_POST[inter_tipo]','$_POST[inter_cuenta_debe_usd]','$_POST[inter_cuenta_debe_bs]','$_POST[inter_cuenta_haber]','$_POST[inter_cuenta_baja]','No')";
//                echo $sql.'<br>';
                $conec->ejecutar($sql);
                $mensaje='Intercambio Agregado Correctamente';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	

	
	function modificar_tcp()
	{
            $conec= new ADO();
            $sql="update ter_intercambio set 
                        inter_nombre='".$_POST['inter_nombre']."',
                        inter_descripcion='".$_POST['inter_descripcion']."',
                        inter_cuenta_debe_usd='".$_POST['inter_cuenta_debe_usd']."',
                        inter_cuenta_debe_bs='".$_POST['inter_cuenta_debe_bs']."',
                        inter_cuenta_haber='".$_POST['inter_cuenta_haber']."',
                        inter_cuenta_baja='".$_POST['inter_cuenta_baja']."'
                        where inter_id='".$_GET['id']."'";
//            echo $sql;	
            $conec->ejecutar($sql);
            $mensaje='Intercambio Modificado Correctamente';
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function formulario_confirmar_eliminacion(){
            $mensaje='Esta seguro de eliminar el El tipo de Intercambio?';
            $this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'ban_id');
	}
	
	function eliminar_tcp(){
            $cantidad=  FUNCIONES::atributo_bd_sql("select count(*) as campo from venta_intercambio where vint_inter_id='$_GET[id]'");
            if($cantidad==0){
                $conec= new ADO();		
                $sql="update ter_intercambio set inter_eliminado='Si' where inter_id='".$_POST['ban_id']."'";
                $conec->ejecutar($sql);
                $mensaje='Intercambio Eliminado Correctamente.';
            }else{
                $mensaje='El Motivo Intercambio no puede ser eliminado, por que ya fue referenciado en algunas ventas.';
            }
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
        
        

        

        

	

	

	


}
?>