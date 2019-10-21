/**
* got this from http://www.webreference.com/js/column8/functions.html
* works nice. --andrej
* 
* added support to set/read objects, not only primitive types (strings).
* this requires the wddx and xmlparser classes:
*   core/util/Bs_Wddx.class.js
*   core/util/Bs_XmlParser.class.js
* 
* @package    javascript_core
* @subpackage lang
*/



// name - name of the cookie
// value - value of the cookie
// [expires] - expiration date of the cookie (defaults to end of current session)
//    example: 
//			var timeIn30  = new Date();
//			timeIn30.setMinutes(timeIn30.getMinutes() +30);
//			setCookie('varName', 'varValue, timeIn30, null, null, null);
// [path] - path for which the cookie is valid (defaults to path of calling document)
// [domain] - domain for which the cookie is valid (defaults to domain of calling document)
// [secure] - Boolean value indicating if the cookie transmission requires a secure transmission
// * an argument defaults when it is assigned null as a placeholder
// * a null placeholder is not required for trailing omitted arguments
function setCookie(name, value, expires, path, domain, secure) {
	if (typeof(value) == 'object') {
		if (typeof(Bs_Wddx) == 'undefined') {
			alert('Webmaster: To set an object as cookie the wddx class is required: core/util/Bs_Wddx.class.js');
			return false;
		}
		var wddx = new Bs_Wddx();                             // Create the WDDX-handler instance
		//dump(value);
		var value = wddx.serialize(value);                  // Serialize the JS-variable
		//dump(value);
	}
	
  var curCookie = name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
  document.cookie = curCookie;
}

// name - name of the desired cookie
// * return string containing value of specified cookie or null if cookie does not exist. false on error.
function getCookie(name) {
  var dc = document.cookie;
  var prefix = name + "=";
  var begin = dc.indexOf("; " + prefix);
  if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0) return null;
  } else
    begin += 2;
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
    end = dc.length;
	
	var value = dc.substring(begin + prefix.length, end);
	//alert(value);
	value = unescape(value);
	//alert(value);
	
	if (value.substr(0, 11) == '<wddxPacket') {
		if ((typeof(Bs_Wddx) == 'undefined') || (typeof(Bs_XmlParser) == 'undefined')) {
			alert('Webmaster: To read an object from cookie the wddx and xmlparser classes are required: core/util/Bs_Wddx.class.js and Bs_XmlParser.class.js');
			return false;
		}
		var wddx = new Bs_Wddx();              // Create the WDDX-handler instance
		//dump(value);
		value = wddx.deserialize(value);     // Deserialize the wddx-XML data back to the JS-variable
		//dump(value);
	}
	//value = unescape(value);
  return value;
}

// name - name of the cookie
// [path] - path of the cookie (must be same as path used to create cookie)
// [domain] - domain of the cookie (must be same as domain used to create cookie)
// * path and domain default if assigned null or omitted if no explicit argument proceeds
function deleteCookie(name, path, domain) {
  if (getCookie(name)) {
    document.cookie = name + "=" + 
    ((path) ? "; path=" + path : "") +
    ((domain) ? "; domain=" + domain : "") +
    "; expires=Thu, 01-Jan-70 00:00:01 GMT";
  }
}

// date - any instance of the Date object
// * hand all instances of the Date object to this function for "repairs"
function fixDate(date) {
  var base = new Date(0);
  var skew = base.getTime();
  if (skew > 0)
    date.setTime(date.getTime() - skew);
}
