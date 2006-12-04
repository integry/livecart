Backend.Customize = Class.create();
Backend.Customize.prototype = 
{		
	controllerUrl: false,
	
	currentElement: false,
	
	initialize: function()
	{
	  
	},
	
	setControllerUrl: function(url)
	{
		this.controllerUrl = url;  
	},

	initLang: function()
	{
		elements = document.getElementsByClassName('transMode');  
		for (k in elements)
		{
		  	elements[k].onmousemove = function(e) {cust.showTranslationMenu(this, e);}
		}
	},
	
	showTranslationMenu: function(element, e)
	{
		dialog = document.getElementById('transDialogMenu');
		
		xPos = e.pageX + 5;
		yPos = e.pageY;
		
		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 100, 50);
		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		dialog.style.display = 'block';	
		
		this.currentElement = element;			
	},
	
	translationMenuClick: function(e)
	{
		this.showTranslationDialog(this.currentElement, e);  	
		document.getElementById('transDialogMenu').style.display = 'none';
	},
	
	showTranslationDialog: function(element, e)
	{
		id = element.className.split(' ')[1];
		id = id.substr(8, id.length);

		file = element.className.split(' ')[2];
		file = file.substr(6, file.length);
	
		url = this.controllerUrl + '/translationDialog?id=' + id + '&file=' + file;

		dialog = document.getElementById('transDialogBox');
		
		xPos = e.pageX;
		yPos = e.pageY;
		
		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 300, 77);
		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		dialog.style.display = 'block';
		
		document.getElementById('transDialogContent').style.display = 'none';
		document.getElementById('transDialogIndicator').style.display = 'block';
				
		self = this;
		new Ajax.Updater('transDialogContent', url, {onComplete: self.displayDialogContent});
		
		Event.observe(document, 'mousedown', cust.cancelTransDialog, false);
	},
	
	displayDialogContent: function()
	{
		document.getElementById('transDialogContent').style.display = 'block';
		document.getElementById('transDialogIndicator').style.display = 'none';
	},

	saveTranslationDialog: function(form)
	{
		this.showTranslationSaveIndicator(); 
		this.updateDocumentTranslations(form.elements.namedItem('id').value, form.elements.namedItem('translation').value);
		new LiveCart.AjaxUpdater(form, 'translationDialog', 'transSaveIndicator'); 
	},
	
	showTranslationSaveIndicator: function()
	{
		indicator = document.getElementById('transSaveIndicator');
		button = document.getElementById('transDialogSave');
		button.parentNode.replaceChild(indicator, button);
	},
	
	updateDocumentTranslations: function(transKey, translation)
	{
	  	elements = document.getElementsByClassName('__trans_' + transKey);
		for (k = 0; k < elements.length; k++)
	  	{
			elements[k].innerHTML = translation;
			new Effect.Highlight(elements[k], {startcolor:'#FBFF85', endcolor:'#FFFFFF'})
		}
	},
	
	cancelTransDialog: function()
	{
	  	document.getElementById('translationDialog').style.display = 'none'; 
		return false;
	},
	
	stopTransCancel: function(e)
	{
        Event.stop(e);
	},
	
}