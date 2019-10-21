<?php
$path_bases = "";

require_once('../../config/database.conf.php');
require_once('view.class.php');

mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);

$views = new VIEWPLANO();
$acceso = $views->acceso_verificar();
$superficie_min_max = $views->superficie_min_max();
$precio_min_max = $views->precio_min_max();

?>

<!DOCTYPE html>
<html lang="en" >
	<head>
		<meta charset="utf-8">
		<title>Plano de Disponibilidad</title>
		<meta name="author" content="flexphperia.net" />
		<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<link href="<?php echo $path_bases; ?>view/common/css/bootstrap.min.css" rel="stylesheet">
		<link href="<?php echo $path_bases; ?>view/common/css/bootstrap-modal.css" rel="stylesheet">
		<link href="<?php echo $path_bases; ?>view/common/css/leaflet.css" rel="stylesheet">

		<link href="<?php echo $path_bases; ?>view/common/css/map-elements.css" rel="stylesheet">
		<link href="<?php echo $path_bases; ?>view/common/css/common.css" rel="stylesheet">
		<link href="<?php echo $path_bases; ?>view/css/front.css" rel="stylesheet">
		
		<script>
			var head_conf = {widths: [920]};
			var mapServRest = "./view.gestor.php";
			var mapImgPath = "http://orangegroup.com.bo/planos/maps/cdd/"; 
			
		</script>
		
		<script src="<?php echo $path_bases; ?>view/common/js/head.min.js"></script>
		<link rel="icon" href="http://orangegroup.com.bo/web/img/favicon.png" type="image/png" />
		<link href="<?php echo $path_bases; ?>view/css/estilos.css" rel="stylesheet">
	</head>
	<body>
		
		<div id="panelColumn" class="invisible"  >
			<div id="panelNav" class="navbar" >
				<div class="navbar-inner">
					<a class="brand" href="#"><img alt="logo" src="<?php echo $path_bases; ?>view/common/img/logo.png" /></a>
					<ul class="nav">
						<li class="active">
							<a id="viewsTabLink" href="#viewsTab">Ver</a> 
						</li>
						<li>
							<a id="searchTabLink" href="#searchTab" >Buscar</a>
						</li>
					</ul>
				</div>
			</div>
			
			<div id="panelTabs" class="tab-content" >
				<div id="viewsTab" class="tab-pane fade in active">
					<span class="nav-header">Planos:</span>
					<span data-state="noMaps" >No hay Planos.</span>
					<div id="treeElement" >
					</div>
					
					<span class="nav-header">Estados de Lotes</span> 
					<div id="leyenda" class="leyenda">
						
					</div>
					
					<div id="regionList" class="hide">
						<span class="nav-header">Manzanos:</span> 
						<ul id="regionListContent" class="nav nav-list">
						</ul>
					</div>
					
					<div style="height: 30px;" ></div>
				</div>

				<div id="searchTab" class="tab-pane fade" >

					<div class="tab-content" >

						<div id="srchFormTab" class="tab-pane active" >

							<form id="searchForm" >
								<div id="searchValidationAlert" class="alert alert-error">
									<a class="close" href="#">&times;</a>
									Introduzca por lo menos un criterio de b&uacute;squeda e introduzca un m&iacute;nimo de 3 caracteres.
								</div>
								<fieldset>
								
									<div class="formFila">
										<label class="control-label" for="searchTypeSelect">Estado del lote:</label>
										<select id="searchTypeSelect" name="markerTypeId"></select>
									</div>
									
									<input type="text" id="titleInput" name="title" value="000" style="display:none;" >
									
									<div class="formFila">
										<label class="control-label" for="superficieIni">Superficie:</label>
										<div class="formColum formColumA">
											<input type="text" id="superficieIni" name="superficieIni" placeholder="Min: <?php echo $superficie_min_max->min; ?>">
										</div> 
										<div class="formColum formColumB">
											<input type="text" id="superficieFin" name="superficieFin" placeholder="Max: <?php echo $superficie_min_max->max; ?>">
										</div>
									</div>
									
							
									<div class="formFila">
										<label class="control-label" for="precioIni">Rango de precio:</label> 
										<div class="formColum formColumA">
											<input type="text" id="precioIni" name="precioIni" placeholder="Min: <?php echo $precio_min_max->min; ?>">
										</div>
										<div class="formColum formColumB">
											<input type="text" id="precioFin" name="precioFin" placeholder="Max: <?php echo $precio_min_max->max; ?>"> 
										</div>
									</div>
				
									
									<div id="srchInputsContent">
										<!-- ajax response depending on search type -->
									</div> 
									<button type="submit" class="btn btn-info btn-small pull-right">Buscar</button>
								</fieldset>
							</form>

						</div>

						<div id="srchResultsTab" class="tab-pane" >
							<button id="backSearchFormBtn" type="button" class="btn btn-small"><i class="icon-chevron-left"></i> Volver al formulario de b&uacute;squeda</button>
							<span id="resultsCount" class="pull-right"></span>
							<div id="srchResultsContent">
								<!-- ajax response, search -->							
							</div>
						</div>
					</div>
				</div> 

			</div>

		</div>


		<div id="mapColumn" class="invisible" >
			<a id="panelToggleBtn" href="#" class="btn-toggle close-panel"><i class="icon-chevron-left"></i></a>
			<div id="mapContainer" class="" >

				<div id="mapViewer" class="viewer" style="width: 100%; height: 100%;" ></div>

				<div class="left-border" ></div>
				<div class="cfm-legend hide" >
					<strong>Leyenda:</strong>
					<ul class="unstyled"></ul>
				</div>

				<div class="cfm-info" > 
					<strong>Mapa actual:&nbsp;</strong>
					<ul class="cfm-breadcrumb breadcrumb"></ul>
				</div>

			</div>

		</div>


		<div id="messageModal" class="modal hide fade" data-backdrop="static" tabindex="-1" data-focus-on="button:first">
			<div class="modal-header">
				<h3 data-state="oldBrowser">Navegador obsoleto detectado</h3>
			</div>
			<div class="modal-body" data-state="oldBrowser">
				<p> Usted est&aacute; utilizando un navegador obsoleto. </ p>
				<p> El uso de su navegador actual que pueda tener un acceso limitado a todas las funciones de esta aplicaci&oacute;n. </ p>
			</div>
			<div class="modal-footer">
				<button href="#" class="btn btn-primary" data-dismiss="modal">OK</button>
			</div>
		</div>

		<div id="longTextModal" class="modal hide fade" data-backdrop="false">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" >&times;</button>
				<h3></h3>
			</div>
			<div class="modal-body">
				<p></p>
			</div>
		</div>

		<div class="notifications"></div>

		<div id="preloader"> 
			<img alt="logo" src="<?php echo $path_bases; ?>view/common/img/logo.png" />
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>

		<noscript>
		<div class="no-js">
			Parece ser que su navegador ha deshabilitado JavaScript. <br />
			Este sitio web requiere su navegador para ser habilitado JavaScript. 
		</div> 
		</noscript>

		<script src="<?php echo $path_bases; ?>view/common/js/jquery-1.11.3.min.js"></script>
		<script src="<?php echo $path_bases; ?>view/common/js/bootstrap.js"></script> 
		<script src="<?php echo $path_bases; ?>view/common/js/bootstrap-adds.js"></script> 
		<script src="<?php echo $path_bases; ?>view/common/js/leaflet.js"></script> 
		<script src="<?php echo $path_bases; ?>view/common/js/jquery.jstree.js"></script> 
		<script src="<?php echo $path_bases; ?>view/js/jquery.address.js"></script> 
		<script src="<?php echo $path_bases; ?>view/js/jquery.columnizer.js"></script>
		<script src="<?php echo $path_bases; ?>view/common/js/common-plugins.js"></script>

		<script src="<?php echo $path_bases; ?>view/common/js/common.js"></script>
		<script src="<?php echo $path_bases; ?>view/js/front.js"></script>

	</body>
</html>