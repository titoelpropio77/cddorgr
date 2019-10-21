<?php
class PROFORMA2 extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;
    function PROFORMA2() {
        $this->ele_id = 186;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "pro_id";
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
        $this->arreglo_campos[3]["texto"] = "Urbanización";
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

        $this->arreglo_campos[6]["nombre"] = "pro_fecha";
        $this->arreglo_campos[6]["texto"] = "Fecha";
        $this->arreglo_campos[6]["tipo"] = "fecha";
        $this->arreglo_campos[6]["tamanio"] = 12;

        $this->arreglo_campos[7]["nombre"] = "pro_usu_id";
        $this->arreglo_campos[7]["texto"] = "Usuario";
        $this->arreglo_campos[7]["tipo"] = "cadena";
        $this->arreglo_campos[7]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'proforma2';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('PROFORMAS');

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
//                            monto='<br />Ingrese Monto que Devolver?<br /><input type="text" size="5" name="monto_devolucion" id="monto_devolucion" value="0" />';
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
                                    location.href='gestor.php?mod=reserva&tarea='+tarea+'&id='+id;
                                }
                            }
                        });
                    }else{
                        $.prompt('La reserva y se encuentra Deshabilitada');
                    }
                }

                if(tarea==='CONCRETAR VENTA')
                {				
                    var txt = 'Esta seguro de concretar la Venta?';

                    $.prompt(txt,{ 
                        buttons:{Concretar:true, Cancelar:false},
                        callback: function(v,m,f){
                            if(v){
                                //location.href='gestor.php?mod=reserva&tarea='+tarea+'&id='+id;
                                location.href = 'gestor.php?mod=venta&tarea=AGREGAR&id_res=' + id + '&concretar=ok';
                            }
                            
                        }
                    });
                }
                if (tarea === 'ANTICIPOS RESERVA')
                {
                    $.post("datos_reserva.php", {id: id},
                    function(respuesta) {
                    var res = eval("(" + respuesta + ")");
                        if(res[0].value === 'Pendiente' || res[0].value === 'Habilitado'){
                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                        }else{
                            $.prompt('No puede realizar anticipos a esta reserva.');
                        }    
                    });
                }
                if (tarea === 'DEVOLVER')
                {
                    $.post("datos_reserva.php", {id: id},
                    function(respuesta) {
                    var res = eval("(" + respuesta + ")");
                        if(res[0].value === 'Deshabilitado'){
                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                        }else{
                            $.prompt('No puede realizar devoluciones a esta reserva.');
                        }    
                    });
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

       
    }

    function dibujar_listado() {
        $sql = "SELECT 
                   *
                FROM 
                    proforma 
                    inner join interno on (pro_int_id=int_id)
                    inner join lote on (pro_lot_id=lot_id)
                    inner join uv on (uv_id=lot_uv_id)
                    inner join manzano on (lot_man_id=man_id)
                    inner join urbanizacion on (man_urb_id=urb_id)";

        $this->set_sql($sql, ' order by pro_id desc ');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nro Proforma</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Cliente</th>
            <!--<th>Vendedor</th>-->
            <th>Urbanizacion</th>
            <th>Uv</th>
            <th>Manzano</th>
            <th>Lote</th>
			<th>Precio</th>
            <!--
			<th>Anticipo</th>
            <th>Estado</th>
            -->
			<th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();
        $color=array('Pendiente'=>'#ff9601','Habilitado'=>'#019721','Concretado'=>'#0356ff','Deshabilitado'=>'#ff0000','Devuelto'=>'#000');
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            
            $operaciones[]="VER";
            $operaciones[]="ANULAR";
            $operaciones[]="ANTICIPOS RESERVA";
            $operaciones[]="CONCRETAR VENTA";
            $operaciones[]="DEVOLVER";
            $operaciones[]="VER DEVOLUCION";
            $operaciones[]="CAMBIAR FECHA";
            
            $operaciones =array();            
            if($objeto->res_estado=='Pendiente'){
//                $operaciones[]="CONCRETAR VENTA";
                $operaciones[]="DEVOLVER";
                $operaciones[]="VER DEVOLUCION";
                
            }
            if($objeto->res_estado=='Habilitado'){
                $operaciones[]="DEVOLVER";
                $operaciones[]="VER DEVOLUCION";
            }
            if($objeto->res_estado=='Deshabilitado'){
                $operaciones[]="ANULAR";
                $operaciones[]="ANTICIPOS RESERVA";
                $operaciones[]="CONCRETAR VENTA";                
                $operaciones[]="VER DEVOLUCION";
                $operaciones[]="CAMBIAR FECHA";
            }
            if($objeto->res_estado=='Devuelto'){
                $operaciones[]="ANULAR";
                $operaciones[]="ANTICIPOS RESERVA";
                $operaciones[]="CONCRETAR VENTA";
                $operaciones[]="DEVOLVER";
                $operaciones[]="CAMBIAR FECHA";
                
            }
            if($objeto->res_estado=='Concretado'){
                $operaciones[]="ANULAR";
                $operaciones[]="ANTICIPOS RESERVA";
                $operaciones[]="CONCRETAR VENTA";
                $operaciones[]="DEVOLVER";
                $operaciones[]="VER DEVOLUCION";
                $operaciones[]="CAMBIAR FECHA";
            }
            
            

            
            echo '<tr>';

            echo "<td>";
            echo $objeto->pro_id;
            echo "</td>";
            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->pro_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->pro_hora;
            echo "</td>";
            echo "<td>";
            echo $objeto->int_nombre . " " . $objeto->int_apellido;
            echo "</td>";
            // echo "<td>";
            // echo $this->nombre_persona_vendedor($objeto->res_vdo_id);
            // echo "</td>";
            echo "<td>";
            echo $objeto->urb_nombre;
            echo "</td>";
            echo "<td>";
            echo $objeto->uv_nombre;
            echo "</td>";
            echo "<td>";
            echo $objeto->man_nro;
            echo "</td>";
            echo "<td>";
            echo $objeto->lot_nro;
            echo "</td>";
			echo "<td>";
            echo number_format($objeto->pro_lote_precio,2,'.',',');
            echo "</td>";
            // echo "<td>";
            // echo FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo from reserva_pago where respag_res_id='$objeto->res_id' and respag_estado='Pagado'")*1;
            // echo "</td>";

            // echo "<td id='estado-$objeto->res_id'>";
            // echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->res_estado]}'>$objeto->res_estado</span>";
            // echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->pro_id,"",$operaciones);
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
            $valores[$num]["etiqueta"] = "Vendedor";
            $valores[$num]["valor"] = $_POST['vendedor'];
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Lote";

            $valores[$num]["valor"] = $id_lote;
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;

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
        <script>
            function reset_interno()
            {
                document.frm_sentencia.ven_int_id.value = "";
                document.frm_sentencia.int_nombre_persona.value = "";
            }
            function enviar_formulario()
            {
                var persona = document.frm_sentencia.ven_int_id.value;
                var vendedor = document.frm_sentencia.vendedor.options[document.frm_sentencia.vendedor.selectedIndex].value;
                var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;

                if (persona !== '' && vendedor !== '' && lote !== '')
                {
                    document.frm_sentencia.submit();
                }
                else
                {
                    $.prompt('Para registrar una Reserva debe ingresar Persona , Vendedor, Lote y el Monto referencial acordado.', {opacity: 0.8});
                }
            }

            function cargar_datos(valor)
            {
                
            }

            function calcular_monto()
            {
                /*var vt=parseFloat(document.frm_sentencia.valor_terreno.value);
                 var des=parseFloat(document.frm_sentencia.descuento.value);
                 var td=(vt*des)/100;
                 document.frm_sentencia.monto.value=vt-td;*/

//                var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
//                var des = parseFloat(document.frm_sentencia.descuento.value);
//                //var td=(vt*des)/100;
//                document.frm_sentencia.monto.value = vt - des;
            }
            function calcular_valor_terreno()
            {
                var sup = parseFloat(document.frm_sentencia.superficie.value);
                var val = parseFloat(document.frm_sentencia.valor.value);

                document.frm_sentencia.valor_terreno.value = sup * val;

//                calcular_cuota()
                calcular_monto()
            }

            function cargar_uv(id)
            {
                //cargar_lote(0);

                var valores = "tarea=uv&urb=" + id;

                ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
            }

            function cargar_manzano(id, uv)
            {
                //cargar_lote(0);

                var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;

                ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
            }

            function cargar_lote(id, uv)
            {
                var valores = "tarea=lotes&man=" + id + "&uv=" + uv;

                ejecutar_ajax('ajax.php', 'lote', valores, 'POST');
            }

            function obtener_valor_uv() {
                var axuUv = $('#ven_uv_id').val();
                var axuMan = $('#ven_man_id').val();

                cargar_lote(axuMan, axuUv);
            }

            function obtener_valor_manzano() {
                var auxUrb = $('#ven_urb_id').val();
                var auxUv = $('#ven_uv_id').val();

                cargar_manzano(auxUrb, auxUv);
            }

            function ValidarNumero(e)
            {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }
//            function calcular_cuota()
//            {
//                var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
//
//                if (tipo == 'Credito')
//                {
//                    var v_vt = parseFloat(document.frm_sentencia.valor_terreno.value);
//
//                    var dato_s = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
//
//                    var dato_a = dato_s.split('-');
//
//                    document.frm_sentencia.cuota_inicial.value = (dato_a[3] * v_vt) / 100;
//                }
//            }
        </script>
        <!--<script type="text/javascript" src="js/cal2.js"></script>-->
        <!--<script type="text/javascript" src="js/cal_conf2.js"></script>-->
        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
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
                                    <input name="int_nombre_persona" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                    <a class="group" style="float:left; margin:0 0 0 7px;float:right;"  href="sueltos/llamada.php?accion=agregar_persona">
                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                    </a>
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <?php
                                } else {
                                    echo 'No se le asigno ning?na personas, para poder cargar las personas.';
                                }
                                ?>


                                <!--<input type="hidden" name="ci" id="ci"  value="<?php //echo $ci;        ?>">-->
                                <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                                <input type="hidden" name="tca" id="tca" size="5" value="<?php echo $this->tc; ?>" readonly="readonly">
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor I</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="vendedor" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    <?php $sql="select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre, vdo_grupo
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               where vdo_estado='Habilitado' order by int_nombre;";?>
                                    <?php $vendedores1=  FUNCIONES::objetos_bd_sql($sql);?>
                                    <?php for($i=0;$i<$vendedores1->get_num_registros();$i++){?>
                                       <?php $objeto=$vendedores1->get_objeto();?>
                                       <option value="<?php echo $objeto->id;?>"><?php echo $objeto->nombre?></option>
                                       <?php $vendedores1->siguiente();?>
                                    <?php }?>
                               </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv" style="display: none">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor II</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="vendedor2" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    <?php $sql="select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre, vdo_grupo
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               where vdo_estado='Habilitado' and vdo_grupo in ('free', 'empresa', 'co_propietario','especial') order by nombre asc;";?>
                                    <?php $vendedores1=  FUNCIONES::objetos_bd_sql($sql);?>
                                    <?php for($i=0;$i<$vendedores1->get_num_registros();$i++){?>
                                       <?php $objeto=$vendedores1->get_objeto();?>
                                       <option value="<?php echo $objeto->id;?>"><?php echo $objeto->nombre?></option>
                                       <?php $vendedores1->siguiente();?>
                                    <?php }?>
                               </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv" style="display: none">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor III</div>
                            <div id="CajaInput">
                                <?php $sql="select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre, vdo_grupo
                                            from vendedor 
                                            inner join interno on (vdo_int_id=int_id) 
                                            where vdo_estado='Habilitado' and vdo_grupo in ('mdu_ext', 'co_propietario','especial') order by nombre asc;";?>
                                 <?php $vendedores1=  FUNCIONES::objetos_bd_sql($sql);?>
                                <select style="width:200px;" name="vendedor3" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    
                                    <?php for($i=0;$i<$vendedores1->get_num_registros();$i++){?>
                                       <?php $objeto=$vendedores1->get_objeto();?>
                                       <option value="<?php echo $objeto->id;?>"><?php echo $objeto->nombre?></option>
                                       <?php $vendedores1->siguiente();?>
                                    <?php }?>
                               </select>
                            </div>
                        </div>
                        
                        <input readonly="true" type="hidden" name="ven_moneda" id="ven_moneda" size="5" value="">

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizac&oacute;n</div>
                            <div id="CajaInput">
                                <?php
                                if(isset($_GET['lote_reserva']) && $_GET['lote_reserva']!=''){
                                    $sql="select urb_id as id, urb_nombre as nombre from lote l, zona z, urbanizacion u 
                                            where l.lot_zon_id=z.zon_id and z.zon_urb_id=u.urb_id and l.lot_id='".$_GET['lote_reserva']."';";
                                    $urbanizacion=FUNCIONES::objeto_bd_sql($sql);
                                    ?>
                                        <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" >
                                            <option value="<?php echo $urbanizacion->id;?>"><?php echo $urbanizacion->nombre;?></option>                                                                            
                                        </select>
                                    <?php  
                                }else{
                                ?>
                                    <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                <?php                                
                                }
                                ?>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Fase</div>
                            <div id="CajaInput">
                                <div id="uv">
                                    <?php
                                    if(isset($_GET['lote_reserva']) && $_GET['lote_reserva']!=''){
                                        $sql="select uv_id as id, uv_nombre as nombre from lote l, uv uv 
                                                where l.lot_uv_id=uv.uv_id and l.lot_id='".$_GET['lote_reserva']."';";
                                        $uv=FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                            <select style="width:200px;" name="ven_uv_id" id="ven_uv_id" class="caja_texto" >
                                                <option value="<?php echo $uv->id;?>">Uv Nro: <?php echo $uv->nombre;?></option>                                                                            
                                            </select>
                                    <?php  
                                    } else{
                                    ?>
                                        <select style="width:200px;" name="ven_uv_id" class="caja_texto">
                                            <option value="">Seleccione</option>
                                            <?php
                                            if ($_POST['ven_urb_id'] <> "") {
                                                $fun = NEW FUNCIONES;
                                                $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_uv_id']);
                                            }
                                            ?>
                                        </select>
                                    <?php 
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
                            <div id="CajaInput">
                                <div id="manzano">
                                    <?php
                                    if(isset($_GET['lote_reserva']) && $_GET['lote_reserva']!=''){
                                        $sql="select man_id as id,man_nro as nombre  from lote l, manzano m 
                                                where l.lot_man_id=m.man_id and l.lot_id='".$_GET['lote_reserva']."';";
                                        $mz=FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                            <select style="width:200px;" name="ven_man_id" id="ven_man_id" class="caja_texto" >
                                                <option value="<?php echo $mz->id;?>">Manzano Nro: <?php echo $mz->nombre;?></option>                                                                            
                                            </select>
                                    <?php  
                                    } else{
                                    ?>
                                        <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
                                            <option value="">Seleccione</option>
                                            <?php
                                            if ($_POST['ven_urb_id'] <> "") {
                                                $fun = NEW FUNCIONES;
                                                $fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_man_id']);
                                            }
                                            ?>
                                        </select>
                                    <?php 
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                            <div id="CajaInput">
                                <div id="lote">
                                    <?php
                                    if(isset($_GET['lote_reserva']) && $_GET['lote_reserva']!=''){
                                        $sql="select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre 
                                                from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_id='".$_GET['lote_reserva']."'";
                                        $mz=FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                            <select style="width:200px;" name="ven_lot_id" id="ven_lot_id" class="caja_texto" >
                                                <option value="<?php echo $mz->id;?>">Uv Nro: <?php echo $mz->nombre;?></option>                                                                            
                                            </select>                                            
                                    <?php  
                                    } else{
                                    ?>
                                        <select style="width:200px;" name="ven_lot_id" class="caja_texto">
                                            <option value="">Seleccione</option>
                                            <?php
                                            if ($_POST['ven_man_id'] <> "") {
                                                $fun = NEW FUNCIONES;
                                                $fun->combo("select lot_id as id,lot_nro as nombre from lote where lot_man_id='" . $_POST['ven_man_id'] . "' ", $_POST['ven_lot_id']);
                                            }
                                            ?>
                                        </select>
                                    <?php 
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>                        
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nota</div>
                            <div id="CajaInput">
                                <textarea class="area_texto" name="nota" id="nota" cols="31" rows="3"><?php echo $_POST['nota'] ?></textarea>
                            </div>
                        </div>

                        
                    </div>
                    <?php if(isset($_GET['lote_reserva']) && $_GET['lote_reserva']!=''){ ?>
                        <script>
                            cargar_datos($("#ven_lot_id option:selected").val());
                        </script>
                    <?php 
                    }
                    ?>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php
                                if (!($ver)) {
                                    ?>
                                                                <!--<input type="submit" class="boton" name="" value="Guardar">-->
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
            <script>
            var options1 = {
                script: "sueltos/suggest_persona_usuario.php?json=true&",
                varname: "input",
                minchars: 1,
                timeout: 10000,
                noresults: "No se encontro ninguna persona",
                json: true,
                callback: function(obj) {
                    document.getElementById('ven_int_id').value = obj.id;
                }
            };
            var as_json1 = new _bsn.AutoSuggest('int_nombre_persona', options1);
            </script>
        <?php } ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $("a.group").fancybox({
                    'hideOnContentClick': false,
                    'overlayShow': true,
                    'zoomOpacity': true,
                    'zoomSpeedIn': 300,
                    'zoomSpeedOut': 200,
                    'overlayOpacity': 0.5,
                    'frameWidth': 700,
                    'frameHeight': 350,
                    'type': 'iframe'
                });

                $('a.close').click(function() {
                    $(this).fancybox.close();
                });

            });
        </script>
        <script>
        jQuery(function($) {
            $("#fecha_plazo_final").mask("99/99/9999");                    
            });
        </script>
        <?php
    }

    function obtener_grupo_id($usu_id) {
        $conec = new ADO();

        $sql = "SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_gru_id;
    }

	function obtener_id_interno_tbl_usuario($usu_id){
		$conec= new ADO();
		
		$sql="SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->usu_per_id;
	}

    function insertar_reserva_pago($id_reserva, $monto, &$new_respag_id) {
        $conec = new ADO();

        $sql = "insert into reserva_pago (respag_monto,respag_fecha,respag_hora,respag_estado,respag_usu_id,respag_res_id) values ('" . $monto . "','" . date('Y-m-d') . "','" . date('H:i:s') . "','Pagado','" . $this->usu->get_id() . "','" . $id_reserva . "')";

        $conec->ejecutar($sql, false);

        $new_respag_id = mysql_insert_id();
    }
    
    
        function enviar_correo_caja($res_id) {

//            $ven_id = 1;
            
            $conec2 = new ADO();

            $sql2 = "SELECT * FROM ad_parametro";

            $conec2->ejecutar($sql2);
            $objeto2 = $conec2->get_objeto();

            require("clases/class.phpmailer.php");

            $mail = new PHPMailer();

            $mail->Mailer = "smtp";

            $mail->IsHTML(true);

//                $mail->Port = 465;
            $mail->Port = 25;
//            
//                $mail->SMTPSecure = "ssl"; //luego comentar, por que no estaba originalmente

            $mail->CharSet = "UTF-8";

            $mail->Host = "$objeto2->par_smtp";

            $mail->SMTPAuth = true;

            $mail->Username = "$objeto2->par_salida";

            $mail->Password = "$objeto2->par_pas_salida";

            $mail->From = "$objeto2->par_salida";

            $mail->FromName = "Sistema " . strip_tags(_nombre_empresa);

            $mail->Subject = utf8_encode("Nota de Reserva");

            $email = split(',', $objeto2->par_correo_caja);

            for ($i = 0; $i < count($email); $i++) {
                $mail->AddAddress($email[$i]);
            }


            $conec = new ADO();
            $sql = "select 
				res_fecha,res_usu_id,res_moneda,res_plazo_fecha,res_monto_referencial,res_vdo_id,(zon_precio*lot_superficie)as monto,res_int_id,res_estado,int_nombre,int_apellido,
				urb_nombre,man_nro,lot_nro,zon_precio,lot_superficie,zon_nombre,uv_nombre		
				from 
				reserva_terreno 
				inner join interno on(res_id = '$res_id' and res_int_id=int_id)
				inner join lote on(res_lot_id=lot_id)
				inner join zona on(lot_zon_id=zon_id)
				inner join uv on(lot_uv_id=uv_id)
				inner join manzano on (lot_man_id=man_id)                                
				inner join urbanizacion on (man_urb_id=urb_id)";
//            echo $sql;
            $conec->ejecutar($sql);
            $num = $conec->get_num_registros();
            $objeto = $conec->get_objeto();
            $myday = setear_fecha(strtotime($objeto->res_fecha));
            $fecha_plazo = setear_fecha(strtotime($objeto->res_plazo_fecha));;
//            $tc = $objeto->ven_tipo_cambio;
            $body = '<div id="contenido_reporte" style="clear:both;">
                        <center>
                        <table style="font-size:12px; border:1px solid #bfc4c9" cellpadding="5" cellspacing="0" width="100%">
                            <tbody><tr>
                            <td width="33%"><strong>' . _nombre_empresa . '</strong><br>

                            </td>';
            $body .= '
                    <td width="33%"><center><p align="center"><strong></strong></p><h3><strong>NOTA DE RESERVA</strong></h3><p></p></center></td>
            <td><div align="right"><img src="imagenes/micro_puertosantacruz.png"></div></td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong><br>Nro de Reserva: </strong>' . $res_id . '<br>';
            if ($objeto->res_int_id <> 0) {
                $persona = $objeto->int_nombre . ' ' . $objeto->int_apellido;
                $body .= '<strong>Persona: </strong>' . $persona . '<br>';
            }            
            
            $body .= '<strong>Estado: </strong>' . $objeto->res_estado . '<br>';
            $body .= '<strong>Moneda: </strong>';
            $moneda = $objeto->res_moneda;
            if ($moneda == "1")
                $body .= "Bolivianos";
            else
                $body .= "Dolares";

            $body .= '<br>';            
            $body .='<br><br>';
            $body .='</td>
        <td align="right">
                                <strong>Fecha: </strong>' . $myday;
            $body .= '<br>
                                <strong>Fecha Plazo: </strong>' . $fecha_plazo;
            $body .= '<br>
                                <strong>Vendedor: </strong>' . $this->nombre_vendedor($objeto->res_vdo_id);
            $body .= '<br>
                                <strong>Usuario: </strong>' . $this->nombre_persona($objeto->res_usu_id);
            $body .= '<br>
                            </td>
                        </tr>

                    </table>';
            $body .= '<table   width="100%" style="font-size:12px; border:1px solid #bfc4c9" class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="color:#333;font-size:13px;text-align:center;border-right:1px solid #87deff;background-color:#C3C8CD">Terreno</th>
                                <th style="color:#333;font-size:13px;text-align:center;border-right:1px solid #87deff;background-color:#C3C8CD">Superficie</th>
                                <th style="color:#333;font-size:13px;text-align:center;border-right:1px solid #87deff;background-color:#C3C8CD">Valor del m2</th>
                                <th style="color:#333;font-size:13px;text-align:center;border-right:1px solid #87deff;background-color:#C3C8CD">Valor del Terreno</th>                                
                                <th style="color:#333;font-size:13px;text-align:center;border-right:1px solid #87deff;background-color:#C3C8CD">Monto Referencial</th>';            
            $body .= '</tr>		
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-size:11px;border-bottom:1px dashed #d6d8d7;border-right:1px solid #cccccc;padding:3px 0px 3px 5px;">';
            $terreno = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
            $body .= $terreno;
            $valor_terreno = $objeto->lot_superficie * $objeto->zon_precio;
            $body .= '</td>
                                <td style="font-size:11px;border-bottom:1px dashed #d6d8d7;border-right:1px solid #cccccc;padding:3px 0px 3px 5px;">';
            $body .= $objeto->lot_superficie . 'm2</td>
                                <td style="font-size:11px;border-bottom:1px dashed #d6d8d7;border-right:1px solid #cccccc;padding:3px 0px 3px 5px;">';
            $body .= $objeto->zon_precio . '</td>
                                <td style="font-size:11px;border-bottom:1px dashed #d6d8d7;border-right:1px solid #cccccc;padding:3px 0px 3px 5px;">';
            $body .= $valor_terreno . '</td>            
                                <td style="font-size:11px;border-bottom:1px dashed #d6d8d7;border-right:1px solid #cccccc;padding:3px 0px 3px 5px;">';
            $body .= $objeto->res_monto_referencial . '</td>';            
            $body .= '</tr>	
                        </tbody>
                    </table>';

            $mail->Body = $body;

//    if (!$mail->Send()) {
//        $mensaje = "La nota de venta no pudo ser enviada. Reintente Denuevo por favor!";
//    } else {
//        $mensaje = "La nota de venta ha sido enviada Correctamente.";
//    }
//    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            
//            echo $body;
            $mail->Send();
        }
        

    function insertar_tcp() {
//        $cta_a=FUNCIONES::cuenta_analitica_persona($_POST['ven_int_id']);
//        return;
        $conec = new ADO();
//        $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "' and cja_estado='1'";
//        $conec->ejecutar($sql);
//        $nume = $conec->get_num_registros();
        if (true) {
            $valores = explode("-", $_POST['ven_lot_id']);
            $id_lote = $valores[0]; // piece1
            //echo $pieces[1]; // piece2

            $verificar = NEW VERIFICAR;

            $parametros[0] = array('res_lot_id', 'res_estado');
            $parametros[1] = array($id_lote, 'Habilitado');
            $parametros[2] = array('reserva_terreno');
            
            $exito = true;

            if ($verificar->validar($parametros)) {
                $conec = new ADO();
                $conv = new convertir();
                $_POST['ven_moneda']=2;
                
                $fecha_plazo=date('Y-m-d');
                $hora=date('H:i:s');
                $dias_plazo=3;
                $j = 0;
                while($j < $dias_plazo){
                    $fecha_plazo=  FUNCIONES::sumar_dias(1, $fecha_plazo);
                    $f=new DateTime($fecha_plazo);
                    if($f->format('w')!=6 && $f->format('w')!=5){
                        $j++;
                    }
                }
                
                $sql = "insert into reserva_terreno (res_int_id,res_vdo_id,res_lot_id,
                                                    res_fecha,res_hora,res_monto_referencial,
                                                    res_moneda,res_estado,res_usu_id,
                                                    res_plazo_fecha,res_plazo_hora,res_nota, res_vdo_ext,
                                                    res_vdo_iii,res_urb_id
                                                    ) 
                                                    
                                                    values ('" . $_POST['ven_int_id'] . "','" . $_POST['vendedor'] . "','" . $id_lote . "',
                                                    '" . date('Y-m-d') . "','" . $hora . "','" . $_POST['ven_monto_referencial'] . "',
                                                    '" . $_POST['ven_moneda'] . "','Pendiente','" . $this->usu->get_id() . "',
                                                    '" . $fecha_plazo ."','" . $hora . "','" . $_POST['nota'] . "','$_POST[vendedor2]',
                                                    '$_POST[vendedor3]','$_POST[ven_urb_id]'
                                                    )";


                $conec->ejecutar($sql, false);
                $llave = mysql_insert_id();


                
                
                //Cambio Estado de 'Reservado' el Lote.
                $sql = "update lote set lot_estado='Reservado' where lot_id=$id_lote";

                $conec->ejecutar($sql);
//                $sql = "SELECT 
//			res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_estado,res_usu_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,uv_nombre,zon_nombre 
//			FROM 
//			reserva_terreno 
//			inner join interno on (res_int_id=int_id)
//			inner join lote on (res_lot_id=lot_id)
//			inner join uv on (uv_id=lot_uv_id)
//			inner join manzano on (lot_man_id=man_id)
//			inner join urbanizacion on (man_urb_id=urb_id)
//			inner join zona on (zon_urb_id=urb_id) WHERE res_id=" . $llave;

//                $conec->ejecutar($sql);

//                $objeto = $conec->get_objeto();

                //Historial//	
//                include_once("clases/registrar_historial.class.php");

//                $historial = new HISTORIAL();

//                $historial->agregar_historial(date('Y-m-d'), date("H:i:s"), 'Reserva de Terreno Nro:' . $llave . ' (Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre . ')', $_POST['ven_int_id']);
                //Fin Historial//

                //$this->enviar_correo_caja($llave);
                $mensaje = 'Reserva de Terreno Agregado Correctamente';
            } else {
                $exito = false;
                $mensaje = 'No se pudo reservar el Terreno, por que el Lote que seleccion?, ya se encuentra Reservado.';
            }
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            if($exito){
                $this->nota_reserva($llave);
            }
        } else {

            $mensaje = 'No puese realizar reserva de terreno, por que usted no esta registrado como cajero.';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }
    function frm_anular(){
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=reserva&tarea=ANULAR&id=$_GET[id]";?>" method="POST" enctype="multipart/form-data">
            <div id="ContenedorDiv">
                <div class="Etiqueta" >Fecha de Anulaci&oacute;n</div>
                <div id="CajaInput">
                    <input type="text" id="fecha" name="fecha" value="<?php echo date('d/m/Y');?>">
                </div>
            </div>
             <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>
                        <input type="button" class="boton" name="" value="Guardar" onclick="guardar_fecha();">
                        <input type="button" class="boton" name="" value="Volver" onClick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                    </center>
                </div>
            </div>
        </form>
        <script>
            $('#fecha').mask('99/99/9999');
            function guardar_fecha(){
                var fecha =$('#fecha').val();
                if(trim(fecha)===''){
                    $.prompt('Ingrese la fecha de Retencion');
                    return false;
                }

                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
                        document.frm_sentencia.submit();
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });


            }
        </script>
        <?php
    }
    function anular() {
		if(false){
                        $mensaje = 'No se puede Deshabilitar la reserva por que ya tiene pagos registrados!!!';
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,"","error");
		}else{
			$conec = new ADO();
                        $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$_GET[id]'");
//			$sql = "select res_estado from reserva_terreno where res_id='" . $_GET['id'] . "'";
//			$conec->ejecutar($sql);
//			$objeto = $conec->get_objeto();
			$estado = $reserva->res_estado;
			if ($estado == 'Pendiente' || $estado == 'Habilitado') {
//				$sql = "select res_lot_id from reserva_terreno where res_id='" . $_GET['id'] . "'";
//				$conec->ejecutar($sql);
//				$objeto = $conec->get_objeto();
				$lote = $reserva->res_lot_id;
				$sql = "update lote set lot_estado='Disponible' where lot_id='$lote'";
				$conec->ejecutar($sql);
				$sql = "update reserva_terreno set res_estado='Deshabilitado' where res_id='" . $_GET['id'] . "'";
				$conec->ejecutar($sql);
                                
                                $monto_pagado=  FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo from reserva_pago where respag_res_id='$reserva->res_id' and respag_estado='Pagado'");
                                $fecha_cmp=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
                                if($monto_pagado){

//                                    include_once 'clases/registrar_comprobantes.class.php';
//                                    $ges_id=$_SESSION['ges_id'];
//                                    $glosa="Anulacion de Reserva Nro. ".$reserva->res_id;
//                                    $comprobante = new stdClass();
//                                    $comprobante->tipo = "Diario";
//                                    $comprobante->mon_id = $reserva->res_moneda;
//                                    $comprobante->nro_documento = date("Ydm");
//                                    $comprobante->fecha = $fecha_cmp;
//                                    $comprobante->ges_id = $ges_id;
//                                    $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
//                                    $comprobante->forma_pago='Efectivo';
//                                    $comprobante->ban_id='';
//                                    $comprobante->ban_char='';
//                                    $comprobante->ban_nro='';
//                                    $comprobante->glosa =$glosa;
//                                    $comprobante->referido = FUNCIONES::atributo_bd("interno", "int_id='$reserva->res_int_id'", "concat(int_nombre,' ',int_apellido)");
//                                    $comprobante->tabla = "reserva";
//                                    $comprobante->tabla_id = $reserva->res_id;
//                                    
//                                    $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '2.1.1.05.2.01'),"debe"=>$monto_pagado,"haber"=>0,
//                                                    "glosa"=>$glosa,"ca"=> FUNCIONES::get_cuenta_ca($ges_id, '01.00001'),"cf"=> '0',"cc"=>'0','int_id'=>$reserva->res_int_id
//                                            );
//                                    $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.04'),"debe"=>0,"haber"=>$monto_pagado,
//                                                    "glosa"=>$glosa,"ca"=> FUNCIONES::get_cuenta_ca($ges_id, '01.00001'),"cf"=> '0',"cc"=>  FUNCIONES::get_cuenta_cc($ges_id, '1.03'),'int_id'=>$reserva->res_int_id
//                                            );
//                                    COMPROBANTES::registrar_comprobante($comprobante);
                                }
                                
				$mensaje = 'La reserva de terrenos fue deshabilitada correctamente!!!';
//				$sql = "SELECT 
//                                        res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_estado,res_usu_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,uv_nombre,zon_nombre 
//                                        FROM 
//                                        reserva_terreno 
//                                        inner join interno on (res_int_id=int_id)
//                                        inner join lote on (res_lot_id=lot_id)
//                                        inner join uv on (uv_id=lot_uv_id)
//                                        inner join manzano on (lot_man_id=man_id)
//                                        inner join urbanizacion on (man_urb_id=urb_id)
//                                        inner join zona on (zon_urb_id=urb_id) WHERE res_id=" . $_GET['id'];
//				$conec->ejecutar($sql);
//				$objeto = $conec->get_objeto();
//				include_once("clases/registrar_historial.class.php");
//				$historial = new HISTORIAL();
//				$historial->agregar_historial(date('Y-m-d'), date("H:i:s"), 'Anulacion de Reserva de Terreno Nro:' . $_GET['id'] . ' (Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre . ')', $objeto->res_int_id);
				
			} 
			else {
				$mensaje = 'No se pudo <b>Deshabilitar la Reserva de Terreno</b> por que la Reserva ya se encuentra en estado <b>Deshabilitado, Expirado o Concretado</b>';
			}

			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);		
		}
    }

    function nombre_persona_vendedor($vdo_id) {
        $conec = new ADO();

        $sql = "SELECT int_nombre,int_apellido FROM interno
		inner join vendedor on (vdo_int_id=int_id) 
		WHERE vdo_id='$vdo_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function obtener_urbanizacion_reserva($id_reserva) {
        $conec = new ADO();

        $sql = "SELECT urb_id from urbanizacion
			inner join uv on (uv_urb_id=urb_id)
			inner join lote on (lot_uv_id=uv_id)
			inner join reserva_terreno on (res_lot_id=lot_id)
			where res_id=$id_reserva";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->urb_id;
    }

    function cuenta_reserva($urbanizacion, $tipo, &$cue, &$cco) {
        $conec = new ADO();

        $sql = "select urb_cue_res_cobro,urb_cue_res_dev,urb_cco_id from urbanizacion where urb_id=$urbanizacion";

//        echo $sql;

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        if ($tipo == 'Ingreso') {
            $cue = $objeto->urb_cue_res_cobro;

            $cco = $objeto->urb_cco_id;
        } else {
            if ($tipo == 'Egreso') {
                $cue = $objeto->urb_cue_res_dev;

                $cco = $objeto->urb_cco_id;
            }
        }
    }

    function descripcion_terreno(&$des) {
        $conec = new ADO();

        $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
		from 
		lote
		inner join zona on (lot_id='" . $_POST['ven_lot_id'] . "' and lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)	
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on(man_urb_id=urb_id)
		";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $des = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
    }

