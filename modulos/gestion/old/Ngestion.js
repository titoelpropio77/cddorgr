// JavaScript Document

var hass = ''
var idimg = '';
var is = 0;
var transaction_plan = "";

var $$ = function(id) {
    return document.getElementById(id);
}

function on() {
    hass = location.hash;
    $$('haspost').value = hass.replace('#', '');
    if (hass != '') {
        a = hass.replace('#', '');
        toggle($$(a), (parseInt(a) + 900));
        //var id = "c" + a;
        //$(id).click();
    }
}

var openDialogAccount = function(title)
{
    $("#dialog_cuenta").dialog('option', 'title', title);
    $("#dialog_cuenta").dialog("open");
}

var getLastAccount = function(fila)
{
    var account = "";
    dim = fila.cells[6].innerHTML;
    vec = fila.cells[3].innerHTML.split('.');
    ultimo_valor = getCuenta(vec, dim);
    aux_fila = fila.nextSibling;
    last = vec;
    while ((ultimo_valor == getCuenta(vec, dim))) {
        try {
            vec = aux_fila.cells[3].innerHTML.split('.');
        } catch (e) {
            break;
        }
        if (ultimo_valor == getCuenta(vec, dim))
            last = vec;
        aux_fila = aux_fila.nextSibling;
    }
    ultimo_valor = getCuenta(last, dim);
    if (last[dim] == ""){
       last[dim] = 0;
    }
    var newData = parseInt(last[dim]) + 1;
    newData = addNumberCode(newData, dim);
    var nueva_serie = ultimo_valor + newData;
    account = addCero(nueva_serie);
    return account;
}

var addNumberCode = function(data, long)
{
    var format = $("#formatoCodigo").val().split(".");

    if (format.length > long) {
        for (var i = (String(data).length); i < format[long].length; i++) {
            data = "0" + data;
        }
    }
    return data;
}

var addCero = function(cadena)
{
    var cad = cadena.split(".");
    var format = $("#formatoCodigo").val().split(".");
    var reg = $("#formatoCodigo").val().split(".");
    var aux;
    for (var j = cad.length; j < format.length; j++)
    {
        aux = "";
        for (var k = 0; k < String(reg[j]).length; k++) {
            aux = String(aux) + "0";
        }
        cadena = cadena + "." + aux;
    }
    return cadena;
}

var fragCode = [];

var fragmentar = function(account, level)
{
    var account = String(account).split(".");
    var code1 = "", code2 = "", code3 = "";
    for (var j = 0; j < account.length; j++) {
        if (j < (level)) {
            code1 = code1 + account[j] + ".";
        }
        if ((level) == j) {
            code2 = account[j];
        }
        if (j > (level)) {
            code3 = code3 + "." + account[j];
        }
    }
    fragCode[0] = code1;
    fragCode[1] = code3;
    var len = String(code2).length;
    var width = len * 20;
    var cadena = code1 + "<input name='codigo_pg' type='text' value='" + code2 + "' class='required' "
            + " onkeypress='return onlyEnteros(event," + len + ");' id='codigo_pg'  style='width:" + width + "px;' />" + code3;
    $("#codePlan").html(cadena);
}

function newTransaction(fila, type)
{
    var title = "";
    $("#idplanpadre").val(fila.cells[11].innerHTML);
    //$("#idplanpadre").val(fila.cells[2].innerHTML);

    transaction_plan = type;
    $("#codigo_pg").val("");
    $("#cuenta_pg").val("");
    if (type == "insert") {
        var account = getLastAccount(fila);
        fragmentar(account, fila.cells[6].innerHTML);
        title = "Nueva Cuenta";
        $("#codigo_pg").attr("disabled", false);
    } else {
        var codigo = fila.cells[3].innerHTML;
        var moneda = fila.cells[10].innerHTML;

        $("#moneda_pg").val(moneda).trigger("change");

        var cadena = "<input name='codigo_pg' type='text' value='" + codigo + "' class='required' "
                + " id='codigo_pg'  style='width:140px;' />";
        $("#codePlan").html(cadena);

        $("#cuenta_pg").val(fila.cells[9].innerHTML);
        $("#codigo_pg").attr("disabled", true);
        title = "Modificar Cuenta";
    }
    openDialogAccount(title);
    $("#cuenta_pg").focus();
    return;
}

function onlyEnteros(evt, len) {
    var tecla = (document.all) ? evt.keyCode : evt.which;
    var lenData = $("#codigo_pg").val();
    return (((tecla > 47 && tecla < 58 && lenData.length <= (len - 1)) || tecla == 8 || tecla == 0));
}

var sendTransaction = function()
{
    if (validCode()) {
        var code = "";
        $("#dialog_cuenta").dialog("close");
        if (transaction_plan == "insert")
            code = fragCode[0] + $("#codigo_pg").val() + fragCode[1];
        else
            code = $("#codigo_pg").val();

        $.ajax({
            url: "sueltos/plancuentas.php",
            type: "POST",
            data: {
                transaccion: transaction_plan,
                codigo: code,
                cuenta: $("#cuenta_pg").val(),
                moneda: $("#moneda_pg").val(),
                ges_id: $("#ges_id").val(),
                idplanpadre: $("#idplanpadre").val(),
                auxtable: $("#auxtable").val(),
                tableplan: $("#tableplan").val()
            },
            success: function(data) {
                setDatas(data);
            }
        });
    }
}

