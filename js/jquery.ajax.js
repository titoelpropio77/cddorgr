/**********************************************************************************

		Plugin jquery desarrollado para motores de busqueda SEO
		Autor: Isaac Quispe Q. // www.qualitywebsrl.net
		E-mail: quispeisaac@hotmail.com 


**********************************************************************************/
/* =AJAX */

function ajaxSeo(parrametro){
	$(parrametro).click(function(){
		var _href = $(this).attr('href');
		var _data = _href.split("?");
		var _contenedor = $(this).attr("rel");
		

		var menuActi = $(this).parent().attr('id');
		menuUrl = _href;
		if(menuActi ==""){
			menuActivo = "menu2";
			menuTopActivo();
		}else{
			menuActivo = menuActi;
			menuTopActivo();
		}


		$.ajax({
			type: "POST",
			url: _data[0],
			data: _data[1],
			dataType: "html",
			async: true,
			beforeSend: function(objeto){
				$("#cargador").css("display","block");
			},
			success: function(datoHtml){
				$("#"+_contenedor).slideUp('slow',function(){
					$(this).html(datoHtml).slideDown("show");
					$("#cargador").css("display","none");
				});
			}
		});
	    return false;	
	});	
}


function cargar_pagina(auxUr1, auxDiv, auxVar, auxTipo){
	$.ajax({
        type: auxTipo,
        url: auxUr1,
        data: auxVar,
		dataType: "html",
		async: true,
        beforeSend: function(objeto){
			$("#cargador").css("display","block");
        },
        success: function(datoHtml){
			$("#"+auxDiv).slideUp('slow',function(){
				$("#"+auxDiv).html(datoHtml).slideDown("show");
				$("#cargador").css("display","none");
			});
		}
	});
	
}