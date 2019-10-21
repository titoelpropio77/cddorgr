/**
* Bs_TreeElement class. 
* 
* an instance of this class represents a folder/file/whatever.
* 
* note: sometimes we use long and non-compliant id's for tags. 
*       example:
*       out += '<span id="this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_children">';
*       netscape does not like long id's and ns4 did not like underscores. a fix would be to 
*       md5() the strings to get a shorter and still unique id.
* 
* 
* "tree element" and "tree node" are synonyms.
* 
* 
* @package    javascript_components
* @subpackage tree
* @author     andrej arn <andrej-at-blueshoes-dot-org>
* @copyright  blueshoes.org
*/
function Bs_TreeElement() {
  
  /**
  * the unique identifier of this element. 
  * 
  * for the built in auto-sequence (Bs_Tree.useAutoSequence) this is 
  * an int >0, but if you use your own handling it can be a string aswell.
  * 
  * @access public (read only)
  * @var    mixed id
  */
  this.id;
  
  /**
  * reference to the parent tree element object.
  * @var object parent
  */
  this.parent;
  
  /**
  * reference to the tree object.
  * @access public
  * @var object _tree
  */
  this._tree;
  
	/**
	* the visible text.
	* use setCaption() to modify it if it's already outrendered.
	* @access public
	* @var    string caption
	* @see    setCaption()
	*/
  this.caption;
	
	/**
	* @access public
	* @var    string url
	*/
  this.url;
	
	/**
	* @access public
	* @var    string target
	*/
  this.target;
	
	/**
	* @access public
	* @var    string tooltip
	*/
  this.tooltip;
	
	/**
  * see Bs_Tree->linkStyle, it's the same, you can simply overwrite it here.
  * then it will be used for this and everything beyond that (also see 
  * Bs_Tree->walkTree!).
  * @access public
	*/
	this.linkStyle;
  
	/**
	* a title for the link, it's the <a title=""> thing.
	* @access public
	* @var    string linkTitle
  * @since  bs-4.6
	*/
	this.linkTitle;
  
	/**
	* a text to set on the status bar when mouse-over'ing the link.
	* @access public
	* @var    string linkStatus
  * @since  bs-4.6
	*/
	this.linkStatus;
	
	/**
  * see Bs_Tree->divStyle, it's the same, you can simply overwrite it here.
  * then it will be used for this and everything beyond that (also see 
  * Bs_Tree->walkTree!).
  * @access public
	*/
  this.divStyle;
  
  /**
  * javascript code that fires on the onClick event.
  * can go together with an url, they don't byte each other.
  * 
  * special case: may use the string __this.id__ 
  *               it will be replaced with the actual id.
  *               example: yourElement.onClick = "doThis(__this.id__, 'foobar');"
  * 
  * @access public
  * @param  string onClick
  */
  this.onClick;
  
	/**
	* stores if this tree node is open or not.
	* @access ?
	* @var    bool isOpen
	*/
  this.isOpen = false;
  
  /**
  * if set to false then the element will be set to display:none.
  * it's still there (loaded) so you can use its methods etc. 
  * @access public
  * @var    bool visible
  */
  this.visible = true;
  
  /**
  * used if the checkbox feature is used. 
  * 
  *   0 = not checked
  *   1 = checked gray (part of the sub-elements are, part are not)
  *   2 = checked (this or everything below is checked)
  * 
  * now i think it's a dirty name. isChecked sounds like a function, 
  * not a var. but it's public, so i don't feel like renaming it 
  * and changing it everywhere. and checked sounds like boolean, not 
  * integer with 3 possibilities. (historic reasons when there was 
  * just true/false ...)
  * 
  * @access public
  * @var    int isChecked (default is 0)
  * @see    setCheckboxValue(), checkboxEvent(), Bs_Tree.useCheckboxSystem
  */
  this.isChecked = 0;
  
  /**
  * the name of the checkbox. 
  * gets set in this.initByArray().
	* 
	* if not defined then one will be made up like this:
	* 'bsTreeChk_' + tree._objectId + '_' + this.id
	* 
  * @access public (read-only, please)
  * @var    string checkboxName
  */
  this.checkboxName;
	
	/**
	* if the radio button of this element should be selected by default. only one can be!
	* @access public
	* @var    bool radioButtonSelected
	* @see    Bs_Tree.
	* @since  bs4.3
	*/
	this.radioButtonSelected;
  
  /**
  * instance of bs_Checkbox (if used).
  * @access private
  * @var    object _checkboxObject
  */
  this._checkboxObject;
  
  /**
  * the level this element is in. think about the identing.
  * @access ?
  * @var int _level (default is 0, but that really should be overwritten.)
  */
  this._level = 0;
  
  /**
  * array (vector) holding children elements.
  * @access private
  * @var    array _children
	* @see    var this._undoneChildren
  */
  this._children = new Array;
  
	/**
	* settings for undone children. 
	* these are children for which we have the data structure, but we 
	* haven't imported them into the tree yet.
	* @access private
	* @var    array _undoneChildren
	* @see    var this._children
	*/
	this._undoneChildren;
	
	/**
	* if there are children which we haven't loaded from the server yet. 
	* @access private
	* @var    bool _unloadedChildren
	* @see    this._undoneChildren
	*/
	this._unloadedChildren;
	this._checkedForChildren = false;
	
	/**
	* @access public
	* @return int (0=no, 1=maybe, 2=yes)
	* @since  bs-4.6
	*/
	this.hasUnloadedChildren = function() {
		if (!this._tree.hasNodeLoader()) return 0;
		//if (this._children.length > 0) return false;
		if (typeof(this._unloadedChildren) == 'boolean') return (this._unloadedChildren) ? 2 : 0;
		return 1;
	}
	/**
	* @access public
	* @return bool
	* @since  bs-4.6
	*/
	this.hasUninitializedChildren = function() {
		if (typeof(this._undoneChildren) == 'undefined') return false;
		return (this._undoneChildren.length > 0);
	}
	
  /**
  * see Bs_Tree->imageDir, it's the same, you can simply overwrite it here.
  * then it will be used for this and everything beyond that (also see 
  * Bs_Tree->walkTree!).
  * @access public
  */
  this.imageDir;
  
  /**
  * see Bs_Tree->imageHeight, it's the same, you can simply overwrite it here.
  * then it will be used for this and everything beyond that (also see 
  * Bs_Tree->walkTree!).
  * @access public
  */
  this.imageHeight;
  
  /**
  * if not set then the default icons will be used.
	* this means that both icons folderOpen.gif and folderClosed.gif
	* have to exist in the image dir (see this.imageDir).
	* 
  * set to bool false if you don't want an icon.
	* 
  * set to a string for an image (in the same img dir as the others, 
  * see imageDir). use the file ending (.gif or so) as well.
	* if your icon is in another dir, you can give the path also. but 
	* then it has to start with a slash / or http:// or https://.
	* 
	* examples: myIcon.gif
	*           myIcon.png
	*           /some/dir/myIcon.jpg
	*           http://www.blueshoes.org/_bsImages/buttons/globes/1.gif
	* 
  * @access public
  * @var    mixed icon
  */
  this.icon;
  
  /**
  * html code that's put inside a span tag and displayed before the "folder" icon.
  * @access public
  * @var string beforeIconSpan (html code)
  * @see beforeCaptionSpan, afterCaptionSpan
  */
  this.beforeIconSpan;
  
  /**
  * html code that's put inside a span tag and displayed before the caption text.
  * could also be named afterIconSpan. it's between the icon and the caption.
  * @access public
  * @var string beforeCaptionSpan (html code)
  * @see beforeIconSpan, afterCaptionSpan
  */
  this.beforeCaptionSpan;
  
  /**
  * html code that's put inside a span tag and displayed after the caption text.
  * @access public
  * @var string afterCaptionSpan (html code)
  * @see beforeIconSpan, beforeCaptionSpan
  */
  this.afterCaptionSpan;
  
	/**
	* can be used to stick in any data.
	* 
	* this is useful if you want to init your tree with a data array, and on the 
	* click of a node something should happen. but then the node misses some 
	* related information. 
	* 
	* hrm, let's give you an example:
	* your tree looks like a directory tree, with dirs and files. when the user 
	* clicks on a file, you want to show in a div the size in kb of this file.
	* but the tree does not know this. the tree just fires a registered function, 
	* and tells which node was clicked. but you don't have the nodeId-to-file 
	* relation. so what? well, much easier if you can stick in your data into 
	* the tree element directly, and later read that data. hrm, got the idea?
	* 
	* @access public
	* @var    mixed dataContainer
	* @since  bs4.3
	*/
	this.dataContainer;
  
  /**
  * array holding all the information about attached events. 
  * the structure can be like these:
  * 
  * 1) attach a function directly
  *    syntax:  _attachedEvents['eventName'] = yourFunctionName;
  * 2) attach some javascript code
  *    syntax:  _attachedEvents['eventName'] = "yourCode();";
  *    example: _attachedEvents['eventName'] = "alert('hi'); callSomething('foo');";
  *    just keep in mind that you cannot use vars in that code, because when it 
  *    gets executed that will be another scope (unless the vars are global...)
  * 3) attach multiple things for the same event
  *    syntax:  _attachedEvents['eventName']    = new Array;
  *             _attachedEvents['eventName'][0] = yourFunctionName;
  *             _attachedEvents['eventName'][1] = "yourCode();";
  * 
  * @access private
  * @var    array _attachedEvents (hash, see above)
  * @see    this.attachEvent(), this.fireEvent();
  */
  this._attachedEvents;
  
  
  /**
  * once this element has been rendered and outputted, this var 
  * is set to true. because then we can go and view/hide it easily 
  * without the need of re-rendering. this saves lots of cpu.
  * 
  * @access private
  * @var    bool _isOutrendered
  */
  this._isOutrendered = false;
  
	/**
	* tells if this node is loaded from the server 
	* (we have the right data). if not then it is a dummy "Loading..." node.
	* @access private
	* @var    bool _isLoaded
	*/
	this._isLoaded = true;
	
  /**
  * @access private
  * @var    array _errorArray
  * @see    this.getLastError()
  */
  this._errorArray;
  
	
  /**
  * returns a reference to this object.
  * @access public
  * @return object
  */
  this.getThis = function() {
    return this;
  }
	
	this.getDhtmlCompatibleId = function() {
		return this.id;
		if (typeof(this._id_md5) != 'undefined') return this._id_md5;
		this._id_md5 = MD5(this.id);
		return this._id_md5;
	}
  
  
  /**
  * adds a treeElement child to this tree element (at the bottom).
  * @access public
  * @param  object treeElement
	* @param  bool renderChild (default is TRUE)
  * @return void
	* @see    addChildByArray();
  */
  this.addChild = function(treeElement, renderChild) {
		if (typeof(renderChild) == 'undefined') renderChild = true; 
		
    treeElement.parent = this;
		if (typeof(this._children) != 'object') this._children = new Array;
    if (this._children.push) {
      this._children.push(treeElement);
    } else {
      this._children[this._children.length] = treeElement;
    }
		
		treeElement._level = this._level +1;
		this._updateLevelAndParent(treeElement);
		
    this._tree._clearingHouse[treeElement.id] = treeElement; //maybe done twice now, but who cares. need to make sure it's done here.
		
		if (this._isOutrendered && renderChild) {
			//have to re-render this node to make the change visible.
			//this.render(true, true);
			this.render(false, true);
		}
  }
  
	
	/**
	* adds a tree element based on the data given.
	* @access public
	* @param  array elementData
	* @param  bool renderChild (is passed on to this.addChild().)
	* @return treeElement (the new instance of Bs_TreeElement)
	* @see    addChild();
	* @since  bs4.5
	*/
	this.addChildByArray = function(elementData, renderChild) {
		var treeElement = this._tree._createTreeElement(elementData, this._level+1);
		this.addChild(treeElement, renderChild);
		return treeElement;
	}
	
	/**
	* @access public
	* @param  array childrenArray
	* @param  bool renderChildren
	* @return bool 
	*/
	this.addChildrenByArray = function(childrenArray, renderChildren) {
    for (var i=0; i<childrenArray.length; i++) {
      var e = this._tree._createTreeElement(childrenArray[i], this._level+1);
      if (e == false) { //throw
        return false;
      }
      //this.addElement(e);
      this.addChild(e, renderChildren);
    }
		return true;
	}
	
	
	/**
	* tells if the element specified is a child of this one.
	* if bubble is set to true, then not only a direct child will return true.
	* @access public
	* @param  mixed elementId (int or string)
	* @param  bool  bubble
	* @return bool
	* @since  bs4.4
	*/
	this.isChild = function(elementId, bubble) {
		for (var i=0, n=this._children.length; i<n; i++) {
			if (this._children[i].id == elementId) return true;
			if (bubble) {
				if (this._children[i].isChild(elementId, true)) return true;
			}
		}
		return false;
	}
	
	
	/**
	* can be used to modify the caption of the tree is already outrendered.
	* @access public
	* @param  string caption
	* @return void
	* @see    var caption
	*/
	this.setCaption = function(caption) {
		this.caption = caption;
		if (this._isOutrendered) {
			var span = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption2');
			if (span) span.innerHTML = caption;
		}
	}
	
	
  /**
  * renders this tree element with its sub-elements.
  * 
  * return value:
  *    element 0 is the html output
  *    element 1 is js code that needs to be evaluated AFTER the 
  *              html has been outputted (because it depends on elements 
  *              in the html code).
  *              if that element is an empty string, don't execute it.
  * 
  * @access public
  * @param  bool omitDivTags (set to true if you don't want the <div><div> tags anymore. used internally to re-render.)
	* @param  bool putIntoPage (set to true if you don't want the result returned, it will be added to the page automatically. you will get bool true back. since bs4.4.)
  * @param  int  lookAhead (how many levels to render down. default is this._tree.lookAhead. added in bs4.5)
  * @return mixed (vector if not param putIntoPage, bool otherwise.)
  */
  this.render = function(omitDivTags, putIntoPage, lookAhead) {
    if (typeof(this._tree.stopWatch) == 'object') this._tree.stopWatch.takeTime('Bs_TreeElement.render() for id: ' + this.id + ' in level: ' + this._level);
		//debug('Bs_TreeElement.render() for id: ' + this.id + ' in level: ' + this._level + ' kids: ' + this._children.length);
		
		/*
		//if (!this._isLoaded) {
		if (this._unloadedChildren) {
		//if (this._tree.hasNodeLoader() && !this._checkedForChildren) {
			this._tree.loadNodeChildrenFromServer(this.id);
			return false;
		}
		*/
		
    if (((this._level) == 0) && (!this._tree.showPseudoElement) && this._tree.hasNodeLoader() && !this._checkedForChildren) { //
			this._tree.loadNodeChildrenFromServer(this.id);
			return false;
		}
    
    if (typeof(lookAhead) == 'undefined') {
      lookAhead = this._tree.lookAhead;
    }
    if ((this._tree._pseudoElement == this) && !this._tree.showPseudoElement && (lookAhead != -1)) {
      lookAhead++; //because the pseudoelement does not count this time, visually.
    }
    
    var imageDir    = this._getVar('imageDir');
    var imageHeight = this._getVar('imageHeight');
    
    var out      = new Array();
    var outI     = 0;
    //var evalStr  = new Array();
    //var evalStrI = 0;
    var evalStr  = '';
    
    //out[outI++] = '<nobr>';
    
    //if (!omitDivTags) {
      var divTagStart = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '"';
      divTagStart += ' style="'; //height:10;
      if (!this.visible) {
        divTagStart += 'display:none;';
      }
      divTagStart += '">';
    //}
    
    if ((this._level) > 0 || (this._tree.showPseudoElement)) { //maybe hide first pseudo-element.
      out[outI++] = '<nobr>';
      out[outI++] = '<div style="float:none;"';
      out[outI++] = ' id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_drag"';
			if (this._tree.draggable) {
				out[outI++] = ' onDragStart="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onDragStart\'));"';
				out[outI++] = ' onDragEnter="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onDragEnter\'));"';
				out[outI++] = ' onDragOver="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onDragOver\'));"';
				out[outI++] = ' onDrop="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onDrop\'));"';
			}
			out[outI++] = '>';
      
			out[outI++] = '<div style="overflow:visible; height:' + imageHeight + '; ' + this._getVar('divStyle') + '">';
      
      var level = this._level;
      if (!this._tree.showPseudoElement) --level;
      
      //visually indent the output
      var obj = this;
			var outTemp = '';
      for (var i=0; i<level; i++) {
        obj = obj.parent;
        if (obj.hasSiblingsDown(true)) {
          var img = 'line1';
        } else {
          var img = 'empty';
        }
				//out[outI++] = '<img src="' + imageDir + img + '.gif" height="' + imageHeight + '" border="0" align="top">';
				outTemp = '<img src="' + imageDir + img + '.gif" height="' + imageHeight + '" border="0" align="top">' + outTemp;
      }
			out[outI++] = outTemp;
			
      if (this.hasSiblingsDown()) {
        var imgNumber = 3;
      } else {
        var imgNumber = 2;
      }
      
      if (this.hasVisibleChildren()) {
        //hacky at the end: 
				//if we don't use the useAutoSequence, we still have to make sure it's the first element in level 1. 
				//there could be more than one element in level 1, thus they'd be on the same line. this special 
				//behavior only applies for the first one. so it would be a bug. but a known one, it's written here :-)
        if ((this._level == 0) || (!this._tree.showPseudoElement && (this._level == 1) && ((this._tree.useAutoSequence && (this.id == 1)) || (!this._tree.useAutoSequence && true)))) {
          //it's the first line of all, there are no parents, nothing on top, special case, different image.
          if (this.hasSiblingsDown()) {
            imgNumber++;
          } else {
            imgNumber--;
          }
        }
        if (this.isOpen) {
          var plusImg = 'minus' + imgNumber;
          var onClick = 'Close';
        } else {
          var plusImg = 'plus' + imgNumber;
          var onClick = 'Open';
        }
      } else {
        var plusImg = 'line' + imgNumber;
        var onClick = false;
      }
      
      if (onClick) {
        //var onClickStr = 'onClick="Bs_Objects['+this._tree._id+'].element' + onClick + '(\'' + this.id + '\');"';
        var onClickStr = 'onClick="Bs_Objects['+this._tree._id+'].elementToggleOpenClose(\'' + this.id + '\');"';
      } else {
        var onClickStr = '';
      }
			
			var useClickSpan = false;
			var clickSpanTags = '';
      if (this.onClick) {
				useClickSpan = true;
        var onClick = this.onClick;
        onClick = onClick.replace(/__this\.id__/g, this.id); //replace the string __this.id__ with the actual id (int).
        clickSpanTags += ' style="cursor:pointer; cursor:hand;" onClick="' + onClick + '" ';
      }
			if (this.hasEventAttached('onContextMenu')) {
				useClickSpan = true;
				clickSpanTags += ' onContextMenu="return Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onContextMenu\'));" ';
			}
			if (useClickSpan) {
	      out[outI++] = '<span ' + clickSpanTags + '>';
			}
			
      out[outI++] = '<img id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_openClose" src="' + imageDir + plusImg + '.gif" height="' + imageHeight + '" border="0" ' + onClickStr + ' align="middle"';
      out[outI++] = ' style="vertical-align:' + ((imageHeight > 16) ? 'middle' : 'top') + '">';
      
      if (this.beforeIconSpan) {
        out[outI++] = "<span>" + this.beforeIconSpan + "</span>";
      }
      
			
      if (this.url) {
        var hRef = '<a href="' + this.url + '"';
        hRef += ' name="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_href"';
				//hRef += ' onkeydown="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\');"';
				//hRef += ' onkeydown="Bs_Objects['+this._id+'].onKeyDown();"';
				hRef += ' onkeydown="return Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\', Array(event));"';
        if (this.target) {
          hRef += ' target="' + this.target + '"';
        }
        if (this.tooltip) {
          hRef += ' alt="' + this.tooltip + '"';
          hRef += ' title="' + this.tooltip + '"';
        }
				hRef += ' style="'  + this._getVar('linkStyle') + '"';
				if (typeof(this.linkTitle)  !== 'undefined') hRef += ' title="'  + this.linkTitle + '"';
				if (typeof(this.linkStatus) !== 'undefined') {
					hRef += ' onmouseover="window.status=\''  + this.linkStatus + '\'; return true;"';
					hRef += ' onmouseout="window.status=\'\';"';
				}
        hRef += '>';
      } else {
				/* dev code
				this.url = true;
        var hRef = '<a';
				hRef += ' href="#"';
        hRef += ' name="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_href"';
				hRef += ' onkeydown="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\', Array(event));"';
				hRef += '>';
				*/
			}
      
      var folderIconId = this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_folder';
      if (this._getVar('useFolderIcon')) {
        if (hRef) out[outI++] = hRef;
        switch (typeof(this.icon)) {
          case 'undefined':
          //case undefined:
            if (this._tree.useLeaf && !this.hasChildren()) {
              var folderImg = 'leaf';
            } else {
              var folderImg = 'folder';
              folderImg += (this.isOpen) ? 'Open' : 'Closed';
            }
            out[outI++] = '<img id="' + folderIconId + '" src="' + imageDir + folderImg + '.gif" height="' + imageHeight + '" border="0" align="top">';
            break;
          case 'bool':
          case 'boolean':
            //bool false, no icon at all.
            break;
          case 'string':
            if (this.icon != 'false') { //buggy sort of bool false.
              out[outI++] = '<img id="' + folderIconId + '" src="';

							if (!this._iconHasPath(this.icon)) out[outI++] = imageDir;
							out[outI++] = this.icon;
							if (!this._iconHasExtension(this.icon)) out[outI++] = '.gif';
							out[outI++] = '" height="' + imageHeight + '" border="0" align="top">';
            }
        }
        if (hRef) out[outI++] = '</a>';
      }
      
      
      if (this.beforeCaptionSpan) {
        out[outI++] = "<span>" + this.beforeCaptionSpan + "</span>";
      }
      
			if (this._tree.useRadioButton) {
	      out[outI++] = '<input type="radio"';
				out[outI++] = ' name="' + this._tree.getRadioButtonName() + '"';
				out[outI++] = ' id="' + this._tree.getRadioButtonName() + '_' + this.getDhtmlCompatibleId() + '"';
				out[outI++] = ' value="' + this.id + '"';
				//alert(this._tree.getRadioButtonName() + '_' + this.id);
				if (ie && !moz) { //only do that for ie. in moz it makes the button larger. in ie it limits the buttons borders. needs browser detection.
					out[outI++] = ' style="height:16px;"';
				}
				if (this.radioButtonSelected) {
					out[outI++] = ' checked';
				}
				if (this._tree._disabled) {
					out[outI++] = ' readonly disabled';
				}
				out[outI++] = '>';
			}
			
      if (this._tree.useCheckboxSystem) {
        /*
        //old version using html checkboxes
        out[outI++] = '&nbsp;<input type="checkbox" name="' + this.checkboxName + '" onClick="t.elementCheckboxEvent(' + this.id + ', this.checked);" value="1"';
        if (this.isChecked == 2) {
          out[outI++] = ' checked';
        } else if (this.isChecked == 1) {
          out[outI++] = ' checked';
          //out[outI++] = ' style="background: Silver;"';
        }
        out[outI++] = '>';
        */
        
        
        //new version using Bs_Checkbox (dhtml checkbox). this is needed to be able to use 
        //"part-selected" values (with gray background).
				var cleanedCheckboxName = this._cleanCheckboxName(this.checkboxName);
        var checkboxSpan = cleanedCheckboxName + 'Span';
        var checkboxObj  = cleanedCheckboxName + 'Obj';
        out[outI++] = '&nbsp;<span id="' + checkboxSpan + '">';
        
        var t = new Bs_Checkbox();
        t.objectName = checkboxObj;
				//alert(this.checkboxName);
        t.checkboxName = this.checkboxName;
        t.value = this.isChecked;
				if (this._tree._disabled) t.disabled = true;
				
        if (this._getVar('checkboxSystemGuiNochange')) {
          t.guiNochange = true;
        }
        var chkImagDir = this._getVar('checkboxSystemImgDir');
        if (chkImagDir) {
          t.imgDir = chkImagDir;
        } else {
          t.imgDir     = "/_bsJavascript/components/checkbox/img/win2k_noBorder/";
        }
        t.imgWidth   = '1';
        t.imgHeight  = '1';
        
        if (this._tree.checkboxSystemWalkTree) {
          t.attachOnClick('Bs_Objects['+this._tree._id+'].elementCheckboxEvent(\'' + this.id + '\', ' + checkboxObj + '.value);');
        }
        eval(checkboxObj + ' = t;'); //set reference. the 't' thingie is a hack so we don't need to eval it all.
        this._checkboxObject = t;
        evalStr += checkboxObj + ".draw('" + checkboxSpan + "');";
        
        out[outI++] = '</span>';
      }
      
      out[outI++] = '&nbsp;';
      
			out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption"';
			if (this.onClick || this.hasEventAttached('onClickCaption')) {
				out[outI++] = ' style="cursor:pointer; cursor:hand;"';
			} else {
				out[outI++] = ' style="cursor:default;"';
			}
			out[outI++] = ' onclick="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onClickCaption\'));"';
			//out[outI++] = ' onkeydown="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\');"';
			out[outI++] = '>';
			
      if (hRef) out[outI++] = hRef;
			//tabIndex=1
      out[outI++] = '<span';
      if (!this.url) {
	      out[outI++] = ' tabindex="0"';
				out[outI++] = ' onkeydown="return Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\', Array(event));"';
			}
			out[outI++] = ' id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption2">';
			out[outI++] = this.caption;
			out[outI++] = '</span>';
			

			
						
      if (hRef) out[outI++] = '</a>';
      out[outI++] = '</span>';
      if (useClickSpan) {
        out[outI++] = '</span>';
      }
      
      out[outI++] = '</div>';
      
      if (this.afterCaptionSpan) {
        // border:1px solid blue;
        //out[outI++] = '<div style="float:right; width:40;">' + this.afterCaptionSpan + '</div>';
        out[outI++] = '<div style="overflow:visible;">' + this.afterCaptionSpan + '</div>';
      } else {
        // #Bug reported by fklee@isuisse.com: commented out empty <div></div>. It caused an empty line in the tree. 
        // out[outI++] = '<div style="overflow:visible;"></div>';
      }
      out[outI++] = '</div>';
      out[outI++] = '</nobr>';
    }
    
    //if ((this._tree.preloadDown == -1) || (this.isOpen && this.hasChildren())) {
    out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_children"';
    if (!this.isOpen) {
      out[outI++] = ' style="display:none;"';
    }
    out[outI++] = '>';
    if (this.isOpen || (lookAhead > 0) || (lookAhead == -1)) {
			// work.www.blueshoes.org/_bsJavascript/components/tree/examples/example22.html
			
			
	    if (this.hasUnloadedChildren()) {
				//debug(lookAhead);
				//this._tree._workStack[this._tree._workStack.length] = 'Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'render\', Array(false,true,'+newLookAhead+'));';
				this._tree.loadNodeChildrenFromServer(this.id);
			} else {
	      for (var i=0, n=this._children.length; i<n; i++) {
	        if (lookAhead == -1) {
	          var newLookAhead = -1;
	        } else {
	          if (this.isOpen) { //have to open up one more since this one is open already.
	            var newLookAhead = lookAhead;
	          } else {
	            var newLookAhead = lookAhead -1;
	          }
	        }
					if (false) {
						this._tree._workStack[this._tree._workStack.length] = 'Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this._children[i].id + '\', \'render\', Array(false,true,'+newLookAhead+'));';
					} else {
		        var t = this._children[i].render(false, false, newLookAhead);
		        out[outI++] = t[0];
		        evalStr    += t[1];
					}
	      }
			}
    }
    out[outI++] = '</span>';
    //}
    //if (!omitDivTags) {
      //out[outI++] = '</span>';
      divTagEnd = '</span>';
    //}
    //out[outI++] = "\n";
    
    this._isOutrendered = true;
		
		var content = new Array(out.join(''), evalStr);
		if (putIntoPage) {
			var addNotReplace = false;
      var doc = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId());
			if ((doc == null) && (typeof(this.parent) != 'undefined')) {
				//alert(this.id);
				addNotReplace = true;
		    //if (!omitDivTags) {
					content[0] = divTagStart + content[0] + divTagEnd;
				//}
				var doc = document.getElementById(this._tree._objectId + '_e_' + this.parent.getDhtmlCompatibleId() + '_children');
			}
			if (doc != null) {
				if (addNotReplace) {
					//alert(doc.innerHTML);
					doc.innerHTML += content[0];
				} else {
					doc.innerHTML = content[0];
				}
  	    if (content[1] != '') {
    	    eval(content[1]);
      	}
				return true;
			} else {
				//alert('failed 882!');
				return false;
			}
		} else {
	    if (!omitDivTags) {
		    content[0] = divTagStart + content[0] + divTagEnd;
			}
	    return content;
		}
  }
  
	
  /**
	* faster render function that omits many features.
	* was added to see if we can speed up things significantly.
	* is ok for basic trees. try it if it works for you.
	* @status experimental
	* @access public
  * @param  bool omitDivTags (set to true if you don't want the <div><div> tags anymore. used internally to re-render.)
	* @param  bool putIntoPage (set to true if you don't want the result returned, it will be added to the page automatically. you will get bool true back.)
  * @param  int  lookAhead (how many levels to render down. default is this._tree.lookAhead. added in bs4.5)
  * @return mixed (vector if not param putIntoPage, bool otherwise.)
  */
  this.renderSimple = function(omitDivTags, putIntoPage, lookAhead) {
    if (typeof(this._tree.stopWatch) == 'object') this._tree.stopWatch.takeTime('Bs_TreeElement.renderSimple() for id: ' + this.id + ' in level: ' + this._level);
    
    if (typeof(lookAhead) == 'undefined') {
      lookAhead = this._tree.lookAhead;
    }
    if ((this._tree._pseudoElement == this) && !this._tree.showPseudoElement && (lookAhead != -1)) {
      lookAhead++; //because the pseudoelement does not count this time, visually.
    }
    
    var imageDir    = this._getVar('imageDir');
    var imageHeight = this._getVar('imageHeight');
    
    var out      = new Array;
    var outI     = 0;
    var evalStr  = new Array;
    
    if (!omitDivTags) {
      out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '"';
      out[outI++] = ' style="'; //height:10;
      if (!this.visible) {
        out[outI++] = 'display:none;';
      }
      out[outI++] = '">';
    }
    
    if ((this._level) > 0 || (this._tree.showPseudoElement)) { //maybe hide first pseudo-element.
      out[outI++] = '<nobr>';
      out[outI++] = '<div style="float:none;"';
      out[outI++] = ' id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_drag"';
			out[outI++] = '>';
      
			out[outI++] = '<div style="overflow:visible; height:' + imageHeight + '; ' + this._getVar('divStyle') + '">';
      
      var level = this._level;
      if (!this._tree.showPseudoElement) --level;
      
			
      //visually indent the output
      var obj = this;
			var outTemp = '';
      for (var i=0; i<level; i++) {
        obj = obj.parent;
        if (obj.hasSiblingsDown(true)) {
          var img = 'line1';
        } else {
          var img = 'empty';
        }
				//out[outI++] = '<img src="' + imageDir + img + '.gif" height="' + imageHeight + '" border="0" align="top">';
				outTemp = '<img src="' + imageDir + img + '.gif" height="' + imageHeight + '" border="0" align="top">' + outTemp;
      }
			out[outI++] = outTemp;
			
			
      if (this.hasSiblingsDown()) {
        var imgNumber = 3;
      } else {
        var imgNumber = 2;
      }
      
      if (this.hasVisibleChildren()) {
        //hacky at the end: 
				//if we don't use the useAutoSequence, we still have to make sure it's the first element in level 1. 
				//there could be more than one element in level 1, thus they'd be on the same line. this special 
				//behavior only applies for the first one. so it would be a bug. but a known one, it's written here :-)
        if ((this._level == 0) || (!this._tree.showPseudoElement && (this._level == 1) && ((this._tree.useAutoSequence && (this.id == 1)) || (!this._tree.useAutoSequence && true)))) {
          //it's the first line of all, there are no parents, nothing on top, special case, different image.
          if (this.hasSiblingsDown()) {
            imgNumber++;
          } else {
            imgNumber--;
          }
        }
        if (this.isOpen) {
          var plusImg = 'minus' + imgNumber;
          var onClick = 'Close';
        } else {
          var plusImg = 'plus' + imgNumber;
          var onClick = 'Open';
        }
      } else {
        var plusImg = 'line' + imgNumber;
        var onClick = false;
      }
      
      if (onClick) {
        //var onClickStr = 'onClick="Bs_Objects['+this._tree._id+'].element' + onClick + '(\'' + this.id + '\');"';
        var onClickStr = 'onClick="Bs_Objects['+this._tree._id+'].elementToggleOpenClose(\'' + this.id + '\');"';
      } else {
        var onClickStr = '';
      }
      
      out[outI++] = '<img id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_openClose" src="' + imageDir + plusImg + '.gif" height="' + imageHeight + '" border="0" ' + onClickStr + ' align="middle"';
      out[outI++] = ' style="vertical-align:' + ((imageHeight > 16) ? 'middle' : 'top') + '">';
      
      if (this.url) {
        var hRef = '<a href="' + this.url + '"';
        hRef += ' name="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_href"';
				hRef += ' onkeydown="return Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'onKeyDown\', Array(event));"';
        if (this.target) {
          hRef += ' target="' + this.target + '"';
        }
				hRef += ' style="'  + this._getVar('linkStyle') + '"';
        hRef += '>';
      }
			
      var folderIconId = this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_folder';
      if (this._getVar('useFolderIcon')) {
        if (hRef) out[outI++] = hRef;
        switch (typeof(this.icon)) {
          case 'undefined':
          //case undefined:
            if (this._tree.useLeaf && !this.hasChildren()) {
              var folderImg = 'leaf';
            } else {
              var folderImg = 'folder';
              folderImg += (this.isOpen) ? 'Open' : 'Closed';
            }
            out[outI++] = '<img id="' + folderIconId + '" src="' + imageDir + folderImg + '.gif" height="' + imageHeight + '" border="0" align="top">';
            break;
          case 'bool':
          case 'boolean':
            //bool false, no icon at all.
            break;
          case 'string':
            if (this.icon != 'false') { //buggy sort of bool false.
              out[outI++] = '<img id="' + folderIconId + '" src="';

							if (!this._iconHasPath(this.icon)) out[outI++] = imageDir;
							out[outI++] = this.icon;
							if (!this._iconHasExtension(this.icon)) out[outI++] = '.gif';
							out[outI++] = '" height="' + imageHeight + '" border="0" align="top">';
            }
        }
        if (hRef) out[outI++] = '</a>';
      }
      
      out[outI++] = '&nbsp;';
      
			out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption"';
			if (this.onClick) {
				out[outI++] = ' style="cursor:pointer; cursor:hand;"';
			} else {
				out[outI++] = ' style="cursor:default;"';
			}
			out[outI++] = ' onClick="Bs_Objects['+this._tree._id+'].executeOnElement(\'' + this.id + '\', \'fireEvent\', Array(\'onClickCaption\'));">';
			
      if (hRef) out[outI++] = hRef;
      out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption2">' + this.caption + '</span>';
      if (hRef) out[outI++] = '</a>';
      out[outI++] = '</span>';
      //if (this.onClick) {
        //out[outI++] = '</span>';
      //}
      
      out[outI++] = '</div>';
      
      out[outI++] = '</div>';
      out[outI++] = '</nobr>';
    }
    
    if (typeof(this._tree.stopWatch) == 'object') this._tree.stopWatch.takeTime('Bs_TreeElement.renderSimple() 3');
		
    //if ((this._tree.preloadDown == -1) || (this.isOpen && this.hasChildren())) {
    out[outI++] = '<span id="' + this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_children"';
    if (!this.isOpen) {
      out[outI++] = ' style="display:none;"';
    }
    out[outI++] = '>';
    if (this.isOpen || (lookAhead > 0) || (lookAhead == -1)) {
      for (var i=0, n=this._children.length; i<n; i++) {
        if (lookAhead == -1) {
          var newLookAhead = -1;
        } else {
          if (this.isOpen) { //have to open up one more since this one is open already.
            var newLookAhead = lookAhead;
          } else {
            var newLookAhead = lookAhead -1;
          }
        }
        var t = this._children[i].renderSimple(false, false, newLookAhead);
        out[outI++]             = t[0];
        evalStr[evalStr.length] = t[1];
      }
    }
    out[outI++] = '</span>';
    //}
    if (!omitDivTags) {
      out[outI++] = '</span>';
    }
    out[outI++] = "\n";
    
    this._isOutrendered = true;
		
		var content = new Array(out.join(''), evalStr.join(''));
		//this._tree._out[this._tree._out.length] = out.join('');
		
		if (putIntoPage) {
      var doc = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId());
			if (doc != null) {
				doc.innerHTML = content[0];
  	    if (content[1] != '') {
    	    eval(content[1]);
      	}
				return true;
			} else {
        //alert('false');
				return false;
			}
		} else {
	    return content;
		}
  }
	
	
  /**
  * resets this tree element.
  * @access public
  * @return void
  * @todo update this method so all vars get reset. what about the id? ...
  */
  this.reset = function() {
    this.caption           = null;
    this.url               = null;
    this.target            = null;
    this.tooltip           = null;
    this.onClick           = null;
    this.isOpen            = false;
    this.isChecked         = 0;
    this.checkboxName      = null;
    this.beforeIconSpan    = null;
    this.beforeCaptionSpan = null;
    this.afterCaptionSpan  = null;
		this.linkStyle         = null;
		this.linkTitle         = null;
		this.linkStatus        = null;
	  this.divStyle          = null;
  }
  
  /**
  * inits this tree element with the data of the given array (etc).
	* 
	* the given hash may have the following keys:
	*   'id'                  => a unique id. if tree.useAutoSequence is true (default) then this is ignored.
	* 	'caption'             => string
	* 	'url'                 => string, a link on the caption
	* 	'target'              => string, a target for the url (eg '_blank')
	* 	'tooltip'             => string, some mouse-over text, uses alt and title attributes of anchor
	* 	'onClick'             => !deprecated! string, a javascript onClick event (function or string afaik)
	* 	'isOpen'              => bool, if the node should be open or not
	* 	'isChecked'           => int, 0 1 or 2 telling the value of the checkbox
	* 	'visible'             => bool, if the node should be visible at all (including all its kids)
	* 	'icon'                => string, name of the icon if you want to overwrite it.
	* 	'imageDir'            => string, path to the dir where the imges are, if you want to overwrite it.
	* 	'beforeIconSpan'      => string, html code you can put in a predefined span that shows up in front of the icon.
	* 	'beforeCaptionSpan'   => string, html code you can put in a predefined span that shows up in front of the caption.
	* 	'afterCaptionSpan'    => string, html code you can put in a predefined span that shows up behind the caption.
	* 	'checkboxName'        => string, name of the checkbox. if not specified then something will be made up.
	*   'radioButtonSelected' => bool, if the radio button of this element should be selected by default. only one can be!
	* 	'onClickCaption'      => !deprecated! string, a javascript onClick event on the caption (function or string afaik)
	* 	'onChangeCheckbox'    => !deprecated! string, a javascript onChange event on the checkbox (function or string afaik)
	*   'dataContainer'       => mixed (whatever, see var this.dataContainer).
	*   'events'              => array (hash with key/value pairs, see attachEvent().)
	*   'linkStyle'
	*   'linkTitle'
	*   'linkStatus'
	*   'divStyle'
	* 
  * @access public
  * @param  array  a (hash, see above)
  * @param  object tree (so we can set the reference to the tree object.)
  * @param  int    level (the level of the tree element.)
  * @return bool (true on success, false and sets error on failure.)
	* @see    exportAsArray()
  */
  this.initByArray = function(a, tree, level) {
    this._tree   = tree;
    this._level  = level;
    
    if (typeof(this._tree.stopWatch) == 'object') this._tree.stopWatch.takeTime('Bs_TreeElement.initByArray()');
    
    if (this._tree.useAutoSequence && (level > 0)) {
      this.id      = ++this._tree._elementSequence;
    } else {
      if (typeof(a['id']) == 'undefined') { //throw
				var err = 'tree error: useAutoSequence is set to false, but for an array element there is no id defined.';
				if (typeof(a['caption']) != 'undefined') err += ' (' + a['caption'] + ')';
				alert(err);
        this._addError(err);
        return false;
      }
      this.id = a['id'];
    }
    
    if (typeof(a['caption'])             != 'undefined') this.caption             = a['caption'];
    if (typeof(a['url'])                 != 'undefined') this.url                 = a['url'];
    if (typeof(a['target'])              != 'undefined') this.target              = a['target'];
    if (typeof(a['tooltip'])             != 'undefined') this.tooltip             = a['tooltip'];
    if (typeof(a['isOpen'])              != 'undefined') this.isOpen              = a['isOpen'];
		
		if (!this._tree.simple) {
	    if (typeof(a['linkStyle'])           != 'undefined') this.linkStyle           = a['linkStyle'];
	    if (typeof(a['linkTitle'])           != 'undefined') this.linkTitle           = a['linkTitle'];
	    if (typeof(a['linkStatus'])          != 'undefined') this.linkStatus          = a['linkStatus'];
	    if (typeof(a['divStyle'])            != 'undefined') this.divStyle            = a['divStyle'];
	    if (typeof(a['onClick'])             != 'undefined') this.onClick             = a['onClick'];
	    if (typeof(a['isChecked'])           != 'undefined') this.isChecked           = parseInt(a['isChecked']); //parseInt is a bugfix in bs4.3 --andrej
	    if (typeof(a['visible'])             != 'undefined') this.visible             = a['visible'];
	    if (typeof(a['icon'])                != 'undefined') this.icon                = a['icon'];
	    if (typeof(a['imageDir'])            != 'undefined') this.imageDir            = a['imageDir'];
	    if (typeof(a['beforeIconSpan'])      != 'undefined') this.beforeIconSpan      = a['beforeIconSpan'];
	    if (typeof(a['beforeCaptionSpan'])   != 'undefined') this.beforeCaptionSpan   = a['beforeCaptionSpan'];
	    if (typeof(a['afterCaptionSpan'])    != 'undefined') this.afterCaptionSpan    = a['afterCaptionSpan'];
	    if (typeof(a['radioButtonSelected']) != 'undefined') this.radioButtonSelected = a['radioButtonSelected'];
	    if (typeof(a['dataContainer'])       != 'undefined') this.dataContainer       = a['dataContainer'];
	    if (typeof(a['checkboxName']) != 'undefined') {
	      this.checkboxName  = a['checkboxName'];
	    } else {
	      if (this._tree.useCheckboxSystem) {
	        //have to make one up.
	        this.checkboxName = 'bsTreeChk_' + this._tree._objectId + '_' + this.id;
	      }
	    }
	    if (typeof(a['onClickCaption'])    != 'undefined') {
	      this.attachEvent('onClickCaption', a['onClickCaption']);
	    }
	    if (typeof(a['onChangeCheckbox'])    != 'undefined') {
	      this.attachEvent('onChangeCheckbox', a['onChangeCheckbox']);
	    }
	    if (typeof(a['onContextMenu'])    != 'undefined') {
	      this.attachEvent('onContextMenu', a['onContextMenu']);
	    }
	    if (typeof(a['events']) != 'undefined') {
				for (ev in a['events']) {
					this.attachEvent(ev, a['events'][ev]);
				}
			}
			
		}
		
    return true;
  }
  
	
	/**
	* export this object as hash. reverse function of initByArray().
	* 
	* note that the 'onClickCaption' and 'onChangeCheckbox' events, as well as 'events' (all 
	* attached events at all) set in initByArray() are not included in the returned array.
	* this is a "todo".
	* 
	* @access public
	* @param  bool withChildren (set to true if you want to have the children too.)
	* @return array (hash)
	* @since  bs4.4
	* @see    initByArray()
	*/
	this.exportAsArray = function(withChildren) {
		var ret = new Array();
    if (typeof(this.id)                       != 'undefined') ret['id']                       = this.id;
    if (typeof(this.caption)                  != 'undefined') ret['caption']                  = this.caption;
    if (typeof(this.url)                      != 'undefined') ret['url']                      = this.url;
    if (typeof(this.target)                   != 'undefined') ret['target']                   = this.target;
    if (typeof(this.tooltip)                  != 'undefined') ret['tooltip']                  = this.tooltip;
    if (typeof(this.onClick)                  != 'undefined') ret['onClick']                  = this.onClick;
    if (typeof(this.isOpen)                   != 'undefined') ret['isOpen']                   = this.isOpen;
    if (typeof(this.isChecked)                != 'undefined') ret['isChecked']                = this.isChecked;
    if (typeof(this.visible)                  != 'undefined') ret['visible']                  = this.visible;
    if (typeof(this.icon)                     != 'undefined') ret['icon']                     = this.icon;
    if (typeof(this.imageDir)                 != 'undefined') ret['imageDir']                 = this.imageDir;
    if (typeof(this.beforeIconSpan)           != 'undefined') ret['beforeIconSpan']           = this.beforeIconSpan;
    if (typeof(this.afterCaptionSpan)         != 'undefined') ret['afterCaptionSpan']         = this.afterCaptionSpan;
    if (typeof(this.radioButtonSelected)      != 'undefined') ret['radioButtonSelected']      = this.radioButtonSelected;
    if (typeof(this.dataContainer)            != 'undefined') ret['dataContainer']            = this.dataContainer;
    if (typeof(this.checkboxName)             != 'undefined') ret['checkboxName']             = this.checkboxName;
    if (typeof(this.beforeCaptionSpan)        != 'undefined') ret['beforeCaptionSpan']        = this.beforeCaptionSpan;
    if (typeof(this.linkStyle)                != 'undefined') ret['linkStyle']                = this.linkStyle;
    if (typeof(this.linkTitle)                != 'undefined') ret['linkTitle']                = this.linkTitle;
    if (typeof(this.linkStatus)               != 'undefined') ret['linkStatus']               = this.linkStatus;
    if (typeof(this.divStyle)                 != 'undefined') ret['divStyle']                 = this.divStyle;

		
		/*
    if (typeof(a['onClickCaption'])    != 'undefined') {
      this.attachEvent('onClickCaption', a['onClickCaption']);
    }
    if (typeof(a['onChangeCheckbox'])    != 'undefined') {
      this.attachEvent('onChangeCheckbox', a['onChangeCheckbox']);		
		*/
		
		if (withChildren) {
			ret['children'] = new Array();
			for (var i=0; i<this._children.length; i++) {
				ret['children'][ret['children'].length] = this._children[i].exportAsArray(true);
			}
		}
		
		return ret;
	}
	
	
  /**
  * updates the object vars from the data given in the array.
	* 
	* caution: code not up to date. consider merging this with initByArray() somehow.
	* 
  * @access public
  * @param  array a
  * @return void
  */
  this.updateObjectByArray = function(a) {
    //reset first
    this.reset();
    
		this._isLoaded = true;
		
    //now set new values
    if (a['caption'])            this.caption            = a['caption'];
    if (a['url'])                this.url                = a['url'];
    if (a['target'])             this.target             = a['target'];
    if (a['tooltip'])            this.tooltip            = a['tooltip'];
    if (a['onClick'])            this.onClick            = a['onClick'];
    if (a['isOpen'])             this.isOpen             = a['isOpen'];
    if (a['isChecked'])          this.isChecked          = a['isChecked'];
    if (a['imageDir'])           this.imageDir           = a['imageDir'];
		
    if (a['checkboxName']) {
      this.checkboxName  = a['checkboxName'];
    } else {
      if (this._tree.useCheckboxSystem) {
        //have to make one up.
        this.checkboxName = 'bsTreeCheckbox' + this.id;
      }
    }
    
    if (a['beforeIconSpan'])     this.beforeIconSpan     = a['beforeIconSpan'];
    if (a['beforeCaptionSpan'])  this.beforeCaptionSpan  = a['beforeCaptionSpan'];
    if (a['afterCaptionSpan'])   this.afterCaptionSpan   = a['afterCaptionSpan'];
  }
  
	
  /**
  * returns this tree element as js array.
  * @access public
  * @param  string varName (the var name to use in the js code for the array.)
  * @param  bool   recursive (if the children should be used aswell, recursively.)
  * @return string
  */
  this.getJavascriptCode = function(varName, recursive) {
    var ret = "";
		
    if (
        (this._tree.useAutoSequence && (this.id > 1)) 
        || (!this._tree.useAutoSequence && !this.parent)
    ) {
      //skip the first element aka pseudo element.
    } else {
      ret += varName + " = new Array();\n";
      if (!this._tree.useAutoSequence) {
        ret += varName + "['id'] = \"" + this.id + "\";\n";
      }
      if (this.caption)           ret += varName + "['caption']            = \"" + this.caption            + "\";\n";
      if (this.url)               ret += varName + "['url']                = \"" + this.url                + "\";\n";
      if (this.target)            ret += varName + "['target']             = \"" + this.target             + "\";\n";
      if (this.tooltip)           ret += varName + "['tooltip']            = \"" + this.tooltip            + "\";\n";
      //if (this.onClick)           ret += varName + "['onClick']            = '" + this.onClick            + "';\n";
      if (this.onClick) {
        //var onClick = this.onClick.replace(/'/g,  "\\'");
        var onClick = this.onClick.replace(/"/g,  '\\"');
        ret += varName + "['onClick']            = \"" + onClick            + "\";\n";
      }
      if (this.imageDir)          ret += varName + "['imageDir']           = \"" + this.imageDir           + "\";\n";
      
      if (this.isOpen)            ret += varName + "['isOpen']             = '" + this.isOpen              + "';\n";
      if (this.isChecked)         ret += varName + "['isChecked']          = '" + this.isChecked           + "';\n";
//      checkboxName
      if (this.checkboxName)      ret += varName + "['checkboxName']       = '" + this.checkboxName        + "';\n";
      if (this.icon)              ret += varName + "['icon']               = \"" + this.icon               + "\";\n";
      if (this.beforeIconSpan)    ret += varName + "['beforeIconSpan']     = \"" + this.beforeIconSpan     + "\";\n";
      if (this.beforeCaptionSpan) ret += varName + "['beforeCaptionSpan']  = \"" + this.beforeCaptionSpan  + "\";\n";
      if (this.afterCaptionSpan)  ret += varName + "['afterCaptionSpan']   = \"" + this.afterCaptionSpan   + "\";\n";
      if (this.linkStyle)         ret += varName + "['linkStyle']          = \"" + this.linkStyle          + "\";\n";
      if (this.linkTitle)         ret += varName + "['linkTitle']          = \"" + this.linkTitle          + "\";\n";
      if (this.linkStatus)        ret += varName + "['linkStatus']         = \"" + this.linkStatus         + "\";\n";
      if (this.divStyle)          ret += varName + "['divStyle']           = \"" + this.divStyle           + "\";\n";
			
      varName += "['children']";
    }
    if (recursive) {
      if (this._children.length > 0) {
        ret += varName + " = new Array();\n";
        for (var i=0; i<this._children.length; i++) {
          ret += this._children[i].getJavascriptCode(varName + "[" + i + "]", recursive);
        }
      }
    }
    return ret;
  }
  
  
  /**
  * sets this tree element as the currently active one.
  * also sets the previously selected element as inactive, 
  * because only one can be active at a time.
  * 
  * @access public
  * @return void
  * @see    this.unsetActive()
  */
  this.setActive = function() {
    //if (this._tree._currentActiveElement) this._tree._currentActiveElement.unsetActive();
		var activeElement = this._tree.getActiveElement();
		if (activeElement != false) {
			activeElement.unsetActive();
		}
    this._tree.setActiveElement(this);
		this._highlight();
  }
	
	/**
	* this code was in setActive(), now it's here.
	* when the tree is not ready yet, we can use setTimeout() to try it again after a while.
	* @access private
	* @return void
	*/
	this._highlight = function() {
    var elmSetActive = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption');
		if (elmSetActive != null) {
	    elmSetActive.style.backgroundColor = this._getVar('captionBgColor');
		} else {
			setTimeout("Bs_Objects["+this._tree._id+"].executeOnElement('" + this.id + "', '_highlight');", 800);
		}
	}
  
  /**
  * i think this name is better than "setInactive" because we don't 
  * inactivate the element, we just take the focus from it. 
  * maybe setFocus and unsetFocus would be better? whatever.
  * @access public
  * @return void
  * @see    this.setActive()
  */
  this.unsetActive = function() {
    var e = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_caption');
		if (e != null) e.style.backgroundColor = 'transparent';
  }
  
  
  /**
  * changes the open/closed mode.
  * @access public
  * @return void
  */
  this.toggleOpenClose = function() {
    if (this.isOpen) {
	    if (this.hasEventAttached('onBeforeClose')) {
				var status = this.fireEvent('onBeforeClose');
				if (status != true) return;
			}
      this.close();
	    if (this.hasEventAttached('onAfterClose')) this.fireEvent('onAfterClose');
    } else {
	    if (this.hasEventAttached('onBeforeOpen')) {
				var status = this.fireEvent('onBeforeOpen');
				if (status != true) return;
			}
      this.open();
	    if (this.hasEventAttached('onAfterOpen')) this.fireEvent('onAfterOpen');
    }
  }
  
  
  /**
  * opens this element.
	* does not fire the 'onBeforeOpen' and 'onAfterOpen' events.
  * @access public
	* @param  bool checkParents (used internally, you should not have to care about it.)
  * @return void
  */
  this.open = function(checkParents) {
		//debug('Bs_TreeElement.open() for id: ' + this.id);
		
		if (this.isOpen) return; //already open!
    this.isOpen = true;
    
    //make sure all children are rendered:
    if (this.hasVisibleChildren()) {
      var lookAhead = this._tree.lookAhead;
      var doRender  = false;
      for (var i=0; i<this._children.length; i++) {
				//alert(this.caption + ' ' + this._children[i].hasUnloadedChildren());
        if (!this._children[i]._isOutrendered || (this._children[i].hasUnloadedChildren())) {
          doRender = true;
          break;
        }
      }
      if (doRender) this.render(true, true, lookAhead);
    }
    if (true || !doRender) {
      if (this._isOutrendered) {
        var d = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_children');
				
        if (d) d.style.display = 'block';
        this._switchIconsOnToggleOpenClose();
      } else {
  			if (checkParents) {
  				//now if someone uses Bs_Tree.elementOpenWalkUp() it could be that we open a node 4 levels down 
  				//(this one), but the node 3 and 2 levels down (parent and parent-parent) have not been rendered.
  				//so the next command would fail, getElementById() could not work cause not outrendered. we have 
  				//to check this.
  				//alert('a' + this.id);
  				this._renderParentsUp();
  			}
        this.render(true, true);
      }
			if (this._tree.rememberState) this._tree._updateStateCookie(this.id, 'open', true);
    }
    
    if (this._tree.autoCollapse) {
      //we need to close all existing siblings
      var sib = this.getSiblings();
      for (var i=0, n=sib.length; i<n; i++) {
        if (sib[i].id != this.id) {
          //don't close this one.
          sib[i].close();
        }
      }
    }
    
    //look ahead:
		/*
		//alert(typeof(this._undoneChildren));
		if (typeof(this._undoneChildren) == 'object') {
      for (var i=0; i<this._undoneChildren.length; i++) {
 	      var newE = this._tree._createTreeElement(this._undoneChildren[i], this._level +1);
     	  this.addChild(newE);
				if (typeof(newE._undoneChildren) == 'object') {
		      for (var j=0; i<newE._undoneChildren.length; j++) {
 	  		    var newE2 = this._tree._createTreeElement(newE._undoneChildren[j], newE._level +1);
     	  		newE.addChild(newE2);
					}
				}
      }
		}
		*/
    if (this.hasVisibleChildren()) {
      var lookAhead = this._tree.lookAhead;
      var treeElm     = this;
      //for (var i=lookAhead; i>0; i--) {
        for (var j=0, n=treeElm._children.length; j<n; j++) {
					if (typeof(treeElm._children[j]._undoneChildren) == 'object') {
			      for (var k=0, kn=treeElm._children[j]._undoneChildren.length; k<kn; k++) {
		 		      var newE = this._tree._createTreeElement(treeElm._children[j]._undoneChildren[k], treeElm._children[j]._level +1);
    			 	  treeElm._children[j].addChild(newE);
						}
						treeElm._children[j]._undoneChildren = false;
					}
          if (treeElm._children[j].hasVisibleChildren()) {
            var doRender = false;
            for (var k=0, kn=treeElm._children[j]._children.length; k<kn; k++) {
              if (!treeElm._children[j]._children[k]._isOutrendered) {
                var doRender = true;
                break;
              }
            }
            if (doRender) {
              treeElm._children[j].render(true, true, lookAhead);
            }
          }
        }
      //}
    }

  }
	
	
	/**
	* helper function for this.open().
	* @access private
	* @return void
	* @since  bs4.4
	*/
	this._renderParentsUp = function() {
		if (typeof(this.parent) == 'undefined') this.parent._renderParentsUp();
		if (this._isOutrendered) return;
		this.render(true, true);
	}
  
  
  /**
  * closes this element.
	* does not fire the 'onBeforeClose' and 'onAfterClose' events.
  * @access public
  * @return void
  */
  this.close = function() {
		if (!this.isOpen) return; //already closed!
    this.isOpen = false;
    if (this._isOutrendered) {
      var d = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_children');
      d.style.display = 'none';
      this._switchIconsOnToggleOpenClose();
    } else {
      this.render(true, true);
    }
		if (this._tree.rememberState) this._tree._updateStateCookie(this.id, 'open', false);
  }
  
  
  /**
  * toggles the icons (folder, plus-minus icon)
  * @access private
  * @return void
  */
  this._switchIconsOnToggleOpenClose = function() {
    var openClose = document.getElementById(this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_openClose');
		if (openClose) openClose.src = this._getSourceOpenCloseIcon();
		
    if (this._getVar('useFolderIcon')) {
      var folderIconId = this._tree._objectId + '_e_' + this.getDhtmlCompatibleId() + '_folder';
      var fIcon = document.getElementById(folderIconId);
      if (fIcon) {
        fIcon.src = this._getSourceFolderIcon();
      }
    }
  }
	
	
	/**
	* opens this and all child nodes recursively.
	* another function name would have been openAll.
	* @access public
	* @return void
	* @see    collapseAll()
	* @since  bs-4.6
	*/
	this.expandAll = function() {
		this.open();
		if (this.hasVisibleChildren()) {
			for (var i=0; i<this._children.length; i++) {
				this._children[i].expandAll();
			}
		}
	}
	
	
	/**
	* closes all child nodes recursively and possibly this one (param closeThis).
	* another function name would have been closeAll.
	* @access public
	* @param  bool closeThis (default is true)
	* @return void
	* @see    expandAll()
	* @since  bs-4.6
	*/
	this.collapseAll = function(closeThis) {
		if (typeof(closeThis) == 'undefined') closeThis = true;
		if (closeThis) this.close();
		if (this.hasVisibleChildren()) {
			for (var i=0; i<this._children.length; i++) {
				this._children[i].collapseAll(true);
			}
		}
	}
	
  
  /**
  * returns the new image source for the open/close icon.
  * used from this.open() and this.close(), so look there. 
  * @access private
  * @return string
  */
  this._getSourceOpenCloseIcon = function() {
    if (this.hasSiblingsDown()) {
      var imgNumber = 3;
    } else {
      var imgNumber = 2;
    }
		
    if (this.hasVisibleChildren()) {
	    //hacky at the end: 
			//if we don't use the useAutoSequence, we still have to make sure it's the first element in level 1. 
			//there could be more than one element in level 1, thus they'd be on the same line. this special 
			//behavior only applies for the first one. so it would be a bug. but a known one, it's written here :-)
      if ((this._level == 0) || (!this._tree.showPseudoElement && (this._level == 1) && ((this._tree.useAutoSequence && (this.id == 1)) || (!this._tree.useAutoSequence && true)))) {
        //it's the first line of all, there are no parents, nothing on top, special case, different image.
        if (this.hasSiblingsDown()) {
          imgNumber++;
        } else {
          imgNumber--;
        }
      }


/*
    if (this.hasVisibleChildren()) {
      if ((this._level == 0) || ((this.id == 2) && !this._tree.showPseudoElement)) {
        //it's the first line of all, there are no parents, nothing on top, special case, different image.
        if (this.hasSiblingsDown()) {
          imgNumber++;
        } else {
          imgNumber--;
        }
      }*/
			
      if (this.isOpen) {
        var plusImg = 'minus' + imgNumber;
        var onClick = 'Close';
      } else {
        var plusImg = 'plus' + imgNumber;
        var onClick = 'Open';
      }
    } else {
      var plusImg = 'line' + imgNumber;
    }
    var imageDir = this._getVar('imageDir');
    return imageDir + plusImg + '.gif';
  }

  /**
  * returns the new image source for the folder icon.
  * used from this.open() and this.close(), so look there. 
  * @access private
  * @return string
  */
  this._getSourceFolderIcon = function() {
    var imageDir = this._getVar('imageDir');
    switch (typeof(this.icon)) {
      case 'undefined':
        if (this._tree.useLeaf && !this.hasChildren()) {
          var folderImg = 'leaf';
        } else {
          var folderImg = 'folder';
          folderImg += (this.isOpen) ? 'Open' : 'Closed';
        }
        return imageDir + folderImg + '.gif';
        break;
      case 'bool':
      case 'boolean':
        //bool false, no icon at all.
        break;
			case 'string':
				if (this.icon != 'false') { //buggy sort of bool false.
					var ret = '';
					if (!this._iconHasPath(this.icon)) ret += imageDir;
					ret += this.icon;
					if (!this._iconHasExtension(this.icon)) ret += '.gif';
					return ret;
				}
    }
    return ''; // murphy
  }
	
	
  /**
  * tells if this tree element has children or not.
  * for insiders: another name would be isLeaf() (spelling?).
  * @access public
  * @return bool
  * @see    hasVisibleChildren(), numChildren()
  */
  this.hasChildren = function() {
    return (this._children.length > 0);
  }
  
  /**
  * tells if this tree element has children (at least 1) that are visible.
  * @access public
  * @return bool
  * @see    hasChildren(), numChildren()
  */
  this.hasVisibleChildren = function() {
		//cache
		//cannot be cached because of the lookahead feature.
		//if (typeof(this._hasVisibleChildren) != 'undefined') return this._hasVisibleChildren;
		
    if (!this._children || !(this._children.length > 0)) {
			this._hasVisibleChildren = false;
			return false;
		}
    for (var i=0, n=this._children.length; i<n; i++) {
      if (this._children[i].visible) {
				this._hasVisibleChildren = true;
				return true;
			}
    }
		this._hasVisibleChildren = false;
    return false;
  }
  
  /**
  * returns the number of children this element has.
  * @access public
  * @return int (0-n)
  * @see    hasChildren(), hasVisibleChildren()
  */
  this.numChildren = function() {
    return this._children.length;
  }
  
  /**
  * returns the position of the child for the given id.
  * 
  * example: this element has 5 children, the given child 
  *          is one of them. which one? 
  *          1st = displayed on top.
  *          3rd = displayed in the middle
  *          5th = displayed at the end
  * 
  * @access public
  * @param  int id
  * @return int (1-n, starting at 1)
  * @throws bool false (if not a child of this element.)
  */
  this.childPos = function(id) {
    for (var i=0, n=this._children.length; i<n; i++) {
      if (this._children[i].id == id) return ++i;
    }
    return false;
  }
  
  /**
  * @access public
  * @todo   all
  */
  this.hasSiblings = function() {
  }
  
  /**
  * tells if we have siblings down.
  * @access public
	* @param  bool ignoreCache
  * @return bool
  */
  this.hasSiblingsDown = function(ignoreCache) {
		if (false && !ignoreCache) {
			if (typeof(this._hasSiblingsDown) != 'undefined') return this._hasSiblingsDown; //cache
		}
    try {
      var tot = this.parent.numChildren();
      var pos = this.parent.childPos(this.id);
			this._hasSiblingsDown = (pos < tot);
      return this._hasSiblingsDown;
    } catch (e) {
			this._hasSiblingsDown = false;
      return false;
    }
  }
  
  /**
  * @access public
  * @todo   all
  */
  this.hasSiblingsAbove = function() {
  }
  
  /**
  * returns an array with references to all siblings.
	* note that it's the parents children, not only the siblings, which means 
	* the element itself (of which you want the siblings) is included too.
  * @access public
  * @return array (may be empty = no siblings)
  */
  this.getSiblings = function() {
    try {
      return this.parent.getChildren();
    } catch(e) {
      return new Array;
    }
  }
  
  /** 
  * returns the children as an array
  * @access public
  * @return array
  */
  this.getChildren = function() {
    return this._children;
  }
  
  
  /**
  * returns the parent id
  * @access public
  * @return mixed (string, number, whatever)
  * @throws bool false (if no parent)
  */
  this.getParentId = function() {
    try {
      return this.parent.id;
    } catch (e) {
      return false;
    }
  }
  
  /**
  * tells if this tree element has a parent or not.
  * @access public
  * @return bool
  */
  this.hasParent = function() {
    return (this.parent);
  }
  
  /**
  * attaches an event.
  * 
  * possible triggers: 
  *   'onClickCaption'
  *   'onChangeCheckbox'
  *   'onDragStart'
  *   'onDragEnter'
	*   'onDragOver'
	*   'onDrop'
	*   'onBeforeOpen'      return false to cancel the opening of the node
	*   'onAfterOpen'
	*   'onBeforeClose'     return false to cancel the closing of the node
	*   'onAfterClose'
	* 
	* if the attached thing is a function (not code string) then it will 
	* receive one parameter: the Bs_TreeElement object that was clicked.
  * 
  * @access public
  * @param  string trigger (for example 'onClickCaption')
  * @param  mixed  yourEvent (string (of code) or function)
  * @return void
  * @see    var this._attachedEvents
  */
  this.attachEvent = function(trigger, yourEvent) {
    if (typeof(this._attachedEvents) == 'undefined') {
      this._attachedEvents = new Array();
    }
    
		//let's see if with a function was provided but in quotes. happens when creating 
		//the tree data on the server with serverside scripting.
		if (typeof(yourEvent) == 'string') {
			if ((yourEvent.indexOf('(') == -1) && (yourEvent.indexOf(' ') == -1) && (yourEvent.indexOf(';') == -1)) {
				if (typeof(window[yourEvent]) == 'function') {
					yourEvent = window[yourEvent];
				}
			}
		}
		
    if (typeof(this._attachedEvents[trigger]) == 'undefined') {
      this._attachedEvents[trigger] = new Array(yourEvent);
    } else {
      this._attachedEvents[trigger][this._attachedEvents[trigger].length] = yourEvent;
    }
  }
  
  /**
  * tells if an event is attached for the trigger specified. 
  * @access public
  * @param  string trigger
  * @return bool
  */
  this.hasEventAttached = function(trigger) {
    //return ((this._attachedEvents) && (this._attachedEvents[trigger])); //does not work in moz :/
    return ((typeof(this._attachedEvents) != 'undefined') && (typeof(this._attachedEvents[trigger]) != 'undefined'));
  }
  
  
  /**
  * fires the events for the trigger specified.
  * @access public (used internally but feel free to trigger events yourself...)
  * @param  string trigger (for example 'onClickCaption')
  * @return bool (false if at least one of the attached functions returned false, true otherwise.)
  */
  this.fireEvent = function(trigger) {
		var ret = true;
		
	    //NO, BACK OUTSIDE AGAIN. 2003-12-23 --andrej   [now this is inside the if() above, it was the first code in the method before, outside the if().]
  	  if (trigger == 'onClickCaption') {
	      this.setActive();
  	  }
		
    //if (this._attachedEvents && this._attachedEvents[trigger]) { //does not work in moz :/
    if ((typeof(this._attachedEvents) != 'undefined') && (typeof(this._attachedEvents[trigger]) != 'undefined')) {
			
      var e = this._attachedEvents[trigger];
      if ((typeof(e) == 'string') || (typeof(e) == 'function')) {
        e = new Array(e);
      }
      for (var i=0, n=e.length; i<n; i++) {
        if (typeof(e[i]) == 'function') {
          var status = e[i](this);
					if (status == false) ret = false;
        } else if (typeof(e[i]) == 'string') {
        	var ev = e[i].replace(/__this\.id__/g, this.id); //replace the string __this.id__ with the actual id.
        	//ev = ev.replace(/__this__/g, 'this'); //replace the string __this__ with 'this'.
          eval(ev); //e[i]
        } //else murphy
      }
    }
		
		return ret;
  }
	
	
	/**
	* handels keystrokes to navigate the tree.
	* @access private
	* @return bool
	* @since  bs-4.6
	*/
	this.onKeyDown = function(event) {
		//if (!ie) return;
		if (event == null)                        return;
		if (typeof(event)         == 'undefined') return;
		if (typeof(event.keyCode) == 'undefined') return;
		
		//alert(event.keyCode);
		switch (event.keyCode) {
			case 107: // + plus
			case 39:  // cursor-right
				if (this.isOpen) {
					//go to the first child, if any.
		      if (this.hasVisibleChildren()) {
						var treeElm = this.getNextVisibleElement();
						if (treeElm != null) {
							var name = this._tree._objectId + '_e_' + treeElm.id + '_href';
							var captionSpan = document.getElementsByName(name);
							if (captionSpan.length > 0) {
								captionSpan[1].focus();
							} else {
								name = this._tree._objectId + '_e_' + treeElm.id + '_caption2';
								captionSpan = document.getElementById(name);
								if (captionSpan) captionSpan.focus();
							}
						}
					}
				} else {
					this.open();
				}
				return false;
			case 37:  // cursor-left
			case 109: // - minus
				if (this.isOpen && (this._children.length > 0)) {
					this.close();
				} else {
					//go to the parent, if any.
					var treeElm = this.parent;
					if (treeElm != null) {
						var name = this._tree._objectId + '_e_' + treeElm.id + '_href';
						var captionSpan = document.getElementsByName(name);
						if (captionSpan.length > 0) {
							captionSpan[1].focus();
						} else {
							name = this._tree._objectId + '_e_' + treeElm.id + '_caption2';
							captionSpan = document.getElementById(name);
							if (captionSpan) captionSpan.focus();
						}
					}
				}
				return false;
	    case 40: //cursor-down
				var treeElm = this.getNextVisibleElement();
				//treeElm.fireEvent('onClickCaption');
				//var captionSpan = document.getElementById(this._tree._objectId + '_e_' + treeElm.id + '_caption');
				//captionSpan.focus();
				if (treeElm != null) {
					var name = this._tree._objectId + '_e_' + treeElm.id + '_href';
					var captionSpan = document.getElementsByName(name);
					if (captionSpan.length > 0) {
						captionSpan[1].focus();
					} else {
						name = this._tree._objectId + '_e_' + treeElm.id + '_caption2';
						captionSpan = document.getElementById(name);
						if (captionSpan) captionSpan.focus();
					}
				}
				return false;
	    case 38: //cursor-up
				var treeElm = this.getPreviousVisibleElement();
				//treeElm.fireEvent('onClickCaption');
				//var captionSpan = document.getElementById(this._tree._objectId + '_e_' + treeElm.id + '_caption');
				//captionSpan.focus();
				if (treeElm != null) {
					var name = this._tree._objectId + '_e_' + treeElm.id + '_href';
					var captionSpan = document.getElementsByName(name);
					if (captionSpan.length > 0) {
						captionSpan[1].focus();
					} else {
						name = this._tree._objectId + '_e_' + treeElm.id + '_caption2';
						captionSpan = document.getElementById(name);
						if (captionSpan) captionSpan.focus();
					}
				}
				return false;
			case 32: //space
				if (this._tree.useCheckboxSystem) {
					switch (this.isChecked) {
						case 0:
							var newCheckboxValue = 2; break;
						case 2:
							var newCheckboxValue = 0; break;
						case 1:
							if (this._tree.checkboxSystemIfPartlyThenFull) {
								var newCheckboxValue = 2;
							} else {
								var newCheckboxValue = 0;
							}
							break;
					}
					this.setCheckboxValue(newCheckboxValue);
				} else if (this._tree.useRadioButton) {
					document.getElementById(this._tree.getRadioButtonName() + '_' + this.getDhtmlCompatibleId()).checked = true;
				}
				return false;
	  }
		return true;
	}
  
	
	/**
	* returns the previous visible element up the tree. can be a sibling or the parent.
	* @access public
	* @return object (instance of Bs_TreeElement)
	* @throws null
	* @see    getNextVisibleElement()
	* @since  bs-4.6
	*/
	this.getPreviousVisibleElement = function() {
		var treeElm = null;
		do {
      var sib = this.getSiblings();
      for (var i=0, n=sib.length; i<n; i++) {
        if (sib[i].id == this.id) {
					if (i > 0) {
						treeElm = sib[--i];
	
						if (treeElm.isOpen && treeElm.hasVisibleChildren()) {
							//alert(treeElm._children.length);
							treeElm = treeElm._children[treeElm._children.length -1];
						}
						break;
					}
        }
      }
			//if (treeElm != null) break;
			if (treeElm == null) {
				if (typeof(this.parent) == 'undefined') break;
				treeElm = this.parent;
			}
			
			/*
			//if (typeof(this.parent) != 'undefined') {
				//treeElm = this.parent;
				if (treeElm.isOpen && treeElm.hasVisibleChildren()) {
					//alert(treeElm._children.length);
					treeElm = treeElm._children[treeElm._children.length -1];
				}
				break;
			//}
			*/
		} while (false);
		return treeElm;
	}
	
	/**
	* returns the next visible element down the tree. can be a child, sibling, or 
	* sibling of a parent.
	* @access public
	* @return object (instance of Bs_TreeElement)
	* @throws null
	* @see    getPreviousVisibleElement()
	* @since  bs-4.6
	*/
	this.getNextVisibleElement = function() {
		var treeElm = null;
		do {
			if (this.isOpen && this.hasVisibleChildren()) {
				treeElm = this._children[0];
				break;
			}
			if (this.hasSiblingsDown()) {
	      var sib = this.getSiblings();
	      for (var i=0, n=sib.length; i<n; i++) {
	        if (sib[i].id == this.id) {
						treeElm = sib[++i];
						break;
	        }
	      }
				if (treeElm != null) break;
			}
			var elm = this.parent;
			do {
				var sib = elm.getSiblings();
	      for (var i=0, n=sib.length; i<n; i++) {
	        if (sib[i].id == elm.id) {
						treeElm = sib[++i];
						break;
	        }
	      }
				if (treeElm != null) break;
				if (typeof(elm.parent) == 'undefined') break;
				elm = elm.parent;
			} while (true);
		} while (false);
		return treeElm;
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
  * returns the value of a var. if the var is not defined in that element, 
  * the parent elements will be walked until something is found (if this.walkTree 
  * is set to true). in the end the tree object will be asked for the value. 
  * and if that fails aswell, null is returned.
  * 
  * note: it is a wise idea for the tree object to have a default value even if 
  *       nothing is set by the user.
  * 
  * @access private
  * @param  string varName
  * @see    this.walkTree
  */
  this._getVar = function(varName) {
    if (typeof(this[varName]) != 'undefined') {
      return this[varName];
    } else {
      if (this._tree.walkTree && (typeof(this.parent) != 'undefined')) {
        return this.parent._getVar(varName);
      } else if (typeof(this._tree[varName]) != 'undefined') {
        return this._tree[varName];
      } else {
        return null;
      }
    }
  }



  /**
  * built in mouseover effect to switch icons (+/- to open/close tree nodes).
  * also preloads the icons if they are not already.
  * @see this.onMouseOut()
  */
  this.onMouseOver = function() {
    var img = document.getElementById(this._spanId + 'icon');
    if (!img.swapOver0) {
      //load it
      img.swapOver0 = new Image();
      img.swapOver0.src = this.imgDir + 'enabled_0_over.gif';
      img.swapOver1 = new Image();
      img.swapOver1.src = this.imgDir + 'enabled_1_over.gif';
      img.swapOver2 = new Image();
      img.swapOver2.src = this.imgDir + 'enabled_2_over.gif';
      img.swapOut0 = new Image();
      img.swapOut0.src = this.imgDir + 'enabled_0.gif';
      img.swapOut1 = new Image();
      img.swapOut1.src = this.imgDir + 'enabled_1.gif';
      img.swapOut2 = new Image();
      img.swapOut2.src = this.imgDir + 'enabled_2.gif';
    }
    img.src = img['swapOver' + this.value].src;
  }
  
  /**
  * built in mouseover effect to switch icons
  * @see this.onMouseOver()
  */
  this.onMouseOut = function() {
    var img = document.getElementById(this._spanId + 'icon');
    img.src = img['swapOut' + this.value].src;
  }
  
  
  
  /* ******** checkbox system functions ******** */
  
	
	/**
  * @access public
  * @param  int value (the new value of the checkbox)
	* @param  bool fireEvents (default is true)
	* @param  bool doWalk     (default is true)
  * @return void
	* @see    var this.isChecked, this.checkboxEvent()
	* @since  bs4.4
  */
	this.setCheckboxValue = function(value, fireEvents, doWalk) {
		if (typeof(fireEvents) == 'undefined') fireEvents = true;
		if (typeof(doWalk)     == 'undefined') doWalk     = true;
    
    if (!this.hasChildren()) {
      //special case 1
      value = (value) ? 2 : 0;
    } else {
      if (this.isChecked == 0) { //was not checked
        if (this._tree.checkboxSystemWalkTree && (this._tree.checkboxSystemWalkTree != 2) && (this._tree.checkboxSystemWalkTree != 3) && this.hasChildren()) {
          value = 1;
        }
      }
    }
    
    this.isChecked = value;
    this._checkboxObject.setTo(value, true); //update the checkbox visually.
    
		if (fireEvents) {
	    if (this.hasEventAttached('onChangeCheckbox')) this.fireEvent('onChangeCheckbox');
		}
    
		if (doWalk) {
	    if ((this._tree.checkboxSystemWalkTree == 3) || (this._tree.checkboxSystemWalkTree == 1) || (this._tree.checkboxSystemWalkTree == 4)) {
	      this.parent.updateCheckboxFromChild();
	    }
	    if ((this._tree.checkboxSystemWalkTree == 3) || (this._tree.checkboxSystemWalkTree == 2) || ((this._tree.checkboxSystemWalkTree == 4) && (value == 0))) {
	      this.checkboxUpdateDown(value);
	    }
		}
	}
	
	
  /**
  * fires (from the Bs_Tree class) when one clicks on the checkbox.
  * uses the checkbox feature.
  * 
  * the tree may be walked up and/or down, see Bs_Tree.checkboxSystemWalkTree.
  * this checkbox, if selected, may be checked "partly" or "completely". 
  * if it was checked completely, then we uncheck it. if it was checked 
  * partly, what then? 2 options, deselect it or completely check it. 
  * what to do is defined in Bs_Tree.checkboxSystemIfPartlyThenFull. 
  * 
  * if the checkbox was not checked at all, then we can check it completely 
  * or just partly. what we do depends on Bs_Tree.checkboxSystemWalkTree. 
  * if we walk the tree somehow, that means there is a relation between the 
  * checkboxes and their nodes. so if we do, but we don't walk the tree down 
  * to activate all boxes, we check this box partly. because then the status 
  * is: this box checked somehow, below checked nothing. got that?
  * 
  * some special cases here. 
  *   1) if it's a leaf (has no children), there is no "partly" mode.
  *   2) if there is no tree-walking, there is no "partly" mode. every 
  *      checkbox/node works on it's own.
  *   3) if the checkbox is checked partly, and gets clicked, and there is 
  *      no walking-down-to-check, the checkbox will get unchecked no 
  *      matter what.
  * 
  * @access public
  * @param  int value (the new value of the checkbox)
  * @return void
	* @see    var this.isChecked
  */
  this.checkboxEvent = function(value) {
		
    /*
      *   0 = not checked
      *   1 = checked gray (part of the sub-elements are, part are not)
      *   2 = checked (this or everything below is checked)
    
      * 0 = no walking
      * 1 = walking up only
      * 2 = walking down only
      * 3 = walking both ways, up and down (default)
      * 4 = walking down to uncheck only, walking up for both (quite useful, an option to consider instead of 3.)
    */
    
    //alert(this.isChecked + ' ' + value);
    
    if (!this.hasChildren()) {
      //special case 1
      value = (value) ? 2 : 0;
    } else {
      if (this.isChecked == 1) { //was partly checked
        if ((!this._tree.checkboxSystemIfPartlyThenFull) || ((this._tree.checkboxSystemWalkTree) && (this._tree.checkboxSystemWalkTree != 2) && (this._tree.checkboxSystemWalkTree != 3))) {
          //special case 3
          value = 0;
        } else {
          value = 2;
        }
      } else if (this.isChecked == 0) { //was not checked
        if (this._tree.checkboxSystemWalkTree && (this._tree.checkboxSystemWalkTree != 2) && (this._tree.checkboxSystemWalkTree != 3) && this.hasChildren()) {
          value = 1;
        }
      }
    }
    
    this.isChecked = value;
    this._checkboxObject.setTo(value, true); //update the checkbox visually.
    
    
    if (this.hasEventAttached('onChangeCheckbox')) this.fireEvent('onChangeCheckbox');
    
    if ((this._tree.checkboxSystemWalkTree == 3) || (this._tree.checkboxSystemWalkTree == 1) || (this._tree.checkboxSystemWalkTree == 4)) {
      this.parent.updateCheckboxFromChild();
    }
    if ((this._tree.checkboxSystemWalkTree == 3) || (this._tree.checkboxSystemWalkTree == 2) || ((this._tree.checkboxSystemWalkTree == 4) && (value == 0))) {
      this.checkboxUpdateDown(value);
    }
  }
  
  /**
  * un/checks the children of this checkbox recursively. fires after the 
  * checkboxEvent().
  * @access public
  * @param  bool value
  * @return void
  */
  this.checkboxUpdateDown = function(value) {
    for (var i=0; i<this._children.length; i++) {
      this._children[i]._updateCheckboxFromParent(value, true);
    }
  }
  
  
  /**
  * also updates the internal value of the checkbox object.
  */
  this.updateCheckboxVisually = function() {
    /*
    var c = document.getElementById(this.checkboxName);
    if (c) {
      c.checked = this.isChecked;
      //c.style.background = "Silver";
    }
    */
    if (typeof(this._checkboxObject) == 'object') {
      try {
        //here we have a problem if a tree is not opened anymore, but it has been. 
        //an exception occures cause this object does not exist anymore.
        //there's nothing more to do, everything works fine afaik.
        this._checkboxObject.setTo(this.isChecked);
      } catch (e) {
      }
    }
  }
  
  
  /**
  * @access private
  * @param  int  newValue
  * @param  bool recursiveDown
  */
  this._updateCheckboxFromParent = function(newValue, recursiveDown) {
    var backupValue = this.isChecked;
    this.isChecked = (newValue) ? 2 : 0;
    
    var hasChanged = (this.isChecked != backupValue);
    if (hasChanged) {
      this.updateCheckboxVisually();
      if (this.hasEventAttached('onChangeCheckbox')) this.fireEvent('onChangeCheckbox');
    }
    
    //i really think we should do that even if there was no change here.
    if (recursiveDown) this.checkboxUpdateDown(newValue, true);
  }
  
  
  /**
  * fires after a checkbox of a child element has been changed. 
  * @access public
  * @return void
  */
  this.updateCheckboxFromChild = function() {
    var backupIsChecked = this.isChecked;
    
    var numYes   = 0;
    var numNo    = 0;
    var isPartly = false;
    for (var i=0, n=this._children.length; i<n; i++) {
      if (this._children[i].isChecked == 1) {
        isPartly = true;
        this.isChecked = 1;
        break; //already done.
      } else if (this._children[i].isChecked) { // (==2)
        numYes++;
      } else {
        numNo++;
      }
      if ((numYes > 0) && (numNo > 0)) { //this is an optimization.
        //we already know that part of all are selected.
        break;
      }
    }
    if (!isPartly) {
      if ((numYes > 0) && (numNo > 0)) {
        this.isChecked = 1;
      } else if (numYes > 0) {
        this.isChecked = 2;
      } else {
        this.isChecked = 0;
      }
    }
    
    if (backupIsChecked != this.isChecked) {
      this.updateCheckboxVisually();
      if (this.hasEventAttached('onChangeCheckbox')) this.fireEvent('onChangeCheckbox');
    }
    
    if (typeof(this.parent) == 'object') {
      this.parent.updateCheckboxFromChild();
    }
  }
  
	
	/**
	* helper method that updates the 'parent' and '_level' data all children down.
	* calls itself recursively.
	* @access private
	* @param  object treeElement
	* @return void
	*/
	this._updateLevelAndParent = function(treeElement) {
		if ((typeof(treeElement._children) == 'object') && (treeElement._children.length > 0)) {
			for (var i=0, n=treeElement._children.length; i<n; i++) {
				treeElement._children[i].parent = treeElement;
				treeElement._children[i]._level = treeElement._level +1;
				this._updateLevelAndParent(treeElement._children[i]);
			}
		}
	}
	
	
	/**
	* @access private
	* @return string (may be empty)
	* @see    vars this.linkStyle, Bs_TreeElement.linkStyle
	* @since  bs4.4
	*/
	this._getLinkStyle = function() {
		if (typeof(this.linkStyle)       != 'undefined') return this.linkStyle;
		if (typeof(this._tree.linkStyle) != 'undefined') return this._tree.linkStyle;
		return '';
	}
	
	
	/**
	* replace all not a-z A-Z 0-9 chars with an underscore.
	* @access private
	* @var    string checkboxName
	* @return string
	*/
	this._cleanCheckboxName = function(checkboxName) {
		//var myReg = new RegExp("(src|href)\\s*=\\s*([\"']?)http:\/\/" + host + "", "gim");
		//ret = ret.replace(myReg, "$1=$2");
		var ret = '';
		for (var i=0; i<checkboxName.length; i++) {
			var chr = checkboxName.charCodeAt(i);
			if ((chr < 48) || (chr > 57 && chr < 65) || (chr > 90 && chr < 97) || (chr > 122)) {
				ret += '_';
			} else {
				ret += checkboxName.substr(i, 1);
			}
		}
		return ret;
	}
	
	
	/**
	* if the given string has one of the extensions 'gif', 'png', 'jpg' or 'jpeg' then 
	* this returns true, false otherwise.
	* @access private
	* @param  string iconStr
	* @return bool
	*/
	this._iconHasExtension = function(iconStr) {
		var iconLower = iconStr.toLowerCase();
		var iconPos   = iconLower.lastIndexOf('.');
		if (iconPos > -1) {
			var iconExt = iconLower.substr(iconPos +1);
			if ((iconExt != 'gif') && (iconExt != 'png') && (iconExt != 'jpg') && (iconExt != 'jpeg')) {
				return false;
			}
		} else {
			return false;
		}
		return true;
  }
	
	
	/**
	* if the given string has one of the extensions 'gif', 'png', 'jpg' or 'jpeg' then 
	* this returns true, false otherwise.
	* @access private
	* @param  string iconStr
	* @return bool
	*/
	this._iconHasPath = function(iconStr) {
		if (iconStr.indexOf('://') > -1) return true;
		if (iconStr.substr(0, 1) == '/') return true;
		return false;
	}
	
  
}