if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.SpecFieldManager = Class.create();
LiveCart.SpecFieldManager.prototype = {
	/**
	 * Constructor
	 *
	 * @var types Hash of options (where hash key is value type and value is array of Option objects)
	 */
	initialize: function(specField)
	{
	    this.id = specField.id;
	    this.rootId = specField.rootId ? specField.rootId : 'specField_item';
		this.type = specField.type;
		this.values = specField.values;
		this.handle = specField.handle;
		this.multipleSelector = specField.multipleSelector;
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

		this.nodes.parent = document.getElementById(this.rootId);

		this.nodes.valueType 			= document.getElementsByClassName("specField_form_valueType", this.nodes.parent)[0].getElementsByTagName("input");
		this.nodes.type 				= document.getElementsByClassName("specField_form_type", this.nodes.parent)[0];
		this.nodes.stateLinks 			= document.getElementsByClassName("change_state", this.nodes.parent);
		this.nodes.stepTranslations 	= document.getElementsByClassName("step_translations", this.nodes.parent)[0];
		this.nodes.stepMain 			= document.getElementsByClassName("step_main", this.nodes.parent)[0];

		this.nodes.stepLevOne 			= document.getElementsByClassName("step_lev1", this.nodes.parent);
		this.nodes.mainTitle 			= document.getElementsByClassName("specField_title", this.nodes.parent)[0];
		this.nodes.id 					= document.getElementsByClassName("specField_form_id", this.nodes.parent)[0];
		this.nodes.description 			= document.getElementsByClassName("specField_form_description", this.nodes.parent)[0];
		this.nodes.multipleSelector 	= document.getElementsByClassName("specField_form_multipleSelector", this.nodes.parent)[0];
		this.nodes.handle 				= document.getElementsByClassName("specField_form_handle", this.nodes.parent)[0];
		this.nodes.title 				= document.getElementsByClassName("specField_form_title", this.nodes.parent)[0];
		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName("specField_form_values_group", this.nodes.parent)[0];
		this.nodes.translationsLinks 	= document.getElementsByClassName("specFields_form_values_translations_language_links", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.valuesDefaultGroup.getElementsByClassName("add_field", this.nodes.parent)[0];
	},

	bindTranslationValues: function()
	{
		this.nodes.translatedValues = document.getElementsByClassName("specField_form_values_translations", this.nodes.parent);
	},



	/**
	 * Binds fields to some events
	 *
	 */
	bindFields: function()
	{
		for(var i = 0; i < this.nodes.valueType.length; i++)
		{
			this.nodes.valueType[i].onclick = this.valueTypeChangedAction.bind(this);
		}

		for(var i = 0; i < this.nodes.stateLinks.length; i++)
		{
			this.nodes.stateLinks[i].onclick = this.changeStateAction.bind(this);
		}

		this.nodes.title.onkeyup = this.generateHandleAndTitleAction.bind(this);
		this.nodes.valuesAddFieldLink.onclick = this.addValueFieldAction.bind(this);
		this.nodes.type.onchange = this.typeWasChangedAction.bind(this);

		// Some actions must be executed on load. Also be aware of the order in which those actions are called
		this.loadLanguagesAction();
		this.createLanguagesLinks();
		this.loadSpecFieldAction();
		this.loadValueFieldsAction();
		this.bindTranslationValues();
		this.valueTypeChangedAction();
		this.loadTypes();
		this.typeWasChangedAction();
	},

	loadTypes: function()
	{
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


	bindDefaultFields: function()
	{
		var self = this;
		$A(this.nodes.valuesDefaultGroup.getElementsByTagName("input")).each(function(input)
		{
			input.onkeyup = self.mainValueFieldChangedAction.bind(self);
		});

		try
		{
    		require_once('backend/activeList.js');
    	    new LiveCart.ActiveList(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0], {
    	        beforeSort: function(li, order){ return 'sort.php?'+order},
    	        afterSort: function(li, response){ },
    	        beforeEdit: function(li){ },
    	        afterEdit: function(li, response){ },
    	        beforeDelete: function(li){ if(confirm('Are you realy want to delete this item?')) return 'delete.php?id='+this.getRecordId(); },
    	        afterDelete: function(li, response){ Element.remove(li) }
    	    });
		}
		catch(e)
		{
		    jsTrace.debug(e)
		}
	},


	loadSpecFieldAction: function()
	{
		// Default language
		this.nodes.id.value = this.id;
		this.nodes.handle.value = this.handle;

		this.nodes.title.value = this.translations[this.languageCodes[0]].title;
		this.nodes.title.name = "translations[" + this.languageCodes[0] + "][title]";

		this.nodes.multipleSelector.checked = this.multipleSelector ? true : false;

		this.nodes.mainTitle.firstChild.nodeValue = this.nodes.title.value;

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


		// Translations
		var translations = document.getElementsByClassName("step_translations_language", this.nodes.stepTranslations);
		// we should have a template to continue
		if(translations.length > 0 && translations[0].className.split(' ').indexOf('dom_template') !== -1)
		{
			this.nodes.translations = new Array();
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				// copy template class
				var newTranslation = translations[0].cloneNode(true);
				Element.removeClassName(newTranslation, "dom_template");

				newTranslation.className += this.languageCodes[i];

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
	},


	/**
	 * Load values when page is loaded
	 *
	 */
	loadValueFieldsAction: function()
	{
		var self = this;

		if(this.values)
		{
			$H(this.values).each(function(value) {
				self.addField(value.value, value.key)
			});
		}
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

	createLanguagesLinks: function()
	{
		var languageTemplateLink = document.getElementsByClassName("dom_template", this.nodes.translationsLinks)[0];

		for(var i = 1; i < this.languageCodes.length; i++)
		{
			var languageLinkDiv = languageTemplateLink.cloneNode(true);
			Element.removeClassName(languageLinkDiv, "dom_template");

			var languageLink = languageLinkDiv.getElementsByTagName("a")[0];
			languageLink.hash += this.languageCodes[i];
			var test = this.languages[this.languageCodes[i]];
			languageLink.firstChild.nodeValue = this.languages[this.languageCodes[i]];

			this.nodes.translationsLinks.appendChild(languageLinkDiv);

			// bind it
			languageLinkDiv.onclick = this.changeTranslationLanguageAction.bind(this);
		}
	},

	changeTranslationLanguageAction: function(e)
	{
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

		Event.stop(e);

		var currentLanguageClass = e.target.hash.substring(1);
		var translationsNodes = document.getElementsByClassName("step_translations_language", this.nodes.stepTranslations);

		for(var i = 0; i < translationsNodes.length; i++)
		{
			translationsNodes[i].style.display = (translationsNodes[i].className.split(' ').indexOf(currentLanguageClass) === -1 || translationsNodes[i].style.display == 'block') ? 'none' : 'block';
		}
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

		this.addField(null, "new_" + this.countNewValues);
		this.countNewValues++;
	},


	/**
	 * Delete field
	 *
	 */
	deleteValueFieldAction: function(e)
	{
		if(confirm(this.messages.deleteField))
		{
			if(!e)
			{
				e = window.event;
				e.target = e.srcElement;
			}

			Event.stop(e);

			var splitedHref = e.target.parentNode.id.split("_");
			var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
			var id = (isNew ? 'new_' : '') + splitedHref[splitedHref.length - 1];

			for(var i = 0; i < this.languageCodes.length; i++)
			{
				var translatedValue = document.getElementById("specField_form_values_" + this.languageCodes[i] + "_" + id);

				// if new or not main language
				if(isNew || i > 0)
    			{
    				Element.remove(translatedValue);
    			}
    			else
    			{
    			    translatedValue.id += '_deleted';
    			    var input = translatedValue.getElementsByTagName('input')[0];
    			    input.name = input.name.replace(/\[\w+\]\[([\d]+)\]/, "[deleted][$1]");
    			    translatedValue.style.display = 'none';
    			}
			}
		}
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
					this.nodes.type.options[j] = this.types[this.nodes.valueType[i].value][j].cloneNode(true);
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

		var splitedHref = e.target.parentNode.id.split("_");
		var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
		var id = (isNew ? 'new_' : '') + splitedHref[splitedHref.length - 1];

		for(var i = 1; i < this.languageCodes.length; i++)
		{
			$("specField_form_values_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].firstChild.nodeValue = e.target.value;
		}
	},


	/**
	 * Automatically generates field name from title
	 *
	 */
	generateHandleAndTitleAction: function(e)
	{
		// generate handle
		var handle = this.nodes.title.value;

		handle = handle.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,""); // trim
		handle = handle.replace(/[^a-zA-Z_\d ]/g, ""); // remove all illegal simbols
		handle = handle.replace(/^[\d\_]+/g, "_"); // replace first digits with "_"
		handle = handle.replace(/ /g, "_"); // reokace spaces with "_"
		handle = handle.toLowerCase();

		this.nodes.handle.value = handle;

		this.nodes.mainTitle.firstChild.nodeValue = this.nodes.title.value;
	},


	/**
	 * Add new value field
	 *
	 */
	addField: function(value, id)
	{
	    var values = document.getElementsByClassName("specField_form_values_value", this.nodes.valuesDefaultGroup);

		// If we have a template class then copy it
		if(values.length > 0 && values[0].className.split(' ').indexOf('dom_template') !== -1)
		{
			var newValue = values[0].cloneNode(true);
			Element.removeClassName(newValue, "dom_template");

			newValue.id = newValue.id + this.languageCodes[0] + "_" + id;

			var input = newValue.getElementsByTagName("input")[0];
			input.name = "values[" + this.languageCodes[0] + "]" + (id ? "["+id+"]" : '[new][]');
			input.value = (value && value[this.languageCodes[0]]) ? value[this.languageCodes[0]] : '' ;

			var ul = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];
			ul.id = 'specField_form_'+this.id+'_values_'+this.languageCodes[0];
			ul.appendChild(newValue);

			// now insert all translation fields
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				var newValueTranslation = document.getElementsByClassName("specField_form_values_value", this.nodes.translations[this.languageCodes[i]])[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");

				newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;

				var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
				inputTranslation.name = "values[" + this.languageCodes[i] + "]" + (id ? "["+id+"]" : '[new][]');
				inputTranslation.value = (value && value[this.languageCodes[i]]) ? value[this.languageCodes[i]] : '' ;

				var label = newValueTranslation.getElementsByTagName("label")[0];
				label.appendChild(document.createTextNode(input.value));

				// add to node tree
				var translationsUl = document.getElementsByClassName("specField_form_values_translations", this.nodes.translations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
				translationsUl.id = 'specField_form_'+this.id+'_values_'+this.languageCodes[i];
				translationsUl.appendChild(newValueTranslation);
			}

			this.bindDefaultFields();
		}
		else
		{
			return false;
		}
	}
}