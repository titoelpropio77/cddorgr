function graficar_arbol(ul, params) {
    $(ul).children().remove();
    var listado = params.listado;
    var show_name = params.show_name;
    var tree_li = llenar_hijos(listado, [], 0, 999,show_name);
    $(ul).append(tree_li);
}
function llenar_hijos(listado, sec_padres, nivel, zindex,show_name) {
    var list_li = '';
    for (var i = 0; i < listado.length; i++) {
        var nodo = listado[i];
        var style_zindex = 'style="z-index: ' + zindex + '"';
        var str_pri_magin = '';
        var padding = '';
        if (i === 0) {
            if (nivel > 0) {
                var npad = 3 * 33 * nivel;
                str_pri_magin = 'style="margin-top: ' + npad + 'px;"';
            }
        } else {
            for (var j = 0; j < nivel; j++) {
                var lineh = '';
                if (!sec_padres[j]) {
                    lineh = 'img_node_lineh';
                }
                padding += '<i class="img_node ' + lineh + '"></i>';
                padding += '<i class="img_node"></i>';
                padding += '<i class="img_node"></i>';
            }
        }

        var ultimo = false;
        if (i === listado.length - 1) {
            ultimo = true;
        }
        var sec_padres_sub = sec_padres.slice(0);
        if (ultimo) {
            sec_padres_sub.push(1);
        } else {
            sec_padres_sub.push(0);
        }
        if (nodo.id === 130) {
            console.log(sec_padres);
            console.log(sec_padres_sub);
        }
        var img_node_line = '';

        if (i === 0 && i === listado.length - 1) {
            img_node_line = 'img_node_linev';
        } else if (i === 0 && listado.length > 0) {
            img_node_line = 'img_node_linetv';
        } else if (i > 0 && i < listado.length - 1) {
            img_node_line = 'img_node_lineth';
        } else if (i > 0 && i === listado.length - 1) {
            img_node_line = 'img_node_linec';
        }


        var image_node = 'img_node_person';
        if (nodo.image !== undefined) {
            image_node = 'img_node_' + nodo.image;
        }
        var str_icon_more_less = '';
        if (nodo.children !== undefined && nodo.children.length > 0) {
            str_icon_more_less = '<a href="javascript:void(0);" class="icon_ml icon_less"></a>';
        }
        var str_name=nodo.name;
        if(!show_name){
            var str_name='';
        }
        var str_info=JSON.stringify(nodo.info);
        list_li += '<li data-info=\''+str_info+'\' data-nivel="'+nivel+'">';
        list_li += '<div class="box_node" ' + style_zindex + '>';
        list_li += padding;
        list_li += '	<i ' + str_pri_magin + 'class="img_node ' + img_node_line + '"></i>';
        list_li += '	<i class="img_node image_node ' + image_node + '"></i>';
        list_li += str_icon_more_less;
        list_li += '	<div class="label_node">';
        list_li += '		<span class="id_node">' + nodo.id + '</span>';
        list_li += '		<span class="nombre_node">' + str_name + '</span>';
        list_li += '	</div>';
        list_li += '</div>';

        if (nodo.children !== undefined && nodo.children.length > 0) {
            list_li += '<ul>';
            list_li += llenar_hijos(nodo.children, sec_padres_sub, nivel + 1, zindex - 1);
            list_li += '</ul>';
        }



        list_li += '</li>';
    }
    return list_li;
}