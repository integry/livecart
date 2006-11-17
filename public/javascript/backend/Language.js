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
	tbody = tableInstance.getElementsByTagName('div')[0];
	tbody.style.display = ('none' == tbody.style.display) ? '' : 'none';
	
	// toggle collapse/expand images
	img = tableInstance.getElementsByTagName('img')[1];
	img.src = 'image/backend/icon/' + (('none' == tbody.style.display) ? 'expand.gif' : 'collapse.gif');
	
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

if (LiveCart == undefined)
{
    var LiveCart = {}
}

LiveCart.Language = Class.create();
LiveCart.Language.prototype = 
{		
	templ: false,
	
	row: false,
	
	expandedFiles: false,
	
	english: false,
	
	initialize: function(translations, english, container)
	{
		this.templ = document.getElementsByClassName('lang-template')[0];
		this.row   = this.templ.getElementsByClassName('lang-trans-template')[0];  			
		this.expandedFiles =  document.getElementById('navLang').elements.namedItem('langFileSel').value.parseJSON();	
		
		this.english = english;
		
		this.generateForm(translations, container);
		
	},
	
	generateForm: function(translations, container, fileName)
	{
		// create container
		var t = this.templ.cloneNode(true);
		transcont = t.getElementsByTagName('table')[0];
		t.style.display = '';
		t.file = fileName;

		t.id = 'cont-' + fileName;
		
		if ('translations' == container.id)
		{
		  	t.className += ' transContainer';
		}
		else 
		{
		  	t.className += ' transValues';	
		}
		
		if (fileName)
		{
			// set caption
			var caption = fileName;
			
			// remove file extension
			if (caption.indexOf('.lng') > 0)
			{
			  	caption = caption.substr(0, caption.indexOf('.lng'));
			}
			
			// remove directory path
			temp = caption.split('/');
			if (temp.length > 1)
			{
			  	caption = temp[temp.length - 1];
			}			
			
			// capitalize
			caption = caption.substring(0,1).toUpperCase() + caption.substring(1, caption.length);
			
		  
		  
		}
		
		t.getElementsByTagName('legend')[0].getElementsByTagName('a')[0].innerHTML = caption;
		t.getElementsByTagName('legend')[0].onclick = 
			function () 
			{
				langToggleVisibility(this.parentNode, this.parentNode.file);
  			}
		
		t.getElementsByTagName('legend')[0].getElementsByTagName('a')[0].onkeydown = 			
			function (event)
			{
				if (getPressedKey(event) != KEY_TAB && getPressedKey(event) != KEY_SHIFT) 
				{
				  	langToggleVisibility(this.parentNode.parentNode, this.parentNode.parentNode.file);
				}									  	
			}					
		
		// set visibility
		if (this.expandedFiles[fileName])
		{
		  	t.getElementsByTagName('div')[0].style.display = '';
			img = t.getElementsByTagName('img')[1];
			img.src = 'image/backend/icon/collapse.gif';  	
		}		

		// generate container content
		zebra = 0;

		for (var file in translations)
		{
			if ('object' == typeof translations[file])
			{
				var cont = (undefined == fileName ? container : t.getElementsByTagName('div')[0]);
				this.generateForm(translations[file], cont, file);
			}
			else if ('function' != typeof translations[file])
			{
				k = file;
				r = this.row.cloneNode(true);
				r.style.display = '';
				r.id = 'cont-' + fileName + '-' + k;

				if (++zebra % 2 == 1)
				{
				  	r.className += ' altrow';
				}
				r.getElementsByClassName('lang-key')[0].innerHTML = k;
				r.getElementsByClassName('lang-translation')[0].getElementsByTagName('span')[0].innerHTML = this.english[fileName][k];
				
				try 
				{
					inp = r.getElementsByClassName('lang-translation')[0].getElementsByTagName('input')[0];
				}
				catch (e) 
				{
					inp = r.getElementsByClassName('lang-translation')[0].getElementsByTagName('textarea')[0];  	
				}
								
				inp.value = translations[k];
				inp.name = "lang[" + fileName + "][" + k + "]";
				
				inp.onkeydown = 
						function(e) 
						{ 
							key = new KeyboardEvent(e); 
							if(key.getKey() == key.KEY_DOWN)
							{
								langReplaceInputWithTextarea(this);
							} 
						}
				
				if (inp.value.indexOf("\n") > -1)
				{
				  	langReplaceInputWithTextarea(inp);
				}
				
				transcont.appendChild(r);
				
				t.getElementsByTagName('div')[0].style.borderLeft = '0px'; 
				transcont.style.display = 'table';
			}
		}

		// no translations to display for this file
		if (3 == transcont.childNodes.length && 0 == container.childNodes.length)
		{
		//  	continue;
		}			
								
		if (undefined != fileName)
		{
			container.appendChild(t);	
		}
 
		cont = t.getElementsByTagName('div')[0];
		subCont = cont.getElementsByTagName('fieldset');
		if (subCont.length > 0)
		{
		  	subCont[subCont.length - 1].className += " transValuesLast";
		}
	
	}  
  
}

function langSearch(query)
{
	query = query.toLowerCase();  
	found = langFileSearch(query, translations);
	document.getElementById('langNotFound').style.display = (found) ? 'none' : 'block';  	
}

function langFileSearch(query, translations, file)
{	
	var found = false;
		
	var showFile = false;

	for (var k in translations)
	{			
		if ('object' == typeof translations[k])
		{
		  	if (langFileSearch(query, translations[k], k))
			{
			  	showFile = true;
			}			  	
		}
		else
		{		
			matchIndex = (k.toLowerCase().indexOf(query) > -1);
			
			valueInput = document.getElementById('cont-' + file + '-' + k);
//			addlog(valueInput);
			if (!valueInput)
			{
			  	continue;
			}
			
			inp = valueInput.getElementsByTagName('input');
//			addlog(k + ' - ' + inp.length);
			if (0 == inp.length) 
			{				
				inp = valueInput.getElementsByTagName('textarea');  	
			}
			inp = inp[0];
			
			matchValue = (inp.value.toLowerCase().indexOf(query) > -1);
			
			if (english[file][k])
			{
				matchEnValue = (english[file][k].toLowerCase().indexOf(query) > -1);						
			}

			valueInput.style.display = (!matchIndex && !matchValue && !matchEnValue) ? 'none' : '';					
			if ('' == valueInput.style.display)
			{
			  	showFile = true;
			}
		}
	}
	
	container = document.getElementById('cont-' + file);
	
	if (container)
	{
		langSetVisibility(container, showFile)
	}
	
	if (showFile)
	{
	  	found = true;
	}	
	
//	addlog(file + ' - ' + found);	
	
	return found;				
	
}

function langSetVisibility(container, visibility)
{
	container.style.display =  (visibility) ? '' : 'none';
	container.getElementsByTagName('div')[0].style.display = '';
	img = container.getElementsByTagName('img')[1];
	img.src = 'image/backend/icon/' + (visibility ? 'collapse.gif' : 'expand.gif');						  	
}

function langReplaceInputWithTextarea(element)
{
	textarea = document.createElement('textarea');  	
	element.parentNode.replaceChild(textarea, element);
	textarea.value = element.value;
	textarea.name = element.name;
	textarea.focus();								  	
}