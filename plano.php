<script type="text/javascript" src="swfobject.js"></script>
<div id="flashcontent">
</div>
<script type="text/javascript">
   var so = new SWFObject("mapa_print.swf", "mymovie", "1000", "455", "8", "#FFFFFF");
   so.addParam("flashVars", "url=urvanizacion.xml.php");
   so.addParam("play", "true");
   so.addParam("allowFullScreen", "allowFullScreen");
   so.write("flashcontent");
</script>