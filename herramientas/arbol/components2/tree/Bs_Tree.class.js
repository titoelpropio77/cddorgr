//this is in Bs_Misc.lib.js but the file may not be included:
var ie = document.all != null;
var moz = !ie && document.getElementById != null && document.layers == null;


// The global array of objects that have been instanciated
if (!Bs_Objects)         {var Bs_Objects         = [];};
if (!Bs_XmlRequestQueue) {var Bs_XmlRequestQueue = [];};


function bsTree_loadNodeChildrenFromServer_callback() {
	for (var bsObjectID in Bs_XmlRequestQueue) {
		if (typeof(Bs_XmlRequestQueue[bsObjectID]) == 'object') {
			for (var nodeID in Bs_XmlRequestQueue[bsObjectID]) {
				if (typeof(Bs_XmlRequestQueue[bsObjectID][nodeID]) == 'object') {
					if (Bs_XmlRequestQueue[bsObjectID][nodeID].readyState == 4) { // only if req shows "loaded"
						if (Bs_XmlRequestQueue[bsObjectID][nodeID].status == 200) { // only if "OK"
							// ...processing statements go here...
							//alert(Bs_XmlRequestQueue[bsObjectID][nodeID].responseText);
							//req.responseXML 
							eval(Bs_XmlRequestQueue[bsObjectID][nodeID].responseText);
							delete Bs_XmlRequestQueue[bsObjectID][nodeID];
							//alert(bsObjectID);
							var elm = Bs_Objects[bsObjectID].getElement(nodeID);
							//debug('bsTree_loadNodeChildrenFromServer_callback for id: ' + nodeID);
							//alert('loaded kids for: ' + nodeID);
							elm._unloadedChildren = false;
							elm._checkedForChildren = true;
							elm.addChildrenByArray(childrenData, false);
							//elm.updateObjectByArray(childrenData);
							if (elm._level == 0) { //pseudo element
								//myTree.drawInto('treeDiv1');
								myTree._afterImgPreload();
							} else {
								//var status = elm.render(false, true, 0); //maybe put that into the queue...
								//if (!status) alert('failed');
								//myTree._workStack[myTree._workStack.length] = 'Bs_Objects['+myTree._id+'].executeOnElement(\'' + elm.id + '\', \'render\', Array(false,true,0));';
							}
						} else {
							alert("There was a problem retrieving the XML data:\n" + Bs_XmlRequestQueue[bsObjectID][nodeID].statusText);
						}
					}
					
				}
			}
		}
	}
}



