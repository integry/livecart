/**
 * Backend.Filter
 *
 * This class manages filters forms
 *
 * Create object by passing json to constructor
 * @example
 * <code>
 *     new Backend.Filter({
 *         'ID': 15
 *         'ID': 15
 *         'name': {'lt': 'Pagal dydi'}
 *         'rootId': 'filter_item_new_41_form'
 *         'categoryID': 41
 *         'specFields: { // SpecFieldArray in json // } 
 *     });
 * </code>
 *
 * You should also modify prototype by passing settins to it
 * 
 * @example
 * <code>highli
 *   Backend.Filter.prototype.links = {};
 *   Backend.Filter.prototype.links.deleteGroup = '/en/backend.filter/delete/';
 *   Backend.Filter.prototype.links.editGroup = '/en/backend.filter/item/';
 *   Backend.Filter.prototype.links.sortGroup = '/en/backend.filter/sort/';
 *   Backend.Filter.prototype.links.deleteFilter = '/en/backend.filter/deleteFilter/';
 *   Backend.Filter.prototype.links.sortFilter = '/en/backend.filter/sortFilter/';
 *   Backend.Filter.prototype.links.generateFilters = '/en/backend.filter/generateFilters/';
 *   
 *   Backend.Filter.prototype.languages = {"en":"English","lt":"Lithuanian","lv":"Latvian"};
 *   Backend.Filter.prototype.messages = {"deleteField":"delete field"};
 *   Backend.Filter.prototype.selectorValueTypes = [1,5];
 *   Backend.Filter.prototype.countNewFilters = 0;
 *   Backend.Filter.prototype.typesWithNoFiltering = [];
 *   Backend.Filter.prototype.dateFormat = "%d-%b-%Y";
 * </code>
 *
 * @version 1.0
 * @author Sergej Andrejev
 */
if (Backend == undefined)
{
    var Backend = {}
}

