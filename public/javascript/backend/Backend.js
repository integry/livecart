var Backend = {};

function showHelp(url)
{
  	return window.open(url, 'helpWin', 'width=400, height=700, resizable, scrollbars, location=no');
}

/*************************************************
	Layout Control
**************************************************/
Backend.LayoutManager = Class.create();

/**
 * Manage 100% heights
 *
 * IE does this pretty good natively (only the main content div height is changed on window resize),
 * however FF won't handle cascading 100% heights unless the page is being rendered in quirks mode.
 *
 * You can specify a block to take 100% height by assigning a "maxHeight" CSS class to it
 * This class also simulates an "extension" of CSS, that allows to add or substract some height
 * in pixels from percentage defined height (for example 100% minus 40px). This will often be needed
 * to compensate for parent elements padding. For example, if the parent element has a top and bottom
 * padding of 10px, you'll have to substract 20px from child block size. This will also be needed when
 * there are other siblings that consume some known height (like TabControl, which contains a
 * tab bar with known height and content div, which must take 100% of the rest of the available height).
 *
 * Example: 
 * 
 * <code>
 * 		<div class="maxHeight h--50">
 *			This div will take 100% of available space minus 50 pixels		
 *		</div>
 * </code>
 *
 * @todo automatically substract parent padding
 */
Backend.LayoutManager.prototype = 
{
	initialize: function()
	{	  	
		window.onresize = this.onresize.bindAsEventListener(this);
		this.onresize();	
	},	
	
	/**
	 * Set the minimum possible height to all involved elements, so that 
	 * their height could be enlarged to necessary size
	 */
	collapseAll: function(cont)
	{
		el = document.getElementsByClassName("maxHeight", document);

		for (k = 0; k < el.length; k++)
		{
			el[k].style.minHeight = '0px';

			if (document.all) 
			{
				el[k].style.height = '0px';
			}
			else
			{
				el[k].style.minHeight = '0px';
			}

		}
	},

	/**
	 * @todo Figure out why IE needs additional 2px offset
	 * @todo Figure out a better way to determine the body height for all browsers
	 */
	onresize: function(stop)
	{

        return;

		if (document.all)
		{
			document.getElementById('pageContentContainer').style.height = '0px';
		}
				
		// calculate content area height
		ph = new PopupMenuHandler();
		w = ph.getWindowHeight();

		// for IE 7 only
		if (document.all && (document.documentElement.scrollHeight > w + 140) && BrowserDetect.version == 7)
		{
		  	w = document.documentElement.scrollHeight + 60;		  	
		}
		
		h = w - 99 - 61 - (document.all ? 1 : 0);

        jsTrace.send(h);

		cont = document.getElementById('pageContentContainer');

		// IE	
		if (document.all)
		{
			try {
				if (cont.style.minHeight)
				{
					cont.style.minHeight = h + 'px';	
				}
				else
				{
					cont.style.height = h + 'px';				  
				}
			}
			catch (e)
			{
				cont.style.height = h + 'px';
				cont.style.height = '0px';				  	
			}
			
			// force re-render for IE

			document.getElementById('pageContainer').style.display = 'none';
			document.getElementById('pageContainer').style.display = 'block';
			document.getElementById('nav').style.display = 'none';
			document.getElementById('nav').style.display = 'block';

			if (!stop)
			{
			  	this.onresize(true);
			}	  		  

		}
		
		// FF, etc.
		else
		{
			cont.style.minHeight = h + 'px';		  

			this.collapseAll(cont);
			this.setMaxHeight(cont);
		}
	},

	setMaxHeight: function(parent)
	{
	  	el = document.getElementsByClassName('maxHeight', parent);
	  	for (k = 0; k < el.length; k++)
		{
			var parentHeight = el[k].parentNode.offsetHeight;

			offset = 0;
			if (el[k].className.indexOf(' h-') > 0)
			{
			  	offset = el[k].className.substr(el[k].className.indexOf(' h-') + 3, 10);
			  	if (offset.indexOf(' ') > 0)
			  	{
			  		offset = offset.substr(0, offset.indexOf(' '));
			  	}				  	
			}  
			offset = parseInt(offset);
 			newHeight = parentHeight + offset;
			el[k].style.minHeight = newHeight + 'px';				    
		}
	}	
}

/*************************************************
	Breadcrumb navigation
**************************************************/
Backend.Breadcrumb = Class.create();

/**
 * Builds breadcrumb navigation menu
 */
