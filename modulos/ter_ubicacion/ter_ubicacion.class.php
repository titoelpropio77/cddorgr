<?php
class ter_ubicacion extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function ter_ubicacion()
	{		//permisos
		$this->ele_id=183;		
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
		
		$this->modulo='ter_ubicacion';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('UBICACION');
		
		$this->usu=new USUARIO;
	}
	
        function main() {
            $this->formulario->dibujar_cabecera();
            ?>
            <style>
                .error{
                    color: #ff0000;
                    margin-left: 5px;
                }            
                .tab_lista_cuentas{
                    list-style: none;
                    width: 100%;                                    
                    overflow:scroll ;
                    background-color: #ededed;
                    border-collapse: collapse;  
                    font-size: 12px;
                }
                .tab_lista_cuentas tr td{
                    padding: 3px 3px;
                }
                .tab_lista_cuentas tr:hover{
                    background-color: #f9e48c;
                }                                
                .img_del_cuenta,.img_edit_cuenta{                                    
                    font-weight: bold;
                    cursor: pointer;
                    width: 14px;
                }
                .box_lista_cuenta{
                    width:95%;height:170px;background-color:#F2F2F2;overflow:auto;
                    border: 1px solid #8ec2ea;
                }
                .txt_rojo{
                    color: #ff0000;
                }
                .Subtitulo:hover{
                    cursor: pointer;
                }
                .tit_config{
                    font-size: 12px;
                    font-weight: bold;
                    color: #3a3a3a;
                    margin-top:5px;                
                    padding:5px 10px 5px 10px;
                    background:#cdd0d5;        
                    cursor: pointer;        
                }
                .tit_config:hover{               
                    background:#bdc1c6;        

                }
                .box-ubicacion-col{ float: left; width: 33%;text-align: left}
                .tr_select{ background-color: #0072ff !important; color: #fff; }
                .tr_select a{color: #fff !important;}
                .tab_lista_cuentas a{color: #000;}
/*                class="tr_select"*/
            </style>
            <div class="box-ubicacion">
                <input type="hidden" id="pais_id" name="pais_id" value="<?php echo $_GET[pais_id];?>">
                <input type="hidden" id="est_id" name="est_id" value="<?php echo $_GET[est_id];?>">
                <div class="box-ubicacion-col">
                    <div style="text-align: center; font-size: 16px; font-weight: bold;margin-top: 10px;">PAISES</div>
                    <input id="btn_add_pais" type="button" class="boton" value="Agregar Pais" style=" margin: 5px 0;"><br>
                    <div class="box_lista_cuenta"> 
                        <table id="tab_cuentas_cap" class="tab_lista_cuentas">
                            <?php $paises=  FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'");?>
                            <?php $pais_id=$_GET[pais_id];?>
                            <?php foreach ($paises as $pais) {?>
                                <tr data-id="<?php echo $pais->pais_id;?>" class="<?php echo $pais_id==$pais->pais_id?'tr_select':'';?>">
                                    <td class=""><a href="<?php echo $pais_id!=$pais->pais_id?"gestor.php?mod=ter_ubicacion&tarea=ACCEDER&pais_id=$pais->pais_id":'#';?>"><?php echo $pais->pais_nombre;?></a></td>
                                    <!--<td width="5%"><a><img class="img_del_cuenta" src="images/retener.png"></a></td>-->
                                    <td width="5%"><a href="gestor.php?mod=ter_ubicacion&tarea=MODIFICAR&acc=mod_pais&pais_id=<?php echo $pais->pais_id;?>"><img class="img_edit_cuenta" src="images/b_edit.png"></a></td>
                                </tr>
                            <?php }?>
                        </table>
                    </div>
                </div>
                <div class="box-ubicacion-col">
                    <div style="text-align: center; font-size: 16px; font-weight: bold;margin-top: 10px;">ESTADO</div>
                    <?php if ($pais_id>0){?>
                        <input id="btn_add_estado" type="button" class="boton" value="Agregar Estado" style=" margin: 5px 0;" >
                    <?php }else{?>
                        <input type="button" class="boton" value="" style=" visibility: hidden;margin: 5px 0;" disabled="true">
                    <?php } ?>
                    <div class="box_lista_cuenta">
                        <table id="tab_cuentas_cap" class="tab_lista_cuentas">
                            <?php 
                            $estados=array();
                            if($pais_id>0){
                                $estados=  FUNCIONES::lista_bd_sql("select * from ter_estado where est_eliminado='No' and est_pais_id=$pais_id");
                            }
                            $est_id=$_GET[est_id];
                            ?>
                            <?php foreach ($estados as $est) {?>
                                <tr data-id="<?php echo $est->est_id;?>" class="<?php echo $est_id==$est->est_id?'tr_select':'';?>">
                                    <td class=""><a href="<?php echo $est_id!=$est->est_id?"gestor.php?mod=ter_ubicacion&tarea=ACCEDER&pais_id=$pais_id&est_id=$est->est_id":'#';?>"><?php echo $est->est_nombre;?></a></td>
                                    <!--<td width="5%"><img class="img_del_cuenta" src="images/retener.png"></td>-->
                                    <td width="5%"><a href="gestor.php?mod=ter_ubicacion&tarea=MODIFICAR&acc=mod_estado&pais_id=<?php echo $pais_id;?>&est_id=<?php echo $est->est_id;?>"><img class="img_edit_cuenta" src="images/b_edit.png"></a></td>
                                </tr>
                            <?php }?>
                        </table>                                    
                    </div>
                </div>
                <div class="box-ubicacion-col">
                    <div style="text-align: center; font-size: 16px; font-weight: bold;margin-top: 10px;">LUGAR</div>
                    <?php if ($pais_id>0 && $est_id>0){?>
                        <input id="btn_add_lugar" type="button" class="boton" value="Agregar Lugar" style=" margin: 5px 0;"><br>
                    <?php }else{?>
                        <input type="button" class="boton" value="" style=" visibility: hidden;margin: 5px 0;"><br>
                    <?php } ?>
                    <div class="box_lista_cuenta"> 
                        <table id="tab_cuentas_cap" class="tab_lista_cuentas">
                            <?php 
                            $lugares=array();
                            if($est_id>0){
                                $lugares=  FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_eliminado='No' and lug_est_id='$est_id'");
                            }
                            ?>
                            <?php ?>
                            <?php foreach ($lugares as $lug) {?>
                                <tr data-id="<?php echo $lug->lug_id;?>">
                                    <td class=""><?php echo $lug->lug_nombre;?></td>
                                    <!--<td width="5%"><img class="img_del_cuenta" src="images/retener.png"></td>-->
                                    <td width="5%"><a href="gestor.php?mod=ter_ubicacion&tarea=MODIFICAR&acc=mod_lugar&pais_id=<?php echo $pais_id;?>&est_id=<?php echo $est_id;?>&lug_id=<?php echo $lug->lug_id?>"><img class="img_edit_cuenta" src="images/b_edit.png"></a></td>
                                </tr>
                            <?php }?>
                        </table>
                    </div>
                </div>

            </div>
            
            <script>
                $('#btn_add_pais').click(function(){
                    location.href='gestor.php?mod=ter_ubicacion&tarea=AGREGAR&acc=add_pais';
                });
                $('#btn_add_estado').click(function(){
                    console.log('estado');
                    location.href='gestor.php?mod=ter_ubicacion&tarea=AGREGAR&acc=add_estado&pais_id=<?php echo $pais_id;?>';
                });
                $('#btn_add_lugar').click(function(){
                    console.log('lugar');
                    location.href='gestor.php?mod=ter_ubicacion&tarea=AGREGAR&acc=add_lugar&pais_id=<?php echo $pais_id;?>&est_id=<?php echo $est_id;?>';
                });
            </script>
                
            <?php
        }
        
        function modificar(){
            $acc="$_GET[acc]";
            if($acc=='mod_lugar'){
                if($_POST){
                    $this->modificar_ubicacion();
                }else{
                    $lugar=  FUNCIONES::objeto_bd_sql("select * from ter_lugar where lug_id=$_GET[lug_id]");
                    $data=array(
                        'label'=>'LUGAR',
                        'tipo'=>'lugar',
                        'acc'=>'mod',
                        'nombre'=>$lugar->lug_nombre
                    );
                    $this->frm_ubicacion($data);
                }
            }elseif($acc=='mod_estado'){
                if($_POST){
                    $this->modificar_ubicacion();
                }else{
                    $estado=  FUNCIONES::objeto_bd_sql("select * from ter_estado where est_id=$_GET[est_id]");
                    $data=array(
                        'label'=>'ESTADO',
                        'tipo'=>'estado',
                        'acc'=>'mod',
                        'nombre'=>$estado->est_nombre
                    );
                    $this->frm_ubicacion($data);
                }
            }else{
                if($_POST){
                    $this->modificar_ubicacion();
                }else{
                    $pais=  FUNCIONES::objeto_bd_sql("select * from ter_pais where pais_id=$_GET[pais_id]");
                    $data=array(
                        'label'=>'PAIS',
                        'tipo'=>'pais',
                        'acc'=>'mod',
                        'nombre'=>$pais->pais_nombre
                    );
                    $this->frm_ubicacion($data);
                }
            }
        }
        function modificar_ubicacion(){
            $pais_id=$_GET[pais_id];
            $est_id=$_GET[est_id];
            $lug_id=$_GET[est_id];
            if($_POST[tipo]=='pais'){
                $sql_ins="update ter_pais set pais_nombre='$_POST[nombre]' where pais_id=$pais_id";
            }elseif($_POST[tipo]=='estado'){
                $sql_ins="update ter_estado set est_nombre='$_POST[nombre]' where est_id=$est_id";
            }elseif($_POST[tipo]=='lugar'){
                $sql_ins="update ter_lugar set lug_nombre='$_POST[nombre]' where lug_id=$lug_id";
            }
            $conec=new ADO();
            $conec->ejecutar($sql_ins);
            
            $url="gestor.php?mod=ter_ubicacion&tarea=ACCEDER&acc=add_pais";

            if($pais_id>0){
                $url.="&pais_id=$pais_id";
            }
            if($est_id>0){
                $url.="&est_id=$est_id";
            }
            $this->formulario->dibujar_titulo("MODIFICAR REGISTRO");
//            $this->main();
            ?>
            <div class="ancho100"><div class="msCorrecto limpiar">Registro Modificado exitosamente</div></div>
            <input type="button" class="boton" value="Ver Listado" onclick="location.href='<?php echo $url?>'">
            <?php
        }
        function agregar(){
            $acc="$_GET[acc]";
            if($acc=='add_lugar'){
                if($_POST){
                    $this->guardar_ubicacion();
                }else{
                    $data=array(
                        'label'=>'LUGAR',
                        'tipo'=>'lugar',
                        'acc'=>'add',
                        'nombre'=>''
                    );
                    $this->frm_ubicacion($data);
                }
            }elseif($acc=='add_estado'){
                if($_POST){
                    $this->guardar_ubicacion();
                }else{
                    $data=array(
                        'label'=>'ESTADO',
                        'tipo'=>'estado',
                        'acc'=>'add',
                        'nombre'=>''
                    );
                    $this->frm_ubicacion($data);
                }
            }else{
                if($_POST){
                    $this->guardar_ubicacion();
                }else{
                    $data=array(
                        'label'=>'PAIS',
                        'tipo'=>'pais',
                        'acc'=>'add',
                        'nombre'=>''
                    );
                    $this->frm_ubicacion($data);
                }
            }
        }
        
        function guardar_ubicacion() {
            $pais_id=$_GET[pais_id];
            $est_id=$_GET[est_id];
            if($_POST[tipo]=='pais'){
                $sql_ins="insert into ter_pais(pais_nombre,pais_eliminado)values('$_POST[nombre]','No')";
            }elseif($_POST[tipo]=='estado'){
                $sql_ins="insert into ter_estado(est_nombre,est_pais_id,est_eliminado)values('$_POST[nombre]','$pais_id','No')";
            }elseif($_POST[tipo]=='lugar'){
                $sql_ins="insert into ter_lugar(lug_nombre,lug_est_id,lug_eliminado)values('$_POST[nombre]','$est_id','No')";
            }
            $conec=new ADO();
            $conec->ejecutar($sql_ins);
            
            $url="gestor.php?mod=ter_ubicacion&tarea=ACCEDER&acc=add_pais";

            if($pais_id>0){
                $url.="&pais_id=$pais_id";
            }
            if($est_id>0){
                $url.="&est_id=$est_id";
            }
            
//            $this->main();
            $this->formulario->dibujar_titulo("AGREGAR REGISTRO");
            ?>
            <div class="ancho100"><div class="msCorrecto limpiar">Registro guardado exitosamente</div></div>
            <input type="button" class="boton" value="Ver Listado" onclick="location.href='<?php echo $url?>'">
            <?php
        }
        
        function frm_ubicacion($data){
            
            $data=(Object)$data;
            $this->formulario->dibujar_titulo("AGREGAR $data->label");
            $tar=$data->acc=='mod'?'MODIFICAR':'AGREGAR';
            $url="gestor.php?mod=ter_ubicacion&tarea=$tar&acc={$data->acc}_pais";
            $pais_id=$_GET[pais_id];
            $est_id=$_GET[est_id];
            if($pais_id>0){
                $url.="&pais_id=$pais_id";
            }
            if($est_id>0){
                $url.="&est_id=$est_id";
            }
            
            ?>
            <div id="Contenedor_NuevaSentencia">
                <form id="formulario" name="formulario" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                    <input type="hidden" id="pais_id" name="pais_id" value="<?php echo $_GET[pais_id];?>">
                    <input type="hidden" id="est_id" name="est_id" value="<?php echo $_GET[est_id];?>">
                    <input type="hidden" id="lug_id" name="lug_id" value="<?php echo $_GET[lug_id];?>">
                    <input type="hidden" id="tipo" name="tipo" value="<?php echo $data->tipo;?>">
                    <div id="FormSent" style="width: 100%">
                        <div id="ContenedorSeleccion">
                            <?php if ($_GET[pais_id]>0 && $data->acc!='mod'){?>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>PAIS</div>
                                <div id="CajaInput">
                                    <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select pais_nombre as campo from ter_pais where pais_id='$_GET[pais_id]'")?></div>
                                        
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($_GET[est_id]>0 && $data->acc!='mod'){?>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>ESTADO</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select est_nombre as campo from ter_estado where est_id='$_GET[est_id]'")?></div>
                                    </div>
                                </div>
                            <?php }?>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span><?php echo $data->label;?></div>
                                <div id="CajaInput">
                                    <input type="text" id="nombre" name="nombre" value="<?php echo $data->nombre;?>" size="45">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                             <center>
                                <input type="button" class="boton" name="" value="Guardar" id="btn_guardar">
                                <input type="button" class="boton" name="" value="Volver" onclick="volver();">
                             </center>
                        </div>
                     </div>
                </form>
            </div>
            <script>
                function volver(){
                    var pais_id=$('#pais_id').val()*1;
                    var est_id=$('#est_id').val()*1;
                    var and_filtro='';
                    if(pais_id>0){
                        and_filtro+='&'+pais_id;
                    }
                    if(est_id>0){
                        and_filtro+='&'+est_id;
                    }
                    location.href='gestor.php?mod=ter_ubicacion&tarea=ACCEDER'+and_filtro;
                }
                $('#btn_guardar').click(function (){
                    if($('#nombre').val()===''){
                        return false;
                    }
                    document.formulario.submit();
                });
            </script>
            <?php
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
				ter_ubicacion 
                            where ubi_eliminado='No' ";
		$this->set_sql($sql,'order by ubi_pais asc, ubi_estado asc, ubi_lugar asc');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
        function dibujar_encabezado(){
        ?>
            <tr>
                <th>Pais</th>		
                <th>Estado</th>
                <th>Lugar</th>
                
                <th class="tOpciones" width="100px">Opciones</th>
            </tr>
        <?php
	}
	
	function mostrar_busqueda(){
            
            for($i=0;$i<$this->numero;$i++){
                $objeto=$this->coneccion->get_objeto();
                echo '<tr>';
                        echo "<td>";
                                echo $objeto->ubi_pais;
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->ubi_estado;
                                echo "&nbsp;";
                        echo "</td>";
                        echo "<td>";
                                echo $objeto->ubi_lugar;
                                echo "&nbsp;";
                        echo "</td>";

                        echo "<td>";
                                echo $this->get_opciones($objeto->ubi_id);
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
                        <div id="FormSent" style="width: 100%">
                                
                                <div id="ContenedorSeleccion">
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Pais</div>
                                        <div id="CajaInput">
                                            <?php $paises=  FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'");?>
                                            <div style="float: left;">
                                                <select name="pais_id" id="pais_id" style="width: 250px;">
                                                    <?php foreach ($paises as $pais) {?>
                                                        <option value="<?php echo $pais->pais_id;?>"><?php echo $pais->pais_nombre;?></option>
                                                    <?php }?>
                                                </select>
                                                <input name="txt_ubi_pais" id="txt_ubi_pais"  type="text" class="caja_texto" value="" size="50">
                                            </div>
                                            <input type="hidden" name="new_pais" id="new_pais" value="1">
                                            <img id="btn_new_pais" src="images/add-reg.png" width="20px" style="cursor: pointer; float: left">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                                        <div id="CajaInput">
                                            <?php $estados=  FUNCIONES::lista_bd_sql("select * from ter_estados where est_eliminado='No'");?>
                                            <div style="float: left;">
                                                <select name="pais_id" id="pais_id" style="width: 250px;">
                                                    <?php foreach ($paises as $pais) {?>
                                                        <option value="<?php echo $pais->pais_id;?>"><?php echo $pais->pais_nombre;?></option>
                                                    <?php }?>
                                                </select>
                                                <input name="ubi_estado" id="ubi_estado"  type="text" class="caja_texto" value="<?php echo $_POST['ubi_estado'];?>" size="50">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Lugar</div>
                                        <div id="CajaInput">
                                            <input name="ubi_lugar" id="ubi_lugar"  type="text" class="caja_texto" value="<?php echo $_POST['ubi_lugar'];?>" size="50">
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                <div id="ContenedorDiv">
                                   <div id="CajaBotones">
                                        <center>
                                            <?php
                                            if(!($ver)){
                                                    ?>
                                                    
                                                    <input type="button" class="boton" name="" value="Guardar" id="btn_guardar">
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
                        $('#btn_new_pais').click(function (){
                            if($('#new_pais').val()==='1'){
                                $('#txt_ubi_pais').hide();
                                $('#pais_id').show();
                                $('#new_pais').val('0');
                            }else{
                                $('#txt_ubi_pais').show();
                                $('#pais_id').hide();
                                $('#new_pais').val('1');
                            }
                        });
                        $('#btn_new_pais').trigger('click');
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