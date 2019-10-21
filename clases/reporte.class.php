<?php

class REPORTE {

    public static function html($data) {
        $data = (object) $data;
        
        $show_header=  isset($data->show_header)?$data->show_header:true;
        ?>
        <?php if ($show_header){?>
        <div class="fila_formulario_cabecera"> <?php echo strtoupper($data->titulo);?></div>
        <table border="0" align="right">
            <tbody>
                <tr>
                    <td>
                        <a href="javascript:var c = window.open('about:blank','reportes','left=100,width=900,height=500,top=0,scrollbars=yes'); c.document.write('<html><head><title>Vista Previa</title><head> <link href=css/estilos.css rel=stylesheet type=text/css /> </head> <body> <div id=imprimir> <div id=status> <p> <a href=javascript:window.print();>Imprimir</a> <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>'); var dato = document.getElementById('contenido_reporte').innerHTML; c.document.write(dato); c.document.write('</center></body></html>'); c.document.close(); ">
                            <img width="20" border="0" align="right" title="IMPRIMIR" src="images/printer.png">
                        </a>
                    </td>
                    <td>
                        <img style="cursor: pointer" id="exportar_excel" width="20" title="EXPORTAR EXCEL" src="images/excel.png">
                    </td>
                    <td>
                        <img style="cursor: pointer" width="20" border="0" align="right" onclick="javascript:location.href = 'gestor.php?mod=<?php echo $data->modulo; ?>';" title="VOLVER" src="images/back.png">
                    </td>


                </tr>
            </tbody>
        </table>
        <br>
        <br>
        
        <?php // FUNCIONES::print_pre($data->foot); ?>
        
        <div id="contenido_reporte" style="clear:both;">
            <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                <tr>
                    <td width="30%" >
                        </br><strong><?php echo _nombre_empresa; ?></strong></br>
        <?php echo _datos_empresa; ?></br></br>
                    </td>
                    <td  width="40%" >
                        <p align="center" >
                            <strong>
                                <h3>
                                    <center><?php echo $data->titulo; ?></center>
                                </h3>
                            </strong>
                        </p>
                        <br><br>
                        <p align="center">
        <?php $infos = $data->info; ?>
        <?php foreach ($infos as $inf) { ?>
                                <label><b><?php echo $inf[label]; ?>:</b></label>
                                <span><?php echo $inf[valor]; ?></span>
                                <br>
        <?php } ?>
                        </p>
                    </td>
                    <td  width="30%" ><div align="right"></br><img src="imagenes/micro.png" /></div><br/><br/></td>
                </tr>
            </table>
            <?php }?>
            <table class="tablaLista" cellspacing="0" cellpadding="0" style="100%">
                <thead>
                    <tr>
                        <?php $cabecera = $data->head; ?>
                        <?php foreach ($cabecera as $head) { ?>
                            <?php if (gettype($head) == 'array') { ?>
                                <th <?php echo $head[attr]; ?>><?php echo $head[texto]; ?></th>
                            <?php } else { ?>
                                <th><?php echo $head; ?></th>
            <?php } ?>
        <?php } ?>
                    </tr>
                </thead>
                <tbody>

                        <?php $result = $data->result; ?>
                        <?php foreach ($result as $objeto) { ?>
                        <tr>
                            <?php foreach ($objeto as $valor) { ?>
                                <td><?php echo $valor; ?></td>
                        <?php } ?>
                        </tr>
                <?php } ?>

                </tbody>
                    <?php if (count($data->foot) > 0) { ?>
                    <tfoot>

                            <?php $foot = $data->foot; ?>
                            <?php foreach ($foot as $fila) { ?>
                            <tr>
                                <?php foreach ($fila as $pie) { ?>
                                    <?php if (gettype($pie) == 'array') { ?>
                                        <td <?php echo $pie[attr]; ?>><?php echo $pie[texto]; ?></td>
                                    <?php } else { ?>
                                        <td><?php echo $pie; ?></td>
                                <?php } ?>
                            <?php } ?>
                            </tr>
                    <?php } ?>

                    </tfoot>
            <?php } ?>
            </table>

            <?php
//                echo "<pre>";
//                print_r($data);
//                echo "</pre>";
            ?>

        </div>
        <script src="js/jquery.thfloat-0.7.2.min.js"></script>
        <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $data->modulo; ?>&frame=false" method="POST" enctype="multipart/form-data">
            <?php foreach ($_POST as $key => $value) { ?>
                <?php if (gettype($value) == 'array') { ?>
                    <?php foreach ($value as $val) { ?>
                        <input type="hidden" name="<?php echo $key ?>[]" id="<?php echo $key ?>" value="<?php echo $val ?>">
                    <?php } ?>
                <?php } else { ?>
                    <input type="hidden" name="<?php echo $key ?>" id="<?php echo $key ?>" value="<?php echo $value ?>">
                <?php } ?>
            <?php } ?>
            <?php if (!isset($_POST[imprimir])) { ?>
                <input type="hidden" name="imprimir" id="imprimir" value="">
            <?php } ?>
            <?php if (!isset($_POST[frame])) { ?>
                <input type="hidden" name="frame" id="frame" value="">
        <?php } ?>

        </form>  
        <script>
            $(".tablaLista").thfloat();
            $('#exportar_excel').click(function() {
                $('#imprimir').val('excel');
                $('#frame').val('false');
                document.frm_sentencia.submit();
            });
        </script>

        <?php
    }

