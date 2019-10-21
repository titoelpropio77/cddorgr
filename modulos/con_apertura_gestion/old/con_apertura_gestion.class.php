<?php
class con_apertura_gestion extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function con_apertura_gestion()
	{		//permisos
		$this->ele_id=211;		
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
		
		$this->modulo='con_apertura_gestion';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('Vendedor');
		
		$this->usu=new USUARIO;
	}
	
	function main(){
            $acc=$_GET[acc];
            if($acc=='generar'){

                $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='apertura_gestion' and cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]' and cmp_eliminado='No'");
                if(!$cmp){
                    if($_POST[form]=='ok'){
                        $this->insertar_apertura();
                    }else{
                        if($_POST){
                            $this->generar_vista($_POST[gestion]);
                        }else{
                            $this->frm_generar();
                        }
                    }
                }else{
                    $this->formulario->ventana_volver('La gestion actual ya tiene un Comprobante de Apertura generado', "gestor.php?mod=$this->modulo",'','Info');
                }
            }elseif($acc=='ver'){
                require_once('modulos/con_comprobante/con_comprobante.class.php');
                $con_comprobante = new con_comprobante();
                $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='apertura_gestion' and cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]' and cmp_eliminado='No'");
                $con_comprobante->imprimir_cmp($cmp->cmp_id);
            }elseif($acc=='actualizar'){
                $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='apertura_gestion' and cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]' and cmp_eliminado='No'");
                if($_POST[form]=='ok'){
                    echo "actualizar";
                    $this->actualizar_apertura($cmp);
                }else{
                    $_POST[fecha]=  FUNCIONES::get_fecha_latina($cmp->cmp_fecha);
                    $this->generar_vista($cmp->cmp_tabla_id);


                }
            }else{
                $this->ver_opciones();
            }
	}

        function actualizar_apertura($cmp) {
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";
            $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
            $af=  explode('-', $fecha);
            $ges_id=$_SESSION[ges_id];
            $glosa="Apertura de la Gestion $af[0]";
            
            $listado=  $this->listado_saldos($cmp->cmp_tabla_id);
            include_once 'clases/registrar_comprobantes.class.php';
            $comprobante = new stdClass();

            $comprobante->cmp_id = $cmp->cmp_id;
            $comprobante->tipo = "Diario";
            $comprobante->mon_id = 1;
            $comprobante->nro_documento = date("Ydm");
            $comprobante->fecha = $fecha;
            $comprobante->ges_id = $ges_id;
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha);
            $comprobante->forma_pago = 'Efectivo';
            $comprobante->ban_id = '';
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = '';
            $comprobante->tabla = "apertura_gestion";
            $comprobante->tabla_id = $cmp->cmp_tabla_id;
            $comprobante->apertura = '1';
            $sum_debe=0;
            $sum_haber=0;
            $aprobado=1;
            $msj='';
            foreach ($listado as $fila) {
                if($fila->aprobado){
                    if($fila->debe>0 || $fila->haber>0){
                        $can_id=0;
                        if($fila->can_codigo){
                            $can_id=  FUNCIONES::get_cuenta_ca($ges_id, $fila->can_codigo);
                        }
                        if($fila->cfl_codigo){
                            $cfl_id=  FUNCIONES::get_cuenta_ca($ges_id, $fila->cfl_codigo);
                        }
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $fila->codigo),
                            "debe" => $fila->debe, "haber" => $fila->haber,"glosa" => $glosa, "ca" => $can_id, 
                            "cf" => $cfl_id, "cc" => 0,'int_id'=>$fila->int_id
                        );
                        $sum_debe+=$fila->debe;
                        $sum_haber+=$fila->haber;
                    }
                }else{
                    $aprobado=0;
                    $msj.="$fila->codigo | $fila->descripcion: $fila->observacion".'<br>';
                }
            }
            if($aprobado){
                $saldo=$sum_debe-$sum_haber;
//                echo"$saldo=$sum_debe-$sum_haber;";
                $tdebe=0;
                $thaber=0;
                if($saldo>0){
                    $thaber=$saldo;
                }else{
                    $tdebe=$saldo*(-1);
                }
                $sum_debe+=$tdebe;
                $sum_haber+=$thaber;
                $comprobante->detalles[] = array("cuen" => $_POST[resultado_id],
                    "debe" => $tdebe, "haber" => $thaber,"glosa" => $glosa, "ca" => 0, "cf" => 0, "cc" => 0
                );

                COMPROBANTES::modificar_comprobante($comprobante);
                require_once('modulos/con_comprobante/con_comprobante.class.php');
                $con_comprobante = new con_comprobante();
                $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='apertura_gestion' and cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]' and cmp_eliminado='No'");
                $con_comprobante->imprimir_cmp($cmp->cmp_id);

            }else{
                $this->formulario->ventana_volver($msj, "gestor.php?mod=$this->modulo",'','Error');
            }
        }
        
        function insertar_apertura() {
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";
            $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
            $af=  explode('-', $fecha);
            $ges_id=$_SESSION[ges_id];
            $glosa="Apertura de la Gestion $af[0]";
            $listado=  $this->listado_saldos($_POST[gestion]);
            include_once 'clases/registrar_comprobantes.class.php';
            $comprobante = new stdClass();

            $comprobante->tipo = "Diario";
            $comprobante->mon_id = 1;
            $comprobante->nro_documento = date("Ydm");
            $comprobante->fecha = $fecha;
            $comprobante->ges_id = $ges_id;
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha);
            $comprobante->forma_pago = 'Efectivo';
            $comprobante->ban_id = '';
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = '';
            $comprobante->tabla = "apertura_gestion";
            $comprobante->tabla_id = $_POST[gestion];
            $comprobante->apertura = '1';
            $sum_debe=0;
            $sum_haber=0;
            $aprobado=1;
            $msj='';
            foreach ($listado as $fila) {
                if($fila->aprobado){
                    if($fila->debe>0 || $fila->haber>0){
                        $can_id=0;
                        if($fila->can_codigo){
                            $can_id=  FUNCIONES::get_cuenta_ca($ges_id, $fila->can_codigo);
                        }
                        if($fila->cfl_codigo){
                            $cfl_id=  FUNCIONES::get_cuenta_ca($ges_id, $fila->cfl_codigo);
                        }
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $fila->codigo),
                            "debe" => $fila->debe, "haber" => $fila->haber,"glosa" => $glosa, "ca" => $can_id, 
                            "cf" => $cfl_id, "cc" => 0,'int_id'=>$fila->int_id
                        );
                        $sum_debe+=$fila->debe;
                        $sum_haber+=$fila->haber;
                    }
                }else{
                    $aprobado=0;
                    $msj.="$fila->codigo | $fila->descripcion: $fila->observacion".'<br>';
                }
            }
            if($aprobado){
                $saldo=$sum_debe-$sum_haber;
//                echo"$saldo=$sum_debe-$sum_haber;";
                $tdebe=0;
                $thaber=0;
                if($saldo>0){
                    $thaber=$saldo;
                }else{
                    $tdebe=$saldo*(-1);
                }
                $sum_debe+=$tdebe;
                $sum_haber+=$thaber;
                $comprobante->detalles[] = array("cuen" => $_POST[resultado_id],
                    "debe" => $tdebe, "haber" => $thaber,"glosa" => $glosa, "ca" => 0, "cf" => 0, "cc" => 0
                );

                COMPROBANTES::registrar_comprobante($comprobante);
                require_once('modulos/con_comprobante/con_comprobante.class.php');
                $con_comprobante = new con_comprobante();
                $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='apertura_gestion' and cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]' and cmp_eliminado='No'");
                $con_comprobante->imprimir_cmp($cmp->cmp_id);

            }else{
                $this->formulario->ventana_volver($msj, "gestor.php?mod=$this->modulo",'','Error');
            }
        }
        
        function frm_generar() {
            $this->formulario->dibujar_titulo('GENERAR COMPROBANTE DE APERTURA DE GESTION');
            $fun=new FUNCIONES();
            ?>
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <script type="text/javascript" src="js/util.js"></script>
            <script type="text/javascript" src="js/jquery-ui.min.js"></script>
            <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
            <div id="FormSent">
                <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=ACCEDER&acc=generar";?>" method="POST" enctype="multipart/form-data">
                    <div class="Subtitulo">Seleccion la Gestion</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Gestion</div>
                            <div id="CajaInput">
                                <select id="gestion" name="gestion">
                                    <option value="">-- Seleccione --</option>
                                    <?php $fun->combo("select ges_id as id, ges_descripcion as nombre from con_gestion where ges_id!='$_SESSION[ges_id]' order by ges_id desc", $_POST[gestion]);?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >&nbsp;</div>
                            <div id="CajaInput">
                                <input id="btn-vista-prev" class="boton" type="button" value="Vista Previa" name="">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <script>
                $('#btn-vista-prev').click(function(){
                    $('#form').val('');
//                    var fecha=$('#fecha').val();
                    var gestion=$('#gestion option:selected').val();
//                    var resultado_id=$('#resultado_id').val();
                    if(gestion===''){
                        $.prompt('Ingrese correctamente la Fecha');
                        return;
                    }
                    document.frm_sentencia.submit();
                });
            </script>
            <?php
        }

        function generar_vista($gestion_id) {
            $this->formulario->dibujar_titulo('GENERAR COMPROBANTE DE APERTURA DE GESTION');
            ?>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <script type="text/javascript" src="js/util.js"></script>
                <script type="text/javascript" src="js/jquery-ui.min.js"></script>
                <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
                <div id="FormSent">
                    <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=ACCEDER&acc=$_GET[acc]";?>" method="POST" enctype="multipart/form-data">
                        <div class="Subtitulo">OPCIONES</div>
                        <?php 
                            $listado=  $this->listado_saldos($gestion_id);
                            $nro=1;
                        ?>
                        <div id="ContenedorSeleccion">
                            <table id="cmp_detalle" class="tablaLista" width="100%" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Codigo</th>
                                        <th>Cuenta</th>
                                        <th>Debe</th>
                                        <th>Haber</th>
                                        <th>Cuenta Analitica</th>
                                        <th>Cuenta de Flujo</th>
                                        <th>Cliente</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sum_debe=0;?>
                                    <?php $sum_haber=0;?>
                                    <?php $aprobado=1;?>
                                    <?php $msj='';?>
                                    <?php foreach ($listado as $fila) {?>
                                        <?php if($fila->debe>0 ||$fila->haber>0 ){?>
                                            <tr>
                                                <?php
                                                    $color_back="#00bc0d";
                                                    if(!$fila->aprobado){
                                                        $color_back="#ff0000";
                                                        $aprobado=0;
                                                        $msj.="$fila->codigo | $fila->descripcion: $fila->observacion".'<br>';
                                                    }
                                                ?>
                                                <td><?php echo $nro;?></td>
                                                <td><?php echo $fila->codigo;?></td>
                                                <td><?php echo $fila->descripcion;?></td>
                                                <td><?php echo $fila->debe>0?round($fila->debe,2):'';?></td>
                                                <td><?php echo $fila->haber>0?round($fila->haber,2):'';?></td>
                                                <td><?php echo $fila->can_id>0?FUNCIONES::atributo_bd_sql("select concat(can_codigo,' | ',can_descripcion) as campo from con_cuenta_ca where can_id='$fila->can_id'"):'';?></td>
                                                <td><?php echo $fila->cfl_id>0?FUNCIONES::atributo_bd_sql("select concat(cfl_codigo,' | ',cfl_descripcion) as campo from con_cuenta_cf where cfl_id='$fila->cfl_id'"):'';?></td>
                                                <td><?php echo $fila->int_id>0?FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$fila->int_id'"):'';?></td>
                                                <td ><span style="line-height: 16px; padding: 3px 5px; color: #fff; background-color: <?php echo $color_back?>;"><?php echo $fila->aprobado?'Correcto':$fila->observacion;?></span></td>
                                            </tr>
                                        <?php 
                                                $sum_debe+=$fila->debe;
                                                $sum_haber+=$fila->haber;
                                                $nro++;
                                            } 
                                    }
                                    $saldo=$sum_debe-$sum_haber;
//                                    echo"$saldo=$sum_debe-$sum_haber;";
                                    $tdebe=0;
                                    $thaber=0;
                                    if($saldo>0){
                                        $thaber=$saldo;
                                    }else{
                                        $tdebe=$saldo*(-1);
                                    }
                                    $sum_debe+=$tdebe;
                                    $sum_haber+=$thaber;
                                    ?>
                                    <tr>
                                        <td><?php echo $nro;?></td>
                                        <td>
                                            
                                            <input type="hidden" id="msj" value="<?php echo $msj;?>">
                                            <input type="hidden" id="aprobado" name="aprobado" value="<?php echo $aprobado;?>">
                                            <input type="hidden"id="resultado_id" name="resultado_id" value="">
                                            <b id="txt_codigo_resultado"></b>
                                        </td>
                                        <td>
                                            <input type="text"id="txt_resultado" name="txt_resultado" value="" size="45">
                                        </td>
                                        <td><?php echo $tdebe>0?round($tdebe,2):'';?></td>
                                        <td><?php echo $thaber>0?round($thaber,2):'';?></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td >&nbsp;</td>
                                        <td ><span style="padding: 3px 5px; color: #fff; background-color: #00bc0d;">Correcto</span></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">TOTALES</td>
                                        <td><?php echo number_format($sum_debe,2);?></td>
                                        <td><?php echo number_format($sum_haber,2);?></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="ContenedorSeleccion">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Fecha</div>
                                <div id="CajaInput">
                                    <?php $pperiodo=  FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_ges_id=$_SESSION[ges_id] order by pdo_fecha_inicio asc limit 1");?>
                                    <input type="hidden" id="form" name="form" value="" >
                                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                                    <input type="hidden" id="fecha_ini" value="<?php echo $pperiodo->pdo_fecha_inicio;?>" >
                                    <input type="hidden" id="fecha_fin" value="<?php echo $pperiodo->pdo_fecha_fin;?>" >
                                    <input type="hidden" id="gestion" name="gestion" value="<?php echo $gestion_id?>" >
                                    <input type="text" id="fecha" name="fecha" value="<?echo $_POST[fecha]?$_POST[fecha]:"01/01/".date('Y');?>">
                                    
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Gestion</div>
                                <div id="CajaInput">
                                    <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select ges_descripcion as campo from con_gestion where ges_id='$gestion_id'");?></div>
                                </div>
                            </div>
                        </div>
                        <div id="ContenedorSeleccion">
                            <div id="ContenedorDiv">
                                <div style="text-align: center">
                                    <input id="btn-guardar" class="boton" type="button" value="Guardar" name="">
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
            <script>
                $('#fecha').mask('99/99/9999');
                var projects = JSON.parse('<?php echo FUNCIONES::cuentas_json("and (cue_codigo like '1.%' or cue_codigo like '2.%' or cue_codigo like '3.%')");?>')
                function complete_cuenta(input){
                    autocomplete_ui(
                        {
                            input:'#txt_resultado',
                            bus_info:true,
                            lista:projects,
                            select:function(obj){
//                                console.log(obj);
                                $(input).val(obj.id);
                                $('#txt_codigo_resultado').text(obj.info);
                                $('#txt_resultado').val(obj.value);
                                return false;
                            }
                        }
                    );
                }
                
                $('#btn-guardar').click(function(){
                    $('#form').val('ok');
                    var fecha_ini=$('#fecha_ini').val();
                    var fecha_fin=$('#fecha_fin').val();
                    var fecha=$('#fecha').val();
                    var gestion=$('#gestion option:selected').val();
                    var resultado_id=$('#resultado_id').val();
                    if(fecha==='' || gestion==='' || resultado_id*1===0 ){
                        $.prompt('Ingrese correctamente la Fecha, Gestion y la Cuenta de Resultado');
                        return;
                    }
                    
                    var _fecha= fecha_mysql(fecha)
                    if(!(_fecha>=fecha_ini && _fecha<=fecha_fin)){
                        $.prompt('La fecha no se encuentra en el rango del primer periodo de la gestion');
                        return;
                    }
                    var aprobado=$('#aprobado').val();
                    if(aprobado*1===0 ){
                        $.prompt($('#msj').val());
                        return;
                    }
                    mostrar_ajax_load();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        ocultar_ajax_load();
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
//                            console.info('OK');
                            document.frm_sentencia.submit();
                        } else if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }
                    }); 
                });
                complete_cuenta('#resultado_id');
            </script>
            <?php
            
        }
        
        function listado_saldos($gestion_id) {
//            $gestion_id=$_POST[gestion];
            
            $ges_id=$_SESSION[ges_id];
            $parametro=  FUNCIONES::parametro("formula_bal_gral",$ges_id);
            $formula=  json_decode($parametro);
            $equ1=$formula->{'1'};
            
//            $formula_val_1=array();
            $lista_saldos=array();
            for ($i = 0; $i < count($equ1); $i++) {
                $fm=$equ1[$i];
                $_acod=  explode('.', $fm->cuenta);
                $sql_sel="SELECT 
                            cue_codigo,cue_descripcion,cde_cue_id, SUM(cde_valor) as saldo,cde_can_id,cde_cfl_id,cde_int_id 
                        FROM
                            con_comprobante_detalle, con_comprobante,con_cuenta
                        WHERE
                            cde_mon_id = '1'  and cmp_ges_id=$gestion_id and cmp_eliminado='No' and cue_codigo like '$_acod[0].%' and cmp_id=cde_cmp_id  and cue_id=cde_cue_id 
                        group by cde_cue_id, cde_can_id,cde_cfl_id,cde_int_id order by cue_codigo asc";
						
				$sql_sel="SELECT 
                            cue_codigo,cue_descripcion,cde_cue_id, SUM(cde_valor) as saldo
                        FROM
                            con_comprobante_detalle, con_comprobante,con_cuenta
                        WHERE
                            cde_mon_id = '1'  and cmp_ges_id=$gestion_id and cmp_eliminado='No' and cue_codigo like '$_acod[0].%' and cmp_id=cde_cmp_id  and cue_id=cde_cue_id 
                        group by cde_cue_id order by cue_codigo asc";
						
                $result=  FUNCIONES::lista_bd_sql($sql_sel);
                foreach ($result as $fila) {
                    $objcu=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_codigo='$fila->cue_codigo' and cue_ges_id='$ges_id'");
                    $aprobado=1;
                    $obs=array();
                    if(!$objcu){
                        $aprobado=0;
                        $obs[]='No existe una cuenta con el mismo codigo en la Gestion actual';
                    }
                    $can_codigo='';
                    if($fila->cde_can_id>0){
                       $cue_ca=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_id='$fila->cde_can_id'");
                       $can_codigo=$cue_ca->can_codigo;
                       $objcan=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_codigo='$cue_ca->can_codigo' and can_ges_id='$ges_id'");
                       if(!$objcan){
                            $aprobado=0;
                            $obs[]='No existe una cuenta analitica con el mismo codigo en la Gestion actual';
                        }
                    }
                    $cfl_codigo='';
                    if($fila->cde_cfl_id>0){
                       $cue_cf=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_id='$fila->cde_cfl_id'");
                       $cfl_codigo=$cue_cf->cfl_codigo;
                       $objcfl=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_codigo='$cue_cf->cfl_codigo' and cfl_ges_id='$ges_id'");
                       if(!$objcfl){
                            $aprobado=0;
                            $obs[]='No existe una cuenta de flujo con el mismo codigo en la Gestion actual';
                        }
                    }
//                    if(!$objcu){
//                        $aprobado=0;
//                        $obs='No existe una cuenta con el mismo codigo en la Gestion actual';
//                    }
                    $debe=0;
                    $haber=0;
                    if($fila->saldo>0){
                        $debe=$fila->saldo;
                    }else{
                        $haber=$fila->saldo*(-1);
                    }
                    
                    $lista_saldos[]=(object)array(
                        'descripcion'=>$fila->cue_descripcion,
                        'codigo'=>$fila->cue_codigo,
                        'cue_id'=>$fila->cde_cue_id,
                        'can_codigo'=>$can_codigo,
                        'can_id'=>$fila->cde_can_id,
                        'cfl_codigo'=>$cfl_codigo,
                        'cfl_id'=>$fila->cde_cfl_id,
                        'int_id'=>$fila->cde_int_id,
                        'debe'=>$debe,
                        'haber'=>$haber,
                        'aprobado'=>$aprobado,
                        'observacion'=>  implode('<br>', $obs),
                    );
                }
            }
            $equ2=$formula->{'2'};
//            $formula_val_2=array();
            for ($i = 0; $i < count($equ2); $i++) {
                $fm=$equ2[$i];
                $_acod=  explode('.', $fm->cuenta);
                $sql_sel="SELECT 
                            cue_codigo,cue_descripcion,cde_cue_id, SUM(cde_valor) as saldo,cde_can_id,cde_cfl_id,cde_int_id 
                        FROM
                            con_comprobante_detalle, con_comprobante,con_cuenta
                        WHERE
                            cde_mon_id = '1'  and cmp_ges_id=$gestion_id and cmp_eliminado='No' and cue_codigo like '$_acod[0].%' and cmp_id=cde_cmp_id  and cue_id=cde_cue_id 
                        group by cde_cue_id, cde_can_id,cde_cfl_id,cde_int_id order by cue_codigo asc";
						
				$sql_sel="SELECT 
                            cue_codigo,cue_descripcion,cde_cue_id, SUM(cde_valor) as saldo
                        FROM
                            con_comprobante_detalle, con_comprobante,con_cuenta
                        WHERE
                            cde_mon_id = '1'  and cmp_ges_id=$gestion_id and cmp_eliminado='No' and cue_codigo like '$_acod[0].%' and cmp_id=cde_cmp_id  and cue_id=cde_cue_id 
                        group by cde_cue_id order by cue_codigo asc";
						
                $result=  FUNCIONES::lista_bd_sql($sql_sel);
                foreach ($result as $fila) {
                    $objcu=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_codigo='$fila->cue_codigo' and cue_ges_id='$ges_id'");
                    $aprobado=1;
                    $obs=array();
                    if(!$objcu){
                        $aprobado=0;
                        $obs[]='No existe una cuenta con el mismo codigo en la Gestion actual';
                    }
                    $can_codigo='';
                    if($fila->cde_can_id>0){
                       $cue_ca=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_id='$fila->cde_can_id'");
                       $can_codigo=$cue_ca->can_codigo;
                       $objcan=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_codigo='$cue_ca->can_codigo' and can_ges_id='$ges_id'");
                       if(!$objcan){
                            $aprobado=0;
                            $obs[]='No existe una cuenta analitica con el mismo codigo en la Gestion actual';
                        }
                    }
                    $cfl_codigo='';
                    if($fila->cde_cfl_id>0){
                       $cue_cf=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_id='$fila->cde_cfl_id'");
                       $cfl_codigo=$cue_cf->cfl_codigo;
                       $objcfl=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_codigo='$cue_cf->cfl_codigo' and cfl_ges_id='$ges_id'");
                       if(!$objcfl){
                            $aprobado=0;
                            $obs[]='No existe una cuenta de flujo con el mismo codigo en la Gestion actual';
                        }
                    }
                    $debe=0;
                    $haber=0;
                    if($fila->saldo>0){
                        $debe=$fila->saldo;
                    }else{
                        $haber=$fila->saldo*(-1);
                    }
                    $lista_saldos[]=(object)array(
                        'descripcion'=>$fila->cue_descripcion,
                        'codigo'=>$fila->cue_codigo,
                        'cue_id'=>$fila->cde_cue_id,
                        'can_codigo'=>$can_codigo,
                        'can_id'=>$fila->cde_can_id,
                        'cfl_codigo'=>$cfl_codigo,
                        'cfl_id'=>$fila->cde_cfl_id,
                        'int_id'=>$fila->cde_int_id,
                        'debe'=>$debe,
                        'haber'=>$haber,
                        'aprobado'=>$aprobado,
                        'observacion'=>  implode('<br>', $obs),
                    );
                }                                                
            }
            return $lista_saldos;
//            $monto_res=  $this->obtener_total_estado_resultado();                        
//            $this->mostrar_tabla_resultados($formula_val_1,$formula_val_2,$monto_res);
        }
        function guardar_generar() {
            
        }

        function ver_opciones() {
            $gestion = FUNCIONES::objeto_bd_sql("select * from con_gestion where ges_id='$_SESSION[ges_id]'");
            $this->formulario->dibujar_titulo('APERTURA DE ' . strtoupper($gestion->ges_descripcion));
            $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_apertura=1 and cmp_ges_id='$_SESSION[ges_id]'");
            ?>
                <style>
                    a.boton{
                        padding: 10px 20px; text-decoration: none;
                    }
                </style>
                <div id="FormSent">
                    <div class="Subtitulo">OPCIONES</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaInput">
                                <br>
                                <?php if($cmp){?>
                                    <a href="<?php echo "gestor.php?mod=$this->modulo&tarea=ACCEDER&acc=ver";?>" class="boton">Ver Comprobante</a>
                                    <a href="<?php echo "gestor.php?mod=$this->modulo&tarea=ACCEDER&acc=actualizar";?>" class="boton">Actualizar Comprobante</a>
                                <?php }else{?>
                                    <a href="<?php echo "gestor.php?mod=$this->modulo&tarea=ACCEDER&acc=generar";?>" class="boton">Generar Comprobante</a>
                                <?php }?>
                                
                                <br>
                                <br>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
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
		$page="'gestor.php?mod=con_banco&tarea=AGREGAR&acc=Emergente'";
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
				document.frm_con_banco.ban_int_id.value="";
				document.frm_con_banco.ban_nombre_persona.value="";
			}
            </script>
            
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_con_banco" name="frm_con_banco" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
                                                    <div id="ContenedorDiv">
                                                        <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                                                        <div id="CajaInput">
                                                            <input name="ban_nombre" id="ban_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['ban_nombre'];?>" size="50">
                                                        </div>
                                                    </div>
                                                    <div id="ContenedorDiv">
                                                        <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                                                        <div id="CajaInput">
                                                            <textarea id="ban_descripcion" cols="33" rows="2" name="ban_descripcion"><?php echo $_POST['ban_descripcion'];?></textarea>                                                            
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
				document.getElementById('ban_int_id').value = obj.id;
			}
		};
		var as_json1 = new _bsn.AutoSuggest('ban_nombre_persona', options1);
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
	
	function emergente()
	{
		$this->formulario->dibujar_cabecera();		
		$valor=trim($_POST['valor']);
	?>
			
			<script>
				function poner(id,valor)
				{
					opener.document.frm_con_banco.ban_int_id.value=id;
					opener.document.frm_con_banco.ban_nombre_persona.value=valor;
					window.close();
				}			
			</script>
			<br><center><form name="form" id="form" method="POST" action="gestor.php?mod=con_banco&tarea=AGREGAR&acc=Emergente">
				<table align="center">
					<tr>
						<td class="txt_contenido" colspan="2" align="center">
							<input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor;?>">
							<input name="Submit" type="submit" class="boton" value="Buscar">
						</td>
					</tr>
				</table>
			</form><center>
			<?php
			
			$conec= new ADO();
		
			if($valor<>"")
			{
				$sql="select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
			}
			else
			{
				$sql="select int_id,int_nombre,int_apellido from interno";
			}
			
			$conec->ejecutar($sql);
			
			$num=$conec->get_num_registros();
			
			echo '<table class="tablaLista" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>
							Nombre
						</th>
						<th>
							Apellido
						</th>
						<th width="80" class="tOpciones">
							Seleccionar
						</th>
				</tr>
				</thead>
				<tbody>
			';
			
			for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				echo '<tr>
						 <td>'.$objeto->int_nombre.'</td>
						 <td>'.$objeto->int_apellido.'</td>
						 <td><a href="javascript:poner('."'".$objeto->int_id."'".','."'".$objeto->int_nombre.' '.$objeto->int_apellido."'".');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>	 
				';
				
				$conec->siguiente();
			}
			
			?>
			</tbody></table>
			<?php
	}
	
	function insertar_tcp()
	{

                $conec= new ADO();		
                $sql="insert into con_banco (ban_nombre,ban_descripcion,ban_eliminado)
                    values ('".$_POST['ban_nombre']."','".$_POST['ban_descripcion']."','No')";
                //echo $sql.'<br>';
                $conec->ejecutar($sql);
                $mensaje='Banco Agregado Correctamente';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function modificar_tcp()
	{
            $conec= new ADO();
            $sql="update con_banco set 
                        ban_nombre='".$_POST['ban_nombre']."',
                        ban_descripcion='".$_POST['ban_descripcion']."'
                        where ban_id='".$_GET['id']."'";
            //echo $sql;	
            $conec->ejecutar($sql);
            $mensaje='Banco Modificado Correctamente';		
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}

}
?>