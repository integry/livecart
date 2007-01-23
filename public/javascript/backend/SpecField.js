/**
 * Backend.SpecField
 *
 * Script for managing spec field form
 *
 * The following class manages spec field forms. I have used an separate js file (a class)
 * because there are a lot of thing happening when you are dealing with spec fields forms.
 *
 * To use this class you should simply pass specFIelds values to it like so
 * @example
 * <code>
 *     new Backend.SpecField({
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
 *     Backend.SpecField.prototype.languages = {"en":"English","lt":"Lithuanian","de":"German"};
 *     Backend.SpecField.prototype.types = createTypesOptions({"2":{"1":"Selector","2":"Numbers"},"1":{"3":"Text","4":"Word processer","5":"selector","6":"Date"}});
 *     Backend.SpecField.prototype.messages = {"deleteField":"delete field"};
 *     Backend.SpecField.prototype.selectorValueTypes = [1,5];
 *     Backend.SpecField.prototype.doNotTranslateTheseValueTypes = [2];
 *     Backend.SpecField.prototype.countNewValues = 0;
 * </code>
 *
 * @version 1.0
 * @author Sergej Andrejev
 */
if (Backend == undefined)
{
	var Backend = {}
}

Backend.SpecField = Class.create();
Backend.SpecField.prototype = {
    DATATYPE_TEXT: 1,
    DATATYPE_NUMBERS: 2,
    
    TYPE_NUMBERS_SELECTOR: 1,
    TYPE_NUMBERS_SIMPLE: 2,
        
    TYPE_TEXT_SIMPLE: 3,
    TYPE_TEXT_ADVANCED: 4,
    TYPE_TEXT_SELECTOR: 5,
    TYPE_TEXT_DATE: 6,

	cssPrefix: "specField_",

    /**
	 * Constructor
	 *
	 * @param specFieldsJson Spec Field values
	 * @param hash If true the passed specField is an object. If hash is not passed or false then specFieldJson will be parsed as json string
	 *
	 * @access public
	 *
	 */
	initialize: function(specFieldJson, hash)
	{

    	    this.specField = !hash ? eval("(" + specFieldJson + ")" ) : specFieldJson;
    	    this.cloneForm('specField_item_blank', this.specField.rootId);
    
    	    this.id = this.specField.ID;
    	    this.categoryID = this.specField.categoryID;
    	    this.rootId = this.specField.rootId;
    
    		this.type = this.specField.type;
    		this.values = this.specField.values;
    
    		this.name = this.specField.name;
    		this.backupName = this.name;
    
    		this.description = this.specField.description;
    
    		this.handle = this.specField.handle;
    		this.multipleSelector = this.specField.multipleSelector;
    		this.dataType = this.specField.dataType;
    
    		this.loadLanguagesAction();
    		this.findUsedNodes();

		    this.bindFields();
	},

    /**
	 * This function destroys the old spec field form, then clones the prototype and then calls constructor once again
	 *
	 * @param specFields Spec Field values
	 * @param hash If true the passed specField is an object. If hash is not passed or false then specFieldJson will be parsed as json string
	 *
	 * @access public
	 *
	 */
	recreate: function(specFieldJson, hash)
	{
	    var root = ($(this.specField.rootId).tagName.toLowerCase() == 'li') ?  window.activeSpecFieldsList[this.categoryID].getContainer($(this.specField.rootId), 'edit') : $(this.specField.rootId);
        root.innerHTML = '';
        $H(this).each(function(el)
        {
            el = false;
        });
	    this.initialize(specFieldJson, hash);
	    this.clearAllFeedBack();
	},


	/**
	 * Instead of sending spec field form we store form prototype which is cloned every time new spec field data is being recieved.
	 *
	 * @param prototypeId Id of root prototype element
	 * @param rootId Id of root element where the copy of prototype will be copied
	 *
	 * @access private
	 */
	cloneForm: function(prototypeId, rootId)
	{
	    var blankForm = $(prototypeId);
                
        var blankFormValues = blankForm.getElementsByTagName("*");
        var root = ($(this.specField.rootId).tagName.toLowerCase() == 'li') ?  window.activeSpecFieldsList[this.specField.categoryID].getContainer($(this.specField.rootId), 'edit') : $(this.specField.rootId);

        for(var i = 0; i < blankFormValues.length; i++)
        {
            if(blankFormValues[i] && blankFormValues[i].parentNode == blankForm)
            {
                root.appendChild(blankFormValues[i].cloneNode(true));
            }
        }
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

		this.nodes.form 			    = this.nodes.parent.getElementsByTagName("form")[0];

		this.nodes.dataType 			= document.getElementsByClassName(this.cssPrefix + "form_dataType", this.nodes.parent)[0].getElementsByTagName("input");
		this.nodes.type 				= document.getElementsByClassName(this.cssPrefix + "form_type", this.nodes.parent)[0];
		this.nodes.stateLinks 			= document.getElementsByClassName(this.cssPrefix + "change_state", this.nodes.parent);
		this.nodes.stepTranslations 	= document.getElementsByClassName(this.cssPrefix + "step_translations", this.nodes.parent)[0];
		this.nodes.stepMain 			= document.getElementsByClassName(this.cssPrefix + "step_main", this.nodes.parent)[0];
		this.nodes.stepValues       	= document.getElementsByClassName(this.cssPrefix + "step_values", this.nodes.parent)[0];

		this.nodes.stepLevOne 			= document.getElementsByClassName(this.cssPrefix + "step_lev1", this.nodes.parent);

		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
		    if(!this.nodes.stepLevOne[i].id) this.nodes.stepLevOne[i].id = this.nodes.stepLevOne[i].className.replace(/ /, "_") + "_" + this.id;
		}

		this.nodes.mainTitle 			= document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
		this.nodes.id 					= document.getElementsByClassName(this.cssPrefix + "form_id", this.nodes.parent)[0];
		this.nodes.categoryID 			= document.getElementsByClassName(this.cssPrefix + "form_categoryID", this.nodes.parent)[0];
		this.nodes.description 			= document.getElementsByClassName(this.cssPrefix + "form_description", this.nodes.parent)[0];
		this.nodes.multipleSelector 	= document.getElementsByClassName(this.cssPrefix + "form_multipleSelector", this.nodes.parent)[0];
		this.nodes.handle 				= document.getElementsByClassName(this.cssPrefix + "form_handle", this.nodes.parent)[0];
		this.nodes.name 				= document.getElementsByClassName(this.cssPrefix + "form_name", this.nodes.parent)[0];
		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName(this.cssPrefix + "form_values_group", this.nodes.parent)[0];

		this.nodes.cancel 	            = document.getElementsByClassName(this.cssPrefix + "cancel", this.nodes.parent)[0];
		this.nodes.save 	            = document.getElementsByClassName(this.cssPrefix + "save", this.nodes.parent)[0];

		this.nodes.translationsLinks 	= document.getElementsByClassName(this.cssPrefix + "form_values_translations_language_links", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.valuesDefaultGroup.getElementsByClassName(this.cssPrefix + "add_field", this.nodes.parent)[0];

        this.nodes.valuesTranslations = {};

		var ul = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];
		ul.id = this.cssPrefix + "form_"+this.id+'_values_'+this.languageCodes[0];

	},

	/**
	 * Find all translations fields. This is done every time when new field is being added
	 *
	 * @access private
	 *
	 */
	bindTranslationValues: function()
	{
		this.nodes.translatedValues = document.getElementsByClassName(this.cssPrefix + "form_values_translations", this.nodes.parent);
	},



	/**
	 * Binds fields to some events
	 *
	 * @access private
	 *
	 */
	bindFields: function()
	{
		var self = this;

	    for(var i = 0; i < this.nodes.dataType.length; i++)
		{
			this.nodes.dataType[i].onclick = this.dataTypeChangedAction.bind(this);
		}

		for(var i = 0; i < this.nodes.stateLinks.length; i++)
		{
			this.nodes.stateLinks[i].onclick = this.changeStateAction.bind(this);
		}

		this.nodes.name.onkeyup = this.generateHandleAndTitleAction.bind(this);
		this.nodes.valuesAddFieldLink.onclick = this.addValueFieldAction.bind(this);
		this.nodes.type.onchange = this.typeWasChangedAction.bind(this);

		this.nodes.cancel.onclick = this.cancelAction.bind(this);
		this.nodes.save.onclick = this.saveAction.bind(this);

		// Also some actions must be executed on load. Be aware of the order in which those actions are called
		this.loadSpecFieldAction();

		this.loadValueFieldsAction();

		this.bindTranslationValues();
		this.dataTypeChangedAction();
		this.loadTypes();
		this.typeWasChangedAction();


		new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
		Form.backup(this.nodes.form);
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
        var valuesTranslations = document.getElementsByClassName(this.cssPrefix + "step_values_translations", this.nodes.stepValues)[0];
		if(this.selectorValueTypes.indexOf(this.nodes.type.value) === -1)
		{
			this.nodes.stateLinks[1].parentNode.style.display = 'none';
			this.nodes.stateLinks[1].style.display = 'none';
		}
		else
		{
			this.nodes.stateLinks[1].parentNode.style.display = 'inline';
			this.nodes.stateLinks[1].style.display = 'inline';
            
            
            valuesTranslations.style.display = (this.dataType == this.DATATYPE_NUMBERS) ? 'none' : 'block';
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
		$A(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0].getElementsByTagName("input")).each(function(input)
		{
		    if(input.type == 'text')
            {
                input.onkeyup = self.mainValueFieldChangedAction.bind(self);
    			input.onkeydown = self.mainValueFilterKeysAction.bind(self);
            }
		});



	    this.fieldsList = new ActiveList(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0], {
	        beforeSort: function(li, order)
	        {
	            return self.links.sortValues + '?target=' + this.ul.id + '&' + order;
	        },
	        afterSort: function(li, response){    },

	        beforeDelete: function(li){
	                if(this.getRecordId(li).match(/^new/))
	                {
    	                var emptyFilters = true;
                        var inputValues = li.getElementsByTagName("input");
                        for(var i = 0; i < inputValues.length; i++) 
                        {
                            if(!Element.hasClassName('dom_template', inputValues[i]) && inputValues[i].style.display != 'none' && inputValues[i].value != '')
                            {
                                emptyFilters =  false;
                            }
                        }
                        
                        if(emptyFilters || confirm('Are you realy want to delete this item?'))
                        {
                            self.deleteValueFieldAction(li, this);
                        }
	                }
	                else if(confirm('Are you realy want to delete this item?'))
	                {
	                    return Backend.SpecField.prototype.links.deleteValue + this.getRecordId(li);
	                }
	        },
	        afterDelete: function(li, response){ self.deleteValueFieldAction(li, this) }
	    }, this.activeListMessages);
	},


	changeMainTitleAction: function(newTitle)
	{
		if(this.nodes.mainTitle)
		{
		    if(this.nodes.mainTitle.firstChild)
		    {
		        this.nodes.mainTitle.firstChild.nodeValue = newTitle;
		    }
		    else
		    {
		        this.nodes.mainTitle.appendChild(document.createTextNode(newTitle));
		    }
		}
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
		if(this.categoryID) this.nodes.categoryID.value = this.categoryID;
		if(this.handle) this.nodes.handle.value = this.handle;

		if(this.name[this.languageCodes[0]]) this.nodes.name.value = this.name[this.languageCodes[0]];
		this.nodes.name.name = "name[" + this.languageCodes[0] + "]";

		this.nodes.multipleSelector.checked = this.multipleSelector ? true : false;

        this.changeMainTitleAction(this.nodes.name.value);

		if(this.description && this.description[this.languageCodes[0]]) this.nodes.description.value = this.description[this.languageCodes[0]];
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
		var translations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations);
		var valuesTranslations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepValues);
        // we should have a template to continue
		if(translations.length > 0 && Element.hasClassName(translations[0], 'dom_template'))
		{
			this.nodes.translations = new Array();
			for(var i = 1; i < this.languageCodes.length; i++)
			{
                // Name, description, etc translations                
				// copy template class
				var newTranslation = translations[0].cloneNode(true);
				Element.removeClassName(newTranslation, "dom_template");
    
    			// bind it
    			newTranslation.getElementsByTagName("legend")[0].onclick = this.changeTranslationLanguageAction.bind(this);
    
				newTranslation.className += this.languageCodes[i];
                
                document.getElementsByClassName(this.cssPrefix + "legend_text", newTranslation.getElementsByTagName("legend")[0])[0].appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));

				var inputFields = $A(newTranslation.getElementsByTagName('input'));
				var textAreas = newTranslation.getElementsByTagName('textarea');
				for(var j = 0; j < textAreas.length; j++)
				{
				    inputFields[inputFields.length] = textAreas[j];
				}

				for(var j = 0; j < inputFields.length; j++)
				{
                    if(Element.hasClassName(inputFields[j].parentNode.parentNode, this.cssPrefix + 'language_translation'))
                    {
    				    eval("if(self." + inputFields[j].name + " && self."+inputFields[j].name+"['"+self.languageCodes[i]+"']) inputFields[j].value = self."+inputFields[j].name+"['"+self.languageCodes[i]+"'];");
    					inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";
                    }
				}

				this.nodes.stepTranslations.appendChild(newTranslation);

				// add to nodes list
				this.nodes.translations[this.languageCodes[i]] = newTranslation;
                                
                // Create place for values translations
				var newValueTranslation = valuesTranslations[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");
				newValueTranslation.className += this.languageCodes[i];
                
                var valueTranslationLegend = newValueTranslation.getElementsByTagName("legend")[0];
                document.getElementsByClassName(this.cssPrefix + "legend_text", valueTranslationLegend)[0].appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));

                 
                
                valueTranslationLegend.onclick = this.toggleValueLanguage.bind(this);
                
				valuesTranslations[0].parentNode.appendChild(newValueTranslation);
                this.nodes.valuesTranslations[this.languageCodes[i]] = newValueTranslation;
			}
		}

		// Delete language template, so that included in that template variables would not be sent to server
		Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
	},
    
    toggleValueLanguage: function(e)
    {
        if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}
        
        var values = document.getElementsByClassName(this.cssPrefix + "language_translation", e.target.parentNode.parentNode)[0];
        values.style.display = (values.style.display == 'block') ? 'none' : 'block';
               
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (values.style.display == 'block') ? '[-] ' : '[+] ';
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
				self.addField(value.value, value.key);

			});

            this.bindDefaultFields();
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
		this.languageCodes = [];

		$H(this.languages).each(function(language) {
			self.languageCodes[self.languageCodes.length] = language.key;
		});
	},



	/**
	 * Programm should change language section if we have click on a link meaning different language. If we click current
	 * language it will callapse
	 *
	 * @param Event e Event
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
        var currentTranslationNode = document.getElementsByClassName(this.cssPrefix + "language_translation", e.target.parentNode.parentNode)[0];               
        currentTranslationNode.style.display = (currentTranslationNode.style.display == 'block') ? 'none' : 'block';
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (currentTranslationNode.style.display == 'block') ? '[-] ' : '[+] ';    
    },

	/**
	 * When we add new value "Values" step we are also adding it to "Translations" step. Field name
	 * will have new3 (or any other number) in its name. We are not realy creating a field here. Instead
	 * we are calling for addField method to do the job. The only usefull thing we are doing here is
	 * generating an id for new field
	 *
	 * @param Event e Event
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

		this.addField(null, "new" + this.countNewValues, true);
        this.bindDefaultFields();
		this.countNewValues++;
	},


	/**
	 * This one is easy. When we click on delete value from "Values" step we delete the value and it's
	 * translation in "Translations" step
	 *
	 * @param Event e Event
	 *
	 * @access private
	 *
	 */
	deleteValueFieldAction: function(li, activeList)
	{
		var splitedHref = li.id.split("_");
		var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
		var id = (isNew ? 'new' : '') + splitedHref[splitedHref.length - 1];

        activeList.remove(li);

		for(var i = 1; i < this.languageCodes.length; i++)
		{
			var translatedValue = document.getElementById(this.cssPrefix + "form_values_" + this.languageCodes[i] + "_" + id);

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
	 * @param Event e Event
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
					this.nodes.type.options[j] = new Option(this.types[this.nodes.dataType[i].value][j][1], this.types[this.nodes.dataType[i].value][j][0]);
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
	 * @param Event e Event
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

		var currentStep = this.cssPrefix + e.target.hash.substring(1);
		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
		    this.nodes.stateLinks[i].id = this.cssPrefix + 'change_state' + this.id;

			if(!Element.hasClassName(this.nodes.stepLevOne[i], currentStep))
			{
			    this.nodes.stepLevOne[i].style.display = 'none';
			    Element.removeClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
			    Element.removeClassName(this.nodes.stateLinks[i].parentNode, 'active');
			}
			else
			{
			    this.nodes.stepLevOne[i].style.display = 'block';
			    Element.addClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
			    Element.addClassName(this.nodes.stateLinks[i].parentNode, 'active');
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
	 * @param Event e Event
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
		var id = (isNew ? 'new' : '') + splitedHref[splitedHref.length - 1];

		for(var i = 1; i < this.languageCodes.length; i++)
		{
            $(this.cssPrefix + "form_values_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].innerHTML = e.target.value;
		}
	},

	/**
	 * Making sure that user won't enter invalid number
	 *
	 * @param Event e Event
	 *
	 * @access private
	 */
	mainValueFilterKeysAction: function(e)
	{
		if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

		keyboard = new KeyboardEvent(e);

		if(
            this.dataType == this.DATATYPE_NUMBERS && // if it is a number
    		!(
    		    // you can use +/- as the first character
        		(keyboard.getCursorPosition() == 0 && !e.target.value.match('[\-\+]') && (keyboard.getKey() == 109 || keyboard.getKey() == 107 || (keyboard.isShift() && keyboard.getKey() == 61))) ||
        		// You even can use dots or commas, but only once and not as the first symbol
        		(e.target.value != '' && !e.target.value.match('[\.\,]') && [110, 188, 190].indexOf(keyboard.getKey()) >= 0) ||
        		// at last but not the least i have implemanted such a great feature, that you can use digits to create numbers. [applause]
        		([48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105].indexOf(keyboard.getKey()) > 0) ||
        		// special chars
        		([46, 8, 17, 16, 37, 38, 39, 40].indexOf(keyboard.getKey()) >= 0)
    		)
		){
		    Event.stop(e);
		}
	},

	/**
	 * When we are filling spec field name in "Main" step we are changing it's handle and a title
	 * on the top of the form. Handle is actuali a stripped version of spec field name with all spec
	 * symbols changed to "." (dots)
	 *
	 * @param Event e Event
	 *
	 * @access private
	 *
	 */
	generateHandleAndTitleAction: function(e)
	{
		// generate handle
		var handle = ActiveForm.prototype.generateHandle(this.nodes.name.value);

		if(this.id == 'new')
		{
		    this.nodes.handle.value = handle;
		}


		if(this.nodes.mainTitle)
		{
		    if(this.nodes.mainTitle.firstChild)
		    {
		        this.nodes.mainTitle.firstChild.nodeValue = this.nodes.name.value;
		    }
		    else
		    {
		        this.nodes.mainTitle.appendChild(document.createTextNode(this.nodes.name.value));
		    }
		}
	},


	/**
	 * Here we are adding new field to values list in "Values" step and "Translations" step.
	 *
	 * @param hash value Value of newly created field. The value is a hash array with value for every language {'en': "One", 'lt': "Vienas", 'de': "Einz"}
	 * @param int id Id of a newly created field
	 *
	 * @access private
	 *
	 */
	addField: function(value, id, isDefault)
	{
	    var values = document.getElementsByClassName(this.cssPrefix + "form_values_value", this.nodes.valuesDefaultGroup);

		var ul = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];

		if(values.length > 0 && values[0].className.split(' ').indexOf('dom_template') !== -1)
		{
			var newValue = values[0].cloneNode(true);
			Element.removeClassName(newValue, "dom_template");

            if(!this.fieldsList) this.bindDefaultFields();

            var li = this.fieldsList.addRecord(id, newValue.getElementsByTagName("*"));
            CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);

			// The field itself
			var input = li.getElementsByTagName("input")[0];
			input.name = "values[" + id + "]["+this.languageCodes[0]+"]";
			input.value = (value && value[this.languageCodes[0]]) ? value[this.languageCodes[0]] : '' ;

			// now insert all translation fields
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				var newValueTranslation = document.getElementsByClassName(this.cssPrefix + "form_values_value", this.nodes.valuesTranslations[this.languageCodes[i]])[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");

				newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;

				var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
				inputTranslation.name = "values[" + id + "][" + this.languageCodes[i] + "]";
				inputTranslation.value = (value && value[this.languageCodes[i]]) ? value[this.languageCodes[i]] : '' ;
                
                var label = newValueTranslation.getElementsByTagName("label")[0].innerHTML = input.value;
                
				// add to node tree
				var translationsUl = document.getElementsByClassName(this.cssPrefix + "form_values_translations", this.nodes.valuesTranslations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
				translationsUl.id = this.cssPrefix + "form_"+this.id+'_values_'+this.languageCodes[i];
				translationsUl.appendChild(newValueTranslation);
			}
		}
		else
		{
			return false;
		}
	},


    /**
     * This method is called when user click on cancel link. It resets all fields to its defaults and closes form
     *
	 * @param Event e Event
	 *
	 * @access public
	 *
     */
    cancelAction: function(e)
    {
        if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

		Event.stop(e);

		// first cancel all modifications if they took place
		if(this.id == 'new')
		{
		    this.recreate(this.specField, true);
		}
		else if(Form.hasBackup(this.nodes.form) && this.formChanged)
		{
            Form.restore(this.nodes.form);

            this.typeWasChangedAction();
            this.changeMainTitleAction(this.nodes.name.value);
		}

		// Use Active list toggleContainer() method if this specField is inside Active list
		// Note that if it is inside a list we are showing and hidding form with the same action,
		// butt =] when dealing with new form showing form action is handled by Backend.SpecField::createNewAction()
        if(this.nodes.parent.tagName.toLowerCase() == 'li')
        {
            window.activeSpecFieldsList[this.categoryID].toggleContainer(this.nodes.parent, 'edit');
        }
        else
        {
            this.hideNewSpecFieldAction(this.categoryID);
        }
    },


    /**
     * Clears all feedback messages in current spec field
     *
     */
	clearAllFeedBack: function()
	{
	    var feedback = document.getElementsByClassName('feedback', this.nodes.parent);

	    $A(feedback).each(function(field)
	    {
            field.style.visibility = 'hidden';
	    });
	},


    /**
     * This method is called when user clicks on save button. It saves form values, and does i don't know what (i guess it should close the form)
     *
	 * @param Event e Event
	 *
	 * @access public
	 *
     */
    saveAction: function(e)
    {
        var self = this;
        if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

		Event.stop(e);

		// Toggle progress won't work on new form
		try
		{
		    window.activeSpecFieldsList[this.categoryID].toggleProgress(self.nodes.parent);
		}
		catch (e)
		{
		    // New item has no pr06r3s5 indicator
		}
        
		this.clearAllFeedBack();
        new Ajax.Request(
            this.nodes.form.action,
            {
                method: this.nodes.form.method,
                postBody: Form.serialize(this.nodes.form),
                onComplete: function(param) {
                    self.afterSaveAction(param.responseText)
                }
            }
        );

    },


    /**
     * This action is executed after server response with possible errors in entered
     * spec field fields
     *
     */
    afterSaveAction: function(jsonResponseString)
    {
        var self = this;

        try
        {
            var jsonResponse = eval("("+jsonResponseString+")");
        }
        catch(e)
        {
            alert("json error");
        }
		// Toggle progress won't work on new form

        if(jsonResponse.status == 'success')
        {
            Form.backup(this.nodes.form);
            this.backupName = this.nodes.name.value;

            if(this.nodes.parent.tagName.toLowerCase() == 'li')
            {
                window.activeSpecFieldsList[this.categoryID].toggleContainer(this.nodes.parent, 'edit');
            }
            else
            {

                var div = document.createElement('span');
                Element.addClassName(div, 'specField_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                window.activeSpecFieldsList[this.categoryID].addRecord(jsonResponse.id, [document.createTextNode(' '), div]);
                this.hideNewSpecFieldAction(this.categoryID);
    		    this.recreate(this.specField, true);
            }
            
            // Reload filters (uncomment when API is frozen)
            
            try { // try to remove filter container
                var tc = Backend.Category.tabControl;    
                Element.remove($(tc.getContainerId('tabFilters', tc.treeBrowser.getSelectedItemId())));
            } catch (e){ }
        }
        else
        {
            if(jsonResponse.errors)
            {
                for(var fieldName in jsonResponse.errors)
                {
                    if(fieldName == 'toJSONString') continue;
                    if(fieldName == 'values')
                    {
                        $H(jsonResponse.errors[fieldName]).each(function(value)
                        {
                            ActiveForm.prototype.setFeedback($(self.cssPrefix + "form_" + self.id + "_values_" + self.languageCodes[0] + "_" + value.key).getElementsByTagName("input")[0], value.value);
                        });
                    }
                    else
                    {
                       ActiveForm.prototype.setFeedback(this.nodes[fieldName], jsonResponse.errors[fieldName]);
                    }
                }
            }
        }

		try
		{
		    window.activeSpecFieldsList[this.categoryID].toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
		    // new item has no progress indicator
		}
    },


    /**
     * All Your Base Are Belong To Us! A mystery function.
     * Hides new spec field form
     *
     * @static
     */
    hideNewSpecFieldAction: function(categoryID)
    {
        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");

        ActiveForm.prototype.hideNewItemForm(link, form);
    },


    /**
     * When the form is created it gets all it's parameters from JSON. However when getting options
     * list we should create an array of Option objects from JSON.
     *
     * @example
     * var json = {
     *              pc:  'Personal Computer',
     *              mac: 'PowerPC',
     *              sun: 'Sun Server'
     *           }
     *
     * is converted to
     *
     * var options = (
     *                new Option('Personal Computer', pc),
     *                new Option('e', mac),
     *                new Option('Sun Server', sun)
     *             )
     *
	 * @static
     */
    createTypesOptions: function(types)
    {
       var typesOptions = {};
       $H(types).each(function(value) {
           var options = [];

           $H(value.value).each(function(option) {
               options[options.length] = [option.key, option.value];
           });

           typesOptions[value.key] = options;
    	});

    	return typesOptions;
    },

    /**
     * This method unfolds "Create new Spec Field entry" form. Items from existing spec fields list are unfolded using
     * ActiveList methods
     *
     * @see ActiveList
     *
     * @param HTMLElement parent form node (it should have "create new entry" and an empty spec field form inside it)
     *
     * @static
     *
     */
    createNewAction: function(e, categoryID)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");
        
        window.activeSpecFieldsList[categoryID].collapseAll();
        
        ActiveForm.prototype.showNewItemForm(link, form);
    }
}