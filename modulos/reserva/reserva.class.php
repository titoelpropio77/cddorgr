<?php

class RESERVA extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function RESERVA() {
        $this->ele_id = 154;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $num = 0;
        $this->arreglo_campos[$num]["nombre"] = "res_id";
        $this->arreglo_campos[$num]["texto"] = "Nro";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "int_nombre_apellido";
        $this->arreglo_campos[$num]["campo_compuesto"] = "concat(int_nombre,' ',int_apellido)";
        $this->arreglo_campos[$num]["texto"] = "Nombre completo";
        $this->arreglo_campos[$num]["tipo"] = "compuesto";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "urb_id";
        $this->arreglo_campos[$num]["texto"] = "Urbanizacion";
        $this->arreglo_campos[$num]["tipo"] = "combosql";
        $this->arreglo_campos[$num]["sql"] = "select urb_id as codigo,urb_nombre as descripcion from urbanizacion order by urb_nombre asc";        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "man_nro";
        $this->arreglo_campos[$num]["texto"] = "Manzano";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "lot_nro";
        $this->arreglo_campos[$num]["texto"] = "Lote";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "res_fecha";
        $this->arreglo_campos[$num]["texto"] = "Fecha";
        $this->arreglo_campos[$num]["tipo"] = "fecha";
        $this->arreglo_campos[$num]["tamanio"] = 12;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "res_estado";
        $this->arreglo_campos[$num]["texto"] = "Estado";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "Pendiente,Habilitado,Deshabilitado,Expirado,Concretado:Pendiente,Habilitado,Deshabilitado,Expirado,Concretado";
        $colores = array('Pendiente' => '#ff9601', 'Habilitado' => '#019721', 'Concretado' => '#0356ff', 'Deshabilitado' => '#ff0000', 'Devuelto' => '#000');
        $this->arreglo_campos[$num]["colores"] = $colores;
        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "res_of_id";
        $this->arreglo_campos[$num]["texto"] = "Oferta";
        $this->arreglo_campos[$num]["tipo"] = "combosql";
        $this->arreglo_campos[$num]["sql"] = "select of_id as codigo, of_nombre as descripcion from oferta where of_eliminado='No'";

