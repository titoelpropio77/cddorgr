function autocomplete_ui(params) {
    var lista = params.lista;
    if (params.bus_info === undefined) {
        params.bus_info = false;
    }
    $(params.input).autocomplete({
        minLength: 0,
        source: function(request, response) {
            var results = [];
            var term = request.term.toLowerCase();
            var nro_resp = 0;
            for (var i = 0; i < lista.length; i++) {
                var res = lista[i];
                var _value = res.value.toLowerCase();
                if (_value.search(term) >= 0) {
                    results.push(res);
                    nro_resp++;
                } else {
                    if (params.bus_info) {
                        var _info = res.info.toLowerCase();
                        if (_info.search(term) >= 0) {
                            results.push(res);
                            nro_resp++;
                        }
                    }
                }
                if (nro_resp === 10) {
                    break;
                }
            }
            response(results.slice(0, 10));
        },
        focus: function() {
            return false;
        },
        select: function(event, ui) {
            params.select({id: ui.item.id, value: ui.item.value, info: ui.item.info});

            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        var info = "";
        if (item.info) {
            info = "<br><span style='font-size:11px'>" + item.info + "</span></a>";
        }
        return $("<li>")
                .append("<a><b>" + item.label + "</b>" + info)
                .appendTo(ul);
    };

}

var Base64 = {
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    encode: function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },
    decode: function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },
    _utf8_encode: function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },
    _utf8_decode: function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}

function esFecha(strValue) {
    //check to see if its in a correct format
    var objRegExp = /\d{1,2}\/\d{1,2}\/\d{4}/;

    if (!objRegExp.test(strValue))
        return false; //doesn't match pattern, bad date
    else {
        var strSeparator = strValue.substring(2, 3)
        //create a lookup for months not equal to Feb.
        var arrayDate = strValue.split(strSeparator);

        var arrayLookup = {'01': 31, '03': 31,
            '04': 30, '05': 31,
            '06': 30, '07': 31,
            '08': 31, '09': 30,
            '10': 31, '11': 30, '12': 31
        }

        var intDay = parseInt(arrayDate[0], 10);
        var intMonth = parseInt(arrayDate[1], 10);
        var intYear = parseInt(arrayDate[2], 10);

        if (arrayLookup[arrayDate[1]] !== null) {
            if (intDay <= arrayLookup[arrayDate[1]] && intDay !== 0
                    && intYear > 1975 && intYear < 2050)
                return true;     //found in lookup table, good date
        }
        if (intMonth === 2) {
            var intYear = parseInt(arrayDate[2]);
            if (intDay > 0 && intDay < 29) {
                return true;
            } else if (intDay === 29) {
                if ((intYear % 4 === 0) && (intYear % 100 !== 0) ||
                        (intYear % 400 === 0)) {
                    // year div by 4 and ((not div by 100) or div by 400) ->ok
                    return true;
                }
            }
        }
    }

    return false; //any other values, bad date
}

function comparar_fechas(fechaA, fechaB) {
    var afechaA = fechaA.split('/');
    var afechaB = fechaB.split('/');

    var da = parseInt(afechaA[0], 10);
    var ma = parseInt(afechaA[1], 10);
    var ya = parseInt(afechaA[2], 10);

    var db = parseInt(afechaB[0], 10);
    var mb = parseInt(afechaB[1], 10);
    var yb = parseInt(afechaB[2], 10);



    if (db === da && mb === ma && yb === ya) {
        return 0;
    }

    if (ya > yb) {
        return 1;
    } else {
        if (ya === yb) {
            if (ma > mb) {
                return 1;
            } else {
                if (ma === mb) {
                    if (da > db) {
                        return 1;
                    } else {
                        return -1;
                    }
                } else {
                    return -1;
                }
            }
        } else {
            return -1;
        }
    }
}

function sumar_dias(fecha, dias) {
//    console.log("FECHA INGRESADA: " + txt_fecha);
    var milisegundos = parseInt(35 * 24 * 60 * 60 * 1000);
    var _array = fecha.split('-');
    var nfecha = new Date(_array[0], _array[1] - 1, _array[2]);
    var day = nfecha.getDate();
    // el mes es devuelto entre 0 y 11
    var month = nfecha.getMonth() + 1;
    var year = nfecha.getFullYear();

//    console.log("Fecha actual: " + day + "/" + month + "/" + year);

    //Obtenemos los milisegundos desde media noche del 1/1/1970
    var tiempo = nfecha.getTime();
    //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
    milisegundos = parseInt(dias * 24 * 60 * 60 * 1000);
    //Modificamos la fecha actual
    var total = nfecha.setTime(tiempo + milisegundos);
    day = nfecha.getDate();
    month = nfecha.getMonth() + 1;
    year = nfecha.getFullYear();
    if ((month + '').length === 1) {
        month = '0' + month;
    }
    if ((day + '').length === 1) {
        day = '0' + day;
    }
//    console.log("Fecha modificada: " + day + "/" + month + "/" + year);
    return year + '-' + month + '-' + day;
}

