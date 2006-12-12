var Backend = {};

function showHelp(url)
{
  	return window.open(url, 'helpWin', 'width=400, height=700, resizable, scrollbars, location=no');
}

/* Backend menu */

Backend.NavMenu = Class.create();

/**
 * Builds navigation menu from passed JSON array
 **/
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
				item = menuArray[topIndex];
				
				if (item['controller'] == controller)
				{
				  	index = topIndex;
				}
				
				match = false;
				
				if ('object' == typeof item['items'])
				{
				  	for (subIndex in item['items'])
					{
					  	subItem = item['items'][subIndex];
					  	
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

		// build menu
		var topItem = document.getElementById('navTopItem-template');
		var subItem = document.getElementById('navSubItem-template');
		
		navCont = document.getElementById('nav');
		
		for (topIndex in menuArray)
		{
		  	if('object' == typeof menuArray[topIndex])
		  	{
				item = menuArray[topIndex];
				
				menuItem = topItem.cloneNode(true);
				
				menuItem.getElementsByTagName('a')[0].href = item['url'];
				menuItem.getElementsByTagName('a')[0].innerHTML = item['title'];
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

				ul = menuItem.getElementsByTagName('ul')[0];

				if (1 == topIndex) 
				{
				  	ul.style.left = '150px';
				}

				if ('object' == typeof item['items'])
				{
				  	for (subIndex in item['items'])
					{
					  	sub = item['items'][subIndex];

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
	},

}
	
function initializeNavigationMenu() {
	return false;
	if (document.all&&document.getElementById) {
		navRoot = document.getElementById("nav");
		for (i=0; i<navRoot.childNodes.length; i++) {
			node = navRoot.childNodes[i];
			if (node.nodeName=="LI") {
				node.onmouseover=function() {
					this.className+=" over";
				}
				node.onmouseout=function() {
					this.className=this.className.replace(" over", "");
				}
				
				if (node.childNodes.length > 0)
				{
					cont = node.firstChild.firstChild.firstChild;	  
					for (z=0; z < cont.childNodes.length; z++) 
					{
						menuNode = cont.childNodes[z];
						if (menuNode.nodeName=="UL") 
						{
							for (zz=0; zz < menuNode.childNodes.length; zz++) 
							{
							 	menuCommandNode = menuNode.childNodes[zz];
					
							 	menuCommandNode.onmouseover = function() {
									this.className+=" menuCommandHover";
								}
								
								menuCommandNode.onmouseout = function() {
									this.className=this.className.replace(" menuCommandHover", "");
								}
							}		
						}					
					}
				}				
			}
		}
	}
}

/* Language switch menu */

function showLangMenu(display) {		
	menu = document.getElementById('langMenuContainer');
	if (display)
	{
		menu.style.display = 'block';
		new Ajax.Updater('langMenuContainer', langMenuUrl);
		
		try 
		{
			// Mozilla
			document.addEventListener('click', hideLangMenu, true);
		} 
		catch (e)
		{
		  	// IE...
			setTimeout(ieHideLangMenu, 500);
		}
	}
	else
	{
	  	menu.style.display = 'none';

		try 
		{
			// Mozilla
			document.removeEventListener('click', hideLangMenu, false);
		} 
		catch (e)
		{
		  	// IE...
			document.detachEvent('onclick', hideLangMenu); 	 	
		}
	}
}

function ieHideLangMenu()
{
	document.attachEvent('onclick', hideLangMenu); 	 	
}

function hideLangMenu()
{
	showLangMenu(false);
}

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