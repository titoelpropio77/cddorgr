function stringBuscar(str, str_to_match)
{
    if (str.indexOf(str_to_match) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getUrlVars() {
    var map = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
        map[key] = value;
    });
    return map;
}

function getUrl(url) {
    var map = {};
    var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
        map[key] = value;
    });
    return map;
}

function ajax_cargar(url, type, data, dataType)
{
    var request = $.ajax({
        type: type, // POST, GET
        url: url,
        data: data, //variables
        cache: false,
        async: false,
        dataType: dataType, // html, json, xml
        beforeSend: function () {

        },
        complete: function () {

        },
        fail: function () {

        }
    });
    return request;
}

//============ AJAX JSONP ============// 
function iqCallback(jsondata)
{

}
function ajax_jsonp(url, data, type)
{
    var request = $.ajax({
        dataType: "jsonp",
        url: url,
        data: data, // &id=1&h=125
        type: type, // POST, GET
        crossDomain: true,
        cache: false,
        async: false,
        jsonpCallback: 'iqCallback',
        beforeSend: function () {
            //myApp.showIndicator();
        },
        complete: function () {
            //myApp.hideIndicator();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //myApp.hideIndicator();
        },
        fail: function () {
            //myApp.hideIndicator();
        }
    });
    return request;
}
//============ AJAX JSONP 2 ============// 
function iqCallback2(jsondata)
{

}
function ajax_jsonp2(url, data, type)
{
    var request = $.ajax({
        dataType: "jsonp",
        url: url,
        data: data, // &id=1&h=125
        type: type, // POST, GET
        crossDomain: true,
        cache: false,
        async: false,
        jsonpCallback: 'iqCallback2',
        beforeSend: function () {
            //myApp.showIndicator();
        },
        complete: function () {
            //myApp.hideIndicator();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //myApp.hideIndicator();
        },
        fail: function () {
            //myApp.hideIndicator();
        }
    });
    return request;
}
//============ AJAX JSONP 2  fin ============// 

function page_offline_actuallizar()
{
    mainView.router.refreshPage();
}

function page_offline()
{
    var auxHtml = '';
    auxHtml += '<div class="navbar">';
    auxHtml += '<div class="navbar-inner"> ';
    auxHtml += '<div class="left"><a href="#" class="open-panel link icon-only"><i class="icon icon-bars"></i></a></div>';
    auxHtml += '<div class="center sliding">Santa Cruz Life</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';

    auxHtml += '<div class="pages navbar-through">';
    auxHtml += '<div data-page="inicio" class="page">  ';
    auxHtml += '<div class="page-content">';
    auxHtml += '<div class="content-block" style="margin-top: 20px;">';
    auxHtml += '<div class="descripcion">';
    auxHtml += '<center>';
    auxHtml += '<br/><br/>';
    auxHtml += '<img src="recursos/img/offline.png">';
    auxHtml += '<div class="content-block-title">No se puede conectar a Internet</div>';
    auxHtml += '<p>Toca en boton reintentar</p> ';
    auxHtml += '<a class="linkCats linkReintentar" href="#" onclick="page_offline_actuallizar()">Reintentar</a>';
    auxHtml += '<br/><br/> ';
    auxHtml += '</center>';
    auxHtml += '</div> ';
    auxHtml += '</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';
    return auxHtml;
}

function page_sinsesion()
{
    var auxHtml = '';
    auxHtml += '<div class="navbar">';
    auxHtml += '<div class="navbar-inner"> ';
    auxHtml += '<div class="left"><a href="#" class="link back"> <i class="icon icon-back"></i><span>Atras</span></a></div>';
    auxHtml += '<div class="center sliding">{{appNombre}}</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';
    auxHtml += '<div class="pages navbar-through">';
    auxHtml += '<div data-page="inicio" class="page">  ';
    auxHtml += '<div class="page-content">';
    auxHtml += '<div class="content-block" style="margin-top: 20px;">';
    auxHtml += '<div class="descripcion">';
    auxHtml += '<center>';
    auxHtml += '<br/><br/>';
    auxHtml += '<img src="recursos/img/sinsesion.png">';
    auxHtml += '<p>Para poder ingresar esta sección necesitas iniciar sesión <br/><br/></p>';
    auxHtml += '<a href="recursos/pages/login/login_formulario.html" class="item-link close-panel">Iniciar Sesión</a>';
    auxHtml += '<br/><br/> ';
    auxHtml += '</center>';
    auxHtml += '</div> ';
    auxHtml += '</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';
    auxHtml += '</div>';
    return auxHtml;
}
//============ LocalStorage ============// 
function localStorageSet(db_nombre, jsonString)
{
    window.localStorage.setItem(db_nombre, JSON.stringify(jsonString));
}