Backend.Filter = Class.create();
Backend.Filter.prototype = {
    cssPrefix: "filter_",
    countNewFilters: 0,
    
    activeListCallbacks: {
         beforeEdit:     function(li)
         {
             Backend.Filter.prototype.hideNewFilterAction(this.getRecordId(li, 2));
              
             if(this.isContainerEmpty(li, 'edit')) return Backend.Filter.prototype.links.editGroup + this.getRecordId(li)
             else this.toggleContainer(li, 'edit');
         },

         afterEdit:      function(li, response)
         {
             new Backend.Filter(response);
             this.toggleContainer(li, 'edit');
         },
 
         beforeDelete:   function(li)
         {
             if(confirm('{/literal}{t _FilterGroup_remove_question|addslashes}{literal}'))  return Backend.Filter.prototype.links.deleteGroup + this.getRecordId(li)
         },
   
         afterDelete:    function(li, jsonResponse)
         {
             var response = eval("("+jsonResponse+")");
 
             if(response.status == 'success') 
             {
                 this.remove(li);
                 CategoryTabControl.prototype.resetTabItemsCount(this.getRecordId(li, 2));
             }
         },   

         beforeSort:     function(li, order)
         {
             return Backend.Filter.prototype.links.sortGroup + '?target=' + "filter_items_list_" + this.getRecordId(li, 2) + "&" + order
         },
    
         afterSort:      function(li, response) { }
     }, 
    
    
    /**
     * Constructor
     *
     * @param filtersJson Spec Field filters
     * @param hash If true the passed filter is an object. If hash is not passed or false then filterJson will be assumed as a string
     *
     * @access public
     */
    initialize: function(filterJson, hash)
    {
        try
        {
            this.filter = !hash ? eval("(" + filterJson + ")" ) : filterJson;
            
            this.cloneForm('filter_item_blank', this.filter.rootId);
    
            this.id = this.filter.ID;
            
            
            this.categoryID = this.filter.categoryID;
            this.rootId = this.filter.rootId;
            this.filtersCount = this.filter.filtersCount ? this.filter.filtersCount : 0;
            this.specFields = this.filter.specFields;
            this.name = this.filter.name;
            this.filters = this.filter.filters;
            this.backupName = this.name;
            
            this.filterCalendars = {};

            this.loadLanguagesAction();
            this.findUsedNodes();
            this.bindFields();
            this.generateTitleFromSpecField();
        }
        catch(e)
        {
            console.info(e);
        }
    },

    /**
     * This function destroys the old filter group form, then clones the prototype and then calls constructor once again
     *
     * @param object filterJson Filter group form values
     *
     * @access public
     */
    recreate: function(filterJson)
    {
        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ? ActiveList.prototype.getInstance(this.nodes.parent.parentNode).getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);
        root.innerHTML = '';
        $H(this).each(function(el)
        {
            if(el[1])
            {
                if(el[1].ul) 
                {
                    ActiveList.prototype.destroy(el[1].ul.id);
                    console.info(el[1].ul.id);
                }
                
                delete el[1];
            }
        });
        
        this.initialize(filterJson, true);
    },


    /**
     * Create a clone of form from prototype form
     *
     * @param prototypeId Id of root prototype element
     * @param rootId Id of root element where the copy of prototype will be copied
     *
     * @access private
     */
    cloneForm: function(prototypeId, rootId)
    {
        var blankForm = $(prototypeId);
        var blankFormFilters = blankForm.getElementsByTagName("*");

        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ? ActiveList.prototype.getInstance("filter_items_list_" + this.filter.categoryID).getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);
        
        for(var i = 0; i < blankFormFilters.length; i++)
        {
            if(blankFormFilters[i] && blankFormFilters[i].parentNode == blankForm)
            {
                root.appendChild(blankFormFilters[i].cloneNode(true));
            }
        }
    },


    /**
     * Find ussed nodes
     *
     * @access private
     */
    findUsedNodes: function()
    {
        if(!this.nodes) this.nodes = [];

        this.nodes.parent = document.getElementById(this.rootId);

        this.nodes.form                   = this.nodes.parent.getElementsByTagName("form")[0];

        this.nodes.id                     = document.getElementsByClassName(this.cssPrefix + "form_id", this.nodes.parent)[0];
        this.nodes.name                   = document.getElementsByClassName(this.cssPrefix + "form_name", this.nodes.parent)[0];
        this.nodes.specFieldID            = document.getElementsByClassName(this.cssPrefix + "form_specFieldID", this.nodes.parent)[0];

        this.nodes.stepTranslations       = document.getElementsByClassName(this.cssPrefix + "step_translations", this.nodes.parent)[0];
        this.nodes.stepMain               = document.getElementsByClassName(this.cssPrefix + "step_main", this.nodes.parent)[0];
        this.nodes.stepValues             = document.getElementsByClassName(this.cssPrefix + "step_filters", this.nodes.parent)[0];
        this.nodes.stepLevOne             = document.getElementsByClassName(this.cssPrefix + "step_lev1", this.nodes.parent);
        this.nodes.generateFiltersLink    = document.getElementsByClassName(this.cssPrefix + "generate_filters", this.nodes.parent)[0];
        this.nodes.defaultFiltersList     = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.filtersDefaultGroup);

        for(var i = 0; i < this.nodes.stepLevOne.length; i++)
        {
            if(!this.nodes.stepLevOne[i].id) this.nodes.stepLevOne[i].id = this.nodes.stepLevOne[i].className.replace(/ /, "_") + "_" + this.id;
        }

        this.nodes.mainTitle              = document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
        this.nodes.filtersCount           = document.getElementsByClassName(this.cssPrefix + "count", this.nodes.parent)[0];
        this.nodes.stateLinks             = document.getElementsByClassName(this.cssPrefix + "change_state", this.nodes.parent);
        this.nodes.cancel                 = document.getElementsByClassName(this.cssPrefix + "cancel", this.nodes.parent)[0];
        this.nodes.save                   = document.getElementsByClassName(this.cssPrefix + "save", this.nodes.parent)[0];

        this.nodes.translationsLinks      = document.getElementsByClassName(this.cssPrefix + "form_filters_translations_language_links", this.nodes.parent)[0];
        this.nodes.filtersDefaultGroup    = document.getElementsByClassName(this.cssPrefix + "form_filters_group", this.nodes.parent)[0];
        this.nodes.addFilterLink          = this.nodes.filtersDefaultGroup.getElementsByClassName(this.cssPrefix + "add_filter", this.nodes.parent)[0];

        this.nodes.translationsUl = {};
        this.nodes.valuesTranslations = {};
        this.nodes.translation_templates = {};
        
        this.nodes.filterTemplate = this.nodes.filtersDefaultGroup.down("." + this.cssPrefix + "form_filters_value");
        this.nodes.filtersList = this.nodes.filtersDefaultGroup.down('ul');
        
        var ul = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0];
        ul.id = this.cssPrefix + "form_"+this.id+'_filters_'+this.languageCodes[0];

    },

    /**
     * Find all translations fields. This is done every time when new filter is being added
     *
     * @access private
     */
    bindTranslationFilters: function()
    {
        this.nodes.translatedFilters = document.getElementsByClassName(this.cssPrefix + "form_filters_translations", this.nodes.parent);
    },


    /**
     * Generate filters
     * 
     * @param Object e
     */
    generateFiltersAction: function(e)
    {
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);
    
        // execute the action
        var self = this;
        new Ajax.Request(
            this.links.generateFilters + "?specFieldID="+this.nodes.specFieldID.value,
            {
                method: 'get',
                onComplete: function(param)
                {
                    self.addGeneratedFilters(param.responseText)
                }
        }); 
    },
    
    /**
     * Add generated filters to filters list
     * 
     * @param Object jsonString
     */
    addGeneratedFilters: function(jsonString)
    {
        var self = this;
    
        var jsonResponse = eval("("+jsonString+")");
              
        for(var i = 0; i < this.specFields.length; i++)
        {
            if(this.specFields[i].ID != this.nodes.specFieldID.value) continue;
           
            var specField = this.specFields[i];
            if(this.selectorValueTypes.indexOf(specField.type) !== -1)
            {
                $A(this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName("li")).each(function(li)
                {
                    if(!Element.hasClassName(li, 'dom_template'))
                    {
                        delete jsonResponse.filters[document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0].value];
                    }
                });
            }
            
            $H(jsonResponse.filters).each(function(filter) {
                self.addFilter(filter.value, "new" + Backend.Filter.prototype.countNewFilters, true);
                self.changeFiltersCount(self.filtersCount+1);
                Backend.Filter.prototype.countNewFilters++;
            });
            
            this.bindDefaultFields();
            this.filtersList.touch();
                
            return;
        }
    },  


    /**
     * Binds fields to some events
     */
    bindFields: function()
    {
        var self = this;

        for(var i = 0; i < this.nodes.stateLinks.length; i++)
        {
            Event.observe(this.nodes.stateLinks[i], "click", function(e) { self.changeStateAction(e) });
        }

        Event.observe(this.nodes.name, "keyup", function(e) { self.generateTitleAction(e) });
        Event.observe(this.nodes.addFilterLink, "click", function(e) { self.addFilterFieldAction(e) });
        
        Event.observe(this.nodes.specFieldID, "change", function(e) { self.specFieldIDWasChangedAction(e) });        
        Event.observe(this.nodes.specFieldID, "change", function(e) { self.generateTitleFromSpecField(e) });
        
        Event.observe(this.nodes.cancel, "click", function(e) { self.cancelAction(e) });
        Event.observe(this.nodes.save, "click", function(e) { self.saveAction(e) });
        
        Event.observe(this.nodes.generateFiltersLink, "click", function(e) { self.generateFiltersAction(e) });
             
        
        // Also some actions must be executed on load. Be aware of the order in which those actions are called
        this.fillSpecFieldsSelect();
        
        if(this.filter.SpecField) this.nodes.specFieldID.value = this.filter.SpecField.ID;
        
        this.bindDefaultFields();
        this.loadFilterAction();

        this.specFieldIDWasChangedAction();
        this.loadValueFieldsAction();

        this.bindTranslationFilters();

        new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
        Form.backup(this.nodes.form);
    },

    /**
     * Fill spec field select with options
     */
    fillSpecFieldsSelect: function()
    {
        var self = this;

        this.nodes.specFieldID.options.length = 0;
        this.specFields.each(function(value)
        {
            self.nodes.specFieldID.options[self.nodes.specFieldID.options.length] = new Option(value.name, value.ID);
        });

    },


    /**
     * When specField is changed show dates, ranges or select in filters tab
     */
    specFieldIDWasChangedAction: function()
    {
        var self = this;
        for(var i = 0; i < this.specFields.length; i++)
        {
             if(this.specFields[i].ID == this.nodes.specFieldID.value)
             {
                $A(this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName("li")).each(function(li)
                {                    
                    document.getElementsByClassName('filter_range', li)[0].style.display = (self.selectorValueTypes.indexOf(self.specFields[i].type) === -1 && self.specFields[i].type != Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'block' : 'none';
                                                
                    if(self.selectorValueTypes.indexOf(self.specFields[i].type) !== -1)
                    {
                        var select = document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0];
                        select.options.length = 0;
                        for(var j = 0; j < self.specFields[i].values.length; j++)
                        {
                           select.options[select.options.length] = new Option(self.specFields[i].values[j].value[self.languageCodes[0]], self.specFields[i].values[j].ID);
                        } 
                    }                          
                    
                    if((self.selectorValueTypes.indexOf(self.specFields[i].type) === -1 || self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE))
                    {
                        self.nodes.generateFiltersLink.style.visibility = 'hidden';
                        document.getElementsByClassName('filter_selector', li)[0].style.display = 'none';                            
                    }
                    else
                    {
                        self.nodes.generateFiltersLink.style.visibility = 'visible';
                        document.getElementsByClassName('filter_selector', li)[0].style.display = 'block';  
                    }
 
                    document.getElementsByClassName('filter_date_range', li)[0].style.display = (self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'block' : 'none'; 
                });
                
                document.getElementsByClassName(this.cssPrefix + "step_filters_translations", this.nodes.filtersDefaultGroup)[0].style.display = (Backend.SpecField.prototype.TYPE_TEXT_SELECTOR != self.specFields[i].type) ? 'block' : 'block';
                
                return;
             }
        }
    },
    
    
    generateTitleFromSpecField: function(e)
    {    
        var self = this;
        var newTitle = '';
        var changeTitle = false;
        
        this.specFields.each(function(specField) {
            if(self.nodes.name.value == specField.name) changeTitle = true;
            if(specField.ID == self.nodes.specFieldID.value) newTitle = specField.name;
        });
        
        if(changeTitle || self.nodes.name.value == '') 
        {
            self.nodes.name.value = newTitle;;
            this.generateTitleAction(e);
        }
    },
    
    generateTitleAndHandleFromSpecFieldValue: function(li)
    {            
        var self = this;
        var newTitle = '';
        var changeTitle = false;
        
        var nameParagraph     = document.getElementsByClassName('filter_name', li)[0];
        var handleParagraph   = document.getElementsByClassName('filter_handle', li)[0];
        var selectorParagraph = document.getElementsByClassName('filter_selector', li)[0];
        
        var name              = nameParagraph.getElementsByTagName("input")[0];
        var handle            = handleParagraph.getElementsByTagName("input")[0];
        var select            = selectorParagraph.getElementsByTagName("select")[0];
        
        $A(select).each(function(option) {
            if(name.value == option.text) changeTitle = true;
            if(option.selected) newTitle = option.text;
        });
        
        if(changeTitle || name.value == '') 
        {
            name.value = newTitle;
            handle.value = ActiveForm.prototype.generateHandle(newTitle);
        }       
    },

    /**
     * Bind default language filter fields to actions
     */
    bindDefaultFields: function()
    {
        var self = this;
        var liList = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName('li');

        this.filtersList = ActiveList.prototype.getInstance(this.nodes.filtersDefaultGroup.getElementsByTagName("ul")[0], {
            beforeSort: function(li, order)
            {
                return self.links.sortFilter + '?target=' + this.ul.id + '&' + order;
            },
            afterSort: function(li, response){    },

            beforeDelete: function(li){
                if(this.getRecordId(li).match(/^new/))
                {
	                var emptyFilters = true;
                    var inputValues = li.getElementsByTagName("input");
                    for(var i = 0; i < inputValues.length; i++) 
                    {
                        if(!Element.hasClassName('dom_template', inputValues[i]) && inputValues[i].parentNode.style.display != 'none' && inputValues[i].type != 'hidden' && inputValues[i].value != '')
                        {
                            emptyFilters =  false;
                        }
                    }
                    
                    if(emptyFilters || confirm(self.messages.removeFilter))
                    {
                        self.deleteValueFieldAction(li, this);
                    }
                    
                }
                else if(confirm(self.messages.removeFilter))
                {
                    return Backend.Filter.prototype.links.deleteFilter + this.getRecordId(li);
                }
            },
            afterDelete: function(li, response){ self.deleteValueFieldAction(li, this) }
        }, this.activeListMessages);
    },
   
    

    /**
     * Check if range values are valid floats
     * @param Event e
     */
    rangeChangedAction: function(e)
    {
        if(!e.target) e.target = e.srcElement;

        NumericFilter(e.target)

    },

    /**
     * @param string newTitle Modify AR title
     */
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
     * @param string newTitle Modify AR title
     */
    changeFiltersCount: function(count)
    {
        this.filtersCount = count;
        if(this.nodes.filtersCount)
        {
            if(this.nodes.filtersCount.firstChild) this.nodes.filtersCount.firstChild.nodeValue = "(" + this.filtersCount + ")";
            else this.nodes.filtersCount.appendChild(document.createTextNode("(" + this.filtersCount + ")"));
            
            if(this.filtersCount == 0) Element.addClassName(this.nodes.parent, "filtergroup_has_no_filters");
            else Element.removeClassName(this.nodes.parent, "filtergroup_has_no_filters");
        }
    },

    /**
     * Fill main filter group values (name and spec field) and create translations for those values
     */
    loadFilterAction: function()
    {
        var self = this;

        // Default language
        if(this.id) this.nodes.id.value = this.id;

        if(this.name[this.languageCodes[0]]) this.nodes.name.value = this.name[this.languageCodes[0]];        
        
        this.nodes.name.name = "name[" + this.languageCodes[0] + "]";

        this.changeMainTitleAction(this.nodes.name.value);
        this.changeFiltersCount(this.filtersCount);


        // Translations
        var translations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations);
		var valuesTranslations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepValues);
        // we should have a template to continue
        if(translations.length > 0 && translations[0].className.split(' ').indexOf('dom_template') !== -1)
        {
            this.nodes.translations = new Array();
            for(var i = 1; i < this.languageCodes.length; i++)
            {
                // copy template class
                var newTranslation = translations[0].cloneNode(true);
                Element.removeClassName(newTranslation, "dom_template");
                
    			// bind it
                Event.observe(newTranslation.getElementsByTagName("legend")[0], "click", function(e) { self.changeTranslationLanguageAction(e) });

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
                        eval("if(self."+inputFields[j].name+"['"+self.languageCodes[i]+"']) inputFields[j].value = self."+inputFields[j].name+"['"+self.languageCodes[i]+"'];");
                        inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";
                    }
                }

                this.nodes.stepTranslations.appendChild(newTranslation);

                // add to nodes list
                this.nodes.translations[this.languageCodes[i]] = newTranslation;
                  
                // Create place for values translations
				var newValueTranslation = valuesTranslations[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");
				newValueTranslation.className += "_" + this.languageCodes[i];
                
                var valueTranslationLegend = document.getElementsByClassName(this.cssPrefix + "legend_text", newValueTranslation.getElementsByTagName("legend")[0])[0];
				valueTranslationLegend.appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));
                
                Event.observe(valueTranslationLegend.parentNode, "click", function(e) { self.toggleValueLanguage(e) });
                
				valuesTranslations[0].parentNode.appendChild(newValueTranslation);
                
                this.nodes.valuesTranslations[this.languageCodes[i]] = newValueTranslation;
    			this.nodes.translation_templates[this.languageCodes[i]] = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.valuesTranslations[this.languageCodes[i]])[0]
                this.nodes.translationsUl[this.languageCodes[i]] = document.getElementsByClassName(this.cssPrefix + "form_language_translation", this.nodes.valuesTranslations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
            }
        }

        // Delete language template, so that included in that template variables would not be sent to server
        Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
    },


    toggleValueLanguage: function(e)
    {
        if(!e.target) e.target = e.srcElement;
        
        var values = document.getElementsByClassName(this.cssPrefix + "form_language_translation", e.target.parentNode.parentNode)[0];
        values.style.display = (values.style.display == 'block') ? 'none' : 'block';
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (values.style.display == 'block') ? '[-] ' : '[+] ' ;
    },
    
    

    /**
     * Create filters from json Object
     *
     * @access private
     *
     */
    loadValueFieldsAction: function()
    {
        var self = this;
        
        if(this.filters)
        {
           
            $H(this.filters).each(function(value) {
                self.addFilter(value.value, value.key);
            });

            this.filtersList.touch();
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
     * Change translation language tab
     *
     * @param Event e Event
     *
     * @access private
     */
	changeTranslationLanguageAction: function(e)
	{
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);
        var currentTranslationNode = document.getElementsByClassName(this.cssPrefix + "language_translation", e.target.parentNode.parentNode)[0];               
        currentTranslationNode.style.display = (currentTranslationNode.style.display == 'block') ? 'none' : 'block';
        
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (currentTranslationNode.style.display == 'block') ? '[-] ' : '[+] ';
	},

    /**
     * Create appropriate fields in translation tab when creating new filter
     *
     * @param Event e Event
     *
     * @access private
     */
    addFilterFieldAction: function(e)
    {
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);

        var li = this.addFilter(null, "new" + Backend.Filter.prototype.countNewFilters, true);
        this.changeFiltersCount(this.filtersCount+1);
        this.filtersList.touch();
        this.bindDefaultFields();
        this.filtersList.highlight(li);
        
        Backend.Filter.prototype.countNewFilters++;
    },


    /**
     * Delete filter
     *
     * @param Event e Event
     *
     * @access private
     */
    deleteValueFieldAction: function(li, activeList)
    {
        var splitedHref = li.id.split("_");
        var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
        var id = (isNew ? 'new' : '') + splitedHref[splitedHref.length - 1];

        activeList.remove(li);
        this.changeFiltersCount(this.filtersCount-1);

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            var translatedValue = document.getElementById(this.cssPrefix + "form_filters_" + this.languageCodes[i] + "_" + id);

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
     */
    changeStateAction: function(e)
    {
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);

        var currentStep = this.cssPrefix + e.target.hash.substring(1);
        for(var i = 0; i < this.nodes.stepLevOne.length; i++)
        {
            this.nodes.stateLinks[i].id = this.cssPrefix + 'change_state' + this.id;

            if(this.nodes.stepLevOne[i].className.split(' ').indexOf(currentStep) === -1)
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
     * When some dumbass creates/modifies value in "Filters" step, we are automatically creating
     * a label for similar field in every language section in "Translations" step.
     *
     * @example If we tipe one in "Filters" step like so
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
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);
        
        var li = e.target.up('li');
        var splitedHref  = li.id.match(/(new)*(\d+)$/); //    splitedHref[splitedHref.length - 2] == 'new' ? true : false;
        var id = splitedHref[0];
        
        if(id.match(/^new/))
        {
    		// generate handle
            var handleParagraph = document.getElementsByClassName('filter_handle', li)[0];
    		handleParagraph.getElementsByTagName('input')[0].value = ActiveForm.prototype.generateHandle(e.target.value);
        }

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            $(this.cssPrefix + "form_filters_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].innerHTML = e.target.value;
        }
    },


    /**
     * When we are filling spec field name in "Main" step we are changing it's handle and a title
     * on the top of the form. Handle is actuali a stripped version of spec field name with all spec
     * symbols changed to "_" (underscope)
     *
     * @param Event e Event 
     *
     * @access private
     *
     */
    generateTitleAction: function(e)
    {
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
     * Here we are adding new field to filters list in "Filters" step and "Translations" step.
     *
     * @param hash value Value of newly created field. The value is a hash array with value for every language {'en': "One", 'lt': "Vienas", 'de': "Einz"}
     * @param int id Id of a newly created field
     *
     * @access private
     *
     */
    addFilter: function(value, id, generateTitle)
    {        
        var self = this;
        if(!value) value = {}
        if(!this.filtersList) this.bindDefaultFields();
        
        var li = this.filtersList.addRecord(id, this.nodes.filterTemplate);
        Element.addClassName(li, this.cssPrefix + "default_filter_li");

        var nameValue = (value.name && value.name[self.languageCodes[0]]) ? value.name[self.languageCodes[0]] : '';

        // Filter name
        var filter_name_paragraph = li.down('.filter_name');
        var input = filter_name_paragraph.down("input");
        input.name = "filters[" + id + "][name][" + self.languageCodes[0] + "]";
        input.value = nameValue;
        Event.observe(input, "keyup", function(e) { self.mainValueFieldChangedAction(e) }, false);

        filter_name_paragraph.siblings().each(function(paragraph) 
        {
            if(Element.hasClassName(paragraph, 'filter_handle'))
            {
                // Handle name
                var handleInput = paragraph.down("input");
                handleInput.name = "filters[" + id + "][handle]";
                handleInput.value = (value.handle) ? value.handle : '' ;
            }
            else if(Element.hasClassName(paragraph, 'filter_range'))
            {
                // Numeric range
                var rangeStartInput = paragraph.down("input");
                var rangeEndInput = rangeStartInput.next("input");
                
                rangeStartInput.name = "filters[" + id + "][rangeStart]";
                rangeStartInput.value = (value.rangeStart) ? value.rangeStart : '' ;
                
                rangeEndInput.name = "filters[" + id + "][rangeEnd]";
                rangeEndInput.value = (value.rangeEnd) ? value.rangeEnd : '' ;
                
                Event.observe(rangeStartInput, "keyup", function(e) { self.rangeChangedAction(e) });
                Event.observe(rangeEndInput, "keyup", function(e) { self.rangeChangedAction(e) });      
            }
            else if(Element.hasClassName(paragraph, 'filter_selector'))
            {
                // Select
                var specFieldValueIDInput = paragraph.down("select");
                specFieldValueIDInput.name = "filters[" + id + "][specFieldValueID]";
                specFieldValueIDInput.value = (value.SpecFieldValue && value.SpecFieldValue.ID) ? value.SpecFieldValue.ID : '' ;
                Event.observe(specFieldValueIDInput, "change", function(e) { self.generateTitleAndHandleFromSpecFieldValue(li) });    
            }
            else if(Element.hasClassName(paragraph, 'filter_date_range'))
            {
                // Date range.
                var rangeDateStart = paragraph.down("input");
                var rangeDateEnd = rangeDateStart.next("input");
                
                var rangeDateStartButton = paragraph.down("img.calendar_button");
                var rangeDateEndButton   = rangeDateStartButton.next("img.calendar_button");
                
                var rangeDateStartReal   = paragraph.down("input." + self.cssPrefix + "date_start_real");
                var rangeDateEndReal     = paragraph.down("input." + self.cssPrefix + "date_end_real");
        
                rangeDateStart.id         = self.cssPrefix + "rangeDateStart_" + id;
                rangeDateEnd.id           = self.cssPrefix + "rangeDateEnd_" + id;
                rangeDateStartReal.id     = rangeDateStart.id + "_real";
                rangeDateEndReal.id       = rangeDateEnd.id + "_real";
                rangeDateStartButton.id   = rangeDateStart.id + "_button";
                rangeDateEndButton.id     = rangeDateEnd.id + "_button";      
                
                rangeDateStart.name       = "filters[" + id + "][rangeDateStart_show]";
                rangeDateEnd.name         = "filters[" + id + "][rangeDateEnd_show]";
                rangeDateStartReal.name   = "filters[" + id + "][rangeDateStart]";
                rangeDateEndReal.name     = "filters[" + id + "][rangeDateEnd]";
                      
                rangeDateStartReal.value  = (value.rangeDateStart) ? value.rangeDateStart : '' ;
                rangeDateEndReal.value    = (value.rangeDateEnd) ? value.rangeDateEnd : '' ;
                
                rangeDateStart.value  = rangeDateStartReal.value;
                rangeDateEnd.value    = rangeDateEndReal.value ;
                  
                rangeDateStart.value = Date.parseDate(rangeDateStartReal.value, "%y-%m-%d").print(self.dateFormat);
                rangeDateEnd.value = Date.parseDate(rangeDateEnd.value, "%y-%m-%d").print(self.dateFormat);
                           
                Event.observe(rangeDateStartButton, "mousedown", function(e){
                    if(!self.filterCalendars[rangeDateStart.id]) 
                    {
                        self.filterCalendars[rangeDateStart.id] = true;
                        Calendar.setup( {
                            inputField:       rangeDateStart.id,
                            inputFieldReal:   rangeDateStartReal.id,
                            ifFormat:         self.dateFormat, 
                            button:           rangeDateStartButton.id,
                            eventName:        'mouseup',
                            cache: true
                        });
                    }
                });
          
                Event.observe(rangeDateEndButton, "mousedown", function(e){
                    if(!self.filterCalendars[rangeDateEnd.id])
                    {
                        self.filterCalendars[rangeDateEnd.id] = true;
                        Calendar.setup({
                            inputField:       rangeDateEnd.id,
                            inputFieldReal:   rangeDateEndReal.id,
                            ifFormat:         self.dateFormat, 
                            button:           rangeDateEndButton.id,
                            eventName:        'mouseup',
                            cache: true
                        });
                    }
                });
            }
 
        });
       
        if(generateTitle) this.generateTitleAndHandleFromSpecFieldValue(li);
        
		// now insert all translation fields
		for(var i = 1; i < this.languageCodes.length; i++)
		{
            var newValueTranslation = this.nodes.translation_templates[this.languageCodes[i]].cloneNode(true);
            var translationsUl = this.nodes.translationsUl[this.languageCodes[i]];
            var inputTranslation = newValueTranslation.down("input");

			inputTranslation.name = "filters[" + id + "][name][" + this.languageCodes[i] + "]";
			inputTranslation.value = (value && value.name && value.name[this.languageCodes[i]]) ? value.name[this.languageCodes[i]] : '' ;

            newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;
            newValueTranslation.down("label").update(nameValue);
			
            // add to node tree
			translationsUl.id = this.cssPrefix + "form_" + this.id + '_values_' + this.languageCodes[i];
			translationsUl.appendChild(newValueTranslation);
            
            Element.removeClassName(newValueTranslation, "dom_template");
		}
        
        return li;
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
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);

        // first cancel all modifications if they took place
        if(this.id == 'new')
        {
            this.recreate(this.filter, true);
        }
        else if(Form.hasBackup(this.nodes.form) && this.formChanged)
        {
            Form.restore(this.nodes.form);

            this.changeMainTitleAction(this.nodes.name.value);
            this.specFieldIDWasChangedAction();
        }
        
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);

        // Use Active list toggleContainer() method if this filter is inside Active list
        // Note that if it is inside a list we are showing and hidding form with the same action,
        // butt =] when dealing with new form showing form action is handled by Backend.Filter::createNewAction()
        if(this.nodes.parent.tagName.toLowerCase() == 'li')
        {
             ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleContainer(this.nodes.parent, 'edit');
        }
        else
        {
            this.hideNewFilterAction(this.categoryID);
        }
    },

    /**
     * This method is called when user clicks on save button. It saves form filters, and does i don't know what (i guess it should close the form)
     *
     * @param Event e Event
     *
     * @access public
     *
     */
    saveAction: function(e)
    {
        if(!e.target) e.target = e.srcElement;

        Event.stop(e);
        
        this.saveFilterGroup();
    },

    saveFilterGroup: function()
    {
        // Toggle progress won't work on new form
        try
        {
             ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleProgress(this.nodes.parent);
        }
        catch (e)
        {
            ActiveForm.prototype.offProgress(this.nodes.form);
        }

        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        
        var self = this;
        new Ajax.Request(
            this.nodes.form.action,
            {
                method: this.nodes.form.method,
                postBody: Form.serialize(this.nodes.form),
                onComplete: function(param) {
                    console.info(param.responseText);
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
                Element.addClassName(div, 'filter_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                
                var activeList = ActiveList.prototype.getInstance($(this.cssPrefix + "items_list_" + this.categoryID));
                
                var filterCount = document.getElementsByClassName(this.cssPrefix + "default_filter_li", this.nodes.filtersDefaultGroup).length;
                var spanCount = document.createElement('span');
                Element.addClassName(spanCount, this.cssPrefix + "count");
                spanCount.update(" (" + filterCount + ")");
                
                var li = activeList.addRecord(jsonResponse.id, [div, spanCount]);
                if(0 == filterCount) Element.addClassName(li, 'filtergroup_has_no_filters');
                activeList.touch();
                
                CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
                
                this.hideNewFilterAction(this.categoryID);
                this.recreate(this.filter, true);   
            }
        }
        else if(jsonResponse.errors)
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, jsonResponse.errors);
        }

        // Toggle progress won't work on new form
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
    hideNewFilterAction: function(categoryID)
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
        if(!e.target)e.target = e.srcElement;
        
        Event.stop(e);

        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");     
        
        ActiveList.prototype.getInstance("filter_items_list_" + categoryID).collapseAll();
        ActiveForm.prototype.showNewItemForm(link, form);
    }
}
