var Backend = {};

/* Debugger functions */

function print_r(input, _indent)
{
    if(typeof(_indent) == 'string') {
        var indent = _indent + '    ';
        var paren_indent = _indent + '  ';
    } else {
        var indent = '    ';
        var paren_indent = '';
    }
    switch(typeof(input)) {
        case 'boolean':
            var output = (input ? 'true' : 'false') + "\n";
            break;
        case 'object':
            if ( input===null ) {
                var output = "null\n";
                break;
            }
            var output = ((input.reverse) ? 'Array' : 'Object') + " (\n";
            for(var i in input) {
                output += indent + "[" + i + "] => " + print_r(input[i], indent);
            }
            output += paren_indent + ")\n";
            break;
        case 'number':
        case 'string':
        default:
            var output = "" + input  + "\n";
    }
    return output;
}

function addlog(info)
{
	document.getElementById('log').innerHTML += info + '<br />';
}

function showHelp(url)
{
  	return window.open(url, 'helpWin', 'width=400, height=700, resizable, scrollbars, location=no');
}

function initializeNavigationMenu() {
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