        $num++;
        $this->arreglo_campos[$num]["nombre"] = "res_multinivel";
        $this->arreglo_campos[$num]["texto"] = "Modalidad Reserva";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "no,si:Tradicional,Multinivel";
        $colores = array('no'=>'#db7093','si'=>'#32cd32');
        $this->arreglo_campos[$num]["colores"] = $colores;
        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "res_usu_id";
        $this->arreglo_campos[$num]["texto"] = "Usuario";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'reserva';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('RESERVA DE TERRENO');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        ?>
        <script>
            function completar_datos(id) {
        //                alert('yendo a completar datos de: ' + id);
        //                $(".popup").click(function() {
                var popup = window.open('gestor.php?mod=interno&tarea=MODIFICAR&popup=1&id=' + id, 'reportes', 'left=100,width=800,height=600,top=0,scrollbars=yes');
                popup.focus();
                $.prompt.close();
        //                } );
            }
            function ejecutar_script(id, tarea) {
                if (tarea === 'ANULAR')
                {
                    var estado = $("#estado-" + id).text();
                    estado = estado.replace(/^\s+/g, '').replace(/\s+$/g, '');
                    var monto = '';
                    if (estado !== 'Deshabilitado') {
                        if (estado === 'Habilitado') {
                            monto = '<br />Ingrese Monto que Devolver�<br /><input type="text" size="5" name="monto_devolucion" id="monto_devolucion" value="0" />';
                        }
                        var txt = 'Esta seguro de Deshabilitar la Reserva?' + monto;

                        $.prompt(txt, {
                            buttons: {Deshabilitar: true, Cancelar: false},
                            callback: function(v, m, f) {
                                if (v) {
                                    var devolucion = '';
                                    if (estado === 'Habilitado') {
                                        devolucion = '&monto_devolucion=' + f.monto_devolucion;
                                    }

                                    location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id + devolucion;
                                }
                            }
                        });
                    } else {
                        $.prompt('La reserva y se encuentra Deshabilitada');
                    }
                }

                if (tarea === 'CONCRETAR VENTA')
                {

                    $.post("datos_reserva.php", {tarea: 'concretar', res_id: id},
                    function(respuesta) {
                        var res = JSON.parse(respuesta);
                        if (res.resultado === 'no') {

                            $.prompt(res.mensaje);
                            return;
                        } else {
                            var txt = 'Esta seguro de concretar la Venta?';

                            $.prompt(txt, {
                                buttons: {Concretar: true, Cancelar: false},
                                callback: function(v, m, f) {
                                    if (v) {
                                        location.href = 'gestor.php?mod=venta&tarea=AGREGAR&id_res=' + id + '&concretar=ok';
                                    }

                                }
                            });
                        }
                    }
                    );
        //                    var txt = 'Esta seguro de concretar la Venta?';
        //
        //                    $.prompt(txt, {
        //                        buttons: {Concretar: true, Cancelar: false},
        //                        callback: function(v, m, f) {
        //                            if (v) {                                
        //                                location.href = 'gestor.php?mod=venta&tarea=AGREGAR&id_res=' + id + '&concretar=ok';        
        //                            }
        //
        //                        }
        //                    });
                }
                if (tarea === 'ANTICIPOS RESERVA')
                {
                    $.post("datos_reserva.php", {id: id},
                    function(respuesta) {
                        var res = eval("(" + respuesta + ")");
                        if (res[0].value === 'Pendiente' || res[0].value === 'Habilitado') {
                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                        } else {
                            $.prompt('No puede realizar anticipos a esta reserva.');
                        }
                    });
                }
                if (tarea === 'DEVOLVER')
                {
                    $.post("datos_reserva.php", {id: id},
                    function(respuesta) {
                        var res = eval("(" + respuesta + ")");
                        if (res[0].value === 'Pendiente' || res[0].value === 'Habilitado') {
                            location.href = 'gestor.php?mod=reserva&tarea=' + tarea + '&id=' + id;
                        } else {
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

        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }

        if ($this->verificar_permisos('ANULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DESHABILITAR';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('CONCRETAR VENTA')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CONCRETAR VENTA';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/migrar.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CONCRETAR';
            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('ANTICIPOS RESERVA')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANTICIPOS RESERVA';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/seg.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'LISTADO ANTICIPOS';
            $nun++;
        }
        if ($this->verificar_permisos('PAGAR ANTICIPO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PAGAR ANTICIPO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cuenta.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PAGAR ANTICIPO';
            $nun++;
        }
        if ($this->verificar_permisos('CAMBIAR LOTE')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CAMBIAR LOTE';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cambiar.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR LOTE';
            $nun++;
        }
        if ($this->verificar_permisos('CAMBIAR PROPIETARIO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CAMBIAR PROPIETARIO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cambio_propietario.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR TITULAR';
            $nun++;
        }
        if ($this->verificar_permisos('DEVOLVER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'DEVOLVER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/pagarmenos.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DEVOLVER';
            $nun++;
        }
        if ($this->verificar_permisos('VER DEVOLUCION')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER DEVOLUCION';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search_eg.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER DEVOLUCION';
            $nun++;
        }
        if ($this->verificar_permisos('CAMBIAR VENDEDOR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CAMBIAR VENDEDOR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/edit_user.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR PARAMETROS VARIOS';
            $nun++;
        }
        
//        if ($this->verificar_permisos('PARAMETROS VARIOS')) {
//            $this->arreglo_opciones[$nun]["tarea"] = 'PARAMETROS VARIOS';
//            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
//            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR PARAMETROS VARIOS';
//            $nun++;
//        }
        
        if ($this->verificar_permisos('DOCUMENTOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'DOCUMENTOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/documentos.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DOCUMENTOS';
            $nun++;
        }
    }

    function dibujar_listado() {

        $sql = "SELECT 
			res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,
                        res_monto_referencial,res_moneda,res_estado,res_usu_id,
                        int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,
                        uv_nombre,res_ci,res_multinivel,of_nombre,res_of_id,res_prod_id
			FROM 
			reserva_terreno 
			inner join interno on (res_int_id=int_id)
			inner join lote on (res_lot_id=lot_id)
			inner join uv on (uv_id=lot_uv_id)
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
			left join oferta on (res_of_id=of_id)
			where lot_nro not like 'NET-%'";

        $this->set_sql($sql, ' order by res_id desc ');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nro Reserva</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Persona</th>
            <th>Vendedor</th>
            <th>Urbanizacion</th>
            <th>Uv</th>
            <th>Manzano</th>
            <th>Lote</th>
            <th>Cuota Inicial</th>
            <th>Anticipos</th>            
            <th>Modalidad</th>
            <th>Oferta</th>
            <th>Producto</th>
            <th>Estado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();
        $color = array('Pendiente' => '#ff9601', 'Habilitado' => '#019721', 'Concretado' => '#0356ff', 'Deshabilitado' => '#ff0000', 'Devuelto' => '#000');
        $color_mod = array('si' => '#32cd32', 'no' => '#db7093');
        $arr_estados_val_prod = array('Pendiente','Habilitado','Concretado');
        
        for ($i = 0; $i < $this->numero; $i++) {


            $objeto = $this->coneccion->get_objeto();
            $operaciones = array();
            if ($objeto->res_estado == 'Pendiente') {
                $operaciones[] = "VER DEVOLUCION";
                $operaciones[] = "DEVOLVER";
            } elseif ($objeto->res_estado == 'Habilitado') {
                $operaciones[] = "VER DEVOLUCION";
                $operaciones[] = "DEVOLVER";
            } elseif ($objeto->res_estado == 'Concretado') {
                $operaciones[] = "CONCRETAR VENTA";
                $operaciones[] = "PAGAR ANTICIPO";
                $operaciones[] = "ANULAR";
                $operaciones[] = "DEVOLVER";
                $operaciones[] = "CAMBIAR PROPIETARIO";
                $operaciones[] = "CAMBIAR LOTE";
                $operaciones[] = "VER DEVOLUCION";
                $operaciones[] = 'CAMBIAR VENDEDOR';
            } elseif ($objeto->res_estado == 'Deshabilitado') {
                $operaciones[] = "CONCRETAR VENTA";
                $operaciones[] = "PAGAR ANTICIPO";
                $operaciones[] = "ANULAR";
                $operaciones[] = "CAMBIAR PROPIETARIO";
                $operaciones[] = "CAMBIAR LOTE";
                $operaciones[] = "VER DEVOLUCION";
                $operaciones[] = 'CAMBIAR VENDEDOR';
            } elseif ($objeto->res_estado == 'Devuelto') {
                $operaciones[] = "CONCRETAR VENTA";
                $operaciones[] = "PAGAR ANTICIPO";
                $operaciones[] = "ANULAR";
                $operaciones[] = "CAMBIAR PROPIETARIO";
                $operaciones[] = "CAMBIAR LOTE";
                $operaciones[] = "DEVOLVER";
                $operaciones[] = 'CAMBIAR VENDEDOR';
            }

            $anticipos = FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo 
                from reserva_pago 
                where respag_res_id='$objeto->res_id' and respag_estado='Pagado'") * 1;

            echo '<tr>';

            echo "<td>";
            echo $objeto->res_id;
            echo "</td>";
            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->res_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->res_hora;
            echo "</td>";
            echo "<td>";
            echo $objeto->int_nombre . " " . $objeto->int_apellido;
            echo "</td>";
            echo "<td>";
            echo $this->nombre_persona_vendedor($objeto->res_vdo_id);
            echo "</td>";
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
            echo number_format($objeto->res_ci, 2, '.', ',') . " USD";
            echo "</td>";
            echo "<td>";
            echo number_format($anticipos, 2, '.', ',') . " USD";
            echo "</td>";
//            echo "<td>";
//            if ($objeto->res_moneda == '1')
//                echo 'Bolivianos'; if ($objeto->res_moneda == '2')
//                echo 'Dolares';
//            echo "</td>";
            
            $modalidad = ($objeto->res_multinivel == 'si')?'Multinivel':'Tradicional';
            
            echo "<td id='mod-$objeto->res_id'>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color_mod[$objeto->res_multinivel]}'>$modalidad</span>";
            echo "</td>";
            
            echo "<td>";
            if ($objeto->res_of_id > 0) {
                echo "<span style='background-color:yellow'>$objeto->of_nombre</span>";
            } else {
                echo "Ninguna";
            }
            echo "</td>";
            
            $txt_producto = '';
            if ($objeto->res_prod_id > 0) {
                $uproducto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$objeto->res_prod_id'");
                $txt_producto = $uproducto->uprod_nombre . "";
                $color_producto = "background-color:blue";
            }
                        
            echo "<td>";
            if ($txt_producto != '' && in_array($objeto->res_estado, $arr_estados_val_prod) === TRUE) {
                echo "<span style='padding:1px 3px ;color:#fff;font-size:9px; $color_producto'>$txt_producto</span>";
            }
            echo "&nbsp;</td>";
            
            echo "<td id='estado-$objeto->res_id'>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->res_estado]}'>$objeto->res_estado</span>";
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->res_id, "", $operaciones);
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

        if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') {
            $sql = "select urb_id as id, urb_nombre as nombre from lote l, zona z, urbanizacion u 
                    where l.lot_zon_id=z.zon_id and z.zon_urb_id=u.urb_id and l.lot_id='" . $_GET['lote_reserva'] . "' and urb_ventas_internas='Si';";
            $urbanizacion = FUNCIONES::objeto_bd_sql($sql);
            if (!$urbanizacion) {
                $mensaje = "La Urbanizacion no esta habilitada para venderse internamente";
                $this->mora_genulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
                return;
            }
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
            function set_valor_interno(data) {
                document.frm_sentencia.ven_int_id.value = data.id;
                document.frm_sentencia.int_nombre_persona.value = data.nombre;
            }

            function reset_interno() {
                document.frm_sentencia.ven_int_id.value = "";
                document.frm_sentencia.int_nombre_persona.value = "";
            }
            function enviar_formulario()
            {
                var persona = document.frm_sentencia.ven_int_id.value;
                var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                var monto_ref = document.frm_sentencia.ven_monto_referencial.value;

                if ($('#multinivel option:selected').val() === 'si') {
                    $('#vendedor').val($('#afiliado option:selected').val());
                } else {
                    $('#vendedor').val($('#vendedor_id option:selected').val());
                }

                var vendedor = $('#vendedor').val();
                if (persona !== '' && vendedor !== '' && lote !== '' && monto_ref !== '') {
                    document.frm_sentencia.submit();
                } else {
                    $.prompt('Para registrar una Reserva debe ingresar Persona , Vendedor, Lote y el Monto referencial acordado.', {opacity: 0.8});
                }
            }

            function validar_parametros_multinivel(res){
                
                console.log(res);
                
                var b = true;
                if (res.response === 'no') {
                    b = false;
                }
                
                if (b === false) {
                    $('#multinivel option[value="si"]').css('display','none');
                } else {
                    $('#multinivel option[value="si"]').css('display','block');
                }
            }

            function cargar_datos(valor)
            {
                var datos = valor;
                var val = datos.split('-');
                
                $.get('ajax_comisiones.php',{tarea:'parametros_mlm', lot_id:val[0]},
                    function(respuesta){
                        console.log(respuesta);
                        var res = JSON.parse(respuesta);
                        
                        validar_parametros_multinivel(res);
                        
                        document.frm_sentencia.superficie.value = val[1];

                        var m2 = 0;
                        //                if ($('#multinivel option:selected').val() === 'no') {
                        if (true) {
                            document.frm_sentencia.valor.value = val[2];
                            m2 = val[2];
                        } else {
                            var porc_red = val[6] * 1 / 100;
                            var porc_empresa = val[7] * 1 / 100;
                            m2 = (val[2] * 1) + (val[2] * porc_red);
                            m2 = m2 + (m2 * porc_empresa);
                            var seguro = parseFloat(val[8]) * parseFloat(val[9]);
                            m2 += seguro / parseFloat(val[1]);
                            m2 = m2.toFixed(2);
                            document.frm_sentencia.valor.value = m2;

                        }

                        document.frm_sentencia.valor_terreno.value = parseFloat(val[1]) * parseFloat(m2);
                        document.frm_sentencia.ven_moneda.value = val[4];

                        if (val[4] === 1) {
                            var simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;&nbsp;Valor m2 Bs&nbsp;&nbsp;&nbsp;';
                            var simbolo_moneda_vt = 'Valor del Terreno Bs';
                            var simbolo_moneda_desc = 'Bs';
                        } else {
                            var simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;Valor m2 $us&nbsp;&nbsp;';
                            var simbolo_moneda_vt = 'Valor del Terreno $us';
                            var simbolo_moneda_desc = '$us';
                        }

                        $('#simb_moneda_vm2').html(simbolo_moneda_vm2);
                        $('#simb_moneda_vt').html(simbolo_moneda_vt);
                        $('#simb_moneda_descuento').html('&nbsp;' + simbolo_moneda_desc);


                        if (val[5] === 'Vivienda') {
                            $('#seccion_sup_valor').css("visibility", "hidden");
                        } else {
                            $('#seccion_sup_valor').css("visibility", "visible");
                        }

                        calcular_monto();
                                                
                        if ($('#prod_id').length > 0) {
                            $('#prod_id').trigger('change');
                        }
                    }
                );
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

            function cargar_uv(id) {
                var valores = "tarea=uv&urb=" + id;
                ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
                
                
                var urb_id = $('#ven_urb_id option:selected').val();

                if (urb_id*1 > 0) {
                    console.log('selecciono urbanizacion ' + urb_id);
                    agregar_opciones_producto(urb_id);                    
                } else {
                    console.log('no selecciono urbanizacion');
                }
            }

            function cargar_manzano(id, uv) {
                var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;
                ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
            }

            function cargar_lote(id, uv) {
                var valores = "tarea=lotes&man=" + id + "&uv=" + uv;
                ejecutar_ajax('ajax.php', 'lote', valores, 'POST');                                
                
                if ($('#prod_id').length > 0) {
                    $('#prod_id option[value=""]').attr('selected', true);
                    $('#prod_id').trigger("chosen:updated");
                }
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

        </script>

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
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
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
                                    echo 'No se le asigno ninguna personas, para poder cargar las personas.';
                                }
                                ?>
                                <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                                <input type="hidden" name="tca" id="tca" size="5" value="<?php echo $this->tc; ?>" readonly="readonly">
                            </div>
                        </div>


                        <input readonly="true" type="hidden" name="ven_moneda" id="ven_moneda" size="5" value="">

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizac&oacute;n</div>
                            <div id="CajaInput">
                                <?php
                                if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') {
                                    $sql = "select urb_id as id, urb_nombre as nombre from lote l, zona z, urbanizacion u 
                                            where l.lot_zon_id=z.zon_id and z.zon_urb_id=u.urb_id and l.lot_id='" . $_GET['lote_reserva'] . "' and urb_ventas_internas='Si';";
                                    $urbanizacion = FUNCIONES::objeto_bd_sql($sql);
                                    ?>
                                    <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" >
                                        <option value="<?php echo $urbanizacion->id; ?>"><?php echo $urbanizacion->nombre; ?></option>                                                                            
                                    </select>
                                    <?php
                                } else {
                                    ?>
                                    <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where urb_ventas_internas='Si' and urb_eliminado='No'", $_POST['ven_urb_id']);
                                        ?>
                                    </select>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
                            <div id="CajaInput">
                                <div id="uv">
                                    <?php
                                    if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') {
                                        $sql = "select uv_id as id, uv_nombre as nombre from lote l, uv uv 
                                                where l.lot_uv_id=uv.uv_id and l.lot_id='" . $_GET['lote_reserva'] . "';";
                                        $uv = FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                        <select style="width:200px;" name="ven_uv_id" id="ven_uv_id" class="caja_texto" >
                                            <option value="<?php echo $uv->id; ?>">Uv Nro: <?php echo $uv->nombre; ?></option>                                                                            
                                        </select>
                                        <?php
                                    } else {
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
                                    if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') {
                                        $sql = "select man_id as id,man_nro as nombre  from lote l, manzano m 
                                                where l.lot_man_id=m.man_id and l.lot_id='" . $_GET['lote_reserva'] . "';";
                                        $mz = FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                        <select style="width:200px;" name="ven_man_id" id="ven_man_id" class="caja_texto" >
                                            <option value="<?php echo $mz->id; ?>">Manzano Nro: <?php echo $mz->nombre; ?></option>                                                                            
                                        </select>
                                        <?php
                                    } else {
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
                                    if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') {
                                        $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre 
                                                from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_id='" . $_GET['lote_reserva'] . "'";
                                        $mz = FUNCIONES::objeto_bd_sql($sql);
                                        ?>
                                        <select style="width:200px;" name="ven_lot_id" id="ven_lot_id" class="caja_texto" >
                                            <option value="<?php echo $mz->id; ?>">Uv Nro: <?php echo $mz->nombre; ?></option>                                                                            
                                        </select>                                            
                                        <?php
                                    } else {
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

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Multinivel?</div>
                            <div id="CajaInput">
                                <select id="multinivel" name="multinivel">
                                    <option value="no">No</option>
                                    <option value="si">Si</option>
                                </select>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Oferta</div>
                            <div id="CajaInput">
                                <select id="oferta" name="oferta">
                                    <option value="">Ninguna</option>
                                    <?php
                                    $hoy = date('Y-m-d');
                                    $sql_of = "select * from oferta where of_eliminado='No'
                                        and of_fecha_ini<='$hoy' and of_fecha_fin>='$hoy'";
                                    $l_ofertas = FUNCIONES::lista_bd_sql($sql_of);

                                    foreach ($l_ofertas as $of) {
                                        ?>
                                        <option title="<?php echo $of->of_descripcion; ?>" value="<?php echo $of->of_id; ?>"><?php echo $of->of_nombre; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div id="ContenedorDiv" class="div_vendedor">
                            <div class="Etiqueta"><span class="flechas1">*</span>Vendedor</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="vendedor_id" id="vendedor_id" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre!='AFILIADOS';"; ?>
                                    <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                    <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                        <?php $objeto = $vendedores1->get_objeto(); ?>
                                        <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php $vendedores1->siguiente(); ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!--Inicio-->
                        <div id="ContenedorDiv" class="div_afiliado">
                            <div class="Etiqueta"><span class="flechas1">*</span>Patrocinador</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="afiliado" id="afiliado" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where vdo_estado='Habilitado'
                                               and ven_estado in ('Pendiente','Pagado')
                                               and vgru_nombre='AFILIADOS'"; ?>
                                    <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                    <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                        <?php $objeto = $vendedores1->get_objeto(); ?>
                                        <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php $vendedores1->siguiente(); ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->   

                        <input readonly="true" type="hidden" name="vendedor" id="vendedor" size="5" value="" >
                        
                        <div id="ContenedorDiv" class="div_producto div_producto_list" style="display: block;">
                            <div class="Etiqueta" >Productos:</div>
                            <div id="CajaInput" style="display:block;">                                
                                <select style="width:350px;" id="prod_id" name="prod_id">
                                    <option value="">Seleccione</option>                                                                        
                                </select>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nota</div>
                            <div id="CajaInput">
                                <textarea class="area_texto" name="nota" id="nota" cols="31" rows="3"><?php echo $_POST['nota'] ?></textarea>
                            </div>
                        </div>
                        
                        <div class="Subtitulo">Info. Lote</div>
                        <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="seccion_sup_valor">
                                <div class="Etiqueta" >Superficie</div>
                                <div id="CajaInput">
                                    <input readonly="true" type="text" name="superficie" id="superficie" size="5" value="" >
                                </div>
                                <div id="CajaInput">
                                    <span id="simb_moneda_vm2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Valor m2&nbsp;&nbsp;&nbsp;</span>
                                    <input  readonly="true" type="text" name="valor" id="valor" size="5" value=""   onKeyUp="javascript:calcular_valor_terreno();">
                                </div>

                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" id="simb_moneda_vt"><span class="flechas1">*</span>Valor del Terreno</div>
                            <div id="CajaInput">
                                <input readonly="true" type="text" name="valor_terreno" id="valor_terreno" size="5" value="">
                            </div>


                            <div id="CajaInput" hidden="" style="display: none;">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto Referencial Acordado&nbsp;&nbsp;&nbsp;</span><input type="text" name="ven_monto_referencial" id="ven_monto_referencial" size="5" value="0" onkeypress="return ValidarNumero(event);">
                            </div>
                            <!--
        <div id="CajaInput">
                              &nbsp;&nbsp;&nbsp;Cuota Inicial&nbsp;&nbsp;&nbsp;<input type="text" name="cuota_inicial" id="cuota_inicial" size="5" value=""  onKeyPress="return ValidarNumero(event);">
                       </div>
                            -->
                        </div>
                        </div>
                        
                        <div class="Subtitulo div_producto" style="display: none;">Info. Producto</div>
                        <div id="ContenedorSeleccion" style="display: none;" class="div_producto">
                            <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                <div class="Etiqueta" >Precio Producto:</div>
                                <div id="CajaInput">
                                    <input readonly="true" type="text" name="uprod_precio" id="uprod_precio" size="8" value="" >
                                </div>
                            </div>

                            <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                <div class="Etiqueta" >Descuento Producto:</div>
                                <div id="CajaInput">
                                    <input type="text" name="uprod_descuento" id="uprod_descuento" size="8" value="" >
                                </div>
                            </div>

                            <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                <div class="Etiqueta" >Monto Producto:</div>
                                <div id="CajaInput">
                                    <input readonly type="text" name="uprod_monto" id="uprod_monto" size="8" value="" >
                                </div>
                                
                                <div class="Etiqueta" >Monto Total:</div>
                                <div id="CajaInput">
                                    <div class="read-input" id="txt_monto_total"></div>
                                </div>
                            </div>
                            
                            <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                <div class="Etiqueta" >Cuota Inicial:</div>
                                <div id="CajaInput">
                                    <input readonly type="text" name="uprod_ci" id="uprod_ci" size="8" value="" >
                                </div>
                            </div>

                            <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                <div class="Etiqueta" style="min-width: 30px; width: 60px;">Observaci&oacute;n</div>
                                <div id="CajaInput">
                                    <textarea name="ven_observacion_producto" id="ven_observacion_producto"></textarea>
                                </div>
                            </div> 

                        </div>

                    </div>
                    <?php 
                    if (isset($_GET['lote_reserva']) && $_GET['lote_reserva'] != '') { 
                    ?>
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
        <script>
            $('#vendedor_id').chosen({
                allow_single_deselect: true
            });

            $('#afiliado').chosen({
                allow_single_deselect: true
            });
            
            $('#prod_id').chosen({
                allow_single_deselect: true
            }).change(function(){
                var opcion = $('#prod_id option:selected');
                console.log($(opcion).attr('data-info'));
                var prod_id = $(opcion).val();

                if (prod_id != '') {
                    var info = $(opcion).attr('data-info');
                    var datos_prod = info.split('|');

                    $('.div_producto').css('display', 'block');
                    $('#uprod_precio').val(datos_prod[3]);
                    $('#uprod_monto').val(datos_prod[3]);
                    $('#uprod_ci').val(datos_prod[4]);
                    mask_decimal('#uprod_descuento', null);
                    calcular_monto_total();
                    $('#uprod_descuento').keyup(function(){
                        calcular_monto_total();
                    });
                } else {
                    console.log('no selecciono producto');
                    $('#uprod_precio').val(0);
                    $('#uprod_descuento').val(0);
                    $('#uprod_monto').val(0);
                    $('#uprod_ci').val(0);
                    $('.div_producto').css('display', 'none');
                    $('.div_producto_list').css('display', 'block');                    
                }
            });
            
            function calcular_monto_total(){
                var valor_terreno = $('#valor_terreno').val()*1;
                var descuento_prod = $('#uprod_descuento').val()*1;
                var uprod_precio = $('#uprod_precio').val()*1;
                
                var prod_monto = uprod_precio - descuento_prod;
                $('#uprod_monto').val(prod_monto);
                var monto_total = valor_terreno + prod_monto;
                $('#txt_monto_total').text(monto_total.toFixed(2));
            }
            
            function agregar_opciones_producto(urb_id){
                
                var archivo = "AjaxRequest.php";
                var params = {peticion:'urb_productos', urb_id:urb_id};
                $.get(archivo,params, function(respuesta){
                    
                    var res = JSON.parse(respuesta);
                    var productos = res.productos;
                                        
                    var s_opciones = '<option value="">Seleccione</option>';
                    $('#prod_id').children().remove();                                
                    
                    for (var i = 0; i < productos.length; i++) {
                        var prod = productos[i];
                        var sep = "|";
                        var info = prod.uprod_id + sep + prod.uprod_nombre + sep + prod.uprod_descripcion + sep +
                        prod.uprod_precio + sep + prod.uprod_ci + sep + prod.uprod_superficie + sep +
                        prod.uprod_moneda;
                        var s_op = '<option value="'+prod.uprod_id+'" data-info="'+info+'">' + prod.uprod_nombre + '('+ prod.uprod_descripcion +')</option>';
                        s_opciones += s_op;
                        console.log(s_op);
                    }
                    $('#prod_id').append(s_opciones);
                    $('#prod_id').trigger("chosen:updated");
                });
            }
//            agregar_opciones_producto();
            $('#multinivel').change(function() {
                var datos = $('#ven_lot_id option:selected').val();
                if (typeof datos !== 'undefined') {
                    cargar_datos(datos);
                }
                var op_mul = $('#multinivel option:selected').val();

                if (op_mul === 'si') {
                    $('.div_vendedor').hide();
                    $('.div_afiliado').show();
                    $('#afiliado option[value=""]').attr('selected', true);
                } else {
                    $('.div_afiliado').hide();
                    $('.div_vendedor').show();
                    $('#vendedor option[value=""]').attr('selected', true);
                }
            });
            $('#multinivel').trigger('change');

//            $(function() {
//                $(document).tooltip();
//            });
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

    function obtener_id_interno_tbl_usuario($usu_id) {
        $conec = new ADO();

        $sql = "SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_per_id;
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
        $fecha_plazo = setear_fecha(strtotime($objeto->res_plazo_fecha));
        ;
//            $tc = $objeto->ven_tipo_cambio;
        $body = '<div id="contenido_reporte" style="clear:both;">
                        <center>
                        <table style="font-size:12px; border:1px solid #bfc4c9" cellpadding="5" cellspacing="0" width="100%">
                            <tbody><tr>
                            <td width="33%"><strong>' . _nombre_empresa . '</strong><br>

                            </td>';
        $body .= '
                    <td width="33%"><center><p align="center"><strong></strong></p><h3><strong>NOTA DE RESERVA</strong></h3><p></p></center></td>
            <td><div align="right"><img src="imagenes/micro.png"></div></td>
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

//        FUNCIONES::print_pre($_POST);
//        return FALSE;
        
        $conec = new ADO();
        if (true) {
            $valores = explode("-", $_POST['ven_lot_id']);
            $id_lote = $valores[0]; // piece1
            //echo $pieces[1]; // piece2

            $verificar = NEW VERIFICAR;

            $parametros[0] = array('res_lot_id', 'res_estado');
            $parametros[1] = array($id_lote, 'Habilitado');
            $parametros[2] = array('reserva_terreno');

            $exito = true;

            $res_fecha = date('Y-m-d');
            $res_plazo_fecha = FUNCIONES::sumar_dias(1, $res_fecha);
            if ($verificar->validar($parametros)) {
                $conec = new ADO();

                $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion
                    where urb_id=$_POST[ven_urb_id]");

                $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id=$id_lote");
                $precio_terreno = $lote->lot_superficie * $_POST[valor];

                $oferta = $_POST[oferta] * 1;
                $ci = 0;

                if ($oferta == 0) {
                    if ($_POST[multinivel] == 'si') {
                        $plazo = $urbanizacion->urb_nro_cuotas_multinivel;
                        $params_mlm = FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id=$id_lote");
                        
                        if ($params_mlm->lm_anticipo_tipo == 'fijo') {
                            $ci = $params_mlm->lm_anticipo_min;
                        } else {
                            $ci = $precio_terreno * $params_mlm->lm_anticipo_min / 100;
                        }
                                                
                    } else {
                        $plazo = $urbanizacion->urb_nro_maxplazo * 12;
                        $ci = $urbanizacion->urb_monto_anticipo;
                    }
                } else {
                    $obj_oferta = FUNCIONES::objeto_bd_sql("select * from oferta where of_id=$oferta");
                    if ($_POST[multinivel] == 'si') {
                        $plazo = $urbanizacion->urb_nro_cuotas_multinivel;
                    } else {
                        $plazo = $urbanizacion->urb_nro_maxplazo * 12;
                    }

                    if ($obj_oferta->of_forma_ci == 'cuota_mensual') {
                        $ci = round(FUNCIONES::get_cuota($precio_terreno, $urbanizacion->urb_interes_anual, $plazo), 2);
                    } else if ($obj_oferta->of_forma_ci == 'monto_fijo') {
                        $ci = $obj_oferta->of_monto_fijo;
                    } else if ($obj_oferta->of_forma_ci == 'porcentaje') {
                        $ci = $precio_terreno * $obj_oferta->of_porc / 100;
                    }
                }

                
                $cm = round(FUNCIONES::get_cuota($precio_terreno - $ci, $urbanizacion->urb_interes_anual, $plazo - 1), 2);

                if ($_POST[prod_id]*1 > 0) {
                    $cm = 0;
                    $ci = $_POST[uprod_ci]*1;
                    $prod_id = $_POST[prod_id]*1;
                    $monto_prod = $_POST[uprod_monto]*1;
                    $precio_prod = $_POST[uprod_precio]*1;
                    $descuento_prod = $_POST[uprod_descuento]*1;
                    $nota_prod = $_POST[ven_observacion_producto];
                }
                
//                $sql = "insert into reserva_terreno (res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_anticipo,res_moneda,res_estado,res_usu_id,res_plazo_fecha) values ('" . $_POST['ven_int_id'] . "','" . $_POST['vendedor'] . "','" . $id_lote . "','" . date('Y-m-d') . "','" . date('H:i') . "','" . $_POST['ven_anticipo'] . "','" . $_POST['ven_moneda'] . "','Habilitado','" . $this->usu->get_id() . "','" . $_POST['fecha_plazo_final'] . "')";
                $sql = "insert into reserva_terreno (
                            res_int_id,res_vdo_id,res_lot_id,
                            res_fecha,res_hora,res_monto_referencial,
                            res_moneda,res_estado,res_usu_id,
                            res_plazo_fecha,res_plazo_hora,res_nota, 
                            res_vdo_ext,res_urb_id,res_monto_m2,res_multinivel,
                            res_ci,res_of_id,res_cm,res_monto_producto,
                            res_valor_producto,res_descuento_producto,res_prod_id,
                            res_nota_prod
                        )values (
                            '" . $_POST['ven_int_id'] . "','" . $_POST['vendedor'] . "','" . $id_lote . "',
                            '" . $res_fecha . "','" . date('H:i') . "','" . $_POST['ven_monto_referencial'] . "',
                            '" . $_POST['ven_moneda'] . "','Pendiente','$_SESSION[id]',
                            '" . $res_plazo_fecha . "','" . date('H:i') . "','" . $_POST['nota'] . "',
                            '$_POST[vendedor2]','$_POST[ven_urb_id]',$_POST[valor],'$_POST[multinivel]',
                            '$ci','$oferta','$cm','$monto_prod','$precio_prod','$descuento_prod','$prod_id',
                            '$nota_prod'
                        )";

//            echo $sql;
                $conec->ejecutar($sql, true, true);
                $llave = ADO::$insert_id;

                //Cambio Estado de 'Reservado' el Lote.
                $sql = "update lote set lot_estado='Reservado' where lot_id=$id_lote";

                $conec->ejecutar($sql);
                $mensaje = 'Reserva de Terreno Agregado Correctamente';
            } else {
                $exito = false;
                $mensaje = 'No se pudo reservar el Terreno, por que el Lote que seleccion�, ya se encuentra Reservado.';
            }
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            if ($exito) {
                $this->nota_reserva($llave);
            }
        } else {

            $mensaje = 'No puese realizar reserva de terreno, por que usted no esta registrado como cajero.';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }

    function total_anticipo($res_id) {
        $pagos = FUNCIONES::lista_bd_sql("select * from reserva_pago where respag_res_id='$res_id' and respag_estado='Pagado'");
        $sum_total = 0;
        foreach ($pagos as $pag) {
            if ($pag->respag_moneda == 2) {
                $sum_total+=$pag->respag_monto;
            } elseif ($pag->respag_moneda == 1) {
                $tc_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$pag->respag_fecha' and tca_mon_id=2");
                $sum_total+=$pag->respag_monto / $tc_usd;
            }
        }
        return $sum_total;
    }

    function anular() {
        $this->formulario->dibujar_tarea();
        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
        if ($reserva->res_estado == 'Pendiente' || $reserva->res_estado == 'Habilitado') {
            if ($_POST[fecha]) {
                $this->guardar_anulacion($reserva);
            } else {
                $this->frm_anulacion($reserva);
            }
        } else {
            $mensaje = 'No se pudo <b>Deshabilitar la Reserva de Terreno</b> por que la Reserva ya se encuentra en estado <b>Deshabilitado, Expirado o Concretado</b>';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, "", "error");
        }
    }

    function guardar_anulacion($reserva) {
//        FUNCIONES::print_pre($_POST);
        $fecha_cmp = FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $conec = new ADO();
        $lote = $reserva->res_lot_id;
        $sql = "update lote set lot_estado='Disponible' where lot_id='$lote'";
        $conec->ejecutar($sql);
        $sql = "update reserva_terreno set res_estado='Deshabilitado', res_anu_fecha='$fecha_cmp', res_anu_usu='$_SESSION[id]', res_anu_glosa='$_POST[glosa]' where res_id='$reserva->res_id'";
        $conec->ejecutar($sql);
        $mensaje = 'La reserva de terrenos fue deshabilitada correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);

        $monto_anticipo = $this->total_anticipo($reserva->res_id);
        if ($monto_anticipo > 0) {

            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            $referido = FUNCIONES::interno_nombre($reserva->res_int_id);
            $_glosa = "";
            if ($_POST[glosa]) {
                $_glosa = ": $_POST[glosa]";
            }
            $glosa = "Anulacion de la Reserva Nro $reserva->res_id $_glosa";
            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$reserva->res_urb_id");


            $data = array(
                'moneda' => 2,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $fecha_cmp,
                'glosa' => $glosa,
                'interno' => $referido,
                'tabla_id' => $reserva->res_id,
                'urb' => $urb,
                'monto' => $monto_anticipo,
            );

            $comprobante = MODELO_COMPROBANTE::anular_reserva($data);

            COMPROBANTES::registrar_comprobante($comprobante);
        }
    }

    function frm_anulacion($reserva) {
        ?>
        <script src="js/util.js"></script>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=reserva&tarea=ANULAR&id=<?php echo $reserva->res_id ?>" name="frm_sentencia">
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <center>
                                <b>Esta seguro de anular la Reserva Nro <?php echo "'$reserva->res_id'"; ?> de <?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?> con un Anticipo de <?php echo $this->total_anticipo($reserva->res_id); ?> $us?</b>
                                <br><br>
                            </center>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Glosa</div>
                            <div id="CajaInput">
                                <textarea type="text" id="glosa" name="glosa" ></textarea>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Fecha de Anulacion</div>
                            <div id="CajaInput">
                                <input type="text" id="fecha" name="fecha" value="<?php echo date('d/m/Y') ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="button" onclick="location.href = 'gestor.php?mod=reserva&tarea=ACCEDER'" value="Volver" name="">
                                    <input class="boton" type="button" onclick="javascript:enviar_formulario()" value="Anular Reserva" name="">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            $('#fecha').mask('99/99/9999');
            function enviar_formulario() {
                var fecha = $('#fecha').val();
                if (fecha !== '') {
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response !== "ok") {
                            $.prompt(dato.mensaje);
                        } else {
                            document.frm_sentencia.submit();
                        }
                    });
                } else {
                    $.prompt('Ingres una fecha de anulacion');
                }


            }
        </script>

        <?php
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

    function descripcion_terreno($lot_id) {
        $conec = new ADO();

        $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
		from 
		lote
		inner join zona on (lot_id='" . $lot_id . "' and lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)	
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on(man_urb_id=urb_id)
		";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
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

    //----------------------------------------- TAREA - ANTICIPOS -------------------------------------------------------//


    function guardar_anticipo() {
        $conec = new ADO();

        $sql = "select * from reserva_terreno where res_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $estado = $objeto->res_estado;

        if ($estado == 'Habilitado' || $estado == 'Pendiente') {

            if (true) {

                $fecha = FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);


//                $this->obtener_datos_reserva($_GET['id'], $moneda, $interno, $res_lot_id);
                $reserva = FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
                $moneda = $_POST['moneda'];
//                $interno = $reserva->res_int_id;
                $res_lot_id = $reserva->res_lot_id;
                $_POST['ven_lot_id'] = $res_lot_id;
                $des = $this->descripcion_terreno($res_lot_id);
                $glosa = $des;
                $nro_recibo = FUNCIONES::nro_recibo($fecha);
                $respag_tabla=$_POST[respag_tabla];
                $respag_tabla_id=$_POST[respag_tabla_id];
                $sql = "insert into reserva_pago(respag_monto,respag_moneda,respag_fecha,respag_hora,
                                                respag_estado,respag_usu_id,respag_glosa,respag_res_id,respag_recibo,
                                                respag_suc_id,respag_tabla,respag_tabla_id
                                        )values (
                                                '" . $_POST['respag_monto'] . "','" . $_POST['moneda'] . "','" . $fecha . "','" . date('H:i:s') . "',
                                                'Pagado','$_SESSION[id]','$glosa','" . $_GET['id'] . "','$nro_recibo',
                                                '$_SESSION[suc_id]','$respag_tabla','$respag_tabla_id'
                                               )";
                //echo $sql;
                $conec->ejecutar($sql, true, true);

                $llave = ADO::$insert_id;

                include_once 'clases/recibo.class.php';
                $data_recibo = array(
                    'recibo' => $nro_recibo,
                    'fecha' => $fecha,
                    'monto' => $_POST['respag_monto'],
                    'moneda' => $_POST['moneda'],
                    'tabla' => 'reserva_pago',
                    'tabla_id' => $llave
                );
                RECIBO::insertar($data_recibo);

//                $interno=FUNCIONES::atributo_bd("interno", "int_id=".$_POST['ven_int_id'], "concat(int_nombre,' ',int_apellido )");
                $fecha_cmp = FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);
                $monto = $_POST['respag_monto'] * 1;

                $urb = FUNCIONES::objeto_bd_sql("select urbanizacion.* from urbanizacion,manzano,lote,reserva_terreno where res_lot_id=lot_id and lot_man_id=man_id and man_urb_id=urb_id and res_id=$_GET[id]");
                if (true) {
                    include_once 'clases/modelo_comprobantes.class.php';
                    include_once 'clases/registrar_comprobantes.class.php';
                    $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$reserva->res_int_id'");
//                    $glosa = "Pago de Reserva: " . $glosa;
                    $glosa = "Pago de la Reserva Nro. $llave - $des - $referido - Rec. $nro_recibo";
                    $params = array(
                        'tabla' => 'reserva_pago',
                        'tabla_id' => $llave,
                        'fecha' => $fecha_cmp,
                        'moneda' => $moneda,
                        'ingreso' => true,
                        'une_id' => $urb->urb_une_id,
                        'glosa' => $glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
                    );
                    $detalles = FORMULARIO::insertar_pagos($params);

                    $data = array(
                        'moneda' => $moneda,
                        'ges_id' => $_SESSION[ges_id],
                        'fecha' => $fecha_cmp,
                        'glosa' => $glosa,
                        'interno' => $referido,
                        'tabla_id' => $llave,
                        'urb' => $urb,
                        'monto' => $monto,
                        'detalles' => $detalles,
                    );

                    $comprobante = MODELO_COMPROBANTE::anticipo($data);

                    COMPROBANTES::registrar_comprobante($comprobante);
                }

                if ($estado == 'Pendiente') {
                    $sqlres = "update reserva_terreno set res_estado='Habilitado' where res_id='" . $_GET['id'] . "'";
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

    function nota_reserva($res_id) {

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
        $sql = "select res_fecha,res_usu_id,res_moneda,res_plazo_fecha,res_multinivel,res_nota,
                res_plazo_hora,res_monto_referencial,res_vdo_id,res_prod_id,
                (res_monto_m2*lot_superficie)as monto,res_int_id,
                res_estado,int_nombre,int_apellido,urb_nombre,res_monto_m2,
                man_nro,lot_nro,zon_precio,lot_superficie,zon_nombre,
                uv_nombre,res_monto_producto,res_descuento_producto,res_valor_producto		
                from 
                reserva_terreno 
                inner join interno on(res_id = '$res_id' and res_int_id=int_id)
                inner join lote on(res_lot_id=lot_id)
                inner join zona on(lot_zon_id=zon_id)
                inner join uv on(lot_uv_id=uv_id)
                inner join manzano on (lot_man_id=man_id)                                
                inner join urbanizacion on (man_urb_id=urb_id)";

        if ($this->verificar_permisos('ACCEDER')) {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }


//            echo $sql;
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $objeto = $conec->get_objeto();
        $myday = setear_fecha(strtotime($objeto->res_fecha));
        $fecha_plazo = setear_fecha(strtotime($objeto->res_plazo_fecha));
        $desc_mod_reserva = ($objeto->res_multinivel == 'si') ? " - MLM" : " - TRAD";
        ?>
        <div id="contenido_reporte" style="clear:both;">
            <center>
                <table style="font-size:12px" cellpadding="5" cellspacing="0" width="100%">
                    <tbody><tr>
                            <td width="33%"><strong><?php echo _nombre_empresa; ?></strong><br></td>

                            <td width="33%"><center><p align="center"><strong></strong></p><h3><strong>NOTA DE RESERVA</strong></h3><p></p></center></td>
                    <td><div align="right"><img src="imagenes/micro.png"></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong><br>Nro de Reserva: </strong><?php echo $res_id . $desc_mod_reserva; ?><br>
                            <?php
                            if ($objeto->res_int_id <> 0) {
                                $persona = $objeto->int_nombre . ' ' . $objeto->int_apellido;
                                ?><strong>Persona: </strong><?php echo $persona; ?><br><?php
                                }
                                ?>
                            <strong>Estado: </strong><?php echo $objeto->res_estado; ?><br>
                            <?php
                            $moneda = $objeto->res_moneda;
                            if ($moneda == "1")
                                $s_moneda = "Bolivianos";
                            else
                                $s_moneda = "Dolares";
                            ?>
                            <strong>Moneda: </strong><?php echo $s_moneda; ?>
                            <br>
							<strong>Observaciones: </strong><?php echo $objeto->res_nota; ?>
                            <br>
                            <br><br>
                        </td>
                        <td align="right">
                            <strong>Fecha: </strong><?php echo $myday; ?>

                            <br>    
                            <strong>Vendedor: </strong><?php echo $this->nombre_vendedor($objeto->res_vdo_id); ?><br>
                            <strong>Usuario: </strong><?php echo $this->nombre_persona($objeto->res_usu_id); ?><br>
                        </td>
                    </tr>

                </table>
                <?php
                if ($objeto->res_prod_id > 0) {
//                    echo "mostrando otra nota...";
                    $this->nota_reserva_producto($objeto);
                } else {
                ?>
                <table   width="70%" class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Terreno</th>
                            <th>Superficie</th>
                            <th>Valor del m2</th>
                            <th>Valor del Terreno</th>
                        </tr>		
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                $terreno = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
                                echo $terreno;
//                        $valor_terreno = $objeto->lot_superficie * $objeto->zon_precio;
                                $valor_terreno = $objeto->monto;
                                ?>
                            </td>
                            <td><?php echo $objeto->lot_superficie; ?> m2</td>
                            <td><?php echo $objeto->res_monto_m2; ?></td>
                            <td><?php echo $valor_terreno; ?></td>
                        </tr>	
                    </tbody>
                </table>
                <?php
                }
                ?>
            </center>
        </div>
        <?php
    }

    function nota_reserva_producto($reserva){
        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto 
            where uprod_id='$reserva->res_prod_id'");
        ?>
        <table   width="70%" class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>                                                                                    
                    <th>Proyecto</th>
                    <th>Descripcion</th>
                    <th>Superficie</th>
                    <th>Valor m2</th>
                    <th>Monto</th>
                    <th>Descuento</th>
                    <th>Monto Total</th>                                                 
                </tr>		
            </thead>
            <tbody>
                <tr>
                    <td>Terreno</td>
                    <td>
                        <?php
                        $terreno = 'Urb:' . $reserva->urb_nombre . ' - Mza:' . $reserva->man_nro . ' - Lote:' . $reserva->lot_nro . ' - Zona:' . $reserva->zon_nombre . ' - UV:' . $reserva->uv_nombre;
                        echo $terreno;
//                        $valor_terreno = $objeto->lot_superficie * $objeto->zon_precio;
                        $valor_terreno = $reserva->monto;
                        ?>
                    </td>
                    <td><?php echo $reserva->lot_superficie; ?> m2</td>
                    <td><?php echo number_format($reserva->res_monto_m2, 2, '.', ','); ?></td>
                    <td><?php echo number_format($valor_terreno, 2, '.', ','); ?></td>
                    <td><?php echo '0.00'; ?></td>
                    <td rowspan="2"><?php echo number_format($valor_terreno + $reserva->res_monto_producto, 2, '.', ','); ?></td>
                </tr>	
                <tr>
                    <td colspan="1">Casa</td>
                    <td colspan="1"><?php echo $producto->uprod_nombre;?></td>
                    <td colspan="1"><?php echo $producto->uprod_superficie;?> m2</td>
                    <td colspan="1"><?php echo number_format($reserva->res_valor_producto/$producto->uprod_superficie, 2, '.', ',');?></td>
                    <td colspan="1"><?php echo number_format($reserva->res_valor_producto, 2, '.', ',');?></td>
                    <td colspan="1"><?php echo number_format($reserva->res_descuento_producto, 2, '.', ',');?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    function nota_comprobante_reserva($cmp_id) {
        $conec = new ADO();

        $sql = "SELECT cmp_fecha,cmp_estado,cmp_tc,cmp_interesado,cmp_tco_id,tco_descripcion, cmp_usu_id,
       int_nombre,int_apellido,cmp_tpa_id,tpa_descripcion,cmp_mon_id, cmp_id,
      (SELECT ABS(cde_monto) FROM comprobante_detalle WHERE cde_cmp_id = cmp_id limit 0,1) as monto
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
                        <td width="35%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
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

    function ver_nota_devolucion($res_id) {
        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$res_id");
//        $resrev_id=  FUNCIONES::atributo_bd_sql("select resrev_id as campo from reserva_reversion where resrev_res_id='$res_id'");
        $this->nota_comprobante_devolucion($reserva);
    }

    function nota_comprobante_devolucion($reserva) {
        $moneda = 2;
        $conec = new ADO();


        $monto = $reserva->res_dev_monto;
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
        $myday = setear_fecha(strtotime($reserva->res_dev_fecha));
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

                    <img class="reciboLogo" src="imagenes/micro.png" width="150" height="80"alt="">

                    <div class="reciboTi">
                        <div class="reciboText">RECIBO OFICIAL</div>
                        <div class="reciboNum"><b>Nro.</b> <?php echo $reserva->res_id; ?></div>
                        <div class="reciboText"><h5>(Original)</h5></div>
                    </div>

                    <div class="reciboMoney">
                        <div class="reciboCapa">
                            <div class="reciboLabel">
                                <?php
                                if ($moneda == '1')
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
                                <span class="reciboTexts"> <?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?></span>
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
                                    if ($moneda == '1')
                                        echo 'Bolivianos';
                                    if ($moneda == '2')
                                        echo 'Dolares';
                                    ?>
                                </span> 
                            </td>
                        </tr>

        <?php ?>

                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Por concepto de:</span> 
                                <span class="reciboTexts"> <?php echo 'Devoluci&oacute;n de Reserva Nro.' . $reserva->res_id; ?></span>
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
					            $valores = explode('-', $reserva->res_dev_fecha);
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
                                <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?></b></span>
                            </td>
                            <td class="reciboFirma reciboCenter" colspan="2">
                                <span class="reciboTextsLinea"> </span>
                                <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $this->nombre_persona($reserva->res_dev_usu); ?></b></span>
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

        $sql = "SELECT rp.*,i.int_nombre, i.int_apellido ,rt.res_urb_id from  reserva_pago rp, reserva_terreno rt, interno i
                where rp.respag_res_id=rt.res_id and rt.res_int_id=i.int_id and rp.respag_id='$id' and rp.respag_estado='Pagado'
                order by respag_fecha asc";
        //echo $sql;
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $objeto = $conec->get_objeto();



        if ($_GET[ori] == 'caja') {
            $ret_url = "gestor.php?mod=caja&tarea=ACCEDER";
        } else {
            $ret_url = "gestor.php?mod=reserva";
        }


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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'' . $ret_url . '\';"></td></tr></table>
				';
        if ($this->verificar_permisos('ACCEDER') && $_SESSION[usu_gru_id] != 'CAJERO') {
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=reserva&tarea=ACCEDER" title="LISTADO DE RESERVAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <?php
        }
        include_once 'clases/recibo.class.php';
        $sum = FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo from reserva_pago where respag_res_id=$objeto->respag_res_id and respag_estado='Pagado'");
        $anticipo_est = FUNCIONES::atributo_bd_sql("select urb_monto_anticipo as campo from urbanizacion where urb_id='$objeto->res_urb_id'");
        $saldo = $anticipo_est - $sum;
        $nota = "";
        if ($saldo > 0) {
            $nota = "Usted tiene un saldo por completar el anticipo de $saldo";
        }
        $data = array(
            'titulo' => 'PAGO DE ANTICIPO',
            'referido' => "$objeto->int_nombre $objeto->int_apellido",
            'usuario' => FUNCIONES::usuario_nombre($objeto->respag_usu_id),
            'monto' => $objeto->respag_monto,
            'moneda' => $objeto->respag_moneda,
            'nro_recibo' => $objeto->respag_recibo,
            'fecha' => $objeto->respag_fecha,
            'concepto' => "Pago de la Reserva Nro. $objeto->respag_res_id - " . $objeto->respag_glosa,
//            'has_detalle'=>'1',
//            'det_cabecera'=>array('Interes','Capital','Form.','Total'),
//            'det_body'=>array($objeto->vpag_interes,$objeto->vpag_capital,$objeto->vpag_form,$objeto->vpag_monto),
            'nota' => $nota,
        );
        ?>



        <br><br>
        <?php RECIBO::pago($data); ?>

        <?php
    }

    function nombre_persona($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function nombre_vendedor($vendedor) {
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

    function obtener_total_anticipos_reserva($res_id) {
        $sql = "select SUM(respag_monto) as monto_anticipo from reserva_pago where respag_res_id=" . $res_id . " and respag_estado='Pagado'";
//        echo $sql;
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $total = 0;
        if ($num > 0) {
            if ($conec->get_objeto()->monto_anticipo != null)
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

        $sql = "select * from reserva_terreno where res_id='" . $_GET['id'] . "'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        if ($num > 0) {
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
        $url.="&ori=$_GET[ori]";
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

                function f_tipo_pago() {
                    var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                    if (tipo == 'efectivo') {
                        $('#div_datos_operacion').css("display", "none");
                    } else {
                        if (tipo == 'cheque') {
                            $('#lbl_transaccion').html('Nro de Cheque');
                        }
                        if (tipo == 'deposito') {
                            $('#lbl_transaccion').html('Nro de Deposito');
                        }
                        if (tipo == 'transferencia') {
                            $('#lbl_transaccion').html('Nro de Transferencia');
                        }
                        $('#div_datos_operacion').css("display", "block");
                    }
                }

                function cambiar_moneda() {
                    var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                    var moneda_original = document.frm_sentencia.moneda_original.value;
                    var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                    var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                    var tc = parseFloat(<?php echo $this->tc; ?>);
                    if (moneda_original !== moneda_sel) {
                        if (moneda_sel === '1') {
                            pagara *= tc;
                        } else {
                            pagara /= tc;
                        }
                    }
                    document.frm_sentencia.valor_convertido.value = roundNumber(pagara, 2);
                }

                function enviar_formulario_anticipo() {
                    var respag_monto = document.frm_sentencia.respag_monto.value;
                    var fecha = document.getElementById('respag_fecha').value;
                    if (respag_monto !== '') {
                        if ((respag_monto * 1) > 0) {
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response !== "ok") {
                                    $.prompt(dato.mensaje);
                                } else {
                                    if (!validar_fpag_montos(dato.cambios)) {
                                        $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                        return false;
                                    }
                                    document.frm_sentencia.submit();
                                }
                            });
                        } else {
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
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <div id="FormSent">
                    <div class="Subtitulo">Anticipos Reserva</div>
                    <div id="ContenedorSeleccion" style="width: 100%;">
                        <div id="ContenedorDiv" style="display: none;"s>
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
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div> &nbsp;&nbsp;$us
                            <div id="CajaInput">
                                <input  class="caja_texto" name="respag_monto" id="respag_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                <input type="hidden" name="moneda" id="moneda" value="2">
                            </div>                              
                        </div>
                        <!--                                <div id="ContenedorDiv" >                                                                    							                                    
                                                            <div class="Etiqueta"><span class="flechas1">*</span>Moneda:</div>
                                                                <div id="CajaInput">
                                                                    <select id="moneda" name="moneda">
                                                                        <option value="1" <?php // if($objeto->res_moneda == '1')echo "selected";   ?>>Bolivianos</option>
                                                                        <option value="2" <?php // if($objeto->res_moneda == '2')echo "selected";   ?>>Dolares</option>
                                                                    </select>
                                                                </div>                                                                                
                                                        </div>-->

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                            <div id="CajaInput">
                                <?php echo FORMULARIO::cmp_fecha('respag_fecha'); ?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Transaccion Origen</div>
                            <div id="CajaInput">
                                <select id="respag_tabla" name="respag_tabla">
                                    <option value="">-- Ninguno --</option>
                                    <option value="Venta">Venta</option>
                                    <option value="Reserva">Reserva</option>
                                </select>
                                <input hidden="" id="respag_tabla_id" name="respag_tabla_id" value="" size="10">
                            </div>
                        </div>
                        <!--Fin-->
                        <script>
                        jQuery(function($) {
                            $("#respag_fecha").mask("99/99/9999");
                            mask_decimal('#respag_tabla_id',null);
                            $('#respag_tabla').change(function(){
                                var valor=$(this).val();
                                if(valor!==''){
                                    $('#respag_tabla_id').show();
                                }else{
                                    $('#respag_tabla_id').hide();
                                }
                            });
                        });

                        </script>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Pagos</b></div>                                
        <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'respag_fecha', 'cmp_monto' => 'respag_monto', 'cmp_moneda' => 'moneda')); ?>
                        </div>
                    </div>

                    <div id="ContenedorDiv">

                        <div id="CajaBotones">

                            <center>

                                <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">

                                <input type="reset" class="boton" name="" value="Cancelar">
                                <?php if ($_GET[ori] == 'caja') { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="location.href = 'gestor.php?mod=caja&tarea=ACCEDER'">
                                <?php } else { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="location.href = 'gestor.php?mod=reserva'">
                                <?php } ?>


                            </center>

                        </div>

                    </div>

                </div>

        </div>					

        <?php
    }

    function dibujar_encabezado_anticipo() {
        $this->formulario->dibujar_titulo("ANTICIPOS RESERVA");
        ?>
        <br>
        <div style="clear:both;"></div>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>&acc=anular" method="POST" enctype="multipart/form-data">  
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="pag_id" name="pag_id" value="">
            <input type="hidden" id="observacion_anu" name="observacion_anu" value="">

        </form>
        <center>
            <script>
                function anular_pago(id) {
        //                            var txt = 'Esta seguro de anular el el anticipo de la Reserva?';
                    var txt = 'Esta seguro de anular el anticipo de la Reserva?' +
                            '<br />Observacion:<br /><textarea id="observacion_anu" name="observacion_anu" rows="2" cols="40"></textarea><br />'
                            ;
                    $.prompt(txt, {
                        buttons: {Anular: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                $('#pag_id').val(id);
                                $('#observacion_anu').val(f.observacion_anu);
                                document.frm_sentencia.submit();
        //                                        location.href = 'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&id=<?php // echo $_GET[id]  ?>&acc=anular&pag_id=' + id;
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
                        echo number_format($objeto->respag_monto, 2, ',', '.');
                        $suma_anticipo+=$objeto->respag_monto;
                        ?>

                    </td>

                    <td align="left">

                        <?php
//            if ($objeto->respag_moneda == '2')
                        if ($objeto->respag_moneda == '2')
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
                                    <a href="gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&acc=ver_comprobante&id=<?php echo $objeto->respag_id; ?>">
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
                ?>
                <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php
		        if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
		            echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
		        else
		            echo $this->link . '?mod=' . $this->modulo;
                ?>';">

                <?php
            }

            function anular_anticipo($id) {
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";
//            echo "----$id----";
                $id = $_POST[pag_id];
//            echo "***** $id *****";
                $obsevacion = $_POST[observacion_anu];
                $conec = new ADO();
                $sql = "select * from reserva_pago where respag_id=$id";
                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                include_once 'clases/registrar_comprobantes.class.php';
                $bool = COMPROBANTES::anular_comprobante('reserva_pago', $id);
                if (!$bool) {
                    $mensaje = "El pago de la cuota no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
                    $tipo = 'Error';
                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $objeto->respag_res_id, '', $tipo);
                    return;
                }

                include_once 'clases/recibo.class.php';
                RECIBO::anular($objeto->respag_recibo);

                $id_reserva = $objeto->respag_res_id;
                $fecha_now = date('Y-m-d H:i:s');
                $sql = "update reserva_pago set respag_estado='Anulado', respag_fecha_anu='$fecha_now',respag_usu_anu='$_SESSION[id]', respag_observacion_anu='$obsevacion' where respag_id=$id";
                $conec->ejecutar($sql);
                $sql = "select sum(respag_monto) as suma from reserva_pago where respag_estado!='Anulado' and respag_res_id='$id_reserva';";
//                        echo $sql;
                $pago = FUNCIONES::objeto_bd_sql($sql);

                if ($pago->suma == '' || floatval($pago->suma) <= 0) {
                    $sql = "update reserva_terreno set res_estado='Pendiente' where res_id='$id_reserva'";
                    $conec->ejecutar($sql);
                }
                $mensaje = 'Anticipo de Reserva Anulado Correctamente';
                //$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
                $this->mensaje = $mensaje;
                if ($this->mensaje <> "") {
                    $this->formulario->dibujar_titulo("ANULACION DE ANTICIPOS");
                    $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
                }
            }

            function devolver_anticipo() {
                $reserva = FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
                $monto_pagado = $this->total_anticipo($reserva->res_id);
                if ($monto_pagado > 0) {
                    if ($_POST) {
                        $this->guardar_devolucion($reserva, $monto_pagado);
                    } else {
                        $this->formulario_devolucion($reserva, $monto_pagado);
                    }
                } else {
                    $this->formulario->dibujar_titulo("DEVOLUCION PAGOS DE RESERVA");
                    $mensaje = 'No existen montos a Devolver';
                    $this->mensaje = $mensaje;
                    if ($this->mensaje <> "") {
                        $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
                    }
                }
            }

            function formulario_devolucion($reserva, $monto_pagado) {

                $monto_ref_sus = $monto_pagado;
//            $monto_ref_bs=0;
//            $monto_ref_sus=0;
//            $anticipos=  FUNCIONES::objetos_bd_sql("select * from reserva_pago where respag_res_id='$_GET[id]' and respag_estado='Pagado'");
//            
//            for ($i = 0; $i < $anticipos->get_num_registros(); $i++) {
//                $anticipo=$anticipos->get_objeto();
//                $cambios=  FUNCIONES::get_cambios($anticipo->respag_fecha);
//                $monto_ref_bs+=FUNCIONES::cambiar_monto($anticipo->respag_monto, $anticipo->respag_moneda, 1, $cambios);
//                $monto_ref_sus+=FUNCIONES::cambiar_monto($anticipo->respag_monto, $anticipo->respag_moneda, 2, $cambios);
////                $montos[]=array('1'=>FUNCIONES::cambiar_monto($anticipo->respag_monto, $anticipo->respag_moneda, 1, $cambios),'2'=>FUNCIONES::cambiar_monto($anticipo->respag_monto, $anticipo->respag_moneda, 2, $cambios));
//                $anticipos->siguiente();
//            }

                $url = $this->link . '?mod=' . $this->modulo . "&tarea=DEVOLVER&id=$reserva->res_id";

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

                $this->formulario->dibujar_titulo("DEVOLUCION PAGOS DE RESERVA");

                if ($this->mensaje <> "" and $this->tipo_mensaje <> "") {
                    $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
                }
                ?>


                <div id="Contenedor_NuevaSentencia">
                    <script>
                function ValidarNumero(e){
                    evt = e ? e : event;
                    tcl = (window.Event) ? evt.which : evt.keyCode;
                    if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                    {
                        return false;
                    }
                    return true;
                }

                function f_tipo_pago() {
                    var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                    if (tipo == 'efectivo') {
                        $('#div_datos_operacion').css("display", "none");
                    } else {
                        if (tipo == 'cheque') {
                            $('#lbl_transaccion').html('Nro de Cheque');
                        }
                        if (tipo == 'deposito') {
                            $('#lbl_transaccion').html('Nro de Deposito');
                        }
                        if (tipo == 'transferencia') {
                            $('#lbl_transaccion').html('Nro de Transferencia');
                        }
                        $('#div_datos_operacion').css("display", "block");
                    }
                }

                function cambiar_moneda() {
                    var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                    var moneda_original = document.frm_sentencia.moneda_original.value;
                    var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                    var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                    var tc = parseFloat(<?php echo $this->tc; ?>);
                    if (moneda_original !== moneda_sel) {
                        if (moneda_sel === '1') {
                            pagara *= tc;
                        } else {
                            pagara /= tc;
                        }
                    }
                    document.frm_sentencia.valor_convertido.value = roundNumber(pagara, 2);
                }

                function enviar_formulario_anticipo() {
                    var respag_monto = document.frm_sentencia.respag_monto.value * 1;
                    var monto_pag = document.frm_sentencia.monto_pagado.value * 1;
                    if (respag_monto > monto_pag) {
                        $.prompt('El monto a Devolver no debe ser mayor al monto Pagado');
                        return false;
                    }
                    var fecha = document.getElementById('respag_fecha').value;
                    if (respag_monto !== '') {
                        if ((respag_monto * 1) > 0) {
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response !== "ok") {
                                    $.prompt(dato.mensaje);
                                } else {
                                    if (!validar_fpag_montos(dato.cambios)) {
                                        $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                        return false;
                                    }
                                    document.frm_sentencia.submit();
                                }
                            });
                        } else {
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
                        <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                        <div id="FormSent">
                            <div class="Subtitulo">Devoluci&oacute;n de Reserva</div>
                            <div id="ContenedorSeleccion" style="width: 100%">
                                <div id="ContenedorDiv" >
                                    <div class="Etiqueta" >Monto Pagado</div>
                                    <div id="CajaInput">
                                        <input  class="caja_texto" readonly="readonly" id="txt_monto_pagado" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);">
                                        <!--<input  class="caja_texto" readonly="readonly" name="moneda_original" id="moneda_original" size="2" value="<? echo $objeto->res_moneda; ?>" type="hidden" >-->
                                        <input  name="monto_pagado" data-mp1="<?php echo $monto_ref_bs; ?>" data-mp2="<?php echo $monto_ref_sus; ?>" id="monto_pagado" value="" type="hidden" >
                                    </div>							
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>
                                    <div id="CajaInput">
                                        <input  class="caja_texto" name="respag_monto" id="respag_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                    </div>							
                                </div>
                                <input type="hidden" id="moneda" name="moneda" value="2">
                                <!--                                    <div id="ContenedorDiv" >                                                                    							                                    
                                                                        <div class="Etiqueta"><span class="flechas1"></span>En:</div>
                                                                        <div id="CajaInput">
                                                                            <select id="moneda" name="moneda">
                                                                                <option value="1" >Bolivianos</option>
                                                                                <option value="2" selected="">Dolares</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                    <div id="CajaInput">
                                        <input class="caja_texto" name="respag_fecha" id="respag_fecha" size="12" value="<?php
        if (isset($_POST['respag_fecha']))
            echo $_POST['respag_fecha'];
        else
            echo date("d/m/Y");
                ?>" type="text">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                    <div id="CajaInput">
                                        <textarea class="caja_texto" name="glosa" id="glosa"  ></textarea>
                                    </div>
                                </div>
                                <script>
                $('#moneda').change(function() {
                    var val = $(this).val();
                    var pagado = $('#monto_pagado').attr('data-mp' + val) * 1;
                    $('#monto_pagado').val(pagado.toFixed(2));
                    $('#txt_monto_pagado').val(pagado.toFixed(2));
                    //                                            $('monto_pagado').trigger('focusout');
                });
                $('#moneda').trigger('change');
                jQuery(function($) {
                    $("#respag_fecha").mask("99/99/9999");
                });
                                </script>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><b>Pagos</b></div>                                
        <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'respag_fecha', 'cmp_monto' => 'respag_monto', 'cmp_moneda' => 'moneda')); ?>
                                </div>
                            </div>

                            <div id="ContenedorDiv">

                                <div id="CajaBotones">

                                    <center>

                                        <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">

                                        <input type="reset" class="boton" name="" value="Cancelar">

                                        <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php
                                        if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
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

    function guardar_devolucion($reserva, $monto_pagado) {

        $conec = new ADO();

//            $reserva = FUNCIONES::objeto_bd_sql("SELECT * FROM reserva_terreno WHERE res_id='$_GET[id]'");
        $estado = $reserva->res_estado;
        if ($estado == 'Deshabilitado') {

//                $monto = $_POST[respag_monto]*1;
            $fecha = FUNCIONES::get_fecha_mysql($_POST['respag_fecha']);


            $res_lot_id = $reserva->res_lot_id;
            $_POST['ven_lot_id'] = $res_lot_id;
//                $monto_pagado=  $this->total_anticipo($reserva->res_id);
            $dev_monto = $_POST[respag_monto];
            $dev_ingreso = $monto_pagado - $dev_monto;
            $sql_update = "update reserva_terreno set 
                    res_estado='Devuelto',
                    res_dev_monto='$dev_monto',
                    res_dev_ingreso='$dev_ingreso',
                    res_dev_fecha='$fecha',
                    res_dev_usu='$_SESSION[id]',
                    res_dev_glosa='$_POST[glosa]'
                where
                    res_id=$reserva->res_id";
//                echo $sql_update;
            $conec->ejecutar($sql_update);


            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$reserva->res_urb_id'");

            $moneda = 2;
            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            $referido = FUNCIONES::interno_nombre($reserva->res_int_id);
            $_glosa = "";
            if ($_POST[glosa]) {
                $_glosa = ": $_POST[glosa]";
            }
            if ($dev_monto > 0) {
                $glosa = "Pago de Devolucion de la Reserva Nro $reserva->res_id $_glosa";
                $params = array(
                    'tabla' => 'reserva_devolucion',
                    'tabla_id' => $reserva->res_id,
                    'fecha' => $fecha,
                    'moneda' => $moneda,
                    'ingreso' => false,
                    'une_id' => $urb->urb_une_id,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
                );
                $detalles = FORMULARIO::insertar_pagos($params);
            } else {
                $glosa = "Ingreso del Pago de la Reserva Nro $reserva->res_id $_glosa";
                $detalles = array();
            }


            $data = array(
                'moneda' => $moneda,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $fecha,
                'glosa' => $glosa,
                'interno' => $referido,
                'tabla_id' => $reserva->res_id,
                'urb' => $urb,
                'dev_monto' => $dev_monto,
                'dev_ingreso' => $dev_ingreso,
                'monto_pagado' => $monto_pagado,
                'detalles' => $detalles,
            );

            $comprobante = MODELO_COMPROBANTE::devolucion_reserva($data);

            COMPROBANTES::registrar_comprobante($comprobante);


            $sqlres = "update reserva_terreno set res_estado='Devuelto' where res_id='" . $reserva->res_id . "'";
            $conec->ejecutar($sqlres);


//                $this->nota_comprobante_reserva($cmp_id);                
            $this->ver_nota_devolucion($reserva->res_id);
//                    $this->ver_comprobante($llave);
        } else {
            $this->formulario->dibujar_titulo("DEVOLUCION PAGOS DE RESERVA");
            $mensaje = 'No se pudo <b>Ingresar Anticipo</b> por que la Reserva ya se encuentra en estado <b>Deshabilitado, Expirado , Concretado o Pendiente</b>';
            $this->mensaje = $mensaje;
            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ANTICIPOS RESERVA&id=' . $id_reserva, "");
            }
        }
    }

    /// CAMBIAR PROPIETARIO
    function cambio_propietario() {
        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
        $this->formulario->dibujar_titulo("CAMBIO DE PROPIETARIO");
        ?>
                <div id="Contenedor_NuevaSentencia">
                    <div id="FormSent">
                        <div class="Subtitulo">Datos Generales</div>
                        <div id="ContenedorSeleccion">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta">Nro Reserva:</div>
                                <div id="CajaInput">
                                    <div class="read-input"><?php echo $reserva->res_id; ?></div>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta">Concepto:</div>
                                <div id="CajaInput">
                                    <div class="read-input"><?php echo $this->descripcion_terreno($reserva->res_lot_id); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
        if ($_GET[acc] == 'anular') {
            $this->anular_cambio_propietario($reserva);
            $this->formulario_cambio_propietario($reserva);
            $this->listado_cambios_propietario($reserva);
        } else {
            if ($this->validar_cambio_propietario()) {
                $this->guardar_cambio_propietario($reserva);
                $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
            }
            $this->formulario_cambio_propietario($reserva);
            $this->listado_cambios_propietario($reserva);
        }
    }

    function anular_cambio_propietario($venta) {

        $vph_id = $_GET[vph_id];
        $vph = FUNCIONES::objeto_bd_sql("select * from venta_propietarios_historial where vph_id=$vph_id");
        if ($vph->vph_estado == 'Pendiente') {
            $conec = new ADO();
            $fecha_now = date('Y-m-d');
            $sql_up = "update venta_propietarios_historial set vph_estado='Anulado' where vph_id=$vph_id";
            $conec->ejecutar($sql_up);
            $sql_up = "update extra_pago set epag_estado='Anulado' ,epag_fecha_mod='$fecha_now' where epag_tabla='ven_prop_his' and epag_tabla_id=$vph_id";
            $conec->ejecutar($sql_up);
        }
    }

    function validar_cambio_propietario() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail, todo
            require_once('clases/validar.class.php');
            $i = 0;
            $valores[$i]["etiqueta"] = "Persona";
            $valores[$i]["valor"] = $_POST['int_id'];
            $valores[$i]["tipo"] = "numero";
            $valores[$i]["requerido"] = true;
            $i++;
            $val = NEW VALIDADOR;

            $this->mensaje = "";

            if ($val->validar($valores)) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;

                $this->tipo_mensaje = "Error";

                return false;
            }
        }
        return false;
    }

    function guardar_cambio_propietario($reserva) {

        $conec = new ADO();
        $propietario_actual = $reserva->res_int_id;

        if (true) {
            $sql = "update reserva_terreno set res_int_id='$_POST[int_id]' where res_id=" . $_GET['id'];
            $conec->ejecutar($sql);

            $fecha_cambio = date('Y-m-d');

            //            $costo=  FUNCIONES::ad_parametro('par_cambio_titular');
            $int_id_new = $_POST[int_id];
            $sql = "insert into reserva_propietarios_historial(
                        rph_int_id,rph_int_id_nuevo,rph_fecha_cambio,
                        rph_observacion,rph_usu_id,rph_res_id
                ) values(
                        '$propietario_actual','$int_id_new','$fecha_cambio',
                    '$_POST[rph_observacion]','$_SESSION[id]','$reserva->res_id'
                )";
            $conec->ejecutar($sql);

            /// INSERTAR COBRO DE CAMBIO DE TITULAR
            $this->mensaje = "Se realiz� el cambio de Propietario";
            $this->tipo_mensaje = 'Correcto';
        } else {
            $this->mensaje = "No puede realizar Cambio de Propietario en una Venta al Contado";
            $this->tipo_mensaje = 'Error';
        }
	}

    function formulario_cambio_propietario($reserva) {
        $conec = new ADO();

        if ($reserva->res_estado == 'Pendiente' || $reserva->res_estado == 'Habilitado') {
            $propietario_actual = $reserva->res_int_id;
            if ($propietario_actual <> 0) {
                $nombre_propietario_actual = FUNCIONES::interno_nombre($propietario_actual);
            }

            $sql = "select * from interno";
            $conec->ejecutar($sql);
            $nume = $conec->get_num_registros();
            $personas = 0;
            if ($nume > 0) {
                $personas = 1;
            }

            if (true) {
                $url = $this->link . '?mod=' . $this->modulo . "&tarea=CAMBIAR PROPIETARIO&id=$reserva->res_id";

                if ($this->mensaje <> "" and $this->tipo_mensaje <> "") {
                    $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
                }
                ?>
                        <div id="Contenedor_NuevaSentencia">
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
                            <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
                            <!--FancyBox-->
                            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">    
                                <div id="FormSent">
                                    <div class="Subtitulo">Datos</div>
                                    <div id="ContenedorSeleccion">
                                        <div id="ContenedorDiv">
                                            <center>
                                                <input type="hidden" name="ven_tit_act" id="ven_tit_act" value="<?php echo $reserva->res_int_id; ?>">
                                                <span style="float:right;"><b>Propietario Actual:</b> <?php echo $nombre_propietario_actual; ?><br />
                                                </span>
                                            </center>
                                            <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                                            <div id="CajaInput">
                                                <?php if ($personas <> 0) { ?>
                                                    <input name="int_id" id="int_id" readonly type="hidden" class="caja_texto" value="" size="2">
                                                    <input name="int_nombre_persona" readonly="readonly" id="int_nombre_persona"  type="text" class="caja_texto" value="" size="40">
                                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                                    </a>
                                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                                        <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                                    </a>                                                    
                                                    <a style="float:left; margin:0 0 0 7px;float:right;display:inline;" href="#" onClick="reset_interno();">
                                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR"></a>
                                                    <?php
                                                } else {
                                                    echo 'No se le asigno ninguna personas, para poder cargar las personas.';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Observacion</div>
                                            <div id="CajaInput">
                                                <textarea name="rph_observacion" id="rph_observacion" rows="3" cols="29"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div id="CajaBotones">
                                            <center>
                                                <input type="submit" class="boton" name="" value="Guardar">
                                                <input type="reset" class="boton" name="" value="Cancelar">
                                                <input type="button" class="boton" name="" value="Volver" onClick="location.href = '<?php
                                                if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
                                                    echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
                                                else
                                                    echo $this->link . '?mod=' . $this->modulo;
                                                ?>';">
                                            </center>
                                        </div>
                                    </div>
                                </div>
                        </div>	
                        <script>
		                $('#frm_sentencia').submit(function() {
		                    var int_act = $('#ven_tit_act').val() * 1;
		                    var int_new = $('#int_id').val() * 1;
		
		                    if (int_new > 0) {
		                        console.log(int_act + '===' + int_new + ' && ' + cop_act + '===' + cop_new);
		                        if (int_act === int_new && cop_act === cop_new) {
		                            $.prompt('Debe cambiar el Propietario o el Co-Propietario');
		                            return false;
								}
		                    } else {
		                        $.prompt('Ingrese el Propietario');
		                        return false;
		                    }
		                    return true;
		                });

		                function reset_interno() {
		                    $("#int_nombre_persona").val("");
		                    $("#int_id").val("");
		                }
		
		                function set_valor_interno(data) {
		                    document.frm_sentencia.int_id.value = data.id;
		                    document.frm_sentencia.int_nombre_persona.value = data.nombre;
		                }

                        </script>

                <?php
            } else {
                $this->mensaje = 'No puede realizar Cambio de Propietario, por que la Venta que seleccion? es al <b>Contado</b>';

                $this->tipo_mensaje = 'Error';

                $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
                ?>
                        <div id="CajaBotones" style="width:100%;text-align:center;">

                            <center>

                                <input type="button" class="boton" name="" value="Volver" onClick="location.href = '<?php
                if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
                    echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
                else
                    echo $this->link . '?mod=' . $this->modulo;
                ?>';">

                            </center>

                        </div>
                        <?php
                    }
                }
                else {
                    $this->mensaje = 'No puede realizar Cambio de Propietario, por que la Venta que seleccion? (Nro:<b>' . $_GET['id'] . '</b>) se encuentra en Estado <b>Pagado</b>, <b>Retenido</b>, <b>Anulado</b> o <b>Cambiado</b>';

                    $this->tipo_mensaje = 'Error';

                    $this->formulario->mensaje($this->tipo_mensaje, $this->mensaje);
                    ?>
                    <div id="CajaBotones" style="width:100%;text-align:center;">

                        <center>

                            <input type="button" class="boton" name="" value="Volver" onClick="location.href = '<?php
            if ($_GET['acc'] == "MODIFICAR_PRODUCTO")
                echo $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTOS&id=" . $_GET['id'];
            else
                echo $this->link . '?mod=' . $this->modulo;
            ?>';">

                        </center>

                    </div>
                    <?php
                }
            }

            function listado_cambios_propietario($reserva) {
                $aest = array('Pendiente' => '#ff9601', 'Activado' => '#019721');
                ?>
                <div style="clear:both;"></div><center>
                    <center><h4>HISTORIAL DE CAMBIO DE PROPIETARIOS</h4></center>
                    <table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
                        <thead>
                            <tr>
                                <th >Nro</th>
                                <th >Fecha de Cambio</th>
                                <th >Propietario(s) Anterior(es) </th>
                                <th >Propietario(s) Nuevo(s)</th>
                                <th >Observacion</th>
                            </tr>
                        </thead>
                        <tbody>
        <?php
        $sql = "select * from reserva_propietarios_historial where rph_res_id='$_GET[id]' order by rph_id asc";
        $cambios = FUNCIONES::objetos_bd_sql($sql);
        $num = $cambios->get_num_registros();
        for ($i = 0; $i < $num; $i++) {
            $objeto = $cambios->get_objeto();
            ?>
                                <tr class="busqueda_campos">
                                    <td ><?php echo $i + 1; ?></td>
                                    <td ><?php echo FUNCIONES::get_fecha_latina($objeto->rph_fecha_cambio); ?></td>
                                    <td >
                                <?php
                                if ($objeto->rph_cop_id <> 0) {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_int_id) . ' - <b>Copropietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_cop_id);
                                } else {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_int_id);
                                }
                                ?>
                                    </td>
                                    <td align="left">
                                        <?php
                                        if ($objeto->rph_cop_id_nuevo <> 0) {
                                            echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_int_id_nuevo) . ' - <b>Copropietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_cop_id_nuevo);
                                        } else {
                                            echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->rph_int_id_nuevo);
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $objeto->rph_observacion; ?></td>
                                </tr>
            <?php
            $cambios->siguiente();
        }
        ?>
                        </tbody>
                    </table>
                </center><br>
                <script>
                $('#anular_cambio').click(function() {
                    var id = $(this).attr('data-id');
                    var txt = 'Esta seguro de anular el cambio de titular';
                    $.prompt(txt, {
                        buttons: {Anular: true, Cancelar: false},
                        callback: function(v, m, f) {
                            if (v) {
                                location.href = 'gestor.php?mod=venta&tarea=CAMBIAR PROPIETARIO&id=<?php echo $reserva->ven_id; ?>&acc=anular&vph_id=' + id;
                            }

                        }
                    });

                });
                </script>
                <?php
            }

            function cambiar_lote() {
                $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
                if ($_POST) {
                    $this->guardar_cambio_lote($reserva);
                } else {
                    $this->frm_cambio_lote($reserva);
                }
            }

            function guardar_cambio_lote($reserva) {
                FUNCIONES::print_pre($_POST);
//                return;
                $anticipos = $this->total_anticipo($reserva->res_id);
                $conec = new ADO();
                $urb_ant_id = $reserva->res_urb_id;
                $urb_new_id = $_POST[new_urb_id];
                $lote_new_id = $_POST[new_lot_id];
                $lote_ant_id = $_POST[lote_id_ant];
                $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
                $sql_up = "update reserva_terreno set res_urb_id='$urb_new_id', res_lot_id='$lote_new_id' where res_id='$reserva->res_id'";
//                echo "$sql_up<br>";
                $conec->ejecutar($sql_up);

                $sql_up = "update lote set lot_estado='Disponible' where lot_id='$lote_ant_id'";
                $conec->ejecutar($sql_up);
                $sql_up = "update lote set lot_estado='Reservado' where lot_id='$lote_new_id'";
                $conec->ejecutar($sql_up);

                $concepto = $this->descripcion_terreno($lote_new_id);
                echo "$urb_ant_id!=$urb_new_id && $anticipos>0";
                if ($urb_ant_id != $urb_new_id && $anticipos > 0) {
                    include_once 'clases/modelo_comprobantes.class.php';
                    include_once 'clases/registrar_comprobantes.class.php';
                    $referido = FUNCIONES::interno_nombre($reserva->res_int_id);
                    $glosa = "Cambio de lote entre diferentes urbanizaciones Reserva Nro. $reserva->res_id - $referido - $concepto";
                    $urb_ori = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_ant_id");
                    $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_new_id");
                    $data = array(
                        'moneda' => 2,
                        'ges_id' => $_SESSION[ges_id],
                        'fecha' => $fecha,
                        'glosa' => $glosa,
                        'interno' => $referido,
                        'tabla_id' => $reserva->res_id,
                        'urb_ori' => $urb_ori,
                        'urb_des' => $urb_des,
                        'anticipo' => $anticipos,
                    );

                    $comprobante = MODELO_COMPROBANTE::reserva_cambio_lote($data);

                    COMPROBANTES::registrar_comprobante($comprobante);
                }

                $mensaje = "Cambio de Ubicacion guardada Correctamente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);

                //        FUNCIONES::print_pre($_POST);
            }

            function frm_cambio_lote($reserva) {
                $this->formulario->dibujar_titulo("CAMBIO DE UBICACION");
                ?>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <script type="text/javascript" src="js/util.js"></script>
                <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=reserva&tarea=CAMBIAR LOTE&id=<?php echo $reserva->res_id; ?>" name="frm_sentencia">
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                    <input type="hidden" id="res_id" name="res_id" value="<?php echo $reserva->res_id; ?>">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width: 100%">
                            <div class="Subtitulo">Datos de la Venta</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro Venta:</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $reserva->res_id; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Cliente:</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="lote_id_ant" id="lote_id_ant" value="<?php echo $reserva->res_lot_id; ?>">
                                        <div class="read-input"><?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Concepto:</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $this->descripcion_terreno($reserva->res_lot_id); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width: 100%">
                            <div class="Subtitulo">Datos Nuevo Terreno</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                    <div id="CajaInput">
                                        <input type="text" name="fecha" id="fecha" value="<?php echo date('d/m/Y'); ?>">
                                        <div class="read-input" id="txt_fecha" hidden=""></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Nuevo Terreno</div>
                                    <div id="CajaInput">
                                        <input type="hidden" id="new_urb_id" name="new_urb_id">
                                        <input type="hidden" id="new_uv_id" name="new_uv_id">
                                        <input type="hidden" id="new_man_id" name="new_man_id">
                                        <input type="hidden" id="new_lot_id" name="new_lot_id">
                                        <input type="hidden" id="new_lot_sup" name="new_lot_sup">
                                        <input type="hidden" id="new_zon_precio" name="new_zon_precio">
                                        <input type="hidden" id="new_zon_moneda" name="new_zon_moneda">
                                        <div class="read-input" id="txt_terreno">&nbsp;</div><img id="img-buscar"style="margin: 4px 0 0 3px; cursor: pointer;"src="images/b_search.png">
                                    </div>
                                </div>
                            </div>

                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>
                                        <input class="boton" type="button" value="Guardar" name="" id="btn_guardar">
                                        <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                $('#fecha').mask('99/99/9999');
                mask_decimal('#ven_metro', null);
                mask_decimal('#ven_descuento', null);
                mask_decimal('#ven_incremento', null);
                mask_decimal('#interes_anual', null);
                var popup = null;
                $('#img-buscar').click(function() {

                    var par = {};
                    par.tarea = 'buscar_lote';
                    par.lote_id = $('#lote_id_ant').val();

                    $.post('ajax.php', par, function(resp) {
                        var html = resp;
                        if (popup !== null) {
                            popup.close();
                        }
                        popup = window.open('about:blank', 'reportes', 'left=100,width=800,height=400,top=0,scrollbars=yes');
                        var extra = '';
                        popup.document.write(extra);
                        popup.document.write(html);
                        popup.document.close();
                    });
                });

                function cargar_lote_cambio(data) {
                    console.log(data);
                    $('#new_urb_id').val(data.urb_id);
                    $('#new_uv_id').val(data.uv_id);
                    $('#new_man_id').val(data.man_id);
                    $('#new_lot_id').val(data.lot_id);
                    $('#new_lot_sup').val(data.lot_sup);
                    $('#new_zon_precio').val(data.zon_precio);
                    $('#new_zon_moneda').val(data.zon_moneda);
                    $('#txt_terreno').text(data.lot_descripcion);

                    $('#btn_pagos').show();
                    $('#btn_editar').hide();
                }
                pagos = true;
                function habilitar_edicion() {
                    $('#fecha').show();
                    $('#txt_fecha').hide();

                    $('#btn_pagos').show();
                    $('#btn_editar').hide();
                    $('#img-buscar').show();
                    $('.box-new-pagos').hide();
                    pagos = false;
				}

                function habilitar_pagos() {
                    if ($('#new_lot_id').val() === '') {
                        $.prompt("Seleccion el Nuevo Lote");
                        return false;
                    }
                    var fecha = $('#fecha').val();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }

                        $('#txt_fecha').text($('#fecha').val());
                        $('#fecha').hide();
                        $('#txt_fecha').show();

                        habilitar_montos();

                        $('#btn_pagos').hide();
                        $('#btn_editar').show();
                        $('#img-buscar').hide();
                        $('.box-new-pagos').show();
                        var _fecha = fecha_mysql(fecha);
                        var nfecha = siguiente_mes(_fecha);
                        $('#fecha_pri_cuota').val(fecha_latina(nfecha));
                        pagos = true;

                    });

                }

                function habilitar_montos() {
                    var lot_sup = $('#new_lot_sup').val();
                    var zon_precio = $('#new_zon_precio').val();
                    $('#txt_superficie').text(lot_sup);
                    $('#ven_metro').val(zon_precio);
                    calcular_valor();


                }
                function calcular_valor() {
                    var lot_sup = $('#new_lot_sup').val() * 1;
                    var ven_metro = $('#ven_metro').val() * 1;
                    var ven_valor = (lot_sup * ven_metro).toFixed(2);
                    $('#ven_valor').val(ven_valor);
                    calcular_monto();
                }
                function calcular_monto() {
                    var valor = $('#ven_valor').val() * 1;
                    var desc = $('#ven_descuento').val() * 1;
                    var inc = $('#ven_incremento').val() * 1;
                    var ven_pagado = $('#ven_pagado').val() * 1;
                    var monto = valor + inc - desc - ven_pagado;
                    $('#ven_monto_efectivo').val(monto.toFixed(2));
                    if (monto > 0) {
                        $('#box-reformular').show();
                    } else {
                        $('#box-reformular').hide();
                    }
                }
                $('#ven_metro').keyup(function() {
                    calcular_valor();
                });
                $('#ven_descuento, #ven_incremento').keyup(function() {
                    calcular_monto();
                });
                $('#fecha_pri_cuota').mask('99/99/9999');
                $('#def_plan_efectivo').change(function() {
                    var def = $(this).val();
                    if (def === 'mp') {
                        $('#meses_plazo').parent().show();
                        $('#cuota_mensual').parent().hide();
                        $('#cuota_interes').parent().hide();
                        $('#ver_plan_efectivo').show();
                        $('#add_cuota_efectivo').hide();
                        $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
                        $('#plan_manual_efectivo').hide();
                        $('#def_cuota').parent().hide();
                    } else if (def === 'cm') {
                        $('#meses_plazo').parent().hide();
                        $('#cuota_mensual').parent().show();
                        $('#cuota_interes').parent().hide();
                        $('#ver_plan_efectivo').show();
                        $('#add_cuota_efectivo').hide();
                        $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                        $('#cuota_mensual').prev('span').show();
                        $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
                        $('#plan_manual_efectivo').hide();
                        $('#def_cuota').parent().hide();
                    } else if (def === 'manual') {
                        $('#meses_plazo').parent().hide();
                        $('#cuota_mensual').parent().show();
                        $('#cuota_interes').parent().show();
                        $('#ver_plan_efectivo').hide();
                        $('#add_cuota_efectivo').show();
                        $('#cuota_mensual').prev('span').hide();
                        $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                        $('#plan_manual_efectivo').show();
                        $('#def_cuota').parent().show();
                    }
                });

                $('#def_plan_efectivo').trigger('change');

                function ver_plan_pago() {
                    var saldo_financiar = 0;
                    var ncuotas = 0;
                    var fecha_pri_cuota = 0;
                    var monto_cuota = 0;
                    var def = $('#def_plan_efectivo option:selected').val();
                    if (def === 'mp') {
                        //                        ncuotas = $('#ven_plazo').val(); /////// ELIMINAR
                        //                        monto_cuota = $('#ven_cuota').val(); /////// ELIMINAR
                        ncuotas = $('#meses_plazo').val();
                        monto_cuota = '';
                    } else if (def === 'cm') {
                        ncuotas = '';
                        monto_cuota = $('#cuota_mensual').val();
                    }

                    saldo_financiar = $('#ven_monto_efectivo').val() * 1;
                    fecha_pri_cuota = $('#fecha_pri_cuota').val();
                    var fecha_inicio = $('#fecha').val();

                    var rango = $('#ven_rango option:selected').val();
                    var frec = $('#ven_frecuencia option:selected').val();

                    var interes = $('#interes_anual').val();

                    if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && saldo_financiar > 0 && fecha_pri_cuota !== '') {
                        var moneda = $('#ven_moneda').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                        var par = {};
                        par.tarea = 'plan_pagos';
                        par.saldo_financiar = saldo_financiar;
                        par.monto_total = saldo_financiar;
                        par.meses_plazo = ncuotas;
                        par.cuota_mensual = monto_cuota;
                        par.ven_moneda = moneda;
                        par.nro_inicio = $('#nro_cuota_sig').val();
                        par.fecha_inicio = fecha_inicio;
                        par.fecha_pri_cuota = fecha_pri_cuota;
                        par.interes = interes;
                        par.rango = rango;
                        par.frecuencia = frec;
                        mostrar_ajax_load();
                        $.post('ajax.php', par, function(resp) {
                            ocultar_ajax_load();
                            abrir_popup(resp);
                        });

                    } else {
                        $('#tprueba tbody').remove();
                        $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                    }

                }

                function abrir_popup(html) {
                    if (popup !== null) {
                        popup.close();
                    }
                    popup = window.open('about:blank', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                    var extra = '';
                    extra += '<html><head><title>Vista Previa</title><head>';
                    extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                    extra += '</head> <body> <div id=imprimir> <div id=status> <p>';
                    extra += '<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                    popup.document.write(extra);
                    popup.document.write(html);
                    popup.document.write('</center></body></html>');
                    popup.document.close();

                }


                $('#btn_guardar').click(function() {
                    var lote_id = $('#new_lot_id').val() * 1;
                    if (lote_id <= 0) {
                        $.prompt("Seleccione el lote");
                        return false;
                    }

                    var fecha = $('#fecha').val();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {

                            document.frm_sentencia.submit();
                        } else if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }
                    });
                });
                    </script>
                </form>
        <?php
    }

    function cambiar_vendedor() {
        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id=$_GET[id]");
        if ($reserva->res_estado != 'Pendiente' && $reserva->res_estado != 'Habilitado') {
            $this->formulario->ventana_volver('La venta ya no se encuentra en estado <b>Pendiente</b>', $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        if ($_POST) {
            $this->guardar_cambiar_vendedor($reserva);
        } else {
            $this->frm_cambiar_vendedor($reserva);
        }
    }

    function guardar_cambiar_vendedor($reserva) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $conec = new ADO();
//        $ufecha_prog=  FUNCIONES::get_fecha_mysql($_POST[ven_ufecha_prog]);
        $vendedor_id = $_POST[vendedor_id];
        $sql_up = "update reserva_terreno set res_vdo_id='$vendedor_id' where res_id='$reserva->res_id'";
        $conec->ejecutar($sql_up);
        $mensaje = "Cambio de Vendedor de la Reserva, modificado correctamente realizado Exitosamente";
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Correcto');
    }

    function frm_cambiar_vendedor($reserva) {
//        $this->barra_opciones($venta);
//        echo "<br><br>";
        $this->formulario->dibujar_titulo("EDITAR DATOS VENTA");
        ?>
                <script type="text/javascript" src="js/util.js"></script>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <script src="js/chosen.jquery.min.js"></script>
                <link href="css/chosen.min.css" rel="stylesheet"/>
                <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=reserva&tarea=CAMBIAR VENDEDOR&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                    <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $reserva->res_id; ?>">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width: 100%">
                            <div class="Subtitulo">Datos de la Reserva</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Nro Reserva</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $reserva->res_id; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::interno_nombre($reserva->res_int_id); ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Concepto</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_concepto($reserva->res_lot_id); // $reserva->ven_concepto; ?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><?php echo ($reserva->res_multinivel == 'si')?'Patrocinador':'Vendedor';?>;</div>
                                    <div id="CajaInput">
                                        <select name="vendedor_id" id="vendedor_id">
                                            <option value="">--Seleccione--</option>
        <?php
        if ($reserva->res_multinivel == 'si') {
            $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre='AFILIADOS'";
        } else {
            $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre!='AFILIADOS';";
        }
        $fun = new FUNCIONES();
        $fun->combo($sql, $reserva->res_vdo_id);
        ?>
                                        </select>
                                        <!--<input type="text" id="ven_ufecha_prog" name="ven_ufecha_prog" value="<?php // echo FUNCIONES::get_fecha_latina($reserva->ven_ufecha_prog);?>">-->
                                    </div>
                                </div>
                            </div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <input type="button" id="btn_guardar" value="Guardar" class="boton">
                                    <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=venta&tarea=ACCEDER';">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <script>
                    $('#vendedor_id').chosen({
                        allow_single_deselect: true
                    });
                                $('#ven_ufecha_prog').mask('99/99/9999');
                                $('#frm_sentencia').submit(function() {
                                    return false;
                                });
                                $('#btn_guardar').click(function() {
        //                var ufecha_valor_mysql=$('#ufecha_valor').val();
        //                var nfecha_valor_mysql=fecha_mysql($('#nfecha_valor').val());
        //                if(ufecha_valor_mysql>=nfecha_valor_mysql){
        //                    $.prompt('La Nueva fecha valor debe ser mayor a la Ultima Fecha Valor');
        //                    return false;
        //                }
        //                console.log('submit');
                                    var val_interes = $('#ven_val_interes').val();
                                    var ufecha_prog = $('#ven_ufecha_prog').val();
                                    if (val_interes === '' || ufecha_prog === '') {
                                        $.prompt('Ingrese correctamente el interes y la maxima fecha Programada');
                                        return false;
                                    }
                                    document.frm_sentencia.submit();
                                });
                </script>
        <?php
    }

}
?>