function localStorageGet(db_nombre)
{
    var _datosString = localStorage.getItem(db_nombre);
    var _datos = JSON.parse(_datosString);
    return _datos;
}

function localStorageSupport()
{
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}

//============ Set Interval ============// 
function intervalInit()
{
    var minutoInterval = appConfig.intervalTime * 60 * 1000;
    //var minutoInterval = 3000;
    appConfig.interval = setInterval(function () {
        intervalActualizar();
    }, minutoInterval);
}

function intervalClear()
{
    clearInterval(appConfig.interval);
}
function intervalActualizar()
{
    recordatorio();
}
//============ Recordatorios  ============//
function recordatorio() {

    var sqlCliente = "SELECT int_id,int_nombre || ' ' || int_apellido as cliente,seg_tipo_contacto,seg_situacion,sac_fecha,sac_hora,sac_accion,sac_alerta,seg_id,sac_id FROM seguimiento INNER JOIN seguimiento_accion ON (sac_seg_id=seg_id) INNER JOIN interno ON (seg_int_id=int_id) WHERE sac_fecha ='" + fecha_actual() + "' and sac_estado='Pendiente'";
    var clienteInser = query(sqlCliente);
    clienteInser.done(function (rowsArray) {

        if (rowsArray.length > 0) {
            $.each(rowsArray, function (i, v) {
                var selectFecha = v.sac_fecha.split("-");
                var fechaAlerta = selectFecha[1] + "/" + selectFecha[2] + "/" + selectFecha[0] + " " + v.sac_hora;
                var fechaActual = new Date();
                var fechaAlertaOrigen = new Date(fechaAlerta);
                var fechaAlertaAdelantado = new Date(fechaAlerta);
                fechaAlertaAdelantado.setMinutes(fechaAlertaAdelantado.getMinutes() - parseInt(v.sac_alerta));
                if (fechaAlertaAdelantado <= fechaActual && fechaAlertaOrigen >= fechaActual)
                {
                    var auxTitulo = "Acción a Seguir ";
                    var auxMensaje = "Cliente: " + v.cliente + " | Hora: " + v.sac_hora + " | Acción: " + v.sac_accion;
                    var datos = "accion|" + v.seg_id + "|" + v.int_id;
                    recordatorio_estado(v.sac_id);
                    localNotification(v.seg_id, auxTitulo, auxMensaje, datos);
                }
            });
        }
    });
}

function recordatorio_estado(sac_id) {
    var sqlCliente = "UPDATE  seguimiento_accion SET sac_estado='Confirmado', sac_sincronizado='No' WHERE sac_id='" + sac_id + "';";
    var clienteInser = query(sqlCliente);
    clienteInser.done(function (rowsArray) {

    });
}

//============ Notificacion fin ============//
function log(msg) {
    console.log(msg);
}

function page_preproces(jsondata, contentPage, plantillaHtml, contenHtml)
{
    var HandlebarsTemplate = Handlebars.compile($(contentPage).find(plantillaHtml).html());
    var HandlebarsResult = HandlebarsTemplate(jsondata);
    $("#ajaxHiden").html(contentPage);
    $("#ajaxHiden").find(contenHtml).append(HandlebarsResult);
    var auxHtml = $("#ajaxHiden").html();
    $("#ajaxHiden").html("");
    return auxHtml;
}

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode != 46 && charCode > 31
            && (charCode < 48 || charCode > 57))
    {
        return false;
    } else {
        return true;
    }
}
//============ coordenadas GPS ============//

function gps_distancia(lat1, lon1, lat2, lon2)
{
    var distancia = gps_distance(lat1, lon1, lat2, lon2);
    var metros = ((distancia) * (1000));
    var resul = "";
    if (metros < 1000) {
        resul = metros + " mts";
    } else {
        resul = distancia + " km";
    }
    return resul;
}

function gps_distance(lat1, lon1, lat2, lon2)
{
    var R = 6371;
    var dLat = (lat2 - lat1) * (Math.PI / 180);
    var dLon = (lon2 - lon1) * (Math.PI / 180);
    var lat1 = lat1 * (Math.PI / 180);
    var lat2 = lat2 * (Math.PI / 180);

    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;

    return d.toFixed(3);
}
//============ Imagen ============//
function img_server() {
    var auxURL = appConfig.urlServer.split("apps");
    var auxUrlImg = auxURL[0];
    return auxUrlImg;
}
//============ tool date fecha ============//
// 2015-09-11
function fecha_latina(date) {
    //Entrada 2015-10-29
    //Resultado 05/12/1990
    var arrayDate = date.split("-");
    var dateResul = arrayDate[2] + "/" + arrayDate[1] + "/" + arrayDate[0];
    return dateResul;
}
function fecha_sql(date) {
    //Entrada 05/12/1990 
    //Resultado 2015-10-29
    var arrayDate = date.split("/");
    var dateResul = arrayDate[2] + "-" + arrayDate[1] + "-" + arrayDate[0];
    return dateResul;
}
function fecha_corta(date) {
    var arrayDate = date.split("-");
    var dateResul = arrayDate[1] + "/" + arrayDate[2] + "/" + arrayDate[0];
    return dateResul;
}