function mask_decimal_(input, funcion) {
    $(input).live('keypress', function(e) {

//        if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39
//                && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9 && !(e.keyCode >= 97 && e.keyCode <= 255)) {

        if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39
                && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9 && !(e.keyCode >= 97 && e.keyCode <= 255)) {
            var valor = $(this).val();
            var char = String.fromCharCode(e.which);
            valor = valor + char;
            if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                return false;
            }
        }
    });
    $(input).live('keyup', function(e) {
        if (funcion != undefined || funcion != null) {
            funcion(this);
        }

    });
}

function mask_decimal(input, funcion) {
    $(input).live('keypress', function(e) {

//        if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39
//                && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9 && !(e.keyCode >= 97 && e.keyCode <= 255)) {

        if (e.keyCode !== 8 && e.keyCode !== 9) {
            var valor = $(this).val();
            var char = String.fromCharCode(e.which);
            valor = valor + char;
            if (!/^([0-9])+[.]?[0-9]*$/.test(valor)) {
                return false;
            }
        }
    });
    $(input).live('keyup', function(e) {
        if (funcion != undefined || funcion != null) {
            funcion(this);
        }

    });
}

function mask_integer(input, funcion) {
    $(input).live('keypress', function(e) {
        if (e.keyCode !== 8 && e.keyCode !== 9) {
            var valor = $(this).val();
            var char = String.fromCharCode(e.which);
            valor = valor + char;
            if (!/^([0-9])*[0-9]*$/.test(valor)) {
                return false;
            }
        }
    });
    $(input).live('keyup', function(e) {
        if (funcion != undefined || funcion != null) {
            funcion(this);
        }
    });
}

function trim(str) {
    return str.replace(/^\s+|\s+$/g, "");
}

function fecha_mysql(fecha) {
    var $arr = fecha.split('/');
    return $arr[2] + "-" + $arr[1] + "-" + $arr[0];
}
function fecha_latina(fecha) {
    var $arr = fecha.split('-');
    return $arr[2] + "/" + $arr[1] + "/" + $arr[0];
}
function diferencia_dias(fecha1, fecha2) {
    var d1 = (fecha1).split("-");
    var dat1 = new Date(d1[0], parseFloat(d1[1]) - 1, parseFloat(d1[2]));
    var d2 = (fecha2).split("-");
    var dat2 = new Date(d2[0], parseFloat(d2[1]) - 1, parseFloat(d2[2]));
    var fin = dat2.getTime() - dat1.getTime();
    var dias = Math.floor(fin / (1000 * 60 * 60 * 24));
    return dias;
}

function restar_dias(fecha, dias) {
    var f = (fecha).split("-");
    var objfecha = new Date(f[0], (f[1] - 1), f[2]);
    var newfecha = new Date(objfecha.getTime() - (dias * 24 * 3600 * 1000));
    var anio = newfecha.getFullYear();
    var mes = ((newfecha.getMonth() * 1) + 1) + "";
    var dia = newfecha.getDate() + "";
    if ((mes).length === 1) {
        mes = "0" + mes;
    }
    if ((dia).length === 1) {
        dia = "0" + dia;
    }
    return anio + "-" + mes + "-" + dia;
}

function maximo_dia(year, mes) {
    if (mes === 1 || mes === 3 || mes === 5 || mes === 7 || mes === 8 || mes === 10 || mes === 12) {
        return 31;
    } else if (mes === 4 || mes === 6 || mes === 9 || mes === 11) {
        return 30;
    } else if (mes === 2) {
        if (year % 4 === 0) {
            return 29;
        } else {
            return 28;
        }
    }
}

function siguiente_mes(fecha, dia) {
    var fa = fecha.split("-");
    var anio = fa[0];
    var mes = fa[1] * 1 + 1;
    if (dia === undefined)
        dia = fa[2];
    if (mes > 12) {
        mes = 1;
        anio = anio * 1 + 1;
    }
    var max_dia = dia_max(anio, mes);
    if (dia > max_dia) {
        dia = max_dia;
    }
    mes = mes + '';
    dia = dia + '';
    if (mes.length === 1)
        mes = "0" + mes;
    if (dia.length === 1)
        dia = "0" + dia;
    return anio + '-' + mes + '-' + dia;//"anio-mes-dia";
}

