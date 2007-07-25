Backend.SiteNews = Class.create();
Backend.SiteNews.prototype = 
{
	initialize: function(newsList, container, template)
	{
		newsList.each(function(el)
		{
			new Backend.SiteNews.PostEntry(container, template, el);
		});
		
		ActiveList.prototype.getInstance('newsList', {
	         beforeEdit:     function(li) { return '{/literal}{link controller=backend.currency action=edit}{literal}?id=' + this.getRecordId(li); 
             },
	         beforeSort:     function(li, order) 
			 { 
				 return '{/literal}{link controller=backend.currency action=saveorder}{literal}?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if (confirm($('confirmDelete').innerHTML)) return $('deleteUrl').innerHTML + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) { document.getElementsByClassName('currEdit', li)[0].innerHTML = response; },
	         afterSort:      function(li, response) { curr.resetRatesContainer(); },
	         afterDelete:    function(li, response)  { Element.remove(li); curr.resetRatesContainer(); }
	     }, []);		
	}
}

Backend.SiteNews.PostEntry = Class.create();
Backend.SiteNews.PostEntry.prototype = 
{	
	var data: null,
	
	initialize: function(container, template, data)
	{
		var newNode = template.cloneNode(true);
		container.appendChild(newNode);
		newNode.down('.newsTitle').innerHTML = data.title;
		newNode.down('.newsDate').innerHTML = data.time;
		newNode.down('.newsText').innerHTML = data.text;
		newNode.id = 'newsEntry_' + data.ID;
		newNode.handler = this;
		this.data = data;

		Element.show(newNode);
	},
	
	update: function()
	{
		
	},
	
	delete: function()
	{
		
	}
}

Backend.SiteNews.Save = Class.create();
Backend.SiteNews.Save.prototype = 
{
	form: null,
	
	initialize: function(form)
	{
		this.form = form;
		new LiveCart.AjaxRequest(this.form, null, this.onComplete.bind(this));
	},
	
	onComplete: function(originalRequest)
	{
		console.log(originalRequest);
		new Backend.SiteNews.PostEntry($('newsList'), $('newsList_template'), originalRequest.responseData);
	}
}