function fecha_faltan_dias(fecha) {
    var arrayDate = fecha.split('-');
    var dateFormat = arrayDate[1] + "/" + arrayDate[2] + "/" + arrayDate[0];
    var date = new Date(dateFormat);
    var now = new Date();
    var diff = date.getTime() - now.getTime();
    var days = Math.floor(diff / (1000 * 60 * 60 * 24));

    var result = "";
    if (days > 1) {
        result = "Quedan " + days + " días";
    } else if (days == 1) {
        result = "Termina en dos días";
    } else if (days == 0) {
        result = "Mañana termina";
    } else {
        result = "Hoy termina";
    }
    return result;
}

function trim(x) {
    return x.replace(/^\s+|\s+$/gm, '');
}
function fecha_actual() {
    var hoy = new Date();
    var dd = hoy.getDate();
    var mm = hoy.getMonth() + 1; //hoy es 0!
    var yyyy = hoy.getFullYear();

    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }

    var result = yyyy + '-' + mm + '-' + dd;
    return result;
}
function fecha_objeto(fecha, hora) {
    if (fecha != "" && hora != "") {

        //Fecha formato 23/02/1025 
        var fechaArray = fecha.split("/");
        var mes = Number(parseInt(fechaArray[1])) - Number(1);

        var fechaObj = new Date();
        fechaObj.setDate(parseInt(fechaArray[0]));
        fechaObj.setMonth(mes);
        fechaObj.setYear(parseInt(fechaArray[2]));

        //Hora formato 08:25
        var horaArray = hora.split(":");
        fechaObj.setHours(parseInt(horaArray[0]));
        fechaObj.setMinutes(parseInt(horaArray[1]));
        fechaObj.setSeconds(00);

        return fechaObj;
    } else {
        console.log("Error en funcion fecha_objeto()");
        return new Date();
    }
}

function hora_actual() {
    var myDate = new Date();
    var hora = myDate.getHours();
    var minuto = myDate.getMinutes();
    var segundo = myDate.getSeconds();

    if (hora < 10) {
        hora = '0' + hora;
    }

    if (minuto < 10) {
        minuto = '0' + minuto;
    }

    if (segundo < 10) {
        segundo = '0' + segundo;
    }

    var result = hora + ":" + minuto + ":" + segundo;
    return result;
}

function hora_corto(hora) {
    var auxHora = hora.split(":");
    return auxHora[0] + ":" + auxHora[1];
}

function getRandomId(min, max) {
    var hoy = new Date();
    var resFecha = hoy.getFullYear() + "" + hoy.getMonth() + "" + hoy.getDate() + "" + hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
    var randoms = parseInt(Math.random() * (max - min) + min);
    var resul = resFecha + "" + randoms;
    return resul;
}

//============ texto string ============//
function txt_mayus_primera(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function clearInput(html) {
    var textoLimpio = html.replace(/<\/?[^>]+(>|$)/g, "");
    var comiSimple = textoLimpio.replace(/\'/g, "");
    var comiBoble = comiSimple.replace(/\"/g, "");
    return comiBoble;
}

function clearObjet(objects) {
    for (var key in objects) {
        if (objects[key] != '') {
            objects[key] = clearInput(objects[key]);
        }
    }
    return objects;
}

function htmlLimpiar(str) {
    var limpios = str.replace(new RegExp("\n", "g"), "");
    var limpios2 = limpios.replace(new RegExp("\r", "g"), "");
    var limpios3 = limpios2.replace(/\s+/g, ' ');
    return limpios3;
}

function htmlEntities(str) {
    var limpios = str.replace(new RegExp("\n", "g"), "");
    var limpios2 = limpios.replace(new RegExp("\r", "g"), "");
    var limpios3 = limpios2.replace(/\s+/g, ' ');
    var limpios4 = String(limpios3).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    return limpios4;
}

Number.prototype.formatMoney = function (decPlaces, thouSeparator, decSeparator) {
    var n = this,
            decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
            decSeparator = decSeparator == undefined ? "." : decSeparator,
            thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
            sign = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
    return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
};

//============ Boton atras mobil ============//
function navegation_atras() {
    myApp.closePanel();
    mainView.router.back();
}
function appBotonAtras() {
    if (myApp.mainView.history.length > 1) {
        navegation_atras();
    } else {
        salir_init();
    }
}