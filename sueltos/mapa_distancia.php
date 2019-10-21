<?php
require_once('../config/constantes.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>Ubicación</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;hl=es&amp;key=<?php echo _key_google;?>" type="text/javascript"></script>
  </head>
  <body onunload="GUnload()">

	<div style="width:200px height:40px; padding:10px; background:#FFF; position:absolute; top:14px; left:80px; z-index:50; border: 1px #ccc solid;">Distancia: <?php echo round(distanciaGeodesica($_GET['ola'], $_GET['olo'], $_GET['dla'], $_GET['dlo']),2);?> Km.</div>	
	<div id="map" style="width: 580px; height: 380px"></div>



    <script type="text/javascript">
    //<![CDATA[

    if (GBrowserIsCompatible()) {
      
      // === The basis of the arrow icon information ===
      var arrowIcon = new GIcon();
      arrowIcon.iconSize = new GSize(24,24);
      arrowIcon.shadowSize = new GSize(1,1);
      arrowIcon.iconAnchor = new GPoint(12,12);
      arrowIcon.infoWindowAnchor = new GPoint(0,0);
      
      // === Returns the bearing in degrees between two points. ===
      // North = 0, East = 90, South = 180, West = 270.
      var degreesPerRadian = 180.0 / Math.PI;
      function bearing( from, to ) {
        // See T. Vincenty, Survey Review, 23, No 176, p 88-93,1975.
        // Convert to radians.
        var lat1 = from.latRadians();
        var lon1 = from.lngRadians();
        var lat2 = to.latRadians();
        var lon2 = to.lngRadians();

        // Compute the angle.
        var angle = - Math.atan2( Math.sin( lon1 - lon2 ) * Math.cos( lat2 ), Math.cos( lat1 ) * Math.sin( lat2 ) - Math.sin( lat1 ) * Math.cos( lat2 ) * Math.cos( lon1 - lon2 ) );
        if ( angle < 0.0 )
	 angle  += Math.PI * 2.0;

        // And convert result to degrees.
        angle = angle * degreesPerRadian;
        angle = angle.toFixed(1);

        return angle;
      }
       
      // === A function to create the arrow head at the end of the polyline ===
      function arrowHead(points) {
        // == obtain the bearing between the last two points
        var p1=points[points.length-1];
        var p2=points[points.length-2];
        var dir = bearing(p2,p1);
        // == round it to a multiple of 3 and cast out 120s
        var dir = Math.round(dir/3) * 3;
        while (dir >= 120) {dir -= 120;}
        // == use the corresponding triangle marker 
        arrowIcon.image = "http://www.google.com/intl/en_ALL/mapfiles/dir_"+dir+".png";
        map.addOverlay(new GMarker(p1, arrowIcon));
      }
      
      // === A function to put arrow heads at intermediate points
      function midArrows(points) {
        for (var i=1; i < points.length-1; i++) {  
          var p1=points[i-1];
          var p2=points[i+1];		  
          var dir = bearing(p1,p2);
          // == round it to a multiple of 3 and cast out 120s
          var dir = Math.round(dir/3) * 3;
          while (dir >= 120) {dir -= 120;}
         // == use the corresponding triangle marker 		 
          arrowIcon.image = "http://www.google.com/intl/en_ALL/mapfiles/dir_"+dir+".png";
          map.addOverlay(new GMarker(points[i], arrowIcon));
        }
      }
	   var side_bar_html = "";
      var gmarkers = [];
	  var htmls = [];
	  var i = 0;
	  function createMarker(point,name,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        // save the info we need to use later for the side_bar
        gmarkers[i] = marker;
        htmls[i] = html;
        // add a line to the side_bar html
        side_bar_html += '<a href="javascript:myclick(' + i + ')">' + name + '<\/a><br>';
        i++;
        return marker;
      }
      
	   function myclick(i) {
        gmarkers[i].openInfoWindowHtml(htmls[i]);
      }
	  
      // create the map
      var map = new GMap2(document.getElementById("map"));
      map.setMapType(G_HYBRID_MAP);
	  map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng(<?php echo $_GET['ola'];?>,<?php echo $_GET['olo'];?>), 15);
      
      // === The array of points for the polyline ===
      var points = [ new GLatLng(<?php echo $_GET['ola'];?>,<?php echo $_GET['olo'];?>), new GLatLng(<?php echo $_GET['dla'];?>,<?php echo $_GET['dlo'];?>)];
	  
	
	   var marker1 = createMarker(new GLatLng(<?php echo $_GET['ola'];?>,<?php echo $_GET['olo'];?>),1,'<?php echo "<b>COMUNIDAD</b><br>".$_GET['ode'];?>');
	    map.addOverlay(marker1);
		
		var marker2 = createMarker(new GLatLng(<?php echo $_GET['dla'];?>,<?php echo $_GET['dlo'];?>),2,'<?php echo "<b>AREA</b><br>".$_GET['dde'];?>');
	    map.addOverlay(marker2);
	 
      // === Create the polyline
      map.addOverlay(new GPolyline(points));
      // === add the arrow head
      arrowHead(points);
	  //midArrows(points);
	  

      
    }

    else {
      alert("Lo sentimos, el API de Google Maps no es compatible con este navegador");
    }

    // This Javascript is based on code provided by the
    // Community Church Javascript Team
    // http://www.bisphamchurch.org.uk/   
    // http://econym.org.uk/gmap/

    //]]>
	
    </script>
  </body>

</html>
<?php
 function distanciaGeodesica($lat1, $long1, $lat2, $long2){

		 $degtorad = 0.01745329;
		 $radtodeg = 57.29577951;

		 $dlong = ($long1 - $long2);
		 $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad))
		   + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad)
		   * cos($dlong * $degtorad));
		   
		  $dd = acos($dvalue) * $radtodeg;
		 
		  $miles = ($dd * 69.16);
		  $km = ($dd * 111.302);

		  return $km;
}
?>