function dia_max(year, mes) {
    if (mes === 1 || mes === 3 || mes === 5 || mes === 7 || mes === 8 || mes === 10 || mes === 12) {
        return 31;
    } else if (mes === 4 || mes === 6 || mes === 9 || mes === 11) {
        return 30;
    } else if (mes === 2) {
        if (year % 4 === 0) {
            return 29;
        } else {
            return 28;
        }
    }
}

function ValidarNumero(e) {
    evt = e ? e : event;
    tcl = (window.Event) ? evt.which : evt.keyCode;
    if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
    {
        return false;
    }
    return true;
}

function diferencia_dias(fecha1, fecha2) {
    var d1 = (fecha1).split("-");
    var dat1 = new Date(d1[0], parseFloat(d1[1]) - 1, parseFloat(d1[2]));
    var d2 = (fecha2).split("-");
    var dat2 = new Date(d2[0], parseFloat(d2[1]) - 1, parseFloat(d2[2]));
    var fin = dat2.getTime() - dat1.getTime();
    var dias = Math.floor(fin / (1000 * 60 * 60 * 24));
    return dias;
}


function mostrar_ajax_load() {
    var over = $('#div-over-body');
    var img = $('#img-over-load');
    if (!over.length) {
        over = $('<div id="div-over-body">');
        img = $('<img id="img-over-load" src="images/load-ajax.gif">');
        $('body').append(over);
        $('body').append(img);
        $(window).resize(function() {
            ajustar_ajax_load();
        });
    }
    var height = $(document).height();
    var width = $(document).width();
    $(over).css({'display': 'block', 'height': height + 'px'});
    var left = (width / 2) - 32;
    var top = (height / 2) - 32;
    $(img).css({'left': left + 'px', 'top': top + 'px'});

}
function ajustar_ajax_load() {
    var over = $('#div-over-body');
    var img = $('#img-over-load');

    var height = $(document).height();
    var width = $(document).width();
    $(over).css({'height': height + 'px'});
    var left = (width / 2) - 32;
    var top = (height / 2) - 32;
    $(img).css({'left': left + 'px', 'top': top + 'px'});
}
function ocultar_ajax_load() {
    var over = $('#div-over-body');
    var img = $('#img-over-load');
    $(img).hide();
    $(over).hide();
}


function cmp_edit(input, params) {
    var idinput = $(input).attr('id');
    $(input).addClass('cmp_edit_input');
    var padre = $(input).parent();
    var cont = $('<div>');
    $(cont).append($(input));
    $(cont).append('<div class="read-input" id="cmp_lab_' + idinput + '">&nbsp;</div>');
    $(cont).append('<div class="cmp_ico_edit" id="cmp_a_edit_' + idinput + '"><img src="images/m_edit.png"></div>');
    $(cont).append('<div class="cmp_ico_edit" id="cmp_c_edit_' + idinput + '"><img src="images/m_c_edit.png"></div>');
    $(padre).append(cont);
    $(input).change(function() {
        $('#cmp_lab_' + idinput).text($(this).val());
    });

    $('#cmp_a_edit_' + idinput).click(function() {
        habilitar_cmp_edit();
        $(input).select();
    });

    $('#cmp_c_edit_' + idinput).click(function() {
        deshabilitar_cmp_edit();
        var res_monto = 0;
        if (params.cancel !== undefined) {
            res_monto = params.cancel();
        }
        console.info(res_monto);
        $(input).val(res_monto);
        $('#cmp_lab_' + idinput).text(res_monto);
    });

    function habilitar_cmp_edit() {
        $(input).removeClass('dnone');
        $('#cmp_lab_' + idinput).addClass('dnone');
        $('#cmp_a_edit_' + idinput).addClass('dnone');
        $('#cmp_c_edit_' + idinput).removeClass('dnone');
    }

    function deshabilitar_cmp_edit() {
        $(input).addClass('dnone');
        $('#cmp_lab_' + idinput).removeClass('dnone');
        $('#cmp_a_edit_' + idinput).removeClass('dnone');
        $('#cmp_c_edit_' + idinput).addClass('dnone');
    }

    if (params.estado === undefined || params.estado === 'ready') {
        deshabilitar_cmp_edit();
    } else {
        habilitar_cmp_edit();
    }

    if (params.keyup !== undefined) {
        $(input).keyup(function(e) {
            params.keyup(this, e);
        });
    }

}