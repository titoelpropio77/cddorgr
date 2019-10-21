

$(document).ready(function (){
    var popup=null;
    $('a.group-popup').click(function(){
//        console.log('persona');
        
        
        var url=$(this).attr('data-url')+'&popup=1';
        if( popup!==undefined && popup!==null){
            popup.close();
        }
        popup = window.open(url,'reportes','left=100,width=900,height=600,top=0,scrollbars=yes');
        popup.document.close();
    });

});
function CreaAjax()
{
    var objetoAjax = false;
    try {
        objetoAjax = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
        try
        {
            objetoAjax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (E)
        {
            objetoAjax = false;
        }
    }
    if (!objetoAjax && typeof XMLHttpRequest != 'undefined')
    {
        objetoAjax = new XMLHttpRequest();
    }
    return objetoAjax;
}
function ejecutar_ajax(url, capa, valores, metodo)
{

    var ajax = CreaAjax();
    var capaContenedora = document.getElementById(capa);
    mostrar_ajax_load();
    if (metodo.toUpperCase() == 'POST')
    {
        ajax.open('POST', url, true);
        ajax.onreadystatechange = function()
        {
            if (ajax.readyState == 1)
            {
                capaContenedora.innerHTML = "<img src='ajax-loader.gif'>";
            }
            else if (ajax.readyState == 4)
            {
                ocultar_ajax_load();
                if (ajax.status == 200){
                    document.getElementById(capa).innerHTML = ajax.responseText;
                }else if (ajax.status == 404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    
                    capaContenedora.innerHTML = "Error: " + ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        ajax.send(valores);
        return;
    }
    if (metodo.toUpperCase() == 'GET')
    {
        ajax.open('GET', url, true);
        ajax.onreadystatechange = function()
        {
            if (ajax.readyState == 1)
            {
                capaContenedora.innerHTML = "Cargando.......";
            }
            else if (ajax.readyState == 4)
            {
                ocultar_ajax_load();
                if (ajax.status == 200)
                {
                    document.getElementById(capa).innerHTML = ajax.responseText;
                }
                else if (ajax.status == 404)
                {
                    capaContenedora.innerHTML = "La direccion no existe";
                }
                else
                {
                    capaContenedora.innerHTML = "Error: " + ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        ajax.send(valores);
        return
    }
}

function cargar_pagina(auxUr1, auxDiv, auxVar, auxTipo) {

    $.ajax({
        type: auxTipo,
        url: auxUr1,
        data: auxVar,
        dataType: "html",
        async: true,
        success: function(datoHtml) {
            $("#" + auxDiv).html(datoHtml);

        }
    });
}

function roundNumber(number, decimals) {
    var newString;// The new rounded number
    decimals = Number(decimals);
    if (decimals < 1) {
        newString = (Math.round(number)).toString();
    } else {
        var numString = number.toString();
        if (numString.lastIndexOf(".") == -1) {// If there is no decimal point
            numString += ".";// give it one at the end
        }
        var cutoff = numString.lastIndexOf(".") + decimals;// The point at which to truncate the number
        var d1 = Number(numString.substring(cutoff, cutoff + 1));// The value of the last decimal place that we'll end up with
        var d2 = Number(numString.substring(cutoff + 1, cutoff + 2));// The next decimal, after the last one we want
        if (d2 >= 5) {// Do we need to round up at all? If not, the string will just be truncated
            if (d1 == 9 && cutoff > 0) {// If the last digit is 9, find a new cutoff point
                while (cutoff > 0 && (d1 == 9 || isNaN(d1))) {
                    if (d1 != ".") {
                        cutoff -= 1;
                        d1 = Number(numString.substring(cutoff, cutoff + 1));
                    } else {
                        cutoff -= 1;
                    }
                }
            }
            d1 += 1;
        }
        if (d1 == 10) {
            numString = numString.substring(0, numString.lastIndexOf("."));
            var roundedNum = Number(numString) + 1;
            newString = roundedNum.toString() + '.';
        } else {
            newString = numString.substring(0, cutoff) + d1.toString();
        }
    }
    if (newString.lastIndexOf(".") == -1) {// Do this again, to the new string
        newString += ".";
    }
    var decs = (newString.substring(newString.lastIndexOf(".") + 1)).length;
    for (var i = 0; i < decimals - decs; i++)
        newString += "0";
    //var newNumber = Number(newString);// make it a number if you like
    return newString;
    //document.roundform.roundedfield.value = newString; // Output the result to the form field (change for your purposes)
}


function posicionar_datos_usuario()
{
    //alert(screen.width);

    switch (screen.width)
    {
        case 800:
            {
                document.getElementById('datos_usuario_logeado').style.left = '85%';
                break;
            }

        case 1024:
            {
                document.getElementById('datos_usuario_logeado').style.left = '67%';
                document.getElementById('datos_usuario_logeado').style.top = '96%';
                break;
            }

        case 1280:
            {
                document.getElementById('datos_usuario_logeado').style.left = '62%';
                document.getElementById('datos_usuario_logeado').style.top = '93.5%';
                break;
            }
    }


}

function formulario_filtro()
{
    if (document.getElementById('formulario_de_filtro').style.display == 'none')
    {
        document.getElementById('formulario_de_filtro').style.display = 'block';
    }
    else
    {
        document.getElementById('formulario_de_filtro').style.display = 'none'
    }
}

function form_filtro_ver_js()
{
    document.getElementById('formulario_de_filtro').style.display = 'block';
}

function ocultar_tipo_cambio()
{

    if (egr_tipo.value == "DOLARES")
    {
        document.getElementById('tipo_cambio_texto').style.display = 'block';
        document.getElementById('tipo_cambio_caja_texto').style.display = 'block';
    }
    else
    {
        document.getElementById('tipo_cambio_texto').style.display = 'none'
        document.getElementById('tipo_cambio_caja_texto').style.display = 'none'
    }
}
