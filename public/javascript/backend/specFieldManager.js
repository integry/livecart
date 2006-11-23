/**
 * LiveCart.SpecFieldManager
 *
 * Script for managing spec field form
 *
 * The following class manages spec field forms. I have used an separate js file (a class)
 * because there are a lot of thing happening when you are dealing with spec fields forms.
 *
 * To use this class you should simply pass specFIelds values to it like so
 * @example
 * <code>
 *     new LiveCart.SpecFieldManager({
 *        "ID":"new",
 *        "name":"a:2:{s:2:\"en\";s:11:\"Electronics\";s:2:\"lt\";s:11:\"Elektronika\";}",
 *        "description":[],
 *        "handle":"",
 *        "values":[],
 *        "rootId": "specField_item_new",
 *        "type":5,
 *        "dataType":2
 *     });
 * </code>
 *
 * I hope whoever reads this will figure aut what each value means. Name, description and values
 * can have multiple values for each language
 *
 * Also you should know that some values are not meant to be passed to constructor (it will also
 * work fine... meaby) Here is an example
 *
 * @example
 * <code>
 *     LiveCart.SpecFieldManager.prototype.languages = {"en":"English","lt":"Lithuanian","de":"German"};
 *     LiveCart.SpecFieldManager.prototype.types = createTypesOptions({"2":{"1":"Selector","2":"Numbers"},"1":{"3":"Text","4":"Word processer","5":"selector","6":"Date"}});
 *     LiveCart.SpecFieldManager.prototype.messages = {"deleteField":"delete field"};
 *     LiveCart.SpecFieldManager.prototype.selectorValueTypes = [1,5];
 *     LiveCart.SpecFieldManager.prototype.doNotTranslateTheseValueTypes = [2];
 *     LiveCart.SpecFieldManager.prototype.countNewValues = 0;
 * </code>
 *
 * @version 1.0
 * @author Sergej Andrejev
 */

