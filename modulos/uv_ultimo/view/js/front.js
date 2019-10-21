$(document).ready(function(){
$.extend(Templates, {noResults:"Ning&uacute;n resultado, tratan de cambiar los criterios de b&uacute;squeda.", resultsCount:function(e){
return e = parseInt(e), e?"Resultados " + e + (1 == e?" marker.":" de la busqueda."):""},  
	paramLabel:function(e){return'<label class="control-label" for="param-' + e.number + '">' + $.escapifyHTML(e.label) + "</label>"}, paramInput:function(e){return'<input type="text" id="param-' + e.number + '" name="param' + e.number + 'Value" >'}, paramSelect:function(e, t){var a = this.selectOption("", "-- all --"); if (t){var r = this; $.each(t, function(e, t){a += r.selectOption(t.id, t.value, !0)})}return'<select id="param-' + e.number + '" name="param' + e.number + 'Value" >' + a + "</select>"}, regionListElements:function(t){var a = ""; return $.each(t, function(t, r){a += '<li><a href="#/?' + e.MAP_PARAM + "=" + r.mapId + "&" + e.VIEW_PARAM + "=" + e.VIEW_PARAM_REGION + "&id=" + r.id + '">' + $.escapifyHTML(r.name) + "</a></li>"}), a}, markerTypesSelect:function(e){var t = this.selectOption("", "all"); if (e){var a = this; $.each(e, function(e, r){t += a.selectOption(r.id, r.name)})}return t}}); var e = function(){this.wait = !0, this.lastQuery = null}; e.VIEW_PARAM = "view", e.VIEW_PARAM_MARKER = "marker", e.VIEW_PARAM_REGION = "region", e.MAP_PARAM = "map", e.ID_PARAM = "id", e.prototype = {init:function(){$.address.autoUpdate(!1); $.address.externalChange(function(){t.addressChangeEventHandler()})}, setMapId:function(t){var a = $.address.parameter(e.MAP_PARAM); t != a && ($.address.parameter(e.MAP_PARAM, t?t:null), $.address.update())}, setMarkerId:function(t){var a = $.address.parameter(e.VIEW_PARAM); viewId = $.address.parameter(e.ID_PARAM), (a != e.VIEW_PARAM_MARKER || viewId != t) && ($.address.parameter(e.VIEW_PARAM, t?e.VIEW_PARAM_MARKER:null), $.address.parameter(e.ID_PARAM, t), $.address.update())}, setRegionId:function(t){var a = $.address.parameter(e.VIEW_PARAM), r = $.address.parameter(e.ID_PARAM); (a != e.VIEW_PARAM_REGION || r != t) && ($.address.parameter(e.VIEW_PARAM, t?e.VIEW_PARAM_REGION:null), $.address.parameter(e.ID_PARAM, t), $.address.update())}, dispatch:function(){this.wait = !1, this.addressChangeEventHandler()}, addressChangeEventHandler:function(){if (!this.wait && this.lastQuery != $.address.queryString()){var t = $.address.parameter(e.MAP_PARAM), a = $.address.parameter(e.VIEW_PARAM), r = $.address.parameter(e.ID_PARAM); switch (a){case e.VIEW_PARAM_MARKER:mapViewerCtrl.selectMarker(t?t:treeController.defaultMapId, r, !1, !0); break; case e.VIEW_PARAM_REGION:mapViewerCtrl.showRegion(t?t:treeController.defaultMapId, r); break; default:mapViewerCtrl.showMap(t?t:treeController.defaultMapId)}this.lastQuery = $.address.queryString()}}}, treeController = {$treeElement:null, treeInstance:null, defaultMapId:null, init:function(){this.$treeElement = $("#treeElement"), this.$treeElement = this.$treeElement.jstree({json_data:{data:[]}, ui:{select_limit:1}, themes:{

theme:"apple",
url:"view/common/css/apple/style.css"
}, types:{
types:{
	"default":{
		icon:{
			image:"view/common/img/map_icon.png"
		}, 
	select_node:function(e){this.is_closed(e) && this.toggle_node(e)}, deselect_node:function(){return!1}}}}, plugins:["themes", "json_data", "ui", "types"]}).on("select_node.jstree", function(e, t){t.rslt.e && mapViewerCtrl.showMap(t.rslt.obj.data("id"))}), this.treeInstance = $.jstree._reference(this.$treeElement.jstree("get_index"))}, setData:function(e){this.treeInstance._get_settings().json_data = {data:e}, this.treeInstance.refresh( - 1); var t = this; setTimeout(function(){t.$treeElement.jstree("open_all")}, 30)}, selectNodeById:function(e){if (!viewCtrl.settings.disableViewTab){var t = this; $("li", this.$treeElement).each(function(){return $li = $(this), $li.data("id") == e?(t.$treeElement.jstree("select_node", $li, !0), !1):void 0})}}}, $.extend(serviceCtrl, {mapRequest:null, markerInfoRequest:null, markerTypeRequest:null, searchMarkersRequest:null, getStartData:function(e){

this.callAjax(null, null, e)}, 
getMapView:function(e, t){this.abortMapRequest(),
        this.mapRequest = this.callAjax(null, "getMapView", t, {id:e}).always(function(){ 
		serviceCtrl.mapRequest = null
}
)}, abortMapRequest:function(){
this.mapRequest && this.mapRequest.abort()},
        getMarkerInfo:function(e, t){
			this.abortMarkerInfoRequest(),
		
                this.markerInfoRequest = this.callAjax(null, "getMarkerInfo", t, {
				id:e
				}).always(
					function(){
					serviceCtrl.markerInfoRequest = null
				})
				
		}, abortMarkerInfoRequest:function(){this.markerInfoRequest && this.markerInfoRequest.abort()}, getSearchType:function(e, t){this.abortMarkerTypeRequest(), this.markerTypeRequest = this.callAjax(null, "getSearchType", t, {id:e}).always(function(){serviceCtrl.markerTypeRequest = null})}, abortMarkerTypeRequest:function(){this.markerTypeRequest && this.markerTypeRequest.abort()}, searchMarkers:function(e, t){this.abortsearchMarkersRequest(), this.searchMarkersRequest = this.callAjax(null, "searchMarkers", t, e).always(function(){serviceCtrl.searchMarkersRequest = null})}, abortsearchMarkersRequest:function(){this.searchMarkersRequest && this.searchMarkersRequest.abort()}}), $.extend(modalCtrl, {_super:{markerLongTextModal:$.proxy(
				modalCtrl.markerLongTextModal, modalCtrl)}}, {
						markerLongTextModal:function(e){
						e.parents(".search-result-row").length?(
							$(".modal-body p", this.$longTextModal).html(e.data("cfm-long")), 
							$(".modal-header h3", this.$longTextModal).html($(".icon", e.parents(".search-result-row")).html() + $(".cfm-title", e.parents(".search-result-row")).text()), 
							this.$longTextModal.modal()
						):this._super.markerLongTextModal(e)
					}
				}), $.extend(mapViewerCtrl, {_super:{showMap:$.proxy(mapViewerCtrl.showMap, mapViewerCtrl), getMapSuccess:$.proxy(mapViewerCtrl.getMapSuccess, mapViewerCtrl), showRegion:$.proxy(mapViewerCtrl.showRegion, mapViewerCtrl), selectMarker:$.proxy(mapViewerCtrl.selectMarker, mapViewerCtrl), unselectElement:$.proxy(mapViewerCtrl.unselectElement, mapViewerCtrl)}}, {pendingRegionId:null, showMap:function(e){viewCtrl.smallScreen && viewCtrl.panelDisplay("close", !0), this._super.showMap(e) && viewCtrl.mapShowLoading()}, getMapSuccess:function(e, a){if (viewCtrl.removeAjaxLoader(), 4 != e && (this.pendingMarkerId || this.pendingRegionId || t.setMarkerId(null), t.setMapId(a.map.id != treeController.defaultMapId?a.map.id:null), treeController.selectNodeById(a.map.id), this._super.getMapSuccess(e, a), !viewCtrl.settings.disableViewTab)){
				
				var r = $("#regionListContent"); 
				r.empty(), this._regionsData?(
				r.html(Templates.regionListElements(this._regionsData)), $("#regionList").removeClass("hide"), viewCtrl.$panelColumn.data("last-display", viewCtrl.$panelColumn.css("display")), viewCtrl.$panelColumn.add("#viewsTab").show(), r.columnize({width:160}), $("#viewsTab").css({display:""}), viewCtrl.$panelColumn.css("display", viewCtrl.$panelColumn.data("last-display"))):$("#regionList").addClass("hide")}}, selectMarker:function(e, a, r, n){viewCtrl.smallScreen && viewCtrl.panelDisplay("close", !0), this._super.selectMarker(e, a, r, n)?t.setMarkerId(a):this.unselectElement(!0)}, showRegion:function(e, a, r){viewCtrl.smallScreen && viewCtrl.panelDisplay("close", !0), this._super.showRegion(e, a, r) && t.setRegionId(a)}, unselectElement:function(e){this._super.unselectElement() && e && t.setMarkerId(null)}}), $.extend(viewCtrl, {_super:{init:$.proxy(viewCtrl.init, viewCtrl)}}, {$panelColumn:null, $mapColumn:null, $searchTypeSelect:null, 
				
				$searchValidationAlert:null, $markerShowInfoBtn:null, $ajaxAnimElement:null, settingsData:null, smallScreen:null, init:function(){this._super.init(), this.$panelColumn = $("#panelColumn"), this.$mapColumn = $("#mapColumn"), 
				
				this.$searchValidationAlert = $("#searchValidationAlert"), this.$ajaxAnimElement = $('<img id="ajaxAnimation" src="view/common/img/progress_48.gif" width="48" height="48" alt="loading" />'), this.$searchTypeSelect = $("#searchTypeSelect"); 
				
				var e = $(document); 
				$("#panelToggleBtn").click(function(e){
					viewCtrl.togglePanel(), 
					e.preventDefault()
				}), e.on("click", "#markerInfoCloseBtn", function(){return viewCtrl.hideMarkerInfo(), !1}), e.on("click", "#markerInfoPopover", function(e){e.stopPropagation()}), e.on("click", "#markerModal", function(e){e.stopPropagation()}), e.on("click", "a[data-cfm-map-link]", function(e){
				var t = $(this).data("cfm-map-link"); mapViewerCtrl.showMap(t), e.preventDefault()}), e.on("click", "a[data-cfm-marker-link]", function(e){
				var t = $(this).data("cfm-marker-link"); mapViewerCtrl.selectMarker(t.mapId, t.markerId, !1, !0), e.preventDefault()}), e.on("click", "a[data-cfm-region-link]", function(e){var t = $(this).data("cfm-region-link"); mapViewerCtrl.showRegion(t.mapId, t.regionId), e.preventDefault()}), e.on("click", "*[data-cfm-marker-info]", function(){return viewCtrl.showMarkerInfo($(this)), !1}), 
				
					$("#backSearchFormBtn").click(function(){viewCtrl.hideMarkerInfo(), $("#srchResultsContent,#resultsCount").empty(), $("#srchResultsTab.tab-pane").removeClass("active"), $("#srchFormTab.tab-pane").addClass("active")}), 
				
				this.$searchTypeSelect.change(function(){
					viewCtrl.changeSearchType()
				}), 
				
				$("#searchForm").submit(function(){
					var e = $(this).serializeObject();
					//e.urb = getUrlVars()["id"]; 
					return $("#currentCbx").prop("checked") && (e.mapId = mapViewerCtrl.currentMapId), viewCtrl.searchMarkers(e), !1
				}), 
					$(".close", this.$searchValidationAlert).click(function(){
						return viewCtrl.$searchValidationAlert.slideUp(), !1
					}), 
				$("#panelTabs").scroll(function(){viewCtrl.hideMarkerInfo()}), 
					$(window).resize(function(){
						viewCtrl.windowResize()
					}), 
					$("#panelNav li a").click(
						function(e){
							$(this).tab("show"), e.preventDefault()
						}
					), serviceCtrl.getStartData($.proxy(this.getStartDataSuccess, this))}, getStartDataSuccess:function(e, a){this.settings = a.settings, this.settings.disableViewTab?($("#searchTabLink").tab("show"), $("#viewsTab").addClass("hide"), $("#viewsTabLink").parent().addClass("hide")):(treeController.init(), $("#viewsTabLink").tab("show"), a.tree.length?(treeController.setData(a.tree), viewCtrl.applyViewState(this.$panelColumn, "")):viewCtrl.applyViewState(this.$panelColumn, "noMaps")), this.settings.panelOpened || this.panelDisplay("close", !0), this.$searchTypeSelect.html(Templates.markerTypesSelect(a.markerTypes)), this.settings.defaultMarkerType && (this.$searchTypeSelect.val(this.settings.defaultMarkerType), 
				
				this.changeSearchType()), 
				treeController.defaultMapId = a.defaultMapId, 
				this.windowResize(), 
					$("body").tooltip({
						selector:"[rel=tooltip]", 
						placement:viewCtrl.smallScreen?"left":"right", 
						container:"body"
					}), t.dispatch(), 
					$("#preloader").remove(), 
					this.$mapColumn.add(this.$panelColumn).removeClass("invisible")}, 
						windowResize:function(){
								var e = $(window), 
								t = $("#panelTabs"), 
								a = e.height(); 
								this.$mapColumn.css("height", a), 
								t.children(".tab-pane").css("height", 
								a - $("#panelNav").outerHeight(!0) - 10), 
								this.smallScreen = $("html").hasClass("lte-920")
							}, 
							searchMarkers:function(e){
							
							var t = !0, 
							a = !1, 
							r = $("#searchForm"); 
							
							$('input[type="text"],select:not(#searchTypeSelect)', r).each(function(){
								
								var e = $(this), 
								r = e.val();

								$(this).is("select") && r?t = !1:r.length >= 1?(t = !1, e.removeClass("error")):r.length < 1 && r.length > 0 && (a = !0, e.addClass("error"))
								
							}), 
							t || a?this.$searchValidationAlert.slideDown():this.$searchValidationAlert.slideUp(), 
							t || a || ($("#srchFormTab.tab-pane").removeClass("active"), 
							$("#srchResultsTab.tab-pane").addClass("active"), 
							this.searchMarkersShowLoading(), 
							serviceCtrl.searchMarkers(e, $.proxy(this.searchMarkersSuccess, this)))
							}, 
							searchMarkersSuccess:function(e, t){
									this.removeAjaxLoader(), 
									$("#resultsCount").html(Templates.resultsCount(t.count)), 
									$("#srchResultsContent").html(t.count?t.result:Templates.noResults)
							}, 
							searchMarkersShowLoading:function(){
								$("#srchResultsContent").html(this.$ajaxAnimElement)
							},
							changeSearchType:function(){
					//var e = $("#searchTypeSelect").val(); 
					//return this.$searchValidationAlert.slideUp(), e?(this.searchOptionsShowLoading(), serviceCtrl.getSearchType(e, $.proxy(this.getSearchTypeSuccess, this)), void 0):($("#srchInputsContent").empty(), void 0)
				}, 
				getSearchTypeSuccess:function(e, t){
					
					if (this.removeAjaxLoader(), $("#srchInputsContent").empty(),t.markerType.params)
					{
						var a = ""; 
						$.each(t.markerType.params, function(e, r){
							if (a += Templates.paramLabel(r), "dictionary" == r.type){
								var n = t.dictEntries?t.dictEntries[r.typeValue]:null; a += Templates.paramSelect(r, n)
							} else a += Templates.paramInput(r); 
								$("#srchInputsContent").html(a)
						})
					}
					
				}, searchOptionsShowLoading:function(){
					$("#srchInputsContent").html(this.$ajaxAnimElement)
				}, mapShowLoading:function(){
					mapViewerCtrl.$mapContainer.append(this.$ajaxAnimElement), this.$ajaxAnimElement.css({position:"absolute", top:"50%", left:"50%", width:"48px", height:"48px", margin:"-24px 0 0 -24px"})}, showMarkerInfo:function(e){this.hideMarkerInfo(), this.$markerShowInfoBtn = e; var t = this.$markerShowInfoBtn.data("cfm-marker-info"); this.markerInfoShowLoading(), serviceCtrl.getMarkerInfo(t, $.proxy(this.getMarkerInfoSuccess, this))}, hideMarkerInfo:function(){serviceCtrl.abortMarkerInfoRequest(), this.$markerShowInfoBtn && (this.$markerShowInfoBtn.data("popover") && this.$markerShowInfoBtn.popover("destroy"), this.markerInfoHideLoading(), this.$markerShowInfoBtn = null)}, getMarkerInfoSuccess:function(e, t){if (this.markerInfoHideLoading(), t){var a = $(t), r = mapViewerCtrl.markerPopoverContentFunction.apply(a), n = mapViewerCtrl.markerPopoverTitleFunction.apply(a); this.$markerShowInfoBtn.popover({placement:viewCtrl.smallScreen?"left":"right", html:!0, content:r, title:n, trigger:"manual", container:"body", template:'<div id="markerInfoPopover" class="popover cfm-marker-popover"><div class="arrow"></div><div class="popover-inner"><button id="markerInfoCloseBtn" type="button" class="close" >&times;</button><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'}), this.$markerShowInfoBtn.popover("show"), $(document).one("click", function(){viewCtrl.hideMarkerInfo()})}}, markerInfoShowLoading:function(){this.$markerShowInfoBtn.tooltip("hide"), this.$markerShowInfoBtn.hide(), this.$ajaxAnimElement.insertAfter(this.$markerShowInfoBtn).attr("width", this.$markerShowInfoBtn.outerWidth()).attr("height", this.$markerShowInfoBtn.outerHeight())}, markerInfoHideLoading:function(){this.removeAjaxLoader(), this.$markerShowInfoBtn.slideDown()}, removeAjaxLoader:function(){this.$ajaxAnimElement.remove().attr("style", "")}, panelDisplay:function(e, t){switch (e = "undefined" != typeof e?e:"show", $btn = $("#panelToggleBtn"), e){case"open":this.$panelColumn.show(), $btn.addClass("close-panel").show(), this.$panelColumn.animate({"margin-left":0}, t?0:500, function(){$("body").removeClass("panel-closed"), viewCtrl.smallScreen || mapViewerCtrl.mapViewer.invalidateSize(!0)}); break; case"close":$("body").addClass("panel-closed"); var a = this; this.$panelColumn.animate({"margin-left": - this.$panelColumn.width()}, t?0:500, function(){a.$panelColumn.hide(), $btn.removeClass("close-panel"), !viewCtrl.smallScreen && mapViewerCtrl.mapViewer && mapViewerCtrl.mapViewer.invalidateSize(!0)}); break; default:return $.error("not supported panel display action given"), void 0}$btn.attr("data-original-title", $btn.data("title-" + ("close" == e?"open":"close"))), $("i", $btn).attr("class", "icon-chevron-" + ("open" == e?"left":"right"))}, togglePanel:function(){"none" == this.$panelColumn.css("display")?this.panelDisplay("open"):this.panelDisplay("close")}});
        var t = new e; t.init(), modalCtrl.init(), mapViewerCtrl.init(), viewCtrl.init()
		
		$(document).on( "click", ".leyenda a", function() {
			var clase = $(this).attr("data-class");			
			$(".cfm-marker").hide();
			$(".leyenda a").removeClass('leyendafilaActivo');
			$(this).addClass('leyendafilaActivo');
			if(clase=="Todos"){
				$(".cfm-marker").show();
			} else {
				$(".cfm-marker-"+clase).show(); 
			}
		});
		
});