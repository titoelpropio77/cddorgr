<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>
<style>
    .alineado_derecho{
        text-align: right;
    }
</style>
<h3>HISTORIAL DE COMISIONES <?php echo strtoupper($datos->BIR->estado); ?></h3>
<br/>
<!--<h4>Resumen:</h4>-->
<?php
barra_de_impresion();
?>
<div class="row">
    <div class="col-lg-12">
        <div id="contenido_reporte" class="hpanel">
            <div class="tab-content">

                <div id="tab-resumen" class="tab-pane active">
                    <div class="panel-body">
                        <table id="tablaResumen" class="footable table table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Periodo</th>                                                                         
                                    <th>BIR</th>                                    
                                    <th>BVI</th>
                                    <th>BRA</th>
                                    <th>Total</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $coms_per = $datos->historial;
                                $total = 0;
                                $tbir = 0;
                                $tbvi = 0;
                                $tbra = 0;
                                for ($i = 0; $i < count($coms_per); $i++) {
                                    $com = $coms_per[$i];
                                    ?>
                                    <tr>
                                        <td><?php echo $com->periodo; ?></td>
                                        <td class="alineado_derecho"><?php echo number_format($com->comisiones->BIR, 2, '.', ','); ?></td>
                                        <td class="alineado_derecho"><?php echo number_format($com->comisiones->BVI, 2, '.', ','); ?></td>
                                        <td class="alineado_derecho"><?php echo number_format($com->comisiones->BRA, 2, '.', ','); ?></td>
                                        <?php
                                        $tbir += $com->comisiones->BIR;
                                        $tbvi += $com->comisiones->BVI;
                                        $tbra += $com->comisiones->BRA;
                                        $subtotal = $com->comisiones->BIR + $com->comisiones->BVI + $com->comisiones->BRA;
                                        ?>
                                        <td class="alineado_derecho"><?php echo number_format($subtotal, 2, '.', ','); ?></td>						
                                    </tr>					
                                    <?php
                                }
                                ?>    
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="alineado_derecho"><b>Totales:</b></td>
                                    <td class="alineado_derecho"><b><?php echo number_format(($tbir), 2, '.', ','); ?></b></td>
                                    <td class="alineado_derecho"><b><?php echo number_format(($tbvi), 2, '.', ','); ?></b></td>
                                    <td class="alineado_derecho"><b><?php echo number_format(($tbra), 2, '.', ','); ?></b></td>
                                    <td class="alineado_derecho"><b><?php echo number_format(($tbir + $tbvi + $tbra), 2, '.', ','); ?></b></td>					
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
        $('#tablaCom1,#tablaCom2,#tablaCom3').footable({
            paginate: false
        });
        
        $('#importar_excel').click(function(e) {
            
            $('#tablaResumen').attr('border', '1');
            window.open('data:application/vnd.ms-excel,' + encodeURIComponent($('#contenido_reporte').html()));
            e.preventDefault();
            $('#tablaResumen').attr('border', '0');
        });
        
    });

</script>
<?php

function barra_de_impresion() {
    $pagina = "'contenido_reporte'";
    $page = "'about:blank'";
    $extpage = "'reportes'";
    $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
    $extra1 = "'<html><head><title>Vista Previa</title>
        
                            <link href=". _base_url . "recursos/librerias/bootstrap/dist/css/bootstrap.css rel=stylesheet type=text/css />
                                <link href=". _base_url . "recursos/css/estilos.css rel=stylesheet type=text/css />
                            <link href=". _base_url . "recursos/css/style.css rel=stylesheet type=text/css />
                            <link href=". _base_url . "recursos/librerias/fooTable/css/footable.core.min.css rel=stylesheet type=text/css />    
                                
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
                    <img src="'. _base_url  .'recursos/img/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                    </a></td><td><img src="'. _base_url  .'recursos/img/excel.png" align="right" border="0" title="EXPORTAR EXCEL" id="importar_excel"></td></tr></table><br><br>
            ';
 
}
?>