Backend.Breadcrumb.prototype = 
{
	items: false,
	
	initialize: function()	
	{
		this.items = new Array();
		window.onload = this.display.bindAsEventListener(this);	  
	},
	
	addItem: function(title, url)
	{
		this.items[this.items.length] = {"title": title, "url": url}		
	},
	
	display: function()
	{
		// there must be at least 2 items added for the breadcrumb to be displayed
		if (this.items.length < 2)
		{
			return false;  
		}
	
		cont = document.getElementById('breadcrumb');
		itemTemplate = document.getElementById('breadcrumb_item');
		sepTemplate = document.getElementById('breadcrumb_separator');
		lastItemTemplate = document.getElementById('breadcrumb_lastItem');
										
		for (k = 0; k < this.items.length; k++)
		{
			if (k + 1 < this.items.length)
			{
				it = itemTemplate.cloneNode(true);
				it.firstChild.href = this.items[k].url;
				it.firstChild.innerHTML = this.items[k].title;			  
								
				it.appendChild(sepTemplate.cloneNode(true));				
			} 
			else
			{
				it = lastItemTemplate.cloneNode(true);
				it.innerHTML = this.items[k].title;			  
				it.id = 'breadcrumbLast';
			}
			
			cont.appendChild(it);	 	
		}  
	}
}

var breadcrumb = new Backend.Breadcrumb();

/*************************************************
	Backend menu 
**************************************************/
Backend.NavMenu = Class.create();

/**
 * Builds navigation menu from passed JSON array
 */
Backend.NavMenu.prototype = 
{
	initialize: function(menuArray, controller, action)
	{	
		var index = 0;
		var subIndex = 0;
		var match = false;
		
		// find current menu items
		for (topIndex in menuArray)
		{
		  	if('object' == typeof menuArray[topIndex])
		  	{
				mItem = menuArray[topIndex];
				
				if (mItem['controller'] == controller)
				{
				  	index = topIndex;
				}
				
				if (mItem['controller'] == controller && mItem['action'] == action)				
				{
				  	index = topIndex;
					subItemIndex = 0;
					match = true;
					break;    
				}

				match = false;
				
				if ('object' == typeof mItem['items'])
				{
				  	for (subIndex in mItem['items'])
					{
					  	subItem = mItem['items'][subIndex];
					  	
					  	if (subItem['controller'] == controller && subItem['action'] == action)
					  	{
							index = topIndex;
							subItemIndex = subIndex;
							match = true;
							break;    
						}
						else if (controller == subItem['controller'])
						{
							index = topIndex;
							subItemIndex = subIndex;						  
						}						
					}
					
					if (match)
					{
					  	break;
					}	
				}
			}
		}

		// add current menu items to breadcrumb
		breadcrumb.addItem(menuArray[index]['title'], menuArray[index]['url']);
		if (subItemIndex > 0)
		{
			breadcrumb.addItem(menuArray[index]['items'][subItemIndex]['title'], 
					     	   menuArray[index]['items'][subItemIndex]['url']);							
		}

		// build menu
		var topItem = document.getElementById('navTopItem-template');
		var subItem = document.getElementById('navSubItem-template');
		
		navCont = document.getElementById('nav');
		
		for (topIndex in menuArray)
		{
		  	if('object' == typeof menuArray[topIndex])
		  	{
				mItem = menuArray[topIndex];
				
				menuItem = topItem.cloneNode(true);
				
				menuItem.getElementsByTagName('a')[0].href = mItem['url'];
				menuItem.getElementsByTagName('a')[0].innerHTML = mItem['title'];
				menuItem.style.display = 'block';
									
				if (topIndex == index)
				{
				  	menuItem.id = 'navSelected';
				}
				else
				{
				  	Event.observe(menuItem, 'mouseover', this.hideCurrentSubMenu);
				  	Event.observe(menuItem, 'mouseout', this.showCurrentSubMenu);
				}

				/* for IE >> */
				if ('Explorer' == BrowserDetect.browser)
				{
					menuItem.onmouseover=function() {
						this.className+=" over";
					}
					menuItem.onmouseout=function() {
						this.className=this.className.replace(" over", "");
					}
				}
				/* << IE */

				// submenu container
				ul = menuItem.getElementsByTagName('ul')[0];

				if ('object' == typeof mItem['items'])
				{
				  	for (subIndex in mItem['items'])
					{
					  	sub = mItem['items'][subIndex];

						if ('object' == typeof sub)
						{
						  	subNode = subItem.cloneNode(true);
						  	
						  	subNode.getElementsByTagName('a')[0].href = sub['url'];
						  	subNode.getElementsByTagName('a')[0].innerHTML = sub['title'];
							
							if ((topIndex == index) && (subIndex == subItemIndex))
							{
							  	subNode.id = 'navSubSelected';
							}
							
							ul.appendChild(subNode);						  					  	
						}
					}					
				}
				else 				
				{
				  	// no subitems
				  	ul.parentNode.removeChild(ul);
				}
			
				navCont.appendChild(menuItem);
			}
		}
	},
	
	hideCurrentSubMenu: function()
	{
	  	document.getElementById('navSelected').getElementsByTagName('ul')[0].style.visibility = 'hidden';
	},
	
	showCurrentSubMenu: function()
	{
	  	document.getElementById('navSelected').getElementsByTagName('ul')[0].style.visibility = 'visible';
	}
}
	
