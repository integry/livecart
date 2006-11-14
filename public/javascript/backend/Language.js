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

function langGenerateTranslationForm(query)
{
	if (query != undefined)
	{
		query = query.toLowerCase();  
	}	
		
	container = document.getElementById('translations');
	while (container.firstChild)
	{
	  	container.removeChild(container.firstChild);
	}
	
	expandedFiles = document.getElementById('navLang').elements.namedItem('langFileSel').value.parseJSON();	
	
	templ = document.getElementsByClassName('lang-template')[0];
	row = templ.getElementsByClassName('lang-trans-template')[0];
	for (var file in translations)
	{
		if ('object' == typeof translations[file])
		{
			t = templ.cloneNode(true);
			transcont = t.getElementsByTagName('tbody')[0];
			t.style.display = '';
			t.file = file;
			
			// set caption
			t.getElementsByTagName('caption')[0].getElementsByTagName('a')[0].innerHTML = file;
			t.getElementsByTagName('caption')[0].onclick = 
				function () 
				{
	  				langToggleVisibility(this.parentNode, this.getElementsByTagName('a')[0].innerHTML);
	  			}
			
			t.getElementsByTagName('caption')[0].getElementsByTagName('a')[0].onkeydown = 			
				function (event)
				{
					if (getPressedKey(event) != KEY_TAB && getPressedKey(event) != KEY_SHIFT) 
					{
					  	langToggleVisibility(this.parentNode.parentNode, this.innerHTML);
					}									  	
				}			 
			
			// set visibility
			if (expandedFiles[file])
			{
			  	transcont.style.display = '';
			}
			
			// set translations
			zebra = 0;
			for (var k in translations[file])
			{
				if ('function' != typeof translations[file][k])
				{
					if (query != undefined)
					{
					  	if (k.toLowerCase().indexOf(query) == -1)
					  	{
						    continue;
						}
					}
					
					r = row.cloneNode(true);
					r.style.display = '';
					if (++zebra % 2 == 1)
					{
					  	r.className += ' altrow';
					}
					r.getElementsByClassName('lang-key')[0].innerHTML = k;
					r.getElementsByClassName('lang-translation')[0].getElementsByTagName('span')[0].innerHTML = english[file][k];
					inp = r.getElementsByClassName('lang-translation')[0].getElementsByTagName('input')[0];
					inp.value = translations[file][k];
					inp.name = "lang[" + file + "][" + k + "]";
					
					transcont.appendChild(r);
	
				}
			}						
						
			// no translations to display for this file
			if (3 == transcont.childNodes.length)
			{
			  	continue;
			}			
						
			document.getElementById('translations').appendChild(t);					
		}	  
	}  
}

function langSearch(query)
{
	if (query != undefined)
	{
		query = query.toLowerCase();  
	}	
	
	for (var file in translations)
	{
		for (var k in translations[file])
		{			
			matchIndex = (k.toLowerCase().indexOf(query) > -1);
			matchValue = (translations[file][k].toLowerCase().indexOf(query) > -1);
			matchEnValue = (english[file][k].toLowerCase().indexOf(query) > -1);						
		}
	}
  
}