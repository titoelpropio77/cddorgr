<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>
<style>
    .alineado_derecho{
        text-align: right;
    }
</style>
<h3>MIS COMISIONES <?php echo strtoupper($datos->BIR->estado); ?></h3>
<br/>
<!--<h4>Resumen:</h4>-->
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">

            <div id="accordion" class="panel-group" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">

                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne" class="collapsed">
                                RESUMEN:
                            </a>
                        </h4>
                    </div>

                    <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" aria-expanded="false" style="height: 0px;">

                        <div class="tab-content">

                            <div id="tab-resumen" class="tab-pane active">
                                <div class="panel-body">
                                    <table id="tablaResumen" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>                                                                         
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>B. de Inicio Rapido</td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BIR->total_monto, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BIR->total_pagado, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BIR->total_saldo, 2, '.', ','); ?></td>
                                                <td>Dolares</td>
                                            </tr>
                                            <tr>
                                                <td>B. de Venta Indirecta</td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BVI->total_monto, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BVI->total_pagado, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BVI->total_saldo, 2, '.', ','); ?></td>
                                                <td>Dolares</td>
                                            </tr>
                                            <tr>
                                                <td>B. Residual Abierto</td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BRA->total_monto, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BRA->total_pagado, 2, '.', ','); ?></td>
                                                <td class="alineado_derecho"><?php echo number_format($datos->BRA->total_saldo, 2, '.', ','); ?></td>
                                                <td>Dolares</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="alineado_derecho"><b>Totales:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format(($datos->BIR->total_monto + $datos->BVI->total_monto + $datos->BRA->total_monto), 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format(($datos->BIR->total_pagado + $datos->BVI->total_pagado + $datos->BRA->total_pagado), 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format(($datos->BIR->total_saldo + $datos->BVI->total_saldo + $datos->BRA->total_saldo), 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                            </div>

                        </div>

                    </div>



                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingTwo">
                        <h4 class="panel-title">
                            <a id="a_finaciero" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseOne" class="collapsed">
                                Pendientes:
                            </a>
                        </h4>
                    </div>

                    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" aria-expanded="false" style="height: 0px;">
                        <ul class="nav nav-tabs">
            <!--<li class="active"><a data-toggle="tab" href="#tab-1"> <?php echo $datos->BIR->titulo; ?> </a></li>
            <li class=""><a data-toggle="tab" href="#tab-2"> <?php echo $datos->BVI->titulo; ?> </a></li>
            <li class=""><a data-toggle="tab" href="#tab-3"> <?php echo $datos->BRA->titulo; ?> </a></li>-->

                            <li class="active"><a data-toggle="tab" href="#tab-1"> <?php echo strtoupper("Bono de Inicio Rapido"); ?> </a></li>
                            <li class=""><a data-toggle="tab" href="#tab-2"> <?php echo strtoupper("Bono de Venta Indirecta"); ?> </a></li>
                            <li class=""><a data-toggle="tab" href="#tab-3"> <?php echo strtoupper("Bono Residual Abierto"); ?> </a></li>
                            <li class=""><a data-toggle="tab" href="#tab-4"> <?php echo strtoupper("Fondo Equipo Diamante"); ?> </a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane active">
                                <div class="panel-body">




                                    <table id="tablaCom1" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BIR->lista as $key => $value) {
                                                if ($value->estado == 'Pendiente') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>

                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>
                                </div>




                            </div>

                            <div id="tab-2" class="tab-pane">
                                <div class="panel-body">

                                    <table id="tablaCom2" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th data-hide="phone,tablet">Vendedor</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BVI->lista as $key => $value) {
                                                if ($value->estado == 'Pendiente') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>                                        
                                                    <td><?php echo $value->vendedor; ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>

                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>

                                </div>
                            </div>

                            <div id="tab-3" class="tab-pane">
                                <div class="panel-body">

                                    <table id="tablaCom3" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BRA->lista as $key => $value) {
                                                if ($value->estado == 'Pendiente') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>                                        
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>


                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
                
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingTwo">
                        <h4 class="panel-title">
                            <a id="a_finaciero" data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" aria-controls="collapseOne" class="collapsed">
                                Pagadas:
                            </a>
                        </h4>
                    </div>

                    <div id="collapse3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" aria-expanded="false" style="height: 0px;">
                        <ul class="nav nav-tabs">
            <!--<li class="active"><a data-toggle="tab" href="#tab-1"> <?php echo $datos->BIR->titulo; ?> </a></li>
            <li class=""><a data-toggle="tab" href="#tab-2"> <?php echo $datos->BVI->titulo; ?> </a></li>
            <li class=""><a data-toggle="tab" href="#tab-3"> <?php echo $datos->BRA->titulo; ?> </a></li>-->

                            <li class="active"><a data-toggle="tab" href="#tab-11"> <?php echo strtoupper("Bono de Inicio Rapido"); ?> </a></li>
                            <li class=""><a data-toggle="tab" href="#tab-22"> <?php echo strtoupper("Bono de Venta Indirecta"); ?> </a></li>
                            <li class=""><a data-toggle="tab" href="#tab-33"> <?php echo strtoupper("Bono Residual Abierto"); ?> </a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-11" class="tab-pane active">
                                <div class="panel-body">
                                    <table id="tablaCom11" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BIR->lista as $key => $value) {
                                                if ($value->estado == 'Pagado') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>

                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>
                                </div>




                            </div>

                            <div id="tab-22" class="tab-pane">
                                <div class="panel-body">

                                    <table id="tablaCom22" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th data-hide="phone,tablet">Vendedor</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BVI->lista as $key => $value) {
                                                if ($value->estado == 'Pagado') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>                                        
                                                    <td><?php echo $value->vendedor; ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>

                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>

                                </div>
                            </div>

                            <div id="tab-33" class="tab-pane">
                                <div class="panel-body">

                                    <table id="tablaCom33" class="footable table table-bordered table-hover tableSinpaginar" >
                                        <thead>
                                            <tr>
                                                <th>Venta</th> 
                                                <th data-hide="phone,tablet">Titular</th>
                                                <th data-hide="phone,tablet">Observacion</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>                                    
                                                <th>Pagado</th>
                                                <th>Saldo</th>
                                                <th>Moneda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totMonto = 0;
                                            $totPagado = 0;
                                            $totSaldo = 0;
                                            foreach ($datos->BRA->lista as $key => $value) {
                                                if ($value->estado == 'Pagado') {
                                                ?>
                                                <tr>
                                                    <td><?php echo $value->venta; ?></td>
                                                    <td><?php echo $value->titular; ?></td>                                        
                                                    <td><?php echo $value->observacion; ?></td>
                                                    <td><?php echo $value->fecha; ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->monto, 2, '.', ','); ?></td>
                                                    <td class="alineado_derecho"><?php echo number_format($value->pagado, 2, '.', ','); ?></td>                                        
                                                    <td class="alineado_derecho"><?php echo number_format($value->saldo, 2, '.', ','); ?></td> 
                                                    <td><?php echo $value->moneda; ?></td>                                        
                                                </tr>
                                                <?php
                                                $totMonto += $value->monto;
                                                $totPagado += $value->pagado;
                                                $totSaldo += $value->saldo;
                                                }
                                            }
                                            ?>


                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align:right"><b>Subtotal:</b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totMonto, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totPagado, 2, '.', ','); ?></b></td>
                                                <td class="alineado_derecho"><b><?php echo number_format($totSaldo, 2, '.', ','); ?></b></td>
                                                <td>&nbsp;</td>
                                            </tr> 
                                        </tfoot>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
            </div>


        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $('#tablaCom1,#tablaCom2,#tablaCom3,#tablaCom11,#tablaCom22,#tablaCom33').footable({
            paginate: false
        });
    });

</script>