/**
* 
* features:
*  - all the normal behavior you know from a tree.
*  - optional autocollapse; only one "subtree" may be open at a time.
*  - hide first element, in the windows explorer (file manager) 
*    that would be the 'desktop'. it's the root element. then one 
*    that has no siblings. this way you can have root elements 
*    with siblings.
*  - checkbox system (like an ms installer on windows).
*  - radio button (to use it as form field where one can select a node). (since bs4.3)
* 
* dependencies: /_bsJavascript/core/lang/Bs_Array.class.js
* 
* @author     andrej arn <andrej-at-blueshoes-dot-org>
* @copyright  blueshoes.org
* @package    javascript_components
* @subpackage tree
*/
function Bs_Tree() {
  
  /**
	* ID is initialized in the constuctor. Represents the position of 'this' in the global array 'Bs_Objects'.
	* Use: This is the only way we can reference ourself in an evaluation string 
  *  E.g. str = "var me = Bs_Objects["+this._id+"];";
  *       str += "me.foo();" 
  *       eval(str); 
  * 
  * @access private
	* @var  integer 
  */
  this._id;
  
  /**
	* Unique Object ID is initialized in the constuctor.
	* Based on this._id. Can be used in genarated JS-code as ID. Is set together 
  * from the classname + this._id (see constructor code at the bottom).
  * 
  * @access private
	* @var  string 
  */
  this._objectId;
  
  /**
	* if set to true then only one node can be open at a time. 
	* any other open sibling will close when another opens.
	* @access public
  * @var    bool autoCollapse
  */
  this.autoCollapse      = false;
	
  /**
  * how much to preload down (how many levels of tree elements).
  * 
  *   -1 = unlimited. not recommended for large trees!
  * 
  * checkboxes with checkboxSystemWalkTree:
  * if a checkbox click needs to walk down the tree (checkboxSystemWalkTree >= 2) 
  * then the tree elements data must be loaded, and tree element 
  * objects are instanced. that takes a little time. but the rendering 
  * is still omitted, which saves a lot of time. when the browser has to render 
  * all those icons for hundreds of (invisible) nodes...
	* 
	* otherwise:
  * the node objects are not instanced at all. they are once the user opens nodes 
	* into that direction. this makes it possible to use the tree even with 
	* thousands of nodes. 
  * 
  * check example 12 for this feature: 
  * http://www.blueshoes.org/_bsJavascript/components/tree/examples/example12.html
  * 
  * @access public
  * @var    int lookAhead
  * @since  bs-4.5
  */
  this.lookAhead = 2;
	
	/**
	* 
  * @since  bs-4.6
	*/
	this.loadAhead = 2;
	
	this.initAhead = 2;
	
	this.renderAhead = 2;
	
	this._workStack = new Array();
	
	
	/**
	* saves the tree state in a cookie. when the page reloads, the tree goes 
	* into the state it was before (opens nodes etc).
	* 
	* NOTE: this requires dependencies: include 
	*       core/util/Bs_Wddx.class.js
	*       core/util/Bs_XmlParser.class.js
	*       core/lang/Bs_Cookie.lib.js
	* 
	* and don't forget to call applyState() after rendering the tree! see the example 17
	* http://www.blueshoes.org/_bsJavascript/components/tree/examples/example17.html
	* 
	* @access public
	* @var    bool rememberState
	* @see    this.applyState(), this._updateStateCookie()
	* @since  bs-4.6
	*/
	this.rememberState = false;
  
	/**
	* the background color that is used on the caption if the tree element is active.
	* @access public
	* @var    string captionBgColor
	*/
  this.captionBgColor    = "DBEAF5";
	
	/**
	* a css style string to use on the link.
	* @access public
	* @var    string linkStyle
	* @see    Bs_TreeElement.linkStyle
	* @since  bs4.4
	*/
	this.linkStyle;
	
	/**
	* a css style string to use on the div.
	* @access public
	* @var    string divStyle
	* @see    Bs_TreeElement.divStyle
	* @since  bs4.5
	*/
  this.divStyle = 'font-family: Arial, Helvetica, sans-serif; font-size: 11px;';
  
	/**
	* don't set to true, it won't work (currently).
	* @access public
	* @var    bool showPseudoElement
	*/
  this.showPseudoElement = false;
  
  /**
  * specifies if the built-in checkbox system is used. 
  * even if you don't want it, check it out, it's cool. :-)
  * @access public
  * @var    bool useCheckboxSystem
  * @see    vars checkboxSystemWalkTree, checkboxSystemImgDir, checkboxSystemGuiNochange, checkboxSystemIfPartlyThenFull
  */
  this.useCheckboxSystem = false;
  
  /**
  * how the checkboxes should walk the tree if a checkbox value changes. 
  * 
  * eg you're in level 3. now if you check the box, should all levels 
  * down automatically be checked aswell? should level 2 be "partly 
  * selected" so the user sees that something below is selected, à la 
  * windows installer? 
  * 
  * 0 = no walking
  * 1 = walking up only
  * 2 = walking down only
  * 3 = walking both ways, up and down (default)
  * 4 = walking down to uncheck only, walking up for both (quite useful, an option to consider instead of 3.)
  * 
  * @access public
  * @var    int checkboxSystemWalkTree
  * @see    var useCheckboxSystem
  */
  this.checkboxSystemWalkTree = 3;
  
  /**
  * if a checkbox is checked partly, and one clicks it, what should happen?
  * we can check it completely, or uncheck it.
  * 
  * default is to check it completely, set this var to false if you want to have it unchecked.
  * 
  * @access public
  * @var    bool checkboxSystemIfPartlyThenFull
  * @see    var useCheckboxSystem (and others)
  */
  this.checkboxSystemIfPartlyThenFull = true;
  
  /**
  * if you want to use other icons than the default ones, 
  * you can specify another path here. there are different ones 
  * to chose from, and you can even create your own (path/icons) 
  * (just keep the same file names...).
  * 
  * note: the image width/height is set to 13/13 for the tree.
  *       when you use the checkbox alone, default is 20/20. if you 
  *       want something else than 13/13 in the tree, add some 
  *       functionality.
  * 
  * @access public
  * @var    string checkboxSystemImgDir
  * @see    useCheckboxSystem
  */
  this.checkboxSystemImgDir;
  
  /**
  * if set to true then the user cannot change things by clicking. 
  * changes through the api functions (for the coder, you) are still possible.
  * @access public
  * @var    bool checkboxSystemGuiNochange
  * @see    var useCheckboxSystem
  */
  this.checkboxSystemGuiNochange;
  
  /**
  * specifies if the built-in radio button is used. 
  * @access public
  * @var    bool useRadioButton
	* @see    vars this.radioButtonName, Bs_TreeElement.radioButtonSelected
	* @since  bs4.3
	* @todo   allow each TreeElement to overwrite this setting for itself. at least to deactivate.
  */
  this.useRadioButton = false;
	
	/**
	* the field name for the radio button. if not specified then something will be made up.
	* @access public
	* @var    string radioButtonName
	* @see    getRadioButtonName(), vars this.useRadioButton, Bs_TreeElement.radioButtonSelected
	* @since  bs4.3
	* @todo   allow each TreeElement to overwrite this setting for itself.
	*/
	this.radioButtonName;
	
  /**
  * the dir where the images (icons) are located. 
  * feel free to create your own dir with your own images, just name 
  * them the same way.
  */
  this.imageDir = '/_bsJavascript/components/tree/img/win98/';
	
  /**
  * the image height for the icons (folder icon, lines etc). 
  * i don't think you need to change this. only do it if the images 
  * in this.imageDir have a different height.
	* @access public
  * @var    int imgHeight
  * @since  bs4.5
  */
  this.imageHeight = 16;
  
  /**
  * if the folder icons (includes leaf icon) should be used or not.
  * default is true.
  * @access public
  * @var    bool useFolderIcon
  * @see    this.useLeaf
  */
  this.useFolderIcon = true;
  
  /**
  * uses a leaf icon instead of the default folder icon for the last 
  * element in a tree. default is true. this is neat, see 'leaf.gif'.
  * @access public
  * @var    bool useLeaf
  * @see    this.useFolderIcon
  */
  this.useLeaf = true;
  
  /**
  * if we should walk the tree up to fetch a tree elements value (setting).
  * 
  * if not then we're going to ask the tree object immediatly if the 
  * tree element we're looking at does not have the value.
  * 
  * by setting this var to false, you gain speed, but loose functionality.
  * it works like apache's htaccess files. if you're down the tree somewhere, 
  * apache walks the tree up until it finds a .htaccess file. once apache 
  * found one, it will be used. so these .htaccess config files inherit their 
  * settings down. the same is done here. and if you don't want the tree walking, 
  * only the current tree node will be checked, and then the tree object itself 
  * will be asked for a value. 
  * 
  * @access public
  * @var    bool walkTree
  * @see    TreeElement._getVar();
  */
  this.walkTree = true;
  
  /**
  * every tree element needs a unique id to be identified, just like 
  * records in a database. this can be done for you internally (default). 
  * 
  * but if you want to access tree elements from outside code you have 
  * to tell the tree somehow which element you mean. a string (the caption) 
  * is not good enough because it is not unique. so if you set this var 
  * to false, your arrays need to have an 'id' field that is truly 
  * unique over the whole structure, in the whole tree. 
  * 
  * @access public
  * @var    bool useAutoSequence
  * @see    vars this._clearingHouse, this._elementSequence
  */
  this.useAutoSequence = true;
  
	/**
	* tells if the tree elements are draggable or not.
	* @access public
	* @var    bool draggable
	* @since  bs4.4
	*/
	this.draggable = false;
	
  /**
  * clearing house array that holds a reference to each tree element.
	* 
  * the key of this array (hash starting at pos 1, not 0) is a 
  * unique int like with a rdbms table. see var _elementSequence.
	* or a string or whatever you choose as id if you don't use the 
	* useAutoSequence feature.
	* 
  * @access private
  * @var    array _clearingHouse
  * @see    this.getElement()
  */
  this._clearingHouse = new Array;
  
  /**
  * first element, pseudo element, to hold the others.
  * instance of Bs_TreeElement.
  * @access private
  * @var    object _pseudoElement
  */
  this._pseudoElement;
  
  /**
  * the currently "active" tree element, if any.
  * @access private (Bs_TreeElement is gonna use it)
  * @var    object _currentActiveElement (reference to an instance of Bs_TreeElement)
  * @see    this.getActiveElement(), this.setActiveElement(), Bs_TreeElement->setActive(), Bs_TreeElement->unsetActive()
  */
  this._currentActiveElement;
  
  /**
  * sequence used to give tree elements a unique id.
  * starts at 0 and increases itself on each element creation.
  * @access protected
  * @var    int _elementSequence
  * @see    var this.useAutoSequence
  */
  this._elementSequence = 0;
  
  /**
  * @access private
  * @var    array _errorArray
  * @see    this.getLastError()
  */
  this._errorArray;
	
  /**
  * reference to a Bs_StopWatch instance, if used.
  * helps while profiling, looking for slow code.
  * @access public
  * @var    object stopWatch
  * @since  bs-4.5
  * @see    Bs_StopWatch class
  */
  this.stopWatch;
  
	
  this.simple = false; //experimental
	
	
	/**
	* the pseudo constructor.
	* @access private
	* @return void
	*/
	this._constructor = function() {
  	// Put this instance into the global object instance list
    this._id = Bs_Objects.length;
    Bs_Objects[this._id] = this; 
    this._objectId = "Bs_Tree_" + this._id;

    // Create super root element
    //this is important to avoid all sorts of conflicts! before, this got set to 1 with the 
    // auto-sequence feature, and that was the cause for a very stupid bug.    
    var a = [];
    a['id']               = 'pseudoElement001'; 
    a['caption']          = "root";
    a['url']              = "";
    a['target']           = "";
    a['isOpen']           = true;
    this._pseudoElement = this._createTreeElement(a, 0);
	}
	
	
	/**
	* loads a skin by its name. 
	* 
	* you can do the same with manual calls to setSliderIcon() etc, but this is quick and easy.
	* 
	* available skins:
	*   
	* 
	* @access public
	* @param  string skinName
	* @return bool
	* @since  bs-4.6
	*/
	this.loadSkin = function(skinName) {
		switch (skinName) {
			case 'win2k':
			case 'win98':
				this.imageDir = './_bsJavascript/components/tree/img/win98/';
				this.imageHeight = 16;
				//this.divStyle  = 'font-size:20px; color:green;';
				//this.linkStyle = 'color:blue;';
				break;
			case 'winxp':
				this.imageDir = './_bsJavascript/components/tree/img/winXp/';
				this.imageHeight = 17;
				break;
			case 'bobby-blue':
				this.imageDir      = './_bsJavascript/components/tree/img/bobby/blue/';
				this.imageHeight   = 16;
				this.useFolderIcon = false;
				break;
				
			default:
				return false;
		}
		return true;
	}
	
	
	/**
	* @access public
	* @param  bool b (true = disabled, false = enabled, not specified or null = toggle current state.)
	* @return void
	* @since  bs-4.6
	*/
	this.setDisabled = function(b) {
		if (typeof(b) == 'undefined') b = !this._disabled;
		this._disabled = b;
		for (var elementId in this._clearingHouse) {
			if (this.useRadioButton) {
				var radioId  = this.getRadioButtonName() + '_' + elementId;
				var radioElm = document.getElementById(radioId);
				if (radioElm) {
					radioElm.readonly = this._disabled;
					radioElm.disabled = this._disabled;
				}
			} else if (typeof(this._clearingHouse[elementId]._checkboxObject) != 'undefined') {
				this._clearingHouse[elementId]._checkboxObject.setDisabled(this._disabled);
			}
		}
	}
	
	
  /**
  * inits the tree with the given data array (holding info 
  * about the tree elements).
  * @access public
  * @param  array arr
  * @return bool (true on success, false on failure)
  */
  this.initByArray = function(arr) {
		this._pseudoElement._unloadedChildren = false;
		this._pseudoElement._checkedForChildren = true;
		
		if (arr.length > 0) {
	    for (var i=0; i<arr.length; i++) {
	      var e = this._createTreeElement(arr[i], 1);
	      if (e == false) { //throw
	        return false;
	      }
	      //this.addElement(e);
	      this._pseudoElement.addChild(e);
	    }
		} else {
	    for (var key in arr) {
				if (typeof(arr[key]) == 'object') {
		      var e = this._createTreeElement(arr[key], 1);
		      if (e == false) { //throw
		        return false;
		      }
		      //this.addElement(e);
		      this._pseudoElement.addChild(e);
				}
	    }
		}
    return true;
  }
  
	
	/**
	* returns the currently active tree element as object.
  * 
  * active means the 'clicked' one. it has nothing to do with the 
  * checkbox (in case you're using this.useCheckboxSystem) or the 
  * radio (if you're using useRadioButton).
  * 
	* @access public
	* @return object (or bool false if none)
	* @see    var this._currentActiveElement, this.setActiveElement()
	* @since  bs4.3
	*/
	this.getActiveElement = function() {
		if (typeof(this._currentActiveElement) != 'undefined') return this._currentActiveElement;
		return false;
	}
	
	
	/**
	* sets the currently active tree element.
  * 
	* don't use this, use yourElement.setActive() instead.
	* i think this method should be declared private.
	* 
	* @access public
	* @param  object treeElement
	* @return void
	* @see    var this._currentActiveElement, this.getActiveElement()
	* @since  bs4.3
	*/
	this.setActiveElement = function(treeElement) {
		this._currentActiveElement = treeElement;
	}
	
	
	this.hasNodeLoader = function() {
		return (typeof(this._nodeLoaderType) != 'undefined');
	}
	
	/**
	* @access public
	* @param  string type (currently only 'iframe' supported)
	* @param  string url (the url to load)
	* @return void
	*/
	this.setNodeLoader = function(type, url) {
		this._nodeLoaderType = type;
		this._nodeLoaderUrl  = url;
		
		
		if (!this._pseudoElement.hasChildren()) {
			//this._pseudoElement._isLoaded = false;
			this._pseudoElement._unloadedChildren = true;
		}
		
		/*
		//add the pseudo "Loading..." element:
		var arr = new Array
		arr['caption']     = "Loading...";
		arr['id']          = "_loading_1";
		var e = this._createTreeElement(arr, 1);
		if (e == false) {
			//murphy
		}
		e._unloadedChildren = true;
		e._isLoaded         = false;
		this._pseudoElement.addChild(e);
		*/
	}
	
	/**
	* 
	*/
	this.loadNodeChildrenFromServer = function(nodeID) {
		if (typeof(Bs_XmlRequestQueue[this._id]) == 'undefined') {
			Bs_XmlRequestQueue[this._id] = new Array();
		} else {
			if (typeof(Bs_XmlRequestQueue[this._id][nodeID]) != 'undefined') return; //already in the queue
		}
		
		
		//debug('loadNodeChildrenFromServer for id: ' + nodeID);
		if (window.ActiveXObject) {
			Bs_XmlRequestQueue[this._id][nodeID] = new ActiveXObject("Microsoft.XMLHTTP");
		} else if (window.XMLHttpRequest) {
			Bs_XmlRequestQueue[this._id][nodeID] = new XMLHttpRequest();
		} else {
			return false;
		}
		var url = this._nodeLoaderUrl + '?bsObjectID=' + this._id + '&nodeID=' + nodeID;
		Bs_XmlRequestQueue[this._id][nodeID].onreadystatechange = bsTree_loadNodeChildrenFromServer_callback;
		Bs_XmlRequestQueue[this._id][nodeID].open("GET", url, true);
		Bs_XmlRequestQueue[this._id][nodeID].send();
	}
	
	
  /**
  * creates a tree element object with the given data.
  * 
  * special case: the arr['onClick'] may use the string __this.id__ 
  *               it will be replaced witht the actual id.
  *               example: arr['onClick'] = "doThis(__this.id__, 'foobar');"
  * 
  * @access private
  * @param  array arr
  * @param  int level
  * @return object (Bs_TreeElement)
  * @throws bool false (and sets error)
  */
  this._createTreeElement = function(arr, level) {
		if (typeof(level) == 'undefined') level = 1; //hacky.
    var e = new Bs_TreeElement();
    var status = e.initByArray(arr, this, level);
    if (!status) {
      this._addError(e.getLastError());
      return false;
    }
    
    /*
    if (this.useAutoSequence) {
      e.id      = ++this._elementSequence;
    } else {
      if (typeof(arr['id']) == 'undefined') { //throw
        this._addError('tree error: useAutoSequence is set to false, but for an array element there is no id defined.');
        return false;
      }
      e.id = arr['id'];
    }
    */
    
    this._clearingHouse[e.id] = e;
    /*
    e._tree   = this;
    e._level  = level; //yes, we access that half-private var here.
    e.caption           = arr['caption'];
    e.url               = arr['url'];
    e.target            = arr['target'];
    if (typeof(arr['onClick'])      != 'undefined') e.onClick = arr['onClick'];
    
    if (typeof(arr['icon'])       != 'undefined') e.icon     = arr['icon'];
    e.beforeIconSpan    = arr['beforeIconSpan'];
    e.beforeCaptionSpan = arr['beforeCaptionSpan'];
    e.afterCaptionSpan  = arr['afterCaptionSpan'];
    //if (arr['isOpen'])    e.isOpen    = true;
    //if (arr['isChecked']) e.isChecked = arr['isChecked'];
    if (typeof(arr['isOpen'])       != 'undefined') e.isOpen     = true;
    if (typeof(arr['isChecked'])    != 'undefined') e.isChecked  = arr['isChecked'];
    if (typeof(arr['checkboxName']) != 'undefined') {
      e.checkboxName  = arr['checkboxName'];
    } else {
      if (this.useCheckboxSystem) {
        //have to make one up.
        e.checkboxName = 'bsTreeCheckbox' + e.id;
      }
    }
    if (typeof(arr['imageDir'])   != 'undefined') e.imageDir     = arr['imageDir'];
    */    
    
    if (arr['children']) {
			e._unloadedChildren   = false;
			e._checkedForChildren = true;
			//true || 
			if ((this.useCheckboxSystem && (this.checkboxSystemWalkTree >= 2)) || e.isOpen || ((this.lookAhead +2) > level) || (this.lookAhead == -1) || ((typeof(e.parent) == 'object') && (e.parent.isOpen))) {
			
				if (arr['children'].length > 0) {
		      for (var i=0, n=arr['children'].length; i<n; i++) {
	  	      var newE = this._createTreeElement(arr['children'][i], level +1);
	    	    if (!newE) return false; //throw
	      	  e.addChild(newE);
		      }
				} else {
		      for (key in arr['children']) {
	  	      var newE = this._createTreeElement(arr['children'][key], level +1);
	    	    if (!newE) return false; //throw
	      	  e.addChild(newE);
		      }
				}
			} else {
     	  e._undoneChildren = arr['children'];
			}
    }
    return e;
  }
  
	
  /**
  * returns a reference to the tree-element with the given id.
  * special case: elementId 0 = first element aka pseudo element.
  * 
  * @access public
  * @param  mixed elementId
  * @return object (byref)
  * @throws bool false
	* @see    getElementByCaptionPath()
  */
  this.getElement = function(elementId) {
    if (elementId == 0) return this._pseudoElement; //special case
    if (typeof(this._clearingHouse[elementId]) == 'object') {
      return this._clearingHouse[elementId];
    } else {
      return false;
    }
  }
	
	
	/**
	* returns the tree element for the given 'caption path'. 
	* 
	* example usage:
	*   var myArr = new Array('world', 'europe', 'france');
	*   var myElm = yourTree.getElementByCaptionPath(myArr);
	* 
	* note that it does not work if you have two tree elements with the same caption 
	* (and the one you want does not come first).
	* 
	* @access public
	* @param  array data
	* @return object (instance of Bs_TreeElement)
	* @throws bool false (not found)
	*/
	this.getElementByCaptionPath = function(data) {
		var elm = this._pseudoElement;
	  for (var i=0, n=data.length; i<n; i++) {
			var newElm = null;
			for (var j=0, jn=elm._children.length; j<jn; j++) {
				if (elm._children[j].caption == data[i]) {
					newElm = elm._children[j];
					elm = newElm;
					
					if (typeof(elm._undoneChildren) == 'object') {
			      for (var k=0, kn=elm._undoneChildren.length; k<kn; k++) {
					    var newE = this._createTreeElement(elm._undoneChildren[k], elm._level +1);
			 			 	elm.addChild(newE);
						}
						elm._undoneChildren = false;
					}
					
					break;
				}
			}
			if (newElm == null) return false;
		}
		return newElm;
	}
	
	
	/**
	* removes the tree-element specified and all it's children.
	* 
	* internally we have to do this:
	* 1) in the parent of the given elementId, remove it from the _children array.
	* 2) remove it and all its children in the _clearingHouse.
	* 3) if the parent has already been rendered, re-render it.
	* 
	* @access public
	* @return bool (true on success, false if it does not exist.)
	* @since  bs4.4
	*/
	this.removeElement = function(elementId) {
		if (typeof(this._clearingHouse[elementId]) == 'undefined') return false;
    var elm = this._clearingHouse[elementId];
		
		//removing in parents children array:
		if ((typeof(elm.parent) == 'object') && (typeof(elm.parent._children) == 'object')) {
			for (var i=0, n=elm.parent._children.length; i<n; i++) {
				if (elm.parent._children[i].id == elementId) {
					//elm.parent._children[i] = null;
					elm.parent._children.deleteItem(i);
					break;
				}
			}
		}
		
		//remove in clearinghouse:
		//this._clearingHouse[elementId] = null;
		this._clearingHouse.deleteItemHash(elementId);
		//todo: remove all children too. recursively.
		for (var i=0, n=elm._children.length; i<n; i++) {
			this._clearingHouse.deleteItemHash(elm._children[i].id);
		}
		
		if ((typeof(elm.parent) == 'object') && (elm.parent._isOutrendered)) {
			elm.parent.render(true, true);
		}
		
		return true;
	}
  
  
	/**
	* opens all tree nodes.
	* another function name would have been openAll.
	* @access public
	* @return void
	* @see    collapseAll()
	* @since  bs-4.6
	*/
	this.expandAll = function() {
		this._pseudoElement.expandAll();
	}
	
	
	/**
	* closes all tree nodes.
	* another function name would have been closeAll.
	* @access public
	* @return void
	* @see    expandAll()
	* @since  bs-4.6
	*/
	this.collapseAll = function() {
		this._pseudoElement.collapseAll(false);
	}
	
	
  /**
  * outputs the tree as rendered html to the browser (document.writeln).
  * @access public
  * @return string
  * @see    toHtml(), drawInto()
  */
  this.draw = function() {
		if (this.simple) {
	    var content = this._pseudoElement.renderSimple();
		} else {
	    var content = this._pseudoElement.render();
		}
    document.writeln(content[0]);
    eval(content[1]);
  }
  
  
  /**
  * returns the tree as rendered html string
  * @access public
  * @return vector (element 0=html, 1=js (to execute))
  * @see    draw(), drawInto()
  */
  this.toHtml = function() {
		if (this.hasNodeLoader()) {
			alert("Webmaster: Sorry, the toHtml() method cannot be used together with a node loader. Use drawInto() or draw().");
			return;
		}
		if (this.simple) {
	    return this._pseudoElement.renderSimple();
		} else {
	    return this._pseudoElement.render();
		}
  }
  
  
  /** 
  * calls an api function of the tree element specified (by id).
  * 
  * possible methods are (currently there are about 15):
  *   open
  *   close
  *   hasChildren
  *   ... (check out the class api functions yourself
  * 
  * the first 2 params are must have. you can give a 3rd param. if you 
  * do, that array will be cut into it's elements, and the elements will 
  * be used as params for the function you want to call. note: a max of 
  * 4 params is currently defined. if you want more, recode.
  * 
  * @access public
  * @param  mixed id (string, number, whatever it is)
  * @param  string func (function name, without brackets!)
  * @param  array params (vector, see above)
  * @return mixed (the return value of the called function, void if that was not possible.)
	* @see    getElement()
  */
  this.executeOnElement = function(id, func, params) {
    if (this._clearingHouse[id]) {
      if (this._clearingHouse[id][func]) {
        //todo: check that func does not start with an underscore _ because 
        //      then it would be a private method.
        if (params) {
          //well this is not nice, but works. if you want an unlimited number of params 
          //you need eval().
          //for (var i=0; i<params.length; i++) {
          //}
          switch (params.length) {
            case 1:
              return this._clearingHouse[id][func](params[0]);
              break;
            case 2:
               return this._clearingHouse[id][func](params[0], params[1]);
              break;
            case 3:
               return this._clearingHouse[id][func](params[0], params[1], params[2]);
              break;
            case 4:
               return this._clearingHouse[id][func](params[0], params[1], params[2], params[3]);
              break;
          }
        } else {
          return this._clearingHouse[id][func]();
        }
      }
    }
    return;
  }
  
  
  /**
  * returns the js code that's needed to build this tree. 
  * @access public
  * @return string
  * @see    Bs_TreeElement.getJavascriptCode()
  */
  this.getJavascriptCode = function() {
    return this._pseudoElement.getJavascriptCode('a', true);
  }
  
	/**
	* opens the element if closed, and vice versa.
	* @access public
	* @param  mixed id (element id)
	* @return void
	*/
  this.elementToggleOpenClose = function(id) {
    this._clearingHouse[id].toggleOpenClose();
  }
	
	
	/**
	* same as elementOpen but also walks up the tree (to the root) opening all parents.
	* calls itself recursively.
	* 
	* @access public
	* @param  mixed id
	* @see    elementOpen(), elementClose()
	* @since  bs4.3
	* @return bool (true on success, false on failure)
	*/
	this.elementOpenWalkUp = function(id) {
		if (typeof(this._clearingHouse[id]) != 'undefined') {
			var elm = this._clearingHouse[id];
			elm.open(true);
			if (typeof(elm.parent) != 'undefined') this.elementOpenWalkUp(elm.parent.id);
		} else {
			return false;
		}
		return true;
	}
	
	
	/**
	* same as elementClose but also walks up the tree (to the root) closing all parents.
	* calls itself recursively.
	* 
	* @access public
	* @param  mixed id
	* @see    elementOpen(), elementClose(), elementOpenWalkUp()
	* @since  bs4.4
	* @return bool (true on success, false on failure)
	*/
	this.elementCloseWalkUp = function(id) {
		if (typeof(this._clearingHouse[id]) != 'undefined') {
			var elm = this._clearingHouse[id];
			elm.close(true);
			if (typeof(elm.parent) != 'undefined') this.elementCloseWalkUp(elm.parent.id);
		} else {
			return false;
		}
		return true;
	}
	
	
	/**
	* same as elementClose but also walks down the tree (to the leaf) closing all children.
	* calls itself recursively.
	* 
	* @access public
	* @param  mixed id (don't specify to close all tree elements)
	* @see    elementClose(), elementCloseWalkUp()
	* @since  bs4.4
	* @return bool (true on success, false on failure)
	*/
	this.elementCloseWalkDown = function(id) {
		if (typeof(id) == 'undefined') {
			var elm = this._pseudoElement;
		} else if (typeof(this._clearingHouse[id]) != 'undefined') {
			var elm = this._clearingHouse[id];
			elm.close(true);
		} else {
			return false;
		}
		if (typeof(elm._children) != 'undefined') {
			for (var i=0; i<elm._children.length; i++) {
				this.elementCloseWalkDown(elm._children[i].id);
			}
		}
		return true;
	}
	
	/**
	* opens up the tree-element with the given id.
	* 
	* note that if one of the parents is not open, you do not see the change 
	* immediatly. parents have to be opened manually, this method does not do 
	* it for you. or use elementOpenWalkUp().
	* 
	* only works once the tree has been rendered to the browser.
	* 
	* @access public
	* @param  mixed id
	* @return void
	* @see    elementOpenWalkUp(), elementClose()
	*/
  this.elementOpen = function(id) {
		if (typeof(this._clearingHouse[id]) != 'undefined') {
	    this._clearingHouse[id].open();
		}
  }
	
	/**
	* closes the tree-element with the given id.
	* 
	* note that the children won't be closed. they disappear when closing their parent, 
	* sure, but if you re-open this element they will be back as they were. open or closed.
	* 
	* only works once the tree has been rendered to the browser.
	* 
	* @access public
	* @param  mixed id
	* @return void
	* @see    elementOpen(), elementOpenWalkUp()
	*/
  this.elementClose = function(id) {
    this._clearingHouse[id].close();
  }
  
	
	/**
	* opens up the tree nodes to the element specified.
	* you need to know the path to your element, for examples the captions.
	* 
	* example: you have a tree node with the captions "world", "europe" , "france". 
	*          now you don't know the id's of the tree elements, but you want to open 
	*          up the tree to (and including) the "france" element. so you do:
	*          var myArr = new Array('world', 'europe', 'france');
	*          yourTree.openPath(myArr, 'caption');
	* 
	* note: if you know the id of that element, use elementOpenWalkUp().
	*       but if you don't, this method may help you.
	* 
	* note that it currently does not work if you have two tree elements with the same caption 
	* (and the one you want does not come first).
	* 
	* @access public
	* @param  array data
	* @param  string valueType (currently only 'caption' is supported', and it's the default anyway.)
	* @return bool (true on success, false if not found.)
	*/
	this.openPath = function(data, valueType) {
		//if (typeof(valueType) == 'undefined') valueType = 'caption';
		
		var elm = this.getElementByCaptionPath(data);
		if (elm == false) return false;
		
		this.elementOpenWalkUp(elm.id);
		return true;
	}
	
	
	/**
	* @access public
	* @param  mixed id
	* @param  bool value
	* @return void
	* @see    Bs_TreeElement->checkboxEvent() for details.
	*/
  this.elementCheckboxEvent = function(id, value) {
  // alert('asdas');
    this._clearingHouse[id].checkboxEvent(value);
  }
  
	
	/**
	* apply the cookie-stored state. note that the rememberState feature needs to be activated.
	* @access public
	* @return bool
	* @see    var rememberState
	* @since  bs-4.6
	*/
	this.applyState = function() {
		if (typeof(getCookie) == 'undefined') {
			alert('Webmaster: please make sure core/lang/Bs_Cookie.lib.js is included for the rememberState/applyState feature.');
			return false;
		}
		var name = this._objectId;
		var data = getCookie(name);
		//dump(data);
		for (treeElementId in data) {
			var treeElm = this.getElement(treeElementId);
			for (action in data[treeElementId]) {
				if (data[treeElementId][action]) {
					treeElm.open();
				} else {				   
					treeElm.close();
				}
			}
		}
		
		return true;
	}
	
	
	/**
	* updates the 
	* @access private
	* @param  mixed treeElementId
	* @param  string action (one of 'open', ...)
	* @param  bool value
	* @return bool
	* @see    this.rememberState
	* @since  bs-4.6
	*/
	this._updateStateCookie = function(treeElementId, action, value) {
		if (typeof(setCookie) == 'undefined') {
			alert('Webmaster: please make sure core/lang/Bs_Cookie.lib.js is included for the rememberState/applyState feature.');
			return false;
		}
		//full syntax: setCookie(name, data, expires, path, domain, secure);
		var name = this._objectId;
		var data = getCookie(name);
		if ((typeof(data) != 'object') || (data == null)) data = new Object(); //Array();
		if ((typeof(data[treeElementId]) != 'object') || (typeof(data[treeElementId]) == null)) {
			data[treeElementId] = new Object(); //Array();
		}
		if (typeof(data[treeElementId][action]) == 'undefined') {
			data[treeElementId][action] = value;
		} else {
			if (data[treeElementId][action] != value) {
				delete data[treeElementId][action]; //remove it
				delete data[treeElementId];
			} //otherwise just keep it
		}
		
		/*
		var timeIn30  = new Date();
		timeIn30.setMinutes(timeIn30.getMinutes() +30);
		setCookie(name, data, timeIn30);
		*/
		
		//deleteCookie(name);
		setCookie(name, data);
		return true;
	}
	
	
	/**
	* @access public
	* @return string
	* @since  bs-4.6
	* @see    vars useRadioButton, radioButtonName
	*/
	this.getRadioButtonName = function () {
		if (typeof(this.radioButtonName) != 'undefined') {
			return this.radioButtonName;
		} else {
			return 'bsTreeRad_' + this._objectId;
		}
	}
	
	
	/**
	* debug method to see the tree content in a js alert box.
  * calls itself recursively to loop the children.
	* @access public
  * @param  int elm (the element, not specified = use first element (pseudo-element))
  * @param  string indent (used internally only on recursive calls)
	* @return void
	* @since  bs4.4
	*/
	this.debugDumpTree = function(elm, indent) {
		if (typeof(elm) == 'undefined') {
			elm    = this._pseudoElement;
			indent = '';
			var firstCall = true;
		}
		var ret = '';
		if (typeof(elm._children) == 'object') {
			for (var i=0; i<elm._children.length; i++) {
				ret += indent + i + ': ' + elm._children[i].id + ': ' + elm._children[i].caption + "\n";
				ret += this.debugDumpTree(elm._children[i], indent + '  ');
			}
		}
		if (firstCall) {
			alert(ret);
		} else {
			return ret;
		}
	}
	
	
  /**
  * adds an error string to the error stack.
  * @access private
  * @param  string str
  * @return void
  * @see this.getLastError()
  */
  this._addError = function(str) {
    if (typeof(this._errorArray) == 'undefined') {
      this._errorArray = new Array(str);
    } else {
      this._errorArray[this._errorArray.length] = str;
    }
  }

  /**
  * returns the last occured error.
  * @access public
  * @return mixed (string if there was an error, bool false otherwise.)
  * @see    var this._errorArray
  */
  this.getLastError = function() {
    if (typeof(this._errorArray) != 'undefined') {
      if (this._errorArray.length > 0) {
        return this._errorArray[this._errorArray.length -1];
      }
    }
    return false;
  }
	
	
  
	
  /**
  * outputs the tree as rendered html into the element specified.
  * @access public
  * @param  string id (element id)
  * @return void
  * @see    draw(), toHtml()
  */
  this.old_drawInto = function(id) {
		if (this.simple) {
	    var content = this._pseudoElement.renderSimple();
		} else {
	    var content = this._pseudoElement.render();
		}
    var e       = document.getElementById(id);
    if (e) {
      e.innerHTML = content[0];
      if ('' != content[1]) eval(content[1]);
    }
  }
  
// ================================================================================================
//  NEW this.drawInto
//  Following code is in beta stage. It's a fist *working* solution to overcome an ID bug when working
//  with innerHTML that has many repeating images. 
//  The Bug: IE loads every!! imges referenced in the innerHTML even if it has it already. 
//  The solution: Similar  to an image preload. Init the innerHTML with each image you intend to use
//     e.g. <img src="' + this.imageDir + 'line3.gif" border="0" style="display:none;">
//  Then wait a little (300 ms or so) and overwrite the innerHTML with your real content.
//  ID will then realize that it can use the images from the cache
//  Known issus:
//       The images cached are static taken from the tree dir. Any other images are not taken 
//       in account (jet).
// ================================================================================================
	
	/**
	* @access private
	* @return void
	*/
  this._imgPreload = function() {
    var id = this.globalId;
    var e  = document.getElementById(id);
    var ii = 0;
    var outTemp = new Array();
    outTemp[ii++] = '<img src="' + this.imageDir + 'line1.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'line2.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'line3.gif" border="0" style="display:none;">';

    outTemp[ii++] = '<img src="' + this.imageDir + 'minus1.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'minus2.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'minus3.gif" border="0" style="display:none;">';

    outTemp[ii++] = '<img src="' + this.imageDir + 'plus1.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'plus2.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'plus3.gif" border="0" style="display:none;">';

    outTemp[ii++] = '<img src="' + this.imageDir + 'line3.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'empty.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'leaf.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'folderClosed.gif" border="0" style="display:none;">';
    outTemp[ii++] = '<img src="' + this.imageDir + 'folderOpen.gif" border="0" style="display:none;">';

    if (e) e.innerHTML = outTemp.join('');
  }
  
	
	/**
	* @access private
	* @return void
	*/
  this._afterImgPreload = function() {
    var id = this.globalId;
		
		//special case:
    if (this.hasNodeLoader() && !this._pseudoElement._checkedForChildren && !this.showPseudoElement) { //
			this.loadNodeChildrenFromServer(this._pseudoElement.id);
			return;
		}
		
		if (this.simple) {
	    var content = this._pseudoElement.renderSimple();
		} else {
	    var content = this._pseudoElement.render();
		}
    var e       = document.getElementById(id);
    if (e) {
      e.innerHTML = content[0];
      if ('' != content[1]) {
				eval(content[1]);
			}
			this._afterRender();
    }
  }  
  
	this._afterRender = function() {
		if (typeof(this._workStack) == 'object') {
			for (var i=0; i<this._workStack.length; i++) {
				//alert(this._workStack[i]);
				eval(this._workStack[i]);
				delete this._workStack[i];
			}
		}
	}
	
  // This is the new drawInto() function.
  this.drawInto = function(id) {
    this.globalId = id;
    setTimeout('Bs_Objects['+this._id+']._imgPreload()', 0);
    setTimeout('Bs_Objects['+this._id+']._afterImgPreload()', 5); //500
  }
	
	
	this._constructor(); //call the constructor. needs to be at the end.	
  	
}