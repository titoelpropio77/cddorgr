<?php
$fp = fopen("test_csv.csv", "w");

$list = array (
    array('aaa', 'bbb', 'ccc', 'dddd'),
    array('123', '456', '789'),
    array('"aaa"', '"bbb"')
);
foreach ($list as $arr_lineas) {
	fputcsv($fp, $arr_lineas, ";");
}

fclose($fp);
?>