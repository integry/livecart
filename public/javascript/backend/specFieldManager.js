if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.SpecFieldManager = Class.create();
LiveCart.SpecFieldManager.prototype = {	
	/**
	 * Every time new field value is created it uses this number to find field node. After 
	 * new value is created thi number is incremented to give unique id to every new value
	 *
	 * @var int
	 */
	countNewValues: 0,
	
	selectorValueTypes: [],
	doNotTranslateTheseValueTypes: [],

	/**
	 * This hash table stores all field types
	 *
	 * @var Array
	 */
	types: new Array(),
	
	/**
	 * This array stores all available languages codes. (this.languageCodes[0] is default language code)
	 *
	 */
	languageCodes: [],
	
	/**
	 * This hash table stores all language titles by code
	 *
	 * @var Array
	 */
	languages: new Array(),
	
	/**
	 * Constructor
	 *
	 * @var types Hash of options (where hash key is value type and value is array of Option objects)
	 */
	initialize: function(specField) 
	{		
		this.id = specField.id;
		this.type = specField.type;
		this.values = specField.values;
		this.handle = specField.handle;
		this.valueType = specField.valueType;
		this.translations = specField.translations;
				
		this.findUsedNodes();
		this.bindFields();
	},
	
	
	/**
	 * Find ussed nodes
	 *
	 */
	findUsedNodes: function()
	{
		if(!this.nodes) this.nodes = [];
		
		this.nodes.parent = document.getElementById("specField-item-"+this.id);
		
		this.nodes.valueType 			= document.getElementsByClassName("specField-form-valueType", this.nodes.parent)[0].getElementsByTagName("input");
		this.nodes.type 				= document.getElementsByClassName("specField-form-type", this.nodes.parent)[0];
		this.nodes.stateLinks 			= document.getElementsByClassName("change-state", this.nodes.parent);
		this.nodes.stepTranslations 	= document.getElementsByClassName("step-translations", this.nodes.parent)[0];
		this.nodes.stepMain 			= document.getElementsByClassName("step-main", this.nodes.parent)[0];
		
		this.nodes.stepLevOne 			= document.getElementsByClassName("step-lev1", this.nodes.parent);
		this.nodes.id 					= document.getElementsByClassName("specField-form-id", this.nodes.parent)[0];
		this.nodes.description 			= document.getElementsByClassName("specField-form-description", this.nodes.parent)[0];
		this.nodes.handle 				= document.getElementsByClassName("specField-form-handle", this.nodes.parent)[0];
		this.nodes.title 				= document.getElementsByClassName("specField-form-title", this.nodes.parent)[0];
		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName("specField-form-values-group", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.valuesDefaultGroup.getElementsByClassName("add-field", this.nodes.parent)[0];
	},
	
	bindTranslationValues: function()
	{
		this.nodes.translatedValues = document.getElementsByClassName("specField-form-values-translations", this.nodes.parent);
	},
	
	
	
	/**
	 * Binds fields to some events
	 *
	 */
	bindFields: function()
	{
		var self = this;
		
		for(var i = 0; i < this.nodes.valueType.length; i++)
		{
			this.nodes.valueType[i].onclick = this.valueTypeChangedAction.bind(this);
		}
		
		for(var i = 0; i < this.nodes.stateLinks.length; i++)
		{
			this.nodes.stateLinks[i].onclick = this.changeStateAction.bind(this);
		}

		this.nodes.title.onkeyup = self.generateHandleAction.bind(self);
		this.nodes.valuesAddFieldLink.onclick = self.addValueFieldAction.bind(self);
		this.nodes.type.onchange = self.typeWasChangedAction.bind(self);
		
		// Some actions must be executed on load. Also be aware of the order in which those actions are called
		this.loadLanguagesAction();
		this.loadSpecFieldAction();
		this.loadValueFieldsAction();
	},
	
	
	typeWasChangedAction: function()
	{
		// if selected type is a selector type then show selector options fields (aka step 2)
		if(this.selectorValueTypes.indexOf(this.nodes.type.value) === -1)
		{
			this.nodes.stateLinks[1].style.display = 'none';
			for(var i = 0; i < this.nodes.translatedValues.length; i++) 
			{
				this.nodes.translatedValues[i].style.display = 'none';
			}
		}
		else
		{
			this.nodes.stateLinks[1].style.display = 'inline';
			for(var i = 0; i < this.nodes.translatedValues.length; i++) 
			{
				this.nodes.translatedValues[i].style.display = (this.doNotTranslateTheseValueTypes.indexOf(this.valueType) === -1) ? 'block' : 'none';
			}
		}
	},
	
	
	/**
	 * Find all delete value links (this function is needed because we need to refresh links list when new value is added or removed)
	 * 
	 */
	bindDeleteLinks: function()
	{
		var deleteValueLinks = document.getElementsByClassName("delete-value", this.nodes.valuesDefaultGroup);
		for(var i = 0; i < deleteValueLinks.length; i++)
		{
			deleteValueLinks[i].onclick = this.deleteValueFieldAction.bind(this);
		}
	},
	
	
	bindDefaultFields: function()
	{
		var self = this;
		$A(this.nodes.valuesDefaultGroup.getElementsByTagName("input")).each(function(input) 
		{
			input.onkeyup = self.mainValueFieldChangedAction.bind(self);
		});
	},
	
	
	loadSpecFieldAction: function()
	{	
		// Default language		
		this.nodes.id.value = this.id;
		this.nodes.handle.value = this.handle;
		
		this.nodes.title.value = this.translations[this.languageCodes[0]].title;
		this.nodes.title.name = "translations[" + this.languageCodes[0] + "][title]";
		
		this.nodes.description.value = this.translations[this.languageCodes[0]].description;
		this.nodes.description.name = "translations[" + this.languageCodes[0] + "][description]";
				
		// select valueType (or first)
		if(this.valueType)
		{
			for(var i = 0; i < this.nodes.valueType.length; i++)
			{
				if(this.nodes.valueType[i].value == this.valueType)
				{
					this.nodes.valueType[i].checked = true;
					break;
				}
			}
		}
		else if(this.nodes.valueType.length > 0)
		{
			this.nodes.valueType[0].checked = true;
		}
		
		// load types and select one
		if(this.type)
		{
			for(var i = 0; i < this.nodes.type.options.length; i++)
			{
				if(this.nodes.type.options[i].value == this.type)
				{
					this.nodes.type.selectedIndex = i;
					break;
				}
			}
		}
		
		
		// Translations
		var translations = document.getElementsByClassName("step-translations-language", this.nodes.stepTranslations);
		// we should have a template to continue
		if(translations.length > 0 && translations[0].className.split(' ').indexOf('dom-template') !== -1)
		{
			this.nodes.translations = new Array();
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				// copy template class
				var newTranslation = translations[0].cloneNode(true);
				Element.removeClassName(newTranslation, "dom-template");
				
				newTranslation.getElementsByTagName("legend")[0].appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));
				
				var inputFields = newTranslation.getElementsByTagName('input');
				for(var j = 0; j < inputFields.length; j++)
				{
					inputFields[j].value = (this.translations[this.languageCodes[i]] && this.translations[this.languageCodes[i]][inputFields[j].name]) ? this.translations[this.languageCodes[i]][inputFields[j].name] : '';
					inputFields[j].name = "translations[" + this.languageCodes[i] + "][" + inputFields[j].name + "]";
				}

				var textareaFields = newTranslation.getElementsByTagName('textarea');
				for(var j = 0; j < textareaFields.length; j++)
				{
					if(textareaFields[j].parrentNode = newTranslation)
					{
						textareaFields[j].value = (this.translations[this.languageCodes[i]] && this.translations[this.languageCodes[i]][textareaFields[j].name]) ? this.translations[this.languageCodes[i]][textareaFields[j].name] : '';
						textareaFields[j].name = "translations[" + this.languageCodes[i] + "][" + textareaFields[j].name + "]";
					}
				}
				
				this.nodes.stepTranslations.appendChild(newTranslation);
				
				// add to nodes list
				
				this.nodes.translations[this.languageCodes[i]] = newTranslation;
			}
		}
		
		this.bindTranslationValues();
		this.valueTypeChangedAction();
	},
	
	
	/**
	 * Load values when page is loaded
	 *
	 */
	loadValueFieldsAction: function()
	{
		var self = this;
		
		$H(this.values).each(function(value) {
			self.addField(value.value, value.key)
		});
		
		this.bindDeleteLinks();
	},
	
	
	/**
	 * Load languages
	 * 
	 */
	loadLanguagesAction: function()
	{
		var self = this;
		if(!this.languageCodes) this.languageCodes = [];
		
		$H(this.languages).each(function(language) {
			self.languageCodes[self.languageCodes.length] = language.key;
		});
	},
	
	
	/**
	 * Add new field to values
	 *
	 */
	addValueFieldAction: function(e)
	{
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}
		
		Event.stop(e);
				
		this.addField(null, null);
		
		this.bindDeleteLinks();
	},
	
	
	/**
	 * Delete field
	 *
	 */
	deleteValueFieldAction: function(e)
	{
//		if(confirm(this.messages.deleteField)) 
//		{
			if(!e)
			{
				e = window.event;
				e.target = e.srcElement;
			}
			
			Event.stop(e);
					
			var splitedHref = e.target.parentNode.id.split("-");
			var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
			var id = (isNew ? 'new-' : '') + splitedHref[splitedHref.length - 1];
			
			for(var i = 0; i < this.languageCodes.length; i++)
			{
				var translatedValue = document.getElementById("specField-form-values-" + this.languageCodes[i] + "-" + id);
				Element.remove(translatedValue);
			}
			
			if(!isNew)
			{
				// send AJAX request to remove field from database
			}
			
			this.bindDeleteLinks();
//		}
	},
	
	
	/**
	 * This callback is executed when user change the value type
	 *
	 */
	valueTypeChangedAction: function(e) 
	{
		this.nodes.type.length = 0;
		for(var i = 0; i < this.nodes.valueType.length; i++)
		{
			if(this.nodes.valueType[i].checked) 
			{
				for(var j = 0; j < this.types[this.nodes.valueType[i].value].length; j++)
				{
					this.nodes.type.options[j] = this.types[this.nodes.valueType[i].value][j];
				}
				
				this.valueType = this.nodes.valueType[i].value;
			}
		}
		
		
		this.typeWasChangedAction();		
	},
	
	
	/**
	 * This callback is executed when user changes the state. When user change the state all other
	 * states are hidden and only current state s shown
	 *
	 */
	changeStateAction: function(e)
	{		
		var self = this;
		
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}		
		
		Event.stop(e);
		
		var currentStep = e.target.hash.substring(1);
		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
			this.nodes.stepLevOne[i].style.display = (this.nodes.stepLevOne[i].className.split(' ').indexOf(currentStep) === -1 || this.nodes.stepLevOne[i].style.display == 'block') ? 'none' : 'block';
		}
	},
	
	
	mainValueFieldChangedAction: function(e)
	{
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}		
		
		Event.stop(e);

		var splitedHref = e.target.parentNode.id.split("-");
		var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
		var id = (isNew ? 'new-' : '') + splitedHref[splitedHref.length - 1];
		
		for(var i = 1; i < this.languageCodes.length; i++)
		{
			$("specField-form-values-" +  this.languageCodes[i] + "-" + id).getElementsByTagName("label")[0].firstChild.nodeValue = e.target.value;
		}
	},
	
	
	/**
	 * Automatically generates field name from title 
	 *
	 */
	generateHandleAction: function(e)
	{
		var handle = this.nodes.title.value;
		
		handle = handle.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,""); // trim
		handle = handle.replace(/[^a-zA-Z_\d ]/g, ""); // remove all illegal simbols
		handle = handle.replace(/^[\d\_]+/g, "_"); // replace first digits with "_"
		handle = handle.replace(/ /g, "_"); // reokace spaces with "_"
		handle = handle.toLowerCase();
		
		this.nodes.handle.value = handle;
	},	
	
	
	/**
	 * Add new value field 
	 *
	 */
	addField: function(value, id)
	{
		var values = document.getElementsByClassName("specField-form-values-value", this.nodes.valuesDefaultGroup);
		
		// If we have a template class then copy it
		if(values.length > 0 && values[0].className.split(' ').indexOf('dom-template') !== -1) 
		{
			var newValue = values[0].cloneNode(true);
			Element.removeClassName(newValue, "dom-template");
			
			newValue.id = newValue.id + this.languageCodes[0] + "-" + (id ? id :  "new-" + this.countNewValues);
					
			var input = newValue.getElementsByTagName("input")[0];
			input.name = "values[" + this.languageCodes[0] + "]" + (id ? "["+id+"]" : '[new][]');
			input.value = (value && value[this.languageCodes[0]]) ? value[this.languageCodes[0]] : '' ;
			
			this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0].appendChild(newValue);
			
			// now insert all translation fields
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				var newValueTranslation = document.getElementsByClassName("specField-form-values-value", this.nodes.translations[this.languageCodes[i]])[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom-template");
				
				newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "-" + (id ? id :  "new-" + this.countNewValues);
				
				var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
				inputTranslation.name = "values[" + this.languageCodes[i] + "]" + (id ? "["+id+"]" : '[new][]');
				inputTranslation.value = (value && value[this.languageCodes[i]]) ? value[this.languageCodes[i]] : '' ;
				
				var label = newValueTranslation.getElementsByTagName("label")[0];
				label.appendChild(document.createTextNode(input.value));
				
				// add to node tree
				document.getElementsByClassName("specField-form-values-translations", this.nodes.translations[this.languageCodes[i]])[0].appendChild(newValueTranslation);
			}
			
			if(!id) this.countNewValues++;
			this.bindDeleteLinks();
			this.bindDefaultFields();
		}
		else
		{
			return false;
		}
	}
}