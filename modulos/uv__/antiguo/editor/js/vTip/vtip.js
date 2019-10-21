/**
Vertigo Tip by www.vertigo-project.com
Requires jQuery
*/

this.vtip = function() {    
    this.xOffset = -10; // x distance from mouse
    this.yOffset = 30; // y distance from mouse       
	
    $(".mapaMarkerPuntos").unbind().hover(    
        function(e) {
            this.t = this.title;
            this.title = ''; 
            this.top = (e.pageY + yOffset); this.left = (e.pageX + xOffset);
            
            $('body').append( '<div id="vtip"><div id="vtipArrow">&nbsp; </div>' + this.t + '</div>' );
                        
            //$('p#vtip #vtipArrow').attr("src", 'editor/js/vTip/images/vtip_arrow.png');
            $('#vtip').css("top", this.top+"px").css("left", this.left+"px").fadeIn("slow");
            
        },
        function() {
            this.title = this.t;
            $("#vtip").fadeOut("slow").remove();
        }
    ).mousemove(
        function(e) {
            this.top = (e.pageY + yOffset);
            this.left = (e.pageX + xOffset);
                         
            $("#vtip").css("top", this.top+"px").css("left", this.left+"px");
        }
    );            
    
};