<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/fooTable/css/footable.core.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/fooTable/dist/footable.all.min.js"></script>
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
?>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-1">Hijos Linea 1</a></li>
                <li class=""><a data-toggle="tab" href="#tab-2">Hijos Linea 2</a></li>                
            </ul>
            <div id="contenido_reporte">
            <div class="tab-content">
                
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body">
                        <h4>Linea 1</h4>
                        <table id="tablaResumen" class="footable table table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>#</th>                                                                         
                                    <th>Nombre</th>                                                                         
                                    <th>Fecha Nacimiento</th>                                    
                                    <th>Telefonos</th>
                                    <th>Email</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hijos1 = $datos->hijos1;
                                for ($i = 0; $i < count($hijos1); $i++) {
                                    $hijo = $hijos1[$i];
                                    ?>
                                    <tr>
                                        <td><?php echo ($i + 1); ?></td>
                                        <td><?php echo $hijo->nombre; ?></td>
                                        <td class="alineado_derecho fecha"><?php echo $hijo->fecha_nac; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->telefono . ", " . $hijo->celular; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->email; ?></td>                                        
                                    </tr>					
                                    <?php
                                }
                                ?>    
                            </tbody>
                            
                        </table>
                    </div>
                </div>
                
                <div id="tab-2" class="tab-pane">
                    <div class="panel-body">
                        <h4>Linea 2</h4>
                        <table id="tablaResumen" class="footable table table-bordered table-hover tableSinpaginar" >
                            <thead>
                                <tr>
                                    <th>#</th>                                                                         
                                    <th>Nombre</th>                                                                         
                                    <th>Fecha Nacimiento</th>                                    
                                    <th>Telefonos</th>
                                    <th>Email</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hijos2 = $datos->hijos2;
                                for ($i = 0; $i < count($hijos2); $i++) {
                                    $hijo = $hijos2[$i];
                                    ?>
                                    <tr>
                                        <td><?php echo ($i + 1); ?></td>
                                        <td><?php echo $hijo->nombre; ?></td>
                                        <td class="alineado_derecho fecha"><?php echo $hijo->fecha_nac; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->telefono . ", " . $hijo->celular; ?></td>
                                        <td class="alineado_derecho"><?php echo $hijo->email; ?></td>                                        
                                    </tr>					
                                    <?php
                                }
                                ?>    
                            </tbody>
                            
                        </table>
                    </div>
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