//--------------------------------------------- TAREA - CONCRETAR VENTA RESERVA ----------------------------------------//

    function cambiar_estado_reserva($id_reserva, $nuevo_estado) {
        $conec = new ADO();

        $sql = "update reserva_terreno set res_estado='$nuevo_estado' where res_id=$id_reserva";

        $conec->ejecutar($sql);
    }

    function get_estado_reserva($id_reserva) {
        $conec = new ADO();

        $sql = "select res_estado from reserva_terreno where res_id=$id_reserva";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->res_estado;
    }

    function cargar_datos_reserva() {
        $conec = new ADO();

        $sql = "SELECT 		res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_estado,res_usu_id,int_nombre,int_apellido,urb_id,urb_nombre,man_id,man_nro,lot_id,lot_nro,lot_tipo,uv_id,uv_nombre 
			FROM 
			reserva_terreno 
			inner join interno on (res_int_id=int_id)
			inner join lote on (res_lot_id=lot_id)
			inner join uv on (uv_id=lot_uv_id)
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id) where res_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['ven_int_id'] = $objeto->res_int_id;

        $_POST['int_nombre_persona'] = $objeto->int_nombre . ' ' . $objeto->int_apellido;

        $_POST['vendedor'] = $objeto->res_vdo_id;

        $_POST['ven_urb_id'] = $objeto->urb_id;

        $_POST['ven_man_id'] = $objeto->man_id;

        $_POST['ven_uv_id'] = $objeto->uv_id;

        $_POST['ven_lot_id'] = $objeto->res_lot_id;


        $sql = "select SUM(respag_monto) as monto_anticipo from reserva_pago where respag_res_id=" . $_GET['id'] . " and respag_estado='Pagado'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        //$_POST['ven_anticipo']=$objeto->res_anticipo;
        $_POST['ven_anticipo'] = $objeto->monto_anticipo;



        $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,zon_color,cast(lot_nro as SIGNED) as numero from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_man_id='" . $_POST['ven_man_id'] . "' and lot_uv_id='" . $_POST['ven_uv_id'] . "' and lot_estado='Reservado' and lot_id='" . $_POST['ven_lot_id'] . "' order by numero asc";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['datos_lote'] = $objeto->id;
    }

    function guardar_venta_reserva() {
        if ($this->get_estado_reserva($_POST['aux_id_reserva']) == 'Habilitado') {
            include_once("modulos/venta/venta.class.php");

            $venta = new VENTA();

            $conversor = new convertir();

            $conec = new ADO();

            if ($_POST['ven_tipo'] == "Contado") {
                $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "' and cja_estado='1'";

                $conec->ejecutar($sql);

                $nume = $conec->get_num_registros();

                if ($nume > 0) {
                    $obj = $conec->get_objeto();

                    $caja = $obj->cja_cue_id;

                    //$esta="Pagado";
                    $esta = "Pendiente por Cobrar";

                    $sql = "insert into venta(ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_monto,ven_estado,ven_tipo_cambio,ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,ven_valor,ven_decuento,ven_metro,ven_superficie,ven_interes,ven_co_propietario,ven_vdo_id) values 
										('" . $_POST['ven_int_id'] . "','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . date('H:i:s') . "','" . $_POST['ven_moneda'] . "','" . $_POST['monto'] . "','" . $esta . "','" . $_POST['tca'] . "','" . $this->usu->get_id() . "','" . $_POST['ven_tipo'] . "','" . $_POST['lote_hidden'] . "','" . $_POST['cuota_inicial'] . "','" . $_POST['meses_plazo'] . "','" . $_POST['ven_observacion'] . "','" . $_POST['valor_terreno'] . "','" . $_POST['descuento'] . "','" . $_POST['valor'] . "','" . $_POST['superficie'] . "','No','" . $_POST['ven_co_propietario'] . "','" . $_POST['vendedor'] . "')";

                    $conec->ejecutar($sql, false);

                    $llave = mysql_insert_id();

                    /*
                      if($_POST['vendedor']<>"")
                      {
                      //Obtengo Tipo de Comision del Vendedor (Porcentaje,Monto especifico, Metro Cuadrado)
                      $tipo_comision=$venta->obtener_tipo_comision($_POST['vendedor'],$_POST['ven_urb_id']);

                      if($tipo_comision<>'')
                      {
                      //Obtengo datos de los Montos de comision
                      $venta->obtener_porcentaje_comision($_POST['vendedor'],$porcentaje_contado,$porcentaje_credito,$tipo_comision,$_POST['ven_urb_id']);

                      $comision=$venta->obtener_comision_venta($_POST['monto'],$porcentaje_contado,$tipo_comision,$_POST['superficie']);


                      $sql="INSERT INTO `comision` (
                      `com_ven_id` ,
                      `com_vdo_id` ,
                      `com_monto` ,
                      `com_moneda` ,
                      `com_estado` ,
                      `com_fecha_cre`
                      )
                      VALUES (
                      '$llave', '".$_POST['vendedor']."', '$comision', '".$_POST['ven_moneda']."', 'Pendiente', '".date('Y-m-d')."'
                      );

                      ";

                      $conec->ejecutar($sql);
                      }
                      }
                     */

                    ///**REFLEJO EN LAS CUENTAS**///

                    /*
                      include_once("clases/registrar_comprobantes.class.php");

                      $comp = new COMPROBANTES();


                      $mt=$_POST['monto'];


                      $cmp_id = $comp->ingresar_comprobante($conversor->get_fecha_mysql($_POST['ven_fecha']),$_POST['tca'],$_POST['ven_moneda'],"",$_POST['ven_int_id'],$this->usu->get_id(),'1','1','venta',$llave);


                      if($_POST['ven_moneda']=='1')
                      {
                      $comp->ingresar_detalle($cmp_id,$mt,$caja,0);

                      $venta->cuenta_urbanizacion($_POST['ven_urb_id'],$cue,$cco);

                      $venta->descripcion_terreno($des);

                      $comp->ingresar_detalle($cmp_id,($mt * (-1)),$cue,$cco,'Compra al contado '.$des);
                      }
                      else
                      {

                      $comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mt);

                      $venta->cuenta_urbanizacion($_POST['ven_urb_id'],$cue,$cco);

                      $venta->descripcion_terreno($des);

                      $comp->ingresar_detalle($cmp_id,0,$cue,$cco,'Compra al contado '.$des,($mt * (-1)));
                      }
                     */

                    ///**FIN REFLEJO**///

                    $sql = "update lote set lot_estado='Vendido' where lot_id='" . $_POST['lote_hidden'] . "'";

                    $conec->ejecutar($sql);

                    ///
                    //Historial//
                    include_once("clases/registrar_historial.class.php");

                    $historial = new HISTORIAL();

                    $_POST['ven_lot_id'] = $_POST['lote_hidden'];

                    $venta->descripcion_terreno($des);

                    $historial->agregar_historial(date('Y-m-d'), date("H:i:s"), 'Reserva de Terreno Nro: ' . $_POST['aux_id_reserva'] . ' Concretada - (' . $des . ') - (Venta Nro: ' . $llave . ' Tipo: ' . $_POST['ven_tipo'] . ')', $_POST['ven_int_id']);
                    //Fin Historial//

                    $this->cambiar_estado_reserva($_POST['aux_id_reserva'], 'Concretado');

                    $venta->nota_de_venta($llave);
                } else {
                    $mensaje = 'No puese realizar ninguna venta, por que usted no esta registrado como cajero.';

                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                }
            } else {
                if ($_POST['ven_tipo'] == "Credito") {
                    $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "' and cja_estado='1'";

                    $conec->ejecutar($sql);

                    $nume = $conec->get_num_registros();

                    if ($nume > 0) {
                        $obj = $conec->get_objeto();

                        $caja = $obj->cja_cue_id;

                        $esta = "Pendiente";

                        $sql = "insert into venta(ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_monto,ven_estado,ven_tipo_cambio,ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,ven_valor,ven_decuento,ven_metro,ven_superficie,ven_interes,ven_co_propietario,ven_rango_mes,ven_vdo_id) values 
											('" . $_POST['ven_int_id'] . "','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . date('H:i:s') . "','" . $_POST['ven_moneda'] . "','" . $_POST['monto'] . "','" . $esta . "','" . $_POST['tca'] . "','" . $this->usu->get_id() . "','" . $_POST['ven_tipo'] . "','" . $_POST['lote_hidden'] . "','" . $_POST['cuota_inicial'] . "','" . $_POST['meses_plazo'] . "','" . $_POST['ven_observacion'] . "','" . $_POST['valor_terreno'] . "','" . $_POST['descuento'] . "','" . $_POST['valor'] . "','" . $_POST['superficie'] . "','" . $_POST['con_interes'] . "','" . $_POST['ven_co_propietario'] . "','" . $_POST['rango_mes'] . "','" . $_POST['vendedor'] . "')";

                        $conec->ejecutar($sql, false);

                        $llave = mysql_insert_id();

                        if ($_POST['vendedor'] <> "") {
                            //-- Comision --//
                            //Obtengo Tipo de Comision del Vendedor (Porcentaje,Monto especifico, Metro Cuadrado)
                            $tipo_comision = $venta->obtener_tipo_comision($_POST['vendedor'], $_POST['ven_urb_id']);

                            if ($tipo_comision <> '') {
                                //Obtengo datos de los Montos de comision
                                $venta->obtener_porcentaje_comision($_POST['vendedor'], $porcentaje_contado, $porcentaje_credito, $tipo_comision, $_POST['ven_urb_id']);

                                $comision = $venta->obtener_comision_venta($_POST['monto'], $porcentaje_credito, $tipo_comision, $_POST['superficie']);


                                $sql = "INSERT INTO `comision` (
								`com_ven_id` ,
								`com_vdo_id` ,
								`com_monto` ,
								`com_moneda` ,
								`com_estado` ,
								`com_fecha_cre`
								)
								VALUES (
								'$llave', '" . $_POST['vendedor'] . "', '$comision', '" . $_POST['ven_moneda'] . "', 'Pendiente', '" . date('Y-m-d') . "'
								);
			
								";

                                $conec->ejecutar($sql);
                            }
                            //-- Comision --//
                        }

                        $venta->descripcion_terreno($des);

                        $venta->cuenta_urbanizacion($_POST['ven_urb_id'], $cue, $cco);

                        $venta->cuenta_urbanizacion_formulario($_POST['ven_urb_id'], $cue_urb_form, $cco_urb_form, $valor_form);


                        $saldo = $_POST['monto'] - $_POST['cuota_inicial'];
                        $moneda = $_POST['ven_moneda'];
                        $tc = $_POST['tca'];


                        $valor_form = $valor_form;


                        if ($_POST['cuota_inicial'] > 0) {
                            $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_estado,ind_valor_form)
								values('" . $_POST['ven_int_id'] . "','" . $_POST['cuota_inicial'] . "','" . $_POST['ven_moneda'] . "','Cuota Inicial - $des ','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . $this->usu->get_id() . "','" . $cue . "','" . $cco . "','venta','" . $llave . "','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','0','" . $_POST['cuota_inicial'] . "','$saldo','Pendiente','" . $valor_form . "')";

                            $conec->ejecutar($sql);
                        }

                        ///generacion de cuotas
                        //$sql="select par_interes_mensual from ad_parametro";
                        $sql = "select urb_interes_anual from urbanizacion where urb_id=" . $_POST['ven_urb_id'];

                        $conec->ejecutar($sql);

                        $objeto = $conec->get_objeto();

                        if ($_POST['con_interes'] == 'Si') {
                            //$interes_mensual=($objeto->par_interes_mensual/12)/100;
                            if ($_POST['rango_mes'] == 1)
                                $interes_mensual = ($objeto->urb_interes_anual / 12) / 100;
                            if ($_POST['rango_mes'] == 2)
                                $interes_mensual = ($objeto->urb_interes_anual / 6) / 100;
                            if ($_POST['rango_mes'] == 3)
                                $interes_mensual = ($objeto->urb_interes_anual / 4) / 100;
                            if ($_POST['rango_mes'] == 6)
                                $interes_mensual = ($objeto->urb_interes_anual / 2) / 100;
                            if ($_POST['rango_mes'] == 12)
                                $interes_mensual = ($objeto->urb_interes_anual / 1) / 100;
                        }
                        else
                            $interes_mensual = 0;

                        $fecha = $conversor->get_fecha_mysql($_POST['ven_fecha']);

                        if ($_POST['cuota_mensual'] <> "" && $_POST['meses_plazo'] == "" && $_POST['mes_cuota'] == "") {
                            $cuota = $_POST['cuota_mensual'];

                            $i = 0;

                            while ($saldo > 0) {
                                $i++;

                                $fecha = $venta->calcular_fecha(+$_POST['rango_mes'], $fecha);

                                $interes = $saldo * $interes_mensual;

                                if ($saldo < $cuota) {
                                    $capital = $saldo;

                                    $cuota = $capital + $interes;
                                } else {
                                    $capital = $cuota - $interes;
                                }

                                $saldo = $saldo - $capital;

                                $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form)
									values('" . $_POST['ven_int_id'] . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . $i . " - $des ','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . $this->usu->get_id() . "','" . $cue . "','" . $cco . "','venta','" . $llave . "','$fecha','$interes','$capital','$saldo','" . $valor_form . "')
									";

                                $conec->ejecutar($sql);

                                //Inserto el Monto de Cuota Mensual Auxiliar - 'Reporte Control de Mora'
                                if ($i == 1) {
                                    $sql = "update venta set ven_cuotam_aux='" . ($cuota + $valor_form) . "' where ven_id='$llave'";

                                    $conec->ejecutar($sql);
                                }
                            }

                            $sql = "update venta set ven_meses_plazo='$i' where ven_id='$llave'";

                            $conec->ejecutar($sql);
                        } else {
                            if ($_POST['cuota_mensual'] == "" && $_POST['meses_plazo'] <> "" && $_POST['mes_cuota'] == "") {

                                if ($_POST['con_interes'] == 'Si') {
                                    //$objeto->par_interes_mensual=$objeto->par_interes_mensual;
                                    if ($_POST['rango_mes'] == 1)
                                        $objeto->urb_interes_anual = $objeto->urb_interes_anual;
                                    if ($_POST['rango_mes'] == 2)
                                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 2);
                                    if ($_POST['rango_mes'] == 3)
                                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 3);
                                    if ($_POST['rango_mes'] == 6)
                                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 6);
                                    if ($_POST['rango_mes'] == 12)
                                        $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 12);
                                }
                                else
                                    $objeto->urb_interes_anual = 0;

                                $cuota = $venta->cuota($saldo, $objeto->urb_interes_anual, $_POST['meses_plazo']);

                                for ($i = 1; $i <= $_POST['meses_plazo']; $i++) {
                                    $fecha = $venta->calcular_fecha(+$_POST['rango_mes'], $fecha);

                                    $interes = $saldo * $interes_mensual;

                                    $capital = $cuota - $interes;

                                    $saldo = $saldo - $capital;

                                    $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form)
										values('" . $_POST['ven_int_id'] . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . $i . " - $des ','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . $this->usu->get_id() . "','" . $cue . "','" . $cco . "','venta','" . $llave . "','$fecha','$interes','$capital','$saldo','" . $valor_form . "')
										";

                                    $conec->ejecutar($sql);

                                    //Inserto el Monto de Cuota Mensual Auxiliar - 'Reporte Control de Mora'
                                    if ($i == 1) {
                                        $sql = "update venta set ven_cuotam_aux='" . ($cuota + $valor_form) . "' where ven_id='$llave'";

                                        $conec->ejecutar($sql);
                                    }
                                }
                            } else {
                                if ($_POST['cuota_mensual'] <> "" && $_POST['meses_plazo'] == "" && $_POST['mes_cuota'] <> "") {
                                    if ($_POST['con_interes'] == 'Si') {
                                        //$objeto->par_interes_mensual=$objeto->par_interes_mensual;
                                        if ($_POST['rango_mes'] == 1)
                                            $objeto->urb_interes_anual = $objeto->urb_interes_anual;
                                        if ($_POST['rango_mes'] == 2)
                                            $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 2);
                                        if ($_POST['rango_mes'] == 3)
                                            $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 3);
                                        if ($_POST['rango_mes'] == 6)
                                            $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 6);
                                        if ($_POST['rango_mes'] == 12)
                                            $objeto->urb_interes_anual = ($objeto->urb_interes_anual * 12);
                                    }
                                    else
                                        $objeto->urb_interes_anual = 0;

                                    //$cuota=cuota($saldo,$objeto->par_interes_mensual,$_POST['meses_plazo']);
                                    $cuota = $_POST['cuota_mensual'];

                                    $meses_plazo = $_POST['mes_cuota'];

                                    for ($i = 0; $i < $_POST['mes_cuota']; $i++) {
                                        if (($_POST['mes_cuota'] - $i) > 1) {
                                            $fecha = $venta->calcular_fecha(+$_POST['rango_mes'], $fecha);

                                            $interes = $saldo * $interes_mensual;

                                            if ($saldo <= $cuota) {
                                                $capital = $saldo;

                                                $cuota = $capital + $interes;
                                            } else {
                                                $capital = $cuota - $interes;
                                            }

                                            $saldo = $saldo - $capital;

                                            $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form)
										values('" . $_POST['ven_int_id'] . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . ($i + 1) . " - $des ','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . $this->usu->get_id() . "','" . $cue . "','" . $cco . "','venta','" . $llave . "','$fecha','$interes','$capital','$saldo','" . $valor_form . "')
										";

                                            $conec->ejecutar($sql);

                                            //Inserto el Monto de Cuota Mensual Auxiliar - 'Reporte Control de Mora'
                                            if ($i == 0) {
                                                $sql = "update venta set ven_cuotam_aux='" . ($cuota + $valor_form) . "' where ven_id='$llave'";

                                                $conec->ejecutar($sql);
                                            }
                                        } else {
                                            $cuota = $saldo;

                                            $fecha = $venta->calcular_fecha(+$_POST['rango_mes'], $fecha);

                                            $interes = $saldo * $interes_mensual;

                                            if ($saldo <= $cuota) {
                                                $capital = $saldo;

                                                $cuota = $capital + $interes;
                                            } else {
                                                $capital = $cuota - $interes;
                                            }

                                            $saldo = $saldo - $capital;

                                            $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form)
										values('" . $_POST['ven_int_id'] . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . ($i + 1) . " - $des ','" . $conversor->get_fecha_mysql($_POST['ven_fecha']) . "','" . $this->usu->get_id() . "','" . $cue . "','" . $cco . "','venta','" . $llave . "','$fecha','$interes','$capital','$saldo','" . $valor_form . "')
										";

                                            $conec->ejecutar($sql);

                                            //Inserto el Monto de Cuota Mensual Auxiliar - 'Reporte Control de Mora'
                                            if ($i == 0) {
                                                $sql = "update venta set ven_cuotam_aux='" . ($cuota + $valor_form) . "' where ven_id='$llave'";

                                                $conec->ejecutar($sql);
                                            }
                                        }

                                        if ($saldo <= 0)
                                            break;
                                    }
                                }
                            }
                        }

                        $sql = "update lote set lot_estado='Vendido' where lot_id='" . $_POST['lote_hidden'] . "'";

                        $conec->ejecutar($sql);

                        //Actualizo Campo 'Valor Formulario' para usarlo en una Amortizacion
                        $sql = "update venta set ven_valor_form=$valor_form where ven_id='" . $llave . "'";

                        $conec->ejecutar($sql);

                        //Historial//
                        include_once("clases/registrar_historial.class.php");

                        $historial = new HISTORIAL();

                        $_POST['ven_lot_id'] = $_POST['lote_hidden'];

                        $venta->descripcion_terreno($des);

                        $historial->agregar_historial(date('Y-m-d'), date("H:i:s"), 'Reserva de Terreno Nro: ' . $_POST['aux_id_reserva'] . ' Concretada - (' . $des . ') - (Venta Nro: ' . $llave . ' Tipo: ' . $_POST['ven_tipo'] . ' Fecha de Venta: ' . $_POST['ven_fecha'] . ')', $_POST['ven_int_id']);
                        //Fin Historial//

                        $venta->nota_de_venta($llave);

                        $this->cambiar_estado_reserva($_POST['aux_id_reserva'], 'Concretado');
                    }
                    else {
                        $mensaje = 'No puese realizar ninguna venta, por que usted no esta registrado como cajero.';

                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                    }
                }
            }
        } else {
            $mensaje = 'No puede realizar la Venta, por que la Reserva que pertenece al Terreno seleccionado ya se encuentra en estado <b>Deshabilitado</b>, <b>Expirado</b> o <b>Concretado</b>.';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }

    

    //----------------------------------------- TAREA - ANTICIPOS -------------------------------------------------------//


    function guardar_anticipo() {
        $conec = new ADO();
        $sql = "select * from reserva_terreno where res_id='" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $estado = $objeto->res_estado;
        if ($this->usu->get_id()=='admin' || $estado == 'Habilitado' || $estado == 'Pendiente') {
            if (true) {
                $fecha=  FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);
                $respag_tipopago=$_POST['respag_tipopago'];
                $tipo_comprobante=1;
                if($respag_tipopago=='Efectivo'|| $respag_tipopago==''){
                    $respag_ban_id=0;
                    $respag_ban_char='';
                    $respag_ban_nro='';
                }  elseif ($respag_tipopago=='Cheque') {            
                    if($tipo_comprobante==1){
                        $respag_ban_id=0;
                        $respag_ban_char=$_POST['respag_ban_char'];        
                        $respag_ban_nro=$_POST['respag_ban_nro'];                
                    }  elseif ($tipo_comprobante==2) {
                        $respag_ban_id=$_POST['respag_ban_id'];
                        $respag_ban_char='';
                        $respag_ban_nro=$_POST['respag_ban_nro'];
                    }

                } elseif($respag_tipopago=='Deposito'){
                    if($tipo_comprobante==1){
                        $respag_ban_id=$_POST['respag_ban_id'];
                        $respag_ban_char='';
                        $respag_ban_nro=$_POST['respag_ban_nro'];               
                    }  elseif ($tipo_comprobante==2) {
                        $respag_ban_id=0;
                        $respag_ban_char=$_POST['respag_ban_char'];        
                        $respag_ban_nro=$_POST['respag_ban_nro'];
                    }

                } elseif ($respag_tipopago=='Transferencia' ) {
                    $respag_ban_id=$_POST['respag_ban_id'];        
                    $respag_ban_char=$_POST['respag_ban_char'];        
                    $respag_ban_nro=$_POST['respag_ban_nro'];
                }
//                $this->obtener_datos_reserva($_GET['id'], $moneda, $interno, $res_lot_id);
                $reserva=  FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
                $moneda = $reserva->res_moneda;
//                $interno = $reserva->res_int_id;
                $res_lot_id = $reserva->res_lot_id;
                $_POST['ven_lot_id'] = $res_lot_id;
                $this->descripcion_terreno($des);
                $glosa=$des;
                $sql = "insert into reserva_pago(respag_monto,respag_moneda,respag_fecha,respag_hora,
                                                respag_estado,respag_usu_id,respag_glosa,respag_res_id,
                                                respag_tipopago,respag_ban_id,respag_ban_char,
                                                respag_ban_nro) 
                                        values (
                                                '" . $_POST['respag_monto'] . "','".$_POST['moneda']."','" . $fecha . "','" . date('H:i:s') . "',
                                                'Pagado','" . $this->usu->get_id() . "','$glosa','" . $_GET['id'] . "',
                                                '" . $respag_tipopago . "','" . $respag_ban_id . "','" . $respag_ban_char . "',
                                                '" . $respag_ban_nro . "')";
                //echo $sql;
                $conec->ejecutar($sql, false);
                
                $llave = mysql_insert_id();

//                $interno=FUNCIONES::atributo_bd("interno", "int_id=".$_POST['ven_int_id'], "concat(int_nombre,' ',int_apellido )");
                $fecha_cmp=  FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);
                $monto=$_POST['respag_monto']*1;
                $urbanizacion=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$reserva->res_urb_ib'");
                $interno=FUNCIONES::atributo_bd("interno", "int_id='$reserva->res_int_id'", "concat(int_nombre,' ',int_apellido)");
                include_once 'clases/registrar_comprobantes.class.php';
                include_once 'clases/modelo_comprobantes.class.php';
                $glosa = "Pago de Reserva: " . $glosa;
                
                $params=array(
                        'tabla'=>'reserva_pago',
                        'tabla_id'=>$llave,
                        'fecha'=>$fecha_cmp,
                        'moneda'=>$moneda,
                        'ingreso'=>true,
                        'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
                    );
                $detalles = FORMULARIO::insertar_pagos($params);
                
                $data=array(
                    'moneda'=>$moneda,
                    'ges_id'=>$_SESSION[ges_id],
                    'fecha'=>$fecha_cmp,
                    'glosa'=>$glosa,
                    'interno'=>$interno,
                    'tabla_id'=>$llave,
                    'urb'=>$urbanizacion,
                    'monto'=>$monto,
                    'detalles'=>$detalles,
                );
                        
                $comprobante = MODELO_COMPROBANTE::anticipo($data);
                
                
                COMPROBANTES::registrar_comprobante($comprobante);
                
                if($estado == 'Pendiente'){
                    $sqlres = "update reserva_terreno set res_estado='Habilitado' where res_id='".$_GET['id']."'";
                    $conec->ejecutar($sqlres);
                }
                
