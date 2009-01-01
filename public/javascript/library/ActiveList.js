/**
 * ActiveList
 *
 * Sortable list
 *
 * @example
 * <code>
 * <ul id="specField_items_list" class="activeList_add_sort activeList_add_edit activeList_add_delete">
 *	<li id="specField_items_list_96" class="">Item 1</li>
 *	<li id="specField_items_list_95"  class="">Item 2</li>
 *	<li id="specField_items_list_100" class="activeList_remove_sort">Item 3</li>
 *	<li id="specField_items_list_101" class="">Item 4</li>
 *	<li id="specField_items_list_102" class="">Item 5</li>
 * </ul>
 *
 * <script type="text/javascript">
 *	 new ActiveList('specField_items_list', {
 *		 beforeEdit:	 function(li)
 *		 {
 *			 if(this.isContainerEmpty()) return 'edit.php?id='+this.getRecordId(li)
 *			 else his.toggleContainer()
 *		 },
 *		 beforeSort:	 function(li, order) { return 'sort.php?' + order },
 *		 beforeDelete:   function(li)
 *		 {
 *			 if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?')) return 'delete.php?id='+this.getRecordId(li)
 *		 },
 *		 afterEdit:	  function(li, response) { this.getContainer(li, 'edit').innerHTML = response; this.toggleContainer();  },
 *		 afterSort:	  function(li, response) { alert( 'Record #' + this.getRecordId(li) + ' changed position'); },
 *		 afterDelete:	function(li, response)  { this.remove(li); }
 *	 });
 * </script>
 * </code>
 *
 * First argument passed to active list constructor is list id, and the second is hash object of callbacks
 * Events in active list will automatically call two actions one before ajax request to server and one after.
 * Those callbacks which are called before the request hase "before" prefix. Those which will be called after - "after".
 *
 * Functions which are called before request must return a link or a false value. If a link returned then
 * request to that link is made. On the other hand if false is returned then no request is send and "after" function
 * is not called. This is useful for caching.
 *
 * Note that there are some usefful function you can use inside your callbacks
 * this.isContainerEmpty() - Returns if container is empty
 * this.getRecordId(li) - Get real item's id (used to identify that item in database)
 * this.getContainer() - Get items container. Also every action has it's own container
 *
 * There are also some usefull variables available to you in callback
 * this - A reference to ActiveList object.
 * li - Current item
 * order - Serialized order
 * response - Ajax response text
 *
 * @author   Integry Systems
 *
 */
if (LiveCart == undefined)
{
	var LiveCart = {}
}

