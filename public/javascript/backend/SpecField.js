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
 * @author Sergej Andrejev
 * @namespace Backend.SpecField
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

    callbacks: {
        beforeEdit:     function(li) {
            Backend.SpecField.prototype.hideNewSpecFieldAction(this.getRecordId(li, 3));
            
            if(this.isContainerEmpty(li, 'edit')) return Backend.SpecField.prototype.links.editField + this.getRecordId(li)
            else this.toggleContainer(li, 'edit');
        },
        afterEdit:      function(li, response) {
            var specField = eval("(" + response + ")" );
            specField.rootId = li.id;
            new Backend.SpecField(specField, true);
            this.createSortable();
            this.toggleContainer(li, 'edit');
        },
        beforeDelete:   function(li) {
            if(confirm(Backend.SpecField.prototype.msg.removeFieldQuestion))
            return Backend.SpecField.prototype.links.deleteField + this.getRecordId(li)
        },
        afterDelete:    function(li, jsonResponse)
        {
            var response = eval("("+jsonResponse+")");
            if(response.status == 'success') {
                this.remove(li);
                CategoryTabControl.prototype.resetTabItemsCount(this.getRecordId(li, 3));
            }
        },
        beforeSort:     function(li, order) {
            return Backend.SpecField.prototype.links.sortField + "?target=" + this.ul.id + "&" + order
        },
        afterSort:     function(li, order) {    }
    },
    
    isNumber: function(type) 
    {
          return type == Backend.SpecField.prototype.TYPE_NUMBERS_SELECTOR || type == Backend.SpecField.prototype.TYPE_NUMBERS_SIMPLE;
    },

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
        try 
        {
    	    this.specField = !hash ? eval("(" + specFieldJson + ")" ) : specFieldJson;
    	    this.cloneForm('specField_item_blank');
    
    	    this.id                    = this.specField.ID;
    	    this.categoryID            = this.specField.categoryID;
    	    this.rootId                = this.specField.rootId;
    
    		this.type                  = this.specField.type;
    		this.values                = this.specField.values;
    		this.name                  = this.specField.name_lang;
    		this.backupName            = this.name;
            
    		this.valuePrefix           = this.specField.valuePrefix ? this.specField.valuePrefix : '';
    		this.valueSuffix           = this.specField.valueSuffix ? this.specField.valueSuffix : '';
    
    		this.description           = this.specField.description;
    
    		this.handle                = this.specField.handle;
    		this.isMultiValue          = this.specField.isMultiValue == 1 ? true : false;
    		this.isRequired            = this.specField.isRequired == 1 ? true : false;
    		this.isDisplayed           = this.specField.isDisplayed == 1 ? true : false;
    		this.isDisplayedInList     = this.specField.isDisplayedInList == 1 ? true : false;
            
    		this.loadLanguagesAction();
    		this.findUsedNodes();
    
    	    this.bindFields();
        } catch(e) {
            console.info(e);
        }
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
	    var root = ($(this.specField.rootId).tagName.toLowerCase() == 'li') ? ActiveList.prototype.getInstance("specField_items_list_" + this.categoryID).getContainer($(this.specField.rootId), 'edit') : $(this.specField.rootId);
        root.innerHTML = '';
        $H(this).each(function(el) { el = false; });
	    this.initialize(specFieldJson, hash);
	},


	/**
	 * Instead of sending spec field form we store form prototype which is cloned every time new spec field data is being recieved.
	 *
	 * @param prototypeId Id of root prototype element
	 * @param rootId Id of root element where the copy of prototype will be copied
	 *
	 * @access private
	 */
	cloneForm: function(prototypeId)
	{
	    var blankForm = $(prototypeId);
                
        var blankFormValues = blankForm.getElementsByTagName("*");
        var root = ($(this.specField.rootId).tagName.toLowerCase() == 'li') ?  ActiveList.prototype.getInstance(this.specField.rootId).getContainer($(this.specField.rootId), 'edit') : $(this.specField.rootId);

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

        var self = this;
        this.nodes.labels = {};  
        $A(['description', 'handle', 'type', 'name', 'valuePrefix', 'valueSuffix', 'advancedText', 'multipleSelector', 'isRequired', 'isDisplayed', 'isDisplayedInList']).each(function(field)
        {
            self.nodes.labels[field] = document.getElementsByClassName(self.cssPrefix + "form_" + field + "_label", self.nodes.parent)[0];
        });   

		this.nodes.mainTitle 			= document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
		this.nodes.id 					= document.getElementsByClassName(this.cssPrefix + "form_id", this.nodes.parent)[0];
		this.nodes.categoryID 			= document.getElementsByClassName(this.cssPrefix + "form_categoryID", this.nodes.parent)[0]; 
		this.nodes.description 			= document.getElementsByClassName(this.cssPrefix + "form_description", this.nodes.parent)[0];
		
        this.nodes.multipleSelector 	= document.getElementsByClassName(this.cssPrefix + "form_multipleSelector", this.nodes.parent)[0];
		this.nodes.isRequired          	= document.getElementsByClassName(this.cssPrefix + "form_isRequired", this.nodes.parent)[0];
		this.nodes.isDisplayed          = document.getElementsByClassName(this.cssPrefix + "form_isDisplayed", this.nodes.parent)[0];
		this.nodes.isDisplayedInList    = document.getElementsByClassName(this.cssPrefix + "form_isDisplayedInList", this.nodes.parent)[0];
		
        this.nodes.handle 				= document.getElementsByClassName(this.cssPrefix + "form_handle", this.nodes.parent)[0];
		this.nodes.name 				= document.getElementsByClassName(this.cssPrefix + "form_name", this.nodes.parent)[0];
		this.nodes.valueSuffix			= document.getElementsByClassName(this.cssPrefix + "form_valueSuffix", this.nodes.parent)[0];
		this.nodes.valuePrefix			= document.getElementsByClassName(this.cssPrefix + "form_valuePrefix", this.nodes.parent)[0];
                
		this.nodes.valuePrefix          = document.getElementsByClassName(this.cssPrefix + "form_valuePrefix", this.nodes.parent)[0];
		this.nodes.valueSuffix          = document.getElementsByClassName(this.cssPrefix + "form_valueSuffix", this.nodes.parent)[0];
        
		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName(this.cssPrefix + "form_values_group", this.nodes.parent)[0];
        this.nodes.formatedText         = document.getElementsByClassName(this.cssPrefix + 'form_advancedText', this.nodes.parent)[0];
        
		this.nodes.cancel 	            = document.getElementsByClassName(this.cssPrefix + "cancel", this.nodes.parent)[0];
		this.nodes.save 	            = document.getElementsByClassName(this.cssPrefix + "save", this.nodes.parent)[0];
        
        this.nodes.cancelLink          = $("specField_item_new_" + this.categoryID + "_cancel");

		this.nodes.translationsLinks 	= document.getElementsByClassName(this.cssPrefix + "form_values_translations_language_links", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.valuesDefaultGroup.getElementsByClassName(this.cssPrefix + "add_field", this.nodes.parent)[0];

        this.nodes.valuesTranslations = {};

		var ul = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];
		ul.id = this.cssPrefix + "form_" + this.id + '_values_' + this.languageCodes[0];
        
        this.nodes.specFieldValuesTemplate = document.getElementsByClassName(this.cssPrefix + "form_values_value", this.nodes.valuesDefaultGroup)[0];
        this.nodes.specFieldValuesUl       = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];
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

		for(var i = 0; i < this.nodes.stateLinks.length; i++)
		{
            Event.observe(this.nodes.stateLinks[i], "click", function(e) { self.changeStateAction(e) } );
		}

        Event.observe(this.nodes.name, "keyup", function(e) { self.generateHandleAndTitleAction(e) } );
        Event.observe(this.nodes.valuesAddFieldLink, "click", function(e) { self.addValueFieldAction(e) } );
        Event.observe(this.nodes.type, "change", function(e) { self.typeWasChangedAction(e) } );
        Event.observe(this.nodes.cancel, "click", function(e) { Event.stop(e); self.cancelAction() } );
        Event.observe(this.nodes.cancelLink, "click", function(e) { Event.stop(e); self.cancelAction() } );
        Event.observe(this.nodes.save, "click", function(e) { self.saveAction(e) } );

		// Also some actions must be executed on load. Be aware of the order in which those actions are called
		this.loadSpecFieldAction();
		this.loadValueFieldsAction();
		this.bindTranslationValues();
		this.typeWasChangedAction();

		new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
		Form.backup(this.nodes.form);
        
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
        this.type = this.nodes.type.value;
    
		// if selected type is a selector type then show selector options fields (aka step 2)
        var valuesTranslations = document.getElementsByClassName(this.cssPrefix + "step_values_translations", this.nodes.stepValues)[0];
		if(this.selectorValueTypes.indexOf(this.nodes.type.value) === -1)
		{
			this.nodes.stateLinks[1].parentNode.style.display = 'none';
			this.nodes.stateLinks[1].style.display = 'none';
            this.nodes.multipleSelector.parentNode.style.display = 'none';
		}
		else
		{
			this.nodes.stateLinks[1].parentNode.style.display = 'inline';
			this.nodes.stateLinks[1].style.display = 'inline';
            this.nodes.multipleSelector.parentNode.style.display = 'block';
            
            valuesTranslations.style.display = this.isNumber(this.type) ? 'none' : 'block';
		}
        
        this.nodes.formatedText.style.display = this.type == Backend.SpecField.prototype.TYPE_TEXT_SIMPLE ? 'block' : 'none';
	},


    bindOneValue: function(li)
    {        
        var self = this;
	    var input = li.getElementsByTagName("input")[0];
        if(input.type == 'text')
        {
            Event.observe(input, "keyup", function(e) { self.mainValueFieldChangedAction(e) } );
            Event.observe(input, "keydown", function(e) { self.mainValueFilterKeysAction(e) } );
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
	    this.fieldsList = ActiveList.prototype.getInstance(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0], {
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
                    
                    if(emptyFilters || confirm(self.messages.removeFieldQuestion))
                    {
                        self.deleteValueFieldAction(li, this);
                    }
                }
                else if(confirm(self.messages.removeFieldQuestion))
                {
                    return Backend.SpecField.prototype.links.deleteValue + this.getRecordId(li);
                }
	        },
	        afterDelete: function(li, response){ self.deleteValueFieldAction(li, this) }
	    }, this.msg.activeListMessages);
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
        this.nodes.handle.id = this.cssPrefix + this.categoryID + "_" + this.id + "_handle"; 

		this.nodes.name.value = this.specField.name_lang ? this.specField.name_lang : '';
        this.nodes.valuePrefix.value = this.specField.valuePrefix_lang ? this.specField.valuePrefix_lang : '';        
        this.nodes.valueSuffix.value = this.specField.valueSuffix_lang ? this.specField.valueSuffix_lang : '';
        
        this.nodes.name.id = this.cssPrefix + this.categoryID + "_" + this.id + "_name_" + this.languageCodes[0]; 
        this.nodes.valuePrefix.id = this.cssPrefix + this.categoryID + "_" + this.id + "_valuePrefix_" + this.languageCodes[0]; 
        this.nodes.valueSuffix.id = this.cssPrefix + this.categoryID + "_" + this.id + "_valueSuffix_" + this.languageCodes[0]; 
        
        
		this.nodes.name.name = "name[" + this.languageCodes[0] + "]";
		this.nodes.valuePrefix.name = "valuePrefix[" + this.languageCodes[0] + "]";
		this.nodes.valueSuffix.name = "valueSuffix[" + this.languageCodes[0] + "]";
                   
		this.nodes.multipleSelector.checked = this.isMultiValue;
		this.nodes.isRequired.checked = this.isRequired;
		this.nodes.isDisplayed.checked = this.isDisplayed;
		this.nodes.isDisplayedInList.checked = this.isDisplayedInList;
        
        this.nodes.multipleSelector.id     = this.cssPrefix + this.categoryID + "_" + this.id + "_multipleSelector"; 
        this.nodes.isRequired.id           = this.cssPrefix + this.categoryID + "_" + this.id + "_isRequired"; 
        this.nodes.isDisplayed.id          = this.cssPrefix + this.categoryID + "_" + this.id + "_isDisplayed"; 
        this.nodes.isDisplayedInList.id    = this.cssPrefix + this.categoryID + "_" + this.id + "_isDisplayedInList"; 
        

        this.nodes.labels.name.onclick                = function() { self.nodes.name.focus() };
        this.nodes.labels.valuePrefix.onclick         = function() { self.nodes.valuePrefix.focus() };
        this.nodes.labels.valueSuffix.onclick         = function() { self.nodes.valueSuffix.focus() };
        this.nodes.labels.handle.onclick              = function() { self.nodes.handle.focus() };
        this.nodes.labels.multipleSelector.onclick    = function() { self.nodes.multipleSelector.focus() };
        this.nodes.labels.isRequired.onclick 	      = function() { self.nodes.isRequired.focus() };
        this.nodes.labels.isDisplayed.onclick         = function() { self.nodes.isDisplayed.focus() };
        this.nodes.labels.isDisplayedInList.onclick   = function() { self.nodes.isDisplayedInList.focus() };
        this.nodes.labels.type.onclick                = function() { self.nodes.type.focus() };
        this.nodes.labels.description.onclick         = function() { self.nodes.description.focus() };
        
        if(this.type == Backend.SpecField.prototype.TYPE_TEXT_ADVANCED)
        {
            this.nodes.type.value = Backend.SpecField.prototype.TYPE_TEXT_SIMPLE;
            this.nodes.formatedText.down('input').checked = true;
        }
        else
        {
            this.nodes.type.value = this.type;
            this.nodes.formatedText.checked = false;
        }

        this.changeMainTitleAction(this.nodes.name.value);

		if(this.specField.description_lang) this.nodes.description.value = this.specField.description_lang;
		this.nodes.description.name = "description[" + this.languageCodes[0] + "]";
        
        this.nodes.description.id = this.cssPrefix + this.categoryID + "_" + this.id + "_description_" + this.languageCodes[0]; 
        

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
                var legend = newTranslation.getElementsByTagName("legend")[0];
                Event.observe(legend, "click", function(e) { ActiveForm.prototype.toggleTranslations(this.up('fieldset')) } );
                
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
                        var translationId = this.cssPrefix + this.categoryID + "_" + this.id + "_" + inputFields[j].name + "_" + this.languageCodes[i];
						var translationLabel = inputFields[j].up().down("label");
                        translationLabel.languageCode = this.languageCodes[i];
                        translationLabel.simpleName = inputFields[j].name;
                        
                        var _self_ = this;
                        Event.observe(translationLabel, "click", function(e) { 
                            $(_self_.cssPrefix + _self_.categoryID + "_" + _self_.id + "_" + this.simpleName + "_" + this.languageCode).focus();
                        });
                        
                        inputFields[j].id = translationId;
                        eval("if(self.specField." + inputFields[j].name + "_" + self.languageCodes[i] + ") inputFields[j].value = self.specField."+inputFields[j].name + "_" + self.languageCodes[i] + ";");
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
                
                Event.observe(valueTranslationLegend, "click", function(e) { ActiveForm.prototype.toggleTranslations(e.target.parentNode.parentNode) } );
                
                
				valuesTranslations[0].parentNode.appendChild(newValueTranslation);
                this.nodes.valuesTranslations[this.languageCodes[i]] = newValueTranslation;
			}
		}
        
		// Delete language template, so that included in that template variables would not be sent to server
		Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
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
            this.fieldsList.touch();
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
        if(!e.target)
		{
			e.target = e.srcElement;
		}

		Event.stop(e);

		this.addField(null, "new" + Backend.SpecField.prototype.countNewValues, true);
        this.bindDefaultFields();
		Backend.SpecField.prototype.countNewValues++;
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
        CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);

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
		if(!e.target)
		{
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
        if(!e.target)
		{
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
        if(!e.target)
		{
			e.target = e.srcElement;
		}

		var keyboard = new KeyboardEvent(e);

		if(
            this.isNumber(this.type) && // if it is a number
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

		if(this.id.match(/new$/))
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
		if(!value) value = {};
		
		var values_template = this.nodes.specFieldValuesTemplate;
		var ul = this.nodes.specFieldValuesUl;

        if(!this.fieldsList) this.bindDefaultFields();
        var li = this.fieldsList.addRecord(id, values_template);

		// The field itself
		var input = li.down("input");
		input.name = "values[" + id + "]["+this.languageCodes[0]+"]";
		input.value = value.value_lang ? value.value_lang : '' ;
        
        input.id = this.cssPrefix + "field_" + id + "_value_" + this.languageCodes[0];

		// now insert all translation fields
		for(var i = 1; i < this.languageCodes.length; i++)
		{
			var newValueTranslation = document.getElementsByClassName(this.cssPrefix + "form_values_value", this.nodes.valuesTranslations[this.languageCodes[i]])[0].cloneNode(true);
			Element.removeClassName(newValueTranslation, "dom_template");

			newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;

			var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
			inputTranslation.name = "values[" + id + "][" + this.languageCodes[i] + "]";
			inputTranslation.value = value['value_' + this.languageCodes[i]] ? value['value_' + this.languageCodes[i]] : '';
            
            var translationLabel = newValueTranslation.down("label");
            translationLabel.update(input.value);
            
			// add to node tree
			var translationsUl = document.getElementsByClassName(this.cssPrefix + "form_values_translations", this.nodes.valuesTranslations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
			translationsUl.id = this.cssPrefix + "form_"+this.id+'_values_'+this.languageCodes[i];
			translationsUl.appendChild(newValueTranslation);
            
            inputTranslation.id = this.cssPrefix + "field_" + id + "_value_" + this.languageCodes[i];
            translationLabel['for'] = inputTranslation.id;
            translationLabel.onclick = function() { $(this['for']).focus(); }
		}
        
        this.bindOneValue(li);
	},


    /**
     * This method is called when user click on cancel link. It resets all fields to its defaults and closes form
     *
	 * @param Event e Event
	 *
	 * @access public
	 *
     */
    cancelAction: function()
    {
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
            ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleContainer(this.nodes.parent, 'edit');
        }
        else
        {
            this.hideNewSpecFieldAction(this.categoryID);
        }
        
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
    },

    /**
     * This method is called when user clicks on save button. It saves form values, and does i don't know what (i guess it should close the form)
     *
	 * @param Event e Event
	 *
	 * @access public
     */
    saveAction: function(e)
    {
        if(!e.target)
		{
			e.target = e.srcElement;
		}

		Event.stop(e);
        
        this.saveSpecField();
    },
    
    /**
     * This action is executed when saving specification field. THis method will be executed before ajax request to the server is sent
     */
    saveSpecField: function()
    {
		// Toggle progress won't work on new form
		try
		{
		    ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
		    ActiveForm.prototype.onProgress(this.nodes.form);
            // New item has no pr06r3s5 indicator
		}
        
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        
        var self = this;
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
        var jsonResponse = eval("("+jsonResponseString+")");
        
        if(jsonResponse.status == 'success')
        {
            Form.backup(this.nodes.form);
            this.backupName = this.nodes.name.value;

            if(this.nodes.parent.tagName.toLowerCase() == 'li')
            {
                ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleContainer(this.nodes.parent, 'edit');
            }
            else
            {
                var div = document.createElement('span');
                Element.addClassName(div, 'specField_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                
                var activeRecord = ActiveList.prototype.getInstance("specField_items_list_" + this.categoryID + '_');
                activeRecord.addRecord(jsonResponse.id, [document.createTextNode(' '), div]);
                activeRecord.touch();
                
                this.hideNewSpecFieldAction(this.categoryID);
    		    this.recreate(this.specField, true);
            }
            
            CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
            
            // Reload filters (uncomment when API is frozen)
            
            try { // try to remove filter container
                var tc = Backend.Category.tabControl;    
                
                var tabContent = $(tc.getContainerId('tabFilters', tc.treeBrowser.getSelectedItemId()));
                $A(tabContent.getElementsByTagName("ul")).each(function(ul) {
                    try{ ActiveList.prototype.destroy(ul); } catch(e){ }
                });
                
                Element.remove(tabContent);
            } catch (e){ 
            }
        }
        else if(jsonResponse.errors) 
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, jsonResponse.errors);
        }

		try
		{
		    ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
            ActiveForm.prototype.offProgress(this.nodes.form);
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
        
        Backend.SpecFieldGroup.prototype.hideMenuItems($("specField_menu_" + categoryID), [$("specField_group_new_" + categoryID + "_show"), $("specField_item_new_" + categoryID + "_show")]);
            
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
    createNewAction: function(categoryID)
    {
        ActiveList.prototype.collapseAll();        
        ActiveForm.prototype.showNewItemForm($(this.cssPrefix + "item_new_"+categoryID+"_show"), $(this.cssPrefix + "item_new_"+categoryID+"_form"));  
        Backend.SpecFieldGroup.prototype.hideMenuItems($("specField_menu_" + categoryID), [$(this.cssPrefix + "item_new_" + categoryID + "_cancel")]);
    }    
}




/**
 * Backend.SpecFieldGroup manages specification field groups 
 * 
 * To create group you should pass parent element (HTMLLiElement if you this group is allready in ActiveList or HTMLDivElement if it's a new group) if it is 
 * 
 * @author Sergej Andrejev
 * @namespace Backend.SpecField
 */
Backend.SpecFieldGroup = Class.create();
Backend.SpecFieldGroup.prototype = {
     cssPrefix: 'specField_',
     
     callbacks: {
        beforeEdit:     function(li) 
        {
            try
            {
                if(!Backend.SpecFieldGroup.prototype.isGroupTranslated(li))
                {
                    return Backend.SpecField.prototype.links.getGroup + this.getRecordId(li);
                }
                else
                {
                    if('block' != document.getElementsByClassName('specField_group_form', li)[0].style.display)
                    {
                         Backend.SpecFieldGroup.prototype.displayGroupTranslations(li);
                    }
                    else
                    {
                         Backend.SpecFieldGroup.prototype.hideGroupTranslations(li);
                    }   
                }
            } 
            catch(e) 
            {  
                console.info(e) 
            }
        },
        afterEdit:      function(li, response) { 
            try
            {
                new Backend.SpecFieldGroup(li, eval("(" + response + ")"));
                Backend.SpecFieldGroup.prototype.displayGroupTranslations(li);  
            } 
            catch(e) 
            {  
                console.info(e) 
            }
        },
        beforeDelete:   function(li) {
            if(confirm(Backend.SpecField.prototype.msg.removeGroupQuestion))
            return Backend.SpecField.prototype.links.deleteGroup + this.getRecordId(li)
        },
        afterDelete:    function(li, jsonResponse)
        {
            var response = eval("("+jsonResponse+")");
            if(response.status == 'success') {
                this.remove(li);
                CategoryTabControl.prototype.resetTabItemsCount(this.getRecordId(li, 2));
            }
        },
        beforeSort:     function(li, order) {
            return Backend.SpecField.prototype.links.sortGroups + "?target=" + this.ul.id + "&" + order
        },
        afterSort:     function(li, order) { }
     },
     
     /**
      * Consturctor
      * 
      * @param HTMLElement parent Parent node
      * @param Object group Evaluated group data
      */
     initialize: function(parent, group)
     {
         try
         {
             this.group = group;
             this.findNodes(parent);
             this.generateGroupTranslations();
             this.bindEvents(); 
             Form.backup(this.nodes.form);
         }
         catch(e)
         {
             console.info(e);
         }
     },
     
     /**
      * Find all nodes used by this object
      * 
      * @param HTMLElement parent Parent node
      */
     findNodes: function(parent)
     {
        this.nodes = {};

        this.nodes.parent              = parent;
        this.nodes.form                = document.getElementsByClassName(this.cssPrefix + 'group_form', this.nodes.template)[0].cloneNode(true);
        this.nodes.template            = $('specField_group_blank');
        this.nodes.translations        = document.getElementsByClassName(this.cssPrefix + 'group_translations', this.nodes.form)[0];
        this.nodes.controls            = document.getElementsByClassName(this.cssPrefix + 'group_controls', this.nodes.form)[0];
        this.nodes.translationTemplate = document.getElementsByClassName(this.cssPrefix + 'group_translations_language_', this.nodes.translations)[0];
        this.nodes.name                = document.getElementsByClassName(this.cssPrefix + 'group_default_language', this.nodes.translations)[0].down("input");
        this.nodes.mainTitle           = document.getElementsByClassName(this.cssPrefix + 'group_title', this.nodes.parent)[0];
        this.nodes.categoryID          = document.getElementsByClassName(this.cssPrefix + 'group_categoryID', this.nodes.form)[0];
        this.nodes.save                = document.getElementsByClassName(this.cssPrefix + 'save', this.nodes.controls)[0];
        this.nodes.cancel              = document.getElementsByClassName(this.cssPrefix + 'cancel', this.nodes.controls)[0];
        this.nodes.topCancel           = $(this.cssPrefix + 'group_new_' + this.group.Category.ID + '_cancel')
     },
     
     bindEvents: function()
     {
         var self = this;
		 if(this.nodes.mainTitle) Event.observe(self.nodes.name, 'keyup', function(e) { self.nodes.mainTitle.innerHTML = self.nodes.name.value });
		 Event.observe(self.nodes.save, 'click', function(e) { Event.stop(e); self.beforeSave() });
		 Event.observe(self.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel() });
		 Event.observe(self.nodes.topCancel, 'click', function(e) { Event.stop(e); self.cancel() });
     },
     
    /**
     * Genereate HTML code from group object
     * 
     * @param HTMLElement parent Parent element
     * @param integer id Group Id
     */
    generateGroupTranslations: function(parent, group)
    {
        var self = this;
        Backend.SpecField.prototype.loadLanguagesAction();

        this.nodes.name.name += "[" + Backend.SpecField.prototype.languageCodes[0] + "]";
        if(this.group.name_lang) this.nodes.name.value = this.group.name_lang;
        
        this.nodes.categoryID.value = this.group.Category.ID;
        
        $H(Backend.SpecField.prototype.languages).each(function(language) {
            if(language.key == Backend.SpecField.prototype.languageCodes[0]) throw $continue;
            
            var languageTranlation = self.nodes.translationTemplate.cloneNode(true);
            Element.removeClassName(languageTranlation, self.cssPrefix + 'group_translations_language_');
            Element.removeClassName(languageTranlation, 'dom_template');
            Element.addClassName(languageTranlation, self.cssPrefix + 'group_translations_language_' + language.key);

            
            var legend = languageTranlation.getElementsByTagName('legend')[0];
            var languageName = document.getElementsByClassName(self.cssPrefix + 'group_translation_language_name', legend)[0];  
            languageName.innerHTML  = language.value;
                        
            var translationInput   = languageTranlation.getElementsByTagName("input")[0];
            translationInput.name  += '[' + language.key + ']';
            translationInput.value = self.group["name_" + language.key] ? self.group["name_" + language.key] : '';
            
            Event.observe(legend, "click", function(e) { ActiveForm.prototype.toggleTranslations(legend.parentNode) } );

            self.nodes.translations.appendChild(languageTranlation);
            
        });
        
        Element.remove(self.nodes.translationTemplate);
        
        try
        {
            this.nodes.parent.insertBefore(this.nodes.form, this.nodes.mainTitle.nextSibling);
        }
        catch(e)
        {
            this.nodes.parent.appendChild(this.nodes.form);
        }
    },
    
    /**
     * Run this code before saving group in database
     */
    beforeSave: function()
    {
		try
		{
            ActiveList.prototype.getInstance(this.cssPrefix + 'groups_list_' + this.group.Category.ID).toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
		    ActiveForm.prototype.offProgress(this.nodes.form);
		}

        var self = this;
        
        new Ajax.Request(
            this.nodes.form.getElementsByTagName('form')[0].action + (this.group.ID ? this.group.ID : ''),
            {
                method: 'post',
                postBody: Form.serialize(this.nodes.form),
                onComplete: function(response) { 
                    self.afterSave(eval("(" + response.responseText + ")")); 
                }
            }
        );
    },
    
    /**
     * Run this code after trying to save group in database
     * 
     * @param Object response Evaluated server response
     */
    afterSave: function(response)
    {
		if(response.status == 'success')
        {
    		try
    		{
                ActiveList.prototype.getInstance(this.cssPrefix + 'groups_list_' + this.group.Category.ID).toggleProgress(this.nodes.parent);
                Form.backup(this.nodes.form);
                Backend.SpecFieldGroup.prototype.hideGroupTranslations(this.nodes.parent);
    		}
    		catch (e)
    		{
    		    ActiveForm.prototype.offProgress(this.nodes.form);
                
                var title = document.createElement('span');
                Element.addClassName(title, this.cssPrefix + 'group_title');
                title.appendChild(document.createTextNode(this.nodes.name.value));
                
                var ul = document.createElement('ul');
                ul.id = this.cssPrefix + "items_list_" + this.group.Category.ID + "_" + response.id;
                Element.addClassName(ul, 'specFieldList'); 
                Element.addClassName(ul, 'activeList_add_sort'); 
                Element.addClassName(ul, 'activeList_add_edit'); 
                Element.addClassName(ul, 'activeList_add_delete'); 
                Element.addClassName(ul, 'activeList_accept_specFieldList'); 
                Element.addClassName(ul, 'activeList'); 
                
                $(this.cssPrefix + "group_new_" + this.group.Category.ID + "_show").style.display = 'inline';
                
                var groupsList = ActiveList.prototype.getInstance(this.cssPrefix + "groups_list_" + this.group.Category.ID);
                groupsList.addRecord(response.id, [title, ul]);
                groupsList.touch();
                
                ActiveList.prototype.recreateVisibleLists();
                ActiveList.prototype.getInstance(ul, Backend.SpecFieldGroup.prototype.specFieldGroupCallbacks, Backend.SpecField.prototype.msg.activeListMessages);
                
                Form.restore(this.nodes.form);
                
                Backend.SpecFieldGroup.prototype.hideMenuItems($("specField_menu_" + this.group.Category.ID), [$("specField_group_new_" + this.group.Category + "_show"), $("specField_item_new_" + this.group.Category + "_show")]);
                ActiveForm.prototype.hideNewItemForm($(this.cssPrefix + "group_new_" + this.group.Category.ID + "_show"), this.nodes.parent); 
    		}
            
            ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        }
        else if(response.errors) 
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    /**
     * This code is executed when you hit on cancel button
     */
    cancel: function()
    {
        if(Form.hasBackup(this.nodes.form))
		{
            Form.restore(this.nodes.form);
        }
        
        if(!this.group || !this.group.ID)
        {
            Backend.SpecFieldGroup.prototype.hideMenuItems($("specField_menu_" + this.group.Category.ID), [$("specField_group_new_" + this.group.Category.ID + "_show"), $("specField_item_new_" + this.group.Category.ID + "_show")]);
            ActiveForm.prototype.hideNewItemForm($(this.cssPrefix + "group_new_" + this.group.Category.ID + "_show"), this.nodes.parent); 
        }
        else
        {
            Backend.SpecFieldGroup.prototype.hideGroupTranslations(this.nodes.parent);
        }
        
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
    },
    
    
    /**
     * Remove display none from group translations
     * 
     * @param HTMLElement parent
     */
    displayGroupTranslations: function(parent)
    {
        ActiveForm.prototype.showNewItemForm(
            document.getElementsByClassName(this.cssPrefix + 'group_title', parent)[0], 
            document.getElementsByClassName(this.cssPrefix + 'group_form', parent)[0]
        ); 
    },
    
    
    /**
     * Hide group group translations and show group title
     * 
     * @param HTMLElement parent
     */
    hideGroupTranslations: function(parent)
    {
        ActiveForm.prototype.hideNewItemForm(
            document.getElementsByClassName(this.cssPrefix + 'group_title', parent)[0], 
            document.getElementsByClassName(this.cssPrefix + 'group_form', parent)[0]
        ); 
    },
    
    /**
     * Check if form elements for translating this group are created or not
     * 
     * @param HTMLElement parent
     * @return boolean
     */
    isGroupTranslated: function(parent)
    {
        return document.getElementsByClassName(this.cssPrefix + 'group_form', parent).length > 0;
    },

    hideMenuItems: function(menu, except)
    {
        menu = $(menu);
        
        $A(menu.getElementsByTagName('li')).each(function(li) {
            a = $(li).down('a');
            a.addClassName('hidden');
            $A(except).each(function(el) { if(a == $(el)) a.removeClassName('hidden');  });
        });
    },

    /**
     * This method unfolds "Create new Spec Field group" form. 
     */
    createNewAction: function(categoryID)
    {                
        ActiveList.prototype.collapseAll();        
        ActiveForm.prototype.showNewItemForm(
            $(this.cssPrefix + "group_new_" + categoryID + "_show"), 
            $(this.cssPrefix + "group_new_" + categoryID + "_form")
        );   
        
        Backend.SpecFieldGroup.prototype.hideMenuItems($("specField_menu_" + categoryID), [$(this.cssPrefix + "group_new_" + categoryID + "_cancel")]);
    }    
}