if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.SpecFieldManager = Class.create();
LiveCart.SpecFieldManager.prototype = {
	/**
	 * Constructor
	 *
	 * @var specFields Spec Field values
	 *
	 * @access public
	 *
	 */
	initialize: function(specField)
	{
	    this.id = specField.ID;
	    this.rootId = specField.rootId ? specField.rootId : 'specField_item';
		this.type = specField.type;
		this.values = specField.values;

		this.name = specField.name;
		this.description = specField.description;

		this.handle = specField.handle;
		this.multipleSelector = specField.multipleSelector;
		this.dataType = specField.dataType;
		this.translations = specField.translations;

		this.isNew = specField.isNew;

		this.findUsedNodes();
		this.bindFields();
	},


	/**
	 * Find ussed nodes
	 *
	 * @access private
	 *
	 */
	findUsedNodes: function()
	{
		if(!this.nodes) this.nodes = [];

		this.nodes.parent = document.getElementById(this.rootId);

		this.nodes.dataType 			= document.getElementsByClassName("specField_form_dataType", this.nodes.parent)[0].getElementsByTagName("input");
		this.nodes.type 				= document.getElementsByClassName("specField_form_type", this.nodes.parent)[0];
		this.nodes.stateLinks 			= document.getElementsByClassName("change_state", this.nodes.parent);
		this.nodes.stepTranslations 	= document.getElementsByClassName("step_translations", this.nodes.parent)[0];
		this.nodes.stepMain 			= document.getElementsByClassName("step_main", this.nodes.parent)[0];

		this.nodes.stepLevOne 			= document.getElementsByClassName("step_lev1", this.nodes.parent);

		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
            var test = this.nodes.stepLevOne[i];
		    if(!this.nodes.stepLevOne[i].id) this.nodes.stepLevOne[i].id = this.nodes.stepLevOne[i].className.replace(/ /, "_") + "_" + this.id;
		}

		this.nodes.mainTitle 			= document.getElementsByClassName("specField_title", this.nodes.parent)[0];
		this.nodes.id 					= document.getElementsByClassName("specField_form_id", this.nodes.parent)[0];
		this.nodes.description 			= document.getElementsByClassName("specField_form_description", this.nodes.parent)[0];
		this.nodes.multipleSelector 	= document.getElementsByClassName("specField_form_multipleSelector", this.nodes.parent)[0];
		this.nodes.handle 				= document.getElementsByClassName("specField_form_handle", this.nodes.parent)[0];
		this.nodes.title 				= document.getElementsByClassName("specField_form_name", this.nodes.parent)[0];
		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName("specField_form_values_group", this.nodes.parent)[0];
		this.nodes.translationsLinks 	= document.getElementsByClassName("specFields_form_values_translations_language_links", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.valuesDefaultGroup.getElementsByClassName("add_field", this.nodes.parent)[0];
	},

	/**
	 * Find all translations fields. This is done every time when new field is being added
	 *
	 * @access private
	 *
	 */
	bindTranslationValues: function()
	{
		this.nodes.translatedValues = document.getElementsByClassName("specField_form_values_translations", this.nodes.parent);
	},



	/**
	 * Binds fields to some events
	 *
	 * @access private
	 *
	 */
	bindFields: function()
	{
		for(var i = 0; i < this.nodes.dataType.length; i++)
		{
			this.nodes.dataType[i].onclick = this.dataTypeChangedAction.bind(this);
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
		this.dataTypeChangedAction();
		this.loadTypes();
		this.typeWasChangedAction();
	},

	/**
	 * Whem Mike changes input type from "numbers" to "text" programm should select
	 * appropriate value from types list (like selector, text, date, number, etc)
	 *
	 * @access private
	 *
	 */
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

	/**
	 * When the value type changes whe should decide whether show step "Values" (for selectors) or not,
	 * and whether to show translations or not (show for text, hide for numbers)
	 *
	 * @access private
	 *
	 */
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
				this.nodes.translatedValues[i].style.display = (this.doNotTranslateTheseValueTypes.indexOf(this.dataType) === -1) ? 'block' : 'none';
			}
		}
	},


	/**
	 * This method binds all default values (those which are field in "Values" step) and create new fields in "Translations"
	 * step where user can fill translations for those values
	 *
	 * @access private
	 *
	 */
	bindDefaultFields: function()
	{
		var self = this;
		$A(this.nodes.valuesDefaultGroup.getElementsByTagName("input")).each(function(input)
		{
			input.onkeyup = self.mainValueFieldChangedAction.bind(self);
			input.onkeydown = self.mainValueFieldChangedAction.bind(self);
		});

		require_once('backend/activeList.js');
	    new LiveCart.ActiveList(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0], {
	        beforeSort: function(li, order){ return 'sort.php?'+order},
	        afterSort: function(li, response){ },
	        beforeEdit: function(li){ },
	        afterEdit: function(li, response){ },
	        beforeDelete: function(li){ if(confirm('Are you realy want to delete this item?')) return 'delete.php?id='+this.getRecordId(); },
	        afterDelete: function(li, response){ self.deleteValueFieldAction(li) }
	    });
	},


	/**
	 * Here we fill "Main" step field values like name, handle, input type and value type
	 *
	 * @access private
	 *
	 */
	loadSpecFieldAction: function()
	{
        var self = this;

	    // Default language
		if(this.id) this.nodes.id.value = this.id;
		if(this.handle) this.nodes.handle.value = this.handle;

		if(this.name[this.languageCodes[0]]) this.nodes.title.value = this.name[this.languageCodes[0]];
		this.nodes.title.name = "name[" + this.languageCodes[0] + "]";

		this.nodes.multipleSelector.checked = this.multipleSelector ? true : false;

		if(this.nodes.mainTitle)
		{
    		this.nodes.mainTitle.firstChild.nodeValue = this.nodes.title.value;
		}

		if(this.description[this.languageCodes[0]]) this.nodes.description.value = this.description[this.languageCodes[0]];
		this.nodes.description.name = "description[" + this.languageCodes[0] + "]";

		// select dataType (or first)
		if(this.dataType)
		{
			for(var i = 0; i < this.nodes.dataType.length; i++)
			{
				if(this.nodes.dataType[i].value == this.dataType)
				{
					this.nodes.dataType[i].checked = true;
					break;
				}
			}
		}
		else if(this.nodes.dataType.length > 0)
		{
			this.nodes.dataType[0].checked = true;
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

				var inputFields = $A(newTranslation.getElementsByTagName('input'));
				var textAreas = newTranslation.getElementsByTagName('textarea');
				for(var j = 0; j < textAreas.length; j++)
				{
				    inputFields[inputFields.length] = textAreas[j];
				}

				for(var j = 0; j < inputFields.length; j++)
				{
                    if(Element.hasClassName(inputFields[j].parentNode, 'step_translations_language'))
                    {
    				    eval("if(self."+inputFields[j].name+"['"+self.languageCodes[i]+"']) [j].value = self."+inputFields[j].name+"['"+self.languageCodes[i]+"'];");
    					inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";
                    }
				}

				this.nodes.stepTranslations.appendChild(newTranslation);

				// add to nodes list

				this.nodes.translations[this.languageCodes[i]] = newTranslation;
			}
		}

		// Delete language template, so that included in that template variables would not be sent to server
		Element.remove(document.getElementsByClassName("step_translations_language", this.nodes.stepTranslations)[0]);
	},


	/**
	 * When we create form from JSON string we should create and fill in values fields (from "Values" step)
	 * and their translations in "Translations" step if needed
	 *
	 * @access private
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
	 * This method separates language codes from language titles
	 *
	 * @example (lt: Lithuanian, ru: Russian) will create [lt, ru] array
	 *
	 * @access private
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
	 * In SpecField form template we not yet know what languages we'll be using so
	 * what we are doing here is looking at what languages we are using and creating separate
	 * sections for each language in "Translations" section
	 *
	 * @access private
	 *
	 */
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

	/**
	 * Programm should change language section if we have click on a link meaning different language. If we click current
	 * language it will callapse (not the programme of course =)
	 *
	 * @var Event e Event
	 *
	 * @access private
	 *
	 */
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
	 * When we add new value "Values" step we are also adding it to "Translations" step. Field name
	 * will have new_3 (or any other number) in its name. We are not realy creating a field here. Instead
	 * we are calling for addField method to do the job. The only usefull thing we are doing here is
	 * generating an id for new field
	 *
	 * @var Event e Event
	 *
	 * @access private
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
	 * This one is easy. When we click on delete value from "Values" step we delete the value and it's
	 * translation in "Translations" step
	 *
	 * @var Event e Event
	 *
	 * @access private
	 *
	 */
	deleteValueFieldAction: function(li)
	{
		var splitedHref = li.id.split("_");
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
	},


	/**
	 * This callback is executed when user change the value type
	 *
	 * @var Event e Event
	 *
	 * @access private
	 *
	 */
	dataTypeChangedAction: function(e)
	{
		this.nodes.type.length = 0;
		for(var i = 0; i < this.nodes.dataType.length; i++)
		{
			if(this.nodes.dataType[i].checked)
			{
				for(var j = 0; j < this.types[this.nodes.dataType[i].value].length; j++)
				{
					this.nodes.type.options[j] = this.types[this.nodes.dataType[i].value][j].cloneNode(true);
				}

				this.dataType = this.nodes.dataType[i].value;
			}
		}


		this.typeWasChangedAction();
	},


	/**
	 * This callback is executed when user changes the state. When user change the state all other
	 * states are hidden and only current state shown or if the user was so stupid to click on current
	 * state whe whole thing will crash (or the current step will collapse. I don't realy remember)
	 *
	 * @var Event e Event
	 *
	 * @access private
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
			this.nodes.stateLinks[i].id = 'change_state' + this.id;

			if(this.nodes.stepLevOne[i].className.split(' ').indexOf(currentStep) === -1 || this.nodes.stepLevOne[i].style.display == 'block')
			{
			    this.nodes.stepLevOne[i].style.display = 'none';
			}
			else
			{
			    this.nodes.stepLevOne[i].style.display = 'block';
			}
		}
	},


	/**
	 * When some dumbass creates/modifies value in "Values" step, we are automatically creating
	 * a label for similar field in every language section in "Translations" step.
	 *
	 * @example If we tipe one in "Values" step like so
	 * ___________
	 * |One       |
	 * ------------
	 *
	 * the programm will change label of similar fields in every translation language like so
	 *
	 * Lithuanian:
	 *        ___________
	 * One:   |Vienas    |
	 *        ------------
	 *
	 * German:
	 *        ___________
	 * One:   |Einz      |   * I don't realy know how to write one in germat and also tooday i am to lazy to google for it :(
	 *        ------------
	 *
	 * @var Event e Event
	 *
	 * @access private
	 */
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

	mainValueFieldChangedAction: function(e)
	{
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

		keyboard = new KeyboardEvent(e);

		if(!(
		    // you can use +/- as the first character
    		(keyboard.getCursorPosition() == 0 && !e.target.value.match('[\-\+]') && (keyboard.getKey() == 109 || keyboard.getKey() == 107 || (keyboard.isShift() && keyboard.getKey() == 61))) ||
    		// You even can use dots or commas, but only once and not as the first symbol
    		(e.target.value != '' && !e.target.value.match('[\.\,]') && [110, 188, 190].indexOf(keyboard.getKey()) >= 0) ||
    		// at last but not the least i have implemanted such a great feature, that you can use digits to create numbers. [applause]
    		([48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105].indexOf(keyboard.getKey()) > 0) ||
    		// special chars
    		([46, 8, 17, 16, 37, 38, 39, 40].indexOf(keyboard.getKey()) >= 0)
		))
		{
		    Event.stop(e);
		}
	},

	/**
	 * When we are filling spec field name in "Main" step we are changing it's handle and a title
	 * on the top of the form. Handle is actuali a stripped version of spec field name with all spec
	 * symbols changed to "_" (underscope)
	 *
	 * @var Event e Event
	 *
	 * @access private
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

		if(this.nodes.mainTitle) this.nodes.mainTitle.firstChild.nodeValue = this.nodes.title.value;
	},


	/**
	 * Here we are adding new field to values list in "Values" step and "Translations" step.
	 *
	 * @var hash value Value of newly created field. The value is a hash array with value for every language {'en': "One", 'lt': "Vienas", 'de': "Einz"}
	 * @var int id Id of a newly created field
	 *
	 * @access private
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