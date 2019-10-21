<?php

class RECIBO {

    public static function anular($nro_recibo,$conec=null) {
        if($conec==null){
            $conec=new ADO();
        }
        $fecha_cre=date('Y-m-d H:i:s');
        $sql_up="update recibo_log set rec_estado='Anulado', rec_fecha_anu='$fecha_cre', rec_usu_anu='$_SESSION[id]' where rec_recibo='$nro_recibo'";
        $conec->ejecutar($sql_up);
    }

    public static function insertar($data,$conec=null) {
        if($conec==null){
            $conec=new ADO();
        }
        $d=(object) $data;
        $fecha_cre=date('Y-m-d H:i:s');
        $sql_insert="insert into recibo_log(
                            rec_recibo,rec_fecha,rec_monto,rec_moneda,rec_tabla,rec_tabla_id,rec_estado,rec_fecha_cre,rec_usu_cre,rec_suc_id
                        )values(
                            '$d->recibo','$d->fecha','$d->monto','$d->moneda','$d->tabla','$d->tabla_id','Activo','$fecha_cre','$_SESSION[id]','$_SESSION[suc_id]'
                        )";
        $conec->ejecutar($sql_insert);
        
    }
    
    public static function pago_ticket($data) {
        $data=(Object)$data;
        ?>
        <style>
            #sel_recibo{font-size: 18px; width: 100px}
        </style>
<!--        <select id="sel_recibo">
            <option value="normal">Normal</option>
            <option value="ticket">Ticket</option>
        </select>-->
        <input type="button" id="imprimir" value="IMPRIMIR" class="boton">
        <br><br>
        <div id="contenido_reporte" style="clear:both;">
            <link href="css/recibos.css" rel="stylesheet" type="text/css" />

            
            
            <?php
            $json_parametros=  FUNCIONES::atributo_bd_sql("select usu_recibo_parametro as campo from ad_usuario where usu_id='$_SESSION[id]'");
            
            if($json_parametros==""){
                $json_parametros='{}';
            }
            $params=  json_decode($json_parametros);
            
            if(!$params->font_size){
                $params->font_size='12px';
            }
            if(!$params->line_height){
                $params->line_height='12px';
            }
            ?>
            <script type='text/javascript' src='js/StarWebPrintBuilder.js'></script>
            <script type='text/javascript' src='js/StarWebPrintTrader.js'></script>
            <style>
                #recibo_ticket{font-size: <?php echo $params->font_size;?> !important;}

            </style>
            <div id="recibo_ticket" style="width: 100%; line-height: <?php echo $params->line_height;?>">
                <div class="box_recibo_titulo">
                    <h3 >RECIBO OFICIAL</h3>
                    <h3 ><b>Nro.</b> <?php echo $data->nro_recibo; ?></h3>
                    <div >(<?php echo $data->titulo?>)</div>
                    <div ><?php echo _nombre_empresa;?></div>
                </div>
                
                <div class="box_recibo_titulo" style="text-align: left; line-height: <?php echo $params->line_height;?>">
                    <b>Recibi de: </b><?php echo $data->referido;?><br>
                    <b>la Suma de </b>
                    <?php
                    $monto=$data->monto;
                    $aux = intval($monto);
                    $str_monto='';
                    if ($aux == ($monto)) {
                        $str_monto .= strtoupper(FUNCIONES::num2letras($monto)) . '&nbsp;&nbsp;00/100';
                        echo $str_monto;
                    } else {
                        $val = explode('.', $monto);
                        $str_monto .=strtoupper(FUNCIONES::num2letras($val[0]));
                        if (strlen($val[1]) == 1)
                            $str_monto .='&nbsp;&nbsp;' . $val[1] . '0/100';
                        else
                            $str_monto .='&nbsp;&nbsp;' . $val[1] . '/100';
                        echo $str_monto;
                    }
                    ?> &nbsp;&nbsp;
                    <?php
                    $str_moneda='';
                    if ($data->moneda == '1')
                        $str_moneda= ' Bolivianos';
                    if ($data->moneda== '2')
                        $str_moneda=' Dolares';
                    ?>
                    <b><?php echo $str_moneda;?></b>
                    <br>
                    <b>Por concepto de: </b><?php echo $data->concepto;?><br>
                    <b>Usuario: </b><?php echo $data->usuario;?>

                </div>
                <!--<hr>-->
                <?php if ($data->has_detalle){?>
                <!--<br>-->
                    <!--<br><br>-->
                    <div style="text-align: left">
                    <?php $heads=$data->det_cabecera;?>
                    <?php $dbodys=$data->det_body;?>
                    <?php for ($i = 0; $i < count($heads); $i++) {?>
                        <?php $head=$heads[$i];?>
                        <?php $body=$dbodys[$i];?>
                        <b><?php echo $head?>: </b><?php echo $body;?><br>
                    <?php }?>
                    </div>
                <?php }?>
                    
                <br><br>
                <div style="font-size: <?php echo $params->font_size;?>; text-align: right; width: 100%">
                    <span class="reciboTextsLinea">
                        <?php
                        $valores = explode('-', $data->fecha);
                        echo $valores[2];
                        ?></span>
                    <span class="reciboLabels">de</span> 
                    <span class="reciboTextsLinea"><?php echo strtoupper(FUNCIONES::nombremes($valores[1])); ?></span>
                    <span class="reciboLabels">del</span> 
                    <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                </div>
                <?php if($data->nota){?>
                <br>
                <div style="font-size: <?php echo $params->font_size;?>; width: 100%">
                    <b>Nota: </b><?php echo $data->nota;?>
                </div>
                <?php }?>
                <table style="width: 100%; ">
                    <tr>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">INTERESADO</span>
                        </td>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">CAJERO(A)</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            $('#imprimir').click(function(){
                imprimir();
            });
            function imprimir() {
                var builder = new StarWebPrintBuilder();

                var request = '';
                request += builder.createInitializationElement();
                request += builder.createTextElement({characterspace: 2});

                request += builder.createAlignmentElement({position: 'center'});
//                request += builder.createLogoElement({number: 1});
                request += builder.createTextElement({data: 'RECIBO OFICIAL\n'});
                request += builder.createTextElement({data: 'Nro. <?php echo $data->nro_recibo;?>\n'});
                request += builder.createTextElement({data: '(<?php echo $data->titulo;?>)\n'});
                request += builder.createTextElement({data: '(<?php echo _nombre_empresa;?>\n'});
                request += builder.createAlignmentElement({position: 'left'});

//                request += builder.createAlignmentElement({position: 'center'});
//                request += builder.createTextElement({data: 'Thank you for your coming. \n'});
//                request += builder.createTextElement({data: "We hope you'll visit again.\n"});
                request += builder.createAlignmentElement({position: 'left'});

                request += builder.createTextElement({data: '\n'});

                request += builder.createTextElement({data: 'Recibi de: <?php echo $data->referido;?>\n'});
                request += builder.createTextElement({data: 'la Suma de <?php echo "$str_monto $str_moneda";?>\n'});
                request += builder.createTextElement({data: 'Por concepto de: <?php echo $data->concepto;?>\n'});
                request += builder.createTextElement({data: 'Usuario: <?php echo $data->usuario;?>\n'});
                <?php for ($i = 0; $i < count($heads); $i++) {?>
                    <?php $head=$heads[$i];?>
                    <?php $body=$dbodys[$i];?>
                    request += builder.createTextElement({data: '<?php echo $head?>: <?php echo $body?>\n'});
                <?php } ?>
//                request += builder.createTextElement({data: 'Orange               $60.00\n'});
                
//                request += builder.createTextElement({emphasis: true, data: 'Subtotal            $200.00\n'});
                request += builder.createTextElement({data: '\n'});
                request += builder.createTextElement({data: '\n'});
                request += builder.createTextElement({data: '\n'});
                request += builder.createTextElement({data: '\n'});
                
                
                request += builder.createAlignmentElement({position: 'center'});
//                request += builder.createLogoElement({number: 1});
                request += builder.createTextElement({data: 'INTERESADO             CAJERO(A)\n'});

//                request += builder.createTextElement({underline: true, data: 'Tax                  $10.00\n'});
                request += builder.createTextElement({underline: false});

                request += builder.createTextElement({emphasis: true});
//                request += builder.createTextElement({width: 2, data: 'Total'});
//                request += builder.createTextElement({width: 1, data: '   '});
//                request += builder.createTextElement({width: 2, data: '$210.00\n'});
                request += builder.createTextElement({width: 1});
                request += builder.createTextElement({emphasis: false});

//                request += builder.createTextElement({data: '\n'});

//                request += builder.createTextElement({data: 'Received            $300.00\n'});

//                request += builder.createTextElement({width: 2, data: 'Change'});
//                request += builder.createTextElement({width: 1, data: '   '});
//                request += builder.createTextElement({width: 2, data: '$90.00\n'});
//                request += builder.createTextElement({characterspace: 0});

                //Paper   
                request += builder.createCutPaperElement({feed: true});
                //http://localhost:8001/StarWebPRNT/SendMessage 

                var trader = new StarWebPrintTrader({url: "http://localhost:8001/StarWebPRNT/SendMessage", papertype: "normal", blackmark_sensor: "front_side"});

                trader.onReceive = function (response) {
                    var msg = '- onReceive -\n\n';
                    msg += 'TraderSuccess : [ ' + response.traderSuccess + ' ]\n';
                    msg += 'TraderStatus : [ ' + response.traderStatus + ',\n';
                    if (trader.isCoverOpen({traderStatus: response.traderStatus})) {
                        msg += '\tCoverOpen,\n';
                    }
                    if (trader.isOffLine({traderStatus: response.traderStatus})) {
                        msg += '\tOffLine,\n';
                    }
                    if (trader.isCompulsionSwitchClose({traderStatus: response.traderStatus})) {
                        msg += '\tCompulsionSwitchClose,\n';
                    }
                    if (trader.isEtbCommandExecute({traderStatus: response.traderStatus})) {
                        msg += '\tEtbCommandExecute,\n';
                    }
                    if (trader.isHighTemperatureStop({traderStatus: response.traderStatus})) {
                        msg += '\tHighTemperatureStop,\n';
                    }
                    if (trader.isNonRecoverableError({traderStatus: response.traderStatus})) {
                        msg += '\tNonRecoverableError,\n';
                    }
                    if (trader.isAutoCutterError({traderStatus: response.traderStatus})) {
                        msg += '\tAutoCutterError,\n';
                    }
                    if (trader.isBlackMarkError({traderStatus: response.traderStatus})) {
                        msg += '\tBlackMarkError,\n';
                    }
                    if (trader.isPaperEnd({traderStatus: response.traderStatus})) {
                        msg += '\tPaperEnd,\n';
                    }
                    if (trader.isPaperNearEnd({traderStatus: response.traderStatus})) {
                        msg += '\tPaperNearEnd,\n';
                    }
                    msg += '\tEtbCounter = ' + trader.extractionEtbCounter({traderStatus: response.traderStatus}).toString() + ' ]\n';
                    alert(msg);
                }

                trader.onError = function (response) {
                    var msg = '- onError -\n\n';
                    msg += '\tStatus:' + response.status + '\n';
                    msg += '\tResponseText:' + response.responseText;
                    alert(msg);
                }

                trader.sendMessage({request: request});

            }
