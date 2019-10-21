<?php
for ($i = 0; $i < 500; $i++) {
    $fecha=date('YmdHis');
    $now = (string) microtime();

    $ticket=$fecha.$now[2].$now[3];
//    $ticket=$fecha.$mm;
    echo $ticket.'<br>';
}

return ;

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

    <script>
        function cargar_datos(valor) {

            var datos = valor;
    //                        alert(datos);
            var val = datos.split('-');
            document.frm_sentencia.valor_terreno.value = Math.round(parseFloat(val[1]) * parseFloat(val[2]));
            document.frm_sentencia.superficie.value = val[1];
            document.frm_sentencia.valor.value = val[2];
            document.frm_sentencia.valor_oculto.value = val[2];
            document.frm_sentencia.ven_moneda.value = val[4];

            if (val[4] == 1) {
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

            if (val[5] == 'Vivienda') {
                $('#seccion_sup_valor').css("visibility", "hidden");
            } else {
                $('#seccion_sup_valor').css("visibility", "visible");
            }
    <?php if ($_GET['moneda_monto_reserva']) {
        echo "convertir_a_moneda_de_reserva();";
    } ?>

            calcular_cuota();
            calcular_monto();
            reflejar_campos_plan_manual();
    //                        $('#ven_moneda_combo option[value=' + val[4] + ']').attr('selected',true);
            $('#ven_moneda_combo').removeAttr('disabled');

    <?php
    if ($_GET['moneda_monto_reserva']) {
        echo "var simb_moneda_anticipo =  $_GET[moneda_monto_reserva];
                    if(simb_moneda_anticipo == '1'){
                        $('#simb_moneda_anticipo').html(' Bs.');
                    }else{
                        $('#simb_moneda_anticipo').html(' Sus.');
                    }";
    }
    ?>
        }

        function convertir_a_moneda_de_reserva() {
            //var moneda_elegida = $("#ven_moneda_combo option:selected").val();
            var moneda_monto_reserva = document.frm_sentencia.moneda_monto_reserva.value;
            var ven_moneda = document.frm_sentencia.ven_moneda.value;
            var tca = parseFloat(document.frm_sentencia.tca.value);
            var valor_terreno = parseFloat(document.frm_sentencia.valor_terreno.value);
            var monto = parseFloat(document.frm_sentencia.monto.value);
            var valor = parseFloat(document.frm_sentencia.valor.value);
            var cuota_inicial = parseFloat(document.frm_sentencia.cuota_inicial.value);
            var cuota_inicial_cap = parseFloat(document.frm_sentencia.cuota_inicial_cap.value);
            var ven_anticipo = parseFloat(document.frm_sentencia.ven_anticipo.value);
            $('#ven_moneda_combo option[value=' + <?php echo $_GET['moneda_monto_reserva']; ?> + ']').attr('selected', true);

            var simbolo_moneda_vm2 = '';
            var simbolo_moneda_vt = '';
            var simbolo_moneda_desc = '';

            if (moneda_monto_reserva != ven_moneda) {
                if (moneda_monto_reserva == '1') {
                    valor_terreno *= tca;
                    monto *= tca;
                    valor *= tca;
                    cuota_inicial *= tca;
                    cuota_inicial_cap *= tca;

                    document.frm_sentencia.ven_moneda.value = '1';
                    simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;&nbsp;Valor m2 Bs&nbsp;&nbsp;&nbsp;';
                    simbolo_moneda_vt = 'Valor del Terreno Bs';
                    simbolo_moneda_desc = 'Bs';

                }
                if (moneda_monto_reserva == '2') {
                    valor_terreno /= tca;
                    monto /= tca;
                    valor /= tca;
                    cuota_inicial /= tca;
                    cuota_inicial_cap /= tca;

                    document.frm_sentencia.ven_moneda.value = '2';
                    simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;Valor m2 $us&nbsp;&nbsp;';
                    simbolo_moneda_vt = 'Valor del Terreno $us';
                    simbolo_moneda_desc = ' $us';
                }


                document.frm_sentencia.valor_terreno.value = valor_terreno;
                document.frm_sentencia.monto.value = monto;
                document.frm_sentencia.valor.value = valor;
                document.frm_sentencia.valor_oculto.value = valor;
                document.frm_sentencia.cuota_inicial.value = cuota_inicial;
                document.frm_sentencia.cuota_inicial_cap.value = cuota_inicial_cap;
                document.frm_sentencia.ven_anticipo.value = ven_anticipo;

                document.frm_sentencia.monto_a_financiar_cap.value = monto - cuota_inicial_cap;
                document.frm_sentencia.monto_acumulado_cap.value = monto - cuota_inicial_cap;

                $('#simb_moneda_vm2').html(simbolo_moneda_vm2);
                $('#simb_moneda_vt').html(simbolo_moneda_vt);
                $('#simb_moneda_descuento').html('&nbsp;' + simbolo_moneda_desc);
            }

        }

        function calcular_valor_terreno()
        {
            var sup = parseFloat(document.frm_sentencia.superficie.value);
            var val = parseFloat(document.frm_sentencia.valor.value);
            document.frm_sentencia.valor_terreno.value = sup * val;
            calcular_cuota()
            calcular_monto()
        }

        function calcular_monto()
        {

            var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
            var des = parseFloat(document.frm_sentencia.descuento.value);

            var res = parseFloat(document.frm_sentencia.ven_anticipo.value);

            document.frm_sentencia.monto.value = vt - des - res;
            document.frm_sentencia.monto_a_financiar_cap.value = document.frm_sentencia.monto.value;
            document.frm_sentencia.monto_acumulado_cap.value = document.frm_sentencia.monto_a_financiar_cap.value;
            document.frm_sentencia.cuota_inicial_cap.value = '0';
            calcular_cuota();
        }

        function cargar_uv(id) {
            cargar_manzano(id, 0);
            cargar_lote(0);
            var valores = "tarea=uv&urb=" + id;
            ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
        }

        function cargar_manzano(id, uv) {
            cargar_lote(0);
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

        function ValidarNumero(e) {
            evt = e ? e : event;
            tcl = (window.Event) ? evt.which : evt.keyCode;
            if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46)) {
                return false;
            }
            return true;
        }

        function generar_pagos() {
            var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
            if (tipo == 'Credito') {
                var ncuotas = document.frm_sentencia.meses_plazo.value;
                var vt = parseFloat(document.frm_sentencia.monto.value);
                var ci = parseFloat(document.frm_sentencia.cuota_inicial.value);
                var descuento = parseFloat(document.frm_sentencia.descuento.value);
                var superficie = parseFloat(document.frm_sentencia.superficie.value);
                var valor = parseFloat(document.frm_sentencia.valor.value);
                var vterreno = parseFloat(document.frm_sentencia.valor_terreno.value);

                //var fecha = document.frm_sentencia.ven_fecha.value;  // esto era para la generada del plan de pago

                var fecha = document.frm_sentencia.fecha_cuota_inicial.value;
                var cuota_mensual = document.frm_sentencia.cuota_mensual.value;
                var con_interes = document.frm_sentencia.con_interes.value;
                var rango_mes = document.frm_sentencia.rango_mes.value;
                var mes_cuota = document.frm_sentencia.mes_cuota.value;
                var urb_id = document.frm_sentencia.ven_urb_id.value;
                var res = parseFloat(document.frm_sentencia.ven_anticipo.value);
                var comenzar = parseFloat(document.frm_sentencia.comenzar.value);
                ci = ci - res;

                if ((ncuotas > 0 || cuota_mensual > 0) && vt > +0 && ci >= 0 && ci < vt && fecha != '') {

                    var moneda = document.frm_sentencia.ven_moneda_combo.options[document.frm_sentencia.ven_moneda_combo.selectedIndex].value;
                    var tc = parseFloat(document.frm_sentencia.tca.value);
                    var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                    var res = document.frm_sentencia.ven_anticipo.value;

                    var valores = "tarea=plan_pagos&valor_terreno=" + vt + "&cuota_inicial=" + ci + "&meses_plazo=" + ncuotas + "&tca=" + tc + "&ven_moneda=" + moneda + "&ven_lote=" + lote + "&ven_descuento=" + descuento + "&superficie=" + superficie + "&valor=" + valor + "&vterreno=" + vterreno + "&fecha=" + fecha + "&cuota_mensual=" + cuota_mensual + "&con_interes=" + con_interes + "&rango_mes=" + rango_mes + "&mes_cuota=" + mes_cuota + "&urb_id=" + urb_id + "&res=" + res + "&comenzar=" + comenzar;
                    ejecutar_ajax('ajax.php', 'plan_de_pagos', valores, 'POST');
                } else {
                    $('#tprueba tbody').remove();
                    $.prompt('-La Fecha no debe estar vacia.</br>-El valor del terreno debe ser mayor a cero.</br>-La cuota inicial debe der mayor o igual a cero y menor al valor del terreno.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                }
            } else {
                $.prompt('La venta es al contado, no necesita generar un plan de pagos.', {opacity: 0.8});
            }
        }

        function f_tipo() {
            var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
            if (tipo == 'Contado') {
                document.frm_sentencia.cuota_inicial.value = "";
                document.frm_sentencia.meses_plazo.value = "";
                document.frm_sentencia.cuota_inicial.disabled = true;
                document.frm_sentencia.meses_plazo.disabled = true;
                document.frm_sentencia.descuento.value = 0;
                $('#tprueba tbody').remove();
                ocultar_campos_innecesarios(1);
                actualizar_monto_con_reserva();
            } else {
    //                    mostrar_tipo_plan_credito();
                mostrar_campos_credito_plan_normal();
                document.frm_sentencia.cuota_inicial.disabled = false;
                document.frm_sentencia.meses_plazo.disabled = false;
                document.frm_sentencia.descuento.value = 0;
                calcular_cuota()

            }
        }

        function actualizar_dependientes() {
            var monto = document.frm_sentencia.monto.value;
            document.frm_sentencia.monto_acumulado_cap.value = monto;
        }

        function cambiar_moneda_venta() {
            var moneda_elegida = $("#ven_moneda_combo option:selected").val();
            var ven_moneda = document.frm_sentencia.ven_moneda.value;
            var tca = parseFloat(document.frm_sentencia.tca.value);
            var valor_terreno = parseFloat(document.frm_sentencia.valor_terreno.value);
            var monto = parseFloat(document.frm_sentencia.monto.value);
            var valor = parseFloat(document.frm_sentencia.valor.value);
            var cuota_inicial = parseFloat(document.frm_sentencia.cuota_inicial.value);
            var cuota_inicial_cap = parseFloat(document.frm_sentencia.cuota_inicial_cap.value);
            var ven_anticipo = parseFloat(document.frm_sentencia.ven_anticipo.value);
            var descuento = parseFloat(document.frm_sentencia.descuento.value);

            var simbolo_moneda_vm2 = '';
            var simbolo_moneda_vt = '';
            var simbolo_moneda_desc = '';

            if (moneda_elegida != ven_moneda) {
                if (moneda_elegida == '1') {
                    valor_terreno *= tca;
                    monto *= tca;
                    valor *= tca;
                    cuota_inicial *= tca;
                    cuota_inicial_cap *= tca;
                    descuento *= tca;
                    document.frm_sentencia.ven_moneda.value = '1';
    <?php
    if ($_GET['moneda_monto_reserva']) {
        echo "var moneda_monto_reserva = document.frm_sentencia.moneda_monto_reserva.value;     
                if(moneda_monto_reserva != moneda_elegida){
                    ven_anticipo *= tca;                                                        
                    document.frm_sentencia.moneda_monto_reserva.value = '1';
                    $('#simb_moneda_anticipo').html(' Bs.');
                }";
    }
    ?>
                    simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;&nbsp;Valor m2 Bs&nbsp;&nbsp;&nbsp;';
                    simbolo_moneda_vt = 'Valor del Terreno Bs';
                    simbolo_moneda_desc = 'Bs';

                }
                if (moneda_elegida == '2') {
                    valor_terreno /= tca;
                    monto /= tca;
                    valor /= tca;
                    cuota_inicial /= tca;
                    cuota_inicial_cap /= tca;
                    descuento /= tca;
                    document.frm_sentencia.ven_moneda.value = '2';
    <?php
    if ($_GET['moneda_monto_reserva']) {
        echo "var moneda_monto_reserva = document.frm_sentencia.moneda_monto_reserva.value;     
                if(moneda_monto_reserva != moneda_elegida){
                    ven_anticipo /= tca;                              
                    document.frm_sentencia.moneda_monto_reserva.value = '2';
                    $('#simb_moneda_anticipo').html(' Sus.');
                }";
    }
    ?>
                    simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;Valor m2 $us&nbsp;&nbsp;';
                    simbolo_moneda_vt = 'Valor del Terreno $us';
                    simbolo_moneda_desc = ' $us';

                }


                document.frm_sentencia.valor_terreno.value = valor_terreno;
                document.frm_sentencia.monto.value = monto;
                document.frm_sentencia.valor.value = valor;
                document.frm_sentencia.valor_oculto.value = valor;
                document.frm_sentencia.cuota_inicial.value = cuota_inicial;
                document.frm_sentencia.cuota_inicial_cap.value = cuota_inicial_cap;
                document.frm_sentencia.ven_anticipo.value = ven_anticipo;

                document.frm_sentencia.monto_a_financiar_cap.value = monto - cuota_inicial_cap;
                document.frm_sentencia.monto_acumulado_cap.value = monto - cuota_inicial_cap;

                document.frm_sentencia.descuento.value = descuento;

                $('#simb_moneda_vm2').html(simbolo_moneda_vm2);
                $('#simb_moneda_vt').html(simbolo_moneda_vt);
                $('#simb_moneda_descuento').html('&nbsp;' + simbolo_moneda_desc);

            }

        }

        function actualizar_monto_con_reserva() {
    <?php
    if ($_GET['moneda_monto_reserva']) {
        echo "var moneda_elegida = $('#ven_moneda_combo option:selected').val();
        var moneda_monto_reserva = document.frm_sentencia.moneda_monto_reserva.value;
        if(moneda_elegida != moneda_monto_reserva){
            if(moneda_elegida == '1'){
                document.frm_sentencia.ven_anticipo.value = $_GET[monto_bs];
                document.frm_sentencia.moneda_monto_reserva.value = '1';
            }else{
                document.frm_sentencia.ven_anticipo.value = $_GET[monto_sus];
                document.frm_sentencia.moneda_monto_reserva.value = '2';
            }
        }";
    }
    ?>

            var res = parseFloat(document.frm_sentencia.ven_anticipo.value);
            if (res > 0) {
                var monto = parseFloat(document.frm_sentencia.monto.value);
                document.frm_sentencia.monto.value = monto - res;
            }
        }


        function mostrar_tipo_plan_credito() {
            $("div[name='divTipoPlanCredito']").css("display", "block");
            $("#divEtiTipoPlanCredito").css("display", "block");
            $(".div_datos_credito").css("display", "block");
        }

        function ocultar_tipo_plan_credito() {
            $("div[name='divTipoPlanCredito']").css("display", "none");
            $("#divEtiTipoPlanCredito").css("display", "none");
            $(".div_datos_credito").css("display", "none");
        }

        //function mostrar
        function tipo_credito() {

            if ($("#ven_tipo_plan_credito option:selected").val() === 'normal') {
                mostrar_campos_credito_plan_normal();
            } else {
                if ($("#ven_tipo_plan_credito option:selected").val() === 'manual') {
                    ocultar_campos_innecesarios(0);
                    mostrar_formulario_credito_plan_manual();
                }
            }


        }

        function mostrar_formulario_credito_plan_manual() {

            var vt = parseFloat(document.getElementById('valor_terreno').value);
            document.frm_sentencia.monto_a_financiar_cap.value = vt;
            $('#divSeccionCaprichos').css('display', 'block');

        }

        function mostrar_campos_credito_plan_normal() {
            $("div[name='divConInteres']").css("display", "block");
            $("div[name='divCuotaInicial']").css("display", "block");
            $("div[name='divRangoMes']").css("display", "block");
            $("div[name='divMesesPlazo']").css("display", "block");
            $("div[name='divCuotaMensual']").css("display", "block");
            $("div[name='divBotonVerPlan']").css("display", "block");
            $('#divSeccionCaprichos').css('display', 'none');
            $('#divMesesPlazo').css("display", "block");
            $('.div_datos_credito').css("display", "block");
        }

        function ocultar_campos_innecesarios(n) {

            if (n == 1) {
                ocultar_tipo_plan_credito();
            }

            $("div[name='divConInteres']").css("display", "none");
            $("div[name='divCuotaInicial']").css("display", "none");
            $("div[name='divRangoMes']").css("display", "none");
            $("div[name='divCuotaMensual']").css("display", "none");
            $("div[name='divMesesPlazo']").css("display", "none");
            $("#divTipoPlanCredito").css("display", "none");
            $("div[name='divBotonVerPlan']").css("display", "none");
            $('#divSeccionCaprichos').css('display', 'none');
            $('.div_datos_credito').css('display', 'none');
        }

        function reflejar_campos_plan_manual() {
            var monto_a_financiar_cap = document.getElementById('monto_a_financiar_cap');
            monto_a_financiar_cap.value = document.getElementById('monto').value;
        }

        function actualizar_cuota_inicial() {

    //            var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
            var vt = parseFloat(document.frm_sentencia.monto.value);
            var cuota_inicial_cap = parseFloat(document.frm_sentencia.cuota_inicial_cap.value);
            var monto_a_financiar_cap = document.getElementById('monto_a_financiar_cap');
            var monto_acumulado_cap = document.getElementById('monto_acumulado_cap');
            monto_a_financiar_cap.value = vt - cuota_inicial_cap;
            monto_acumulado_cap.value = monto_a_financiar_cap.value;
        }


        function datos_fila() {

            $("#ven_moneda_combo").attr('disabled', 'true');
            var fecha_pago_cuota_cap = document.getElementById('fecha_pago_cuota_cap').value;
            var cuota_inicial_cap = parseFloat(document.getElementById('cuota_inicial_cap').value);
            var glosa_cap = document.getElementById('glosa_cap').value;
            var capital_cap = parseFloat(document.getElementById('capital_cap').value);
            var interes_cap = parseFloat(document.getElementById('interes_cap').value);
            var monto_a_financiar_cap = parseFloat(document.getElementById('monto_a_financiar_cap').value);

            var ven_moneda = document.frm_sentencia.ven_moneda.value;
            var desc_moneda = '';
            if (ven_moneda === '1') {
                desc_moneda = ' Bolivianos';
            } else {
                desc_moneda = ' Dolares';
            }

            if (capital_cap > monto_a_financiar_cap) {
                $.prompt('El capital no puede ser mayor al monto a financiar.');
            } else {
                var monto_acumulado_cap = parseFloat(document.getElementById('monto_acumulado_cap').value);
                if (capital_cap <= monto_acumulado_cap) {
                    if (fecha_pago_cuota_cap !== '' && cuota_inicial_cap !== '' && glosa_cap !== '' && capital_cap !== '' && interes_cap !== '') {
                        var monto = parseFloat(monto_acumulado_cap - capital_cap);
                        monto = roundNumber(monto, 1);
                        document.getElementById('monto_acumulado_cap').value = monto;
                    } else {
                        $.prompt('Ingrese los datos correspondientes');
                    }
                } else {
                    $.prompt('El capital no puede ser mayor al saldo.');
                }

            }

        }

        function actualizar_monto_acumulado(row, columna)
        {
            var dato = $(row).parent().parent().parent().children().eq(columna).children().eq(0).attr('value');
            var datos = dato.split('-');
            var capital = parseFloat(datos[1]);
            var monto_acumulado_cap = parseFloat(document.getElementById('monto_acumulado_cap').value);
            document.getElementById('monto_acumulado_cap').value = parseFloat(monto_acumulado_cap + capital);
    //                    if ($('#tablaCapricho >tbody >tr').length == 0){
    //                        document.getElementById('monto_acumulado_cap').value = document.getElementById('monto_a_financiar_cap').value;
    //                    }
        }

        function actualizar_total(row, columna)
        {
            var dato = $(row).parent().parent().parent().children().eq(columna).children().eq(0).attr('value');
            var datos = dato.split('-');
            var tpbs = parseFloat(document.frm_sentencia.tbs.value);
            var tpsus = parseFloat(document.frm_sentencia.tsus.value);
            document.frm_sentencia.tbs.value = parseFloat(roundNumber((tpbs - datos[0]), 2));
            document.frm_sentencia.tsus.value = parseFloat(roundNumber((tpsus - datos[1]), 2));
        }

        function Solo_Numerico(variable) {
            Numer = parseInt(variable);
            if (isNaN(Numer)) {
                return "";
            }
            return Numer;
        }

        function ValNumero(Control) {
            Control.value = Solo_Numerico(Control.value);
        }

        function remover_cap(row)
        {
            var cant = $(row).parent().parent().parent().children().length;
            if (cant > 1)
                $(row).parent().parent().parent().remove();
            if ($('#tablaCapricho >tbody >tr').length === 0) {
                //alert ( "No hay filas en la tabla!!" );
                $("#ven_moneda_combo").removeAttr('disabled');
            }


        }


        function addTableRow(id, valor) {
            $(id).append(valor);
        }

        function enviar_formulario() {
            var interno = document.frm_sentencia.ven_int_id.value;
            var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
            var vendedor = document.frm_sentencia.vendedor.options[document.frm_sentencia.vendedor.selectedIndex].value;
            //var moneda=document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
            var moneda = document.frm_sentencia.ven_moneda.value;
            var urbanizacion = document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
            var manzano = document.frm_sentencia.ven_man_id.options[document.frm_sentencia.ven_man_id.selectedIndex].value;
            var lote = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
            var cuota_mensual = document.frm_sentencia.cuota_mensual.value;
            var valor_terreno = parseFloat(document.frm_sentencia.valor_terreno.value);
            var descuento = parseFloat(document.frm_sentencia.descuento.value);
            var valor = parseFloat(document.frm_sentencia.valor.value);
            var valor_oculto = parseFloat(document.frm_sentencia.valor_oculto.value);
            var tipo_plan = document.frm_sentencia.ven_tipo_plan_credito.options[document.frm_sentencia.ven_tipo_plan_credito.selectedIndex].value;
            if (interno != '' && tipo != '' && vendedor != '' && moneda != '' && urbanizacion != '' && manzano != '' && lote != '' && (descuento < valor_terreno) && (valor >= valor_oculto)) {
                if (tipo == 'Credito') {
                    if (tipo_plan == 'normal') {
                        var ncuotas = document.frm_sentencia.meses_plazo.value;
                        var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                        var ci = parseFloat(document.frm_sentencia.cuota_inicial.value);
                        var res = parseFloat(document.frm_sentencia.ven_anticipo.value);

                        if ((ncuotas > 0 || cuota_mensual > 0) && vt > 0 && ci >= 0 && ci < vt && ci >= res) {
                            document.frm_sentencia.cuota_inicial.value = ci - res;
                            document.frm_sentencia.submit();
                        } else {
                            $.prompt('-El valor del terreno debe ser mayor a cero.</br>-La cuota inicial debe der mayor o igual a cero,mayor al valor de la reserva y menor al valor del terreno.</br>-Los meses de plazo debe ser mayor a cero.', {opacity: 0.8});
                        }
                    } else {

                        var monto_acumulado_cap = parseFloat(document.getElementById('monto_acumulado_cap').value);
                        var ci = parseFloat(document.frm_sentencia.cuota_inicial_cap.value);
                        var res = parseFloat(document.frm_sentencia.ven_anticipo.value);
                        if (monto_acumulado_cap == 0 && ci >= res) {
                            document.frm_sentencia.cuota_inicial_cap.value = ci - res;
                            document.frm_sentencia.submit();
                        } else {
                            if (monto_acumulado_cap != 0)
                                $.prompt('Debe cubrirse todo el monto a financiar con las cuotas.', {opacity: 0.8});
                            else
                                $.prompt('-La cuota inicial debe der mayor o igual a cero,mayor al valor de la reserva y menor al valor del terreno.', {opacity: 0.8});
                        }

                    }
                } else {

                    var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                    if (vt > 0) {
                        document.frm_sentencia.submit();
                    } else {
                        $.prompt('El valor del terreno debe ser mayor a cero', {opacity: 0.8});
                    }
                }
            } else {
                $.prompt('Para Guardar la Venta dede seleccionar los campos Persona, Vendedor, Tipo, Moneda, Urbanizaci?n, Manzano, Lote.</br>- El Descuento debe ser menos al Monto a Pagar.</br>- El Valor de M2 no puede ser menos a lo Establecido (' + valor_oculto + ' Dolares)', {opacity: 0.8});
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
                    if (id == datos[2])
                    {
                        ban = false;
                    }

                });
            }
            return ban;
        }

        function calcular_cuota()
        {
            var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
            if (tipo == 'Credito') {
                var v_vt = parseFloat(document.frm_sentencia.valor_terreno.value);<!---- -->
                var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                var des = parseFloat(document.frm_sentencia.descuento.value);
                var res = parseFloat(document.frm_sentencia.ven_anticipo.value);
                //var td=(vt*des)/100;
    //                            document.frm_sentencia.monto.value = vt - des - res;
                document.frm_sentencia.monto.value = vt - des;
                var monto_pagar = parseFloat(document.frm_sentencia.monto.value);<!-- -->

                var dato_s = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;

                var dato_a = dato_s.split('-');

                document.frm_sentencia.cuota_inicial.value = (dato_a[3] * monto_pagar) / 100;
    //                            alert(dato_a[3]);
            }
        }

        function limpiar_meses_plazo()
        {
            $("div[name='divMesesPlazo']").css("visibility", "hidden");
            document.frm_sentencia.meses_plazo.value = "";
            if (document.frm_sentencia.cuota_mensual.value === '') {
                $("div[name='divMesesPlazo']").css("visibility", "visible");
            }
        }

        function limpiar_cuota_mensual()
        {
            $("div[name='divCuotaMensual']").css("visibility", "hidden");
            document.frm_sentencia.cuota_mensual.value = "";
            if (document.frm_sentencia.meses_plazo.value === '') {
                $("div[name='divCuotaMensual']").css("visibility", "visible");
            }
        }

        function reset_interno()
        {
            document.frm_sentencia.ven_int_id.value = "";
            document.frm_sentencia.int_nombre_persona.value = "";
        }

        function reset_co_propietario()
        {
            document.frm_sentencia.ven_co_propietario.value = "";
            document.frm_sentencia.int_nombre_copropietario.value = "";
        }
    </script>

    <?
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

    $page = "'gestor.php?mod=venta&tarea=AGREGAR&acc=Emergente'";
    $extpage = "'persona'";
    $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

    ////
    $pagina = "'contenido_reporte'";

    $page2 = "'about:blank'";

    $extpage2 = "'reportes'";

    $features2 = "'left=100,width=900,height=500,top=0,scrollbars=yes'";

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


    ////	


    $this->formulario->dibujar_tarea('PERSONA');

    $this->datos_venta($ci, $im);



    if ($this->mensaje <> "") {
        $this->formulario->dibujar_mensaje($this->mensaje);
    }
    ?>
    <table align=right border=0><tr>

    <?php
    if ($this->verificar_permisos('ACCEDER')) {
        ?>
                <td><a href="gestor.php?mod=venta&tarea=ACCEDER" title="LISTADO DE VENTAS"><img border="0" width="20" src="images/listado.png"></a></td>
        <?php
    }
    ?>
        </tr></table>

    <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>

    <script type="text/javascript" src="js/cal2.js"></script>
    <script type="text/javascript" src="js/cal_conf2.js"></script>
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
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
            <div id="FormSent" style="width:820px;">
                <div class="Subtitulo">Datos</div>
                <div id="ContenedorSeleccion">





                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><span class="flechas1">*</span>Persona</div>
                        <div id="CajaInput">
                                     <!--<input name="ven_int_id" id="ven_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id'] ?>" size="2">
                                     <input name="int_nombre_persona" id="int_nombre_persona" readonly="readonly"  type="text" class="caja_texto" value="" size="40">
                                     <img src="images/ir.png"  onclick="javascript:window.open(<?php echo $page; ?>,<?php echo $extpage; ?>,<?php echo $features; ?>);">-->
    <?php
    if ($personas <> 0) {
        ?>
                                <input name="ven_int_id" id="ven_int_id" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id'] ?>" size="2">
                                <input name="int_nombre_persona" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                <a class="group" style="float:left; margin:0 0 0 7px;float:right;"  href="sueltos/llamada.php?accion=agregar_persona">
                                    <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                </a>
                                <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onClick="reset_interno();">
                                    <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                </a>
        <?php
    } else {
        echo 'No se le asigno ning?na personas, para poder cargar las personas.';
    }
    ?>


                                                                                                                                                               <!--<input type="hidden" name="ci" id="ci"  value="<?php //echo $ci;                   ?>">-->
                            <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                        </div>
                            <?php $conversor = new convertir(); ?>
                        <div id="CajaInput">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha&nbsp;
                            <!--<input readonly="readonly" class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php
                            if (isset($_POST['ven_fecha']))
                                echo $conversor->get_fecha_latina($_POST['ven_fecha']);
                            else
                                echo date("Y-m-d")
                                ?>" type="text">-->
                            <input class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php
                            if (isset($_POST['ven_fecha']))
                                echo $_POST['ven_fecha'];
                            else
                                echo date("d-m-Y");
                            ?>" type="text">

                        </div>
                        <div id="CajaInput">
                            &nbsp;&nbsp;&nbsp;
                            Tipo de Cambio&nbsp;<input type="text" name="tca" id="tca" size="5" value="<?php echo $this->tc; ?>" readonly>
                        </div>
                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Co-Propietario</div>
                        <div id="CajaInput">
    <?php
    if ($personas <> 0) {
        ?>
                                <input name="ven_co_propietario" id="ven_co_propietario" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['ven_co_propietario'] ?>" size="2">
                                <input name="int_nombre_copropietario" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="int_nombre_copropietario"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_copropietario'] ?>" size="40">
                                <a class="group" style="float:left; margin:0 0 0 7px;float:right;"  href="sueltos/llamada.php?accion=agregar_persona">
                                    <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                </a>
                                <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onClick="reset_co_propietario();">
                                    <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                </a>
        <?php
    } else {
        echo 'No se le asigno ning?na personas, para poder cargar las personas.';
    }
    ?>
                        </div>


                    </div>
                    <!--Fin-->



                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><span class="flechas1">*</span>Vendedor</div>
                        <div id="CajaInput">
                                <?php
                                if ($this->obtener_grupo_id($this->usu->get_id()) == "Vendedores") {
                                    $id_interno = $this->obtener_id_interno_tbl_usuario($this->usu->get_id());
                                    ?>
                                <select style="width:200px;" name="vendedor" id="vendedor" class="caja_texto">
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' AND vdo_int_id=$id_interno", $_POST['vendedor']);
        ?>
                                </select>
                                    <?php
                                } else {
                                    ?>
                                <select style="width:200px;" name="vendedor" id="vendedor" class="caja_texto">
                                    <option value="">Seleccione</option>
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['vendedor']);
        ?>
                                </select>
        <?php
    }
    ?>
                        </div>
                    </div>
                    <!--Fin-->


                    <!--Inicio-->
                    <input readonly type="hidden" name="ven_moneda" id="ven_moneda" size="5" value="">
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizaci?n</div>
                        <div id="CajaInput">
                            <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" <?php if ($_POST['id_res'] == '') {
        echo 'onChange="cargar_uv(this.value);"';
    } ?>>

    <?php
    if ($_POST['id_res'] <> "") {
        $fun = NEW FUNCIONES;
        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where urb_id=" . $_POST['ven_urb_id'], $_POST['ven_urb_id']);
    } else {
        echo '<option value="">Seleccione</option>';
        $fun = NEW FUNCIONES;
        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
    }
    ?>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
                        <div id="CajaInput">
                            <div id="uv">
                                <select style="width:200px;" name="ven_uv_id" class="caja_texto">

                                    <?php
                                    if ($_POST['id_res'] <> '') {
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select uv_id as id,CONCAT('Uv Nro:',' ',uv_nombre) as nombre from uv where uv_id='" . $_POST['ven_uv_id'] . "' ", $_POST['ven_uv_id']);
                                    } else {
                                        echo '<option value="">Seleccione</option>';
                                        if ($_POST['ven_urb_id'] <> "") {
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_uv_id']);
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
                        <div id="CajaInput">
                            <div id="manzano">
                                <select style="width:200px;" name="ven_man_id" class="caja_texto" onChange="cargar_lote(this.value);">

                                    <?php
                                    if ($_POST['id_res'] <> '') {
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select man_id as id,CONCAT('Manzano Nro:',' ',man_nro) as nombre from manzano where man_id='" . $_POST['ven_man_id'] . "' ", $_POST['ven_man_id']);
                                    } else {
                                        echo '<option value="">Seleccione</option>';
                                        if ($_POST['ven_urb_id'] <> "") {
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_man_id']);
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                        <div id="CajaInput">
                            <div id="lote">
                                <select style="width:200px;" name="ven_lot_id" class="caja_texto">

                                    <?php
                                    if ($_POST['id_res'] <> '') {
                                        $fun = NEW FUNCIONES;
                                        $fun->combo_lote("select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre,zon_color,cast(lot_nro as SIGNED) as numero from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_man_id='" . $_POST['ven_man_id'] . "' and lot_uv_id='" . $_POST['ven_uv_id'] . "' and lot_estado='Reservado' and lot_id='" . $_POST['ven_lot_id'] . "' order by numero asc");
                                    } else {
                                        echo '<option value="">Seleccione</option>';
                                        if ($_POST['ven_man_id'] <> "") {
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select lot_id as id,lot_nro as nombre from lote where lot_man_id='" . $_POST['ven_man_id'] . "' ", $_POST['ven_lot_id']);
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
                        <div id="CajaInput">
                            <select style="width:100px;" name="ven_tipo" class="caja_texto" onChange="javascript:f_tipo();">
                                <!--<option value="">Seleccione</option>-->
                                <option value="Contado" <?php if ($_POST['ven_tipo'] == 'Contado') echo 'selected="selected"'; ?>>Contado</option>
                                <option value="Credito" <?php if ($_POST['ven_tipo'] == 'Credito') echo 'selected="selected"'; ?>>Credito</option>
                            </select>
                        </div>

                        <div class="Etiqueta" id="divEtiTipoPlanCredito"><span class="flechas1">*</span>Tipo Plan</div>
                        <div id="CajaInput" name="divTipoPlanCredito">
                            <select style="width:100px;" name="ven_tipo_plan_credito" id="ven_tipo_plan_credito" class="caja_texto" onChange="javascript:tipo_credito();">
                                <!--<option value="">Seleccione</option>-->
                                <option value="normal" <?php if ($_POST['ven_tipo_plan_credito'] == 'normal') echo 'selected="selected"'; ?>>Plan Normal</option>
                                <option value="manual" <?php if ($_POST['ven_tipo_plan_credito'] == 'manual') echo 'selected="selected"'; ?>>Plan Manual</option>
                            </select>
                        </div>

                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>La venta se realizara en:</div>
                        <div id="CajaInput">

                            <select style="width:100px;" name="ven_moneda_combo" id="ven_moneda_combo" class="caja_texto" disabled="disabled" onchange="javascript:cambiar_moneda_venta();">
                                <option value="2" <?php // if ($_POST['ven_moneda'] == '2') echo 'selected="selected"';       ?>>Dolares</option>
                                <option value="1" <?php // if ($_POST['ven_moneda'] == '1') echo 'selected="selected"';       ?>>Bolivianos</option>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Observaci?n</div>
                        <div id="CajaInput">
                            <textarea class="area_texto" name="ven_observacion" id="ven_observacion" cols="31" rows="3"><?php echo $_POST['ven_observacion'] ?></textarea>
                        </div>
                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div id="seccion_sup_valor">
                            <div class="Etiqueta" >Superficie</div>
                            <div id="CajaInput">
                                <input readonly type="text" name="superficie" id="superficie" size="15" value="" >
                            </div>
                            <div id="CajaInput">
                                <span id="simb_moneda_vm2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Precio m2&nbsp;&nbsp;&nbsp;</span><input  type="text" name="valor" id="valor" size="15" value="" <?php
                                    if (!$this->puede_modificar_valorM2()) {
                                        echo 'readonly="readonly"';
                                    }
                                    ?>  onKeyUp="javascript:calcular_valor_terreno();">
                                <input  type="hidden" name="valor_oculto" id="valor_oculto" size="15" value="">
                            </div>

                        </div>



                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" id="simb_moneda_vt"><span class="flechas1">*</span>Precio del Terreno</div>
                        <div id="CajaInput">
                            <input readonly type="text" name="valor_terreno" id="valor_terreno" size="15" value="">
                        </div>                            



                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Monto a Pagar</div>
                        <div id="CajaInput">
                            <input readonly type="text" name="monto" id="monto" size="15" value="">
                        </div>

                        <?php
                        if ($_POST['ven_anticipo']) {
                            ?>
                            <div id="CajaInput">
                                &nbsp;&nbsp;&nbsp;
                                Reserva&nbsp;<input type="text" name="ven_anticipo" id="ven_anticipo" size="5" value="<?php echo $_POST['ven_anticipo']; ?>" readonly="readonly"><span id="simb_moneda_anticipo"></span>
                                <input type="hidden" name="id_res" id="id_res" size="5" value="<?php echo $_POST['id_res']; ?>" readonly="readonly">
                                <input type="hidden" name="moneda_monto_reserva" id="moneda_monto_reserva" size="7" value="<?php echo $_GET['moneda_monto_reserva']; ?>">
                            </div>
        <?php
    } else {
        ?>
                            <input type="hidden" name="ven_anticipo" id="ven_anticipo" size="5" value="0" readonly="readonly">
        <?php
    }
    ?>

                        <div id="CajaInput">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Descuento&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="descuento" id="descuento" size="15" value="0" onKeyUp="javascript:calcular_monto();"><span id="simb_moneda_descuento"></span>
                        </div>
                    </div>
                    <!--Fin-->

                    <div class="div_datos_credito">
                        <div class="Subtitulo">Datos Credito</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="cuota_inicial" id="divCuotaInicial" name="divCuotaInicial">
                                    <div class="Etiqueta" >Cuota Inicial</div>
                                    <div id="CajaInput" name="divCuotaInicial">
                                        <input type="text" name="cuota_inicial" id="cuota_inicial" size="5" value=""  onKeyPress="return ValidarNumero(event);">
                                    </div>

                                </div> 

                                <div class="meses_plazo" id="divMesesPlazo" name="divMesesPlazo">

                                    <div id="CajaInput">
                                        &nbsp;&nbsp;Nro de Cuotas&nbsp;&nbsp;&nbsp;<input type="text" name="meses_plazo" id="meses_plazo" size="5" value="" onKeyUp="limpiar_cuota_mensual();" onKeyPress="return ValidarNumero(event);">
                                    </div>
                                </div>



                                <div id="CajaInput" name="divCuotaMensual">
                                    &nbsp;&nbsp;Monto Cuota&nbsp;&nbsp;&nbsp;<input type="text" name="cuota_mensual" id="cuota_mensual" size="5" value="" onKeyUp="limpiar_meses_plazo();" onKeyPress="return ValidarNumero(event);">
                                </div>

                                <div id="CajaInput" name="divComenzarEn">
                                    &nbsp;&nbsp;Comenzar cuota en&nbsp;&nbsp;&nbsp;<input type="text" name="comenzar" id="comenzar" size="5" value="" >
                                </div>

                                <div id="CajaInput" name="divNroCuotas" class="mes_cuota" style="visibility:hidden;">
                                    &nbsp;&nbsp;Nro de Cuotas<input type="text" name="mes_cuota" id="mes_cuota" size="5" value="" onKeyUp="" onKeyPress="return ValidarNumero(event);">
                                </div>

                            </div>

                            <div id="ContenedorDiv">

                                <!--<div class="Etiqueta" >Con Interes</div>-->
                                <div id="CajaInput" name="divConInteres">
                                    <div class="Etiqueta" >Con Interes</div>
                                    <span style="float:left;"></span><select name="con_interes" id="con_interes">
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>


                                <div id="CajaInput" name="divRangoMes">
                                    <span style="float:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rango Meses&nbsp;&nbsp;&nbsp;</span><select name="rango_mes" id="rango_mes">
                                        <option value="1">Mensual</option>
                                        <option value="2">Bimestral</option>
                                        <option value="3">Trimestral</option>
                                        <option value="6">Semestral</option>
                                        <option value="12">Anual</option>
                                    </select> 
                                </div>

    <?php $conversor = new convertir(); ?>
                                <div id="CajaInput">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha de la Cuota Inicial&nbsp;                                
                                    <input class="caja_texto" name="fecha_cuota_inicial" id="fecha_cuota_inicial" size="12" value="<?php
    if (isset($_POST['fecha_cuota_inicial']))
        echo $_POST['fecha_cuota_inicial'];
    else
        echo date("d-m-Y");
    ?>" type="text">

                                </div>

                            </div>

                            <!--Fin-->
                        </div>


                    </div>

                    <!--Inicio-->
                    <div id="ContenedorDiv">

                        <div id="CajaInput" name="divBotonVerPlan">
                            <img src="imagenes/generar.png" style='margin:0px 0px 0px 10px' onClick="javascript:generar_pagos();">
                        </div>
                    </div>
                    <!--Fin-->
                    <div id="divSeccionCaprichos">
                        <div id="divFormCaprichos">

                            <div class="Subtitulo">Datos del Credito</div>
                            <div id="ContenedorSeleccion">
                                <h4>Datos de la Cuota Inicial</h4><br/>
                                <div id="ContenedorDiv">

                                    <div class="Etiqueta" >Cuota Inicial</div>
                                    <div id="CajaInput">
                                        <input type="text" name="cuota_inicial_cap" id="cuota_inicial_cap" size="10" onkeyup="javascript:actualizar_cuota_inicial();" onkeypress="return ValidarNumero(event);"   value="0">
                                    </div>


                                    <div class="Etiqueta" >Fecha Cuota Inicial</div>
                                    <div id="CajaInput">
                                        <input type="text" name="fecha_cuota_inicial_cap" id="fecha_cuota_inicial_cap" size="10" value="<?php echo date("d-m-Y"); ?>">
                                    </div>

                                </div>

                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Monto a Financiar</div>
                                    <div id="CajaInput">
                                        <input type="text" name="monto_a_financiar_cap" id="monto_a_financiar_cap" size="10" onchange="actualizar_dependientes();" readonly="readonly" value="0">
                                        <input type="hidden" name="monto_acumulado_cap" id="monto_acumulado_cap">
                                    </div>
                                </div>

                                <br/><br/>
                                <h4>Datos de las demas Cuotas</h4><br/>
                                <div id="ContenedorDiv">

                                    <div class="Etiqueta" style="width: 4ex">Glosa</div>
                                    <div id="CajaInput">                                    
                                        <!--<textarea class="area_texto" name="glosa_cap" id="glosa_cap" cols="31" rows="3"><?php // echo $_POST['glosa_cap']    ?></textarea>-->
                                        <input type="text" name="glosa_cap" id="glosa_cap" size="50" value="<?php echo $_POST['glosa_cap'] ?>">
                                    </div>


                                    <!--<div class="Etiqueta" >Capital</div>-->
                                    <div id="CajaInput">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Capital&nbsp;&nbsp;&nbsp;<input type="text" name="capital_cap" id="capital_cap" size="5" value="0" onkeypress="return ValidarNumero(event);">
                                    </div>

                                    <div id="CajaInput">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Interes&nbsp;&nbsp;&nbsp;<input type="text" name="interes_cap" id="interes_cap" size="5" value="0" onkeypress="return ValidarNumero(event);">
                                    </div>



                                    <div id="CajaInput">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha&nbsp;
                                        <!--<input readonly="readonly" class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php
                                        if (isset($_POST['fecha_pago_cuota_cap']))
                                            echo $conversor->get_fecha_latina($_POST['fecha_pago_cuota_cap']);
                                        else
                                            echo date("Y-m-d")
                                            ?>" type="text">-->
                                        <input class="caja_texto" name="fecha_pago_cuota_cap" id="fecha_pago_cuota_cap" size="12" value="<?php
                                        if (isset($_POST['fecha_pago_cuota_cap']))
                                            echo $_POST['fecha_pago_cuota_cap'];
                                        else
                                            echo date("d-m-Y");
                                        ?>" type="text">

                                    </div>


                                    <br/><br/>    
                                    <div id="CajaInput" name="divBotonAgregar">
                                        <img src="imagenes/boton_agregar.png" style='margin:0px 0px 0px 10px' onClick="javascript:datos_fila();">
                                    </div>
                                </div>

                            </div>





                            <br><br><br><br><br><br>
                            <div id="divTablaCapricho">
                                <br><br><br><br><br><br>
                                <table width="98%"   class="tablaReporte" id="tablaCapricho" cellpadding="0" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Glosa</th>
                                            <th>Fecha</th>
                                            <th>Capital</th>
                                            <th>Interes</th>                                                
                                            <th>Monto a Pagar</th>
                                            <th>Saldo</th>
                                            <th>Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                    <tfoot>

                                    </tfoot>
                                </table>
                            </div>

                        </div>
                    </div>


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
                            <?php
                            if (!($ver)) {
                                ?>
                                <input type="button" class="boton" name="" value="Guardar Venta" onClick="javascript:enviar_formulario();"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php echo '<a href="javascript:var c = window.open(' . $page2 . ',' . $extpage2 . ',' . $features2 . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR PLAN DE PAGO">
				</a>'; ?>
        <?php
    } else {
        ?>
                                <input type="button" class="boton" name="" value="Volver" onClick="javascript:location.href = '<?php echo $red; ?>';">
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
        var options2 = {
            script: "sueltos/suggest_persona_usuario.php?json=true&",
            varname: "input",
            minchars: 1,
            timeout: 10000,
            noresults: "No se encontro ninguna persona",
            json: true,
            callback: function(obj) {
                document.getElementById('ven_co_propietario').value = obj.id;
            }
        };
        var as_json2 = new _bsn.AutoSuggest('int_nombre_copropietario', options2);</script>
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
        });</script>
    <script>
        jQuery(function($) {
            $("#ven_fecha").mask("99/99/9999");
            $("#fecha_pago_cuota_cap").mask("99/99/9999");
            $("#fecha_cuota_inicial").mask("99/99/9999");
            $("#fecha_cuota_inicial_cap").mask("99/99/9999");
        });
        ocultar_campos_innecesarios(1);
    <?php
    if ($_POST['datos_lote']) {
        ?>
            cargar_datos('<?php echo $_POST['datos_lote']; ?>');
        <?php
    }
    ?>
    </script>
    <?php
}
?>
