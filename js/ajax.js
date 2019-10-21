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
}function CreaAjax()
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