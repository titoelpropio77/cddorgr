<?
$dbuser="sistema.com.bo";   //usuario mysql
$dbpass="wfnuxEHbH5"; //Clave mysqñ
 
$schema2compare= array('Sch1' => 'sistema_com_bo_pruebas3', 'Sch2' => 'sistema_com_bo_pruebas10');
$k= mysql_connect('localhost',$dbuser,$dbpass);
if (!$k)
{
    echo "No me pude conectar al servidor mysql<br>";
    exit();
}
 
 
//Comparacion de las dos bases de datos (Schemas) tabla por tabla
foreach ($schema2compare as $schema)
{
//    $sql="show tables from $schema like '%_community_%'"; //Filtro para comparar solo algunas tablas  
    $sql="show tables from $schema"; //Filtro para comparar solo algunas tablas  
    $res=mysql_query($sql);
    if (!$res)
    {
        echo "Error de BD, no se pudieron listar las tablas<br>";
        echo 'Error MySQL: ' . mysql_error();
        exit;
    }
    while ($r = mysql_fetch_row($res))
    {
        $base[$schema]['tabla'][$r[0]]=1;
    }
}
 
//Muestro las tablas que no estan en las dos bases de datos
 
$tablas_diferentes[$schema2compare['Sch1']]=(array_diff_key($base[$schema2compare['Sch1']]['tabla'], $base[$schema2compare['Sch2']]['tabla']));
$tablas_diferentes[$schema2compare['Sch2']]=(array_diff_key($base[$schema2compare['Sch2']]['tabla'], $base[$schema2compare['Sch1']]['tabla']));
?>
<div align="center"><h1> Tablas Diferentes </h1></div>
<table align="center">
<tr>
<?
foreach ($tablas_diferentes as $tabla_diferente=>$valor)
{
    ?>
        <td valign="top">
        <table border="1" cellpadding="0" cellpadding="0">
        <tr><th><? echo $tabla_diferente ?></th> </tr>
    <?
    foreach($valor as $v=>$val)
    {
        echo "<tr><td>$v</td></tr>";  
    }
    ?>
        </table>
        </td>
    <?
}
?>
</tr>
</table>
</table>
<?
 
 
// Comparacion de las tablas que existen en las dos bases de datos
?>
<div align="center"><h1>Columnas Diferentes de las Tablas Presentes en las dos Bases </h1></div>
 
 
<?
$tablas_iguales=(array_intersect_key($base[$schema2compare['Sch1']]['tabla'], $base[$schema2compare['Sch2']]['tabla']));
foreach($tablas_iguales as $tabla_igual=>$valor)
{
    foreach ($schema2compare as $schema)
    {
        $sql="describe $schema.$tabla_igual";
        $res=mysql_query($sql);
        while ($r=mysql_fetch_row($res))
        {
           $tabla[$schema]['columna'][$r[0]]=1;  
        }
    }
    $columnas_diferentes[$schema2compare['Sch1']]=(array_diff_key($tabla[$schema2compare['Sch1']]['columna'], $tabla[$schema2compare['Sch2']]['columna']));
    $columnas_diferentes[$schema2compare['Sch2']]=(array_diff_key($tabla[$schema2compare['Sch2']]['columna'], $tabla[$schema2compare['Sch1']]['columna']));
    if ($columnas_diferentes[$schema2compare['Sch1']])
    {
        ?>
        <table border="1" align="center">
            <tr>
                <th colspan="2"><? echo $tabla_igual ?></th>
            </tr>
            <tr>
        <?
        foreach ($columnas_diferentes as $columna_diferente=>$valor)
        {
        ?>
            <td valign="top">
            <table border="1" cellpadding="0" cellpadding="0">
            <tr><th><? echo $columna_diferente ?></th> </tr>
        <?
            foreach($valor as $v=>$val)
            {
                echo "<tr><td>$v</td></tr>";  
            }
            ?>
                </table>
                </td>
            <?
        }
        ?>
        </tr>
        </table>
        <hr />
        <?
 
        
    }
    $columnas_diferentes=array();
    $tabla=array();
}
?>