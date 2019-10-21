<?phprequire_once('../config/constantes.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
      	<title>Buscar latitud y longitud en Google Maps</title>
      <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>	  	  <script type="text/javascript" src="../js/jquery-impromptu.2.7.min.js"></script>	  	  <link href="../css/examples.css" rel="stylesheet" type="text/css" />
	  <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;hl=es&amp;key=<?php echo _key_google;?>"
	  type="text/javascript"></script>	  	  <script type="text/javascript" src="../js/urm2lat.js"></script><script>	function cmdLat2UTM_click(){        var xy = new Array(2);        if (isNaN (parseFloat (document.frmConverter.txtLon.value))) {            alert ("Por favor ingrese una longitud valida.");            return false;        }        lon = parseFloat (document.frmConverter.txtLon.value);        if ((lon < -180.0) || (180.0 <= lon)) {            alert ("La longitud ingresada esta fuera de rango.  " +                   "Por favor ingrese un valor comprendido entre [-180, 180).");            return false;        }        if (isNaN (parseFloat (document.frmConverter.txtLat.value))) {            alert ("Por favor ingrese una latitud valida.");            return false;        }        lat = parseFloat (document.frmConverter.txtLat.value);        if ((lat < -90.0) || (90.0 < lat)) {            alert ("La latitud ingresada esta fuera de rango.  " +                   "Por favor ingrese un valor comprendido entre [-90, 90].");            return false;        }        // Compute the UTM zone.        zone = Math.floor ((lon + 180.0) / 6) + 1        zone = LatLonToUTMXY (DegToRad (lat), DegToRad (lon), zone, xy);        /* Set the output controls.  */        document.frmConverter.txtX.value = xy[0];        document.frmConverter.txtY.value = xy[1];        document.frmConverter.txtZone.value = zone;        if (lat < 0)            // Set the S button.            document.frmConverter.rbtnHemisphere[1].checked = true;        else            // Set the N button.            document.frmConverter.rbtnHemisphere[0].checked = true;        return true;	}	function cmdUTM2Lat_click(){        latlon = new Array(2);        var x, y, zone, southhemi;        if (isNaN (parseFloat (document.frmConverter.txtX.value))) {			$.prompt('Por favor ingrese un valor valido para X.',{ opacity: 0.8 });            return false;        }        x = parseFloat (document.frmConverter.txtX.value);        if (isNaN (parseFloat (document.frmConverter.txtY.value))) {			$.prompt('Por favor ingrese un valor valido para Y.',{ opacity: 0.8 });            return false;        }        y = parseFloat (document.frmConverter.txtY.value);        if (isNaN (parseInt (document.frmConverter.txtZone.value))) {			$.prompt('Por favor ingrese una zona valida de UTM.',{ opacity: 0.8 });            return false;        }        zone = parseFloat (document.frmConverter.txtZone.value);        if ((zone < 1) || (60 < zone)) {			$.prompt('El valor de zona de UTM esta fuera de rango. Por favor ingrese un valor entre [1, 60]."',{ opacity: 0.8 });	               return false;        }        if (document.frmConverter.rbtnHemisphere[1].checked == true)            southhemi = true;        else            southhemi = false;        UTMXYToLatLon (x, y, zone, southhemi, latlon);        //document.frmConverter.txtLon.value = RadToDeg (latlon[1]);        //document.frmConverter.txtLat.value = RadToDeg (latlon[0]);				document.frmConverter.address.value =RadToDeg (latlon[0])+','+RadToDeg (latlon[1]);;        return true;	}</script>
      
      
<script type="text/javascript" language="javascript">
 
 function load() {
 
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));				 map.setMapType(G_HYBRID_MAP);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
       var center = new GLatLng(<?php echo $_GET['latitud'];?>,<?php echo $_GET['longitud'];?>);
        map.setCenter(center, 15);
        geocoder = new GClientGeocoder();
        var marker = new GMarker(center, {draggable: true});  
        map.addOverlay(marker);
        document.getElementById("lat").innerHTML = center.lat().toFixed(5);
        document.getElementById("lng").innerHTML = center.lng().toFixed(5);
 
	  GEvent.addListener(marker, "dragend", function() {
       var point = marker.getPoint();
	      map.panTo(point);
       document.getElementById("lat").innerHTML = point.lat().toFixed(5);
       document.getElementById("lng").innerHTML = point.lng().toFixed(5);
        });
 
 
	 GEvent.addListener(map, "moveend", function() {

		  map.clearOverlays();
    var center = map.getCenter();
		  var marker = new GMarker(center, {draggable: true});
		  map.addOverlay(marker);
		  document.getElementById("lat").innerHTML = center.lat().toFixed(5);
	   document.getElementById("lng").innerHTML = center.lng().toFixed(5);
 
 
	 GEvent.addListener(marker, "dragend", function() {
      var point =marker.getPoint();
	     map.panTo(point);
      document.getElementById("lat").innerHTML = point.lat().toFixed(5);
	     document.getElementById("lng").innerHTML = point.lng().toFixed(5);
 
        });
 
        });
 
      }
    }
 
	   function showAddress(address) {
	
	   var map = new GMap2(document.getElementById("map"));
       map.addControl(new GSmallMapControl());
       map.addControl(new GMapTypeControl());
       if (geocoder) {
        geocoder.getLatLng(
          address,
          function(point) {
            if (!point) {			  $.prompt(address + " (No Encontrado)",{ opacity: 0.8 });
            } else {
		  document.getElementById("lat").innerHTML = point.lat().toFixed(5);
	   document.getElementById("lng").innerHTML = point.lng().toFixed(5);
		 map.clearOverlays()
			map.setCenter(point, 15);
   var marker = new GMarker(point, {draggable: true});  
		 map.addOverlay(marker);
 
		GEvent.addListener(marker, "dragend", function() {
      var pt = marker.getPoint();
	     map.panTo(pt);
      document.getElementById("lat").innerHTML = pt.lat().toFixed(5);
	     document.getElementById("lng").innerHTML = pt.lng().toFixed(5);
        });
 
 
	 GEvent.addListener(map, "moveend", function() {
		  map.clearOverlays();
    var center = map.getCenter();
		  var marker = new GMarker(center, {draggable: true});
		  map.addOverlay(marker);
		  document.getElementById("lat").innerHTML = center.lat().toFixed(5);
	   document.getElementById("lng").innerHTML = center.lng().toFixed(5);
 
	 GEvent.addListener(marker, "dragend", function() {
     var pt = marker.getPoint();
	    map.panTo(pt);
		document.getElementById("lat").innerHTML = pt.lat().toFixed(5);
	   document.getElementById("lng").innerHTML = pt.lng().toFixed(5);
	  
        });
		
        });
 
            }
          }
        );
      }
    }
    function Limpiar(_nombre)
    {
        if(document.getElementById(_nombre).value=='Introduce el lugar, departamento, pais o las cordenadas latitud, longitud')
        {
            document.getElementById(_nombre).value='';
            document.getElementById(_nombre).style.color= 'black';
        }
        else
        {
        
        
        }
       
    }
  function bloquear()
  {
     if(document.getElementById('chkAceptaposicion').checked==true)
     {
     
     document.getElementById('map').style.display="none";
     document.getElementById('chkAceptaposicion').focus();
      
        return false;
        
     }
  }
  
  function MuestraTrasparencia()
    {    

            var offsetTrail = document.getElementById('anclaid');
            var offsetLeft = 0;
            var offsetTop = 0;
            while (offsetTrail) {
            offsetLeft += offsetTrail.offsetLeft;
            offsetTop += offsetTrail.offsetTop;
            offsetTrail = offsetTrail.offsetParent;
            }
            if (navigator.userAgent.indexOf("Mac") != -1 && typeof document.body.leftMargin != "undefined" && navigator.appName=="Microsoft Internet Explorer" ) {
            offsetLeft += parseInt(document.body.leftMargin);
            offsetTop += parseInt(document.body.topMargin);
            }
            window.scrollTo(offsetLeft,offsetTop)
            var elemento = document.getElementById('divTransparencia');
            try{

                document.getElementById('divTransparencia').style.height = "420px";                 
                document.getElementById('divTransparencia').style.width = "570px";                 
                document.getElementById('divTransparencia').style.top = "0px";                
                elemento.style.visibility = 'visible';
            } catch(e){}
            
    }    

    function OcultaDivs()
    {
            var elemento = document.getElementById('divTransparencia');
            try{
                document.getElementById('divTransparencia').style.height = "0px";
                elemento.style.visibility = 'hidden';
                
            } catch(e){}
    
    }
  function inhabilitar(_nombre)
  {
		//center.lat().toFixed(5));
		//. = $('#lat').html());
		//alert($(this).parent().parent().html()+'hhhhh');
      if(document.getElementById('chkAceptaposicion').checked==true)
      {
     
        MuestraTrasparencia();
        document.getElementById('chkAceptaposicion').focus();
        return false;
      }
      else
      {
      OcultaDivs();
      }
  }
  function rellenar(_nombre)
  {
   if(document.getElementById(_nombre).value=='')
        {
            document.getElementById(_nombre).value='Introduce el lugar, departamento, pais o las cordenadas latitud, longitud';
            document.getElementById(_nombre).style.color="gray";
        }
  }
  
    </script>

  </head>
  
