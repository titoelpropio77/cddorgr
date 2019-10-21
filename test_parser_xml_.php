<?php
$xml_string = '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
    <linenumber>3097</linenumber>
    <foffset>788583</foffset>
    <fn>tabla_mirror.sql</fn>
    <totalqueries>8</totalqueries>
    <delimiter>;</delimiter>
    <elem1>3096</elem1>
    <elem2>3096</elem2>
    <elem3> ? </elem3>
    <elem4> ? </elem4>
    <elem5>8</elem5>
    <elem6>8</elem6>
    <elem7> ? </elem7>
    <elem8> ? </elem8>
    <elem9>788583</elem9>
    <elem10>788583</elem10>
    <elem11>6450599</elem11>
    <elem12>7239182</elem12>
    <elem13>770.1</elem13>
    <elem14>770.1</elem14>
    <elem15>6299.41</elem15>
    <elem16>7069.51</elem16>
    <elem17>0.75</elem17>
    <elem18>0.75</elem18>
    <elem19>6.15</elem19>
    <elem20>6.9</elem20>
    <elem21>11</elem21>
    <elem22>11</elem22>
    <elem23>89</elem23>
    <elem24>100</elem24>
    <elem_bar>
    </elem_bar>
</root>';

$xml_doc = new SimpleXMLElement($xml_string);

//echo ($xml_doc->elem15555) ? "belleza":'shit';


$i = 0;

while ($i <= 5) {
    $i++;
    sleep(1);
    echo date('H:i:s')."<br/>";
}

?>
