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
             var categoryID = this.getRecordId(li, 2);
			 Backend.Filter.prototype.hideNewFilterAction(categoryID);
              
             if(this.isContainerEmpty(li, 'edit')) return Backend.Filter.prototype.links.editGroup + this.getRecordId(li) + "/?categoryID=" + categoryID
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
            
            this.hideSpecField();
            this.toggleFilters();
            
            new SectionExpander(this.nodes.parent)
        }
        catch(e)
        {
            console.trace();
            console.info(e);
        }
    },

    getSpecField: function()
    {
        var specField = {};
        for(var k = 0; k < this.specFields.length; k++) 
        {
            if(this.specFields[k].ID == this.nodes.specFieldID.value) 
            {
                var specField = this.specFields[k];
                break;
            }
        }
        
        return specField;
    },

    toggleFilters: function()
    {
        var specField = this.getSpecField();
        var showFilters = this.selectorValueTypes.indexOf(specField.type) === -1;
        
        if(showFilters) 
        {
            this.nodes.stepFilters.show(); 
        }
        else 
        {
            this.nodes.stepFilters.hide();
        }
        
        for(var i = 1; i < this.languageCodes.length; i++)
        {
   			var filterTranslations = this.nodes.translationsUl[this.languageCodes[i]].up("fieldset");           
            if(showFilters) filterTranslations.show(); else filterTranslations.hide();
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
        this.nodes.name.value             = '';
        this.nodes.specFieldID            = document.getElementsByClassName(this.cssPrefix + "form_specFieldID", this.nodes.parent)[0];
        this.nodes.specFieldText          = document.getElementsByClassName(this.cssPrefix + "form_specFieldText", this.nodes.parent)[0];
        this.nodes.specFieldParagraph     = document.getElementsByClassName(this.cssPrefix + "specField", this.nodes.parent)[0];
               
        this.nodes.stepTranslations       = document.getElementsByClassName(this.cssPrefix + "step_translations", this.nodes.parent)[0];
        this.nodes.stepFiltersTranslations= document.getElementsByClassName(this.cssPrefix + "step_filters_translations", this.nodes.parent)[0];
        this.nodes.stepFilters = document.getElementsByClassName(this.cssPrefix + "step_filters", this.nodes.parent)[0];
        
        this.nodes.filtersTranslationTemplate = this.nodes.stepTranslations.down("." + this.cssPrefix + "form_filters_value");
        this.nodes.generateFiltersLink    = document.getElementsByClassName(this.cssPrefix + "generate_filters", this.nodes.parent)[0];
        this.nodes.defaultFiltersList     = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.filtersDefaultGroup);

        this.nodes.mainTitle              = document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
        this.nodes.filtersCount           = document.getElementsByClassName(this.cssPrefix + "count", this.nodes.parent)[0];
        this.nodes.cancel                 = document.getElementsByClassName(this.cssPrefix + "cancel", this.nodes.parent)[0];
        this.nodes.cancelNewItemLink      = $("filter_item_new_" + this.categoryID + "_cancel");
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
        ul.id = this.cssPrefix + "form_" + this.id + '_filters_' + this.languageCodes[0];
        
        var self = this;
        this.nodes.labels = {};  
        $A(['name', 'specFieldID']).each(function(field)
        {
            self.nodes.labels[field] = document.getElementsByClassName(self.cssPrefix + "form_" + field + "_label", self.nodes.parent)[0];
        });  
    },

    hideSpecField: function()
    {
        if(!this.id.match(/new/)) 
        {
            var specField = this.getSpecField();
            
            this.nodes.specFieldID.hide();
            this.nodes.specFieldText.update(specField.name_lang);
            this.nodes.specFieldText.show();
        }
        else
        {
            this.nodes.specFieldID.show();
            this.nodes.specFieldText.hide();
        }
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
     * Binds fields to some events
     */
    bindFields: function()
    {
        var self = this;

        Event.observe(this.nodes.name, "keyup", function(e) { self.generateTitleAction(e) });
        Event.observe(this.nodes.addFilterLink, "click", function(e) { Event.stop(e); self.addFilterFieldAction() });
        
        Event.observe(this.nodes.specFieldID, "change", function(e) { Event.stop(e); self.specFieldIDWasChangedAction() });        
        Event.observe(this.nodes.specFieldID, "change", function(e) { Event.stop(e); self.generateTitleFromSpecField() });
        Event.observe(this.nodes.specFieldID, "change", function(e) { self.toggleFilters(); } );
        
        Event.observe(this.nodes.cancel, "click", function(e) { Event.stop(e); self.cancelAction() });
        Event.observe(this.nodes.cancelNewItemLink, "click", function(e) { Event.stop(e); self.cancelAction(); });
        
        Event.observe(this.nodes.save, "click", function(e) { Event.stop(e); self.saveAction() });
        
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
            self.nodes.specFieldID.options[self.nodes.specFieldID.options.length] = new Option(value.name_lang, value.ID);
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
            if(this.specFields[i].ID != this.nodes.specFieldID.value) 
            {
                continue;   
            }
            else if(self.selectorValueTypes.indexOf(this.specFields[i].type) !== -1)
            {
                return;
            }
            
            var specField = this.specFields[i];
           
            $A(this.nodes.filtersDefaultGroup.down('ul').getElementsByTagName("li")).each(function(li)
            {                    
                if(specField.type == Backend.SpecField.prototype.TYPE_NUMBERS_SIMPLE)
                {
                    li.down('.filter_range').show();
                }
                else
                {
                    li.down('.filter_range').hide();
                }                      
 
                if (specField.type == Backend.SpecField.prototype.TYPE_TEXT_DATE) 
                {
                    li.down('.filter_date_range').style.display = 'block';
                }
                else
                {
                    li.down('.filter_date_range').style.display = 'none';
                }
            });

            return;
        }
    },
    
    
    generateTitleFromSpecField: function()
    {    
        var self = this;
        var newTitle = '';
        var changeTitle = false;
        
        this.specFields.each(function(specField) {
            if(self.nodes.name.value == specField.name_lang) changeTitle = true;
            if(specField.ID == self.nodes.specFieldID.value) newTitle = specField.name_lang;
        });
        
        if(changeTitle || self.nodes.name.value == '') 
        {
            self.nodes.name.value = newTitle;
            this.generateTitleAction(e);
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

        this.nodes.name.value = this.filter.name_lang ? this.filter.name_lang : '';     
        this.nodes.name.name = "name[" + this.languageCodes[0] + "]";
        this.nodes.labels.name.onclick = function() { self.nodes.name.focus() };
        this.nodes.labels.specFieldID.onclick = function() { self.nodes.specFieldID.focus() };

        this.changeMainTitleAction(this.nodes.name.value);
        this.changeFiltersCount(this.filtersCount);

        // Translations
        var translations = this.nodes.stepTranslations.down("." + this.cssPrefix + "step_translations_language");
        
        // we should have a template to continue
        this.nodes.translations = new Array();
        for(var i = 1; i < this.languageCodes.length; i++)
        {
            // copy template class
            var newTranslation = translations.cloneNode(true);
            Element.removeClassName(newTranslation, "dom_template");
            
            newTranslation.className += this.languageCodes[i];
            newTranslation.down("legend").update(this.languages[this.languageCodes[i]]);

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
                    translationLabel.for = translationId;
                    
                    Event.observe(translationLabel, "click", function(e) { 
                        console.info(this.for);
                        $(this.for).focus();
                    });
                    
                    inputFields[j].id = translationId;
					if(self.filter[inputFields[j].name + "_" + self.languageCodes[i]]) inputFields[j].value = self.filter[inputFields[j].name + "_" + self.languageCodes[i]];
					inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";

                }
            }

            this.nodes.stepTranslations.appendChild(newTranslation);
   			this.nodes.translationsUl[this.languageCodes[i]] = newTranslation.down("." + this.cssPrefix + "form_language_translation").down('ul');
        }

        // Delete language template, so that included in that template variables would not be sent to server
        Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
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
     * Create appropriate fields in translation tab when creating new filter
     *
     * @param Event e Event
     *
     * @access private
     */
    addFilterFieldAction: function()
    {
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
        var nameValue = value.name_lang ? value.name_lang : '';

        // Filter name
        var filter_name_paragraph = li.down('.filter_name');
        var input = filter_name_paragraph.down("input");
        input.name = "filters[" + id + "][name][" + self.languageCodes[0] + "]";
        input.value = nameValue;
        Event.observe(input, "keyup", function(e) { self.mainValueFieldChangedAction(e) }, false);
        Event.observe(input, "keyup", function(e) {
                if(!this.up('li').next() && this.value != '') self.addFilterFieldAction();
            });
        var label = filter_name_paragraph.down("label"); 
        input.id = this.cssPrefix + "filter_filter_" + id + "_name";
        label['for'] = input.id;
        label.onclick = function() { $(this["for"]).focus() };

        filter_name_paragraph.siblings().each(function(paragraph) 
        {
            var part = false;
            if(Element.hasClassName(paragraph, 'filter_range'))
            {
                part = "range";
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
            else if(Element.hasClassName(paragraph, 'filter_date_range'))
            {
                part = "date_range";
                
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
                           
                rangeDateStartButton.realInput  = rangeDateStart.realInput  = rangeDateStartReal;
                rangeDateEndButton.realInput    = rangeDateEnd.realInput    = rangeDateEndReal;
                rangeDateStartButton.showInput  = rangeDateStart.showInput  = rangeDateStart;
                rangeDateEndButton.showInput    = rangeDateEnd.showInput    = rangeDateEnd;
                                               
                rangeDateStartReal.value  = (value.rangeDateStart) ? value.rangeDateStart : (new Date()).print("%Y-%m-%d");
                rangeDateEndReal.value    = (value.rangeDateEnd) ? value.rangeDateEnd : (new Date()).print("%y-%m-%d");
                
                rangeDateStart.value  = rangeDateStartReal.value;
                rangeDateEnd.value    = rangeDateEndReal.value ;
                  
                rangeDateStart.value = Date.parseDate(rangeDateStartReal.value, "%y-%m-%d").print(self.dateFormat);
                rangeDateEnd.value = Date.parseDate(rangeDateEnd.value, "%y-%m-%d").print(self.dateFormat);
                         
                Event.observe(rangeDateStart,       "keyup",     Calendar.updateDate );
                Event.observe(rangeDateEnd,         "keyup",     Calendar.updateDate );
                Event.observe(rangeDateStart,       "blur",      Calendar.updateDate );
                Event.observe(rangeDateEnd,         "blur",      Calendar.updateDate );
                Event.observe(rangeDateStartButton, "mousedown", Calendar.updateDate );
                Event.observe(rangeDateEndButton,   "mousedown", Calendar.updateDate );
                            
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
 
            if(part)
            {
                input = paragraph.down("input");
                label = paragraph.down("label"); 
                input.id = self.cssPrefix + "filter_filter_" + id + "_" + part;
                label['for'] = input.id;
                label.onclick = function() { $(this["for"]).focus() };
            }
        });

                       
		// now insert all translation fields
		for(var i = 1; i < this.languageCodes.length; i++)
		{
            var newValueTranslation = this.nodes.filtersTranslationTemplate.cloneNode(true);
            Element.removeClassName(newValueTranslation, "dom_template");
            
            var translationsUl = this.nodes.translationsUl[this.languageCodes[i]];
            var inputTranslation = newValueTranslation.down("input");

			inputTranslation.name = "filters[" + id + "][name][" + this.languageCodes[i] + "]";
			inputTranslation.value = value["name_" + this.languageCodes[i]] ? value["name_" + this.languageCodes[i]] : '' ;

            newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;
            var translationLabel = newValueTranslation.down("label");
            translationLabel.update(nameValue);
            
            inputTranslation.id = this.cssPrefix + "filter_filter_" + id + "_name_" + this.languageCodes[i];
            translationLabel['for'] = inputTranslation.id;
            translationLabel.onclick = function() { $(this['for']).focus(); }           
            
            // add to node tree
			translationsUl.id = this.cssPrefix + "form_" + this.id + '_values_' + this.languageCodes[i];
			translationsUl.appendChild(newValueTranslation);
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
    cancelAction: function()
    {
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
    saveAction: function()
    {
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
            ActiveForm.prototype.updateNewFields('filter_update', $H(jsonResponse.newIDs), this.nodes.parent)
            
            Form.backup(this.nodes.form);
            this.backupName = this.nodes.name.value;

            if(this.nodes.parent.tagName.toLowerCase() == 'li')
            {
                try
                {
                    var specField = this.getSpecField();
                    if(this.selectorValueTypes.indexOf(specField.type) === -1)
                    {
                        var filters = document.getElementsByClassName(this.cssPrefix + "default_filter_li", this.nodes.filtersDefaultGroup);
                        var filterCount = filters.length;
                        if(filters[filterCount - 1].down(".filter_name").down("input").value == '') filterCount--;
                        this.changeFiltersCount(filterCount);
                    }
                }
                catch(e)
                {
                    console.info(e);
                }

                ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleContainer(this.nodes.parent, 'edit');
            }
            else
            {
                var div = document.createElement('span');
                Element.addClassName(div, 'filter_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                
                var activeList = ActiveList.prototype.getInstance($(this.cssPrefix + "items_list_" + this.categoryID));
            
                var specField = this.getSpecField();
                
                var filterCount = 0;
                if(this.selectorValueTypes.indexOf(specField.type) === -1)
                {
                    var filters = document.getElementsByClassName(this.cssPrefix + "default_filter_li", this.nodes.filtersDefaultGroup);
                    filterCount = filters.length;
                    if(filters[filterCount - 1].down(".filter_name").down("input").value == '') filterCount--;
                }
                else
                {
                    filterCount = specField.values.length;
                }
                                
                var spanCount = document.createElement('span');
                Element.addClassName(spanCount, this.cssPrefix + "count");
                spanCount.update(" (" + filterCount + ")");
                
                var newRecord = document.createElement('div');
                newRecord.appendChild(div);
                newRecord.appendChild(spanCount);
                
                var li = activeList.addRecord(jsonResponse.id, newRecord);
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
        ActiveForm.prototype.hideMenuItems($(this.cssPrefix + "new_" + categoryID + "_menu"), [$(this.cssPrefix + "item_new_" + categoryID + "_show")]);
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
        ActiveForm.prototype.showNewItemForm($(this.cssPrefix + "item_new_" + categoryID + "_show"), $(this.cssPrefix + "item_new_"+categoryID+"_form"));  
        ActiveForm.prototype.hideMenuItems($(this.cssPrefix + "new_" + categoryID + "_menu"), [$(this.cssPrefix + "item_new_" + categoryID + "_cancel")]);
    }
}
