function mostrar_mi_red(vdo_id, url_sistema) {
    var token = $.base64.encode(vdo_id + '-' + 'lapropia');
    var url = url_sistema + "ajax_comisiones.php?red=ov&p=" + token;
    window.open(url, "MY WINDOW" + vdo_id, "directories=no, location=no, menubar=no, scrollbars=yes, statusbar=no, tittlebar=no, width=1280, height=600");
}

function mensaje(datos) {
    swal({
        title: datos.titulo,
        text: datos.mensaje,
        type: datos.tipo,
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        closeOnConfirm: true,
        closeOnCancel: true});
}