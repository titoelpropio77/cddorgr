<?php
class EXTRA_PAGO extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;
    function EXTRA_PAGO() {
        $this->ele_id = 182;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "res_id";
        $this->arreglo_campos[0]["texto"] = "Nro";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;

        $this->arreglo_campos[1]["nombre"] = "int_nombre";
        $this->arreglo_campos[1]["texto"] = "Nombre";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 40;

        $this->arreglo_campos[2]["nombre"] = "int_apellido";
        $this->arreglo_campos[2]["texto"] = "Apellido";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 40;

        $this->arreglo_campos[3]["nombre"] = "urb_nombre";
        $this->arreglo_campos[3]["texto"] = "Urbanizaciï¿½n";
        $this->arreglo_campos[3]["tipo"] = "cadena";
        $this->arreglo_campos[3]["tamanio"] = 40;

        $this->arreglo_campos[4]["nombre"] = "man_nro";
        $this->arreglo_campos[4]["texto"] = "Manzano";
        $this->arreglo_campos[4]["tipo"] = "cadena";
        $this->arreglo_campos[4]["tamanio"] = 40;

        $this->arreglo_campos[5]["nombre"] = "lot_nro";
        $this->arreglo_campos[5]["texto"] = "Lote";
        $this->arreglo_campos[5]["tipo"] = "cadena";
        $this->arreglo_campos[5]["tamanio"] = 40;

        $this->arreglo_campos[6]["nombre"] = "res_fecha";
        $this->arreglo_campos[6]["texto"] = "Fecha";
        $this->arreglo_campos[6]["tipo"] = "fecha";
        $this->arreglo_campos[6]["tamanio"] = 12;

        $this->arreglo_campos[7]["nombre"] = "res_estado";
        $this->arreglo_campos[7]["texto"] = "Estado";
        $this->arreglo_campos[7]["tipo"] = "comboarray";
        $this->arreglo_campos[7]["valores"] = "Habilitado,Deshabilitado,Expirado,Concretado:Habilitado,Deshabilitado,Expirado,Concretado";

        $this->arreglo_campos[8]["nombre"] = "res_usu_id";
        $this->arreglo_campos[8]["texto"] = "Usuario";
        $this->arreglo_campos[8]["tipo"] = "cadena";
        $this->arreglo_campos[8]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'extra_pago';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('PAGOS EXTRA');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        ?>
        <script>

            function ejecutar_script(id,tarea){
                if(tarea==='ANULAR')
                {
                    var estado=$("#estado-"+id).text();
                    estado= estado.replace(/^\s+/g,'').replace(/\s+$/g,'');
                    var monto='';
                    if(estado!=='Deshabilitado'){
                        if(estado==='Habilitado'){
                            monto='<br />Ingrese Monto que Devolverá<br /><input type="text" size="5" name="monto_devolucion" id="monto_devolucion" value="0" />';
                        }                    
                        var txt = 'Esta seguro de Deshabilitar la Reserva?'+monto;

                        $.prompt(txt,{ 
                            buttons:{Deshabilitar:true, Cancelar:false},
                            callback: function(v,m,f){
                                if(v){
                                    var devolucion='';
                                    if(estado==='Habilitado'){
                                        devolucion='&monto_devolucion='+f.monto_devolucion;
                                    }
//                                    alert(devolucion);
//                                    alert(estado);
                                    location.href='gestor.php?mod=reserva&tarea='+tarea+'&id='+id+devolucion;
                                }
                            }
                        });
                    }else{
                        $.prompt('La reserva y se encuentra Deshabilitada');
                    }
                }
                
            }
        </script>

        <?php
        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }


        if ($this->verificar_permisos('ANULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DESHABILITAR';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }


        if ($this->verificar_permisos('PAGOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PAGOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cuenta.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PAGOS';
            $nun++;
        }

    }

    function dibujar_listado() {
        $sql = "select * from extra_pago,extra_pago_tipo, interno where epag_ept_id= ept_id and epag_int_id=int_id";
        $this->set_sql($sql, ' order by epag_id desc ');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nro Pago</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Fecha Programada</th>
            <th>Monto</th>
            <th>Moneda</th>
            <th>Estado</th>
            <th>Ejecutado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        
        $color=array('Pendiente'=>'#ff9601','Pagado'=>'#019721','Anulado'=>'#ff0000','Devuelto'=>'#000');
        
                
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            $operaciones =array();
            if($objeto->epag_estado=='Pendiente'){
                
            }elseif($objeto->epag_estado=='Pagado' ){
                $operaciones[]="PAGOS";
            }elseif($objeto->epag_estado=='Anulado'){
                $operaciones[]="ANULAR";
                $operaciones[]="PAGOS";
            }
            
            if($objeto->epag_estado=='Pagado' && $objeto->epag_tabla!=''){
                $operaciones[]="ANULAR";
            }
            echo '<tr>';
                echo "<td>";
                    echo $objeto->epag_id;
                echo "</td>";
                echo "<td>";
                    echo "$objeto->int_nombre $objeto->int_apellido";
                echo "</td>";
                echo "<td>";
                    echo $objeto->ept_nombre;
                echo "</td>";
                echo "<td>";
                    echo FUNCIONES::get_fecha_latina($objeto->epag_fecha_programada);
                echo "</td>";
                echo "<td>";
                    echo $objeto->epag_monto;
                echo "</td>";
                echo "<td>";
                    echo $this->epag_moneda==1?'Bolivianos':'Dolares';
                echo "</td>";
                echo "<td id='estado-$objeto->epag_id'>";
                    echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->epag_estado]}'>$objeto->epag_estado</span>";
                echo "</td>";
                echo "<td id='estado-$objeto->epag_id'>";
                    if($objeto->epag_ejecutado){
                        echo "<span style='padding:0 2px;color:#fff;background-color:#0356ff'>Si</span>";
                    }else{
                        echo "<span style='padding:0 2px;color:#fff;background-color:#ff9601'>No</span>";
                    }
                    
                echo "</td>";
                echo "<td>";
                echo $this->get_opciones($objeto->epag_id,"",$operaciones);
                echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from ad_usuario
				where usu_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['usu_id'] = $objeto->usu_id;

        $_POST['usu_password'] = '123456789';

        $_POST['usu_per_id'] = $objeto->usu_per_id;

        $_POST['usu_estado'] = $objeto->usu_estado;

        $_POST['usu_gru_id'] = $objeto->usu_gru_id;

        $fun = NEW FUNCIONES;

        $_POST['usu_nombre_persona'] = $fun->nombre($objeto->usu_per_id);
    }

    function datos() {
        if ($_POST) {
            $valores_lote = explode("-", $_POST['ven_lot_id']);
            $id_lote = $valores_lote[0];

            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Persona";
            $valores[$num]["valor"] = $_POST['ven_int_id'];
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;
            $num++;
            

            $val = NEW VALIDADOR;
            $this->mensaje = "";
            if ($val->validar($valores)) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;
                return false;
            }
        }
        return false;
    }

    function formulario_tcp($tipo) {
        $conec = new ADO();

        $sql = "select * from interno";
        $conec->ejecutar($sql);
        $nume = $conec->get_num_registros();
        $personas = 0;
        if ($nume > 0) {
            $personas = 1;
        }

        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }

        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        $page = "'gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente'";
        $extpage = "'persona'";
        $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        
        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <!--MaskedInput-->
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
                                <?php
                                if ($personas <> 0) {
                                    ?>
                                    <input name="ven_int_id" id="ven_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id'] ?>" size="2">
                                    <input name="int_nombre_persona" readonly="readonly" id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                    </a>
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                        <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                    </a>
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <?php
                                } else {
                                    echo 'No se le asigno ningï¿½na personas, para poder cargar las personas.';
                                }
                                ?>
                                <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                                <input type="hidden" name="tca" id="tca" size="5" value="<?php echo $this->tc; ?>" readonly="readonly">
                            </div>
                        </div>
                        <?php
                        $servicios = FUNCIONES::lista_bd('extra_pago_tipo', '*', "ept_eliminado='No' and (ept_llave='' or ept_llave is null)");
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Motivo</div>
                            <div id="CajaInput">
                                <select id="tipo" name="tipo">
                                    <option value="">Seleccione</option>
                                    <?php foreach($servicios as $serv){?>
                                        <option value="<?php echo $serv->ept_id;?>" data-costo="<?php echo $serv->ept_costo;?>"><?php echo $serv->ept_nombre?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Urbanizacion</div>
                            <div id="CajaInput">
                                <select id="urb_id" name="urb_id">
                                    <option value="">Seleccione</option>
                                    <?php
                                        $fun=new FUNCIONES();
                                        $fun->combo("select urb_id as id , urb_nombre as nombre from urbanizacion where 1 and urb_eliminado='No'", '');
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Monto</div>
                            <div id="CajaInput">
                                <input type="hidden" id="moneda" name="moneda" value="2">
                                <input type="text" id="monto" name="monto" value=""> $us.
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Fecha Programada</div>
                            <div id="CajaInput">
                                <input type="text" id="fecha_prog" name="fecha_prog" value="<?php echo date('d/m/Y');?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nota</div>
                            <div id="CajaInput">
                                <textarea type="text" id="nota" name="nota" ></textarea>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php
                                if (!($ver)) {
                                    ?>
                                    <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario()">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php
                                } else {
                                    ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php
                                }
                                ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!($ver || $cargar)) { ?>
            
        <?php } ?>
        
        <script>
            mask_decimal('#monto',null);
            $('#fecha_prog').mask('99/99/9999');
            $('#tipo').change(function(){
                var costo=$('#tipo option:selected').attr('data-costo')*1;
                $('#monto').val(costo);
            });
            function set_valor_interno(data){
                document.frm_sentencia.ven_int_id.value = data.id;
                document.frm_sentencia.int_nombre_persona.value = data.nombre;
            }
            function reset_interno()
            {
                document.frm_sentencia.ven_int_id.value = "";
                document.frm_sentencia.int_nombre_persona.value = "";
            }
            function enviar_formulario(){
                var persona = document.frm_sentencia.ven_int_id.value;
                var tipo=$('#tipo option:selected').val()*1;
                var urb_id=$('#urb_id option:selected').val()*1;
                var monto=$('#monto').val();
                var fecha=$('#fecha_prog').val();
                
                if(persona>0 && tipo>0 && urb_id>0 && monto!=='' && fecha !==''){
                    document.frm_sentencia.submit();
                }else{
                    $.prompt('Para registrar el Pago Extra debe ingresar Persona , Motivo, Monto y Fecha.', {opacity: 0.8});
                }
            }
            
            function ValidarNumero(e){
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46)){
                    return false;
                }
                return true;
            }

        </script>
        <?php
    }
    function nombre_persona($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function insertar_tcp() {
        $conec = new ADO();
        if (true) {
            $tep=  FUNCIONES::objeto_bd_sql("select * from extra_pago_tipo where ept_id='$_POST[tipo]'");
            $fecha_cre=  date('Y-m-d');
            $fecha_prog=  FUNCIONES::get_fecha_mysql($_POST[fecha_prog]);
            $monto=$_POST[monto];
            $estado="Pendiente";
            if($monto<=0){
                $estado="Pagado";
                $nro_recibo =  FUNCIONES::nro_recibo($fecha_prog);
                $fecha_pag=$fecha_prog;
                $usu_pago=$_SESSION[id];
            }
            $urb_id=$_POST[urb_id];
            $sql_insert="insert into extra_pago(
                            epag_ept_id, epag_tabla,epag_int_id, epag_nota,epag_fecha_programada,epag_monto,
                            epag_moneda,epag_fecha_cre,epag_usu_cre,epag_estado,epag_ejecutado, 
                            epag_recibo,epag_fecha_pago,epag_usu_pago,epag_urb_id,epag_suc_cre
                        )values(
                            '$tep->ept_id','','$_POST[ven_int_id]','$_POST[nota]','$fecha_prog','$_POST[monto]',
                            $_POST[moneda],'$fecha_cre','$_SESSION[id]','$estado','0',
                            '$nro_recibo','$fecha_pag','$usu_pago','$urb_id','$_SESSION[suc_id]'
                        )";
            $conec->ejecutar($sql_insert,false);
            $llave=  mysql_insert_id();
            $mensaje = 'Pago Extra Agregado Correctamente';
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            
            $this->nota_pago($llave);
        } else {

            $mensaje = 'No puese realizar reserva de terreno, por que usted no esta registrado como cajero.';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }
    
    function total_anticipo($res_id) {
        $pagos=  FUNCIONES::lista_bd_sql("select * from reserva_pago where respag_res_id='$res_id' and respag_estado='Pagado'");
        $sum_total=0;
        foreach ($pagos as $pag) {
            if($pag->respag_moneda==2){
                $sum_total+=$pag->respag_monto;
            }elseif($pag->respag_moneda==1){
                $tc_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$pag->respag_fecha' and tca_mon_id=2");
                $sum_total+=$pag->respag_monto/$tc_usd;
            }
        }
        return $sum_total;
    }

    function anular() {
        $this->formulario->dibujar_tarea();
        $pago=  FUNCIONES::objeto_bd_sql("select * from extra_pago where epag_id=$_GET[id]");
        if(($pago->epag_estado=='Pendiente'||$pago->epag_estado=='Pagado' ) && !$pago->epag_ejecutado){
            if($_POST){
                $this->guardar_anulacion($pago);
            }else{
                $this->frm_anulacion($pago);
            }
        }else{
            $mensaje = 'No se pudo <b>Deshabilitar la el pago extra</b> por que el Pago ya se encuentra en estado <b>Anulado</b> o ya fue <b>Ejecutado</b>';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,"","error");
        }
    }
    
    function guardar_anulacion($pago){
//        FUNCIONES::print_pre($_POST);
        $conec=new ADO();
        $fecha_now=date('Y-m-d');
        $sql = "update extra_pago set epag_estado='Anulado', epag_fecha_mod='$fecha_now'where epag_id='$pago->epag_id'";
        $conec->ejecutar($sql);
        if($pago->epag_estado=='Pagado'){
            include_once 'clases/registrar_comprobantes.class.php';
            $bool=COMPROBANTES::anular_comprobante(_CTE::$EXTRA_PAGO, $pago->epag_id);
            if(!$bool){
                $mensaje="El pago de la cuota no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
                return;
            }
            include_once 'clases/recibo.class.php';
            RECIBO::anular($pago->epag_recibo);
        }
        if($pago->epag_tabla!=''){
            include_once 'clases/eventos.class.php';
            if($pago->epag_tabla=='ven_prop_his'){
                $vph=  FUNCIONES::objeto_bd_sql("select * from venta_propietarios_historial where vph_id=$pago->epag_tabla_id");
                $data=array(
                    'objeto'=>$vph,
                    'fecha'=>$pago->epag_fecha_pago,
                );
                EVENTOS::cambiar_propietario($data,'R');
            }
            
            if($pago->epag_tabla=='ven_neg'){
                $vneg=  FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_id=$pago->epag_tabla_id");
                $data=array(
                    'objeto'=>$vneg,
                    'fecha'=>$pago->epag_fecha_pago,
                );
                EVENTOS::cambiar_lote($data,'R');
            }
        }
        
        $mensaje = 'El pago extra fue anulada correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        
        
        
    }
    
    function frm_anulacion($pago){
        ?>
        <script src="js/util.js"></script>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=extra_pago&tarea=ANULAR&id=<?php echo $pago->epag_id?>" name="frm_sentencia">
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <center>
                                <b>Esta seguro de anular el Pago Nro <?php echo "'$pago->epag_id'";?> de <?php echo FUNCIONES::interno_nombre($pago->epag_int_id);?> de un Monto de <?php echo $pago->epag_monto;?> $us?</b>
                                <br><br>
                            </center>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="button" onclick="location.href='gestor.php?mod=extra_pago&tarea=ACCEDER'" value="Volver" name="">
                                    <input class="boton" type="button" onclick="javascript:enviar_formulario()" value="Anular Pago" name="">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            
            function enviar_formulario(){
                document.frm_sentencia.submit();
                
            }
        </script>
            
        <?php
    }
    
    function nota_pago($pag_id){
        $conec = new ADO();
        $sql="select * from extra_pago where epag_id=$pag_id";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        if($num > 0){
            $objeto = $conec->get_objeto();
            ////
            $pagina = "'contenido_reporte'";
            $page = "'about:blank'";
            $extpage = "'reportes'";
            $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
            $extra1 = "'<html><head><title>Vista Previa</title><head>
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
            $extra2 = "'</center></body></html>'";

            if ($this->verificar_permisos('AMORTIZAR')) {
                $link_amortizar = '<td><a href="#"><img src="images/reformular.png" align="right"  border="0"  title="AMORTIZAR" onclick="javascript:location.href=\'gestor.php?mod=venta&tarea=AMORTIZAR&id=' . $objeto->ind_tabla_id . '\';"></a></td>';
            }

            $modulo='extra_pago';
            if($_SESSION[usu_gru_id]=='CAJERO'){
                $modulo='caja&tarea=ACCEDER';
            }

            echo '<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                                      c.document.write(' . $extra1 . ');
                                      var dato = document.getElementById(' . $pagina . ').innerHTML;
                                      c.document.write(dato);
                                      c.document.write(' . $extra2 . '); c.document.close();"><img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR"></a></td>' . $link_amortizar . '
                                    <td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod='.$modulo. '\';"></td></tr></table>
                                    ';
            
        include_once 'clases/recibo.class.php';
        $data=array(
            'titulo'=>'PAGO DE SERVICIOS EXTRA',
            'referido'=> FUNCIONES::interno_nombre($objeto->epag_int_id),
            'usuario'=>  FUNCIONES::usuario_nombre($objeto->epag_usu_pago),
            'monto'=> $objeto->epag_monto,
            'moneda'=> $objeto->epag_moneda,
            'nro_recibo'=> $objeto->epag_recibo,
            'fecha'=> $objeto->epag_fecha_pago,
            'concepto'=> "$objeto->epag_concepto" ,
//            'has_detalle'=>'1',
//            'det_cabecera'=>array('Interes','Capital','Form.','Total'),
//            'det_body'=>array($objeto->vpag_interes,$objeto->vpag_capital,$objeto->vpag_form,$objeto->vpag_monto),
//            'nota'=>"nodaladsfalsdf s",
            
        );
            
            ?>
                <br><br>
                <?php RECIBO::pago($data);?>
                
            
        <?php
        }
    }
        
    
    function pagos() {
        $pago=  FUNCIONES::objeto_bd_sql("select * from extra_pago where epag_id=$_GET[id]");
        if($pago->epag_estado=='Pendiente'){
            if($_POST){
                $this->guardar_pago($pago);
            }else{
                $this->frm_pago($pago);
            }
        }else{
            $mensaje = 'No existe pago para efecutar';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }
    function guardar_pago($pago) {
        $conec=new ADO();
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $nro_recibo=  FUNCIONES::nro_recibo($fecha);
        $fecha_mod=date('Y-m-d');
        $sql_update="update extra_pago set
                        epag_estado='Pagado',
                        epag_recibo='$nro_recibo',
                        epag_fecha_pago='$fecha',
                        epag_usu_pago='$_SESSION[id]',
                        epag_fecha_mod='$fecha_mod',
                        epag_suc_pago='$_SESSION[suc_id]'
                    where epag_id='$pago->epag_id'" ;
        
        echo "<p>sql_update:$sql_update</p>";
        $conec->ejecutar($sql_update);
        
        $moneda=$pago->epag_moneda;
        $monto=$pago->epag_monto;
        
        include_once 'clases/recibo.class.php';
        $data_recibo=array(
                'recibo'=>$nro_recibo,
                'fecha'=>$fecha,
                'monto'=>$monto,
                'moneda'=>$moneda,
                'tabla'=>'extra_pago',
                'tabla_id'=>$pago->epag_id
            );
        RECIBO::insertar($data_recibo);
        
        $this->nota_pago($pago->epag_id);
        
        $tep=  FUNCIONES::objeto_bd_sql("select * from extra_pago_tipo where ept_id=$pago->epag_ept_id");
        
        if($monto>0){
			echo "entrando por monto>0";
            $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$pago->epag_urb_id'");
            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            $referido= FUNCIONES::interno_nombre($pago->epag_int_id);
            $_concepto=$pago->epag_concepto?"- $pago->epag_concepto":'';
            $glosa = "Pago por $tep->ept_nombre Nro. $pago->epag_id, rec. $nro_recibo $_concepto";
            $params=array(
                'tabla'=>'extra_pago',
                'tabla_id'=>$pago->epag_id,
                'fecha'=>$fecha,
                'moneda'=>$moneda,
                'ingreso'=>true,
                'une_id'=>$urb->urb_une_id,
                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
            );
            $detalles = FORMULARIO::insertar_pagos($params);
            $data=array(
                'moneda'=>$moneda,
                'ges_id'=>$_SESSION[ges_id],
                'fecha'=>$fecha,
                'glosa'=>$glosa,
                'interno'=>$referido,
                'tabla_id'=>$pago->epag_id,
                'urb'=>$urb,
                'tipo'=>$tep,
                
                'monto'=>$monto,
                'detalles'=>$detalles,
            );
            $comprobante = MODELO_COMPROBANTE::extra_pago($data);
            COMPROBANTES::registrar_comprobante($comprobante);
        }
        if($pago->epag_tabla!=''){
            include_once 'clases/eventos.class.php';
            if($pago->epag_tabla=='ven_prop_his'){
                $vph=  FUNCIONES::objeto_bd_sql("select * from venta_propietarios_historial where vph_id=$pago->epag_tabla_id");
                $data=array(
                    'objeto'=>$vph,
                    'fecha'=>$fecha,
                );
                EVENTOS::cambiar_propietario($data,'P');
            }elseif($pago->epag_tabla=='ven_neg'){
                $vneg=  FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_id=$pago->epag_tabla_id");
                $data=array(
                    'objeto'=>$vneg,
                    'fecha'=>$fecha,
                );
                if($vneg->vneg_tipo=='cambio_lote'){
                    EVENTOS::cambiar_lote($data,'P');
                }elseif($vneg->vneg_tipo=='fusion'){
                    EVENTOS::fusion_venta($data,'P');
                }
            }
        }
    }
    function frm_pago($pago) {
        $this->formulario->dibujar_titulo('PAGAR COBRO EXTRA');
        ?>
        <script src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" enctype="multipart/form-data" method="POST" action="gestor.php?mod=extra_pago&tarea=PAGOS&id=<?php echo $pago->epag_id;?>" name="frm_sentencia">
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <input type="hidden" id="monto" name="monto" value="<?php echo $pago->epag_monto;?>">
                <input type="hidden" id="moneda" name="moneda" value="<?php echo $pago->epag_moneda;?>">
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion" style="width: 100%">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Motivo</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select ept_nombre as campo from extra_pago_tipo where ept_id='$pago->epag_ept_id'");?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo FUNCIONES::interno_nombre($pago->epag_int_id);?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Monto</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $pago->epag_monto.' $us';?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Fecha Pago</div>
                            <div id="CajaInput">
                                <?php FORMULARIO::cmp_fecha('fecha');?>
                                <!--<input type="text" name="fecha" id="fecha" value="<?php echo date('d/m/Y');?>">-->
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                                <div class="Etiqueta" ><b>Pagos</b></div>                                
                                <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'fecha','cmp_monto'=>'monto','cmp_moneda'=>'moneda'));?>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="button" onclick="javascript:enviar_formulario();" value="Pagar" name="">
                                    <?php if($_SESSION[usu_gru_id]=='CAJERO'){?>
                                        <input class="boton" type="button" onclick="location.href='gestor.php?mod=caja&tarea=ACCEDER'" value="Volver" name="">
                                    <?php }else{?>
                                        <input class="boton" type="button" onclick="location.href='gestor.php?mod=extra_pago&tarea=ACCEDER'" value="Volver" name="">
                                    <?php }?>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            $('#fecha').mask('99/99/9999');
            function enviar_formulario(){
                var fecha = $('#fecha').val();
                if(fecha!==''){
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response !== "ok") {
                            $.prompt(dato.mensaje);
                        }else{
                            if(!validar_fpag_montos(dato.cambios)){
                                $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                return false;
                            }
                            document.frm_sentencia.submit();
                        }
                    });
                }else{
                    $.prompt('Ingres una fecha de anulacion');
                }
            }
        </script>
            
                <?php
    }

}
?>