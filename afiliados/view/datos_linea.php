<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>
<script src="<?php echo _base_url; ?>recursos/librerias/datatables/media/js/jquery.dataTables.min.js"></script>
<style>
    .alineado_derecho{
        text-align: right;
    }
</style>
<h3>DATOS DE MI RED</h3>
<br/>
<!--<h4>Resumen:</h4>-->
<?php
barra_de_impresion();

//echo "<pre>";
//print_r($datos);
//echo "</pre>";
?>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            
            <ul class="nav nav-tabs">
                <li class="active"><a class="pestania" data-toggle="tab" href="#tab-1">Linea 1</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-2">Linea 2</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-3">Linea 3</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-4">Linea 4</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-5">Linea 5</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-6">Linea 6</a></li>
                <li class=""><a class="pestania" data-toggle="tab" href="#tab-7">Linea 7</a></li>
            </ul>
            <div id="contenido_reporte">
            <div class="tab-content">
                
                <?php
                for ($k = 1; $k <= 7; $k++) {
                    $s_active = ($k == 1)?"active":'';
                ?>
                
                <div id="tab-<?php echo $k;?>" class="tab-pane <?php echo $s_active;?>">
                    <div class="panel-body">
                        <div class="table-responsive">
                        <!--<h4>Linea 1</h4>-->
                        <table id="tablaResumen<?php echo $k;?>" class="footable table dataTable table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>Codigo Venta</th>                                    
                                    <th>Nombre</th>      
                                    <th data-hide="phone,tablet">Terreno</th>
                                    <th data-hide="phone,tablet">Firmado</th>
                                    <th data-hide="phone,tablet">Fecha Nacimiento</th>                                    
                                    <th data-hide="phone,tablet">Telefonos</th>
                                    <th data-hide="phone,tablet">Email</th>                                                        
                                    <th data-hide="phone,tablet">Direccion</th> 
                                    <th data-hide="phone,tablet">Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $s = 'hijos'.$k;
//                                $hijos = $datos->hijos{$k};
                                $hijos = $datos->{$s};
//                                echo "<span style='color:red'>$k</span>";
                                for ($i = 0; $i < count($hijos); $i++) {
                                    $hijo = $hijos[$i];
                                    $color= $hijo->firmado == 'No'?'#ff0000':'#328d01';
                                    ?>
                                    <tr>
                                        <td class="alineado_derecho"><?php echo $hijo->codigo_venta; ?></td>                                        
                                        <td><?php echo $hijo->nombre; ?></td>
                                        <td><?php echo $hijo->terreno; ?></td>
                                        <td><span style="padding: 0 2px; color:#fff; background-color:<?php echo $color;?>"><?php echo $hijo->firmado; ?></span></td>
                                        <td class="alineado_derecho fecha"><?php echo $hijo->fecha_nac; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->telefono . ", " . $hijo->celular; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->email; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->direccion; ?></td>  
                                        <td class="alineado_derecho"><?php echo $hijo->login; ?></td>
                                    </tr>					
                                    <?php
                                }
                                ?>    
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" >Total Registros: <?php echo count($hijos);?></td>
                                </tr> 
                            </tfoot>
                            
                        </table>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
                                                                
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