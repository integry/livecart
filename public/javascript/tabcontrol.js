var current_tab = new Array();

function tab_changed(control, i, first, md5, class_all, class_selected) {

	if (!current_tab[control] && current_tab[control] != 0) {
	  
	  	current_tab[control] = first;	  	
	} 
	  
  	if (i != current_tab[control]) {

		doc = new DocumentHelper();
		try {
		  	
		  	layer = doc.getLayer("tab_page_" + control + "_" + current_tab[control]);
	  		layer.style.visibility = "hidden";	  	
		} catch(err) {}
  	
		layer = doc.getLayer("tab_page_" + control + "_" + i);
	  	layer.style.visibility = "visible";	  	
	  	
	  	td = doc.getLayer("tab_td_" + control + "_" + current_tab[control]);
	  	td.className = class_all;
	  	
	  	td = doc.getLayer("tab_td_" + control + "_" + i);
	  	td.className = class_selected;
	  	
	  	current_tab[control] = i;	  	
	  	document.cookie = 'TabPageCurrent_' + md5 + '[' + control + ']=' + i;		
	}		
}	


function readCookie(name)
{
	
}