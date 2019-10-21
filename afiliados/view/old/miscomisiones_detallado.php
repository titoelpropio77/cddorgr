<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>
<script src="<?php echo _base_url; ?>recursos/librerias/datatables/media/js/jquery.dataTables.min.js"></script>
<style>
    .alineado_derecho{
        text-align: right;
    }
</style>
<h3>DETALLE DE COMISIONES (<?php echo strtoupper($datos->detalle[0]->periodo);?>)</h3>
<br/>
<!--<h4>Resumen:</h4>-->
<?php
barra_de_impresion();

//echo "<pre>";
//print_r($datos);
//echo "</pre>";

$bir = $datos->detalle[0]->comisiones->BIR;
$bvi = $datos->detalle[0]->comisiones->BVI;
$bra = $datos->detalle[0]->comisiones->BRA;


?>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            
            <ul class="nav nav-tabs">
                <li class="active"><a class="pestania" data-toggle="tab" href="#tab-1">BIR</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-2">BVI</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-3">BRA</a></li>
                
            </ul>
            <div id="contenido_reporte">
            <div class="tab-content">
                
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body">
                        <div class="table-responsive">
                        <!--<h4>Linea 1</h4>-->
                        <table id="tablaResumen1" class="footable table dataTable table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Venta</th>
                                    <th>Afiliado</th>                                                                         
                                    <th data-hide="phone,tablet">Descripcion</th>                                    
                                    <th data-hide="phone,tablet">Importe(USD)</th>
                                    <th data-hide="phone,tablet">%</th>                                                        
                                    <th>Bono(USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hijos1 = $bir;
                                $total_bir = 0;
                                for ($i = 0; $i < count($hijos1); $i++) {
                                    $hijo = $hijos1[$i];
                                    ?>
                                    <tr>
                                        <td class="alineado_derecho"><?php echo $hijo->venta; ?></td>                                        
                                        <td><?php echo $hijo->afiliado; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->com_descripcion; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->importe; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->porcentaje; ?></td>                                            
                                        <td class="alineado_derecho"><?php echo $hijo->monto; ?></td>                                        
                                    </tr>					
                                    <?php
                                    $total_bir += $hijo->monto;
                                }
                                ?>    
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="alineado_derecho" ><b>Total:</b></td>
                                    <td class="alineado_derecho" ><b><?php echo $total_bir;?></b></td>
                                </tr> 
                            </tfoot>
                            
                        </table>
                        </div>
                    </div>
                </div>
                
                <div id="tab-2" class="tab-pane">
                    <div class="panel-body">
                        <div class="table-responsive">
<!--                        <h4>Linea 2</h4>-->
                        <table id="tablaResumen2" class="footable table dataTable table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Venta</th>
                                    <th>Afiliado</th>                                                                         
                                    <th data-hide="phone,tablet">Descripcion</th>                                    
                                    <th data-hide="phone,tablet">Importe(USD)</th>
                                    <th data-hide="phone,tablet">%</th>                                                        
                                    <th>Bono(USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hijos2 = $bvi;
                                $total_bvi = 0;
                                for ($i = 0; $i < count($hijos2); $i++) {
                                    $hijo = $hijos2[$i];
                                    ?>
                                    <tr>
                                        <td class="alineado_derecho"><?php echo $hijo->venta; ?></td>                                        
                                        <td><?php echo $hijo->afiliado; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->com_descripcion; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->importe; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->porcentaje; ?></td>                                            
                                        <td class="alineado_derecho"><?php echo $hijo->monto; ?></td>                                        
                                    </tr>					
                                    <?php
                                    $total_bvi += $hijo->monto;
                                }
                                ?>    
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="alineado_derecho" ><b>Total:</b></td>
                                    <td class="alineado_derecho" ><b><?php echo $total_bvi;?></b></td>
                                </tr> 
                            </tfoot>
                        </table>
                        </div>
                    </div>
                </div>
                
                <div id="tab-3" class="tab-pane">
                    <div class="panel-body">
                        <div class="table-responsive">
<!--                        <h4>Linea 2</h4>-->
                        <table id="tablaResumen3" class="footable table dataTable table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Venta</th>
                                    <th>Afiliado</th>                                                                         
                                    <th data-hide="phone,tablet">Descripcion</th>                                    
                                    <th data-hide="phone,tablet">Importe(USD)</th>
                                    <th data-hide="phone,tablet">%</th>                                                        
                                    <th>Bono(USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hijos3 = $bra;
                                $total_bra = 0;
                                for ($i = 0; $i < count($hijos3); $i++) {
                                    $hijo = $hijos3[$i];
                                    ?>
                                    <tr>
                                        <td class="alineado_derecho"><?php echo $hijo->venta; ?></td>                                        
                                        <td><?php echo $hijo->afiliado; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->com_descripcion; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->importe; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->porcentaje; ?></td>                                            
                                        <td class="alineado_derecho"><?php echo $hijo->monto; ?></td>                                        
                                    </tr>					
                                    <?php
                                    $total_bra += $hijo->monto;
                                }
                                ?>    
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="alineado_derecho" ><b>Total:</b></td>
                                    <td class="alineado_derecho" ><b><?php echo $total_bra;?></b></td>
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



<script>
    $(document).ready(function() {
        $('#tablaResumen1,#tablaResumen2,#tablaResumen3,#tablaResumen4,#tablaResumen5,#tablaResumen6,#tablaResumen7').footable({
            paginate: false
        });

        $('#importar_excel').click(function(e) {

            $('.tableSinpaginar').attr('border', '1');
            window.open('data:application/vnd.ms-excel,' + encodeURIComponent($('#contenido_reporte').html()));
            e.preventDefault();
            $('.tableSinpaginar').attr('border', '0');
        });
        
        $('.fecha').each(function () {
            var val_celda = $(this).text();
            var datos = val_celda.split('-');
            var nFecha = datos[2] + '/' + datos[1] + '/' + datos[0];
            $(this).text(nFecha);
            console.log(val_celda);
        });

    });
    
    $('.pestania').click(function(){
        $(window).trigger('resize');
    });
    
    $('#tablaResumen1,#tablaResumen2,#tablaResumen3,#tablaResumen4,#tablaResumen5,#tablaResumen6,#tablaResumen7').dataTable({paging: false});


</script>
<?php

function barra_de_impresion() {
    $pagina = "'contenido_reporte'";
    $page = "'about:blank'";
    $extpage = "'reportes'";
    $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
    $extra1 = "'<html><head><title>Vista Previa</title>
        
                            <link href=" . _base_url . "recursos/librerias/bootstrap/dist/css/bootstrap.css rel=stylesheet type=text/css />
                                <link href=" . _base_url . "recursos/css/estilos.css rel=stylesheet type=text/css />
                            <link href=" . _base_url . "recursos/css/style.css rel=stylesheet type=text/css />
                            <link href=" . _base_url . "recursos/librerias/fooTable/css/footable.core.min.css rel=stylesheet type=text/css />    
                                
                      </head>
                      <body>
                      <div id=imprimir>
                      <div id=status>
                      <p>";
    $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
                              <a href=javascript:self.close();>Cerrar</a>
                              </p>
                              </div>
                              </div>
                              <center>'";
    $extra2 = "'</center></body></html>'";
//            $myday = setear_fecha(strtotime(date('Y-m-d')));

    echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                      c.document.write(' . $extra1 . ');
                      var dato = document.getElementById(' . $pagina . ').innerHTML;
                      c.document.write(dato);
                      c.document.write(' . $extra2 . '); c.document.close();
                      ">
                    <img src="' . _base_url . 'recursos/img/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                    </a></td><td><img src="' . _base_url . 'recursos/img/excel.png" align="right" border="0" title="EXPORTAR EXCEL" id="importar_excel"></td></tr></table><br><br>
            ';
}
?>