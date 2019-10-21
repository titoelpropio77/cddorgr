<?php
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
      	<title>Buscar latitud y longitud en Google Maps</title>
      <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
	  <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;hl=es&amp;key=<?php echo _key_google;?>"
	  type="text/javascript"></script>
      
      
<script type="text/javascript" language="javascript">
 
 function load() {
 
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
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
            if (!point) {
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
     <p style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;	font-weight: bold;	text-decoration: none;	color: #666666;">        
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
