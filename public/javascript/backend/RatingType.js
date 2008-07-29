/**
 *	@author Integry Systems
 */

Backend.RatingType = Class.create();
Backend.RatingType.prototype =
{
	container: null,

	initialize: function(typeList, container, template)
	{
		this.container = container;
		this.container.handler = this;
		this.categoryID = this.container.id.match(/_([0-9]*)/)[1];

		Element.observe(container.down(".addRatingTypeLink"), "click", function(e)
		{
			Event.stop(e);
			this.showAddForm();
		}.bind(this));

		Element.observe(container.down(".addRatingTypeCancelLink"), "click", function(e)
		{
			Event.stop(e);
			this.hideAddForm();
		}.bind(this));

		ActiveList.prototype.getInstance(container.down('.typeList'), {
			 beforeEdit:	 function(li)
			 {
				 if (!this.isContainerEmpty(li, 'edit'))
				 {
					 li.handler.cancelEditForm();
					 return;
				 }

				 li.handler.showEditForm();
				 return false;
			 },
			 beforeSort:	 function(li, order)
			 {
				 return container.down('.sortUrl').innerHTML + '?draggedId=' + this.getRecordId(li) + '&' + order
			 },
			 beforeDelete:   function(li)
			 {
				 if (confirm(container.down('.confirmDelete').innerHTML)) return container.down('.deleteUrl').innerHTML + this.getRecordId(li)
			 },
			 afterEdit:	  function(li, response) { li.handler.update(response);},
			 afterSort:	  function(li, response) {  },
			 afterDelete:	function(li, response)
			 {
				 try
				 {
					 response = eval('(' + response + ')');
					 CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
				 }
				 catch(e)
				 {
					 return false;
				 }
			 }.bind(this)
		 }, []);

		$A(typeList).each(function(el)
		{
			new Backend.RatingType.PostEntry(container, template, el, false);
		});

		ActiveList.prototype.getInstance(container.down('.typeList')).touch(true);

		CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
	},

	showAddForm: function()
	{
		$H($(this.container.down('.typeList')).getElementsByTagName('li')).each(function(li)
		{
			if (li && li[1] && li[1].handler)
			{
				li[1].handler.cancelEditForm();
			}
		});

		var menu = new ActiveForm.Slide(this.container.down('.addTypeMenu'));
		menu.show('addRatingType', this.container.down('.addRatingTypeForm'));
	},

	hideAddForm: function()
	{
		var menu = new ActiveForm.Slide(this.container.down('.addTypeMenu'));
		menu.hide('addRatingType', this.container.down('.addRatingTypeForm'));
	}
}

Backend.RatingType.PostEntry = Class.create();
Backend.RatingType.PostEntry.prototype =
{
	data: null,

	node: null,

	list: null,

	container: null,

	initialize: function(container, template, data, highlight, isNew)
	{
		this.container = container;

		this.data = data;

		this.list = ActiveList.prototype.getInstance(this.container.down('.typeList'));

		this.node = this.list.addRecord(data.ID, template.innerHTML, highlight);

		this.updateHtml();

		this.node.handler = this;

		Element.show(this.node);
	},

	showEditForm: function()
	{
		this.container.handler.hideAddForm();

		var nodes = this.node.parentNode.getElementsByTagName('li');
		$H(nodes).each(function(li)
		{
			if (li && li[1] && li[1].handler && li != this.node)
			{
				li[1].handler.cancelEditForm();
			}
		});

		var form = this.container.down('.ratingTypeForm').cloneNode(true);

		$H(this.data).each(function(el)
		{
			if (form.elements.namedItem(el[0]))
		 	{
				form.elements.namedItem(el[0]).value = el[1];
			}
		});
		form.elements.namedItem('id').value = this.data['ID'];

		this.node.down('div.formContainer').appendChild(form);

		form.down('a.cancel').onclick = this.cancelEditForm.bindAsEventListener(this);
		form.onsubmit = this.save.bindAsEventListener(this);

		new Backend.LanguageForm(form);

		this.list.toggleContainerOn(this.list.getContainer(this.node, 'edit'));
	},

	cancelEditForm: function(e)
	{
		if (!this.list.isContainerEmpty(this.node, 'edit'))
		{
			this.list.toggleContainerOff(this.list.getContainer(this.node, 'edit'));
		}

		var formContainer = this.node.down('div.formContainer');

		if (!formContainer.firstChild)
		{
			return;
		}

		formContainer.innerHTML = '';

		if (e)
		{
			Event.stop(e);
		}
	},

	save: function(e)
	{
		Element.saveTinyMceFields(this.node);
		var form = this.node.down('form');
		form.action = this.container.down('.saveUrl').innerHTML;
		new LiveCart.AjaxRequest(form, null, this.update.bind(this));
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
		this.node.down('.newsTitle').innerHTML = this.data.name;
		this.node.id = 'newsEntry_' + this.data.ID;
	}
}

Backend.RatingType.Add = Class.create();
Backend.RatingType.Add.prototype =
{
	form: null,

	container: null,

	initialize: function(form)
	{
		this.container = form.up('.tabRatingCategories');
		new LiveCart.AjaxRequest(form, null, this.onComplete.bind(this));
	},

	onComplete: function(originalRequest)
	{
		new Backend.RatingType.PostEntry(this.container, this.container.down('.typeList_template'), originalRequest.responseData, true, true);
		CategoryTabControl.prototype.resetTabItemsCount(this.container.handler.categoryID);
		this.container.handler.hideAddForm();
	}
}