<body onload="load()" onunload="GUnload()" bgcolor="#FFFFFF" >
   <form  name="frmConverter" action="#" onsubmit="showAddress(this.address.value); return false">
     <p style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;">             X:<input name="txtX" type="text" id="txtX3" size="15" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;">	 Y:<input name="txtY" type="text" id="txtY3" size="15" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;">	 Zona:<input name="txtZone" type="text" id="txtZone3" size="4" value=20 style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;">	 Norte:<INPUT onclick=0 type=radio style="border:none"  value=N name=rbtnHemisphere >     Sur:<INPUT onclick=0  type=radio style="border:none" CHECKED value=S name=rbtnHemisphere>	 <a href="javascript:var a=cmdUTM2Lat_click();">Convertir de UTM a Lat,Lon</a>      </p>	 	 <p>        
      <input type="text" size="80" name="address" id ="address" value="" onblur="rellenar('address');"  onclick=" Limpiar('address');" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;" />
      <input name="ctl00" type="submit" value="Buscar" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;" CssClass="error" ForeColor="Red" />
      </p>
     <p align="left">
      <a name="Aqui" id="anclaid" style="display:none;"><div></div></a>

      <table  bgcolor="#CCCCCC" width="550">
       <tr style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;" CssClass="error" ForeColor="Red">
	<td width="100"><b>Latitud</b></td>
	<td id="lat"></td>
</tr>

       <tr style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;" CssClass="error" ForeColor="Red">
	<td width="100"><b>Longitud</b></td>
	<td id="lng"></td>

</tr>

      </table>
     </p>
     <p>
      <div align="center" id="map"  style="width: 550px; height: 300px; z-index:0;">
       <br/>
      </div>
     </p>
     <p></p>

     
     
     <div id="divTransparencia" style="position:absolute; visibility: hidden;
height: 0px; width: 0px; vertical-align: middle; top: 0px; left: 0px;
z-index: 300; background-color: Gray; filter: alpha(opacity=50); float: left;
-moz-opacity: .50; opacity: .50; overflow: hidden; display: block;">
    &nbsp;
</div>
     
   </form>
</body>
</html>

