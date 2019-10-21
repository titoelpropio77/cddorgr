<?php
if ($_GET['accion'] == 'agregar_persona') {
    ?>
    <iframe id="grafico" name="grafico" src="sueltos/agregarpersona.php"  width="700" height="350" allowtransparency="true"  allowtransparency="allowtransparency" frameborder="0">
    </iframe>
    <?php

} else {
    if ($_GET['accion'] == 'agregar_divisa') {
        ?>
        <iframe id="grafico" name="grafico" src="sueltos/agregar_divisa.php"  width="700" height="350" allowtransparency="true"  allowtransparency="allowtransparency" frameborder="0">
        </iframe>
        <?php

    }
}
?>
