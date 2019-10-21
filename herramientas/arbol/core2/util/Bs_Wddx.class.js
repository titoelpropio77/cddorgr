/**
* Bs_Wddx.class.js
* Basic transformer from any Javascript type to WDDX-format and back. Used mainly for js-rs:
* Passing data from js-code to the server script (written in PHP in my case) and back to the script.
*
* Usage Sample:
*   var jsVar = new Object();                              // Take any random Object   
*   jsVar.anArray  = new Array(1,2,"hallo!", "<hallo>!");
*
*   var wddx = new Bs_Wddx();                             // Create the WDDX-handler instance
*   var wddxXml = wddx.serialize(jsVar);                  // Serialize the JS-variable
*   // Transfer the WDDX data to where you want 
*   var tmp = wddx.deserialize(wddxXml);                  // Deserialize the wddx-XML data back to the JS-variable
*
* INFO: PHP Object Support
*   - Object handling in WDDX is very poorly and inconsistently supported (Found that out *after* implementation :-( ).
*     If you pass a  WDDX-js-objects it will convert to a simple PHP-hash array. This is usually fully sufficient;
*     If you want PHP to instanciate a PHP-object you *must* add a attribute name called 'php_class_name' to the js-object 
*     and set it to the PHP class name you wish to instanceiate. PHP will then try to create that class.
*     I recomend to live without this feature.
*
* INFO: Js Object Support When deserializing: 
*   - If you want specific js-object to be instanciated from the WDDX data, then the class name musst be given in
*     the 'type' of the WDDX <struct>-tag. E.g.  <struct type='MyClassName'>...</struct>. Unfortunately PHP's WDDX  
*     does *not* support this feature. I recomend to live without this feature.
*
* NOTE:
*   - WDDX extion 'recordset', 'binary' and timezone are not supported (yet?)  
*
* @author      sam blum <sam-at-blueshoes-dot-org>
* @copyright   blueshoes.org
* @version     4.0.$Id: Bs_Wddx.class.js,v 1.5 2005/07/22 06:57:42 andrej Exp $
* @package     javascript_core
* @subpackage  util
* @dependences /_bsJavascript/core/util/Bs_XmlParser.class.js, /_bsJavascript/core/lang/Bs_Misc.lib.js
* @access      public
*/
function Bs_Wddx () {
	
  //**************************************************************************************************************
  //***  Serialize
  //**************************************************************************************************************
  /**
  * Creates a WDDX packet 
  * INFO: Put all your data in one 'top-level' variable or object and pass it to this function.
  * @param obj the 'top-level' variable or object
  * @return string A WDDX Package (= XML)
  */
  this.serialize = function(obj) { 
    var ret = "<wddxPacket version='1.0'><header /><data>" + this._recursiveSerialize(obj) + '</data></wddxPacket>';
    return ret;
  }
  
  /**
  * Convert js-vars and js-objects to corresponding WDDX recursively 
  * @param obj any variable or object
  */
  this._recursiveSerialize = function(obj) { 
    var status = false;
    var cr = ''; // '\n' adds cr for more readabillity, but took it away (could desturb)
    var ret = '';
    var tmpArray = new Array();
    var ii = 0;
    do {
      if (('undefined' == typeof(obj)) || (null == obj)) {
    		ret = '<null />';
        status = true;
        break;
      }
      var value = obj.valueOf();
      switch(typeof(value)) {
        case 'boolean':
  		    ret = "<boolean value='"+value+"'/>";
          break;
        case 'number':
  		    // Distinguish between numbers and date-time values
					if (typeof(instanceOf) == 'function') {
						var isAdate = instanceOf(obj, Date);
					} else {
						var isAdate = obj instanceof Date;
					}
    		  if (isAdate) {
            var tmp = 0;
            var Y  = 1000 < (tmp = obj.getYear())  ? tmp : tmp+1900; // Compansate Y2K bug
            var M  = 10 < (tmp = obj.getMonth()+1) ? tmp : '0'+tmp;
            var D  = 10 < (tmp = obj.getDate())    ? tmp : '0'+tmp;
            var H  = 10 < (tmp = obj.getHours())   ? tmp : '0'+tmp;
            var mm = 10 < (tmp = obj.getMinutes()) ? tmp : '0'+tmp;
            var s  = 10 < (tmp = obj.getSeconds()) ? tmp : '0'+tmp;            
            ret = '<dateTime>'+Y+'-'+M+'-'+D+'T'+H+':'+mm+':'+s+'</dateTime>';
          } else {
  			    ret = '<number>'+value+'</number>';
  		    }
          break;
        case 'string':
          // NOTE: & first to replace! Then repace the rest
          value = value.replace(/&/g,'&amp;').replace(/'/g,'&#039;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
          // Then repace white spaces with the corresponding entity code (hope this will avoid problems)
          value = value.replace(/\r/g,'&#x0D;').replace(/\f/g,'&#x0C;').replace(/\n/g,'&#x0A;').replace(/\t/g,'&#x09;');
          ret = '<string>'+ value + '</string>'; 
          break;
        case 'object':
					if (typeof(instanceOf) == 'function') {
						var isAnArray = instanceOf(obj, Array);
					} else {
						var isAnArray = obj instanceof Array;
					}
  		    if (typeof(obj.wddxSerialize) == 'function') {
            // Object knows how to serialize itself
            ret = obj.wddxSerialize(this);
          } 
          else if (isAnArray) {
            tmpArray[ii++] = cr + "<array length='"+obj.length+"'>";
            for (var i=0; i<obj.length; i++) {tmpArray[ii++] = this._recursiveSerialize(obj[i]);}
  			    tmpArray[ii++] =  cr + '</array>' + cr;
            ret = tmpArray.join(''); 
          } else {
      		  // Some generic object; treat it as a structure
            tmpArray[ii++] = ('string' == typeof(obj.wddxSerializationType)) ?  cr + "<struct type='"+obj.wddxSerializationType+"'>" :  cr + '<struct>';
      			for (var prop in obj) {  
      				if ('wddxSerializationType' == prop) continue;
              if ('function' == typeof(prop)) continue;                    
      				tmpArray[ii++] = cr + "<var name='"+prop+"'>" + this._recursiveSerialize(obj[prop]) + '</var>';
      			}
  			    tmpArray[ii++] = cr + '</struct>';
            ret = tmpArray.join(''); 
          }
          break;
        default :
          // do nothing
      } // end switch
      status = true;
    } while(false);
	  return ret;
  }
	
  //**************************************************************************************************************
  //***  DeSerialize
  //**************************************************************************************************************
  /**
  * Unserialize a WDDX packet 
	* @access public
  * @param  string WDDX pack (XML)
  * @return (mixed) A js-variable or js-object
  */
  this.deserialize = function(wddxPacket) { 
    var ret = null;
    var xmlParser = new Bs_XmlParser();
    var xmlRoot = xmlParser.parse(wddxPacket);
    
    // Find wrapping WDDX <data>..</data> 
    for (var i=0; i<xmlRoot.index.length; i++) {
      if ('data' == xmlRoot.index[i].name) {
        ret = this._recursiveDeserialize(xmlRoot.index[i].children[0]);
        break;
      }   
    }
    return ret;
  }
	
	
  /**
  * Called by the Deserializer function
  */
  this._recursiveDeserialize = function(wddxElement) {
    var i=0; var leng=0;
    var ret = null;
    switch (wddxElement.name) {
      case 'array':
        ret = new Array();
        leng = parseInt(wddxElement.attributes["length"]);
        for (i=0; i<leng; i++) { 
          ret[i] = this._recursiveDeserialize(wddxElement.children[i]);
        }
        break;
      case 'struct':
        leng = wddxElement.children.length;
        
        // If 'type' is set, assume it's a constructer to call. Otherwise make a standard Object 
        var constructorFound = false;
        if (typeof(wddxElement.attributes['type']) == 'string') {
          var constructorCheck = 'typeof(' + wddxElement.attributes['type']+ ')';
          if ( eval(constructorCheck) == 'function' ) constructorFound = true;
        }
      
        ret = (constructorFound) ? eval('new '+ wddxElement.attributes['type']+'()') : new Object();
        
        // Go through all elements of the struct. Note: Each struct element is wrapped by 
        // 2 XML tags a <var name="foo"> and the next deper type. That's way we use wddxElement.children[i].children[0]
        var varName = '';
        for (i=0; i<leng; i++) { 
          varName = wddxElement.children[i].attributes['name'];
          ret[varName] = this._recursiveDeserialize(wddxElement.children[i].children[0]);
        }
        break;
      case 'recordset':
        // Not supported. (yet?) 
        /*   
        var wddxElement = wddxElement;
        var colNames = wddxElement.attributes['fieldNames'].toLowerCase();
        var colNames = colNames.split(',');
        var rowCount = parseInt(wddxElement.attributes['rowCount']);
        */
        break;
      case 'binary':
        // Not supported. (yet?) 
        break;
      default:
        return this._parseSimpleType(wddxElement);
    }
    return ret;
  }


  /**
  * 
  */
  this._parseSimpleType = function(wddxElement) {
    var ret = ''; var value;
    
    switch (wddxElement.name) {
      case 'boolean':
        ret = (wddxElement.attributes['value']=='true');
        break;
      case 'string':
        if (wddxElement.children.length == 0) {
          ret = '';
          break;
        }
        
        var tmp = new Array(); 
        var ii = 0;
        for (var i=0; i<wddxElement.children.length; i++) {
          if (wddxElement.children[i].type == 'chardata') {
            tmp[ii++] =  wddxElement.children[i].value;
          } else if (wddxElement.children[i].name == 'char') {
            var code = wddxElement.children[i].attributes['code'];
            tmp[ii++] =  (1 == code.indexOf('x')) ? String.fromCharCode(code) : String.fromCharCode('0x'+ code);
          }
        }
       
        ret = tmp.join('');
        // Unrepace entities. NOTE: & *last* to replace! 
        ret = ret.replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
        // Unrepace nummeric entity codes
        ret = ret.replace(/&#(\w+);/g, this._unEntityNummeric);
        break;
      case 'number':
        value = wddxElement.children[0].value
        ret = parseFloat(value);
        break;
      case 'null':
        ret = null;
        break;
      case 'datetime':
        value = wddxElement.children[0].value
        var parts = value.match(/(\w+)-(\w+)-(\w+)T(\w+):(\w+):(\w+)(.*)/);
        if (null != parts) {
          ret = new Date(parts[1], parts[2]-1, parts[3], parts[4], parts[5], parts[6]);
        } else {
          ret = new Date();
        }
        break;
      default :
        ret = null;
    }
    return ret;
  }
  
  /**
  *  Instead of ret.replace(/&#x0D;/g,'\r').replace(/&#x0C;/g,'\f').replace(/&#x0A;/g,'\n').replace(/&#x09;/g,'\t');
  */
  this._unEntityNummeric = function(str) {
    if (0 == str.indexOf('x')) str = '0'+ str;
    if (isNaN(parseInt(str))) {
      return '';
    } else {
      return String.fromCharCode(str);
    }
  } 

	// Time zone stuff not implemented (yet?)
  /**
  var _tzOffset = (new Date()).getTimezoneOffset();
	// Invert timezone offset to convert local time to UTC time
  this._timezoneString = (_tzOffset >= 0) ? '-' : '+';
  this._timezoneString += Math.floor(Math.abs(_tzOffset) / 60) + ":" + (Math.abs(_tzOffset) % 60);
  // Calculate hours/minutes for this deserializer's timezoneOffset property 
  this._timezoneOffsetHours   = Math.round(this.timezoneOffset/60);
  this._timezoneOffsetMinutes = (this.timezoneOffset % 60);
  */
}