    public static function excel($data) {
        $data = (object) $data;
        ini_set('memory_limit', '2048M');
        //ini_set('display_errors', TRUE);
        //ini_set('display_startup_errors', TRUE);
        require 'clases/PHPExcel/PHPExcel.php';


        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Mirador del Urubo")
                ->setLastModifiedBy("Mirador del Urubo")
                ->setTitle("Office 2007 XLSX Report Document")
                ->setSubject("Office 2007 XLSX Report Document")
                ->setDescription("Report document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("report result file");


        // Add some data
        $fil = 0;
        $infos = $data->info;
        if ($infos && gettype($infos) == 'array') {
            foreach ($infos as $inf) {
                REPORTE::set_value($fil, 0, $inf[label], $objPHPExcel);
                REPORTE::set_value($fil, 1, $inf[valor], $objPHPExcel);
                $fil++;
            }
        }


        for ($c = 0; $c < count($data->head); $c++) {
            $val_col = $data->head[$c];
            REPORTE::set_value($fil, $c, $val_col, $objPHPExcel);
        }
        $fil++;
        $result = $data->result;

        for ($i = 0; $i < count($result); $i++) {
            $fobj = $result[$i];
            $f = $fil + $i;
            for ($c = 0; $c < count($fobj); $c++) {
                $val_col = strip_tags($fobj[$c]);
                REPORTE::set_value($f, $c, $val_col, $objPHPExcel);
            }
        }
        $fil +=count($result);
        $foots = $data->foot;

        for ($i = 0; $i < count($foots); $i++) {
            $foot = $foots[$i];
            $f = $fil + $i;
            $c=0;
            $_nn=0;
            $_pc=0;
            while ($_pc < count($foot) && $_nn<100 ) {
                $val_col = $foot[$_pc];
                if (gettype($val_col) == 'array') {
                    REPORTE::set_value($f, $c, $val_col[texto], $objPHPExcel);
                    if($val_col[attr]){
                        $ncolspan = REPORTE::get_attr($val_col[attr], 'colspan');
                        if($ncolspan){
                            $fi=$f;
                            $ff=$f;
                            $ci=$c;
                            $cf=$c+$ncolspan-1;
                            REPORTE::combinar_celdas($fi, $ci,$ff,$cf, $objPHPExcel);
                            $c=$cf+1;
                        }else{
                            $c++;
                        }
                    }else{
                        $c++;
                    }
                } else {
                    REPORTE::set_value($f, $c, $val_col, $objPHPExcel);
                    $c++;
                }
                $_pc++;
                $_nn++;
            }
        }

//        REPORTE::set_value(40, 0, 'holamundo', $objPHPExcel);
        
        
        $objPHPExcel->getActiveSheet()->setTitle($data->titulo . ' ' . date('d.m.Y'));


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $data->titulo . ' ' . date('d.m.Y.H.i.s') . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public static function combinar_celdas($fi, $ci,$ff,$cf, $objPHPExcel) {
        $array = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ'
        );
//        $col--;
        $nfi = $fi + 1;
        $nff = $ff + 1;
        
        if ($ci >= 0) {
            $nchar_i = $array[$ci];
            $nchar_f = $array[$cf];
            $objPHPExcel->getActiveSheet()->mergeCells("{$nchar_i}{$nfi}:{$nchar_f}{$nff}");
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("{$nchar_i}{$nfi}", $valo{$numchar}{$nf}r);
        }
    }
    public static function set_value($fil, $col, $valor, $objPHPExcel) {
        $array = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ'
        );
//        $col--;
        $nf = $fil + 1;
        if ($col >= 0) {
            $numchar = $array[$col];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("{$numchar}{$nf}", $valor);
        }
    }

    public static function get_columna($num) {

        $num--;
        if ($num >= 0) {
            return $array[$num];
        }
        return'';
    }

    public static function get_attr($str_attr, $attr) {
        $attr = trim($attr);
        $str_attr = str_replace(' ', '', $str_attr);
        $pos = strpos($str_attr, $attr);
        $nstr = "";
        if ($pos !== false) {
            $ini = $pos + strlen($attr) + 1;
            $num = $pos + strlen($attr) + 2;
            $str_ini = $str_attr[$ini];
            
            $char = $str_attr[$num];
            while ($num < strlen($str_attr) && $char != $str_ini) {
                $nstr.=$char;
                $num++;
                $char = $str_attr[$num];
            }
        }
        return $nstr;
    }

}