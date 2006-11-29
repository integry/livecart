function showHelp(url)
{
  	window.open(url, 'helpWin', 'width=400, height=700, resizable, scrollbars');
  	return false;
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