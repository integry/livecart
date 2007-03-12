/**
 * Passes language display settings from navigation form to translation modification form
 */
function langPassDisplaySettings(form)
{
	nav = document.getElementById('navLang');
	form.langFileSel.value = nav.elements.namedItem('langFileSel').value;
	form.show.value = nav.elements.namedItem('show').value;
}

Backend.LanguageIndex = Class.create();
Backend.LanguageIndex.prototype = 
{		
	addUrl: false,

	statusUrl: false,
	
	formUrl: false,

	editUrl: false,
	
	sortUrl: false,	
	
	deleteUrl: false,	
	
	delConfirmMsg: false,
	
	initialize: function()
	{
	  
	},
	
    initLangList: function ()
    {	
		ActiveList.prototype.getInstance('languageList', {
	         beforeEdit:     function(li) {window.location.href = lng.editUrl + '/' + this.getRecordId(li); },
	         beforeSort:     function(li, order) 
			 { 
				 return lng.sortUrl + '?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if(confirm(lng.delConfirmMsg)) return lng.deleteUrl + '/' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); }
	     },  this.activeListMessages);
	},	
	
	renderList: function(data)
	{
	  	t = new TimeTrack();
		var template = $('languageList_template');
	  	var list = $('languageList');

		for (k = 0; k < data.length; k++)
	  	{			
			z = template.cloneNode(true);
			z = this.renderItem(data[k], z);
			
			list.appendChild(z);
		}		 
	},
	
	renderItem: function(itemData, node)
	{
		node.id = 'languageList_' + itemData.ID;
		node.style.display = 'block';
		
		checkbox = node.getElementsByTagName('input')[0];
		
		if (1 == itemData.isEnabled)
		{
		  	node.removeClassName('disabled');
			node.getElementsByClassName('listLink')[0].href += itemData.ID;
			checkbox.checked = true;
		}
		
		if (0 == itemData.isDefault)
		{
		  	node.removeClassName('default');		  
		  	node.removeClassName('activeList_remove_delete');		  
		  	checkbox.disabled = false;
		  	checkbox.onclick = function() {lng.setEnabled(this); }
		}
		
		node.getElementsByClassName('langTitle')[0].innerHTML = itemData.name;
		
		var img = node.getElementsByClassName('langData')[0].getElementsByTagName('img')[0];
		if (itemData.image)
		{
			img.src = itemData.image;		
		} 
		else
		{
			img.parentNode.removeChild(img);  
		}
				
		return node;  
	},
	
	updateItem: function(originalRequest)
	{
 	    eval('var itemData = ' + originalRequest.responseText);
		
		var node = $('languageList_' + itemData.ID);
	  	var template = $('languageList_template');
		var cl = template.cloneNode(true);
	  	
		node.parentNode.replaceChild(cl, node);
	  	
		this.renderItem(itemData, cl);
		this.initLangList();
		new Effect.Highlight(cl, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
	},
	
	showAddForm: function()
	{
		document.getElementById('langAddMenuLoadIndicator').style.display = 'inline';
		new Ajax.Request(
		  			this.formUrl,
					{
					  method: 'get',
					  onComplete: this.doShowAddForm
					}	  										  
					);
	},
	
	doShowAddForm: function(request)
	{
		document.getElementById('langAddMenuLoadIndicator').style.display = 'none';
		cont = document.getElementById('addLang');
		cont.innerHTML = request.responseText;
		slideForm('addLang', 'langPageMenu');	  	
	},
	
	add: function(langCode)
	{
	  	// deactivate submit button and display feedback
	  	button = document.getElementById('addLang').getElementsByTagName('input')[0];
	  	button.disabled = true;

		document.getElementById('addLangFeedback').style.display = 'inline';
		  
		new Ajax.Request(
		  			this.addUrl,
					{
					  method: 'get',
					  parameters: 'id=' + langCode,
					  onComplete: this.addToList.bind(this)
					}	  										  
					);

	},
	
	addToList: function(originalRequest)
	{		
 	    eval('var itemData = ' + originalRequest.responseText);
		
	  	var template = $('languageList_template');
	  	
	  	var list = $('languageList');
		var node = template.cloneNode(true);
		node = this.renderItem(itemData, node);
		list.appendChild(node);
  	
		this.initLangList();
				
		restoreMenu('addLang', 'langPageMenu');
		document.getElementById('addLangFeedback').style.display = 'none';
				
		new Effect.Highlight(node, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
	},
	
	setEnabled: function(node) 
	{
		p = node;
		while (p.tagName != 'LI')
		{
		  	p = p.parentNode;
		}
		langId = p.id.substr(p.id.length - 2, 2);
		
		url = this.statusUrl + langId + "?status=" + (node.checked - 1 + 1);

		img = document.createElement('img');
		img.src = 'image/indicator.gif';
		img.className = 'activateIndicator';
										
		node.parentNode.replaceChild(img, node);
		
		new LiveCart.AjaxRequest(url, img, this.updateItem.bind(this));
	},
		
	setFormUrl: function(url)
	{
	  	this.formUrl = url;
	},

	setAddUrl: function(url)
	{
	  	this.addUrl = url;
	},

	setStatusUrl: function(url)
	{
	  	this.statusUrl = url;
	},

	setEditUrl: function(url)
	{
	  	this.editUrl = url;
	},

	setSortUrl: function(url)
	{
	  	this.sortUrl = url;
	},

	setDeleteUrl: function(url)
	{
	  	this.deleteUrl = url;
	},
	
	setDelConfirmMsg: function(msg)
	{
	  	this.delConfirmMsg = msg;
	}
}

Backend.LanguageEdit = Class.create();
Backend.LanguageEdit.prototype = 
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
	
	langContainerVisibility: function (container, visibility)
	{
		container.style.display = (visibility ? '' : 'none');  	
	},
	
	generateForm: function(translations, container, fileName)
	{
		// create container
		var t = this.templ.cloneNode(true);
		transcont = t.getElementsByTagName('table')[0].getElementsByTagName('tbody')[0];

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
				langEdit.langToggleVisibility(this.parentNode);
  			}
		
		t.getElementsByTagName('legend')[0].getElementsByTagName('img')[1].onclick = 
			function () 
			{
				// shallow collapse
				if (langEdit.isContainerVisible(this.parentNode.parentNode.parentNode))
				{
					langEdit.langToggleVisibility(this.parentNode);	
				}
				// full expand ..advanced stuff..sss..aaaa..sss...
				else
				{
					langEdit.langExpandAll(this.parentNode.parentNode.id, 1 - langEdit.isContainerVisible(this.parentNode.	parentNode.parentNode));				  
				}
  			}

		t.getElementsByTagName('legend')[0].getElementsByTagName('a')[0].onkeydown = 			
			function (event)
			{
				if (getPressedKey(event) != KEY_TAB && getPressedKey(event) != KEY_SHIFT) 
				{
				  	langEdit.langToggleVisibility(this.parentNode.parentNode);
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
								langEdit.replaceInputWithTextarea(this);
							} 
						}
				
				if (inp.value.indexOf("\n") > -1)
				{
				  	this.replaceInputWithTextarea(inp);
				}

				transcont.appendChild(r);
				
				t.getElementsByTagName('div')[0].style.borderLeft = '0px'; 
				transcont.style.display = '';
				transcont.parentNode.style.display = '';
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
	
	},
	
	langFileSearch: function(query, translations, file, display)
	{	
		var found = false;
			
		var showFile = false;
			
		for (var k in translations)
		{			
			if ('object' == typeof translations[k])
			{
			  	if (this.langFileSearch(query, translations[k], k, display))
				{
				  	showFile = true;
				}			  	
			}
			else
			{		
				matchIndex = (k.toLowerCase().indexOf(query) > -1);
				
				valueInput = document.getElementById('cont-' + file + '-' + k);
	
				if (!valueInput)
				{
				  	continue;
				}
				
				inp = valueInput.getElementsByTagName('input');
	
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
				
				// filter by translated/untranslated radio buttons
				if ((display > 0) && ('none' != valueInput.style.display))
				{
					if (1 == display)
				  	{
						d = ('' == inp.value);
					}
					else if (2 == display)
					{
						d = ('' != inp.value);							  
					}
				
					if (d)
					{
						valueInput.style.display = 'none';  
					}					
				}
				
				if ('' == valueInput.style.display)
				{
				  	showFile = true;
				}
			}
		}
		
		container = document.getElementById('cont-' + file);
		
		if (container)
		{
			this.langContainerVisibility(container, showFile);  
		}	
		
		if (showFile)
		{
		  	found = true;
		}	
		
		// for IE
		$('filter').focus();		
		window.setTimeout("$('filter').focus()", 200);
		
		return found;				
		
	},
	
	langSearch: function(query, display, expand)
	{
		query = query.toLowerCase();  
		found = this.langFileSearch(query, translations, '', display);
		document.getElementById('langNotFound').style.display = (found) ? 'none' : 'block';  	
		document.getElementById('editLang').style.display = (found) ? 'block' : 'none';  			
		
		if (expand)
		{
			this.langExpandAll('translations', true);
		}
	},
	
	replaceInputWithTextarea: function(element)
	{
		textarea = document.createElement('textarea');  	
		element.parentNode.replaceChild(textarea, element);
		textarea.value = element.value;
		textarea.name = element.name;
		textarea.focus();								  	
	},
	
	langExpandAll: function(containerId, expand)
	{
		containers = document.getElementById(containerId).getElementsByTagName('fieldset');
		for (k = 0; k < containers.length; k++)
		{
		  	this.langSetVisibility(containers[k], expand);
		}
		window.onresize();
	},
	
	langSetVisibility: function(container, visibility)
	{
		// toggle translation input visibility
		transCont = container.getElementsByTagName('div')[0];
		transCont.style.display = (visibility ? '' : 'none');
	
		// toggle collapse/expand images
		img = container.getElementsByTagName('img')[1];
		img.src = 'image/backend/icon/' + (visibility ? 'collapse.gif' : 'expand.gif');
		
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
			
		arr[container.file] = visibility;
	
		sel.value = arr.toJSONString();	
	},
	
	/**
	 * Toggles visibility for lang file
	 */
	langToggleVisibility: function(container)
	{
		this.langSetVisibility(container, 1 - this.isContainerVisible(container));
		
		// rerender the document otherwise IE will screw it up
		document.body.style.display = 'none';
		document.body.style.display = 'block';
		window.onresize();
	},
	
	isContainerVisible: function(container)
	{
		return container.getElementsByTagName('div')[0].style.display != 'none';  	
	},
	
	preFilter: function()
	{
		this.langSearch('', this.getDisplayFilter(), false);	  
	},
	
	displayFilter: function(display)
	{
		// get search query
		var query = document.getElementById('filter').value;
		
		this.langSearch(query, display, true);
	},
	
	getDisplayFilter: function()
	{
	  	var filter = 0;
		if (document.getElementById('show-all').checked)
	  	{
			filter = 0;    
		}
		else if(document.getElementById('show-undefined').checked)
		{
		  	filter = 1;
		}
		else if(document.getElementById('show-defined').checked)
		{
		  	filter = 2;
		}
		
		return filter;
	}
}