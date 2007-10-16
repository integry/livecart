/**
 *	@author Integry Systems
 */

var TranslationMenuEvent = Class.create();
TranslationMenuEvent.prototype = 
{  
  	element: false,
  	
  	translationHandler: false,
  	
	initialize: function(element, translationHandler)
  	{
		this.element = element;
		this.translationHandler = translationHandler;
		this.eventMouseMove = this.move.bindAsEventListener(this);
		Event.observe(this.element, 'mousemove', this.eventMouseMove);
	},
	
	move: function(e)
	{
		this.translationHandler.showTranslationMenu(this.element, e);
	}
}

Customize = Class.create();
Customize.prototype = {		
  
	controllerUrl: false,
	
	currentElement: false,
	
	initialValue: null,
	
	currentId: false,
	
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
		  	new TranslationMenuEvent(elements[k], this);
		}
	},
	
	showTranslationMenu: function(element, e)
	{
		if (element == this.currentElement)
		{
			return false;
		}

/*
		xPos = Event.pointerX(e) - 5;
		yPos = Event.pointerY(e) - 5;
*/		
		var pos = Position.cumulativeOffset(element);
		
		xPos = pos[0];
		yPos = pos[1] - 23;
		
		var dialog = $('transDialogMenu');	
		
		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 100, 30);
		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		Element.show(dialog);
		this.currentElement = element;			
		Event.observe(document, 'click', this.hideTranslationMenu.bindAsEventListener(this), false);
	},
		
	hideTranslationMenu: function()
	{		
		Element.hide($('transDialogMenu'));
		
		this.currentElement = null;
	},

	translationMenuClick: function(e)
	{
		e.stopPropagation();
		this.showTranslationDialog(this.currentElement, e);  	
		this.hideTranslationMenu();
		Event.stop(e);
	},
	
	showTranslationDialog: function(element, e)
	{		
		this.initialValue = null;
		
		id = element.className.split(' ')[1];
		id = id.substr(8, id.length);
		
		this.currentId = id;

		file = element.className.split(' ')[2];
		file = file.substr(6, file.length);
	
		url = this.controllerUrl + '/translationDialog?id=' + id + '&file=' + file;

		dialog = document.getElementById('transDialogBox');

		xPos = Event.pointerX(e);
		yPos = Event.pointerY(e);

		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 300, 77);

		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		dialog.style.display = 'block';

		document.getElementById('transDialogContent').style.display = 'none';
		document.getElementById('transDialogIndicator').style.display = 'block';

		new LiveCart.AjaxRequest(url, null, this.displayDialogContent.bind(this));
		
		this.bfx = this.cancelTransDialog.bind(this);
		
		Event.observe(document, 'mousedown', this.bfx, false);
	},
	
	handleTransFieldClick: function(e)
	{
	 	e.stopPropagation();
	},

	displayDialogContent: function(originalRequest)
	{
		window.req = originalRequest;

		$('transDialogIndicator').hide();
			
		if (originalRequest.getResponseHeader('NeedLogin'))
		{
			$('transDialogContent').update('');
			$('transDialogContent').hide();
			return false;
		}
	
		$('transDialogContent').update(originalRequest.responseText);
		$('transDialogContent').show();
		Event.observe($('trans'), 'mousedown', this.handleTransFieldClick.bindAsEventListener(this), true);
	},

	saveTranslationDialog: function(form)
	{
		form.elements.namedItem('translation').value = document.getElementById('trans').value;
		this.showTranslationSaveIndicator(); 
		this.updateDocumentTranslations(form.elements.namedItem('id').value, form.elements.namedItem('translation').value);
		
        new LiveCart.AjaxUpdater(form, 'translationDialog', 'transSaveIndicator'); 
		Event.stopObserving(document, 'mousedown', this.bfx, false);
	},
	
	showTranslationSaveIndicator: function()
	{
		indicator = document.getElementById('transSaveIndicator');
		button = document.getElementById('transDialogSave');
		button.parentNode.replaceChild(indicator, button);
	},
	
	/**
	 * @todo disable for IE (too slow)
	 **/
	previewTranslations: function(transKey, translation)
	{
		elements = document.getElementsByClassName('__trans_' + transKey);  		
		for (k = 0; k < elements.length; k++)
	  	{
			if (!this.initialValue)
			{
			  	this.initialValue = elements[k].innerHTML;
			}
			elements[k].innerHTML = translation;	
		}
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
	  	if (null != this.initialValue)
	  	{
			this.previewTranslations(this.currentId, this.initialValue);		    
		}
		
		if ($('translationDialog'))
		{
			$('translationDialog').hide();
		}

		return false;
	},
	
	stopTransCancel: function(e)
	{
        Event.stop(e);
	}	
}