//                $this->nota_comprobante_reserva($cmp_id);                
                $this->ver_comprobante($llave);
            } else {
                $mensaje = 'No puese realizar Anticipos, por que usted no esta registrado como cajero.';

                $this->mensaje = $mensaje;

                if ($this->mensaje <> "") {
                    $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
                }
            }
        } else {


            $mensaje = 'No se pudo <b>Ingresar Anticipo</b> por que la Reserva ya se encuentra en estado <b>Deshabilitado, Expirado o Concretado</b>';

            $this->mensaje = $mensaje;

            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
            }
        }
    }

    function num2letras($num, $fem = false, $dec = true) {
        /* ! 
          @function num2letras ()
          @abstract Dado un n?mero lo devuelve escrito.
          @param $num number - N?mero a convertir.
          @param $fem bool - Forma femenina (true) o no (false).
          @param $dec bool - Con decimales (true) o no (false).
          @result string - Devuelve el n?mero escrito en letra.

         */
//if (strlen($num) > 14) die("El n?mero introducido es demasiado grande"); 
        $matuni[2] = "dos";
        $matuni[3] = "tres";
        $matuni[4] = "cuatro";
        $matuni[5] = "cinco";
        $matuni[6] = "seis";
        $matuni[7] = "siete";
        $matuni[8] = "ocho";
        $matuni[9] = "nueve";
        $matuni[10] = "diez";
        $matuni[11] = "once";
        $matuni[12] = "doce";
        $matuni[13] = "trece";
        $matuni[14] = "catorce";
        $matuni[15] = "quince";
        $matuni[16] = "dieciseis";
        $matuni[17] = "diecisiete";
        $matuni[18] = "dieciocho";
        $matuni[19] = "diecinueve";
        $matuni[20] = "veinte";
        $matunisub[2] = "dos";
        $matunisub[3] = "tres";
        $matunisub[4] = "cuatro";
        $matunisub[5] = "quin";
        $matunisub[6] = "seis";
        $matunisub[7] = "sete";
        $matunisub[8] = "ocho";
        $matunisub[9] = "nove";

        $matdec[2] = "veint";
        $matdec[3] = "treinta";
        $matdec[4] = "cuarenta";
        $matdec[5] = "cincuenta";
        $matdec[6] = "sesenta";
        $matdec[7] = "setenta";
        $matdec[8] = "ochenta";
        $matdec[9] = "noventa";
        $matsub[3] = 'mill';
        $matsub[5] = 'bill';
        $matsub[7] = 'mill';
        $matsub[9] = 'trill';
        $matsub[11] = 'mill';
        $matsub[13] = 'bill';
        $matsub[15] = 'mill';
        $matmil[4] = 'millones';
        $matmil[6] = 'billones';
        $matmil[7] = 'de billones';
        $matmil[8] = 'millones de billones';
        $matmil[10] = 'trillones';
        $matmil[11] = 'de trillones';
        $matmil[12] = 'millones de trillones';
        $matmil[13] = 'de trillones';
        $matmil[14] = 'billones de trillones';
        $matmil[15] = 'de billones de trillones';
        $matmil[16] = 'millones de billones de trillones';

        $num = trim((string) @$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        }
        else
            $neg = '';
        while ($num[0] == '0')
            $num = substr($num, 1);
        if ($num[0] < '1' or $num[0] > 9)
            $num = '0' . $num;
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt)
                    break;
                else {
                    $punt = true;
                    continue;
                }
            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0')
                        $zeros = false;
                    $fra .= $n;
                }
                else
                    $ent .= $n;
            }
            else
                break;
        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' coma';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0')
                    $fin .= ' cero';
                elseif ($s == '1')
                    $fin .= $fem ? ' una' : ' un';
                else
                    $fin .= ' ' . $matuni[$s];
            }
        }
        else
            $fin = '';
        if ((int) $ent === 0)
            return 'Cero ' . $fin;
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'una';
                $subcent = 'as';
            } else {
                $matuni[1] = $neutro ? 'un' : 'uno';
                $subcent = 'os';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
                
            } elseif ($n2 < 21)
                $t = ' ' . $matuni[(int) $n2];
            elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = 'i' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }else {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = ' y ' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' ciento' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
            }
            if ($sub == 1) {
                
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' mil';
                } elseif ($num > 1) {
                    $t .= ' mil';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . '?n';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ones';
            }
            if ($num == '000')
                $mils++;
            elseif ($mils != 0) {
                if (isset($matmil[$sub]))
                    $t .= ' ' . $matmil[$sub];
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        return ucfirst($tex);
    }
    
    function nota_reserva($res_id){
        
        $pagina = "'contenido_reporte'";

        $page = "'about:blank'";

        $extpage = "'reportes'";

        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";
        
        
        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reserva&tarea=ACCEDER\';"></td></tr></table>
				';
        
        
        $conec = new ADO();
            $sql = "select 
				*
				from 
				proforma 
				inner join interno on(pro_id = '$res_id' and pro_int_id=int_id)
				inner join lote on(pro_lot_id=lot_id)
				inner join zona on(lot_zon_id=zon_id)
				inner join uv on(lot_uv_id=uv_id)
				inner join manzano on (lot_man_id=man_id)                                
				inner join urbanizacion on (man_urb_id=urb_id)";
            
            if ($this->verificar_permisos('ACCEDER')) {
            ?>
                <table align=right border=0><tr><td><a href="gestor.php?mod=proforma2&tarea=ACCEDER" title="LISTADO DE PROFORMAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
            }
        
            
//            echo $sql;
            $conec->ejecutar($sql);
            $num = $conec->get_num_registros();
            $objeto = $conec->get_objeto();
            $myday = setear_fecha(strtotime($objeto->pro_fecha));
            // $fecha_plazo = setear_fecha(strtotime($objeto->res_plazo_fecha));;
//            $tc = $objeto->ven_tipo_cambio;
            $body = '<div id="contenido_reporte" style="clear:both;">
                        <center>
                        <table style="font-size:12px" cellpadding="5" cellspacing="0" width="100%">
                            <tbody><tr>
                            <td width="33%"><strong>' . _nombre_empresa . '</strong><br>

                            </td>';
            $body .= '
                    <td width="33%"><center><p align="center"><strong></strong></p><h3><strong>PROFORMA</strong></h3><p></p></center></td>
            <td><div align="right"><img src="imagenes/micro_puertosantacruz.png"></div></td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong><br>Nro de Proforma: </strong>' . $res_id . '<br>';
            if ($objeto->pro_int_id <> 0) {
                $persona = $objeto->int_nombre . ' ' . $objeto->int_apellido;
                $body .= '<strong>Cliente: </strong>' . $persona . '<br>';
            }            
            
            $body .= '<strong>Moneda: </strong>';
           
            
            $moneda = $objeto->zon_moneda;
            if ($moneda == "1")
                $body .= "Bolivianos";
            else
                $body .= "Dolares";

            $body .= '<br>';            
            $body .='<br><br>';
            $body .='</td>
        <td align="right">
                                <strong>Fecha: </strong>' . $myday;            
            
            $body .= '<br>
                                <strong>Usuario: </strong>' . $this->nombre_persona($objeto->pro_usu_id);
            
            $body .= '<br>
                            </td>
                        </tr>

                    </table>';
					
            $body .= '<table   width="70%" class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Terreno</th>
                                <th>Superficie</th>
                                <th>Valor del m2</th>
                                <th>Valor del Terreno</th>';            
            $body .= '</tr>		
                        </thead>
                        <tbody>
                            <tr>
                                <td>';
            $terreno = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
            $body .= $terreno;
            $valor_terreno = $objeto->lot_superficie * $objeto->zon_precio;
            $body .= '</td>
                                <td>';
            $body .= $objeto->lot_superficie . 'm2</td>
                                <td>';
            $body .= $objeto->zon_precio . '</td>
                                <td>';
            $body .= $valor_terreno . '</td>';            
            $body .= '</tr>	
                        </tbody>
                    </table>';
            echo $body;
		
			
		
			$pro_parametros=$objeto->pro_parametros;
			
			
			$data = $pro_parametros;
			$products = json_decode($data);
			
			// echo '<pre>';
			// print_r($products);
			// echo '</pre>';
			
			// foreach ($products as $product) {
				// echo ($product->par_pro_descuento1);
			// }
			?>
			<br />
			<br />
			<table   width="70%" class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th>Plazo</th>
						<th>Cuota Inicial</th>
						<th>Cuota Mensual</th>						
					</tr>		
				</thead>
				<tbody>
				<?php
				for ($i = 1; $i < ($objeto->pro_parametros+1); $i++) 
				{
					echo '<tr>';
					echo '<td>'.$i.' años </td>';
					echo '<td>'.$objeto->pro_ci.'</td>';
					echo '<td>'.number_format($this->cuota(($objeto->pro_lote_precio)-($objeto->pro_ci),$objeto->pro_urb_interes_anual, ($i * 12)),2,'.',',').'</td>'; 
					echo'</tr>';
				}
				?>	
				</tbody>
			</table>
			<?php
    }
	
	function cuota($monto, $interes, $meses) {
            if ($interes > 0) {
                $interes /= 100;
                $interes /= 12;
                $power = pow(1 + $interes, $meses);
                return ($monto * $interes * $power) / ($power - 1);
            } else {
                return round($monto / $meses, 2);
            }
        }

    function nota_comprobante_reserva($cmp_id) {
        $conec = new ADO();

        $sql = "SELECT cmp_fecha,cmp_estado,cmp_tc,cmp_interesado,cmp_tco_id,tco_descripcion, cmp_usu_id,
                int_nombre,int_apellido,cmp_tpa_id,tpa_descripcion,cmp_mon_id, cmp_id,
                (SELECT ABS(cde_monto) 
                FROM comprobante_detalle WHERE cde_cmp_id = cmp_id limit 0,1) as monto
                FROM comprobante left join interno on (cmp_int_id = int_id)
								 inner join tipo_comprobante on (cmp_tco_id = tco_id)
								 inner join tipo_pago on (cmp_tpa_id = tpa_id)
				where cmp_estado =1 and cmp_id =" . $cmp_id;
        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $objeto = $conec->get_objeto();

        $tc = $objeto->cmp_tc;

        $cmp_mon = $objeto->cmp_mon_id;

        $monto = $objeto->monto;

        ////
        $pagina = "'contenido_reporte'";

        $page = "'about:blank'";

        $extpage = "'reportes'";

        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";

        $myday = setear_fecha(strtotime($objeto->cmp_fecha));
        ////

        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reserva&tarea=ACCEDER\';"></td></tr></table>
				';
        if ($this->verificar_permisos('ACCEDER')) {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }
        ?>



        <br><br><div id="contenido_reporte" style="clear:both;">
            <center>
                <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                    <tr>
                        <td width="35%">
                            <strong><?php echo _nombre_empresa; ?></strong><BR>
                            <strong>Santa Cruz - Bolivia</strong>
                        <td><p align="center" ><strong><h3><center>RECIBO OFICIAL</center></h3></strong></p></td>
                        <td width="35%"><div align="right"><img src="imagenes/micro_puertosantacruz.png" width="" /></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
        <?php
        if ($objeto->int_nombre != "") {
            ?> 
                                <strong>Persona: </strong> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?> <br>

            <?php
        } else
        if ($objeto->cmp_tco_id == 1) {
            ?> 
                                <strong>Recibi de: </strong> <?php echo $objeto->cmp_interesado; ?> <br>
                                <?php
                            } else {
                                ?>
                                <strong>Pagado a: </strong> <?php echo $objeto->cmp_interesado; ?> <br>  
                                <?php
                            }
                            ?>					
                            <strong>Forma de Pago: </strong> <?php echo $objeto->tpa_descripcion; ?> <br>
                            <strong>Nro. Comprobante: </strong> <?php echo $objeto->cmp_id; ?> <br>
                            <?php
                            if ($objeto->cmp_tpa_id == 2) {
                                $sql = "SELECT cue_descripcion, cpa_nro_cheque FROM cuenta, comprobante_pago
							         WHERE cpa_cue_id = cue_id and cpa_cmp_id =" . $cmp_id;
                                $conec->ejecutar($sql);
                                $o = $conec->get_objeto();
                                ?>
                                <strong>Banco: </strong> <?php echo $o->cue_descripcion; ?> <br>
                                <strong>Nro. Cheque: </strong> <?php echo $o->cpa_nro_cheque; ?> <br>
                                <?php
                            }
                            ?>
                            <br><br>
                        </td>
                        <td align="right">
                            <strong>Fecha: </strong> <?php echo $myday; ?> <br>
                            <strong>Tipo de Cambio: </strong> <?php echo $tc; ?> <br>
                            <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cmp_usu_id); ?> <br>
                            <br>
                        </td>
                    </tr>

                </table>
                <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Cuenta</th>
                            <th>Detalle</th>
                            <th>Centro Costo</th>
                            <th class="tOpciones" >Bs</th>
                            <th class="tOpciones" >$us</th>
                        </tr>		
                    </thead>
                    <tbody>
        <?php
        $sql = "select 
				cue_numero,cue_descripcion,cco_descripcion,cde_monto,cde_glosa,cde_monto_sus
				from 
				comprobante_detalle inner join cuenta on ( cde_cue_id = cue_id)
				                    left join centrocosto on (cde_cco_id = cco_id)
			    where cde_cmp_id =" . $cmp_id;

        $conec->ejecutar($sql . " order by cde_id asc");

        $num = $conec->get_num_registros();

        $tbs = 0;
        $tsus = 0;

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            if ($i != 0) {
                echo '<tr>';

                echo "<td>";
                echo $objeto->cue_numero;
                echo "</td>";

                echo "<td>";
                echo $objeto->cue_descripcion;
                echo "</td>";
                echo "<td>";
                echo $objeto->cde_glosa;
                echo "&nbsp;</td>";

                echo "<td>";
                echo $objeto->cco_descripcion;
                echo "</td>";

                echo "<td>&nbsp;";
                if (abs($objeto->cde_monto) > 0) {
                    $tbs+=abs($objeto->cde_monto);

                    echo abs($objeto->cde_monto);
                }
                echo "</td>";

                echo "<td>&nbsp;";
                if (abs($objeto->cde_monto_sus) > 0) {
                    $tsus+=abs($objeto->cde_monto_sus);

                    echo abs($objeto->cde_monto_sus);
                }
                echo "</td>";

                echo "</tr>";
            }
            $conec->siguiente();
        }
        ?>
                        <tr>
                            <td class="">&nbsp;

                            </td>
                            <td class="">&nbsp;

                            </td>
                            <td class="">&nbsp;

                            </td>
                            <td class="">&nbsp;

                            </td>
                            <td class="">
                                <b><?php echo $tbs; ?></b>
                            </td>
                            <td class="">
                                <b><?php echo $tsus; ?></b>
                            </td>
                        </tr>


                    </tbody></table>

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
            <br>
            <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
        </div><br>
        <?php
    }

    function ver_comprobante($id) {
        $this->nota_comprobante_reserva2($id);
    }

    
    function ver_nota_devolucion($id) {
        $resrev_id=  FUNCIONES::atributo_bd_sql("select resrev_id as campo from reserva_reversion where resrev_res_id='$id'");
        $this->nota_comprobante_devolucion($resrev_id);
    }
    function nota_comprobante_devolucion($id) {
        $conec = new ADO();
        
        $sql = "SELECT rd.*,rt.res_id,i.int_nombre, i.int_apellido 
                from  reserva_reversion rd, reserva_terreno rt, interno i
                where rd.resrev_res_id=rt.res_id and rt.res_int_id=i.int_id and rd.resrev_id='$id' and rd.resrev_estado='Activo'
                ";
//        echo $sql;
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $objeto = $conec->get_objeto();

        $monto = $objeto->resrev_monto;
//        echo $monto;
        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
                                <link href=css/recibos.css rel=stylesheet type=text/css>
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";
        $myday = setear_fecha(strtotime($objeto->resrev_fecha));
        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reserva&tarea=ACCEDER\';"></td></tr></table>
				';
        if ($this->verificar_permisos('ACCEDER')) {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }
        ?>



        <br><br>
        <div id="contenido_reporte" style="clear:both;">
        <link href="css/recibos.css" rel="stylesheet" type="text/css" />
            <!-- recibo inicio -->
            <div class="recibo">
                <div class="reciboTop">

                    <img class="reciboLogo" src="imagenes/micro_puertosantacruz.png" width="150" height="80"alt="">

                    <div class="reciboTi">
                        <div class="reciboText">RECIBO OFICIAL</div>
                        <div class="reciboNum"><b>Nro.</b> <?php echo $objeto->resrev_id; ?></div>
                        <div class="reciboText"><h5>(Original)</h5></div>
                    </div>

                    <div class="reciboMoney">
                        <div class="reciboCapa">
                            <div class="reciboLabel">
        <?php
        if ($objeto->resrev_moneda == '1')
            echo 'Bs.';
        else
            echo '$us.';
        ?>
                            </div>
                            <div class="reciboMonto">
        <?php echo number_format($monto, 2, '.', ','); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="reciboCont">
                    <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Pagado al Sr.(a):</span> 
                                <span class="reciboTexts"> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">La Suma de:</span>
                                <span class="reciboTexts"><?php
        $aux = intval($monto);

        if ($aux == ($monto)) {

            echo strtoupper($this->num2letras($monto)) . '&nbsp;&nbsp;00/100';
        } else {

            $val = explode('.', $monto);

            echo strtoupper($this->num2letras($val[0]));

            if (strlen($val[1]) == 1)
                echo '&nbsp;&nbsp;' . $val[1] . '0/100';
            else
                echo '&nbsp;&nbsp;' . $val[1] . '/100';
        }
        ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <span class="reciboLabels">
                                    <?php
                                    if ($objeto->resrev_moneda == '1')
                                        echo 'Bolivianos';
                                    if ($objeto->resrev_moneda == '2')
                                        echo 'Dolares';
                                    ?>
                                </span> 
                            </td>
                        </tr>

                                    <?php

                                    ?>

                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Por concepto de:</span> 
                                <span class="reciboTexts"> <?php echo 'Devoluci&oacute;n de Reserva Nro.' . $objeto->res_id; ?></span>
                            </td>
                        </tr>

                        
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Forma de Pago:</span> 
                                <span class="reciboTexts"> Efectivo </span>                                                              
                            </td>
                        </tr>
                        <tr>
                            <td class="reciboRight" colspan="4">
                                <?php
                                function nombremes($mes) {
                                    setlocale(LC_TIME, 'spanish');
                                    $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
                                    return $nombre;
                                }
                                ?>
                                <span class="reciboLabels">Fecha:</span> 
                                <span class="reciboTextsLinea">
                                    <?php 
                                    $valores = explode('-', $objeto->resrev_fecha);
                                    echo $valores[2]; 
                                    ?></span>
                                <span class="reciboLabels">de</span> 
                                <span class="reciboTextsLinea"><?php echo strtoupper(nombremes($valores[1])); ?></span>
                                <span class="reciboLabels">del</span> 
                                <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                            </td>
                        </tr>

                        <tr>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></b></span>
                            </td>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo $this->nombre_persona($objeto->resrev_usu_id); ?></b></span>
                            </td>
                        </tr>
                    </table>
                </div><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
            </div>
            <!-- recibo -->

            <p style="page-break-after: always;"></p>

            <!-- recibo copia inicio -->

                    <!-- recibo copia-->


                </div><br>
        <?php
    }
    
    function nota_comprobante_reserva2($id) {
        $conec = new ADO();
        
        $sql = "SELECT rp.*,i.int_nombre, i.int_apellido from  reserva_pago rp, reserva_terreno rt, interno i
                where rp.respag_res_id=rt.res_id and rt.res_int_id=i.int_id and rp.respag_id='$id' and rp.respag_estado='Pagado'
                order by respag_fecha asc";
        //echo $sql;
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $objeto = $conec->get_objeto();

        $monto = $objeto->respag_monto;
//        echo $monto;
        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
                                <link href=css/recibos.css rel=stylesheet type=text/css>
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";
        $myday = setear_fecha(strtotime($objeto->respag_fecha));
        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&id='.$objeto->respag_res_id.'\';"></td></tr></table>
				';
        if ($this->verificar_permisos('ACCEDER')) {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }
        ?>



        <br><br>
        <div id="contenido_reporte" style="clear:both;">
        <link href="css/recibos.css" rel="stylesheet" type="text/css" />
            <!-- recibo inicio -->
            <div class="recibo">
                <div class="reciboTop">

                    <img class="reciboLogo" src="imagenes/micro_puertosantacruz.png" width="150" height="80"alt="">

                    <div class="reciboTi">
                        <div class="reciboText">RECIBO OFICIAL</div>
                        <div class="reciboNum"><b>Nro.</b> <?php echo $objeto->respag_id; ?></div>
                        <div class="reciboText"><h5>(Original)</h5></div>
                    </div>

                    <div class="reciboMoney">
                        <div class="reciboCapa">
                            <div class="reciboLabel">
        <?php
        if ($objeto->respag == '1')
            echo 'Bs.';
        else
            echo '$us.';
        ?>
                            </div>
                            <div class="reciboMonto">
        <?php echo number_format($monto, 2, '.', ','); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="reciboCont">
                    <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Recibi del (a)Sr.(a):</span> 
                                <span class="reciboTexts"> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">La Suma de:</span>
                                <span class="reciboTexts"><?php
        $aux = intval($monto);

        if ($aux == ($monto)) {

            echo strtoupper($this->num2letras($monto)) . '&nbsp;&nbsp;00/100';
        } else {

            $val = explode('.', $monto);

            echo strtoupper($this->num2letras($val[0]));

            if (strlen($val[1]) == 1)
                echo '&nbsp;&nbsp;' . $val[1] . '0/100';
            else
                echo '&nbsp;&nbsp;' . $val[1] . '/100';
        }
        ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <span class="reciboLabels">
                                    <?php
                                    if ($objeto->respag_moneda == '1')
                                        echo 'Bolivianos';
                                    if ($objeto->respag_moneda == '2')
                                        echo 'Dolares';
                                    ?>
                                </span> 
                            </td>
                        </tr>

                                    <?php

                                    ?>

                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Por concepto de:</span> 
                                <span class="reciboTexts"> <?php echo 'Anticipo: ' . $objeto->respag_glosa; ?></span>
                            </td>
                        </tr>

                        
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Forma de Pago:</span> 
                                <span class="reciboTexts"> <?php echo $objeto->respag_tipopago; ?></span>
                                <?php
                                if($objeto->respag_tipopago=="Cheque"){
                                    if(true){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->respag_ban_char.'</span>';
                                    }
                                    echo '<span class="reciboLabels">Nro. de Cheque:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->respag_ban_nro.'</span>';
                                    
                                }elseif($objeto->respag_tipopago=="Deposito"){
                                    if(true){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->respag_ban_id, 'ban_nombre').'</span>';
                                    }
                                    echo '<span class="reciboLabels">Nro. de Deposito:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->respag_ban_nro.'</span>';
                                    
                                }elseif($objeto->respag_tipopago=="Transferencia"){
                                    if(true){
                                        echo '<span class="reciboLabels">Banco Destino:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->respag_ban_id, 'ban_nombre').'</span>';
                                        echo '<span class="reciboLabels">Banco Origen:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->respag_ban_char.'</span>';
                                    }
                                    echo '<span class="reciboLabels">Nro. de Trans.:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->respag_ban_nro.'</span>';
                                }
                                ?>                                
                            </td>
                        </tr>
                        <tr>
                            <td class="reciboRight" colspan="4">
                                <?php
                                function nombremes($mes) {
                                    setlocale(LC_TIME, 'spanish');
                                    $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
                                    return $nombre;
                                }
                                ?>
                                <span class="reciboLabels">Fecha:</span> 
                                <span class="reciboTextsLinea">
                                    <?php 
                                    $valores = explode('-', $objeto->respag_fecha);
                                    echo $valores[2]; 
                                    ?></span>
                                <span class="reciboLabels">de</span> 
                                <span class="reciboTextsLinea"><?php echo strtoupper(nombremes($valores[1])); ?></span>
                                <span class="reciboLabels">del</span> 
                                <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                            </td>
                        </tr>
						<tr>
							<td colspan="4">
								<br/><span style="font-size:11px;"><?php echo utf8_decode("La validez de la reserva tiene una duración de 7 días calendario, dentro de este plazo el comprador deberá depositar la cuota inicial que es el 6% del valor de lista del terreno.  Pasado ese plazo el terreno queda disponible y el monto erogado por concepto de reserva queda en favor de la empresa por costos administrativos. - El pago de la cuota inicial y las cuotas mensuales deben realizarse en las cuentas del banco GANADERO en Bolivianos Cta nro 1310-099782 y en Dólares Americanos 1310-099785 a nombre de PEDRO COLANZI SERRATE - La fecha de realizado el pago de la cuota inicial constituye la fecha del mes para cancelar las cuotas del plan de pagos. Salvo cambio de fecha a solicitud expresa por el comprador. - El comprador acepta que el depósito de la cuota inicial es la aceptación de hecho de compra del terreno a cuotas. La carencia de pagos de cinco cuotas hace al comprador perder el derecho propietario y el monto de la cuota inicial queda en favor de la empresa por conceptos de comisiones comerciales a los ejecutivos de  ventas. La fecha es determinada por el día del depósito y es inherente al día de la firma del contrato. - El valor del contrato de pago a plazos del terreno es el valor de precio de lista de la fecha, y se realizaran los descuentos por pronto pago en la siguiente proporción. 10% en 50 cuotas, 20% en 24 cuotas, 35% en 6 cuotas.- El cliente de no estar satisfecho con la atención recibida por su ejecutivo de cuentas podrá solicitara la empresa el cambio a otro ejecutivo comerial."); ?></span>
							</td>
						</tr>
						<tr>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?></b></span>
                            </td>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo $this->nombre_persona($objeto->respag_usu_id); ?></b></span>
                            </td>
                        </tr>
                        
                    </table>
                </div>
				
				<b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
				
            </div>
            <!-- recibo -->
                </div><br>
        <?php
    }

    function nombre_persona($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }
    
    function nombre_vendedor($vendedor){
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from vendedor inner join interno on (vdo_id='$vendedor' and vdo_int_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function obtener_datos_reserva($id_reserva, &$moneda, &$interno, &$res_lot_id) {
        $conec = new ADO();

        $sql = "SELECT * FROM reserva_terreno WHERE res_id=$id_reserva";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $moneda = $objeto->res_moneda;

        $interno = $objeto->res_int_id;

        $res_lot_id = $objeto->res_lot_id;
    }
    function obtener_total_anticipos_reserva($res_id){
        $sql = "select SUM(respag_monto) as monto_anticipo from reserva_pago where respag_res_id=" . $res_id . " and respag_estado='Pagado'";
//        echo $sql;
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $total = 0;
        if($num > 0){
            if($conec->get_objeto()->monto_anticipo != null)
                $total = $conec->get_objeto()->monto_anticipo;
        }
        return $total;
    }

    function limpiar() {
        $_POST['respag_monto'] = '';
    }

    function datos_anticipo() {

        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail, todo
            require_once('clases/validar.class.php');

            $i = 0;
            $valores[$i]["etiqueta"] = "Monto";
            $valores[$i]["valor"] = $_POST['respag_monto'];
            $valores[$i]["tipo"] = "real";
            $valores[$i]["requerido"] = true;
            $i++;




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

    function formulario_anticipo($tipo) {        
        
        $sql = "select * from reserva_terreno where res_id='".$_GET['id']."'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        if($num > 0){
            $objeto = $conec->get_objeto();
            $monto_ref = $objeto->res_monto_referencial;
        }
        $url = $this->link . '?mod=' . $this->modulo;
        $re = $url;
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
        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }
        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        if ($_GET['acc'] == 'MODIFICAR_PRODUCTO') {
            $url.='&acc=MODIFICAR_PRODUCTO';
        }
        $this->formulario->dibujar_titulo("ANTICIPOS RESERVA");
        if ($this->mensaje <> "" and $this->tipo_mensaje <> "") {
            $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
        }
        ?>
                <div id="Contenedor_NuevaSentencia">
                    <script>
                        function ValidarNumero(e)
                        {
                            evt = e ? e : event;
                            tcl = (window.Event) ? evt.which : evt.keyCode;
                            if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                            {
                                return false;
                            }
                            return true;
                        }
                        
                        function f_tipo_pago(){
                            var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                            if(tipo == 'efectivo'){
                                $('#div_datos_operacion').css("display","none");
                            } else {
                                if(tipo == 'cheque'){
                                    $('#lbl_transaccion').html('Nro de Cheque');
                                }
                                if(tipo == 'deposito'){
                                    $('#lbl_transaccion').html('Nro de Deposito');
                                }
                                if(tipo == 'transferencia'){
                                    $('#lbl_transaccion').html('Nro de Transferencia');
                                }
                                $('#div_datos_operacion').css("display","block");
                            }
                        }
                        
                        function cambiar_moneda(){
                            var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                            var moneda_original = document.frm_sentencia.moneda_original.value;
                            var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                            var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                            var tc = parseFloat(<?php echo $this->tc;?>);
                            if(moneda_original !== moneda_sel){
                                if(moneda_sel === '1'){                                                                                
                                    pagara *= tc;                                                                           
                                }else{                                                                                
                                    pagara /= tc;                                                   
                                }
                            }                                
                            document.frm_sentencia.valor_convertido.value = roundNumber(pagara,2);
                        }

                        function enviar_formulario_anticipo(){
                            var respag_monto = document.frm_sentencia.respag_monto.value;
                            var fecha = document.getElementById('respag_fecha').value;                        
                            if (respag_monto !== '') {
                                if((respag_monto*1)>0){
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
                                    $.prompt('El monto del anticipo debe ser mayor a 0');
                                }
                            } else {
                                $.prompt('Ingrese el monto del anticipo de la reserva.');
                            }
                        }
                    </script>
                    <script type="text/javascript" src="js/util.js"></script>
                    <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                    <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  

                        <div id="FormSent">



                            <div class="Subtitulo">Anticipos Reserva</div>

                            <div id="ContenedorSeleccion">
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv" style="display: none;">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Monto Referencial Acordado</div>
                                    <div id="CajaInput">
                                        <input  class="caja_texto" readonly="readonly" name="" id="" size="12" value="<? echo $monto_ref; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                                        <input  class="caja_texto" readonly="readonly" name="moneda_original" id="moneda_original" size="2" value="<? echo $objeto->res_moneda; ?>" type="hidden" >
                                        <?php                                                
                                        if ($objeto->res_moneda == '1')
                                            echo 'Bs.';
                                        else
                                            echo '$us';
                                        ?>
                                    </div>							
                                </div>
                                <!--Fin-->
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>
                                    <div id="CajaInput">
                                        <input  class="caja_texto" name="respag_monto" id="respag_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);">
                                        $us.
                                    </div>							
                                                                        
                                </div>
                                <!--Fin-->
                                
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv" style="display: none">                                                                    							                                    
                                    <div class="Etiqueta"><span class="flechas1"></span>En:</div>
                                        <div id="CajaInput">
                                            <select id="moneda" name="moneda">
                                                <option value="1" <?php if($objeto->res_moneda == '1')echo "selected";?>>Bolivianos</option>
                                                <option value="2" <?php if($objeto->res_moneda == '2')echo "selected";?>>Dolares</option>
                                            </select>
                                        </div>                                                                                
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                               <div id="ContenedorDiv">
                                   <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                   <div id="CajaInput">
                                       <input class="caja_texto" name="respag_fecha" id="respag_fecha" size="12" value="<?php if (isset($_POST['respag_fecha'])) echo $_POST['respag_fecha'];else echo date("d/m/Y"); ?>" type="text">
                                   </div>
                                </div>
                               <!--Fin-->
                                <script>
                                     jQuery(function($){
                                        $("#respag_fecha").mask("99/99/9999");			   
                                     });
                                </script>
                                <?php if($this->usu->get_id()=='admin' || $objeto->res_estado=='Pendiente'||$objeto->res_estado=='Habilitado'){ ?>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><b>Pagos</b></div>                                
                                    <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'respag_fecha','cmp_monto'=>'respag_monto','cmp_moneda'=>'moneda'));?>
                                </div>
                                <?php }?>
                            </div>
                            <?php if($this->usu->get_id()=='admin' || $objeto->res_estado=='Pendiente'||$objeto->res_estado=='Habilitado'){ ?>
                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>
                                        <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">
                                        <input type="reset" class="boton" name="" value="Cancelar">
                                        <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
                                                echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
                                            else
                                                echo $this->link . '?mod=' . $this->modulo;
                                        ?>';">
                                    </center>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                </div>					

        <?php
    }

    function dibujar_encabezado_anticipo() {
        ?>
                <div style="clear:both;"></div><center>
                    <script>
                        function anular_pago(id) {
                            var txt = 'Esta seguro de anular el el anticipo de la Reserva?';

                            $.prompt(txt, {
                                buttons: {Anular: true, Cancelar: false},
                                callback: function(v, m, f) {

                                    if (v) {
                                        location.href = 'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&acc=anular&id=' + id;
                                    }

                                }
                            });
                        }
                        function ver_comprobante(id)
                        {
                            location.href = 'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&acc=ver_comprobante&id=' + id;
                        }
                    </script>
                    <table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
                        <thead>
                            <tr>
                                <th >
                                    Nro
                                </th>
                                <th >
                                    Fecha
                                </th>
                                <th >
                                    Hora
                                </th>
                                <th >
                                    Monto
                                </th>
                                <th >
                                    Moneda
                                </th>
                                <th class="tOpciones" width="100px">
                                    Opciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
        <?PHP
    }

    function mostrar_busqueda_anticipo() {

        $conec = new ADO();

        $sql = "SELECT * FROM reserva_pago
		inner join reserva_terreno on (res_id=respag_res_id)                
		WHERE 
		respag_res_id='" . $_GET['id'] . "' and respag_estado='Pagado' 
		order by 
		respag_fecha asc,respag_hora asc";
        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $suma_anticipo = 0;

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            echo '<tr class="busqueda_campos">';
            ?>

                            <td align="left">

                                <?php
                                echo $i + 1;
                                ?>

                            </td>

                            <td align="left">

                                <?php
                                $convertir = new convertir;
                                echo $convertir->get_fecha_latina($objeto->respag_fecha);
                                ?>

                            </td>

                            <td align="left">

            <?php
            echo $objeto->respag_hora;
            ?>

                            </td>

                            <td align="left">

                                <?php
                                echo number_format($objeto->respag_monto, 2);
                                $suma_anticipo+=$objeto->respag_monto;
                                ?>

                            </td>

                            <td align="left">

            <?php
//            if ($objeto->respag_moneda == '2')
            if ($objeto->respag_moneda== '2')    
                echo 'Dolares';
            else
                echo 'Bolivianos';
            ?>
                            </td>





                            <td style="text-align:center;">

                            <center>

                                <table>
                                    <tr>
                                        <td>
                                            <a href="javascript:ver_comprobante('<?php echo $objeto->respag_id; ?>');">
                                                <img src="images/ver.png" alt="VER" title="VER" border="0">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="javascript:anular_pago('<?php echo $objeto->respag_id; ?>');">
                                                <img src="images/anular.png" alt="ANULAR" title="ANULAR" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                            </center>

                            </td>


            <?php
            echo "</tr>";

            $conec->siguiente();
        }
        ?>

                        <?php
                        echo "</tbody></table></center><br>";
                    }

        function anular_anticipo($id) {

            include_once 'clases/registrar_comprobantes.class.php';
            $bool=COMPROBANTES::anular_comprobante('reserva_pago', $id);
            if(!$bool){
                $mensaje="El pago de la cuota no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
                return;
            }
            $conec = new ADO();
            $sql = "select * from reserva_pago where respag_id=$id";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();

            $id_reserva = $objeto->respag_res_id;
            $sql = "update reserva_pago set respag_estado='Anulado' where respag_id=$id";
            $conec->ejecutar($sql);
            $sql="select sum(respag_monto) as suma from reserva_pago where respag_estado!='Anulado' and respag_res_id='$id_reserva';";
//                        echo $sql;
            $pago=  FUNCIONES::objeto_bd_sql($sql);

            if($pago->suma=='' || floatval($pago->suma)<=0){
                $sql = "update reserva_terreno set res_estado='Pendiente' where res_id='$id_reserva'";
                $conec->ejecutar($sql);

            }
            FORMULARIO::anular_pagos(_CTE::$RESERVA_PAGO, $id, $conec);
            $mensaje = 'Anticipo de Reserva Anulado Correctamente';
            //$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
            $this->mensaje = $mensaje;
            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
            }
        }
        
        function formulario_devolucion($tipo) {        
            
            $conec = new ADO();
            
            $sql = "select sum(respag_monto)as campo from reserva_pago where respag_res_id=$_GET[id]";
            $monto_ref = FUNCIONES::atributo_bd_sql($sql);

            $url = $this->link . '?mod=' . $this->modulo;

            $re = $url;

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

            if (!($ver)) {
                $url.="&tarea=" . $_GET['tarea'];
            }

            if ($cargar) {
                $url.='&id=' . $_GET['id'];
            }

            if ($_GET['acc'] == 'MODIFICAR_PRODUCTO') {
                $url.='&acc=MODIFICAR_PRODUCTO';
            }

            $this->formulario->dibujar_titulo("ANTICIPOS RESERVA");

            if ($this->mensaje <> "" and $this->tipo_mensaje <> "") {
                $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
            }
            ?>


                    <div id="Contenedor_NuevaSentencia">
                        <script>
                            function ValidarNumero(e)
                            {
                                evt = e ? e : event;
                                tcl = (window.Event) ? evt.which : evt.keyCode;
                                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                                {
                                    return false;
                                }
                                return true;
                            }

                            function f_tipo_pago(){
                                var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                                if(tipo == 'efectivo'){
                                    $('#div_datos_operacion').css("display","none");
                                } else {
                                    if(tipo == 'cheque'){
                                        $('#lbl_transaccion').html('Nro de Cheque');
                                    }
                                    if(tipo == 'deposito'){
                                        $('#lbl_transaccion').html('Nro de Deposito');
                                    }
                                    if(tipo == 'transferencia'){
                                        $('#lbl_transaccion').html('Nro de Transferencia');
                                    }
                                    $('#div_datos_operacion').css("display","block");
                                }
                            }

                            function cambiar_moneda(){
                                var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                                var moneda_original = document.frm_sentencia.moneda_original.value;
                                var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                                var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                                var tc = parseFloat(<?php echo $this->tc;?>);
                                if(moneda_original !== moneda_sel){
                                    if(moneda_sel === '1'){                                                                                
                                        pagara *= tc;                                                                           
                                    }else{                                                                                
                                        pagara /= tc;                                                   
                                    }
                                }                                
                                document.frm_sentencia.valor_convertido.value = roundNumber(pagara,2);
                            }

                            function enviar_formulario_anticipo(){
                                var respag_monto = document.frm_sentencia.respag_monto.value;
                                var fecha = document.getElementById('respag_fecha').value;                        
                                if (respag_monto !== '') {
                                    if((respag_monto*1)>0){
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
                                        $.prompt('El monto del anticipo debe ser mayor a 0');
                                    }
                                } else {
                                    $.prompt('Ingrese el monto del anticipo de la reserva.');
                                }
                            }
                        </script>
                        <script type="text/javascript" src="js/util.js"></script>
                        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  

                            <div id="FormSent">



                                <div class="Subtitulo">Anticipos Reserva</div>

                                <div id="ContenedorSeleccion">

                                    <!--Inicio-->
                                    <div id="ContenedorDiv" >
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Monto Pagado</div>
                                        <div id="CajaInput">
                                            <input  class="caja_texto" readonly="readonly" name="" id="" size="12" value="<? echo $monto_ref; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                                            <input  class="caja_texto" readonly="readonly" name="moneda_original" id="moneda_original" size="2" value="<? echo $objeto->res_moneda; ?>" type="hidden" >                                            
                                        </div>							
                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>
                                        <div id="CajaInput">
                                            <input  class="caja_texto" name="respag_monto" id="respag_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);">
                                            $us.
                                        </div>							

                                    </div>
                                    <!--Fin-->


                                    <!--Inicio-->
                                    <div id="ContenedorDiv" style="display: none">                                                                    							                                    
                                        <div class="Etiqueta"><span class="flechas1"></span>En:</div>
                                            <div id="CajaInput">
                                                <select id="moneda" name="moneda">
                                                    <option value="1" >Bolivianos</option>
                                                    <option value="2" selected="">Dolares</option>
                                                </select>
                                            </div>                                                                                
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                   <div id="ContenedorDiv">
                                       <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                       <div id="CajaInput">
                                           <input class="caja_texto" name="respag_fecha" id="respag_fecha" size="12" value="<?php if (isset($_POST['respag_fecha'])) echo $_POST['respag_fecha'];else echo date("d/m/Y"); ?>" type="text">
                                       </div>
                                    </div>
                                   <!--Fin-->
                                    <script>
                                         jQuery(function($){
                                            $("#respag_fecha").mask("99/99/9999");			   
                                         });
                                    </script>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><b>Pagos</b></div>                                
                                        <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'respag_fecha','cmp_monto'=>'respag_monto','cmp_moneda'=>'moneda'));?>
                                    </div>
                                </div>

                                <div id="ContenedorDiv">

                                    <div id="CajaBotones">

                                        <center>

                                            <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">

                                            <input type="reset" class="boton" name="" value="Cancelar">

                                            <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
                echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
            else
                echo $this->link . '?mod=' . $this->modulo;
            ?>';">

                                        </center>

                                    </div>

                                </div>

                            </div>

                    </div>					

            <?php
        }
        function guardar_devolucion() {            

            $conec = new ADO();
            $sql = "select * from reserva_terreno where res_id='" . $_GET['id'] . "'";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();
            $estado = $objeto->res_estado;
            if ($estado == 'Deshabilitado') {
                if (true) {
                    $reserva=  FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
                    $moneda = $_POST[moneda];
                    $monto = $_POST[respag_monto]*1;
                    $fecha=  FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);
                    
                    $interno = $reserva->res_int_id;
                    $res_lot_id = $reserva->res_lot_id;
                    $_POST['ven_lot_id'] = $res_lot_id;
//                    $this->descripcion_terreno($des);
//                    $glosa=$des;
                    $sql = "insert into reserva_reversion(
                                resrev_res_id,resrev_monto,resrev_moneda,resrev_fecha,resrev_usu_id,resrev_estado) 
                            values (
                                '$reserva->res_id','$monto','$moneda','$fecha','{$this->usu->get_id()}','Activo'
                                )";
                    //echo $sql;
                    $conec->ejecutar($sql, false);
                    $llave = mysql_insert_id();

    //                $interno=FUNCIONES::atributo_bd("interno", "int_id=".$_POST['ven_int_id'], "concat(int_nombre,' ',int_apellido )");
                    $sql = "select sum(respag_monto)as campo from reserva_pago where respag_res_id=$_GET[id]";
                    $monto_ref = FUNCIONES::atributo_bd_sql($sql);
                    $fecha_cmp=  FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);
                    include_once 'clases/registrar_comprobantes.class.php';
                    $ges_id=$_SESSION['ges_id'];
                    $glosa="Pago de Reversion de la reserva Nro. $reserva->res_id";
                    $comprobante = new stdClass();
                    $comprobante->tipo = "Egreso";
                    $comprobante->mon_id = $_POST[moneda];
                    $comprobante->nro_documento = date("Ydm");
                    $comprobante->fecha = $fecha_cmp;
                    $comprobante->ges_id = $ges_id;
                    $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
                    $comprobante->forma_pago='Efectivo';
                    $comprobante->ban_id='';
                    $comprobante->ban_char='';
                    $comprobante->ban_nro='';
                    $comprobante->glosa =$glosa;
                    $comprobante->referido = FUNCIONES::atributo_bd("interno", "int_id='$interno'", "concat(int_nombre,' ',int_apellido)");
                    $comprobante->tabla = "reserva_reversion";
                    $comprobante->tabla_id = $llave;
                    
                    $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '2.1.1.05.2.01'),"debe"=>$monto_ref,"haber"=>0,
                                "glosa"=>$glosa,"ca"=> FUNCIONES::get_cuenta_ca($ges_id, '01.00001'),"cf"=>'0',"cc"=>'0','int_id'=>  $reserva->res_int_id
                        );
                    $ingreso=$monto_ref-$monto;
                    if($ingreso>0){
                        $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.04'),"debe"=>0,"haber"=>$ingreso,
                                    "glosa"=>$glosa,"ca"=> '0',"cf"=>'0',"cc"=>  FUNCIONES::get_cuenta_cc($ges_id, '1.03')
                            );
                    }
