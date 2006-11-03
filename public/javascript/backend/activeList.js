//alert('OK');

function copyPrototype(descendant, parent) { 
    var sConstructor = parent.toString(); 
    var aMatch = sConstructor.match( /\s*function (.*)\(/ ); 
    if ( aMatch != null ) 
	{ 
	  	descendant.prototype[aMatch[1]] = parent; 
	} 
    for (var m in parent.prototype) 
	{ 
        descendant.prototype[m] = parent.prototype[m]; 
    } 
}; 

function activeList()
{
	
}

activeList.prototype.create = function (listIdParam)
{

	/**
	 * List identification string (e.g. languageList)
	 */ 
	this.listId = listIdParam;

	/**
	 * ID of the last (currently) dragged item
	 */ 
	this.draggedId = 0;
	
	/**
	 * HTML code of the dragged item (needs to be cached as feedback graphics element is displayed when reordering)
	 */ 
	this.draggedItemHtml = new Array();

	this.createSortable();  
  
}

activeList.prototype.getDeleteUrl = function (id)
{	  
	alert('getDeleteUrl() must be implemented');
}  

activeList.prototype.getEditUrl = function (id)
{	  
	alert('getEditUrl() must be implemented');
}  
  
activeList.prototype.getSortUpdateUrl = function(order)
{	  
	alert('getSortUpdateUrl() must be implemented');
}  

/* All methods below are considered as final */		 

/**
 * Initialize Scriptaculous Sortable on the list
 */ 
activeList.prototype.createSortable = function ()
{
	handler = this.getInstanceName();
	
	Sortable.create(this.listId, 
					{
				// the object context mystically dissapears when onComplete function is called,
				// so the only way I could make it work is this
					  onChange: function(param) { eval("inst = " + handler +";"); inst.registerDraggedItem(param);},
					  onUpdate: function(param) { eval("inst = " + handler +";"); inst.saveSortOrder(param);}
					}
					);	  
}

/**
 * Initiates item order (position) saving action
 *
 * @param string id Record ID
 * @access public
 */
activeList.prototype.saveSortOrder = function()
{			
	var item = this.draggedId;
	
	// there may be more than one dragging operation in progress, so save the item only once
	if (false == this.draggedItemHtml[item] || undefined == this.draggedItemHtml[item])
	{
		this.draggedItemHtml[item] = document.getElementById(this.getFullId(item)).innerHTML;
	}
	
	// display feedback
	this.displayProgress(item);			

	order = Sortable.serialize(this.listId);
	url = this.getSortUpdateUrl(order);						
				
	// execute the action
	handler = this.getInstanceName();
	var sortSaveAction = new Ajax.Request(
			url, 
			{
				method: 'get', 
				// the object context mystically dissapears when onComplete function is called,
				// so the only way I could make it work is this
				onComplete: function(param) { eval("inst = " + handler +";"); inst.restoreDraggedItem(param);}
			});
	
	deselectText();
}

/**
 * Initiates item removal action
 *
 * @param string id Record ID
 * @access public
 */
activeList.prototype.deleteItem = function(id)
{
	
	function doRemove (param)
	{

		  inst = this.getInstance();
	  	alert('went');
		inst.removeListItem(param);
	  	alert('ok');
	}	
	
	if (!confirm('Are you sure you wish to remove this language?'))
		return false;
	
	// get delete action URL	  	
	url = this.getDeleteUrl(id);

	// display feedback
	this.displayProgress(id);

	// execute the action
	handler = this.getInstanceName();
	var delAction = new Ajax.Request(
			url, 
			{
				method: 'get', 

				// the object context mystically dissapears when onComplete function is called,
				// so the only way I could make it work is this
				onComplete: function(param) { eval("inst = " + handler +";"); inst.removeListItem(param);}
			});
}

/**
 * Returns instance variable name
 *
 * @return string Instance name
 * @access private
 */
activeList.prototype.getInstanceName = function()
{
	return this.listId + "HandlerInstance";
}

/**
 * Registers which item is currently being dragged
 *
 * @param HTMLElement element
 * @access public
 */
activeList.prototype.registerDraggedItem = function(elementObj)
{	
	this.draggedId = this.getRecordId(elementObj.id);	
}		
		
/**
 * Displays list item menu (and hides other list item menus)
 *
 * @param HTMLElement element
 * @access public
 */
activeList.prototype.showMenu = function(element)
{
    // first hide all other menus
	for (k = 0; k < element.parentNode.childNodes.length; k++)
	{
		this.hideMenu(element.parentNode.childNodes.item(k));
	}

	// now show the needed menu
	element.firstChild.style.visibility = 'visible';
}

/**
 * Hides list item menu
 *
 * @param HTMLElement element
 * @access public
 */
activeList.prototype.hideMenu = function(element)
{
  	element.firstChild.style.visibility = 'hidden';
}

/**
 * Removes item from list after receiving AJAX response 
 * (responseText is expected to contain record ID on success or blank on failure)
 *
 * @param XMLHttpRequest ajaxRequest
 * @access private
 */
activeList.prototype.removeListItem = function(ajaxRequest)
{
	id = ajaxRequest.responseText;
	if ('' == id)
	{
	  	return false;
	}
	
	itemId = this.listId + '_' + id;
	
	// @todo - add effect
	document.getElementById(itemId).remove();
}

/**
 * Keyboard access functionality 
 * 	- navigate list using up/down arrow keys
 * 	- move items up/down using Shift + up/down arrow keys
 * 	- delete items with Del key
 * 	- drop focus ("exit" list) with Esc key
 *
 * @param e Event
 * @param sender HTMLElement
 * @access public		 
 * @todo Edit items with Enter key
 */
activeList.prototype.navigate = function(e, sender)
{
    // IE
	if (window.event) 
    {
    	keynum = e.keyCode;
    }

    // Netscape/Firefox/Opera
	else if (e.which) 
    {
    	keynum = e.which;
    }
    
    // determine if Shift key is pressed
	isShift = e.shiftKey;
    
    // move/navigate up
	if (KEY_UP == keynum)
    {
        this.getPrevSibling(sender).focus();

        if (isShift)
        {
            prev = this.getPrevSibling(sender);
            
            if (prev == prev.parentNode.lastChild)
            {
			  	insSib = null;
			}
			else
			{
			  	insSib = prev;
			}
            
			this.moveNode(sender, insSib);		        
		}
    }
    
	// move/navigate down
	else if (KEY_DOWN == keynum)
    {
        this.getNextSibling(sender).focus();

        if (isShift)
        {
            next = this.getNextSibling(sender);
            
            if (next == next.parentNode.firstChild)
            {
			  	insSib = next;					  
			}
			else
			{
			  	insSib = next.nextSibling;
			}
        	
			this.moveNode(sender, insSib);
		}
    }

    // delete
	else if (KEY_DEL == keynum)
    {
		this.getPrevSibling(sender).focus();
		this.deleteItem(this.getRecordId(sender.id));      
    }
    
    // escape - lose focus
	else if (KEY_ESC == keynum)
    {
        sender.blur();
    }
    else
    {
    
    //alert(keynum);
    
  }

	deselectText();
//	this.createSortable();    
}

/**
 * Moves list node
 *
 * @param Node node Node being moved
 * @param Node beforeNode Inserted before this node
 * @access private
 */
activeList.prototype.moveNode = function(node, beforeNode)
{
    this.registerDraggedItem(node);
    node.parentNode.insertBefore(node, beforeNode);
    node.focus();
	this.saveSortOrder();
}

/**
 * Gets next sibling for element in node list.
 * If the element is the last node, the first node is being returned
 *
 * @param Node element Element instance
 * @return Node Next sibling
 * @access private
 */
activeList.prototype.getNextSibling = function(element)
{
  	if (!element.nextSibling)
  	{
	    return element.parentNode.firstChild;
	}			
	else 
	{
	  	return element.nextSibling;
	}
}

/**
 * Gets previous sibling for element in node list.
 * If the element is the first node, the last node is being returned
 *
 * @param Node element Element instance
 * @return Node Previous sibling
 * @access private
 */
activeList.prototype.getPrevSibling = function(element)
{
  	if (!element.previousSibling)
  	{
	    return element.parentNode.lastChild;
	}			
	else 
	{
	  	return element.previousSibling;
	}
}

/**
 * @param string id Record ID (e.g. 1)
 * @return string Element ID (e.g. record_1)
 * @access private
 */
activeList.prototype.getFullId = function(id)
{
	return this.listId + '_' + id;
}

/**
 * Displays progress indicator bar for list element
 * @param string id Record ID (e.g. 1)
 * @access private
 */
activeList.prototype.displayProgress = function(id)
{
	progressIndicatorId = this.listId + '_progress_' + id;
	document.getElementById(progressIndicatorId).innerHTML = '<img src="image/backend/list/indicator_bar_small.gif" />';
}

/**
 * Returns record ID from element ID
 *
 * @param string fullId
 * @return string Record ID
 * @access private
 */
activeList.prototype.getRecordId = function(fullId)
{
	return fullId.substr(this.listId.length + 1, this.listId.length);  
}	

/**
 * Restore dragged item to initial state after saving the order 
 *
 * @param XMLHttpRequest originalRequest
 * @access private
 */
activeList.prototype.restoreDraggedItem = function(originalRequest)
{
	item = originalRequest.responseText;
	
	// there may be more than one dragging operation in progress, so restore the item only once
	if (false != this.draggedItemHtml[item])
	{
		document.getElementById(this.getFullId(item)).innerHTML = this.draggedItemHtml[item];
		this.draggedItemHtml[item] = false;
	}
	
	// sometimes text gets selected when items are dragged around - perhaps there are nicer ways to handle this
	deselectText();

	// items are restored with their menus, so to avoid several menus 
	// being displayed at once, we'll redraw the menu (and so hide the other menus)
	this.showMenu(document.getElementById(this.getFullId(item)));
}