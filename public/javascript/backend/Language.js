/**
 *	@author Integry Systems
 */
 
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
		return ActiveList.prototype.getInstance('languageList', {
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
	         afterDelete:    function(li, response)  
			 { 
	             try 
	             { 
	                 response = eval('(' + response + ')'); 
	             } 
	             catch(e) 
	             { 
	                 return false; 
	             }
             }
	     },  this.activeListMessages);
	},	
	
	renderList: function(data)
	{
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
 	    var response = eval('(' + originalRequest.responseText + ')');
		var itemData = response.language;
        
		var node = $('languageList_' + itemData.ID);
	  	var template = $('languageList_template');
		var cl = template.cloneNode(true);
	  	
		node.parentNode.replaceChild(cl, node);
	  	
		this.renderItem(itemData, cl);

        var list = this.initLangList();
        list.decorateItems();
        list.createSortable(true);
        
		new Effect.Highlight(cl, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
	},
	
	showAddForm: function()
	{       
        new LiveCart.AjaxRequest(this.formUrl, 'langAddMenuLoadIndicator', this.doShowAddForm);
	},
	
	doShowAddForm: function(request)
	{
		$('addLang').innerHTML = request.responseText;
		
        var menu = new ActiveForm.Slide("langPageMenu");
        menu.show("addNewLanguage", 'addLang', ['id']);
	},
    
    hideAddForm: function(request)
    {
        Element.hide($('langAddMenuLoadIndicator'));
        
        var menu = new ActiveForm.Slide("langPageMenu");
        menu.hide("addNewLanguage", 'addLang', ['id']);
    },
	
	add: function(form)
	{
		new LiveCart.AjaxRequest(form, 'addLangFeedback', this.addToList.bind(this));
	},
	
	addToList: function(originalRequest)
	{		
 	    var response = eval('(' + originalRequest.responseText + ')');
        
	  	var template = $('languageList_template');
        
        var list = this.initLangList();
		var node = this.renderItem(response.language, template.cloneNode(true));
		list.addRecord(response.language['ID'], node);
        		
		Backend.LanguageIndex.prototype.hideAddForm();
		
		Element.hide('addLangFeedback');
		
						
        new Effect.Highlight(node, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
	},
	
	setEnabled: function(node) 
	{
		p = node.up('li');
        
		langId = p.id.substr(p.id.length - 2, 2);
		url = this.statusUrl + langId + "?status=" + (node.checked - 1 + 1);
        
		new LiveCart.AjaxRequest(url, p.down('.progressIndicator'), this.updateItem.bind(this));
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

/**
 *  Edit translations
 */
Backend.LangEdit = Class.create();
Backend.LangEdit.prototype = 
{
	translations: false,
	
	english: false,
	
	editedTranslations: false,
	
	treeBrowser: false,
    
    initialize: function(translations, english)
	{
        // set up language file tree
		this.initTreeBrowser();
        
        this.translations = translations;
        this.english = english;
        this.editedTranslations = {};
        
		for (var file in english)
		{
            if ('object' == typeof english[file])
            {
				this.insertMenuItem(file);
            }
        }        
        
        this.treeBrowser.closeAllItems();
        
        Backend.Breadcrumb.setTree(this.treeBrowser);
        //Backend.Breadcrumb.pageTitle = $('translationFile');
        
        // set up filter control
        $('show-all').onclick = this.search.bindAsEventListener(this);
        $('show-defined').onclick = this.search.bindAsEventListener(this);
        $('show-undefined').onclick = this.search.bindAsEventListener(this);
        
        $('filter').onchange = this.search.bindAsEventListener(this);
        $('filter').onkeyup = this.search.bindAsEventListener(this);
        $('filter').onpaste = this.search.bindAsEventListener(this);
        
        $('allFiles').onclick = this.search.bindAsEventListener(this);
                        
        $('clearFilter').onclick = 
            function(e)
            {
                $('filter').value = '';
                this.search(e);
            }.bind(this);
                        
        // set up form
		var form = $('editLang');		

		form.onsubmit = 
			function()
			{	
    			this.focus();
                this.elements.namedItem('translations').value = Object.toJSON(this.handler.editedTranslations);
				new LiveCart.AjaxRequest(this, $('saveProgress'), this.handler.saveCompleted.bind(this.handler));
				return false;
			};
		
		form.handler = this;
		
		this.activateCategory('Base.lng');
		this.treeBrowser.selectItem('Base.lng');
    },
    
	saveCompleted: function(originalRequest)
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);		
	},	    
    
    insertMenuItem: function(file)
    {
        var path = file.substr(0, file.lastIndexOf('/'))
        if (!this.treeBrowser.getItemText(path) && '' != path)
        {
            this.insertMenuItem(path);
        }
        
        if (file.lastIndexOf('/'))
        {
            var fileName = file.substr(file.lastIndexOf('/') + 1);    
        }
        else
        {
            var fileName = file;   
        }        
        
        if (fileName.substr(-4) == '.lng')
        {
            fileName = fileName.substr(0, fileName.length - 4);   
        }
        
        this.treeBrowser.insertNewItem(path, file, fileName, null, 0, 0, 0, '');
    },
    
    initTreeBrowser: function()
    {
		this.treeBrowser = new dhtmlXTreeObject("langBrowser","","", false);
		Backend.Breadcrumb.setTree(this.treeBrowser);
		
		this.treeBrowser.def_img_x = 'auto';
		this.treeBrowser.def_img_y = 'auto';
				
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory.bind(this));

		this.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		this.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}        
    },
    
	activateCategory: function(id)
	{
        if (!this.treeBrowser.hasChildren(id))
		{
            $('allFiles').checked = false;
            this.treeBrowser.showFeedback(id);
			$('translations').innerHTML = '';
            this.displayFile(id);
            
            Backend.Breadcrumb.display(id);
		}
	},	
	
	showSelected: function(e)
	{
        var id = this.treeBrowser.getSelectedItemId();
        this.activateCategory(id);
    },
    
    search: function(e)
    {
        Element.hide($('langNotFound'));
        Element.hide($('foundMany'));
         
        if ($('filter').value)
        {
            $('clearFilter').show();
        }
        else
        {
            $('clearFilter').hide();            
        }
                
        if ($('allFiles').checked/* && $('filter').value*/)
        {
            $('allFilesTitle').show();
            $('pageTitle').hide();
            this.showAll(e);
        }
        else
        {
            $('allFilesTitle').hide();
            $('pageTitle').show();
            this.showSelected(e);
        }
        
        if (!$('translations').getElementsByTagName('input').length)
        {
            Element.show($('langNotFound'));
        }
        
    },
    
    showAll: function(e)
    {
        $('translations').innerHTML = '';
        
        for (file in this.translations)    
        {
            if ('function' != typeof this.translations[file])
            {
                if ($('translations').getElementsByTagName('input').length > 50)
                {
                    Element.show($('foundMany'));
                    return false;
                }

                this.displayFile(file);
            }            
        }
    },
	
	displayFile: function(file)
	{
        Element.hide($('foundMany'));
        
		this.treeBrowser.hideFeedback();
		
		var transTemplate = $('transTemplate').innerHTML;
		
		var english = this.english[file];		
		var edit = '';
        var template = '';
        
        if ($('show-defined').checked)
        {
            var status = 1;
        }
        else if ($('show-undefined').checked)
        {
            var status = 2;
        }
        else
        {
            var status = 0;
        }
        
        for (var key in english)
		{
            if ('function' != typeof english[key])
            {
                // check translation status filter
                if ((status == 1 && !this.translations[file][key]) || (status == 2 && this.translations[file][key]))
                {
                    continue;
                }
                
                // check word filter
                var filter = $('filter').value.toLowerCase();
                
                if (key.toLowerCase().indexOf(filter) == -1
                   && this.translations[file][key].toLowerCase().indexOf(filter) == -1
                   && english[key].toLowerCase().indexOf(filter) == -1
                   )
                {
                    continue;
                }
                
                template = transTemplate;
    			template = template.replace(/_file_/g, file);
    			template = template.replace(/_key_/g, key);
    			template = template.replace(/___english___/g, english[key]);
    			edit += template;                
            }
        }
        
        if (!edit)
        {
            return false;
        }
        
        // append to translation container
        var tr = $('translations');
        var r = tr.ownerDocument.createRange();
        
        r.selectNodeContents(tr);
        r.collapse(false);
        df = r.createContextualFragment(edit);
        tr.appendChild(df);
                  
        // set field values and behavior through DOM
        for (var key in english)
		{
            var input = $(file + '#' + key);
            if (!input)
            {
                continue;
            }

            var value = this.translations[file][key];
            input.value = value;
            input.handler = this;
            input.file = file;
            input.key = key;
            input.onchange = 
                function()
                {
                    if (!this.handler.editedTranslations[this.file])
                    {
						this.handler.editedTranslations[this.file] = {};		
					}
					
					this.handler.translations[this.file][this.key] = this.value;
                    this.handler.editedTranslations[this.file][this.key] = this.value;
                }
            
            input.onkeyup = input.onchange;
                
			input.onkeydown = 
					function(e) 
					{ 
						key = new KeyboardEvent(e); 
						if (key.getKey() == key.KEY_DOWN)
						{
							this.handler.replaceInputWithTextarea(this);
						} 
						else if (key.getKey() == key.KEY_ENTER)
						{
							$('editLang').onsubmit();
						} 
						
						return true;
					}
                
            if (value.indexOf("\n") > -1)
			{
			  	var textarea = this.replaceInputWithTextarea(input);
			  	textarea.value = value;
			}                
        }
	},
	
	replaceInputWithTextarea: function(element)
	{
		var textarea = document.createElement('textarea');  	
		element.parentNode.replaceChild(textarea, element);
		
        textarea.value = element.value;
		textarea.handler = element.handler;
		textarea.file = element.file;
		textarea.key = element.key;
		textarea.onchange = element.onchange;
                		
		textarea.focus();								  	
		
		return textarea;
	}	
}