//                    $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '2.1.1.01.2.02'),"debe"=>0,"haber"=>$monto_pagado-$monto,
//                                "glosa"=>$glosa,"ca"=> '0',"cf"=>'0',"cc"=> FUNCIONES::get_cuenta_cc($ges_id, '1.03')
//                        );
                    
                    $params=array(
                        'tabla'=>'reserva_reversion',
                        'tabla_id'=>$llave,
                        'fecha'=>$fecha_cmp,
                        'moneda'=>$_POST['moneda'],
                        'ingreso'=>false,
                        'glosa'=>$glosa,'ca'=>'0','cf'=>FUNCIONES::get_cuenta_cf($ges_id, "2.02.015"),'cc'=>'0'
                    );
                    $detalle = FORMULARIO::insertar_pagos($params);
                    FUNCIONES::add_elementos($comprobante->detalles, $detalle);
//                    COMPROBANTES::registrar_comprobante($comprobante);
                    
                    $sqlres = "update reserva_terreno set res_estado='Devuelto' where res_id='".$reserva->res_id."'";
                    $conec->ejecutar($sqlres);                   

    //                $this->nota_comprobante_reserva($cmp_id);                
                    $this->nota_comprobante_devolucion($llave);
//                    $this->ver_comprobante($llave);
                } else {
                    $mensaje = 'No puese realizar la <b>Devolucion</b> de la Reserva.';

                    $this->mensaje = $mensaje;

                    if ($this->mensaje <> "") {
                        $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
                    }
                }
            } else {
                $mensaje = 'No se pudo <b>Ingresar Anticipo</b> por que la Reserva ya se encuentra en estado <b>Deshabilitado, Expirado , Concretado o Pendiente</b>';
                $this->mensaje = $mensaje;
                if ($this->mensaje <> "") {
                    $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
                }
            }
        }
        
        function vencidos(){
//            echo '<pre>';
//            print_r($_POST);
//            echo '</pre>';
            $tipo=$_GET[tipo];
            $is_listar=true;
            $and_fecha_plazo="";
            if($tipo=='ayer'){
                $op_rel='<=';
                $fecha= FUNCIONES::sumar_dias(-1,date('Y-m-d'));
                
                $and_fecha_plazo="and res_plazo_fecha$op_rel'$fecha'";
            }elseif($tipo=='manana'){
                $op_rel='=';
                $fecha= FUNCIONES::sumar_dias(1,date('Y-m-d'));
                $and_fecha_plazo="and res_plazo_fecha$op_rel'$fecha'";
            }elseif($tipo=='personal'){
                if($_POST){
                    $op_rel=$_POST[op_rel];
                    $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
                    $and_fecha_plazo="and res_plazo_fecha$op_rel'$fecha'";
                }else{
                    $is_listar=false;
                }
                
            }else{
                $tipo = 'hoy';
                $op_rel = '=';
                $fecha = date('Y-m-d');
                $hora = date('H:i:s');
                $and_fecha_plazo = "and concat(res_plazo_fecha,' ',res_plazo_hora)$op_rel'$fecha $hora'";
            }
            ?>
                <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
                <div class="aTabsCont">
                    <div class="aTabsCent">
                        <ul class="aTabs">
                            <li><a <?php echo $tipo=='hoy'?'class="activo"':'';?> href="gestor.php?mod=reserva&tarea=VENCIDOS&tipo=hoy">Hoy</a></li>
                            <li><a <?php echo $tipo=='ayer'?'class="activo"':'';?> href="gestor.php?mod=reserva&tarea=VENCIDOS&tipo=ayer">Hasta Ayer</a></li>
                            <li><a <?php echo $tipo=='manana'?'class="activo"':'';?> href="gestor.php?mod=reserva&tarea=VENCIDOS&tipo=manana">Ma&ntilde;ana</a></li>
                            <li><a <?php echo $tipo=='personal'?'class="activo"':'';?> href="gestor.php?mod=reserva&tarea=VENCIDOS&tipo=personal">Personalizado</a></li>
                        </ul>
                    </div>
                </div>
                <div style="clear: both;"></div>
                <div id="ContenedorSeleccion" style="width: 98%;margin: 0 auto; position: relative; float: none;">
                    <?php if($tipo=='personal'){?>
                        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=reserva&tarea=VENCIDOS&tipo=personal" name="frm_sentencia">
                            <div id="ContenedorDiv">
                                <div id="CajaInput">
                                    <select name="op_rel" style="margin-top: 5px; width: 85px;">
                                        <option value="=" <?php echo $_POST[op_rel]=='='?'selected="true"':'';?>>Igual</option>
                                        <option value=">" <?php echo $_POST[op_rel]=='>'?'selected="true"':'';?>>Mayor</option>
                                        <option value="<" <?php echo $_POST[op_rel]=='<'?'selected="true"':'';?>>Menor</option>
                                        <option value=">=" <?php echo $_POST[op_rel]=='>='?'selected="true"':'';?>>Mayor Igual</option>
                                        <option value="<=" <?php echo $_POST[op_rel]=='<='?'selected="true"':'';?>>Menor Igual</option>
                                    </select>
                                </div>
                                <div class="Etiqueta" style="width: 60px; margin-top: 4px; "><b>A la Fecha</b></div>
                                <div id="CajaInput">
                                    <input id="fecha" class="caja_texto" type="text" value="<?php echo $_POST[fecha];?>" size="20" name="fecha" style="margin-top: 2px;">
                                </div>
                                <div id="CajaInput" style="margin-left: 5px;">
                                    <input class="boton" type="submit" value="Listar" >
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                            <script>
                                $("#fecha").mask("99/99/9999");
                                $('#fecha').focus();
                            </script>
                        </form>
                    <?php }?>
                    <div>
                        <?php if($is_listar){?>
                        <?php 
                        $sql_select = "SELECT 
                                    res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_monto_referencial,res_moneda,
                                    res_estado,res_usu_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,uv_nombre,
                                    res_plazo_fecha,res_plazo_hora
                                FROM 
                                    reserva_terreno 
                                    inner join interno on (res_int_id=int_id)
                                    inner join lote on (res_lot_id=lot_id)
                                    inner join uv on (uv_id=lot_uv_id)
                                    inner join manzano on (lot_man_id=man_id)
                                    inner join urbanizacion on (man_urb_id=urb_id)
                                where 
                                    (res_estado='Pendiente' or res_estado='Habilitado')
                                    $and_fecha_plazo
                                                        order by res_plazo_fecha desc ";
//                        echo $sql_select;
                        $reservas=  FUNCIONES::objetos_bd_sql($sql_select);
                        ?>
                        <div style="font-size: 14px; text-align: left; color: #0000FF;margin-bottom: 3px;"><b>Numero Reservas: <?php echo $reservas->get_num_registros();?> </b></div>
                        <table class="tablaLista" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Nro</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Fecha Plazo</th>
                                    <th>Hora Plazo</th>
                                    <th>Persona</th>
                                    <th>Vendedor</th>
                                    <th>Urbanizacion</th>
                                    <th>Uv</th>
                                    <th>Manzano</th>
                                    <th>Lote</th>
                                    <th>Anticipo</th>
                                    <th>Estado</th>
                                    <th class="tOpciones" width="100px">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $color=array('Pendiente'=>'#ff9601','Habilitado'=>'#019721','Concretado'=>'#0356ff','Deshabilitado'=>'#ff0000','Devuelto'=>'#000');
                                ?>
                                <?php for($i=0;$i<$reservas->get_num_registros();$i++){?>
                                    <?php $reserva=$reservas->get_objeto();?>
                                    <tr>
                                        <td><?php echo $reserva->res_id;?></td>
                                        <td><?php echo FUNCIONES::get_fecha_latina($reserva->res_fecha);?></td>
                                        <td><?php echo $reserva->res_hora;?></td>
                                        <td><?php echo FUNCIONES::get_fecha_latina($reserva->res_plazo_fecha);?></td>
                                        <td><?php echo $reserva->res_plazo_hora;?></td>
                                        <td><?php echo "$reserva->int_nombre $reserva->int_apellido";?></td>
                                        <td><?php echo $this->nombre_persona_vendedor($reserva->res_vdo_id);;?></td>
                                        <td><?php echo $reserva->urb_nombre;?></td>
                                        <td><?php echo $reserva->uv_nombre;?></td>
                                        <td><?php echo $reserva->man_nro;?></td>
                                        <td><?php echo $reserva->lot_nro;?></td>
                                        <td><?php echo FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo from reserva_pago where respag_res_id='$reserva->res_id' and respag_estado='Pagado'")*1;?></td>
                                        <td id='estado-<?php $reserva->res_id;?>'><?php echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$reserva->res_estado]}'>$reserva->res_estado</span>";?></td>
                                        <td>
                                            <?php if ($this->verificar_permisos('VER')) {?>
                                            <a class="linkOpciones" title="VER" href="gestor.php?mod=reserva&tarea=VER&id=<?php echo $reserva->res_id;?>" target="contenido">
                                                <img border="0" width="16" alt="VER" src="images/b_search.png">
                                            </a>
                                            <?php }?>
                                            <?php if ($this->verificar_permisos('ANULAR')) {?>
                                            <a class="linkOpciones" title="DESHABILITAR" href="javascript:ejecutar_script('<?php echo $reserva->res_id?>','ANULAR');" target="contenido">
                                                <img border="0" width="16" alt="DESHABILITAR" src="images/anular.png">
                                            </a>
                                            <?php }?>
                                            <?php if ($this->verificar_permisos('CONCRETAR VENTA')) {?>
                                            <a class="linkOpciones" title="CONCRETAR" href="javascript:ejecutar_script('<?php echo $reserva->res_id?>','CONCRETAR VENTA');" target="contenido">
                                                <img border="0" width="16" alt="CONCRETAR" src="images/migrar.png">
                                            </a>
                                            <?php }?>
                                            <?php if ($this->verificar_permisos('CAMBIAR FECHA')) {?>
                                            <a class="linkOpciones" title="CAMBIAR FECHA" href="gestor.php?mod=reserva&tarea=CAMBIAR FECHA&id=<?php echo $reserva->res_id;?>" target="contenido">
                                                <img border="0" width="16" alt="CAMBIAR FECHA" src="images/edit_date.png">
                                            </a>
                                            <?php }?>
                                        </td>
                                    </tr>
                                    <?php $reservas->siguiente();?>
                                <?php }?>
                            </tbody>
                        </table>
                        
                        <script>
                            function ejecutar_script(id,tarea){
                                if(tarea==='ANULAR'){
                                    var estado=$("#estado-"+id).text();
                                    estado= estado.replace(/^\s+/g,'').replace(/\s+$/g,'');
                                    var monto='';
                                    if(estado!=='Deshabilitado'){
                                        if(estado==='Habilitado'){
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
                                                    location.href='gestor.php?mod=reserva&tarea='+tarea+'&id='+id;
                                                }
                                            }
                                        });
                                    }else{
                                        $.prompt('La reserva y se encuentra Deshabilitada');
                                    }
                                }

                                if(tarea==='CONCRETAR VENTA'){
                                    var txt = 'Esta seguro de concretar la Venta?';
                                    $.prompt(txt,{ 
                                        buttons:{Concretar:true, Cancelar:false},
                                        callback: function(v,m,f){
                                            if(v){
                                                location.href = 'gestor.php?mod=venta&tarea=AGREGAR&id_res=' + id + '&concretar=ok';
                                            }
                                        }
                                    });
                                }

                                if (tarea === 'ANTICIPOS RESERVA'){
                                    $.post("datos_reserva.php", {id: id},
                                    function(respuesta) {
                                    var res = eval("(" + respuesta + ")");
                                        if(res[0].value === 'Pendiente' || res[0].value === 'Habilitado'){
                                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                                        }else{
                                            $.prompt('No puede realizar anticipos a esta reserva.');
                                        }    
                                    });
                                }

                                if (tarea === 'DEVOLVER'){
                                    $.post("datos_reserva.php", {id: id},
                                    function(respuesta) {
                                    var res = eval("(" + respuesta + ")");
                                        if(res[0].value === 'Deshabilitado'){
                                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                                        }else{
                                            $.prompt('No puede realizar devoluciones a esta reserva.');
                                        }    
                                    });
                                }
                            }
                        </script>
                        <?php }?>
                    </div>
                </div>
            <?php
        }
        
        function ampliar_fecha(){
            if($_POST){
                $this->guardar_ampliar_fecha($_GET[id]);
            }else{
                $this->frm_ampliar_fecha($_GET[id]);
            }
        }

        function guardar_ampliar_fecha($res_id){
            $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$res_id'");
            $plazo_fecha = FUNCIONES::get_fecha_mysql($_POST[plazo_fecha]);
            if(trim($_POST[nota_observacion])){
                $nota_observacion = $reserva->res_plazo_nota."<-p->$_POST[nota_observacion]";
                $set_nota=", res_plazo_nota='$nota_observacion'";
            }
            
            $sql_up="update reserva_terreno set res_plazo_fecha='$plazo_fecha' $set_nota where res_id='$reserva->res_id'";
//            echo $sql_up.';<br>';
            $conec=new ADO();
            $conec->ejecutar($sql_up);
            $this->formulario->dibujar_mensaje("El cambio de fecha fue realizado correctamente", $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER','','Correcto');
        }
        function frm_ampliar_fecha($res_id){
            $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$res_id'");
            $this->formulario->dibujar_titulo("CAMBIAR FECHA DE VENCIMIENTO DE RESERVA");
            ?>
                <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
                <script src="js/util.js" type="text/javascript"></script>
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=reserva&tarea=CAMBIAR FECHA&id=<?php echo $res_id;?>" method="POST" enctype="multipart/form-data">
                    <div id="ContenedorSeleccion" style="width: 98%;margin: 0 auto; position: relative; float: none;">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Fecha Plazo Actual:</div>
                            <div id="CajaInput">
                                &nbsp;&nbsp;<b><?php echo FUNCIONES::get_fecha_latina($reserva->res_plazo_fecha);?></b>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Nueva Fecha Plazo:</div>
                            <div id="CajaInput">
                                <input type="text" id="plazo_fecha" name="plazo_fecha" value="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Observacion:</div>
                            <div id="CajaInput">
                                <textarea id="nota_observacion" name="nota_observacion"></textarea>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="button" onclick="javascript:enviar_formulario();" value="Guardar" name="">
                                    <input class="boton" type="reset" value="Cancelar" name="">
                                    <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=reserva';" value="Volver" name="">
                                </center>
                            </div>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                    <script>
                        $("#plazo_fecha").mask("99/99/9999");
                        $('#plazo_fecha').focus();
                        function enviar_formulario(){
                            var fecha=trim($('#plazo_fecha').val());
                            if(fecha===''){
                                $.prompt('Ingrese la Nueva Fecha Plazo');
                                return;
                            }
                            document.frm_sentencia.submit();
                        }
                    </script>
                </form>
                <?php
        }

    }
?>