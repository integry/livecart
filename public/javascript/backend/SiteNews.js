Backend.SiteNews = Class.create();
Backend.SiteNews.prototype = 
{
	initialize: function(newsList, container, template)
	{		
	    Element.observe("addNewsLink", "click", function(e)
	    {
	        Event.stop(e);
	        Backend.SiteNews.prototype.showAddForm();
	    });
	    
	    Element.observe("addNewsCancelLink", "click", function(e)
	    {
	        Event.stop(e);
	        Backend.SiteNews.prototype.hideAddForm();
	    });
	
		ActiveList.prototype.getInstance('newsList', {
	         beforeEdit:     function(li) 
			 { 
				 li.handler.showEditForm();
				 return false;
             },
	         beforeSort:     function(li, order) 
			 { 
				 return $('sortUrl').innerHTML + '?draggedId=' + this.getRecordId(li) + '&' + order 
			 },
	         beforeDelete:   function(li)
	         {
	             if (confirm($('confirmDelete').innerHTML)) return $('deleteUrl').innerHTML + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) { li.handler.update(response);},
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
	     }, []);		
	
		newsList.each(function(el)
		{
			new Backend.SiteNews.PostEntry(container, template, el, false);
		});
	
	},
	
	showAddForm: function()
	{
		var menu = new ActiveForm.Slide('newsMenu');
		menu.show("addNews", 'addNews');	
	},
	
	hideAddForm: function()
	{
        var menu = new ActiveForm.Slide('newsMenu');
        menu.hide("addNews", 'addNews');    	
	}
}

Backend.SiteNews.PostEntry = Class.create();
Backend.SiteNews.PostEntry.prototype = 
{	
	data: null,
	
	node: null,
	
	initialize: function(container, template, data, highlight)
	{
		this.data = data;
		var newsList = ActiveList.prototype.getInstance('newsList');

        this.node = newsList.addRecord(data.ID, template.innerHTML, highlight);
		
		this.updateHtml();
		
		this.node.handler = this;

		Element.show(this.node);
	},
	
	showEditForm: function()
	{
		Backend.SiteNews.prototype.hideAddForm();
		
		var nodes = this.node.parentNode.getElementsByTagName('li');
		$H(nodes).each(function(li)
		{
			if (li && li[1] && li[1].handler && li != this.node)
			{
				li[1].handler.cancelEditForm();				
			}
		});
	
		var form = $('newsForm').cloneNode(true);

		$H(this.data).each(function(el) 
		{ 
			if (form.elements.namedItem(el[0])) 
		 	{
				form.elements.namedItem(el[0]).value = el[1];
			}
		});
		form.elements.namedItem('id').value = this.data['ID'];

		form.elements.namedItem('time').value = this.data['time'];
		this.node.down('div.formContainer').appendChild(form);

		// set up calendar field
		var time = this.node.down('#time');
		var time_real = this.node.down('#time_real');
		var time_button = this.node.down('#time_button');
		
		time_button.realInput = time_real;
		time_button.showInput = time;
		time.realInput = time_real;
		time.showInput = time;

		time_real.value = this.data['time'];
		
		Event.observe(time,        "keyup",     Calendar.updateDate );
		Event.observe(time,        "blur",      Calendar.updateDate );
		Event.observe(time_button, "mousedown", Calendar.updateDate );
		
		Calendar.setup({
		    inputField:     time,
		    inputFieldReal: time_real,    
		    ifFormat:       "%d-%b-%Y",
		    button:         time_button,
		    align:          "BR",
		    singleClick:    true
		});

		tinyMCE.idCounter = 0;
		ActiveForm.prototype.initTinyMceFields(this.node.down('div.formContainer'));
		
		form.down('a.cancel').onclick = this.cancelEditForm.bindAsEventListener(this);
		form.onsubmit = this.save.bindAsEventListener(this);
		
		new Backend.LanguageForm();
	},
	
	cancelEditForm: function(e)
	{		
		var formContainer = this.node.down('div.formContainer');
		
		if (!formContainer.firstChild)
		{
			return;
		}
		
		ActiveForm.prototype.destroyTinyMceFields(formContainer);
	
		formContainer.innerHTML = '';		
			
		if (e)
		{
			Event.stop(e);
		}
	},
	
	save: function(e)
	{
		Element.saveTinyMceFields(this.node);
		new LiveCart.AjaxRequest(this.node.down('form'), null, this.update.bind(this));		
		Event.stop(e);
	},
	
	update: function(originalRequest)
	{
		this.data = originalRequest.responseData;
		this.updateHtml();
		this.cancelEditForm();
		ActiveList.prototype.highlight(this.node, 'yellow');
	},
	
	del: function()
	{
		
	},

	updateHtml: function()
	{
		this.node.down('.newsTitle').innerHTML = this.data.title;
		this.node.down('.newsDate').innerHTML = this.data.time;
		this.node.down('.newsText').innerHTML = this.data.text;		
		this.node.id = 'newsEntry_' + this.data.ID;
	}
}

Backend.SiteNews.Add = Class.create();
Backend.SiteNews.Add.prototype = 
{
	form: null,
	
	initialize: function(form)
	{
		new LiveCart.AjaxRequest(form, null, this.onComplete.bind(this));
	},
	
	onComplete: function(originalRequest)
	{
		new Backend.SiteNews.PostEntry($('newsList'), $('newsList_template'), originalRequest.responseData);
		Backend.SiteNews.prototype.hideAddForm();
		Form.State.restore($("newsForm"));
	}
}