/*************************************************
	Language switch menu
*************************************************/
function showLangMenu(display) {		
	menu = document.getElementById('langMenuContainer');
	if (display)
	{
		menu.style.display = 'block';
		new Ajax.Updater('langMenuContainer', langMenuUrl);
				
		setTimeout("Event.observe(document, 'click', hideLangMenu, true);", 500);
	}
	else
	{
	  	menu.style.display = 'none';
		Event.stopObserving(document, 'click', hideLangMenu, true);
	}
}

function hideLangMenu()
{
	showLangMenu(false);
}

/*************************************************
	Popup Menu Handler
*************************************************/
/** 
 * Popup menu (absolutely positioned DIV's) position handling
 * This class calculates the optimal menu position, so that the 
 * menu would always be within visible window boundaries
 **/
PopupMenuHandler = Class.create();
PopupMenuHandler.prototype = 
{
	x: 0,
	y: 0,
	
	initialize: function(xPos, yPos, width, height)
	{
		scrollX = this.getScrollX();
		scrollY = this.getScrollY();

		if ((xPos + width) > (scrollX + this.getWindowWidth()))
		{
			xPos = scrollX + this.getWindowWidth() - width - 40;
		}
		
		if (xPos < scrollX)
		{
		  	xPos = scrollX + 1;
		}

//		alert((yPos + height) + ' - ' + (this.getWindowHeight()));
		if ((yPos + height) > (scrollY + this.getWindowHeight()))
		{
			yPos = scrollY + this.getWindowHeight() - height - 40;
		}

		if (yPos < scrollY)
		{
		  	yPos = scrollY + 1;
		}
		
		this.x = xPos;
		this.y = yPos;
	},
	
	getScrollX: function() 
	{
		var scrOfX = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfX = window.pageXOffset;
		} 
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
		{
			//DOM compliant
			scrOfX = document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
		{
			//IE6 standards compliant mode
			scrOfX = document.documentElement.scrollLeft;
		}
		return scrOfX;
	},
	
	getScrollY: function() 
	{
		var scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfY = window.pageYOffset;
		} 
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
		{
			//DOM compliant
			scrOfY = document.body.scrollTop;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
		{
			//IE6 standards compliant mode
			scrOfY = document.documentElement.scrollTop;
		}
		return scrOfY;
	},
	
	getWindowWidth: function() 
	{
		var myWidth = 0;
		if( typeof( window.innerWidth ) == 'number' ) 
		{
			//Non-IE
			myWidth = window.innerWidth;
		} 
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) 
		{
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
		} 
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
		{
			//IE 4 compatible
			myWidth = document.body.clientWidth;
		}
		return myWidth;
	},	

	getWindowHeight: function() 
	{
		var myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) 
		{
			//Non-IE
			myHeight = window.innerHeight;
		} 
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) 
		{
			//IE 6+ in 'standards compliant mode'
			myHeight = document.documentElement.clientHeight;
		} 
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
		{
			//IE 4 compatible
			myHeight = document.body.clientHeight;
		}
		return myHeight;
	}
}


/*************************************************
	Browser detector
*************************************************/

/**
 * Browser detector
 * @link http://www.quirksmode.org/js/detect.html
 */
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

BrowserDetect.init();

/*************************************************
	Save confirmation message animation
*************************************************/
Backend.SaveConfirmationMessage = Class.create();
Backend.SaveConfirmationMessage.prototype = 
{
	element: false,
	
	initialize: function(element)
  	{
		if (typeof element != 'object')
		{
		  	element = document.getElementById(element);
		}

		element = element.getElementsByTagName('div')[0];
		
		this.element = element;
		
		this.show();
	},
	
	show: function()
	{
		new Effect.SlideDown(this.element.parentNode, {afterFinish: this.highlight.bind(this)});	  
	},

	highlight: function()
	{
		new Effect.Highlight(this.element, {duration: 2.5, afterFinish: this.hide.bind(this)});
	},

	hide: function()
	{
		new Effect.SlideUp(this.element.parentNode);	  
	}
}

/*************************************************
	...
*************************************************/

function slideForm(id, menuId)
{
	Effect.Appear(id, {duration: 0.15});	  	
	document.getElementById(menuId).style.display = 'none';
	setTimeout('document.getElementById("' +  id + '").focus()', 100);
}

function restoreMenu(blockId, menuId)
{
	Effect.Fade(blockId, {duration: 0.15});	  	
	document.getElementById(menuId).style.display = 'block'; 	
}