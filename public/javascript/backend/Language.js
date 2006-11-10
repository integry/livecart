/**
 * Toggles visibility for
 */
function langToggleVisibility(tableInstance, file)
{
	// toggle translation input visibility
	tbody = tableInstance.getElementsByTagName('tbody')[0];
	tbody.style.display = ('none' == tbody.style.display) ? '' : 'none';
	
	// toggle collapse/expand images
	img = tableInstance.getElementsByTagName('img')[0];
	img.src = 'image/backend/icon/' + (('none' == tbody.style.display) ? 'collapse.gif' : 'expand.gif');
	
	// save explode/collapse status in form variable
	sel = document.getElementById('navLang').elements.namedItem('langFileSel');
	try 
	{
		var arr = sel.value.parseJSON();
	}
	catch (e)
	{
		var arr = new Object();  	
	}
	
	arr[file] = ('' == tbody.style.display);
	
	sel.value = arr.toJSONString();	
}

/**
 * Passes language display settings from navigation form to translation modification form
 */
function langPassDisplaySettings(form)
{
	nav = document.getElementById('navLang');
	form.langFileSel.value = nav.elements.namedItem('langFileSel').value;
	form.show.value = nav.elements.namedItem('show').value;
}