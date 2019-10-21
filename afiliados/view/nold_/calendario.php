<link href='<?php echo _base_url; ?>fullcalendar.min.css' rel='stylesheet' />
<link href='<?php echo _base_url; ?>fullcalendar.print.css' rel='stylesheet' media='print' />
<link href='<?php echo _base_url; ?>calendario.css' rel='stylesheet'/>

<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/moment.min.js'></script> 
<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/fullcalendar.min.js'></script>
<script src='<?php echo _base_url; ?>recursos/librerias/fullcalendar/dist/es.js'></script>


<h3>CALENDARIO</h3> 
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
		
			<ul class="nav nav-tabs">
				<li class="active" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/lista"> Listar</a></li>
                <li class="" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/agregar"> Agregar</a></li>
            </ul>
			
            <div class="tab-content">
                <div class="tab-pane active">
					<div class="panel-body">
					
					<div id='calendar'> </div>
					
					<script>
					$('#calendar').fullCalendar({
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,agendaWeek,agendaDay,listWeek'
						},
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
						// events: {
							// url: 'modulos/crm_calendario/ajax_gestor.php?&ajaxTarea=actividades',
							// error: function() {
								//$('#script-warning').show();
							// }
						// },
						loading: function(bool) {
							//$('#loading').toggle(bool);
						}
					});
					</script>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
