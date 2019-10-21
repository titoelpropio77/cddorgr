<?php
ini_set('display_errors', 'On');

class VENTA extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;
    var $ver_estado;
    var $ver_pagado;
    var $ver_saldo_capital;

    function VENTA() {
        //permisos
        $this->ele_id = 130;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }

        $this->ver_estado = true;
        if (!($this->verificar_permisos('VER ESTADO'))) {
            $this->ver_estado = false;
        }
        $this->ver_pagado = true;
        if (!($this->verificar_permisos('VER PAGADO'))) {
            $this->ver_pagado = false;
        }
        $this->ver_saldo_capital = true;
        if (!($this->verificar_permisos('VER SALDO CAPITAL'))) {
            $this->ver_saldo_capital = false;
        }
        //fin permisos
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $num = 0;
        $this->arreglo_campos[$num]["nombre"] = "ven_id";
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
        $this->arreglo_campos[$num]["tamanio"] = 30;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "lot_nro";
        $this->arreglo_campos[$num]["texto"] = "Lote";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "ven_numero";
        $this->arreglo_campos[$num]["texto"] = "Nro Adj";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;

        $num++;
        $this->arreglo_campos[$num]["nombre"] = "ven_multinivel";
        $this->arreglo_campos[$num]["texto"] = "Modalidad Venta";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "no,si:Tradicional,Multinivel";
        $colores = array('no' => '#db7093', 'si' => '#32cd32');
        $this->arreglo_campos[$num]["colores"] = $colores;

        $num++;
        $this->arreglo_campos[$num]["nombre"] = "ven_estado";
        $this->arreglo_campos[$num]["texto"] = "Estado";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "Pendiente,Pagado,Anulado,Retenido,Cambiado,Fusionado:Pendiente,Pagado,Anulado,Retenido,Cambiado,Fusionado";

        $num++;
        $this->arreglo_campos[$num]["nombre"] = "ven_tipo";
        $this->arreglo_campos[$num]["texto"] = "Tipo de Venta";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "Contado,Credito:Contado,Credito";
		
		$num++;
        $this->arreglo_campos[$num]["nombre"] = "suc_id";
        $this->arreglo_campos[$num]["texto"] = "Sucursal";
        $this->arreglo_campos[$num]["tipo"] = "combosql";
        $this->arreglo_campos[$num]["sql"] = "select suc_id as codigo,suc_nombre as descripcion from ter_sucursal";

        $num++;
        $this->arreglo_campos[$num]["nombre"] = "producto";
        $this->arreglo_campos[$num]["texto"] = "Producto";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "reserva_producto,compra_conjunta,compra_posterior:Reserva Producto,Compra Conjunta,Compra Posterior";
        $colores_compras = array('reserva_producto' => 'green', 'compra_conjunta' => 'blue', 'compra_posterior' => 'gray');
        $this->arreglo_campos[$num]["colores"] = $colores_compras;

        $this->link = 'gestor.php';

        $this->modulo = 'venta';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('VENTA');

        $this->usu = new USUARIO;
    }

    function concretar_venta_por_reserva() {
        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$_GET[id_res]'");
        if ($reserva->res_estado == 'Habilitado') {

            $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id=$reserva->res_int_id");
            if ($interno->int_completo) {
                $this->cargar_datos_para_concretar_venta_por_reserva($_GET['id_res']);
                $this->formulario_tcp('reserva');
            } else {
                $mensaje = 'No puede realizar la Venta, por que la persona no tiene completa su informacion.';
                $this->formulario->ventana_volver($mensaje, 'gestor.php?mod=reserva&tarea=ACCEDER');
            }
        } else {
            $mensaje = 'No puede realizar la Venta, por que la Reserva que pertenece al Terreno seleccionado no se encuentra en estado <b>Habilitado</b>.';
            $this->formulario->ventana_volver($mensaje, 'gestor.php?mod=reserva&tarea=ACCEDER');
        }
    }

    function cargar_datos_para_concretar_venta_por_reserva($reserva) {
        //        echo "la reserva es: " . $reserva;
        $conec = new ADO();

        $sql = "SELECT 		
                            res_id,res_int_id,res_vdo_id,res_lot_id,res_fecha,
                                                        res_hora,res_estado,res_usu_id,int_nombre,int_apellido,
                                                        urb_id,urb_nombre,man_id,man_nro,lot_id,lot_nro,lot_tipo,
                                                        uv_id,uv_nombre,res_multinivel,
                            zon_moneda, res_vdo_ext
                            FROM 
                            reserva_terreno 
                            inner join interno on (res_int_id=int_id)
                            inner join lote on (res_lot_id=lot_id)
                            inner join zona on (lot_zon_id=zon_id)
                            inner join uv on (uv_id=lot_uv_id)
                            inner join manzano on (lot_man_id=man_id)
                            inner join urbanizacion on (man_urb_id=urb_id) where res_id='" . $reserva . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();
        $_POST['ven_int_id'] = $objeto->res_int_id;
        $_POST['int_nombre_persona'] = $objeto->int_nombre . ' ' . $objeto->int_apellido;
        $_POST['vendedor'] = $objeto->res_vdo_id;
        $_POST['vendedor2'] = $objeto->res_vdo_ext;
        $_POST['ven_urb_id'] = $objeto->urb_id;
        $_POST['ven_man_id'] = $objeto->man_id;
        $_POST['ven_uv_id'] = $objeto->uv_id;
        $_POST['ven_lot_id'] = $objeto->res_lot_id;
        $_POST['ven_moneda'] = $objeto->res_lot_id;
        $_POST['ven_moneda'] = $objeto->zon_moneda;
//        $sql = "select SUM(respag_monto) as monto_anticipo from reserva_pago where respag_res_id=" . $reserva . " and respag_estado='Pagado'";
//        $conec->ejecutar($sql);
//        $objeto = $conec->get_objeto();
//        $_POST['ven_anticipo'] = $objeto->monto_anticipo;
        if ($_GET['moneda_monto_reserva'] == '1') {
            $_POST['ven_anticipo'] = $_GET['monto_bs'];
        } else {
            $_POST['ven_anticipo'] = $_GET['monto_sus'];
        }
        $_POST['id_res'] = $reserva;
        $_POST['multinivel'] = $objeto->res_multinivel;


        $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,zon_color,cast(lot_nro as SIGNED) as numero from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_man_id='" . $_POST['ven_man_id'] . "' and lot_uv_id='" . $_POST['ven_uv_id'] . "' and lot_estado='Reservado' and lot_id='" . $_POST['ven_lot_id'] . "' order by numero asc";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['datos_lote'] = $objeto->id;
    }

    function get_estado_reserva($id_reserva) {
        $conec = new ADO();

        $sql = "select res_estado from reserva_terreno where res_id=$id_reserva";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->res_estado;
    }

    function dibujar_busqueda() {
        ?>
        <script>
            function ejecutar_script(id, tarea) {

                if (tarea == 'ANULAR')
                {
                    var txt = 'Esta seguro de anular la Venta?';
                    $.prompt(txt, {
                        buttons: {Anular: true, Cancelar: false},
                        callback: function(v, m, f) {
                            if (v) {
                                location.href = 'gestor.php?mod=venta&tarea=' + tarea + '&id=' + id;
                            }

                        }
                    });
                }

                if (tarea == 'RETENER')
                {
                    var txt = 'Esta seguro de retener la Venta?';

                    $.prompt(txt, {
                        buttons: {Retener: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                location.href = 'gestor.php?mod=venta&tarea=' + tarea + '&id=' + id;
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

    function set_opciones() {

        $nun = 0;

//		if($this->verificar_permisos('AGREGAR'))
//		{
//			$this->arreglo_opciones[$nun]["tarea"]='AGREGAR';
//			$this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
//			$this->arreglo_opciones[$nun]["nombre"]='VER';
//			$nun++;
//		}
        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }

        if ($this->verificar_permisos('ANULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('RETENER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'RETENER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/retener.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'REVERTIR VENTA';
//			$this->arreglo_opciones[$nun]["script"]="ok";
            $nun++;
        }

        if ($this->verificar_permisos('FIRMA CONTRATO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'FIRMA CONTRATO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/firma.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'FIRMA CONTRATO';
            $nun++;
        }


        if ($this->verificar_permisos('IMPRIMIR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'IMPRIMIR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/imprimir.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'IMPRIMIR DOCUMENTO';
            $nun++;
        }
        if ($this->verificar_permisos('INTERCAMBIO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'INTERCAMBIO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/shop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'INTERCAMBIO';
            $nun++;
        }

        if ($this->verificar_permisos('AUTORIZACION')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'AUTORIZACION';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/autorizacion.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'AUTORIZACION DE PAGOS';
            $nun++;
        }
        if ($this->verificar_permisos('PAGOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PAGOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cuenta.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PAGOS';
            $nun++;
        }
        if ($this->verificar_permisos('SEGUIMIENTO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'SEGUIMIENTO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/seguimiento.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'SEGUIMIENTO';
            $nun++;
        }
        if ($this->verificar_permisos('EXTRACTOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'EXTRACTOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/extractos.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'EXTRACTOS';
            $nun++;
        }
        if ($this->verificar_permisos('REFORMULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'REFORMULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/reformular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'REFORMULAR';
            $nun++;
        }
        if ($this->verificar_permisos('CAMBIAR LOTE')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CAMBIAR LOTE';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cambiar.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR LOTE';
            $nun++;
        }
        if ($this->verificar_permisos('FUSION')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'FUSION';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/union.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'FUSION DE VENTAS';
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
        if ($this->verificar_permisos('NOTA')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'NOTA';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/notas.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'NOTAS';
            $nun++;
        }
        if ($this->verificar_permisos('FECHA VALOR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'FECHA VALOR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/edit_date.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'FECHA VALOR';
            $nun++;
        }
        if ($this->verificar_permisos('EDITAR DOCUMENTO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'EDITAR DOCUMENTO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/edit_document.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'EDITAR DATOS VENTA';
            $nun++;
        }
        if ($this->verificar_permisos('ACTIVAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ACTIVAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/active.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'REACTIVAR VENTA';
            $nun++;
        }
        if ($this->verificar_permisos('DESBLOQUEAR CUOTAS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'DESBLOQUEAR CUOTAS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/candado.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'BLOQUEAR/DESBLOQUEAR';
            $nun++;
        }
        if ($this->verificar_permisos('DOCUMENTOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'DOCUMENTOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/documentos.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DOCUMENTOS';
            $nun++;
        }

        if ($this->verificar_permisos('PARAMETROS VARIOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PARAMETROS VARIOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/parametros.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PARAMETROS VARIOS';
            $nun++;
        }

        if ($this->verificar_permisos('CAMBIAR VENDEDOR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CAMBIAR VENDEDOR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/edit_user.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CAMBIAR VENDEDOR';
            $nun++;
        }

        if ($this->verificar_permisos('PRODUCTO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PRODUCTO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/producto2.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'GESTIONAR PRODUCTOS';
            $nun++;
        }

        if ($this->verificar_permisos('RESERVA PRODUCTO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'RESERVA PRODUCTO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anticipo_producto.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'RESERVA PRODUCTO';
            $nun++;
        }
    }

    function total_pagado($ven_id) {
//        $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, 
//                                sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento ,
//                                sum(ind_costo_pagado) as costo
//                                from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
//                        ";
//        $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
        $pagado = FUNCIONES::total_pagado($ven_id);
        return $pagado;
    }

    function existe_algun_monto_parcial($id_venta) {
        $conec = new ADO();

        $sql = "select SUM(ind_monto_parcial) as monto_parcial from interno_deuda where ind_tabla='venta' and ind_tabla_id='$id_venta' and ind_estado<>'Anulado'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->monto_parcial;
    }

    function tipo_venta($id_venta) {
        $conec = new ADO();

        $sql = "select ven_tipo from venta where ven_id=$id_venta";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->ven_tipo;
    }

    function estado_venta($id_venta) {
        $conec = new ADO();

        $sql = "select ven_estado from venta where ven_id=$id_venta";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->ven_estado;
    }

    function amortizar() {
        if ($this->tipo_venta($_GET['id']) <> 'Contado') {
            if ($this->estado_venta($_GET['id']) <> 'Anulado' && $this->estado_venta($_GET['id']) <> 'Retenido' && $this->estado_venta($_GET['id']) <> 'Pagado') {
                //Verifico que no exista Monto Parcial, para asi recien Amortizar
                if ($this->existe_algun_monto_parcial($_GET['id']) == 0) {
                    ?>
                    <script type="text/javascript">


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


                        function limpiar_meses_plazo3()
                        {
                            document.frm_sentencia3.meses.value = "";
                        }

                        function limpiar_cuota_mensual3()
                        {
                            document.frm_sentencia3.cuota_mensual.value = "";
                        }

                    </script>
                    <?php
                    $this->formulario->dibujar_cabecera();
                    ?>
                    <!--MaskedInput-->
                    <script type="text/javascript" src="js/util.js"></script>
                    <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                    <!--MaskedInput-->

                    <div id="Contenedor_NuevaSentencia">
                        <form id="frm_sentencia3" name="frm_sentencia3" action="gestor.php?mod=venta&tarea=PAGOS&id=<?php echo $_GET['id']; ?>&am=ok" method="POST" enctype="multipart/form-data">  
                    <?php $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'"); ?>
                            <div id="FormSent" style="width:65%">
                                <input name="ven_factor_anual" id="ven_factor_anual" value="<?php echo $venta->ven_factor_anual; ?>" type="hidden" >
                                <input name="ven_moneda" id="ven_moneda" value="<?php echo $venta->ven_moneda; ?>" type="hidden" >
                                <div class="Subtitulo">Situacion Actual</div>
                                <div id="ContenedorSeleccion">
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Valor Contado</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_valor" id="ven_valor" size="12" value="<?php echo $venta->ven_valor; ?>" type="text" readonly="">
                                        </div>
                                        <div class="Etiqueta" >Descuento</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_descuento" id="ven_descuento" size="12" value="<?php echo $venta->ven_decuento; ?>" type="text" readonly="">
                                        </div>   		
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Cuota Inicial</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_cuota_inicial" id="ven_cuota_inicial" size="12" value="<?php echo $venta->ven_cuota_inicial; ?>" type="text" readonly="">
                                        </div>
                                        <div class="Etiqueta" >Anticipo de Reserva</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_anticipo" id="ven_anticipo" size="12" value="<?php echo $venta->ven_res_anticipo; ?>" type="text" readonly="">
                                        </div>   		
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Saldo a Financiar</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_saldo" id="ven_saldo" size="12" value="<?php echo $saldo_finanaciar = $venta->ven_monto - $venta->ven_res_anticipo - $venta->ven_cuota_inicial; ?>" type="text" readonly="">
                                        </div>
                                        <?php $sql_sum = "select sum(ind_capital_pagado) as campo from interno_deuda 
                                            where ind_tabla_id='$venta->ven_id' and ind_tabla='venta' and ind_estado='Pagado' and ind_num_correlativo!=0 and ind_tipo='pcuota'"; ?>
                    <?php $capital_pagado = FUNCIONES::atributo_bd_sql($sql_sum); ?>
                                        <div class="Etiqueta" >Capital Pagado</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_capital_pagado" id="ven_capital_pagado" size="12" value="<?php echo $capital_pagado * 1; ?>" type="text" readonly="">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <?php $sql_count = "select count(*) as campo from interno_deuda 
                                            where ind_tabla_id='$venta->ven_id' and ind_tabla='venta' and ind_num_correlativo>0 and ind_tipo='pcuota'"; ?>
                    <?php $plazo_venta = FUNCIONES::atributo_bd_sql($sql_count); ?>
                                        <div class="Etiqueta" >Plazo Venta</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="ven_meses_plazo" id="ven_meses_plazo" size="12" value="<?php echo $plazo_venta; ?>" type="text" readonly="">
                                        </div>
                                        <div class="Etiqueta" >Plazo Restante</div>
                                        <div id="CajaInput">
                                            <?php $sql_count = "select count(*) as campo from interno_deuda 
                                            where ind_tabla_id='$venta->ven_id' and ind_tabla='venta' and ind_estado='Pendiente' and ind_num_correlativo>0 and ind_tipo='pcuota'"; ?>
                    <?php $plazo_restante = FUNCIONES::atributo_bd_sql($sql_count); ?>
                                            <input class="caja_texto" name="ven_plazo_restante" id="ven_plazo_restante" size="12" value="<?php echo $plazo_restante; ?>" type="text" readonly="">
                                        </div>

                                    </div>

                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Saldo Restante</div>
                                        <div id="CajaInput">                                            
                                            <input class="caja_texto" name="ven_saldo_restante" id="ven_saldo_restante" size="12" value="<?php echo $saldo_finanaciar - $capital_pagado; ?>" type="text" readonly="">
                                        </div>
                                        <div class="Etiqueta" >Ultima Fecha Pagada</div>
                                        <?php
                                        $ucuota = FUNCIONES::objeto_bd_sql("SELECT * FROM interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' AND ind_estado='Pagado' ORDER BY ind_id DESC LIMIT 0,1");
                                        $ufecha = $ucuota ? $ucuota->ind_fecha_pago : $venta->ven_fecha;
                                        ?>
                                        <div id="CajaInput">                                            
                                            <input class="caja_texto" name="txt_ufecha" id="txt_ufecha" size="12" value="<?php echo FUNCIONES::get_fecha_latina($ufecha); ?>" type="text" readonly="">
                                            <input  name="ufecha" id="ufecha" value="<?php echo $ufecha; ?>" type="hidden" >
                                            <input  id="interes_anual" value="<?php echo $venta->ven_val_interes; ?>" type="hidden" >
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">

                                    </div>
                                    <!--Fin-->


                                </div>
                                <div class="Subtitulo">Nueva Reformulacion</div>
                                <div id="ContenedorSeleccion">
                                    <div id="ContenedorDiv">

                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Monto a Amortizar</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="nmonto_amortizar" id="nmonto_amortizar" size="12" value="" type="text" >
                                        </div>
                                        <div class="Etiqueta" ><span class="flechas1">*</span>Fecha de Reformulacion</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="fecha_amortizacion" id="fecha_amortizacion" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                        </div>
                                        <div id="box_amortizacion" style="float: left;">
                                            <div class="Etiqueta" ><span class="flechas1">*</span>Interes a la Fecha</div>
                                            <div id="CajaInput">
                                                <input class="caja_texto" name="interes_fecha" id="interes_fecha" size="12" value="" type="text" >
                                            </div>
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Definir Cuotas por:</div>
                                        <div id="CajaInput">
                                            <select id="def_plan" name="def_plan" style="width: 85px;">
                                                <option value="meses">Meses</option>
                                                <option value="cuota">Cuota</option>
                                            </select>
                                        </div>
                                        <div class="def_meses Etiqueta" ><span class="flechas1">* </span>Meses</div>
                                        <div class="def_meses" id="CajaInput">
                                            <input class="caja_texto" name="nmeses" id="nmeses" size="12" value="" type="text" >
                                        </div>
                                        <div class="def_cuota Etiqueta" ><span class="flechas1">* </span>Cuota</div>
                                        <div class="def_cuota" id="CajaInput">
                                            <input class="caja_texto" name="ncuota" id="ncuota" size="12" value="" type="text" >
                                        </div>
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Primer Pago</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="nprimera_fecha" id="nprimera_fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                        </div>
                                        <script>
                                            mask_decimal('#nmonto_amortizar', null);
                                            mask_decimal('#nmeses_plazo', null);
                                            $("#nprimera_fecha").mask("99/99/9999");
                                            $("#fecha_amortizacion").mask("99/99/9999");
                                        </script>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <img onclick="javascript:generar_pagos();" style="margin:0px 0px 0px -15px; cursor: pointer" src="imagenes/generar.png">
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div id="contenido_reporte">
                                            <div id="plan_de_pagos">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div id="CajaBotones">
                                            <center>
                                                <input type="hidden" class="boton" name="formu" value="ok">
                                                <input type="button" class="boton" name="" value="Generar Cuotas" onClick="enviar3(this.form);">
                                            </center>
                                        </div>
                                    </div>
                                    <script>
                                            $('#nmonto_amortizar').keyup(function() {
                                                var val = $(this).val() * 1;
                                                if (val > 0) {
                                                    $('#box_amortizacion').show();
                                                } else {
                                                    $('#box_amortizacion').hide();
                                                }
                                            });
                                            $('#def_plan').change(function() {
                                                var tipo = $(this).val();
                                                if (tipo === 'meses') {
                                                    $('.def_meses').show();
                                                    $('.def_cuota').hide();
                                                } else if (tipo === 'cuota') {
                                                    $('.def_meses').hide();
                                                    $('.def_cuota').show();
                                                }
                                            });

                                            $('#def_plan').trigger('change');
                                            $('#nmonto_amortizar').trigger('keyup');
                                            function generar_pagos() {
                                                var saldo_restante = $('#ven_saldo_restante').val();
                                                var interes = $('#interes_anual').val();
                                                var fecha_pri = $('#nprimera_fecha').val();

                                                var monto_amortizar = $('#nmonto_amortizar').val() * 1;
                                                var monto_interes = $('#interes_fecha').val() * 1;

                                                var tipo = $('#def_plan option:selected').val();
                                                var meses = '';
                                                var cuota = '';
                                                if (tipo === 'meses') {
                                                    meses = $('#nmeses').val();
                                                    cuota = '';
                                                } else if (tipo === 'cuota') {
                                                    meses = '';
                                                    cuota = $('#ncuota').val();
                                                }

                                                if ((meses !== '' || cuota !== '') && fecha_pri !== '') {
                                                    // saldo financiar,meses plazo, interes,fecha_pri_cuota,moneda
                                                    var nsaldo_financiar = saldo_restante;
                                                    if (monto_amortizar > 0) {
                                                        var capital_amortizar = monto_amortizar - monto_interes;
                                                        if (capital_amortizar < 0) {
                                                            $.prompt('El monto a amortizar debe ser mayor al interes generado', {opacity: 0.8});
                                                            return;
                                                        }
                                                        nsaldo_financiar = saldo_restante - capital_amortizar;
                                                    }
                                                    console.info(interes + ' - ' + fecha_pri);
                                                    var valores = "tarea=plan_pagos"
                                                            + "&saldo_financiar=" + nsaldo_financiar
                                                            + "&monto_total=" + ''
                                                            + "&cuota_inicial=" + ''
                                                            + "&meses_plazo=" + meses
                                                            + "&cuota_mensual=" + cuota
                                                            + "&fecha_pri_cuota=" + fecha_pri
                                                            + "&interes=" + interes;
                                                    ejecutar_ajax('ajax.php', 'plan_de_pagos', valores, 'POST');
                                                } else {
                                                    $('#tprueba tbody').remove();
                                                    $.prompt('Los campos marcado con (*) son requeridos.', {opacity: 0.8});
                                                }
                                            }
                                            $('#fecha_amortizacion').focusout(function() {
                                                var fecha_am = fecha_mysql($(this).val());
                                                var fecha_ultimo = $('#ufecha').val();
                                                var interes_anual = $('#interes_anual').val();
                                                if (fecha_am < fecha_ultimo) {
                                                    fecha_am = fecha_ultimo;
                                                    $('#fecha_amortizacion').val(fecha_latina(fecha_ultimo));
                                                }

                                                var dif_dias = diferencia_dias(fecha_ultimo, fecha_am);
                                                var idia = (interes_anual / 360) / 100;
                                                var saldo = $('#ven_saldo_restante').val();
                                                var interes = idia * saldo * dif_dias;
                                                console.info(idia + '*' + saldo + '*' + dif_dias);
                                                $('#interes_fecha').val(interes.toFixed(2));
                                            });

                                            $('#fecha_amortizacion').trigger('focusout');

                                            function enviar3(frm) {

                                                var fecha_pri = $('#nprimera_fecha').val();

                                                var monto_amortizar = $('#nmonto_amortizar').val() * 1;
                                                var monto_interes = $('#interes_fecha').val() * 1;

                                                var tipo = $('#def_plan option:selected').val();
                                                var meses = '';
                                                var cuota = '';
                                                if (tipo === 'meses') {
                                                    meses = $('#nmeses').val();
                                                    cuota = '';
                                                } else if (tipo === 'cuota') {
                                                    meses = '';
                                                    cuota = $('#ncuota').val();
                                                }

                                                if (monto_amortizar > 0) {
                                                    var capital_amortizar = monto_amortizar - monto_interes;
                                                    if (capital_amortizar < 0) {
                                                        $.prompt('El monto a amortizar debe ser mayor al interes generado', {opacity: 0.8});
                                                        return;
                                                    }

                                                }

                                                if ((meses !== '' || cuota !== '') && fecha_pri !== '') {
                                                    var fecha_am = $('#fecha_amortizacion').val()
                                                    if (fecha_am !== '') {
                                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_am}, function(respuesta) {
                                                            var dato = JSON.parse(respuesta);
                                                            if (dato.response === "ok") {
                                                                frm.submit();
                                                            } else if (dato.response === "error") {
                                                                $.prompt(dato.mensaje);
                                                                return false;
                                                            }
                                                        });
                                                    } else {
                                                        $.prompt('Ingrese la fecha de la transaccion.', {opacity: 0.8});
                                                    }

                                                } else {
                                                    $.prompt('Los campos marcado con (*) son requeridos.', {opacity: 0.8});
                                                }
                                            }
                                    </script>
                                </div>
                        </form>	
                    </div>
                    <?php
                } else {

                    $mensaje = 'No puede realizar la accion de <b>Amortizar</b>, por que existe <b>Cuotas con Pagos Parciales</b> que no han sido Pagado Totalmente.';
                    $this->mensaje = $mensaje;
                    if ($this->mensaje <> "") {
                        $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo, "", "Error");
                    }
                }
            } else {
                $mensaje = 'No puede realizar la accion de <b>Amortizar</b>, por que la venta se encuentra en estado <b>Pagado</b> o la venta ya fue <b>Anulada</b> o <b>Retenida</b> anteriormente..';
                //$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
                $this->mensaje = $mensaje;
                if ($this->mensaje <> "") {
                    $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo, "", "Error");
                }
            }
        } else {
            $mensaje = 'No puede realizar la accion de <b>Amortizar</b>, por que la venta es al <b>Contado</b>.';
            $this->mensaje = $mensaje;
            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo, "", "Error");
            }
        }
    }

    function amortizar_capital() {
        $conec = new ADO();
        $id_venta = $_GET[id];
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$id_venta'");

        $sql_primero = "select * from interno_deuda where ind_estado='Pagado' and ind_tabla='venta' and ind_tabla_id='$id_venta' and ind_num_correlativo>0 order by ind_num_correlativo desc limit 1";

        $primer_pago = FUNCIONES::objeto_bd_sql($sql_primero);

        $fecha = FUNCIONES::get_fecha_mysql($_POST[nprimera_fecha]);

        $def_plan = $_POST[def_plan];
        if ($def_plan == 'meses') {
            $cuota_mensual = '';
            $meses_plazo = $_POST[nmeses];
        } elseif ($def_plan == 'cuota') {
            $cuota_mensual = $_POST[ncuota];
            $meses_plazo = '';
        }

        $saldo_financiar = $venta->ven_monto - $venta->ven_res_anticipo - $venta->ven_cuota_inicial;

        $sql_sum = "select sum(ind_capital_pagado) as campo from interno_deuda 
                                            where ind_tabla_id='$venta->ven_id' and ind_tabla='venta' and ind_estado='Pagado' and ind_num_correlativo!=0 and ind_tipo='pcuota'";
        $capital_pagado = FUNCIONES::atributo_bd_sql($sql_sum);

        $nsaldo_financiar = $saldo_financiar - $capital_pagado;


        $sql_sum_interes = "select sum(ind_interes) as campo from interno_deuda where ind_tabla='venta' and ind_tabla_id='$id_venta' and ind_estado='Pendiente'";
        $sum_anterior_interes = FUNCIONES::atributo_bd_sql($sql_sum_interes);

        $sqlo = "delete from interno_deuda where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id='" . $id_venta . "' and ind_tipo='pcuota'";
        $conec->ejecutar($sqlo);

        $nmonto_amortizar = $_POST[nmonto_amortizar];
        $fecha_amortizacion = FUNCIONES::get_fecha_mysql($_POST[fecha_amortizacion]);
        if ($nmonto_amortizar * 1 > 0) {
            $interes_am = $_POST[interes_fecha];
            $capital_am = $nmonto_amortizar - $interes_am;
            $nsaldo_financiar = $nsaldo_financiar - $capital_am;
            $sql = "insert into interno_deuda(
                    ind_int_id,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,
                    ind_estado,ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
                    ind_dias_interes,ind_interes,ind_capital,ind_monto,ind_saldo,ind_monto_parcial,ind_num_correlativo,ind_tipo
                )values(
                    '$venta->ven_int_id','$venta->ven_moneda','Cuota Amortizar - $venta->ven_concepto','$venta->fecha','$_SESSION[id]',
                    'Pendiente','0000-00-00','venta','$venta->ven_id','$fecha_amortizacion',
                    '30','$interes_am','$capital_am','$nmonto_amortizar','$nsaldo_financiar','0','-1','pcuota'
                )";
            $conec->ejecutar($sql);
        }

        $parametros = array(
            'int_id' => $venta->ven_int_id,
            'saldo' => $nsaldo_financiar,
            'saldo_total' => $nsaldo_financiar,
            'interes' => $venta->ven_val_interes,
            'meses_plazo' => $meses_plazo,
            'cuota_mensual' => $cuota_mensual,
            'moneda' => $venta->ven_moneda,
            'concepto' => $venta->ven_concepto,
            'fecha' => $venta->ven_fecha,
            'fecha_inicio' => $venta->ven_fecha,
            'fecha_pri_pago' => $fecha,
            'usuario' => $this->usu->get_id(),
            'tabla' => 'venta',
            'nro_cuota_inicio' => $primer_pago->ind_num_correlativo + 1,
            'tabla_id' => $venta->ven_id,
            'ind_tipo' => 'pcuota',
        );
//            var_dump($parametros);

        $this->generar_plan_pagos($parametros, $conec);
        $sum_nuevo_interes = FUNCIONES::atributo_bd_sql($sql_sum_interes);
        $perdida_interes = $sum_anterior_interes - $sum_nuevo_interes;
//            echo $perdida_interes;
        if (round($perdida_interes, 2) != 0) {
            include_once 'clases/registrar_comprobantes.class.php';
            $interno = FUNCIONES::atributo_bd("interno", "int_id=" . $venta->ven_int_id, "concat(int_nombre,' ',int_apellido )");

            $glosa = "Reformulacion de la Venta Nro. $venta->ven_id: " . $venta->ven_concepto; //"Recaudacion de Cobranza ".$fecha_comprobante." - ".$interno;

            $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

            $fecha_cmp = $fecha_amortizacion;
            $ges_id = $_SESSION['ges_id'];

            $comprobante = new stdClass();
            $comprobante->une_id = $urbanizacion->urb_une_id;
            $comprobante->tipo = "Diario";
            $comprobante->mon_id = $venta->ven_moneda;
            $comprobante->nro_documento = date("Ydm");
            $comprobante->fecha = $fecha_cmp;
            $comprobante->ges_id = $ges_id;
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
            $comprobante->forma_pago = 'Efectivo';
            $comprobante->ban_id = '';
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = $interno;
            $comprobante->tabla = "reformulacion_venta";
            $comprobante->tabla_id = $venta->ven_id;

            if ($perdida_interes > 0) { /// perdida de interes
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.1.06.03'), "debe" => $perdida_interes, "haber" => 0,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0'
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.01.02'), "debe" => 0, "haber" => $perdida_interes,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0'
                );
            } else { /// ganancia de interes
                $perdida_interes = $perdida_interes * -1;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.1.06.03'), "debe" => 0, "haber" => $perdida_interes,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0'
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.01.02'), "debe" => $perdida_interes, "haber" => 0,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0'
                );
            }

//                FUNCIONES::print_pre($comprobante);
            COMPROBANTES::registrar_comprobante($comprobante);
        }
    }

    function dibujar_listado() {

        $sql = "SELECT 
			ven_monto,ven_id,ven_fecha,ven_tipo,ven_moneda,ven_estado,
                        int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,ven_codigo,ven_fecha_firma,
                        ven_cuota_inicial,ven_res_anticipo,ven_monto,ven_numero,
                        ven_devuelto,ven_monto_intercambio,ven_bloqueado,
                        ven_devuelto,ven_dev_destino,ven_multinivel,ven_vdo_id,ven_urb_id,
                        ven_prod_id
			FROM 
			venta 
			inner join interno on (ven_int_id=int_id)
			inner join lote on (ven_lot_id=lot_id)
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
                        inner join ter_sucursal on (suc_id=ven_suc_id)
                        left join venta_producto on (vprod_ven_id=ven_id and vprod_estado in ('Pendiente','Pagado'))
                        left join reserva_producto on (rprod_ven_id=ven_id and rprod_estado in ('Pendiente','Habilitado'))
			";


        /* $sql = "SELECT 
          distinct(ven_id),ven_monto,ven_fecha,ven_tipo,ven_moneda,ven_estado,
          int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,lot_tipo,ven_codigo,ven_fecha_firma,
          ven_cuota_inicial,ven_res_anticipo,ven_monto,ven_numero,
          ven_devuelto,ven_monto_intercambio,ven_bloqueado,
          ven_devuelto,ven_dev_destino,ven_multinivel,ven_vdo_id,ven_prod_id as uprod_id
          FROM
          venta
          inner join interno on (ven_int_id=int_id)
          inner join lote on (ven_lot_id=lot_id)
          inner join manzano on (lot_man_id=man_id)
          inner join urbanizacion on (man_urb_id=urb_id)
          inner join ter_sucursal on (suc_id=ven_suc_id)
          "; */


        $this->set_sql($sql, ' order by ven_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function ver() {
        $this->nota_de_venta($_GET['id']);
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nro</th>
            <th>Nro. Adj.</th>
            <th>C&oacute;digo</th>
            <th>Fecha</th>
            <th>Persona</th>
            <th>Vdo/Patr</th>
            <th>Tipo</th>
            <th>Monto</th>
            <th>Moneda</th>
            <?php
            if ($this->ver_pagado) {
                ?>
                    <!--<th class="tOpciones">Pagado</th>-->
                <?php
            }
            if ($this->ver_saldo_capital) {
                ?>
                    <!--<th class="tOpciones">Saldo Capital</th>-->
                <?php
            }
            ?>
            <th>Urbanizaci?n</th>
            <th>Manzano</th>		
            <th>Lote</th>
            <th>Firmado</th>
            <th>Modalidad</th>
            <th>Estado</th>
            <th>Est. Cuotas</th>
            <!--<th>Revisado</th>-->
            <th>Producto</th>
            <th class="tOpciones" width="140px">Opciones</th>

        </tr>

        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();
        $a_est = array('Pendiente' => '#b9a100', 'Pagado' => '#037e00', 'Anulado' => '#ff0000', 'Retenido' => '#7b0000', 'Cambiado' => '#8701c6', 'Fusionado' => '#e34000');
        $color_mod = array('si' => '#32cd32', 'no' => '#db7093');
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            $operaciones = array();
            $filtros = array();
            // $operaciones[] = "IMPRIMIR";
            //('Pendiente','Pagado','Anulado','Retenido','Cambiado','Fusionado')

            $urb = FUNCIONES::lista_bd_sql("select * from urbanizacion_producto where uprod_urb_id='$objeto->ven_urb_id'");
            if (count($urb) == 0) {
                $operaciones[] = "PRODUCTO";
                $operaciones[] = "RESERVA PRODUCTO";
            }
            
            if ($objeto->ven_prod_id > 0) {
                $operaciones[] = "PRODUCTO";
                $operaciones[] = "RESERVA PRODUCTO";
            }

            if ($objeto->ven_monto_intercambio == 0) {
                $operaciones[] = "INTERCAMBIO";
            }
            if ($objeto->ven_estado == 'Anulado') {
                $operaciones[] = "RETENER";
                $operaciones[] = "AUTORIZACION";
                $operaciones[] = "PAGOS";
                $operaciones[] = "FUSION";
                $operaciones[] = "CAMBIAR PROPIETARIO";
                $operaciones[] = "DEVOLVER";
                $operaciones[] = "VER DEVOLUCION";
                $operaciones[] = "CAMBIAR LOTE";
                $operaciones[] = "REFORMULAR";
                $operaciones[] = "SEGUIMIENTO";
                $operaciones[] = "EXTRACTOS";
                $operaciones[] = "ANULAR";
                $operaciones[] = "FECHA VALOR";
                $operaciones[] = "ACTIVAR";
                $operaciones[] = "EDITAR DOCUMENTO";
                $operaciones[] = "ACTIVAR";
                $operaciones[] = "PRODUCTO";
                $operaciones[] = "RESERVA PRODUCTO";
            }
            if ($objeto->ven_tipo == 'Contado') {
//                    $operaciones[] = "RETENER";
                if ($objeto->ven_estado == 'Pagado') {
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    //                    $operaciones[] = "CAMBIAR PROPIETARIO"; 
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "VER DEVOLUCION";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "SEGUIMIENTO";
                    $operaciones[] = "EXTRACTOS";
                    $operaciones[] = "FECHA VALOR";
                    $operaciones[] = "ACTIVAR";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
                if ($objeto->ven_estado == 'Retenido' && $objeto->ven_devuelto == '0') {
                    $operaciones[] = "RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "CAMBIAR PROPIETARIO";
                    $operaciones[] = "VER DEVOLUCION";
                    $operaciones[] = "ANULAR";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "FECHA VALOR";

                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
                if ($objeto->ven_estado == 'Retenido' && $objeto->ven_devuelto == '1') {
                    $operaciones[] = "RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "CAMBIAR PROPIETARIO";
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "ANULAR";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "FECHA VALOR";
                    $operaciones[] = "ACTIVAR";
                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
            } else {//CREDITO
                if ($objeto->ven_estado == 'Pendiente') {
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "VER DEVOLUCION";
                    $operaciones[] = "ACTIVAR";
//                                $operaciones[]="ANULAR";anular.png
                }
                if ($objeto->ven_estado == 'Pagado') {
//                                $operaciones[]="RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "VER DEVOLUCION";
//                        $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "ANULAR";
//                        $operaciones[] = "FECHA VALOR";
                    $operaciones[] = "ACTIVAR";
                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }

                if ($objeto->ven_estado == 'Cambiado' || $objeto->ven_estado == 'Fusionado') {
                    $operaciones[] = "RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "CAMBIAR PROPIETARIO";
                    $operaciones[] = "CAMBIAR VENDEDOR";
                    $operaciones[] = "DESBLOQUEAR CUOTAS";
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "ANULAR";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "VER DEVOLUCION";
                    $operaciones[] = "FECHA VALOR";
                    $operaciones[] = "ACTIVAR";
                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
				
				if ($objeto->ven_estado == 'Retenido' && $objeto->ven_devuelto == '0') {
                    $operaciones[] = "RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "CAMBIAR PROPIETARIO";
                    $operaciones[] = "VER DEVOLUCION";
                    $operaciones[] = "ANULAR";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "FECHA VALOR";

                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
                if ($objeto->ven_estado == 'Retenido' && $objeto->ven_devuelto == '1') {
                    $operaciones[] = "RETENER";
                    $operaciones[] = "AUTORIZACION";
                    $operaciones[] = "PAGOS";
                    $operaciones[] = "FUSION";
                    $operaciones[] = "CAMBIAR PROPIETARIO";
                    $operaciones[] = "DEVOLVER";
                    $operaciones[] = "ANULAR";
                    $operaciones[] = "REFORMULAR";
                    $operaciones[] = "CAMBIAR LOTE";
                    $operaciones[] = "FECHA VALOR";
                    $operaciones[] = "ACTIVAR";
                    $operaciones[] = "EDITAR DOCUMENTO";
                    $operaciones[] = "PRODUCTO";
                    $operaciones[] = "RESERVA PRODUCTO";
                }
            }
            if ($objeto->ven_bloqueado) {
                $operaciones[] = "RETENER";
                $operaciones[] = "RETENER";
                $operaciones[] = "AUTORIZACION";
                $operaciones[] = "PAGOS";
                $operaciones[] = "FUSION";
                $operaciones[] = "CAMBIAR PROPIETARIO";
                $operaciones[] = "DEVOLVER";
                $operaciones[] = "ANULAR";
                $operaciones[] = "REFORMULAR";
                $operaciones[] = "CAMBIAR LOTE";
                $operaciones[] = "FECHA VALOR";
                $operaciones[] = "ACTIVAR";
                $operaciones[] = "EDITAR DOCUMENTO";
                $operaciones[] = "PRODUCTO";
                $operaciones[] = "RESERVA PRODUCTO";
            }

            if ($objeto->ven_multinivel == 'no') {
                $operaciones[] = "PARAMETROS VARIOS";
            } else {
//                    $operaciones[] = "CAMBIAR LOTE";
//                $operaciones[] = "CAMBIAR PROPIETARIO";
                // $operaciones[] = "FUSION";
            }
            echo '<tr>';
            echo "<td>";
            echo $objeto->ven_id;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->ven_numero;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->ven_codigo;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->ven_fecha);
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->int_nombre . ' ' . $objeto->int_apellido;
            echo "&nbsp;</td>";

            $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor 
                    inner join interno on (vdo_int_id=int_id)
                    where vdo_id=$objeto->ven_vdo_id");

            if ($vendedor) {
                $ad = ($vendedor->vdo_venta_inicial > 0) ? "($vendedor->vdo_venta_inicial)" : '';
                echo "<td>";
                echo $vendedor->int_nombre . ' ' . $vendedor->int_apellido . $ad;
                echo "&nbsp;</td>";
            } else {
                echo "<td>";
                echo "No asignado";
                echo "&nbsp;</td>";
            }

            echo "<td>";
            echo $objeto->ven_tipo;
            echo "&nbsp;</td>";
            echo "<td>";
            echo number_format($objeto->ven_monto, 2);
            echo "&nbsp;</td>";
            echo "<td>";
            echo FUNCIONES::atributo_bd("con_moneda", "mon_id='$objeto->ven_moneda'", "mon_titulo");
            echo "&nbsp;</td>";
//                                if($this->ver_pagado){
//                                    $_tipo=$objeto->ven_tipo=='Contado'?'pcontado':'pcuota';
//                                    $and_correlativo=$objeto->ven_tipo=='Credito'?'and ind_num_correlativo!=0':'';
//                                    echo "<td>";
//                                            $sql_sum=  "select sum(ind_capital_pagado) as campo from interno_deuda 
//                                            where ind_tabla_id='$objeto->ven_id' and ind_tabla='venta' and ind_estado='Pagado' $and_correlativo and ind_tipo='$_tipo'";
//                                            $capital_pagado=  FUNCIONES::atributo_bd_sql($sql_sum)+$objeto->ven_cuota_inicial+$objeto->ven_res_anticipo;
//                                            echo number_format($capital_pagado, 2);
//                                    echo "&nbsp;</td>";
//                                }
//                                if($this->ver_saldo_capital){                                            
//                                    echo "<td>";
//                                    $saldo_capital=$objeto->ven_monto-$capital_pagado;
//                                        echo number_format($saldo_capital, 2);
//                                    echo "&nbsp;</td>";
//                                }
            echo "<td>";
            echo $objeto->urb_nombre;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->man_nro;
            echo "&nbsp;</td>";
            echo "<td>";
            echo $objeto->lot_nro;
            echo "&nbsp;</td>";
            $color = $objeto->ven_fecha_firma == '0000-00-00' ? '#ff0000' : '#328d01';
            echo "<td>";
            echo '<span style="padding: 0 2px; color:#fff; background-color:' . $color . '">';
            echo $objeto->ven_fecha_firma == '0000-00-00' ? 'No' : 'Si';
            echo "</span>";
            echo "&nbsp;</td>";
            $str_dev = "";
            if ($objeto->ven_devuelto) {
                $str_dev = "|Dev";
                if ($objeto->ven_dev_destino != 'Salida' || $objeto->ven_dev_destino != '') {
                    $str_dev.="|$objeto->ven_dev_destino";
                }
            }

            $modalidad = ($objeto->ven_multinivel == 'si') ? 'Multinivel' : 'Tradicional';

            echo "<td id='mod-$objeto->res_id'>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color_mod[$objeto->ven_multinivel]}'>$modalidad</span>";
            echo "</td>";

            echo "<td>";
            echo "<span style='padding:1px 3px ;color:#fff; background-color:{$a_est[$objeto->ven_estado]}'>$objeto->ven_estado$str_dev</span>";
            echo "&nbsp;</td>";
            $txt_bloqueado = $objeto->ven_bloqueado ? 'BLOQUEADO' : '';
            $color_bloqueado = $objeto->ven_bloqueado ? 'background-color:#000' : '';
            echo "<td>";
            echo "<span style='padding:1px 3px ;color:#fff; $color_bloqueado'>$txt_bloqueado</span>";
            echo "&nbsp;</td>";
            
            $txt_producto = '';
            if ($objeto->ven_prod_id > 0) {
                $uproducto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$objeto->ven_prod_id'");
                $txt_producto = $uproducto->uprod_nombre . "<br/>(COMPRA CONJUNTA)";
                $color_producto = "background-color:blue";
            } else {
                $obj_prod = FUNCIONES::obtener_producto($objeto->ven_id);
                if ($obj_prod) {
                    $txt_producto = $obj_prod->uprod_nombre . "<br/>(COMPRA POSTERIOR)";
                    $color_producto = "background-color:gray";
                } else {
                    $obj_res_prod = FUNCIONES::obtener_reserva_producto($objeto->ven_id);
                    if ($obj_res_prod) {
                        $estado = strtoupper($obj_res_prod->rprod_estado);
                        $txt_producto = $obj_res_prod->uprod_nombre . "<br/>(RESERVA $estado PRODUCTO)";
                        $color_reserva = ($obj_res_prod->rprod_estado == 'Pendiente') ? "#ff9601": "#019721";
                        $color_producto = "background-color:$color_reserva";
                    }
                }
            }
            
            echo "<td>";
            if ($txt_producto != '' && ($objeto->ven_estado == 'Pendiente' || $objeto->ven_estado == 'Pagado')) {
                echo "<span style='padding:1px 3px ;color:#fff;font-size:9px; $color_producto'>$txt_producto</span>";
            }
            echo "&nbsp;</td>";
            
            echo "<td>";
            echo $this->get_opciones($objeto->ven_id, "", $operaciones, $filtros);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function montopagado($id, $capital = false) {
        if (!$capital) {
            $conec = new ADO();
            //Sumatoria del monto de los registros que estan en estado 'Pagados'
            $sql = "select sum(ind_monto_pagado) as monto from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();
            $monto_totales = $objeto->monto;
            //Sumatoria del monto de los formularios(ind_valor_form) que estan en estado 'Pagados'
            $sql = "select sum(ind_valor_form) as monto_valores_form from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();
            $monto_valores_form = $objeto->monto_valores_form;
            //Sumatoria de los montos Parciales que estan en estado 'Pendiente'
            $sql = "select sum(ind_monto_parcial) as monto_parcial from interno_deuda where ind_tabla='venta' and ind_estado='Pendiente' and ind_tabla_id='$id'";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();
            $monto_parciales = $objeto->monto_parcial;
            return ($monto_totales + $monto_valores_form + $monto_parciales);
        } else {
            $conec = new ADO();
            //Sumatoria del monto de los registros que estan en estado 'Pagados'
            $sql = "select sum(ind_capital_pagado) as monto from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
            $conec->ejecutar($sql);
            $objeto = $conec->get_objeto();
            $monto_totales = $objeto->monto;
            //Sumatoria del monto de los formularios(ind_valor_form) que estan en estado 'Pagados'

            return $monto_totales;
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from tipo_comprobante
				where tco_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['tco_descripcion'] = $objeto->tco_descripcion;
    }

    function datos() {
        if ($_POST) {
            return true;
        } else {
            return false;
        }
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
        ?>
        <script type="text/javascript" src="js/ajax.js"></script>
        <?php
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
        $this->formulario->dibujar_tarea('PERSONA');
//            $this->datos_venta($ci,$im);	
        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
        ?>
        <table align=right border=0><tr>
                <?php if ($this->verificar_permisos('ACCEDER')) { ?>
                    <td><a href="gestor.php?mod=venta&tarea=ACCEDER" title="LISTADO DE VENTAS"><img border="0" width="20" src="images/listado.png"></a></td>
                <?php } ?>
            </tr></table>
        <!--MaskedInput-->
        <style>
            .img-boton{
                margin-left: 2px; float: left;cursor: pointer;
            }
            .img-boton:hover{opacity: 0.7}
            .tablaReporte thead tr th{ padding: 0 10px;}

            .nav-paso{ float: left; }
            .nav-pasos{ width: 100%; margin: 0 auto; }
            .num-paso{
                width: 35px; height: 33px; color: #fff; line-height: 32px; margin-bottom: 8px; border-radius: 17px; font-size: 25px;
            }
            .estado-espera{ background-color: #727272; }
            .estado-activo{ background-color: #3066ff; }
            .estado-success{ background-color: #068400; }
            .box-input-read{background: #ededed; border: 1px solid #bfc4c9; float: left; font-size: 12px; height: 23px; line-height: 22px; padding: 0 4px; width: 140px; font-style: italic;}
            .fwbold{font-weight: bold;}
        </style>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <div class="nav-pasos" >
                    <div id="nav-paso-1" class="nav-paso" style="width: 50%">
                        <div class="num-paso estado-activo">1</div>
                        <div class="estado-activo">&nbsp;</div>
                    </div>
                    <div id="nav-paso-2" class="nav-paso" style="width: 50%">
                        <div class="num-paso estado-espera">2</div>
                        <div class="estado-espera">&nbsp;</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <?php
                if ($tipo == "reserva") {
                    $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id={$_GET[id_res]}");
                    if ($reserva->res_of_id > 0) {
                        ?>
                        <input type="hidden" id="oferta" name="oferta" value="<?php echo $reserva->res_of_id; ?>">
                        <input type="hidden" id="res_ci" name="res_ci" value="<?php echo $reserva->res_ci; ?>">
                        <input type="hidden" id="res_cm" name="res_cm" value="<?php echo $reserva->res_cm; ?>">
                        <?php
                    }
                    ?>
                    <input type="hidden" name="frm_reserva" value="1">
                    <input type="hidden" id="multinivel" name="multinivel" value="<?php echo $_POST[multinivel]; ?>">
                <?php } ?>
                <div class="cont-pasos">
                    <div class="box-paso" id="frm_paso1">
                        <div id="FormSent" style="width:90%;">
                            <div class="Subtitulo">Datos</div>
                            <div id="ContenedorSeleccion" style="position: relative;">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                                    <div id="CajaInput">
                                        <?php
                                        if ($personas <> 0) {
                                            ?>
                                            <input name="res_int_id" id="res_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id'] ?>" size="2">
                                            <input name="ven_int_id" id="ven_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id'] ?>" size="2">
                                            <input name="int_nombre_persona"  readonly="true"
                                                   id="int_nombre_persona"  type="text" class="caja_texto" 
                                                   value="<?php echo trim($_POST['int_nombre_persona']) ?>" 
                                                   size="40" >
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
                                            echo 'No se le asigno ning?na personas, para poder cargar las personas.';
                                        }
                                        ?>
                                        <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                                    </div>
                                    <?php $conversor = new convertir(); ?>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta"><span class="flechas1">*</span>Co-Propietario</div>
                                    <div id="CajaInput">
                                        <?php if ($personas <> 0) { ?>
                                            <input name="ven_co_propietario" id="ven_co_propietario" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_co_propietario'] ?>" size="2">
                                            <input name="int_nombre_copropietario" <?php if ($_GET['change'] == "ok") { ?>readonly="readonly" <?php } ?> id="int_nombre_copropietario"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_copropietario'] ?>" size="40">
                                            <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                                <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                            </a>
                                            <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar&mt=set_valor_copropietario" href="javascript:void(0)">
                                                <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                            </a>
                                            <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_co_propietario();">
                                                <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                            </a>
                                            <?php
                                        } else {
                                            echo 'No se le asigno ning?na personas, para poder cargar las personas.';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                    <div id="CajaInput">
                                        <?php FORMULARIO::cmp_fecha('ven_fecha'); ?>
                                         <!--<input class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php // if (isset($_POST['ven_fecha'])) echo $_POST['ven_fecha']; else echo date("d/m/Y");   ?>" type="text"><label id="lbl_periodo" ></label>-->
                                        <input type="hidden" id="ven_peri_id" value="" >
                                        <input type="hidden" id="tca_cambios" value="" >
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Sucursal</div>
                                    <div id="CajaInput">
                                        <select name="ven_suc_id">
                                            <option value="">-- Seleccione --</option>
                                            <?php
                                            $fun = new FUNCIONES();
                                            $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal where suc_eliminado='no'", $_SESSION[suc_id]);
                                            ?>
                                        </select>
                                        <!--<div class="read-input"><?php // echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal, ad_usuario where usu_suc_id=suc_id and usu_id='$_SESSION[id]'");  ?></div>-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Lugar de venta</div>
                                    <div id="CajaInput">
                                        <?php $paises = FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'"); ?>
                                        <div style="float: left;">
                                            <select name="pais_id" id="pais_id" style="width: 150px;">
                                                <?php foreach ($paises as $pais) { ?>
                                                    <option value="<?php echo $pais->pais_id; ?>"><?php echo $pais->pais_nombre; ?></option>
                                                <?php } ?>
                                            </select>
                                            <select name="est_id" id="est_id" style="width: 150px;">

                                            </select>
                                            <select name="lug_id" id="lug_id" style="width: 150px;">

                                            </select>
                                        </div>
                                        <div id="json_estados" hidden="">
                                            <?php $estados = FUNCIONES::lista_bd_sql("select * from ter_estado where est_eliminado='No'") ?>
                                            <?php
                                            foreach ($estados as $est) {
                                                $est->est_nombre = FUNCIONES::limpiar_cadena($est->est_nombre);
                                            }
                                            echo json_encode($estados);
                                            ?>
                                        </div>
                                        <div id="json_lugares" hidden="">
                                            <?php $lugares = FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_eliminado='No'") ?>
                                            <?php
                                            foreach ($lugares as $lug) {
                                                $lug->lug_nombre = FUNCIONES::limpiar_cadena($lug->lug_nombre);
                                            }
                                            echo json_encode($lugares);
                                            ?>
                                        </div>
                                        <script>
                                            $('#pais_id').change(function() {
                                                var pais_id = $(this).val();
                                                $('#est_id').children().remove();
                                                $('#lug_id').children().remove();
                                                var estados = JSON.parse(trim($('#json_estados').text()));
                                                var options = '';
                                                for (var i = 0; i < estados.length; i++) {
                                                    var est = estados[i];
                                                    if (pais_id === est.est_pais_id) {
                                                        options += '<option value="' + est.est_id + '">' + est.est_nombre + '</option>';
                                                    }
                                                }
                                                $('#est_id').append(options);
                                                $('#est_id').trigger('change');

                                            });
                                            $('#est_id').change(function() {
                                                var est_id = $(this).val();
                                                //                                                       $('#est_id').children().remove();
                                                $('#lug_id').children().remove();
                                                var lugares = JSON.parse(trim($('#json_lugares').text()));
                                                var options = '';
                                                for (var i = 0; i < lugares.length; i++) {
                                                    var lug = lugares[i];
                                                    if (est_id === lug.lug_est_id) {
                                                        options += '<option value="' + lug.lug_id + '">' + lug.lug_nombre + '</option>';
                                                    }
                                                }
                                                $('#lug_id').append(options);

                                            });
                                            $('#pais_id').trigger('change');
                                            $('#est_id').trigger('change');
                                        </script>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta vdo_afil" ><?php echo ($reserva->res_multinivel == 'no') ? 'Vendedor' : 'Patrocinador'; ?></div>
                                    <div id="CajaInput">
                                        <?php if ($tipo != "reserva") { ?>
                                            <select style="width:200px;" name="vendedor" class="caja_texto">
                                                <option value="">Seleccione</option>
                                                <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                                                    from vendedor 
                                                                    inner join interno on (vdo_int_id=int_id) 
                                                                    where vdo_estado='Habilitado' "; ?>
                                                <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                                <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                                    <?php $objeto = $vendedores1->get_objeto(); ?>
                                                    <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                                    <?php $vendedores1->siguiente(); ?>
                                                <?php } ?>
                                            </select>
                                            <?php
                                        } else {
                                            $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' AND  vdo_id=" . $_POST['vendedor'];
                                            $vendedor = FUNCIONES::objeto_bd_sql($sql);
                                            ?>
                                            <select style="width:200px;" name="vendedor" class="caja_texto">
                                                <option value="<?php echo $vendedor->id; ?>"><?php echo $vendedor->nombre; ?></option>
                                            </select>
                                            <?php
                                        }
                                        ?>
                                    </div>

                                </div>

                                <!--
                                <div style="position: absolute; left: 400px; top: 145px;">
                                    <div class="Etiqueta" style="min-width: 30px; width: 60px;">Observaci&oacute;n</div>
                                    <div id="CajaInput">
                                        <textarea name="ven_observacion" id="ven_observacion"></textarea>
                                    </div>
                                </div>
                                -->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizaci&oacute;n</div>
                                    <div id="CajaInput">
                                        <?php
                                        if ($tipo != "reserva") {
                                            if (isset($_GET['lote_venta']) && $_GET['lote_venta'] != '') {
                                                $sql = "select urb_id as id, urb_nombre as nombre, urb_anio_base,urb_anio_inc from lote l, zona z, urbanizacion u 
                        where l.lot_zon_id=z.zon_id and z.zon_urb_id=u.urb_id and l.lot_id='" . $_GET['lote_venta'] . "' and urb_ventas_internas='Si'
						 and urb_eliminado='No';";
                                                $urbanizacion = FUNCIONES::objeto_bd_sql($sql);
                                                ?>
                                                <select style="width:200px;" name="ven_urb_id" data-abase="<?php echo $urbanizacion->urb_anio_base; ?>" data-ainc="<?php echo $urbanizacion->urb_anio_inc; ?>" id="ven_urb_id" class="caja_texto" >
                                                    <option value="<?php echo $urbanizacion->id; ?>"  data-abase="<?php echo $urbanizacion->urb_anio_base; ?>" data-ainc="<?php echo $urbanizacion->urb_anio_inc * 1; ?>" ><?php echo $urbanizacion->nombre; ?></option>
                                                </select>
                                                <?php
                                            } else {
                                                ?>
                                                <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                                    <?php $urbs = FUNCIONES::objetos_bd_sql("select * from urbanizacion where urb_ventas_internas='Si'
												and urb_eliminado='No'"); ?>
                                                    <option value="">Seleccione</option>
                                                    <?php for ($i = 0; $i < $urbs->get_num_registros(); $i++) { ?>
                                                        <?php $urb = $urbs->get_objeto(); ?>
                                                        <option value="<?php echo $urb->urb_id; ?>"  data-abase="<?php echo $urb->urb_anio_base; ?>" data-ainc="<?php echo $urb->urb_anio_inc * 1; ?>" data-interes="<?php echo $urb->urb_interes_anual; ?>" data-cuotas_mlm="<?php echo $urbanizacion->urb_nro_cuotas_multinivel; ?>"><?php echo $urb->urb_nombre; ?></option>
                                                        <?php $urbs->siguiente(); ?>
                                                    <?php } ?>
                                                </select>
                                                <?php
                                            }
                                        } else {
                                            $sql = "select urb_id as id,urb_nombre as nombre, urb_interes_anual,urb_nro_cuotas_multinivel ,urb_anio_base,urb_anio_inc
                                                from urbanizacion 
                                                where urb_ventas_internas='Si' 
												and urb_eliminado='No'
                                                and urb_id=" . $_POST['ven_urb_id'];
                                            $urbanizacion = FUNCIONES::objeto_bd_sql($sql);
                                            ?>
                                            <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" >
                                                <option value="<?php echo $urbanizacion->id; ?>" data-abase="<?php echo $urbanizacion->urb_anio_base; ?>" data-ainc="<?php echo $urbanizacion->urb_anio_inc * 1; ?>" data-interes="<?php echo $urbanizacion->urb_interes_anual; ?>" data-cuotas_mlm="<?php echo $urbanizacion->urb_nro_cuotas_multinivel; ?>" ><?php echo $urbanizacion->nombre; ?></option>
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
                                            if ($tipo != "reserva") {
                                                if (isset($_GET['lote_venta']) && $_GET['lote_venta'] != '') {
                                                    $sql = "select uv_id as id, uv_nombre as nombre from lote l, uv uv 
                                                                    where l.lot_uv_id=uv.uv_id and l.lot_id='" . $_GET['lote_venta'] . "';";
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
                                            } else {
                                                $sql = "select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_POST['ven_urb_id'] . "' and uv_id='" . $_POST['ven_uv_id'] . "'";
                                                $uv = FUNCIONES::objeto_bd_sql($sql);
                                                ?>
                                                <select style="width:200px;" name="ven_uv_id" class="caja_texto">
                                                    <option value="<?php echo $uv->id ?>">Uv Nro: <?php echo $uv->nombre; ?></option>                                                                            
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
                                            if ($tipo != "reserva") {
                                                if (isset($_GET['lote_venta']) && $_GET['lote_venta'] != '') {
                                                    $sql = "select man_id as id,man_nro as nombre  from lote l, manzano m 
                                                                    where l.lot_man_id=m.man_id and l.lot_id='" . $_GET['lote_venta'] . "';";
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
                                            } else {
                                                $sql = "select man_id as id,man_nro as nombre from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "' and man_id=' " . $_POST['ven_man_id'] . "'";
                                                $mz = FUNCIONES::objeto_bd_sql($sql);
                                                ?>
                                                <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
                                                    <option value="<?php echo $mz->id; ?>">Manzano Nro: <?php echo $mz->nombre; ?></option>                                                                            
                                                </select>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                                    <div id="CajaInput">
                                        <div id="lote">
                                            <?php
                                            if ($tipo != "reserva") {
                                                if (isset($_GET['lote_venta']) && $_GET['lote_venta'] != '') {
                                                    $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre 
                                                                    from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_id='" . $_GET['lote_venta'] . "'";
                                                    $mz = FUNCIONES::objeto_bd_sql($sql);
                                                    ?>
                                                    <select style="width:200px;" name="ven_lot_id" id="ven_lot_id" class="caja_texto" >
                                                        <option value="<?php echo $mz->id; ?>">Uv Nro: <?php echo $mz->nombre; ?></option>                                                                            
                                                    </select>
                                                    <script>
                                            cargar_datos($("#ven_lot_id option:selected").val());
                                                    </script>
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
                                            } else {
                                                $sql = "select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo,'-',urb_porc_red,'-',urb_porc_empresa,'-',res_monto_m2,'-',urb_nro_cuotas_multinivel,'-',urb_monto_seguro_cuota) as id,
                            concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre 
                            from lote inner join zona on (lot_zon_id=zon_id) 
                            inner join uv on (lot_uv_id=uv_id) 
                            inner join urbanizacion on (uv_urb_id=urb_id)
                            inner join reserva_terreno on (lot_id=res_lot_id and res_id={$_GET[id_res]})
                            where lot_id='" . $_POST['ven_lot_id'] . "'";
                                                //echo $sql;
                                                $lote = FUNCIONES::objeto_bd_sql($sql);
                                                ?>
                                                <select style="width:200px;" name="ven_lot_id" id="ven_lot_id"class="caja_texto">
                                                    <option value="<?php echo $lote->id; ?>"><?php echo $lote->nombre; ?></option>
                                                </select>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                    $productos = FUNCIONES::lista_bd_sql("select * from urbanizacion_producto where uprod_urb_id='$_POST[ven_urb_id]'");
                                    if (count($productos) > 0) {
                                        ?>
                                        <div id="CajaInput" style="display:block;">
                                            <div class="Etiqueta" ><span class="flechas1">* </span>Productos:</div>
                                            <select id="prod_id" name="prod_id">
                                                
                                                <?php
//                                                $fun->combo("select concat(uprod_id,'-',uprod_precio,'-',uprod_costo) as id,
//                                            concat(uprod_nombre,' - Precio:',' ',uprod_precio) as nombre from 
//                                            urbanizacion_producto 
//                                            where uprod_urb_id='" . $_POST['ven_urb_id'] . "'", "");
                                                ?>
                                                <?php
                                                if ($reserva->res_prod_id > 0) {
                                                    $sql_prod = "select * from 
                                                urbanizacion_producto inner join reserva_terreno on(res_prod_id=uprod_id)
                                                where uprod_id='$reserva->res_prod_id' and res_id='$reserva->res_id'";
                                                    
//                                                    $prod = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto
//                                                        inner join reserva_terreno on(res_prod_id=uprod_id)
//                                                        where uprod_id='$reserva->res_prod_id'");
                                                    
                                                    $prod = FUNCIONES::objeto_bd_sql($sql_prod);
                                                    $txt_prod = $prod->uprod_nombre . " Precio: " . $prod->uprod_precio;
                                                    $data = "$prod->uprod_id-$prod->uprod_precio-$prod->uprod_costo";
//                                                        $selected = ($prod->uprod_id == $reserva->res_prod_id) ? "selected" : "";
                                                    $selected = "selected";
                                                        ?>
                                                    <option <?php echo $selected;?> value="<?php echo $data?>"><?php echo $txt_prod;?></option>
                                                        <?php
                                                }else{
                                                ?>
                                                    <option value="">Seleccione</option>
                                                    <?php                                                    
//                                                    foreach ($productos as $prod) {
//                                                        $txt_prod = $prod->uprod_nombre . " Precio: " . $prod->uprod_precio;
//                                                        $data = "$prod->uprod_id-$prod->uprod_precio-$prod->uprod_costo";
//                                                        $selected = ($prod->uprod_id == $reserva->res_prod_id) ? "selected" : "";
                                                        ?>
                                                    <!--<option <?php // echo $selected;?> data-valores="<?php // echo $data;?>" value="<?php // echo $prod->uprod_id;?>"><?php // echo $txt_prod;?></option>-->
                                                        <?php
//                                                    }
                                                $fun->combo("select concat(uprod_id,'-',uprod_precio,'-',uprod_costo) as id,
                                                concat(uprod_nombre,' - Precio:',' ',uprod_precio) as nombre from 
                                                urbanizacion_producto 
                                                where uprod_urb_id='" . $_POST['ven_urb_id'] . "'", "");
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <?php
                                        if ($reserva->res_prod_id > 0) {
                                            ?>
                                    <script>
//                                        establecer_campos_producto(<?php echo $reserva->res_descuento_producto;?>);
                                    </script>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div id="ContenedorDiv" >
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                                    <div id="CajaInput">
                                        <select style="width:100px;" name="ven_moneda" class="caja_texto" id="ven_moneda" onchange="javascript:f_moneda();" >
                                            <option value="1" <?php if ($_POST['ven_moneda'] == '1') echo 'selected="selected"'; ?>>Boliviano</option>
                                            <option value="2" <?php if ($_POST['ven_moneda'] == '2') echo 'selected="selected"'; ?>>Dolar</option>
                                        </select>
                                    </div>                                                            
                                </div>
                                <div id="ContenedorDiv" >
                                    <input type="button" id="btn_siguiente" class="boton" value="Siguiente >>" onclick="frm_paso(2);">
                                </div>
                            </div>
                        </div>

                        <style>
                            .del_fespecial{
                                cursor:pointer;
                            }
                            .del_fespecial_h{
                                display: none;
                            }
                            .fsitalic{ font-style: italic;}
                        </style>
                    </div>
                    <div class="box-paso" id="frm_paso2">
                        <div id="FormSent" style="width:90%;">
                            <div class="Subtitulo">Datos de Pago</div>
                            <div id="ContenedorSeleccion" style="position: relative;">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fsitalic" >Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input fsitalic" id="txt_cliente">&nbsp;</div>
                                    </div>
                                    <div class="Etiqueta" >Fecha</div>
                                    <div id="CajaInput">
                                        <div class="read-input fsitalic" id="txt_fecha">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Vendedor</div>
                                    <div id="CajaInput">
                                        <div class="read-input fsitalic" id="txt_vendedor">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Terreno</div>
                                    <div id="CajaInput">
                                        <div class="read-input fsitalic" id="txt_terreno">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Moneda</div>
                                    <div id="CajaInput">
                                        <div class="read-input fsitalic" id="txt_moneda">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Generar Comision</div>
                                    <div id="CajaInput">
                                        <select id="ven_comision_gen" name="ven_comision_gen" style="min-width: 100px;">
                                            <option value="1">Si</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <?php
                                    if ($_POST[multinivel] == 'si') {
                                        ?>
                                        <div class="Etiqueta" >Meses para pagar BRA</div>
                                        <div id="CajaInput">
                                            <input type="text" name="meses_bra" id="meses_bra" size="8" value="0" >
                                        </div>
                                        <?php
                                    }
                                    ?>

                                </div>

                                <div class="Subtitulo">Info. Lote</div>
                                <div id="ContenedorSeleccion">
                                    <div id="ContenedorDiv">
                                        <div id="seccion_sup_valor">
                                            <div class="Etiqueta" >Superficie</div>
                                            <div id="CajaInput">
                                                <input readonly="true" type="text" name="superficie" id="superficie" size="8" value="" >
                                            </div>
                                            <div id="CajaInput">
                                                <span id="simb_moneda_vm2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Valor m2&nbsp;&nbsp;&nbsp;</span>
                                                <input type="text" name="valor" id="valor" size="8" value=""   onKeyUp="javascript:calcular_valor_terreno();" readonly="true">
                                                <input type="hidden" name="valorhidden" id="valorhidden" data-moneda="" value="" >
                                                <input type="hidden" id="urb_anio_base" value="" >
                                                <input type="hidden" id="urb_anio_inc" value="" >

                                            </div>

                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" id="simb_moneda_vt"><span class="flechas1">*</span>Valor del Terreno</div>
                                        <div id="CajaInput">
                                            <input readonly="true" type="text" name="valor_terreno" id="valor_terreno" size="8" value="">
                                        </div>
                                        <div id="CajaInput">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Descuento&nbsp;&nbsp;&nbsp;
                                            <input type="text" name="descuento" id="descuento" size="8" value="0" onKeyUp="javascript:calcular_monto();" autocomplete="off"><span id="simb_moneda_descuento"></span>
                                        </div>							  
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Monto a Pagar</div>
                                        <div id="CajaInput">
                                            <input readonly="true" type="text" name="monto" id="monto" size="8" value="">
                                        </div>
                                        <?php
                                        //if ($_POST['ven_anticipo']) {
                                        if ($tipo == "reserva") {
                                            $sql = "select respag_monto as monto, respag_moneda as moneda from reserva_pago where respag_estado!='Anulado' and respag_res_id='" . $_GET['id_res'] . "';";
                                            $anticipos = FUNCIONES::objetos_bd_sql($sql);
                                            $anticipos_json = '[';
                                            for ($i = 0; $i < $anticipos->get_num_registros(); $i++) {
                                                $anticipo = $anticipos->get_objeto();
                                                if ($i > 0)
                                                    $anticipos_json.=',';
                                                $anticipos_json.='{"monto":"' . $anticipo->monto . '", "moneda":"' . $anticipo->moneda . '"}';
                                                $anticipos->siguiente();
                                            }
                                            $anticipos_json.=']';
                                            ?>
                                            <div id="CajaInput">
                                                &nbsp;&nbsp;&nbsp;
                                                Reserva&nbsp;<input type="text" name="ven_anticipo" id="ven_anticipo" size="8" value="<?php echo $_POST['ven_anticipo']; ?>" readonly="readonly"><span id="simb_moneda_anticipo"></span>
                                                <input type="hidden" name="id_res" id="id_res" size="5" value="<?php echo $_POST['id_res']; ?>" readonly="readonly">
                                                <input type="hidden" name="res_anticipos" id="res_anticipos" size="5" value='<?php echo $anticipos_json; ?>' >

                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Intercambio</div>
                                        <div id="CajaInput">
                                            <input type="text" name="ven_monto_intercambio" id="ven_monto_intercambio" size="8" value="" onkeyup="javascript:calcular_intercambio();" autocomplete="off" >
                                        </div>
                                    </div>

                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" style="min-width: 30px; width: 60px;">Observaci&oacute;n</div>
                                        <div id="CajaInput">
                                            <textarea name="ven_observacion" id="ven_observacion"></textarea>
                                        </div>
                                    </div>    

                                    <div id="ContenedorSeleccion" style="width: 100%; padding: 5px 0; margin-bottom: 10px; display: none;" class="cont_det_intercambio" >
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >&nbsp;</div>
                                            <div id="CajaInput">
                                                <input type="text" name="det_inter_monto" id="det_inter_monto" size="8" value="" autocomplete="off" >
                                            </div>
                                            <div id="CajaInput">
                                                <select name="det_inter_id" id="det_inter_id" >
                                                    <option value="">-- Seleccione --</option>
                                                    <?php
                                                    $fun = new FUNCIONES();
                                                    $fun->combo("select inter_id as id, inter_nombre as nombre from ter_intercambio where inter_eliminado='no'", '')
                                                    ?>
                                                </select>
                                            </div>
                                            <div id="CajaInput" style="margin-left: 5px; cursor: pointer; ">
                                                <img src="images/btn_add_detalle.png" onclick="add_detalle_intercambio();">
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >&nbsp;</div>
                                            <div id="CajaInput">
                                                <table id="tab_intercambios" class="tablaLista" cellspacing="0" cellpadding="0">
                                                    <thead>
                                                        <tr>
                                                            <th>Monto</th>
                                                            <th>Tipo</th>
                                                            <th class="tOpciones"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td style="padding: 0 3px;font-size: 11px">
                                                                <span id="txt_total_inter"class="fwbold"></span>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td>&nbsp;</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="Subtitulo div_producto">Info. Producto</div>
                                <div id="ContenedorSeleccion" class="div_producto">
                                    <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                        <div class="Etiqueta" >Precio Producto</div>
                                        <div id="CajaInput">
                                            <input readonly="true" type="text" name="uprod_precio" id="uprod_precio" size="8" value="" >
                                        </div>
                                    </div>

                                    <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                        <div class="Etiqueta" >Descuento Producto</div>
                                        <div id="CajaInput">
                                            <input type="text" name="uprod_descuento" id="uprod_descuento" size="8" value="" >
                                        </div>
                                    </div>

                                    <div id="ContenedorDiv" style="display: none;" class="div_producto">
                                        <div class="Etiqueta" >Monto Producto</div>
                                        <div id="CajaInput">
                                            <input readonly type="text" name="uprod_monto" id="uprod_monto" size="8" value="" >
                                        </div>
                                    </div>

                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" style="min-width: 30px; width: 60px;">Observaci&oacute;n</div>
                                        <div id="CajaInput">
                                            <textarea name="ven_observacion_producto" id="ven_observacion_producto"></textarea>
                                        </div>
                                    </div> 

                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Saldo Efectivo</div>
                                    <div id="CajaInput">
                                        <input readonly="true" type="text" name="ven_monto_efectivo" id="ven_monto_efectivo" size="8" value="" >
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
                                    <div class="read-input" id="txt_ven_tipo">&nbsp;</div>
                                    <input type="hidden" id="ven_tipo" name="ven_tipo" value="">
                                </div>
                                <div style="clear: both;"></div>
                                <div id="plan_credito" style="display: none; color:#3a3a3a">
                                    <div class="Subtitulo">Datos Credito</div>
                                    <div id="ContenedorSeleccion">
                                        <div id="ContenedorDiv">
                                            <div id="CajaInput" name="divComenzarEn" >
                                                <span style="float: left; margin-top: 2px;">Interes Anual: &nbsp;</span>
                                                <input type="text" name="interes_anual" id="interes_anual" size="8" value="8" >
                                            </div>

                                            <div id="CajaInput" name="divCuotaInicial" style="display: none;">
                                                <span style="float: left; margin-top: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;Cuota Inicial: &nbsp;</span>
                                                <input type="text" name="cuota_inicial" id="cuota_inicial" size="8" value=""  onKeyPress="return ValidarNumero(event);">
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div id="CajaInput">
                                                <span style="float: left; margin-top: 2px;">Definir Plan de Pagos por: &nbsp;</span>
                                                <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo">
                                                    <option value="mp">Meses Plazo</option>
                                                    <option value="cm">Cuota Mensual</option>
                                                    <!--<option value="manual">Manual</option>-->
                                                </select>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv"  >
                                            <div id="CajaInput">
                                                <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                                <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                            </div>
                                            <div id="CajaInput" name="divCuotaMensual" >
                                                <select  id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                                    <option value="dcuota">Monto Cuota</option>
                                                    <option value="dcapital">Monto Capital</option>
                                                </select>
                                            </div>
                                            <div id="CajaInput" name="divCuotaMensual" >
                                                <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                                <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                            </div>
                                            <div id="CajaInput">
                                                <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha Pri. Cuota: </span>
                                                <input class="caja_texto" name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo FUNCIONES::get_fecha_latina(FUNCIONES::sumar_dias(30, date("Y-m-d"))); ?>" type="text" autocomplete="off">
                                                <script>
                                            $("#fecha_pri_cuota").mask("99/99/9999");
                                                </script>
                                            </div>
                                            <div id="CajaInput" name="divCuotaMensual" >
                                                <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                                <select id="ven_rango" name="ven_rango">
                                                    <option value="1">Mensual</option>
                                                    <option value="2">Bimestral</option>
                                                    <option value="3">Trimestral</option>                                                            
                                                    <option value="4">Cuatrimestral</option>
                                                    <option value="6">Semestral</option>
                                                </select>
                                            </div>
                                            <div id="CajaInput" name="divCuotaMensual" >
                                                <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                                <select id="ven_frecuencia" name="ven_frecuencia">
                                                    <option value="30_dias">Cada 30 dias</option>
                                                    <option value="dia_mes">Mantener el dia</option>
                                                </select>
                                            </div>
                                            <div id="CajaInput">
                                                <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                                            </div>
                                            <div id="CajaInput">
                                                <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                                            </div>
                                        </div>

                                        <div style="clear: both"></div>
                                        <div class="ContenedorDiv" id="plan_manual_efectivo">
                                            <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Nro. Cuota</th>
                                                        <th>Fecha de Pago</th>                                                            
                                                        <th>Mondeda</th>
                                                        <th>Interes</th>
                                                        <th>Capital</th>
                                                        <th>Monto a Pagar</th>
                                                        <th>Saldo</th>
                                                        <th></th>
                                                    </tr>							
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>	
                                                    <tr>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>
                                                            <input type="hidden" id="c_total_efectivo" value="0">
                                                            <input type="hidden" id="pag_total_efectivo" value="0">
                                                        </td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv" >
                                    <br>
                                    <input type="button" id="btn_anterior" class="boton" value="<< Anterior" onclick="frm_paso(1);">
                                    <input type="button" id="btn_guardar" class="boton" value="Guardar" onclick="enviar_formulario();">
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                                            function frm_paso(nro_paso) {
                                                if (nro_paso === 1) {
                                                    habilitar_formulario(1);
                                                } else if (nro_paso === 2) {
                                                    var suc_id = $('#ven_suc_id option:selected').val();
                                                    var fecha = $('#ven_fecha').val();
                                                    var interno = document.frm_sentencia.ven_int_id.value;
                                                    var moneda = $('#ven_moneda option:selected').val();//document.frm_sentencia.ven_moneda.value;
                                                    var urbanizacion = document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                                                    var manzano = document.frm_sentencia.ven_man_id.options[document.frm_sentencia.ven_man_id.selectedIndex].value;
                                                    var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                                                    console.log(fecha + ' - ' + interno + ' - ' + ' - ' + moneda + ' - ' + urbanizacion + ' - ' + manzano + ' - ' + lote);
                                                    if (fecha !== '' && interno !== '' && moneda !== '' && urbanizacion !== '' && manzano !== '' && lote !== '' && suc_id !== '') {
                                                        mostrar_ajax_load();
                                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                            $.post('ajax.php', {tarea: 'revisar_anticipos', res_id: $('#id_res').val(), fecha: fecha}, function(resp) {
                                                                ocultar_ajax_load();
                                                                var _resp = JSON.parse(resp);
                                                                if (_resp.type === 'success') {
                                                                    var dato = JSON.parse(respuesta);
                                                                    if (dato.response === "ok") {
                                                                        habilitar_formulario(2);
                                                                    } else if (dato.response === "error") {
                                                                        $.prompt(dato.mensaje);
                                                                        return false;
                                                                    }
                                                                } else {
                                                                    // $.prompt("El monto del anticipo no cumple con lo establecido en el proyecto");
                                                                    $.prompt("El monto del anticipo(USD. " + _resp.anticipos + ") no cumple con lo establecido(USD. " + _resp.monto_referencial + ").");
                                                                    return false;
                                                                }
                                                            });

                                                        });

                                                    } else {
                                                        $.prompt('Ingrese Correctamente los datos de la Venta');
                                                    }
                                                    return false;
                                                }
                                            }
                                            var _FECHA_PAGO = '';
                                            var _FECHA_VALOR = '';
                                            function habilitar_formulario(form) {
                                                if (form === 1) {
                                                    $('.box-paso').hide();
                                                    $('#frm_paso1').show();
                                                    $('#nav-paso-2 .estado-activo').each(function() {
                                                        $(this).removeClass('estado-activo');
                                                        $(this).addClass('estado-espera');
                                                    });
                                                    $('#nav-paso-1 .estado-success').each(function() {
                                                        $(this).removeClass('estado-success');
                                                        $(this).addClass('estado-activo');
                                                    });
                                                } else if (form === 2) {
                                                    _VEN_FECHA = $('#ven_fecha').val();

                                                    $('#txt_fecha_pago').text(_FECHA_PAGO);
                                                    $('#txt_fecha_valor').text(_FECHA_VALOR);
                                                    $('.box-paso').hide();
                                                    $('#frm_paso2').show();
                                                    $('#nav-paso-2 .estado-espera').each(function() {
                                                        $(this).removeClass('estado-espera');
                                                        $(this).addClass('estado-activo');
                                                    });
                                                    $('#nav-paso-1 .estado-activo').each(function() {
                                                        $(this).removeClass('estado-activo');
                                                        $(this).addClass('estado-success');
                                                    });
                                                    $('#descuento').val(0);
                                                    $('#cuota_inicial').val(0);
                                                    $('#meses_plazo').val('');
                                                    $('#cuota_mensual').val('');
                                                    $('#tab_plan_efectivo tbody tr').remove();
                                                    $('#c_total_efectivo').val('');
                                                    $('#pag_total_efectivo').val('');
                                                    $('#fecha_pri_cuota').val(fecha_latina(sumar_dias(fecha_mysql(_VEN_FECHA), 30)));

                                                    var txt_cliente = $('#int_nombre_persona').val();
                                                    var txt_fecha = $('#ven_fecha').val();
                                                    var txt_vendedor = $('select[name="vendedor"] option:selected').text();
                                                    var txt_terreno = $('#ven_urb_id option:selected').text() + ' - ' + $('select[name="ven_man_id"] option:selected').text() + ' - ' + $('#ven_lot_id option:selected').text();
                                                    var txt_moneda = $('#ven_moneda option:selected').text();
                                                    $('#txt_cliente').text(txt_cliente);
                                                    $('#txt_fecha').text(txt_fecha);
                                                    $('#txt_vendedor').text(txt_vendedor);
                                                    $('#txt_terreno').text(txt_terreno);
                                                    $('#txt_moneda').text(txt_moneda);
                                                    $('#urb_anio_base').val($('#ven_urb_id option:selected').attr('data-abase'));
                                                    $('#urb_anio_inc').val($('#ven_urb_id option:selected').attr('data-ainc'));
                                                    establecer_campos_producto(<?php echo $reserva->res_descuento_producto;?>);
                                                    calcular_monto();
                                                    establecer_campos_oferta();
                                                }
                                            }

                                            function establecer_campos_oferta() {
                                                if ($('#oferta').length > 0) {
                                                    //                                                            alert($('#oferta').val());
                                                    $('#cuota_mensual').val($('#res_cm').val() * 1);
                                                    $('#def_plan_efectivo option[value="cm"]').attr('selected', true);
                                                    $('#def_plan_efectivo').trigger('change');
                                                } else {
                                                    //                                                            alert('nada choco');
                                                }
                                            }

                                            function establecer_campos_producto(descuento) {
                                                var prod_id = $('#prod_id option:selected').val();

                                                if (prod_id != '') {
                                                    var datos_prod = prod_id.split('-');
//                                                    var datos_prod = $('#prod_id option:selected').attr('data-valores').split('-');

                                                    $('.div_producto').css('display', 'block');
                                                    $('#uprod_precio').val(datos_prod[1]);
                                                    mask_decimal('#uprod_descuento', null);
                                                    if (descuento) {
                                                        $('#uprod_descuento').val(descuento);
                                                    }
                                                } else {
                                                    console.log('no selecciono producto');
                                                    $('#uprod_precio').val(0);
                                                    $('#uprod_descuento').val(0);
                                                    $('#uprod_monto').val(0);
                                                    $('.div_producto').css('display', 'none');
                                                }

                                                $('#uprod_descuento').keyup(function() {
                                                    console.log('keyup de uprod_descuento...precio_prod:' + datos_prod[1] + ' - desc_prod:' + $(this).val());
                                                    calcular_monto_efectivo();
                                                });
                                            }

                                            habilitar_formulario(1);
                                            mask_decimal('#det_inter_monto', null);
                                            function add_detalle_intercambio() {
                                                var monto = $('#det_inter_monto').val() * 1;
                                                var id = $('#det_inter_id option:selected').val() * 1;
                                                var txt_intercambio = $('#det_inter_id option:selected').text();
                                                var monto_inter = $('#ven_monto_intercambio').val() * 1;
                                                console.log(id + ' - ' + monto);
                                                var _total = sumar_intercambio();
                                                var _monto = monto + _total;
                                                if (id > 0 && monto > 0 && _monto <= monto_inter) {
                                                    var fila = '';
                                                    fila += '<tr>';
                                                    fila += '      <td>';
                                                    fila += '         <input type="hidden" name="intercambio_ids[]" class="intercambio_ids" value="' + id + '">';
                                                    fila += '         <input type="hidden" name="intercambio_montos[]" class="intercambio_montos" value="' + monto + '">';
                                                    fila += '         ' + monto;
                                                    fila += '      </td>';
                                                    fila += '      <td>';
                                                    fila += '         ' + txt_intercambio;
                                                    fila += '      </td>';
                                                    fila += '      <td>';
                                                    fila += '         <img src="images/retener.png" class="del_inter cpointer" onclick="delete_intercambio(this);">';
                                                    fila += '      </td>';
                                                    fila += '</tr>';
                                                    $('#tab_intercambios tbody').append(fila);
                                                    //                                                            var total=sumar_intercambio();
                                                    $('#txt_total_inter').text(_monto);

                                                    $('#det_inter_monto').val('');
                                                    $('#det_inter_id option[value=""]').attr('selected', 'true');

                                                } else {
                                                    $.prompt('Ingrese Correctamente el Monto de Intercambio');
                                                    return false;
                                                }
                                            }
                                            function delete_intercambio(obj) {
                                                $(obj).parent().parent().remove();
                                                var _monto = sumar_intercambio();
                                                $('#txt_total_inter').text(_monto);
                                            }
                                            function sumar_intercambio() {
                                                var montos = $('.intercambio_montos');
                                                var lon = $(montos).size();
                                                var sum = 0;
                                                for (var i = 0; i < lon; i++) {
                                                    sum += $(montos[i]).val() * 1;
                                                }
                                                return sum.toFixed(2) * 1;
                                            }

                                            var mon_select = 0;
                                            mask_decimal('#interes_anual', null);
                                            mask_decimal('#meses_bra', null);

                                            function cargar_datos(valor) {
                                                //                                    alert(valor);
                                                console.log('cargar_datos => ' + valor);

                                                var valor = $('#ven_lot_id option:selected').val();
                                                var cambios = $("#tca_cambios").val();
                                                if (cambios === "") {
                                                    return false;
                                                }
                                                if (typeof(valor) === "undefined") {
                                                    return false;
                                                }
                                                var datos = valor;
                                                var val = datos.split('-');

                                                var valor_terreno = (parseFloat(val[1]) * parseFloat(val[2])).toFixed(2);

                                                var m2 = val[2];
                                                if ($('#multinivel').length > 0) {
        //                                            m2 = val[8];
                                                    m2 = val[2];

                                                    //                                            alert('oh yeah!');
                                                    //                                            var datos_lote = $('#ven_lot_id option:selected').val().split('-');
                                                    //                                            console.log(val[6] + '***' + val[7]);
                                                    //                                            
                                                    //                                            var porc_red = val[6] * 1 / 100;
                                                    //                                            var porc_empresa = val[7] * 1 / 100;
                                                    //                                            console.log(valor_terreno + '***1');
                                                    //                                            valor_terreno = valor_terreno + (valor_terreno*porc_red);
                                                    //                                            console.log(valor_terreno + '***2');
                                                    //                                            valor_terreno = valor_terreno + (valor_terreno*porc_empresa);
                                                    //                                            $('#valor_terreno').val(vt);
                                                    valor_terreno = (parseFloat(val[1]) * parseFloat(m2)).toFixed(2);
                                                    console.log(valor_terreno + '***3');

                                                } else {
                                                    //                                            alert('shit!');                                            
                                                }

                                                document.frm_sentencia.valor_terreno.value = valor_terreno;
                                                document.frm_sentencia.superficie.value = val[1];
                                                document.frm_sentencia.valor.value = m2 * 1;
                                                document.frm_sentencia.valorhidden.value = m2;
                                                document.frm_sentencia.ven_moneda.value = val[4];
                                                mon_select = val[4];
                                                $("#valorhidden").attr("data-moneda", val[4]);
                                                document.frm_sentencia.ven_tipo.value = "Contado";
                                                $('#ven_tipo').trigger('change');
                                                //                                    calcular_cuota();
                                                calcular_monto();
                                            }

                                            function calcular_descuento() {
                                                var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                                                var porc_desc = parseFloat(document.frm_sentencia.porc_descuento.value);
                                                var desc = vt * (porc_desc / 100);

                                                //var td=(vt*des)/100;
                                                document.frm_sentencia.descuento.value = desc.toFixed(2);
                                                calcular_monto();
                                            }
                                            function calcular_monto() {
        //                                        cargar_datos($("#ven_lot_id option:selected").val(), val);
        //                                        var producto = $('#prod_id option:selected').val();
        //                                        var datos_prod = producto.split('-');
        //                                        var inc_m2 = 0;
        //                                        var inc_precio = 0;
        //                                        if (datos_prod[0]*1 > 0) {
        //                                            var val_m2 = $('#valor').val()*1;
        //                                            var valor_terreno = $('#valor_terreno').val()*1;
        //                                            inc_m2 = parseFloat(datos_prod[1]) / parseFloat($('#superficie').val());
        //                                            inc_precio = datos_prod[1]*1;
        //                                            val_m2 += inc_m2;
        //                                            valor_terreno += inc_precio;
        //                                            $('#valor').val(val_m2.toFixed(2));
        ////                                            $('#valorhidden').val(val_m2.toFixed(2));
        //                                            $('#valorhidden').val(val_m2);
        //                                            $('#valor_terreno').val(valor_terreno.toFixed(2));
        //                                            console.error('entrando a obtener los incrementos: inc_m2 => ' + inc_m2 + ' - inc_precio => ' + inc_precio);
        //                                        }

                                                var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                                                var des = document.frm_sentencia.descuento.value;
                                                if (des === "") {
                                                    des = 0;
                                                }
                                                //var td=(vt*des)/100;
                                                document.frm_sentencia.monto.value = (vt - des).toFixed(2);

                                                calcular_monto_efectivo();
                                            }

                                            var _ANT_INTERCAMBIO = $('#ven_monto_intercambio').val() * 1;
                                            function calcular_intercambio() {
                                                var intercambio = $('#ven_monto_intercambio').val() * 1;
                                                if (_ANT_INTERCAMBIO !== intercambio) {
                                                    if (intercambio > 0) {
                                                        $('.cont_det_intercambio').show();
                                                        $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                                        $('#txt_total_inter').text('');
                                                        //                                                            $('#tipo_intercambio').show();
                                                    } else {
                                                        $('.cont_det_intercambio').hide();
                                                        $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                                        $('#txt_total_inter').text('');
                                                        //                                                            $('#tipo_intercambio').hide();
                                                    }
                                                    _ANT_INTERCAMBIO = intercambio;
                                                    calcular_monto_efectivo();
                                                }
                                            }
                                            function calcular_monto_efectivo() {
                                                var monto = $('#monto').val() * 1;
                                                var anticipo = 0;
                                                var precio_prod = 0;
                                                var descuento_prod = 0;

                                                if ($('#ven_anticipo').length) {
                                                    anticipo = $('#ven_anticipo').val() * 1;
                                                }

                                                if ($('#uprod_precio').length) {
                                                    precio_prod = $('#uprod_precio').val() * 1;
                                                }

                                                if ($('#uprod_descuento').length) {
                                                    descuento_prod = $('#uprod_descuento').val() * 1;
                                                }

                                                var intercambio = $('#ven_monto_intercambio').val() * 1;
                                                //                                                        if(intercambio>0){
                                                //                                                            $('.cont_det_intercambio').show();
                                                //                                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                                ////                                                            $('#tipo_intercambio').show();
                                                //                                                        }else{
                                                //                                                            $('.cont_det_intercambio').hide();
                                                //                                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                                ////                                                            $('#tipo_intercambio').hide();
                                                //                                                        }

                                                console.log(monto + '-' + anticipo + '-' + intercambio);
        //                                        var efectivo = monto - anticipo - intercambio;
                                                var efectivo = monto - (anticipo + intercambio) + (precio_prod - descuento_prod);
                                                var monto_prod = precio_prod - descuento_prod;
                                                $('#ven_monto_efectivo').val(efectivo.toFixed(2));
                                                $('#uprod_monto').val(monto_prod.toFixed(2));
                                                $('#tab_plan_efectivo tbody tr').remove();
                                                $('#c_total_efectivo').val('');
                                                $('#pag_total_efectivo').val('');
                                                if (efectivo > 0) {//credigo
                                                    var interes = $('#ven_urb_id option:selected').attr('data-interes');
                                                    var meses_bra = $('#ven_urb_id option:selected').attr('data-cuotas_mlm');
                                                    $('#interes_anual').val(interes * 1);

                                                    if ($('#meses_bra').length > 0) {

                                                        $('#meses_bra').val(meses_bra);
                                                    }
                                                    var sup = $('#superficie').val() * 1;
                                                    var vm2 = $('#valor').val() * 1;
                                                    //                                                        var ci=(sup*vm2)*0.1;
                                                    var ci = 0;
                                                    $('#cuota_inicial').val(ci.toFixed(2));
                                                    $("#plan_credito").show();
                                                    var tipo_pag = $('#ven_tipo_pago option:selected').val();
                                                    if (tipo_pag === 'Intercambio') {
                                                        $("#plan_credito_inter").show();
                                                    } else {
                                                        $("#plan_credito_inter").hide();
                                                    }
                                                    $('#txt_ven_tipo').text('Credito');
                                                    $('#ven_tipo').val('Credito');

                                                } else {// contado
                                                    document.frm_sentencia.cuota_inicial.value = "";
                                                    document.frm_sentencia.meses_plazo.value = "";
                                                    document.frm_sentencia.cuota_mensual.value = "";
                                                    $("#plan_credito").hide();
                                                    $("#plan_credito_inter").hide();
                                                    $('#tprueba tbody').remove();

                                                    $('#txt_ven_tipo').text('Contado');
                                                    $('#ven_tipo').val('Contado');
                                                }
                                            }

                                            function calcular_valor_terreno() {
                                                var sup = parseFloat(document.frm_sentencia.superficie.value);
                                                var val = document.frm_sentencia.valor.value;
                                                if (val === "") {
                                                    val = 0;
                                                }
                                                var vt = sup * val;
                                                document.frm_sentencia.valor_terreno.value = vt.toFixed(2);
                                                //					calcular_cuota();
                                                console.log('calcular_valor_terreno => val:' + val);
                                                calcular_monto(val);

                                            }

                                            function cargar_uv(id) {
                                                var valores = "tarea=uv&urb=" + id;
                                                ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
                                            }

                                            function cargar_manzano(id, uv) {
                                                //cargar_lote(0);					
                                                var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;
                                                ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
                                            }

                                            function cargar_lote(id, uv) {
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

                                            function ver_plan_pago() {
                                                var tipo_venta = document.frm_sentencia.ven_tipo.value;
                                                if (tipo_venta === 'Credito') {
                                                    var saldo_financiar = 0;
                                                    var ncuotas = 0;
                                                    var fecha_pri_cuota = 0;
                                                    var monto_cuota = 0;


                                                    var def = $('#def_plan_efectivo option:selected').val();
                                                    if (def === 'mp') {
                                                        ncuotas = $('#meses_plazo').val();
                                                        monto_cuota = '';
                                                    } else if (def === 'cm') {
                                                        ncuotas = '';
                                                        monto_cuota = $('#cuota_mensual').val();
                                                    }
                                                    var monto_efectivo = $('#ven_monto_efectivo').val();
                                                    //                                                            var anticipo=0;
                                                    //                                                            if($('#ven_anticipo').length){
                                                    //                                                                anticipo=$('#ven_anticipo').val();
                                                    //                                                            }                                            
                                                    var cuota_inicial = $('#cuota_inicial').val();
                                                    saldo_financiar = monto_efectivo - cuota_inicial;
                                                    fecha_pri_cuota = $('#fecha_pri_cuota').val();
                                                    var rango = $('#ven_rango option:selected').val();
                                                    var frec = $('#ven_frecuencia option:selected').val();
                                                    //                                                if(_MODALIDAD==='interes'){
                                                    var interes = $('#interes_anual').val();
                                                    //                                                }
                                                    var fecha_pri_mysql = fecha_mysql(fecha_pri_cuota);
                                                    var fecha_ini_mysql = fecha_mysql($('#ven_fecha').val());
                                                    if (fecha_ini_mysql > fecha_pri_mysql) {
                                                        $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                                        return false;
                                                    }
                                                    if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && monto_efectivo > +0 && fecha_pri_cuota !== '') {
                                                        var moneda = $('#ven_moneda option:selected').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                                                        var par = {};
                                                        par.tarea = 'plan_pagos';
                                                        par.saldo_financiar = saldo_financiar;
                                                        par.monto_total = saldo_financiar;
                                                        par.meses_plazo = ncuotas;
                                                        par.ven_moneda = moneda;
                                                        par.fecha_inicio = $('#ven_fecha').val();
                                                        par.fecha_pri_cuota = fecha_pri_cuota;
                                                        par.cuota_mensual = monto_cuota;
                                                        par.interes = interes;
                                                        par.rango = rango;
                                                        par.frecuencia = frec;
                                                        par.urbanizacion = $('#ven_urb_id option:selected').val();
                                                        par.multinivel = ($('#multinivel').length > 0) ? $('#multinivel').val() : 'no';
                                                        $.post('ajax.php', par, function(resp) {
                                                            abrir_popup(resp);
                                                        });

                                                    } else {
                                                        $('#tprueba tbody').remove();
                                                        $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                                                    }
                                                } else {
                                                    $.prompt('La venta es al contado, no necesita generar un plan de pagos.', {opacity: 0.8});
                                                }
                                            }
                                            var popup = null;
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

                                            function obtener_tipo_cambio(moneda) {
                                                var tcambios = JSON.parse($("#tca_cambios").val());
                                                for (var i = 0; i < tcambios.length; i++) {
                                                    if (tcambios[i].id == moneda) {
                                                        return tcambios[i].id;
                                                    }
                                                }
                                                return 1;
                                            }

                                            function actualizar_total(row, columna)
                                            {
                                                var dato = $(row).parent().parent().parent().children().eq(columna).children().eq(0).attr('value');
                                                var datos = dato.split('?');
                                                var tpbs = parseFloat(document.frm_sentencia.tbs.value);
                                                var tpsus = parseFloat(document.frm_sentencia.tsus.value);
                                                document.frm_sentencia.tbs.value = parseFloat(roundNumber((tpbs - datos[0]), 2));
                                                document.frm_sentencia.tsus.value = parseFloat(roundNumber((tpsus - datos[1]), 2));

                                            }

                                            function remove(row) {
                                                var cant = $(row).parent().parent().parent().children().length;
                                                if (cant > 1)
                                                    $(row).parent().parent().parent().remove();
                                            }

                                            function addTableRow(id, valor) {
                                                $(id).append(valor);
                                            }

                                            function enviar_formulario() {
                                                //                                    console.log('aa');
                                                var fecha = document.getElementById('ven_fecha').value;
                                                var interno = document.frm_sentencia.ven_int_id.value;
                                                var tipo = document.frm_sentencia.ven_tipo.value;
                                                //var moneda=document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                                                var moneda = document.frm_sentencia.ven_moneda.value;
                                                var urbanizacion = document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                                                var manzano = document.frm_sentencia.ven_man_id.options[document.frm_sentencia.ven_man_id.selectedIndex].value;
                                                var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                                                var cuota_mensual = document.frm_sentencia.cuota_mensual.value;

                                                var valor_terreno = parseFloat(document.frm_sentencia.valor_terreno.value);
                                                var descuento = parseFloat(document.frm_sentencia.descuento.value);

                                                if (interno !== '' && tipo !== '' && moneda !== '' && urbanizacion !== '' && manzano !== '' && lote !== '' && (descuento < valor_terreno)) {
													
													if (descuento < 0) {
														$.prompt('El descuento no puede ser negativo.');
                                                        return false;
													}
													
                                                    var monto_e = $('#monto').val() * 1;
                                                    if (monto_e < 0) {
                                                        $.prompt('El monto efectivo debe ser mayor o igual 0.');
                                                        return false;
                                                    }
                                                    var intercambio = $('#ven_monto_intercambio').val() * 1;
                                                    if (intercambio > 0) {
                                                        var sum_det_inter = sumar_intercambio();
                                                        if (intercambio !== sum_det_inter) {
                                                            $.prompt('La Suma del detalle de intercambio debe ser igual al Monto de Intercambio.');
                                                            return false;
                                                        }

                                                    }
                                                    //                                        console.info($('#ven_tipo option:selected').val())
                                                    if ($('#ven_tipo').val() === 'Credito') {
                                                        //                                            var cuota_inicial=$('#cuota_inicial').val()*1;
                                                        //                                            var reserva=0;
                                                        //                                            if($('#ven_anticipo').length){
                                                        //                                                reserva=$('#ven_anticipo').val()*1;
                                                        //                                            }

                                                        var prog_efectivo = monto_e;
                                                        var def = $('#def_plan_efectivo option:selected').val();
                                                        if (def === 'mp') {
                                                            var mp = $('#meses_plazo').val() * 1;
                                                            var fpc = $('#fecha_pri_cuota').val();

                                                            var fecha_pri_mysql = fecha_mysql(fpc);
                                                            var fecha_ini_mysql = fecha_mysql($('#ven_fecha').val());
                                                            if (fecha_ini_mysql > fecha_pri_mysql) {
                                                                $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                                                return false;
                                                            }
                                                            if (!(mp > 0 && fpc !== '')) {
                                                                $.prompt('Revise los datos del credito efectivo:<br> - La meses plazo <br> - Fecha de la primera cuota ');
                                                                return false;
                                                            }
                                                        } else if (def === 'cm') {
                                                            var cm = $('#cuota_mensual').val() * 1;
                                                            var fpc = $('#fecha_pri_cuota').val();

                                                            var fecha_pri_mysql = fecha_mysql(fpc);
                                                            var fecha_ini_mysql = fecha_mysql($('#ven_fecha').val());
                                                            if (fecha_ini_mysql > fecha_pri_mysql) {
                                                                $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                                                return false;
                                                            }
                                                            if (!(cm > 0 && fpc !== '')) {
                                                                $.prompt('Revise los datos del credito efectivo:<br> - La cuota Mensual <br> - Fecha de la primera cuota ');
                                                                return false;
                                                            }
                                                        } else if (def === 'manual') {
                                                            var capital_total = $('#c_total_efectivo').val() * 1;
                                                            var anticipo = 0;
                                                            if ($('#ven_anticipo').length) {
                                                                anticipo = $('#ven_anticipo').val() * 1;
                                                            }
                                                            var cuota_i = $('#cuota_inicial').val() * 1;
                                                            var saldo = prog_efectivo - anticipo - cuota_i;
                                                            if (capital_total !== saldo) {
                                                                $.prompt('en el plan de pagos manual del monto en efectivo falta definir mas cuotas para igualar al monto en efectivo de la venta');
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                    /* ----------------------- monto efectivo ----------------------- */
                                                    var fecha = $('#ven_fecha').val();
                                                    if (fecha !== '') {
                                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                            var dato = JSON.parse(respuesta);
                                                            if (dato.response !== "ok") {
                                                                $.prompt(dato.mensaje);

                                                            } else {
                                                                //                                                            $.prompt('todo bien..');
                                                                document.frm_sentencia.submit();
                                                            }
                                                        });
                                                    } else {
                                                        $.prompt('La fecha del pago de la Venta no debe ser vacio.');
                                                    }
                                                    console.info('ok');

                                                } else {
                                                    $.prompt('Para Guardar la Venta dede seleccionar los campos Persona, Tipo, Moneda, Urbanizaci?n, Manzano, Lote.</br>- El Descuento debe ser menos al Monto a Pagar', {opacity: 0.8});
                                                }
                                            }

                                            function verificar(id)
                                            {
                                                var cant = $('#tprueba tbody').children().length;
                                                var ban = true;
                                                if (cant > 0)
                                                {
                                                    $('#tprueba tbody').children().each(function() {
                                                        var dato = $(this).eq(0).children().eq(0).children().eq(0).attr('value');
                                                        var datos = dato.split('?');
                                                        if (id === datos[2])
                                                        {
                                                            ban = false;
                                                        }

                                                    });
                                                }
                                                return ban;
                                            }


                                            function limpiar_meses_plazo() {
                                                //document.frm_sentencia.meses_plazo.value="";
                                                if (document.frm_sentencia.cuota_mensual.value === "") {
                                                    $('.meses_plazo').css("visibility", "visible");
                                                    document.frm_sentencia.meses_plazo.value = "";
                                                    $('.mes_cuota').css("visibility", "hidden");
                                                    document.frm_sentencia.mes_cuota.value = "";
                                                } else {
                                                    $('.mes_cuota').css("visibility", "visible");
                                                    $('.meses_plazo').css("visibility", "hidden");
                                                    document.frm_sentencia.meses_plazo.value = "";
                                                }
                                            }

                                            function limpiar_cuota_mensual() {
                                                document.frm_sentencia.cuota_mensual.value = "";
                                            }

                                            function set_valor_interno(data) {
                                                document.frm_sentencia.ven_int_id.value = data.id;
                                                document.frm_sentencia.int_nombre_persona.value = data.nombre;
                                            }

                                            function reset_interno() {
                                                document.frm_sentencia.ven_int_id.value = "";
                                                document.frm_sentencia.int_nombre_persona.value = "";
                                            }

                                            function set_valor_copropietario(data) {
                                                document.frm_sentencia.ven_co_propietario.value = data.id;
                                                document.frm_sentencia.int_nombre_copropietario.value = data.nombre;
                                            }
                                            function reset_co_propietario() {
                                                document.frm_sentencia.ven_co_propietario.value = "";
                                                document.frm_sentencia.int_nombre_copropietario.value = "";
                                            }
                                            mask_decimal('#ven_monto_intercambio', null)

                                            $('#def_plan_efectivo').change(function() {
                                                var tipo = 'efectivo';

                                                var def = $(this).val();
                                                if (def === 'mp') {
                                                    $('#meses_plazo').parent().show();
                                                    $('#cuota_mensual').parent().hide();
                                                    $('#cuota_interes').parent().hide();
                                                    $('#ver_plan_efectivo').show();
                                                    $('#add_cuota_efectivo').hide();
                                                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
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
                                                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
                                                    $('#plan_manual_efectivo').hide();
                                                    $('#def_cuota').parent().hide();
                                                } else if (def === 'manual') {
                                                    $('#meses_plazo').parent().hide();
                                                    $('#cuota_mensual').parent().show();
                                                    $('#cuota_interes').parent().show();
                                                    $('#ver_plan_efectivo').hide();
                                                    $('#add_cuota_efectivo').show();
                                                    //                                                            $('#cuota_mensual').prev('span').text('Capital Cuota: ');
                                                    $('#cuota_mensual').prev('span').hide();
                                                    $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                                                    $('#plan_manual_efectivo').show();
                                                    $('#def_cuota').parent().show();
                                                }
                                            });
                                            $('#def_plan_efectivo').trigger('change');

                                            $('#cuota_inicial,#interes_anual').change(function() {
                                                var def = $('#def_plan_efectivo option:selected').val();
                                                if (def === 'manual') {
                                                    limpiar_plan_manual();
                                                }
                                            });

                                            function limpiar_plan_manual() {
                                                $('#tab_plan_efectivo tbody tr').remove();
                                                $('#c_total_efectivo').val('');
                                                $('#pag_total_efectivo').val('');
                                            }

                                            function obtener_ultima_fecha() {
                                                var det_plan = $('.det_plan_efectivo');
                                                if (det_plan.length) {
                                                    var ult_det = $(det_plan).last();
                                                    var fila = JSON.parse($(ult_det).val());
                                                    console.log(fecha_mysql(fila.fecha));
                                                    return fecha_mysql(fila.fecha);
                                                } else {
                                                    console.log(fecha_mysql($('#ven_fecha').val()));
                                                    return fecha_mysql($('#ven_fecha').val());
                                                }
                                            }

                                            function datos_fila() {
                                                var moneda = 2;
                                                var txt_moneda = 'Dolares';


                                                var ci = $('#cuota_inicial').val();
                                                var anticipo = 0;
                                                if ($('#ven_anticipo').length) {
                                                    anticipo = $('#ven_anticipo').val();
                                                }
                                                var intercambio = $('#ven_monto_intercambio').val();
                                                var efectivo = $('#monto').val();
                                                var monto_financiar = efectivo - ci - anticipo - intercambio;

                                                var fecha = $('#fecha_pri_cuota').val();
                                                var n_fecha = fecha_mysql(fecha);
                                                //CALCULAR INTERES
                                                var u_fecha = obtener_ultima_fecha();

                                                console.log(n_fecha + '>' + u_fecha);
                                                if (n_fecha <= u_fecha) {
                                                    $.prompt('Ingrese una Fecha mayor a la fecha de la ultima cuota o a la fecha de la venta');
                                                    return false;
                                                }

                                                var capital_total = $("#c_total_efectivo").val() * 1;
                                                var saldo_final = monto_financiar - capital_total;
                                                var interes_anual = $('#interes_anual').val();
                                                var dias = diferencia_dias(u_fecha, n_fecha);
                                                var interes_dia = (interes_anual / 360) / 100;

                                                var interes = ((dias * interes_dia) * saldo_final).toFixed(2) * 1;//$('#cuota_interes').val()*1;calcular
                                                console.log(dias + '*' + interes_dia + '*' + saldo_final);
                                                var monto_pagar = ($('#cuota_mensual').val() * 1).toFixed(2) * 1;
                                                if (monto_pagar <= 0) {
                                                    $.prompt('Ingrese un Monto');
                                                    return false;
                                                }
                                                var capital = monto_pagar;
                                                var def_cuota = $('#def_cuota option:selected').val();
                                                if (def_cuota === 'dcuota') {
                                                    capital = (monto_pagar - interes).toFixed(2) * 1;
                                                    if (capital <= 0) {
                                                        $.prompt('El monto de la Cuota Ingresada no cubre el interes a la Fecha ');
                                                        return false;
                                                    }
                                                }
                                                //                                                        var capital= $('#cuota_mensual').val()*1;
                                                var tab = '#tab_plan_efectivo';

                                                if ((monto_financiar !== '' && parseInt(monto_financiar) > 0) && moneda !== '' && fecha !== '' && (interes !== '' || parseInt(interes) >= 0) && (capital !== '' || parseInt(capital) > 0)) {
                                                    var saldo_hidden = (monto_financiar - (capital_total + capital * 1)).toFixed(2) * 1;
                                                    var det_plan = {fecha: fecha, interes: interes, capital: capital, saldo: saldo_hidden};

                                                    if (saldo_hidden >= 0) {
                                                        var montopagar = (interes + capital * 1).toFixed(2);
                                                        var nro = $(tab + ' tbody tr').size();
                                                        $(tab + " .del_fespecial").attr('class', 'del_fespecial_h');
                                                        var txt_fila = '';
                                                        txt_fila += '<tr>';
                                                        txt_fila += '     <td>';
                                                        txt_fila += '         <input class="det_plan_efectivo" name="det_plan_efectivo[]" type="hidden" value=\'' + JSON.stringify(det_plan) + '\'>';
                                                        txt_fila += (nro * 1 + 1) + '&nbsp;';
                                                        txt_fila += '     </td>';
                                                        txt_fila += '     <td>' + fecha + '</td>';
                                                        txt_fila += '     <td>' + txt_moneda + '</td>';
                                                        txt_fila += '     <td>' + interes + '</td>';
                                                        txt_fila += '     <td>' + capital + '</td>';
                                                        txt_fila += '     <td>' + montopagar + '</td>';
                                                        txt_fila += '     <td>' + saldo_hidden + '</td>';

                                                        txt_fila += '     <td><img data-tipo="efectivo" src="images/b_drop.png" class="del_fespecial"></td>';
                                                        txt_fila += '</tr>';
                                                        $(tab + ' tbody').append(txt_fila);
                                                        calcular_monto_capital();
                                                        limpiar();
                                                    } else {
                                                        $.prompt('- El capital a ingresar es sobrepasa el monto acordado', {opacity: 0.8});
                                                    }
                                                } else {
                                                    $.prompt('- Ingrese Fecha <br>-Ingrese Interes a Pagar<br>-Ingrese Capital a Pagar', {opacity: 0.8});
                                                }
                                            }

                                            function limpiar(tipo) {

                                                $("#cuota_interes").val("");
                                                $("#cuota_mensual").val("");
                                                $("#cuota_mensual").focus();
                                                var fecha_act = $("#fecha_pri_cuota").val();
                                                var nfecha = siguiente_mes(fecha_mysql(fecha_act));
                                                $("#fecha_pri_cuota").val(fecha_latina(nfecha));

                                            }

                                            function calcular_monto_capital() {
                                                var tab = '#tab_plan_efectivo';
                                                var filas = $(tab + " tbody tr");
                                                var tcapital = 0;
                                                var tmontopagar = 0;
                                                for (var i = 0; i < filas.size(); i++) {
                                                    var cols = $(filas[i]).children();
                                                    var capital = $(cols[4]).text();
                                                    var monto_pagar = $(cols[5]).text();
                                                    tcapital += capital * 1;
                                                    tmontopagar += monto_pagar * 1;
                                                }
                                                $("#c_total_efectivo").val(tcapital);
                                                $("#pag_total_efectivo").val(tmontopagar);
                                            }

                                            $(".del_fespecial").live('click', function() {
                                                $(this).parent().parent().remove();
                                                var tipo = $(this).attr('data-tipo');
                                                var tab = '#tab_plan_' + tipo;
                                                var filas = $(tab + " tbody tr");
                                                $(filas[filas.size() - 1]).find('img').attr('class', 'del_fespecial');
                                                calcular_monto_capital(tipo);
                                            });

                                            fecha_sel = "";
                                            function obtener_periodo() {
                                                var fecha = $('#ven_fecha').val();
                                                if (fecha !== fecha_sel) {
                                                    mostrar_ajax_load();
                                                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                        ocultar_ajax_load();
                                                        var dato = JSON.parse(respuesta);
                                                        if (dato.response === "ok") {
                                                            $('#ven_peri_id').val(dato.id);
                                                            $('#tca_cambios').val(JSON.stringify(dato.cambios));
                                                            $('#lbl_periodo').text(dato.descripcion);
                                                            $('#lbl_periodo').css('color', '#0072b0');
                                                            if ($("#ven_anticipo").length) {
                                                                calcular_reserva();
                                                            }

                                                            cargar_datos($("#ven_lot_id option:selected").val());
                                                            //                                                                    calcular_valor_terreno();
                                                            //                                                                    calcular_monto();

                                                            //                                                                    calcular_cuota();


                                                        } else if (dato.response === "error") {
                                                            $('#ven_peri_id').val("");
                                                            $('#tca_cambios').val(dato.cambios);
                                                            $('#lbl_periodo').text(dato.mensaje);
                                                            $('#lbl_periodo').css('color', '#ff0000');

                                                            $("#superficie").val("##");
                                                            $("#valor").val("##");
                                                            $("#valor_terreno").val("##");
                                                            if ($("#ven_anticipo").length) {
                                                                $("#ven_anticipo").val("##");
                                                            }
                                                            $("#monto").val("##");
                                                            mon_select = 0;
                                                        }
                                                        fecha_sel = fecha;

                                                    });
                                                }
                                            }
                                            $("#ven_fecha").focusout(function() {
                                                obtener_periodo();
                                            });
                                            obtener_periodo();

                                            function calcular_reserva() {
                                                var anticipos = $("#res_anticipos").val();
                                                var cambios = $("#tca_cambios").val();
                                                var moneda = $("#ven_moneda option:selected").val();
                                                var janticipos = JSON.parse(anticipos);
                                                var jcambios = JSON.parse(cambios);
                                                var sum_anticipo = 0;
                                                for (var i = 0; i < janticipos.length; i++) {
                                                    if (janticipos[i].moneda === moneda) {
                                                        sum_anticipo = sum_anticipo + (janticipos[i].monto * 1);
                                                        //                                                                alert(sum_anticipo+" true");
                                                    } else {
                                                        sum_anticipo = sum_anticipo + convertir_monto(moneda, janticipos[i].moneda, janticipos[i].monto, jcambios);
                                                        //                                                                alert(sum_anticipo+" false");
                                                    }

                                                }
                                                $("#ven_anticipo").val(sum_anticipo.toFixed(2));
                                            }


                                            function convertir_monto(moneda, moneda_monto, monto, jcambios) {
                                                var valor_base = monto * valor(moneda_monto, jcambios);
                                                //                                                        alert(valor_base);
                                                //                                                        alert( valor_base/valor(moneda,jcambios));
                                                return valor_base / valor(moneda, jcambios);

                                            }

                                            function valor(moneda, jcambios) {
                                                for (var i = 0; i < jcambios.length; i++) {
                                                    if (moneda === jcambios[i].id) {
                                                        return jcambios[i].val * 1;
                                                    }
                                                }
                                                return 1;
                                            }

                                            function f_moneda() {

                                                var moneda = $("#ven_moneda option:selected").val();

                                                var cambios = $("#tca_cambios").val();
                                                if (cambios === "") {
                                                    return false;
                                                }
                                                var jcambios = JSON.parse(cambios);

                                                var superficie = $("#superficie").val();
                                                if (superficie === "##") {
                                                    superficie = 0;
                                                } else {
                                                    superficie = superficie * 1;
                                                }
                                                var valor = $("#valor").val();
                                                if (valor === "##") {
                                                    valor = 0;
                                                } else {
                                                    valor = valor * 1;
                                                }
                                                var conv_superficie = superficie;//convertir_monto(moneda,mon_select,superficie,jcambios);
                                                var conv_valor = convertir_monto(moneda, mon_select, valor, jcambios);
                                                //                                                        document.frm_sentencia.superficie.value=conv_superficie.toFixed(2);
                                                document.frm_sentencia.valor.value = conv_valor.toFixed(2);
                                                document.frm_sentencia.valor_terreno.value = (conv_valor * conv_superficie).toFixed(2);
                                                calcular_monto();
                                                if ($("#ven_anticipo").length) {
                                                    calcular_reserva()
                                                    //                                                        var anticipo=$("#ven_anticipo").val();
                                                    //                                                        if(anticipo==="##"){
                                                    //                                                            anticipo=0;
                                                    //                                                        }else{
                                                    //                                                            anticipo=anticipo*1;
                                                    //                                                        }
                                                    //                                                        var conv_anticipo=convertir_monto(moneda,mon_select,anticipo,jcambios);
                                                    //                                                        document.frm_sentencia.ven_anticipo.value=conv_anticipo.toFixed(2);
                                                }
                                                mon_select = moneda;
                                            }

                                            function f_tipo() {
                                                var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
                                                if (tipo === 'Contado') {
                                                    document.frm_sentencia.cuota_inicial.value = "";
                                                    document.frm_sentencia.meses_plazo.value = "";
                                                    document.frm_sentencia.cuota_mensual.value = "";
                                                    document.frm_sentencia.descuento.value = 0;
                                                    $("#plan_credito").hide();
                                                    $("#plan_credito_inter").hide();
                                                    $('#tprueba tbody').remove();
                                                } else {
                                                    if (tipo === 'Credito') {
                                                        var interes = $('#ven_urb_id option:selected').attr('data-interes');
                                                        $('#interes_anual').val(interes);
                                                        var sup = $('#superficie').val() * 1;
                                                        var vm2 = $('#valor').val() * 1;
                                                        var ci = 0;
                                                        //                                                            var ci=(sup*vm2)*0.1;
                                                        $('#cuota_inicial').val(ci.toFixed(2));
                                                        $("#plan_credito").show();
                                                        var tipo_pag = $('#ven_tipo_pago option:selected').val();
                                                        if (tipo_pag === 'Intercambio') {
                                                            $("#plan_credito_inter").show();
                                                        } else {
                                                            $("#plan_credito_inter").hide();
                                                        }
                                                    }
                                                }
                                            }

                                            function f_tipo_cancelacion() {
                                                var tipo_pag = document.frm_sentencia.ven_tipo_pago.options[document.frm_sentencia.ven_tipo_pago.selectedIndex].value;
                                                if (tipo_pag === 'Normal') {
                                                    document.frm_sentencia.monto_intercambio.value = "";
                                                    document.frm_sentencia.monto_efectivo.value = $("#monto").val();
                                                    $(".pago_intercambio").hide();
                                                    var tipo = $('#ven_tipo option:selected').val();
                                                    if (tipo === 'Contado') {
                                                        $("#plan_credito").hide();
                                                        $("#plan_credito_inter").hide();
                                                    } else {// credito                                                                    
                                                        $("#plan_credito").show();
                                                        $("#plan_credito_inter").hide();
                                                    }
                                                } else {
                                                    if (tipo_pag === 'Intercambio') {
                                                        document.frm_sentencia.monto_intercambio.value = "";
                                                        document.frm_sentencia.monto_efectivo.value = $("#monto").val();
                                                        ;
                                                        $(".pago_intercambio").show();
                                                        var tipo = $('#ven_tipo option:selected').val();
                                                        if (tipo === 'Contado') {
                                                            $("#plan_credito").hide();
                                                            $("#plan_credito_inter").hide();
                                                        } else {// credito                                                                    
                                                            $("#plan_credito").show();
                                                            $("#plan_credito_inter").show();
                                                        }
                                                    }
                                                }
                                            }

                                            $('#meses_plazo').focusout(function() {
                                                var plazo = $(this).val();
                                                calcular_precio_metro(plazo);
                                            });

                                            $('#cuota_mensual').focusout(function() {

                                            });

                                            function calcular_precio_metro_old_version(plazo) {
                                                var rango = $('#ven_rango').val();
                                                var anios = (rango * plazo) / 12;
                                                var anio_base = $('#urb_anio_base').val();
                                                var anio_inc = $('#urb_anio_inc').val();
                                                var dif_anio = anios - Math.trunc(anios);
                                                if (dif_anio > 0) {
                                                    anios = Math.trunc(anios) + 1;
                                                }
                                                var dif_base = anios - anio_base;
                                                console.log(dif_base);
                                                if (dif_base > 0) {
                                                    var sum_prec_metro = dif_base * anio_inc;
                                                    var precio_metro = $('#valorhidden').val() * 1;
                                                    var precio_final = precio_metro + sum_prec_metro;
                                                    $('#valor').val(precio_final * 1);
                                                } else {
                                                    var precio_metro = $('#valorhidden').val() * 1;

                                                    $('#valor').val(precio_metro);
                                                }
                                                $('#valor').trigger('keyup');
                                            }

                                            function calcular_precio_metro(plazo) {

                                                var datos_lote = $('#ven_lot_id').val().split('-');
                                                var lot_id = datos_lote[0];
                                                var params = {peticion: 'valor_m2', lote: lot_id, meses_plazo: plazo};

                                                $.get('AjaxRequest.php', params, function(respuesta) {
                                                    console.log(respuesta);
                                                    var resp = JSON.parse(respuesta);

                                                    if (resp.ok == 'no') {
                                                        $.prompt(resp.mensaje);
                                                        var precio_metro = $('#valorhidden').val() * 1;
                                                        $('#valor').val(precio_metro);
                                                    } else {
                                                        var val_m2 = resp.dato;
                                                        if (val_m2 * 1 == 0) {
                                                            var val_m2 = $('#valorhidden').val() * 1;
                                                        }
                                                        $('#valor').val(val_m2 * 1);
                                                        console.log('val_m2 => ' + $('#valor').val());
                                                    }
                                                    $('#valor').trigger('keyup');

                                                });

        //                                        if(dif_base>0){
        //                                            var sum_prec_metro=dif_base*anio_inc;
        //                                            var precio_metro=$('#valorhidden').val()*1;
        //                                            var precio_final=precio_metro+sum_prec_metro;
        //                                            $('#valor').val(precio_final*1);
        //                                        }else{
        //                                            var precio_metro=$('#valorhidden').val()*1;
        //                                            
        //                                            $('#valor').val(precio_metro);
        //                                        }
        //                                        $('#valor').trigger('keyup');
                                            }


                                            cargar_datos($("#ven_lot_id option:selected").val());
                    </script>
            </form>
        </div>
        <?php // if($tipo!="reserva"){  ?>

        <?php // }  ?>


        <script>
            jQuery(function($) {
                $("#ven_fecha").mask("99/99/9999");
                $("#ven_fecha_1pago").mask("99/99/9999");
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

    function emergente() {
        $this->formulario->dibujar_cabecera();

        $valor = trim($_POST['valor']);

        $conec = new ADO();
        ?>

        <script>
            function poner(id, valor)
            {
                opener.document.frm_sentencia.ven_int_id.value = id;
                opener.document.frm_sentencia.int_nombre_persona.value = valor;
                window.close();
            }
        </script>
        <?php
        if ($_GET['gp'] == 'ok') {
            if ($this->datos_per()) {
                $verificar = NEW VERIFICAR;

                $parametros[0] = array('int_nombre', 'int_apellido');
                $parametros[1] = array($_POST['int_nombre'], $_POST['int_apellido']);
                $parametros[2] = array('interno');

                if ($verificar->validar($parametros)) {
                    $sql = "insert into interno(int_nombre,int_apellido,int_ci) values 
									('" . $_POST['int_nombre'] . "','" . $_POST['int_apellido'] . "','" . $_POST['int_ci'] . "')";

                    $conec->ejecutar($sql);

                    $valor = $_POST['int_apellido'];
                } else {
                    $mensaje = 'La persona no puede ser agregada, por que existe una persona con ese nombre y apellido.';

                    $this->formulario->mensaje('Error', $mensaje);
                }
            } else {
                
            }
        }

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <div style="clear:both; ">
            <center>
                <form name="forma" id="forma" method="POST" action="gestor.php?mod=venta&tarea=AGREGAR&acc=Emergente&gp=ok">
                    <table align="center">
                        <tr>
                            <td class="txt_contenido" colspan="2" align="center">
                                <font style="color: #006239; font-family: tahoma; font-size: 11px;">Nombre</font><input name="int_nombre" type="text" class="caja_texto" size="10" value="">
                                <font style="color: #006239; font-family: tahoma; font-size: 11px;">Apellido</font><input name="int_apellido" type="text" class="caja_texto" size="10" value="">
                                <font style="color: #006239; font-family: tahoma; font-size: 11px;">CI</font><input name="int_ci" type="text" class="caja_texto" size="10" value="">
                                <input name="Submit" type="submit" class="boton" value="Agregar">
                            </td>
                        </tr>
                    </table>
                </form>
            </center>
            <center>
                <form name="form" id="form" method="POST" action="gestor.php?mod=venta&tarea=AGREGAR&acc=Emergente">
                    <table align="center">
                        <tr>
                            <td class="txt_contenido" colspan="2" align="center">
                                <input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor; ?>">
                                <input name="Submit" type="submit" class="boton" value="Buscar">
                            </td>
                        </tr>
                    </table>
                </form>
            </center>
            <?php
            if ($valor <> "") {
                $sql = "select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
            } else {
                $sql = "select int_id,int_nombre,int_apellido from interno";
            }

            $conec->ejecutar($sql);

            $num = $conec->get_num_registros();

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

            for ($i = 0; $i < $num; $i++) {
                $objeto = $conec->get_objeto();

                echo '<tr>
						 <td>' . $objeto->int_nombre . '</td>
						 <td>' . $objeto->int_apellido . '</td>
						 <td><a href="javascript:poner(' . "'" . $objeto->int_id . "'" . ',' . "'" . $objeto->int_nombre . ' ' . $objeto->int_apellido . "'" . ');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>	 
				';

                $conec->siguiente();
            }
            ?>
        </tbody></table>
        <div>
            <?php
        }

        function datos_per() {
            if ($_POST) {
                //texto,  numero,  real,  fecha,  mail.
                $num = 0;
                $valores[$num]["etiqueta"] = "Nombre";
                $valores[$num]["valor"] = $_POST['int_nombre'];
                $valores[$num]["tipo"] = "todo";
                $valores[$num]["requerido"] = true;
                $num++;
                $valores[$num]["etiqueta"] = "Apellido";
                $valores[$num]["valor"] = $_POST['int_apellido'];
                $valores[$num]["tipo"] = "todo";
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

        function calcular_fecha($meses, $fecha) {

            $fechaComparacion = strtotime($fecha);

            $calculo = strtotime("$meses month", $fechaComparacion);

            return date("Y-m-d", $calculo);
        }

        function calcular_fecha_dias($dias, $fecha) {
            $fechaComparacion = strtotime($fecha);
            $calculo = strtotime("$dias day", $fechaComparacion);
            return date("Y-m-d", $calculo);
        }

        function insertar_tcp() {






            $conec = new ADO();
            $conec->begin_transaccion();
            $valores_lote = explode("-", $_POST['ven_lot_id']);
            $id_lote = $valores_lote[0];
            $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$id_lote'");
            $frm_reserva = isset($_POST['frm_reserva']);
            $concepto = $this->get_concepto($id_lote);
            $loteven = FUNCIONES::objeto_bd_sql("select * from venta where ven_lot_id='$lote->lot_id' and ven_estado in ('Pendiente','Pagado')");
            if (!$loteven && ( ($lote->lot_estado == 'Disponible') || ($frm_reserva && $lote->lot_estado = 'Reservado'))) {
                $venta_nro = FUNCIONES::atributo_bd("venta", "ven_lot_id='$id_lote'", "count(*)") + 1;
                $codigo = $lote->lot_codigo . $venta_nro;
                if ($_POST['ven_tipo'] == "Contado" || $_POST['ven_tipo'] == "Credito") {
                    $res_id = 0;
                    $ven_anticipo = 0;
                    if (isset($_POST['id_res']) && $_POST['id_res'] != "") {
                        $res_id = $_POST['id_res'];
                        $ven_anticipo = $_POST['ven_anticipo'];
                    }

                    $ven_fecha = FUNCIONES::get_fecha_mysql($_POST[ven_fecha]);
                    $superficie = $lote->lot_superficie;
                    $valor_metro = $_POST[valor];

                    $precio_prod = 0;
                    if (isset($_POST[prod_id])) {
                        $datos_prod = explode('-', $_POST[prod_id]);
//                        $precio_prod = $datos_prod[1] * 1;
                        $prod_id = $datos_prod[0];
//                        $inc_m2 = $precio_prod / $superficie;
//                        $valor_metro += $inc_m2;
//                        $valor_metro = $_POST[valorhidden];

                        $valor_producto = $_POST[uprod_precio] * 1;
                        $descuento_producto = $_POST[uprod_descuento] * 1;
                        $precio_prod = $valor_producto - $descuento_producto;
                        $costo_producto = $datos_prod[2];
                    }


                    $valor = $superficie * $valor_metro;
                    $descuento = $_POST[descuento];
                    $monto = $valor - $descuento;
                    $monto_intercambio = $_POST[ven_monto_intercambio];
                    $monto_efectivo = (($monto_intercambio + $ven_anticipo) > $monto) ?  0: $monto - ($monto_intercambio + $ven_anticipo);
//                    $monto_efectivo = $monto - ($monto_intercambio + $ven_anticipo) + $precio_prod;
//                    $monto_efectivo = bcadd($monto - $monto_intercambio - $ven_anticipo, 0);
//                    enum('Normal','Intercambio')
                    $tipo_pago = 'Normal';
                    $tipo_intercambio = '';
                    $intercambio_ids = '';
                    $intercambio_montos = '';
                    if ($monto_intercambio > 0) {
                        $tipo_pago = 'Intercambio';
//                        $tipo_intercambio=$_POST[tipo_intercambio];
                        $intercambio_ids = implode(',', $_POST[intercambio_ids]);
                        $intercambio_montos = implode(',', $_POST[intercambio_montos]);
                    }

                    $estado = "Pendiente";
                    $ven_tipo = "Credito";
                    $monto_pagar = 0; //devolver
                    if (($monto_efectivo + $precio_prod) <= 0) {
                        $ven_tipo = "Contado";
                        $estado = "Pagado";
                        $monto_pagar = $monto_efectivo * (-1);
                        $monto_efectivo = 0;
                    }

                    $cuota_inicial = 0;
                    $interes_anual = 0;
                    $tipo_plan = 'plazo';
                    $atp = array('mp' => 'plazo', 'cm' => 'cuota', 'manual' => 'manual');

                    $int_id = $_POST[ven_int_id];
                    $urb_id = $_POST[ven_urb_id];
                    $moneda = $_POST[ven_moneda];
                    $vendedor = $_POST[vendedor];
                    $cambio_usd = 1;
                    if ($moneda == '1') {
                        $cambio_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$ven_fecha' and tca_mon_id=2");
                    }

                    if (($monto_efectivo + $precio_prod) > 0) {
                        $cuota_inicial = $_POST[cuota_inicial];
                        $interes_anual = $_POST[interes_anual];
                        $tipo_plan = $atp[$_POST[def_plan_efectivo]];
                    }
                    $fecha_cre = date('Y-m-d H:i:s');

                    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_id");

                    if (isset($_POST[multinivel])) {
                        $val_form = ($_POST[multinivel] == 'si') ? 0 : $urb->urb_val_form;
                    } else {
                        $val_form = $urb->urb_val_form; /// FUNCIONES::ad_parametro('par_valor_form');
                    }

                    $total_venta = $monto + $precio_prod;
//                    $prorat_lote = round($monto / $total_venta, 2);
                    $prorat_lote = ($monto / $total_venta);

                    $costo = $superficie * ($urb->urb_val_costo * $cambio_usd);
                    $costo_cub = ($ven_anticipo + $monto_intercambio) * $prorat_lote;
                    $costo_pag = 0;
                    if ($costo_cub > $costo) {
                        $costo_cub = $costo;
                    }

                    //  PARA EL PRORATEO DEL PRODUCTO
                    if ($precio_prod > 0) {
//                        $prorat_prod = round($precio_prod / $total_venta, 2);
                        $prorat_prod = ($precio_prod / $total_venta);
                        $costo_prod_cub = ($ven_anticipo + $monto_intercambio) * $prorat_prod;

                        if ($costo_prod_cub > $costo_producto) {
                            $costo_prod_cub = $costo_producto;
                        }
                    }
                    //  PARA EL PRORATEO DEL PRODUCTO

                    $txt_ubicacion = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id='$_POST[lug_id]'");
//                    $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_SESSION[id]'");
                    $fecha_pri_cuota = FUNCIONES::get_fecha_mysql($_POST[fecha_pri_cuota]);
                    $oferta = $_POST[oferta] * 1;
                    $ven_comision_gen = $_POST[ven_comision_gen];
                    
                    $ven_orden = FUNCIONES::orden_venta_lote($id_lote);
                    
                    $sql = "insert into venta(
                                ven_int_id,ven_co_propietario,ven_lot_id,ven_urb_id,ven_fecha,ven_moneda,ven_res_id,
                                ven_metro,ven_superficie,ven_valor,ven_decuento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,
                                ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,
                                ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_concepto,
                                ven_tipo_pago,ven_fecha_cre,ven_monto_pagar,ven_form,ven_intercambio_ids,ven_intercambio_montos,
                                ven_costo,ven_costo_cub,ven_costo_pag,ven_lug_id, ven_ubicacion,ven_suc_id,
                                ven_ufecha_pago,ven_ufecha_valor,ven_usaldo,ven_cuota_pag,ven_capital_pag,ven_capital_inc,ven_capital_desc,ven_sfecha_prog,
                                ven_multinivel,ven_of_id,ven_comision_gen,ven_monto_producto,ven_prod_id,
                                ven_valor_producto,ven_descuento_producto,ven_costo_producto,ven_costo_producto_cub,ven_orden
                            ) values (
                                '$int_id','$_POST[ven_co_propietario]','$id_lote','$urb_id','$ven_fecha','$moneda','$res_id',
                                '$valor_metro','$superficie','$valor','$descuento','$monto','$ven_anticipo','$monto_intercambio','$monto_efectivo',
                                '$estado','{$this->usu->get_id()}','$ven_tipo','$interes_anual','$cuota_inicial','$tipo_plan','0','0',
                                '$_POST[ven_observacion]', '$codigo','$vendedor','0','$concepto',
                                '$tipo_pago','$fecha_cre','$monto_pagar','$val_form','$intercambio_ids','$intercambio_montos',
                                '$costo','$costo_cub','$costo_pag','$_POST[lug_id]','$txt_ubicacion','$_POST[ven_suc_id]',
                                '$ven_fecha','$ven_fecha','$monto_efectivo',0,0,0,0,'$fecha_pri_cuota',
                                '$_POST[multinivel]','$oferta','$ven_comision_gen','$precio_prod','$prod_id',
                                '$valor_producto','$descuento_producto','$costo_producto','$costo_prod_cub','$ven_orden'
                            )";
//                    echo $sql.'<br>';

                    $conec->ejecutar($sql, true, true);
                    $llave = ADO::$insert_id;
					
					$ven_can_codigo = FUNCIONES::crear_cuentas_analiticas($llave);

                    if ($monto_intercambio > 0) {
                        $inter_ids = $_POST[intercambio_ids];
                        $inter_montos = $_POST[intercambio_montos];
                        for ($i = 0; $i < count($inter_ids); $i++) {
                            $_inter_id = $inter_ids[$i];
                            $_inter_monto = $inter_montos[$i];
                            $sql_insert = "insert into venta_intercambio(
                                            vint_ven_id,vint_inter_id,vint_monto,vint_estado
                                        )values(
                                            $llave,'$_inter_id','$_inter_monto','Pendiente'
                                        )";
                            $conec->ejecutar($sql_insert);
                        }
                    }

                    if (isset($_POST['id_res']) && $_POST['id_res'] != "") {
                        $sql = "update reserva_terreno set res_estado='Concretado' where res_id='" . $_POST['id_res'] . "';";
                        $conec->ejecutar($sql);
                    }

                    $vplazo = 0;
                    if (($monto_efectivo + $precio_prod) > 0) {
                        $plan_data = array(
                            'ven_id' => $llave,
                            'int_id' => $int_id,
                            'fecha' => $ven_fecha,
                            'moneda' => $moneda,
                            'concepto' => $concepto,
                            'monto' => $monto_efectivo + $precio_prod,
                            'cuota_inicial' => $cuota_inicial,
                            'interes_anual' => $interes_anual,
                            'tipo_plan' => $tipo_plan,
                            'plazo' => $_POST[meses_plazo],
                            'cuota' => $_POST[cuota_mensual],
                            'rango' => $_POST[ven_rango],
                            'frecuencia' => $_POST[ven_frecuencia],
                            'fecha_pri_cuota' => $fecha_pri_cuota,
                            'det_plan_manual' => $_POST[det_plan_efectivo],
                        );
                        $vplazo = $this->insertar_plan_pagos($plan_data, $conec);
                    }

//                    $monto_venta=$_POST[superficie]*$_POST[valor]-$_POST[descuento];
                    if ($_POST[multinivel] === 'no') {
                        $data_com = array(
                            'ven_id' => $llave,
                            'vendedor' => $_POST[vendedor],
                            'superficie' => $superficie,
                            'tipo' => $ven_tipo,
                            'plazo' => $vplazo,
                            'monto' => $monto,
                            'moneda' => $moneda,
                            'fecha' => $ven_fecha,
                            'urb' => $urb,
                        );

                        $this->insertar_comision($data_com, $conec);
                    }

                    $sql = "update lote set lot_estado='Vendido' where lot_id='" . $id_lote . "'";
                    $conec->ejecutar($sql);
                    if ($_POST[id_res]) {
                        if ($_POST[res_int_id] != $_POST[ven_int_id]) {
                            $this->cambiar_analitico_interno($_POST[id_res], $_POST[ven_int_id], $conec);
                        }
                    }
                    if ($urb->urb_tipo == 'Interno') {
                        include_once 'clases/modelo_comprobantes.class.php';
                        include_once 'clases/registrar_comprobantes.class.php';
                        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$int_id'");
                        $glosa = "Venta Nro. $llave - $referido - " . $concepto;
                        $data = array(
                            'moneda' => $moneda,
                            'ges_id' => $_SESSION[ges_id],
                            'fecha' => $ven_fecha,
                            'glosa' => $glosa,
                            'interno' => $referido,
                            'tabla_id' => $llave,
                            'urb' => $urb,
                            'anticipo' => $ven_anticipo,
                            'saldo_efectivo' => $monto_efectivo,
                            'monto_intercambio' => $monto_intercambio,
                            'intercambio_ids' => $_POST[intercambio_ids],
                            'intercambio_montos' => $_POST[intercambio_montos],
                            'monto_pagar' => $monto_pagar,
                            'costo' => $costo,
                            'costo_cub' => $costo_cub,
                            'monto_producto' => $precio_prod,
                            'monto_venta' => $monto,
                            'prorat_lote' => $prorat_lote,
                            'prorat_producto' => $prorat_prod,
                            'costo_producto' => $costo_producto,
                            'costo_producto_cub' => $costo_prod_cub,
                            'ven_can_codigo' => $ven_can_codigo,
                            'descuento' => $descuento,
                            'orden' => $ven_orden,
                        );

                        $comprobante = MODELO_COMPROBANTE::venta($data);

                        COMPROBANTES::registrar_comprobante($comprobante);
                    }

                    if ($_POST[multinivel] === 'si') {
                        //MLM::agregar_asociado
                        include_once 'clases/mlm.class.php';

//                        $fecha_act_mlm = FUNCIONES::atributo_bd_sql("select 
//                                    DATE_ADD('{$ven_fecha}',INTERVAL {$urb->urb_nro_cuotas_multinivel} month)as campo");

                        $fecha_act_mlm = FUNCIONES::atributo_bd_sql("select 
                                    DATE_ADD('{$ven_fecha}',INTERVAL {$_POST[meses_bra]} month)as campo");

                        $sql_com = "update venta set ven_porc_comisiones='$urb->urb_porc_comisiones',
                                        ven_fecha_act_mlm='$fecha_act_mlm'
                                        where ven_id=$llave";
                        FUNCIONES::bd_query($sql_com);

                        $data_asociado = array(
                            'int_id' => $_POST[ven_int_id],
                            'vdo_id' => $_POST[vendedor],
                            'ven_id' => $llave
                        );
                        MLM::agregar_asociado($data_asociado);


                        if ($oferta > 0) {
                            include_once 'clases/oferta.class.php';
                            $data_oferta = array('venta' => $llave, 'oferta' => $oferta);
                            OFERTA::guardar_venta_oferta($data_oferta);
                        }
                        $data_bono = array('ven_id' => $llave, 'oferta' => $oferta);
                        MLM::calcular_montos_bonos($data_bono);
                        $data_cobro = array('venta' => $llave, 'vendedor' => $_POST[vendedor]);
                        MLM::insertar_comisiones_cobro($data_cobro);
                    }

                    $exito = $conec->commit();
                    if ($exito) {
                        $this->nota_de_venta($llave);
                    } else {
                        $exito = false;
                        $mensajes = $conec->get_errores();
                        $mensaje = implode('<br>', $mensajes);
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                    }
                }
            } else {
                $mensaje = 'El Lote que ha seleccionado, ya se encuentra Vendido.';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }
        }

        function insertar_plan_pagos($params, &$conec = null) {
            if ($conec == null) {
                $conec = new ADO();
            }
            $par = (object) $params;
            $usuario_id = $this->usu->get_id();
            $monto = $par->monto;
            $cuota_inicial = $par->cuota_inicial * 1;
            $saldo_financiar = $monto - $cuota_inicial;
            $tipo_plan = $par->tipo_plan;
            $fecha_cre = date('Y-m-d H:i:s');
            if ($cuota_inicial > 0) {
                $sql = "insert into interno_deuda(
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                        )values(
                            'venta','$par->ven_id','0','$par->int_id','$par->fecha','$par->moneda',
                            '0','$cuota_inicial','$cuota_inicial','$saldo_financiar','$par->fecha','Pendiente',
                            '0','Cuota Inicial - $par->concepto','$usuario_id','pcuota','$fecha_cre','0'
                        )";
                $conec->ejecutar($sql);
            }
            if ($tipo_plan == 'plazo' || $tipo_plan == 'cuota') {
                if ($tipo_plan == 'plazo') {
                    $_mp = $par->plazo;
                    $_cm = '';
                    $vcuota = FUNCIONES::get_cuota($saldo_financiar, $par->interes_anual, $_mp, 1);
                } elseif ($tipo_plan == 'cuota') {
                    $_mp = '';
                    $_cm = $par->cuota;
                    $vcuota = $_cm;
                }
                $plan_data = array(
                    'int_id' => $par->int_id,
                    'saldo' => $saldo_financiar,
                    'interes' => $par->interes_anual,
                    'tipo' => $tipo_plan,
                    'plazo' => $_mp,
                    'cuota' => $_cm,
                    'moneda' => $par->moneda,
                    'concepto' => $par->concepto,
                    'fecha' => $par->fecha,
                    'fecha_inicio' => $par->fecha,
                    'fecha_pri_cuota' => $par->fecha_pri_cuota,
                    'usuario' => $usuario_id,
                    'tabla' => 'venta',
                    'nro_cuota_inicio' => 1,
                    'tabla_id' => $par->ven_id,
                    'ind_tipo' => 'pcuota',
                    'rango' => $par->rango,
                    'frecuencia' => $par->frecuencia
                );
                $vplazo = $this->generar_plan_pagos($plan_data, $conec); //
                $sql_sel = "select ind_fecha_programada as campo from interno_deuda where ind_tabla='venta' and ind_tabla_id='$par->ven_id' 
                            and ind_estado in ('Pendiente','Pagado')order by ind_id desc limit 1";

                $ufecha_prog = FUNCIONES::atributo_bd_sql($sql_sel);
                $sql_up_venta = "update venta set ven_plazo='$vplazo', ven_cuota='$vcuota', ven_rango='$par->rango', ven_frecuencia='$par->frecuencia' , ven_ufecha_prog='$ufecha_prog' where ven_id=$par->ven_id";

                $conec->ejecutar($sql_up_venta);
                return $vplazo;
            } elseif ($tipo_plan == 'manual') {
                $lista_pagos = $_POST[det_plan_efectivo];
                $nro = 0;
                foreach ($lista_pagos as $txt_fila) {
                    $nro++;
                    $txt_fila = str_replace('\"', '"', $txt_fila);
                    $fila = json_decode($txt_fila);
                    $fecha_prog = FUNCIONES::get_fecha_mysql($fila->fecha);
                    $monto = $fila->interes + $fila->capital;
                    $sql = "insert into interno_deuda(
                                ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                                ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                                ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                            )values(
                                'venta','$par->ven_id','$nro','$par->int_id','$par->fecha','$par->moneda',
                                '$fila->interes','$fila->capital','$monto','$fila->saldo','$fecha_prog','Pendiente',
                                '0','Cuota Nro $nro - $par->concepto','$usuario_id','pcuota','$fecha_cre',0
                            )";

                    $conec->ejecutar($sql);
                }
                $sql_up_venta = "update venta set ven_plazo='$nro' where ven_id=$par->ven_id";
                $conec->ejecutar($sql_up_venta);
                return $nro;
            }
        }

        function cuotas_gestion($ven_id, $ges_id) {
            $gestion = FUNCIONES::objeto_bd_sql("select * from con_gestion where ges_id='$ges_id'");
            $cuotas = FUNCIONES::objetos_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$ven_id'");
            $_acuotas = array();
            $sum_monto = 0;
            $sum_pagado = 0;
            for ($i = 0; $i < $cuotas->get_num_registros(); $i++) {
                $cuota = $cuotas->get_objeto();
                $fc_prog = $cuota->ind_fecha_programada;
                $fg_ini = $gestion->ges_fecha_ini;
                $fg_fin = $gestion->ges_fecha_fin;
                if ($fc_prog >= $fg_ini && $fc_prog <= $fg_fin) {
                    $_acuotas[] = $cuota;
                    $sum_monto+=$cuota->ind_monto;
                    if ($cuota->ind_estado == 'Pagado') {
                        $sum_pagado+=$cuota->ind_monto;
                    }
                }
                $cuotas->siguiente();
            }
            return (object) array('cuotas' => $_acuotas, 'total' => $sum_monto, 'tpagado' => $sum_pagado);
        }

        function cambiar_analitico_interno($res_id, $ven_int_id, &$conec = null) {
            if ($conec == null) {
                $conec = new ADO();
            }
            $pagos = FUNCIONES::objetos_bd_sql("select * from reserva_pago where respag_res_id='$res_id'");
//            echo "select * from reserva_pago where respag_res_id='$res_id';<br>";
            for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
                $pago = $pagos->get_objeto();
                $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='reserva_pago' and cmp_tabla_id='$pago->respag_id'");
//                echo "select * from con_comprobante where cmp_tabla='reserva_pago' and cmp_tabla_id='$pago->respag_id';<br>";
                $can_id = FUNCIONES::atributo_bd_sql("select can_id as campo from con_cuenta_ca where can_ges_id='$cmp->cmp_ges_id' and can_codigo='01.00001'");
//                echo "select can_id from con_cuenta_ca where can_ges_id='$cmp->cmp_ges_id' and can_codigo='01.00001';<br>";
                $sql_up = "update con_comprobante_detalle set cde_int_id='$ven_int_id' where cde_cmp_id='$cmp->cmp_id' and cde_can_id='$can_id'";
//                echo $sql_up.';<br>';
                $conec->ejecutar($sql_up);
                $pagos->siguiente();
            }
        }

//        function insertar_comision($ven_id,$tipo ,$monto,$moneda, $fecha,$urb, &$conec=null){
        function insertar_comision($data, &$conec = null) {
            if ($conec == null) {
                $conec = new ADO();
            }
            $par = (object) $data;
            $urb = $par->urb;
            $ven_id = $par->ven_id;
            $tipo = $par->tipo;
            $monto = $par->monto;
            $moneda = $par->moneda;
            $fecha = $par->fecha;
            $sql_insert = "insert into comision(com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id) values";

            if ($par->vendedor) {
                $vdo_id = $par->vendedor;
                $vendedor = FUNCIONES::objeto_bd_sql("select vendedor.*,vendedor_grupo.* from vendedor,vendedor_grupo where vdo_vgru_id=vgru_id and vdo_id='$vdo_id'");
                $monto_com = 0;
                $valor_comision = 0;
                if ($tipo == 'Contado') {
                    $valor_comision = $vendedor->vgru_contado;
                } elseif ($tipo == 'Credito') {
                    if ($par->plazo <= 3) {
                        $valor_comision = $vendedor->vgru_credito3m;
                    } else {
                        $valor_comision = $vendedor->vgru_credito;
                    }
                }
                if ($vendedor->vgru_comision == 'Porcentaje') {
                    $monto_com = ($valor_comision / 100) * $monto;
                    if ($moneda == 1) {
                        $tc_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_mon_id=2 and tca_fecha='$fecha'");
                        $monto_com = $monto_com / $tc_usd;
                    }
                } elseif ($vendedor->vgru_comision == 'Metro') {
                    $monto_com = $valor_comision * $par->superficie;
                }
                $com_venta = 0;
                $com_pagar = 0;
                $it_pagar = 0;
                $iue_pagar = 0;
                $afp_pagar = 0;
                $cred_fis = 0;
                if (true) {
                    $com_pagar = $monto_com;
                } elseif ($vendedor->vdo_impuesto == 'Retencion') {
                    $it = 3;
                    $iue = 12.5;
                    $it_pagar = $monto_com * (($it) / 100);
                    $iue_pagar = $monto_com * (($iue) / 100);
                    $com_venta = $monto_com;
                    $com_pagar = $monto_com - $it_pagar - $iue_pagar;
                } elseif ($vendedor->vdo_impuesto == 'Factura') {
                    $cf = 13;
                    $cred_fis = $monto_com * (($cf) / 100);
                    $com_venta = $monto_com - $cred_fis;
                    $com_pagar = $monto_com;
                } elseif ($vendedor->vdo_impuesto == 'Interno') {
                    $afp = 12.71;
                    $afp_pagar = $monto_com * (($afp) / 100);
                    $com_venta = $monto_com;
                    $com_pagar = $com_venta - $afp_pagar;
                }

                $sql = $sql_insert . "('$ven_id','$vdo_id','$com_pagar','2','Pendiente','$fecha','{$this->usu->get_id()}')";
                $conec->ejecutar($sql, true, true);
                $llave = ADO::$insert_id;

                $sql_up_venta = "update venta set ven_comision='$com_pagar' where ven_id=$ven_id";
                $conec->ejecutar($sql_up_venta);

                $glosa = "Provision de comision a vendedor Interno por la venta Nro $ven_id";
                $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from vendedor, interno where vdo_int_id=int_id and vdo_id='$par->vendedor'");
//                $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_id");
//                if($urb->urb_tipo=='Interno'){
                include_once 'clases/modelo_comprobantes.class.php';
                include_once 'clases/registrar_comprobantes.class.php';

                $data = array(
                    'moneda' => 2,
                    'ges_id' => $_SESSION[ges_id],
                    'fecha' => $fecha,
                    'glosa' => $glosa,
                    'interno' => $referido,
                    'tabla_id' => $llave,
                    'urb' => $urb,
                    'monto' => $monto_com,
                );

                $comprobante = MODELO_COMPROBANTE::comision($data);

                COMPROBANTES::registrar_comprobante($comprobante);
//                }
            }
        }

        function generar_plan_pagos($parametros, &$conec) {
            /*
              //            'int_id'=>$par->int_id,
              //            'saldo'=>$saldo_financiar,
              //            'interes'=>$par->interes_anual,
              //            'plazo'=>$_mp,
              //            'cuota'=>$_cm,
              //            'moneda'=>$par->moneda,
              //            'concepto'=>$par->concepto,
              //            'fecha'=>$par->fecha,
              //            'fecha_pri_pago'=>  $par->fecha_pri_pago,
              //            'usuario'=>  $usuario_id,
              //            'tabla'=> 'venta',
              //            'nro_cuota_inicio'=>  1,
              //            'tabla_id'=>  $par->ven_id,
              //            'ind_tipo'=>  'pcuota'
             */

            if ($conec == null) {
                $conec = new ADO();
            }
            $par = (object) $parametros;
            $lista_pagos = array();
            if ($par->tipo == "plazo") {//plazo
                $data = array(
                    'tipo' => 'plazo',
                    'plazo' => $par->plazo,
                    'interes_anual' => $par->interes,
                    'saldo' => $par->saldo,
                    'fecha_inicio' => $par->fecha_inicio,
                    'fecha_pri_cuota' => $par->fecha_pri_cuota,
                    'nro_cuota_inicio' => $par->nro_cuota_inicio,
                    'rango' => $par->rango,
                    'frecuencia' => $par->frecuencia
                );
                $lista_pagos = FUNCIONES::plan_de_pagos($data);
            } elseif ($par->tipo == "cuota") {//cuota mensual
                $data = array(
                    'tipo' => 'cuota',
                    'cuota' => $par->cuota,
                    'plazo' => $par->plazo,
                    'interes_anual' => $par->interes,
                    'saldo' => $par->saldo,
                    'fecha_inicio' => $par->fecha_inicio,
                    'fecha_pri_cuota' => $par->fecha_pri_cuota,
                    'nro_cuota_inicio' => $par->nro_cuota_inicio,
                    'rango' => $par->rango,
                    'frecuencia' => $par->frecuencia
                );
                $lista_pagos = FUNCIONES::plan_de_pagos($data);
            } elseif ($par->cuota != "" && $par->plazo != "") {//plazo cuota
                $data = array(
                    'tipo' => 'plazo_cuota',
                    'cuota' => $par->cuota_mensual,
                    'plazo' => $par->plazo,
                    'interes_anual' => $par->interes,
                    'saldo' => $par->saldo,
                    'fecha_inicio' => $par->fecha_inicio,
                    'fecha_pri_cuota' => $par->fecha_pri_cuota,
                    'nro_cuota_inicio' => $par->nro_cuota_inicio
                );
                $lista_pagos = FUNCIONES::plan_de_pagos($data);
            }
            $nro_cuota = 0;
//            FUNCIONES::print_pre($lista_pagos);
//            return;
            $fecha_cre = date('Y-m-d H:i:s');
            foreach ($lista_pagos as $fila) {
                $sql = "insert into interno_deuda(
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                        )values(
                            'venta','$par->tabla_id','$fila->nro_cuota','$par->int_id','$par->fecha','$par->moneda',
                            '$fila->interes','$fila->capital','$fila->monto','$fila->saldo','$fila->fecha','Pendiente',
                            '$fila->dias','Cuota Nro $fila->nro_cuota - $par->concepto','$par->usuario','pcuota','$fecha_cre','$par->val_form'
                        )";
//                echo "$sql;<br>";
                $nro_cuota = $fila->nro_cuota;
                $conec->ejecutar($sql);
            }
//            $nro_cuota_inicio++;
            return $nro_cuota;
        }

        //($i, $saldo,$fecha, $interes_mensual, $des, $cuota, $valor_form,$dif_dias);


        function existe_lote_en_venta($lote_id) {
            $conec = new ADO();
            $sql = "select ven_lot_id from venta where ven_lot_id=$lote_id";
            $conec->ejecutar($sql);
            $num = $conec->get_num_registros();
            if ($num > 0) {
                return true;
            } else {
                return false;
            }
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

        function cabecera_venta($venta) {
            $myday = setear_fecha(strtotime($venta->ven_fecha));
//            $myday = setear_fecha(strtotime('2018-08-08'));
            $desc_mod_venta = ($venta->ven_multinivel == 'si') ? " - MLM" : " - TRAD";
            ?>
            <br><br>
            <center>
                <table border='0' style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                    <tr>
                        <td width="33%">
                            <strong><?php echo _nombre_empresa; ?></strong><BR>
                        </td>
                        <td width="33%"><center><p align="center" ><strong><h3>NOTA DE VENTA</h3></strong></p></center></td>
                    <td><div align="right"><img src="imagenes/micro.png"  /></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Nro. de Venta: </strong> <?php echo $venta->ven_id . $desc_mod_venta; ?> <br>
                            <strong>Nro. Adj.: </strong> <?php echo $venta->ven_numero; ?> <br>
                            <?php
                            if ($venta->ven_int_id <> 0) {
                                $nombre_cliente = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id=$venta->ven_int_id");
                                ?>
                                <strong>Nombre: </strong> <?php echo $nombre_cliente; ?> <br>
                                <?php
                            }
                            ?>
                            <?php
                            if ($venta->ven_co_propietario <> 0) {
                                $persona = $this->obtener_nombrepersona_interno($venta->ven_co_propietario);
                                ?>						
                                <strong>Co-Propietario: </strong> <?php echo $persona; ?> <br>						
                                <?php
                            }
                            ?>				

                            <strong>Tipo: </strong> <?php echo $venta->ven_tipo; ?> <br>
                            <strong>Interes: </strong> <?php echo ($venta->ven_val_interes * 1 ) . " %"; ?> <br>
                            <strong>Tipo de Pago: </strong> <?php echo $venta->ven_tipo_pago; ?> <br>
                            <strong>Moneda: </strong> <?php
                    $moneda = $venta->ven_moneda;
                    if ($moneda == "1")
                        echo "Bolivianos";
                    else
                        echo "Dolares";
                            ?> <br>
                            <?php
                            if ($venta->ven_vdo_id > 0) {
                                $vendedor1 = $this->obtener_nombre_vendedor($venta->ven_vdo_id);
                            } else if ($venta->ven_promotor) {
                                $vendedor1 = $venta->ven_promotor;
                            }
                            ?>
                            <strong>Vendedor: </strong> <?php echo $vendedor1; ?> <br>
                            <strong>Lugar: </strong> <?php echo $venta->ven_ubicacion; ?> <br>
                            <strong>Sucursal: </strong> <?php echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$venta->ven_suc_id'"); ?> <br><br>
                            <?php
                            $sql_fv = "select vpag_fecha_valor as campo from venta_pago where vpag_ven_id=$venta->ven_id and vpag_estado='Activo' order by vpag_id desc";
                            $u_fecha_valor = FUNCIONES::atributo_bd_sql($sql_fv);
                            if (!$u_fecha_valor) {
                                $u_fecha_valor = $venta->ven_fecha;
                            }
                            ?>

                            <strong>U. Fecha Valor: </strong> <?php echo FUNCIONES::get_fecha_latina($u_fecha_valor); ?> <br><br>

                            <?php if ($venta->ven_observacion <> '') { ?>
                                <strong>Observaci&oacute;n: </strong> <?php echo $venta->ven_observacion; ?> <br><br>
                            <?php } ?>					

        <?php if ($venta->ven_observacion_firma <> '') { ?>
                                <strong>Observaciones Firma Contrato: </strong> <?php echo $venta->ven_observacion_firma; ?> <br><br>
        <?php } ?>					

                        </td>
                        <td align="right">
                            <strong>Fecha: </strong> <?php echo $myday; ?> <br>
                            <!--<strong>Usuario: </strong> <?php // echo $this->nombre_persona($venta->ven_usu_id);  ?> <br>-->
                            <strong>Usuario: </strong> <?php echo $this->nombre_persona(FUNCIONES::get_usuario($venta)); ?> <br>
							<?php
							if ($_SESSION[id] == 'admin') {
								?>
								<strong>ESTADO: </strong> <?php echo strtoupper($venta->ven_estado); ?> <br>
								<strong>COSTO: </strong> <?php echo strtoupper($venta->ven_costo); ?> <br>
								<strong>COSTO CUB.: </strong> <?php echo strtoupper($venta->ven_costo_cub); ?> <br>
								<strong>COSTO PAG.: </strong> <?php echo strtoupper($venta->ven_costo_pag); ?> <br>
								<?php
							}
							?>
                        </td>
                    </tr>

                </table>
                <?php
                $hay_casa = ($venta->ven_prod_id > 0);
                $precio_lote = $venta->ven_valor;
                if ($hay_casa) {
//                    $precio_lote = $venta->ven_valor - $venta->ven_monto_producto;
//                    $precio_lote = $venta->ven_valor;
                    $prod_casa = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto 
                        where uprod_id='$venta->ven_prod_id'");
                }
                ?>
                <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <?php if ($venta->ven_lot_ids) { ?>
                                <th>#</th>
                            <?php } ?>
                            <th>Terreno</th>
                            <?php
                            if ($hay_casa) {
                                ?>
                                <th>Casa</th>                            
            <?php
        }
        ?>
                            <th>Superficie</th>

                            <th>Valor del m2</th>
                            <th>Precio Lote</th>                            

        <!--<th>Monto</th>-->
                            <?php
                            if ($venta->ven_lot_ids) {
                                ?>
                                <th>Sup. Total</th>
                                <?php
                            }

                            if ($venta->ven_decuento) {
                                ?>
                                <th>Descuento</th>
                                <!--<th>Monto Total</th>-->
                                <th>Monto</th>
                                <?php
                            }

                            if ($hay_casa) {
                                ?>
                                <th>Precio Casa</th>
                                <th>Desc. Casa</th>
                                <th>Monto Casa</th>
                                <th>Monto Total</th>
                                <?php
                            }
                            ?>

                            <?php
                            if ($venta->ven_res_anticipo > 0) {
                                ?>
                                <th>Cuota Inicial</th>
                            <?php } ?>
                            <?php if ($venta->ven_monto_intercambio > 0) { ?>
                                <th>Intercambio</th>
        <?php } ?>
        <?php if ($venta->ven_venta_id > 0) { ?>
                                <th>
                                    Pagado Anteriormente
                                    <img id="btn_ver_venta" style="display: inline; cursor: pointer; "src="images/b_browse.png" width="16">
                                </th>
        <?php } ?>
                            <?php if ($venta->ven_tipo == 'Credito') { ?>
                                <th>Saldo a Financiar</th>
                                <th>Monto Cuota</th>
                                <th>Plazo</th>
        <?php } ?>
                        </tr>
                    </thead>

                    <tbody>
                            <?php if (!$venta->ven_lot_ids) { ?>
                            <tr>
                                <td><?php echo $venta->ven_concepto; ?></td>
                                <?php
                                if ($hay_casa) {
                                    ?>
                                    <td><?php echo $prod_casa->uprod_nombre; ?></td>                                
                <?php
            }
            ?>
                                <td><?php echo $venta->ven_superficie; ?> m2</td>
                                <td><?php echo round($venta->ven_metro, 2); ?></td>
                                <td><?php echo $precio_lote; ?></td>                                

                                <?php
                                if ($venta->ven_decuento) {
                                    ?>
                                    <td><?php echo number_format($venta->ven_decuento, 2); ?></td>
                                    <td><?php echo number_format($venta->ven_monto, 2); ?></td>
                                <?php
                                }
                                if ($hay_casa) {
                                    ?>
                                    <td><?php echo number_format($venta->ven_valor_producto, 2); ?></td>
                                    <td><?php echo number_format($venta->ven_descuento_producto, 2); ?></td>
                                    <td><?php echo number_format($venta->ven_monto_producto, 2); ?></td>
                                    <td><?php echo number_format($venta->ven_monto + $venta->ven_monto_producto, 2); ?></td>
                <?php
            }
            ?>




                                <?php if ($venta->ven_res_anticipo > 0) { ?>
                                    <td><?php echo number_format($venta->ven_res_anticipo, 2); ?></td>
                                <?php } ?>
                                <?php if ($venta->ven_monto_intercambio > 0) { ?>
                                    <td><?php echo number_format($venta->ven_monto_intercambio, 2); ?></td>
                                    <?php } ?>
                                <?php if ($venta->ven_venta_id > 0) { ?>
                                    <td>
                                    <?php echo number_format($venta->ven_venta_pagado, 2); ?>
                                    </td>
            <?php } ?>
                                <?php if ($venta->ven_tipo == 'Credito') { ?>                                    
                                    <td><?php echo number_format($venta->ven_monto_efectivo + $venta->ven_monto_producto, 2); ?></td>
                                    <td><?php echo number_format($venta->ven_cuota, 2); ?></td>
                                    <td><?php echo $venta->ven_plazo; ?></td>
                            <?php } ?>
                            </tr>
                        <?php } else { ?>
                            <?php
//                                            echo $venta->ven_lot_ids.'<br>';
                            $vlotes = FUNCIONES::lista_bd_sql("select * from venta_lote where vlot_ven_id='$venta->ven_id'");
                            $numl = count($vlotes);
                            ?>
            <?php for ($i = 0; $i < $numl; $i++) { ?>
                <?php $vlot = $vlotes[$i]; ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo $vlot->vlot_concepto; ?></td>
                                    <td><?php echo $vlot->vlot_superficie; ?> m2</td>
                                    <td><?php echo $vlot->vlot_metro; ?></td>
                                    <td><?php echo $vlot->vlot_valor; ?></td>
                <?php if ($i == 0) { ?>
                                        <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_superficie, 2); ?></td>
                                        <?php if ($venta->ven_decuento) { ?>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_decuento, 2); ?></td>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_monto, 2); ?></td>
                                        <?php } ?>
                                        <?php if ($venta->ven_res_anticipo > 0) { ?>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_res_anticipo, 2); ?></td>
                                        <?php } ?>
                                        <?php if ($venta->ven_monto_intercambio > 0) { ?>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_monto_intercambio, 2); ?></td>
                                            <?php } ?>
                                        <?php if ($venta->ven_venta_id > 0) { ?>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>">
                                            <?php echo number_format($venta->ven_venta_pagado, 2); ?>
                                            </td>
                    <?php } ?>
                                        <?php if ($venta->ven_tipo == 'Credito') { ?>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_monto_efectivo, 2); ?></td>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo number_format($venta->ven_cuota, 2); ?></td>
                                            <td style="vertical-align: top;" rowspan="<?php echo $numl; ?>"><?php echo $venta->ven_plazo; ?></td>
                                    <?php } ?>
                                <?php } ?>
                                </tr>
            <?php } ?>
                <?php } ?>

                    </tbody>
                </table>
        <?php if ($venta->ven_venta_id > 0) { ?>
                    <script>
                        $('#btn_ver_venta').click(function() {
                            window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id=<?php echo $venta->ven_venta_id ?>', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                        });
                    </script>
                <?php } ?>
                <?php
            }

            function cabecera_venta_producto($venta) {
//            $myday = setear_fecha(strtotime($venta->ven_fecha));
//            $desc_mod_venta = ($venta->ven_multinivel == 'si') ? " - MLM" : " - TRAD";
                ?>
                <br><br>
                <center>

                    <?php
                    $venta = FUNCIONES::objeto_bd_sql("select * from venta_producto 
                inner join urbanizacion_producto on (vprod_prod_id=uprod_id)
                where vprod_id='$venta->vprod_id'");
                    ?>
                    <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>                                                                                    
                                <th>Casa</th>                                                        
                                <th>Precio Casa</th>                                                                                    
                                <th>Descuento</th>                                                            
                                <th>Monto</th>               
                                <th>Cuota Inicial</th>    
                                <th>Saldo a Financiar</th>
                                <?php if ($venta->vprod_tipo == 'Credito') { ?>                                
                                    <th>Monto Cuota</th>
                                    <th>Plazo</th>
        <?php } ?>

                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td><?php echo $venta->uprod_nombre; ?></td>
                                <td><?php echo number_format($venta->vprod_precio, 2, '.', ','); ?></td>                            
                                <td><?php echo number_format($venta->vprod_descuento, 2, '.', ','); ?></td>                            
                                <td><?php echo number_format($venta->vprod_monto, 2, '.', ','); ?></td>
                                <td><?php echo number_format($venta->vprod_anticipo, 2, '.', ','); ?></td>
                                <td><?php echo number_format($venta->vprod_monto_efectivo, 2, '.', ','); ?></td>
                                <?php
                                if ($venta->vprod_tipo == 'Credito') {
                                    $sql_cuo = "select * from interno_deuda_producto
                                    where idpr_tabla='venta_producto' and idpr_tabla_id='$venta->vprod_id'
                                    and idpr_num_correlativo=1 and idpr_estado in ('Pendiente','Pagado')";
                                    $cuota = FUNCIONES::objeto_bd_sql($sql_cuo);

                                    $sql_plazo = "select count(idpr_id)as campo 
                                from interno_deuda_producto where idpr_estado in ('Pendiente','Pagado')
                                and idpr_tabla='venta_producto' and idpr_tabla_id='$venta->vprod_id'";
                                    $plazo = FUNCIONES::atributo_bd_sql($sql_plazo);
                                    ?>
                                    <td><?php echo number_format($cuota->idpr_monto, 2, '.', ','); ?></td>
                                    <td><?php echo $plazo; ?></td>
                                    <?php
                                }
                                ?>

                            </tr>
                        </tbody>
                    </table>

                    <?php
                }
            function cabecera_venta_producto_new_version($venta, $tipo_compra) {
            $myday = setear_fecha(strtotime($venta->ven_fecha));
            $desc_mod_venta = ($venta->ven_multinivel == 'si') ? " - MLM" : " - TRAD";
                ?>
                <br><br>
                <center>

                    <table border='0' style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                    <tr>
                        <td width="33%">
                            <strong><?php echo _nombre_empresa; ?></strong><BR>
                        </td>
                        <td width="33%"><center><p align="center" ><strong><h3>NOTA DE VENTA</h3></strong></p></center></td>
                    <td><div align="right"><img src="imagenes/micro.png"  /></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Nro. de Venta: </strong> <?php echo $venta->ven_id . $desc_mod_venta; ?> <br>
                            <strong>Nro. Adj.: </strong> <?php echo $venta->ven_numero; ?> <br>
                            <?php
                            if ($venta->ven_int_id <> 0) {
                                $nombre_cliente = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id=$venta->ven_int_id");
                                ?>
                                <strong>Nombre: </strong> <?php echo $nombre_cliente; ?> <br>
                                <?php
                            }
                            ?>
                            <?php
                            if ($venta->ven_co_propietario <> 0) {
                                $persona = $this->obtener_nombrepersona_interno($venta->ven_co_propietario);
                                ?>						
                                <strong>Co-Propietario: </strong> <?php echo $persona; ?> <br>						
                                <?php
                            }
                            ?>				

                            <strong>Tipo: </strong> <?php echo $venta->ven_tipo; ?> <br>
                            <strong>Interes: </strong> <?php echo ($venta->ven_val_interes * 1 ) . " %"; ?> <br>
                            <strong>Tipo de Pago: </strong> <?php echo $venta->ven_tipo_pago; ?> <br>
                            <strong>Moneda: </strong> <?php
                    $moneda = $venta->ven_moneda;
                    if ($moneda == "1")
                        echo "Bolivianos";
                    else
                        echo "Dolares";
                            ?> <br>
                            <?php
                            if ($venta->ven_vdo_id > 0) {
                                $vendedor1 = $this->obtener_nombre_vendedor($venta->ven_vdo_id);
                            } else if ($venta->ven_promotor) {
                                $vendedor1 = $venta->ven_promotor;
                            }
                            ?>
                            <strong>Vendedor: </strong> <?php echo $vendedor1; ?> <br>
                            <strong>Lugar: </strong> <?php echo $venta->ven_ubicacion; ?> <br>
                            <strong>Sucursal: </strong> <?php echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$venta->ven_suc_id'"); ?> <br><br>
                            <?php
                            $sql_fv = "select vpag_fecha_valor as campo from venta_pago where vpag_ven_id=$venta->ven_id and vpag_estado='Activo' order by vpag_id desc";
                            $u_fecha_valor = FUNCIONES::atributo_bd_sql($sql_fv);
                            if (!$u_fecha_valor) {
                                $u_fecha_valor = $venta->ven_fecha;
                            }
                            ?>

                            <strong>U. Fecha Valor: </strong> <?php echo FUNCIONES::get_fecha_latina($u_fecha_valor); ?> <br><br>

                            <?php if ($venta->ven_observacion <> '') { ?>
                                <strong>Observaci&oacute;n: </strong> <?php echo $venta->ven_observacion; ?> <br><br>
                            <?php } ?>					

        <?php if ($venta->ven_observacion_firma <> '') { ?>
                                <strong>Observaciones Firma Contrato: </strong> <?php echo $venta->ven_observacion_firma; ?> <br><br>
        <?php } ?>					

                        </td>
                        <td align="right">
                            <strong>Fecha: </strong> <?php echo $myday; ?> <br>
                            <!--<strong>Usuario: </strong> <?php // echo $this->nombre_persona($venta->ven_usu_id);  ?> <br>-->
                            <strong>Usuario: </strong> <?php echo $this->nombre_persona(FUNCIONES::get_usuario($venta)); ?> <br>
							<?php
							if ($_SESSION[id] == 'admin') {
								?>								
								<strong>ESTADO: </strong> <?php echo strtoupper($venta->ven_estado); ?> <br>
								<strong>COSTO: </strong> <?php echo strtoupper($venta->ven_costo); ?> <br>
								<strong>COSTO CUB.: </strong> <?php echo strtoupper($venta->ven_costo_cub); ?> <br>
								<strong>COSTO PAG.: </strong> <?php echo strtoupper($venta->ven_costo_pag); ?> <br>
								<?php
							}
							?>
                        </td>
                    </tr>

                </table>
                    <?php
//                    $venta = FUNCIONES::objeto_bd_sql("select * from venta_producto 
//                inner join urbanizacion_producto on (vprod_prod_id=uprod_id)
//                where vprod_id='$venta->vprod_id'");
                    $monto_producto = 0;
                    $cuota_inicial = 0;
                    $descuento_prod = 0;
                    if ($tipo_compra == 'conjunta') {
                        $urb_prod = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$venta->ven_prod_id'");
                        $monto_producto = $venta->ven_monto_producto;
                        $cuota_inicial = $venta->ven_res_anticipo;
                        $descuento_prod = $venta->ven_descuento_producto;
                        $ci_prod = 0;
                        $valor_producto = $venta->ven_valor_producto;
                    } else {
                        $urb_prod = FUNCIONES::obtener_producto($venta->ven_id);
                        $monto_producto = $urb_prod->vprod_monto;
                        $cuota_inicial = $urb_prod->vprod_anticipo + $venta->ven_res_anticipo;
                        $descuento_prod = $urb_prod->vprod_descuento;
                        $ci_prod = $urb_prod->vprod_anticipo;
                        $valor_producto = $urb_prod->vprod_precio;
                        
                        
                        $sql_cuo = "select * from interno_deuda_producto
                        where idpr_tabla='venta_producto' and idpr_tabla_id='$urb_prod->vprod_id'
                        and idpr_num_correlativo=1 and idpr_estado in ('Pendiente','Pagado')";
                        
                        $obj_cuota_vprod = FUNCIONES::objeto_bd_sql($sql_cuo);
                        $cuota_vprod = $obj_cuota_vprod->idpr_monto;

                        $sql_plazo = "select count(idpr_id)as campo 
                        from interno_deuda_producto where idpr_estado in ('Pendiente','Pagado')
                        and idpr_tabla='venta_producto' and idpr_tabla_id='$urb_prod->vprod_id'";
                        
                        $plazo_vprod = FUNCIONES::atributo_bd_sql($sql_plazo);
                    }
                    
                    $desc_lote = FUNCIONES::get_concepto($venta->ven_lot_id);
                    $desc_casa = $urb_prod->uprod_nombre;
                    $val_m2_prod = $valor_producto / $urb_prod->uprod_superficie;
//                    $monto_total = ($venta->ven_monto + $monto_producto) - ($venta->ven_decuento + $descuento_prod);
                    $monto_total = $venta->ven_monto + $monto_producto;
                    if ($_SESSION[id] == 'admin') {
                        echo "<p style='color:white;'>ven_monto:$venta->ven_monto - monto_producto:$monto_producto - ven_descuento:$venta->ven_decuento - desc_prod:$descuento_prod</p>";
                    }
                    $saldo_financiar = $monto_total - $cuota_inicial;
                    
                    $cuota_venta = $venta->ven_cuota;
//                    $cuota_vprod = $
                    ?>
                    <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>                                                                                    
                                <th>Proyecto</th>                                                        
                                <th>Descripcion</th>                                                                                    
                                <th>Superficie</th>                                                            
                                <th>Valor m2</th>                                                            
                                <th>Monto</th>               
                                <th>Descuento</th>               
                                <th>Monto Total</th>      
								<?php 
								if ($venta->ven_venta_id > 0) {
								?>
								<th>
                                    Pagado Anteriormente
                                    <img id="btn_ver_venta" style="display: inline; cursor: pointer; "src="images/b_browse.png" width="16">
                                </th>
								<?php
								}
								?>
                                <th>Cuota Inicial</th>    
                                <th>Saldo a Financiar</th>                                
                                <th>Monto Cuota</th>
                                <th>Plazo</th>                                
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <?php
                                $rowspan = ($tipo_compra == 'conjunta') ? 'rowspan="2"': '';
                                ?>
                                <td>Terreno</td>
                                <td><?php echo $desc_lote; ?></td>                            
                                <td><?php echo number_format($venta->ven_superficie, 2, '.', ','); ?></td>                            
                                <td><?php echo number_format($venta->ven_metro, 2, '.', ','); ?></td>
                                <td><?php echo number_format($venta->ven_valor, 2, '.', ','); ?></td>
                                <td><?php echo number_format($venta->ven_decuento, 2, '.', ','); ?></td>
                                <td rowspan="2"><?php echo number_format($monto_total, 2, '.', ','); ?></td>
								<?php if ($venta->ven_venta_id > 0) { ?>
                                    <td rowspan="2">
                                    <?php echo number_format($venta->ven_venta_pagado, 2); ?>
                                    </td>
								<?php } ?>
                                <td><?php echo number_format($venta->ven_res_anticipo, 2, '.', ','); ?></td>
                                <td rowspan="2"><?php echo number_format($saldo_financiar, 2, '.', ','); ?></td>
                                <td <?php echo $rowspan;?>><?php echo number_format($cuota_venta, 2, '.', ','); ?></td>
                                <td <?php echo $rowspan;?>><?php echo number_format($venta->ven_plazo, 2, '.', ','); ?></td>
                                <?php
                                
                                ?>

                            </tr>
                            
                            <tr>
                                <?php
                                
                                ?>
                                <td>Casa</td>
                                <td><?php echo $desc_casa; ?></td>                            
                                <td><?php echo number_format($urb_prod->uprod_superficie, 2, '.', ','); ?></td>                            
                                <td><?php echo number_format($val_m2_prod, 2, '.', ','); ?></td>
                                <td><?php echo number_format($valor_producto, 2, '.', ','); ?></td>
                                <td><?php echo number_format($descuento_prod, 2, '.', ','); ?></td>
                                <!--<td>&nbsp;</td>-->
                                <td><?php echo number_format($ci_prod, 2, '.', ','); ?></td>
                                <!--<td>&nbsp;</td>-->
                                <?php
                                if ($tipo_compra == 'posterior') {
                                ?>
                                <td><?php echo number_format($cuota_vprod, 2, '.', ','); ?></td>
                                <td><?php echo number_format($plazo_vprod, 2, '.', ','); ?></td>
                                <?php
                                }
                                ?>

                            </tr>
                        </tbody>
                    </table>
					<?php if ($venta->ven_venta_id > 0) { ?>
                    <script>
                        $('#btn_ver_venta').click(function() {
                            window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id=<?php echo $venta->ven_venta_id ?>', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                        });
                    </script>
                <?php } ?>
                    <?php
                }

                function barra_opciones($venta, $tarea = "", $print = false, $vpag_id = 0) {

                    $venta_id = $venta->ven_id;
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
                    $permisos = "";

                    if ($vpag_id > 0) {
                        if ($this->verificar_permisos('CUPON')) {
                            //                                $permisos.="<td><a title='CUPON' href='gestor.php?mod=venta&tarea=CUPON'><img width='18' src='images/cupon_48.png'></a></td>";
                            $permisos.="<td><a title='CUPON' href='#' id='a_cupon'><img width='18' src='images/cupon_48.png'></a></td>";
                        }
                    }

                    if ($this->verificar_permisos('ACCEDER') && $tarea != 'ACCEDER' && $_SESSION[usu_gru_id] != 'CAJERO') {
                        $permisos.="<td><a title='LISTADO' href='gestor.php?mod=venta&tarea=ACCEDER'><img width='18' src='images/listado.png'></a></td>";
                    }
                    if ($this->verificar_permisos('VER') && $tarea != 'VER') {
                        $permisos.="<td><a title='VER' href='gestor.php?mod=venta&tarea=VER&id=$venta_id'><img width='18' src='images/ver.png'></a></td>";
                    }
                    if ($this->verificar_permisos('SEGUIMIENTO') && $tarea != 'SEGUIMIENTO' && $venta->ven_tipo == 'Credito') {
                        $permisos.="<td><a title='SEGUIMIENTO' href='gestor.php?mod=venta&tarea=SEGUIMIENTO&id=$venta_id'><img width='18' src='images/seguimiento.png'></a></td>";
                    }
                    if ($this->verificar_permisos('EXTRACTOS') && $tarea != 'EXTRACTOS' && $venta->ven_tipo == 'Credito') {
                        $permisos.="<td><a title='EXTRACTOS' href='gestor.php?mod=venta&tarea=EXTRACTOS&id=$venta_id'><img width='18' src='images/extractos.png'></a></td>";
                    }

                    if ($this->verificar_permisos('PAGOS') && $tarea != 'PAGOS' && $venta->ven_estado == 'Pendiente') {
                        $permisos.="<td><a title='PAGOS' href='gestor.php?mod=venta&tarea=PAGOS&id=$venta_id'><img width='18' src='images/cuenta.png'></a></td>";
                    }

                    if ($this->verificar_permisos('AUTORIZACION') && $tarea != 'AUTORIZACION' && $venta->ven_estado == 'Pendiente') {
                        $permisos.="<td><a title='AUTORIZACION DE PAGOS' href='gestor.php?mod=venta&tarea=AUTORIZACION&id=$venta_id'><img width='18' src='images/autorizacion.png'></a></td>";
                    }
                    if ($this->verificar_permisos('REFORMULAR') && $tarea != 'REFORMULAR' && $venta->ven_estado == 'Pendiente') {
                        $permisos.="<td><a title='REFORMULAR' href='gestor.php?mod=venta&tarea=REFORMULAR&id=$venta_id'><img width='18' src='images/reformular.png'></a></td>";
                    }
                    if ($this->verificar_permisos('FECHA VALOR') && $tarea != 'FECHA VALOR' && $venta->ven_estado == 'Pendiente') {
                        $permisos.="<td><a title='FECHA VALOR' href='gestor.php?mod=venta&tarea=FECHA VALOR&id=$venta_id'><img width='18' src='images/edit_date.png'></a></td>";
                    }
                    if ($tarea == 'SEGUIMIENTO' || $tarea == 'EXTRACTOS') {
                        if ($this->verificar_permisos('IMPORTAR EXCEL') && $tarea != 'IMPORTAR EXCEL' && $venta->ven_estado == 'Pendiente') {
                            $num_pagos = FUNCIONES::atributo_bd_sql("select count(*) as campo from venta_pago where vpag_ven_id='$venta_id' and vpag_estado='Activo'");
                            $num_cuotas = FUNCIONES::atributo_bd_sql("select count(*) as campo  from interno_deuda where ind_tabla_id='$venta_id' and ind_tabla='venta' and ind_estado in ('Pendiente','Pagado')");
                            if ($num_cuotas == 0 && $num_pagos == 0) {
                                $permisos.="<td><a title='IMPORTAR PLAN DE PAGO Y PAGOS' href='gestor.php?mod=venta&tarea=IMPORTAR EXCEL&id=$venta_id'><img width='18' src='images/importar_excel.png'></a></td>";
                            }
                        }
                    }
                    if ($print) {
                        $permisos.='
                            <td>
                                <a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                                    c.document.write(' . $extra1 . ');
                                    var dato = document.getElementById(' . $pagina . ').innerHTML;
                                    c.document.write(dato);
                                    c.document.write(' . $extra2 . '); c.document.close();
                                    ">
                                    <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                                </a>
                            </td>
                    ';
                    }

                    if ($tarea != 'ACCEDER' && $_SESSION[usu_gru_id] == 'CAJERO') {
                        $permisos.="<td><a title='LISTADO' href='gestor.php?mod=caja&tarea=ACCEDER'><img width='18' src='images/back.png'></a></td>";
                    }

                    echo '  <div id="box_barra_opciones">
                        <table id="barra_opciones" align=right border=0>
                            <tr>
                                ' . $permisos . '
                            </tr>
                        </table>
                    </div>
                            ';
                }

                function nota_de_venta($venta_id) {
                    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$venta_id");
                    $this->barra_opciones($venta, 'VER', true);
                    ?>
                    <div id="contenido_reporte" style="clear:both;">
                        <?php
                        
                        if ($venta->ven_prod_id > 0) {
                            $this->cabecera_venta_producto_new_version($venta, 'conjunta');
                        } else {
                            $vprod = FUNCIONES::obtener_producto($venta->ven_id);
                            if ($vprod) {
                                $this->cabecera_venta_producto_new_version($venta, 'posterior');
                            } else {
                                $this->cabecera_venta($venta);
                            }
                        }
                        
//                        $this->cabecera_venta($venta);

                        $sql = "SELECT ind_id,ind_interes,ind_capital,ind_monto,ind_saldo,ind_moneda,ind_concepto,
                ind_fecha_programada,ind_fecha_pago,int_nombre,int_apellido,ind_estado,
                ind_monto_pagado,ind_num_correlativo,ind_capital_pagado, ind_form_pagado
            FROM 
            interno_deuda 
            inner join interno on (ind_int_id=int_id)
            where
            (ind_estado='Pendiente' or ind_estado='Pagado') and
            ind_tabla='venta' and
            ind_tabla_id='$venta_id' order by ind_id asc
            ";
                        $conec = new ADO();
                        $conec->ejecutar($sql);

                        $num = $conec->get_num_registros();

                        if ($num > 0) {
                            ?>

                            <br/><h3>PLAN DE PAGO</h3>
                            <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>

                                        <th>Nro Cuota</th>                                      
                                        <th>Fecha Programada</th>                                        
                                        <th>Interes</th>                                        
                                        <th>Capital</th>                                        
                                        <th>Cuota</th>                                        
                                        <th>Form</th>                                        
                                        <th>Monto a Pagar</th>
                                        <th>Estado</th>
                                        <th>Fecha de Pago</th>
                                        <th>Capital Pag.</th>
                                        <th>Saldo Capital</th>
                                    </tr>		

                                </thead>
                                <tbody>
                                    <tr>
                                        <?php
                                        $conversor = new convertir();
//					$form=  FUNCIONES::atributo_bd_sql("select urb_val_form as campo from urbanizacion where urb_id='$venta->ven_urb_id'");
                                        $form = $venta->ven_form;
                                        for ($i = 0; $i < $num; $i++) {
                                            $objeto = $conec->get_objeto();

                                            echo '<tr>';



                                            echo "<td>";
                                            //echo $objeto->ind_concepto;
                                            if ($objeto->ind_num_correlativo > 0) {
                                                echo "$objeto->ind_num_correlativo";
                                            } elseif ($objeto->ind_num_correlativo == 0) {
                                                echo "Cuota Inicial";
                                            }

                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            if ($objeto->ind_fecha_programada <> '0000-00-00')
                                                echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            echo $objeto->ind_estado == 'Pagado' ? $objeto->ind_interes_pagado : $objeto->ind_interes;
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            echo $objeto->ind_capital_pagado > 0 ? $objeto->ind_capital_pagado : $objeto->ind_capital;
                                            echo "&nbsp;</td>";
                                            echo "<td>";

                                            echo $objeto->ind_monto_pagado > 0 ? $objeto->ind_monto_pagado : $objeto->ind_monto;
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            if ($objeto->ind_num_correlativo >= 0) {
                                                if ($objeto->ind_capital_pagado > 0) {
                                                    echo $objeto->ind_form_pagado;
                                                } else {
                                                    echo $form;
                                                }
                                            }
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            if ($objeto->ind_num_correlativo >= 0) {
                                                if ($objeto->ind_monto_pagado > 0) {
                                                    echo $objeto->ind_monto_pagado + $objeto->ind_form_pagado;
                                                } else {
                                                    echo round($objeto->ind_interes + $objeto->ind_capital + $form, 2);
                                                }
                                            }
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            echo $objeto->ind_estado;
                                            echo "&nbsp;</td>";
                                            echo "<td>";
                                            if ($objeto->ind_fecha_pago <> '0000-00-00' && $objeto->ind_fecha_pago != '')
                                                echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);
                                            echo "&nbsp;</td>";

                                            echo "<td>";
                                            echo $objeto->ind_capital_pagado;
                                            echo "&nbsp;</td>";

                                            echo "<td>";
//                                                                        echo "$objeto->ind_estado--$objeto->ind_saldo_final--";
                                            echo $objeto->ind_estado == 'Pagado' ? $objeto->ind_saldo_final : $objeto->ind_saldo;
                                            echo "&nbsp;</td>";



                                            echo "</tr>";

                                            $conec->siguiente();
                                        }
                                        ?>	
                                </tbody>
                            </table>
                            <?php
                        }
                        ?>
                        </br></br></br></br></br></br>
                        <table border="0"  width="70%" style="font-size:12px;">
                            <?php
                            if ($tipo == 'Credito') {
                                ?>
                                <!--
                                        <tr>
                                                <td colspan="2" width="50%" align ="center">
                                                <b>NOTA</b>.- El Sr. <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?> (Cliente) dejo <?php echo $cuota_ini; ?> <?php if ($moneda == "1")
                        echo "Bolivianos";
                    else
                        echo "Dolares";
                    ?>  (importe de la cuota inicial) por el terreno (<?php echo $terreno; ?>). <br/>En caso de no pagar dos cuotas pierde los dep?sitos y el lote sin necesidad de Orden Judicial.</br></br></br></br></br>
                                                </td>
                                        </tr>
                                -->
                                <?php
                            }
                            else {
                                ?>
                                </br></br></br>
            <?php
        }
        ?>
                            <tr>
                                <td width="50%" align ="center">-------------------------------------</td>
                                <td width="50%" align ="center">-------------------------------------</td>
                            </tr>
                            <tr>
                                <td align ="center"><strong>VENDEDOR</strong></td>
                                <td align ="center"><strong>COMPRADOR</strong></td>
                            </tr>
                        </table>

                </center>
                <br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
        </div><br>
        <?php
    }

    function get_concepto($lot_id) {
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
        $des = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
        return $des;
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

    function nombre_persona($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function anular() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id] and ven_multinivel='si'");
        if ($venta) {
            include_once 'clases/mlm.class.php';
            $resp = MLM::anular_afiliado($venta);

            if (!$resp->exito) {
                $mensaje = $resp->mensaje;
                $tipo = 'Error';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
                return;
            }
        }
//                return;

        $conec = new ADO();
        $sql = "select * from venta_pago where vpag_ven_id='" . $_GET['id'] . "' and vpag_estado='Activo' ";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        if ($num > 0) {
            $mensaje = 'La venta no puede ser anulada, por que ya tiene pagos registrados!!!';
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        include_once 'clases/registrar_comprobantes.class.php';
        $bool = COMPROBANTES::anular_comprobante('venta', $_GET[id]);
        if (!$bool) {
            $mensaje = "El pago de la mora no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $sql = "select * from venta where ven_id='" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $lote = $objeto->ven_lot_id;

        if ($objeto->ven_estado <> 'Anulado' && $objeto->ven_estado <> 'Retenido') {
            if ($objeto->tipo == 'Credito') {
                $sql = "update interno_deuda set 
                                ind_estado='Anulado'
                                where 
                                ind_tabla='venta' and
                                ind_tabla_id = '" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
            }

            if ($objeto->ven_res_id > 0) {
                $sql = "update lote set  lot_estado='Reservado' where lot_id = '" . $lote . "'";
                $conec->ejecutar($sql);
                $sql_up = "update reserva_terreno set res_estado='Habilitado' where res_id=$objeto->ven_res_id";
                $conec->ejecutar($sql_up);
            } else {
                $sql = "update lote set  lot_estado='Disponible' where lot_id = '" . $lote . "'";
                $conec->ejecutar($sql);
            }
            $sql = "update venta set ven_estado='Anulado' where ven_id = '" . $_GET['id'] . "'";
            $conec->ejecutar($sql);
            $this->anular_comision($conec);
            $mensaje = 'Venta Anulada Correctamente!!!';
            $tipo = 'Correcto';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
        } else {
            $mensaje = 'La venta no puede ser Anulada por que ya fue Anulada o Retenida anteriormente.';
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
        }
    }

    function anular_comision($conec) {
        $sql = "select * from comision 
            where com_ven_id = '" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        if ($num > 0) {
            $objeto = $conec->get_objeto();
            if ($objeto->com_estado == 'Pendiente') {
                $sql = "update comision set 
                        com_estado='Anulado'
                        where com_ven_id = '" . $_GET['id'] . "'";
                $conec->ejecutar($sql);
            }
            COMPROBANTES::anular_comprobante('comision', $objeto->com_id);
        }
    }

    function datos_venta(&$ci, &$im) {
        $conec = new ADO();
        $sql = "select par_cuota_inicial,par_interes_mensual   
		from 
		ad_parametro ";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $ci = $objeto->par_cuota_inicial;
        $im = $objeto->par_interes_mensual;
    }

    function existe_monto_parcial($cuota, &$monto_parcial) {
        $conec = new ADO();

        $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,ind_int_id,ind_cue_id,ind_cco_id,ind_tabla_id 
			FROM interno_deuda
			where ind_id='" . $cuota . "'
			";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $monto_parcial = $objeto->ind_monto_parcial;

        if ($monto_parcial > 0) {
            return true;
        } else {
            return false;
        }
    }

    function obtener_tipoventa_tipolote_urbanizacion($id_venta, &$tipo_venta, &$tipo_lote, &$urb_id) {
        // Verifico si es (Casa o Lote) y (Contado o Credito) 


        $conec = new ADO();

        $sql = "SELECT urb_id,lot_tipo,ven_tipo from urbanizacion
			inner join uv on (uv_urb_id=urb_id)
			inner join lote on (lot_uv_id=uv_id)
			inner join venta on (ven_lot_id=lot_id)
			where ven_id=$id_venta";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $tipo_venta = $objeto->ven_tipo;

        $tipo_lote = $objeto->lot_tipo;

        $urb_id = $objeto->urb_id;
    }

    function imprimir_documento() {
        $this->obtener_tipoventa_tipolote_urbanizacion($_GET['id'], $tipo_venta, $tipo_lote, $urb_id);
//		echo $tipo_venta. ' - '.$tipo_lote.' - '.$urb_id;
        if ($tipo_venta == 'Contado' && $tipo_lote == 'Lote') {
            $s = 'contratos/' . $urb_id . '_lote_contado.php';
        } elseif ($tipo_venta == 'Credito' && $tipo_lote == 'Lote') {
            $s = 'contratos/' . $urb_id . '_lote_credito.php';
        } elseif ($tipo_venta == 'Contado' && $tipo_lote == 'Vivienda') {
            $s = 'contratos/' . $urb_id . '_vivienda_contado.php';
        } elseif ($tipo_venta == 'Credito' && $tipo_lote == 'Vivienda') {
            $s = 'contratos/' . $urb_id . '_vivienda_credito.php';
        }

        echo "<p>$s</p>";
        if (file_exists($s)) {
            include($s);
        } else {
            $mensaje = "No existe plantilla de contrato para el proyecto especificado.";
            $link = "$this->link?mod=$this->modulo&tarea=ACCEDER";
            $this->formulario->ventana_volver($mensaje, $link, "", "Error");
        }
    }

    function num2letras($num, $fem = false, $dec = true) {

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

////////////////////////////////-------------- MODULO DESBLOQUEO CUOTAS   ----------------------/////////////////////////////////////

    function obtener_idpersona_usuario($usu_id) {
        $conec = new ADO();

        $sql = "select * from ad_usuario where usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_per_id;
    }

    function obtener_nombre_vendedor($vdo_id) {
        $conec = new ADO();

        $sql = "select 
		int_id,int_nombre,int_apellido 
		from 
		vendedor inner join interno on(vdo_int_id=int_id)
		where 
		vdo_id=$vdo_id";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function obtener_nombrepersona_interno($int_id) {
        $conec = new ADO();

        $sql = "select int_id,int_nombre,int_apellido from interno where int_id=$int_id";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    //CAMBIAR PROPIETARIO
    function cambio_propietario() {

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        $this->formulario->dibujar_titulo("CAMBIO DE PROPIETARIO");
        ?>
        <div id="Contenedor_NuevaSentencia">
            <div id="FormSent">
                <div class="Subtitulo">Datos Generales</div>
                <div id="ContenedorSeleccion">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Nro Venta:</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_id; ?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Concepto:</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_concepto; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if ($_GET[acc] == 'anular') {
            $this->anular_cambio_propietario($venta);
            $this->formulario_cambio_propietario($venta);
            $this->listado_cambios_propietario($venta);
        } else {
            if ($this->validar_cambio_propietario()) {
                $this->guardar_cambio_propietario($venta);
            }
            $lista = FUNCIONES::objetos_bd_sql("select * from venta_propietarios_historial where vph_ven_id=$venta->ven_id and  vph_estado='Pendiente'");
            if (!$lista->get_num_registros() > 0) {
                $this->formulario_cambio_propietario($venta);
            }
            $this->listado_cambios_propietario($venta);
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

    function guardar_cambio_propietario($venta) {
        $conec = new ADO();
        $propietario_actual = $venta->ven_int_id;
        $co_propietario_actual = $venta->ven_co_propietario;
        if ($venta->ven_tipo == 'Credito' || $venta->ven_tipo == 'Contado') {
            //            $sql = "update venta set ven_int_id='$_POST[int_id]' where ven_id=" . $_GET['id'];
            //            $conec->ejecutar($sql);
            //            $sql = "update interno_deuda set ind_int_id='$_POST[int_id]' where ind_tabla='venta' and ind_tabla_id=" . $_GET['id'] . " and ind_estado='Pendiente'";
            //            $conec->ejecutar($sql);
            if ($_POST['copropietario_id'] <> '') {
                //                $sql = "update venta set ven_co_propietario='$_POST[copropietario_id]' where ven_id=" . $_GET['id'];
                //                $conec->ejecutar($sql);
            } else {
                $_POST['copropietario_id'] = 0;
                //                $sql = "update venta set ven_co_propietario='$_POST[copropietario_id]' where ven_id=" . $_GET['id'];
                //                $conec->ejecutar($sql);
            }
            $fecha_cambio = FUNCIONES::get_fecha_mysql($_POST[fecha]);
            $costo = $_POST[costo];
            $moneda = 2;
            //            $costo=  FUNCIONES::ad_parametro('par_cambio_titular');
            $int_id_new = $_POST[int_id];
            $sql = "insert into venta_propietarios_historial(
                                vph_int_id,vph_int_id_nuevo,vph_cop_id,vph_cop_id_nuevo,vph_fecha_cambio,
                                vph_observacion,vph_usu_id,vph_ven_id,vph_costo,vph_estado
                        ) values(
                                '$propietario_actual','$int_id_new','$co_propietario_actual','$_POST[copropietario_id]','$fecha_cambio',
                            '$_POST[vph_observacion]','$_SESSION[id]','$venta->ven_id','$costo','Pendiente'
                        )";
            $conec->ejecutar($sql, true, true);
            $llave = ADO::$insert_id;

            /// INSERTAR COBRO DE CAMBIO DE TITULAR
            $propietario = FUNCIONES::interno_nombre($_POST[int_id]);
            $co_propietario = "";
            if ($_POST[copropietario_id] > 0) {
                $co_propietario = 'y ' . FUNCIONES::interno_nombre($_POST[copropietario_id]);
            }
            $fecha_cre = date('Y-m-d');
            $concepto = "Venta Nro. $venta->ven_id a nombre de $propietario $co_propietario, $venta->ven_concepto";
            $extra_pago_id = 1;
            $sql_ins_cobro = "insert into extra_pago(
                                    epag_ept_id, epag_tabla, epag_tabla_id, epag_int_id, epag_urb_id, epag_modulo, epag_concepto,
                                    epag_nota, epag_fecha_programada, epag_monto, epag_moneda, epag_monto_detalle, epag_fecha_cre,
                                    epag_usu_cre, epag_estado
                                )values(
                                    '$extra_pago_id','ven_prop_his','$llave','$int_id_new','$venta->ven_urb_id','venta-$venta->ven_id','$concepto',
                                    '$_POST[vph_observacion]','$fecha_cambio','$costo','$moneda','costo:$costo','$fecha_cre',
                                    '$_SESSION[id]','Pendiente'
                                )";
            //epag_recibo, epag_fecha_pago, epag_usu_pago, epag_fecha_mod
            $conec->ejecutar($sql_ins_cobro);                                    
            $this->mensaje = "Se realizo el cambio de Propietario";
            $this->tipo_mensaje = 'Correcto';
        } else {
            $this->mensaje = "No puede realizar Cambio de Propietario en una Venta al Contado";
            $this->tipo_mensaje = 'Error';
        }
    }

    function formulario_cambio_propietario($venta) {
        $conec = new ADO();

        if ($venta->ven_estado == 'Pendiente' or $venta->ven_estado == 'Pagado') {
            $propietario_actual = $venta->ven_int_id;
            $co_propietario_actual = $venta->ven_co_propietario;
            if ($propietario_actual <> 0) {
                $nombre_propietario_actual = FUNCIONES::interno_nombre($propietario_actual);
            }
            if ($co_propietario_actual <> 0) {
                $nombre_copropietario_actual = FUNCIONES::interno_nombre($co_propietario_actual);
            }

            $sql = "select * from interno";
            $conec->ejecutar($sql);
            $nume = $conec->get_num_registros();
            $personas = 0;
            if ($nume > 0) {
                $personas = 1;
            }

            if ($venta->ven_tipo == 'Credito' || $venta->ven_tipo == 'Contado') {
                $url = $this->link . '?mod=' . $this->modulo . "&tarea=CAMBIAR PROPIETARIO&id=$venta->ven_id";

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
                                        <input type="hidden" name="ven_tit_act" id="ven_tit_act" value="<?php echo $venta->ven_int_id; ?>">
                                        <input type="hidden" name="ven_cop_act" id="ven_cop_act" value="<?php echo $venta->ven_co_propietario; ?>">
                                        <span style="float:right;"><b>Propietario Actual:</b> <?php echo $nombre_propietario_actual; ?><br />
                <?php if ($co_propietario_actual <> 0) { ?>
                                                <b>Co-Propietario Actual:</b> <?php echo $nombre_copropietario_actual; ?>
                                        <?php } ?>
                                        </span>
                                    </center>
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                                    <div id="CajaInput">
                <?php if ($personas <> 0) { ?>
                                            <input name="int_id" id="int_id" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['int_id'] ?>" size="2">
                                            <input name="int_nombre_persona" readonly="readonly" id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
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
                                    <div class="Etiqueta" >Co-Propietario</div>
                                    <div id="CajaInput">
                <?php
                if ($personas <> 0) {
                    ?>
                                            <input name="copropietario_id" id="copropietario_id" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['copropietario_id'] ?>" size="2">
                                            <input name="copropietario_nombre" readonly="readonly" id="copropietario_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['copropietario_nombre'] ?>" size="40">
                                            <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                                <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR"></a>
                                            <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar&mt=set_valor_copropietario" href="javascript:void(0)">
                                                <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                            </a>
                                            <a style="float:left; margin:0 0 0 7px;float:right;display:inline;" href="#" onClick="reset_interno();">
                                                <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR"></a>
                    <?php
                } else {
                    echo 'No se le asigno ning?na personas, para poder cargar las personas.';
                }
                ?>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Observacion</div>
                                    <div id="CajaInput">
                                        <textarea name="vph_observacion" id="vph_observacion" rows="3" cols="29"></textarea>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Fecha</div>
                                    <div id="CajaInput">
                <?php FORMULARIO::cmp_fecha('fecha'); ?>
                                        <!--<input type="text" name="fecha" id="fecha" value="<?php echo date('d/m/Y'); ?>">-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Costo de Cambio</div>
                                    <div id="CajaInput">
                                        <input type="text" name="costo" id="costo" value="<?php echo FUNCIONES::ad_parametro('par_cambio_titular'); ?>"> $us
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
                            var cop_act = $('#ven_cop_act').val() * 1;
                            var int_new = $('#int_id').val() * 1;
                            var cop_new = $('#copropietario_id').val() * 1;
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
                        mask_decimal('#costo', null);
                        $('#fecha').mask('99/99/9999');

                        function set_valor_interno(data) {
                            document.frm_sentencia.int_id.value = data.id;
                            document.frm_sentencia.int_nombre_persona.value = data.nombre;
                        }
                        function set_valor_copropietario(data) {
                            document.frm_sentencia.copropietario_id.value = data.id;
                            document.frm_sentencia.copropietario_nombre.value = data.nombre;
                        }
                        function reset_interno() {
                            $("#int_nombre_persona").val("");
                            $("#int_id").val("");
                        }

                        function reset_copropietario() {
                            $("#copropietario_nombre").val("");
                            $("#copropietario_id").val("");
                        }

                </script>				
                <?php
            }
            else {
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

    function listado_cambios_propietario($venta) {
        // $aest = array('Pendiente' => '#ff9601', 'Activado' => '#019721');
        $aest = array('Pendiente' => '#ff9601', 'Pagado' => '#019721');
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
                        <th >Estado</th>
                    <?php if ($_SESSION[usu_gru_id] == 'Administradores') { ?>
                            <th class="topciones">&nbsp;</th>
                    <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $sql = "select * from venta_propietarios_historial where vph_ven_id='$_GET[id]' and vph_estado in ('Pendiente','Activado') order by vph_id asc";
                    $sql = "select * from venta_propietarios_historial where vph_ven_id='$_GET[id]' and vph_estado in ('Pendiente','Pagado') order by vph_id asc";
                    // echo $sql;
                    $cambios = FUNCIONES::objetos_bd_sql($sql);
                    $num = $cambios->get_num_registros();

                    for ($i = 0; $i < $num; $i++) {
                        $objeto = $cambios->get_objeto();
                        ?>
                        <tr class="busqueda_campos">
                            <td ><?php echo $i + 1; ?></td>
                            <td ><?php echo FUNCIONES::get_fecha_latina($objeto->vph_fecha_cambio); ?></td>
                            <td >
                                <?php
                                if ($objeto->vph_cop_id <> 0) {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_int_id) . ' - <b>Copropietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_cop_id);
                                } else {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_int_id);
                                }
                                ?>
                            </td>
                            <td align="left">
                                <?php
                                if ($objeto->vph_cop_id_nuevo <> 0) {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_int_id_nuevo) . ' - <b>Copropietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_cop_id_nuevo);
                                } else {
                                    echo '<b>Propietario:</b> ' . FUNCIONES::interno_nombre($objeto->vph_int_id_nuevo);
                                }
                                ?>
                            </td>
                            <td><span style="padding:1px 3px;color:#fff; background-color: <?php echo $aest[$objeto->vph_estado]; ?>"><?php echo $objeto->vph_estado; ?></span></td>
                            <td style="text-align:center;">
                    <?php if ($_SESSION[usu_gru_id] == 'Administradores' && $i == $num - 1 && $objeto->vph_estado == 'Pendiente') { ?>
                        <center>
                            <a id="anular_cambio" data-id="<?php echo $objeto->vph_id; ?>" href="javascript:void(0)">
                                <img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0">
                            </a>
                        </center>
                    <?php } ?>
                    </td>


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
                                        location.href = 'gestor.php?mod=venta&tarea=CAMBIAR PROPIETARIO&id=<?php echo $venta->ven_id; ?>&acc=anular&vph_id=' + id;
                                    }

                                }
                            });

                        });
        </script>
        <?php
    }

    /// FIRMA DE CONTRATO

    function firma_contrato() {
        if ($_POST) {
            $this->modificar_firma_contrato();
        } else {
            $this->frm_firma_contrato();
        }
    }

    function modificar_firma_contrato() {
        $fecha = FUNCIONES::get_fecha_mysql($_POST[ven_fecha_firma]);
        $obs = $_POST[ven_observacion_firma];
        $sql = "update venta set ven_fecha_firma='$fecha',ven_observacion_firma='$obs' where ven_id='$_GET[id]'";
        FUNCIONES::bd_query($sql);
        $mensaje = 'Fecha de Firma de contrato modificado exitosamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function frm_firma_contrato() {
        $comisiones = FUNCIONES::objetos_bd_sql("select * from comision where com_ven_id='$_GET[id]' and com_estado='Pagado'");
        if ($comisiones->get_num_registros() > 0) {
            $mensaje = 'La Fecha de Firma de contrato no puede ser modificado por que ya se ha pagado comisiones.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, 'Advertencia', 'error');
            return;
        }
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        $this->formulario->dibujar_tarea();
        ?>
        <style>
            .read-input{
                background-color: #ededed;
                border: 1px solid #bfc4c9;
                color: #6d6d6d;
                float: left;
                padding: 4px 8px;
                min-width: 107px;
            }

        </style>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=venta&tarea=FIRMA CONTRATO&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <div id="ContenedorSeleccion">
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Nro de Venta</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $venta->ven_id; ?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Persona</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'"); ?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Tipo</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $venta->ven_tipo; ?> </div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Tipo de Pago</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $venta->ven_tipo_pago; ?> </div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Concepto</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $venta->ven_concepto; ?> </div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Fecha de Firma de Contrato</div>
                    <div id="CajaInput">

                        <input type="text" id="ven_fecha_firma" name="ven_fecha_firma" value="<?php echo $venta->ven_fecha_firma != '0000-00-00' ? FUNCIONES::get_fecha_latina($venta->ven_fecha_firma) : ''; ?>">
                    </div>
                </div>

                <div id="ContenedorDiv">
                    <div class="Etiqueta">Observaciones:</div>
                    <div id="CajaInput">
                        <textarea class="area_texto" name="ven_observacion_firma" id="ven_observacion_firma" cols="31" rows="3"><?php echo $venta->ven_observacion_firma;
        ; ?></textarea>
                    </div>
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
                        $('#ven_fecha_firma').mask('99/99/9999');
                        function guardar_fecha() {
                            var fecha = $('#ven_fecha_firma').val();
                            if (trim(fecha) === '') {
                                $.prompt('Ingrese la fecha de Firma de Contrato');
                                return false;
                            }
                            document.frm_sentencia.submit();
                        }
        </script>
        <?php
    }

    // CAMBIAR LOTE
}
?>