//            $('#sel_recibo').change(function(){
//                var val=$(this).val();
//                if(val==='normal'){
//                    $('#recibo_normal').show();
//                    $('#recibo_ticket').hide();
//                }else if(val==='ticket'){
//                    $('#recibo_normal').hide();
//                    $('#recibo_ticket').show();
//                }
//            });
//            $('#sel_recibo').trigger('change');
        </script>
        <?php
    }
    public static function pago_old_version($data) {
        $data=(Object)$data;
        ?>
        <style>
            #sel_recibo{font-size: 18px; width: 100px}
        </style>
        <select id="sel_recibo">
            <option value="normal">Normal</option>
            <option value="ticket">Ticket</option>
        </select>
        <br><br>
        <div id="contenido_reporte" style="clear:both;">
            <link href="css/recibos.css" rel="stylesheet" type="text/css" />

            <div id="recibo_normal" >
                <div class="recibo">
                    <div class="reciboTop">
                        <img class="reciboLogo" src="imagenes/micro.png" width="150" height="80"alt="">
                        <div class="reciboTi">
                            <div class="reciboText">RECIBO OFICIAL</div>
                            <div class="reciboNum"><b>Nro.</b> <?php echo $data->nro_recibo; ?></div>
                            <div class="reciboText"><h5>(<?php echo $data->titulo?>)</h5></div>
                        </div>
                        <div class="reciboMoney">
                            <div class="reciboCapa">
                                <div class="reciboLabel">
                                    <?php
                                    if ($data->moneda == '1')echo 'Bs.';else echo '$us.';
                                    ?>
                                </div>
                                <div class="reciboMonto">
                                    <?php echo number_format($data->monto, 2, '.', ','); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="reciboCont">
                        <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">Recibido de:</span> 
                                    <span class="reciboTexts"> <?php echo $data->referido; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">La Suma de:</span>
                                    <span class="reciboTexts"><?php
                                        $monto=$data->monto;
                                        $aux = intval($monto);
                                        if ($aux == ($monto)) {
                                            echo strtoupper(FUNCIONES::num2letras($monto)) . '&nbsp;&nbsp;00/100';
                                        } else {
                                            $val = explode('.', $monto);
                                            echo strtoupper(FUNCIONES::num2letras($val[0]));
                                            if (strlen($val[1]) == 1)
                                                echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                                            else
                                                echo '&nbsp;&nbsp;' . $val[1] . '/100';
                                        }
                                        ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    <span class="reciboLabels">
                                        <?php
                                        if ($data->moneda == '1')
                                            echo 'Bolivianos';
                                        if ($data->moneda== '2')
                                            echo 'Dolares';
                                        ?>
                                    </span> 
                                </td>
                            </tr>

                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">Por concepto de:</span> 
                                    <span class="reciboTexts"> <?php echo $data->concepto; ?></span>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <?php if ($data->has_detalle){?>
                            <table class="recibo_detalle">
                                <thead>
                                    <tr>
                                        <?php foreach ($data->det_cabecera as $head) {?>
                                            <th><?php echo $head?></th>
                                        <?php }?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php foreach ($data->det_body as $body) {?>
                                            <td><?php echo $body?></td>
                                        <?php }?>
                                    </tr>
                                </tbody>
                            </table>
                        <?php }?>

                        <br><br>
                        <div style="font-size: 14px; text-align: right;">
                            <span class="reciboTextsLinea">
                                <?php
                                $valores = explode('-', $data->fecha);
                                echo $valores[2];
                                ?></span>
                            <span class="reciboLabels">de</span> 
                            <span class="reciboTextsLinea"><?php echo strtoupper(FUNCIONES::nombremes($valores[1])); ?></span>
                            <span class="reciboLabels">del</span> 
                            <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                        </div>
                        <?php if($data->nota){?>
                        <div style="font-size: 14px; ">
                            <b>Nota: </b><?php echo $data->nota;?>
                        </div>
                        <?php }?>
                        <table style="width: 100%">
                            <tr>
                                <td class="reciboFirma " colspan="2">
                                    <span class="reciboTextsLinea"> </span>
                                    <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $data->referido; ?></b></span>
                                </td>
                                <td class="reciboFirma " colspan="2">
                                    <span class="reciboTextsLinea"> </span>
                                    <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo $data->usuario; ?></b></span>
                                </td>
                            </tr>
                        </table>
                    </div><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
                </div>
            </div>
            
            <?php
            $json_parametros=  FUNCIONES::atributo_bd_sql("select usu_recibo_parametro as campo from ad_usuario where usu_id='$_SESSION[id]'");
            
            if($json_parametros==""){
                $json_parametros='{}';
            }
            $params=  json_decode($json_parametros);
            
            if(!$params->font_size){
                $params->font_size='12px';
            }
            if(!$params->line_height){
                $params->line_height='12px';
            }
            ?>
            <style>
                #recibo_ticket{font-size: <?php echo $params->font_size;?> !important;}

            </style>
            <div id="recibo_ticket" style="width: 100%; line-height: <?php echo $params->line_height;?>">
                <div class="box_recibo_titulo">
                    <h3 >RECIBO OFICIAL</h3>
                    <h3 ><b>Nro.</b> <?php echo $data->nro_recibo; ?></h3>
                    <div >(<?php echo $data->titulo?>)</div>
                    <div ><?php echo _nombre_empresa;?></div>
                </div>
                <hr>
                <div class="box_recibo_titulo" style="text-align: left; line-height: <?php echo $params->line_height;?>">
                    <b>Recibi de: </b><?php echo $data->referido;?><br>
                    <b>la Suma de </b>
                    <?php
                    $monto=$data->monto;
                    $aux = intval($monto);
                    if ($aux == ($monto)) {
                        echo strtoupper(FUNCIONES::num2letras($monto)) . '&nbsp;&nbsp;00/100';
                    } else {
                        $val = explode('.', $monto);
                        echo strtoupper(FUNCIONES::num2letras($val[0]));
                        if (strlen($val[1]) == 1)
                            echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                        else
                            echo '&nbsp;&nbsp;' . $val[1] . '/100';
                    }
                    ?> &nbsp;&nbsp;
                    <?php
                    if ($data->moneda == '1')
                        echo '<b>Bolivianos</b>';
                    if ($data->moneda== '2')
                        echo '<b>Dolares</b>';
                    ?><br>
                    <b>Por concepto de: </b><?php echo $data->concepto;?><br>
                    <b>Usuario: </b><?php echo $data->usuario;?>

                </div>
                <!--<hr>-->
                <?php if ($data->has_detalle){?>
                <!--<br>-->
                <table class="recibo_detalle" style="width: 100%; font-size: <?php echo $params->font_size;?> !important; margin-top: 10px">
                        <thead>
                            <tr>
                                <?php foreach ($data->det_cabecera as $head) {?>
                                    <th><?php echo $head?></th>
                                <?php }?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach ($data->det_body as $body) {?>
                                    <td><?php echo $body?></td>
                                <?php }?>
                            </tr>
                        </tbody>
                    </table>
                <?php }?>
                <br><br>
                <div style="font-size: <?php echo $params->font_size;?>; text-align: right; width: 100%">
                    <span class="reciboTextsLinea">
                        <?php
                        $valores = explode('-', $data->fecha);
                        echo $valores[2];
                        ?></span>
                    <span class="reciboLabels">de</span> 
                    <span class="reciboTextsLinea"><?php echo strtoupper(FUNCIONES::nombremes($valores[1])); ?></span>
                    <span class="reciboLabels">del</span> 
                    <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                </div>
                <?php if($data->nota){?>
                <br>
                <div style="font-size: <?php echo $params->font_size;?>; width: 100%">
                    <b>Nota: </b><?php echo $data->nota;?>
                </div>
                <?php }?>
                <table style="width: 100%; ">
                    <tr>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">INTERESADO</span>
                        </td>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">CAJERO(A)</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            $('#sel_recibo').change(function(){
                var val=$(this).val();
                if(val==='normal'){
                    $('#recibo_normal').show();
                    $('#recibo_ticket').hide();
                }else if(val==='ticket'){
                    $('#recibo_normal').hide();
                    $('#recibo_ticket').show();
                }
            });
            $('#sel_recibo').trigger('change');
        </script>
        <?php
    }

    public static function pago($data) {
        $data=(Object)$data;
        ?>
        <style>
            #sel_recibo{font-size: 18px; width: 100px}
        </style>
        <select id="sel_recibo">
            <option value="normal">Normal</option>
            <option value="ticket">Ticket</option>
        </select>
        <br><br>
        <div id="contenido_reporte" style="clear:both;">
            <link href="css/recibos.css" rel="stylesheet" type="text/css" />

            <div id="recibo_normal" >
                <div class="recibo">
                    <div class="reciboTop">
                        <img class="reciboLogo" src="imagenes/micro.png" width="150" height="80"alt="">
                        <div class="reciboTi">
                            <div class="reciboText">RECIBO OFICIAL</div>
                            <div class="reciboNum"><b>Nro.</b> <?php echo $data->nro_recibo; ?></div>
                            <div class="reciboText"><h5>(<?php echo $data->titulo?>)</h5></div>
                        </div>
                        <div class="reciboMoney">
                            <div class="reciboCapa">
                                <div class="reciboLabel">
                                    <?php
                                    if ($data->moneda == '1')echo 'Bs.';else echo '$us.';
                                    ?>
                                </div>
                                <div class="reciboMonto">
                                    <?php echo number_format($data->monto, 2, '.', ','); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="reciboCont">
                        <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">Recibido de:</span> 
                                    <span class="reciboTexts"> <?php echo $data->referido; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">La Suma de:</span>
                                    <span class="reciboTexts"><?php
                                        $monto=$data->monto;
                                        $aux = intval($monto);
                                        if ($aux == ($monto)) {
                                            echo strtoupper(FUNCIONES::num2letras($monto)) . '&nbsp;&nbsp;00/100';
                                        } else {
                                            $val = explode('.', $monto);
                                            echo strtoupper(FUNCIONES::num2letras($val[0]));
                                            if (strlen($val[1]) == 1)
                                                echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                                            else
                                                echo '&nbsp;&nbsp;' . $val[1] . '/100';
                                        }
                                        ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    <span class="reciboLabels">
                                        <?php
                                        if ($data->moneda == '1')
                                            echo 'Bolivianos';
                                        if ($data->moneda== '2')
                                            echo 'Dolares';
                                        ?>
                                    </span> 
                                </td>
                            </tr>

                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">Por concepto de:</span> 
                                    <span class="reciboTexts"> <?php echo $data->concepto; ?></span>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <?php if ($data->has_detalle){?>
                            <table class="recibo_detalle">
                                <thead>
                                    <tr>
                                        <?php foreach ($data->det_cabecera as $head) {?>
                                            <th><?php echo $head?></th>
                                        <?php }?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php foreach ($data->det_body as $body) {?>
                                            <td><?php echo $body?></td>
                                        <?php }?>
                                    </tr>
                                </tbody>
                            </table>
                        <?php }?>

                        <br><br>
                        <div style="font-size: 14px; text-align: right;">
                            <span class="reciboTextsLinea">
                                <?php
                                $valores = explode('-', $data->fecha);
                                echo $valores[2];
                                ?></span>
                            <span class="reciboLabels">de</span> 
                            <span class="reciboTextsLinea"><?php echo strtoupper(FUNCIONES::nombremes($valores[1])); ?></span>
                            <span class="reciboLabels">del</span> 
                            <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                        </div>
                        <?php if($data->nota){?>
                        <div style="font-size: 14px; ">
                            <b>Nota: </b><?php echo $data->nota;?>
                        </div>
                        <?php }?>
                        <table style="width: 100%">
                            <tr>
                                <td class="reciboFirma " colspan="2">
                                    <span class="reciboTextsLinea"> </span>
                                    <span class="reciboLabelFirma">ENTREGUE CONFORME<br><b><?php echo $data->referido; ?></b></span>
                                </td>
                                <td class="reciboFirma " colspan="2">
                                    <span class="reciboTextsLinea"> </span>
                                    <span class="reciboLabelFirma">RECIBI CONFORME<br><b><?php echo $data->usuario; ?></b></span>
                                </td>
                            </tr>
                        </table>
                    </div><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
                </div>
            </div>
            
            <?php
            $json_parametros=  FUNCIONES::atributo_bd_sql("select usu_recibo_parametro as campo from ad_usuario where usu_id='$_SESSION[id]'");
            
            if($json_parametros==""){
                $json_parametros='{}';
            }
            $params=  json_decode($json_parametros);
            
            if(!$params->font_size){
//                $params->font_size='12px';
                $params->font_size='10px';
            }
            if(!$params->line_height){
                $params->line_height='12px';
            }
            $params->font_size='10px';
            ?>
            <style>
                #recibo_ticket{font-size: <?php echo $params->font_size;?> !important;}

            </style>
            <div id="recibo_ticket" style="width: 100%; line-height: <?php echo $params->line_height;?>">
                <div class="box_recibo_titulo">
                    <h3 >RECIBO OFICIAL</h3>
                    <h3 ><b>Nro.</b> <?php echo $data->nro_recibo; ?></h3>
                    <div >(<?php echo $data->titulo?>)</div>
                    <div ><?php echo _nombre_empresa;?></div>
                </div>
                <hr>
                <div class="box_recibo_titulo" style="text-align: left; line-height: <?php echo $params->line_height;?>">
                    <b>Recibi de: </b><?php echo $data->referido;?><br>
                    <b>la Suma de </b>
                    <?php
                    $monto=$data->monto;
                    $aux = intval($monto);
                    if ($aux == ($monto)) {
                        echo strtoupper(FUNCIONES::num2letras($monto)) . '&nbsp;&nbsp;00/100';
                    } else {
                        $val = explode('.', $monto);
                        echo strtoupper(FUNCIONES::num2letras($val[0]));
                        if (strlen($val[1]) == 1)
                            echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                        else
                            echo '&nbsp;&nbsp;' . $val[1] . '/100';
                    }
                    ?> &nbsp;&nbsp;
                    <?php
                    if ($data->moneda == '1')
                        echo '<b>Bolivianos</b>';
                    if ($data->moneda== '2')
                        echo '<b>Dolares</b>';
                    ?><br>
                    <b>Por concepto de: </b><?php echo $data->concepto;?><br>
                    <b>Usuario: </b><?php echo $data->usuario;?>

                </div>
                <!--<hr>-->
                <?php if ($data->has_detalle){?>
                <!--<br>-->
                <table class="recibo_detalle" style="width: 100%; font-size: <?php echo $params->font_size;?> !important; margin-top: 10px">
                        <thead>
<!--                            <tr>
                                <?php // foreach ($data->det_cabecera as $head) {?>
                                    <th><?php // echo $head?></th>
                                <?php // }?>
                            </tr>-->
                            <tr>
                                <th>Concepto</th> 
                                <th>Valor</th> 
                            </tr>
                        </thead>
                        <tbody>                          
                            <!--<tr>-->
                                <?php // foreach ($data->det_body as $body) {?>
                                    <td><?php // echo $body."shit"?></td>
                                <?php // }?>
                            <!--</tr>-->
                            <?php 
                            for ($i = 0; $i < count($data->det_body); $i++) {
                                $body = $data->det_body[$i];
                                $head = $data->det_cabecera[$i];
                                ?>
                            <tr>
                                    <td><?php echo $head.""?></td>
                                    <td style="text-align: right;"><?php echo $body.""?></td>
                                
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                <?php }?>
                <br><br>
                <div style="font-size: <?php echo $params->font_size;?>; text-align: right; width: 100%">
                    <span class="reciboTextsLinea">
                        <?php
                        $valores = explode('-', $data->fecha);
                        echo $valores[2];
                        ?></span>
                    <span class="reciboLabels">de</span> 
                    <span class="reciboTextsLinea"><?php echo strtoupper(FUNCIONES::nombremes($valores[1])); ?></span>
                    <span class="reciboLabels">del</span> 
                    <span class="reciboTextsLinea"><?php echo $valores[0]; ?></span>
                </div>
                <?php if($data->nota){?>
                <br>
                <div style="font-size: <?php echo $params->font_size;?>; width: 100%">
                    <b>Nota: </b><?php echo $data->nota;?>
                </div>
                <?php }?>
                <table style="width: 100%; font-size: <?php echo $params->font_size;?>">
                    <tr>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">INTERESADO</span>
                        </td>
                        <td class="reciboFirma reciboCenter" colspan="2">
                            ------------------------ <br>
                            <span class="">CAJERO(A)</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            $('#sel_recibo').change(function(){
                var val=$(this).val();
                if(val==='normal'){
                    $('#recibo_normal').show();
                    $('#recibo_ticket').hide();
                }else if(val==='ticket'){
                    $('#recibo_normal').hide();
                    $('#recibo_ticket').show();
                }
            });
            $('#sel_recibo').trigger('change');
        </script>
        <?php
    }
}