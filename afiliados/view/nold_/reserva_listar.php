<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>

<h3>MIS RESERVAS</h3>

<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <ul class="nav nav-tabs">
                <li class="active"><a class="pestania" data-toggle="tab" href="#tab-1">Reservas sin dinero</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-2">Reservas con dinero</a></li>
                <!--<li class=""><a data-toggle="tab" href="#tab-3"> <?php // echo $datos->DES->estado;   ?> </a></li>-->
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-4">Concretadas</a></li>
                <!--<li class=""><a data-toggle="tab" href="#tab-5"> <?php echo $datos->EXP->estado; ?> </a></li>-->
                <!--<li class=""><a data-toggle="tab" href="#tab-6"> <?php echo $datos->DEV->estado; ?> </a></li>-->
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tablaPend" class="footable table table-bordered table-hover tableSinpaginar" >
                                <thead>
                                    <tr>
                                        <th>Nro</th> 
                                        <th data-hide="phone,tablet">Fecha</th>
                                        <th data-hide="phone,tablet">Hora</th>                                        
                                        <th>Cliente</th>
                                        <th data-hide="phone,tablet">Patrocinador</th> 
                                        <th data-hide="phone,tablet">Descripcion</th> 
                                        <th data-hide="phone,tablet">Valor Terreno</th> 
                                        <th data-hide="phone,tablet">Cuota Inicial</th>    
                                        <th data-hide="phone,tablet">C.I. Pagado</th>
                                        <th data-hide="phone,tablet">C.I. Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($datos->PEND->lista as $key => $value) {
                                        $saldo_ci = $value->cuota_inicial - $value->ci_pagado;
                                        ?>
                                        <tr>
                                            <td><?php echo $value->nro; ?></td>
                                            <td><?php echo $value->fecha; ?></td>
                                            <td><?php echo $value->hora; ?></td>
                                            <td><?php echo $value->cliente; ?></td>                                        
                                            <td><?php echo $value->vendedor; ?></td>
                                            <td><?php echo $value->descripcion; ?></td>                                                                                
                                            <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($value->cuota_inicial, 2, '.', ',') . " {$value->moneda}"; ?></td>                                       
                                            <td><?php echo number_format($value->ci_pagado, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($saldo_ci, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody> 
                                <tfoot>
                                    <tr>
                                        <td colspan="10" >&nbsp;</td>                                    
                                    </tr> 
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="tab-2" class="tab-pane">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tablaHab" class="footable table table-bordered table-hover tableSinpaginar" >
                                <thead>
                                    <tr>
                                        <th>Nro</th> 
                                        <th data-hide="phone,tablet">Fecha</th>
                                        <th data-hide="phone,tablet">Hora</th>
                                        <th>Cliente</th>
                                        <th data-hide="phone,tablet">Patrocinador</th> 
                                        <th data-hide="phone,tablet">Descripcion</th> 
                                        <th data-hide="phone,tablet">Valor Terreno</th> 
                                        <th data-hide="phone,tablet">Cuota Inicial</th>
                                        <th data-hide="phone,tablet">C.I. Pagado</th>
                                        <th data-hide="phone,tablet">C.I. Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($datos->HAB->lista as $key => $value) {
                                        $saldo_ci = $value->cuota_inicial - $value->ci_pagado;
                                        ?>
                                        <tr>
                                            <td><?php echo $value->nro; ?></td>
                                            <td><?php echo $value->fecha; ?></td>
                                            <td><?php echo $value->hora; ?></td>
                                            <td><?php echo $value->cliente; ?></td>                                        
                                            <td><?php echo $value->vendedor; ?></td>
                                            <td><?php echo $value->descripcion; ?></td>                                                                                
                                            <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($value->cuota_inicial, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($value->ci_pagado, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($saldo_ci, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody> 
                                <tfoot>
                                    <tr>
                                        <td colspan="10" >&nbsp;</td>
                                    </tr> 
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

                <!--                <div id="tab-3" class="tab-pane">
                                    <div class="panel-body">
                
                                        <table id="tablaDes" class="footable table table-bordered table-hover tableSinpaginar" >
                                            <thead>
                                                <tr>
                                                    <th>Nro</th> 
                                                    <th data-hide="phone,tablet">Fecha</th>
                                                    <th data-hide="phone,tablet">Hora</th>
                                                    <th>Cliente</th>
                                                    <th>Patrocinador</th> 
                                                    <th>Descripcion</th> 
                                                    <th>Valor Terreno</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                <?php
                foreach ($datos->DES->lista as $key => $value) {
                    ?>
                                                            <tr>
                                                                <td><?php echo $value->nro; ?></td>
                                                                <td><?php echo $value->fecha; ?></td>
                                                                <td><?php echo $value->hora; ?></td>
                                                                <td><?php echo $value->cliente; ?></td>                                        
                                                                <td><?php echo $value->vendedor; ?></td>
                                                                <td><?php echo $value->descripcion; ?></td>                                                                                
                                                                <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>                                                                                
                                                            </tr>
                    <?php
                }
                ?>
                
                                            </tbody> 
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7" >&nbsp;</td>
                                                </tr> 
                                            </tfoot>
                                        </table>
                
                                    </div>
                                </div>-->

                <div id="tab-4" class="tab-pane">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tablaConc" class="footable table table-bordered table-hover tableSinpaginar" >
                                <thead>
                                    <tr>
                                        <th>Nro</th> 
                                        <th data-hide="phone,tablet">Fecha</th>
                                        <th data-hide="phone,tablet">Hora</th>
                                        <th>Cliente</th>
                                        <th data-hide="phone,tablet">Patrocinador</th> 
                                        <th data-hide="phone,tablet">Descripcion</th> 
                                        <th data-hide="phone,tablet">Valor Terreno</th>
                                        <th data-hide="phone,tablet">Cuota Inicial</th>
                                        <th data-hide="phone,tablet">C.I. Pagado</th>
                                        <th data-hide="phone,tablet">C.I. Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($datos->CONC->lista as $key => $value) {
                                        $saldo_ci = $value->cuota_inicial - $value->ci_pagado;
                                        ?>
                                        <tr>
                                            <td><?php echo $value->nro; ?></td>
                                            <td><?php echo $value->fecha; ?></td>
                                            <td><?php echo $value->hora; ?></td>
                                            <td><?php echo $value->cliente; ?></td>                                        
                                            <td><?php echo $value->vendedor; ?></td>
                                            <td><?php echo $value->descripcion; ?></td>                                                                                
                                            <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($value->cuota_inicial, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($value->ci_pagado, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                            <td><?php echo number_format($saldo_ci, 2, '.', ',') . " {$value->moneda}"; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody> 
                                <tfoot>
                                    <tr>
                                        <td colspan="10" >&nbsp;</td>
                                    </tr> 
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-5" class="tab-pane" style="display: none;">
                    <div class="panel-body">

                        <table id="tablaExp" class="footable table table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Nro</th> 
                                    <th data-hide="phone,tablet">Fecha</th>
                                    <th data-hide="phone,tablet">Hora</th>
                                    <th>Cliente</th>
                                    <th>Patrocinador</th> 
                                    <th>Descripcion</th> 
                                    <th>Valor Terreno</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($datos->EXP->lista as $key => $value) {
                                    ?>
                                    <tr>
                                        <td><?php echo $value->nro; ?></td>
                                        <td><?php echo $value->fecha; ?></td>
                                        <td><?php echo $value->hora; ?></td>
                                        <td><?php echo $value->cliente; ?></td>                                        
                                        <td><?php echo $value->vendedor; ?></td>
                                        <td><?php echo $value->descripcion; ?></td>                                                                                
                                        <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>                                                                                
                                    </tr>
                                    <?php
                                }
                                ?>

                            </tbody> 
                            <tfoot>
                                <tr>
                                    <td colspan="7" >&nbsp;</td>
                                </tr> 
                            </tfoot>
                        </table>

                    </div>
                </div>

                <div id="tab-6" class="tab-pane" style="display: none;">
                    <div class="panel-body">

                        <table id="tablaDev" class="footable table table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Nro</th> 
                                    <th data-hide="phone,tablet">Fecha</th>
                                    <th data-hide="phone,tablet">Hora</th>
                                    <th>Cliente</th>
                                    <th>Patrocinador</th> 
                                    <th>Descripcion</th> 
                                    <th>Valor Terreno</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($datos->DEV->lista as $key => $value) {
                                    ?>
                                    <tr>
                                        <td><?php echo $value->nro; ?></td>
                                        <td><?php echo $value->fecha; ?></td>
                                        <td><?php echo $value->hora; ?></td>
                                        <td><?php echo $value->cliente; ?></td>                                        
                                        <td><?php echo $value->vendedor; ?></td>
                                        <td><?php echo $value->descripcion; ?></td>                                                                                
                                        <td><?php echo number_format($value->valor_terreno, 2, '.', ',') . " {$value->moneda}"; ?></td>                                                                                
                                    </tr>
                                    <?php
                                }
                                ?>

                            </tbody> 
                            <tfoot>
                                <tr>
                                    <td colspan="7" >&nbsp;</td>
                                </tr> 
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>


        </div>
    </div>


</div>

<script>
    $(document).ready(function() {
        $('#tablaPend,#tablaHab,#tablaDes,#tablaConc,#tablaExp,#tablaDev').footable({
            paginate: false
        });
    });
        
    $('.pestania').click(function(){
        $(window).trigger('resize');
    });

</script>
