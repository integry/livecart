var Backend = {};

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