var setDatas = function(data)
{
    var data = JSON.parse(data);
    if (data['state'] == "0") {
        location.reload();
    }
    if (data['state'] == "1") {
        $("#dialog_cuenta").dialog("open");
        $.prompt('El código ingresado ya esta activo verifique e intente nuevamente.', {opacity: 0.8});
    }
}

var validCode = function()
{
    var code = "";
    if (transaction_plan == "insert")
        code = fragCode[0] + $("#codigo_pg").val() + fragCode[1];
    else
        code = $("#codigo_pg").val();
    //var code = $("#codigo_pg").val();
    var formate = $("#formatoCodigo").val();
    if ($("#codigo_pg").val() == "") {
        $.prompt('El campo código es requerido.', {opacity: 0.8});
        return false;
    }
    if ($("#cuenta_pg").val() == "") {
        $.prompt('El campo cuenta es requerido.', {opacity: 0.8});
        return false;
    }
    if (formate.split(".").length != code.split(".").length) {
        $.prompt('Código invalido. Formato requerido: ' + formate, {opacity: 0.8});
        return false;
    }
    if (!verifyFormatCode(code, formate)) {
        $.prompt('Código invalido. Formato requerido: ' + formate, {opacity: 0.8});
        return false;
    }
    return true;
}


var verifyFormatCode = function(code, formate)
{
    code = String(code).split(".");
    formate = String(formate).split(".");
    for (var i = 0; i < formate.length; i++)
    {
        if (String(formate[i]).length != String(code[i]).length)
            return false;
    }
    return true;
}

function deleteCuenta(id) {
    transaction_plan = "anular";
    $("#idplanpadre").val(id);
    $("#dialog_cuenta_anular").dialog('option', 'title', "Anular Cuenta");
    $("#dialog_cuenta_anular").dialog("open");
}

var sendDelete = function()
{
    $("#dialog_cuenta_anular").dialog("close");
    $.ajax({
        url: "sueltos/plancuentas.php",
        type: "POST",
        data: {
            transaccion: transaction_plan,
            idplanpadre: $("#idplanpadre").val(),
            ges_id: $("#ges_id").val(),
            auxtable: $("#auxtable").val(),
            tableplan: $("#tableplan").val()
        },
        success: function(data) {
            resultDelete(data);
        }
    });
}

var resultDelete = function(data)
{
    var data = JSON.parse(data);
    switch (data['state']) {
        case 0:
            location.reload();
            break;
        case 1:
            $.prompt('Esta cuenta no puede ser eliminada debido a que tiene sub cuentas.', {opacity: 0.8});
            break;
        case 2:
            $.prompt('Esta cuenta no puede ser eliminada debido a que tiene movimiento.', {opacity: 0.8});
            break;
    }
}

function toggle(fila, idimagen) {
    var escrol = document.documentElement.scrollTop;
    vec_img = $$(idimagen).src.split('/');
    nombre = $$(idimagen).src;
    if (vec_img[vec_img.length - 1] == 'arrowarriba.png') {
        $$(idimagen).src = nombre.replace('arrowarriba.png', 'arrowsabajo.png');
    } else {
        $$(idimagen).src = nombre.replace('arrowsabajo.png', 'arrowarriba.png');
    }
    fila_ = recorrer(fila, idimagen);
    contraerDescontraer();
}

function contraerDescontraer() {
    var cantidad = $$("objetotabla").rows.length;

    if ($$("estado").checked) {
        tipo = "table-row";
    } else {
        tipo = "none";
    }
    var float = false;
    for (var i = 0; i < cantidad; i++) {
        if ($$("objetotabla").rows[i].cells[0].innerHTML != "" && $$("objetotabla").rows[i].cells[0].innerHTML != " ") {
            id = 1000 + i;
            vec_img = $$(id).src.split('/');
            if (vec_img[vec_img.length - 1] == 'arrowsabajo.png') {
                float = true;
            }
            if (vec_img[vec_img.length - 1] == 'arrowarriba.png') {
                float = false;
            }
        }

        if (float && $$("objetotabla").rows[i].cells[3].innerHTML.length > 13)
            $$("objetotabla").rows[i].style.display = tipo;
    }
}

function getPosArray(array, pos) {
    return array[pos];
}

function ultimo(valor) {
    aux = valor.split('.');
    return {val: aux[aux.length - 2], dim: (aux.length - 2)};
}

function tieneHijo(fila) {
    vec = ultimo(fila.cells[3].innerHTML);
    nextFila = fila.nextSibling;
    if (nextFila == null)
        return false;
    next_valor = nextFila.cells[3].innerHTML.split('.');
    return (vec.val == next_valor[vec.dim]);
}

var getCuenta = function(val, dim)
{
    var account = "";
    for (var j = 0; j <= dim - 1; j++) {
        account = account + val[j] + ".";
    }
    return account;
}

function recorrer(fila, idimagen) {

    dim = fila.cells[6].innerHTML;
    vec = fila.cells[3].innerHTML.split('.');
    ultimo_valor = getCuenta(vec, dim);
    aux_fila = fila.nextSibling;
    if (aux_fila.style.display == 'none')
        dis = '';
    else
        dis = 'none';

    while ((ultimo_valor == getCuenta(vec, dim))) {
        try {
            vec = aux_fila.cells[3].innerHTML.split('.');
        } catch (e) {
            return;
        }

        if (ultimo_valor == getCuenta(vec, dim)) {
            aux_fila.style.display = dis;
        }
        aux_fila = aux_fila.nextSibling;
    }
    hass = location.hash;
    idimg = idimagen;
    setTimeout("hass = location.hash;", 100);
}