ActiveList = Class.create();
ActiveList.prototype = {
	/**
	 * Item icons which will apear in top left corner on each item of the list
	 *
	 * @var Hash
	 */
	icons: {
		'sort':	 "image/silk/arrow_switch.png",
		'edit':	 "image/silk/pencil.png",
		'delete':   "image/silk/cancel.png",
		'view':	 "image/silk/zoom.png",
		'progress': "image/indicator.gif"
	},

	/**
	 * User obligated to pass this callbacks to constructor when he creates
	 * new active list.
	 *
	 * @var array
	 */
	requiredCallbacks: [],

	/**
	 * When active list is created it depends on automatically generated html
	 * content.That means that active list uses class names to find icons and
	 * containers in list. Be sure you are using unique prefix
	 *
	 * @var string
	 */
	cssPrefix: 'activeList_',

	/**
	 * List order is send back only if last sort accured more then M milliseconds ago.
	 * M is that value
	 *
	 * @var int
	 */
	keyboardSortTimeout: 1000,

	/**
	 * Tab index of every active list element. Most of the time this value is not important
	 * so any would work fine
	 *
	 * @var int
	 */
	tabIndex: 666,

	/**
	 * The alpha level of menu when it is hidden
	 *
	 * @var double [0,1]
	 */
	visibleMenuOpacity: 1,

	/**
	 * The alpha level of menu when it is visible
	 *
	 * @var double [0,1]
	 */
	hiddenMenuOpacity: 0.15,

	activeListsUsers: {},

	messages: {},

	/**
	 * Constructor
	 *
	 * @param string|ElementUl ul List id field or an actual reference to list
	 * @param Hash callbacks Function which will be executed on various events (like sorting, deleting editing)
	 *
	 * @access public
	 */
	initialize: function(ul, callbacks, messages)
	{
		this.ul = $(ul);

		if(!this.ul)
		{
			throw Error('No list found');
			return false;
		}

		this.messages = messages;

		if (!this.ul.id)
		{
			Backend.setUniqueID(this.ul);
		}

		Element.addClassName(this.ul, this.ul.id);

		// Check if all required callbacks are passed
		var missedCallbacks = [];
		for(var i = 0; i < this.requiredCallbacks.length; i++)
		{
			var before = ('before-' + this.requiredCallbacks[i]).camelize();
			var after = ('after-' + this.requiredCallbacks[i]).camelize();

			if(!callbacks[before]) missedCallbacks[missedCallbacks.length] = before;
			if(!callbacks[after]) missedCallbacks[missedCallbacks.length] = after;
		}

		if(missedCallbacks.length > 0)
		{
				throw Error('Callback' + (missedCallbacks.length > 1 ? 's' : '') + ' are missing (' + missedCallbacks.join(', ') +')' );
				return false;
		}

		this.callbacks = callbacks;
		this.dragged = false;

		this.generateAcceptFromArray();
		this.createSortable();
		this.decorateItems();
	},

	/**
	 * Get active list singleton. If ul list is allready an ActiveList then use it's instance. In other case create new instance
	 *
	 * @param HTMLUlElement ul
	 * @param object callbacks
	 * @param object messages
	 */
	getInstance: function(ul, callbacks, messages)
	{
		var ulElement = $(ul);

		// fix list ID if it was set as numeric only
		if (!isNaN(parseInt(ulElement.id)))
		{
			var id = ulElement.id;
			Backend.setUniqueID(ulElement);
			ulElement.id += '_' + id;
		}

		if (!ulElement.id)
		{
			Backend.setUniqueID(ulElement);
		}

		if(!ActiveList.prototype.activeListsUsers[ulElement.id])
		{
			ActiveList.prototype.activeListsUsers[ulElement.id] = new ActiveList(ulElement.id, callbacks, messages);
		}

		return ActiveList.prototype.activeListsUsers[ulElement.id];
	},

	/**
	 * Destroy active list object associated with given list
	 *
	 * @param HTMLUlElement ul	destroy: function(ul)
	 */
	destroy: function(ul)
	{
	   var id = ul.id ? ul.id : ul;

	   if(ActiveList.prototype.activeListsUsers[id])
	   {
		   delete this.activeListsUsers[id];
	   }
	},

	destroySortable: function()
	{
	   if(this.isSortable)
	   {
		   Sortable.destroy(this.ul);
		   this.isSortable = false;
		   $A(this.acceptFromLists).each(function(ul)
		   {
			   if(ActiveList.prototype.activeListsUsers[ul.id])
			   {
				   ActiveList.prototype.activeListsUsers[ul.id].isSortable = false;
				   var s = Sortable.options(ul);

 				   if(s)
				   {
					  Draggables.removeObserver(s.element);
					  s.draggables.invoke('destroy');
				   }
			   }
		   });
	   }
	},

	makeStatic: function()
	{
	   Sortable.destroy(this.ul);
	   Element.removeClassName(this.ul, 'activeList_add_sort')
	   document.getElementsByClassName('activeList_icons', this.ul).each(function(iconContainer)
	   {
		   iconContainer.hide();
		   iconContainer.style.visibility = 'hidden';
	   });
	},


	makeActive: function()
	{
	   Sortable.create(this.ul);
	   Element.addClassName(this.ul, 'activeList_add_sort')
	   document.getElementsByClassName('activeList_icons', this.ul).each(function(iconContainer)
	   {
		   iconContainer.show();
		   iconContainer.style.visibility = 'visible';
	   });
	},


	/**
	 * Split list by odd and even active records by adding ActiveList_odd or ActiveList_even to each element
	 */
	colorizeItems: function()
	{
		var liArray = this.ul.getElementsByTagName("li");

		var k = 0;
		for(var i = 0; i < liArray.length; i++)
		{
			if(this.ul == liArray[i].parentNode && !Element.hasClassName(liArray[i], 'ignore') && !Element.hasClassName(liArray[i], 'dom_template'))
			{
				this.colorizeItem(liArray[i], k);
				k++;
			}
		}
	},

	/**
	 * Adds classes ActiveList_odd and ActiveList_even to separate odd elements from even
	 *
	 * @param HtmlElementLi A reference to item element. Default is current item
	 * @param {Object} position Element position in ActiveList
	 */
	colorizeItem: function(li, position)
	{
		Element.addClassName(li, 'activeList');

		if(position % 2 == 0)
		{
			Element.removeClassName(li, this.cssPrefix + "odd");
			Element.addClassName(li, this.cssPrefix + "even");
		}
		else
		{
			Element.removeClassName(li, this.cssPrefix + "even");
			Element.addClassName(li, this.cssPrefix + "odd");
		}
	},

	/**
	 * Toggle item container On/Off
	 *
	 * @param HtmlElementLi A reference to item element. Default is current item
	 * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
	 *
	 * @access public
	 */
	toggleContainer: function(li, action, highlight)
	{
		var container = this.getContainer(li, action);

		if(container.style.display == 'none')
		{
			this.toggleContainerOn(container, highlight);
		}
		else
		{
			this.toggleContainerOff(container, highlight);
			Element.removeClassName(li, action + '_inProgress');
		}
	},

	/**
	 * Expand data container
	 *
	 * @param HTMLElementDiv container Reference to the container
	 */
	toggleContainerOn: function(container, highlight)
	{
		container = $(container);
		ActiveList.prototype.collapseAll();

		Sortable.destroy(this.ul);
		// Destroy parent sortable as well
		var parentList = this.ul.up(".activeList");
		if(parentList && ActiveList.prototype.activeListsUsers[parentList.id])
		{
		   ActiveList.prototype.activeListsUsers[parentList.id].destroySortable(true);
		}

		if(BrowserDetect.browser != 'Explorer')
		{
			Effect.BlindDown(container, { duration: 0.5 });
			Effect.Appear(container, { duration: 1.0 });
			setTimeout(function() {
				container.style.height = 'auto';
				container.style.display = 'block';

				if(highlight) this.highlight(container.up('li'), highlight);
			}.bind(this), 300);
		}
		else
		{
			container.style.display = 'block';
			if(highlight) this.highlight(container.up('li'), highlight);
		}

		Element.addClassName(container.up('li'), this.cssPrefix  + this.getContainerAction(container) + '_inProgress');
	},

	/**
	 * Collapse data container
	 *
	 * @param HTMLElementDiv container Reference to the container
	 */
	toggleContainerOff: function(container, highlight)
	{
		var container = $(container);
		this.createSortable(true);

		// Create parent sortable as well
		var parentList = this.ul.up(".activeList");
		if(parentList && ActiveList.prototype.activeListsUsers[parentList.id])
		{
		   ActiveList.prototype.activeListsUsers[parentList.id].createSortable(true);
		}

		if(BrowserDetect.browser != 'Explorer')
		{
			Effect.BlindUp(container, {duration: 0.2});
			setTimeout(function() {
				container.style.display = 'none';
				if(highlight) this.highlight(container.up('li'), highlight);
			}.bind(this), 40);
		}
		else
		{
			container.style.display = 'none';
			if(highlight) this.highlight(container.up('li'), highlight);
		}

		Element.removeClassName(container.up('li'), this.cssPrefix  + this.getContainerAction(container) + '_inProgress');
	},

	getContainerAction: function(container)
	{
		var matches = container.className.match(/activeList_(\w+)Container/);
		if (matches)
		{
			return matches[1];
		}
	},

	/**
	 * Check if item container is empty
	 *
	 * @param HtmlElementLi A reference to item element. Default is current item
	 * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
	 *
	 * @access public
	 *
	 * @return bool
	 */
	isContainerEmpty: function(li, action)
	{
		return this.getContainer(li, action).firstChild ? false : true;
	},

	/**
	 * Get item container
	 *
	 * @param HtmlElementLi A reference to item element. Default is current item
	 * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
	 *
	 * @access private
	 *
	 * @return ElementDiv A refference to container node
	 */
	getContainer: function(li, action)
	{
		if(!li) li = this._currentLi;

		return document.getElementsByClassName(this.cssPrefix + action + 'Container' , li)[0];
	},

	/**
	 * Get item's id. Not as a dom element but real id, which is used id database
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access public
	 *
	 * @return string element id
	 */
	getRecordId: function(li, level)
	{
		if(!level) level = 1;
		var matches = li.id.match(/_([a-zA-Z0-9]*)(?=(?:_|\b))/g);

		var id = matches[matches.length-level];
		return id ? id.substr(1) : false;
	},

	/**
	 * Rebind all icons in item
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access public
	 */
	rebindIcons: function(li)
	{
		var self = this;
		$A(this.ul.className.split(' ')).each(function(className)
		{
			//var container = document.getElementsByClassName(self.cssPrefix + 'icons', li)[0];
			var container = li.iconContainer;

			var regex = new RegExp('^' + self.cssPrefix + '(add|remove)_(\\w+)(_(before|after)_(\\w+))*');
			var tmp = regex.exec(className);

			if(!tmp) return;

			var icon = {};
			icon.type = tmp[1];
			icon.action = tmp[2];
			icon.image = self.icons[icon.action];
			icon.position = tmp[4];
			icon.sibling = tmp[5];

			if(icon.action != 'sort')
			{
				li[icon.action + 'Container'] = document.getElementsByClassName(self.cssPrefix + icon.action + 'Container', li)[0];
			}
		});

		li.prevParentId = this.ul.id;
	},

	/**
	 * Add new item to Active Record. You have 3 choices. Either to add whole element, add array of elements or add all elements
	 * inside given dom element
	 *
	 * @param int id Id of new element (Same ID which is stored in database)
	 * @param HTMLElement|array dom Any HTML Dom element or array array of Dom elements
	 * @param bool insights Use elements inside of given node
	 *
	 * @access public
	 *
	 * @return HTMLElementLi Reference to new active list record
	 */
	addRecord: function(id, dom, touch, noClone)
	{
		var li = document.createElement('li');
		li.id = this.ul.id + "_" + id;
		this.ul.appendChild(li);

		if(typeof dom == 'string')
		{
			li.update(dom);
		}
		else if (dom[0])
		{
			for(var i = 0; i < dom.length; i++)
			{
				var cloned_dom = dom[i].cloneNode(true);
				while(cloned_dom.childNodes.length > 0) li.appendChild(cloned_dom.childNodes[0]);
			}
		}
		else
		{
			var cloned_dom = noClone ? dom : dom.cloneNode(true);
			while(cloned_dom.childNodes.length > 0) li.appendChild(cloned_dom.childNodes[0]);
			li.className = dom.className;
		}

		this.decorateLi(li);
		this.colorizeItem(li, this.ul.childNodes.length);

		if(touch || touch === undefined)
		{
			this.highlight(li, 'yellow');
			this.touch(true);
		}

		return li;
	},

	updateRecord: function(oldLi, newLi)
	{
	  	oldLi.parentNode.replaceChild(newLi, oldLi);
		this.decorateLi(newLi);
		this.colorizeItem(newLi, this.ul.childNodes.length);
		this.rebindIcons(newLi);

		this.highlight(newLi, 'yellow');
		this.touch(true);
	},

	highlight: function(li, color)
	{
		if(!li) li = this._currentLi;
		li = $(li);

		switch(color)
		{
			case 'red':
				new Effect.Highlight(li, {startcolor:'#FFF1F1', endcolor:'#F5F5F5'});
				break;
			case 'pink':
				new Effect.Highlight(li, {startcolor:'#FFF7F7', endcolor:'#FBFBFB'});
				break;
			case 'yellow':
			default:
				new Effect.Highlight(li, {startcolor:'#FBFF85', endcolor:'#F5F5F5'});
				break;
		}

	   setTimeout(function(li)
	   {
		   var textInput = li.down("input[@type=text]");
		   if(textInput)
		   {
				try
				{
					textInput.focus();
				}
				catch (e)
				{
					return false;
				}
		   }
	   }.bind(this, li), 600);
	},


	/***************************************************************************
	 *		   Private methods											   *
	 ***************************************************************************/

	/**
	 * Go throug all list elements and decorate them with icons, containers, etc
	 *
	 * @access private
	 */
	decorateItems: function()
	{

		// This fixes some strange explorer bug/"my stypidity"
		// Basically, what is happening is thet when I push edit button (pencil)
		// on first element, everything just dissapears. All other elements
		// are fine though. To fix this I am adding an hidden first element
		var liArray = this.getChildList();
		for(var i = 0; i < liArray.length; i++)
		{
			this.decorateLi(liArray[i]);
			this.colorizeItem(liArray[i], i);
		}
	},

	/**
	 * Decorate list element with icons, progress bar, container, tabIndex, etc
	 *
	 * @param HtmlElementLi Element to decorate
	 *
	 * @access private
	 */
	decorateLi: function(li)
	{
		var self = this;

		// fix li ID if it was set as numeric only
		if (!isNaN(parseInt(li.id)))
		{
			li.id = li.parentNode.id + '_' + li.id;
		}

		// Bind events
		Event.observe(li, "mouseover", function(e) { self.showMenu(this) });
		Event.observe(li, "mouseout",  function(e) { self.hideMenu(this) });

		// Create icons container. All icons will be placed incide it
		if(!li.iconContainer)
		{
			var iconsDiv = document.createElement('span');
			Element.addClassName(iconsDiv, self.cssPrefix + 'icons');
			li.insertBefore(iconsDiv, li.firstChild);
			li.iconContainer = iconsDiv;

			// add all icons
			$A(this.ul.className.split(' ')).each(function(className)
			{
				// If icon is not progress and it was added to a whole list or only this item then put that icon into container
				self.addIconToContainer(li, className);
			});

			// progress is not a div like all other icons. It has no fixed size and is not clickable.
			// This is done to properly handle animated images because i am not sure if all browsers will
			// handle animated backgrounds in the same way. Also differently from icons progress icon
			// can vary in size while all other icons are always the same size
			iconProgress = document.createElement('img');
			iconProgress.src = this.icons.progress;

			if (this.messages && this.messages._activeList_progress)
			{
				iconImage.alt = this.messages._activeList_progress;
			}

			if (this.messages && this.messages._activeList_progress)
			{
				iconImage.title = iconImage.alt = this.messages._activeList_progress;
			}

			iconProgress.style.visibility = 'hidden';

			Element.addClassName(iconProgress, self.cssPrefix + 'progress');
			iconsDiv.appendChild(iconProgress);


			li.progress = iconProgress;
			li.prevParentId = this.ul.id;
		}
	},

	/**
	 * Add icon to container according to active list classes current record classes
	 *
	 * @param HtmlElementLi Element
	 * @param string className ActiveList(ul) classes separated by space
	 */
	addIconToContainer: function(li, className)
	{
		var container = li.iconContainer;

		var regex = new RegExp('^' + this.cssPrefix + '(add|remove)_(\\w+)(_(before|after)_(\\w+))*');
		var tmp = regex.exec(className);

		if(!tmp) return;

		var icon = {};

		icon.type = tmp[1];
		icon.action = tmp[2];
		icon.image = this.icons[icon.action];
		icon.position = tmp[4];
		icon.sibling = tmp[5];

		if(icon.action == 'accept') return true;

		if(icon.action != 'sort')
		{
			var iconImage = document.createElement('img');

			iconImage.src = icon.image;
			if(this.messages && this.messages['_activeList_' + icon.action])
			{
				iconImage.title = iconImage.alt = this.messages['_activeList_' + icon.action];
			}

			Element.addClassName(iconImage, this.cssPrefix + icon.action);
			Element.addClassName(iconImage, this.cssPrefix + 'icons_container');

			// If icon is removed from this item than do not display the icon
			if((Element.hasClassName(li, this.cssPrefix + 'remove_' + icon.action) || !Element.hasClassName(this.ul, this.cssPrefix + 'add_' + icon.action)) && !Element.hasClassName(li, this.cssPrefix + 'add_' + icon.action))
			{
				iconImage.style.display = 'none';
			}

			// Show icon
			container.appendChild(iconImage);
			iconImage.setOpacity(this.hiddenMenuOpacity);
			li[icon.action] = iconImage;

			Event.observe(iconImage, "mousedown", function(e) { Event.stop(e) }.bind(this));
			Event.observe(iconImage, "click", function() { this.bindAction(li, icon.action) }.bind(this));

			// Append content container
			if('delete' != icon.action && !this.getContainer(li, icon.action))
			{
				var contentContainer = document.createElement('div');
				contentContainer.style.display = 'none';
				Element.addClassName(contentContainer, this.cssPrefix + icon.action + 'Container');
				Element.addClassName(contentContainer, this.cssPrefix + 'container');
				contentContainer.id = this.cssPrefix + icon.action + 'Container_' + li.id;
				li.appendChild(contentContainer);
				li[icon.action + 'Container'] = contentContainer;
			}
		}
	},

	/**
	 * This function executes user specified callback. For example if action was
	 * 'delete' then the beforeDelete function will be called
	 * which should return a valud url adress. After that when AJAX response has
	 * arrived the afterDelete function will be called
	 *
	 * @param HtmlElementLi A reference to item element
	 * @param string action Action
	 *
	 * @access private
	 */
	bindAction: function(li, action)
	{
		this.rebindIcons(li);

		if(action != 'sort')
		{
			this._currentLi = li;

			Element.addClassName(li, this.cssPrefix  + action + '_inProgress');

			var url = this.callbacks[('before-'+action).camelize()].call(this, li);

			if(!url)
			{
				Element.removeClassName(li, this.cssPrefix  + action + '_inProgress');
				return false;
			}

			// display feedback
			this.onProgress(li);

			// execute the action
			new LiveCart.AjaxRequest(
				url,
				false,
				function(param)
				{
					this.callUserCallback(action, param, li);
				}.bind(this)
			);
		}
	},

	/**
	 * Toggle progress bar on list element
	 *
	 * @param HtmlElementLi A reference to item element
	 *
	 * @access private
	 */
	toggleProgress: function(li)
	{
		if(li.progress && li.progress.style.visibility == 'hidden')
		{
			this.onProgress(li);
		}
		else
		{
			this.offProgress(li);
		}
	},

	/**
	 * Toggle progress indicator off
	 *
	 * @param HtmlElementLi li A reference to item element
	 */
	offProgress: function(li)
	{
		if(li.progress) li.progress.style.visibility = 'hidden';
	},

	/**
	 * Toggle progress indicator on
	 *
	 * @param HtmlElementLi li A reference to item element
	 */
	onProgress: function(li)
	{
		if(li.progress) li.progress.style.visibility = 'visible';
	},

	/**
	 * Call a user defined callback function
	 *
	 * @param string action Action
	 * @param XMLHttpRequest response An AJAX response object
	 * @param HtmlElementLi A reference to item element. Default is current item
	 *
	 * @access private
	 */
	callUserCallback: function(action, response, li)
	{
		this._currentLi = li;

		if(action == 'delete')
		{
			var duration = 0.5;
			Effect.Fade(li, { duration: duration });
			setTimeout(
			function()
			{
				Element.remove(li);
				this.callbacks[('after-'+action).camelize()].call(this, li, response.responseText);
			}.bind(this), duration * 1000 );
		}
		else
		{
			this.callbacks[('after-'+action).camelize()].call(this, li, response.responseText);
		}


		//Element.removeClassName(li, this.cssPrefix  + action + '_inProgress');

		this.offProgress(li);
	},

	/**
	 * Generate array of elements from wich this active list can accept elements.
	 * This array is generated from class name. Example: If this ul had "aciveList_accept_otherALClass"
	 * then the list would accept elements from all active lists with class otherALClass
	 *
	 */
	generateAcceptFromArray: function()
	{
		var self = this;
		var regex = new RegExp('^' + self.cssPrefix + 'accept_(\\w+)');

		this.acceptFromLists = [this.ul];
		$A(this.ul.className.split(' ')).each(function(className)
		{
			var tmp = regex.exec(className);
			if(!tmp) return;
			var allowedClassName = tmp[1];

			self.acceptFromLists = $$('ul.' + allowedClassName);
		});
	},

	/**
	 * Initialize Scriptaculous Sortable on the list
	 *
	 * @access private
	 */
	createSortable: function (forse)
	{
		Element.addClassName(this.ul, this.cssPrefix.substr(0, this.cssPrefix.length-1));

		if(Element.hasClassName(this.ul, this.cssPrefix + 'add_sort') && (forse || !this.isSortable))
		{
			Sortable.create(this.ul.id,
			{
				dropOnEmpty:   true,
				containment:   this.acceptFromLists,
				onChange:	  function(elementObj)
				{
					this.dragged = elementObj;
				}.bind(this),
				onUpdate:	  function() {
					setTimeout(function() { this.saveSortOrder(); }.bind(this), 1);
				}.bind(this),

				starteffect: function(){ this.scrollStart() }.bind(this),
				endeffect: function(){ this.scrollEnd() }.bind(this)
			});

			// Undraggable items
			Sortable.options(this.ul).draggables.each(function(draggable)
			{
				if(draggable.element.hasClassName("activeList_remove_sort"))
				{
					draggable.destroy();
				}
			});


			this.isSortable = true;
			$A(this.acceptFromLists).each(function(ul)
			{
				if(ActiveList.prototype.activeListsUsers[ul.id])
				{
					ActiveList.prototype.activeListsUsers[ul.id].createSortable();
				}
			});
		}
	},

	getWindowScroll: function()
	{
		var T, L, W, H;

		if (w.document.document.document.documentElement && documentElement.scrollTop)
		{
			T = documentElement.scrollTop;
			L = documentElement.scrollLeft;
		}
		else if (w.document.body)
		{
			T = body.scrollTop;
			L = body.scrollLeft;
		}

		if (w.innerWidth)
		{
			W = w.innerWidth;
			H = w.innerHeight;
		}
		else if (w.document.documentElement && documentElement.clientWidth)
		{
			W = documentElement.clientWidth;
			H = documentElement.clientHeight;
		}
		else
		{
			W = body.offsetWidth;
			H = body.offsetHeight
		}

		return { top: T, left: L, width: W, height: H };
	},

	findTopY: function(obj)
	{
		var curtop = 0;
		if (obj.offsetParent)
		{
			while (obj.offsetParent)
			{
				curtop += obj.offsetTop;
				obj = obj.offsetParent;
			}
		}
		else if (obj.y)
		{
			curtop += obj.y;
		}

		return curtop;
	},

	findBottomY: function(obj)
	{
		return this.findTopY(obj) + obj.offsetHeight;
	},

	scrollSome: function()
	{
		var scroller = this.getWindowScroll();
		var yTop = this.findTopY(this.dragged);
		var yBottom = this.findBottomY(this.dragged);

		if (yBottom > scroller.top + scroller.height - 20)
		{
			window.scrollTo(0,scroller.top + 30);
		}
		else if (yTop < scroller.top + 20)
		{
			window.scrollTo(0,scroller.top - 30);
		}
	},

	scrollStart: function(e)
	{
		var $this = this;
		this.dragged = e;
	},

	scrollEnd: function(e)
	{
		clearInterval(this.scrollPoll);
	},

	/**
	 * Display list item's menu. Show all item icons except progress
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access private
	 */
	showMenu: function(li)
	{
		var self = this;

		$H(this.icons).each(function(icon)
		{
			if(!li[icon.key] || icon.key == 'progress') return;

			try {
				li[icon.key].setOpacity(self.visibleMenuOpacity);
			} catch(e) {
				li[icon.key].style.visibility = 'visible';
			}
		});
	},

	/**
	 * Hides list item's menu
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access private
	 */
	hideMenu: function(li)
	{
		var self = this;

		$H(this.icons).each(function(icon)
		{
			if(!li[icon.key] || icon.key == 'progress') return;

			try {
				li[icon.key].setOpacity(self.hiddenMenuOpacity);
			} catch(e) {
				li[icon.key].style.visibility = 'hidden';
			}
		});
	},

	/**
	 * Initiates item order (position) saving action
	 *
	 * @access private
	 */
	saveSortOrder: function()
	{
		var order = Sortable.serialize(this.ul.id);
		if(order)
		{
			// execute the action
			this._currentLi = this.dragged;
			var url = this.callbacks.beforeSort.call(this, this.dragged, order);


			if(url)
			{
				this.destroySortable();

				// Destroy parent sortable as well
				var parentList = this.ul.up(".activeList");
				if(parentList && ActiveList.prototype.activeListsUsers[parentList.id])
				{
					ActiveList.prototype.activeListsUsers[parentList.id].destroySortable(true);
				}

				// display feedback
				this.onProgress(this.dragged);

				new LiveCart.AjaxRequest(
					url + "&draggedID=" + this.dragged.id,
					false,
					// the object context mystically dissapears when onComplete function is called,
					// so the only way I could make it work is this
					function(param, uriObject)
					{
						this.restoreDraggedItem(param.responseText, $(uriObject.query.draggedID));
					}.bind(this)
				);
			}
		}
	},

	/**
	 * This function is called when sort response arives
	 *
	 * @param XMLHttpRequest originalRequest Ajax request object
	 *
	 * @access private
	 */
	restoreDraggedItem: function(item, li)
	{
		// if moving elements from one active list to another we should also change the id of the HTMLLElement
		if(li.prevParentId != li.parentNode.id && li.parentNode.id == this.ul.id)
		{
			li.id = li.parentNode.id + "_" + li.id.substring(li.prevParentId.length + 1);
		}

		this.rebindIcons(li);
		this.hideMenu(li);

		this._currentLi = li;

		if (this.callbacks.afterSort)
		{
			var success = this.callbacks.afterSort.call(this, li, item);
		}
		this.createSortable(true);

		// Recreate parent list sortable as well
		var parentList = this.ul.up(".activeList");
		if(parentList && ActiveList.prototype.activeListsUsers[parentList.id])
		{
			ActiveList.prototype.activeListsUsers[parentList.id].createSortable(true);
		}

		this.colorizeItems();
		li.prevParentId = this.ul.id;
		this.offProgress(li);

		if(success !== false && li.up('ul') == this.ul)
		{
			this.highlight(li, 'yellow');
		}

		this.dragged = false;
	},

	/**
	 * Keyboard access functionality
	 *	 - navigate list using up/down arrow keys
	 *	 - move items up/down using Shift + up/down arrow keys
	 *	 - delete items with Del key
	 *	 - drop focus ("exit" list) with Esc key
	 *
	 * @param KeyboardEvent keyboard KeyboardEvent object
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access private
	 *
	 * @todo Edit items with Enter key
	 */
	navigate: function(keyboard, li)
	{
		switch(keyboard.getKey())
		{
			case keyboard.KEY_UP: // sort/navigate up
				if (keyboard.isShift())
				{
					prev = this.getPrevSibling(li);

					prev = (prev == prev.parentNode.lastChild) ? null : prev;

					this.moveNode(li, prev);
				}
			break;

			case keyboard.KEY_DOWN: // sort/navigate down

				if (keyboard.isShift())
				{
					var next = this.getNextSibling(li);
					if (next != next.parentNode.firstChild) next = next.nextSibling;

					this.moveNode(li, next);
				}
			break;

			case keyboard.KEY_DEL: // delete
				if(this.icons['delete']) this.bindAction(li, 'delete');
			break;

			case keyboard.KEY_ESC:  // escape - lose focus
				li.blur();
			break;
		}
	},

	/**
	 * Moves list node
	 *
	 * @param HtmlElementLi li A reference to item element
	 * @param HtmlElementLi beforeNode A reference to item element
	 *
	 * @access private
	 */
	moveNode: function(li, beforeNode)
	{
		var self = this;

		this.dragged = li;

		li.parentNode.insertBefore(this.dragged, beforeNode);

		this.sortTimerStart = (new Date()).getTime();
		setTimeout(function(e)
		{
			if((new Date()).getTime() - self.sortTimerStart >= 1000)
			{
				self.saveSortOrder();
			}
		}, this.keyboardSortTimeout);
	},

	/**
	 * Gets next sibling for element in node list.
	 * If the element is the last node, the first node is being returned
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access private
	 *
	 * @return HtmlElementLi Next sibling
	 */
	getNextSibling: function(element)
	{
		return element.nextSibling ? element.nextSibling : element.parentNode.firstChild;
	},

	/**
	 * Gets previous sibling for element in node list.
	 * If the element is the first node, the last node is being returned
	 *
	 * @param HtmlElementLi li A reference to item element
	 *
	 * @access private
	 *
	 * @return Node Previous sibling
	 */
	getPrevSibling: function(element)
	{
		return !element.previousSibling ? element.parentNode.lastChild : element.previousSibling;
	},

	/**
	 * Remove record from active list
	 *
	 * @param HtmlElementLi li A reference to item element
	 */
	remove: function(li, touch)
	{
		if(touch !== false) touch = true;

		if(touch && BrowserDetect.browser != 'Explorer')
		{
			Effect.SwitchOff(li, {duration: 1});
			setTimeout(function() {
				if (li.parentNode)
				{
					Element.remove(li);
				}
			}, 10);
		}
		else
		{
			Element.remove(li);
		}
	},

	/**
	 * Collapse all opened records
	 *
	 * @param lists You can specify wich lists to collapse
	 */
	collapseAll: function()
	{
		var activeLists = {};

		if(!this.ul)
		{
			activeLists = ActiveList.prototype.activeListsUsers;
		}
		else
		{
			activeLists[this.ul.id] = true;
		}

		$H(activeLists).each(function(activeList)
		{
			if(!activeList.value.ul || 0 >= activeList.value.ul.offsetHeight) return; // if list is invisible there is no need to collapse it

			var containers = document.getElementsByClassName('activeList_container', activeList.value.ul);

			for(var i = 0; i < containers.length; i++)
			{
				if(0 >= containers[i].offsetHeight) continue;

				activeList.value.toggleContainerOff(containers[i]);
			}
		});
	},


	recreateVisibleLists: function()
	{
		$H(ActiveList.prototype.activeListsUsers).each(function(activeList)
		{
			if(!activeList.value.ul || 0 >= activeList.value.ul.offsetHeight) return; // if list is invisible there is no need to collapse it
			ActiveList.prototype.getInstance(activeList.value.ul).touch();
		});
	},

	/**
	 * Get list of references to all ActiveList ActiveRecords (li)
	 */
	getChildList: function()
	{

		var liArray = this.ul.getElementsByTagName("li");
		var childList = [];

		for(var i = 0; i < liArray.length; i++)
		{
			if(this.ul == liArray[i].parentNode && !Element.hasClassName(liArray[i], 'ignore') && !Element.hasClassName(liArray[i], 'dom_template'))
			{
				childList[childList.length] = liArray[i];
			}
		}

		return childList;
	},

	/**
	 * Make list work again
	 */
	touch: function(force)
	{
		this.generateAcceptFromArray(force);
		this.createSortable(force);
	}
}

ActiveList.CallbacksCommon = function() {}
ActiveList.CallbacksCommon.prototype =
{
	beforeDelete: function(li)
	{
		if(confirm(this.callbacks.deleteMessage))
		{
			return Backend.Router.createUrl(this.callbacks.controller, 'delete', {id: this.getRecordId(li)});
		}
	},

	beforeSort: function(li, order)
	{
		return Backend.Router.createUrl(this.callbacks.controller, 'sort', {target: li.parentNode.id}) + '&' + order;
	}
}