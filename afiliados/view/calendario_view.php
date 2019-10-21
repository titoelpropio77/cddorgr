<link href='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/fullcalendar.min.css' rel='stylesheet' />
<link href='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/fullcalendar.print.css' rel='stylesheet' media='print' />

<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/moment.min.js'></script> 
<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/fullcalendar.min.js'></script>
<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/es.js'></script>  

<h3>CALENDARIO</h3> 
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel"> 
            <div class="tab-content">
                <div class="tab-pane active">
					<div class="panel-body">
					
					<div class="row">
						<div class="col-lg-6">
							<div id='calendar'> </div>
						</div>
						<div class="col-lg-6">
							<div id="ajaxConten"> </div>
						</div> 
					</div>
					<?php 
					$auxURL = explode("afiliados",_base_url);
					?>
					<script>
						function cargar_lista(ajaxTarea, fecha) {
							$.ajax({
								url: "<?php echo $auxURL[0]; ?>modulos/crm_calendario/ajax_gestor.php",
								method: 'GET',
								data:  "&ajaxTarea="+ajaxTarea+"&fecha_ini="+fecha+"&origen=afiliados&token=<?php echo $_SESSION['token']?>",
								dataType: 'html',
								error: function(res, text){
									
								},
								success: function(res){
									$("#ajaxConten").html(res); 
								}
							});
						}
						
						$(document).ready(function(){
							$('#calendar').fullCalendar({
								header: {
									left: 'prev,next today',
									center: 'title',
									right: 'month,agendaWeek,agendaDay,listWeek'
								},
								lang: 'es',
								defaultDate: '<?php echo date('Y-m-d'); ?>',
								navLinks: true, 
								editable: false,
								eventLimit: false,
								//
								selectable: true,
								selectHelper: true, 
								select: function(start, end) {
									//console.log(start);
									//console.log(end);
									/*
									$.prompt("", {
										title: "<center>AGREGAR: </center>",
										buttons: { "SEGUIMIENTO": true,"RESERVA": false } 
									});
									*/						
								},
								eventRender: function (event, element) { 
									element.attr('href', 'javascript:void(0);');
									element.click(function() {
										var ajaxTarea = "";
										switch (event.tipo) {
										  case "Seguimiento":
											ajaxTarea = "seguimiento_lista";
											break; 
										  case "Accion":
											ajaxTarea = "accion_lista"; 
											break;
										  case "Reserva":
											ajaxTarea = "reserva_lista";
											break;
										} 
										cargar_lista(ajaxTarea, event._start._i); 
									});
								},
								//
								events: {
									url: '<?php echo $auxURL[0]; ?>modulos/crm_calendario/ajax_gestor.php?&ajaxTarea=actividades&origen=afiliados&token=<?php echo $_SESSION['token']?>',
									error: function() { 
										//$('#script-warning').show(); 
									}
								},
								loading: function(bool) {
									//$('#loading').toggle(bool);
								}
							});
							cargar_lista("seguimiento_lista", "<?php echo date('Y-m-d'); ?>");
						});
						
						
					</script>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
